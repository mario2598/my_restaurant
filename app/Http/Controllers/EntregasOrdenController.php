<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;

class EntregasOrdenController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function crearEntregaOrden($precio, $dsc_lugar, $dsc_contacto, $orden)
    {
        try {
            $idEst = SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_PREPARACION_PEND');
            $ext_id = DB::table('entrega_orden')->insertGetId([
                'id' => null, 'orden' => $orden, 'precio' => $precio,
                'descripcion_lugar' => $dsc_contacto, 'contacto' => $dsc_contacto,
                'estado' => $idEst, 'encargado' => null
            ]);

            $actEstado = $this->creaEstEntregaOrden($orden, $idEst,null);
            if (!$actEstado['estado']) {
                return $this->responseAjaxServerError($actEstado['mensaje'], []);
            }
            return $this->responseAjaxSuccess("", $ext_id);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal creando el envío");
        }
    }

    public function actualizarEntregaOrden($orden, $idEstado)
    {
        try {
            $entregaOrden = DB::table('entrega_orden')
                ->select('entrega_orden.*')
                ->where('entrega_orden.orden', '=', $orden)->orderBy('entrega_orden.id', 'DESC')
                ->get()->first();

            if ($entregaOrden == null) {
                return $this->responseAjaxServerError("No se encontró la entrega");
            }

            $estadoAnt = DB::table('sis_estado')
                ->select('sis_estado.*')
                ->where('id', '=', $entregaOrden->estado)
                ->get()->first();

            if ($estadoAnt == null) {
                return $this->responseAjaxServerError("No se encontró el estado");
            }

            $entregaOrden = DB::table('entrega_orden')
                ->where('orden', '=', $orden)
                ->update(['estado' =>  $idEstado]);

            $crearBit = $this->creaEstEntregaOrden($orden, $idEstado, $estadoAnt->id);
            if (!$crearBit['estado']) {
                return $this->responseAjaxServerError($crearBit['mensaje'], []);
            }
            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal actualizando el envío");
        }
    }

    public function creaEstEntregaOrden($orden, $idEstado, $idEstadoAnteriorV)
    {
        try {
            $fechaActual = date("Y-m-d H:i:s");

            $entrega = DB::table('entrega_orden')
                ->leftjoin('sis_estado', 'sis_estado.id', '=', 'entrega_orden.estado')
                ->select('entrega_orden.id as idEntrega', 'sis_estado.nombre as dscEstado', 'sis_estado.id as idEstado')
                ->where('entrega_orden.orden', '=', $orden)->orderBy('entrega_orden.id', 'DESC')
                ->get()->first();

            if ($entrega == null) {
                return $this->responseAjaxServerError("No se encontró la entrega");
            }

            $estado = DB::table('sis_estado')
                ->select('sis_estado.*')
                ->where('id', '=', $idEstado)
                ->get()->first();

            if ($estado == null) {
                return $this->responseAjaxServerError("No se encontró el estado");
            }

            if ($idEstadoAnteriorV == null) {
                $texto = "Se crea la entrega en estado [ " . $entrega->dscEstado . " ] " .
                    ". Fecha : " . $fechaActual . ", encargado : " . session('usuario')['usuario'];
            } else {
                $estadoAnt = DB::table('sis_estado')
                    ->select('sis_estado.*')
                    ->where('id', '=',   $idEstadoAnteriorV)->get()->first();

                if ($estadoAnt == null) {
                    return $this->responseAjaxServerError("No se encontró el estado");
                }
                $texto = "La orden cambia del estado [ " . $estadoAnt->nombre . " ] al estado [ " . $estado->nombre . " ] " .
                    ". Fecha : " . $fechaActual . ", encargado : " . session('usuario')['usuario'];
            }

            DB::table('est_entrega_orden')->insert([
                'id' => null, 'usuario' => session('usuario')['id'],
                'entrega_orden' => $entrega->idEntrega, 'estado' => $idEstado,
                'fecha' =>  $fechaActual, 'descripcion' => $texto
            ]);

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal creando el estado del envío");
        }
    }
}
