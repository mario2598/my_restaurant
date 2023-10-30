<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;
class MovimientoController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "bodInventarios";
    public function __construct()
    {
       
        setlocale(LC_ALL, "es_CR");
    }
    
    public function index(){

    }

    public function goMovimientoId($id){

        if($id== null || $id < 1){
            $this->setError("Cargar Movimiento","El movimiento no existe!");
            return redirect('/');
        }

        $movimiento = DB::table('movimiento')
        ->leftjoin('tipo_movimiento', 'tipo_movimiento.id', '=', 'movimiento.tipo_movimiento')
        ->leftjoin('sucursal as despacho', 'despacho.id', '=', 'movimiento.sucursal_inicio')
        ->leftjoin('sucursal as destino', 'destino.id', '=', 'movimiento.sucursal_fin')
        ->leftjoin('usuario as entrega', 'entrega.id', '=', 'movimiento.entrega')
        ->leftjoin('usuario as recibe', 'recibe.id', '=', 'movimiento.recibe')
        ->select('movimiento.id','movimiento.detalle', 'movimiento.fecha','movimiento.fecha_entrega',
        'despacho.id as despacho_id','despacho.descripcion as despacho_descripcion',
        'destino.id as destino_id','destino.descripcion as destino_descripcion',
        'entrega.id as entrega_id','entrega.usuario as entrega_usuario',
        'recibe.id as recibe_id','recibe.usuario as recibe_usuario','movimiento.estado',
        'tipo_movimiento.codigo as tipo_movimiento_codigo','tipo_movimiento.descripcion as tipo_movimiento_descripcion')
        ->where('movimiento.id','=',$id)
        ->get()->first();

        $movimiento->fecha = $this->fechaFormat($movimiento->fecha);
        if($movimiento->fecha_entrega != null){
            $movimiento->fecha_entrega = $this->fechaFormat($movimiento->fecha_entrega);
        }
        $movimiento->detalles =  DB::table('detalle_movimiento')
                                ->leftjoin('lote', 'lote.id', '=', 'detalle_movimiento.lote')
                                ->leftjoin('producto', 'producto.id', '=', 'detalle_movimiento.producto')
                                ->select('detalle_movimiento.cantidad', 'detalle_movimiento.id','producto.nombre as producto_nombre',
                                'lote.codigo as lote_codigo','lote.id as lote_id')->where('detalle_movimiento.movimiento','=',$id)
                                ->get();
                               
        $data = [
            'menus'=> $this->cargarMenus(),
            'movimiento' => $movimiento,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
            
        ];

        return view('bodega.movimiento',compact('data'));
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
