<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use App\Http\Controllers\SisEstadoController;

class MesasController extends Controller
{
    use SpaceUtil;

    public function goMesasAdmin()
    {
        $data = [
            'sucursales' => MantenimientoSucursalController::getSucursalesActivas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('mobiliario.mesas.administrar', compact('data'));
    }

    public function goPlanoMesas()
    {
        $data = [
            'sucursales' => MantenimientoSucursalController::getSucursalesActivas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('mobiliario.mesas.plano', compact('data'));
    }

    /** Zonas por defecto según layout típico de sucursal (cocina, baño, entrada, jardín). */
    public static function getZonasPlanoDefault(): array
    {
        return [
            ['id' => 'cocina', 'nombre' => 'Cocina', 'x' => 2, 'y' => 2, 'w' => 40, 'h' => 44, 'color' => '#e9ecef'],
            ['id' => 'bano', 'nombre' => 'Baño', 'x' => 68, 'y' => 36, 'w' => 14, 'h' => 18, 'color' => '#f1f3f5'],
            ['id' => 'entrada', 'nombre' => 'Entrada', 'x' => 36, 'y' => 80, 'w' => 20, 'h' => 18, 'color' => '#fff3cd'],
            ['id' => 'jardin', 'nombre' => 'Jardín', 'x' => 70, 'y' => 68, 'w' => 28, 'h' => 30, 'color' => '#d4edda'],
        ];
    }

    public static function getPlanoDataForSucursal(int $idSucursal): array
    {
        $plano = DB::table('sucursal_plano')->where('sucursal', '=', $idSucursal)->first();
        $zonas = self::getZonasPlanoDefault();
        $anchoRef = 100;
        $altoRef = 150;

        if ($plano != null) {
            $anchoRef = (int) ($plano->ancho_referencia ?? 100);
            $altoRef = (int) ($plano->alto_referencia ?? 150);
            if (!empty($plano->zonas_json)) {
                $decoded = json_decode($plano->zonas_json, true);
                if (is_array($decoded) && count($decoded) > 0) {
                    $zonas = $decoded;
                }
            }
        }

        $mesas = DB::table('mesa')
            ->join('sis_estado', 'sis_estado.id', '=', 'mesa.estado')
            ->where('mesa.sucursal', '=', $idSucursal)
            ->select(
                'mesa.*',
                'sis_estado.cod_general as estado_codigo',
                'sis_estado.nombre as estado_nombre'
            )
            ->orderBy('mesa.numero_mesa', 'ASC')
            ->get();

        return [
            'plano' => $plano,
            'zonas' => $zonas,
            'ancho_referencia' => $anchoRef,
            'alto_referencia' => $altoRef,
            'mesas' => $mesas,
        ];
    }

    public function cargarPlano(Request $request)
    {
        try {
            $idSucursal = (int) $request->input('idSucursal');
            if ($idSucursal < 1) {
                return $this->responseAjaxServerError("La sucursal es obligatoria", []);
            }

            return $this->responseAjaxSuccess("", self::getPlanoDataForSucursal($idSucursal));
        } catch (\Exception $ex) {
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MesasController',
                'descripcion' => 'cargarPlano: ' . $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error al cargar el plano", []);
        }
    }

    public function guardarPosicionMesa(Request $request)
    {
        try {
            $id = (int) $request->input('id');
            $planoX = $request->input('plano_x');
            $planoY = $request->input('plano_y');
            $planoAncho = $request->input('plano_ancho');
            $planoAlto = $request->input('plano_alto');
            $forma = $request->input('forma', 'rectangular');
            $zona = $request->input('zona');

            if ($id < 1) {
                return $this->responseAjaxServerError("ID de mesa inválido", []);
            }

            $formasValidas = ['rectangular', 'cuadrada', 'redonda'];
            if (!in_array($forma, $formasValidas, true)) {
                $forma = 'rectangular';
            }

            DB::table('mesa')->where('id', '=', $id)->update([
                'plano_x' => $planoX !== null && $planoX !== '' ? round((float) $planoX, 2) : null,
                'plano_y' => $planoY !== null && $planoY !== '' ? round((float) $planoY, 2) : null,
                'plano_ancho' => $planoAncho !== null && $planoAncho !== '' ? round((float) $planoAncho, 2) : 7,
                'plano_alto' => $planoAlto !== null && $planoAlto !== '' ? round((float) $planoAlto, 2) : 7,
                'forma' => $forma,
                'zona' => $zona,
            ]);

            return $this->responseAjaxSuccess("Posición guardada", []);
        } catch (\Exception $ex) {
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MesasController',
                'descripcion' => 'guardarPosicionMesa: ' . $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error al guardar la posición", []);
        }
    }

    public function guardarPlanoSucursal(Request $request)
    {
        try {
            $idSucursal = (int) $request->input('idSucursal');
            $zonas = $request->input('zonas');
            $anchoRef = (int) $request->input('ancho_referencia', 100);
            $altoRef = (int) $request->input('alto_referencia', 150);

            if ($idSucursal < 1) {
                return $this->responseAjaxServerError("La sucursal es obligatoria", []);
            }

            if (!is_array($zonas)) {
                $zonas = self::getZonasPlanoDefault();
            }

            $zonasJson = json_encode($zonas, JSON_UNESCAPED_UNICODE);
            $existente = DB::table('sucursal_plano')->where('sucursal', '=', $idSucursal)->first();

            if ($existente != null) {
                DB::table('sucursal_plano')->where('sucursal', '=', $idSucursal)->update([
                    'zonas_json' => $zonasJson,
                    'ancho_referencia' => max(1, $anchoRef),
                    'alto_referencia' => max(1, $altoRef),
                ]);
            } else {
                DB::table('sucursal_plano')->insert([
                    'sucursal' => $idSucursal,
                    'nombre' => 'Plano principal',
                    'ancho_referencia' => max(1, $anchoRef),
                    'alto_referencia' => max(1, $altoRef),
                    'zonas_json' => $zonasJson,
                    'activo' => 'S',
                ]);
            }

            return $this->responseAjaxSuccess("Plano guardado correctamente", []);
        } catch (\Exception $ex) {
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MesasController',
                'descripcion' => 'guardarPlanoSucursal: ' . $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error al guardar el plano", []);
        }
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
                $updateData = [
                    'numero_mesa' => $numero_mesa,
                    'capacidad' => $capacidad
                ];
                if (array_key_exists('plano_x', $mesa)) {
                    $updateData['plano_x'] = $mesa['plano_x'] ?? null;
                    $updateData['plano_y'] = $mesa['plano_y'] ?? null;
                    $updateData['forma'] = $mesa['forma'] ?? 'rectangular';
                    $updateData['zona'] = $mesa['zona'] ?? null;
                }
                DB::table('mesa')
                    ->where('id', '=', $id)
                    ->update($updateData);
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

    public function cambiarEstadoMesa(Request $request)
    {
        try {
            $idMesa = $request->input('id_mesa');
            $nuevoEstado = $request->input('estado'); // 'MESA_DISPONIBLE' o 'MESA_OCUPADA'

            if (is_null($idMesa) || $idMesa < 1) {
                return $this->responseAjaxServerError("El ID de la mesa es obligatorio", []);
            }

            if (!in_array($nuevoEstado, ['MESA_DISPONIBLE', 'MESA_OCUPADA'])) {
                return $this->responseAjaxServerError("Estado inválido", []);
            }

            DB::beginTransaction();

            $mesaEntity = DB::table('mesa')->where('id', '=', $idMesa)->first();

            if ($mesaEntity == null) {
                DB::rollBack();
                return $this->responseAjaxServerError("No se encontró la mesa", []);
            }

            $estadoId = SisEstadoController::getIdEstadoByCodGeneral($nuevoEstado);

            DB::table('mesa')
                ->where('id', '=', $idMesa)
                ->update(['estado' => $estadoId]);

            DB::commit();

            $estadoNombre = $nuevoEstado === 'MESA_DISPONIBLE' ? 'Disponible' : 'Ocupada';
            return $this->responseAjaxSuccess("Estado de la mesa actualizado a: " . $estadoNombre, [
                'id' => $idMesa,
                'estado' => $estadoId,
                'estado_codigo' => $nuevoEstado
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MesasController',
                'descripcion' => $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error al cambiar el estado de la mesa: " . $ex->getMessage(), []);
        }
    }

    public function obtenerMesasSucursal(Request $request)
    {
        try {
            $idSucursal = $request->input('id_sucursal');
            
            if (empty($idSucursal)) {
                $idSucursal = $this->getUsuarioSucursal();
            }

            $mesas = DB::table('mesa')
                ->join('sis_estado', 'sis_estado.id', '=', 'mesa.estado')
                ->where('mesa.sucursal', '=', $idSucursal)
                ->select('mesa.*', 'sis_estado.cod_general as estado_codigo', 'sis_estado.nombre as estado_nombre')
                ->orderBy('mesa.numero_mesa', 'ASC')
                ->get();

            return $this->responseAjaxSuccess("Mesas cargadas correctamente", $mesas);
        } catch (\Exception $ex) {
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MesasController',
                'descripcion' => $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error al cargar las mesas: " . $ex->getMessage(), []);
        }
    }
}
