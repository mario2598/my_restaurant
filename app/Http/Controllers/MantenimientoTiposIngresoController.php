<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoTiposIngresoController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantTipIng";

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
             'tipos_ingreso' => $this->getTiposIngreso(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.tipos_ingreso',compact('data'));
    }

    /**
     * Guarda o actualiza un tipo de ingreso.
     */
    public function guardarTipoIngreso(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        if ($this->validarTipoIngreso($request)) {

            $tipo = $request->input('mdl_generico_ipt_tipo');
            $id = $request->input('mdl_generico_ipt_id');
            $cod_gen = $request->input('mdl_generico_ipt_codGen');
           
            try { 
                DB::beginTransaction();
                if($id == '-1' || $id == null || $this->isEmpty($id)){
                    $tipo = DB::table('tipo_ingreso')->insertGetId( ['id' => null ,'tipo'=>$tipo,'estado'=> 'A','cod_general'=> $cod_gen] );
                }else{
                    DB::table('tipo_ingreso')
                        ->where('id', '=', $id)
                        ->update(['tipo'=>$tipo,'cod_general'=> $cod_gen]);
                }
                DB::commit();
                $this->setSuccess('Guardar Tipo Ingreso','El tipo de ingreso se guardo correctamente.');
                return redirect('mant/tiposingreso');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Tipo Ingreso','Ocurrio un error guardando el tipo de ingreso.');
                return redirect('mant/tiposingreso');
            }
        
        }else{
            return redirect('mant/tiposingreso');
        }
    }

    public static function getIdByCodGeneral($codGeneral){
        return DB::table('tipo_ingreso')
        ->select('tipo_ingreso.id')
        ->where('cod_general', '=', $codGeneral)
        ->get()->first()->id;
    }

    /**
     * Elimina un tipo.
     */
    public function eliminarTipoIngreso(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        $id = $request->input('idGenericoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Tipo Ingreso','Identificador inválido.');
            return redirect('mant/tiposingreso');
        }
        try { 
            DB::beginTransaction();
            $tipo = DB::table('tipo_ingreso')->where('id','=',$id)->get()->first();
            if($tipo == null){
                $this->setError('Eliminar Tipo Ingreso','No existe el tipo de ingreso a eliminar.');
                return redirect('mant/tiposingreso');
            }else{
                DB::table('tipo_ingreso')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Tipo Ingreso','El tipo de ingreso se elimino correctamente.');
            return redirect('mant/tiposingreso');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Tipo Ingreso','Ocurrio un error eliminando el tipo de ingreso.');
            return redirect('mant/tiposingreso');
        }
        
        
    }

    public function validarTipoIngreso(Request $r){
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
            $this->setError('Tamaño exedido',"El tipo de ingreso es de máximo 50 cáracteres.");
            $valido = false;
        }

     
        return $valido;
    } 
}
