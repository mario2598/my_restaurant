<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Traits\SpaceUtil;

class TicketesImpresosController extends Controller
{
    use SpaceUtil;
    private $admin;
    private $pdf;
    /** Tamaño máximo del logo en bytes para no agotar memoria en FPDF (~1 MB). */
    private const LOGO_MAX_BYTES = 1048576;

    public function __construct()
    {
        $this->pdf = new Fpdf();
        setlocale(LC_ALL, "es_ES");
    }

    /**
     * Devuelve 'PNG' o 'JPEG' si el archivo es imagen válida y no excede LOGO_MAX_BYTES; null en caso contrario.
     * Evita "Not a PNG file" y "Allowed memory size exhausted" al no cargar logos enormes.
     */
    private function getLogoImageType($path)
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }
        if (filesize($path) > self::LOGO_MAX_BYTES) {
            return null;
        }
        $info = @getimagesize($path);
        if ($info === false || !isset($info[2])) {
            return null;
        }
        if ($info[2] === IMAGETYPE_PNG) {
            return 'PNG';
        }
        if ($info[2] === IMAGETYPE_JPEG) {
            return 'JPEG';
        }
        return null;
    }

    private function getLogoFacturaPath($sucursalFactura)
    {
        if ($sucursalFactura != null && !empty($sucursalFactura->url_logo_factura)) {
            $logoSucursal = public_path($sucursalFactura->url_logo_factura);
            if (is_file($logoSucursal) && is_readable($logoSucursal)) {
                return $logoSucursal;
            }
        }

        return public_path() . '/assets/images/default-logo.png';
    }

    /**
     * Convierte texto UTF-8 a ISO-8859-1 para FPDF. Omite caracteres no representables
     * (emojis, iconos, etc.) para evitar "Detected an illegal character in input string".
     */
    private function toLatin1($str)
    {
        if ($str === null || $str === '') {
            return (string) $str;
        }
        $str = (string) $str;
        $result = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $str);
        return $result !== false ? $result : preg_replace('/[^\x20-\x7E\xA0-\xFF]/u', '', $str);
    }

    /**
     * Símbolo apto para FPDF/Helvetica (solo ASCII): si el símbolo de BD tiene caracteres no imprimibles
     * en el tiquete (₡, €, etc.), se usa el código ISO de la moneda.
     */
    private function simboloMonedaPdfLegible(?string $simbolo, ?string $codGeneral): string
    {
        $cod = trim((string) ($codGeneral ?? ''));
        $s = trim((string) ($simbolo ?? ''));
        if ($s !== '' && preg_match('/^[\x20-\x7E]+$/', $s)) {
            return $s;
        }

        return $cod;
    }

    /**
     * Monto del cobro expresado en moneda del documento (usa total_moneda_doc si existe; si no, total en base / TC).
     */
    private function montoDocumentoDesdePago($pago): ?float
    {
        if ($pago === null) {
            return null;
        }
        if (isset($pago->total_moneda_doc) && $pago->total_moneda_doc !== null && $pago->total_moneda_doc !== '') {
            return (float) $pago->total_moneda_doc;
        }
        $tc = (float) ($pago->tipo_cambio_snapshot ?? 0);
        $base = (float) ($pago->total ?? 0);
        if ($tc <= 0) {
            return null;
        }

        return $base / $tc;
    }

    /**
     * Cobro en moneda contable base (no se imprime bloque de tipo de cambio).
     */
    private function pagoOrdenEsMonedaBase(?object $pago): bool
    {
        if ($pago === null) {
            return true;
        }
        $flag = $pago->moneda_es_base ?? null;

        return $flag === 'S' || $flag === 's';
    }

    /**
     * Cobros de la orden con moneda y TC registrados (incluye moneda base; orden cronológico).
     * Usado en el resumen "Cobros" del tiquete de orden completa / pre-tiquete.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function pagosOrdenConMonedaDocs(int $idOrden)
    {
        try {
            return DB::table('pago_orden')
                ->join('sis_moneda', 'sis_moneda.id', '=', 'pago_orden.moneda_factura_id')
                ->where('pago_orden.orden', '=', $idOrden)
                ->whereNotNull('pago_orden.moneda_factura_id')
                ->whereNotNull('pago_orden.tipo_cambio_snapshot')
                ->orderBy('pago_orden.id')
                ->select(
                    'pago_orden.id',
                    'pago_orden.total',
                    'pago_orden.tipo_cambio_snapshot',
                    'pago_orden.total_moneda_doc',
                    'pago_orden.nombre_cliente',
                    'sis_moneda.cod_general as moneda_cod_general',
                    'sis_moneda.simbolo as moneda_simbolo',
                    'sis_moneda.es_base as moneda_es_base'
                )
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Indica si el set de pagos incluye al menos un cobro en moneda no base.
     */
    private function pagosIncluyenMonedaNoBase($pagos): bool
    {
        if ($pagos === null || ! method_exists($pagos, 'isEmpty') || $pagos->isEmpty()) {
            return false;
        }

        foreach ($pagos as $p) {
            if (! $this->pagoOrdenEsMonedaBase($p)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Un solo cobro (tiquete por pago): moneda, TC y monto en doc., formato breve.
     *
     * @param  object|null  $pagoRef  Fila pago_orden + moneda_cod_general, moneda_simbolo, tipo_cambio_snapshot, total, total_moneda_doc
     * @return int Altura aproximada en mm usada en el PDF
     */
    private function escribirBloqueTipoCambioSiAplica($pagoRef): int
    {
        if ($pagoRef === null || empty($pagoRef->tipo_cambio_snapshot) || empty($pagoRef->moneda_factura_id)) {
            return 0;
        }
        if ($this->pagoOrdenEsMonedaBase($pagoRef)) {
            return 0;
        }
        $cod = $pagoRef->moneda_cod_general ?? '';
        $simDoc = $this->simboloMonedaPdfLegible($pagoRef->moneda_simbolo ?? null, $cod);
        $tc = number_format((float) $pagoRef->tipo_cambio_snapshot, 2, '.', ',');
        $mDoc = $this->montoDocumentoDesdePago($pagoRef);
        $mDocTxt = $mDoc !== null ? number_format($mDoc, 2, '.', ',') : '—';
        $baseTxt = number_format((float) ($pagoRef->total ?? 0), 2, '.', ',');
        $montoConSim = 'Monto ' . ($simDoc !== '' ? $simDoc . ' ' : '') . $mDocTxt;

        $this->pdf->Ln(5);
        $this->pdf->SetFont('Helvetica', '', 6);
        $this->pdf->setX(6);
        $linea = $cod . ' | TC ' . $tc . ' | ' . $montoConSim . ' | Conversion ' . $baseTxt;
        $this->pdf->MultiCell(63, 3, $this->toLatin1($linea), 0);

        return 9;
    }

    /**
     * Varios cobros (tiquete orden completa): listado compacto por cada pago con moneda/TC.
     *
     * @return int Altura aproximada en mm
     */
    private function escribirResumenPagosMonedaOrden(int $idOrden): int
    {
        $pagos = $this->pagosOrdenConMonedaDocs($idOrden);
        if ($pagos->isEmpty()) {
            return 0;
        }
        if (! $this->pagosIncluyenMonedaNoBase($pagos)) {
            return 0;
        }

        $this->pdf->Ln(9);
        $this->pdf->SetFont('Helvetica', 'B', 6);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 3, $this->toLatin1('Cobros'), 0, 1);
        $this->pdf->Ln(2);
        $this->pdf->SetFont('Helvetica', '', 6);

        $lineas = 0;
        foreach ($pagos as $p) {
            $this->pdf->setX(6);
            $tc = number_format((float) $p->tipo_cambio_snapshot, 2, '.', ',');
            $cod = $p->moneda_cod_general ?? '';
            $simDoc = $this->simboloMonedaPdfLegible($p->moneda_simbolo ?? null, $cod);
            $mDoc = $this->montoDocumentoDesdePago($p);
            $mDocTxt = $mDoc !== null ? number_format($mDoc, 2, '.', ',') : '—';
            $baseTxt = number_format((float) ($p->total ?? 0), 2, '.', ',');
            $montoConSim = 'Monto ' . ($simDoc !== '' ? $simDoc . ' ' : '') . $mDocTxt;
            $linea = '#' . (int) $p->id . ' ' . $cod . ' | TC ' . $tc . ' | ' . $montoConSim . ' | Conversion ' . $baseTxt;
            $this->pdf->MultiCell(63, 3, $this->toLatin1($linea), 0);
            $nombreCli = trim((string) ($p->nombre_cliente ?? ''));
            if ($nombreCli !== '') {
                $this->pdf->setX(6);
                $this->pdf->SetFont('Helvetica', 'I', 6);
                $this->pdf->MultiCell(63, 3, $this->toLatin1('Cliente: ' . $nombreCli), 0);
                $this->pdf->SetFont('Helvetica', '', 6);
            }
            $this->pdf->Ln(1);
            $lineas++;
        }

        return 12 + ($lineas * 7);
    }

    public function index() {}

    public function generarFacturacionOrdenPdf($idOrden)
    {

        if (strpos($idOrden, ':') !== false) {

            list($numeroFactura, $idPago) = explode(':', $idOrden);

            $pagoExistente = DB::table('pago_orden')->where('id', '=', $idPago)->first();
            if (!$pagoExistente) {
                return $this->responseAjaxServerError('El registro de pago no existe.', []);
            }

            $this->generarFacturaPorPago($idPago);
        } else {
            $this->generarFacturaOrdenPdf($idOrden);
        }
    }

    public function generarFacturaOrdenPdf($idOrden)
    {

        $res = OrdenesController::getOrden($idOrden);
        if (!$res['estado']) {
            $this->setError("Imprimir tiquete", "Al parecer no se encontro la orden solicitada.");
            return redirect('/');
        }
        $orden = $res['orden'];
        $detalles = $orden->detalles;

        $cantidadPagosOrden = 0;
        try {
            $cantidadPagosOrden = (int) DB::table('pago_orden')->where('orden', '=', $idOrden)->count();
        } catch (\Throwable $e) {
            $cantidadPagosOrden = 0;
        }

        $detallesAdicionales = DB::table('detalle_pago_orden')
            ->leftjoin('pago_orden', 'pago_orden.id', '=', 'detalle_pago_orden.pago_orden')
            ->select('detalle_pago_orden.*')
            ->where('pago_orden.orden', '=', $idOrden)
            ->whereNull('detalle_orden')->get();

        $sucursalFactura = MantenimientoSucursalController::getSucursalById($orden->sucursal);
        if ($sucursalFactura == null) {
            $nombre_empresa_fe = "";
            $cedula_empresa_fe = "";
            $correo_empresa_fe = "";
        } else {
            $nombre_empresa_fe = $sucursalFactura->nombre_factura ?? '';
            $cedula_empresa_fe = $sucursalFactura->cedula_factura ?? '';
            $correo_empresa_fe = $sucursalFactura->correo_factura ?? '';
        }

        $tamPdf = 120;

        $aumento = (count($detalles) + count($detallesAdicionales)) * 10;
        $aumento2 = 0;
        foreach ($detalles as $d) {
            $aumento2 = $aumento2 + count($d->extras) * 5;
        }
        $tamPdf = $tamPdf  + $aumento + $aumento2;
        if ($orden->mto_impuesto_servicio > 0) {
            $tamPdf = $tamPdf  + 10;
        }

        if (($sucursalFactura->factura_iva ?? 0) == 1) {
            $tamPdf = $tamPdf  + 10;
        }

        $pagosMonedaOrden = $this->pagosOrdenConMonedaDocs((int) $idOrden);
        $nPagosMoneda = $pagosMonedaOrden->count();
        if ($nPagosMoneda > 0 && $this->pagosIncluyenMonedaNoBase($pagosMonedaOrden)) {
            $tamPdf += (int) ceil(14 + ($nPagosMoneda * 7));
        }

        $titulo3 = $this->toLatin1( $nombre_empresa_fe ?? env('APP_NAME', 'SPACE SOFTWARE CR'));
        $titulo4 = $this->toLatin1( 'Cédula : ' . $cedula_empresa_fe ?? '---');
        $titulo5 = $this->toLatin1( 'Correo : ' . $correo_empresa_fe ?? '---');
        $sucursal = $this->toLatin1( 'Sucursal : ' . $orden->nombre_sucursal);
        $detalleMesa = $this->toLatin1( $orden->mesa == null ?  'Tipo : PARA LLEVAR' : 'Mesa : ' . $orden->numero_mesa);
        $numero_orden = $this->toLatin1( 'No.Orden : ' . $orden->numero_orden);
        if ($cantidadPagosOrden > 1) {
            $cliente = null;
        } elseif ($orden->nombre_cliente == null || $orden->nombre_cliente == "") {
            $cliente = null;
        } else {
            $cliente = $this->toLatin1( 'Cliente : ' . $orden->nombre_cliente);
        }

        $fecha = $this->toLatin1( 'Fecha : ' . $this->fechaFormat($orden->fecha_fin));

        $path = $this->getLogoFacturaPath($sucursalFactura);

        $this->pdf->__construct('P', 'mm', array(80, $tamPdf));
        $this->pdf->AcceptPageBreak(true);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial', 'B', 10);
        $logoType = $this->getLogoImageType($path);
        if ($logoType !== null) {
            $this->pdf->Image($path, 15, -5, 50, 50, $logoType);
            $this->pdf->Ln(28);
        } else {
            $this->pdf->Ln(5);
        }
        $this->pdf->SetFont('Helvetica', '', 7);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo3, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo4, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo5, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $sucursal, 0);
        $this->pdf->Ln(1);

        $this->pdf->SetFont('Helvetica', '', 7);
        $this->pdf->Ln(1);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $numero_orden, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $detalleMesa, 0);
        if ($cliente != null && $cliente != "") {
            $this->pdf->setX(6);
            $this->pdf->MultiCell(63, 4, $cliente, 0);
        }
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $fecha, 0);

        $this->pdf->Ln(5);
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->setX(21);
        $this->pdf->Cell(63, 3, $this->toLatin1( 'Detalle de la orden'), 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 0, '', 'T');
        $this->pdf->SetFont('Helvetica', 'B', 9);
        $this->pdf->setX(6);
        $this->pdf->SetFont('Arial', 'B', 8);    //Letra Arial, negrita (Bold), tam. 20
        $this->pdf->Cell(63, 4, 'Cantidad', 0);
        $this->pdf->setX(32);
        $this->pdf->Cell(63, 4, 'Precio U', 0);
        $this->pdf->setX(55);
        $this->pdf->Cell(63, 4, 'SubTotal', 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 0, '', 'T');
        $this->pdf->SetFont('Helvetica', '', 8);

        foreach ($detalles as $d) {
            $this->pdf->Ln(1);
            $this->pdf->setX(6);
            $producto = $d->nombre_producto;
            if ($d->monto_servicio > 0) {
                $producto .= ' ( + 10% )';
            }
            $totalExtra = 0;
            $this->pdf->MultiCell(63, 4, $this->toLatin1( $producto), 0);
            foreach ($d->extras as $e) {
                $this->pdf->Ln(1);
                $this->pdf->setX(10);
                $this->pdf->Cell(63, 4,  $this->toLatin1( $e->descripcion_extra), 0);
                $this->pdf->setX(32);
                $this->pdf->Cell(63, 4, "", 0);
                $this->pdf->setX(56);
                $this->pdf->Cell(63, 4, number_format($e->total, 2, ".", ","), 0);
                $this->pdf->Ln(4);
                $totalExtra = $totalExtra  + $e->total;
            }
            $this->pdf->Ln(1);
            $this->pdf->setX(10);
            $this->pdf->Cell(63, 4,  $d->cantidad, 0);
            $this->pdf->setX(32);
            $this->pdf->Cell(63, 4, number_format($d->precio_unidad, 2, ".", ","), 0);
            $this->pdf->setX(56);
            $this->pdf->Cell(63, 4, number_format(($d->precio_unidad * $d->cantidad) + $totalExtra, 2, ".", ","), 0);
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 0, '', 'T');
        }

        foreach ($detallesAdicionales as $d) {
            $this->pdf->Ln(1);
            $this->pdf->setX(6);
            $producto = $d->dsc_linea;

            $this->pdf->MultiCell(63, 4, $this->toLatin1( $producto), 0);

            $this->pdf->Ln(1);
            $this->pdf->setX(10);
            $this->pdf->Cell(63, 4,  $d->cantidad_pagada, 0);
            $this->pdf->setX(32);
            $this->pdf->Cell(63, 4, number_format($d->total / $d->cantidad_pagada, 2, ".", ","), 0);
            $this->pdf->setX(53);
            $this->pdf->Cell(63, 4, number_format(($d->total), 2, ".", ","), 0);
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 0, '', 'T');
        }
        $this->pdf->SetFont('Arial', '', 8);    //Letra Arial, negrita (Bold), tam. 20
        if (($sucursalFactura->factura_iva ?? 0) == 1) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'SubTotal', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format(($orden->subtotal + $orden->descuento) - $orden->mto_impuesto_servicio, 2, ".", ","), 0);
        } else {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'SubTotal', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format((($orden->subtotal + $orden->impuesto) + $orden->descuento) - $orden->mto_impuesto_servicio, 2, ".", ","), 0);
        }

        if ($orden->mto_impuesto_servicio > 0) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'Servicio a la Mesa (10%)', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format($orden->mto_impuesto_servicio, 2, ".", ","), 0);
        }

        if ($orden->descuento > 0) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'Descuento', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format($orden->descuento, 2, ".", ","), 0);
        }

        if (($sucursalFactura->factura_iva ?? 0) == 1) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'Impuesto (IVA)', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format($orden->impuesto, 2, ".", ","), 0);
        }

        $this->escribirResumenPagosMonedaOrden((int) $idOrden);

        $this->pdf->SetFont('Arial', 'B', 11);    //Letra Arial, negrita (Bold), tam. 20
        $this->pdf->Ln(6);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'Total', 0);
        $this->pdf->setX(55);
        $this->pdf->Cell(63, 4, number_format($orden->total_con_descuento, 2, ".", ","), 0);

        $this->pdf->Ln(10);
        $this->pdf->setX(14);

        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->MultiCell(63, 4, 'GRACIAS POR PREFERIRNOS ');

        $this->pdf->SetFont('Helvetica', 'B', 6);
        $this->pdf->setX(32);
        $this->pdf->Cell(63, 4, env('APP_NAME', 'SPACE SOFTWARE CR'));
        $this->pdf->Ln(10);
        // $this->footer();

        $this->pdf->Output('ordenNo-' . $orden->numero_orden . '.pdf', 'I');

        exit;
    }

    public function generarFacturaPorPago($idPago)
    {
        $pago_orden = DB::table('pago_orden')
            ->leftJoin('sis_moneda', 'sis_moneda.id', '=', 'pago_orden.moneda_factura_id')
            ->where('pago_orden.id', '=', $idPago)
            ->select(
                'pago_orden.*',
                'sis_moneda.simbolo as moneda_simbolo',
                'sis_moneda.nombre as moneda_nombre',
                'sis_moneda.cod_general as moneda_cod_general',
                'sis_moneda.es_base as moneda_es_base'
            )
            ->first();

        $res = OrdenesController::getOrdenPorPago($idPago);
        if (!$res['estado']) {
            $this->setError("Imprimir tiquete", "Al parecer no se encontro la orden solicitada.");
            return redirect('/');
        }
        $orden = $res['orden'];
        $detalles = $orden->detalles;

        $sucursalFactura = MantenimientoSucursalController::getSucursalById($orden->sucursal);
        if ($sucursalFactura == null) {
            $nombre_empresa_fe = "";
            $cedula_empresa_fe = "";
            $correo_empresa_fe = "";
        } else {
            $nombre_empresa_fe = $sucursalFactura->nombre_factura ?? '';
            $cedula_empresa_fe = $sucursalFactura->cedula_factura ?? '';
            $correo_empresa_fe = $sucursalFactura->correo_factura ?? '';
        }

        $tamPdf = 110;

        $aumento = count($detalles) * 10;
        $aumento2 = 0;
        foreach ($detalles as $d) {
            $aumento2 = $aumento2 + count($d->extras) * 5;
        }
        $tamPdf = $tamPdf  + $aumento + $aumento2;
       
        if ($orden->mto_impuesto_servicio > 0) {
            $tamPdf = $tamPdf  + 10;
        }

        if (($sucursalFactura->factura_iva ?? 0) == 1) {
            $tamPdf = $tamPdf  + 10;
        }
        if ($pago_orden && ! empty($pago_orden->tipo_cambio_snapshot) && ! empty($pago_orden->moneda_factura_id)
            && ! $this->pagoOrdenEsMonedaBase($pago_orden)) {
            $tamPdf += 11;
        }
        /**
         * Header
         */
        $titulo3 = $this->toLatin1( $nombre_empresa_fe ?? env('APP_NAME', 'SPACE SOFTWARE CR'));
        $titulo4 = $this->toLatin1( 'Cédula : ' . $cedula_empresa_fe ?? '---');
        $titulo5 = $this->toLatin1( 'Correo : ' . $correo_empresa_fe ?? '---');
        $sucursal = $this->toLatin1( 'Sucursal : ' . $orden->nombre_sucursal);
        $numero_orden = $this->toLatin1( 'No.Orden : ' . $orden->numero_orden . '-P');
        if ($pago_orden->nombre_cliente == null || $pago_orden->nombre_cliente == "") {
            $cliente = null;
        } else {
            $cliente = $this->toLatin1( 'Cliente : ' . $pago_orden->nombre_cliente);
        }

        $fecha = $this->toLatin1( 'Fecha : ' . $this->fechaFormat($pago_orden->fecha_pago));

        $path = $this->getLogoFacturaPath($sucursalFactura);

        $this->pdf->__construct('P', 'mm', array(80, $tamPdf));
        $this->pdf->AcceptPageBreak(true);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial', 'B', 10);
        $logoType = $this->getLogoImageType($path);
        if ($logoType !== null) {
            $this->pdf->Image($path, 15, -5, 50, 50, $logoType);
            $this->pdf->Ln(28);
        } else {
            $this->pdf->Ln(5);
        }
        $this->pdf->SetFont('Helvetica', '', 7);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo3, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo4, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo5, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $sucursal, 0);
        $this->pdf->Ln(1);

        $this->pdf->SetFont('Helvetica', '', 8);
        $this->pdf->Ln(1);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $numero_orden, 0);
        if ($cliente != null && $cliente != "") {
            $this->pdf->setX(6);
            $this->pdf->MultiCell(63, 4, $cliente, 0);
        }
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $fecha, 0);

        /** Fin Header */
        /**BODY */
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->setX(21);
        $this->pdf->Cell(63, 3, $this->toLatin1( 'Detalle de la orden'), 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 0, '', 'T');
        $this->pdf->SetFont('Helvetica', 'B', 9);
        $this->pdf->setX(6);
        $this->pdf->SetFont('Arial', 'B', 8);    //Letra Arial, negrita (Bold), tam. 20
        $this->pdf->Cell(63, 4, 'Cantidad', 0);
        $this->pdf->setX(32);
        $this->pdf->Cell(63, 4, 'Precio U', 0);
        $this->pdf->setX(55);
        $this->pdf->Cell(63, 4, 'SubTotal', 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 0, '', 'T');
        $this->pdf->SetFont('Helvetica', '', 9);


        foreach ($detalles as $d) {
            $this->pdf->Ln(1);
            $this->pdf->setX(6);
            $producto = $d->nombre_producto;
            if ($d->mto_impuesto_servicio > 0) {
                $producto .= ' ( + 10% )';
            }
            $totalExtra = 0;
            $this->pdf->MultiCell(63, 4, $this->toLatin1( $producto), 0);
            foreach ($d->extras as $e) {
                $this->pdf->Ln(1);
                $this->pdf->setX(10);
                $this->pdf->Cell(63, 4,  $this->toLatin1( $e->descripcion_extra), 0);
                $this->pdf->setX(32);
                $this->pdf->Cell(63, 4, "", 0);
                $this->pdf->setX(56);
                $this->pdf->Cell(63, 4, number_format($e->total, 2, ".", ","), 0);
                $this->pdf->Ln(4);
                $totalExtra = $totalExtra  + $e->total;
            }
            $this->pdf->Ln(1);
            $this->pdf->setX(10);
            $this->pdf->Cell(63, 4,  $d->cantidad_pagada, 0);
            $this->pdf->setX(32);
            $this->pdf->Cell(63, 4, number_format($d->precio_unidad, 2, ".", ","), 0);
            $this->pdf->setX(56);
            $this->pdf->Cell(63, 4, number_format(($d->precio_unidad * $d->cantidad_pagada) + $totalExtra, 2, ".", ","), 0);
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 0, '', 'T');
        }

        $this->pdf->SetFont('Arial', '', 8);    //Letra Arial, negrita (Bold), tam. 20
        if (($sucursalFactura->factura_iva ?? 0) == 1) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'SubTotal', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format(($pago_orden->subtotal + $pago_orden->descuento) - $pago_orden->impuesto_servicio, 2, ".", ","), 0);
        } else {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'SubTotal', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format((($pago_orden->subtotal + $pago_orden->iva) + $pago_orden->descuento) - $pago_orden->impuesto_servicio, 2, ".", ","), 0);
        }

        if ($pago_orden->impuesto_servicio > 0) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'Servicio a la Mesa (10%)', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format($pago_orden->impuesto_servicio, 2, ".", ","), 0);
        }

        if ($pago_orden->descuento > 0) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'Descuento', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format($pago_orden->descuento, 2, ".", ","), 0);
        }

        if (($sucursalFactura->factura_iva ?? 0) == 1) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'Impuesto (IVA)', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format($pago_orden->iva, 2, ".", ","), 0);
        }

        if (! $this->pagoOrdenEsMonedaBase($pago_orden) && ! empty($pago_orden->tipo_cambio_snapshot) && ! empty($pago_orden->moneda_factura_id)) {
            $this->pdf->Ln(3);
            $this->escribirBloqueTipoCambioSiAplica($pago_orden);
        }

        $this->pdf->SetFont('Arial', 'B', 11);    //Letra Arial, negrita (Bold), tam. 20
        $this->pdf->Ln(6);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'Total', 0);
        $this->pdf->setX(55);
        $this->pdf->Cell(63, 4, number_format($pago_orden->total, 2, ".", ","), 0);


        $this->pdf->Ln(10);
        $this->pdf->setX(14);

        $this->pdf->MultiCell(63, 4, 'GRACIAS POR PREFERIRNOS ');

        $this->pdf->SetFont('Helvetica', 'B', 6);
        $this->pdf->setX(32);
        $this->pdf->Cell(63, 4, env('APP_NAME', 'SPACE SOFTWARE CR'));
        $this->pdf->Ln(10);

        $this->pdf->Output('ordenNo-' . $orden->numero_orden . '.pdf', 'I');

        exit;
    }

    public function generarPreFacturacionOrdenPdf($idOrden)
    {
        if (!$this->validarSesion(array("ordList_cmds", "fac_ord"))) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $res = OrdenesController::getOrden($idOrden);
        if (!$res['estado']) {
            $this->setError("Imprimir tiquete", "Al parecer no se encontro la orden solicitada.");
            return redirect('/');
        }
        $orden = $res['orden'];
        $cantidadPagosOrdenPre = 0;
        try {
            $cantidadPagosOrdenPre = (int) DB::table('pago_orden')->where('orden', '=', $idOrden)->count();
        } catch (\Throwable $e) {
            $cantidadPagosOrdenPre = 0;
        }
        $titulo3 = $this->toLatin1( 'INVERSIONES FONSECA JIMÉNEZ EL AMANECER SOCIEDAD DE RESPONSABILIDAD LIMITADA');
        $titulo4 = $this->toLatin1( 'Cédula jurídica : 3-102-862760');
        $titulo5 = $this->toLatin1( 'Correo : panaderiamanecer@gmail.com');
        $detalles = $orden->detalles;
        $tamPdf = 125;
        $aumento = count($detalles) * 10;
        $tamPdf = $tamPdf  + $aumento;
        $pagosMonedaPre = $this->pagosOrdenConMonedaDocs((int) $idOrden);
        $nPagosMonedaPre = $pagosMonedaPre->count();
        if ($nPagosMonedaPre > 0 && $this->pagosIncluyenMonedaNoBase($pagosMonedaPre)) {
            $tamPdf += (int) ceil(14 + ($nPagosMonedaPre * 7));
        }
        /**
         * Header
         */
        $titulo1 = $this->toLatin1( 'Panadería y Cafetería');
        $titulo2 = $this->toLatin1( 'El Amanecer');
        $sucursal = $this->toLatin1( 'Sucursal : ' . $orden->nombre_sucursal);
        $numero_orden = $this->toLatin1( 'No.Orden : ORD-' . $orden->numero_orden);
        if ($cantidadPagosOrdenPre > 1) {
            $cliente = null;
        } elseif ($orden->nombre_cliente == null || $orden->nombre_cliente == "") {
            $cliente = null;
        } else {
            $cliente = $this->toLatin1( 'Cliente : ' . $orden->nombre_cliente);
        }

        if ($orden->numero_mesa == null || $orden->numero_mesa == "") {
            $numero_mesa = null;
        } else {
            $numero_mesa = $this->toLatin1( 'No.Mesa : #' . $orden->numero_mesa);
        }
        $fecha = $this->toLatin1( 'Fecha : ' . $this->fechaFormat($orden->fecha_fin));

        $path = public_path() . '/logo_blanco_negro.png';

        $this->pdf->__construct('P', 'mm', array(80, $tamPdf));
        $this->pdf->AcceptPageBreak(true);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial', 'B', 10);
        $logoType = $this->getLogoImageType($path);
        if ($logoType !== null) {
            $this->pdf->Image($path, 23, 0, 30, 30, $logoType);
            $this->pdf->Ln(23);
        } else {
            $this->pdf->Ln(5);
        }
        $this->pdf->SetFont('Helvetica', '', 7);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo3, 0);
        $this->pdf->SetFont('Helvetica', '', 9);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo4, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $titulo5, 0);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $sucursal, 0);
        $this->pdf->Ln(1);
        if ($numero_mesa != null && $numero_mesa != "") {
            $this->pdf->setX(6);
            $this->pdf->MultiCell(63, 4, $numero_mesa, 0);
            $this->pdf->Ln(1);
        }
        $this->pdf->Ln(1);
        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $numero_orden, 0);
        $this->pdf->Ln(1);
        if ($cliente != null && $cliente != "") {
            $this->pdf->setX(6);
            $this->pdf->MultiCell(63, 4, $cliente, 0);
            $this->pdf->Ln(1);
        }

        $this->pdf->setX(6);
        $this->pdf->MultiCell(63, 4, $fecha, 0);

        /** Fin Header */
        /**BODY */
        $this->pdf->Ln(5);
        $this->pdf->SetFont('Helvetica', 'B', 10);
        $this->pdf->setX(21);
        $this->pdf->Cell(63, 3, $this->toLatin1( 'Detalle de la orden'), 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 0, '', 'T');
        $this->pdf->SetFont('Helvetica', 'B', 9);
        $this->pdf->setX(6);
        $this->pdf->SetFont('Arial', 'B', 8);    //Letra Arial, negrita (Bold), tam. 20
        $this->pdf->Cell(63, 4, 'Cantidad', 0);
        $this->pdf->setX(32);
        $this->pdf->Cell(63, 4, 'Precio U', 0);
        $this->pdf->setX(55);
        $this->pdf->Cell(63, 4, 'SubTotal', 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 0, '', 'T');
        $this->pdf->SetFont('Helvetica', '', 9);
        $this->pdf->Ln(1);

        foreach ($detalles as $d) {
            $this->pdf->setX(6);
            $producto = $d->nombre_producto;
            if ($d->servicio_mesa == 'S') {
                $producto .= ' (10%)';
            }
            $this->pdf->MultiCell(63, 4, $this->toLatin1( $producto), 0);
            $this->pdf->Ln(1);
            $this->pdf->setX(10);
            $this->pdf->Cell(63, 4,  $d->cantidad, 0);
            $this->pdf->setX(32);
            $this->pdf->Cell(63, 4, number_format($d->precio_unidad, 2, ".", ","), 0);
            $this->pdf->setX(53);
            $this->pdf->Cell(63, 4, number_format($d->precio_unidad * $d->cantidad, 2, ".", ","), 0);
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 0, '', 'T');
        }
        $this->pdf->Ln(4);
        $this->pdf->SetFont('Arial', 'B', 9);    //Letra Arial, negrita (Bold), tam. 20
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'SubTotal', 0);
        $this->pdf->setX(52);
        $this->pdf->Cell(63, 4, number_format($orden->subtotal, 2, ".", ","), 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'Impuesto (IVA)', 0);
        $this->pdf->setX(52);
        $this->pdf->Cell(63, 4, number_format($orden->impuesto, 2, ".", ","), 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'Impuesto Servicio (10%)', 0);
        $this->pdf->setX(52);
        $this->pdf->Cell(63, 4, number_format($orden->comision_restaurante, 2, ".", ","), 0);
        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'Descuento', 0);
        $this->pdf->setX(52);
        $this->pdf->Cell(63, 4, number_format($orden->descuento, 2, ".", ","), 0);
        $this->pdf->Ln(4);
        $this->escribirResumenPagosMonedaOrden((int) $idOrden);
        $this->pdf->Ln(2);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'Total', 0);
        $this->pdf->setX(52);
        $this->pdf->Cell(63, 4, number_format($orden->total, 2, ".", ","), 0);

        $this->pdf->Ln(10);
        $this->pdf->setX(23);

        $this->pdf->MultiCell(63, 4, '**** Pre Tiquete **** ');

        $this->pdf->SetFont('Helvetica', 'B', 6);
        $this->pdf->setX(23);
        $this->pdf->Cell(63, 4, 'BY SPACE SOFTWARE CR');
        $this->pdf->Ln(10);
        // $this->footer();

        $this->pdf->Output('ordenNo-' . $orden->numero_orden . '.pdf', 'I');

        exit;
    }
}
