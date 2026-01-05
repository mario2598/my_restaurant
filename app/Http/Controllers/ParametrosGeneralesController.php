<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


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
                $destinationPath = public_path('assets/images');
                
                // Verificar si el directorio existe, si no, crearlo
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
                
                // Verificar permisos de escritura
                if (!is_writable($destinationPath)) {
                    $this->setError('Guardar Parámetros Generales', 'No se tienen permisos de escritura en el directorio de imágenes. Por favor, contacte al administrador del sistema.');
                    return redirect('mant/parametrosgenerales');
                }
                
                // Mover el archivo
                $image->move($destinationPath, 'default-logo.png');
            }

            $this->setSuccess('Guardar Parámetros Generales', 'Los parámetros generales se guardaron correctamente.');
            return redirect('mant/parametrosgenerales');
        } catch (\Exception $ex) {
            $this->setError('Guardar Parámetros Generales', 'Ocurrió un error guardando los parámetros generales: ' . $ex->getMessage());
            return redirect('mant/parametrosgenerales');
        }
    }
}
