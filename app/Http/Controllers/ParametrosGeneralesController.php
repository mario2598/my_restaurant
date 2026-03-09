<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Support\Facades\File;


class ParametrosGeneralesController extends Controller
{
    use SpaceUtil;

    public function __construct() {}
    public function index()
    {

        $data = [
            'parametros_generales' => $this->getParametrosGenerales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones(),
            'sucursales' => $this->getSucursalesAll()
        ];
        return view('mant.parametros_generales', compact('data'));
    }

    /**
     * Guarda los logos por sucursal de forma independiente.
     */
    public function guardar(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->guardarLogosSucursales($request->file('logo_sistema', []), 'url_logo_sistema', 'logo_sistema');
            $this->guardarLogosSucursales($request->file('logo_factura', []), 'url_logo_factura', 'logo_factura');

            DB::commit();

            $this->setSuccess('Guardar Parámetros Generales', 'Los parámetros generales se guardaron correctamente.');
            return redirect('mant/parametrosgenerales');
        } catch (\Exception $ex) {
            DB::rollBack();
            $this->setError('Guardar Parámetros Generales', 'Ocurrió un error guardando los parámetros generales: ' . $ex->getMessage());
            return redirect('mant/parametrosgenerales');
        }
    }

    private function guardarLogosSucursales($imagenes, $campoBd, $nombreArchivo)
    {
        if (!is_array($imagenes) || count($imagenes) === 0) {
            return;
        }

        foreach ($imagenes as $idSucursal => $imagen) {
            if ($imagen == null) {
                continue;
            }

            $sucursal = DB::table('sucursal')->where('id', '=', $idSucursal)->first();
            if ($sucursal == null) {
                throw new \Exception('No se encontró la sucursal ' . $idSucursal . ' para guardar el logo.');
            }

            $extension = strtolower($imagen->getClientOriginalExtension() ?: 'png');
            $rutaRelativa = 'assets/images/sucursales/' . $idSucursal . '/' . $nombreArchivo . '.' . $extension;
            $rutaAbsoluta = public_path($rutaRelativa);

            $this->guardarImagen($imagen, $rutaAbsoluta);

            DB::table('sucursal')
                ->where('id', '=', $idSucursal)
                ->update([$campoBd => $rutaRelativa]);
        }
    }

    private function guardarImagen($image, $destinationFile)
    {
        $destinationPath = dirname($destinationFile);

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        if (!is_writable($destinationPath)) {
            throw new \Exception('No se tienen permisos de escritura en el directorio de imágenes. Por favor, contacte al administrador del sistema.');
        }

        $image->move($destinationPath, basename($destinationFile));
    }
}
