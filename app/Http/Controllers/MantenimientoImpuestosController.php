<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoImpuestosController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantImp";

    public function __construct()
    {
        
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
      
         $data = [
             'menus'=> $this->cargarMenus(),
             'impuestos' => $this->getImpuestos(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.impuestos',compact('data'));
    }

    /**
     * Guarda o actualiza un impuestos.
     */
    public function guardarImpuesto(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
     
        if ($this->validarImpuesto($request)) {
            $porcentaje = $request->input('mdl_generico_ipt_porcentaje');
            $desc = $request->input('mdl_generico_ipt_descripcion');
            $id = $request->input('mdl_generico_ipt_id');
            try { 
                DB::beginTransaction();
                if($id == '-1' || $id == null){
                    $impuesto = DB::table('impuesto')->insertGetId( ['id' => null ,'descripcion'=>$desc,'impuesto'=> $porcentaje,'estado' => 'A'] );
                }else{
                    DB::table('impuesto')
                        ->where('id', '=', $id)
                        ->update(['descripcion'=>$desc,'impuesto'=> $porcentaje]);
                }
                DB::commit();
                $this->setSuccess('Guardar Impuesto','El impuesto se guardo correctamente.');
                return redirect('mant/impuestos');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Impuesto','Ocurrio un error guardando el impuesto.');
                return redirect('mant/impuestos');
            }
        
        }else{
            return redirect('mant/impuestos');
        }
    }

    /**
     * Elimina un Impuesto.
     */
    public function eliminarImpuesto(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $id = $request->input('idGenericoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Impuesto','Identificador inválido.');
            return redirect('mant/impuestos');
        }
        try { 
            DB::beginTransaction();
            $banco = DB::table('impuesto')->where('id','=',$id)->get()->first();
            if($banco == null){
                $this->setError('Eliminar Impuesto','No existe el impuesto a eliminar.');
                return redirect('mant/impuestos');
            }else{
                DB::table('impuesto')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Impuesto','El impuesto se elimino correctamente.');
            return redirect('mant/impuestos');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Impuesto','Ocurrio un error eliminando el impuesto.');
            return redirect('mant/impuestos');
        }
        
        
    }

    public function validarImpuesto(Request $r){
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
       
        if($this->isNull($r->input('mdl_generico_ipt_descripcion')) || $this->isEmpty($r->input('mdl_generico_ipt_descripcion')) ){
            $requeridos .= " Descripción ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('mdl_generico_ipt_porcentaje')) ){
            if(!$esPrimero){
                $requeridos .= ",";
            } 
            $requeridos .= " Porcentaje impuesto ";
            $valido = false;
            $esPrimero = false;
        }
        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
        }

        if(!$this->isLengthMinor($r->input('mdl_generico_ipt_descripcion'),50)){
            $this->setError('Tamaño exedido',"La descrición es de máximo 50 cáracteres.");
            $valido = false;
        }

        if(!$this->isNumber($r->input('mdl_generico_ipt_porcentaje'))){
            $this->setError('Fomato inválido',"El porcentaje debe ser un número.");
            $valido = false;
        }

        if($r->input('mdl_generico_ipt_porcentaje') > 99 || $r->input('mdl_generico_ipt_porcentaje') < 0 ){
            $this->setError('Valor incorrecto',"El porcentaje debe ser entre 0 y 99 %.");
            $valido = false;
        }
        
        return $valido;
    } 
}
