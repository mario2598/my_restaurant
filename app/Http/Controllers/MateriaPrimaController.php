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

    public function index()
    {
    }

    public function goProductos()
    {
        if (!$this->validarSesion("mt_product")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'impuesto' => 'T',
            'categoria' => "T",
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'filtros' => $filtros,
            'productos' => $this->getProductosMatPrima(),
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('materiaPrima.productos', compact('data'));
    }

    public function goNuevoProducto()
    {
        if (!$this->validarSesion("mt_product")) {
            return redirect('/');
        }

        $datos = [];
        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => $datos,
            'proveedores' => $this->getProveedores(),
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('materiaPrima.producto.nuevoProducto', compact('data'));
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
            'menus' => $this->cargarMenus(),
            'producto' => $materia_prima,
            'proveedores' => $this->getProveedores(),
            'proveedor' => 0,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('materiaPrima.producto.editarProducto', compact('data'));
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
            'menus' => $this->cargarMenus(),
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
            'menus' => $this->cargarMenus(),
            'inventarios' => MateriaPrimaController::getInventario($filtroSucursal),
            'productos_externos' => MateriaPrimaController::getProductos(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('materiaPrima.inventario.inventarios', compact('data'));
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
            'menus' => $this->cargarMenus(),
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



    public function getProductosMatPrima()
    {
        return  DB::table('materia_prima')
            ->select('materia_prima.*')
            ->where('materia_prima.activo', '=', 1)
            ->get();
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
            $min_deseado = $request->input('cant_min');


            DB::beginTransaction();

            if ($actualizar) { // Editar usuario
                DB::table('materia_prima')
                    ->where('id', '=', $id)
                    ->update([
                        'nombre' => $nombre, 'unidad_medida' => $unidad_medida, 'precio' => $precio,
                        'proveedor' => $proveedor,'cant_min_deseada' => $min_deseado
                    ]);
            } else { // Nuevo usuario
                $id = DB::table('materia_prima')->insertGetId([
                    'id' => null,  'nombre' => $nombre, 'unidad_medida' => $unidad_medida, 'precio' => $precio,
                    'proveedor' => $proveedor, 'activo' => 1,'cant_min_deseada' => $min_deseado
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
                return $this->returnNuevoProductoWithData($request->all());
            }
        }
    }

    public function returnNuevoProductoWithData($datos)
    {
        if (!$this->validarSesion("prod_mnu")) {
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => $datos,
            'proveedores' => $this->getProveedores(),
            'proveedor' => 0,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('materiaPrima.producto.nuevoProducto', compact('data'));
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
            'menus' => $this->cargarMenus(),
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
        if ($this->isNull($r->input('proveedor')) || $this->isEmpty($r->input('proveedor'))) {
            $requeridos .= " Proveedor ";
            $valido = false;
            $esPrimero = false;
        }
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

    public function guardarProductoSucursal(Request $request)
    {
        if (!$this->validarSesion("mt_inv")) {
            return redirect('/');
        }

        $id = $request->input('pe_id');
        $producto_externo = $request->input('producto_externo');
        $sucursal = $request->input('sucursal_agregar_id');
        $cantidad_agregar = $request->input('cantidad_agregar');
        $fecha_actual = date("Y-m-d H:i:s");
        if ($sucursal < 1 || $this->isNull($sucursal)) { //  
            $this->setError('Agregar Producto', 'Debe seleccionar la sucursal.');
            return redirect('productoExterno/inventario/inventarios');
        }
        $sucursalAux = DB::table('sucursal')->select('sucursal.*')->where('id', '=', $sucursal)->get()->first();
        if ($sucursalAux == null) { //  
            $this->setError('Agregar Producto', 'Debe seleccionar la sucursal.');
            return $this->goInventariosFiltroD($sucursal);
        }

        if ($producto_externo < 1 || $this->isNull($producto_externo)) { //  
            $this->setError('Agregar Producto', 'Debe seleccionar el producto.');
            return $this->goInventariosFiltroD($sucursal);
        }
        $producto_inv = DB::table('materia_prima')->select('materia_prima.*')
            ->where('id', '=', $producto_externo)->get()->first();
        if ($producto_inv == null) { //  
            $this->setError('Agregar Producto', 'Debe seleccionar el producto.');
            return $this->goInventariosFiltroD($sucursal);
        }
        
        if ($cantidad_agregar < 0 || $this->isNull($cantidad_agregar)) { //  
            $this->setError('Agregar Producto', 'La cantidad debe ser mayor a -1.');
            return $this->goInventariosFiltroD($sucursal);
        }

        if ($id < 1 || $this->isNull($id)) { //
            $productoExistente = DB::table('mt_x_sucursal')->select('mt_x_sucursal.*')->where('materia_prima', '=', $producto_externo)
                ->where('sucursal', '=', $sucursal)->get()->first();
            if ($productoExistente == null) { //  
                $actualizar = false;
            } else {
                $this->setError('Agregar Producto', 'Ya existe un producto registrado para la sucursal.');
                return $this->goInventariosFiltroD($sucursal);
            }
        } else { // Editar usuario
            $producto = DB::table('mt_x_sucursal')->select('mt_x_sucursal.*')->where('id', '=', $id)->get()->first();

            if ($producto == null) {
                $this->setError('Agregar Producto', 'No existe un producto con los credenciales.');
                return $this->goInventariosFiltroD($sucursal);
            }
            $actualizar = true;
        }

        try {
            DB::beginTransaction();

            if ($actualizar) { // Editar usuario
                DB::table('mt_x_sucursal')
                    ->where('id', '=', $id)
                    ->update([
                        'cantidad' => $cantidad_agregar, 'ultima_modificacion' => $fecha_actual, 'usuario_modifica' => session('usuario')['id']
                    ]);

                $cantidadInventario = $producto->cantidad;
                $cantidadDisminuye = 0;
                $texto = "";
                if ($cantidadInventario < $cantidad_agregar) {
                    $texto = "Aumento de inventario materia prima en " . ($cantidad_agregar - $cantidadInventario) . " " . $producto_inv->unidad_medida;
                    $cantidadDisminuye = ($cantidad_agregar - $cantidadInventario);
                } else {
                    $texto = "Disminuye inventario materia prima en " . ($cantidadInventario  - $cantidad_agregar) . " " . $producto_inv->unidad_medida;
                    $cantidadDisminuye = ($cantidadInventario  - $cantidad_agregar);
                }

                $detalleMp =  'Materia Prima : ' . $producto_inv->nombre .
                    ' | Detalle :' . $texto;
            } else { // Nuevo usuario
                $id = DB::table('mt_x_sucursal')->insertGetId([
                    'id' => null, 'sucursal' => $sucursal, 'materia_prima' => $producto_externo,
                    'cantidad' => $cantidad_agregar, 'ultima_modificacion' => $fecha_actual, 'usuario_modifica' => session('usuario')['id']
                ]);

                $cantidadInventario = 0;
                $cantidadDisminuye = $cantidad_agregar;
                $texto = "Ingreso en inventario en " . ($cantidad_agregar - $cantidadInventario) . " " . $producto_inv->unidad_medida;

                $detalleMp =  'Producto Externo : ' . $producto_inv->nombre .
                    ' | Detalle :' . $texto;
            }

            $fechaActual = date("Y-m-d H:i:s");


            DB::table('bit_materia_prima')->insert([
                'id' => null, 'usuario' => session('usuario')['id'],
                'materia_prima' => $producto_externo, 'detalle' => $detalleMp, 'cantidad_anterior' =>  $cantidadInventario ?? 0,
                'cantidad_ajuste' => $cantidadDisminuye, 'cantidad_nueva' =>  $cantidad_agregar,'fecha' => $fechaActual ,'sucursal' => $this->getUsuarioSucursal()
            ]);

            DB::commit();


            if ($actualizar) { // Editar usuario
                $this->setSuccess('Agregar Producto', 'Se actualizo el producto correctamente.');
            } else { // Nuevo usuario

                $this->setSuccess('Agregar Producto', 'Producto agregado correctamente.');
            }
            return $this->goInventariosFiltroD($sucursal);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Agregar Producto', 'Algo salio mal...');
            return redirect('productoExterno/inventario/inventarios');
        }
    }
}
