<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Exception;

class OrdenesController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {

        setlocale(LC_ALL, "es_CR");
    }

    public function index()
    {
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
        $orden = DB::table('orden')->leftjoin('sucursal', 'sucursal.id', '=', 'orden.sucursal')
            ->select('orden.*',  'sucursal.descripcion as nombre_sucursal')
            ->where('orden.id', '=', $idOrden)->get()->first();
        if ($orden == null) {
            return [
                'estado' => false,
                'mensaje' => 'Orden no existe.'
            ];
        }
        $detalles = DB::table('detalle_orden')->where('orden', '=', $idOrden)->get();

        foreach ($detalles as $d) {
            $d->extras = DB::table('extra_detalle_orden')->where('orden', '=', $idOrden)
            ->where('detalle', '=',  $d->id)->get();
        }

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
