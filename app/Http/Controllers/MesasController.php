<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
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

    /** Plantilla inicial de áreas (se copia a sucursal_plano_area la primera vez). */
    public static function getZonasPlanoDefault(): array
    {
        return [
            ['id' => 'cocina', 'nombre' => 'Cocina', 'x' => 2, 'y' => 2, 'w' => 40, 'h' => 44, 'color' => '#e9ecef'],
            ['id' => 'bano', 'nombre' => 'Baño', 'x' => 68, 'y' => 36, 'w' => 14, 'h' => 18, 'color' => '#f1f3f5'],
            ['id' => 'entrada', 'nombre' => 'Entrada', 'x' => 36, 'y' => 80, 'w' => 20, 'h' => 18, 'color' => '#fff3cd'],
            ['id' => 'jardin', 'nombre' => 'Jardín', 'x' => 70, 'y' => 68, 'w' => 28, 'h' => 30, 'color' => '#d4edda'],
        ];
    }

    public static function slugCodigoArea(string $nombre): string
    {
        $s = strtolower(trim($nombre));
        $s = preg_replace('/[^a-z0-9]+/', '_', $s);
        $s = trim($s, '_');
        return $s !== '' ? substr($s, 0, 40) : 'area';
    }

    /** @return \Illuminate\Support\Collection */
    public static function getAreasCatalogoSucursal(int $idSucursal)
    {
        return DB::table('sucursal_plano_area')
            ->where('sucursal', '=', $idSucursal)
            ->where('activo', '=', 'S')
            ->orderBy('orden', 'ASC')
            ->orderBy('nombre', 'ASC')
            ->get();
    }

    public static function asegurarAreasSucursal(int $idSucursal, $plano = null): void
    {
        $count = DB::table('sucursal_plano_area')->where('sucursal', '=', $idSucursal)->count();
        if ($count > 0) {
            return;
        }

        if ($plano === null) {
            $plano = DB::table('sucursal_plano')->where('sucursal', '=', $idSucursal)->first();
        }

        if ($plano != null && !empty($plano->zonas_json)) {
            $decoded = json_decode($plano->zonas_json, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $orden = 0;
                foreach ($decoded as $z) {
                    $codigo = $z['id'] ?? self::slugCodigoArea($z['nombre'] ?? 'area');
                    DB::table('sucursal_plano_area')->insert([
                        'sucursal' => $idSucursal,
                        'codigo' => $codigo,
                        'nombre' => $z['nombre'] ?? $codigo,
                        'color' => $z['color'] ?? '#e9ecef',
                        'plano_x' => isset($z['x']) ? round((float) $z['x'], 2) : null,
                        'plano_y' => isset($z['y']) ? round((float) $z['y'], 2) : null,
                        'plano_ancho' => isset($z['w']) ? round((float) $z['w'], 2) : null,
                        'plano_alto' => isset($z['h']) ? round((float) $z['h'], 2) : null,
                        'orden' => $orden++,
                        'activo' => 'S',
                    ]);
                }
                return;
            }
        }

        $orden = 0;
        foreach (self::getZonasPlanoDefault() as $z) {
            DB::table('sucursal_plano_area')->insert([
                'sucursal' => $idSucursal,
                'codigo' => $z['id'],
                'nombre' => $z['nombre'],
                'color' => $z['color'],
                'plano_x' => $z['x'],
                'plano_y' => $z['y'],
                'plano_ancho' => $z['w'],
                'plano_alto' => $z['h'],
                'orden' => $orden++,
                'activo' => 'S',
            ]);
        }
    }

    public static function areasCatalogoToZonasPlano($areas): array
    {
        $zonas = [];
        foreach ($areas as $a) {
            if ($a->plano_x === null || $a->plano_y === null) {
                continue;
            }
            $zonas[] = [
                'id' => $a->codigo,
                'area_id' => (int) $a->id,
                'nombre' => $a->nombre,
                'x' => (float) $a->plano_x,
                'y' => (float) $a->plano_y,
                'w' => (float) ($a->plano_ancho ?? 10),
                'h' => (float) ($a->plano_alto ?? 10),
                'color' => $a->color ?? '#e9ecef',
            ];
        }
        return $zonas;
    }

    public static function getPlanoDataForSucursal(int $idSucursal): array
    {
        $anchoRef = 100;
        $altoRef = 150;
        $plano = null;
        $areasCatalogo = collect();
        $zonas = [];

        if (Schema::hasTable('sucursal_plano')) {
            $plano = DB::table('sucursal_plano')->where('sucursal', '=', $idSucursal)->first();
            if ($plano != null) {
                $anchoRef = (int) ($plano->ancho_referencia ?? 100);
                $altoRef = (int) ($plano->alto_referencia ?? 150);
            }
        }

        if (Schema::hasTable('sucursal_plano_area')) {
            self::asegurarAreasSucursal($idSucursal, $plano);
            $areasCatalogo = self::getAreasCatalogoSucursal($idSucursal);
            $zonas = self::areasCatalogoToZonasPlano($areasCatalogo);
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

        // Pisos
        $pisosJson = $plano ? ($plano->pisos_json ?? null) : null;
        $pisos = $pisosJson ? json_decode($pisosJson, true) : [['id' => 1, 'nombre' => 'Área 1']];
        if (!is_array($pisos) || count($pisos) === 0) {
            $pisos = [['id' => 1, 'nombre' => 'Área 1']];
        }
        $pisosIds = array_column($pisos, 'id');
        foreach ($mesas as $m) {
            $pid = (int)($m->piso ?? 1);
            if ($pid > 0 && !in_array($pid, $pisosIds, true)) {
                $pisos[] = ['id' => $pid, 'nombre' => 'Área ' . $pid];
                $pisosIds[] = $pid;
            }
        }
        usort($pisos, function($a, $b) { return ($a['id'] ?? 0) - ($b['id'] ?? 0); });

        return [
            'plano' => $plano,
            'zonas' => $zonas,
            'areas_catalogo' => $areasCatalogo,
            'ancho_referencia' => $anchoRef,
            'alto_referencia' => $altoRef,
            'mesas' => $mesas,
            'pisos' => $pisos,
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
            $piso = max(1, (int) $request->input('piso', 1));

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
                'piso' => $piso,
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
                $zonas = [];
            }

            self::asegurarAreasSucursal($idSucursal);
            $orden = 0;
            foreach ($zonas as $z) {
                $codigo = !empty($z['id']) ? $z['id'] : self::slugCodigoArea($z['nombre'] ?? 'area');
                $existenteArea = DB::table('sucursal_plano_area')
                    ->where('sucursal', '=', $idSucursal)
                    ->where('codigo', '=', $codigo)
                    ->first();

                $datosArea = [
                    'nombre' => $z['nombre'] ?? $codigo,
                    'color' => $z['color'] ?? '#e9ecef',
                    'plano_x' => isset($z['x']) ? round((float) $z['x'], 2) : null,
                    'plano_y' => isset($z['y']) ? round((float) $z['y'], 2) : null,
                    'plano_ancho' => isset($z['w']) ? round((float) $z['w'], 2) : null,
                    'plano_alto' => isset($z['h']) ? round((float) $z['h'], 2) : null,
                    'orden' => $orden++,
                    'activo' => 'S',
                ];

                if ($existenteArea != null) {
                    DB::table('sucursal_plano_area')->where('id', '=', $existenteArea->id)->update($datosArea);
                } else {
                    $datosArea['sucursal'] = $idSucursal;
                    $datosArea['codigo'] = $codigo;
                    DB::table('sucursal_plano_area')->insert($datosArea);
                }
            }

            $existente = DB::table('sucursal_plano')->where('sucursal', '=', $idSucursal)->first();

            if ($existente != null) {
                DB::table('sucursal_plano')->where('sucursal', '=', $idSucursal)->update([
                    'ancho_referencia' => max(1, $anchoRef),
                    'alto_referencia' => max(1, $altoRef),
                ]);
            } else {
                DB::table('sucursal_plano')->insert([
                    'sucursal' => $idSucursal,
                    'nombre' => 'Plano principal',
                    'ancho_referencia' => max(1, $anchoRef),
                    'alto_referencia' => max(1, $altoRef),
                    'zonas_json' => null,
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

    public function guardarAreaPlano(Request $request)
    {
        try {
            if (!Schema::hasTable('sucursal_plano_area')) {
                return $this->responseAjaxServerError(
                    'Falta la tabla sucursal_plano_area. Ejecute database/scripts/17.plano_areas_config.sql o 18.actualizacion_completa_plano_pos_barra.sql en la base de datos.',
                    []
                );
            }

            $idSucursal = (int) $request->input('idSucursal');
            $id = (int) $request->input('id', 0);
            $nombre = trim((string) $request->input('nombre', ''));
            $codigo = trim((string) $request->input('codigo', ''));
            $color = trim((string) $request->input('color', '#e9ecef'));
            $colocarEnPlano = filter_var($request->input('colocar_en_plano', false), FILTER_VALIDATE_BOOLEAN);

            if ($idSucursal < 1) {
                return $this->responseAjaxServerError("La sucursal es obligatoria", []);
            }
            if ($nombre === '') {
                return $this->responseAjaxServerError("El nombre del área es obligatorio", []);
            }
            if ($codigo === '') {
                $codigo = self::slugCodigoArea($nombre);
            } else {
                $codigo = self::slugCodigoArea($codigo);
            }

            $datos = [
                'nombre' => $nombre,
                'color' => $color !== '' ? $color : '#e9ecef',
                'activo' => 'S',
            ];

            self::asegurarAreasSucursal($idSucursal);

            if ($colocarEnPlano) {
                $existentes = DB::table('sucursal_plano_area')
                    ->where('sucursal', '=', $idSucursal)
                    ->whereNotNull('plano_x')
                    ->count();
                $datos['plano_x'] = 10 + ($existentes % 4) * 18;
                $datos['plano_y'] = 10 + floor($existentes / 4) * 15;
                $datos['plano_ancho'] = 18;
                $datos['plano_alto'] = 14;
            }

            if ($id > 0) {
                $area = DB::table('sucursal_plano_area')
                    ->where('id', '=', $id)
                    ->where('sucursal', '=', $idSucursal)
                    ->first();
                if ($area == null) {
                    return $this->responseAjaxServerError("Área no encontrada", []);
                }
                DB::table('sucursal_plano_area')->where('id', '=', $id)->update($datos);
                $idArea = $id;
            } else {
                $dup = DB::table('sucursal_plano_area')
                    ->where('sucursal', '=', $idSucursal)
                    ->where('codigo', '=', $codigo)
                    ->first();
                if ($dup != null) {
                    return $this->responseAjaxServerError("Ya existe un área con el código «{$codigo}»", []);
                }
                $maxOrden = (int) DB::table('sucursal_plano_area')->where('sucursal', '=', $idSucursal)->max('orden');
                $datos['sucursal'] = $idSucursal;
                $datos['codigo'] = $codigo;
                $datos['orden'] = $maxOrden + 1;
                $idArea = DB::table('sucursal_plano_area')->insertGetId($datos);
            }

            return $this->responseAjaxSuccess("Área guardada", [
                'area' => DB::table('sucursal_plano_area')->where('id', '=', $idArea)->first(),
            ]);
        } catch (QueryException $ex) {
            if ((int) ($ex->errorInfo[1] ?? 0) === 1062) {
                return $this->responseAjaxServerError("Ya existe un área con ese código en la sucursal", []);
            }
            $this->registrarLogMesas('guardarAreaPlano', $ex->getMessage());
            return $this->responseAjaxServerError("Error al guardar el área", []);
        } catch (\Exception $ex) {
            $this->registrarLogMesas('guardarAreaPlano', $ex->getMessage());
            return $this->responseAjaxServerError("Error al guardar el área", []);
        }
    }

    private function registrarLogMesas(string $metodo, string $mensaje): void
    {
        try {
            if (Schema::hasTable('log')) {
                DB::table('log')->insertGetId([
                    'id' => null,
                    'documento' => 'MesasController',
                    'descripcion' => $metodo . ': ' . $mensaje,
                ]);
            }
        } catch (\Exception $ignored) {
        }
    }

    public function eliminarAreaPlano(Request $request)
    {
        try {
            $id = (int) $request->input('id');
            $idSucursal = (int) $request->input('idSucursal');

            if ($id < 1 || $idSucursal < 1) {
                return $this->responseAjaxServerError("Datos inválidos", []);
            }

            DB::table('sucursal_plano_area')
                ->where('id', '=', $id)
                ->where('sucursal', '=', $idSucursal)
                ->update(['activo' => 'N', 'plano_x' => null, 'plano_y' => null]);

            return $this->responseAjaxSuccess("Área eliminada", []);
        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al eliminar el área", []);
        }
    }

    public function restaurarAreasPlanoDefault(Request $request)
    {
        try {
            $idSucursal = (int) $request->input('idSucursal');
            if ($idSucursal < 1) {
                return $this->responseAjaxServerError("La sucursal es obligatoria", []);
            }

            DB::table('sucursal_plano_area')->where('sucursal', '=', $idSucursal)->delete();

            $orden = 0;
            foreach (self::getZonasPlanoDefault() as $z) {
                DB::table('sucursal_plano_area')->insert([
                    'sucursal' => $idSucursal,
                    'codigo' => $z['id'],
                    'nombre' => $z['nombre'],
                    'color' => $z['color'],
                    'plano_x' => $z['x'],
                    'plano_y' => $z['y'],
                    'plano_ancho' => $z['w'],
                    'plano_alto' => $z['h'],
                    'orden' => $orden++,
                    'activo' => 'S',
                ]);
            }

            return $this->responseAjaxSuccess("Áreas restauradas al diseño inicial", self::getPlanoDataForSucursal($idSucursal));
        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al restaurar áreas", []);
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

    public function guardarPisos(Request $request)
    {
        try {
            $idSucursal = (int) $request->input('idSucursal');
            $pisos = $request->input('pisos', []);
            if ($idSucursal < 1) {
                return $this->responseAjaxServerError("Sucursal requerida", []);
            }
            if (!is_array($pisos)) $pisos = [];
            // Sanitize
            $pisosSave = [];
            foreach ($pisos as $p) {
                $id = (int)($p['id'] ?? 0);
                $nombre = trim($p['nombre'] ?? '');
                if ($id > 0 && $nombre !== '') {
                    $pisosSave[] = ['id' => $id, 'nombre' => $nombre];
                }
            }
            if (empty($pisosSave)) {
                $pisosSave = [['id' => 1, 'nombre' => 'Área 1']];
            }
            $plano = DB::table('sucursal_plano')->where('sucursal', $idSucursal)->first();
            if ($plano) {
                DB::table('sucursal_plano')->where('sucursal', $idSucursal)->update(['pisos_json' => json_encode($pisosSave)]);
            } else {
                DB::table('sucursal_plano')->insert([
                    'sucursal' => $idSucursal,
                    'nombre' => 'Plano principal',
                    'pisos_json' => json_encode($pisosSave),
                    'activo' => 'S',
                ]);
            }
            return $this->responseAjaxSuccess("Pisos guardados", []);
        } catch (\Exception $ex) {
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'MesasController', 'descripcion' => 'guardarPisos: ' . $ex->getMessage()]);
            return $this->responseAjaxServerError("Error al guardar pisos", []);
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
            $forma = $mesa['forma'] ?? 'rectangular';
            $aplica_impuesto_servicio = isset($mesa['aplica_impuesto_servicio']) ? (int)$mesa['aplica_impuesto_servicio'] : 1;
            $formasValidas = ['rectangular', 'cuadrada', 'redonda'];
            if (!in_array($forma, $formasValidas, true)) {
                $forma = 'rectangular';
            }

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
                    'capacidad' => $capacidad,
                    'forma' => $forma,
                    'aplica_impuesto_servicio' => $aplica_impuesto_servicio,
                ];
                if (array_key_exists('plano_x', $mesa)) {
                    $updateData['plano_x'] = $mesa['plano_x'] ?? null;
                    $updateData['plano_y'] = $mesa['plano_y'] ?? null;
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
                        'forma' => $forma,
                        'estado' => SisEstadoController::getIdEstadoByCodGeneral("MESA_DISPONIBLE"),
                        'aplica_impuesto_servicio' => $aplica_impuesto_servicio,
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
        $id = (int) $request->input('id');

        try {
            if ($id < 1) {
                return $this->responseAjaxServerError("El ID de la mesa es obligatorio", []);
            }

            $mesaEntity = DB::table('mesa')->where('id', '=', $id)->first();
            if ($mesaEntity == null) {
                return $this->responseAjaxServerError("No se encontró la mesa", []);
            }

            $pendientes = DB::table('orden')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
                ->where('orden.mesa', '=', $id)
                ->where('orden.pagado', '=', 0)
                ->where(function ($q) {
                    $q->whereNull('sis_estado.cod_general')
                        ->orWhere('sis_estado.cod_general', '!=', 'ORD_ANULADA');
                })
                ->count();

            if ($pendientes > 0) {
                return $this->responseAjaxServerError(
                    "No se puede eliminar la mesa {$mesaEntity->numero_mesa}: tiene {$pendientes} orden(es) pendiente(s) de cobro en el sistema. Cóbrelas, anúlelas o quíteles la mesa antes de eliminar.",
                    []
                );
            }

            DB::beginTransaction();

            DB::table('orden')->where('mesa', '=', $id)->update(['mesa' => null]);

            if (Schema::hasTable('cuenta_barra')) {
                DB::table('cuenta_barra')->where('mesa', '=', $id)->update(['mesa' => null]);
            }

            DB::table('mesa')->where('id', '=', $id)->delete();

            DB::commit();
            return $this->responseAjaxSuccess("Mesa eliminada correctamente", "");
        } catch (QueryException $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MesasController',
                'descripcion' => 'eliminarMesa: ' . $ex->getMessage()
            ]);
            return $this->responseAjaxServerError(
                "No se pudo eliminar la mesa: tiene registros vinculados (órdenes u otros). " . $ex->getMessage(),
                []
            );
        } catch (\Exception $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MesasController',
                'descripcion' => 'eliminarMesa: ' . $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error al eliminar la mesa: " . $ex->getMessage(), []);
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
