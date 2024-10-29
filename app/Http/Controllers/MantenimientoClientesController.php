<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoClientesController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantCli";

    public function __construct()
    {
        
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        if(!$this->usuarioAdministrador()){
            $this->setError("Seguridad","No tienes permisos para ingresar..");
            return redirect('/');
        }
        $clientes = DB::table('cliente')->where('estado','like','A')->get();
         $data = [
             'menus'=> $this->cargarMenus(),
             'clientes' => $clientes,
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.clientes',compact('data'));
    }

    /**
     * Guarda o actualiza un Cliente.
     */
    public function guardarCliente(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        if(!$this->usuarioAdministrador()){
            $this->setError("Seguridad","No tienes permisos para ingresar..");
            return redirect('/');
        }
        if ($this->validarCliente($request)) {
            $correo = $request->input('mdl_generico_ipt_correo');
            $nombre = $request->input('mdl_generico_ipt_nombre');
            $tel = $request->input('mdl_generico_ipt_tel');
            $ubicacion = $request->input('mdl_generico_ipt_ubicacion');
            $id = $request->input('mdl_generico_ipt_id');
            try { 
                DB::beginTransaction();
                if($id == '-1' || $id == null || $this->isEmpty($id)){
                    $banco = DB::table('cliente')->insertGetId( ['id' => null ,'nombre'=>$nombre,'correo'=> $correo,'ubicacion'=>$ubicacion,'telefono'=> $tel,'estado' => 'A'] );
                }else{
                    DB::table('cliente')
                        ->where('id', '=', $id)
                        ->update(['nombre'=>$nombre,'correo'=> $correo,'ubicacion'=>$ubicacion,'telefono'=> $tel]);
                }
                DB::commit();
                $this->setSuccess('Guardar Cliente','El cliente se guardo correctamente.');
                return redirect('mant/clientes');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Cliente','Ocurrio un error guardando el cliente.');
                return redirect('mant/clientes');
            }
        
        }else{
            return redirect('mant/clientes');
        }
    }

    /**
     * Elimina un Cliente.
     */
    public function eliminarCliente(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        if(!$this->usuarioAdministrador()){
            $this->setError("Seguridad","No tienes permisos para ingresar..");
            return redirect('/');
        }
        $id = $request->input('idGenericoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Cliente','Identificador inválido.');
            return redirect('mant/clientes');
        }
        try { 
            DB::beginTransaction();
            $cliente = DB::table('cliente')->where('id','=',$id)->get()->first();
            if($cliente == null){
                $this->setError('Eliminar Cliente','No existe el cliente a eliminar.');
                return redirect('mant/clientes');
            }else{
                DB::table('cliente')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Cliente','El cliente se elimino correctamente.');
            return redirect('mant/clientes');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Cliente','Ocurrio un error eliminando el cliente.');
            return redirect('mant/clientes');
        }
        
        
    }

    public function validarCliente(Request $r){
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
       
        if($this->isNull($r->input('mdl_generico_ipt_nombre')) || $this->isEmpty($r->input('mdl_generico_ipt_nombre')) ){
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        /*if($this->isNull($r->input('mdl_generico_ipt_tel')) || $this->isEmpty($r->input('mdl_generico_ipt_tel'))){
            if(!$esPrimero){
                $requeridos .= ",";
            } 
            $requeridos .= "Teléfono ";
            $valido = false;
            $esPrimero = false;
        }*/

        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
        }

        if(!$this->isLengthMinor($r->input('mdl_generico_ipt_nombre'),50)){
            $this->setError('Tamaño exedido',"El nombre del cliente es de máximo 50 cáracteres.");
            $valido = false;
        }

        if(!$this->isLengthMinor($r->input('mdl_generico_ipt_tel'),14)){
            $this->setError('Tamaño exedido',"El teléfono es de máximo 14 cáracteres.");
            $valido = false;
        }
        if(!$this->isNull($r->input('mdl_generico_ipt_correo')) && !$this->isLengthMinor($r->input('mdl_generico_ipt_correo'),100)){
            $this->setError('Tamaño exedido',"El correo es de máximo 100 cáracteres.");
            $valido = false;
        }
        if(!$this->isNull($r->input('mdl_generico_ipt_ubicacion')) && !$this->isLengthMinor($r->input('mdl_generico_ipt_ubicacion'),300)){
            $this->setError('Tamaño exedido',"La ubicación es de máximo 300 cáracteres.");
            $valido = false;
        }
        
        
        return $valido;
    } 
}
