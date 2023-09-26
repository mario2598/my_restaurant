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
//Mantenimientos
/*** Sucursales */
Route::get('mant/sucursales', 'MantenimientoSucursalController@index');
Route::post('guardarsucursal', 'MantenimientoSucursalController@guardarSucursal');
Route::post('eliminarsucursal', 'MantenimientoSucursalController@eliminarSucursal');

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

/*** tiposingreso */
Route::get('mant/tiposingreso', 'MantenimientoTiposIngresoController@index');
Route::post('guardartipoingreso', 'MantenimientoTiposIngresoController@guardarTipoIngreso');
Route::post('eliminartipoingreso', 'MantenimientoTiposIngresoController@eliminarTipoIngreso');

/*** parametros Generales */
Route::get('mant/parametrosgenerales', 'ParametrosGeneralesController@index');
Route::post('mant/guardarparametrosgenerales', 'ParametrosGeneralesController@guardar');

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

/*** Clientes */
Route::get('mant/clientes', 'MantenimientoClientesController@index');
Route::post('guardarcliente', 'MantenimientoClientesController@guardarCliente');
Route::post('eliminarcliente', 'MantenimientoClientesController@eliminarCliente');

/*** Usuarios */
Route::get('restaurar_pc', 'MantenimientoUsuariosController@restaurarPc');
Route::get('tema_claro', 'MantenimientoUsuariosController@temaClaro');
Route::get('tema_oscuro', 'MantenimientoUsuariosController@temaOscuro');
Route::post('side_teme', 'MantenimientoUsuariosController@sideTeme');
Route::post('color_teme', 'MantenimientoUsuariosController@colorTeme');
Route::post('sticky', 'MantenimientoUsuariosController@sticky');

Route::get('mant/usuarios', 'MantenimientoUsuariosController@index');
Route::get('usuario/nuevo', 'MantenimientoUsuariosController@goNuevoUsuario');
Route::post('/usuario/editar', 'MantenimientoUsuariosController@goEditarUsuario')->name('usuario/editar');
Route::post('usuario/guardarusuario', 'MantenimientoUsuariosController@guardarUsuario');
Route::post('usuario/editar/cambiarcontra', 'MantenimientoUsuariosController@cambiarContra');
Route::post('eliminarusuario', 'MantenimientoUsuariosController@eliminarUsuario');

/*** Mobiliario */
Route::get('mant/mobiliario', 'MantenimientoMobiliarioController@index');
Route::post('guardarmobiliario', 'MantenimientoMobiliarioController@guardarMobiliario');
Route::post('eliminarmobiliario', 'MantenimientoMobiliarioController@eliminarMobiliario');

//Login
Route::post('ingresar', 'LogInController@ingresar');

Route::get('inicio', function () {
    return view('inicio');
});

Route::get('usuario', function () {
    return view('mant.usuario');
});

Route::get('/', 'LogInController@index');
Route::get('login', 'LogInController@goLogIn');


Route::get('Autentificacion', function () {
    return view('login');
});

///**************Gastosss     */

Route::get('gastos/nuevo', 'GastosController@index');
Route::post('gastos/guardar', 'GastosController@guardarGasto');
Route::post('gastos/editar', 'GastosController@goEditarGasto');
Route::post('gastos/eliminar', 'GastosController@eliminarGasto');
Route::post('gastos/sinaprobar/eliminar', 'GastosController@eliminarGastoSinAprobar');
Route::post('gastos/rechazar', 'GastosController@rechazarGasto');
Route::post('confirmarGasto', 'GastosController@confirmarGasto');
Route::get('gastos/pendientes', 'GastosController@goGastosPendientes');
Route::post('gastos/administracion/filtro', 'GastosController@goGastosAdminFiltro');
Route::get('gastos/administracion', 'GastosController@goGastosAdmin');
Route::post('filtrarGastosPendientes', 'GastosController@filtrarGastosPendientes');
Route::post('filtrarGastosPendientes', 'GastosController@filtrarGastosPendientes');
Route::post('gastos/gasto', 'GastosController@goGasto');
Route::post('gasto/fotoBase64', 'GastosController@getFotoBase64');

///**************Ingresos     */
Route::get('ingresos/nuevo', 'IngresosController@index');
Route::post('ingresos/guardar', 'IngresosController@guardarIngreso');
Route::get('ingresos/administracion', 'IngresosController@goIngresosAdmin');
Route::post('ingresos/administracion/filtro', 'IngresosController@goIngresosAdminFiltro');
Route::post('ingresos/ingreso', 'IngresosController@goIngreso');
Route::post('ingresos/eliminar', 'IngresosController@eliminarIngreso');
Route::post('ingresos/aprobar', 'IngresosController@aprobarIngreso');
Route::post('ingresos/rechazar', 'IngresosController@rechazarIngreso');
Route::get('ingresos/pendientes', 'IngresosController@goIngresosPendientes');
Route::post('ingresos/gastos/rechazar', 'IngresosController@rechazarIngresoGasto');

///**************Caja     */

Route::get('caja/cierre', 'CajaController@goCierre');
Route::post('caja/cerrarcaja', 'CajaController@cerrarCaja');
Route::post('caja/cajaPrevia', 'CajaController@getCajaPrevia');
Route::post('caja/abrirCaja', 'CajaController@abrirCaja');

/******************Informes ********************** */
Route::post('informes/resumencontable/filtro', 'InformesController@goResumenContableFiltro');
Route::get('informes/resumencontable', 'InformesController@goResumenContable');

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
Route::post('menu/producto/guardar', 'ProductosMenuController@guardarProducto');
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

/*****************Materia Prima**************************** */
Route::get('materiaPrima/productos', 'MateriaPrimaController@goProductos');
Route::get('materiaPrima/productos/nuevo', 'MateriaPrimaController@goNuevoProducto');
Route::post('materiaPrima/producto/editar', 'MateriaPrimaController@goEditarProducto');
Route::post('materiaPrima/producto/guardar', 'MateriaPrimaController@guardarProducto');
Route::post('materiaPrima/producto/eliminar', 'MateriaPrimaController@eliminarProducto');
Route::get('materiaPrima/inventario/inventarios', 'MateriaPrimaController@goInventarios'); 
Route::post('materiaPrima/inventario/inventarios/filtro', 'MateriaPrimaController@goInventariosFiltro'); 
Route::post('materiaPrima/inventario/inventarios/guardar', 'MateriaPrimaController@guardarProductoSucursal'); 

/*****************Restaurante**************************** */
Route::get('restaurante/restaurantes', 'RestauranteController@goRestaurantes');
Route::get('restaurante/agregar', 'RestauranteController@goAgregarRestaurante');
Route::post('restaurante/editar', 'RestauranteController@goEditarRestaurante');
Route::post('restaurante/restaurante', 'RestauranteController@goRestaurante');
Route::post('restaurante/restaurante/guardar', 'RestauranteController@guardarRestaurante');
Route::post('restaurante/restaurante/salon/mobiliario/editar', 'RestauranteController@goEditarMobiliarioSalon');
Route::post('restaurante/restaurante/salon/mobiliario/guardar', 'RestauranteController@guardarMobiliarioSalon');
Route::post('restaurante/restaurante/salon/mobiliario/inactivar', 'RestauranteController@inactivarMobiliarioSalon');
Route::post('restaurante/restaurante/salon/mobiliario/eliminar', 'RestauranteController@eliminarMobiliarioSalon');
Route::post('restaurante/restaurante/salon/mobiliario/agregar', 'RestauranteController@goAgregarMobiliarioSalon');
Route::post('restaurante/restaurante/salon/mobiliario/asignar', 'RestauranteController@asignarMobiliarioSalon');
Route::get('restaurante/productos', 'RestauranteController@goProductosMenu');
Route::post('restaurante/productos/filtro', 'RestauranteController@goProductosMenuFiltro');
Route::get('restaurante/producto/nuevo', 'RestauranteController@goNuevoProducto');
Route::post('restaurante/producto/editar', 'RestauranteController@goEditarProducto');
Route::post('restaurante/producto/guardar', 'RestauranteController@guardarProducto');
Route::post('restaurante/producto/eliminar', 'RestauranteController@eliminarProducto');
Route::get('restaurante/menus', 'RestauranteController@goMenus');
Route::post('restaurante/menus/filtro', 'RestauranteController@goMenusFiltro');
Route::post('restaurante/menus/editar', 'RestauranteController@goEditarMenu');
Route::post('restaurante/menus/productos/agregar', 'RestauranteController@agregarProductoAMenu');
Route::post('restaurante/menus/productos/eliminar', 'RestauranteController@eliminarProductoAMenu');
/*****************Salon**************************** */
Route::post('restaurante/salon/guardar', 'RestauranteController@guardarSalon');
Route::post('restaurante/salon/eliminar', 'RestauranteController@eliminarSalon');

/*****************Facturación**************************** */
Route::get('facturacion/pos', 'FacturacionController@goPos');
Route::get('facturacion/ordenesEntrega', 'OrdenesListasController@goOrdenesEntrega');
Route::get('facturacion/ordenesPreparacion', 'OrdenesListasController@goOrdenesPreparacion');
Route::post('facturacion/ordenesPreparacion/terminarPreparacionOrden', 'OrdenesListasController@terminarPreparacionOrden');

Route::post('facturacion/ordenesPreparacion/recargar', 'OrdenesListasController@recargarOrdenesPreparacion');
Route::post('facturacion/ordenesEntrega/recargar', 'OrdenesListasController@recargarOrdenesEntrega'); 
Route::post('facturacion/ordenesPreparacion/terminarEntregaOrden', 'OrdenesListasController@terminarEntregaOrden');
Route::post('facturacion/pos/recargarOrdenes', 'FacturacionController@recargarOrdenes');
Route::post('facturacion/pos/validarCodDescuento', 'FacturacionController@validarCodDescuento');
Route::post('facturacion/pos/crearFactura', 'FacturacionController@crearFactura');
Route::post('facturacion/pos/anularOrden', 'FacturacionController@anularOrden');
Route::post('facturacion/factura', 'FacturacionController@goFactura');
Route::post('facturacion/mobiliario', 'FacturacionController@getMobiliarioDisponibleSalon');
Route::post('facturacion/dividirFactura', 'FacturacionController@dividirFactura');
Route::post('facturacion/pagar', 'FacturacionController@pagar');

/*****************Facturación RUTA**************************** */
Route::get('facturacion/facturarRuta', 'FacturacionRutaController@index');
Route::get('facturas/parciales', 'FacturacionRutaController@goFacturasParciales');
Route::post('facturas/parciales/filtro', 'FacturacionRutaController@goFacturasParcialesFiltro');
Route::post('facturas/parciales/cargarPagos', 'FacturacionRutaController@cargarPagos');
Route::post('facturas/parciales/crearPago', 'FacturacionRutaController@crearPago');

/*****************Prodcutos Externos**************************** */
Route::get('productoExterno/productos', 'ProductosExternosController@goProductosExternos');
Route::get('productoExterno/nuevo', 'ProductosExternosController@goNuevoProducto');
Route::post('productoExterno/editar', 'ProductosExternosController@goEditarProducto');
Route::post('productoExterno/productos/filtro', 'ProductosExternosController@goProductosExternosFiltro');
Route::post('productoExterno/producto/guardar', 'ProductosExternosController@guardarProducto');
Route::get('productoExterno/inventario/inventarios', 'ProductosExternosController@goInventarios'); 
Route::post('productoExterno/inventario/inventarios/filtro', 'ProductosExternosController@goInventariosFiltro'); 

Route::post('productoExterno/inventario/inventarios/guardar', 'ProductosExternosController@guardarProductoSucursal'); 

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
Route::get('impresora/tiquete/{id_orden}','TicketesImpresosController@generarFacturacionOrdenPdf');
Route::get('impresora/tiquete/ruta/{id_orden}','TicketesImpresosController@generarFacturacionOrdenRutaPdf');
Route::get('impresora/tiquete/ruta/parcial/pago/{id_pago}','TicketesImpresosController@generarFacturaPagoParcialRutaPdf');

Route::get('impresora/tiquete/ruta/parcial/{id_orden}','TicketesImpresosController@generarFacturacionOrdenRutaPdf');
Route::get('impresora/pretiquete/{id_orden}','TicketesImpresosController@generarPreFacturacionOrdenPdf');

Route::post('comandaBar/recargar', 'ComandaBarController@recargar');