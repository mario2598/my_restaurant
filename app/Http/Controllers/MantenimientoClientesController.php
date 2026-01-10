<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MantenimientoClientesController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantCli";

    public function __construct() {}
    public function index()
    {
        try {
            $clientes = DB::table('cliente')
                ->leftJoin('cliente_fe_info', 'cliente.id', '=', 'cliente_fe_info.cliente_id')
                ->where('cliente.estado', 'like', 'A')
                ->select(
                    'cliente.*',
                    'cliente_fe_info.codigo_actividad',
                    'cliente_fe_info.tipo_identificacion',
                    'cliente_fe_info.nombre_comercial',
                    'cliente_fe_info.direccion'
                )
                ->get();
            $data = [
                'menus' => $this->cargarMenus(),
                'clientes' => $clientes,
                'panel_configuraciones' => $this->getPanelConfiguraciones()
            ];
            return view('mant.clientes', compact('data'));
        } catch (QueryException $ex) {
            Log::error('Error en MantenimientoClientesController@index: ' . $ex->getMessage()); 

            $this->setError('Error', 'Error al cargar la página de clientes. Por favor, contacte al administrador.');
            return redirect('/');
        } catch (\Exception $ex) {
            Log::error('Error inesperado en MantenimientoClientesController@index: ' . $ex->getMessage());
      
            $this->setError('Error', 'Error inesperado al cargar la página de clientes.');
            return redirect('/');
        }
    }

    /**
     * Guarda o actualiza un Cliente.
     */
    public function guardarCliente(Request $request)
    {
        $validar = $this->validarCliente($request);
        if (!$validar['estado']) {
            return $this->responseAjaxServerError($validar['mensaje'], []);
        }

        $correo = $request->input('mdl_generico_ipt_correo');
        $nombre = $request->input('mdl_generico_ipt_nombre');
        $tel = $request->input('mdl_generico_ipt_tel');
        $ubicacion = $request->input('mdl_generico_ipt_ubicacion');
        $id = $request->input('mdl_generico_ipt_id');
        $apellidos = $request->input('mdl_generico_ipt_apellidos');

        try {
            DB::beginTransaction();
            if ($id == '-1' || $id == null || $this->isEmpty($id)) {
                $id = DB::table('cliente')->insertGetId([
                    'id' => null,
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'ubicacion' => $ubicacion,
                    'telefono' => $tel,
                    'estado' => 'A',
                    'fecha_registro' => now(),
                    'contra' => '123456',
                    'apellidos' => $apellidos
                ]);
            } else {
                $clienteAux = DB::table('cliente')->where('id', '=', $id)->get()->first();
                if ($clienteAux == null) {
                    DB::rollBack();
                    return $this->responseAjaxServerError("El cliente no existe.", []);
                }

                DB::table('cliente')
                    ->where('id', '=', $id)
                    ->update(['nombre' => $nombre, 'correo' => $correo, 'ubicacion' => $ubicacion, 'telefono' => $tel, 'apellidos' => $apellidos]);
            }
            DB::commit();
            return $this->responseAjaxSuccess("El cliente se guardó correctamente.", $id);
        } catch (QueryException $ex) {
            DB::rollBack();
            Log::error('Error en guardarCliente: ' . $ex->getMessage());
            return $this->responseAjaxServerError("Error al guardar el cliente: " . $ex->getMessage(), []);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Error inesperado en guardarCliente: ' . $ex->getMessage());
            return $this->responseAjaxServerError("Error inesperado al guardar el cliente: " . $ex->getMessage(), []);
        }
    }

    /**
     * Elimina un Cliente.
     */
    public function eliminarCliente(Request $request)
    {
        $id = $request->input('cliente_id') ?? $request->input('idGenericoEliminar');

        if ($id == null || $id == '' || $id < 1) {
            return $this->responseAjaxServerError('Identificador inválido.', []);
        }

        try {
            DB::beginTransaction();
            $cliente = DB::table('cliente')->where('id', '=', $id)->get()->first();
            if ($cliente == null) {
                return $this->responseAjaxServerError('No existe el cliente a eliminar.', []);
            } else {
                // Eliminar información de facturación electrónica del cliente
                DB::table('cliente_fe_info')
                    ->where('cliente_id', '=', $id)
                    ->delete();

                // Marcar cliente como inactivo
                DB::table('cliente')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            return $this->responseAjaxSuccess('El cliente y su información de facturación electrónica se eliminaron correctamente.', []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Ocurrió un error eliminando el cliente.', []);
        }
    }

    public function validarCliente(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

        if ($this->isNull($r->input('mdl_generico_ipt_nombre')) || $this->isEmpty($r->input('mdl_generico_ipt_nombre'))) {
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        /*if($this->isNull($r->input('mdl_generico_ipt_tel')) || $this->isEmpty($r->input('mdl_generico_ipt_tel'))){
            if(!$esPrimero){
                $requeridos .= ",";
            } 
            $requeridos .= "Teléfono ";
            $valido = false;
            $esPrimero = false;
        }*/

        $requeridos .= "] ";
        if (!$valido) {
            return $this->responseAjaxServerError("Campos Requeridos: " . $requeridos, []);
        }

        if (!$this->isLengthMinor($r->input('mdl_generico_ipt_nombre'), 50)) {
            return $this->responseAjaxServerError("El nombre del cliente es de máximo 50 caracteres.", []);
        }

        if (!$this->isNull($r->input('mdl_generico_ipt_apellidos')) && !$this->isLengthMinor($r->input('mdl_generico_ipt_apellidos'), 100)) {
            return $this->responseAjaxServerError("Los apellidos son de máximo 100 caracteres.", []);
        }

        if (!$this->isLengthMinor($r->input('mdl_generico_ipt_tel'), 14)) {
            return $this->responseAjaxServerError("El teléfono es de máximo 14 cáracteres.", []);
        }
        if (!$this->isNull($r->input('mdl_generico_ipt_correo')) && !$this->isLengthMinor($r->input('mdl_generico_ipt_correo'), 100)) {
            return $this->responseAjaxServerError("El correo es de máximo 100 cáracteres.", []);
        }
        if (!$this->isNull($r->input('mdl_generico_ipt_ubicacion')) && !$this->isLengthMinor($r->input('mdl_generico_ipt_ubicacion'), 300)) {
            return $this->responseAjaxServerError("La ubicación es de máximo 300 cáracteres.", []);
        }
        // Validar que el correo no exista (si se proporciona)
        $correo = $r->input('mdl_generico_ipt_correo');
        if (!$this->isNull($correo) && !$this->isEmpty($correo)) {
            $clienteExistente = DB::table('cliente')
                ->where('correo', $correo)
                ->where('estado', 'A');

            // Si es edición, excluir el cliente actual
            $id = $r->input('mdl_generico_ipt_id');
            if ($id != '-1' && !$this->isEmpty($id)) {
                $clienteExistente->where('id', '!=', $id);
            }

            if ($clienteExistente->exists()) {
                return $this->responseAjaxServerError("El correo electrónico ya está registrado en el sistema.", []);
            }
        }

        // Validar que el teléfono no exista (si se proporciona)
        $telefono = $r->input('mdl_generico_ipt_tel');
        if (!$this->isNull($telefono) && !$this->isEmpty($telefono)) {
            $clienteExistente = DB::table('cliente')
                ->where('telefono', $telefono)
                ->where('estado', 'A');

            // Si es edición, excluir el cliente actual
            $id = $r->input('mdl_generico_ipt_id');
            if ($id != '-1' && !$this->isEmpty($id)) {
                $clienteExistente->where('id', '!=', $id);
            }

            if ($clienteExistente->exists()) {
                return $this->responseAjaxServerError("El teléfono ya está registrado en el sistema.", []);
            }
        }

        return $this->responseAjaxSuccess("", []);
    }

    /**
     * Obtiene los clientes para DataTables con paginación AJAX
     */
    public function obtenerClientesAjax(Request $request)
    {
        try {
            $draw = $request->input('draw');
            $start = $request->input('start');
            $length = $request->input('length');
            $searchValue = $request->input('search.value');
            $orderColumn = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');

            // Columnas de la tabla
            $columns = ['nombre', 'apellidos', 'telefono', 'correo', 'ubicacion', 'codigo_actividad'];

            // Query base
            $query = DB::table('cliente')
                ->leftJoin('cliente_fe_info', 'cliente.id', '=', 'cliente_fe_info.cliente_id')
                ->where('cliente.estado', 'A')
                ->select(
                    'cliente.id',
                    'cliente.nombre',
                    'cliente.apellidos',
                    'cliente.telefono',
                    'cliente.correo',
                    'cliente.ubicacion',
                    'cliente_fe_info.codigo_actividad'
                );

            // Aplicar búsqueda
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('cliente.nombre', 'like', '%' . $searchValue . '%')
                        ->orWhere('cliente.apellidos', 'like', '%' . $searchValue . '%')
                        ->orWhere('cliente.telefono', 'like', '%' . $searchValue . '%')
                        ->orWhere('cliente.correo', 'like', '%' . $searchValue . '%')
                        ->orWhere('cliente.ubicacion', 'like', '%' . $searchValue . '%');
                });
            }

            // Contar total de registros
            $totalRecords = DB::table('cliente')->where('estado', 'A')->count();

            // Contar registros filtrados
            $filteredRecords = $query->count();

            // Aplicar ordenamiento
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            } else {
                $query->orderBy('cliente.nombre', 'asc');
            }

            // Aplicar paginación
            $clientes = $query->skip($start)->take($length)->get();

            // Formatear datos para DataTables
            $data = [];
            foreach ($clientes as $cliente) {
                $data[] = [
                    'nombre' => $cliente->nombre ?? '',
                    'apellidos' => $cliente->apellidos ?? '',
                    'telefono' => $cliente->telefono ?? '',
                    'correo' => $cliente->correo ?? '',
                    'ubicacion' => $cliente->ubicacion ?? '',
                    'fe_configurado' => $cliente->codigo_actividad ? 'Sí' : 'No',
                    'fe_badge' => $cliente->codigo_actividad ?
                        '<span class="badge badge-success">Sí</span>' :
                        '<span class="badge badge-warning">No</span>',
                    'acciones' => $cliente->id
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error al cargar los datos'
            ]);
        }
    }

    /**
     * Obtiene un cliente específico por ID
     */
    public function obtenerCliente(Request $request)
    {
        $clienteId = $request->input('cliente_id');

        if (!$clienteId) {
            return $this->responseAjaxServerError('ID de cliente requerido', []);
        }

        try {
            $cliente = DB::table('cliente')
                ->where('id', $clienteId)
                ->where('estado', 'A')
                ->first();

            if (!$cliente) {
                return $this->responseAjaxServerError('Cliente no encontrado', []);
            }

            $cliente->info_fe = DB::table('cliente_fe_info')
                ->where('cliente_id', $clienteId)
                ->first();


            return $this->responseAjaxSuccess('Cliente obtenido correctamente', $cliente);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Error al obtener el cliente', []);
        }
    }

    /**
     * Obtiene la información de facturación electrónica de un cliente
     */
    public function obtenerInfoFECliente(Request $request)
    {
        $clienteId = $request->input('cliente_id');

        if (!$clienteId) {
            return $this->responseAjaxServerError('ID de cliente requerido', []);
        }

        try {
            $infoFE = DB::table('cliente_fe_info')
                ->join('cliente', 'cliente_fe_info.cliente_id', '=', 'cliente.id')
                ->where('cliente_id', $clienteId)
                ->select(
                    'cliente_fe_info.*',
                    'cliente.correo'
                )
                ->first();

            return $this->responseAjaxSuccess('Información FE obtenida correctamente', $infoFE);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Error al obtener la información de facturación electrónica', []);
        }
    }

    /**
     * Guarda o actualiza la información de facturación electrónica de un cliente
     */
    public function guardarInfoFECliente(Request $request)
    {
        $clienteId = $request->input('cliente_id');
        $codigoActividad = trim($request->input('codigo_actividad'));
        $tipoIdentificacion = trim($request->input('tipo_identificacion'));
        $identificacion = trim($request->input('identificacion'));
        $nombreComercial = trim($request->input('nombre_comercial'));
        $direccion = trim($request->input('direccion'));

        if (!$clienteId) {
            return $this->responseAjaxServerError('ID de cliente requerido', []);
        }

        // Validar que el cliente existe
        $cliente = DB::table('cliente')->where('id', $clienteId)->where('estado', 'A')->first();
        if (!$cliente) {
            return $this->responseAjaxServerError('Cliente no encontrado', []);
        }

        try {
            DB::beginTransaction();

            // Verificar si ya existe información FE para este cliente
            $infoExistente = DB::table('cliente_fe_info')
                ->where('cliente_id', $clienteId)
                ->first();

            $dataFE = [
                'cliente_id' => $clienteId,
                'codigo_actividad' => $codigoActividad ?: '722003',
                'tipo_identificacion' => $tipoIdentificacion ?: '01',
                'nombre_comercial' => $nombreComercial,
                'direccion' => $direccion,
                'identificacion' => $identificacion,
                'fecha_modificacion' => now()
            ];

            if ($infoExistente) {
                // Actualizar información existente
                DB::table('cliente_fe_info')
                    ->where('cliente_id', $clienteId)
                    ->update($dataFE);
            } else {
                // Crear nueva información
                $dataFE['fecha_creacion'] = now();
                DB::table('cliente_fe_info')->insert($dataFE);
            }

            DB::commit();

            return $this->responseAjaxSuccess('Información de facturación electrónica guardada correctamente', []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError('Error al guardar la información de facturación electrónica', []);
        }
    }
}
