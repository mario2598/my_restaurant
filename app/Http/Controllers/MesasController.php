<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MesasController extends Controller
{
    use SpaceUtil;

    public function goMesasAdmin()
    {
        $data = [
            'sucursales' => MantenimientoSucursalController::getSucursalesActivas(),
            'menus' => $this->cargarMenus(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('mobiliario.mesas.administrar', compact('data'));
    }

    public static function getBySucursal($idSucursal)
    {
        return DB::table('mesa')->where('sucursal', '=', $idSucursal)->get();
    }


    public function cargarMesasAdmin(Request $request)
    {
        try {
            $idSucursal = $request->input('idSucursal');

            return $this->responseAjaxSuccess("", self::getBySucursal($idSucursal));
        } catch (QueryException $ex) {
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'MesasController', 'descripcion' => $ex]);
            return $this->responseAjaxServerError("Error cargando las mesas", []);
        }
    }

    public function guardarMesa(Request $request)
    {
        $mesa = $request->input('mesa');

        try {
            $id = $mesa['id'];
            $idSucursal = $mesa['sucursal'];
            $numero_mesa = $mesa['numero_mesa'];
            $capacidad = $mesa['capacidad'];

            // Validar que nombre y sucursal no sean nulos
            if (empty($numero_mesa)) {
                return $this->responseAjaxServerError("El número de mesa es obligatorio", []);
            }

            if (empty($idSucursal)) {
                return $this->responseAjaxServerError("La sucursal es obligatoria", []);
            }

            $actualizar = !(is_null($id) || $id < 1);

            DB::beginTransaction();

            if ($actualizar) {
                // Obtener la comanda actual
                $mesaEntity = DB::table('mesa')->where('id', '=', $id)->first();

                if ($mesaEntity == null) {
                    return $this->responseAjaxServerError("Ocurrió un error cargando la mesa", []);
                }

                // Validar que el nombre no esté en uso, excepto cuando es el mismo que ya tiene la comanda
                $nombreEnUso = DB::table('mesa')
                    ->where('sucursal', '=', $idSucursal)
                    ->where('numero_mesa', '=', $numero_mesa)
                    ->where('id', '!=', $id) // Excluir la  actual
                    ->exists();

                if ($nombreEnUso) {
                    return $this->responseAjaxServerError("El número de mesa ya está en uso en esta sucursal", []);
                }

                // Actualizar la comanda
                DB::table('mesa')
                    ->where('id', '=', $id)
                    ->update([
                        'numero_mesa' => $numero_mesa,
                        'capacidad' => $capacidad
                    ]);
            } else {
                $nombreEnUso = DB::table('mesa')
                    ->where('sucursal', '=', $idSucursal)
                    ->where('numero_mesa', '=', $numero_mesa)
                    ->exists();

                if ($nombreEnUso) {
                    return $this->responseAjaxServerError("El número de mesa ya está en uso en esta sucursal", []);
                }
                DB::table('mesa')
                    ->insertGetId([
                        'sucursal' => $idSucursal,
                        'numero_mesa' => $numero_mesa,
                        'capacidad' => $capacidad,
                        'estado' => SisEstadoController::getIdEstadoByCodGeneral("MESA_DISPONIBLE")
                    ]);
            }

            DB::commit();
            return $this->responseAjaxSuccess("Mesa guardada correctamente", "");
        } catch (\Exception $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MesasController',
                'descripcion' => $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error cargando las mesas", []);
        }
    }

    public function eliminarMesa(Request $request)
    {
        $id = $request->input('id');

        try {
            if (is_null($id) || $id < 1) {
                return $this->responseAjaxServerError("El ID de la mesa es obligatorio", []);
            }

            DB::beginTransaction();

            // Obtener la comanda actual para asegurarse de que existe
            $mesaEntity = DB::table('mesa')->where('id', '=', $id)->first();

            if ($mesaEntity == null) {
                return $this->responseAjaxServerError("No se encontró la mesa", []);
            }

            // Eliminar la comanda
            DB::table('mesa')->where('id', '=', $id)->delete();

            DB::commit();
            return $this->responseAjaxSuccess("Mesa eliminada correctamente", "");
        } catch (\Exception $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'ComandasController',
                'descripcion' => $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error al eliminar la mesa", []);
        }
    }
}
