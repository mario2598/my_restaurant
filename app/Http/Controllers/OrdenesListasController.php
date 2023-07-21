<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class OrdenesListasController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function goOrdenesListasEntregar()
    {
        if (!$this->validarSesion("ordList_cmds")) {
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'ordenes_listas' => OrdenesListasController::getOrdenesListasEntregar($this->getRestauranteUsuario()),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('cocina.ordenesListas.comanda', compact('data'));
    }

    public static function getOrdenesListasEntregar($restaurante)
    {
        if ($restaurante < 1 || $restaurante == null) {
            return [];
        }

        $ordenes = DB::table('orden')
            ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
            ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
            ->select('orden.*', 'mobiliario_x_salon.numero_mesa', 'mobiliario.nombre as nombre_mobiliario', 'mobiliario.descripcion as descripcion_mobiliario')
            ->whereIn('orden.estado', array('PT', 'PTF'))
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

        if ($orden->estado != 'PT' && $orden->estado != 'PTF') {
            $this->setError('Entregar Orden', 'La orden ya fue procesada');
            return $this->responseAjaxServerError('La orden ya fue procesada', []);
        }

        try {
            DB::beginTransaction();
            if ($orden->estado == 'PTF') {
                $estado = 'FC';
            } else {
                $estado = 'LF';
            }
            DB::table('orden')
                ->where('id', '=', $id_orden)
                ->update([
                    'estado' => $estado, 'fecha_preparado' => date("Y-m-d H:i:s")
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
