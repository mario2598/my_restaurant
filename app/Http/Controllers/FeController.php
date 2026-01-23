<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;
use App\Services\FactuXService;
use Illuminate\Support\Facades\Log;

class FeController extends Controller
{
    use SpaceUtil;
    
    protected $factuXService;
    
    public function __construct(FactuXService $factuXService)
    {
        setlocale(LC_ALL, "es_ES");
        $this->factuXService = $factuXService;
    }

    public function index()
    {
    }

    public function goFacturasFe()
    {
        if (!$this->validarSesion("fe_fes")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("fe.facturas", compact("data"));
    }

    /**
     * Obtiene las unidades de medida desde la API de FactuX o retorna lista estática
     */
    public function obtenerUnidadesMedida()
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        // Lista estática de unidades de medida válidas según documentación de FactuX/Hacienda
        $unidadesEstaticas = [
            ['codigo' => 'Sp', 'nombre' => 'Servicio Profesional'],
            ['codigo' => 'M', 'nombre' => 'Metro'],
            ['codigo' => 'Kg', 'nombre' => 'Kilogramo'],
            ['codigo' => 'h', 'nombre' => 'Hora'],
            ['codigo' => 'Unid', 'nombre' => 'Unidad'],
            ['codigo' => 'Al', 'nombre' => 'Alcance'],
            ['codigo' => 'Alc', 'nombre' => 'Alcance'],
            ['codigo' => 'Cm', 'nombre' => 'Centímetro'],
            ['codigo' => 'I', 'nombre' => 'Pulgada'],
            ['codigo' => 'Os', 'nombre' => 'Onza'],
            ['codigo' => 'Spe', 'nombre' => 'Especial'],
            ['codigo' => 'St', 'nombre' => 'Set'],
            ['codigo' => 'D', 'nombre' => 'Día'],
            ['codigo' => 'cm', 'nombre' => 'Centímetro'],
            ['codigo' => 'M2', 'nombre' => 'Metro Cuadrado'],
            ['codigo' => 'M3', 'nombre' => 'Metro Cúbico'],
            ['codigo' => 'Oz', 'nombre' => 'Onza'],
            ['codigo' => 'Lt', 'nombre' => 'Litro'],
            ['codigo' => 'g', 'nombre' => 'Gramo'],
            ['codigo' => 'ml', 'nombre' => 'Mililitro'],
        ];

        try {
            // Intentar obtener desde la API (si el endpoint existe)
            $endpoint = '/api/v1/unidades-medida';
            $resultado = $this->factuXService->get($endpoint);

            if ($resultado['exito']) {
                $unidadesData = $resultado['respuesta'];
                
                if ($unidadesData !== null) {
                    // Si la respuesta es un array, retornarlo directamente
                    // Si es un objeto con una propiedad que contiene el array, extraerlo
                    if (isset($unidadesData['data'])) {
                        $unidades = $unidadesData['data'];
                    } elseif (isset($unidadesData['unidades'])) {
                        $unidades = $unidadesData['unidades'];
                    } elseif (is_array($unidadesData)) {
                        $unidades = $unidadesData;
                    } else {
                        $unidades = [];
                    }

                    // Si se obtuvieron unidades de la API, retornarlas
                    if (!empty($unidades)) {
                        return $this->responseAjaxSuccess("Unidades de medida obtenidas correctamente.", $unidades);
                    }
                }
            }
            
            // Si la API no está disponible o retorna error, usar lista estática
            // Esto es común ya que el endpoint puede no existir en todas las versiones de la API
            return $this->responseAjaxSuccess("Unidades de medida obtenidas correctamente.", $unidadesEstaticas);

        } catch (\Exception $ex) {
            // En caso de excepción, retornar lista estática como fallback
            return $this->responseAjaxSuccess("Unidades de medida obtenidas correctamente.", $unidadesEstaticas);
        }
    }

  

    public function filtrarFacturas(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        $filtro = $request->input('filtro');

        $filtroSucursal =  $filtro['sucursal'];
        $hasta = $filtro['hasta'];
        $desde = $filtro['desde'];

        $ordenes = DB::table('orden')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'orden.sucursal')
            ->join('fe_info', 'fe_info.orden', '=', 'orden.id')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'fe_info.estado')
            ->leftjoin('sis_estado as estado_hacienda_tbl', 'estado_hacienda_tbl.id', '=', 'fe_info.estado_hacienda')
            ->select(
                'orden.*',
                'sis_estado.nombre as estadoFe',
                'sis_estado.cod_general',
                'sucursal.descripcion as nombreSucursal',
                'fe_info.cedula as cedFe',
                'fe_info.id as idFe',
                'fe_info.num_comprobante as comprobanteFe',
                'fe_info.nombre as nombreFe',
                'fe_info.num_comprobante as num_comprobanteFe',
                'fe_info.correo as correoFe',
                'fe_info.estado_hacienda',
                'estado_hacienda_tbl.nombre as estadoHaciendaNombre',
                'estado_hacienda_tbl.cod_general as estadoHaciendaCod',
                'fe_info.url_consulta_estado'
            );

        $ordenes = $ordenes->where('orden.factura_electronica', '=',  'S');
        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $ordenes = $ordenes->where('orden.sucursal', '=',  $filtroSucursal);
        }

        if (!$this->isNull($desde)) {
            $ordenes = $ordenes->where('orden.fecha_inicio', '>=', $desde);
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $ordenes = $ordenes->where('orden.fecha_inicio', '<', $mod_date);
        }

        $ordenes = $ordenes->orderBy('orden.fecha_inicio', 'DESC')->get();

        foreach ($ordenes as $o) {
            $phpdate = strtotime($o->fecha_inicio);
            $date = date("d-m-Y", strtotime($o->fecha_inicio));
    
            $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
            $fechaAux .= ' - ' . date("g:i a", $phpdate);
            $o->fechaFormat =  $fechaAux;
            $o->idOrdenEnc = encrypt($o->id);
        }
        return  $this->responseAjaxSuccess("", $ordenes);
    }

    public function enviarFe(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idOrden = $request->input("idOrden");
        $idInfoFe = $request->input("idInfoFe");
        $numComprobante =  $request->input("numComprobante");
      
        if ($idOrden == null || $idOrden == 0) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        if ($idInfoFe == null || $idInfoFe == 0) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        $orden = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.cod_general')
            ->where('orden.id', '=', $idOrden)->get()->first();


        if ($orden == null) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        if ($numComprobante == null || $numComprobante == '') {
            return $this->responseAjaxServerError("Debe ingresar el número de comprobante de hacienda", []);
        }

        if ($orden->cod_general == 'ORD_ANULADA') {
            return $this->responseAjaxServerError("La orden se encuentra anulada", []);
        }

        try {
            DB::beginTransaction();

            DB::table('fe_info')
                ->where('id', '=', $idInfoFe)
                ->update(['estado' =>  SisEstadoController::getIdEstadoByCodGeneral('FE_ORDEN_ENVIADA'),
                'num_comprobante' => $numComprobante]);

         
            DB::commit();

            return $this->responseAjaxSuccess("Se marcó como enviada la factura electrónica.", $idOrden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

    public function cargarDatosFEProducto(Request $request)
    {
        Log::info("=== DEBUG cargarDatosFEProducto ===");
        $idProducto = $request->input("idProducto");
        $tipoProducto = $request->input("tipoProducto");
        Log::info("idProducto: " . $idProducto);
        Log::info("tipoProducto: " . $tipoProducto);

        $codigoProducto = null;
       
        if( $tipoProducto == 'MENU'){
            $producto = ProductosMenuController::getById($idProducto);
            Log::info("Producto MENU encontrado:", ['producto' => $producto]);
            if ($producto) {
                $codigoProducto = $producto->codigo;
                Log::info("codigoProducto: " . $codigoProducto);
            }
        }else if($tipoProducto == 'EXTERNO'){
            $producto = ProductosExternosController::getById($idProducto);
            Log::info("Producto EXTERNO encontrado:", ['producto' => $producto]);
            if ($producto) {
                $codigoProducto = $producto->codigo_barra;
                Log::info("codigoProducto: " . $codigoProducto);
            }
        }else{
            Log::error("Tipo de producto no válido: " . $tipoProducto);
            return $this->responseAjaxServerError("Tipo de producto no válido.", []);
        }
        
        if ($producto == null) {
            Log::error("Producto no encontrado para idProducto: " . $idProducto);
            return $this->responseAjaxServerError("Producto no encontrado.", []);
        } 

        $impuesto = DB::table('impuesto')
        ->where('id', '=', $producto->impuesto)
        ->get()->first();

        Log::info("Impuesto encontrado:", ['impuesto' => $impuesto, 'impuesto_id' => $producto->impuesto ?? 'null']);

        if($impuesto == null){
            Log::error("Impuesto no encontrado para producto.impuesto: " . ($producto->impuesto ?? 'null'));
            return $this->responseAjaxServerError("Impuesto no encontrado.", []);
        }

        $info = DB::table('producto_fe_info')
            ->where('codigo_producto', '=', $codigoProducto)
            ->where('tipo_producto', '=', $tipoProducto)
            ->get()->first();

        Log::info("Info FE encontrada:", ['info' => $info, 'codigoProducto' => $codigoProducto]);

        if ($info == null) {
            Log::info("Creando nueva entrada en producto_fe_info");
            DB::table('producto_fe_info')->insert([
                'codigo_producto' => $codigoProducto,
                'tipo_producto' => $tipoProducto,
                'codigo_cabys' => "",
                'tarifa_impuesto' => $impuesto->impuesto,
                'unidad_medida' => "",
                'tipo_codigo' => "01",
                'exento' => "N",
                'impuesto_incluido' => "S"
            ]);

            $info = DB::table('producto_fe_info')
            ->where('codigo_producto', '=', $codigoProducto)
            ->where('tipo_producto', '=', $tipoProducto)
            ->get()->first();
            Log::info("Nueva info FE creada:", ['info' => $info]);
        }
        $info->descripcionDetalle = $producto->nombre;
        
        Log::info("Respuesta final:", ['info' => $info]);
 
        return $this->responseAjaxSuccess("", $info);
    }

    private function validarDatosFE(Request $request){
        $data = $request->input('data');
        $codigoCabys = $data['codigo_cabys'];
        $unidadMedida = $data['unidad_medida'];
        $tipoCodigo = $data['tipo_codigo'];
        $descripcion = $data['descripcion'];

        if($this->isNull($codigoCabys)){
            return $this->responseAjaxServerError("El código CABYS es obligatorio.", []);
        }

        if($this->isNull($unidadMedida)){
            return $this->responseAjaxServerError("La unidad de medida es obligatoria.", []);
        }

        if($this->isNull($tipoCodigo)){
            return $this->responseAjaxServerError("El tipo de código es obligatorio.", []);
        }

        if($this->isNull($descripcion)){
            return $this->responseAjaxServerError("La descripción es obligatoria.", []);
        }

        return true;
    }   

    public function guardarConfigFE(Request $request)
    {
        $idProducto = $request->input('idProducto');
        $tipoProducto = $request->input('tipoProducto');
        $data = $request->input('data');

        $validar = $this->validarDatosFE($request);
        if(!$validar){
            return $this->responseAjaxServerError("Datos inválidos.", []);
        }

        $codigoProducto = null;
       
        if( $tipoProducto == 'MENU'){
            $producto = ProductosMenuController::getById($idProducto);
            $codigoProducto = $producto->codigo;
        }else if($tipoProducto == 'EXTERNO'){
            $producto = ProductosExternosController::getById($idProducto);
            $codigoProducto = $producto->codigo_barra;
        }else{
            return $this->responseAjaxServerError("Tipo de producto no válido.", []);
        }
        
        if ($producto == null) {
            return $this->responseAjaxServerError("Producto no encontrado.", []);
        } 

        $dataFe = DB::table('producto_fe_info')
            ->where('codigo_producto', '=', $codigoProducto)
            ->where('tipo_producto', '=', $tipoProducto)
            ->get()->first();

        $nuevo = ($dataFe == null);

        try {
            DB::beginTransaction();
            if ($nuevo) {
                DB::table('producto_fe_info')
                    ->insertGetId([
                        'id' => null,
                        'codigo_producto' => $codigoProducto,
                        'tipo_producto' => $tipoProducto,
                        'codigo_cabys' => $data['codigo_cabys'],
                        'tarifa_impuesto' => "",
                        'unidad_medida' => $data['unidad_medida'],
                        'tipo_codigo' => $data['tipo_codigo'],
                        'exento' => 'N',
                        'impuesto_incluido' => 'S'
                    ]);
            } else {
                DB::table('producto_fe_info')
                    ->where('id', '=', $dataFe->id)
                    ->update([
                        'codigo_cabys' => $data['codigo_cabys'],
                        'unidad_medida' => $data['unidad_medida'],
                        'tipo_codigo' => $data['tipo_codigo']
                    ]);
            }

            DB::commit();
            return $this->responseAjaxSuccess();
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    /**
     * Genera y envía una factura electrónica a Hacienda
     */
    public function enviarFacturaHacienda(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idInfoFe = $request->input("idInfoFe");
      
        if ($idInfoFe == null || $idInfoFe == 0) {
            return $this->responseAjaxServerError("ID de información FE inválido", []);
        }

        try {
            // Obtener información FE
            $infoFe = DB::table('fe_info')->where('id', $idInfoFe)->first();
            
            if ($infoFe == null) {
                return $this->responseAjaxServerError("Información FE no encontrada", []);
            }

            if ($infoFe->id_pago == null) {
                return $this->responseAjaxServerError("No hay pago asociado a esta factura", []);
            }

            // Obtener información del pago
            $pago = $this->obtenerDatosPago($infoFe->id_pago);
            
            if ($pago == null) {
                return $this->responseAjaxServerError("Pago no encontrado", []);
            }

            // Validar que la orden no esté anulada
            $orden = DB::table('orden')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
                ->select('orden.*', 'sis_estado.cod_general')
                ->where('orden.id', '=', $pago->orden)
                ->first();

            if ($orden && $orden->cod_general == 'ORD_ANULADA') {
                return $this->responseAjaxServerError("La orden se encuentra anulada", []);
            }

            // Construir el JSON para enviar a Hacienda
            $comprobanteJson = $this->construirComprobanteElectronico($pago, $infoFe);

            // Validar que todos los productos tengan información FE
            $validacion = $this->validarProductosFE($pago);
            if (!$validacion['valido']) {
                return $this->responseAjaxServerError($validacion['mensaje'], $validacion['productos_faltantes']);
            }

            // Enviar a la API de Hacienda
            $resultado = $this->enviarAHacienda($comprobanteJson);

            if ($resultado['exito']) {
                // Actualizar estado en la BD
                DB::beginTransaction();
                
                // Obtener la clave del comprobante de la respuesta
                $claveComprobante = $resultado['clave'] ?? null;
                
                DB::table('fe_info')
                    ->where('id', '=', $idInfoFe)
                    ->update([
                        'estado' => SisEstadoController::getIdEstadoByCodGeneral('FE_ORDEN_ENVIADA'),
                        'num_comprobante' => $claveComprobante
                    ]);

                DB::commit();

                return $this->responseAjaxSuccess("Factura electrónica enviada exitosamente a FactuX.", [
                    'clave' => $claveComprobante,
                    'respuesta' => $resultado['respuesta'] ?? null
                ]);
            } else {
                return $this->responseAjaxServerError("Error al enviar a FactuX: " . $resultado['mensaje'], []);
            }

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al procesar la factura: " . $ex->getMessage(), []);
        }
    }

    /**
     * Obtiene todos los datos del pago necesarios para la FE
     */
    private function obtenerDatosPago($idPago)
    {
        $pago = DB::table('pago_orden')
            ->leftJoin('orden', 'orden.id', '=', 'pago_orden.orden')
            ->leftJoin('sucursal', 'sucursal.id', '=', 'orden.sucursal')
            ->leftJoin('cliente', 'cliente.id', '=', 'pago_orden.cliente')
            ->leftJoin('cliente_fe_info', 'cliente_fe_info.cliente_id', '=', 'cliente.id')
            ->select(
                'pago_orden.*',
                'orden.numero_orden',
                'orden.fecha_inicio',
                'sucursal.id as sucursal_id',
                'sucursal.descripcion as nombreSucursal',
                'sucursal.nombre_factura',
                'sucursal.cedula_factura',
                'sucursal.correo_factura',
                'sucursal.id_emisor_fe',
                'sucursal.id_sucursal_fe',
                'cliente.telefono as telefono_cliente',
                'cliente.correo as correo_cliente',
                'cliente_fe_info.tipo_identificacion as tipo_identificacion_cliente',
                'cliente_fe_info.direccion as direccion_cliente',
                'cliente_fe_info.codigo_actividad as codigo_actividad_cliente',
                'cliente_fe_info.identificacion as identificacion_cliente',
                'cliente_fe_info.nombre_comercial as nombre_comercial_cliente'
            )
            ->where('pago_orden.id', '=', $idPago)
            ->first();

        if ($pago) {
            // Obtener detalles del pago
            $pago->detalles = DB::table('detalle_pago_orden')
                ->leftJoin('detalle_orden', 'detalle_orden.id', '=', 'detalle_pago_orden.detalle_orden')
                ->select(
                    'detalle_pago_orden.*',
                    'detalle_orden.codigo_producto',
                    'detalle_orden.nombre_producto',
                    'detalle_orden.tipo_producto',
                    'detalle_orden.precio_unidad'
                )
                ->where('detalle_pago_orden.pago_orden', '=', $idPago)
                ->get();
        }

        return $pago;
    }

    /**
     * Valida que todos los productos tengan información FE completa
     */
    private function validarProductosFE($pago)
    {
        $productosFaltantes = [];
        
        foreach ($pago->detalles as $detalle) {
            // Si es una línea de envío u otra sin producto, saltarla
            if (empty($detalle->codigo_producto)) {
                continue;
            }

            $codigoProducto = $detalle->codigo_producto;
            $tipoProducto = $this->obtenerTipoProducto($detalle->tipo_producto);
            
            if ($tipoProducto == null) {
                continue; // Productos promocionales u otros
            }

            $infoFe = DB::table('producto_fe_info')
                ->where('codigo_producto', $codigoProducto)
                ->where('tipo_producto', $tipoProducto)
                ->first();

            if ($infoFe == null) {
                $productosFaltantes[] = [
                    'codigo' => $codigoProducto,
                    'nombre' => $detalle->dsc_linea ?? $detalle->nombre_producto,
                    'motivo' => 'No tiene configuración FE'
                ];
                continue;
            }

            $motivos = [];
            
            if (empty($infoFe->codigo_cabys)) {
                $motivos[] = 'Falta código CABYS';
            }
            
            if (empty($infoFe->unidad_medida)) {
                $motivos[] = 'Falta unidad de medida';
            }
            
            // Validar que tenga el ID del sistema externo (identificacion)
            // Por mientras no es obligatorio, se usará el ID de producto_fe_info como fallback
            // if (empty($infoFe->identificacion)) {
            //     $motivos[] = 'Falta ID del sistema externo (identificacion)';
            // }
            
            if (count($motivos) > 0) {
                $productosFaltantes[] = [
                    'codigo' => $codigoProducto,
                    'nombre' => $detalle->dsc_linea ?? $detalle->nombre_producto,
                    'motivo' => implode(', ', $motivos)
                ];
            }
        }

        if (count($productosFaltantes) > 0) {
            return [
                'valido' => false,
                'mensaje' => 'Algunos productos no tienen configuración FE completa',
                'productos_faltantes' => $productosFaltantes
            ];
        }

        return ['valido' => true];
    }

    /**
     * Convierte el tipo de producto interno al tipo FE
     */
    private function obtenerTipoProducto($tipoInterno)
    {
        switch ($tipoInterno) {
            case 'R': // Restaurante/Menú
                return 'MENU';
            case 'E': // Externo
                return 'EXTERNO';
            default:
                return null;
        }
    }

    /**
     * Construye el JSON del comprobante electrónico en formato FactuX
     */
    private function construirComprobanteElectronico($pago, $infoFe)
    {
        // Obtener código de actividad del cliente
        $codigoActividad = $pago->codigo_actividad_cliente ?? '722003';
        
        // Obtener información del usuario actual (si está disponible)
        $usuario = session('usuario');
        $emailUsuario = $usuario['correo'] ?? $pago->correo_factura ?? '';
        $nombreUsuario = ($usuario['nombre'] ?? '') . ' ' . ($usuario['ape1'] ?? '');

        // Construir lista de detalles
        $listaDetalles = $this->construirListaDetalleComprobantes($pago);
        
        // Calcular resumen
        $resumen = $this->calcularResumenFactuX($pago, $listaDetalles);
        
        // Obtener medio de pago
        $medioPago = $this->obtenerMedioPagoFactuX($pago);

        // Construir estructura del comprobante en formato FactuX
        $comprobante = [
            'emisor' => ['id' => 1],
            'sucursal' => ['id' => 1],
            'empresa' => ['id' => 1],
            'codigoActividad' => $codigoActividad,
            'tipoCom' => '04', // 04 = Factura electrónica
            'condicionVenta' => '01', // 01 = Contado
            'plazoCredito' => '0',
            'montoTarjeta' => $pago->monto_tarjeta ?? 0,
            'montoTransferencia' => $pago->monto_sinpe ?? 0,
            'montoEfectivo' => $pago->monto_efectivo ?? 0,
            'receptor' => new \stdClass(), // Objeto vacío para la prueba
            'listaDetalleComprobantes' => $listaDetalles,
            
            // Resumen
            'totalServGravadosResumen' => $resumen['totalServGravados'],
            'totalServExentosResumen' => $resumen['totalServExentos'],
            'totalServExoneradosResumen' => 0,
            'totalMercanciasGravadasResumen' => $resumen['totalMercanciasGravadas'],
            'totalMercanciasExentasResumen' => $resumen['totalMercanciasExentas'],
            'totalMercanciasExoneradasResumen' => 0,
            'totalGravadoResumen' => $resumen['totalGravado'],
            'totalExentoResumen' => $resumen['totalExento'],
            'totalExoneradoResumen' => 0,
            'totalVentaResumen' => $resumen['totalVenta'],
            'totalDescuentosResumen' => $resumen['totalDescuentos'],
            'totalVentaNetaResumen' => $resumen['totalVentaNeta'],
            'montoImpuestoResumen' => $resumen['montoImpuesto'],
            'totalIvaDevueltoResumen' => 0,
            'totalOtrosCargosResumen' => 0,
            'totalComprobanteResumen' => $resumen['totalComprobante'],
            'totalImpAsumidoEmisor' => 0,
            
            // Medios de pago
            'medioPago1' => $medioPago['medioPago1'],
            'medioPago2' => '',
            'medioPago3' => '',
            'medioPago4' => '',
            
            // Otros campos
            'codigoMonedaResumen' => 'CRC',
            'tipoCambioResumen' => 1,
            'emailUsuario' => $emailUsuario,
            'nombreUsuario' => trim($nombreUsuario),
            'documentoReferencia' => ''
        ];

        return $comprobante;
    }

    /**
     * Genera la clave numérica de 50 dígitos según especificaciones de Hacienda
     */
    private function generarClaveNumerica($pago, $fechaEmision)
    {
        // Estructura: País(3) + Día(2) + Mes(2) + Año(2) + Cédula(12) + Consecutivo(20) + Situación(1) + Código Seguridad(8)
        
        $pais = '506'; // Costa Rica
        $dia = $fechaEmision->format('d');
        $mes = $fechaEmision->format('m');
        $anio = $fechaEmision->format('y');
        
        $cedula = str_pad(preg_replace('/[^0-9]/', '', $pago->cedula_factura ?? '0'), 12, '0', STR_PAD_LEFT);
        
        // Consecutivo: Sucursal(3) + Terminal(5) + Tipo(2) + Consecutivo(10)
        $sucursal = str_pad($pago->sucursal_id ?? '1', 3, '0', STR_PAD_LEFT);
        $terminal = '00001';
        $tipoComprobante = '01'; // 01 = Factura Electrónica
        $consecutivo = str_pad($pago->id, 10, '0', STR_PAD_LEFT); // Usamos el ID del pago
        
        $situacion = '1'; // 1 = Normal
        $codigoSeguridad = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        $clave = $pais . $dia . $mes . $anio . $cedula . $sucursal . $terminal . $tipoComprobante . $consecutivo . $situacion . $codigoSeguridad;
        
        return $clave;
    }

    /**
     * Construye la lista de detalles en formato FactuX
     */
    private function construirListaDetalleComprobantes($pago)
    {
        $detalles = [];
        $numeroLinea = 1;

        foreach ($pago->detalles as $detalle) {
            // Si es una línea sin producto (ej: envío), saltarla por ahora
            if (empty($detalle->codigo_producto)) {
                continue;
            }

            $codigoProducto = $detalle->codigo_producto;
            $tipoProducto = $this->obtenerTipoProducto($detalle->tipo_producto);
            
            if ($tipoProducto == null) {
                continue;
            }

            // Obtener info FE del producto
            $infoFe = DB::table('producto_fe_info')
                ->where('codigo_producto', $codigoProducto)
                ->where('tipo_producto', $tipoProducto)
                ->first();

            if ($infoFe == null) {
                continue; // Ya se validó antes, pero por seguridad
            }

            // Datos del detalle
            $cantidad = floatval($detalle->cantidad_pagada);
            $subtotalLinea = floatval($detalle->subtotal);
            $descuento = floatval($detalle->descuento ?? 0);
            $iva = floatval($detalle->iva ?? 0);
            $totalLinea = floatval($detalle->total);
            
            // Calcular precio unitario sin impuesto
            $precioUnitario = $cantidad > 0 ? ($subtotalLinea + $descuento) / $cantidad : 0;
            $precioSinImp = $subtotalLinea + $descuento;
            $precioConImp = $totalLinea;
            
            // Obtener tarifa de impuesto y código de tarifa
            $tarifaImpuestoPorcentaje = floatval($infoFe->tarifa_impuesto ?? 13);
            $codigoTarifa = $this->obtenerCodigoTarifa($tarifaImpuestoPorcentaje);
            $exento = ($infoFe->exento ?? 'N') == 'S' ? 'S' : 'N';
            
            // ID del producto en el sistema externo (FactuX)
            // Si no tiene identificacion, usar el ID de producto_fe_info como temporal
            // o un ID por defecto del ejemplo (10) para pruebas
            if (!empty($infoFe->identificacion)) {
                $idProductoExterno = intval($infoFe->identificacion);
            } else {
                // Por mientras, usar el ID de producto_fe_info o un valor por defecto para pruebas
                $idProductoExterno = $infoFe->id ?? 10; // Usar ID de producto_fe_info o 10 como fallback
            }

            // Calcular factorIva: porcentaje / 100 (ej: 13% = 0.13, 1% = 0.01)
            $factorIva = $exento == 'N' ? round($tarifaImpuestoPorcentaje / 100, 4) : 0;

            $detalleFactuX = [
                'id' => $idProductoExterno,
                'codigo' => $codigoProducto,
                'descripcion' => $detalle->dsc_linea ?? $detalle->nombre_producto,
                'unidadMedida' => $infoFe->unidad_medida ?? 'Un',
                'precioUnidad' => round($precioUnitario, 0), // Sin decimales según ejemplo
                'tarifaImpuesto' => intval($codigoTarifa), // Código de tarifa como entero (1, 2, 3, 4, 8)
                'montoImpuesto' => round($iva, 0),
                'precioConImp' => round($precioConImp, 0),
                'existencia' => 0,
                'tipo' => 'M', // Siempre Mercancía según indicación
                'activo' => 'S',
                'impuestoIncluido' => ($infoFe->impuesto_incluido ?? 'N') == 'S' ? 'S' : 'N',
                'tipoImpuesto' => '01', // 01 = IVA
                'precioSinImp' => round($precioSinImp, 0),
                'exento' => $exento,
                'factorIva' => $factorIva,
                'baseImponible' => round($precioSinImp, 0),
                'codigoTarifa' => $codigoTarifa,
                'codigoCabys' => $infoFe->codigo_cabys ?? '',
                'tipoCodigo' => $infoFe->tipo_codigo ?? '01',
                'empresa' => ['id' => 1],
                'sucursal' => ['id' => $pago->sucursal_id ?? 1],
                'sucursalEmisor' => ['id' => 1],
                'emisor' => ['id' => 1],
                'activaIva' => $exento == 'N' ? 'S' : 'N',
                'tag' => 'software',
                'totalLinea' => round($totalLinea, 0),
                'cantidad' => $cantidad,
                'montoDescuento' => round($descuento, 0),
                'porcentajeDescuento' => $cantidad > 0 && $precioUnitario > 0 ? round(($descuento / ($precioUnitario * $cantidad)) * 100, 0) : 0,
                'numeroLinea' => $numeroLinea,
                'productoServicio' => ['id' => $idProductoExterno],
                'detalle' => $detalle->dsc_linea ?? $detalle->nombre_producto,
                'precioUnitario' => round($precioUnitario, 0),
                'montoTotal' => round($precioSinImp, 0),
                'subTotal' => round($subtotalLinea, 0),
                'codigoImpuesto' => '01', // 01 = IVA
                'montoTotalLinea' => round($totalLinea, 0),
                'impAsumidoEmisorFabrica' => 0,
                'impuestoNeto' => round($iva, 0)
            ];

            $detalles[] = $detalleFactuX;
            $numeroLinea++;
        }

        return $detalles;
    }

    /**
     * Obtiene el código de tarifa según el porcentaje de impuesto
     * Retorna el código de tarifa según FactuX: "08", "01", "02", "04", "07"
     */
    private function obtenerCodigoTarifa($porcentaje)
    {
        // Mapeo de tarifas según FactuX
        // "08" = General (13%), "01" = Reducida (1%), "02" = Reducida (2%), "04" = Reducida (4%), "07" = Exento (0%)
        if ($porcentaje == 0) {
            return "07"; // Exento
        } elseif ($porcentaje == 1) {
            return "01"; // Tarifa reducida 1%
        } elseif ($porcentaje == 2) {
            return "02"; // Tarifa reducida 2%
        } elseif ($porcentaje == 4) {
            return "04"; // Tarifa reducida 4%
        } elseif ($porcentaje == 13) {
            return "08"; // Tarifa general 13%
        } else {
            return "08"; // Por defecto tarifa general
        }
    }

    /**
     * Determina si un CABYS es servicio o mercancía según su primer dígito
     * Regla según FactuX:
     * - Si el primer dígito del CABYS es 0-4 → Es mercancía/bien
     * - Si el primer dígito del CABYS es 5-9 → Es servicio
     * 
     * @param string $codigoCabys Código CABYS del producto
     * @return bool true si es servicio, false si es mercancía
     */
    private function esServicioPorCabys($codigoCabys)
    {
        if (empty($codigoCabys) || strlen($codigoCabys) < 1) {
            return false; // Por defecto mercancía si no hay CABYS
        }
        $primerDigito = intval(substr($codigoCabys, 0, 1));
        // 5-9 = Servicio, 0-4 = Mercancía
        return $primerDigito >= 5 && $primerDigito <= 9;
    }

    /**
     * Construye el array de líneas de detalle (método antiguo, mantener por compatibilidad)
     */
    private function construirDetalles($pago)
    {
        $detalles = [];
        $numeroLinea = 1;

        foreach ($pago->detalles as $detalle) {
            // Si es una línea sin producto (ej: envío), tratarla de forma especial
            if (empty($detalle->codigo_producto)) {
                $lineaDetalle = [
                    'numeroLinea' => $numeroLinea,
                    'codigo' => [
                        'tipo' => '04',
                        'codigo' => '99'
                    ],
                    'cantidad' => 1,
                    'unidadMedida' => 'Sp',
                    'detalle' => $detalle->dsc_linea,
                    'precioUnitario' => number_format($detalle->total, 5, '.', ''),
                    'montoTotal' => number_format($detalle->total, 5, '.', ''),
                    'subtotal' => number_format($detalle->total, 5, '.', ''),
                    'montoTotalLinea' => number_format($detalle->total, 5, '.', '')
                ];
                
                $detalles[] = $lineaDetalle;
                $numeroLinea++;
                continue;
            }

            $codigoProducto = $detalle->codigo_producto;
            $tipoProducto = $this->obtenerTipoProducto($detalle->tipo_producto);
            
            if ($tipoProducto == null) {
                continue;
            }

            // Obtener info FE del producto
            $infoFe = DB::table('producto_fe_info')
                ->where('codigo_producto', $codigoProducto)
                ->where('tipo_producto', $tipoProducto)
                ->first();

            if ($infoFe == null) {
                continue; // Ya se validó antes, pero por seguridad
            }

            // Usar los datos de detalle_pago_orden
            $cantidad = $detalle->cantidad_pagada;
            $subtotalLinea = $detalle->subtotal;
            $descuento = $detalle->descuento ?? 0;
            $iva = $detalle->iva ?? 0;
            $total = $detalle->total;
            
            // Calcular precio unitario
            $precioUnitario = $cantidad > 0 ? ($subtotalLinea + $descuento) / $cantidad : 0;

            $lineaDetalle = [
                'numeroLinea' => $numeroLinea,
                'codigo' => [
                    'tipo' => $infoFe->tipo_codigo ?? '04',
                    'codigo' => !empty($infoFe->codigo_cabys) ? $infoFe->codigo_cabys : $codigoProducto
                ],
                'codigoComercial' => [
                    'tipo' => '01',
                    'codigo' => $codigoProducto
                ],
                'cantidad' => $cantidad,
                'unidadMedida' => $infoFe->unidad_medida ?? 'Unid',
                'detalle' => $detalle->dsc_linea,
                'precioUnitario' => number_format($precioUnitario, 5, '.', ''),
                'montoTotal' => number_format($subtotalLinea + $descuento, 5, '.', ''),
                'subtotal' => number_format($subtotalLinea, 5, '.', ''),
                'montoTotalLinea' => number_format($total, 5, '.', '')
            ];

            // Agregar descuento si existe
            if ($descuento > 0) {
                $lineaDetalle['descuento'] = [
                    'montoDescuento' => number_format($descuento, 5, '.', ''),
                    'naturalezaDescuento' => 'Descuento aplicado'
                ];
            }

            // Agregar impuesto si existe
            if ($iva > 0) {
                $tarifaImpuesto = $infoFe->tarifa_impuesto ?? 13;
                $lineaDetalle['impuesto'] = [
                    'codigo' => '01', // 01 = IVA
                    'codigoTarifa' => '08', // 08 = Tarifa general
                    'tarifa' => number_format($tarifaImpuesto, 2, '.', ''),
                    'monto' => number_format($iva, 5, '.', '')
                ];
            }

            $detalles[] = $lineaDetalle;
            $numeroLinea++;
        }

        return $detalles;
    }

    /**
     * Determina el tipo de identificación según el formato
     */
    private function determinarTipoIdentificacion($cedula)
    {
        $cedula = preg_replace('/[^0-9]/', '', $cedula);
        $longitud = strlen($cedula);

        if ($longitud == 9) {
            return '01'; // Cédula Física
        } elseif ($longitud == 10) {
            return '02'; // Cédula Jurídica
        } elseif ($longitud == 11 || $longitud == 12) {
            return '03'; // DIMEX
        } else {
            return '01'; // Por defecto
        }
    }

    /**
     * Calcula el resumen del comprobante en formato FactuX
     * Clasifica servicios vs mercancías según el primer dígito del CABYS:
     * - Si el primer dígito del CABYS es 0-4 → Es mercancía/bien → se suma en totalMercancias*
     * - Si el primer dígito del CABYS es 5-9 → Es servicio → se suma en totalServ*
     * 
     * IMPORTANTE: Usa valores exactos (sin redondear) para los cálculos y redondea solo al final
     * para evitar errores de precisión que causen que los totales no coincidan
     */
    private function calcularResumenFactuX($pago, $listaDetalles)
    {
        $totalServGravados = 0.0;
        $totalServExentos = 0.0;
        $totalMercanciasGravadas = 0.0;
        $totalMercanciasExentas = 0.0;
        $totalDescuentos = 0.0;
        $montoImpuestoTotal = 0.0;
        $totalVenta = 0.0;
        
        // Calcular totales por tipo según CABYS usando valores exactos (sin redondear)
        foreach ($listaDetalles as $detalle) {
            // Usar valores exactos guardados en el detalle (campos _*Exacto)
            // Si no existen, usar los formateados convertidos a float
            $subtotal = isset($detalle['_subTotalExacto']) ? $detalle['_subTotalExacto'] : floatval($detalle['subTotal'] ?? 0);
            $montoDescuento = isset($detalle['_montoDescuentoExacto']) ? $detalle['_montoDescuentoExacto'] : floatval($detalle['montoDescuento'] ?? 0);
            $montoImpuesto = isset($detalle['_montoImpuestoExacto']) ? $detalle['_montoImpuestoExacto'] : floatval($detalle['montoImpuesto'] ?? 0);
            $codigoCabys = $detalle['codigoCabys'] ?? '';
            
            // Clasificar según primer dígito del CABYS:
            // - 0-4 = Mercancía/Bien → se suma en totalMercancias*
            // - 5-9 = Servicio → se suma en totalServ*
            $esServicio = $this->esServicioPorCabys($codigoCabys);
            
            // Acumular descuentos (valores exactos)
            $totalDescuentos += $montoDescuento;
            
            // Acumular impuestos (valores exactos)
            $montoImpuestoTotal += $montoImpuesto;
            
            // Acumular venta (valores exactos)
            $totalVenta += $subtotal;
            
            // Clasificar por tipo (servicio o mercancía) y por gravado/exento
            if ($montoImpuesto > 0.00001) { // Tolerancia para comparar con 0
                // Gravado (tiene IVA)
                if ($esServicio) {
                    // Es servicio → suma en totalServGravados
                    $totalServGravados += $subtotal;
                } else {
                    // Es mercancía → suma en totalMercanciasGravadas
                    $totalMercanciasGravadas += $subtotal;
                }
            } else {
                // Exento (sin IVA)
                if ($esServicio) {
                    // Es servicio → suma en totalServExentos
                    $totalServExentos += $subtotal;
                } else {
                    // Es mercancía → suma en totalMercanciasExentas
                    $totalMercanciasExentas += $subtotal;
                }
            }
        }
        
        // Agregar descuentos generales del pago si existen
        $descuentoGeneral = floatval($pago->descuento ?? 0);
        $totalDescuentos += $descuentoGeneral;
        
        // Calcular totales intermedios (valores exactos)
        $totalGravado = $totalServGravados + $totalMercanciasGravadas;
        $totalExento = $totalServExentos + $totalMercanciasExentas;
        $totalVentaNeta = $totalVenta - $totalDescuentos;
        $totalComprobante = $totalVentaNeta + $montoImpuestoTotal;
        
        // Validar que los totales coincidan matemáticamente (con tolerancia de 0.01 por redondeo)
        // totalVenta debe ser igual a la suma de todos los subTotal
        // totalComprobante debe ser igual a totalVentaNeta + montoImpuesto
        $diferenciaVenta = abs($totalVenta - ($totalGravado + $totalExento));
        $diferenciaComprobante = abs($totalComprobante - ($totalVentaNeta + $montoImpuestoTotal));
        
        // Si hay diferencias mayores a 0.01, continuar (ya se validó antes)
        
        // Redondear solo al final para evitar errores de precisión
        // Usar round() con 2 decimales para todos los totales
        // IMPORTANTE: Asegurar que totalVenta = totalGravado + totalExento después del redondeo
        $totalVentaRedondeado = round($totalVenta, 2);
        $totalGravadoRedondeado = round($totalGravado, 2);
        $totalExentoRedondeado = round($totalExento, 2);
        
        // Ajustar si hay diferencia por redondeo (máximo 0.01)
        $diferenciaRedondeo = $totalVentaRedondeado - ($totalGravadoRedondeado + $totalExentoRedondeado);
        if (abs($diferenciaRedondeo) > 0.01) {
            // Ajustar totalGravado para que coincida
            $totalGravadoRedondeado = $totalVentaRedondeado - $totalExentoRedondeado;
        }
        
        return [
            'totalServGravados' => round($totalServGravados, 2),
            'totalServExentos' => round($totalServExentos, 2),
            'totalMercanciasGravadas' => round($totalMercanciasGravadas, 2),
            'totalMercanciasExentas' => round($totalMercanciasExentas, 2),
            'totalGravado' => $totalGravadoRedondeado,
            'totalExento' => $totalExentoRedondeado,
            'totalVenta' => $totalVentaRedondeado,
            'totalDescuentos' => round($totalDescuentos, 2),
            'totalVentaNeta' => round($totalVentaNeta, 2),
            'montoImpuesto' => round($montoImpuestoTotal, 2),
            'totalComprobante' => round($totalComprobante, 2)
        ];
    }

    /**
     * Obtiene el medio de pago en formato FactuX
     */
    private function obtenerMedioPagoFactuX($pago)
    {
        // 01 = Efectivo, 02 = Tarjeta, 03 = Cheque, 04 = Transferencia/SINPE
        $total = floatval($pago->total ?? 0);
        
        // Determinar el medio de pago principal (el de mayor monto)
        $montos = [
            '01' => floatval($pago->monto_efectivo ?? 0),
            '02' => floatval($pago->monto_tarjeta ?? 0),
            '04' => floatval($pago->monto_sinpe ?? 0)
        ];
        
        $medioPrincipal = '01'; // Por defecto efectivo
        $maxMonto = 0;
        
        foreach ($montos as $codigo => $monto) {
            if ($monto > $maxMonto) {
                $maxMonto = $monto;
                $medioPrincipal = $codigo;
            }
        }
        
        // Si no hay ningún monto específico, usar el total
        if ($maxMonto == 0) {
            $maxMonto = $total;
        }
        
        // Formato: "01:707.00" (con 2 decimales según el nuevo formato)
        return [
            'medioPago1' => $medioPrincipal . ':' . number_format($maxMonto, 2, '.', '')
        ];
    }

    /**
     * Obtiene el código de medio de pago según los montos del pago (método antiguo)
     */
    private function obtenerMedioPago($pago)
    {
        // 01 = Efectivo, 02 = Tarjeta, 03 = Cheque, 04 = Transferencia
        
        // Determinar el medio de pago principal (el de mayor monto)
        $montos = [
            '01' => $pago->monto_efectivo ?? 0,
            '02' => $pago->monto_tarjeta ?? 0,
            '04' => $pago->monto_sinpe ?? 0
        ];
        
        $medioPrincipal = '01'; // Por defecto efectivo
        $maxMonto = 0;
        
        foreach ($montos as $codigo => $monto) {
            if ($monto > $maxMonto) {
                $maxMonto = $monto;
                $medioPrincipal = $codigo;
            }
        }
        
        return $medioPrincipal;
    }

    /**
     * Método alternativo V2: Genera y envía el JSON dinámicamente con el nuevo formato simplificado de FactuX
     */
    public function enviarFacturaHaciendaV2(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idInfoFe = $request->input("idInfoFe");
      
        if ($idInfoFe == null || $idInfoFe == 0) {
            return $this->responseAjaxServerError("ID de información FE inválido", []);
        }

        try {
            // Obtener información FE
            $infoFe = DB::table('fe_info')->where('id', $idInfoFe)->first();
            
            if ($infoFe == null) {
                return $this->responseAjaxServerError("Información FE no encontrada", []);
            }

            if ($infoFe->id_pago == null) {
                return $this->responseAjaxServerError("No hay pago asociado a esta factura", []);
            }

            // Obtener información del pago
            $pago = $this->obtenerDatosPago($infoFe->id_pago);
            
            if ($pago == null) {
                return $this->responseAjaxServerError("Pago no encontrado", []);
            }

            // Validar que la orden no esté anulada
            $orden = DB::table('orden')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
                ->select('orden.*', 'sis_estado.cod_general')
                ->where('orden.id', '=', $pago->orden)
                ->first();

            if ($orden && $orden->cod_general == 'ORD_ANULADA') {
                return $this->responseAjaxServerError("La orden se encuentra anulada", []);
            }

            // Validar que todos los productos tengan información FE
            $validacion = $this->validarProductosFE($pago);
            if (!$validacion['valido']) {
                return $this->responseAjaxServerError($validacion['mensaje'], $validacion['productos_faltantes']);
            }

            // Construir el JSON con el nuevo formato simplificado
            $comprobanteJson = $this->construirComprobanteElectronicoV2($pago, $infoFe);

            // Convertir JSON a string para guardarlo en BD
            $jsonEnvioString = json_encode($comprobanteJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Enviar a la API de FactuX
            $resultado = $this->enviarAHacienda($comprobanteJson);

            // Convertir respuesta a string para guardarla en BD
            $jsonRespuestaString = json_encode($resultado['respuesta'] ?? $resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // Extraer datos de la respuesta
            $respuestaData = $resultado['respuesta'] ?? [];
            $urlConsultaEstado = $respuestaData['urlConsultaEstado'] ?? null;
            $estadoRespuesta = $respuestaData['estado'] ?? null;
            
            // Mapear el estado de la respuesta al ID del estado en sis_estado
            $estadoHaciendaId = $this->obtenerIdEstadoHacienda($estadoRespuesta, $resultado['exito']);

            if ($resultado['exito']) {
                // Actualizar estado en la BD
                DB::beginTransaction();
                
                // Obtener la clave del comprobante de la respuesta
                $claveComprobante = $resultado['clave'] ?? $respuestaData['clave'] ?? null;
                
                DB::table('fe_info')
                    ->where('id', '=', $idInfoFe)
                    ->update([
                        'estado' => SisEstadoController::getIdEstadoByCodGeneral('FE_ORDEN_ENVIADA'),
                        'num_comprobante' => $claveComprobante,
                        'json_envio' => $jsonEnvioString,
                        'json_respuesta' => $jsonRespuestaString,
                        'estado_hacienda' => $estadoHaciendaId,
                        'url_consulta_estado' => $urlConsultaEstado
                    ]);

                DB::commit();

                return $this->responseAjaxSuccess("Factura electrónica enviada exitosamente a FactuX.", [
                    'clave' => $claveComprobante,
                    'respuesta' => $resultado['respuesta'] ?? null,
                    'urlConsultaEstado' => $urlConsultaEstado
                ]);
            } else {
                // Guardar también en caso de error
                DB::beginTransaction();
                
                DB::table('fe_info')
                    ->where('id', '=', $idInfoFe)
                    ->update([
                        'json_envio' => $jsonEnvioString,
                        'json_respuesta' => $jsonRespuestaString,
                        'estado_hacienda' => $estadoHaciendaId,
                        'url_consulta_estado' => $urlConsultaEstado
                    ]);

                DB::commit();

                return $this->responseAjaxServerError("Error al enviar a FactuX: " . $resultado['mensaje'], []);
            }

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al procesar la factura: " . $ex->getMessage(), []);
        }
    }

    /**
     * Obtiene el ID del estado de Hacienda según el estado de la respuesta
     */
    private function obtenerIdEstadoHacienda($estadoRespuesta, $exito)
    {
        // Función helper para obtener ID de estado con validación
        $obtenerIdEstado = function($codGeneral) {
            try {
                $resultado = DB::table('sis_estado')
                    ->select('sis_estado.id')
                    ->where('cod_general', '=', $codGeneral)
                    ->first();
                
                if ($resultado && isset($resultado->id)) {
                    return intval($resultado->id);
                }
                
                return null;
            } catch (\Exception $e) {
                return null;
            }
        };
        
        // Si no hubo éxito, retornar estado rechazado
        if (!$exito) {
            $idEstado = $obtenerIdEstado('HACIENDA_RECHAZADO');
            return $idEstado;
        }

        // Si no hay estado en la respuesta, retornar pendiente
        if (empty($estadoRespuesta)) {
            $idEstado = $obtenerIdEstado('HACIENDA_PENDIENTE');
            return $idEstado;
        }

        // Mapear estados de FactuX/Hacienda a códigos de sis_estado
        // Normalizar a mayúsculas para comparación
        $estadoUpper = strtoupper(trim($estadoRespuesta));
        
        // Mapear códigos de estado (R = Rechazado, A = Aceptado, etc.)
        if (strlen($estadoUpper) == 1) {
            switch ($estadoUpper) {
                case 'A':
                    $idEstado = $obtenerIdEstado('HACIENDA_ACEPTADO');
                    return $idEstado;
                case 'R':
                    $idEstado = $obtenerIdEstado('HACIENDA_RECHAZADO');
                    return $idEstado;
                case 'P':
                default:
                    $idEstado = $obtenerIdEstado('HACIENDA_PENDIENTE');
                    return $idEstado;
            }
        }
        
        // Mapear estados completos
        switch ($estadoUpper) {
            case 'ACEPTADO':
            case 'ACEPTADA':
            case 'ACEPTADO_POR_HACIENDA':
            case 'ACEPTADO POR HACIENDA':
                $idEstado = $obtenerIdEstado('HACIENDA_ACEPTADO');
                return $idEstado;
            case 'RECHAZADO':
            case 'RECHAZADA':
            case 'RECHAZADO_POR_HACIENDA':
            case 'RECHAZADO POR HACIENDA':
            case 'ERROR':
                $idEstado = $obtenerIdEstado('HACIENDA_RECHAZADO');
                return $idEstado;
            case 'GUARDADO':
            case 'PROCESANDO':
            case 'PROCESANDO_EN_SEGUNDO_PLANO':
            case 'PROCESANDO EN SEGUNDO PLANO':
            case 'PENDIENTE':
            case 'PENDIENTE_DE_VALIDACION':
            case 'PENDIENTE DE VALIDACION':
            case 'ENVIADO_HACIENDA':
            case 'ENVIADO A HACIENDA':
            default:
                // Por defecto, cualquier otro estado se considera pendiente
                $idEstado = $obtenerIdEstado('HACIENDA_PENDIENTE');
                return $idEstado;
        }
    }

    /**
     * Envía el comprobante electrónico a Hacienda (sin datos del cliente)
     */
    public function enviarComprobanteHacienda(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idInfoFe = $request->input("idInfoFe");
      
        if ($idInfoFe == null || $idInfoFe == 0) {
            return $this->responseAjaxServerError("ID de información FE inválido", []);
        }

        try {
            // Obtener información FE
            $infoFe = DB::table('fe_info')->where('id', $idInfoFe)->first();
            
            if ($infoFe == null) {
                return $this->responseAjaxServerError("Información FE no encontrada", []);
            }

            if ($infoFe->id_pago == null) {
                return $this->responseAjaxServerError("No hay pago asociado a esta factura", []);
            }

            // Obtener información del pago
            $pago = $this->obtenerDatosPago($infoFe->id_pago);
            
            if ($pago == null) {
                return $this->responseAjaxServerError("Pago no encontrado", []);
            }

            // Validar que la orden no esté anulada
            $orden = DB::table('orden')
                ->leftJoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
                ->select('orden.*', 'sis_estado.cod_general')
                ->where('orden.id', '=', $pago->orden)
                ->first();

            if ($orden && $orden->cod_general == 'ORD_ANULADA') {
                return $this->responseAjaxServerError("La orden se encuentra anulada", []);
            }

            // Validar que todos los productos tengan información FE
            $validacion = $this->validarProductosFE($pago);
            if (!$validacion['valido']) {
                return $this->responseAjaxServerError($validacion['mensaje'], $validacion['productos_faltantes']);
            }

            // Construir el JSON con el nuevo formato simplificado (sin datos del cliente)
            $comprobanteJson = $this->construirComprobanteElectronicoV2SinCliente($pago, $infoFe);

            // Convertir JSON a string para guardarlo en BD
            $jsonEnvioString = json_encode($comprobanteJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Enviar a la API de FactuX
            $resultado = $this->enviarAHacienda($comprobanteJson);

            // Convertir respuesta a string para guardarla en BD
            $jsonRespuestaString = json_encode($resultado['respuesta'] ?? $resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            // Extraer datos de la respuesta
            $respuestaData = $resultado['respuesta'] ?? [];
            $urlConsultaEstado = $respuestaData['urlConsultaEstado'] ?? null;
            $estadoRespuesta = $respuestaData['estado'] ?? null;
            
            // Mapear el estado de la respuesta al ID del estado en sis_estado
            $estadoHaciendaId = $this->obtenerIdEstadoHacienda($estadoRespuesta, $resultado['exito']);

            if ($resultado['exito']) {
                // Actualizar estado en la BD
                DB::beginTransaction();
                
                // Obtener la clave del comprobante de la respuesta
                $claveComprobante = $resultado['clave'] ?? $respuestaData['clave'] ?? null;
                
                // Limpiar datos del cliente ya que es un comprobante (sin cliente)
                DB::table('fe_info')
                    ->where('id', '=', $idInfoFe)
                    ->update([
                        'estado' => SisEstadoController::getIdEstadoByCodGeneral('FE_ORDEN_ENVIADA'),
                        'num_comprobante' => $claveComprobante,
                        'json_envio' => $jsonEnvioString,
                        'json_respuesta' => $jsonRespuestaString,
                        'estado_hacienda' => $estadoHaciendaId,
                        'url_consulta_estado' => $urlConsultaEstado,
                        'cedula' => '',
                        'nombre' => '',
                        'correo' => ''
                    ]);

                DB::commit();

                return $this->responseAjaxSuccess("Comprobante electrónico enviado exitosamente a FactuX.", [
                    'clave' => $claveComprobante,
                    'respuesta' => $resultado['respuesta'] ?? null,
                    'urlConsultaEstado' => $urlConsultaEstado
                ]);
            } else {
                // Guardar también en caso de error
                DB::beginTransaction();
                
                DB::table('fe_info')
                    ->where('id', '=', $idInfoFe)
                    ->update([
                        'json_envio' => $jsonEnvioString,
                        'json_respuesta' => $jsonRespuestaString,
                        'estado_hacienda' => $estadoHaciendaId,
                        'url_consulta_estado' => $urlConsultaEstado
                    ]);

                DB::commit();

                return $this->responseAjaxServerError("Error al enviar a FactuX: " . $resultado['mensaje'], []);
            }

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al procesar el comprobante: " . $ex->getMessage(), []);
        }
    }

    /**
     * Construye el comprobante electrónico en el nuevo formato simplificado de FactuX
     */
    private function construirComprobanteElectronicoV2($pago, $infoFe)
    {
        // Obtener IDs de FactuX desde sucursal (asegurar que no sean 0)
        $idEmisorFe = (!empty($pago->id_emisor_fe) && $pago->id_emisor_fe > 0) ? $pago->id_emisor_fe : 1;
        $idSucursalFe = (!empty($pago->id_sucursal_fe) && $pago->id_sucursal_fe > 0) ? $pago->id_sucursal_fe : 1;
        
        // Construir lista de detalles en formato simplificado
        $listaDetalles = $this->construirListaDetalleComprobantesV2($pago);
        
        // Calcular resumen usando valores exactos
        $resumen = $this->calcularResumenFactuX($pago, $listaDetalles);
        
        // Limpiar campos internos (_*Exacto) antes de enviar a FactuX
        $listaDetalles = $this->limpiarCamposInternosDetalles($listaDetalles);
        
        // Obtener medio de pago (formato: "01:707.00" con 2 decimales)
        $medioPago = $this->obtenerMedioPagoFactuX($pago);

        // Datos del receptor desde fe_info y cliente_fe_info
        $receptorNombre = $infoFe->nombre ?? $pago->nombre_comercial_cliente ?? 'Cliente General';
        $receptorTipoIdentificacion = $pago->tipo_identificacion_cliente ?? '01';
        $receptorNumeroIdentificacion = $infoFe->cedula ?? $pago->identificacion_cliente ?? '0000000000';
        $receptorNombreComercial = $pago->nombre_comercial_cliente ?? $receptorNombre;
        $receptorTelefono = $pago->telefono_cliente ?? 0;
        $receptorCorreo = $infoFe->correo ?? $pago->correo_cliente ?? '';

        // Construir estructura del comprobante en formato nuevo de FactuX
        $comprobante = [
            'tipoCom' => '01', // 01 = Factura electrónica (nuevo formato)
            'emisor' => ['id' => $idEmisorFe],
            'sucursal' => ['id' => $idSucursalFe],
            'receptorNombre' => $receptorNombre,
            'receptorTipoIdentificacion' => $receptorTipoIdentificacion,
            'receptorNumeroIdentificacion' => $receptorNumeroIdentificacion,
            'receptorNombreComercial' => $receptorNombreComercial,
            'receptorTelefono' => intval($receptorTelefono),
            'receptorCorreo' => $receptorCorreo,
            'condicionVenta' => '01', // 01 = Contado
            'plazoCredito' => '0', // Plazo en días (0 para contado, opcional según reglas)
            'medioPago1' => $medioPago['medioPago1'],
            'codigoMonedaResumen' => 'CRC',
            'tipoCambioResumen' => 1.0,
            'totalServGravadosResumen' => number_format($resumen['totalServGravados'], 2, '.', ''),
            'totalServExentosResumen' => number_format($resumen['totalServExentos'], 2, '.', ''),
            'totalServExoneradosResumen' => '0.00',
            'totalMercanciasGravadasResumen' => number_format($resumen['totalMercanciasGravadas'], 2, '.', ''),
            'totalMercanciasExentasResumen' => number_format($resumen['totalMercanciasExentas'], 2, '.', ''),
            'totalMercanciasExoneradasResumen' => '0.00',
            'totalGravadoResumen' => number_format($resumen['totalGravado'], 2, '.', ''),
            'totalExentoResumen' => number_format($resumen['totalExento'], 2, '.', ''),
            'totalExoneradoResumen' => '0.00',
            'totalVentaResumen' => number_format($resumen['totalVenta'], 2, '.', ''),
            'totalDescuentosResumen' => number_format($resumen['totalDescuentos'], 2, '.', ''),
            'totalVentaNetaResumen' => number_format($resumen['totalVentaNeta'], 2, '.', ''),
            'montoImpuestoResumen' => number_format($resumen['montoImpuesto'], 2, '.', ''),
            'totalOtrosCargosResumen' => '0.00', // Requerido según reglas
            'totalComprobanteResumen' => number_format($resumen['totalComprobante'], 2, '.', ''),
            'listaDetalleComprobantes' => $listaDetalles
        ];

        return $comprobante;
    }

    /**
     * Construye la lista de detalles en formato nuevo de FactuX
     * Retorna tanto los detalles formateados como los valores exactos para el cálculo del resumen
     */
    private function construirListaDetalleComprobantesV2($pago)
    {
        $detalles = [];
        $numeroLinea = 1;

        foreach ($pago->detalles as $detalle) {
            // Si es una línea sin producto (ej: envío), saltarla por ahora
            if (empty($detalle->codigo_producto)) {
                continue;
            }

            $codigoProducto = $detalle->codigo_producto;
            $tipoProducto = $this->obtenerTipoProducto($detalle->tipo_producto);
            
            if ($tipoProducto == null) {
                continue;
            }

            // Obtener info FE del producto
            $infoFe = DB::table('producto_fe_info')
                ->where('codigo_producto', $codigoProducto)
                ->where('tipo_producto', $tipoProducto)
                ->first();

            if ($infoFe == null) {
                continue; // Ya se validó antes, pero por seguridad
            }

            // Datos del detalle (valores exactos)
            $cantidad = floatval($detalle->cantidad_pagada);
            $subtotalLinea = floatval($detalle->subtotal);
            $descuento = floatval($detalle->descuento ?? 0);
            $iva = floatval($detalle->iva ?? 0);
            $totalLinea = floatval($detalle->total);
            
            // Obtener tarifa de impuesto y código de tarifa
            $tarifaImpuestoPorcentaje = floatval($infoFe->tarifa_impuesto ?? 13);
            $codigoTarifa = $this->obtenerCodigoTarifa($tarifaImpuestoPorcentaje);
            
            // Calcular precio unitario sin impuesto (valores exactos, sin redondear aún)
            // El precio unitario debe ser: (subtotal + descuento) / cantidad
            // Esto asegura que: cantidad × precioUnitario - descuento = subtotalLinea
            $precioUnitario = $cantidad > 0 ? ($subtotalLinea + $descuento) / $cantidad : 0;
            $baseImponible = $subtotalLinea + $descuento;
            
            // Calcular montoImpuesto según reglas: baseImponible × tarifaImpuesto / 100
            // Si está exento (codigoTarifa = "07"), el montoImpuesto debe ser 0
            $montoImpuestoCalculado = ($codigoTarifa == "07") ? 0.0 : ($baseImponible * $tarifaImpuestoPorcentaje / 100);
            
            // Usar el monto calculado (valores exactos para cálculos)
            $montoImpuesto = $montoImpuestoCalculado;
            
            // Calcular subTotal según reglas de FactuX: cantidad × precioUnitario - montoDescuento
            // IMPORTANTE: Usar la fórmula exacta para que coincida con la validación de FactuX
            // subTotal = cantidad × precioUnitario - montoDescuento
            // Como precioUnitario = (subtotalLinea + descuento) / cantidad,
            // entonces: subTotal = cantidad × ((subtotalLinea + descuento) / cantidad) - descuento
            //          = subtotalLinea + descuento - descuento = subtotalLinea
            // Pero usamos la fórmula para asegurar que coincida con la validación
            $subTotalCalculado = ($cantidad * $precioUnitario) - $descuento;
            
            // Ajustar por errores de redondeo menores a 0.01 (tolerancia de FactuX)
            $diferenciaSubtotal = abs($subTotalCalculado - $subtotalLinea);
            if ($diferenciaSubtotal < 0.01) {
                $subTotalCalculado = $subtotalLinea; // Usar el valor original si la diferencia es mínima
            }
            
            // Validar CABYS: debe tener exactamente 13 dígitos
            $codigoCabys = $infoFe->codigo_cabys ?? '';
            if (!empty($codigoCabys) && strlen(preg_replace('/[^0-9]/', '', $codigoCabys)) != 13) {
                // Si no tiene 13 dígitos, usar un código por defecto o lanzar error
                // Por ahora lo dejamos pero debería validarse antes
            }
            
            // Validar unidad de medida (lista válida según FactuX)
            $unidadesValidas = ['Sp', 'M', 'Kg', 'h', 'Unid', 'Al', 'Alc', 'Cm', 'I', 'Os', 'Spe', 'St', 'D', 'cm', 'M2', 'M3', 'Oz'];
            $unidadMedida = $infoFe->unidad_medida ?? 'Unid';
            if (!in_array($unidadMedida, $unidadesValidas)) {
                $unidadMedida = 'Unid'; // Por defecto
            }

            // Formato nuevo según reglas de FactuX
            // IMPORTANTE: Guardar valores exactos (sin formatear) para el cálculo del resumen
            $detalleFactuX = [
                'numeroLinea' => $numeroLinea,
                'codigoCabys' => $codigoCabys,
                'tipoCodigo' => '04', // 04 según el ejemplo
                'codigo' => $codigoProducto,
                'cantidad' => round($cantidad, 3), // Máximo 3 decimales
                'unidadMedida' => $unidadMedida,
                'detalle' => $detalle->dsc_linea ?? $detalle->nombre_producto,
                'precioUnitario' => number_format($precioUnitario, 5, '.', ''), // Máximo 5 decimales
                'montoTotal' => number_format($baseImponible, 2, '.', ''),
                'subTotal' => number_format($subTotalCalculado, 2, '.', ''), // cantidad × precioUnitario - montoDescuento
                'baseImponible' => number_format($baseImponible, 2, '.', ''),
                'codigoImpuesto' => '01', // 01 = IVA
                'codigoTarifa' => $codigoTarifa, // Ya viene como "08", "01", "02", "04", "07"
                'tarifaImpuesto' => number_format($tarifaImpuestoPorcentaje, 2, '.', ''),
                'factorIva' => 0,
                'montoImpuesto' => number_format($montoImpuesto, 5, '.', ''), // Máximo 5 decimales
                'impuestoNeto' => number_format($montoImpuesto, 5, '.', ''), // Máximo 5 decimales
                'montoTotalLinea' => number_format($totalLinea, 2, '.', ''),
                'impAsumidoEmisorFabrica' => 0.00,
                // Valores exactos (sin formatear) para el cálculo del resumen
                '_subTotalExacto' => $subTotalCalculado,
                '_montoDescuentoExacto' => $descuento,
                '_montoImpuestoExacto' => $montoImpuesto
            ];
            
            // Agregar descuento si existe (según reglas, si montoDescuento > 0, debe enviar naturalezaDescuento)
            if ($descuento > 0) {
                $detalleFactuX['montoDescuento'] = number_format($descuento, 2, '.', '');
                $detalleFactuX['naturalezaDescuento'] = 'Descuento aplicado'; // Requerido cuando hay descuento
            }

            $detalles[] = $detalleFactuX;
            $numeroLinea++;
        }

        return $detalles;
    }

    /**
     * Limpia los campos internos (_*Exacto) de los detalles antes de enviar a FactuX
     */
    private function limpiarCamposInternosDetalles($detalles)
    {
        foreach ($detalles as &$detalle) {
            unset($detalle['_subTotalExacto']);
            unset($detalle['_montoDescuentoExacto']);
            unset($detalle['_montoImpuestoExacto']);
        }
        return $detalles;
    }

    /**
     * Construye el comprobante electrónico en formato FactuX sin datos del cliente
     */
    private function construirComprobanteElectronicoV2SinCliente($pago, $infoFe)
    {
        // Obtener IDs de FactuX desde sucursal (asegurar que no sean 0)
        $idEmisorFe = (!empty($pago->id_emisor_fe) && $pago->id_emisor_fe > 0) ? $pago->id_emisor_fe : 1;
        $idSucursalFe = (!empty($pago->id_sucursal_fe) && $pago->id_sucursal_fe > 0) ? $pago->id_sucursal_fe : 1;
        
        // Construir lista de detalles en formato simplificado
        $listaDetalles = $this->construirListaDetalleComprobantesV2($pago);
        
        // Calcular resumen usando valores exactos
        $resumen = $this->calcularResumenFactuX($pago, $listaDetalles);
        
        // Limpiar campos internos (_*Exacto) antes de enviar a FactuX
        $listaDetalles = $this->limpiarCamposInternosDetalles($listaDetalles);
        
        // Obtener medio de pago (formato: "01:707.00" con 2 decimales)
        $medioPago = $this->obtenerMedioPagoFactuX($pago);

        // Construir estructura del comprobante en formato nuevo de FactuX (SIN datos del receptor)
        // Si no hay cliente, debe ser tipoCom 04 (Tiquete) en lugar de 01 (Factura)
        $comprobante = [
            'tipoCom' => '04', // 04 = Tiquete electrónico (cuando no hay cliente)
            'emisor' => ['id' => $idEmisorFe],
            'sucursal' => ['id' => $idSucursalFe],
            'condicionVenta' => '01', // 01 = Contado
            'plazoCredito' => '0', // Plazo en días (0 para contado, opcional según reglas)
            'medioPago1' => $medioPago['medioPago1'],
            'codigoMonedaResumen' => 'CRC',
            'tipoCambioResumen' => 1.0,
            'totalServGravadosResumen' => number_format($resumen['totalServGravados'], 2, '.', ''),
            'totalServExentosResumen' => number_format($resumen['totalServExentos'], 2, '.', ''),
            'totalServExoneradosResumen' => '0.00',
            'totalMercanciasGravadasResumen' => number_format($resumen['totalMercanciasGravadas'], 2, '.', ''),
            'totalMercanciasExentasResumen' => number_format($resumen['totalMercanciasExentas'], 2, '.', ''),
            'totalMercanciasExoneradasResumen' => '0.00',
            'totalGravadoResumen' => number_format($resumen['totalGravado'], 2, '.', ''),
            'totalExentoResumen' => number_format($resumen['totalExento'], 2, '.', ''),
            'totalExoneradoResumen' => '0.00',
            'totalVentaResumen' => number_format($resumen['totalVenta'], 2, '.', ''),
            'totalDescuentosResumen' => number_format($resumen['totalDescuentos'], 2, '.', ''),
            'totalVentaNetaResumen' => number_format($resumen['totalVentaNeta'], 2, '.', ''),
            'montoImpuestoResumen' => number_format($resumen['montoImpuesto'], 2, '.', ''),
            'totalOtrosCargosResumen' => '0.00', // Requerido según reglas
            'totalComprobanteResumen' => number_format($resumen['totalComprobante'], 2, '.', ''),
            'listaDetalleComprobantes' => $listaDetalles
        ];

        return $comprobante;
    }

    /**
     * Envía el comprobante a la API de FactuX
     */
    private function enviarAHacienda($comprobanteJson)
    {
        $endpoint = '/api/v1/comprobantes';

        try {
            // El payload es directamente el comprobante JSON en formato FactuX
            $payload = $comprobanteJson;

            // Asegurar que el objeto receptor vacío se serialice correctamente
            // Si receptor es un stdClass vacío, json_encode lo convertirá a {}
            // Si es un array vacío, lo convertiremos a objeto
            if (isset($payload['receptor']) && is_array($payload['receptor']) && empty($payload['receptor'])) {
                $payload['receptor'] = new \stdClass();
            }
            
            // Hacer la petición usando el servicio centralizado
            $resultado = $this->factuXService->post($endpoint, $payload);

            if ($resultado['exito']) {
                $respuesta = $resultado['respuesta'];
                
                // La respuesta puede contener la clave numérica u otro identificador
                $clave = $respuesta['clave'] ?? $respuesta['numeroComprobante'] ?? $respuesta['id'] ?? null;
                
                return [
                    'exito' => true,
                    'clave' => $clave,
                    'mensaje' => 'Enviado correctamente a FactuX',
                    'respuesta' => $respuesta
                ];
            } else {
                // Intentar obtener más detalles del error
                $errorDetalle = $resultado['mensaje'];
                $respuesta = $resultado['respuesta'] ?? [];
                
                if (isset($respuesta['message'])) {
                    $errorDetalle = $respuesta['message'];
                } elseif (isset($respuesta['error'])) {
                    $errorDetalle = $respuesta['error'];
                } elseif (isset($respuesta['status'])) {
                    $errorDetalle = 'Status: ' . $respuesta['status'] . ($respuesta['message'] ?? '');
                }
                
                // Para debug: incluir información adicional
                $debugInfo = [
                    'http_code' => $resultado['http_code'],
                    'response' => $resultado['raw_response'] ?? '',
                    'endpoint' => $endpoint
                ];
                
                // Log del error para debugging
                if (config('app.debug')) {
                    logger()->error('Error al enviar a FactuX', [
                        'error' => $errorDetalle,
                        'response' => $resultado['raw_response'] ?? '',
                        'endpoint' => $endpoint
                    ]);
                }
                
                return [
                    'exito' => false,
                    'mensaje' => 'Error HTTP ' . $resultado['http_code'] . ': ' . $errorDetalle,
                    'debug' => $debugInfo
                ];
            }

        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Excepción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene el JSON del comprobante para revisión (útil para debugging)
     */
    public function obtenerJsonComprobante(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idInfoFe = $request->input("idInfoFe");

        try {
            $infoFe = DB::table('fe_info')->where('id', $idInfoFe)->first();
            
            if (!$infoFe) {
                return $this->responseAjaxServerError("Información FE no encontrada", []);
            }

            if ($infoFe->id_pago == null) {
                return $this->responseAjaxServerError("No hay pago asociado a esta factura", []);
            }

            $pago = $this->obtenerDatosPago($infoFe->id_pago);
            
            if (!$pago) {
                return $this->responseAjaxServerError("Pago no encontrado", []);
            }

            $comprobanteJson = $this->construirComprobanteElectronico($pago, $infoFe);
            
            return $this->responseAjaxSuccess("JSON generado correctamente", $comprobanteJson);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error: " . $ex->getMessage(), []);
        }
    }

    /**
     * Consulta el estado del comprobante en Hacienda usando la URL guardada
     */
    public function consultarEstadoHacienda(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idInfoFe = intval($request->input("idInfoFe"));
        $urlConsulta = $request->input("urlConsulta");
      
        if ($idInfoFe == null || $idInfoFe == 0) {
            return $this->responseAjaxServerError("ID de información FE inválido", []);
        }
        
        // Verificar que el registro existe
        $infoFe = DB::table('fe_info')->where('id', $idInfoFe)->first();
        if (!$infoFe) {
            return $this->responseAjaxServerError("Registro de factura electrónica no encontrado", []);
        }
        
        try {
            // Obtener la clave del comprobante (nuevo método preferido)
            $claveComprobante = $infoFe->num_comprobante ?? null;
            
            // Si tenemos la clave, usar el nuevo endpoint de la API v1
            if (!empty($claveComprobante)) {
                $endpoint = '/api/v1/comprobantes/clave/' . $claveComprobante . '/estado';
                
                // Hacer petición usando el servicio centralizado
                $resultado = $this->factuXService->get($endpoint);
                
                if (!$resultado['exito']) {
                    return $this->responseAjaxServerError("Error al consultar estado: " . $resultado['mensaje'], []);
                }
                
                $respuestaData = $resultado['respuesta'];
                $httpCode = $resultado['http_code'];
            } else {
                // Fallback: Si no hay clave pero hay URL de consulta, intentar usar la URL (compatibilidad con registros antiguos)
                if (!empty($urlConsulta)) {
                    // Verificar si la URL es de FactuX para usar el servicio centralizado
                    $esUrlFactuX = strpos($urlConsulta, 'api-factux.spacesoftwarecr.com') !== false;
                    
                    if ($esUrlFactuX) {
                        // Extraer el endpoint relativo de la URL completa usando parse_url
                        $parsedUrl = parse_url($urlConsulta);
                        
                        if ($parsedUrl && isset($parsedUrl['path'])) {
                            $endpoint = $parsedUrl['path'];
                            // Agregar query string si existe
                            if (isset($parsedUrl['query']) && !empty($parsedUrl['query'])) {
                                $endpoint .= '?' . $parsedUrl['query'];
                            }
                        } else {
                            // Si no se puede parsear, intentar extraer manualmente
                            $baseUrl = 'https://www.api-factux.spacesoftwarecr.com';
                            $endpoint = str_replace($baseUrl, '', $urlConsulta);
                            $endpoint = ltrim($endpoint, '/');
                            if (!empty($endpoint)) {
                                $endpoint = '/' . $endpoint;
                            } else {
                                $endpoint = $urlConsulta;
                            }
                        }
                        
                        // Asegurar que el endpoint empiece con /
                        if (!empty($endpoint) && $endpoint[0] !== '/' && strpos($endpoint, 'http') !== 0) {
                            $endpoint = '/' . $endpoint;
                        }
                        
                        // Hacer petición usando el servicio centralizado
                        $resultado = $this->factuXService->get($endpoint);
                        
                        if (!$resultado['exito']) {
                            return $this->responseAjaxServerError("Error al consultar estado: " . $resultado['mensaje'], []);
                        }
                        
                        $respuestaData = $resultado['respuesta'];
                        $httpCode = $resultado['http_code'];
                    } else {
                        // Si no es URL de FactuX, usar curl directo (puede ser URL de Hacienda u otro servicio)
            $ch = curl_init($urlConsulta);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            
            $headers = [
                'Accept: application/json, */*',
                'Connection: keep-alive'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false) {
                $curlError = curl_error($ch);
                $curlErrno = curl_errno($ch);
                curl_close($ch);

                return $this->responseAjaxServerError("Error al consultar estado: " . $curlError, []);
            }

            curl_close($ch);

            // Decodificar la respuesta JSON
            $respuestaData = json_decode($response, true);
                    }
                } else {
                    return $this->responseAjaxServerError("No hay clave de comprobante ni URL de consulta disponible", []);
                }
            }
            
            if ($respuestaData === null) {
                return $this->responseAjaxServerError("La respuesta de Hacienda no es válida", [
                    'respuesta_raw' => $response
                ]);
            }

            // Extraer el estado de la respuesta según la nueva estructura
            // La respuesta de FactuX puede venir directamente en el nivel raíz o dentro de 'respuesta'
            $respuestaInterna = $respuestaData['respuesta'] ?? [];
            $tieneError = $respuestaData['tieneError'] ?? false;
            
            // Prioridad para obtener el estado:
            // 1. nivel raíz: estadoActual (mayúsculas: "RECHAZADO", "ACEPTADO") - PRIORIDAD MÁXIMA
            // 2. nivel raíz: estadoHacienda (minúsculas: "rechazado", "aceptado")
            // 3. nivel raíz: codigoEstadoHacienda ("R", "A", "P")
            // 4. respuesta.estadoActual (si existe objeto respuesta)
            // 5. respuesta.estadoHacienda (si existe objeto respuesta)
            // 6. respuesta.codigoEstadoHacienda (si existe objeto respuesta)
            // 7. estado (nivel raíz)
            // 8. estadoHaciendaId (solo si no hay estado de texto disponible)
            $estadoHaciendaId = null;
            
            // Obtener el estado de texto para mapearlo (prioridad al nivel raíz primero)
            $estadoRespuesta = $respuestaData['estadoActual'] 
                ?? $respuestaData['estadoHacienda'] 
                ?? $respuestaData['codigoEstadoHacienda']
                ?? $respuestaInterna['estadoActual'] 
                ?? $respuestaInterna['estadoHacienda'] 
                ?? $respuestaInterna['codigoEstadoHacienda']
                ?? $respuestaData['estado'] 
                ?? null;
            
            // Obtener estadoHacienda para validar si es null (caso especial: CREADO sin estadoHacienda)
            $estadoHacienda = $respuestaData['estadoHacienda'] ?? $respuestaInterna['estadoHacienda'] ?? null;
            
            // Si el estado es "CREADO" y estadoHacienda es null, tratarlo como rechazo para permitir reenvío
            $esCreadoSinEstado = (
                strtoupper($estadoRespuesta ?? '') === 'CREADO' && 
                $estadoHacienda === null
            );
            
            // Si hay estado de texto, mapearlo a nuestro sistema
            if (!empty($estadoRespuesta)) {
                // Si tiene error o es CREADO sin estadoHacienda, mapear a RECHAZADO
                if ($tieneError || $esCreadoSinEstado) {
                    $estadoHaciendaId = SisEstadoController::getIdEstadoByCodGeneral('HACIENDA_RECHAZADO');
                } else {
                    // Mapear el estado al ID correspondiente (HTTP 200 = éxito en la consulta)
                    $estadoHaciendaId = $this->obtenerIdEstadoHacienda($estadoRespuesta, $httpCode == 200);
                }
            } else {
                // Si no hay estado de texto, usar estadoHaciendaId de la respuesta como último recurso
                if (isset($respuestaData['estadoHaciendaId']) && $respuestaData['estadoHaciendaId'] > 0) {
                    $estadoHaciendaId = $respuestaData['estadoHaciendaId'];
                } else {
                    // Si no hay ningún estado disponible, usar pendiente por defecto
                    $estadoHaciendaId = SisEstadoController::getIdEstadoByCodGeneral('HACIENDA_PENDIENTE');
                }
            }

            // Extraer información adicional de la respuesta (buscar en nivel raíz primero)
            $claveComprobante = $respuestaData['clave'] ?? $respuestaInterna['clave'] ?? null;
            
            // Actualizar el estado en la BD (siempre que tengamos un estado válido)
            if ($estadoHaciendaId !== null && $estadoHaciendaId > 0) {
                try {
                    DB::beginTransaction();
                    
                    $updateData = [
                        'estado_hacienda' => intval($estadoHaciendaId),
                        'json_respuesta' => json_encode($respuestaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    ];
                    
                    // Si viene la clave del comprobante, actualizarla también
                    if (!empty($claveComprobante)) {
                        $updateData['num_comprobante'] = $claveComprobante;
                    }
                    
                    // Verificar el estado actual antes de actualizar
                    $rowsAffected = DB::table('fe_info')
                        ->where('id', '=', $idInfoFe)
                        ->update($updateData);

                    DB::commit();
                    
                    if ($rowsAffected > 0) {
                        // Verificar que realmente se actualizó
                        $feInfoActualizado = DB::table('fe_info')
                            ->where('id', '=', $idInfoFe)
                            ->first();
                        
                        $estadoActualizado = $feInfoActualizado->estado_hacienda ?? null;
                        $estadoNombre = null;
                        if ($estadoActualizado) {
                            $estadoRegistro = DB::table('sis_estado')
                                ->where('id', '=', $estadoActualizado)
                                ->first();
                            $estadoNombre = $estadoRegistro->nombre ?? 'N/A';
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    // Continuar con la respuesta aunque falle la actualización
                }
            }
            // Extraer información adicional (buscar en nivel raíz primero)
            $mensajeRespuestaHacienda = $respuestaData['mensajeRespuestaHacienda'] ?? $respuestaInterna['mensajeRespuestaHacienda'] ?? null;
            $codigoEstadoHacienda = $respuestaData['codigoEstadoHacienda'] ?? $respuestaInterna['codigoEstadoHacienda'] ?? null;
            // Re-obtener estadoHacienda para la respuesta (ya se obtuvo antes pero se reasigna aquí)
            $estadoHaciendaRespuesta = $respuestaData['estadoHacienda'] ?? $respuestaInterna['estadoHacienda'] ?? null;
            $estadoActual = $respuestaData['estadoActual'] ?? $respuestaInterna['estadoActual'] ?? null;
            
            // Si es CREADO sin estadoHacienda, marcar como tieneError para que se muestre como rechazado
            if ($esCreadoSinEstado) {
                $tieneError = true;
            }
            
            return $this->responseAjaxSuccess("Estado consultado exitosamente.", [
                'respuesta' => $respuestaData,
                'respuestaInterna' => $respuestaInterna,
                'estado' => $estadoActual ?? $estadoHaciendaRespuesta ?? $respuestaData['estado'] ?? null,
                'estadoHacienda' => $estadoHaciendaRespuesta,
                'estadoActual' => $estadoActual,
                'codigoEstadoHacienda' => $codigoEstadoHacienda,
                'estadoHaciendaId' => $estadoHaciendaId,
                'clave' => $claveComprobante,
                'tieneError' => $tieneError,
                'codigoError' => $respuestaData['codigoError'] ?? null,
                'mensajeError' => $respuestaData['mensajeError'] ?? ($esCreadoSinEstado ? 'Comprobante creado pero sin respuesta de Hacienda. Se puede reenviar.' : null),
                'mensajeRespuestaHacienda' => $mensajeRespuestaHacienda
            ]);

        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al consultar estado: " . $ex->getMessage(), []);
        }
    }

    /**
     * Reenvía el correo del comprobante electrónico usando la API de FactuX
     */
    public function reenviarCorreoFactuX(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idInfoFe = $request->input("idInfoFe");
        $remitentes = $request->input("remitentes", []);

        if ($idInfoFe == null || $idInfoFe == 0) {
            return $this->responseAjaxServerError("ID de información FE inválido", []);
        }

        try {
            // Obtener información FE
            $infoFe = DB::table('fe_info')->where('id', $idInfoFe)->first();
            
            if ($infoFe == null) {
                return $this->responseAjaxServerError("Información FE no encontrada", []);
            }

            // Verificar que tenga clave del comprobante
            if (empty($infoFe->num_comprobante)) {
                return $this->responseAjaxServerError("El comprobante no tiene clave asignada. Debe enviarse primero a Hacienda.", []);
            }

            $claveComprobante = $infoFe->num_comprobante;

            // Si no se proporcionaron remitentes, usar el correo guardado en fe_info
            if (empty($remitentes) || !is_array($remitentes)) {
                if (!empty($infoFe->correo)) {
                    $remitentes = [$infoFe->correo];
                } else {
                    return $this->responseAjaxServerError("Debe proporcionar al menos un correo para reenviar", []);
                }
            }

            // Validar que los correos sean válidos
            $remitentesValidos = [];
            foreach ($remitentes as $correo) {
                $correo = trim($correo);
                if (!empty($correo) && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $remitentesValidos[] = $correo;
                }
            }

            if (empty($remitentesValidos)) {
                return $this->responseAjaxServerError("No se proporcionaron correos válidos", []);
            }

            // Endpoint de FactuX para reenviar correo
            $endpoint = '/api/v1/comprobantes/clave/' . $claveComprobante . '/reenviar-correo';

            // Preparar payload
            $payload = [
                'remitentes' => $remitentesValidos
            ];

            // Hacer la petición usando el servicio centralizado
            $resultado = $this->factuXService->post($endpoint, $payload);

            if ($resultado['exito']) {
                return $this->responseAjaxSuccess("Correo reenviado exitosamente a: " . implode(', ', $remitentesValidos), [
                    'respuesta' => $resultado['respuesta'],
                    'remitentes' => $remitentesValidos
                ]);
            } else {
                // Intentar obtener más detalles del error
                $errorDetalle = $resultado['mensaje'];
                $respuesta = $resultado['respuesta'] ?? [];
                
                if (isset($respuesta['message'])) {
                    $errorDetalle = $respuesta['message'];
                } elseif (isset($respuesta['error'])) {
                    $errorDetalle = $respuesta['error'];
                } elseif (isset($respuesta['status'])) {
                    $errorDetalle = 'Status: ' . $respuesta['status'] . ($respuesta['message'] ?? '');
                }
                
                return $this->responseAjaxServerError("Error al reenviar correo (HTTP " . $resultado['http_code'] . "): " . $errorDetalle, []);
            }

        } catch (\Exception $e) {
            return $this->responseAjaxServerError("Error al procesar el reenvío de correo: " . $e->getMessage(), []);
        }
    }

    /**
     * Obtiene los pagos de órdenes que no tienen FE asociado
     */
    public function obtenerPagosSinFE(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        try {
            // Contar total de pagos para depuración
            $totalPagos = DB::table('pago_orden')->count();
            $pagosConFE = DB::table('pago_orden')
                ->join('fe_info', 'fe_info.id_pago', '=', 'pago_orden.id')
                ->count();
            
            // Usar NOT EXISTS para verificar que no haya ningún fe_info relacionado con el pago
            $pagos = DB::table('pago_orden')
                ->leftJoin('orden', 'orden.id', '=', 'pago_orden.orden')
                ->leftJoin('cliente', 'cliente.id', '=', 'pago_orden.cliente')
                ->leftJoin('cliente_fe_info', 'cliente_fe_info.cliente_id', '=', 'cliente.id')
                ->leftJoin('sucursal', 'sucursal.id', '=', 'orden.sucursal')
                ->select(
                    'pago_orden.id as id_pago',
                    'pago_orden.fecha_pago',
                    'pago_orden.total',
                    'pago_orden.subtotal',
                    'pago_orden.iva',
                    'orden.numero_orden',
                    'orden.id as id_orden',
                    'cliente.nombre as nombre_cliente',
                    'cliente_fe_info.identificacion as cedula_cliente',
                    'pago_orden.nombre_cliente as nombre_cliente_manual',
                    'sucursal.descripcion as nombre_sucursal'
                )
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('fe_info')
                        ->whereColumn('fe_info.id_pago', 'pago_orden.id');
                })
                ->orderBy('pago_orden.fecha_pago', 'DESC')
                ->get();

            // Formatear los datos para la respuesta
            $pagosFormateados = [];
            foreach ($pagos as $pago) {
                $nombreCliente = !empty($pago->nombre_cliente) ? $pago->nombre_cliente : 
                                (!empty($pago->nombre_cliente_manual) ? $pago->nombre_cliente_manual : 'Sin cliente');
                
                $pagosFormateados[] = [
                    'id_pago' => $pago->id_pago,
                    'id_orden' => $pago->id_orden,
                    'numero_orden' => $pago->numero_orden,
                    'fecha_pago' => $pago->fecha_pago,
                    'nombre_cliente' => $nombreCliente,
                    'cedula_cliente' => $pago->cedula_cliente ?? '',
                    'total' => $pago->total,
                    'subtotal' => $pago->subtotal,
                    'iva' => $pago->iva,
                    'nombre_sucursal' => $pago->nombre_sucursal ?? 'N/A'
                ];
            }

            $mensaje = "Pagos obtenidos correctamente. Total pagos: $totalPagos, Con FE: $pagosConFE, Sin FE: " . count($pagosFormateados);
            return $this->responseAjaxSuccess($mensaje, $pagosFormateados);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al obtener pagos sin FE: " . $ex->getMessage(), []);
        }
    }

    /**
     * Crea un registro de fe_info desde un pago sin FE asociado
     */
    public function crearFeDesdePago(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        try {
            $idPago = $request->input('id_pago');
            $idOrden = $request->input('id_orden');
            $cedula = $request->input('cedula', '');
            $nombre = $request->input('nombre', '');
            $correo = $request->input('correo', '');
            $clienteId = $request->input('cliente_id');
            $esComprobante = $request->input('es_comprobante', 0);

            // Validaciones
            if (!$idPago || !$idOrden) {
                return $this->responseAjaxServerError("Debe proporcionar el ID del pago y la orden.", []);
            }

            // Si NO es comprobante, validar que se proporcionen los datos del cliente
            if (!$esComprobante) {
                if (empty($cedula) || empty($nombre) || empty($correo)) {
                    return $this->responseAjaxServerError("Debe proporcionar cédula, nombre y correo.", []);
                }
            }

            // Validar que el pago existe
            $pago = DB::table('pago_orden')->where('id', $idPago)->first();
            if (!$pago) {
                return $this->responseAjaxServerError("El pago no existe.", []);
            }

            // Validar que la orden existe
            $orden = DB::table('orden')->where('id', $idOrden)->first();
            if (!$orden) {
                return $this->responseAjaxServerError("La orden no existe.", []);
            }

            // Validar que el pago no tenga ya un fe_info asociado
            $feInfoExistente = DB::table('fe_info')->where('id_pago', $idPago)->first();
            if ($feInfoExistente) {
                return $this->responseAjaxServerError("Este pago ya tiene una factura electrónica asociada.", []);
            }

            // Si se proporciona cliente_id y NO es comprobante, intentar obtener información adicional del cliente
            if ($clienteId && !$esComprobante) {
                $clienteFeInfo = DB::table('cliente_fe_info')
                    ->where('cliente_id', $clienteId)
                    ->first();
                
                // Si el cliente tiene información FE y no se proporcionó cédula, usar la del cliente
                if ($clienteFeInfo && $clienteFeInfo->identificacion && empty($cedula)) {
                    $cedula = $clienteFeInfo->identificacion;
                }
            }

            // Si es comprobante, dejar campos vacíos
            if ($esComprobante) {
                $cedula = '';
                $nombre = '';
                $correo = '';
            }

            // Crear el registro de fe_info
            $idFeInfo = DB::table('fe_info')->insertGetId([
                'id' => null,
                'orden' => $idOrden,
                'cedula' => $cedula,
                'nombre' => $nombre,
                'correo' => $correo,
                'estado' => \App\Http\Controllers\SisEstadoController::getIdEstadoByCodGeneral('FE_ORDEN_PEND'),
                'num_comprobante' => '',
                'id_pago' => $idPago
            ]);

            // Actualizar la orden para marcar que tiene factura electrónica
            DB::table('orden')
                ->where('id', $idOrden)
                ->update(['factura_electronica' => 'S']);

            $mensaje = $esComprobante ? "Comprobante electrónico creado exitosamente." : "Factura electrónica creada exitosamente.";
            
            return $this->responseAjaxSuccess($mensaje, [
                'id_fe_info' => $idFeInfo,
                'id_pago' => $idPago,
                'id_orden' => $idOrden
            ]);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al crear la factura electrónica: " . $ex->getMessage(), []);
        }
    }

    /**
     * Actualiza los datos del cliente en fe_info
     */
    public function actualizarClienteFeInfo(Request $request)
    {
        if (!$this->validarSesion("fe_fes")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        try {
            $idInfoFe = $request->input('id_info_fe');
            $cedula = $request->input('cedula', '');
            $nombre = $request->input('nombre', '');
            $correo = $request->input('correo', '');
            $clienteId = $request->input('cliente_id');

            // Validaciones
            if (!$idInfoFe || $idInfoFe == 0) {
                return $this->responseAjaxServerError("ID de información FE inválido.", []);
            }

            // Validar que el fe_info existe
            $feInfo = DB::table('fe_info')->where('id', $idInfoFe)->first();
            if (!$feInfo) {
                return $this->responseAjaxServerError("Información FE no encontrada.", []);
            }

            // Si se proporciona cliente_id, intentar obtener información adicional del cliente
            if ($clienteId) {
                $clienteFeInfo = DB::table('cliente_fe_info')
                    ->where('cliente_id', $clienteId)
                    ->first();
                
                // Si el cliente tiene información FE y no se proporcionó cédula, usar la del cliente
                if ($clienteFeInfo && $clienteFeInfo->identificacion && empty($cedula)) {
                    $cedula = $clienteFeInfo->identificacion;
                }
            }

            // Actualizar fe_info
            DB::table('fe_info')
                ->where('id', $idInfoFe)
                ->update([
                    'cedula' => $cedula,
                    'nombre' => $nombre,
                    'correo' => $correo
                ]);

            return $this->responseAjaxSuccess("Datos del cliente actualizados exitosamente.", [
                'id_fe_info' => $idInfoFe
            ]);

        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al actualizar los datos del cliente: " . $ex->getMessage(), []);
        }
    }

}
