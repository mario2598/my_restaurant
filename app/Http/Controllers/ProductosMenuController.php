<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Traits\SpaceUtil;

class ProductosMenuController extends Controller
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

    public function goProductosMenu()
    {
        if (!$this->validarSesion("prod_mnu")) {
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
            'productos' => [],
            'materia_prima' => MateriaPrimaController::getProductos(),
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('productosMenu.productos', compact('data'));
    }

    public function goProductosMenuFiltro(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $categoria = $request->input('categoria');
        $impuesto = $request->input('impuesto');

        $productos = DB::table('producto_menu')
            ->leftJoin('categoria', 'categoria.id', '=', 'producto_menu.categoria')
            ->leftJoin('impuesto', 'impuesto.id', '=', 'producto_menu.impuesto')
            ->select('producto_menu.*', 'impuesto.impuesto as porcentaje_impuesto', 'categoria.categoria as nombre_categoria')
            ->where('producto_menu.estado', '=', 'A');

        if (!$this->isNull($categoria) && $categoria != 'T') {
            $productos = $productos->where('categoria.id', '=', $categoria);
        }
        if (!$this->isNull($impuesto) && $impuesto != 'T') {
            $productos = $productos->where('impuesto.id', '=', $impuesto);
        }

        $productos = $productos->get();

        foreach ($productos as $p) {
            $p->materia_prima = DB::table('materia_prima')
                ->leftjoin('mt_x_producto', 'mt_x_producto.materia_prima', '=', 'materia_prima.id')
                ->select(
                    'materia_prima.*',
                    'mt_x_producto.cantidad',
                    'mt_x_producto.id as id_mp_x_prod'
                )
                ->where('mt_x_producto.producto', '=', $p->id)
                ->get();

            $p->extras = DB::table('extra_producto_menu')
                ->select(
                    'extra_producto_menu.*'
                )
                ->where('extra_producto_menu.producto', '=', $p->id)
                ->get();
        }
        $filtros = [
            'impuesto' => $impuesto,
            'categoria' => $categoria,
        ];
        //  dd($productos);
        $data = [
            'menus' => $this->cargarMenus(),
            'productos' => $productos,
            'categorias' => $this->getCategorias(),
            'materia_prima' => MateriaPrimaController::getProductos(),
            'impuestos' => $this->getImpuestos(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.productos', compact('data'));
    }

    public function goNuevoProducto()
    {
        if (!$this->validarSesion("prod_mnu")) {
            return redirect('/');
        }

        $datos = [];
        $data = [
            'menus' => $this->cargarMenus(),
            'idProducto' => 0,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.producto.producto', compact('data'));
    }

    /**
     * Guarda o actualiza un producto
     */
    public function guardarProducto(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            return $this->responseAjaxServerError("Error en seguridad", "");
        }

        // dd($request->all());
        $id = $request->input('idProducto');
        $codigo = $request->input('codigo');
        $producto = DB::table('producto_menu')->select('producto_menu.*')->where('id', '=', $id)->get()->first();
        if ($id < 1 || $this->isNull($id)) { // Nuevo 
            if ($this->codigoBarraRegistrado($codigo)) {
                return $this->responseAjaxServerError("El código de barra ya esta en uso.", "");
            }
            $actualizar = false;
        } else { // Editar usuario

            if ($producto == null) {
                return $this->responseAjaxServerError("No existe un producto con los credenciales", "");
            }
            if ($producto->codigo != $codigo) {
                return $this->responseAjaxServerError("El código de barra ya esta en uso.", "");
            }
            $actualizar = true;
        }
        $res = $this->validarProducto($request);
        if ($res == null) {

            $categoria = $request->input('categoria');
            $nombre = $request->input('nombre');
            $precio = $request->input('precio');
            $impuesto = $request->input('impuesto');
            $tipo_comanda = $request->input('tipo_comanda');
            $descripcion = $request->input('descripcion');
            $receta = $request->input('receta');
            $posicion_menu = $request->input('posicion_menu');

            $image = $request->file('foto_producto');
            if ($image != null) {
                $path = $image->store('productos', 'public');
            } else {
                if ($actualizar) {
                    $path = $producto->url_imagen;
                } else {
                    $path = "";
                }
            }

            try {
                DB::beginTransaction();

                if ($actualizar) { // Editar usuario
                    DB::table('producto_menu')
                        ->where('id', '=', $id)
                        ->update([
                            'nombre' => $nombre, 'categoria' => $categoria, 'precio' => $precio,
                            'impuesto' => $impuesto, 'descripcion' => $descripcion,
                            'codigo' => $codigo, 'tipo_comanda' => $tipo_comanda,
                            'url_imagen' => $path, 'receta' => $receta, 'posicion_menu' => $posicion_menu
                        ]);
                } else { // Nuevo usuario
                    $id = DB::table('producto_menu')->insertGetId([
                        'id' => null, 'nombre' => $nombre, 'categoria' => $categoria, 'precio' => $precio,
                        'impuesto' => $impuesto, 'descripcion' => $descripcion,
                        'codigo' => $codigo, 'estado' => 'A',
                        'tipo_comanda' => $tipo_comanda, 'url_imagen' => $path, 'receta' => $receta,
                        'posicion_menu' => $posicion_menu
                    ]);
                }

                DB::commit();
                $this->setSuccess('Guardar Producto', 'El producto se guardo correctamente.');


                return $this->responseAjaxSuccess('', $id);
            } catch (QueryException $ex) {
                DB::rollBack();
                return $this->responseAjaxServerError("Algo salio mal...", "");
            }
        } else {
            return $this->responseAjaxServerError($res, "");
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
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.producto.nuevoProducto', compact('data'));
    }

    public function returnEditarProductoWithId($id)
    {
        if (!$this->validarSesion("prod_mnu")) {
            return redirect('/');
        }

        if ($id < 1 || $this->isEmpty($id)) {
            $this->setError("Error", "El producto no existe..");
            return redirect('restaurante/productos');
        }

        $producto = DB::table('producto_menu')
            ->where('producto_menu.id', '=', $id)->get()->first();

        if ($producto == null) {
            $this->setError('Editar Producto', 'No existe el producto a editar.');
            return redirect('restaurante/productos');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'producto' => $producto,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.producto.editarProducto', compact('data'));
    }

    public function validarProducto(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $error = "";

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
        if ($this->isNull($r->input('precio'))) {
            $requeridos .= " Precio ";
            $valido = false;
            $esPrimero = false;
        }

        $requeridos .= "] ";
        if (!$valido) {
            return $requeridos;
        }

        if (!$this->isLengthMinor($r->input('codigo'), 15)) {
            return "El código de barra debe ser de máximo 15 caracteres.";
        }
        if (!$this->isLengthMinor($r->input('nombre'), 50)) {
            return  "El nombre debe ser de máximo 30 caracteres.";
        }

        if (!$this->isLengthMinor($r->input('descripcion'), 400)) {
            return  "La descripción debe ser de máximo 400 caracteres.";
        }

        if (!$this->isNumber($r->input('precio')) || $r->input('precio') < 0) {
            return "El precio debe ser mayor que 0.00 CRC.";
        }

        return null;
    }

    public function goEditarProducto(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            return redirect('/');
        }


        $id = $request->input('idProductoEditar');

        $data = [
            'menus' => $this->cargarMenus(),
            'idProducto' => $id,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.producto.producto', compact('data'));
    }

    public function cargarProducto(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            return $this->responseAjaxServerError("Error en seguridad", "");
        }
        $id = $request->input('idProducto');

        $producto = DB::table('producto_menu')
            ->where('producto_menu.id', '=', $id)->get()->first();

        if ($producto == null) {
            return $this->responseAjaxServerError('No existe el producto.', "");
        }

        $producto->url_imagen =  asset('storage/' . $producto->url_imagen);

        return $this->responseAjaxSuccess("", $producto);
    }

    /**
     * Inactiva un producto.
     */
    public function eliminarProducto(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            $this->setError("Seguridad", "No tienes permisos para ingresar..");
            return redirect('/');
        }

        $id = $request->input('idProductoEliminar');
        if ($id == null || $id == '' || $id < 1) {
            $this->setError('Eliminar Producto', 'Identificador inválido.');
            return redirect('menu/productos');
        }
        try {
            DB::beginTransaction();
            $producto = DB::table('producto_menu')->where('id', '=', $id)->get()->first();
            if ($producto == null) {
                $this->setError('Eliminar Producto', 'No existe el producto a eliminar.');
                return redirect('menu/productos');
            } else {
                DB::table('producto_menu')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Producto', 'El producto se elimino correctamente.');
            return redirect('menu/productos');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Producto', 'Ocurrio un error eliminando el producto.');
            return redirect('menu/productos');
        }
    }

    public function goMenus()
    {
        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => '',
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'filtros' => $filtros,
            'restaurantes' => [],
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('productosMenu.menus', compact('data'));
    }

    public function goMenusFiltro(Request $request)
    {
        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');

        if ($this->isNull($filtroSucursal) || $filtroSucursal == '-1') {
            $this->setError("Buscar restaurantes", "Debe seleccionar la sucursal");
            return redirect('menu/menus');
        }

        $filtros = [
            'sucursal' => $filtroSucursal,
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.menus', compact('data'));
    }

    public function goEditarMenuByid($idSucursal)
    {
        if ($this->isNull($idSucursal) || $idSucursal == '-1') {
            $this->setError("Editar menú", "Sucursal inexistente.");
            return redirect('menu/menus');
        }

        $sucursal = DB::table('sucursal')

            ->select('sucursal.*')
            ->where('sucursal.id', '=', $idSucursal)->get()->first();

        if ($this->isNull($sucursal)) {
            $this->setError("Editar menú", "Sucursal inexistente.");
            return redirect('menu/menus');
        }


        $menusSucursal = DB::table('pm_x_sucursal')
            ->leftjoin('producto_menu', 'producto_menu.id', '=', 'pm_x_sucursal.producto_menu')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_menu.categoria')
            ->select('producto_menu.*', 'categoria.categoria as nombre_categoria')
            ->where('pm_x_sucursal.sucursal', '=', $sucursal->id)
            ->get();

        $productos_menu = DB::table('producto_menu')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_menu.categoria')
            ->select('producto_menu.*', 'categoria.categoria as nombre_categoria')
            ->where('producto_menu.estado', '=', "A")
            ->get();

        $filtros = [
            'sucursal' => $idSucursal,
        ];
        // dd($productos_menu);
        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'productos_menu' => $productos_menu,
            'filtros' => $filtros,
            'sucursal' => $sucursal,
            'menusSucursal' => $menusSucursal,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.menus', compact('data'));
    }

    public function goEditarMenuFiltro(Request $request)
    {
        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');

        if ($this->isNull($filtroSucursal) || $filtroSucursal == '-1') {
            $this->setError("Buscar sucursal", "Debe seleccionar la sucursal");
            return redirect('menu/menus');
        }

        $sucursal = DB::table('sucursal')

            ->select('sucursal.*')
            ->where('sucursal.id', '=', $filtroSucursal)->get()->first();

        if ($this->isNull($sucursal)) {
            $this->setError("Editar menú", "Sucursal inexistente.");
            return redirect('menu/menus');
        }


        $menusSucursal = DB::table('pm_x_sucursal')
            ->leftjoin('producto_menu', 'producto_menu.id', '=', 'pm_x_sucursal.producto_menu')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_menu.categoria')
            ->select('producto_menu.*', 'categoria.categoria as nombre_categoria')
            ->where('pm_x_sucursal.sucursal', '=', $sucursal->id)
            ->get();

        $productos_menu = DB::table('producto_menu')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_menu.categoria')
            ->select('producto_menu.*', 'categoria.categoria as nombre_categoria')
            ->where('producto_menu.estado', '=', "A")
            ->get();

        $filtros = [
            'sucursal' => $filtroSucursal,
        ];
        // dd($productos_menu);
        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'productos_menu' => $productos_menu,
            'filtros' => $filtros,
            'sucursal' => $sucursal,
            'menusSucursal' => $menusSucursal,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.menus', compact('data'));
    }



    public function goEditarMenu()
    {

        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => ''
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'productos_menu' => [],
            'filtros' => $filtros,
            'sucursal' => [],
            'menusSucursal' => [],
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('productosMenu.menus', compact('data'));
    }

    public function agregarProductoAMenu(Request $request)
    {

        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $idSucursal = $request->input('idSucursal');
        $producto_menu_id = $request->input('prodcuto_menu');

        if ($this->isNull($idSucursal) || $idSucursal == '-1') {
            $this->setError("Agregar menú", "Sucursal inexistente.");
            return $this->goEditarMenu();
        }

        if ($this->isNull($producto_menu_id) || $producto_menu_id == '-1') {
            $this->setError("Agregar menú", "Producto inexistente.");
            return $this->goEditarMenu();
        }

        $sucursal = DB::table('sucursal')
            ->select('sucursal.id')
            ->where('sucursal.id', '=', $idSucursal)->get()->first();

        if ($this->isNull($sucursal)) {
            $this->setError("Editar menú", "Sucursal inexistente.");
            return $this->goEditarMenu();
        }

        $producto_menu = DB::table('producto_menu')
            ->select('producto_menu.id', 'producto_menu.estado')
            ->where('producto_menu.id', '=', $producto_menu_id)
            ->get()->first();

        if ($this->isNull($producto_menu)) {
            $this->setError("Editar menú", "Producto inexistente.");
            return $this->goEditarMenu();
        }
        if ($producto_menu->estado != "A") {
            $this->setError("Editar menú", "Producto Inactivo.");
            return $this->goEditarMenuByid($idSucursal);
        }

        $pm = DB::table('pm_x_sucursal')
            ->select('pm_x_sucursal.id')
            ->where('pm_x_sucursal.sucursal', '=', $idSucursal)
            ->where('pm_x_sucursal.producto_menu', '=', $producto_menu->id)
            ->get()->first();

        if (!$this->isNull($pm)) {
            $this->setError("Editar menú", "Producto ya existente.");
            return $this->goEditarMenuByid($idSucursal);
        }
        try {
            DB::beginTransaction();
            $id = DB::table('pm_x_sucursal')->insertGetId([
                'id' => null,
                'producto_menu' => $producto_menu->id, 'sucursal' => $sucursal->id
            ]);
            DB::commit();
            $this->setSuccess('Agregar menú', 'El menú se agrego correctamente.');
            return $this->goEditarMenuByid($idSucursal);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Agregar menú', "Algo salio mal...");
            return $this->goEditarMenu();
        }
    }

    public function eliminarProductoAMenu(Request $request)
    {
        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $idSucursal = $request->input('idSucursal');
        $producto_menu_id = $request->input('producto_menu_eliminar');
        if ($this->isNull($idSucursal) || $idSucursal == '-1') {
            $this->setError("Eliminar menú", "Sucursal inexistente.");
            return $this->goEditarMenu();
        }

        if ($this->isNull($producto_menu_id) || $producto_menu_id == '-1') {
            $this->setError("Eliminar menú", "Producto inexistente.");
            return $this->goEditarMenuByid($idSucursal);
        }

        $sucursal = DB::table('sucursal')
            ->select('sucursal.id')
            ->where('sucursal.id', '=', $idSucursal)->get()->first();

        if ($this->isNull($sucursal)) {
            $this->setError("Eliminar menú", "Sucursal inexistente.");
            return $this->goEditarMenu();
        }

        $producto_menu = DB::table('producto_menu')
            ->select('producto_menu.id', 'producto_menu.estado')
            ->where('producto_menu.id', '=', $producto_menu_id)
            ->get()->first();

        if ($this->isNull($producto_menu)) {
            $this->setError("Eliminar menú", "Producto inexistente.");
            return $this->goEditarMenuByid($idSucursal);
        }

        $pm = DB::table('pm_x_sucursal')
            ->select('pm_x_sucursal.id')
            ->where('pm_x_sucursal.sucursal', '=', $idSucursal)
            ->where('pm_x_sucursal.producto_menu', '=', $producto_menu->id)
            ->get()->first();

        if ($this->isNull($pm)) {
            $this->setError("Eliminar menú", "Producto no existente en el menú.");
            return $this->goEditarMenuByid($idSucursal);
        }

        try {
            DB::beginTransaction();
            DB::table('pm_x_sucursal')->where('pm_x_sucursal.sucursal', '=', $idSucursal)
                ->where('pm_x_sucursal.producto_menu', '=', $producto_menu->id)
                ->delete();

            DB::commit();
            $this->setSuccess('Eliminar menú', 'El menú se elimino correctamente.');
            return $this->goEditarMenuByid($idSucursal);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar menú', "Algo salio mal...");
            return $this->goEditarMenu();
        }
    }

    public function guardarMpProd(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
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

        $producto_menu = DB::table('producto_menu')
            ->select('producto_menu.id', 'producto_menu.estado')
            ->where('producto_menu.id', '=', $id_prod_seleccionado)
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


        $mt_x_producto = DB::table('mt_x_producto')
            ->select('mt_x_producto.*')
            ->where('mt_x_producto.materia_prima', '=', $id_prod)
            ->where('mt_x_producto.producto', '=', $id_prod_seleccionado)
            ->get()->first();

        if ($this->isNull($mt_x_producto)) {
            $nuevo = true;
        } else {
            $nuevo = false;
        }

        try {
            DB::beginTransaction();
            if ($nuevo) {
                $mt_x_producto1 = DB::table('mt_x_producto')
                    ->insertGetId([
                        'id' => null, 'materia_prima' => $id_prod, 'producto' => $id_prod_seleccionado,
                        'cantidad' => $cant
                    ]);
            } else {
                DB::table('mt_x_producto')
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

    public function eliminarMpProd(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            $this->setMsjSeguridad();
            return $this->responseAjaxServerError("Error de Seguridad");
        }

        $id_prod_mp = $request->input('id_prod_mp');

        if ($this->isNull($id_prod_mp) || $id_prod_mp == '-1') {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        try {
            DB::beginTransaction();
            DB::table('mt_x_producto')->where('id', '=', $id_prod_mp)->delete();

            DB::commit();
            return $this->responseAjaxSuccess();
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    public function eliminarExtra(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            $this->setMsjSeguridad();
            return $this->responseAjaxServerError("Error de Seguridad");
        }

        $id_prod_mp = $request->input('id_prod');

        if ($this->isNull($id_prod_mp) || $id_prod_mp == '-1') {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        try {
            DB::beginTransaction();
            DB::table('extra_producto_menu')->where('id', '=', $id_prod_mp)->delete();

            DB::commit();
            return $this->responseAjaxSuccess();
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    public function cargarExtras(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            $this->setMsjSeguridad();
            return $this->responseAjaxServerError("Error de Seguridad");
        }

        $id_prod_seleccionado = $request->input('id_prod_seleccionado');

        if ($this->isNull($id_prod_seleccionado) || $id_prod_seleccionado == '-1') {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        try {
            $mat_prim = DB::table('extra_producto_menu')
                ->select(
                    'extra_producto_menu.*'
                )
                ->where('extra_producto_menu.producto', '=', $id_prod_seleccionado)
                ->get();
            return $this->responseAjaxSuccess("", $mat_prim);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    public function cargarMpProd(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            $this->setMsjSeguridad();
            return $this->responseAjaxServerError("Error de Seguridad");
        }

        $id_prod_seleccionado = $request->input('id_prod_seleccionado');

        if ($this->isNull($id_prod_seleccionado) || $id_prod_seleccionado == '-1') {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        try {
            $mat_prim = DB::table('materia_prima')
                ->leftjoin('mt_x_producto', 'mt_x_producto.materia_prima', '=', 'materia_prima.id')
                ->select(
                    'materia_prima.*',
                    'mt_x_producto.cantidad',
                    'mt_x_producto.id as id_mp_x_prod'
                )
                ->where('mt_x_producto.producto', '=', $id_prod_seleccionado)
                ->get();
            return $this->responseAjaxSuccess("", $mat_prim);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }

    public function guardarExtras(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            $this->setMsjSeguridad();
            return $this->responseAjaxServerError("Error de Seguridad");
        }

        $id = $request->input('id');
        $producto = $request->input('producto');
        $precio = $request->input('precio');
        $dsc = $request->input('dsc');
        $dsc_grupo = $request->input('dsc_grupo');
        $es_Requerido = $request->input('es_Requerido');
        $multiple = $request->input('multiple');

        $nuevo = true;

        if ($this->isNull($producto) || $producto == '-1') {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        $producto_menu = DB::table('producto_menu')
            ->select('producto_menu.id', 'producto_menu.estado')
            ->where('producto_menu.id', '=', $producto)
            ->get()->first();

        if ($this->isNull($producto_menu)) {
            return $this->responseAjaxServerError("No se puede cargar el producto de menú");
        }

        if ($this->isNull($precio) || $precio < 0) {
            return $this->responseAjaxServerError("Precio incorrecto");
        }

        if ($this->isNull($dsc) || $dsc == '') {
            return $this->responseAjaxServerError("Debe indicar una descripción para el extra");
        }

        if ($this->isNull($dsc_grupo) || $dsc_grupo == '') {
            return $this->responseAjaxServerError("Debe indicar una descripción para el grupo del extra");
        }

        $extra = DB::table('extra_producto_menu')
            ->select('extra_producto_menu.*')
            ->where('extra_producto_menu.descripcion', '=', $dsc)
            ->where('extra_producto_menu.producto', '=', $producto)
            ->where('extra_producto_menu.dsc_grupo', '=', $dsc_grupo)
            ->where('extra_producto_menu.multiple', '=', ($multiple == 'true' ? 1 : 0))
            ->get()->first();

        if (!$this->isNull($extra)) {
            return $this->responseAjaxServerError("Ya existe el extra para el producto en el grupo indicado");
        }
        try {
            DB::beginTransaction();
            if ($nuevo) {
                $mt_x_producto1 = DB::table('extra_producto_menu')
                    ->insertGetId([
                        'id' => null, 'descripcion' => $dsc, 'precio' => $precio,
                        'producto' => $producto, 'dsc_grupo' => $dsc_grupo, 'es_requerido' => ($es_Requerido == 'true' ? 1 : 0), 'multiple' => ($multiple == 'true' ? 1 : 0)
                    ]);
            } else {
                DB::table('extra_producto_menu')
                    ->where('id', '=', $id)
                    ->update(['precio' => $precio, 'descripcion' => $dsc, 'dsc_grupo ' => $dsc_grupo, 'es_requerido' => ($es_Requerido == 'true' ? 1 : 0), 'multiple' => ($multiple == 'true'  ? 1 : 0)]);
            }

            DB::commit();
            return $this->responseAjaxSuccess();
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salio mal", $ex);
        }
    }
}
