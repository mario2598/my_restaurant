<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class MantenimientoUsuariosController extends Controller
{
    use SpaceUtil;
    protected $SpaceSeg;
    public $codigo_pantalla = "mantUsu";

    public function __construct()
    {


    }
    public function index(){
        
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        

        $usuarios = DB::table('usuario')
        ->join('rol', 'rol.id', '=', 'usuario.rol')
        ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
        ->select('usuario.id','usuario.nombre','usuario.ape1','usuario.ape2',
        'usuario.correo','usuario.telefono','usuario.usuario','usuario.rol',
        'rol.rol as rol_nombre','rol.id as rol_id',
        'sucursal.descripcion as sucursal_nombre','sucursal.id as sucursal_id')
        ->where('usuario.estado','like','A')->get();
         $data = [
             'menus'=> $this->cargarMenus(),
             'usuarios' => $usuarios,
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.usuarios',compact('data'));
    }

    public function goNuevoUsuario(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        $roles = DB::table('rol')->where('estado','like','A')->where('codigo','<>','su')->get();
        $sucursales = DB::table('sucursal')->where('estado','like','A')->get();
        $datos = [];
         $data = [
             'menus'=> $this->cargarMenus(),
             'datos' => $datos,
             'roles' => $roles,
             'sucursales' => $this->getSucursalesAndBodegas(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('usuario.nuevoUsuario',compact('data'));
    }

    public function returnNuevoUsuarioWithData($datos){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        $roles = DB::table('rol')->where('estado','like','A')->where('codigo','<>','su')->get();

         $data = [
             'menus'=> $this->cargarMenus(),
             'datos' => $datos,
             'roles' => $roles,
             'sucursales' => $this->getSucursalesAndBodegas(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('usuario.nuevoUsuario',compact('data'));
    }

    public function goEditarUsuario(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        
        $id = $request->input('idUsuarioEditar');
        $usuario = DB::table('usuario')
        ->join('rol', 'rol.id', '=', 'usuario.rol')
        ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
        ->select('usuario.*',
        'rol.id as rol_id',
        'sucursal.id as sucursal_id')
        ->where('usuario.id','=',$id)->get()->first();
        if($usuario == null){
            $this->setError('Editar Usuario','No existe el usuario a editar.');
            return redirect('mant/usuarios');
        }

        $roles = DB::table('rol')->where('estado','like','A')->where('codigo','<>','su')->get();
        
       
         $data = [
             'menus'=> $this->cargarMenus(),
             'roles' => $roles,
             'usuario' => $usuario,
             'sucursales' => $this->getSucursalesAndBodegas(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('usuario.editarUsuario',compact('data'));
    }

    public function returnEditarUsuarioWithId($id){
        
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        if($id < 1 || $this->isEmpty($id)){
            $this->setError("Error","El usuario no existe..");
            return redirect('/');
        }
      
        $usuario = DB::table('usuario')
        ->leftjoin('rol', 'rol.id', '=', 'usuario.rol')
        ->leftjoin('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
        ->select('usuario.*',
        'rol.id as rol_id',
        'sucursal.id as sucursal_id')
        ->where('usuario.id','=',$id)->get()->first();
       
        if($usuario == null){
            $this->setError("Error","El usuario no existe..");
            return redirect('/');
        }
       
        $roles = DB::table('rol')->where('estado','like','A')->where('codigo','<>','su')->get();
       
         $data = [
             'menus'=> $this->cargarMenus(),
             'roles' => $roles,
             'usuario' => $usuario,
             'sucursales' => $this->getSucursales(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('usuario.editarUsuario',compact('data'));
    }

    /**
     * Actualiza la contraseña de el usuario.
     * @param nueva_contra , idUsuarioEditar
     */
    public function cambiarContra(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        

        $id = $request->input('idUsuarioEditar');
        $nueva_contra = $request->input('nueva_contra');

        if($this->isNull($nueva_contra) || $this->isEmpty($nueva_contra) ){
            $this->setError('Cambiar contraseña','La contraseña debe ser minimo 4 caracteres.');
            return $this->returnEditarUsuarioWithId($id);
        }
        if(!$this->isLengthMinor($nueva_contra,25)){
            $this->setError('Cambiar contraseña',"La contraseña debe ser de máximo 25 caracteres.");
            return $this->returnEditarUsuarioWithId($id);
        }

        if(!$this->isLengthMayor($nueva_contra,4)){
            $this->setError('Cambiar contraseña',"La contraseña debe ser minimo 4 caracteres.");
            return $this->returnEditarUsuarioWithId($id);
        }

        $usuario = DB::table('usuario')->select('usuario.id')->where('id','=',$id)->get()->first();
        if($usuario == null ){
            $this->setError('Cambiar contraseña','No existe un usuario con los credenciales.');
            return $this->returnEditarUsuarioWithId($id);
        }

        try { 
            $nueva_contra = trim($nueva_contra);
            
            DB::beginTransaction();

            DB::table('usuario')
            ->where('id', '=', $id)
            ->update(['contra' => md5($nueva_contra)]);
            
            DB::commit();
            $this->setSuccess('Cambiar contraseña','Se actualizo la contraseña correctamente.');
            return $this->returnEditarUsuarioWithId($id);
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Cambiar contraseña','Ocurrio un error cambiando la contraseña.');
            return $this->returnEditarUsuarioWithId($id);
        }
        
        
    }

    /**
     * Guarda o actualiza un Usuario
     */
    public function guardarUsuario(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
       // dd($request->all());
        $id = $request->input('id');
        $nombreUsuario = $request->input('usuario');
        $usuario = DB::table('usuario')->select('usuario.*')->where('id','=',$id)->get()->first();
        
        if($id < 1 || $this->isNull($id)){ // Nuevo usuario
            if($this->usuarioRegistrado($nombreUsuario)){
                $this->setError('Guardar Usuario','El nombre de usuario ya esta en uso.');
                return $this->returnNuevoUsuarioWithData($request->all());
            }
            $actualizar = false;
        }else{// Editar usuario
            
            if($usuario == null ){
                $this->setError('Guardar Usuario','No existe un usuario con los credenciales.');
                return $this->returnEditarUsuarioWithId($id);
            }
            if($usuario->usuario != $nombreUsuario){
                if($this->usuarioRegistrado($nombreUsuario)){
                    $this->setError('Guardar Usuario','El nombre de usuario ya esta en uso.');
                    return $this->returnEditarUsuarioWithId($id);
                }
            }
            $actualizar = true;
        }

       
        if($this->validarUsuario($request)) {
          
            $cedula = $request->input('cedula');
            if($actualizar){ // Editar usuario
                if($cedula != $usuario->cedula){
                    if($this->cedulaRegistrada($cedula)){
                        $this->setError('Guardar Usuario','Ya existe un usuario con el número de cédula.');
                        return $this->returnEditarUsuarioWithId($id);
                    }
                }
            }else{// Nuevo usuario
                if($this->cedulaRegistrada($cedula)){
                    $this->setError('Guardar Usuario','Ya existe un usuario con el número de cédula.');
                    return $this->returnNuevoUsuarioWithData($request->all());
                }
            }

            $correo = $request->input('correo');
            $nombre = $request->input('nombre');
            $ape1 = $request->input('ape1');
            $ape2 = $request->input('ape2');
            $telefono = $request->input('telefono');
            $contra = $request->input('contra');
            $nacimiento = $request->input('nacimiento');
            $sucursal = $request->input('sucursal');
            $rol = $request->input('rol');
            $fecha_actual = date("Y-m-d H:i:s");
            try { 
                DB::beginTransaction();
                
                if($actualizar){// Editar usuario
                    DB::table('usuario')
                        ->where('id', '=', $id)
                        ->update(['nombre' => $nombre,'ape1'=> $ape1,'ape2'=> $ape2,
                        'cedula'=> $cedula,'fecha_nacimiento' => $nacimiento,
                        'correo'=> $correo,'telefono' => $telefono,'usuario'=> $nombreUsuario,
                        'sucursal' => $sucursal,'rol'=> $rol
                        ]);
                }else{// Nuevo usuario
                    $id = DB::table('usuario')->insertGetId( ['id' => null ,'nombre'=>$nombre,'ape1'=> $ape1,'ape2'=> $ape2,
                        'cedula'=> $cedula,'fecha_nacimiento' => $nacimiento,'fecha_ingreso'=> $fecha_actual,
                        'correo'=> $correo,'telefono' => $telefono,'usuario'=> $nombreUsuario,
                        'contra'=> md5($contra),'sucursal' => $sucursal,'rol'=> $rol,'estado'=> 'A'
                        ] );
                }
                 DB::table('panel_configuraciones')->insertGetId( ['id' => null ,'color_fondo' => 1,'color_sidebar' => 1,'color_tema' => "white",
                'mini_sidebar' => 1,'sticky_topbar' => 1,'usuario'=>$id]);
               
                DB::commit();
                
                if($actualizar){// Editar usuario
                    $this->setSuccess('Guardar Usuario','Se actualizo el usuario correctamente.');
                }else{// Nuevo usuario
                    
                    $this->setSuccess('Guardar Usuario','Usuario creado correctamente.');
                }
                return $this->returnEditarUsuarioWithId($id);
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Usuario', $ex);
                $this->index();
            }    
          
        }else{
            if($actualizar){
                return $this->returnEditarUsuarioWithId($id);
            }else{
                return $this->returnNuevoUsuarioWithData($request->all());
            }
        }

    }

    /**
     * Valida si el nombre de usuario ya esta registrado
     * @param $usuario nombre del usuario
     * @return boolean si esta registrado true si no false
     */
    public function usuarioRegistrado($usuario){
        $usuario = DB::table('usuario')->select('usuario.id')->where('usuario','=',$usuario)->get()->first();
    
        return ($usuario == null) ? false : true;
      }
    
    /**
     * Valida si la cedula de usuario ya esta registrada
     * @param $cedula cedula del usuario
     * @return boolean si esta registrada true si no false
     */
    public function cedulaRegistrada($cedula){
        $usuario = DB::table('usuario')->select('usuario.id')->where('cedula','=',$cedula)->get()->first();

        return ($usuario == null) ? false : true;
    }

    /**
     * Inactiva un usuario.
     */
    public function eliminarUsuario(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        $id = $request->input('idGenericoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Usuario','Identificador inválido.');
            return redirect('mant/usuarios');
        }
        try { 
            DB::beginTransaction();
            $usuario = DB::table('usuario')->where('id','=',$id)->get()->first();
            if($usuario == null){
                $this->setError('Eliminar Usuario','No existe el usuario a eliminar.');
                return redirect('mant/usuarios');
            }else{
                DB::table('usuario')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Usuario','El usuario se elimino correctamente.');
            return redirect('mant/usuarios');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Usuario','Ocurrio un error eliminando el usuario.');
            return redirect('mant/usuarios');
        }
        
        
    }

    public function restaurarPc(){
      
        $usuario = $this->getUsuarioAuth();
        try { 
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_fondo' => 1,'color_sidebar' => 1,'color_tema' => "white",
                'mini_sidebar' => 1,'sticky_topbar' => 1,]);
            DB::commit();
            echo 1;
        }catch(QueryException $ex){ 
            DB::rollBack();
            echo 0;
        }
    }

    public function temaClaro(){
       
        try { 
            $usuario = $this->getUsuarioAuth();
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_fondo' => 1,'color_sidebar' => 1,'color_tema' => "white"]);
            DB::commit();
            echo 1;
        }catch(QueryException $ex){ 
            DB::rollBack();
            echo 0;
        }
    }


    public function temaOscuro(){

        try { 
            $usuario = $this->getUsuarioAuth();
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_fondo' => 2,'color_sidebar' => 2,'color_tema' => "black" ]);
            DB::commit();
            echo 1;
        }catch(QueryException $ex){ 
            DB::rollBack();
            echo 0;
        }
    }

    public function sideTeme(Request $request){
       
        try { 
            $usuario = $this->getUsuarioAuth();
            $tema = $request->input('tema');
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_sidebar' => $tema]);
            DB::commit();
            echo 1;
        }catch(QueryException $ex){ 
            DB::rollBack();
            echo 0;
        }
    }

    public function colorTeme(Request $request){
        
        try { 
            $usuario = $this->getUsuarioAuth();
            $color = $request->input('color');
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_tema' => $color]);
            DB::commit();
            echo 1;
        }catch(QueryException $ex){ 
            DB::rollBack();
            echo 0;
        }
    }

    public function sticky(Request $request){
       
        try { 
            $usuario = $this->getUsuarioAuth();
            $sticky = $request->input('sticky');
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['sticky_topbar' => $sticky]);
            DB::commit();
            echo 1;
        }catch(QueryException $ex){ 
            DB::rollBack();
            echo 0;
        }
    }


    public function validarUsuario(Request $r){
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
       
        if($this->isNull($r->input('nombre')) || $this->isEmpty($r->input('nombre')) ){
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('ape1')) || $this->isEmpty($r->input('ape1')) ){
            $requeridos .= " Primer Apellido ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('cedula')) || $this->isEmpty($r->input('cedula')) ){
            $requeridos .= " Cédula ";
            $valido = false;
            $esPrimero = false;
        }

        if($this->isNull($r->input('usuario')) || $this->isEmpty($r->input('usuario')) ){
            $requeridos .= " Usuario ";
            $valido = false;
            $esPrimero = false;
        }
        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
        }

        if(!$this->isLengthMinor($r->input('nombre'),25)){
            $this->setError('Tamaño exedido',"El nombre del usuario debe ser de máximo 25 caracteres.");
            return false;
        }
        if(!$this->isLengthMinor($r->input('ape1'),25)){
            $this->setError('Tamaño exedido',"El primer apellido del usuario debe ser de máximo 25 caracteres.");
            return false;
        }
        if(!$this->isLengthMinor($r->input('ape2'),25)){
            $this->setError('Tamaño exedido',"El primer apellido del usuario debe ser de máximo 25 caracteres.");
            return false;
        }
        if(!$this->isLengthMinor($r->input('cedula'),15)){
            $this->setError('Tamaño exedido',"La cédula del usuario debe ser de máximo 15 caracteres.");
            return false;
        }
        if(!$this->isLengthMinor($r->input('telefono'),8)){
            $this->setError('Tamaño exedido',"El teléfono del usuario debe ser de máximo 8 caracteres.");
            return false;
        }
        if(!$this->isLengthMinor($r->input('usuario'),25)){
            $this->setError('Tamaño exedido',"El usuario debe ser de máximo 25 caracteres.");
            return false;
        }
        if(!$this->isLengthMinor($r->input('correo'),100)){
            $this->setError('Tamaño exedido',"El correo debe ser de máximo 100 caracteres.");
            return false;
        }
        
        return $valido;
    } 
}
