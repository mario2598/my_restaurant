<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Events\ordenesEvent;
use App\Traits\SpaceUtil;
use Exception;

class OrdenesController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function procesarNuevaOrden(Request $request)
    {
        if (!$this->validarSesion(array("fac_ord", "facFacRuta"))) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $orden = $request->input("orden");

        $resValidar = $this->validarOrden($orden);

        if (!$resValidar['estado']) {
            return $this->responseAjaxServerError($resValidar['mensaje'], []);
        }

        $detalles = $orden['detalles'];
        $cliente = $orden['idCliente'] < 1 ? null :  $orden['idCliente'];
        $tipoOrden = 'CR'; // Caja Rapida, Significa que no hay productos de orden de cocina
        $estadoOrden = 'PT'; // Preparación Terminada, 
        $bebida_terminado = 'S';
        $cocina_terminado = 'S';
        $fechaActual = date("Y-m-d H:i:s");

        /**
         * Define el tipo de la orden
         */
        foreach ($detalles as $d) {
            if ($d['impuestoServicio'] === "S") {
                $tipoOrden = 'CA'; // Sgnifica que la orden de cocina va a ser para comer aqui
                if ($estadoOrden != 'EP') {
                    $estadoOrden = 'PT';
                }
                if ($d['tipo'] == 'R') {
                    $estadoOrden = 'EP';
                    if ($d['tipoComanda'] == 'CO') {
                        $cocina_terminado = 'N';
                    } else if ($d['tipoComanda'] == 'BE') {
                        $bebida_terminado = 'N';
                    }
                }
            } else {
                if ($d['tipo'] == 'R') {
                    if ($tipoOrden != 'CA') {
                        $tipoOrden = 'LL'; // Sgnifica que la orden de cocina va a ser para  llevar
                    }
                    $estadoOrden = 'EP';
                    if ($d['tipoComanda'] == 'CO') {
                        $cocina_terminado = 'N';
                    } else if ($d['tipoComanda'] == 'BE') {
                        $bebida_terminado = 'N';
                    }
                }
            }
        }
        if ($tipoOrden == 'CR') {
            $estadoOrden = 'LF';
        }
        if ($tipoOrden == 'CA') {
            if ($orden['mesaId'] < 1) {
                return $this->responseAjaxServerError("Debe asignar una mesa.", []);
            }
        }

        $mesa = $orden['mesaId'] ?? null;
        if ($mesa == '' || $mesa == null) {
            $orden['mesaId'] = null;
        } else if ($mesa  < 1) {
            $orden['mesaId'] = null;
        }

        try {
            DB::beginTransaction();

            $resFacturacion = $this->calcularFacturacionNuevaOrden($detalles);

            if (!$resFacturacion['estado']) {
                return $this->responseAjaxServerError($resFacturacion['mensaje'], []);
            }

            $facturacion = $resFacturacion['facturacion'];

            $id_orden = DB::table('orden')->insertGetId([
                'id' => null, 'numero_orden' => $this->getConsecutivoNuevaOrden(),
                'tipo' => $tipoOrden, 'fecha_fin' => $fechaActual, 'fecha_inicio' => $fechaActual, 'cliente' => $cliente,
                'nombre_cliente' => $orden['nombreCliente'], 'estado' => $estadoOrden, 'total' => $facturacion['total'], 'subtotal' => $facturacion['subtotal'],
                'porcentaje_impuesto' => 0, 'porcentaje_descuento' => 0, 'impuesto' => $facturacion['montoImpuestos'], 'descuento' => 0,
                'total_cancelado' => 0, 'cajero' => session('usuario')['id'], 'monto_sinpe' => 0, 'monto_tarjeta' => 0, 'monto_efectivo' => 0, 'monto_otros' => 0,
                'factura_electronica' => 'N', 'ingreso' => null, 'restaurante' => $this->getRestauranteUsuario(), 'comision_restaurante' => $facturacion['montoImpuestoServicioMesa'],
                'mobiliario_salon' => $orden['mesaId'], 'fecha_preparado' => $fechaActual, 'fecha_entregado' => $fechaActual,
                'cocina_terminado' => $cocina_terminado, 'bebida_terminado' => $bebida_terminado, 'caja_cerrada' => 'N'
            ]);
            $this->aumentarConsecutivoOrden();

            foreach ($detalles as $d) {
                if ($d['cantidad'] > 0) {
                    $producto = $d['producto'];
                    $det = DB::table('detalle_orden')->insertGetId([
                        'id' => null, 'cantidad' => $d['cantidad'],
                        'nombre_producto' => $producto['nombre'], 'codigo_producto' => $producto['codigo'], 'precio_unidad' => $d['precio_unidad'], 'porcentaje_impuesto' => $d['impuesto'], 'impuesto' => $d['precio_unidad'] - ($d['precio_unidad'] / 1.13),
                        'orden' => $id_orden, 'tipo_producto' => $d['tipo'], 'servicio_mesa' => $d['impuestoServicio'], 'observacion' => $d['observacion'],
                        'tipo_comanda' => $d['tipoComanda'], 'cantidad_preparada' => 0, 'fecha_creacion' => $fechaActual
                    ]);
                }
            }

            DB::commit();

            $destinatarios = array();
            if ($tipoOrden != 'CR') {
                if ($cocina_terminado == 'N') {
                    array_push($destinatarios, 'COM_COC'); // comandas cocina
                }
                if ($bebida_terminado == 'N') {
                    array_push($destinatarios, 'COM_BEB'); // comandas bebida
                }
                $data = [
                    'destinatarios' => $destinatarios,
                ];
                try {
                    broadcast(new ordenesEvent($data));
                } catch (Exception $ex) {
                    return $this->responseAjaxSuccess("Pedido creado correctamente.", $id_orden);
                }
            }
            return $this->responseAjaxSuccess("Pedido creado correctamente.", $id_orden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

    public function actualizarOrden(Request $request)
    {
        if (!$this->validarSesion("fac_ord")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $orden = $request->input("orden");
        $ordenId = $orden['id'];

        $ordenAux = FacturacionController::getOrden($ordenId);

        if ($ordenAux == null) {
            $this->setError("Factura", "No existe la orden.");
            return redirect('cocina/facturar/ordenes');
        }

        if ($ordenAux->estado == 'FC' || $ordenAux->estado == 'EPF' || $ordenAux->estado == 'PTF') {
            $this->setError("Factura", "La orden ya fue facturada.");
            return redirect('cocina/facturar/ordenes');
        }

        $resValidar = $this->validarOrden($orden);

        if (!$resValidar['estado']) {
            return $this->responseAjaxServerError($resValidar['mensaje'], []);
        }

        $detalles = $orden['detalles'];

        try {
            DB::beginTransaction();

            $resFacturacion = $this->calcularFacturacionNuevaOrden($detalles);

            if (!$resFacturacion['estado']) {
                return $this->responseAjaxServerError($resFacturacion['mensaje'], []);
            }

            $facturacion = $resFacturacion['facturacion'];

            DB::table('detalle_orden')
                ->where('orden', '=', $ordenAux->id)->delete();
            $bebidasPendiente = false;
            $comidasPendiente = false;
            $fechaActual = date("Y-m-d H:i:s");
            foreach ($detalles  as $d) {
                $fechaAux = $d['fechaCreacion'];
                if ($d['fechaCreacion'] == '0') {
                    $fechaAux = $fechaActual;
                }
                if ($d['cantidad'] > 0) {
                    if ($d['tipo'] == 'R' && $d['cantidad'] != $d['cantidadPreparada']) {
                        if ($d['tipoComanda'] == 'CO') {
                            $comidasPendiente = true;
                        }
                        if ($d['tipoComanda'] == 'BE') {
                            $bebidasPendiente = true;
                        }
                    }
                    $producto = $d['producto'];
                    $det = DB::table('detalle_orden')->insertGetId([
                        'id' => null, 'cantidad' => $d['cantidad'],
                        'codigo_producto' => $producto['codigo'], 'nombre_producto' => $producto['nombre'], 'precio_unidad' => $d['precio_unidad'], 'porcentaje_impuesto' => $d['impuesto'], 'impuesto' => $d['precio_unidad'] - ($d['precio_unidad'] / 1.13),
                        'orden' => $ordenAux->id, 'tipo_producto' => $d['tipo'], 'servicio_mesa' => $d['impuestoServicio'], 'observacion' => $d['observacion'],
                        'tipo_comanda' => $d['tipoComanda'], 'cantidad_preparada' => $d['cantidadPreparada'], 'fecha_creacion' => $fechaAux
                    ]);
                }
            }


            if ($comidasPendiente) {
                $comidaTerminado = 'N';
                $estado = 'EP';
            } else {
                $comidaTerminado = 'S';
            }

            if ($bebidasPendiente) {
                $estado = 'EP';
                $bebidaTerminado = 'N';
            } else {
                $bebidaTerminado = 'S';
            }

            if (!$bebidasPendiente && !$comidasPendiente) {
                $estado = $ordenAux->estado;
            } else {
                $estado = 'EP';
            }

            DB::table('orden')
                ->where('id', '=', $ordenAux->id)
                ->update([
                    'total' => $facturacion['total'], 'subtotal' => $facturacion['subtotal'],
                    'comision_restaurante' => $facturacion['montoImpuestoServicioMesa'], 'impuesto' => $facturacion['montoImpuestos'],
                    'cocina_terminado' =>  $comidaTerminado, 'bebida_terminado' => $bebidaTerminado, 'estado' => $estado
                ]);

            DB::commit();

            $destinatarios = array();
            array_push($destinatarios, 'COM_BEB'); // comandas bebida
            array_push($destinatarios, 'COM_COC'); // comandas cocina
            $data = [
                'destinatarios' => $destinatarios,
            ];
            try {
                broadcast(new ordenesEvent($data));
            } catch (Exception $ex) {
                return $this->responseAjaxSuccess("Pedido actualizado correctamente.", $ordenAux->id);
            }
            return $this->responseAjaxSuccess("Pedido actualizado correctamente.", $ordenAux->id);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

    public static function calcularFacturacionNuevaOrden($detalles)
    {


        $total = 0;
        $subtotal = 0;
        $montoImpuestos = 0;
        $montoImpuestoServicioMesa = 0;

        foreach ($detalles as $d) {
            if ($d['cantidad'] > 0) {
                $totalProducto = $d['cantidad'] * $d['precio_unidad'];

                if ($d['impuestoServicio'] == 'S') {
                    $impuestoServicio = $totalProducto - ($totalProducto / 1.10);

                    $totalProductoImpuesto = $totalProducto + $impuestoServicio;

                    $montoImpuestoServicioMesa = $montoImpuestoServicioMesa + $impuestoServicio;
                } else {
                    $totalProductoImpuesto = $totalProducto;
                }

                if ($d['impuesto'] > 0) {
                    $productoImpuesto = $totalProductoImpuesto - ($totalProductoImpuesto / 1.13);
                } else {
                    $productoImpuesto = 0;
                }

                $montoImpuestos = $montoImpuestos + $productoImpuesto;
                $subtotal = $subtotal + ($totalProducto - $productoImpuesto);

                $total = $total  + $totalProductoImpuesto;
            }
        }

        return [
            'estado' => true,
            'mensaje' => '',
            'facturacion' => [
                'total' => $total,
                'subtotal' => $subtotal,
                'montoImpuestos' => $montoImpuestos,
                'montoImpuestoServicioMesa' => $montoImpuestoServicioMesa,
            ]
        ];
    }

    public static function calcularFacturacionOrden($idOrden)
    {
        $detalles = DB::table('detalle_orden')->where('orden', '=', $idOrden)->get();

        if (count($detalles) < 1) {
            return [
                'estado' => false,
                'mensaje' => 'No hay detalles en la orden.'
            ];
        }

        $total = 0;
        $subtotal = 0;
        $montoImpuestos = 0;
        $montoImpuestoServicioMesa = 0;

        foreach ($detalles as $d) {
            if ($d->cantidad > 0) {
                $totalProducto = $d->cantidad  * $d->precio_unidad;

                if ($d->impuestoServicio == 'S') {
                    $impuestoServicio = $totalProducto - ($totalProducto / 1.10);
                    $totalProducto = $totalProducto + $impuestoServicio;
                    $montoImpuestoServicioMesa = $montoImpuestoServicioMesa + $impuestoServicio;
                } else {
                    $totalProductoImpuesto = $totalProducto;
                }

                if ($d->impuesto > 0) {
                    $productoImpuesto = $totalProductoImpuesto - ($totalProductoImpuesto / 1.13);
                } else {
                    $productoImpuesto = 0;
                }

                $montoImpuestos = $montoImpuestos + $productoImpuesto;
                $subtotal = $subtotal + ($totalProducto - $productoImpuesto);



                $total = $total  + $totalProductoImpuesto;
            }
        }

        return [
            'estado' => true,
            'mensaje' => '',
            'facturacion' => [
                'total' => $total,
                'subtotal' => $subtotal,
                'montoImpuestos' => $montoImpuestos,
                'montoImpuestoServicioMesa' => $montoImpuestoServicioMesa,
            ]
        ];
    }

    public static function getOrden($idOrden)
    {
        $orden = DB::table('orden')->leftjoin('restaurante', 'restaurante.id', '=', 'orden.restaurante')
            ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
            ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'restaurante.sucursal')
            ->select('orden.*', 'mobiliario_x_salon.numero_mesa', 'sucursal.descripcion as nombre_sucursal')
            ->where('orden.id', '=', $idOrden)->get()->first();
        if ($orden == null) {
            return [
                'estado' => false,
                'mensaje' => 'Orden no existe.'
            ];
        }
        $detalles = DB::table('detalle_orden')->where('orden', '=', $idOrden)->get();

        if (count($detalles) < 1) {
            return [
                'estado' => false,
                'mensaje' => 'No hay detalles en la orden.'
            ];
        }

        $orden->detalles = $detalles;
        return [
            'estado' => true,
            'mensaje' => '',
            'orden' => $orden
        ];
    }

    public static function getConsecutivoNuevaOrden()
    {
        $consecutivo = DB::table('parametros_orden')
            ->select('parametros_orden.consecutivo_orden')
            ->get()->first();
        return $consecutivo->consecutivo_orden + 1;
    }

    public static function aumentarConsecutivoOrden()
    {
        $params = DB::table('parametros_orden')
            ->select('parametros_orden.*')
            ->get()->first();

        DB::table('parametros_orden')
            ->where('id', '=', $params->id)
            ->update(['consecutivo_orden' => $params->consecutivo_orden + 1]);
    }

    private function validarOrden($orden)
    {
        if (count($orden['detalles']) < 1) {
            return $this->responseAjaxServerError("Debes agregar detalles a la orden.", []);
        }

        /*if ($orden['estado'] == null || $orden['estado'] == "") {
            return $this->responseAjaxServerError("La orden no tiene estado.", []);
        }*/
        return  $this->responseAjaxSuccess("", "");
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
            ->where('orden.estado', '=', 'LF')
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
        }

        return $ordenes;
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
}
