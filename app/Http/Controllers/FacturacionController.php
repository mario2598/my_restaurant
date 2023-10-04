<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Intervention\Image\ImageManagerStatic as Image;

class FacturacionController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
        if (!$this->validarSesion("facFac")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $tipos =  $this->getTiposCategoriasProductos();
        foreach ($tipos as $i => $t) {
            if (count($t['categorias']) < 1) {
                unset($tipos[$i]);
            }
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'tipos' => $tipos,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("facturacion.facturar", compact("data"));
    }

    public function goPos()
    {
        if (!$this->validarSesion("facFac")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $tipos =  $this->getTiposCategoriasProductos();
        foreach ($tipos as $i => $t) {
            if (count($t['categorias']) < 1) {
                unset($tipos[$i]);
            }
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'tipos' => $tipos,
            'cajaAbierta' =>  CajaController::tieneCajaAbierta(session('usuario')['id'], $this->getUsuarioSucursal()),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("facturacion.pos", compact("data"));
    }

    public function getTiposCategoriasProductos($todas = true)
    {
        if ($todas) {
            return [
                [
                    'nombre' => 'Restaurante',
                    'codigo' => 'R',
                    'color' => '#0DA8EE',
                    'categorias' => $this->getCategorias('R'),
                ],
                [
                    'nombre' => 'Externos',
                    'codigo' => 'E',
                    'color' => '#41C457',
                    'categorias' => $this->getCategorias('E'),
                ]
            ];
        } else {
            return [
                [
                    'nombre' => 'Restaurante',
                    'codigo' => 'R',
                    'color' => '#0DA8EE',
                    'categorias' => $this->getCategorias('R'),
                ],
                [
                    'nombre' => 'Externos',
                    'codigo' => 'E',
                    'color' => '#41C457',
                    'categorias' => $this->getCategorias('E'),
                ]
            ];
        }
    }

    public function getCategorias($tipo)
    {
        $categorias = DB::table('categoria')->select('id', 'categoria')->get();

        switch ($tipo) {
            case "R":
                $categorias = $this->getCategoriasProductosMenu($categorias);
                break;
            case "E":
                $categorias = $this->getCategoriasProductosExternos($categorias);
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
                ->where('pm_x_sucursal.sucursal', $this->getUsuarioSucursal()) //TODO, verificar método de obtener restaurante
                ->join('impuesto', 'producto_menu.impuesto', '=', 'impuesto.id')
                ->join('pm_x_sucursal', 'producto_menu.id', '=', 'pm_x_sucursal.producto_menu')
                ->select('producto_menu.id', 'producto_menu.codigo', 'producto_menu.nombre', 'producto_menu.precio', 'impuesto.impuesto as impuesto', 'producto_menu.tipo_comanda')->get();
            foreach ($categoria->productos as $p) {
                $p->tipoProducto = 'R';
                $grupos = DB::table('extra_producto_menu')
                    ->select(
                        'extra_producto_menu.dsc_grupo',
                        'extra_producto_menu.multiple'
                    )->distinct()
                    ->where('extra_producto_menu.producto', '=', $p->id)
                    ->get();
                $extrasAux = [];
                foreach ($grupos as $g) {
                    $requerido = false;
                    $multiple = false;
                    $listExtras = DB::table('extra_producto_menu')
                        ->select(
                            'extra_producto_menu.*'
                        )
                        ->where('extra_producto_menu.producto', '=', $p->id)
                        ->where('extra_producto_menu.dsc_grupo', '=', $g->dsc_grupo)
                        ->where('extra_producto_menu.multiple', '=', $g->multiple)
                        ->get() ?? [];
                    foreach ($listExtras as $le) {
                        if ($le->es_requerido) {
                            $requerido = true;
                        }

                        if ($le->multiple) {
                            $multiple = true;
                        }
                    }
                    $extras = [
                        'grupo' => $g->dsc_grupo,
                        'requerido' =>  $requerido ? 1 : 0,
                        'multiple' =>  $multiple ? 1 : 0,
                        'extras' =>  $listExtras
                    ];
                    array_push($extrasAux, $extras);
                }
                $p->extras = $extrasAux;
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
                ->select('producto.id', 'producto.codigo_barra as codigo', 'producto.nombre', 'producto.precio', 'impuesto.impuesto as impuesto')
                ->groupBy('producto.id', 'producto.codigo_barra', 'producto.nombre', 'producto.precio', 'impuesto.impuesto')->get();
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
                $grupos = DB::table('extra_producto_externo')
                    ->select(
                        'extra_producto_externo.dsc_grupo',
                        'extra_producto_externo.multiple'
                    )->distinct()
                    ->where('extra_producto_externo.producto', '=', $p->id)
                    ->get();
                $extrasAux = [];
                foreach ($grupos as $g) {
                    $requerido = false;
                    $multiple = false;
                    $listExtras = DB::table('extra_producto_externo')
                        ->select(
                            'extra_producto_externo.*'
                        )
                        ->where('extra_producto_externo.producto', '=', $p->id)
                        ->where('extra_producto_externo.dsc_grupo', '=', $g->dsc_grupo)
                        ->where('extra_producto_externo.multiple', '=', $g->multiple)
                        ->get() ?? [];
                    foreach ($listExtras as $le) {
                        if ($le->es_requerido) {
                            $requerido = true;
                        }

                        if ($le->multiple) {
                            $multiple = true;
                        }
                    }
                    $extras = [
                        'grupo' => $g->dsc_grupo,
                        'requerido' =>  $requerido,
                        'multiple' =>  $multiple,
                        'extras' =>  $listExtras
                    ];
                    array_push($extrasAux, $extras);
                }
                $p->extras = $extrasAux;
            }
        }
        return $categorias;
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

    public function validarCodDescuento(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $codigo_descuento = $request->input('codigo_descuento');
        return FacturacionController::verificaCodDescuento($codigo_descuento);
    }

    public static function verificaCodDescuento($codigo_descuento)
    {
        if ("" == $codigo_descuento ||  $codigo_descuento == null) {
            return [
                "codigo" => 500,
                "mensaje" => "Debe incluir el código a verificar",
                "datos" => "",
                "estado" => false
            ];
        }
        $fecha_actual = date("Y-m-d H:i:s");
        $descuento = DB::table('codigo_descuento')
            ->join('sis_tipo', 'sis_tipo.id', '=', 'codigo_descuento.tipo')
            ->select('codigo_descuento.*', 'sis_tipo.cod_general')
            ->where('fecha_inicio', '<=', $fecha_actual)
            ->where('codigo', '=', $codigo_descuento)
            ->where('fecha_fin', '>=', $fecha_actual)
            ->where('cant_codigos', '>', 0)
            ->where('activo', 1)
            ->get()
            ->first();

        if ($descuento != null) {
            return [
                "codigo" => 500,
                "mensaje" => "",
                "datos" => $descuento,
                "estado" => true
            ];
        } else {
            return [
                "codigo" => 500,
                "mensaje" => "No se encontró un código de descuento activo con el código  brindado",
                "datos" => "",
                "estado" => false
            ];
        }
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


    public function getClientes()
    {
        return DB::table('cliente')
            ->where('estado', 'A')
            ->get();
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

    private function validarOrden($orden, $detalles)
    {
        if (count($detalles) < 1) {
            return $this->responseAjaxServerError("Debes agregar detalles a la orden.", []);
        }

        /*if ($orden['estado'] == null || $orden['estado'] == "") {
            return $this->responseAjaxServerError("La orden no tiene estado.", []);
        }*/
        return  $this->responseAjaxSuccess("", "");
    }

    public static function calcularMontosDetalles($detalles)
    {
        $total = 0;
        $subtotal = 0;
        $montoImpuestos = 0;
        $montoImpuestoServicioMesa = 0;

        foreach ($detalles as $d) {
            if ($d['cantidad'] > 0) {
                $totalProducto = $d['cantidad'] * $d['precio_unidad'];
                $totalExtras = 0;
                if (isset($d['extras'])) {
                    foreach ($d['extras'] as $extra) {
                        $totalExtras = $totalExtras + ($d['cantidad'] * $extra['precio']);
                    }
                }


                $totalProducto = $totalProducto + $totalExtras;
                if ($d['impuestoServicio'] == 'S') {
                    $impuestoServicio = $totalProducto - ($totalProducto / 1.10);

                    $totalProducto = $totalProducto + $impuestoServicio;

                    $montoImpuestoServicioMesa = $montoImpuestoServicioMesa + $impuestoServicio;
                }

                if ($d['impuesto'] > 0) {
                    $productoImpuesto = $totalProducto - ($totalProducto / (floatval("1." . $d['impuesto'])));
                    $montoImpuestos = $montoImpuestos + $productoImpuesto;
                } else {
                    $productoImpuesto = 0;
                }
                $subtotal = $subtotal + ($totalProducto - $productoImpuesto);
            }
        }
        $total = $subtotal + $montoImpuestos;

        return [
            'total' => $total,
            'subtotal' => $subtotal,
            'montoImpuestos' => $montoImpuestos,
            "totalExtras" => $totalExtras,
            'montoImpuestoServicioMesa' => $montoImpuestoServicioMesa,
        ];
    }

    public static function asignarMontosDetalles($detalles, $totalOrden, $descuento)
    {
        $total = 0;
        $subtotal = 0;
        $montoImpuestos = 0;
        $montoImpuestoServicioMesa = 0;
        $listaDetallesNueva = [];  // Crear una lista vacía

        foreach ($detalles as $d) {
            if ($d['cantidad'] > 0) {
                $totalProducto = $d['cantidad'] * $d['precio_unidad'];
                $totalExtras = 0;
                $extraLinea = 0;
                if (isset($d['extras'])) {
                    foreach ($d['extras'] as $extra) {
                        $extraLinea = $d['cantidad'] * $extra['precio'];
                        $totalExtras = $totalExtras + $extraLinea;
                    }
                }

                $totalProducto = $totalProducto + $totalExtras;
                if ($d['impuestoServicio'] == 'S') {
                    $impuestoServicio = $totalProducto - ($totalProducto / 1.10);

                    $totalProducto = $totalProducto + $impuestoServicio;

                    $montoImpuestoServicioMesa = $montoImpuestoServicioMesa + $impuestoServicio;
                }

                $porcentajeDescuento = $totalProducto / $totalOrden;
                $montoDescuentoLinea = $porcentajeDescuento * $descuento;
                $totalProducto  = $totalProducto - $montoDescuentoLinea;
                if ($d['impuesto'] > 0) {
                    $productoImpuesto = $totalProducto - ($totalProducto / (floatval("1." . $d['impuesto'])));
                    $montoImpuestos = $montoImpuestos + $productoImpuesto;
                } else {
                    $productoImpuesto = 0;
                }
                $d['montoIva'] = $productoImpuesto;
                $d['subTotal'] = ($totalProducto - $productoImpuesto);
                $d['totalGen'] = $totalProducto;
                $objeto = [
                    'totalGen' => $totalProducto + $montoDescuentoLinea,
                    'subTotal' => ($totalProducto - $productoImpuesto),
                    'totalExtras' => $extraLinea,
                    'extras' => $d['extras'] ?? [],
                    'montoIva' =>  $productoImpuesto,
                    'descuento' => $montoDescuentoLinea,
                    'detalle' =>  $d
                ];
                array_push($listaDetallesNueva, $objeto);
                $subtotal = $subtotal + ($totalProducto - $productoImpuesto);
            }
        }

        $subtotal = $subtotal + $montoImpuestos + $descuento;
        $total = $subtotal - $descuento;

        return [
            'total' => $total,
            'detalles' => $listaDetallesNueva,
            'total_pagar' => $total,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'montoImpuestos' => $montoImpuestos,
            "totalExtras" => $totalExtras,
            'montoImpuestoServicioMesa' => $montoImpuestoServicioMesa,
        ];
    }

    public static function getConsecutivoNuevaOrdenSucursal($sucursal)
    {
        $consecutivo = DB::table('sucursal')
            ->select('sucursal.cont_ordenes', 'sucursal.cod_general')
            ->where('sucursal.id', $sucursal)
            ->get()->first();
        return date('Y') . '-' . $consecutivo->cod_general . '-' . ($consecutivo->cont_ordenes + 1);
    }

    public static function aumentarConsecutivoOrden($sucursal)
    {
        $params = DB::table('sucursal')
            ->select('sucursal.cont_ordenes')
            ->where('id', '=', $sucursal)
            ->get()->first();
        DB::table('sucursal')
            ->where('id', '=', $sucursal)
            ->update(['cont_ordenes' => $params->cont_ordenes + 1]);
    }


    public function crearFactura(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $orden = $request->input("orden");
        $detalles = $request->input("detalles");
        $resValidar = $this->validarOrden($orden, $detalles);
        if (!$resValidar['estado']) {
            return $this->responseAjaxServerError($resValidar['mensaje'], []);
        }
        $existeDescuento = false;
        $descuento = 0;
        if ($orden['codigo_descuento'] != null) {
            $descuento = $orden['codigo_descuento'];
            $verificaCodDesc = FacturacionController::verificaCodDescuento($descuento['codigo']);
            if (!$verificaCodDesc['estado']) {
                $descuento = 0;
                $existeDescuento = false;
            } else {
                $existeDescuento = true;
            }
        }


        $cliente = $orden['cliente'];

        $infoFacturacionSinDescuento = FacturacionController::calcularMontosDetalles($detalles);
        $totalFacturaGen = $infoFacturacionSinDescuento['total'];
        $totalFacturaGenDescuento = $totalFacturaGen;
        $totalDescuentoGen = 0;
        $descuentoObj = null;
        if ($existeDescuento) {
            $descuentoObj = $verificaCodDesc['datos'];
            if ($descuentoObj->cod_general == 'DESCUENTO_ABSOLUTO') {
                $totalDescuentoGen = $descuentoObj->descuento;
                if ($totalDescuentoGen > $totalFacturaGen) {
                    return $this->responseAjaxServerError("El total del descuento no puede ser mayor al total de la factura", []);
                }
                $totalFacturaGenDescuento = $totalFacturaGenDescuento - $totalDescuentoGen;
            } else if ($descuentoObj->cod_general == 'DESCUENTO_PORCENTAJE') {
                $totalDescuentoGen = $totalFacturaGen * ($descuentoObj->descuento / 100);
                $totalFacturaGenDescuento = $totalFacturaGenDescuento - $totalDescuentoGen;
            } else {
                return $this->responseAjaxServerError("No se encontro el tipo de descuento a aplicar", []);
            }
        }
        $asignarMontosDetalles = FacturacionController::asignarMontosDetalles($detalles, $totalFacturaGen, $totalDescuentoGen);
        $detallesGuardar = $asignarMontosDetalles['detalles'];
        $infoFacturacionFinal = $asignarMontosDetalles;
        $fechaActual = date("Y-m-d H:i:s");

        $mto_sinpe = $request->input("mto_sinpe");
        $mto_efectivo = $request->input("mto_efectivo");
        $mto_tarjeta = $request->input("mto_tarjeta");

        try {
            DB::beginTransaction();


            $id_orden = DB::table('orden')->insertGetId([
                'id' => null, 'numero_orden' => $this->getConsecutivoNuevaOrdenSucursal($this->getUsuarioSucursal()),
                'tipo' => null, 'fecha_fin' => $fechaActual, 'fecha_inicio' => $fechaActual, 'cliente' => null,
                'nombre_cliente' => $cliente, 'estado' => null, 'total' => $infoFacturacionFinal['total'], 'total_con_descuento' => $infoFacturacionFinal['total_pagar'], 'subtotal' => $infoFacturacionFinal['subtotal'],
                'impuesto' => $infoFacturacionFinal['montoImpuestos'], 'descuento' => $totalDescuentoGen,
                'cajero' => session('usuario')['id'], 'monto_sinpe' => $mto_sinpe, 'monto_tarjeta' =>  $mto_tarjeta, 'monto_efectivo' => $mto_efectivo,
                'factura_electronica' => 'N', 'ingreso' => null, 'sucursal' => $this->getUsuarioSucursal(),
                'fecha_preparado' => $fechaActual, 'fecha_entregado' => $fechaActual,
                'cocina_terminado' => 'N', 'bebida_terminado' => 'N', 'caja_cerrada' => 'N', 'pagado' => 1,
                'estado' => SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION'),
                'periodo' => date('Y'), 'cierre_caja' => CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal())
            ]);
            $this->aumentarConsecutivoOrden($this->getUsuarioSucursal());

            foreach ($detallesGuardar as $det) {
                $d = $det['detalle'];
                if ($d['cantidad'] > 0) {
                    $producto = $d['producto'];
                    $det_id = DB::table('detalle_orden')->insertGetId([
                        'id' => null, 'cantidad' => $d['cantidad'],
                        'nombre_producto' => $producto['nombre'], 'codigo_producto' => $producto['codigo'],
                        'precio_unidad' => $d['precio_unidad'],
                        'impuesto' => $det['montoIva'], 'total' => $det['totalGen'], 'descuento' => $det['descuento'], 'subtotal' => $det['subTotal'], 'total_extras' => $det['totalExtras'],
                        'orden' => $id_orden, 'tipo_producto' => $d['tipo'], 'servicio_mesa' => $d['impuestoServicio'], 'observacion' => $d['observacion'],
                        'tipo_comanda' => $d['tipoComanda']
                    ]);
                    foreach ($det['extras'] ?? [] as $extra) {
                        $ext_id = DB::table('extra_detalle_orden')->insertGetId([
                            'id' => null, 'detalle' => $det_id,
                            'orden' => $id_orden, 'descripcion_extra' => $extra['descripcion'],
                            'total' => $extra['precio'] * $d['cantidad']
                        ]);
                    }
                }
            }

            $res = $this->restarInventarioOrden($id_orden);
            if ($existeDescuento) {
                CodigosPromocionController::usarPromocion($descuentoObj->id);
            }

            if (!$res['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($res['mensaje'], []);
            }
            DB::commit();

            return $this->responseAjaxSuccess("Pedido creado correctamente.", $id_orden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

    public function anularOrden(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idOrden = $request->input("idOrden");
        $lineas =  $request->input("lineas");
        $enteros = array_map('intval', $lineas);
        if ($idOrden == null || $idOrden == 0) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        $orden = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.cod_general')
            ->where('orden.id', '=', $idOrden)->get()->first();


        if ($orden == null) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        if ($orden->cod_general == 'ORD_ANULADA') {
            return $this->responseAjaxServerError("La orden no se encuentra en un estado para ser anulada", []);
        }

        try {
            DB::beginTransaction();

            DB::table('orden')
                ->where('id', '=', $idOrden)
                ->update(['estado' =>  SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA')]);

            $res = $this->devolverInventarioOrden($idOrden, $enteros);

            if (!$res['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($res['mensaje'], []);
            }
            DB::commit();


            return $this->responseAjaxSuccess("Pedido anulado correctamente.", $idOrden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

    public function recargarOrdenes()
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $ordenes = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.nombre as estadoOrden', 'sis_estado.cod_general')
            ->where('orden.cierre_caja', '=', CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal()))
            ->orderBy('orden.fecha_inicio', 'DESC')->get();

        foreach ($ordenes as $o) {
            $o->detalles = DB::table('detalle_orden')->where('orden', '=', $o->id)->get();
        }
        return $this->responseAjaxSuccess("Pedido creado correctamente.", $ordenes);
    }


    public function devolverInventarioOrden($id_orden, $lineas)
    {


        $detalles = DB::table('detalle_orden')->select('detalle_orden.*')->where('orden', '=', $id_orden)->whereIn('detalle_orden.id', $lineas)->get();
        foreach ($detalles as $d) {
            if ($d->tipo_producto == 'R') {
                $res = $this->devolverInventarioMateriaPrima($d);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            } else if ($d->tipo_producto == 'E') {
                $res = $this->devolverInventarioProductoExterno($d);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            }
        }
        return $this->responseAjaxSuccess("", "");
    }


    private function restarInventarioOrden($id_orden)
    {
        $detalles = DB::table('detalle_orden')->select('detalle_orden.*')->where('orden', '=', $id_orden)->get();
        foreach ($detalles as $d) {
            if ($d->tipo_producto == 'R') {
                $res = $this->restarInventarioMateriaPrima($d);
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

    public function devolverInventarioMateriaPrima($detalle)
    {
        try {
            $cantidadRebajar = $detalle->cantidad;
            $codigoProductoRebajar = $detalle->codigo_producto;
            $mt_prod = DB::table('mt_x_producto')
                ->leftjoin('producto_menu', 'producto_menu.id', '=', 'mt_x_producto.producto')
                ->select('mt_x_producto.*')
                ->where('producto_menu.codigo', '=', $codigoProductoRebajar)
                ->get();

            foreach ($mt_prod as $i) {
                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario + $i->cantidad]);
            }

            /* foreach ($detalle['extras'] ?? [] as $e) {
                $e = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');
                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario - $i->cantidad]);
            }*/

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }


    private function restarInventarioMateriaPrima($detalle)
    {
        try {
            $cantidadRebajar = $detalle->cantidad;
            $codigoProductoRebajar = $detalle->codigo_producto;
            $mt_prod = DB::table('mt_x_producto')
                ->leftjoin('producto_menu', 'producto_menu.id', '=', 'mt_x_producto.producto')
                ->select('mt_x_producto.*')
                ->where('producto_menu.codigo', '=', $codigoProductoRebajar)
                ->get();

            foreach ($mt_prod as $i) {
                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario - $i->cantidad]);
            }

            /* foreach ($detalle['extras'] ?? [] as $e) {
                $e = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');
                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario - $i->cantidad]);
            }*/

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
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

    public function devolverInventarioProductoExterno($detalle)
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
                ->update(['cantidad' => $inventario->cantidad + $cantidadRebajar]);
        }

        return $this->responseAjaxSuccess("", "");
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
}