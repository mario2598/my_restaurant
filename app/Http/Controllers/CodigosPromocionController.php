<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class CodigosPromocionController extends Controller
{
    use SpaceUtil;


    public function __construct()
    {
    }

    public function goMantPromos()
    {
        if (!$this->validarSesion("mantCodProm")) {
            return redirect('/');
        }

        $data = [
            'promociones' => $this->getAllPromos(),
            'tipos' => SisTipoController::getByCodGeneralGrupo("DESCUENTOS_COD_PROM"),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('mant.codPromo', compact('data'));
    }

    public static function getAllPromos()
    {
        return DB::table('codigo_descuento')
            ->leftjoin('sis_tipo', 'sis_tipo.id', '=', 'codigo_descuento.tipo')
            ->select('codigo_descuento.*', 'sis_tipo.nombre as dscTipo', 'sis_tipo.cod_general')
            ->get();
    }

    public static function codigoPromocionEnUso($codigo, $idPromocion)
    {
        return (DB::table('codigo_descuento')
            ->select('codigo_descuento.*')
            ->where('codigo', '=', $codigo)
            ->where('id', '<>', $idPromocion)
            ->get()
            ->first() != null);
    }

    public static function usarPromocion($idPromocion)
    {
        $params = DB::table('codigo_descuento')
            ->select('codigo_descuento.cant_codigos')
            ->where('id', '=', $idPromocion)
            ->get()->first();
        DB::table('codigo_descuento')
            ->where('id', '=', $idPromocion)
            ->update(['cant_codigos' => $params->cant_codigos - 1]);
    }

    public static function aumentarConsecutivoOrden($sucursal)
    {
        $params = DB::table('sucursal')
            ->select('sucursal.cont_ordenes')
            ->where('id', '=', $sucursal)
            ->get()->first();
        DB::table('sucursal')
            ->where('id', '=', $sucursal)
            ->update(['cont_ordenes' => $params->cont_ordenes + 1]);
    }

    /**
     * Guarda o actualiza un impuestos.
     */

    /**
     * Elimina un Impuesto.
     */
    public function guardarPromocion(Request $request)
    {
        $promocion = $request->input("promocion");

        if ($promocion == null) {
            return $this->responseAjaxServerError("Identificador inválido.", []);
        }
        $nuevaPromocion = $promocion["id"] < 1;

        if (CodigosPromocionController::codigoPromocionEnUso($promocion["codigo"], $promocion["id"])) {
            return $this->responseAjaxServerError("El código ya esta en uso", []);
        }

        try {
            DB::beginTransaction();

            if ($nuevaPromocion) {
                $idPromo = DB::table('codigo_descuento')->insertGetId([
                    'id' => null, 'tipo' => SisTipoController::getIdByCodGeneral($promocion["cod_general"]),
                    'descuento' => $promocion["descuento"], 'fecha_fin' => $promocion["fecha_fin"], 'fecha_inicio' => $promocion["fecha_inicio"], 'descripcion' => $promocion["descripcion"],
                    'codigo' => $promocion["codigo"], 'activo' => $promocion["activo"] ? 1 : 0,
                    'cant_codigos' => $promocion["cant_codigos"]
                ]);
            } else {
                DB::table('codigo_descuento')
                    ->where('id', '=', $promocion["id"])
                    ->update([
                        'tipo' => SisTipoController::getIdByCodGeneral($promocion["cod_general"]),
                        'descuento' => $promocion["descuento"], 'fecha_fin' => $promocion["fecha_fin"], 'fecha_inicio' => $promocion["fecha_inicio"], 'descripcion' => $promocion["descripcion"],
                        'codigo' => $promocion["codigo"], 'activo' => $promocion["activo"]  == "true" ? 1 : 0,
                        'cant_codigos' => $promocion["cant_codigos"]
                    ]);
            }
            $this->setSuccess("Guarda Promoción", "Se guardo la promoción correctamente");
            DB::commit();
            return $this->responseAjaxSuccess("", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error creando la promoción", []);
        }
    }

    public function validarImpuesto(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

        if ($this->isNull($r->input('mdl_generico_ipt_descripcion')) || $this->isEmpty($r->input('mdl_generico_ipt_descripcion'))) {
            $requeridos .= " Descripción ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('mdl_generico_ipt_porcentaje'))) {
            if (!$esPrimero) {
                $requeridos .= ",";
            }
            $requeridos .= " Porcentaje impuesto ";
            $valido = false;
            $esPrimero = false;
        }
        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        if (!$this->isLengthMinor($r->input('mdl_generico_ipt_descripcion'), 50)) {
            $this->setError('Tamaño exedido', "La descrición es de máximo 50 cáracteres.");
            $valido = false;
        }

        if (!$this->isNumber($r->input('mdl_generico_ipt_porcentaje'))) {
            $this->setError('Fomato inválido', "El porcentaje debe ser un número.");
            $valido = false;
        }

        if ($r->input('mdl_generico_ipt_porcentaje') > 99 || $r->input('mdl_generico_ipt_porcentaje') < 0) {
            $this->setError('Valor incorrecto', "El porcentaje debe ser entre 0 y 99 %.");
            $valido = false;
        }

        return $valido;
    }
}
