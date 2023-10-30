<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Intervention\Image\ImageManagerStatic as Image;

class FacturacionRutaController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {
        setlocale(LC_ALL, "es_CR");
    }

    public function index()
    {
        if (!$this->validarSesion("facFacRuta")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $tipos =  $this->getTiposCategoriasProductosRuta();
        foreach ($tipos as $i => $t) {
            if (count($t['categorias']) < 1) {
                unset($tipos[$i]);
            }
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'tipos' => $tipos,
            'clientes' => $this->getClientes(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("facturacion.facturarRuta", compact("data"));
    }

    public function getTiposCategoriasProductosRuta()
    {
        return [
            [
                'nombre' => 'Panadería',
                'codigo' => 'P',
                'color' => '#BB88F3',
                'categorias' => $this->getCategorias('P'),
            ]
        ];
    }

    public function getCategorias($tipo)
    {
        $categorias = DB::table('categoria')->select('id', 'categoria')->get();

        switch ($tipo) {
            case "P":
                $categorias = $this->getCategoriasProductos($categorias);
                break;
        }
        //Elimina las categorias vacias
        foreach ($categorias as $i => $c) {
            if (count($c->productos) < 1) {
                unset($categorias[$i]);
            }
        }
        return $categorias;
    }

    public function getCategoriasProductosMenu($categorias)
    {

        foreach ($categorias as $categoria) {
            $categoria->productos = DB::table("producto_menu")
                ->where('categoria', $categoria->id)
                ->where('producto_menu.estado', "A")
                ->where('pm_x_restaurante.restaurante', $this->getUsuarioRestaurante()) //TODO, verificar método de obtener restaurante
                ->join('impuesto', 'producto_menu.impuesto', '=', 'impuesto.id')
                ->join('pm_x_restaurante', 'producto_menu.id', '=', 'pm_x_restaurante.producto_menu')
                ->select('producto_menu.id', 'producto_menu.codigo', 'producto_menu.nombre', 'producto_menu.precio', 'impuesto.impuesto as impuesto', 'producto_menu.tipo_comanda')->get();
            foreach ($categoria->productos as $p) {
                $p->tipoProducto = 'R';
            }
        }

        return $categorias;
    }

    public function getCategoriasProductos($categorias)
    {
        foreach ($categorias as $categoria) {

            $categoria->productos = DB::table("producto")
                ->where('categoria', $categoria->id)
                ->where('producto.estado', "A")
                ->where('inventario.sucursal', $this->getUsuarioSucursal())
                ->where('inventario.cantidad', ">", 0)
                ->join('inventario', 'producto.id', '=', 'inventario.producto')
                ->join('impuesto', 'producto.impuesto', '=', 'impuesto.id')
                ->select('producto.id', 'producto.codigo_barra as codigo', 'producto.nombre', 'producto.precio_mayoreo as precio', 'impuesto.impuesto as impuesto')
                ->groupBy('producto.id', 'producto.codigo_barra', 'producto.nombre', 'producto.precio_mayoreo', 'impuesto.impuesto')->get();
            foreach ($categoria->productos as $p) {
                $p->cantidad = DB::table('inventario')
                    ->where('inventario.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('inventario.producto', '=', $p->id)
                    ->sum('inventario.cantidad');
                $p->tipoProducto = 'P';
            }
        }

        return $categorias;
    }

    public function getCategoriasProductosExternos($categorias)
    {
        foreach ($categorias as $categoria) {
            $categoria->productos = DB::table("producto_externo")
                ->where('categoria', $categoria->id)
                ->where('producto_externo.estado', "A")
                ->where('pe_x_sucursal.sucursal', $this->getUsuarioSucursal())
                ->where('pe_x_sucursal.cantidad', ">", 0)
                ->join('impuesto', 'producto_externo.impuesto', '=', 'impuesto.id')
                ->join('pe_x_sucursal', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
                ->select('producto_externo.id', 'producto_externo.codigo_barra as codigo', 'producto_externo.nombre', 'producto_externo.precio', 'impuesto.impuesto as impuesto', 'pe_x_sucursal.cantidad')->get();
            foreach ($categoria->productos as $p) {
                $p->tipoProducto = 'E';
            }
        }
        return $categorias;
    }

    public function getSalonesRestaurante($idRestaurante)
    {
        return DB::table('salon')
            ->select('id', 'nombre')
            ->where('salon.restaurante', '=', $idRestaurante)
            ->get();
    }

    public function getInfoMesa($id)
    {
        return DB::table('mobiliario_x_salon')
            ->where('mobiliario_x_salon.id', '=', $id)
            ->get()->first();
    }

    public function goFactura(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            $this->setMsjSeguridad();
            return redirect('cocina/facturar/ordenes');
        }

        $id = $request->input('ipt_id_orden_factura');

        return $this->gofacturaById($id);
    }

    private function gofacturaById($id)
    {
        if (empty($id)) {
            $this->setError("Factura", "Id de orden incorrecto.");
            return redirect('cocina/facturar/ordenes');
        }
        $orden = $this->getOrden($id);

        if ($orden == null) {
            $this->setError("Factura", "No existe la orden.");
            return redirect('cocina/facturar/ordenes');
        }

        if ($orden->estado == 'FC' || $orden->estado == 'EPF') {
            $this->setError("Factura", "La orden ya fue facturada.");
            return redirect('cocina/facturar/ordenes');
        }

        $tipos =  $this->getTiposCategoriasProductos();
        $clientes = $this->getClientes();

        foreach ($clientes as $c) {
            if ($c->id == $orden->cliente) {
                $c->selected = true;
            } else {
                $c->selected = false;
            }
        }
        $data = [
            'menus' => $this->cargarMenus(),
            'orden' => $orden,
            'tipos' => $tipos,
            'mesa' => $this->getInfoMesa($orden->mobiliario_salon),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("facturacion.factura", compact("data"));
    }

    public function getMobiliarioDisponibleSalon(Request $request)
    {
        $idSalon = $request->input('idSalon');

        return DB::table('mobiliario_x_salon')
            ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
            ->select(
                'mobiliario.nombre',
                'mobiliario_x_salon.id',
                'mobiliario_x_salon.estado',
                'mobiliario_x_salon.numero_mesa'
            )
            ->where('mobiliario_x_salon.salon', '=', $idSalon)
            ->where('mobiliario_x_salon.estado', '=', 'D')
            ->get();
    }

    public function getClientes()
    {
        return DB::table('cliente')
            ->where('estado', 'A')
            ->get();
    }

    /* Cobrar */
    public function dividirFactura(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            $this->setMsjSeguridad();
            return redirect('cocina/facturar/ordenes');
        }

        $id = $request->input('ipt_id_orden_dividir');

        if (empty($id)) {
            $this->setError("Cobrar", "Id de orden incorrecto.");
            return redirect('cocina/facturar/ordenes');
        }
        $orden = $this->getOrden($id);

        if ($orden == null) {
            $this->setError("Cobrar", "No existe la orden.");
            return redirect('cocina/facturar/ordenes');
        }

        if ($orden->estado == 'FC' || $orden->estado == 'EPF' || $orden->estado == 'PTF') {
            $this->setError("Cobrar", "La orden ya fue facturada.");
            return $this->gofacturaById($id);
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'orden' => $orden,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("facturacion.dividirFactura", compact("data"));
    }

    public static function getOrden($idOrden)
    {
        if ($idOrden < 1 || $idOrden == null) {
            return [];
        }

        $orden = DB::table('orden')
            ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
            ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
            ->leftjoin('salon', 'salon.id', '=', 'mobiliario_x_salon.salon')
            ->leftjoin('usuario', 'usuario.id', '=', 'orden.cajero')
            ->select('orden.*', 'usuario.usuario as nombre_cajero', 'salon.nombre as nombre_salon', 'mobiliario_x_salon.numero_mesa', 'mobiliario.nombre as nombre_mobiliario', 'mobiliario.descripcion as descripcion_mobiliario')
            ->where('orden.id', '=', $idOrden)
            ->get()->first();

        $phpdate = strtotime($orden->fecha_inicio);
        $date = date("d-m-Y", strtotime($orden->fecha_inicio));

        $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
        $fechaAux .= ' - ' . date("g:i a", $phpdate);
        $orden->fecha_inicio_hora_tiempo = date("g:i a", $phpdate);
        $orden->fecha_inicio_texto =  $fechaAux;
        $orden->detalles = DB::table('detalle_orden')->select('detalle_orden.*')
            ->where('detalle_orden.orden', '=', $orden->id)
            ->get();

        return $orden;
    }

    /**
     * Pagar
     */
    public function pagar(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('ipt_id_orden');

        if (empty($id)) {
            $this->setError("Pagar", "No existe la orden.");
            return redirect('cocina/facturar/ordenes');
        }

        $orden = $this->getOrden($id);

        if ($orden == null) {
            $this->setError("Pagar", "No existe la orden.");
            return redirect('cocina/facturar/ordenes');
        }

        if ($orden->estado == 'FC') {
            $this->setError("Pagar", "La orden ya fue facturada.");
            return $this->gofacturaById($id);
        }
        $data = [
            'clientes' => $this->getClientes(),
            'menus' => $this->cargarMenus(),
            'orden' => $orden,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("facturacion.pagar", compact("data"));
    }

    public function goFacturasParciales()
    {
        if (!$this->validarSesion("factParciales")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'cliente' => 0,
            'estado' => "T",
        ];
        //  dd($filtros);
        $data = [
            'menus' => $this->cargarMenus(),
            'filtros' => $filtros,
            'ordenes' => [],
            'clientes' => $this->getClientes(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('facturas.parciales', compact('data'));
    }

    public function goFacturasParcialesFiltro(Request $request)
    {
        if (!$this->validarSesion("factParciales")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroCliente = $request->input('cliente');

        $estado = $request->input('estado');

        $ordenes =  DB::table('orden')
            ->join('pago_parcial_h', 'pago_parcial_h.orden', '=', 'orden.id')
            ->leftjoin('usuario', 'usuario.id', '=', 'orden.cajero')
            ->select(
                'orden.numero_orden',
                'orden.total',
                'orden.id',
                'orden.fecha_inicio',
                'orden.nombre_cliente',
                'orden.total',
                'usuario.usuario as creador'
            )
            ->where('orden.estado', '=', 'FCP');


        if ($filtroCliente >= 1  && !$this->isNull($filtroCliente)) {
            $ordenes = $ordenes->where('orden.cliente', '=', $filtroCliente);
        }

        if (!$this->isNull($estado) && $estado != 'T') {
            if ($estado == 'COMPLETO') {
                $ordenes = $ordenes->where('orden.total', '<=', 'orden.total_cancelado');
            }
        }


        $ordenes = $ordenes->distinct()->get();
        $totalOrdenes = 0;
        foreach ($ordenes as $i) {
            $i->pagos = DB::table('pago_parcial_h')
                ->leftjoin('usuario', 'usuario.id', '=', 'pago_parcial_h.usuario')
                ->select('pago_parcial_h.*', 'usuario.usuario as cobrador')
                ->where('pago_parcial_h.orden', '=', $i->id)
                ->orderBy('pago_parcial_h.fecha', 'DESC')->get();

            $totalPagos = 0;
            foreach ($i->pagos as $g) {
                $totalPagos = $totalPagos + $g->monto_tarjeta;
                $totalPagos = $totalPagos + $g->monto_sinpe;
                $totalPagos = $totalPagos + $g->monto_efectivo;
                $g->totalPagado = $g->monto_tarjeta + $g->monto_sinpe + $g->monto_efectivo;
                $g->fecha = $this->fechaFormat($g->fecha);
            }

            $i->totalPagado = $totalPagos;
            if ($i->totalPagado <  $i->total) {
                $i->estado = 'PENDIENTE';
            } else {
                $i->estado = 'COMPLETO';
            }
            $i->fecha_inicio = $this->fechaFormat($i->fecha_inicio);
        }
        // dd($ingresos);
        $filtros = [
            'cliente' => $filtroCliente,
            'estado' => $estado
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'filtros' => $filtros,
            'ordenes' => $ordenes,
            'clientes' => $this->getClientes(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('facturas.parciales', compact('data'));
    }

    public function cargarPagos(Request $request)
    {
        $id = $request->input('orden');

        $pagos = DB::table('pago_parcial_h')
            ->leftjoin('usuario', 'usuario.id', '=', 'pago_parcial_h.usuario')
            ->where('pago_parcial_h.orden', '=', $id)
            ->select('pago_parcial_h.*', 'usuario.usuario as cobrador')
            ->orderBy('pago_parcial_h.fecha', 'DESC')->get();

        $total = 0;
        foreach ($pagos as $p) {
            $p->fecha = $this->fechaFormat($p->fecha);
            $p->totalPagado = $p->monto_tarjeta + $p->monto_sinpe + $p->monto_efectivo;
            $total =  $total + $p->totalPagado;
        }

        $data = [
            'pagos' => $pagos,
            'total' => $total,
        ];

        return view('facturas.layout.pagos', compact('data'));
    }

    public function crearPago(Request $request)
    {
        $id = $request->input('orden');
        $tarjeta = $request->input('tarjeta');
        $efectivo = $request->input('efectivo');
        $sinpe = $request->input('sinpe');

        $orden = DB::table('orden')
            ->where('orden.id', '=', $id)
            ->get()->first();
        $total =  $tarjeta +$efectivo + $sinpe;
        if($orden == null){
            return $this->responseAjaxServerError("No existe la orden seleccionada.", "");
        }

        if($orden->total <= $orden->total_cancelado){
            return $this->responseAjaxServerError("La orden no tiene monto pendiente.", "");
        }

        if($orden->total < ($orden->total_cancelado + $total)){
            return $this->responseAjaxServerError("El monto indicado exede el total pendiente de facturar.", "");
        }

        if($orden->estado != "FCP"){
            return $this->responseAjaxServerError("La orden no permite facturación parcial.", "");
        }
        try {
            DB::beginTransaction();
            DB::table('pago_parcial_h')->insert([
                'id' => null, 'orden' => $id,
                'fecha' =>  date("Y-m-d H:i:s"), 'usuario' => session('usuario')['id'],
                'monto_sinpe' => $sinpe, 'monto_tarjeta' => $tarjeta,
                'monto_efectivo' => $efectivo,'estado' => 'PENDIENTE'
            ]);

            DB::table('orden')
            ->where('id', '=', $id)
            ->update([
                'monto_sinpe' => $orden->monto_sinpe + $sinpe, 'monto_tarjeta' => $orden->monto_tarjeta + $tarjeta,
                'monto_efectivo' => $orden->monto_efectivo + $efectivo, 'total_cancelado' => $orden->total_cancelado + $total
            ]);

            DB::commit();
            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
        
    }
}
