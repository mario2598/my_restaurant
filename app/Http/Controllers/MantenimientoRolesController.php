<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoRolesController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantRol";

    public function __construct()
    {
        
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $roles = DB::table('rol')
                    ->select('rol.*')
                    ->where('rol.estado','like','A')->get();
                    
         $data = [
             'menus'=> $this->cargarMenus(),
             'vistas'=> $this->cargarVistas(),
             'roles' => $roles,
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.roles',compact('data'));
    }

    public static function getRolesActivos()
    {
        return DB::table('rol')->where('estado', 'like', 'A')->get();
    }

    public static function getIdByCodigo($codGeneral)
    {
        $rol = DB::table('rol')
            ->select('rol.id')
            ->where('codigo', '=', $codGeneral)
            ->get()->first();
        
        return $rol != null ? $rol->id  : null;
    }

    public function crearMenus($vistas,$rol){
        
        $headers = [];
        foreach($vistas as $v){
            $codigo_grupo = DB::table('vista')
            ->select('codigo_grupo')
            ->where('id', '=',  $v)
            ->get()->first()->codigo_grupo;
            if(!in_array($codigo_grupo, $headers)){
                array_push($headers,$codigo_grupo);
            }
            DB::table('menu')->insertGetId( ['id' => null ,'rol'=>$rol,'vista'=>$v] );
        }
        foreach($headers as $h){
            $id = DB::table('vista')
            ->select('id')
            ->where('codigo_grupo', '=',  $h)
            ->get()->first()->id;
            DB::table('menu')->insertGetId( ['id' => null ,'rol'=>$rol,'vista'=>$id] );
        }
    }

    public function eliminarMenus($rol){
        DB::table('menu')
                ->where('rol','=', $rol)
                ->delete();
    }

    public function cargarPermisosRoles(Request $request){
        $rol = $request->input('idRol');

        
        $permisos = DB::table('menu')
        ->leftJoin('vista','vista.id','=','menu.vista')
        ->where('menu.rol', '=', $rol)
        ->where('vista.tipo', '=', 'M')
        ->select('menu.vista')
        ->get();

        $data = [
            'vistas'=> $this->cargarVistas(),
            'permisos'=> $permisos
        ];
       return view('mant.layout.permisosRoles',compact('data'));
        

    }

    /**
     * Guarda o actualiza un rol.
     */
    public function guardarRol(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
     
        if ($this->validarRol($request)) {
            $rol = $request->input('mdl_generico_ipt_rol');
            $codigo = $request->input('mdl_generico_ipt_codigo');
          
            $id = $request->input('mdl_generico_ipt_id');
            $vistas = $request->input('menus');
            
            try { 
                DB::beginTransaction();
                if($id == '-1' || $id == null){
                    $rol = DB::table('rol')->insertGetId( ['id' => null ,'rol'=>$rol,
                    'codigo'=> $codigo,'estado' => 'A'] );
                    $this->crearMenus($vistas,$rol);
                }else{
                    DB::table('rol')
                        ->where('id', '=', $id)
                        ->update(['rol' => $rol,'codigo' => $codigo]);
                        $this->eliminarMenus($id);
                        $this->crearMenus($vistas,$id);
                }
                DB::commit();
                $this->setSuccess('Guardar Rol','El rol se guardo correctamente.');
                return redirect('mant/roles');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Rol','Ocurrio un error guardando el rol.');
                return redirect('mant/roles');
            }
        
        }else{
            return redirect('mant/roles');
        }
    }

    /**
     * Elimina un rol.
     */
    public function eliminarRol(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
      
        $id = $request->input('idGenericoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Rol','Identificador inválido.');
            return redirect('mant/roles');
        }
        try { 
            DB::beginTransaction();
            $rol = DB::table('rol')->where('id','=',$id)->get()->first();
            if($rol == null){
                $this->setError('Eliminar Rol','No existe el rol a eliminar.');
                return redirect('mant/roles');
            }else{
                DB::table('rol')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Rol','El rol se elimino correctamente.');
            return redirect('mant/roles');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Rol','Ocurrio un error eliminando el rol.');
            return redirect('mant/roles');
        }
        
        
    }

    public function validarRol(Request $r){
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
       
        if($this->isNull($r->input('mdl_generico_ipt_codigo')) || $this->isEmpty($r->input('mdl_generico_ipt_codigo')) ){
            $requeridos .= " Código ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('mdl_generico_ipt_rol')) || $this->isEmpty($r->input('mdl_generico_ipt_rol'))){
            if(!$esPrimero){
                $requeridos .= ",";
            } 
            $requeridos .= "Rol ";
            $valido = false;
            $esPrimero = false;
        }
        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
        }

        if(!$this->isLengthMinor($r->input('mdl_generico_ipt_rol'),50)){
            $this->setError('Tamaño exedido',"El nombre del rol es de máximo 50 cáracteres.");
            $valido = false;
        }

        if(!$this->isLengthMinor($r->input('mdl_generico_ipt_codigo'),15)){
            $this->setError('Tamaño exedido',"El código del rol es de máximo 15 cáracteres.");
            $valido = false;
        }
        
        return $valido;
    } 
}
