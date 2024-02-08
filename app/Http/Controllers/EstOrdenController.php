<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;

class EstOrdenController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }


    public function creaEstOrden($idOrden, $idEstado, $estAnterior)
    {
        try {
            $fechaActual = date("Y-m-d H:i:s");
            $orden = DB::table('orden')
                ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
                ->select('orden.id as idOrden', 'sis_estado.nombre as dscEstado', 'sis_estado.id as idEstado')
                ->where('orden.id', '=', $idOrden)
                ->get()->first();

            if ($orden == null) {
                return $this->responseAjaxServerError("No se encontró la orden");
            }

            $estado = DB::table('sis_estado')
                ->select('sis_estado.*')
                ->where('id', '=', $idEstado)
                ->get()->first();

            if ($estado == null) {
                return $this->responseAjaxServerError("No se encontró el estado");
            }

            if ($estAnterior == null) {
                $texto = "Se crea la orden en estado [ " . $orden->dscEstado . " ] " .
                    ". Fecha : " . $fechaActual . ", encargado : " . session('usuario')['usuario'];
            } else {
                $estadoAnt = DB::table('sis_estado')
                    ->select('sis_estado.*')
                    ->where('id', '=', $estAnterior)
                    ->get()->first();

                if ($estadoAnt == null) {
                    return $this->responseAjaxServerError("No se encontró el estado");
                }

                $texto = "La orden cambia del estado [ " . $estadoAnt->nombre . " ] al estado [ " . $estado->nombre . " ] " .
                    ". Fecha : " . $fechaActual . ", encargado : " . session('usuario')['usuario'];
            }

            DB::table('est_orden')->insert([
                'id' => null, 'usuario' => session('usuario')['id'],
                'orden' => $idOrden, 'estado' => $idEstado,
                'fecha' =>  $fechaActual, 'descripcion' => $texto
            ]);

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal creando el estado de la orden");
        }
    }
}
