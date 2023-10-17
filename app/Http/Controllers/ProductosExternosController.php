<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class ProductosExternosController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "bodProductos";
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function goProductosExternos()
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'impuesto' => 'T',
            'categoria' => "T",
            'proveedor' => "T",
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'filtros' => $filtros,
            'productos' => [],
            'materia_prima' => MateriaPrimaController::getProductos(),
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('productoExterno.productos', compact('data'));
    }

    public function cargarMpProd(Request $request)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            $this->setMsjSeguridad();
            return $this->responseAjaxServerError("Error de Seguridad");
        }

        $id_prod_seleccionado = $request->input('id_prod_seleccionado');

        if ($this->isNull($id_prod_seleccionado) || $id_prod_seleccionado == '-1') {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        try {
            $mat_prim = DB::table('materia_prima')
                ->leftjoin('mt_x_producto_ext', 'mt_x_producto_ext.materia_prima', '=', 'materia_prima.id')
                ->select(
                    'materia_prima.*',
                    'mt_x_producto_ext.cantidad',
                    'mt_x_producto_ext.id as id_mp_x_prod'
                )
                ->where('mt_x_producto_ext.producto', '=', $id_prod_seleccionado)
                ->get();
            return $this->responseAjaxSuccess("", $mat_prim);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    public function eliminarMpProd(Request $request)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            $this->setMsjSeguridad();
            return $this->responseAjaxServerError("Error de Seguridad");
        }

        $id_prod_mp = $request->input('id_prod_mp');

        if ($this->isNull($id_prod_mp) || $id_prod_mp == '-1') {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        try {
            DB::beginTransaction();
            DB::table('mt_x_producto_ext')->where('id', '=', $id_prod_mp)->delete();

            DB::commit();
            return $this->responseAjaxSuccess();
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    public function guardarMpProd(Request $request)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            $this->setMsjSeguridad();
            return $this->responseAjaxServerError("Error de Seguridad");
        }

        $id_prod_mp = $request->input('id_mp_prod');
        $id_prod = $request->input('id_prod');
        $id_prod_seleccionado = $request->input('id_prod_seleccionado');
        $cant = $request->input('cant');
        $nuevo = false;

        if ($this->isNull($id_prod_seleccionado) || $id_prod_seleccionado == '-1') {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        $producto_menu = DB::table('producto_externo')
            ->select('producto_externo.id', 'producto_externo.estado')
            ->where('producto_externo.id', '=', $id_prod_seleccionado)
            ->get()->first();

        if ($this->isNull($producto_menu)) {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        if ($this->isNull($cant) || $cant <= 0) {
            return $this->responseAjaxServerError("Cantidad incorrecta");
        }

        if ($this->isNull($id_prod)) {
            return $this->responseAjaxServerError("No se encontró el producto de materia prima");
        }

        $materia_prima = DB::table('materia_prima')
            ->select('materia_prima.*')
            ->where('materia_prima.id', '=', $id_prod)
            ->get()->first();

        if ($this->isNull($materia_prima)) {
            return $this->responseAjaxServerError("No se encontró el producto de materia prima");
        }


        $mt_x_producto = DB::table('mt_x_producto_ext')
            ->select('mt_x_producto_ext.*')
            ->where('mt_x_producto_ext.materia_prima', '=', $id_prod)
            ->where('mt_x_producto_ext.producto', '=', $id_prod_seleccionado)
            ->get()->first();

        if ($this->isNull($mt_x_producto)) {
            $nuevo = true;
        } else {
            $nuevo = false;
        }

        try {
            DB::beginTransaction();
            if ($nuevo) {
                $mt_x_producto1 = DB::table('mt_x_producto_ext')
                    ->insertGetId([
                        'id' => null, 'materia_prima' => $id_prod, 'producto' => $id_prod_seleccionado,
                        'cantidad' => $cant
                    ]);
            } else {
                DB::table('mt_x_producto_ext')
                    ->where('id', '=', $mt_x_producto->id)
                    ->update(['cantidad' => $cant]);
            }

            DB::commit();
            return $this->responseAjaxSuccess();
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }



    public function goProductosExternosFiltro(Request $request)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $categoria = $request->input('categoria');
        $impuesto = $request->input('impuesto');
        $proveedor = $request->input('proveedor');

        $productos = DB::table('producto_externo')
            ->leftJoin('categoria', 'categoria.id', '=', 'producto_externo.categoria')
            ->leftJoin('impuesto', 'impuesto.id', '=', 'producto_externo.impuesto')
            ->leftJoin('proveedor', 'proveedor.id', '=', 'producto_externo.proveedor')
            ->select('producto_externo.*', 'impuesto.impuesto as porcentaje_impuesto', 'categoria.categoria as nombre_categoria')
            ->where('producto_externo.estado', '=', 'A');

        if (!$this->isNull($categoria) && $categoria != 'T') {
            $productos = $productos->where('categoria.id', '=', $categoria);
        }
        if (!$this->isNull($impuesto) && $impuesto != 'T') {
            $productos = $productos->where('impuesto.id', '=', $impuesto);
        }
        if (!$this->isNull($proveedor) && $proveedor != 'T') {
            $productos = $productos->where('proveedor.id', '=', $proveedor);
        }


        $productos = $productos->get();

        $filtros = [
            'impuesto' => $impuesto,
            'categoria' => $categoria,
            'proveedor' => $proveedor,
        ];
        //  dd($productos);
        $data = [
            'menus' => $this->cargarMenus(),
            'productos' => $productos,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'materia_prima' => MateriaPrimaController::getProductos(),
            'proveedores' => $this->getProveedores(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productoExterno.productos', compact('data'));
    }

    public function goNuevoProducto()
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            return redirect('/');
        }

        $datos = [];
        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => $datos,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productoExterno.nuevo', compact('data'));
    }


    public function returnNuevoProductoWithData($datos)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            return redirect('/');
        }


        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => $datos,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productoExterno.nuevo', compact('data'));
    }

    public function returnEditarProductoWithId($id)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            return redirect('/');
        }

        if ($id < 1 || $this->isEmpty($id)) {
            $this->setError("Error", "El producto no existe..");
            return redirect('productoExterno/productos');
        }

        $producto = DB::table('producto_externo')
            ->where('producto_externo.id', '=', $id)->get()->first();

        if ($producto == null) {
            $this->setError('Editar Producto', 'No existe el producto a editar.');
            return redirect('productoExterno/productos');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'producto' => $producto,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productoExterno.editar', compact('data'));
    }

    public function goEditarProducto(Request $request)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            return redirect('/');
        }


        $id = $request->input('idProductoEditar');
        $producto = DB::table('producto_externo')
            ->where('producto_externo.id', '=', $id)->get()->first();

        if ($producto == null) {
            $this->setError('Editar Producto', 'No existe el producto a editar.');
            return redirect('productoExterno/productos');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'producto' => $producto,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productoExterno.editar', compact('data'));
    }

    /**
     * Guarda o actualiza un producto
     */
    public function guardarProducto(Request $request)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            return redirect('/');
        }

        // dd($request->all());
        $id = $request->input('id');
        $codigo_barra = $request->input('codigo');
        $producto = DB::table('producto_externo')->select('producto_externo.*')->where('id', '=', $id)->get()->first();

        if ($id < 1 || $this->isNull($id)) { // Nuevo 
            if ($this->codigoBarraRegistrado($codigo_barra)) {
                $this->setError('Guardar Producto', 'El código de barra ya esta en uso.');
                return $this->returnNuevoProductoWithData($request->all());
            }
            $actualizar = false;
        } else { // Editar usuario

            if ($producto == null) {
                $this->setError('Guardar Producto', 'No existe un producto con los credenciales.');
                return $this->returnEditarProductoWithId($id);
            }
            if ($producto->codigo_barra != $codigo_barra) {
                if ($this->codigoBarraRegistrado($codigo_barra)) {
                    $this->setError('Guardar Producto', 'El código de barra ya esta en uso.');
                    return $this->returnEditarProductoWithId($id);
                }
            }
            $actualizar = true;
        }


        if ($this->validarProducto($request)) {

            $categoria = $request->input('categoria');
            $nombre = $request->input('nombre');
            $precio = $request->input('precio');
            $descripcion = $request->input('descripcion');
            $proveedor = $request->input('proveedor');
            $impuesto = $request->input('impuesto');
            $precio_compra = $request->input('precio_compra');

            $image = $request->file('foto_producto');
            if ($image != null) {
                $path = $image->store('productos', 'public');
            } else {
                if ($actualizar) {
                    $path = $producto->url_imagen;
                }else{
                    $path = "";
                }
               
            }
            try {
                DB::beginTransaction();

                if ($actualizar) { // Editar usuario
                    DB::table('producto_externo')
                        ->where('id', '=', $id)
                        ->update([
                            'nombre' => $nombre, 'categoria' => $categoria, 'precio' => $precio,
                            'impuesto' => $impuesto, 'precio_compra' => $precio_compra, 'codigo_barra' => $codigo_barra, 'proveedor' => $proveedor,
                             'descripcion' => $descripcion, 'url_imagen' => $path
                        ]);
                } else { // Nuevo usuario
                    $id = DB::table('producto_externo')->insertGetId([
                        'id' => null, 'nombre' => $nombre, 'categoria' => $categoria, 'precio' => $precio,
                        'impuesto' => $impuesto, 'precio_compra' => $precio_compra, 'codigo_barra' => $codigo_barra, 
                        'proveedor' => $proveedor, 'estado' => 'A', 'descripcion' => $descripcion ?? "", 'url_imagen' => $path
                    ]);
                }

                DB::commit();


                if ($actualizar) { // Editar usuario
                    $this->setSuccess('Guardar Producto', 'Se actualizo el producto correctamente.');
                } else { // Nuevo usuario

                    $this->setSuccess('Guardar Producto', 'Producto creado correctamente.');
                }
                return redirect('productoExterno/productos');
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Producto', 'Algo salio mal...');
                return redirect('productoExterno/productos');
            }
        } else {
            if ($actualizar) {
                return $this->returnEditarProductoWithId($id);
            } else {
                return $this->returnNuevoProductoWithData($request->all());
            }
        }
    }

    /**
     * Guarda o actualiza un producto
     */
    public function guardarProductoSucursal(Request $request)
    {
        if (!$this->validarSesion("prod_ext_prods")) {
            return $this->responseAjaxServerError("No tienes permisos", []);
        }

        $id = $request->input('pe_id');
        $producto_externo = $request->input('producto_externo');
        $sucursal = $request->input('sucursal_agregar_id');
        $cantidad_agregar = $request->input('cantidad_agregar');
        $fecha_actual = date("Y-m-d H:i:s");
        if ($sucursal < 1 || $this->isNull($sucursal)) { //  
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", []);
        }
        $sucursalAux = DB::table('sucursal')->select('sucursal.*')->where('id', '=', $sucursal)->get()->first();
        if ($sucursalAux == null) { //  
            return $this->responseAjaxServerError("Debe seleccionar la sucursal", []);
        }

        if ($producto_externo < 1 || $this->isNull($producto_externo)) { //  
            return $this->responseAjaxServerError("Debe seleccionar el producto", []);
        }
        $producto_externoAux = DB::table('producto_externo')->select('producto_externo.*')->where('id', '=', $producto_externo)->get()->first();
        if ($producto_externoAux == null) { //  
            return $this->responseAjaxServerError("Debe seleccionar el producto", []);
        }
        if ($cantidad_agregar < 1 || $this->isNull($cantidad_agregar)) { //  
            return $this->responseAjaxServerError("La cantidad debe ser mayor a 0", []);
        }

        if ($id < 1 || $this->isNull($id)) { //
            $productoExistente = DB::table('pe_x_sucursal')->select('pe_x_sucursal.*')->where('producto_externo', '=', $producto_externo)
                ->where('sucursal', '=', $sucursal)->get()->first();
            if ($productoExistente == null) { //  
                $actualizar = false;
            } else {
                return $this->responseAjaxServerError("Ya existe un producto registrado para la sucursal", []);
            }
        } else { // Editar usuario
            $producto = DB::table('pe_x_sucursal')->select('pe_x_sucursal.*')->where('id', '=', $id)->get()->first();

            if ($producto == null) {
                return $this->responseAjaxServerError("No existe un producto con los credenciales", []);
            }
            $actualizar = true;
        }

        try {
            DB::beginTransaction();

            if ($actualizar) { // Editar usuario
                DB::table('pe_x_sucursal')
                    ->where('id', '=', $id)
                    ->update([
                        'cantidad' => $cantidad_agregar, 'ultima_modificacion' => $fecha_actual, 'usuario_modifica' => session('usuario')['id']
                    ]);
            } else { // Nuevo usuario
                $id = DB::table('pe_x_sucursal')->insertGetId([
                    'id' => null, 'sucursal' => $sucursal, 'producto_externo' => $producto_externo, 'cantidad' => $cantidad_agregar, 'ultima_modificacion' => $fecha_actual, 'usuario_modifica' => session('usuario')['id']
                ]);
            }

            DB::commit();


            if ($actualizar) { // Editar usuario
                $this->setSuccess('Agregar Producto', 'Se actualizo el producto correctamente.');
            } else { // Nuevo usuario
                $this->setSuccess('Agregar Producto', 'Producto agregado correctamente.');
            }
            return $this->responseAjaxSuccess("","");
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salio mal...", []);
        }
    }


    /**
     * Inactiva un producto.
     */
    public function eliminarProducto(Request $request)
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            $this->setError("Seguridad", "No tienes permisos para ingresar..");
            return redirect('/');
        }

        $id = $request->input('idProductoEliminar');
        if ($id == null || $id == '' || $id < 1) {
            $this->setError('Eliminar Producto', 'Identificador inválido.');
            return redirect('bodega/productos');
        }
        try {
            DB::beginTransaction();
            $producto = DB::table('producto')->where('id', '=', $id)->get()->first();
            if ($producto == null) {
                $this->setError('Eliminar Producto', 'No existe el producto a eliminar.');
                return redirect('bodega/productos');
            } else {
                DB::table('producto')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Producto', 'El producto se elimino correctamente.');
            return redirect('bodega/productos');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Producto', 'Ocurrio un error eliminando el producto.');
            return redirect('bodega/productos');
        }
    }


    public function validarProducto(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

        if ($this->isNull($r->input('codigo')) || $this->isEmpty($r->input('codigo'))) {
            $requeridos .= " Código ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('nombre')) || $this->isEmpty($r->input('nombre'))) {
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('categoria')) || $this->isEmpty($r->input('categoria'))) {
            $requeridos .= " Categoría ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('impuesto')) || $this->isEmpty($r->input('impuesto'))) {
            $requeridos .= " Impuesto ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('precio')) || $this->isEmpty($r->input('precio'))) {
            $requeridos .= " Precio ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('precio_compra')) || $this->isEmpty($r->input('precio_compra'))) {
            $requeridos .= " Precio Compra";
            $valido = false;
            $esPrimero = false;
        }

        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        if (!$this->isLengthMinor($r->input('codigo'), 15)) {
            $this->setError('Tamaño exedido', "El código de barra debe ser de máximo 15 caracteres.");
            return false;
        }
        if (!$this->isLengthMinor($r->input('nombre'), 50)) {
            $this->setError('Tamaño exedido', "El nombre debe ser de máximo 30 caracteres.");
            return false;
        }

        if (!$this->isNumber($r->input('precio')) || $r->input('precio') < 10) {
            $this->setError('Número incorrecto', "El precio debe ser mayor que 10.00 CRC.");
            return false;
        }

        if (!$this->isNumber($r->input('precio_compra')) || $r->input('precio_compra') < 10) {
            $this->setError('Número incorrecto', "El precio de compra debe ser mayor que 10.00 CRC.");
            return false;
        }

        return $valido;
    }

    public function goInventarios()
    {
        if (!$this->validarSesion('prod_ext_inv')) {
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
            'productos_externos' => ProductosExternosController::getProductos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('productoExterno.inventario.inventarios', compact('data'));
    }

    public function goInventariosFiltro(Request $request)
    {
        if (!$this->validarSesion('prod_ext_inv')) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');

        if ($this->isNull($filtroSucursal) || $filtroSucursal == '-1') {
            $this->setError("Buscar inventario", "Debe seleccionar la sucursal");
            return redirect('productoExterno/inventario/inventarios');
        }

        $filtros = [
            'sucursal' => $filtroSucursal,
        ];
        $data = [
            'menus' => $this->cargarMenus(),
            'inventarios' => ProductosExternosController::getInventario($filtroSucursal),
            'productos_externos' => ProductosExternosController::getProductos(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productoExterno.inventario.inventarios', compact('data'));
    }

    public static function getInventario($sucursal)
    {

        if ($sucursal == null || $sucursal < 1) {
            return [];
        }

        $inventarios = DB::table('producto_externo')
            ->leftjoin('pe_x_sucursal', 'pe_x_sucursal.producto_externo', '=', 'producto_externo.id')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'pe_x_sucursal.sucursal')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_externo.categoria')
            ->leftjoin('proveedor', 'proveedor.id', '=', 'producto_externo.proveedor')
            ->select('producto_externo.id', 'pe_x_sucursal.id as pe_id', 'producto_externo.codigo_barra', 'producto_externo.nombre', 'categoria.categoria', 'pe_x_sucursal.cantidad', 'proveedor.nombre as nombre_prov')
            ->groupBy('producto_externo.id', 'pe_x_sucursal.id', 'producto_externo.codigo_barra', 'producto_externo.nombre', 'categoria.categoria', 'pe_x_sucursal.cantidad', 'proveedor.nombre')
            ->where('pe_x_sucursal.sucursal', '=', $sucursal)
            ->get();

        return $inventarios;
    }

    public static function getProductos()
    {

        $productos = DB::table('producto_externo')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_externo.categoria')
            ->leftjoin('proveedor', 'proveedor.id', '=', 'producto_externo.proveedor')
            ->select('producto_externo.id', 'producto_externo.codigo_barra', 'producto_externo.nombre', 'categoria.categoria', 'proveedor.nombre as nombre_prov')
            ->where('producto_externo.estado', '=', 'A')
            ->get();

        return $productos;
    }

   
}
