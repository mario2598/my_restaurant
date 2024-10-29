<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;


class ParametrosGeneralesController extends Controller
{
    use SpaceUtil;

    public function __construct() {}
    public function index()
    {

        $data = [
            'menus' => $this->cargarMenus(),
            'parametros_generales' => $this->getParametrosGenerales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('mant.parametros_generales', compact('data'));
    }

    /**
     * Guarda o actualiza un tipo de ingreso.
     */
    public function guardar(Request $request)
    {

        try {
            $image = $request->file('logo_empresa');
            if ($image != null) {
                $image->move(public_path('assets/images'), 'default-logo.png');
            }

            $this->setSuccess('Guardar Parámetros Generales', 'Los parámetros generales se guardaron correctamente.');
            return redirect('mant/parametrosgenerales');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Guardar Parámetros Generales', 'Ocurrio un error guardando los parámetros generales.');
            return redirect('mant/parametrosgenerales');
        }
    }
}
