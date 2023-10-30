<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MovimientoController;
use App\Traits\SpaceUtil;
use Mockery\Mock;

class PedidoSucursalController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "invSucPedido";
    public function __construct()
    {

        setlocale(LC_ALL, "es_CR");
    }

    public function index()
    {
    }

    public function goPedidoId($id)
    {
        if ($id < 1 || $id == null) {
            $this->setError('Editar Pedido.', 'Identificador del pedido incorrecto.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        }


        $pedido = DB::table('pedido')
            ->leftjoin('usuario', 'usuario.id', '=', 'pedido.receptor')
            ->select('pedido.*', 'usuario.usuario as usuarioReceptor')
            ->where('pedido.id', '=', $id)
            ->get()->first();

        $detalles_pedido = DB::table('detalle_pedido')
            ->where('pedido', '=', $id)
            ->get();

        if ($pedido == null) {
            $this->setError('Editar Pedido.', 'No existe el pedido a editar.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        }

        if ($pedido->estado != "P" && $pedido->estado != "A") {
            $this->setError('Editar Pedido.', 'El pedido ya fue procesado.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        }
        $productos = $this->getProductos();

        foreach ($productos as $p) {
            $p->cantidad = 0;
            foreach ($detalles_pedido as $d) {
                if ($d->producto == $p->id) {
                    $p->cantidad = $d->cantidad;
                }
            }
        }
        $data = [
            'menus' => $this->cargarMenus(),
            'productos' => $productos,
            'pedido' => $pedido,
            'sucursales' => $this->getBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('inventario.pedidoInventartioSucursal', compact('data'));
    }

    public function goEditarPedidoInventarioSucursal(Request $request)
    {
        if (!$this->validarSesion("invSucPedido")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $id = $request->input('idPedidoEditar');
        return $this->goPedidoId($id);
    }

    public function goPedidoInventarioSucursal()
    {
        if (!$this->validarSesion("invSucPedido")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $productos = $this->getProductos();
        $data = [
            'menus' => $this->cargarMenus(),
            'productos' => $productos,
            'pedido' => [],
            'sucursales' => $this->getBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('inventario.pedidoInventartioSucursal', compact('data'));
    }

    public function goPedidosInventarioPendientes()
    {
        if (!$this->validarSesion("invSucPedPend")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $pedido_pendientes = DB::table('pedido')->where('sucursal', '=', $this->getUsuarioSucursal())
            ->where(function ($query) {
                $query->where('estado', 'like', 'P')
                    ->orWhere('estado', 'like', 'A');
            })->get();

        foreach ($pedido_pendientes as $p) {
            $p->fecha = $this->fechaFormat($p->fecha);
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'pedidos_pendientes' => $pedido_pendientes,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('inventario.pedidosPendientesInventarioSucursal', compact('data'));
    }

    public function goPedidosBodegaPendientes()
    {
        if (!$this->validarSesion("bodInvPed")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $pedido_pendientes = DB::table('pedido')
            ->join('usuario', 'usuario.id', '=', 'pedido.emisor')
            ->join('sucursal', 'sucursal.id', '=', 'pedido.sucursal')
            ->select('pedido.*', 'usuario.usuario as emisorNombre', 'sucursal.descripcion as sucursalNombre')
            ->where('pedido.estado', 'like', 'P')->get();

        foreach ($pedido_pendientes as $p) {
            $p->fecha = $this->fechaFormat($p->fecha);
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'pedidos_pendientes' => $pedido_pendientes,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('bodega.inventario.pedidosPendientes', compact('data'));
    }

    public function crearPedido(Request $request)
    {

        if (!$this->validarSesion("invSucPedido")) {
            return $this->responseAjaxServerError("Permisos insuficientes.");
        }

        $id = $request->input("id");
        $receptor = $request->input("receptor");
        $movimiento_asociado = $request->input("movimiento_asociado");
        $estado = $request->input("estado");
        $pedido = $request->input("pedido");
        $bodega = $request->input("bodega");
        $detalle = $request->input("det");

        if (count($pedido) < 1 || $pedido == null) {
            return $this->responseAjaxServerError("Pedido vacío.");
        }

        if ($bodega < 1 || $bodega == null) {
            return $this->responseAjaxServerError("Bodega de solicitud incorrecta.");
        }

        if (strlen($detalle) > 200) {
            return $this->responseAjaxServerError("Tamaño máximo del detalle 200 caracteres.");
        }

        if ($id < 1 || $id == null) {
            $actualizar = false;
        } else {
            $actualizar = true;
        }
        $fecha_actual = date("Y-m-d H:i:s");
        $detalle = ($detalle ?? $this->fechaFormat($fecha_actual) . ' - Realizado por ' . $this->getUsuarioAuth()['usuario']);
        try {
            DB::beginTransaction();

            if ($actualizar) {
                // Estados P = Pendiente, C = Cancelado, T = Terminado , A = aceptado, E = eliminado
                DB::table('pedido')->where('pedido.id', '=', $id)->update([
                    'receptor' => $receptor ?? null,
                    'ultima_modificacion' => $fecha_actual,  'movimiento_asociado' => $movimiento_asociado ?? null, 'detalle' => $detalle
                ]);
                DB::table('detalle_pedido')->where('pedido', '=', $id)->delete();

                foreach ($pedido as $d) {
                    if ($d['cantidad'] > 0) {
                        DB::table('detalle_pedido')->insertGetId([
                            'id' => null, 'producto' => $d['id'],
                            'cantidad' => $d['cantidad'], 'pedido' => $id, 'bodega' => $bodega,
                        ]);
                    }
                }
            } else {

                // Estados P = Pendiente, C = Cancelado, T = Terminado , A = aceptado, E = eliminado
                $id_pedido = DB::table('pedido')->insertGetId([
                    'id' => null, 'emisor' => $this->getUsuarioAuth()['id'],
                    'receptor' => null, 'sucursal' => $this->getUsuarioSucursal(), 'bodega' => $bodega, 'fecha' => $fecha_actual,
                    'ultima_modificacion' => $fecha_actual, 'estado' => "P", 'movimiento_asociado' => null, 'detalle' => $detalle
                ]);

                foreach ($pedido as $d) {
                    if ($d['cantidad'] > 0) {
                        DB::table('detalle_pedido')->insertGetId([
                            'id' => null, 'producto' => $d['id'],
                            'cantidad' => $d['cantidad'], 'pedido' => $id_pedido
                        ]);
                    }
                }
            }
            DB::commit();

            return $this->responseAjaxSuccess("Pedido guardado correctamente.", $id_pedido ?? $id);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

    public function eliminarPedido(Request $request)
    {

        if (!$this->validarSesion("invSucPedPend")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input("idPedido");

        if ($id < 1 || $id == null) {
            $this->setError('Eliminar Pedido.', 'Identificador del pedido incorrecto.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        }


        $pedido = DB::table('pedido')
            ->where('id', '=', $id)
            ->get()->first();
        if ($pedido == null) {
            $this->setError('Eliminar Pedido.', 'No existe el pedido a eliminar.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        }

        if ($pedido->estado != "P") {
            $this->setError('Eliminar Pedido.', 'El pedido ya fue procesado.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        }

        try {
            DB::beginTransaction();

            DB::table('detalle_pedido')->where('pedido', '=', $id)->delete();
            DB::table('pedido')->where('id', '=', $id)->delete();

            DB::commit();

            $this->setSuccess('Eliminar Pedido.', 'Pedido eliminado correctamente.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Eliminar Pedido.', 'Algo salío mal.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        }
    }


    /* Bodega */

    public function goPedidoBodegaId($id)
    {
        if ($id < 1 || $id == null) {
            $this->setError('Pedido Pendiente.', 'Identificador del pedido incorrecto.');
            return redirect('bodega/inventario/pedidos');
        }


        $pedido = DB::table('pedido')
            ->join('usuario', 'usuario.id', '=', 'pedido.emisor')
            ->join('sucursal as bodega', 'bodega.id', '=', 'pedido.bodega')
            ->join('sucursal as suc', 'suc.id', '=', 'pedido.sucursal')
            ->select(
                'pedido.*',
                'usuario.usuario as usuarioEmisor',
                'bodega.descripcion as bodegaNombre',
                'bodega.id as bodegaId',
                'suc.descripcion as sucursalNombre'
            )
            ->where('pedido.id', '=', $id)
            ->get()->first();

        $detalles_pedido = DB::table('detalle_pedido')
            ->join('producto', 'producto.id', '=', 'detalle_pedido.producto')
            ->join('categoria', 'categoria.id', '=', 'producto.categoria')
            ->select('detalle_pedido.*', 'producto.nombre', 'producto.codigo_barra', 'categoria.categoria as nombre_categoria')
            ->where('detalle_pedido.pedido', '=', $id)
            ->get();

        if ($pedido == null) {
            $this->setError('Editar Pedido.', 'No existe el pedido a editar.');
            return redirect('inventario/sucursal/pedidos/pendientes');
        }
        $pedido->fecha = $this->fechaFormat($pedido->fecha);

        $data = [
            'menus' => $this->cargarMenus(),
            'detalles_pedido' => $detalles_pedido,
            'pedido' => $pedido,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('bodega.inventario.pedidoPendiente', compact('data'));
    }

    public function goPedidoBodega(Request $request)
    {
        if (!$this->validarSesion("bodInvPed")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $id = $request->input('idPedidoBodega');
        return $this->goPedidoBodegaId($id);
    }

    public function procesarPedidoBodega(Request $request)
    {
        if (!$this->validarSesion("bodInvPed")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $id = $request->input('idPedidoBodega');

        if ($id < 1 || $id == null) {
            $this->setError('Procesar Pedido.', 'Identificador del pedido incorrecto.');
            return redirect('bodega/inventario/pedidos');
        }
        $pedido = DB::table('pedido')
            ->where('pedido.id', '=', $id)
            ->get()->first();

        if ($pedido == null) {
            $this->setError('Procesar Pedido.', 'No existe el pedido a procesar.');
            return redirect('bodega/inventario/pedidos');
        }

        if ($pedido->estado != "P") {
            $this->setError('Procesar Pedido.', 'El pedido ya fue procesado.');
            return redirect('bodega/inventario/pedidos');
        }

        $detalles_pedido = DB::table('detalle_pedido')
            ->where('detalle_pedido.pedido', '=', $id)
            ->get();

        if ($detalles_pedido == null || count($detalles_pedido) < 1) {
            $this->setError('Procesar Pedido.', 'Detalle de pedido vacío.');
            return redirect('bodega/inventario/pedidos');
        }

        try {
            DB::beginTransaction();

            $fecha_actual = date("Y-m-d H:i:s");
            $tipo_mov = DB::table('tipo_movimiento')->where('codigo', '=', 'STI')->get()->first();

            $mov_id = DB::table('movimiento')->insertGetId([
                'id' => null, 'tipo_movimiento' => $tipo_mov->id,
                'sucursal_inicio' => $pedido->bodega, 'sucursal_fin' => $pedido->sucursal, 'entrega' => $this->getUsuarioAuth()['id'], 'recibe' => null, 'fecha' => $fecha_actual, 'fecha_entrega' => null, 'estado' => 'P'
            ]);

            $texto = '';
            foreach ($detalles_pedido as $d) {
                $lotesAux = $this->getLotes($pedido->bodega, $d->producto);
                $cantidadDetalle = $d->cantidad;
                foreach ($lotesAux as $l) {
                    $cantidadAux = 0;
                    if ($cantidadDetalle > 0) {
                        if ($l->cantidad == $cantidadDetalle) {
                            $cantidadDetalle = $l->cantidad - $cantidadDetalle;
                            DB::table('inventario')
                                ->where('id', '=', $l->id)->delete();

                            $det_id = DB::table('detalle_movimiento')->insertGetId([
                                'id' => null, 'producto' => $l->producto,
                                'cantidad' => $l->cantidad, 'lote' => $l->lote, 'movimiento' => $mov_id
                            ]);
                        }
                        if ($l->cantidad > $cantidadDetalle) {
                            $cantidadAux = $l->cantidad - $cantidadDetalle;
                            DB::table('inventario')
                                ->where('id', '=', $l->id)
                                ->update(['cantidad' => $cantidadAux]);

                            $det_id = DB::table('detalle_movimiento')->insertGetId([
                                'id' => null, 'producto' => $l->producto,
                                'cantidad' => $cantidadDetalle, 'lote' => $l->lote, 'movimiento' => $mov_id
                            ]);
                            $cantidadDetalle = 0;
                        }
                        if ($l->cantidad < $cantidadDetalle) {
                            $cantidadDetalle =  $cantidadDetalle - $l->cantidad;
                            DB::table('inventario')
                                ->where('id', '=', $l->id)->delete();

                            $det_id = DB::table('detalle_movimiento')->insertGetId([
                                'id' => null, 'producto' => $l->producto,
                                'cantidad' => $l->cantidad, 'lote' => $l->lote, 'movimiento' => $mov_id
                            ]);
                        }
                    }
                }
            }
           $mov_controller = new MovimientoController();
            DB::table('pedido')
                ->where('id', '=', $id)
                ->update(['estado' => "A", 'movimiento_asociado' => $mov_id]);
            DB::commit();
            $this->setSuccess('Procesar Pedido.', 'Pedido procesado correctamente.');
            return $mov_controller->goMovimientoId($mov_id);
        } catch (QueryException $ex) {
            DB::rollBack();
            $this->setError('Procesar Pedido.', 'Algo salío mal.');
            return redirect('/');
        }
    }

    private function getLotes($sucursal, $producto)
    {

        $lotes = DB::table('inventario')
            ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'inventario.sucursal')
            ->leftjoin('lote', 'lote.id', '=', 'inventario.lote')
            ->select('inventario.*', 'lote.fecha_vencimiento')
            ->where('inventario.sucursal', '=', $sucursal)
            ->where('producto.id', '=', $producto)
            ->orderBy('lote.fecha_vencimiento', 'asc')
            ->get();

        return $lotes;
    }
}
