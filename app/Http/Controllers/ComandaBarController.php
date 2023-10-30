<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;
class ComandaBarController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {
       
        setlocale(LC_ALL, "es_CR");
    }
    
    public function index(){

    }

    public function recargar(Request $r){
        if($this->getUsuarioAuth() == null){
            return 0;
        }

        $ordenes = DB::table('orden')
            ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
            ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
            ->select('orden.*', 'mobiliario_x_salon.numero_mesa', 'mobiliario.nombre as nombre_mobiliario', 'mobiliario.descripcion as descripcion_mobiliario')
            ->whereIn('orden.estado', array("LF","EP","PT"))
            ->where('orden.restaurante', '=', $this->getUsuarioRestaurante())
            ->orderBy('orden.fecha_inicio', 'ASC')->get();

        $data = [
            'ordenes' => $ordenes,
        ];
        return view('layout.layout.comandaBar',compact('data'));
    }

    public function goMovimiento(Request $request){
        if(!$this->validarSesion(array("invTras","goMov"))){
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idMov');
        if($id== null || $id < 1){
            $this->setError("Cargar Movimiento","El movimiento no existe!");
            return redirect('/');
        }

        return $this->goMovimientoId($id);
    }

    public function cancelarMovimiento(Request $request){
        if(!$this->validarSesion("invTras")){
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idMovimientoCancelar');
        $detalle = $request->input('detalleMovimientoCancelar');
        if($id== null || $id < 1){
            $this->setError("Cancelar Movimiento","El movimiento no existe!");
            return redirect('/');
        }

        $movimiento = DB::table('movimiento')
        ->where('movimiento.id','=',$id)
        ->get()->first();
       
        if($movimiento == null){
            $this->setError("Cancelar Movimiento","El movimiento no existe!");
            return redirect('/');
        }
        $transaccionRealizada = true;
        $detalle = ($detalle ?? ''). '- '. $this->fechaFormat($movimiento->fecha) .' -Cancelado Por '.$this->getUsuarioAuth()['usuario'];
        try { 
            DB::beginTransaction();
        
           
            DB::table('movimiento')
            ->where('id', '=', $id)
            ->update(['estado'=>'C','detalle'=>$detalle]);

            $detalles =  DB::table('detalle_movimiento')
            ->leftjoin('lote', 'lote.id', '=', 'detalle_movimiento.lote')
            ->leftjoin('producto', 'producto.id', '=', 'detalle_movimiento.producto')
            ->select('detalle_movimiento.*')->where('detalle_movimiento.movimiento','=',$id)
            ->get();

            DB::commit();
            

        }catch(QueryException $ex){ 
            DB::rollBack();
            $transaccionRealizada = false;
        }       
        if(!$transaccionRealizada){
            $this->setError('Cancelar Movimiento','Algo salio mal...');
            return redirect('/');
        }else{
            try { 
                DB::beginTransaction();
                foreach($detalles as $d){
                    
                    $inventarioExitente = DB::table('inventario')
                    ->select('inventario.*')
                    ->where('inventario.sucursal','=',$movimiento->sucursal_inicio)
                    ->where('inventario.lote','=',$d->lote)
                    ->get()->first();
        
                    if($inventarioExitente != null){
                        $cantidadAux = $inventarioExitente->cantidad +$d->cantidad;
                        DB::table('inventario')
                        ->where('id', '=', $inventarioExitente->id)
                        ->update(['cantidad'=> $cantidadAux]);
                        
                    }else{
                        
                        $idInv = DB::table('inventario')->insertGetId( ['id' => null ,
                        'sucursal' => $movimiento->sucursal_inicio,
                        'producto'=> $d->producto,'lote'=> $d->lote,
                        'cantidad'=> $d->cantidad ] );
                    }
                   
                }
                DB::commit();
                $this->setInfo("Cancelar Movimiento","Se cancelo correctamente!");
                return redirect('/');  

            }catch(QueryException $ex){ 
                DB::rollBack();
                DB::table('movimiento')
                ->where('id', '=', $id)
                ->update(['estado'=>'E','detalle'=>$detalle.'.ERROR : El inventario quedo atrapado en el traslado.']);
                $this->setError('Cancelar Movimiento','Algo salio mal...');
                return redirect('/');
            }
        }

    }

    public function goMovimientos(){
        if(!$this->validarSesion("movs")){
            $this->setMsjSeguridad();
            return redirect('/');
        }
       
        $filtros = [
            'despacho' => 0,
            'destino' => 'T',
            'tipo_movimiento' => 'T',
            'estado' => 'T',
            
        ];

        $data = [
            'menus'=> $this->cargarMenus(),
             'filtros' =>$filtros,
             'tipos_movimiento' => $this->getTiposMovimiento(),
             'sucursales' => $this->getSucursales(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];

         return view('bodega.movimientos',compact('data'));
        
    }   
}
