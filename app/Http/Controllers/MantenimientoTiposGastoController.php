<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoTiposGastoController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantTipGast";

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
             'tipos_gasto' => $this->getTiposGasto(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.tipos_gasto',compact('data'));
    }

    /**
     * Guarda o actualiza un tipo de gasto.
     */
    public function guardarTipoGasto(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        if ($this->validarTipoGasto($request)) {

            $tipo = $request->input('mdl_generico_ipt_tipo');
            $id = $request->input('mdl_generico_ipt_id');
           
            try { 
                DB::beginTransaction();
                if($id == '-1' || $id == null || $this->isEmpty($id)){
                    $tipo = DB::table('tipo_gasto')->insertGetId( ['id' => null ,'tipo'=>$tipo,'estado'=> 'A'] );
                }else{
                    DB::table('tipo_gasto')
                        ->where('id', '=', $id)
                        ->update(['tipo'=>$tipo]);
                }
                DB::commit();
                $this->setSuccess('Guardar Tipo Gasto','El tipo de gasto se guardo correctamente.');
                return redirect('mant/tiposgasto');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Tipo Gasto','Ocurrio un error guardando el tipo de gasto.');
                return redirect('mant/tiposgasto');
            }
        
        }else{
            return redirect('mant/tiposgasto');
        }
    }

    /**
     * Elimina un tipo.
     */
    public function eliminarTipoGasto(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        $id = $request->input('idGenericoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Tipo Gasto','Identificador inválido.');
            return redirect('mant/tiposgasto');
        }
        try { 
            DB::beginTransaction();
            $tipo = DB::table('tipo_gasto')->where('id','=',$id)->get()->first();
            if($tipo == null){
                $this->setError('Eliminar Tipo Gasto','No existe el tipo de gasto a eliminar.');
                return redirect('mant/tiposgasto');
            }else{
                DB::table('tipo_gasto')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Tipo Gasto','El tipo de gasto se elimino correctamente.');
            return redirect('mant/tiposgasto');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Tipo Gasto','Ocurrio un error eliminando el cliente.');
            return redirect('mant/tiposgasto');
        }
        
        
    }

    public function validarTipoGasto(Request $r){
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
       
        if($this->isNull($r->input('mdl_generico_ipt_tipo')) || $this->isEmpty($r->input('mdl_generico_ipt_tipo')) ){
            $requeridos .= " Tipo Gasto ";
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
