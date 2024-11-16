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
    public function __construct()
    {
        $this->pdf = new Fpdf();
        setlocale(LC_ALL, "es_ES");
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

        if ($sucursalFactura->factura_iva == 1) {
            $tamPdf = $tamPdf  + 10;
        }

        
        $titulo3 = iconv('UTF-8', 'ISO-8859-1', $nombre_empresa_fe ?? env('APP_NAME', 'SPACE SOFTWARE CR'));
        $titulo4 = iconv('UTF-8', 'ISO-8859-1', 'Cédula : ' . $cedula_empresa_fe ?? '---');
        $titulo5 = iconv('UTF-8', 'ISO-8859-1', 'Correo : ' . $correo_empresa_fe ?? '---');
        $sucursal = iconv('UTF-8', 'ISO-8859-1', 'Sucursal : ' . $orden->nombre_sucursal);
        $detalleMesa = iconv('UTF-8', 'ISO-8859-1', $orden->mesa == null ?  'Tipo : PARA LLEVAR' : 'Mesa : ' . $orden->numero_mesa );
        $numero_orden = iconv('UTF-8', 'ISO-8859-1', 'No.Orden : ' . $orden->numero_orden);
        if ($orden->nombre_cliente == null || $orden->nombre_cliente == "") {
            $cliente = null;
        } else {
            $cliente = iconv('UTF-8', 'ISO-8859-1', 'Cliente : ' . $orden->nombre_cliente);
        }

        $fecha = iconv('UTF-8', 'ISO-8859-1', 'Fecha : ' . $this->fechaFormat($orden->fecha_fin));

        $path = public_path() . '/assets/images/default-logo.png';


        $this->pdf->__construct('P', 'mm', array(80, $tamPdf));
        $this->pdf->AcceptPageBreak(true);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Image($path, '15', '0', '50', '50');
        $this->pdf->Ln(28);
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
        $this->pdf->Cell(63, 3, iconv('UTF-8', 'ISO-8859-1', 'Detalle de la orden'), 0);
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
            $this->pdf->MultiCell(63, 4, iconv('UTF-8', 'ISO-8859-1', $producto), 0);
            foreach ($d->extras as $e) {
                $this->pdf->Ln(1);
                $this->pdf->setX(10);
                $this->pdf->Cell(63, 4,  iconv('UTF-8', 'ISO-8859-1', $e->descripcion_extra), 0);
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

            $this->pdf->MultiCell(63, 4, iconv('UTF-8', 'ISO-8859-1', $producto), 0);

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
        if ($sucursalFactura->factura_iva == 1) {
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
            $this->pdf->Cell(63, 4, number_format((($orden->subtotal +$orden->impuesto) + $orden->descuento) - $orden->mto_impuesto_servicio, 2, ".", ","), 0);
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

        if ($sucursalFactura->factura_iva == 1) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'Impuesto (IVA)', 0);
            $this->pdf->setX(55);
            $this->pdf->Cell(63, 4, number_format($orden->impuesto, 2, ".", ","), 0);
        }
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
            ->where('pago_orden.id', '=', $idPago)->get()->first();

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
        if ($orden->ind_requiere_envio == 1) {
            $tamPdf = 110;
        }
        $aumento = count($detalles) * 10;
        $aumento2 = 0;
        foreach ($detalles as $d) {
            $aumento2 = $aumento2 + count($d->extras) * 5;
        }
        $tamPdf = $tamPdf  + $aumento + $aumento2;
        /**
         * Header
         */
        $titulo3 = iconv('UTF-8', 'ISO-8859-1', $nombre_empresa_fe ?? env('APP_NAME', 'SPACE SOFTWARE CR'));
        $titulo4 = iconv('UTF-8', 'ISO-8859-1', 'Cédula : ' . $cedula_empresa_fe ?? '---');
        $titulo5 = iconv('UTF-8', 'ISO-8859-1', 'Correo : ' . $correo_empresa_fe ?? '---');
        $sucursal = iconv('UTF-8', 'ISO-8859-1', 'Sucursal : ' . $orden->nombre_sucursal);
        $numero_orden = iconv('UTF-8', 'ISO-8859-1', 'No.Orden : ' . $orden->numero_orden . '-P');
        if ($pago_orden->nombre_cliente == null || $pago_orden->nombre_cliente == "") {
            $cliente = null;
        } else {
            $cliente = iconv('UTF-8', 'ISO-8859-1', 'Cliente : ' . $pago_orden->nombre_cliente);
        }

        $fecha = iconv('UTF-8', 'ISO-8859-1', 'Fecha : ' . $this->fechaFormat($pago_orden->fecha_pago));

        $path = public_path() . '/assets/images/default-logo.png';


        $this->pdf->__construct('P', 'mm', array(80, $tamPdf));
        $this->pdf->AcceptPageBreak(true);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Image($path, '5', '2', '70', '30');

        // $this->pdf->SetTextColor(220, 50, 50);

        $this->pdf->Ln(20);
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
        $this->pdf->Cell(63, 3, iconv('UTF-8', 'ISO-8859-1', 'Detalle de la orden'), 0);
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
            if ($d->servicio_mesa == 'S') {
                $producto .= ' (10%)';
            }
            $totalExtra = 0;
            $this->pdf->MultiCell(63, 4, iconv('UTF-8', 'ISO-8859-1', $producto), 0);
            foreach ($d->extras as $e) {
                $this->pdf->Ln(1);
                $this->pdf->setX(10);
                $this->pdf->Cell(63, 4,  iconv('UTF-8', 'ISO-8859-1', $e->descripcion_extra), 0);
                $this->pdf->setX(32);
                $this->pdf->Cell(63, 4, "", 0);
                $this->pdf->setX(53);
                $this->pdf->Cell(63, 4, number_format($e->total, 2, ".", ","), 0);
                $this->pdf->Ln(4);
                $totalExtra = $totalExtra  + $e->total;
            }
            $this->pdf->Ln(1);
            $this->pdf->setX(10);
            $this->pdf->Cell(63, 4,  $d->cantidad_pagada, 0);
            $this->pdf->setX(32);
            $this->pdf->Cell(63, 4, number_format($d->precio_unidad, 2, ".", ","), 0);
            $this->pdf->setX(53);
            $this->pdf->Cell(63, 4, number_format(($d->precio_unidad * $d->cantidad_pagada) + $totalExtra, 2, ".", ","), 0);
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 0, '', 'T');
        }

        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'Descuento', 0);
        $this->pdf->setX(52);
        $this->pdf->Cell(63, 4, number_format($pago_orden->descuento, 2, ".", ","), 0);

        if ($orden->ind_requiere_envio == 1) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, iconv('UTF-8', 'ISO-8859-1', 'Envío'), 0);
            $this->pdf->setX(52);
            $this->pdf->Cell(63, 4, number_format($orden->monto_envio, 2, ".", ","), 0);
        }

        if ($sucursalFactura->factura_iva == 1) {
            $this->pdf->Ln(4);
            $this->pdf->SetFont('Arial', 'B', 9);    //Letra Arial, negrita (Bold), tam. 20
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'SubTotal', 0);
            $this->pdf->setX(52);
            $this->pdf->Cell(63, 4, number_format($pago_orden->subtotal, 2, ".", ","), 0);
        } else {
            $this->pdf->Ln(4);
            $this->pdf->SetFont('Arial', 'B', 9);    //Letra Arial, negrita (Bold), tam. 20
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'SubTotal', 0);
            $this->pdf->setX(52);
            $this->pdf->Cell(63, 4, number_format($pago_orden->subtotal + $pago_orden->iva, 2, ".", ","), 0);
        }


        if ($sucursalFactura->factura_iva == 1) {
            $this->pdf->Ln(4);
            $this->pdf->setX(6);
            $this->pdf->Cell(63, 4, 'Impuesto (IVA)', 0);
            $this->pdf->setX(52);
            $this->pdf->Cell(63, 4, number_format($pago_orden->iva, 2, ".", ","), 0);
        }


        $this->pdf->Ln(4);
        $this->pdf->setX(6);
        $this->pdf->Cell(63, 4, 'Total', 0);
        $this->pdf->setX(52);
        $this->pdf->Cell(63, 4, number_format($pago_orden->total, 2, ".", ","), 0);

        $this->pdf->Ln(10);
        $this->pdf->setX(14);

        $this->pdf->MultiCell(63, 4, 'GRACIAS POR PREFERIRNOS ');

        $this->pdf->SetFont('Helvetica', 'B', 6);
        $this->pdf->setX(28);
        $this->pdf->Cell(63, 4, env('APP_NAME', 'SPACE SOFTWARE CR'));
        $this->pdf->Ln(10);
        // $this->footer();

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
        $titulo3 = iconv('UTF-8', 'ISO-8859-1', 'INVERSIONES FONSECA JIMÉNEZ EL AMANECER SOCIEDAD DE RESPONSABILIDAD LIMITADA');
        $titulo4 = iconv('UTF-8', 'ISO-8859-1', 'Cédula jurídica : 3-102-862760');
        $titulo5 = iconv('UTF-8', 'ISO-8859-1', 'Correo : panaderiamanecer@gmail.com');
        $detalles = $orden->detalles;
        $tamPdf = 125;
        $aumento = count($detalles) * 10;
        $tamPdf = $tamPdf  + $aumento;
        /**
         * Header
         */
        $titulo1 = iconv('UTF-8', 'ISO-8859-1', 'Panadería y Cafetería');
        $titulo2 = iconv('UTF-8', 'ISO-8859-1', 'El Amanecer');
        $sucursal = iconv('UTF-8', 'ISO-8859-1', 'Sucursal : ' . $orden->nombre_sucursal);
        $numero_orden = iconv('UTF-8', 'ISO-8859-1', 'No.Orden : ORD-' . $orden->numero_orden);
        if ($orden->nombre_cliente == null || $orden->nombre_cliente == "") {
            $cliente = null;
        } else {
            $cliente = iconv('UTF-8', 'ISO-8859-1', 'Cliente : ' . $orden->nombre_cliente);
        }

        if ($orden->numero_mesa == null || $orden->numero_mesa == "") {
            $numero_mesa = null;
        } else {
            $numero_mesa = iconv('UTF-8', 'ISO-8859-1', 'No.Mesa : #' . $orden->numero_mesa);
        }
        $fecha = iconv('UTF-8', 'ISO-8859-1', 'Fecha : ' . $this->fechaFormat($orden->fecha_fin));

        $path = public_path() . '/logo_blanco_negro.png';


        $this->pdf->__construct('P', 'mm', array(80, $tamPdf));
        $this->pdf->AcceptPageBreak(true);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->Image($path, '23', '0', '30', '30');

        // $this->pdf->SetTextColor(220, 50, 50);

        $this->pdf->Ln(23);
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
        $this->pdf->Cell(63, 3, iconv('UTF-8', 'ISO-8859-1', 'Detalle de la orden'), 0);
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
            $this->pdf->MultiCell(63, 4, iconv('UTF-8', 'ISO-8859-1', $producto), 0);
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
