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
            'parametros_generales' => $this->getParametrosGenerales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('mant.parametros_generales', compact('data'));
    }

    /**
     * Guarda el logo de factura. Recodifica la imagen con GD y la guarda como PNG
     * para evitar que quede dañada por move() o por el entorno (siempre se escribe un PNG válido).
     */
    public function guardar(Request $request)
    {
        try {
            $image = $request->file('logo_empresa');
            if ($image != null) {
                $destinationPath = public_path('assets/images');
                $destinationFile = $destinationPath . DIRECTORY_SEPARATOR . 'default-logo.png';

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
                if (!is_writable($destinationPath)) {
                    $this->setError('Guardar Parámetros Generales', 'No se tienen permisos de escritura en el directorio de imágenes. Por favor, contacte al administrador del sistema.');
                    return redirect('mant/parametrosgenerales');
                }

                $tmpPath = $image->getRealPath();
                $info = @getimagesize($tmpPath);
                if ($info === false || !isset($info[2])) {
                    $this->setError('Guardar Parámetros Generales', 'El archivo no es una imagen válida (PNG o JPEG).');
                    return redirect('mant/parametrosgenerales');
                }

                $gd = null;
                if ($info[2] === IMAGETYPE_PNG) {
                    $gd = @imagecreatefrompng($tmpPath);
                } elseif ($info[2] === IMAGETYPE_JPEG || $info[2] === IMAGETYPE_JPEG2000) {
                    $gd = @imagecreatefromjpeg($tmpPath);
                }
                if ($gd === false) {
                    $this->setError('Guardar Parámetros Generales', 'No se pudo procesar la imagen. Use PNG o JPEG.');
                    return redirect('mant/parametrosgenerales');
                }

                // Redimensionar para evitar que FPDF agote memoria (max 600px lado largo)
                $maxLado = 600;
                $w = (int) ($info[0] ?? 0);
                $h = (int) ($info[1] ?? 0);
                if ($w > $maxLado || $h > $maxLado) {
                    if ($w >= $h) {
                        $nw = $maxLado;
                        $nh = (int) round($h * ($maxLado / $w));
                    } else {
                        $nh = $maxLado;
                        $nw = (int) round($w * ($maxLado / $h));
                    }
                    $resized = imagecreatetruecolor($nw, $nh);
                    if ($resized !== false) {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                        imagefill($resized, 0, 0, $transparent);
                        imagecopyresampled($resized, $gd, 0, 0, 0, 0, $nw, $nh, $w, $h);
                        imagedestroy($gd);
                        $gd = $resized;
                    }
                }

                // Guardar siempre como PNG válido (evita corrupción)
                imagealphablending($gd, false);
                imagesavealpha($gd, true);
                $ok = imagepng($gd, $destinationFile, 9);
                imagedestroy($gd);
                if (!$ok) {
                    $this->setError('Guardar Parámetros Generales', 'No se pudo guardar el archivo PNG.');
                    return redirect('mant/parametrosgenerales');
                }
            }

            $this->setSuccess('Guardar Parámetros Generales', 'Los parámetros generales se guardaron correctamente.');
            return redirect('mant/parametrosgenerales');
        } catch (\Exception $ex) {
            $this->setError('Guardar Parámetros Generales', 'Ocurrió un error guardando los parámetros generales: ' . $ex->getMessage());
            return redirect('mant/parametrosgenerales');
        }
    }
}
