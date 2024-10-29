<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Exception;

class PedidoCocinaController extends Controller
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

    public function goComandaCocina()
    {
        if (!$this->validarSesion("cocina_cmds")) {
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'pedidos_pendientes' => PedidoCocinaController::getOrdenesEnPreparacionCocina($this->getRestauranteUsuario()),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('cocina.cocina.comandas', compact('data'));
    }

    public static function getOrdenesEnPreparacionCocina($restaurante)
    {
        if ($restaurante < 1 || $restaurante == null) {
            return [];
        }

        $ordenesConDetalle = DB::table('detalle_orden')
        ->leftjoin('orden', 'orden.id', '=', 'detalle_orden.orden')
        ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
        ->select(
            'orden.id',
            'detalle_orden.fecha_creacion'
        )
        ->whereIn('orden.estado', array('EP', 'EPF'))
        ->where('orden.restaurante', '=', $restaurante)
        ->where('detalle_orden.tipo_comanda', '=', 'CO')
        ->where('orden.cocina_terminado', '=', 'N')
        ->groupBy('orden.id','detalle_orden.fecha_creacion')
        ->orderBy('detalle_orden.fecha_creacion', 'ASC')->distinct()->get();
        $ordenes = collect();
        foreach ($ordenesConDetalle as $i => $o) {
            $o = DB::table('detalle_orden')
            ->leftjoin('orden', 'orden.id', '=', 'detalle_orden.orden')
            ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
            ->select(
                'orden.*',
                'mobiliario_x_salon.numero_mesa',
                'detalle_orden.fecha_creacion as fecha_creacion_detalle',
                'detalle_orden.id as id_detalle'
            ) ->where('orden.id', '=', $o->id)
            ->where('detalle_orden.fecha_creacion', '=', $o->fecha_creacion)->distinct()->get()->first();
            $ordenes->add($o);
        }

        foreach ($ordenes as $o) {
          
            $phpdate = strtotime($o->fecha_inicio);
            $date = date("d-m-Y", strtotime($o->fecha_inicio));

            $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
            $fechaAux .= ' - ' . date("g:i a", $phpdate);
            $o->fecha_inicio_hora_tiempo = date("g:i a", $phpdate);
            $o->fecha_inicio_texto =  $fechaAux;
      
            $o->detalles = DB::table('detalle_orden')->select('detalle_orden.*')
                ->where('detalle_orden.orden', '=', $o->id)
                ->where('detalle_orden.fecha_creacion', '=', $o->fecha_creacion_detalle)
                ->where('detalle_orden.tipo_comanda', '=', 'CO')
                ->get();

                
            foreach ($o->detalles as  $x => $d) {
                if ($d->cantidad == $d->cantidad_preparada) {
                    unset($o->detalles[$x]);
                }
            }
        }
        foreach ($ordenes as $i => $o) {
            if (count($o->detalles) < 1) {
                unset($ordenes[$i]);
            }
        }
        
        return $ordenes;
    }

    public static function getOrdenesEnPreparacion($restaurante)
    {
        if ($restaurante < 1 || $restaurante == null) {
            return [];
        }

        $ordenes = DB::table('orden')->select('orden.*')
            ->whereIn('orden.estado', array('EP', 'EPF'))
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
                ->where('detalle_orden.orden', '=', $o->restaurante)
                ->get();
        }

        return $ordenes;
    }

    public function terminarPreparacionOrdenCocina(Request $request)
    {
        if (!$this->validarSesion("cocina_cmds")) {
            return $this->responseAjaxServerError('Error de seguridad.', []);
        }

        $id_orden = $request->input('id_orden');
        $fecha_detalle = $request->input('fecha_detalle_orden');

        if ($id_orden < 1 || $this->isNull($id_orden)) {
            $this->setError('Terminar Preparación Orden', 'Id de la orden incorrecto...');
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        $orden = DB::table('orden')->select('orden.*')->where('id', '=', $id_orden)->get()->first();

        if ($orden == null) {
            $this->setError('Terminar Preparación Orden', 'No existe la orden.');
            return $this->responseAjaxServerError('No existe la orden.', []);
        }
       
        if ($fecha_detalle == '' || $this->isNull($fecha_detalle)) {
            $this->setError('Terminar Preparación Orden', 'Id de la orden incorrecto...');
            return $this->responseAjaxServerError('Id de la orden incorrecto...', []);
        }

        if ($orden->estado != 'EP' && $orden->estado != 'EPF') {
            $this->setError('Terminar Preparación Orden', 'La orden ya fue procesada');
            return $this->responseAjaxServerError('La orden ya fue procesada', []);
        }

        try {
            DB::beginTransaction();
            // Si las bebidas no han sido hechas
  
            $comidasTerminadas = $this->comidasTerminadas($id_orden, $fecha_detalle);
            
            $detalles = DB::table('detalle_orden')->select('detalle_orden.*')
            ->where('orden', '=', $id_orden)
            ->where('tipo_comanda', '=', 'CO')
            ->where('fecha_creacion', '=', $fecha_detalle)->get();

            foreach ($detalles as $d) {
                DB::table('detalle_orden')
                ->where('id', '=',$d->id)
                ->update([
                    'cantidad_preparada' => $d->cantidad
                ]);
            }

            $estado = '';
            // Si las COCINAS no han sido hechas
            if ($comidasTerminadas) {
                if ($orden->bebida_terminado == 'N') {
                    $estado = 'EP';
                    if ($orden->estado == 'EPF') {
                        $estado = 'EPF';
                    } else {
                        $estado = 'EP';
                    }
                } else {
                    if ($orden->estado == 'EPF') {
                        $estado = 'PTF';
                    } else {
                        $estado = 'PT';
                    }
                }

                DB::table('orden')
                    ->where('id', '=', $id_orden)
                    ->update([
                        'estado' => $estado, 'fecha_preparado' => date("Y-m-d H:i:s"), 'cocina_terminado' => 'S'
                    ]);
            }
            DB::commit();

            return $this->setAjaxResponse(200, "", [], true);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Terminar Preparación Orden', 'Algo salio mal...');
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    public function recargarOrdenesEsperaCocina(Request $request)
    {
        if (!$this->validarSesion("cocina_cmds")) {
            return 'Error de seguridad!';
        }

        $data = [
            'pedidos_pendientes' => PedidoCocinaController::getOrdenesEnPreparacionCocina($this->getRestauranteUsuario()),
        ];
        return view('cocina.cocina.layout.contenedor_comandas', compact('data'));
    }

    public function comidasTerminadas($orden, $fecha_detalle)
    {
        $detalles = DB::table('detalle_orden')->select('id', 'cantidad', 'cantidad_preparada')
            ->where('orden', '=', $orden)
            ->where('tipo_producto', '=', 'R')
            ->where('tipo_comanda', '=', 'CO')
            ->where('fecha_creacion', '<>', $fecha_detalle)
            ->get();

        foreach ($detalles as $d) {
            if ($d->cantidad > $d->cantidad_preparada) {
                return false;
            }
        }
        return true;
    }
}
