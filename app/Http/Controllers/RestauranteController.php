<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class RestauranteController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_CR");
    }

    public function index()
    {
    }

    static function getRestaurantes()
    {
        return DB::table('restaurante')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'restaurante.sucursal')
            ->select('restaurante.*', 'sucursal.descripcion as sucursal_nombre')
            ->where('restaurante.estado', '=', 'A')
            ->get();
    }

    static function getSalones($restaurante)
    {
        return DB::table('salon')
            ->where('salon.restaurante', '=', $restaurante)
            ->get();
    }

    static function getRestaurante($id)
    {
        $restaurante = DB::table('restaurante')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'restaurante.sucursal')
            ->select('restaurante.*', 'sucursal.descripcion as sucursal_nombre')
            ->where('restaurante.id', '=', $id)
            ->get()->first();

        if ($restaurante == null) {
            return null;
        }

        $restaurante->salones = DB::table('salon')
            ->select('salon.*')
            ->where('salon.restaurante', '=', $id)
            ->get();

        foreach ($restaurante->salones as $s) {
            $s->mobiliario = DB::table('mobiliario_x_salon')
                ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
                ->select(
                    'mobiliario.*',
                    'mobiliario_x_salon.inicio_fila',
                    'mobiliario_x_salon.inicio_columna',
                    'mobiliario_x_salon.id as id_mxs',
                    'mobiliario_x_salon.fin_fila',
                    'mobiliario_x_salon.fin_columna',
                    'mobiliario_x_salon.estado',
                    'mobiliario_x_salon.numero_mesa'
                )
                ->where('mobiliario_x_salon.salon', '=', $s->id)
                ->get();
        }

        return $restaurante;
    }

    static function getMobiliarioSalon($mobiliario)
    {
        $mxs = DB::table('mobiliario_x_salon')
            ->where('id', '=', $mobiliario)
            ->get()->first();
        $mxs->mobiliario = DB::table('mobiliario')
            ->where('id', '=', $mxs->mobiliario)
            ->get()->first();

        return $mxs;
    }

    static function getMobiliarioDisponible()
    {
        return DB::table('mobiliario')
            ->where('estado', '=', "A")
            ->get();
    }

    public function goRestaurantes()
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $restaurantes = $this->getRestaurantes();

        if ($restaurantes == null) {
            $this->setError("Restaurantes", "Algo salío mal, comunicate con el encargado de la página..");
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'restaurantes' => $restaurantes,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('restaurante.restaurantes', compact('data'));
    }

    public function goEditarRestaurante(Request $r)
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $id = $r->input('idEditarRestaurante');

        if ($id == null || $id > 1) {
            $this->setError("Editar Restaurante", "Identificador inválido.");
            return redirect('restaurante/restaurantes');
        }

        $restaurante = DB::table('restaurante')->where('id', '=', $id)->get()->first();

        if ($restaurante == null) {
            $this->setError("Editar Restaurante", "No se encontró un restaurante.");
            return redirect('restaurante/restaurantes');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursales(),
            'restaurante' => $restaurante,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('restaurante.editar', compact('data'));
    }

    public function goAgregarRestaurante()
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('restaurante.agregar', compact('data'));
    }

    public function goRestaurante(Request $r)
    {
        if (!$this->validarSesion(array("restTodos"))) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $r->input('idEditarRestauranteMobiliario');
        return $this->goRestauranteById($id);
    }

    public function guardarRestaurante(Request $r)
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id_restaurante = $r->input('id_restaurante');
        $sucursal = $r->input('sucursal');

        if ($sucursal == null || $sucursal == "") {
            $this->setError("Guardar Restaurante", "Debe seleccionar una sucursal.");
            return redirect('restaurante/restaurantes');
        }

        if ($id_restaurante == null || $id_restaurante < 1) {
            $actualizar = false;
        } else {
            $actualizar = true;
        }

        try {
            DB::beginTransaction();
            if (!$actualizar) {
                $restaurante_id = DB::table('restaurante')->insertGetId(['id' => null, 'sucursal' => $sucursal, 'estado' => 'A', 'filas' => 0, 'columnas' => 0]);
            } else {
                DB::table('restaurante')
                    ->where('id', '=', $id_restaurante)
                    ->update(['sucursal' => $sucursal]);
            }
            DB::commit();
            $this->setSuccess('Guardar Restaurante', 'El restaurante se guardo correctamente.');
            return redirect('restaurante/restaurantes');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Guardar Restaurante', "Algo salio mal...");
            return redirect('restaurante/restaurantes');
        }
    }

    public function goRestauranteById($id)
    {
        if ($id == null || $id < 1) {
            $this->setError("Editar Restaurante", "ID del restaurante incorrecto..");
            return redirect('restaurante/restaurantes');
        }

        $restaurante = $this->getRestaurante($id);

        if ($restaurante == null) {
            $this->setError("Editar Restaurante", "El restaurante no existe..");
            return redirect('/');
        }
        $data = [
            'menus' => $this->cargarMenus(),
            'restaurante' => $restaurante,
            'salones' => $this->getSalones($id),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        //dd($restaurante);
        return view('restaurante.restaurante', compact('data'));
    }

    public function guardarSalon(Request $r)
    {

        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $idRestaurante = $r->input('mdl_salon_ipt_id_restaurante');
        $idSalon = $r->input('mdl_salon_ipt_id');
        $nombre = $r->input('mdl_salon_ipt_nombre');
        $ubicacion = $r->input('mdl_salon_ipt_ubicacion');

        if ($this->validarSalon($idRestaurante, $nombre, $ubicacion)) {
            try {

                DB::beginTransaction();

                if ($idSalon != null && $idSalon > 0) {
                    DB::table('salon')
                        ->where('id', '=', $idSalon)
                        ->update([
                            'nombre' => $nombre, 'ubicacion_detallada' => $ubicacion
                        ]);
                } else {
                    $id = DB::table('salon')->insertGetId([
                        'id' => null, 'restaurante' => $idRestaurante, 'filas' => 0, 'columnas' => 0,
                        'nombre' => $nombre, 'ubicacion_detallada' => $ubicacion
                    ]);
                }

                DB::commit();
                $this->setSuccess('Agregar Salón', 'El salón se guardó correctamente.');
                return $this->goRestauranteById($idRestaurante ?? 0);
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Salón', "Algo salió mal...");
                return $this->goRestauranteById($idRestaurante ?? 0);
            }
        } else {
            return $this->goRestauranteById($idRestaurante ?? 0);
        }
    }

    private function validarSalon($idRestaurante, $nombre, $ubicacion)
    {

        $requeridos = "[";
        $valido = true;

        if ($idRestaurante == null || $idRestaurante < 1) {
            $requeridos .= " Restaurante, ";
            $valido = false;
        }

        if ($nombre == null || empty($nombre)) {
            $requeridos .= " Nombre, ";
            $valido = false;
        }

        if ($ubicacion == null || empty($ubicacion)) {
            $requeridos .= " Ubicación ";
            $valido = false;
        }

        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        return $valido;
    }

    public function eliminarSalon(Request $r)
    {

        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $idSalon = $r->input('frm_eliminar_salon_id_salon');
        $idRestaurante = $r->input('frm_eliminar_salon_id_restaurante');

        try {

            if ($idSalon != null && $idSalon > 0) {

                DB::beginTransaction();
                $salon = DB::table('salon')->where('id', '=', $idSalon)->get()->first();
                if ($salon == null) {
                    $this->setError('Eliminar Salón', 'No existe el salón a eliminar.');
                    return $this->goRestauranteById($idRestaurante ?? 0);
                } else {
                    DB::table('salon')->where('id', '=', $idSalon)->delete();
                }
                DB::commit();
                $this->setSuccess('Eliminar Salón', 'El salón se eliminó correctamente.');
                return $this->goRestauranteById($idRestaurante ?? 0);
            } else {
                $this->setError('Eliminar Salón', "Identificador del salón incorrecto.");
                return $this->goRestauranteById($idRestaurante ?? 0);
            }

            DB::commit();
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Salón', "Algo salió mal...");
            return $this->goRestauranteById($idRestaurante ?? 0);
        }
    }

    public function inactivarMobiliarioSalon(Request $r)
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id_mxs = $r->input('id_mxs_inactivar');

        if ($id_mxs == null || $id_mxs < 1) {
            $this->setError("Inactivar Mobiliario", "ID del mobiliario incorrecto..");
            return $this->goRestauranteById($id_mxs ?? 0);
        }

        $mobiliario = DB::table('mobiliario_x_salon')
            ->where('id', '=', $id_mxs)
            ->get()->first();

        if ($mobiliario == null) {
            $this->setError("Inactivar Mobiliario", "No se encontro el mobiliario..");
            return $this->goRestauranteById($id_mxs ?? 0);
        }

        $activar = false;
        if ($mobiliario->estado == "I") {
            $activar = true;
        } else if ($mobiliario->estado != "D") {
            $this->setError("Inactivar Mobiliario", "El mobiliario debe estar disponible y desocupado para poder editarse..");
            return $this->goRestauranteById($id_mxs ?? 0);
        }

        /*if ($mobiliario->cantidad != null) {
            if ($mobiliario->cantidad > 0) {
                $this->setError("Editar Mobiliario", "El mobiliario debe estar disponible y desocupado para poder editarse..");
                return $this->goRestauranteById($id_mxs ?? 0);
            }
        }*/

        try {
            DB::beginTransaction();

            DB::table('mobiliario_x_salon')
                ->where('id', '=', $id_mxs)
                ->update([
                    'estado' => $activar ? "D" : "I"
                ]);

            DB::commit();

            if ($activar) { // Editar usuario
                $this->setSuccess('Inactivar Mobiliario', 'Se activo el mobiliario correctamente.');
            } else { // Nuevo usuario
                $this->setSuccess('Inactivar Mobiliario', 'Se inactivo el mobiliario correctamente.');
            }
            return $this->goRestauranteById($id_mxs);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Inactivar Mobiliario', "Algo salió mal...");
            return $this->goRestauranteById($id_mxs ?? 0);
        }
    }

    public function eliminarMobiliarioSalon(Request $r)
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id_mxs = $r->input('id_mxs_eliminar');

        if ($id_mxs == null || $id_mxs < 1) {
            $this->setError("Eliminar Mobiliario", "ID del mobiliario incorrecto..");
            return $this->goRestauranteById($id_mxs ?? 0);
        }

        $mobiliario = DB::table('mobiliario_x_salon')
            ->where('id', '=', $id_mxs)
            ->get()->first();

        if ($mobiliario == null) {
            $this->setError("Eliminar Mobiliario", "No se encontró el mobiliario..");
            return $this->goRestauranteById($id_mxs ?? 0);
        }

        try {
            DB::beginTransaction();

            DB::table('mobiliario_x_salon')->where('id', $id_mxs)->delete();

            DB::commit();

            $this->setSuccess('Eliminar Mobiliario', 'Se eliminó el mobiliario de este salón correctamente.');

            return $this->goRestauranteById($id_mxs);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Mobiliario', "Algo salio mal...");
            return $this->goRestauranteById($id_mxs ?? 0);
        }
    }

    public function guardarMobiliarioSalon(Request $r)
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        if (!$this->validarMobiliarioSalon($r)) {
            return $this->goRestauranteById($r->input('id_mxs') ?? 0);
        }

        $id_mobiliario = $r->input('mobiliario');
        $id_mxs = $r->input('id_mxs');
        $numero_mesa = $r->input('numero_mesa');

        if ($id_mxs == null || $id_mxs < 1) {
            $actualizar = false;
        } else {
            $actualizar = true;
        }

        try {
            DB::beginTransaction();

            if ($actualizar) { // Editar usuario
                DB::table('mobiliario_x_salon')
                    ->where('id', '=', $id_mxs)
                    ->update([
                        'mobiliario' => $id_mobiliario, 'numero_mesa' => $numero_mesa
                    ]);
            } else { // Nuevo usuario
                $id = DB::table('mobiliario_x_salon')->insertGetId([
                    'id' => null, 'mobiliario' => $id_mobiliario, 'inicio_fila' => 0, 'inicio_columna' => 0,
                    'fin_columna' => 0, 'fin_fila' => 0, 'disponible' => "S",
                    'cantidad_ocupada' => 0, 'estado' => "D", 'numero_mesa' => $numero_mesa
                ]);
            }

            DB::commit();

            if ($actualizar) { // Editar usuario
                $this->setSuccess('Guardar Mobiliario', 'Se actualizó el mobiliario correctamente.');
            } else { // Nuevo usuario

                $this->setSuccess('Guardar Mobiliario', 'Mobiliario creado correctamente.');
            }
            return $this->goRestauranteById($id_mxs);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Guardar Mobiliario', $ex);
            return $this->goRestauranteById($id_mxs ?? 0);
        }
    }

    public function validarMobiliarioSalon(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

        if ($this->isNull($r->input('numero_mesa')) || $this->isEmpty($r->input('numero_mesa'))) {
            $requeridos .= " No. Mesa ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('mobiliario')) || $this->isEmpty($r->input('mobiliario'))) {
            $requeridos .= " Mobiliario ";
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

    public function goEditarMobiliarioSalon(Request $r)
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $r->input('idEditarMobiliario');
        return $this->goEditarMobiliarioSalonBy($id);
    }

    public function goEditarMobiliarioSalonBy($id)
    {
        if ($id == null || $id < 1) {
            $this->setError("Editar Mobiliario", "ID del mobiliario incorrecto..");
            return redirect('restaurante/restaurantes');
        }

        $mobiliario = $this->getMobiliarioSalon($id);

        if ($mobiliario == null) {
            $this->setError("Editar Mobiliario", "El mobiliario no existe..");
            return redirect('restaurante/restaurantes');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'mobiliario' => $mobiliario,
            'mobiliario_disponible' => $this->getMobiliarioDisponible(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        //dd($mobiliario);
        return view('restaurante.salon.mobiliario.editar', compact('data'));
    }

    public function goAgregarMobiliarioSalon(Request $r)
    {
        if (!$this->validarSesion("restTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $idSalon = $r->input('idSalonAgregarMobiliario');
        $idRestaurante = $r->input('idRestauranteAgregarMobiliario');

        if ($idSalon == null || $idSalon < 1) {
            $this->setError("Editar Mobiliario", "ID del salón incorrecto..");
            return redirect('restaurante/restaurantes');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'salon' => $idSalon,
            'restaurante' => $idRestaurante,
            'mobiliario_disponible' => $this->getMobiliarioDisponible(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        //dd($mobiliario);
        return view('restaurante.salon.mobiliario.agregar', compact('data'));
    }

    public function asignarMobiliarioSalon(Request $r)
    {
        $idSalon = $r->input('salon');
        $idRestaurante = $r->input('restaurante');
        $idMobiliario = $r->input('mobiliario');
        $numeroMesa = $r->input('numero_mesa');
        $cantidadPersonas = $r->input('cantidad_personas');

        $mensajeValidacion = $this->validarAsignarMobiliarioSalon($idSalon, $idMobiliario, $numeroMesa, $cantidadPersonas);

        if (!empty($mensajeValidacion)) {
            $this->setError("Asignar mobiliario", $mensajeValidacion);
            return redirect('restaurante/restaurantes');
        } else {
            try {
                DB::beginTransaction();

                $id = DB::table('mobiliario_x_salon')->insertGetId([
                    'id' => null, 'mobiliario' => $idMobiliario, 'salon' => $idSalon, 'inicio_fila' => 0, 'inicio_columna' => 0,
                    'fin_columna' => 0, 'fin_fila' => 0, 'disponible' => "S", 'cantidad_maxima' => $cantidadPersonas,
                    'cantidad_ocupada' => 0, 'estado' => "D", 'numero_mesa' => $numeroMesa
                ]);

                DB::commit();

                $this->setSuccess('Asignar Mobiliario', 'Mobiliario asignado correctamente.');

                return $this->goRestauranteById($idRestaurante);
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Mobiliario', $ex);
                return $this->goRestauranteById($id_mxs ?? 0);
            }
        }
    }

    public function validarAsignarMobiliarioSalon($idSalon, $idMoviliario, $numeroMesa, $cantidadPersonas)
    {

        $mensaje = "";

        if ($idSalon == null || $idSalon < 1) {
            $mensaje += "Salon incorrecto,";
        }

        if ($idMoviliario == null || $idMoviliario < 1) {
            $mensaje += "Moviliario incorrecto,";
        }

        if ($numeroMesa < 0) {
            $mensaje += "Número de mesa incorrecto,";
        }

        if ($cantidadPersonas < 0) {
            $mensaje += "Cantidad de personas incorrecto";
        }

        return $mensaje;
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
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('restaurante.productos', compact('data'));
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

        $filtros = [
            'impuesto' => $impuesto,
            'categoria' => $categoria,
        ];
        //  dd($productos);
        $data = [
            'menus' => $this->cargarMenus(),
            'productos' => $productos,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('restaurante.productos', compact('data'));
    }

    public function goNuevoProducto()
    {
        if (!$this->validarSesion("prod_mnu")) {
            return redirect('/');
        }

        $datos = [];
        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => $datos,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('restaurante.producto.nuevoProducto', compact('data'));
    }

    /**
     * Guarda o actualiza un producto
     */
    public function guardarProducto(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            return redirect('/');
        }

        // dd($request->all());
        $id = $request->input('id');
        $codigo = $request->input('codigo');
        $producto = DB::table('producto_menu')->select('producto_menu.*')->where('id', '=', $id)->get()->first();

        if ($id < 1 || $this->isNull($id)) { // Nuevo 
            if ($this->codigoBarraRegistrado($codigo)) {
                $this->setError('Guardar Producto', 'El código de barra ya esta en uso.');
                return $this->returnNuevoProductoWithData($request->all());
            }
            $actualizar = false;
        } else { // Editar usuario

            if ($producto == null) {
                $this->setError('Guardar Producto', 'No existe un producto con los credenciales.');
                return $this->returnEditarProductoWithId($id);
            }
            if ($producto->codigo != $codigo) {
                if ($this->codigoBarraRegistrado($codigo)) {
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
            $impuesto = $request->input('impuesto');
            $tipo_comanda = $request->input('tipo_comanda');
            $descripcion = $request->input('descripcion');

            try {
                DB::beginTransaction();

                if ($actualizar) { // Editar usuario
                    DB::table('producto_menu')
                        ->where('id', '=', $id)
                        ->update([
                            'nombre' => $nombre, 'categoria' => $categoria, 'precio' => $precio,
                            'impuesto' => $impuesto, 'descripcion' => $descripcion, 'codigo' => $codigo,'tipo_comanda'=>$tipo_comanda
                        ]);
                } else { // Nuevo usuario
                    $id = DB::table('producto_menu')->insertGetId([
                        'id' => null, 'nombre' => $nombre, 'categoria' => $categoria, 'precio' => $precio,
                        'impuesto' => $impuesto, 'descripcion' => $descripcion, 'codigo' => $codigo, 'estado' => 'A','tipo_comanda'=>$tipo_comanda
                    ]);
                }

                DB::commit();

                if ($actualizar) { // Editar usuario
                    $this->setSuccess('Guardar Producto', 'Se actualizo el producto correctamente.');
                } else { // Nuevo usuario
                    $this->setSuccess('Guardar Producto', 'Producto creado correctamente.');
                }
                return $this->returnEditarProductoWithId($id);
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Producto', 'Algo salio mal...');
                return redirect('restaurante/productos');
            }
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
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('restaurante.producto.nuevoProducto', compact('data'));
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
        return view('restaurante.producto.editarProducto', compact('data'));
    }

    public function validarProducto(Request $r)
    {
        $requeridos = "[";
        $valido = true;

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

        if (!$this->isLengthMinor($r->input('descripcion'), 400)) {
            $this->setError('Tamaño exedido', "La descripción debe ser de máximo 400 caracteres.");
            return false;
        }

        if (!$this->isNumber($r->input('precio')) || $r->input('precio') < 0) {
            $this->setError('Número incorrecto', "El precio debe ser mayor que 0.00 CRC.");
            return false;
        }

        return $valido;
    }

    public function goEditarProducto(Request $request)
    {
        if (!$this->validarSesion("prod_mnu")) {
            return redirect('/');
        }


        $id = $request->input('idProductoEditar');
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
        return view('restaurante.producto.editarProducto', compact('data'));
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
            return redirect('restaurante/productos');
        }
        try {
            DB::beginTransaction();
            $producto = DB::table('producto_menu')->where('id', '=', $id)->get()->first();
            if ($producto == null) {
                $this->setError('Eliminar Producto', 'No existe el producto a eliminar.');
                return redirect('restaurante/productos');
            } else {
                DB::table('producto_menu')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Producto', 'El producto se elimino correctamente.');
            return redirect('restaurante/productos');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Producto', 'Ocurrio un error eliminando el producto.');
            return redirect('restaurante/productos');
        }
    }

    public function goMenus()
    {
        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if (!$this->usuarioAdministrador()) {
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

        return view('restaurante.menus', compact('data'));
    }

    public function goMenusFiltro(Request $request)
    {
        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if (!$this->usuarioAdministrador()) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');

        if ($this->isNull($filtroSucursal) || $filtroSucursal == '-1') {
            $this->setError("Buscar restaurantes", "Debe seleccionar la sucursal");
            return redirect('restaurante/menus');
        }

        $filtros = [
            'sucursal' => $filtroSucursal,
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'restaurantes' => $this->getRestaurantesSucursal($filtroSucursal),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'filtros' => $filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('restaurante.menus', compact('data'));
    }

    public function goEditarMenuByid($restauranteId)
    {
        if ($this->isNull($restauranteId) || $restauranteId == '-1') {
            $this->setError("Editar menú", "Restaurante inexistente.");
            return redirect('restaurante/menus');
        }

        $restaurante = DB::table('restaurante')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'restaurante.sucursal')
            ->select('restaurante.*', 'sucursal.descripcion as sucursal_descripcion')
            ->where('restaurante.id', '=', $restauranteId)->get()->first();

        if ($this->isNull($restaurante)) {
            $this->setError("Editar menú", "Restaurante inexistente.");
            return redirect('restaurante/menus');
        }


        $menusRestaurante = DB::table('pm_x_restaurante')
            ->leftjoin('producto_menu', 'producto_menu.id', '=', 'pm_x_restaurante.producto_menu')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_menu.categoria')
            ->select('producto_menu.*', 'categoria.categoria as nombre_categoria')
            ->where('pm_x_restaurante.restaurante', '=', $restaurante->id)
            ->get();

        $productos_menu = DB::table('producto_menu')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_menu.categoria')
            ->select('producto_menu.*', 'categoria.categoria as nombre_categoria')
            ->where('producto_menu.estado', '=', "A")
            ->get();
        // dd($productos_menu);
        $data = [
            'menus' => $this->cargarMenus(),
            'productos_menu' => $productos_menu,
            'restaurante' => $restaurante,
            'menusRestaurante' => $menusRestaurante,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('restaurante.menus.editar', compact('data'));
    }

    public function goEditarMenu(Request $request)
    {

        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if (!$this->usuarioAdministrador()) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $restauranteId = $request->input('idRestauranteMenu');

        return $this->goEditarMenuByid($restauranteId);
    }

    public function agregarProductoAMenu(Request $request)
    {

        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if (!$this->usuarioAdministrador()) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $restauranteId = $request->input('restaurante_id');
        $producto_menu_id = $request->input('prodcuto_menu');

        if ($this->isNull($restauranteId) || $restauranteId == '-1') {
            $this->setError("Agregar menú", "Restaurante inexistente.");
            return $this->goEditarMenuByid($restauranteId);
        }

        if ($this->isNull($producto_menu_id) || $producto_menu_id == '-1') {
            $this->setError("Agregar menú", "Producto inexistente.");
            return $this->goEditarMenuByid($restauranteId);
        }

        $restaurante = DB::table('restaurante')
            ->select('restaurante.id')
            ->where('restaurante.id', '=', $restauranteId)->get()->first();

        if ($this->isNull($restaurante)) {
            $this->setError("Editar menú", "Restaurante inexistente.");
            return $this->goEditarMenuByid($restauranteId);
        }

        $producto_menu = DB::table('producto_menu')
            ->select('producto_menu.id', 'producto_menu.estado')
            ->where('producto_menu.id', '=', $producto_menu_id)
            ->get()->first();

        if ($this->isNull($producto_menu)) {
            $this->setError("Editar menú", "Producto inexistente.");
            return $this->goEditarMenuByid($restauranteId);
        }
        if ($producto_menu->estado != "A") {
            $this->setError("Editar menú", "Producto Inactivo.");
            return $this->goEditarMenuByid($restauranteId);
        }

        $pm = DB::table('pm_x_restaurante')
            ->select('pm_x_restaurante.id')
            ->where('pm_x_restaurante.restaurante', '=', $restauranteId)
            ->where('pm_x_restaurante.producto_menu', '=', $producto_menu->id)
            ->get()->first();

        if (!$this->isNull($pm)) {
            $this->setError("Editar menú", "Producto ya existente.");
            return $this->goEditarMenuByid($restauranteId);
        }
        try {
            DB::beginTransaction();
            $id = DB::table('pm_x_restaurante')->insertGetId([
                'id' => null,
                'producto_menu' => $producto_menu->id, 'restaurante' => $restaurante->id
            ]);
            DB::commit();
            $this->setSuccess('Agregar menú', 'El menú se agrego correctamente.');
            return $this->goEditarMenuByid($restauranteId);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Agregar menú', "Algo salio mal...");
            return $this->goEditarMenuByid($restauranteId);
        }
    }

    public function eliminarProductoAMenu(Request $request)
    {
        if (!$this->validarSesion("mnus")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if (!$this->usuarioAdministrador()) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $restauranteId = $request->input('restaurante_id_eliminar');
        $producto_menu_id = $request->input('producto_menu_eliminar');
        if ($this->isNull($restauranteId) || $restauranteId == '-1') {
            $this->setError("Eliminar menú", "Restaurante inexistente.");
            return $this->goEditarMenuByid($restauranteId);
        }

        if ($this->isNull($producto_menu_id) || $producto_menu_id == '-1') {
            $this->setError("Eliminar menú", "Producto inexistente.");
            return $this->goEditarMenuByid($restauranteId);
        }

        $restaurante = DB::table('restaurante')
            ->select('restaurante.id')
            ->where('restaurante.id', '=', $restauranteId)->get()->first();

        if ($this->isNull($restaurante)) {
            $this->setError("Eliminar menú", "Restaurante inexistente.");
            return $this->goEditarMenuByid($restauranteId);
        }

        $producto_menu = DB::table('producto_menu')
            ->select('producto_menu.id', 'producto_menu.estado')
            ->where('producto_menu.id', '=', $producto_menu_id)
            ->get()->first();

        if ($this->isNull($producto_menu)) {
            $this->setError("Eliminar menú", "Producto inexistente.");
            return $this->goEditarMenuByid($restauranteId);
        }

        $pm = DB::table('pm_x_restaurante')
            ->select('pm_x_restaurante.id')
            ->where('pm_x_restaurante.restaurante', '=', $restauranteId)
            ->where('pm_x_restaurante.producto_menu', '=', $producto_menu->id)
            ->get()->first();

        if ($this->isNull($pm)) {
            $this->setError("Eliminar menú", "Producto no existente enm el menú.");
            return $this->goEditarMenuByid($restauranteId);
        }
        
        try {
            DB::beginTransaction();
            DB::table('pm_x_restaurante') ->where('pm_x_restaurante.restaurante', '=', $restauranteId)
            ->where('pm_x_restaurante.producto_menu', '=', $producto_menu->id)
            ->delete();
           
            DB::commit();
            $this->setSuccess('Eliminar menú', 'El menú se elimino correctamente.');
            return $this->goEditarMenuByid($restauranteId);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar menú', "Algo salio mal...");
            return $this->goEditarMenuByid($restauranteId);
        }
    }
}
