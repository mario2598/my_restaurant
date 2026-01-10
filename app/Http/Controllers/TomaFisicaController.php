<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use App\Traits\SpaceUtil;

class TomaFisicaController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function goCrearToma()
    {
        if (!$this->validarSesion("mt_tomaFis")) {
            return redirect('/');
        }

        $data = [
            'sucursales' => $this->getSucursalesAll(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('materiaPrima.inventario.tomaFisica', compact('data'));
    }

    public function buscarMPTomaFisica(Request $request)
    {
        if (!$this->validarSesion("mt_tomaFis")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        $sucursal = $request->input('sucursal');
        if ($this->isNull($sucursal) || $sucursal == "") {
            return $this->responseAjaxServerError("Debes seleccionar una sucursal.", []);
        }
        $materiaPrima = MateriaPrimaController::getInventario($sucursal);

        return  $this->responseAjaxSuccess("", $materiaPrima);
    }

    public function creaMPTomaFisica(Request $request)
    {
        $fechaActual = date("Y-m-d H:i:s");
        if (!$this->validarSesion("mt_tomaFis")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        $sucursal = $request->input('sucursal');
        $idMateriaPrima = $request->input('idMateriaPrima');
        $cantidadUsuario = $request->input('cantidadAjuste');

        if ($this->isNull($sucursal) || $sucursal == "") {
            return $this->responseAjaxServerError("Debes seleccionar una sucursal.", []);
        }

        if ($this->isNull($idMateriaPrima) || $idMateriaPrima < 1) {
            return $this->responseAjaxServerError("Materia prima no existe.", []);
        }

        $materia_prima = DB::table('materia_prima')
            ->select('materia_prima.*')
            ->where('materia_prima.id', '=', $idMateriaPrima)
            ->get()->first();

        if ($materia_prima == null) {
            return $this->responseAjaxServerError("Materia prima no existe.", []);
        }

        $tomaExiste = DB::table('toma_fisica')
            ->select('toma_fisica.*')
            ->where('toma_fisica.materia_prima', '=', $idMateriaPrima)
            ->where('toma_fisica.sucursal', '=', $sucursal)
            ->whereDate('toma_fisica.fecha', '=', now()->toDateString())
            ->get()->first();

        if ($tomaExiste != null) {
            return $this->responseAjaxServerError("Ya existe una toma física registrada el día de hoy. Solo se puede registrar una toma diaria por producto.", []);
        }

        if ($this->isNull($cantidadUsuario) || $cantidadUsuario < 0) {
            return $this->responseAjaxServerError("La cantidad a ajustar no puede ser vacía o menor a 0.", []);
        }
        $usuarioReportaMas = false;

        $cantidadInventario = (DB::table('mt_x_sucursal')
            ->where('mt_x_sucursal.sucursal', '=', $sucursal)
            ->where('mt_x_sucursal.materia_prima', '=', $idMateriaPrima)
            ->sum('mt_x_sucursal.cantidad')) ?? 0;

        $textoAux = "";
        if ($cantidadInventario >  $cantidadUsuario) {
            $cantidadAjuste =  $cantidadInventario -  $cantidadUsuario;
            $textoAux = "Rebajo inventario ajustado por toma física  # ";
        } else if ($cantidadInventario < $cantidadUsuario) {
            $cantidadAjuste =  $cantidadUsuario -  $cantidadInventario;
            $textoAux = "Aumento inventario ajustado por toma física  # ";
        } else {
            $cantidadAjuste =  $cantidadUsuario -  $cantidadInventario;
            $textoAux = "Actualiza inventario (sin cambios) ajustado por toma física  # ";
        }
        $detalleMp =  'Materia Prima : ' . $materia_prima->nombre .
            ' | Detalle : '.$textoAux;

        try {
            DB::beginTransaction();

            $idToma = DB::table('toma_fisica')->insertGetId([
                'id' => null, 'materia_prima' => $idMateriaPrima,
                'dsc_materia_prima' => $materia_prima->nombre, 'cantidad_sistema' => $cantidadInventario, 'cantidad_usuario' =>  $cantidadUsuario,
                'observaciones' => "", 'usuario' => session('usuario')['id'],
                'fecha' => $fechaActual,
                'sucursal' => $sucursal, 'cantidad_ajuste' => $cantidadAjuste
            ]);

            DB::table('mt_x_sucursal')
                ->where('sucursal', '=', $sucursal)
                ->where('materia_prima', '=', $idMateriaPrima)
                ->update(['cantidad' =>  $cantidadUsuario]);

            DB::table('bit_materia_prima')->insert([
                'id' => null, 'usuario' => session('usuario')['id'],
                'materia_prima' => $idMateriaPrima, 'detalle' => $detalleMp . $idToma,
                'cantidad_anterior' =>  $cantidadInventario ?? 0,
                'cantidad_ajuste' => $cantidadAjuste,
                'cantidad_nueva' =>  $cantidadUsuario, 'fecha' => $fechaActual, 'sucursal' => $sucursal
            ]);

            DB::commit();
            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Algo salio mal...' . $ex, []);
        }
    }
}
