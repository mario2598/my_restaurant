<?php

namespace App\Traits;

use App\Http\Controllers\SisEstadoController;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

trait AuthUtil
{
  /**
   * Valida si el usuario tiene permisos para estar en el sistema
   * @param $codigo_pantalla puede ser array o string
   * @return si es valido (boolean)
   */
  public function validarSesion()
  {
    $usuarioSession = session('usuario');

    try {
      if ($usuarioSession == null) {
        return false;
      }

      if ($usuarioSession['id'] == null) {
        return false;
      }
      $usuario = DB::table('usuario')
        ->join('rol', 'rol.id', '=', 'usuario.rol')
        ->select('usuario.*', 'rol.id as rol_id', 'rol.rol as rol_rol')
       // ->where('usuario.token_auth', '=', $usuarioSession['token'] ?? '')
        ->where('usuario.id', '=', $usuarioSession['id'] ?? '')
        ->get()->first();

      if ($usuario == null) {
        return false;
      }
      if ($usuario->estado == SisEstadoController::getIdEstadoByCodGeneral("USU_INACTIVO")) {
        return false;
      }

      session(['usuario' => [
        'id' => $usuario->id,
        'nombre' => $usuario->nombre,
        'usuario' => $usuario->usuario,
        'sucursal' => $usuario->sucursal,
        'token' => $usuarioSession['token'] ?? ''
      ]]);

      return true;
    } catch (QueryException $ex) {
      return false;
    }
  }

  public function validarPermisos($codigo_pantalla = [])
  {
    $usuarioSession = session('usuario');

    try {
      if ($usuarioSession == null) {
        return false;
      }

      if ($usuarioSession['id'] == null) {
        return false;
      }

      $usuario = DB::table('usuario')
        ->join('rol', 'rol.id', '=', 'usuario.rol')
        ->select('usuario.*', 'rol.id as rol_id', 'rol.rol as rol_rol')
        //->where('usuario.token_auth', '=', $usuarioSession['token'] ?? '')
        ->where('usuario.id', '=', $usuarioSession['id'] ?? '')
        ->where('rol.estado', '=', 'A')
        ->get()->first();

      if ($usuario == null) {
        return false;
      }

      if ($usuario->estado == SisEstadoController::getIdEstadoByCodGeneral("USU_INACTIVO")) {
        return false;
      }
      $entra = false;
      foreach ($codigo_pantalla as $i) {
        if ($i != 'inicio') {
          $permiso = DB::table('menu')
            ->leftjoin('vista', 'vista.id', '=', 'menu.vista')
            ->where('menu.rol', '=', $usuario->rol_id)
            ->where('vista.codigo_pantalla', '=', $i)
            ->where('vista.inactivo', '=', 0)
            ->get()->first();

          if ($permiso != null) {
            $entra = true;
          }
        }
      }

      if (!$entra) {
        return false;
      }

      session(['usuario' => [
        'id' => $usuario->id,
        'nombre' => $usuario->nombre,
        'usuario' => $usuario->usuario,
        'sucursal' => $usuario->sucursal,
        'token' => $usuarioSession['token'] ?? ''
      ]]);

      return true;
    } catch (QueryException $ex) {
      return false;
    }
  }


  public function clearAuthUser()
  {
    if (isset($_SESSION)) {
      session_unset();
      session_destroy();
    }
  }
}
