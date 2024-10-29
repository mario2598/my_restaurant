<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class PerfilUsuarioController extends Controller
{
    use SpaceUtil;
    protected $SpaceSeg;

    public function __construct()
    {
    }

    public function goPerfilUsuario()
    {
        $data = [
            'menus' => $this->cargarMenus(),
            'usuario' => MantenimientoUsuariosController::getUsuarioById(session('usuario')['id']),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('perfil.usuario', compact('data'));
    }

    public function cambiarContraPerfil(Request $request)
    {
        $id = session('usuario')['id'];
       
        $usuario = DB::table('usuario')->select('usuario.*')->where('id', '=', $id)->get()->first();

        if ($usuario == null) {
            return $this->responseAjaxServerError('No existe un usuario con los credenciales.', []);
        }
        
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

}
