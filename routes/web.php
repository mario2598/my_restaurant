<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| Rutas INICIO
|--------------------------------------------------------------------------
*/

Route::group([], function () {
    Route::post('login', 'LogInController@logIn');
    Route::get('login', 'LogInController@index');
    Route::get('/', 'LogInController@index');
    Route::get('logOut', 'LogInController@logOut');
});


Route::group(['middleware' => 'autorizated'], function () {
    Route::get('/perfil/usuario', 'PerfilUsuarioController@goPerfilUsuario');
    Route::post('/perfil/usuario/guardar', 'MantenimientoUsuariosController@guardarUsuarioPerfilAjax');
    Route::post('/perfil/usuario/seg', 'PerfilUsuarioController@cambiarContraPerfil');
    Route::post('/perfil/usuario/guardar', 'MantenimientoUsuariosController@guardarUsuarioPerfilAjax');
    Route::post('/perfil/usuario/seg', 'PerfilUsuarioController@cambiarContraPerfil');
    Route::post('mant/usuarios/cargarUsuario', 'MantenimientoUsuariosController@cargarUsuarioAjax');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de usuarios general
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:mantUsu'], function () {
    Route::get('mant/usuarios', 'MantenimientoUsuariosController@index');
    Route::get('mant/usuarios/cargarUsuarios', 'MantenimientoUsuariosController@cargarUsuariosAjax');
    Route::post('mant/usuarios/usuario', 'MantenimientoUsuariosController@goEditarUsuario');
    Route::post('/mant/usuarios/usuario/guardar', 'MantenimientoUsuariosController@guardarUsuarioAjax');
    Route::post('/mant/usuarios/usuario/seg', 'MantenimientoUsuariosController@cambiarContra');
    Route::post('/mant/usuarios/usuario/inactivar', 'MantenimientoUsuariosController@inactivarUsuario');
    Route::post('/mant/usuarios/usuario/activar', 'MantenimientoUsuariosController@activarUsuario');
});

Route::group(['middleware' => 'autorizated:mantSuc'], function () {
    Route::get('mant/sucursales', 'MantenimientoSucursalController@index');
    Route::post('guardarsucursal', 'MantenimientoSucursalController@guardarSucursal');
    Route::post('eliminarsucursal', 'MantenimientoSucursalController@eliminarSucursal');
    Route::post('mant/sucursales/cargar', 'MantenimientoSucursalController@cargarSucursalAjax');
});


/*
|--------------------------------------------------------------------------
| Mantenimiento de Parametros Generales
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'autorizated:mantParGen'], function () {
    Route::get('mant/parametrosgenerales', 'ParametrosGeneralesController@index');
    Route::post('mant/guardarparametrosgenerales', 'ParametrosGeneralesController@guardar');
});


/*
|--------------------------------------------------------------------------
| Mantenimiento de Gastos
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:gastTodos'], function () {
    Route::get('gastos/administracion', 'GastosController@goGastosAdmin');
    Route::post('gastos/administracion/filtro', 'GastosController@goGastosAdminFiltro');
    Route::post('gastos/gasto', 'GastosController@goGasto');
    Route::post('gasto/fotoBase64', 'GastosController@getFotoBase64');
});

Route::group(['middleware' => 'autorizated:gastNue'], function () {
    Route::get('gastos/nuevo', 'GastosController@goNuevoGasto');
    Route::post('gastos/guardar', 'GastosController@guardarGasto');
    Route::post('gastos/editar', 'GastosController@goEditarGasto');
    Route::post('gastos/eliminar', 'GastosController@eliminarGasto');
    Route::post('gastos/sinaprobar/eliminar', 'GastosController@eliminarGastoSinAprobar');
    Route::post('gastos/rechazar', 'GastosController@rechazarGasto');
    Route::post('confirmarGasto', 'GastosController@confirmarGasto');
    Route::get('gastos/pendientes', 'GastosController@goGastosPendientes');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de Ingresos CREACION
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:ingNue'], function () {
    Route::get('ingresos/nuevo', 'IngresosController@index');
    Route::post('ingresos/guardar', 'IngresosController@guardarIngreso');
    Route::get('ingresos/administracion', 'IngresosController@goIngresosAdmin');
    Route::post('ingresos/administracion/filtro', 'IngresosController@goIngresosAdminFiltro');
    Route::post('ingresos/ingreso', 'IngresosController@goIngreso');

    Route::post('ingresos/eliminar', 'IngresosController@eliminarIngreso');
    Route::post('ingresos/aprobar', 'IngresosController@aprobarIngreso');
    Route::post('ingresos/rechazar', 'IngresosController@rechazarIngreso');
    Route::post('ingresos/gastos/rechazar', 'IngresosController@rechazarIngresoGasto');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de Ingresos ADMIN
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:ingTodos'], function () {
    Route::get('ingresos/administracion', 'IngresosController@goIngresosAdmin');
    Route::post('ingresos/administracion/filtro', 'IngresosController@goIngresosAdminFiltro');
    Route::post('ingresos/ingreso', 'IngresosController@goIngreso');
    Route::post('ingresos/eliminar', 'IngresosController@eliminarIngreso');
    Route::get('ingresos/pendientes', 'IngresosController@goIngresosPendientes');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de Comandas Dispositivos
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:comandasAdmin'], function () {
    Route::get('comandar/admin', 'ComandasController@goComandasAdmin');
    Route::get('comandas/administrar/cargar', 'ComandasController@cargarComandasAdmin');
    Route::post('comandas/administrar/guardarComanda', 'ComandasController@guardarComanda');
    Route::post('comandas/administrar/eliminarComanda', 'ComandasController@eliminarComanda');
});

/*
|--------------------------------------------------------------------------
| Mantenimiento de Mesas
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:mesasAdmin'], function () {
    Route::get('mobiliario/mesas/admin', 'MesasController@goMesasAdmin');
    Route::get('mobiliario/mesas/cargar', 'MesasController@cargarMesasAdmin');
    Route::post('mobiliario/mesas/guardarMesa', 'MesasController@guardarMesa');
    Route::post('mobiliario/mesas/eliminarMesa', 'MesasController@eliminarMesa');
});

/*
|--------------------------------------------------------------------------
|POS
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:facFac'], function () {
    Route::post('facturacion/pos/crearFactura', 'FacturacionController@crearFactura');
    Route::post('facturacion/pos/iniciarOrden', 'FacturacionController@iniciarOrden');
    Route::post('facturacion/pos/actualizarOrden', 'FacturacionController@actualizarOrden');
    Route::post('facturacion/pos/pagarOrden', 'FacturacionController@pagarOrden');
    Route::get('facturacion/pos/cargarPosProductos', 'FacturacionController@cargarPosProductosAjax');
    Route::get('facturacion/pos', 'FacturacionController@goPos');
    Route::post('facturacion/buscar-clientes', 'FacturacionController@buscarClientes');
    Route::post('facturacion/obtener-cliente', 'FacturacionController@obtenerCliente');
    Route::post('facturacion/clientes/guardar', 'MantenimientoClientesController@guardarCliente');
    Route::post('facturacion/clientes/obtener-info-fe-cliente', 'MantenimientoClientesController@obtenerInfoFECliente');
    Route::post('facturacion/clientes/guardar-info-fe-cliente', 'MantenimientoClientesController@guardarInfoFECliente');
});

Route::group(['middleware' => 'autorizated:prod_ext_inv'], function () {
    Route::get('productoExterno/inventario/inventarios/cargarComandas', 'ProductosExternosController@cargarComandas');
    Route::get('productoExterno/inventario/inventarios/cargarPeSucursal', 'ProductosExternosController@cargarPeSucursal');
    Route::post('productoExterno/inventario/inventarios/guardar', 'ProductosExternosController@guardarProductoSucursal');
    Route::post('productoExterno/inventario/inventarios/aumentar', 'ProductosExternosController@aumentarProductoSucursal');
    Route::post('productoExterno/inventario/inventarios/disminuir', 'ProductosExternosController@disminuirProductoSucursal');
});


/*
|--------------------------------------------------------------------------
|COMANDAS PREPRARACION
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:comandaPrep'], function () {
    Route::get('comandas/preparacion/comandaGen', 'ComandasController@goComandaPreparacionGen');
    Route::post('comandas/preparacion/recargarComandas', 'ComandasController@recargarComandas');
    Route::get('comandas/preparacion/comanda/{idComanda}', 'ComandasController@goComandaPreparacionId');
    Route::post('comandas/preparacion/comanda/terminarPreparacionComanda', 'ComandasController@terminarPreparacionComanda');
});

/*
|--------------------------------------------------------------------------
|MATERIA PRIMA
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:mt_inv'], function () {
    Route::get('materiaPrima/inventario/inventarios/cargarMateriPrimaInvSucursal', 'MateriaPrimaController@cargarMateriPrimaInvSucursal');
    Route::get('materiaPrima/inventario/inventarios/cargarMateriPrimaNotSucursal', 'MateriaPrimaController@cargarMateriPrimaNotinSucursal');
    Route::post('materiaPrima/inventario/inventarios/guardar', 'MateriaPrimaController@guardarProductoSucursal');
    Route::post('materiaPrima/inventario/inventarios/crearProducto', 'MateriaPrimaController@crearProductoSucursal');
    Route::post('materiaPrima/inventario/inventarios/aumentar', 'MateriaPrimaController@aumentarProductoSucursal');
    Route::post('materiaPrima/inventario/inventarios/disminuir', 'MateriaPrimaController@disminuirProductoSucursal');
});

/*
|--------------------------------------------------------------------------
|FACTURACION ELECTRONICA
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:fe_fes'], function () {
    Route::get('fe/facturas', 'FeController@goFacturasFe');
    Route::post('fe/filtrarFacturas', 'FeController@filtrarFacturas');
    Route::post('fe/enviarFe', 'FeController@enviarFe');
    Route::post('fe/enviarFacturaHacienda', 'FeController@enviarFacturaHaciendaV2'); // Método V2 - Enviar Factura (con cliente, formato nuevo FactuX)
    Route::post('fe/enviarComprobanteHacienda', 'FeController@enviarComprobanteHacienda'); // Método V2 sin cliente - Enviar Comprobante (sin datos del cliente)
    Route::post('fe/obtenerJsonComprobante', 'FeController@obtenerJsonComprobante');
    Route::post('fe/consultarEstadoHacienda', 'FeController@consultarEstadoHacienda');
    Route::post('fe/reenviarCorreoFactuX', 'FeController@reenviarCorreoFactuX'); // Reenviar correo del comprobante
    Route::get('fe/obtenerUnidadesMedida', 'FeController@obtenerUnidadesMedida');
    Route::post('fe/obtenerPagosSinFE', 'FeController@obtenerPagosSinFE'); // Obtener pagos sin FE asociado
    Route::post('fe/crearFeDesdePago', 'FeController@crearFeDesdePago'); // Crear FE desde un pago sin FE
    Route::post('fe/actualizarClienteFeInfo', 'FeController@actualizarClienteFeInfo'); // Actualizar cliente en fe_info
});

/*
|--------------------------------------------------------------------------
|MANTENIMIENTO DE CLIENTES
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'autorizated:mantClientes'], function () {

    Route::get('mant/clientes', 'MantenimientoClientesController@index');
    Route::post('mant/clientes/guardar', 'MantenimientoClientesController@guardarCliente');
    Route::post('mant/clientes/eliminarcliente', 'MantenimientoClientesController@eliminarCliente');
    Route::post('mant/clientes/obtener-clientes-ajax', 'MantenimientoClientesController@obtenerClientesAjax');
    Route::post('mant/clientes/obtener-cliente', 'MantenimientoClientesController@obtenerCliente');
    Route::post('mant/clientes/obtener-info-fe-cliente', 'MantenimientoClientesController@obtenerInfoFECliente');
    Route::post('mant/clientes/guardar-info-fe-cliente', 'MantenimientoClientesController@guardarInfoFECliente');
});

Route::group(['middleware' => 'autorizated:prod_mnu'], function () {
    Route::post('productos/guardarConfigFE', 'FeController@guardarConfigFE');
    Route::get('productos/cargarDatosFE', 'FeController@cargarDatosFEProducto');
});


/*** tiposgasto */
Route::get('mant/tiposgasto', 'MantenimientoTiposGastoController@index');
Route::post('guardartipogasto', 'MantenimientoTiposGastoController@guardarTipoGasto');
Route::post('eliminartipogasto', 'MantenimientoTiposGastoController@eliminarTipoGasto');

/*** categorias */
Route::get('mant/categoria', 'MantenimientoCategoriaController@index');
Route::post('guardarcategoria', 'MantenimientoCategoriaController@guardarCategoria');
Route::post('eliminarcategoria', 'MantenimientoCategoriaController@eliminarCategoria');

/*** impuestos */
Route::get('mant/impuestos', 'MantenimientoImpuestosController@index');
Route::post('guardarimpuesto', 'MantenimientoImpuestosController@guardarImpuesto');
Route::post('eliminarimpuesto', 'MantenimientoImpuestosController@eliminarImpuesto');

/*** Cod promocion */
Route::get('mant/codPromocion', 'CodigosPromocionController@goMantPromos');
Route::post('mant/guardarPromocion', 'CodigosPromocionController@guardarPromocion');
Route::post('eliminarimpuesto', 'MantenimientoImpuestosController@eliminarImpuesto');

/*** tiposingreso */
Route::get('mant/tiposingreso', 'MantenimientoTiposIngresoController@index');
Route::post('guardartipoingreso', 'MantenimientoTiposIngresoController@guardarTipoIngreso');
Route::post('eliminartipoingreso', 'MantenimientoTiposIngresoController@eliminarTipoIngreso');


/*** tipospago */
Route::get('mant/tipospago', 'MantenimientoTiposPagoController@index');
Route::post('guardartipopago', 'MantenimientoTiposPagoController@guardarTipoPago');
Route::post('eliminartipopago', 'MantenimientoTiposPagoController@eliminarTipoPago');


/*** Proveedores */
Route::get('mant/proveedores', 'MantenimientoProveedorController@index');
Route::post('guardarproveedor', 'MantenimientoProveedorController@guardarProveedor');
Route::post('eliminarproveedor', 'MantenimientoProveedorController@eliminarProveedor');

/*** Bancos */
Route::get('mant/bancos', 'MantenimientoBancoController@index');
Route::post('guardarbanco', 'MantenimientoBancoController@guardarBanco');
Route::post('eliminarbanco', 'MantenimientoBancoController@eliminarBanco');

/*** Bancos */
Route::get('mant/roles', 'MantenimientoRolesController@index');
Route::post('guardarrol', 'MantenimientoRolesController@guardarRol');
Route::post('eliminarrol', 'MantenimientoRolesController@eliminarRol');
Route::post('cargarPermisosRoles', 'MantenimientoRolesController@cargarPermisosRoles');


/*** Usuarios */
Route::get('restaurar_pc', 'MantenimientoUsuariosController@restaurarPc');
Route::get('tema_claro', 'MantenimientoUsuariosController@temaClaro');
Route::get('tema_oscuro', 'MantenimientoUsuariosController@temaOscuro');
Route::post('side_teme', 'MantenimientoUsuariosController@sideTeme');
Route::post('color_teme', 'MantenimientoUsuariosController@colorTeme');
Route::post('sticky', 'MantenimientoUsuariosController@sticky');



Route::get('inicio', function () {
    return view('inicio');
});

Route::get('usuario', function () {
    return view('mant.usuario');
});




///**************Caja     */

Route::get('caja/cierre', 'CajaController@goCierre');
Route::post('caja/cerrarcaja', 'CajaController@cerrarCaja');
Route::post('caja/cajaPrevia', 'CajaController@getCajaPrevia');
Route::post('caja/abrirCaja', 'CajaController@abrirCaja');

/******************Informes ********************** */
Route::post('informes/resumencontable/filtro', 'InformesController@goResumenContableFiltro');
Route::post('informes/resumencontable/generar-pdf', 'InformesController@generarReporteResumenContablePDF');
Route::get('informes/resumencontable', 'InformesController@goResumenContable');
Route::get('informes/ventaXhora', 'InformesController@goVentaXhora');
Route::post('informes/ventaXhora/filtro', 'InformesController@goVentaXhoraFiltro');

Route::get('informes/ventaGenProductos', 'InformesController@goVentaGenProductos');
Route::post('informes/ventaGenProductos/filtro', 'InformesController@goVentaGenProductosFiltro');

Route::get('informes/movInvProductoExterno', 'InformesController@goMovInvProductoExterno');
Route::post('informes/movInvProductoExterno/filtro', 'InformesController@goMovInvProductoExternoFiltro');

Route::get('informes/movConMateriaPrima', 'InformesController@goMovConMateriaPrima');
Route::post('informes/movConMateriaPrima/filtro', 'InformesController@goMovConMateriaPrimaFiltro');

Route::get('informes/conMateriaPrima', 'InformesController@goConMateriaPrima');
Route::post('informes/conMateriaPrima/filtro', 'InformesController@goConMateriaPrimaFiltro');
/******************Bodega **************************** */
Route::get('bodega/productos', 'ProductosController@goProductos');
Route::post('bodega/productos/filtro', 'ProductosController@goProductosFiltro');

/******************Bodega / inventarios **************************** */
Route::get('bodega/inventario/trasladar', 'InventariosController@goTrasladar');
Route::get('bodega/inventario/inventarios', 'InventariosController@goInventarios');
Route::post('bodega/inventario/inventarios/filtro', 'InventariosController@goInventariosFiltro');
Route::post('bodega/inventario/movimiento', 'MovimientoController@goMovimiento');
Route::post('bodega/inventario/trasladar/cargarInventario', 'InventariosController@cargarInventario');
Route::post('iniciarTraslado', 'InventariosController@iniciarTraslado');
Route::post('bodega/movimiento/cancelar', 'MovimientoController@cancelarMovimiento');


/******************Bodega / Lote **************************** */
Route::get('bodega/lote/nuevo', 'LoteController@goNuevo');
Route::post('bodega/lote/guardar', 'LoteController@guardar');

/******************Bodega / Productos **************************** */
Route::get('bodega/producto/nuevo', 'ProductosController@goNuevoProducto');
Route::post('bodega/producto/editar', 'ProductosController@goEditarProducto');
Route::post('bodega/producto/guardar', 'ProductosController@guardarProducto');
Route::post('bodega/producto/eliminar', 'ProductosController@eliminarProducto');
Route::get('bodega/inventario/pedidos', 'PedidoSucursalController@goPedidosBodegaPendientes');
Route::post('bodega/inventario/pedido', 'PedidoSucursalController@goPedidoBodega');
Route::post('bodega/inventario/pedido/procesar', 'PedidoSucursalController@procesarPedidoBodega');


/******************Bitacora / Movimientos **************************** */
// Fondos
Route::get('bitacora/movimientos/fondos', 'BitacoraMovimientosFondoController@goMovimientosFondos');
Route::post('bitacora/movimientos/fondos/filtro', 'BitacoraMovimientosFondoController@goMovimientosFondosFiltro');
Route::post('actualizarBitacoraMoviminetosFondo', 'BitacoraMovimientosFondoController@actualizar');
//Inv
Route::get('bitacora/movimientos/inventario', 'BitacoraMovimientosInventarioController@goMovimientosInv');
Route::post('bitacora/movimientos/inventario/filtro', 'BitacoraMovimientosInventarioController@goMovimientosInvFiltro');
Route::post('actualizarBitacoraMoviminetosInv', 'BitacoraMovimientosInventarioController@actualizar');


/*****************Movimientos**************************** */
Route::get('movimientos', 'MovimientoController@goMovimientos');
Route::post('bodega/productos/filtro', 'ProductosController@goProductosFiltro');

/*****************Inventario**************************** */
Route::get('inventario/movimientos/pendientes', 'InventarioController@goMovimientosPendientesSucursal');
Route::post('inventario/movimientos/pendiente', 'InventarioController@goMovimientoPendienteSucursal');
Route::post('actualizarMoviminetosInvPendSucursal', 'InventarioController@cargarMovimientosPendientesSucursal');
Route::post('aceptarMovimientoSucursal', 'InventarioController@aceptarMovimientoSucursal');
Route::post('iniciarDevolucion', 'InventarioController@iniciarDevolucion');
Route::post('iniciarTrasladoSucursal', 'InventarioController@iniciarTrasladoSucursal');
Route::get('inventario/sucursal/devolucion', 'InventarioController@goDevolucionInventarioSucursal');
Route::get('inventario/sucursal/pedido', 'PedidoSucursalController@goPedidoInventarioSucursal');
Route::get('inventario/sucursal/pedidos/pendientes', 'PedidoSucursalController@goPedidosInventarioPendientes');
Route::post('inventario/sucursal/pedidos/pendientes/eliminar', 'PedidoSucursalController@eliminarPedido');
Route::post('inventario/sucursal/pedidos/pedido', 'PedidoSucursalController@goEditarPedidoInventarioSucursal');
Route::get('inventario/sucursal/traslado', 'InventarioController@goTrasladoInventarioSucursal');
Route::post('aceptarDevolucionSucursal', 'InventarioController@aceptarDevolucionSucursal');
Route::post('inventario/pedido/iniciarPedido', 'PedidoSucursalController@crearPedido');


/*****************Desechos**************************** */
Route::get('desechos', 'DesechosController@goDesechos');
Route::post('desechos/filtro', 'DesechosController@goDesechosFiltro');
Route::get('desechos/agregar', 'DesechosController@goAgregarDesechos');
Route::post('desechos/cambiarInventario', 'DesechosController@cambiarInventario');
Route::post('desechos/confirmar', 'DesechosController@tirarDesechos');

/*****************MENU**************************** */

Route::get('menu/productos', 'ProductosMenuController@goProductosMenu');
Route::post('menu/productos/filtro', 'ProductosMenuController@goProductosMenuFiltro');
Route::post('menu/productos/guardarMpProd', 'ProductosMenuController@guardarMpProd');
Route::post('menu/productos/guardarExtProd', 'ProductosMenuController@guardarExtras');
Route::post('menu/productos/eliminarMpProd', 'ProductosMenuController@eliminarMpProd');
Route::post('menu/productos/eliminarExtra', 'ProductosMenuController@eliminarExtra');
Route::get('menu/productos/cargarMpProd', 'ProductosMenuController@cargarMpProd');
Route::get('menu/productos/cargarExtras', 'ProductosMenuController@cargarExtras');
Route::get('menu/producto/nuevo', 'ProductosMenuController@goNuevoProducto');
Route::post('menu/producto/editar', 'ProductosMenuController@goEditarProducto');
Route::post('menu/producto/eliminar', 'ProductosMenuController@eliminarProducto');

Route::get('menu/menusv2', 'ProductosMenuController@goMenus');
Route::post('menu/menus/filtro', 'ProductosMenuController@goEditarMenuFiltro');
Route::get('menu/menus', 'ProductosMenuController@goEditarMenu');
Route::post('menu/menus/agregar', 'ProductosMenuController@agregarProductoAMenu');
Route::post('menu/menus/eliminar', 'ProductosMenuController@eliminarProductoAMenu');
Route::post('menu/menus/cambiarComanda', 'ProductosMenuController@cambiarComandera');

/*****************Materia Prima**************************** */
Route::get('materiaPrima/productos', 'MateriaPrimaController@goProductos');
Route::post('materiaPrima/producto/editar', 'MateriaPrimaController@goEditarProducto');
Route::post('materiaPrima/producto/guardar', 'MateriaPrimaController@guardarProducto');
Route::post('materiaPrima/producto/guardarAjax', 'MateriaPrimaController@guardarProductoAjax');
Route::post('materiaPrima/producto/cargarAjax', 'MateriaPrimaController@cargarProductoAjax');
Route::post('materiaPrima/producto/eliminar', 'MateriaPrimaController@eliminarProducto');
Route::post('materiaPrima/producto/eliminarAjax', 'MateriaPrimaController@eliminarProductoAjax');
Route::get('materiaPrima/inventario/inventarios', 'MateriaPrimaController@goInventarios');
Route::post('materiaPrima/inventario/inventarios/filtro', 'MateriaPrimaController@goInventariosFiltro');


/*****************Facturación**************************** */

Route::get('facturacion/ordenesAdmin', 'FacturacionController@goOrdenesAdmin');
Route::post('facturacion/filtrarOrdenesAdmin', 'FacturacionController@filtrarOrdenesAdmin');
Route::get('facturacion/ordenesEntrega', 'OrdenesListasController@goOrdenesEntrega');
Route::get('facturacion/ordenesPreparacion', 'OrdenesListasController@goOrdenesPreparacion');
Route::post('facturacion/ordenesPreparacion/terminarPreparacionOrden', 'OrdenesListasController@terminarPreparacionOrden');


Route::get('facturacion/pos/cargarOrdenGestion', 'FacturacionController@cargarOrdenGestion');
Route::post('facturacion/ordenesPreparacion/recargar', 'OrdenesListasController@recargarOrdenesPreparacion');
Route::post('facturacion/ordenesEntrega/recargar', 'OrdenesListasController@recargarOrdenesEntrega');
Route::post('facturacion/ordenesPreparacion/terminarEntregaOrden', 'OrdenesListasController@terminarEntregaOrden');
Route::post('facturacion/pos/recargarOrdenes', 'FacturacionController@recargarOrdenes');
Route::post('facturacion/pos/validarCodDescuento', 'FacturacionController@validarCodDescuento');

Route::post('facturacion/pos/anularOrden', 'FacturacionController@anularOrden');
Route::post('facturacion/factura', 'FacturacionController@goFactura');
Route::post('facturacion/mobiliario', 'FacturacionController@getMobiliarioDisponibleSalon');
Route::post('facturacion/dividirFactura', 'FacturacionController@dividirFactura');
Route::post('facturacion/pagar', 'FacturacionController@pagar');


/*****************Prodcutos Externos**************************** */
Route::get('productoExterno/productos', 'ProductosExternosController@goProductosExternos');
Route::get('productoExterno/nuevo', 'ProductosExternosController@goNuevoProducto');
Route::post('productoExterno/editar', 'ProductosExternosController@goEditarProducto');
Route::post('productoExterno/productos/filtro', 'ProductosExternosController@goProductosExternosFiltro');
Route::post('productoExterno/producto/guardar', 'ProductosExternosController@guardarProducto');
Route::get('productoExterno/inventario/inventarios', 'ProductosExternosController@goInventarios');
Route::post('productoExterno/inventario/inventarios/filtro', 'ProductosExternosController@goInventariosFiltro');
Route::get('productoExterno/productos/cargarMpProd', 'ProductosExternosController@cargarMpProd');
Route::post('productoExterno/productos/eliminarMpProd', 'ProductosExternosController@eliminarMpProd');
Route::post('productoExterno/productos/guardarMpProd', 'ProductosExternosController@guardarMpProd');



/*** Cocina */
Route::get('cocina/cocina/comandas', 'PedidoCocinaController@goComandaCocina');
Route::post('cocina/cocina/comandas/terminarPreparacionOrdenCocina', 'PedidoCocinaController@terminarPreparacionOrdenCocina');
Route::post('cocina/cocina/comandas/recargar', 'PedidoCocinaController@recargarOrdenesEsperaCocina');

/*** Bebidas */
Route::get('cocina/bebidas/comandas', 'PedidoBebidaController@goComandaBebida');
Route::post('cocina/bebidas/comandas/terminarPreparacionOrdenBebida', 'PedidoBebidaController@terminarPreparacionOrdenBebida');
Route::post('cocina/bebidas/comandas/recargar', 'PedidoBebidaController@recargarOrdenesEsperaBebida');

/*** Ordenes Listas */
Route::get('cocina/ordenesListas/comanda', 'OrdenesListasController@goOrdenesListasEntregar');
Route::post('cocina/ordenesListas/comanda/entregarOrdenComida', 'OrdenesListasController@entregarOrdenComida');
Route::post('cocina/ordenesListas/comanda/recargar', 'OrdenesListasController@recargarOrdenesListasEntregar');

/*** Ordenes Facturar */
Route::get('cocina/facturar/ordenes', 'OrdenesFacturarController@goOrdenesFacturar');
Route::get('cocina/ordenes/todo', 'OrdenesFacturarController@goOrdenesTodo');
Route::post('cocina/ordenes/todo/recargar', 'OrdenesFacturarController@recargarOrdenesTodo');
Route::post('cocina/ordenesListas/comanda/recargar', 'OrdenesFacturarController@recargarOrdenesListasEntregar');

Route::post('cocina/facturar/preFacturar', 'OrdenesFacturarController@preFacturarOrden');

Route::post('cocina/orden/detalleFacturacion', 'OrdenesFacturarController@getDetalleFacturacionOrden');
Route::post('cocina/orden/detalleFacturacionPorDetalles', 'OrdenesFacturarController@getDetalleFacturacionOrdenPorDetalles');

/*** Ordenes */
Route::post('cocina/facturar/ordenes/crearOrden', 'OrdenesController@procesarNuevaOrden');
Route::post('cocina/facturar/ordenes/actualizarOrden', 'OrdenesController@actualizarOrden');
Route::post('cocina/facturar/ordenes/facturarOrden', 'OrdenesFacturarController@facturarOrden');
Route::post('cocina/facturar/ordenes/prePagarOrden', 'OrdenesFacturarController@prePagarOrden');

/** Impresora */
Route::get('impresora/tiquete/{id_orden}', 'TicketesImpresosController@generarFacturacionOrdenPdf');
Route::get('impresora/tiquete/ruta/{id_orden}', 'TicketesImpresosController@generarFacturacionOrdenRutaPdf');
Route::get('impresora/tiquete/ruta/parcial/pago/{id_pago}', 'TicketesImpresosController@generarFacturaPagoParcialRutaPdf');

Route::get('impresora/tiquete/ruta/parcial/{id_orden}', 'TicketesImpresosController@generarFacturacionOrdenRutaPdf');
Route::get('impresora/pretiquete/{id_orden}', 'TicketesImpresosController@generarPreFacturacionOrdenPdf');

Route::post('comandaBar/recargar', 'ComandaBarController@recargar');

/*****************Grupos Promociones**************************** */
Route::get('mant/grupoPromocion', 'MantGrupoPromocionesController@goGruposPromociones');
Route::post('mant/grupoPromocion/filtro', 'MantGrupoPromocionesController@filtrarGruposPromociones');
Route::post('mant/grupoPromocion/guardarPromocion', 'MantGrupoPromocionesController@guardarPromocion');
Route::post('mant/grupoPromocion/guardarDetallePromocion', 'MantGrupoPromocionesController@guardarDetallePromocion');
Route::post('mant/grupoPromocion/eliminarDetallePromocion', 'MantGrupoPromocionesController@eliminarDetallePromocion');


/*****************Grupos Promociones**************************** */
Route::get('usuarioExterno/menu', 'UsuarioExternoController@goMenu');
Route::post('usuarioExterno/menu/cargarTiposGeneral', 'UsuarioExternoController@cargarTiposGeneral');
Route::post('usuarioExterno/menuMobile/cargarTiposGeneral', 'UsuarioExternoController@cargarTiposGeneralMobile');


/** PRODUCTOS MENU */
Route::post('productoMenu/producto/cargarProducto', 'ProductosMenuController@cargarProducto');
Route::post('menu/producto/guardar', 'ProductosMenuController@guardarProducto');
Route::get('menu', 'UsuarioExternoController@goMenuMobile');


/** CLIENTE*/
Route::get('cliente/registro', 'ClienteController@goRegistro');
Route::post('cliente/registrarse', 'ClienteController@registrarCliente');
Route::get('cliente/login', 'ClienteController@goLogin');
Route::get('cliente/login/recuperarPassword', 'ClienteController@goRecuperarPassword');
Route::post('cliente/login/solicitarNuevaPassword', 'ClienteController@solicitarNuevaPassword');
Route::post('cliente/verificaCta', 'ClienteController@verificarCuenta');
Route::post('cliente/login/ingresar', 'ClienteController@ingresar');


/** TOMA FISICA */
Route::get('materiaPrima/inventario/tomaFisica', 'TomaFisicaController@goCrearToma');
Route::post('materiaPrima/inventario/buscarMPTomaFisica', 'TomaFisicaController@buscarMPTomaFisica');
Route::post('materiaPrima/inventario/creaMPTomaFisica', 'TomaFisicaController@creaMPTomaFisica');

/* ENTREGAS */
Route::get('entregas/entregasPendientes', 'EntregasOrdenController@goOrdenesEntrega');
Route::post('entregas/filtrarOrdenesEntrega', 'EntregasOrdenController@filtrarOrdenesEntrega');
Route::post('entregas/iniciarRutaEntrega', 'EntregasOrdenController@iniciarRutaEntrega');
Route::post('entregas/entregarOrden', 'EntregasOrdenController@entregarOrden');

/* Tracking orden  */
Route::get('tracking/orden/{encryptedOrderId}', 'UsuarioExternoController@goTrackingOrden');
