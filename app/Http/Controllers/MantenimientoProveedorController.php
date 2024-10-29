<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoProveedorController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantPro";

    public function __construct()
    {
        
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $proveedores = DB::table('proveedor')->where('estado','like','A')->get();
         $data = [
             'menus'=> $this->cargarMenus(),
             'proveedores' => $proveedores,
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.proveedores',compact('data'));
    }

    
    public static function getProvedoresActivos()
    {
        return DB::table('proveedor')->where('estado', 'like', 'A')->get();
    }
    
    /**
     * Guarda o actualiza un proveedor.
     */
    public function guardarProveedor(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        if ($this->validarProveedor($request)) {
            $descripcion = $request->input('mdl_proveedor_ipt_descripcion');
            $nombre = $request->input('mdl_proveedor_ipt_nombre');
            $id = $request->input('mdl_proveedor_ipt_id');
            try { 
                DB::beginTransaction();
                if($id == '-1' || $id == null){
                    $idProveedor = DB::table('proveedor')->insertGetId( ['id' => null ,'nombre'=>$nombre,'descripcion'=> $descripcion,'estado' => 'A'] );
                }else{
                    DB::table('proveedor')
                        ->where('id', '=', $id)
                        ->update(['nombre' => $nombre,'descripcion' => $descripcion]);
                }
                DB::commit();
                $this->setSuccess('Guardar Proveedor','El proveedor se guardo correctamente.');
                return redirect('mant/proveedores');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Proveedor','Ocurrio un error guardando el proveedor.');
                return redirect('mant/proveedores');
            }
        
        }else{
            return redirect('mant/proveedores');
        }
    }

    /**
     * Elimina un proveedor.
     */
    public function eliminarProveedor(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
      
        $id = $request->input('idProveedorEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Proveedor','Identificador inválido.');
            return redirect('mant/proveedores');
        }
        try { 
            DB::beginTransaction();
            $proveedor = DB::table('proveedor')->where('id','=',$id)->get()->first();
            if($proveedor == null){
                $this->setError('Eliminar Proveedor','No existe el proveedor a eliminar.');
                return redirect('mant/proveedores');
            }else{
                DB::table('proveedor')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Proveedor','El proveedor se elimino correctamente.');
            return redirect('mant/proveedores');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Proveedor','Ocurrio un error eliminando el proveedor.');
            return redirect('mant/proveedores');
        }
        
        
    }

    public function validarProveedor(Request $r){
        $requeridos = "";
        $valido = true;
        if($this->isNull($r->input('mdl_proveedor_ipt_nombre')) || $this->isEmpty($r->input('mdl_proveedor_ipt_nombre')) ){
            $requeridos .= "[ Nombre ";
            $valido = false;
        }
        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
        }

        if(!$this->isLengthMinor($r->input('mdl_proveedor_ipt_nombre'),50)){
            $this->setError('Tamaño exedido',"El nombre es de máximo 50 cáracteres.");
            $valido = false;
        }

        if(!$this->isLengthMinor($r->input('mdl_proveedor_ipt_descripcion'),200)){
            $this->setError('Tamaño exedido',"La descripción es de máximo 200 cáracteres.");
            $valido = false;
        }
        
        return $valido;
    } 
}
