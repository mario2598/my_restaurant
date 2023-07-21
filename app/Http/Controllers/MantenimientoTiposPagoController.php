<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoTiposPagoController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantTipPag";

    public function __construct()
    {
        
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        //dd($this->getTiposGasto());
         $data = [
             'menus'=> $this->cargarMenus(),
             'tipos_pago' => $this->getTiposPago(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.tipos_pago',compact('data'));
    }

    /**
     * Guarda o actualiza un tipo de pago.
     */
    public function guardarTipoPago(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        if ($this->validarTipoGasto($request)) {

            $tipo = $request->input('mdl_generico_ipt_tipo');
            $id = $request->input('mdl_generico_ipt_id');
           
            try { 
                DB::beginTransaction();
                if($id == '-1' || $id == null || $this->isEmpty($id)){
                    $tipo = DB::table('tipo_pago')->insertGetId( ['id' => null ,'tipo'=>$tipo,'estado'=> 'A'] );
                }else{
                    DB::table('tipo_pago')
                        ->where('id', '=', $id)
                        ->update(['tipo'=>$tipo]);
                }
                DB::commit();
                $this->setSuccess('Guardar Tipo Pago','El tipo de pago se guardo correctamente.');
                return redirect('mant/tipospago');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Tipo Pago','Ocurrio un error guardando el tipo de pago.');
                return redirect('mant/tipospago');
            }
        
        }else{
            return redirect('mant/tipospago');
        }
    }

    /**
     * Elimina un tipo.
     */
    public function eliminarTipoPago(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        $id = $request->input('idGenericoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Tipo Pago','Identificador inválido.');
            return redirect('mant/tipospago');
        }
        try { 
            DB::beginTransaction();
            $tipo = DB::table('tipo_pago')->where('id','=',$id)->get()->first();
            if($tipo == null){
                $this->setError('Eliminar Tipo Pago','No existe el tipo de pago a eliminar.');
                return redirect('mant/tipospago');
            }else{
                DB::table('tipo_pago')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Tipo Pago','El tipo de gasto se elimino correctamente.');
            return redirect('mant/tipospago');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Tipo Pago','Ocurrio un error eliminando el tipo de pago.');
            return redirect('mant/tipospago');
        }
        
        
    }

    public function validarTipoGasto(Request $r){
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
       
        if($this->isNull($r->input('mdl_generico_ipt_tipo')) || $this->isEmpty($r->input('mdl_generico_ipt_tipo')) ){
            $requeridos .= " Tipo Pago ";
            $valido = false;
            $esPrimero = false;
        }
       

        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
        }

        if(!$this->isLengthMinor($r->input('mdl_generico_ipt_tipo'),50)){
            $this->setError('Tamaño exedido',"El tipo de gasto es de máximo 50 cáracteres.");
            $valido = false;
        }

     
        return $valido;
    } 
}
