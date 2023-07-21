<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoBancoController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantBan";
    public function __construct()
    {
        
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $bancos = DB::table('banco')->where('estado','like','A')->get();
         $data = [
             'menus'=> $this->cargarMenus(),
             'bancos' => $bancos,
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.bancos',compact('data'));
    }

    /**
     * Guarda o actualiza un Banco.
     */
    public function guardarBanco(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
      
        if ($this->validarBanco($request)) {
            $porcentaje = $request->input('mdl_generico_ipt_porcentaje');
            $nombre = $request->input('mdl_generico_ipt_nombre');
            $id = $request->input('mdl_generico_ipt_id');
            try { 
                DB::beginTransaction();
                if($id == '-1' || $id == null){
                    $banco = DB::table('banco')->insertGetId( ['id' => null ,'nombre'=>$nombre,'porcentaje'=> $porcentaje,'estado' => 'A'] );
                }else{
                    DB::table('banco')
                        ->where('id', '=', $id)
                        ->update(['nombre' => $nombre,'porcentaje' => $porcentaje]);
                }
                DB::commit();
                $this->setSuccess('Guardar Banco','El banco se guardo correctamente.');
                return redirect('mant/bancos');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Banco','Ocurrio un error guardando el banco.');
                return redirect('mant/bancos');
            }
        
        }else{
            return redirect('mant/bancos');
        }
    }

    /**
     * Elimina un Banco.
     */
    public function eliminarBanco(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $id = $request->input('idGenericoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Banco','Identificador inválido.');
            return redirect('mant/bancos');
        }
        try { 
            DB::beginTransaction();
            $banco = DB::table('banco')->where('id','=',$id)->get()->first();
            if($banco == null){
                $this->setError('Eliminar Banco','No existe el banco a eliminar.');
                return redirect('mant/bancos');
            }else{
                DB::table('banco')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Banco','El banco se elimino correctamente.');
            return redirect('mant/bancos');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Banco','Ocurrio un error eliminando el banco.');
            return redirect('mant/bancos');
        }
        
        
    }

    public function validarBanco(Request $r){
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
       
        if($this->isNull($r->input('mdl_generico_ipt_nombre')) || $this->isEmpty($r->input('mdl_generico_ipt_nombre')) ){
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('mdl_generico_ipt_porcentaje')) ){
            if(!$esPrimero){
                $requeridos .= ",";
            } 
            $requeridos .= " Porcentaje cobro ";
            $valido = false;
            $esPrimero = false;
        }
        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
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
