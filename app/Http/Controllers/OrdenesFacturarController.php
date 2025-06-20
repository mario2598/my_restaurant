<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Codedge\Fpdf\Fpdf\Fpdf;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use App\Traits\SpaceUtil;

class OrdenesFacturarController extends Controller
{
    use SpaceUtil;
    private $admin;
    private $fpdf;
    public function __construct()
    {
        $this->fpdf = new Fpdf();
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function goOrdenesFacturar()
    {
        if (!$this->validarSesion("fac_ord")) {
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'ordenes' => OrdenesFacturarController::getOrdenesFacturar($this->getRestauranteUsuario()),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('cocina.facturar.ordenes', compact('data'));
    }

    public function goOrdenesTodo()
    {
        if (!$this->validarSesion("fac_ord_tod")) {
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'ordenes' => OrdenesFacturarController::getOrdenesFacturarTodo($this->getRestauranteUsuario(), "T"),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('cocina.ordenes.todo', compact('data'));
    }

    public static function getOrdenesFacturar($restaurante)
    {
        if ($restaurante < 1 || $restaurante == null) {
            return [];
        }

        $ordenes = DB::table('orden')
            ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
            ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
            ->select('orden.*', 'mobiliario_x_salon.numero_mesa', 'mobiliario.nombre as nombre_mobiliario', 'mobiliario.descripcion as descripcion_mobiliario')
            ->whereIn('orden.estado', array('LF', 'EP', 'PT'))
            ->where('orden.restaurante', '=', $restaurante)
            ->orderBy('orden.fecha_inicio', 'ASC')->get();

        foreach ($ordenes as $o) {
            $phpdate = strtotime($o->fecha_inicio);
            $date = date("d-m-Y", strtotime($o->fecha_inicio));

            $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
            $fechaAux .= ' - ' . date("g:i a", $phpdate);
            $o->fecha_inicio_hora_tiempo = date("g:i a", $phpdate);
            $o->fecha_inicio_texto =  $fechaAux;
            $o->detalles = DB::table('detalle_orden')->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $o->id)
                ->get();
            $o->idOrdenEnc = encrypt($o->id);
        }

        return $ordenes;
    }

    public static function getOrdenesFacturarTodo($restaurante, $tipo = "T")
    {
        if ($restaurante < 1 || $restaurante == null) {
            return [];
        }
        $filtro = array('LF', 'EP', 'PT', 'FC', 'CR', 'PTF', 'EPF');
        if ($tipo == "F") { //  facturarado
            $filtro = array('FC', 'PTF', 'EPF');
        } else if ($tipo == "PF") { // pendiente facturar
            $filtro = array('LF', 'EP', 'PT');
        }

        $ordenes = DB::table('orden')
            ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
            ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
            ->select('orden.*', 'mobiliario_x_salon.numero_mesa', 'mobiliario.nombre as nombre_mobiliario', 'mobiliario.descripcion as descripcion_mobiliario')
            ->whereIn('orden.estado', $filtro)
            ->where('orden.restaurante', '=', $restaurante)
            ->where('orden.caja_cerrada', '=', 'N')
            ->orderBy('orden.fecha_inicio', 'DESC')->get();

        foreach ($ordenes as $o) {
            $phpdate = strtotime($o->fecha_inicio);
            $date = date("d-m-Y", strtotime($o->fecha_inicio));

            $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
            $fechaAux .= ' - ' . date("g:i a", $phpdate);
            $o->fecha_inicio_hora_tiempo = date("g:i a", $phpdate);
            $o->fecha_inicio_texto =  $fechaAux;
            $o->detalles = DB::table('detalle_orden')->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $o->id)
                ->get();
            $o->idOrdenEnc = encrypt($o->id);
        }

        return $ordenes;
    }

    public function recargarOrdenesTodo(Request $request)
    {
        if (!$this->validarSesion("fac_ord_tod")) {
            return 'Error de seguridad!';
        }

        $estado = $request->input('estado_orden');

        $data = [
            'ordenes' => OrdenesFacturarController::getOrdenesFacturarTodo($this->getRestauranteUsuario(), $estado ?? "T"),
        ];
        return view('cocina.ordenes.layout.contenedor_comandas', compact('data'));
    }


    public function entregarOrdenComida(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            $this->setError('Entregar Orden', 'Id de la orden incorrecto...');
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            $this->setError('Entregar Orden', 'No existe la orden.');
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($orden->estado != 'PT') {
            $this->setError('Entregar Orden', 'La orden ya fue procesada');
            return $this->responseAjaxServerError('La orden ya fue procesada', []);
        }

        try {
            DB::beginTransaction();

            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'estado' => 'EM', 'fecha_preparado' => date("Y-m-d H:i:s")
                ]);

            DB::commit();

            return $this->setAjaxResponse(200, "", [], true);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Entregar Orden', 'Algo salio mal...');
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    public function recargarOrdenesListasEntregar(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return 'Error de seguridad!';
        }

        $data = [
            'ordenes_listas' => OrdenesListasController::getOrdenesListasEntregar($this->getRestauranteUsuario()),
        ];
        return view('cocina.ordenesListas.layout.contenedor_comandas', compact('data'));
    }

    public function getDetalleFacturacionOrden(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        $facturacionOrden = OrdenesController::calcularFacturacionOrden($id_orden);

        return $this->responseAjaxSuccess("", $facturacionOrden);
    }

    public function getDetalleFacturacionOrdenPorDetalles(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $detalles = $request->input('detalles');



        $facturacionOrden = OrdenesController::calcularFacturacionNuevaOrden($detalles);

        return $this->responseAjaxSuccess("", $facturacionOrden);
    }

    public function preFacturarOrden(Request $request)
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');
        $tipoFacturacion = $request->input('tipoFacturacion'); // T : TOTAL , P : PARCIAL
        if ($id_orden < 1 || $this->isNull($id_orden)) {
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($orden->estado == 'FC' || $orden->estado == 'EPF' || $orden->estado == 'PTF') {
            return $this->responseAjaxServerError('La orden ya fue facturada.', []);
        }

        $detallesOrden = $request->input('detallesOrden');
        $detallesOrdenParcial = $request->input('detallesOrdenParcial');
        $facturacionOrdenParcial = OrdenesController::calcularFacturacionNuevaOrden($detallesOrdenParcial);
        $facturacionOrden = OrdenesController::calcularFacturacionNuevaOrden($detallesOrden);

        $facturacionOrden = $facturacionOrden['facturacion'];
        $facturacionOrdenParcial = $facturacionOrdenParcial['facturacion'];
        try {
            DB::beginTransaction();

            $id_nueva_orden = DB::table('orden')->insertGetId([
                'id' => null, 'numero_orden' => OrdenesController::getConsecutivoNuevaOrden(),
                'tipo' => $orden->tipo, 'fecha_fin' => $orden->fecha_fin, 'fecha_inicio' => $orden->fecha_inicio, 'cliente' => $orden->cliente,
                'nombre_cliente' => $orden->nombre_cliente, 'estado' => $orden->estado, 'total' => $facturacionOrdenParcial['total'], 'subtotal' => $facturacionOrdenParcial['subtotal'],
                'porcentaje_impuesto' => $orden->porcentaje_impuesto, 'porcentaje_descuento' => $orden->porcentaje_descuento, 'impuesto' => $facturacionOrdenParcial['montoImpuestos'], 'descuento' => $orden->descuento,
                'total_cancelado' => $orden->total_cancelado, 'cajero' => session('usuario')['id'], 'monto_sinpe' => $orden->monto_sinpe, 'monto_tarjeta' => $orden->monto_tarjeta, 'monto_efectivo' =>  $orden->monto_efectivo, 'monto_otros' =>  $orden->monto_otros,
                'factura_electronica' => $orden->factura_electronica, 'ingreso' => $orden->ingreso, 'restaurante' => $orden->restaurante, 'comision_restaurante' => $facturacionOrdenParcial['montoImpuestoServicioMesa'],
                'mobiliario_salon' => $orden->mobiliario_salon, 'fecha_preparado' => $orden->fecha_preparado, 'fecha_entregado' => $orden->fecha_entregado,
                'cocina_terminado' => $orden->cocina_terminado, 'bebida_terminado' => $orden->bebida_terminado, 'caja_cerrada' => 'N'
            ]);

            OrdenesController::aumentarConsecutivoOrden();

            foreach ($detallesOrdenParcial as $d) {
                if ($d['cantidad'] > 0) {
                    $cantidadPreparada = 0;
                    if ($orden->estado == 'PT') {
                        $cantidadPreparada = $d['cantidad'];
                    }
                    $producto = $d['producto'];
                    $det = DB::table('detalle_orden')->insertGetId([
                        'id' => null, 'cantidad' => $d['cantidad'],
                        'codigo_producto' => $producto['codigo'], 'nombre_producto' => $producto['nombre'], 'precio_unidad' => $d['precio_unidad'], 'porcentaje_impuesto' => $d['impuesto'], 'impuesto' => $d['precio_unidad'] - ($d['precio_unidad'] / 1.13),
                        'orden' => $id_nueva_orden, 'tipo_producto' => $d['tipo'], 'servicio_mesa' => $d['impuestoServicio'], 'observacion' => $d['observacion'],
                        'tipo_comanda' => $d['tipoComanda'], 'cantidad_preparada' => $cantidadPreparada, 'fecha_creacion' => $d['fechaCreacion']
                    ]);
                }
            }

            // Actualizar factura original
            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'total' => $facturacionOrden['total'], 'subtotal' => $facturacionOrden['subtotal'],
                    'comision_restaurante' => $facturacionOrden['montoImpuestoServicioMesa'], 'impuesto' => $facturacionOrden['montoImpuestos']
                ]);

            DB::table('detalle_orden')
                ->where('orden', '=', $id_orden)->delete();

            foreach ($detallesOrden as $d) {
                if ($d['cantidad'] > 0) {
                    $cantidadPreparada = 0;
                    if ($orden->estado == 'PT') {
                        $cantidadPreparada = $d['cantidad'];
                    }
                    $producto = $d['producto'];
                    $det = DB::table('detalle_orden')->insertGetId([
                        'id' => null, 'cantidad' => $d['cantidad'],
                        'codigo_producto' => $producto['codigo'], 'nombre_producto' => $producto['nombre'], 'precio_unidad' => $d['precio_unidad'], 'porcentaje_impuesto' => $d['impuesto'], 'impuesto' => $d['precio_unidad'] - ($d['precio_unidad'] / 1.13),
                        'orden' => $id_orden, 'tipo_producto' => $d['tipo'], 'servicio_mesa' => $d['impuestoServicio'], 'observacion' => $d['observacion'],
                        'tipo_comanda' => $d['tipoComanda'], 'cantidad_preparada' => $cantidadPreparada, 'fecha_creacion' => $d['fechaCreacion']
                    ]);
                }
            }

            DB::commit();
            return $this->responseAjaxSuccess("", $id_nueva_orden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }



    public function facturarOrden(Request $request)
    {
        if (!$this->validarSesion(array("ordList_cmds", "fac_ord", "facFacRuta"))) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($orden->estado == 'FC' || $orden->estado == 'PTF' || $orden->estado == 'EPF') {
            return $this->responseAjaxServerError('La orden ya fue facturada.', []);
        }

        $monto_sinpe = $request->input('monto_sinpe');
        $monto_tarjeta = $request->input('monto_tarjeta');
        $monto_efectivo = $request->input('monto_efectivo');
        $monto_otros = $request->input('monto_otros');
        $genera_factura = $request->input('genera_factura');
        $imprime_tiquete = $request->input('imprime_tiquete');
        $nombreCliente = $request->input('nombreCliente');
        $idCliente = $request->input('idCliente');
        $porcentaje_descuento = $request->input('porcentaje_descuento');
        $ordenParcialRuta = $request->input('ordenParcialRuta') ?? "NO";
        $nuevoTotal  = $orden->total;
        $montoDescuento = 0;
        if ($porcentaje_descuento != null &&  $porcentaje_descuento > 0) {
            $montoDescuento = $nuevoTotal * doubleval((doubleval($porcentaje_descuento) / 100));
            $nuevoTotal = $nuevoTotal - doubleval($montoDescuento);
        }

        $totalCancelado = doubleval($monto_sinpe) + doubleval($monto_tarjeta) + doubleval($monto_efectivo);

        if($ordenParcialRuta === "SI"){
            if($totalCancelado >= $nuevoTotal){
                return $this->responseAjaxServerError('El monto ingresado iguala ó sobrepasa el total, intenta facturar completo.', []);
            }if($totalCancelado < 0){
                return $this->responseAjaxServerError('El monto ingresado no puede ser negativo.', []);
            }
        }
        try {
            DB::beginTransaction();
            if ($orden->estado == 'EP') {
                $estado = 'EPF';
            } else if ($orden->estado == 'PT') {
                $estado = 'PTF';
            } else if ($orden->estado == 'LF' || $orden->estado == 'CR') {
                if($ordenParcialRuta === "SI"){
                    DB::table('pago_parcial_h')->insert([
                        'id' => null, 'orden' => $id_orden,
                        'fecha' =>  date("Y-m-d H:i:s"), 'usuario' => session('usuario')['id'],
                        'monto_sinpe' => $monto_sinpe, 'monto_tarjeta' => $monto_tarjeta,
                        'monto_efectivo' => $monto_efectivo,'estado' => 'PENDIENTE'
                    ]);
                    $estado = 'FCP';
                    if($totalCancelado == $nuevoTotal){
                        $estado = 'FC';
                    }
                }else{
                    $estado = 'FC';
                }
            }

            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'monto_sinpe' => $monto_sinpe, 'monto_tarjeta' => $monto_tarjeta, 'factura_electronica' => $genera_factura, 'porcentaje_descuento' => $porcentaje_descuento,
                    'monto_efectivo' => $monto_efectivo, 'total_cancelado' => $totalCancelado,
                     'monto_otros' => $monto_otros, 'fecha_fin' => date("Y-m-d H:i:s"), 'estado' => $estado, 'total' => $nuevoTotal,
                    'descuento' => $montoDescuento, 'cliente' => $idCliente, 'nombre_cliente' => $nombreCliente
                ]);
            $res = $this->restarInventarioOrden($id_orden);

            if (!$res['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($res['mensaje'], []);
            }
            DB::commit();
            return $this->responseAjaxSuccess("", $id_orden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    private function restarInventarioOrden($id_orden)
    {
        $detalles = DB::table('detalle_orden')->select('detalle_orden.*')->where('orden', '=', $id_orden)->get();
        foreach ($detalles as $d) {
            if ($d->tipo_producto == 'P') {
                $res = $this->restarInventarioProductoProducido($d);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            } else if ($d->tipo_producto == 'E') {
                $res = $this->restarInventarioProductoExterno($d);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            }
        }
        return $this->responseAjaxSuccess("", "");
    }

    private function restarInventarioProductoExterno($detalle)
    {
        $cantidadRebajar = $detalle->cantidad;
        $codigoProductoRebajar = $detalle->codigo_producto;
        $inventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->select('pe_x_sucursal.*')
            ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->get()->first();
        $cantidadInventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
            ->sum('pe_x_sucursal.cantidad');

        if ($cantidadInventario <  $cantidadRebajar) {
            return $this->responseAjaxServerError('La cantidad solicitada es mayor al inventario de productos producidos.', []);
        } else if ($cantidadInventario == $cantidadRebajar) {
            DB::table('pe_x_sucursal')
                ->where('id', '=', $inventario->id)
                ->delete();
        } else if ($cantidadInventario > $cantidadRebajar) {
            DB::table('pe_x_sucursal')
                ->where('id', '=', $inventario->id)
                ->update(['cantidad' => $inventario->cantidad - $cantidadRebajar]);
        }

        return $this->responseAjaxSuccess("", "");
    }


    private function restarInventarioProductoProducido($detalle)
    {
        $cantidadRebajar = $detalle->cantidad;
        $codigoProductoRebajar = $detalle->codigo_producto;
        $inventarios = DB::table('inventario')
            ->leftjoin('lote', 'lote.id', '=', 'inventario.lote')
            ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
            ->select('inventario.*')
            ->where('producto.codigo_barra', '=', $codigoProductoRebajar)
            ->where('inventario.sucursal', '=', $this->getUsuarioSucursal())
            ->orderBy('lote.fecha_vencimiento', 'asc')->get();
        $cantidadInventario = DB::table('inventario')
            ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
            ->where('inventario.sucursal', '=', $this->getUsuarioSucursal())
            ->where('producto.codigo_barra', '=', $codigoProductoRebajar)
            ->sum('inventario.cantidad');

        if ($cantidadInventario <  $cantidadRebajar) {
            return $this->responseAjaxServerError('La cantidad solicitada es mayor al inventario de productos producidos.', []);
        }

        try {
            foreach ($inventarios as $i) {
                if ($i->cantidad > $cantidadRebajar) {
                    DB::table('inventario')
                        ->where('id', '=', $i->id)
                        ->update(['cantidad' => $i->cantidad - $cantidadRebajar]);
                    break;
                } else if ($i->cantidad == $cantidadRebajar) {
                    DB::table('inventario')
                        ->where('id', '=', $i->id)
                        ->delete();
                    break;
                } else if ($i->cantidad < $cantidadRebajar) {
                    $cantidadRebajar =  $cantidadRebajar - $i->cantidad;

                    DB::table('inventario')
                        ->where('id', '=', $i->id)
                        ->delete();
                }
            }
            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    public function prePagarOrden(Request $request)
    {
        if (!$this->validarSesion(array("ordList_cmds", "fac_ord"))) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            return $this->responseAjaxServerError('No existe la orden.', []);
        }

        if ($orden->estado == 'FC' || $orden->estado == 'PTF' || $orden->estado == 'EPF') {
            return $this->responseAjaxServerError('La orden ya fue facturada.', []);
        }
        $monto = $request->input('monto');
        try {
            DB::beginTransaction();


            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update(['total_cancelado' => $monto]);

            DB::commit();
            return $this->responseAjaxSuccess("", $id_orden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    public function imprimirOrden($id_orden)
    {
        $orden = DB::table('orden')->where('id', '=', $id_orden)->get()->first();
        $detalles = DB::table('detalle_orden')->where('orden', '=', $id_orden)->get();
        $nombreImpresora = $this->getImpresoraCaja();
        $connector = new WindowsPrintConnector($nombreImpresora);
        $impresora = new Printer($connector);
        $impresora->setJustification(Printer::JUSTIFY_CENTER);
        $impresora->setEmphasis(true);
        $impresora->text("Panadería y Cafetería\n");
        $impresora->text("El Amanecer\n");
        $impresora->text($this->fechaFormat($orden->fecha_fin) . "\n");
        $impresora->setEmphasis(false);
        $impresora->text("No.Orden: ");
        $impresora->text($orden->numero_orden . "\n");
        if ($orden->nombre_cliente != "" && $orden->nombre_cliente != null) {
            $impresora->text("Cliente: ");
            $impresora->text($orden->nombre_cliente . "\n");
        }
        //  $impresora->text("\nhttps://parzibyte.me/blog\n");
        $impresora->text("\n===============================\n");
        $total = 0;
        foreach ($detalles as $d) {
            $subtotal = $d->cantidad * $d->precio_unidad;
            if ($d->servicio_mesa == 'S') {
                $subtotal = $subtotal  + (0.1 * $d->precio_unidad);
            }
            $impresora->setJustification(Printer::JUSTIFY_LEFT);
            $impresora->text(sprintf("%.2fx%s\n", $d->cantidad, $d->nombre_producto));
            $impresora->setJustification(Printer::JUSTIFY_RIGHT);
            $impresora->text('₡' . number_format($subtotal, 2) . "\n");
        }
        $impresora->setJustification(Printer::JUSTIFY_CENTER);
        $impresora->text("\n===============================\n");
        $impresora->setJustification(Printer::JUSTIFY_RIGHT);
        $impresora->setEmphasis(true);
        $impresora->text("Total: ₡" . number_format($orden->total, 2) . "\n");
        $impresora->setJustification(Printer::JUSTIFY_CENTER);
        $impresora->setTextSize(1, 1);
        $impresora->text("Gracias por su visita\n");

        $impresora->text("Space Software Costa Rica");
        $impresora->feed(5);
        $impresora->close();
        return redirect('/');
    }
}
