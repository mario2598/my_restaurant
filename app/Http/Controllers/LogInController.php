<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\AuthUtil;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class LogInController extends Controller
{
    use SpaceUtil;
    use AuthUtil;

    public function __construct()
    {
    }

    public function index()
    {

        if (!$this->validarSesion()) {
            return $this->goLogIn();
        } else {
            return $this->goInicio();
        }
    }

    public function goLogIn()
    {
        $this->clearAuthUser();
        return view('login');
    }
    

    public function goInicio()
    {
        $data = [
            'menus' => $this->cargarMenus(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('inicio', compact('data'));
    }

    public function logIn(Request $request)
    {

        $user = $request->input('user');
        $user = trim($user);
        $password = $request->input('password');
        $password = trim($password);
        $requeridos = "[";
        $valido = true;

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
                ->where('rol.estado', '=', 'A')
                ->where('contra', '=', md5($password))
                ->get()->first();

            if ($usuario == null) {
                $this->bitacoraInicioSesion($user, "noAuth");
                session(['usuario' => null]);
                $this->setError('Inicio de sesión', "Usuario ó contraseña incorrectos!");
                return redirect('login');
            }

            if ($usuario->estado == SisEstadoController::getIdEstadoByCodGeneral("USU_INACTIVO")) {
                $this->bitacoraInicioSesion($user, "noAuth");
                session(['usuario' => null]);
                $this->setError('Inicio de sesión', "El usuario esta inactivo!");
                return redirect('login');
            }
            $this->bitacoraInicioSesion($user, "auth");

            $this->verificaCumple($usuario);

            session(['usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'usuario' => $usuario->usuario,
                'sucursal' => $usuario->sucursal,
                'token' => $this->asignaTokenUser($usuario) 
            ]]);

            DB::commit();
            return redirect('/');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->bitacoraInicioSesion($user, "noAuth");
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'LogInController', 'descripcion' => $ex]);
            session(['usuario' => null]);
            $this->setError('Inicio de sesión', "Algo salío mal, reintentalo!");
            return redirect('login');
        }
    }

    private function asignaTokenUser($usuario)
    {
        $token = $this->generarToken(50);
        DB::table('usuario')->where("usuario.id","=",$usuario->id)->update(['token_auth' => $token]);
        DB::commit();
        return  $token;
    }

    private function verificaCumple($usuario)
    {
        if (!$this->isNull($usuario->fecha_nacimiento) && !$this->isEmpty($usuario->fecha_nacimiento)) {
            $current_date = date("d-m");

            $cumplenos  = date("d-m", strtotime($usuario->fecha_nacimiento));


            if ($current_date == $cumplenos) {
                $this->setInfo('Felicidades ' . $usuario->nombre . "!", "Te deseamos un feliz cumpleaños!");
            }
        }
    }

    private function generarToken($longitud = 32)
    {
        // Caracteres permitidos para el token
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $caracteres_longitud = strlen($caracteres);
        $token = '';

        // Generar un token aleatorio
        for ($i = 0; $i < $longitud; $i++) {
            $token .= $caracteres[random_int(0, $caracteres_longitud - 1)];
        }

        return $token;
    }

    public function logOut()
    {
        session()->flush();
        return redirect('/');
    }
}
