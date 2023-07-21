<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Intervention\Image\ImageManagerStatic as Image;

class GastosController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
        if (!$this->validarSesion("gastNue")) {
            return redirect('/');
        }

        $proveedores = DB::table('proveedor')->where('estado', 'like', 'A')->get();
        $data = [
            'menus' => $this->cargarMenus(),
            'menus' => $this->cargarMenus(),
            'datos' => [],
            'proveedores' => $proveedores,
            'tipos_gasto' => $this->getTiposGasto(),
            'tipos_pago' => $this->getTiposPago(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('gastos.registrarGastoAdmin', compact('data'));
    }

    public function goEditarGasto(Request $request)
    {

        if (!$this->validarSesion("gastNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $id = $request->input('idGastoEditar');

        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();
        if ($gasto == null) {
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }
        if ($gasto->aprobado == 'R') {
            $this->setError("Error", "Al parecer el gasto ya fue rechazado.");
            return redirect('/');
        }
        if ($gasto->aprobado == 'S') {
            $this->setError("Error", "Al parecer el gasto ya fue aprobado.");
            return redirect('/');
        }

        if ($this->usuarioAdministrador()) {
            $tipo_gasto = $this->getTiposGasto();
        } else {
            $tipo_gasto = [];
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'proveedores' => $this->getProveedores(),
            'gasto' => $gasto,
            'tipos_gasto' => $tipo_gasto,
            'tipos_pago' => $this->getTiposPago(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        if ($this->usuarioAdministrador()) {
            return view('gastos.editarGastoAdmin', compact('data'));
        } else {
            return view('gastos.editarGastoUsuario', compact('data'));
        }
    }

    public function goEditarGastoById(Request $request)
    {
        if (!$this->validarSesion("gastNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $id = $request->input('idGastoEditar');

        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();
        if ($gasto == null) {
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }
        if ($gasto->aprobado == 'R') {
            $this->setError("Error", "Al parecer el gasto ya fue rechazado.");
            return redirect('/');
        }
        if ($gasto->aprobado == 'S') {
            $this->setError("Error", "Al parecer el gasto ya fue aprobado.");
            return redirect('/');
        }

        if ($this->usuarioAdministrador()) {
            $tipo_gasto = $this->getTiposGasto();
        } else {
            $tipo_gasto = [];
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'proveedores' => $this->getProveedores(),
            'gasto' => $gasto,
            'tipos_gasto' => $tipo_gasto,
            'tipos_pago' => $this->getTiposPago(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        if ($this->usuarioAdministrador()) {
            return view('gastos.editarGastoAdmin', compact('data'));
        } else {
            return view('gastos.editarGastoUsuario', compact('data'));
        }
    }

    public function goGasto(Request $request)
    {
        if (!$this->validarSesion("gastNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idGasto');

        $gasto = DB::table('gasto')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->select('gasto.*', 'usuario.usuario as nombreUsuario')
            ->where('gasto.id', '=', $id)->get()->first();
        if ($gasto == null) {
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }

        $gasto->fecha = $this->fechaFormat($gasto->fecha);

        $data = [
            'menus' => $this->cargarMenus(),
            'proveedores' => $this->getProveedores(),
            'gasto' => $gasto,
            'tipos_pago' => $this->getTiposPago(),
            'tipos_gasto' => $this->getTiposGasto(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('gastos.gasto', compact('data'));
    }

    public function goGastoById($id)
    {
        if (!$this->validarSesion("gastNue")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }


        $gasto = DB::table('gasto')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->select('gasto.*', 'usuario.usuario as nombreUsuario')
            ->where('gasto.id', '=', $id)->get()->first();
        if ($gasto == null) {
            $this->setError("No encontrado", "No se encontro el gasto..");
            return redirect('/');
        }

        $gasto->fecha = $this->fechaFormat($gasto->fecha);

        $data = [
            'menus' => $this->cargarMenus(),
            'proveedores' => $this->getProveedores(),
            'gasto' => $gasto,
            'tipos_pago' => $this->getTiposPago(),
            'tipos_gasto' => $this->getTiposGasto(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('gastos.gasto', compact('data'));
    }

    public function goGastosPendientes()
    {
        if (!$this->validarSesion("gastPendApro")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if ($this->usuarioAdministrador()) {
            return $this->goGastosPendientesAdmin();
        } else {
            return $this->goGastosPendientesUsuario();
        }
    }

    public function goGastosAdmin()
    {
        if (!$this->validarSesion("gastTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'proveedor' => 0,
            'sucursal' => 'T',
            'aprobado' => 'T',
            'hasta' => "",
            'tipo_gasto' => "",
            'desde' => "",
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'totalGastos', 0,
            'gastos' => [],
            'filtros' => $filtros,
            'tipos_gasto' => $this->getTiposGasto(),
            'proveedores' => $this->getProveedores(),
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('gastos.gastosAdmin', compact('data'));
    }

    public function goGastosAdminFiltro(Request $request)
    {
        if (!$this->validarSesion("gastTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroProveedor = $request->input('proveedor');
        $filtroSucursal = $request->input('sucursal');
        $filtroAprobado = $request->input('aprobado');
        $gasto = $request->input('tipo_gasto');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $gastos =  DB::table('gasto')
            ->join('proveedor', 'proveedor.id', '=', 'gasto.proveedor')
            ->join('tipo_gasto', 'tipo_gasto.id', '=', 'gasto.tipo_gasto')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->select('gasto.*', 'tipo_gasto.tipo as nombre_tipo_gasto', 'proveedor.nombre', 'usuario.usuario as nombreUsuario')
            ->where('gasto.aprobado', '<>', 'E');

        if ($filtroProveedor >= 1  && !$this->isNull($filtroProveedor)) {
            $gastos = $gastos->where('gasto.proveedor', '=', $filtroProveedor);
        }

        if ($gasto >= 1  && !$this->isNull($gasto)) {
            $gastos = $gastos->where('gasto.tipo_gasto', '=', $gasto);
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $gastos = $gastos->where('gasto.sucursal', 'like', '%' . $filtroSucursal . '%');
        }

        if ($this->isIn($filtroAprobado, array('S', 'N', 'R'))) {
            $gastos = $gastos->where('gasto.aprobado', 'like', $filtroAprobado);
        }

        if (!$this->isNull($desde)) {
            $gastos = $gastos->where('gasto.fecha', '>=', $desde);
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $gastos = $gastos->where('gasto.fecha', '<', $mod_date);
        }

        $gastos = $gastos->get();
        $totalGastos = 0;
        foreach ($gastos as $i) {
            $totalGastos = $totalGastos + $i->monto;
        }

        $filtros = [
            'proveedor' => $filtroProveedor,
            'sucursal' => $filtroSucursal,
            'aprobado' => $filtroAprobado,
            'tipo_gasto' => $gasto,
            'hasta' => $hasta,
            'desde' => $desde,
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'totalGastos' => $totalGastos,
            'gastos' => $gastos,
            'filtros' => $filtros,
            'tipos_gasto' => $this->getTiposGasto(),
            'proveedores' => $this->getProveedores(),
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('gastos.gastosAdmin', compact('data'));
    }

    public function goGastosPendientesAdmin()
    {

        $gastosSinAprobar =  DB::table('gasto')
            ->join('proveedor', 'proveedor.id', '=', 'gasto.proveedor')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->select('gasto.*', 'proveedor.nombre', 'usuario.usuario as nombreUsuario')
            ->where('aprobado', 'like', 'N')->get();

        foreach ($gastosSinAprobar as $gasto) {
            $gasto->fecha = $this->fechaFormat($gasto->fecha);
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'gastosSinAprobar' => $gastosSinAprobar,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('gastos.gastosPendientesAdmin', compact('data'));
    }

    public function goGastosPendientesUsuario()
    {

        $gastosSinAprobar =  DB::table('gasto')
            ->join('proveedor', 'proveedor.id', '=', 'gasto.proveedor')
            ->select('gasto.*', 'proveedor.nombre')->where('usuario', '=', $this->getUsuarioAuth()['id'])
            ->where('caja_cerrada', '=', 'N')
            ->where('aprobado', 'like', 'N')->get();
        foreach ($gastosSinAprobar as $gasto) {
            $gasto->fecha = $this->fechaFormat($gasto->fecha);
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'gastosSinAprobar' => $gastosSinAprobar,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('gastos.gastosPendientesUsuario', compact('data'));
    }

    public function returnNuevoGastoWithData($datos)
    {
        if (!$this->validarSesion("gastNue")) {
            return redirect('/');
        }

        if ($this->usuarioAdministrador()) {
            $tipo_gasto = $this->getTiposGasto();
        } else {
            $tipo_gasto = [];
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'datos' => $datos,
            'tipos_gasto' =>  $tipo_gasto,
            'tipos_pago' => $this->getTiposPago(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        if ($this->usuarioAdministrador()) {
            return view('gastos.registrarGastoAdmin', compact('data'));
        } else {
            return view('gastos.registrarGastoUsuario', compact('data'));
        }
    }

    /**
     * Guarda o actualiza un gasto
     */
    public function guardarGasto(Request $request)
    {
      
        if (!$this->validarSesion("gastNue")) {
            return redirect('/');
        }
        
        $id = $request->input('id');
        if ($id < 1 || $this->isNull($id)) { // Nuevo usuario
            $actualizar = false;
        } else { // Editar usuario
            $actualizar = true;
        }
       
        if ($this->validarGasto($request)) {

            $tipo_documento = $request->input('tipo_documento');
            $tipo_pago = $request->input('tipo_pago');

            $proveedor = $request->input('proveedor');
            $observacion = $request->input('observacion');
            $descripcion = $request->input('descripcion');
            $total = $request->input('total');
            $num_comprobante = $request->input('num_comprobante');
            $fecha_actual = date("Y-m-d H:i:s");
            $fecha = $request->input('fecha');
            $usuarioId = $this->getUsuarioAuth()['id'];
            $sucursal =  DB::table('sucursal')
                ->join('usuario', 'usuario.sucursal', '=', 'sucursal.id')
                ->select('sucursal.descripcion', 'sucursal.estado')
                ->where('usuario.id', '=', $usuarioId)->get()->first();
            if ($sucursal == null || $sucursal->estado != 'A') {
                $this->setError('Guardar gasto', "La sucursal no existe o esta inactiva.");
                return $this->returnNuevoGastoWithData($request->all());
            }
            

            $proveedorAux =  DB::table('proveedor')
                ->where('proveedor.id', '=', $proveedor)->get()->first();
            if ($proveedorAux == null || $proveedorAux->estado != 'A') {
                $this->setError('Guardar gasto', "El proveedor no existe o esta inactiva.");
                return $this->returnNuevoGastoWithData($request->all());
            }

            if ($fecha != null && $fecha != '') {
                $fecha_actual = $fecha;
            }
            $tipo_gasto = $request->input('tipo_gasto') ?? 1;

            if ($actualizar) { // Editar gasto
                $ingreso =  DB::table('gasto')
                    ->where('id', '=', $id)
                    ->select('gasto.ingreso')->get()->first()->ingreso ?? -1;
            } else {
                $ingreso = -1;
            }

            $aprobado = "S";
         
            try {
                DB::beginTransaction();

               
                if ($actualizar) { // Editar gasto
                    $this->bitacoraMovimientos('gasto', 'editar', $id, $total, $fecha_actual);

                    DB::table('gasto')
                        ->where('id', '=', $id)
                        ->update([
                            'monto' => $total, 'descripcion' => $descripcion, 'num_factura' => $num_comprobante,
                            'proveedor' => $proveedor,
                            'tipo_pago' => $tipo_pago, 'tipo_documento' => $tipo_documento, 'tipo_gasto' => $tipo_gasto,
                            'observacion' => $observacion, 'sucursal' => $sucursal->descripcion
                        ]);
                } else { // Nuevo gasto
                    $id = DB::table('gasto')->insertGetId([
                        'id' => null, 'monto' => $total, 'descripcion' => $descripcion, 'num_factura' => $num_comprobante,
                        'usuario' => $usuarioId, 'proveedor' => $proveedor, 'fecha' => $fecha_actual,
                        'tipo_pago' => $tipo_pago, 'tipo_documento' => $tipo_documento, 'tipo_gasto' => $tipo_gasto,
                        'aprobado' => $aprobado, 'observacion' => $observacion,  'sucursal' => $sucursal->descripcion, 'ingreso' => $ingreso,
                        'url_factura' => null
                    ]);
                   
                    $img = $request->file('foto_comprobante');
                   
                    if ($request->file('foto_comprobante') != null) {
                        $imageName =   'gasto-' . $id . '.png';
                        $img->move(public_path() . '/gastos/', $imageName);
                        $path = public_path() . '/gastos/' . $imageName;
                        //    $img = Image::make($path)->resize(400, 400);
                        //    $img->save($path, 80);
                        DB::table('gasto')
                            ->where('id', '=', $id)->update(['url_factura' => $path]);
                    }
                    
                    $this->bitacoraMovimientos('gasto', 'nuevo', $id, $total, $fecha_actual);
                }
               
                DB::commit();
              

                if ($actualizar) { // Editar usuario
                    $this->setSuccess('Guardar gasto', 'Se actualizo el gasto correctamente.');
                    return $this->goGastoById($id);
                } else { // Nuevo usuario

                    $this->setSuccess('Guardar gasto', 'Gasto creado correctamente.');
                    return $this->goGastoById($id);
                }
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Gasto', 'Algo salío mal, reintentalo!');
                if ($actualizar) { // Editar usuario
                    return $this->goEditarGastoById($id);
                } else { // Nuevo usuario
                    return $this->returnNuevoGastoWithData($request->all());
                }
            }
        } else {
            if ($actualizar) { // Editar usuario
                return $this->goEditarGastoById($id);
            } else { // Nuevo usuario
                return $this->returnNuevoGastoWithData($request->all());
            }
        }
    }

    /**
     * Busca la foto del gasto, la convierte en base 64 y la devuelve
     */
    public function getFotoBase64(Request $request)
    {
        $id = $request->input('gasto');

        if ($id == null || $id < 1) {
            echo '-1';
            exit;
        }
        try {
            $gasto =  DB::table('gasto')->where('id', '=', $id)->get()->first();
            echo $this->getContentsOrDefault($gasto->url_factura);
        } catch (QueryException $ex) {
            DB::rollBack();
            echo "-1"; // error en el proceso de bd
            exit;
        }
    }


    /**
     * Confirma un gasto
     */
    public function confirmarGasto(Request $request)
    {
        if (!$this->validarSesion("gastTodos")) {
            echo "-1";
            exit;
        }

        $id = $request->input('gasto');
        $gasto =  DB::table('gasto')->where('id', '=', $id)->get()->first();
        if ($gasto->caja_cerrada == 'N') {
            echo "400"; // error en el proceso de bd
            exit;
        }
        if ($gasto != null) {
            try {
                DB::beginTransaction();

                DB::table('gasto')
                    ->where('id', '=', $id)->update(["aprobado" => "S"]);
                $this->bitacoraMovimientos('gasto', 'aprobar', $id, $gasto->monto);
                DB::commit();
                echo "500"; // exito
                exit;
            } catch (QueryException $ex) {
                DB::rollBack();
                echo "400"; // error en el proceso de bd
                exit;
            }
        } else {
            echo "404"; // no encontrado
            exit;
        }
    }

    /**
     * 
     * Rechza un gasto
     */
    public function rechazarGasto(Request $request)
    {
        if (!$this->validarSesion("gastTodos")) {
            return redirect('/');
        }
        if (!$this->usuarioAdministrador()) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idGastoRechazar');
        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();

        if ($gasto == null) {
            $this->setError('Rechazar gasto', "El gasto no existe.");
            return redirect('/');
        }

        if ($gasto->aprobado == 'S') {
            $this->setError('Rechazar gasto', "El gasto ya fue aprobado.");
            return redirect('gastos/pendientes');
        }

        if ($gasto->caja_cerrada == 'N') {
            $this->setError('Rechazar gasto', "La caja no ha sido cerrada.");
            return redirect('gastos/pendientes');
        }

        try {
            DB::beginTransaction();

            DB::table('gasto')
                ->where('id', '=', $id)->update(['aprobado' => 'R']); // Rechazado
            $this->bitacoraMovimientos('gasto', 'rechazar', $id, $gasto->monto);

            DB::commit();
            $this->setSuccess('Rechazar gasto', "El gasto se rechazo correctamente.");
            return redirect('gastos/pendientes');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Rechazar gasto', "Algo salío mal, reintentalo.");
            return redirect('/');
        }
    }

    /**
     * Elimina un gasto
     */
    public function eliminarGastoSinAprobar(Request $request)
    {
        if (!$this->validarSesion("gastNue")) {
            return redirect('/');
        }

        $id = $request->input('idGastoEliminar');
        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();

        if ($gasto == null) {
            $this->setError('Eliminar gasto', "El gasto no existe.");
            return redirect('/');
        }

        if (!$this->usuarioAdministrador()) {
            if ($gasto->usuario != session('usuario')['id']) {
                $this->setMsjSeguridad();
                return redirect('/');
            }
            if ($gasto->aprobado == 'S') {
                $this->setError('Eliminar gasto', "El gasto ya fue aprobado.");
                return redirect('gastos/pendientes');
            }

            if ($gasto->caja_cerrada != 'N') {
                $this->setError('Eliminar gasto', "La caja ya fue cerrada.");
                return redirect('gastos/pendientes');
            }
        }

        try {
            DB::beginTransaction();

            DB::table('gasto')
                ->where('id', '=', $id)->delete();
            $this->bitacoraMovimientos('gasto', 'eliminar', $id, $gasto->monto);

            DB::commit();
            $this->setSuccess('Eliminar gasto', "El gasto se elimino correctamente.");
            return redirect('gastos/pendientes');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar gasto', "Algo salío mal, reintentalo.");
            return redirect('/');
        }
    }

    /**
     * Elimina un gasto
     */
    public function eliminarGasto(Request $request)
    {
        if (!$this->validarSesion("gastTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if (!$this->usuarioAdministrador()) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $id = $request->input('idGastoEliminar');
        $gasto = DB::table('gasto')->where('id', '=', $id)->get()->first();

        if ($gasto == null) {
            $this->setError('Eliminar gasto', "El gasto no existe.");
            return redirect('/');
        }

        if ($gasto->caja_cerrada == 'N') {
            $this->setError('Eliminar gasto', "La caja no ha sido cerrada.");
            return redirect('gastos/pendientes');
        }

        try {
            DB::beginTransaction();

            DB::table('gasto')
                ->where('id', '=', $id)->update(['aprobado' => 'E']);
            $this->bitacoraMovimientos('gasto', 'eliminar', $id, $gasto->monto);

            DB::commit();
            $this->setSuccess('Eliminar gasto', "El gasto se elimino correctamente.");
            return redirect('gastos/administracion');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar gasto', "Algo salío mal, reintentalo.");
            return redirect('/');
        }
    }


    public function validarGasto(Request $r)
    {
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;

        if ($this->isNull($r->input('proveedor')) || $this->isEmpty($r->input('proveedor'))) {
            $requeridos .= " Proveedor ";
            $valido = false;
            $esPrimero = false;
        }

        if ($this->isNull($r->input('tipo_pago')) || $this->isEmpty($r->input('tipo_pago'))) {
            $requeridos .= " Tipo pago ";
            $valido = false;
            $esPrimero = false;
        }

        if ($this->isNull($r->input('total')) || $this->isEmpty($r->input('total'))) {
            $requeridos .= " Total ";
            $valido = false;
            $esPrimero = false;
        }
        if ($this->isNull($r->input('descripcion')) || $this->isEmpty($r->input('descripcion'))) {
            $requeridos .= " Descripción del gasto ";
            $valido = false;
            $esPrimero = false;
        }

        $requeridos .= "] ";
        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        if (!$this->isIn($r->input('tipo_documento'), array("F", "O"))) {
            $this->setError('Error de integridad', "Tipo de documento invalido.");
            return false;
        }
        if ($this->isNull($r->input('tipo_pago'))) {
            $this->setError('Error de integridad', "Tipo de pago invalido.");
            return false;
        }

        if (!$this->isLengthMinor($r->input('num_comprobante'), 50)) {
            $this->setError('Tamaño exedido', "El número de comprobante debe ser de máximo 50 caracteres.");
            return false;
        }
        if (!$this->isLengthMinor($r->input('descripcion'), 150)) {
            $this->setError('Tamaño exedido', "La descripción del gasto debe ser de máximo 150 caracteres.");
            return false;
        }
        if (!$this->isLengthMinor($r->input('observacion'), 150)) {
            $this->setError('Tamaño exedido', "La observación debe ser de máximo 150 caracteres.");
            return false;
        }
        if (!$this->isNumber($r->input('total')) || $r->input('total') < 10) {
            $this->setError('Número incorrecto', "El total debe ser mayor que 10.00 CRC.");
            return false;
        }

        return $valido;
    }

    public function filtrarGastosPendientes(Request $request)
    {
        $texto = $request->input('texto');
        $gastosSinAprobar =  DB::table('gasto')
            ->join('proveedor', 'proveedor.id', '=', 'gasto.proveedor')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->select('gasto.*', 'proveedor.nombre', 'usuario.usuario as nombreUsuario')
            ->where('aprobado', 'like', 'N')
            ->where(function ($query) use ($texto) {
                $query->where('proveedor.nombre', 'like', '%' . $texto . '%')
                    ->orWhere('usuario.usuario', 'like', '%' . $texto . '%')
                    ->orWhere('gasto.monto', 'like', '%' . $texto . '%')
                    ->orWhere('gasto.descripcion', 'like', '%' . $texto . '%');
            })->get();

        foreach ($gastosSinAprobar as $gasto) {
            $gasto->fecha = $this->fechaFormat($gasto->fecha);
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'gastosSinAprobar' => $gastosSinAprobar,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('gastos.layout.gastosPendientesAdmin', compact('data'));
    }

    public function filtrarGastosPendientesUsuario(Request $request)
    {
        $texto = $request->input('texto');
        $gastosSinAprobar =  DB::table('gasto')
            ->join('proveedor', 'proveedor.id', '=', 'gasto.proveedor')
            ->join('usuario', 'usuario.id', '=', 'gasto.usuario')
            ->select('gasto.*', 'proveedor.nombre', 'usuario.usuario as nombreUsuario')
            ->where('aprobado', 'like', 'N')
            ->where('usuario.id', '=', $this->getUsuarioAuth()['id'])
            ->where(function ($query) use ($texto) {
                $query->where('proveedor.nombre', 'like', '%' . $texto . '%')
                    ->orWhere('usuario.usuario', 'like', '%' . $texto . '%')
                    ->orWhere('gasto.monto', 'like', '%' . $texto . '%')
                    ->orWhere('gasto.descripcion', 'like', '%' . $texto . '%');
            })->get();

        foreach ($gastosSinAprobar as $gasto) {
            $gasto->fecha = $this->fechaFormat($gasto->fecha);
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'gastosSinAprobar' => $gastosSinAprobar,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('gastos.layout.gastosPendientesUsuario', compact('data'));
    }
}
