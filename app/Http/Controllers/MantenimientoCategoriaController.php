<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MantenimientoCategoriaController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantCat";

    public function __construct()
    {
    }
    public function index()
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            return redirect('/');
        }

        //dd($this->getTiposGasto());
        $data = [
            'categorias' => $this->getCategorias(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('mant.categoria', compact('data'));
    }

    /**
     * Guarda o actualiza un tipo de gasto.
     */
    public function guardarCategoria(Request $request)
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            return redirect('/');
        }

        if ($this->validarCategoria($request)) {

            $categoria = $request->input('mdl_generico_ipt_categoria');
            $codigo = $request->input('mdl_generico_ipt_codigo');
            $id = $request->input('mdl_generico_ipt_id');
            $posicion_menu = $request->input('posicion_menu');
            
            $image = $request->file('foto_producto');
            $path = null;
            
            if ($image != null) {
                $path = $image->store('categorias', 'public');
            } else {
                // Si no hay nueva imagen y estamos editando, mantener la imagen anterior
                if ($id != '-1' && $id != null && !$this->isEmpty($id)) {
                    $categoriaActual = DB::table('categoria')->where('id', '=', $id)->first();
                    if ($categoriaActual && !empty($categoriaActual->url_imagen)) {
                        $path = $categoriaActual->url_imagen;
                    }
                }
            }

            try {
                DB::beginTransaction();
                if ($id == '-1' || $id == null || $this->isEmpty($id)) {
                    // Nuevo registro
                    $dataInsert = [
                        'id' => null,
                        'categoria' => $categoria,
                        'codigo' => $codigo,
                        'estado' => 'A',
                        'posicion_menu' => $posicion_menu ?? 0
                    ];
                    if ($path !== null) {
                        $dataInsert['url_imagen'] = $path;
                    }
                    $tipo = DB::table('categoria')->insertGetId($dataInsert);
                } else {
                    // Actualizar registro
                    $dataUpdate = [
                        'categoria' => $categoria,
                        'codigo' => $codigo,
                        'posicion_menu' => $posicion_menu ?? 0
                    ];
                    if ($path !== null) {
                        $dataUpdate['url_imagen'] = $path;
                    }
                    DB::table('categoria')
                        ->where('id', '=', $id)
                        ->update($dataUpdate);
                }
                DB::commit();
                $this->setSuccess('Guardar Categoría', 'La categoría se guardo correctamente.');
                return redirect('mant/categoria');
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Categoría', 'Ocurrio un error guardando la categoría.');
                return redirect('mant/categoria');
            }
        } else {
            return redirect('mant/categoria');
        }
    }

    /**
     * Elimina un tipo.
     */
    public function eliminarCategoria(Request $request)
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            return redirect('/');
        }

        $id = $request->input('idGenericoEliminar');
        if ($id == null || $id == '' || $id < 1) {
            $this->setError('Eliminar Categoría', 'Identificador inválido.');
            return redirect('mant/categoria');
        }
        try {
            DB::beginTransaction();
            $tipo = DB::table('categoria')->where('id', '=', $id)->get()->first();
            if ($tipo == null) {
                $this->setError('Eliminar Categoría', 'No existe la categoría a eliminar.');
                return redirect('mant/categoria');
            } else {
                DB::table('categoria')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Categoría', 'La categoría se elimino correctamente.');
            return redirect('mant/categoria');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Categoría', 'Ocurrio un error eliminando la categoría.');
            return redirect('mant/categoria');
        }
    }

    public function validarCategoria(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

        if ($this->isNull($r->input('mdl_generico_ipt_categoria')) || $this->isEmpty($r->input('mdl_generico_ipt_categoria'))) {
            $requeridos .= " Categoría ";
            $valido = false;
            $esPrimero = false;
        }

        if ($this->isNull($r->input('mdl_generico_ipt_codigo')) || $this->isEmpty($r->input('mdl_generico_ipt_codigo'))) {
            $requeridos .= " Código ";
            $valido = false;
            $esPrimero = false;
        }


        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        if (!$this->isLengthMinor($r->input('mdl_generico_ipt_categoria'), 30)) {
            $this->setError('Tamaño exedido', "La categoría es de máximo 30 cáracteres.");
            $valido = false;
        }

        if (!$this->isLengthMinor($r->input('mdl_generico_ipt_codigo'), 9)) {
            $this->setError('Tamaño exedido', "El código es de máximo 9 cáracteres.");
            $valido = false;
        }


        return $valido;
    }
}
