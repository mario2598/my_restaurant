<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MateriaPrimaController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index() {}

    public function goProductos()
    {
        if (!$this->validarSesion("mt_product")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'impuesto' => 'T',
            'categoria' => "T",
            'proveedor' => 'T',
        ];

        $data = [
            'filtros' => $filtros,
            'productos' => $this->getProductosMatPrima(),
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('materiaPrima.productos', compact('data'));
    }

    public function goProductosFiltro(Request $request)
    {
        if (!$this->validarSesion("mt_product")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $proveedor = $request->input('proveedor');

        $filtros = [
            'impuesto' => 'T',
            'categoria' => "T",
            'proveedor' => $proveedor ?? 'T',
        ];

        $data = [
            'filtros' => $filtros,
            'productos' => $this->getProductosMatPrima($proveedor),
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('materiaPrima.productos', compact('data'));
    }

    public function goEditarProducto(Request $request)
    {
        if (!$this->validarSesion("mt_product")) {
            return redirect('/');
        }


        $id = $request->input('idProductoEditar');
        $materia_prima = DB::table('materia_prima')
            ->where('materia_prima.id', '=', $id)->get()->first();

        if ($materia_prima == null) {
            $this->setError('Editar Producto', 'No existe el producto a editar.');
            return redirect('materiaPrima/productos');
        }

        $data = [
            'producto' => $materia_prima,
            'proveedores' => $this->getProveedores(),
            'proveedor' => 0,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('materiaPrima.producto.editarProducto', compact('data'));
    }

    public function cargarProductoAjax(Request $request)
    {
        if (!$this->validarSesion("mt_product")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar esta acción.", []);
        }

        try {
            $id = $request->input('id');
            if ($id < 1 || $this->isEmpty($id)) {
                return $this->responseAjaxServerError("ID de producto inválido.", []);
            }

            $producto = DB::table('materia_prima')
                ->where('materia_prima.id', '=', $id)
                ->get()->first();

            if ($producto == null) {
                return $this->responseAjaxServerError("No existe el producto.", []);
            }

            return $this->responseAjaxSuccess("Producto cargado correctamente.", $producto);
        } catch (\Exception $ex) {
            return $this->responseAjaxServerError("Error al cargar el producto: " . $ex->getMessage(), []);
        }
    }

    public function eliminarProductoAjax(Request $request)
    {
        if (!$this->validarSesion("mt_product")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar esta acción.", []);
        }

        try {
            $id = $request->input('id');
            if ($id == null || $id == '' || $id < 1) {
                return $this->responseAjaxServerError("Identificador inválido.", []);
            }

            DB::beginTransaction();
            $producto = DB::table('materia_prima')->where('id', '=', $id)->get()->first();
            
            if ($producto == null) {
                DB::rollBack();
                return $this->responseAjaxServerError("No existe el producto a eliminar.", []);
            }

            DB::table('materia_prima')
                ->where('id', '=', $id)
                ->update(['activo' => 0]);

            DB::commit();
            return $this->responseAjaxSuccess("El producto se eliminó correctamente.", []);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Ocurrió un error eliminando el producto.", []);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error inesperado al eliminar el producto.", []);
        }
    }

    public function goInventarios()
    {
        if (!$this->validarSesion('mt_inv')) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => '',
        ];

        $data = [
            'filtros' => $filtros,
            'inventarios' => [],
            'sucursales' => $this->getSucursalesAndBodegas(),
            'productos_externos' => MateriaPrimaController::getProductos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('materiaPrima.inventario.inventarios', compact('data'));
    }

    public function goInventariosFiltro(Request $request)
    {
        if (!$this->validarSesion('mt_inv')) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');

        if ($this->isNull($filtroSucursal) || $filtroSucursal == '-1') {
            $this->setError("Buscar inventario", "Debe seleccionar la sucursal");
            return redirect('materiaPrima/inventario/inventarios');
        }

        $filtros = [
            'sucursal' => $filtroSucursal,
        ];
        $data = [
            'inventarios' => MateriaPrimaController::getInventario($filtroSucursal),
            'productos_externos' => MateriaPrimaController::getProductos(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('materiaPrima.inventario.inventarios', compact('data'));
    }

    public function cargarMateriPrimaInvSucursal(Request $request)
    {
        $filtroSucursal = $request->input('idSucursal');

        if ($this->isNull($filtroSucursal) || $filtroSucursal == '-1') {
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", "");
        }
        try {

            return $this->responseAjaxSuccess("", MateriaPrimaController::getInventario($filtroSucursal));
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    public function cargarMateriPrimaNotinSucursal(Request $request)
    {
        $filtroSucursal = $request->input('idSucursal');

        if ($this->isNull($filtroSucursal) || $filtroSucursal == '-1') {
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", "");
        }
        try {

            return $this->responseAjaxSuccess("", MateriaPrimaController::getProductosNoInSucursal($filtroSucursal));
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    public function goInventariosFiltroD($filtroSucursal)
    {
        if (!$this->validarSesion('mt_inv')) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        if ($this->isNull($filtroSucursal) || $filtroSucursal == '-1') {
            $this->setError("Buscar inventario", "Debe seleccionar la sucursal");
            return redirect('materiaPrima/inventario/inventarios');
        }

        $filtros = [
            'sucursal' => $filtroSucursal,
        ];
        $data = [
            'inventarios' => MateriaPrimaController::getInventario($filtroSucursal),
            'productos_externos' => MateriaPrimaController::getProductos(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('materiaPrima.inventario.inventarios', compact('data'));
    }

    public static function getInventario($sucursal)
    {

        if ($sucursal == null || $sucursal < 1) {
            return [];
        }

        $inventarios = DB::table('materia_prima')
            ->leftjoin('mt_x_sucursal', 'mt_x_sucursal.materia_prima', '=', 'materia_prima.id')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'mt_x_sucursal.sucursal')
            ->leftjoin('proveedor', 'proveedor.id', '=', 'materia_prima.proveedor')
            ->select('materia_prima.id', 'mt_x_sucursal.id as ms_id', 'materia_prima.nombre', 'materia_prima.unidad_medida', 'mt_x_sucursal.cantidad', 'proveedor.nombre as nombre_prov')
            ->groupBy('materia_prima.id', 'mt_x_sucursal.id',  'materia_prima.nombre',  'mt_x_sucursal.cantidad', 'proveedor.nombre', 'materia_prima.unidad_medida')
            ->where('mt_x_sucursal.sucursal', '=', $sucursal)
            ->get();

        return $inventarios;
    }


    public static function getProductos()
    {

        $productos = DB::table('materia_prima')
            ->leftjoin('proveedor', 'proveedor.id', '=', 'materia_prima.proveedor')
            ->select('materia_prima.*', 'proveedor.nombre as nombre_prov')
            ->where('materia_prima.activo', '=', 1)
            ->get();

        return $productos;
    }

    public static function getProductosNoInSucursal($idSucursal)
    {
        $productos = DB::table('materia_prima')
            ->leftJoin('mt_x_sucursal', function ($join) use ($idSucursal) {
                $join->on('mt_x_sucursal.materia_prima', '=', 'materia_prima.id')
                    ->where('mt_x_sucursal.sucursal', '=', $idSucursal);
            })
            ->leftJoin('proveedor', 'proveedor.id', '=', 'materia_prima.proveedor')
            ->select('materia_prima.*', 'proveedor.nombre as nombre_prov')
            ->whereNull('mt_x_sucursal.id') // Solo trae los que no están en mt_x_sucursal para esa sucursal
            ->where('materia_prima.activo', '=', 1)
            ->get();

        return $productos;
    }

    public function getProductosMatPrima($proveedor = null)
    {
        $query = DB::table('materia_prima')
            ->leftJoin('proveedor', 'proveedor.id', '=', 'materia_prima.proveedor')
            ->select('materia_prima.*', 'proveedor.nombre as nombre_proveedor')
            ->where('materia_prima.activo', '=', 1);

        if (!$this->isNull($proveedor) && $proveedor != 'T' && $proveedor != '') {
            if ($proveedor == 'S') {
                // Filtrar productos sin proveedor (proveedor es NULL)
                $query->whereNull('materia_prima.proveedor');
            } else {
                $query->where('materia_prima.proveedor', '=', $proveedor);
            }
        }

        return $query->get();
    }

    public function eliminarProducto(Request $request)
    {
        if (!$this->validarSesion("mt_product")) {
            $this->setError("Seguridad", "No tienes permisos para ingresar..");
            return redirect('/');
        }

        $id = $request->input('idProductoEliminar');
        if ($id == null || $id == '' || $id < 1) {
            $this->setError('Eliminar Producto', 'Identificador inválido.');
            return redirect('materiaPrima/productos');
        }
        try {
            DB::beginTransaction();
            $producto = DB::table('materia_prima')->where('id', '=', $id)->get()->first();
            if ($producto == null) {
                $this->setError('Eliminar Producto', 'No existe el producto a eliminar.');
                return redirect('materiaPrima/productos');
            } else {
                DB::table('materia_prima')
                    ->where('id', '=', $id)
                    ->update(['activo' => 0]);
            }
            DB::commit();
            $this->setSuccess('Eliminar Producto', 'El producto se elimino correctamente.');
            return redirect('materiaPrima/productos');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Producto', 'Ocurrio un error eliminando el producto.');
            return redirect('materiaPrima/productos');
        }
    }

    public function guardarProducto(Request $request)
    {
        if (!$this->validarSesion("mt_product")) {
            return redirect('/');
        }
        $actualizar = false;
        // dd($request->all());
        $id = $request->input('id');
        if ($id < 1 || $this->isNull($id)) { // Nuevo 
            $actualizar = false;
        } else { // Editar usuario
            $actualizar = true;
        }


        if ($this->validarProducto($request)) {

            $nombre = $request->input('nombre');
            $unidad_medida = $request->input('unidad_medida');
            $precio = $request->input('precio');
            $proveedor = $request->input('proveedor');
            // Si el proveedor está vacío o es -1, establecerlo como null
            if ($this->isNull($proveedor) || $this->isEmpty($proveedor) || $proveedor == '-1' || $proveedor == '') {
                $proveedor = null;
            }
            $min_deseado = $request->input('cant_min');


            DB::beginTransaction();

            if ($actualizar) { // Editar usuario
                DB::table('materia_prima')
                    ->where('id', '=', $id)
                    ->update([
                        'nombre' => $nombre,
                        'unidad_medida' => $unidad_medida,
                        'precio' => $precio,
                        'proveedor' => $proveedor,
                        'cant_min_deseada' => $min_deseado
                    ]);
            } else { // Nuevo usuario
                $id = DB::table('materia_prima')->insertGetId([
                    'id' => null,
                    'nombre' => $nombre,
                    'unidad_medida' => $unidad_medida,
                    'precio' => $precio,
                    'proveedor' => $proveedor,
                    'activo' => 1,
                    'cant_min_deseada' => $min_deseado
                ]);
            }

            DB::commit();

            if ($actualizar) { // Editar usuario
                $this->setSuccess('Guardar Producto', 'Se actualizo el producto correctamente.');
            } else { // Nuevo usuario
                $this->setSuccess('Guardar Producto', 'Producto creado correctamente.');
            }
            return $this->returnEditarProductoWithId($id);
        } else {
            if ($actualizar) {
                return $this->returnEditarProductoWithId($id);
            } else {
                // Si hay error al crear nuevo producto, redirigir a la lista
                return redirect('materiaPrima/productos');
            }
        }
    }

    public function guardarProductoAjax(Request $request)
    {
        if (!$this->validarSesion("mt_product")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar esta acción.", []);
        }

        try {
            $id = $request->input('id');
            $actualizar = ($id >= 1 && !$this->isNull($id));

            if (!$this->validarProducto($request)) {
                return $this->responseAjaxServerError("Por favor complete todos los campos requeridos.", []);
            }

            $nombre = $request->input('nombre');
            $unidad_medida = $request->input('unidad_medida');
            $precio = $request->input('precio');
            $proveedor = $request->input('proveedor');
            // Si el proveedor está vacío o es -1, establecerlo como null
            if ($this->isNull($proveedor) || $this->isEmpty($proveedor) || $proveedor == '-1' || $proveedor == '') {
                $proveedor = null;
            }
            $min_deseado = $request->input('cant_min') ?? 0;

            DB::beginTransaction();

            if ($actualizar) {
                DB::table('materia_prima')
                    ->where('id', '=', $id)
                    ->update([
                        'nombre' => $nombre,
                        'unidad_medida' => $unidad_medida,
                        'precio' => $precio,
                        'proveedor' => $proveedor,
                        'cant_min_deseada' => $min_deseado
                    ]);
                $mensaje = 'Se actualizó el producto correctamente.';
            } else {
                $id = DB::table('materia_prima')->insertGetId([
                    'id' => null,
                    'nombre' => $nombre,
                    'unidad_medida' => $unidad_medida,
                    'precio' => $precio,
                    'proveedor' => $proveedor,
                    'activo' => 1,
                    'cant_min_deseada' => $min_deseado
                ]);
                $mensaje = 'Producto creado correctamente.';
            }

            DB::commit();

            // Obtener el producto recién creado/actualizado
            $producto = DB::table('materia_prima')
                ->where('id', '=', $id)
                ->get()->first();

            return $this->responseAjaxSuccess($mensaje, [
                'producto' => $producto,
                'id' => $id
            ]);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error al guardar el producto: " . $ex->getMessage(), []);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error inesperado al guardar el producto.", []);
        }
    }

    public function returnEditarProductoWithId($id)
    {
        if (!$this->validarSesion("mt_product")) {
            return redirect('/');
        }

        if ($id < 1 || $this->isEmpty($id)) {
            $this->setError("Error", "El producto no existe..");
            return redirect('materiaPrima/productos');
        }

        $producto = DB::table('materia_prima')
            ->where('materia_prima.id', '=', $id)->get()->first();

        if ($producto == null) {
            $this->setError('Editar Producto', 'No existe el producto a editar.');
            return redirect('materiaPrima/productos');
        }

        $data = [
            'proveedores' => $this->getProveedores(),
            'producto' => $producto,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('materiaPrima.producto.editarProducto', compact('data'));
    }

    public function validarProducto(Request $r)
    {
        $requeridos = "[";
        $valido = true;

        if ($this->isNull($r->input('nombre')) || $this->isEmpty($r->input('nombre'))) {
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        // Proveedor ya no es requerido
        if ($this->isNull($r->input('unidad_medida')) || $this->isEmpty($r->input('unidad_medida'))) {
            $requeridos .= " Unidad de medida ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('precio'))) {
            $requeridos .= " Precio ";
            $valido = false;
            $esPrimero = false;
        }

        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        return $valido;
    }

    public function crearProductoSucursal(Request $request)
    {
        $producto_externo = $request->input('producto_externo');
        $sucursal = $request->input('sucursal_agregar_id');
        $cantidad_agregar = $request->input('cantidad_agregar');
        $fecha_actual = date("Y-m-d H:i:s");

        // Validación de que la sucursal es válida
        if ($sucursal < 1 || $this->isNull($sucursal)) {
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", "");
        }
        $sucursalAux = DB::table('sucursal')->select('sucursal.*')->where('id', '=', $sucursal)->first();

        if ($sucursalAux == null) {
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", "");
        }

        // Validación de que el producto es válido
        if ($producto_externo < 1 || $this->isNull($producto_externo)) {
            return $this->responseAjaxServerError("Debe seleccionar el producto", "");
        }
        $producto_inv = DB::table('materia_prima')->select('materia_prima.*')->where('id', '=', $producto_externo)->first();

        if ($producto_inv == null) {
            return $this->responseAjaxServerError("Debe seleccionar el producto", "");
        }

        // Validación de cantidad
        if ($cantidad_agregar <= 0 || $this->isNull($cantidad_agregar)) {
            return $this->responseAjaxServerError("La cantidad debe ser mayor a 0", "");
        }

        // Validación de que el producto no esté previamente en el inventario de la sucursal
        $productoExistente = DB::table('mt_x_sucursal')
            ->where('sucursal', '=', $sucursal)
            ->where('materia_prima', '=', $producto_externo)
            ->first();

        if ($productoExistente) {
            return $this->responseAjaxServerError("El producto ya existe en el inventario de esta sucursal", "");
        }

        try {
            DB::beginTransaction();

            // Inserción del producto en la tabla de inventario de la sucursal
            $id = DB::table('mt_x_sucursal')->insertGetId([
                'id' => null,
                'sucursal' => $sucursal,
                'materia_prima' => $producto_externo,
                'cantidad' => $cantidad_agregar,
                'ultima_modificacion' => $fecha_actual,
                'usuario_modifica' => session('usuario')['id']
            ]);

            $cantidadInventario = 0;
            $cantidadDisminuye = $cantidad_agregar;
            $texto = "Ingreso en inventario en " . ($cantidad_agregar - $cantidadInventario) . " " . $producto_inv->unidad_medida;

            $detalleMp = 'Producto Externo : ' . $producto_inv->nombre .
                ' | Detalle :' . $texto;

            $fechaActual = date("Y-m-d H:i:s");

            // Registro en la bitácora de materia prima
            DB::table('bit_materia_prima')->insert([
                'id' => null,
                'usuario' => session('usuario')['id'],
                'materia_prima' => $producto_externo,
                'detalle' => $detalleMp,
                'cantidad_anterior' => $cantidadInventario ?? 0,
                'cantidad_ajuste' => $cantidadDisminuye,
                'cantidad_nueva' => $cantidad_agregar,
                'fecha' => $fechaActual,
                'sucursal' => $this->getUsuarioSucursal()
            ]);

            DB::commit();

            return $this->responseAjaxSuccess("Producto agregado al inventario correctamente", "");
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error agregando el producto al inventario de Materia Prima", "");
        }
    }

    public function aumentarProductoSucursal(Request $request)
    {

        $id = $request->input('pe_id');
        $producto_externo = $request->input('producto_externo');
        $sucursal = $request->input('sucursal_agregar_id');
        $cantidad_agregar = $request->input('cantidad_agregar');
        $producto_externo = $request->input('prodExt');
        $fecha_actual = date("Y-m-d H:i:s");
        // Validación de que la sucursal es válida
        if ($sucursal < 1 || $this->isNull($sucursal)) {
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", "");
        }
        $sucursalAux = DB::table('sucursal')->select('sucursal.*')->where('id', '=', $sucursal)->first();

        if ($sucursalAux == null) {
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", "");
        }

        // Validación de que el producto es válido
        if ($producto_externo < 1 || $this->isNull($producto_externo)) {
            return $this->responseAjaxServerError("Debe seleccionar el producto", "");
        }
        $producto_inv = DB::table('materia_prima')->select('materia_prima.*')->where('id', '=', $producto_externo)->first();

        if ($producto_inv == null) {
            return $this->responseAjaxServerError("Debe seleccionar el producto", "");
        }

        // Validación de cantidad
        if ($cantidad_agregar <= 0 || $this->isNull($cantidad_agregar)) {
            return $this->responseAjaxServerError("La cantidad debe ser mayor a 0", "");
        }

        // Validación de que el producto no esté previamente en el inventario de la sucursal
        $productoExistente = DB::table('mt_x_sucursal')
            ->where('sucursal', '=', $sucursal)
            ->where('materia_prima', '=', $producto_externo)
            ->first();

        if (!$productoExistente) {
            return $this->responseAjaxServerError("El producto no existe en el inventario de esta sucursal", "");
        }

        try {
            DB::beginTransaction();
            $cantidadInventario = $productoExistente->cantidad;

            DB::table('mt_x_sucursal')
                ->where('id', '=', $id)
                ->update([
                    'cantidad' => $cantidadInventario + $cantidad_agregar,
                    'ultima_modificacion' => $fecha_actual,
                    'usuario_modifica' => session('usuario')['id']
                ]);


            $texto = "Aumento de inventario materia prima en " . ($cantidadInventario) . " " . $producto_inv->unidad_medida;

            $detalleMp =  'Materia Prima : ' . $producto_inv->nombre .
                ' | Detalle :' . $texto;

            $fechaActual = date("Y-m-d H:i:s");


            DB::table('bit_materia_prima')->insert([
                'id' => null,
                'usuario' => session('usuario')['id'],
                'materia_prima' => $producto_externo,
                'detalle' => $detalleMp,
                'cantidad_anterior' =>  $cantidadInventario ?? 0,
                'cantidad_ajuste' => $cantidad_agregar,
                'cantidad_nueva' =>  $cantidadInventario + $cantidad_agregar,
                'fecha' => $fechaActual,
                'sucursal' => $this->getUsuarioSucursal()
            ]);

            DB::commit();
            $this->setSuccess('Aumento en inventario', 'Se actualizó el inventario correctamente');
            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MateriaPrimaController',
                'descripcion' => $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error aumentando el producto en el inventario de Materia Prima", "");
        }
    }

    public function disminuirProductoSucursal(Request $request)
    {
        $id = $request->input('pe_id');
        $producto_externo = $request->input('producto_externo');
        $sucursal = $request->input('sucursal_agregar_id');
        $cantidad_agregar = $request->input('cantidad_agregar');
        $producto_externo = $request->input('prodExt');
        $fecha_actual = date("Y-m-d H:i:s");

        // Validación de que la sucursal es válida
        if ($sucursal < 1 || $this->isNull($sucursal)) {
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", "");
        }
        $sucursalAux = DB::table('sucursal')->select('sucursal.*')->where('id', '=', $sucursal)->first();

        if ($sucursalAux == null) {
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", "");
        }

        // Validación de que el producto es válido
        if ($producto_externo < 1 || $this->isNull($producto_externo)) {
            return $this->responseAjaxServerError("Debe seleccionar el producto", "");
        }
        $producto_inv = DB::table('materia_prima')->select('materia_prima.*')->where('id', '=', $producto_externo)->first();

        if ($producto_inv == null) {
            return $this->responseAjaxServerError("Debe seleccionar el producto", "");
        }

        // Validación de cantidad
        if ($cantidad_agregar <= 0 || $this->isNull($cantidad_agregar)) {
            return $this->responseAjaxServerError("La cantidad debe ser mayor a 0", "");
        }

        // Validación de que el producto no esté previamente en el inventario de la sucursal
        $productoExistente = DB::table('mt_x_sucursal')
            ->where('sucursal', '=', $sucursal)
            ->where('materia_prima', '=', $producto_externo)
            ->first();

        if (!$productoExistente) {
            return $this->responseAjaxServerError("El producto no existe en el inventario de esta sucursal", "");
        }

        if ($cantidad_agregar > $productoExistente->cantidad) {
            return $this->responseAjaxServerError("La cantidad a rebajar excede la cantidad disponible en inventario", "");
        }

        try {
            DB::beginTransaction();
            $cantidadInventario = $productoExistente->cantidad;
            DB::table('mt_x_sucursal')
                ->where('id', '=', $id)
                ->update([
                    'cantidad' => $cantidadInventario - $cantidad_agregar,
                    'ultima_modificacion' => $fecha_actual,
                    'usuario_modifica' => session('usuario')['id']
                ]);


            $texto = "Disminuye inventario materia prima en " . ($cantidad_agregar) . " " . $producto_inv->unidad_medida;

            $detalleMp =  'Materia Prima : ' . $producto_inv->nombre .
                ' | Detalle :' . $texto;
            $fechaActual = date("Y-m-d H:i:s");


            DB::table('bit_materia_prima')->insert([
                'id' => null,
                'usuario' => session('usuario')['id'],
                'materia_prima' => $producto_externo,
                'detalle' => $detalleMp,
                'cantidad_anterior' =>  $cantidadInventario ?? 0,
                'cantidad_ajuste' => $cantidad_agregar,
                'cantidad_nueva' =>  $cantidadInventario + $cantidad_agregar,
                'fecha' => $fechaActual,
                'sucursal' => $this->getUsuarioSucursal()
            ]);

            DB::commit();
            $this->setSuccess('Aumento en inventario', 'Se actualizó el inventario correctamente');
            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            DB::rollBack();
            DB::table('log')->insertGetId([
                'id' => null,
                'documento' => 'MateriaPrimaController',
                'descripcion' => $ex->getMessage()
            ]);
            return $this->responseAjaxServerError("Error disminuyendo el producto en el inventario de Materia Prima", "");
        }
    }
}
