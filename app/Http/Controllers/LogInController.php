<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class LogInController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "inicio";

    public function __construct()
    {
        
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('login');
        }else{
           
            $data = [
                'menus'=> $this->cargarMenus(),
                'panel_configuraciones' => $this->getPanelConfiguraciones()
            ];
            return view('inicio',compact('data'));
        }
    }

    public function goLogIn(){
        session(['usuario'=>null]);
        return view('login');
    }

    public function ingresar(Request $request)
    {

        $user = $request->input('user');
        $user = trim($user);
        $password = $request->input('password');
        $password = trim($password);
        $requeridos = "[";
        $valido = true;
        $estadoLogin = '';

        if ($this->isNull($user) || $this->isEmpty($user)) {
            $requeridos .= " Usuario ";
            $valido = false;
        }
        if ($this->isNull($password) || $this->isEmpty($password)) {
            $requeridos .= ", Contraseña ";
            $valido = false;
        }
        $requeridos .= "] ";
        if (!$valido) {
            session(['usuario' => null]);
            $this->setError('Campos Requeridos', $requeridos);
            return redirect('login');
        }
        try {
            DB::beginTransaction();
            $usuario = DB::table('usuario')
                ->join('rol', 'rol.id', '=', 'usuario.rol')
                ->select('usuario.*', 'rol.codigo as codigo_rol')
                ->where('usuario', '=', $user)
                ->where('contra', '=', md5($password))
                ->get()->first();

            if ($usuario == null) {
                $this->bitacoraInicioSesion($user, "noAuth");
                session(['usuario' => null]);
                $this->setError('Inicio de sesión', "Usuario ó contraseña incorrectos!");
                return redirect('login');
            }

            if ($usuario->estado != 'A') {
                $this->bitacoraInicioSesion($user, "noAuth");
                session(['usuario' => null]);
                $this->setError('Inicio de sesión', "El usuario esta inactivo!");
                return redirect('login');
            }
            $this->bitacoraInicioSesion($user, "auth");

            if (!$this->isNull($usuario->fecha_nacimiento) && !$this->isEmpty($usuario->fecha_nacimiento)) {
                $current_date = date("d-m");

                $cumplenos  = date("d-m", strtotime($usuario->fecha_nacimiento));


                if ($current_date == $cumplenos) {
                    $this->setInfo('Felicidades ' . $usuario->nombre . "!", "Te deseamos un feliz cumpleaños!");
                }
            }

            session(['usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'usuario' => $usuario->usuario
            ]]);

            DB::commit();
            return redirect('/');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->bitacoraInicioSesion($user, "noAuth");
            session(['usuario' => null]);
            $this->setError('Inicio de sesión', "Algo salío mal, reintentalo!");
            return redirect('login');
        }
    }
}
