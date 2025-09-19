<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MantenimientoUsuariosController extends Controller
{

    use SpaceUtil;
    protected $SpaceSeg;
    public $codigo_pantalla = "mantUsu";

    public function __construct() {}
    public function index()
    {

        $usuarios = DB::table('usuario')
            ->join('rol', 'rol.id', '=', 'usuario.rol')
            ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
            ->join('sis_estado', 'sis_estado.id', '=', 'usuario.estado')
            ->select(
                'usuario.id',
                'usuario.nombre',
                'usuario.ape1',
                'usuario.ape2',
                'usuario.correo',
                'usuario.telefono',
                'usuario.usuario',
                'usuario.rol',
                'usuario.estado',
                'rol.rol as rol_nombre',
                'rol.id as rol_id',
                'sucursal.descripcion as sucursal_nombre',
                'sucursal.id as sucursal_id',
                'sis_estado.nombre as estado_nombre',
                'sis_estado.cod_general as estado_codigo'
            )
            ->get();
        $data = [
            'menus' => $this->cargarMenus(),
            'usuarios' => $usuarios,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('mant.usuarios', compact('data'));
    }

    /**
     * Actualiza la contraseña de el usuario.
     * @param nueva_contra , idUsuarioEditar
     */
    public function cambiarContra(Request $request)
    {
        $id = $request->input('idUsuarioEditar');
        $nueva_contra = $request->input('nueva_contra');

        if ($this->isNull($nueva_contra) || $this->isEmpty($nueva_contra)) {
            return $this->responseAjaxServerError("La contraseña debe ser máximo 25 caracteres.", []);
        }
        if (!$this->isLengthMinor($nueva_contra, 25)) {
            return $this->responseAjaxServerError("La contraseña debe ser máximo 25 caracteres.", []);
        }

        if (!$this->isLengthMayor($nueva_contra, 4)) {
            return $this->responseAjaxServerError("La contraseña debe ser máximo 25 caracteres.", []);
        }

        $usuario = DB::table('usuario')->select('usuario.id')->where('id', '=', $id)->get()->first();
        if ($usuario == null) {
            return $this->responseAjaxServerError("No existe un usuario con los credenciales.", []);
        }

        try {
            $nueva_contra = trim($nueva_contra);

            DB::beginTransaction();

            DB::table('usuario')
                ->where('id', '=', $id)
                ->update(['contra' => md5($nueva_contra)]);

            DB::commit();
            return $this->responseAjaxSuccess("Se actualizo la contraseña correctamente", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'MantenimientoSorteosController', 'descripcion' => $ex]);
            return $this->responseAjaxServerError("Ocurrió un error cambiando la contraseña", []);
        }
    }

    /**
     * Valida si el nombre de usuario ya esta registrado
     * @param $usuario nombre del usuario
     * @return boolean si esta registrado true si no false
     */
    public function usuarioRegistrado($usuario)
    {
        $usuario = DB::table('usuario')->select('usuario.id')->where('usuario', '=', $usuario)->get()->first();

        return ($usuario == null) ? false : true;
    }

    /**
     * Valida si la cedula de usuario ya esta registrada
     * @param $cedula cedula del usuario
     * @return boolean si esta registrada true si no false
     */
    public function cedulaRegistrada($cedula)
    {
        $usuario = DB::table('usuario')->select('usuario.id')->where('cedula', '=', $cedula)->get()->first();

        return ($usuario == null) ? false : true;
    }

    public function validarUsuario($usuario)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

        if ($this->isNull($usuario['nombre']) || $this->isEmpty($usuario['nombre'])) {
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($usuario['ape1']) || $this->isEmpty($usuario['ape1'])) {
            $requeridos .= " Primer Apellido ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($usuario['cedula']) || $this->isEmpty($usuario['cedula'])) {
            $requeridos .= " Cédula ";
            $valido = false;
            $esPrimero = false;
        }

        if ($this->isNull($usuario['usuario']) || $this->isEmpty($usuario['usuario'])) {
            $requeridos .= " Usuario ";
            $valido = false;
            $esPrimero = false;
        }
        $requeridos .= "] ";
        if (!$valido) {
            return 'Campos Requeridos : ' . $requeridos;
        }

        if (!$this->isLengthMinor($usuario['nombre'], 25)) {
            return "Tamaño exedido" . "El nombre del usuario debe ser de máximo 25 caracteres.";
        }
        if (!$this->isLengthMinor($usuario['ape1'], 25)) {
            return "Tamaño exedido" . "El primer apellido del usuario debe ser de máximo 25 caracteres.";
        }
        if (!$this->isLengthMinor($usuario['ape2'], 25)) {
            return "Tamaño exedido" . "El primer apellido del usuario debe ser de máximo 25 caracteres.";
        }
        if (!$this->isLengthMinor($usuario['cedula'], 15)) {
            return "Tamaño exedido" . "La cédula del usuario debe ser de máximo 15 caracteres.";
        }
        if (!$this->isLengthMinor($usuario['cedula'], 15)) {
            return "Tamaño exedido" . "El teléfono del usuario debe ser de máximo 15 caracteres.";
        }
        if (!$this->isLengthMinor($usuario['usuario'], 25)) {
            return "Tamaño exedido" . "El usuario debe ser de máximo 25 caracteres.";
        }
        if (!$this->isLengthMinor($usuario['correo'], 100)) {
            return "Tamaño exedido" . "El correo debe ser de máximo 100 caracteres.";
        }

        return null;
    }

    public static function getUsuarioById($id)
    {
        return DB::table('usuario')
            ->join('rol', 'rol.id', '=', 'usuario.rol')
            ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
            ->select(
                'usuario.*',
                'rol.id as rol_id',
                'sucursal.id as sucursal_id'
            )
            ->where('usuario.id', '=', $id)->get()->first();
    }

    public static function getUsuarioByUsuario($usuario)
    {
        return DB::table('usuario')
            ->join('rol', 'rol.id', '=', 'usuario.rol')
            ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
            ->select(
                'usuario.*',
                'rol.id as rol_id',
                'sucursal.id as sucursal_id'
            )
            ->where('usuario.usuario', '=', $usuario)->get()->first();
    }

    public static function getUsuarioByCorreo($correo)
    {
        return DB::table('usuario')
            ->join('rol', 'rol.id', '=', 'usuario.rol')
            ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
            ->select(
                'usuario.*',
                'rol.id as rol_id',
                'sucursal.id as sucursal_id'
            )
            ->where('usuario.correo', '=', $correo)->get()->first();
    }

    public function cargarUsuariosAjax()
    {
        try {
            return $this->responseAjaxSuccess("", MantenimientoUsuariosController::getUsuarios());
        } catch (QueryException $ex) {
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'MantenimientoUsuariosController', 'descripcion' => $ex]);
            return $this->responseAjaxServerError("Error cargando los usuarios", []);
        }
    }

    public function cargarUsuarioAjax(Request $request)
    {
        try {
            $id = $request->input('idUsuario');
            if ($id < 1) {
                return $this->responseAjaxSuccess("", []);
            } else {
                $usuario = MantenimientoUsuariosController::getUsuarioById($id);

                if ($usuario == null) {
                    return $this->responseAjaxServerError("No se encontro  el usuario", []);
                }
                return $this->responseAjaxSuccess("", $usuario);
            }
        } catch (QueryException $ex) {
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'MantenimientoUsuariosController', 'descripcion' => $ex]);
            return $this->responseAjaxServerError("Error cargando el usuario", []);
        }
    }

    public static function getUsuarios()
    {
        return DB::table('usuario')
            ->join('rol', 'rol.id', '=', 'usuario.rol')
            ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
            ->join('sis_estado', 'sis_estado.id', '=', 'usuario.estado')
            ->select(
                'usuario.id',
                'usuario.nombre',
                'usuario.ape1',
                'usuario.ape2',
                'usuario.correo',
                'usuario.cedula',
                'usuario.telefono',
                'usuario.usuario',
                'usuario.rol',
                'usuario.estado',
                'rol.rol as rol_nombre',
                'rol.id as rol_id',
                'sucursal.descripcion as sucursal_nombre',
                'sucursal.id as sucursal_id',
                'sis_estado.nombre as estado_nombre',
                'sis_estado.cod_general as estado_codigo'
            )
            ->get();
    }

    public function guardarUsuarioAjax(Request $request)
    {

        $usuarioR = $request->input('usuario');
        $id = $usuarioR['id'];
        $nombreUsuario = $usuarioR['usuario'] ?? "";
        $usuario = DB::table('usuario')->select('usuario.*')->where('id', '=', $id)->get()->first();

        if ($id < 1 || $this->isNull($id)) { // Nuevo usuario
            if ($this->usuarioRegistrado($nombreUsuario)) {
                return $this->responseAjaxServerError("El nombre de usuario ya esta en uso.", []);
            }
            $actualizar = false;
        } else { // Editar usuario

            if ($usuario == null) {
                return $this->responseAjaxServerError('No existe un usuario con los credenciales.', []);
            }
            if ($usuario->usuario != $nombreUsuario) {
                if ($this->usuarioRegistrado($nombreUsuario)) {
                    return $this->responseAjaxServerError("El nombre de usuario ya esta en uso.", []);
                }
            }
            $actualizar = true;
        }

        $mensajeValidacion = $this->validarUsuario($usuarioR);
        if ($mensajeValidacion == null) {

            $cedula = $usuarioR['cedula'];
            if ($actualizar) { // Editar usuario
                if ($cedula != $usuario->cedula) {
                    if ($this->cedulaRegistrada($cedula)) {
                        return $this->responseAjaxServerError("Ya existe un usuario con el número de cédula.", []);
                    }
                }
            } else { // Nuevo usuario
                if ($this->cedulaRegistrada($cedula)) {
                    return $this->responseAjaxServerError("Ya existe un usuario con el número de cédula.", []);
                }
            }

            $correo = $usuarioR['correo'];
            $nombre = $usuarioR['nombre'];
            $ape1 = $usuarioR['ape1'];
            $ape2 = $usuarioR['ape2'];
            $telefono = $usuarioR['telefono'];
            $contra = $usuarioR['contra'];
            $nacimiento = $usuarioR['fecha_nacimiento'];
            $sucursal = $usuarioR['sucursal'];
            $fecha_actual =  date("Y-m-d H:i:s");
            $tipoUsuario = $usuarioR['tip_u_co'];

            $rol = $usuarioR['rol'];
            try {
                DB::beginTransaction();

                if ($actualizar) { // Editar usuario
                    DB::table('usuario')
                        ->where('id', '=', $id)
                        ->update([
                            'nombre' => $nombre,
                            'ape1' => $ape1,
                            'ape2' => $ape2,
                            'cedula' => $cedula,
                            'fecha_nacimiento' => $nacimiento,
                            'correo' => $correo,
                            'telefono' => $telefono,
                            'usuario' => $nombreUsuario,
                            'sucursal' => $sucursal,
                            'rol' => $rol
                        ]);
                } else { // Nuevo usuario
                    $id = DB::table('usuario')->insertGetId([
                        'id' => null,
                        'nombre' => $nombre,
                        'ape1' => $ape1,
                        'ape2' => $ape2,
                        'cedula' => $cedula,
                        'fecha_nacimiento' => $nacimiento,
                        'fecha_ingreso' => $fecha_actual,
                        'correo' => $correo,
                        'telefono' => $telefono,
                        'usuario' => $nombreUsuario,
                        'contra' => md5($contra),
                        'sucursal' => $sucursal,
                        'rol' => $rol,
                        'estado' => SisEstadoController::getIdEstadoByCodGeneral("USU_ACT")
                    ]);

                    DB::table('panel_configuraciones')->insertGetId([
                        'id' => null,
                        'color_fondo' => 1,
                        'color_sidebar' => 1,
                        'color_tema' => "white",
                        'mini_sidebar' => 1,
                        'sticky_topbar' => 1,
                        'usuario' => $id
                    ]);
                }

                DB::commit();

                return $this->responseAjaxSuccess("Se guardo el usuario correctamente", $id);
            } catch (QueryException $ex) {
                DB::rollBack();
                DB::table('log')->insertGetId(['id' => null, 'documento' => 'MantenimientoUsuariosController', 'descripcion' => $ex]);
                return $this->responseAjaxServerError("Ocurrió un error guardando el usuario", []);
            }
        } else {
            return $this->responseAjaxServerError($mensajeValidacion, []);
        }
    }

    public function guardarUsuarioPerfilAjax(Request $request)
    {

        $usuarioR = $request->input('usuario');
        $id = session('usuario')['id'];
        $usuario = DB::table('usuario')->select('usuario.*')->where('id', '=', $id)->get()->first();

        if ($usuario == null) {
            return $this->responseAjaxServerError('No existe un usuario con los credenciales.', []);
        }

        $mensajeValidacion = $this->validarUsuario($usuarioR);
        if ($mensajeValidacion == null) {

            $nombre = $usuarioR['nombre'];
            $ape1 = $usuarioR['ape1'];
            $ape2 = $usuarioR['ape2'];
            $telefono = $usuarioR['telefono'];
            $nacimiento = $usuarioR['fecha_nacimiento'];

            try {
                DB::beginTransaction();

                DB::table('usuario')
                    ->where('id', '=', $id)
                    ->update([
                        'nombre' => $nombre,
                        'ape1' => $ape1,
                        'ape2' => $ape2,
                        'fecha_nacimiento' => $nacimiento,
                        'telefono' => $telefono
                    ]);

                DB::commit();

                return $this->responseAjaxSuccess("Se guardo el usuario correctamente", $id);
            } catch (QueryException $ex) {
                DB::rollBack();
                DB::table('log')->insertGetId(['id' => null, 'documento' => 'MantenimientoUsuariosController', 'descripcion' => $ex]);
                return $this->responseAjaxServerError("Ocurrió un error guardando el usuario", []);
            }
        } else {
            return $this->responseAjaxServerError($mensajeValidacion, []);
        }
    }

    public function goEditarUsuario(Request $request)
    {

        $id = $request->input('idUsuarioEditar');

        if ($id > 0) {
            $usuario = DB::table('usuario')
                ->join('rol', 'rol.id', '=', 'usuario.rol')
                ->join('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
                ->select(
                    'usuario.*',
                    'rol.id as rol_id',
                    'sucursal.id as sucursal_id'
                )
                ->where('usuario.id', '=', $id)->get()->first();

            if ($usuario == null) {
                $this->setError('Editar Usuario', 'No existe el usuario a editar.');
                return redirect('mant/usuarios');
            }
        } else {
            $usuario = null;
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'roles' => MantenimientoRolesController::getRolesActivos(),
            'usuario' => $usuario,
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('usuario.usuario', compact('data'));
    }

    public function restaurarPc()
    {

        $usuario = $this->getUsuarioAuth();
        try {
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update([
                    'color_fondo' => 1,
                    'color_sidebar' => 1,
                    'color_tema' => "white",
                    'mini_sidebar' => 1,
                    'sticky_topbar' => 1,
                ]);
            DB::commit();
            echo 1;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 0;
        }
    }

    public function temaClaro()
    {

        try {
            $usuario = $this->getUsuarioAuth();
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_fondo' => 1, 'color_sidebar' => 1, 'color_tema' => "white"]);
            DB::commit();
            echo 1;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 0;
        }
    }


    public function temaOscuro()
    {

        try {
            $usuario = $this->getUsuarioAuth();
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_fondo' => 2, 'color_sidebar' => 2, 'color_tema' => "black"]);
            DB::commit();
            echo 1;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 0;
        }
    }

    public function sideTeme(Request $request)
    {

        try {
            $usuario = $this->getUsuarioAuth();
            $tema = $request->input('tema');
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_sidebar' => $tema]);
            DB::commit();
            echo 1;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 0;
        }
    }

    public function colorTeme(Request $request)
    {

        try {
            $usuario = $this->getUsuarioAuth();
            $color = $request->input('color');
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['color_tema' => $color]);
            DB::commit();
            echo 1;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 0;
        }
    }

    public function sticky(Request $request)
    {

        try {
            $usuario = $this->getUsuarioAuth();
            $sticky = $request->input('sticky');
            DB::beginTransaction();
            DB::table('panel_configuraciones')
                ->where('usuario', '=', $usuario['id'])
                ->update(['sticky_topbar' => $sticky]);
            DB::commit();
            echo 1;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 0;
        }
    }

    /**
     * Inactiva un usuario cambiando su estado a USU_INACTIVO
     */
    public function inactivarUsuario(Request $request)
    {
        $id = $request->input('idUsuario');
        
        if ($this->isNull($id) || $this->isEmpty($id)) {
            $this->setError("Error", "ID de usuario requerido");
            return redirect()->back();
        }

        try {
            DB::beginTransaction();
            
            // Verificar que no sea el usuario actual
            $usuarioActual = $this->getUsuarioAuth();
            if ($id == $usuarioActual['id']) {
                $this->setError("Error", "No puedes inactivar tu propio usuario");
                return redirect()->back();
            }

            DB::table('usuario')
                ->where('id', '=', $id)
                ->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("USU_INACTIVO")]);

            DB::commit();
            $this->setSuccess("Éxito", "Usuario inactivado correctamente");
            
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError("Error", "Error al inactivar el usuario: " . $ex->getMessage());
        }
        
        return redirect()->back();
    }

    /**
     * Activa un usuario cambiando su estado a USU_ACT
     */
    public function activarUsuario(Request $request)
    {
        $id = $request->input('idUsuario');
        
        if ($this->isNull($id) || $this->isEmpty($id)) {
            $this->setError("Error", "ID de usuario requerido");
            return redirect()->back();
        }

        try {
            DB::beginTransaction();
            
            DB::table('usuario')
                ->where('id', '=', $id)
                ->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("USU_ACT")]);

            DB::commit();
            $this->setSuccess("Éxito", "Usuario activado correctamente");
            
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError("Error", "Error al activar el usuario: " . $ex->getMessage());
        }
        
        return redirect()->back();
    }
}
