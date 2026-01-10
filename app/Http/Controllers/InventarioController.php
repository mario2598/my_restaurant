<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;

class InventarioController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "inventarios";
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

    public function cargarMovimientosPendientesSucursal()
    {

        $sucursal = $this->getSucursalUsuarioAuth();

        $movimientos = DB::table('movimiento')
            ->leftjoin('tipo_movimiento', 'tipo_movimiento.id', '=', 'movimiento.tipo_movimiento')
            ->leftjoin('sucursal as despacho', 'despacho.id', '=', 'movimiento.sucursal_inicio')
            ->leftjoin('sucursal as destino', 'destino.id', '=', 'movimiento.sucursal_fin')
            ->leftjoin('usuario as entrega', 'entrega.id', '=', 'movimiento.entrega')
            ->select(
                'movimiento.estado',
                'movimiento.id',
                'movimiento.fecha',
                'tipo_movimiento.codigo as codigo_movimiento',
                'tipo_movimiento.descripcion as descripcion_movimiento',
                'despacho.descripcion as despacho',
                'destino.descripcion as detino',
                'entrega.usuario as nombre_usuario'
            )->where('movimiento.sucursal_fin', '=', $sucursal)
            ->where('movimiento.estado', '=', 'P')->orderBy('movimiento.id', 'DESC')->get();

        foreach ($movimientos as $m) {
            $m->fecha = $this->fechaFormat($m->fecha);
        }

        $data = [
            'movimientos' => $movimientos
        ];

        return view('inventario.layout.tbodyMovPendSucursal', compact('data'));
    }

    public function goDevolucionInventarioSucursal()
    {
        if (!$this->validarSesion('invSucDevolucion')) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $sucursalAuth = $this->getSucursalUsuarioAuth();
        $data = [
            'panel_configuraciones' => $this->getPanelConfiguraciones(),
            'inventario_por_lote' => $this->getInventarioPorLote($sucursalAuth),
            'sucursalAuth' => $sucursalAuth,
            'sucursales' => $this->getBodegas(),
            'parametros_generales' => $this->getParametrosGenerales()
        ];

        return view('inventario.devolucionInventartioSucursal', compact('data'));
    }

    public function goTrasladoInventarioSucursal()
    {
        if (!$this->validarSesion('invSucTraslado')) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $sucursalAuth = $this->getSucursalUsuarioAuth();
        $data = [
            'panel_configuraciones' => $this->getPanelConfiguraciones(),
            'inventario_por_lote' => $this->getInventarioPorLote($sucursalAuth),
            'sucursalAuth' => $sucursalAuth,
            'sucursales' => $this->getSucursalesAndBodegas(),
            'parametros_generales' => $this->getParametrosGenerales()
        ];

        return view('inventario.trasladoInventartioSucursal', compact('data'));
    }

    public function goMovimientosPendientesSucursal()
    {
        if (!$this->validarSesion('invMovPends')) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'panel_configuraciones' => $this->getPanelConfiguraciones(),
            'movimientos' => [],
            'parametros_generales' => $this->getParametrosGenerales()
        ];

        return view('inventario.movimientosPendientesSucursal', compact('data'));
    }

    public function goMovimientoPendienteSucursal(Request $request)
    {
        if (!$this->validarSesion(array("invMovPends"))) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idMov');
        if ($id == null || $id < 1) {
            $this->setError("Cargar Movimiento", "El movimiento no existe!");
            return redirect('/');
        }

        $movimiento = DB::table('movimiento')
            ->leftjoin('tipo_movimiento', 'tipo_movimiento.id', '=', 'movimiento.tipo_movimiento')
            ->leftjoin('sucursal as despacho', 'despacho.id', '=', 'movimiento.sucursal_inicio')
            ->leftjoin('sucursal as destino', 'destino.id', '=', 'movimiento.sucursal_fin')
            ->leftjoin('usuario as entrega', 'entrega.id', '=', 'movimiento.entrega')
            ->leftjoin('usuario as recibe', 'recibe.id', '=', 'movimiento.recibe')
            ->select(
                'movimiento.id',
                'movimiento.detalle',
                'movimiento.fecha',
                'movimiento.fecha_entrega',
                'despacho.id as despacho_id',
                'despacho.descripcion as despacho_descripcion',
                'destino.id as destino_id',
                'destino.descripcion as destino_descripcion',
                'entrega.id as entrega_id',
                'entrega.usuario as entrega_usuario',
                'recibe.id as recibe_id',
                'recibe.usuario as recibe_usuario',
                'movimiento.estado',
                'tipo_movimiento.codigo as tipo_movimiento_codigo',
                'tipo_movimiento.descripcion as tipo_movimiento_descripcion'
            )
            ->where('movimiento.id', '=', $id)
            ->get()->first();

        $movimiento->fecha = $this->fechaFormat($movimiento->fecha);
        if ($movimiento->fecha_entrega != null) {
            $movimiento->fecha_entrega = $this->fechaFormat($movimiento->fecha_entrega);
        }
        $movimiento->detalles =  DB::table('detalle_movimiento')
            ->leftjoin('lote', 'lote.id', '=', 'detalle_movimiento.lote')
            ->leftjoin('producto', 'producto.id', '=', 'detalle_movimiento.producto')
            ->select(
                'detalle_movimiento.cantidad',
                'detalle_movimiento.id',
                'producto.nombre as producto_nombre',
                'lote.codigo as lote_codigo',
                'lote.id as lote_id'
            )->where('detalle_movimiento.movimiento', '=', $id)
            ->get();

        $data = [
            'movimiento' => $movimiento,
            'panel_configuraciones' => $this->getPanelConfiguraciones()

        ];
        if ($movimiento->estado == 'P' &&  $movimiento->tipo_movimiento_codigo == "SDI") {
            return view('inventario.movimientoPendienteSucursalDev', compact('data'));
        } else {
            return view('inventario.movimientoPendienteSucursal', compact('data'));
        }
    }

    public function aceptarMovimientoSucursal(Request $request)
    {
        if (!$this->validarSesion("invMovPends")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('idMovSuc');
        $detalle = $request->input('detMovSuc');

        if ($id == null || $id < 1) {
            $this->setError("Aceptar Movimiento", "El movimiento no existe!");
            return redirect('/');
        }

        $movimiento = DB::table('movimiento')
            ->where('movimiento.id', '=', $id)
            ->get()->first();

        if ($movimiento == null) {
            $this->setError("Aceptar Movimiento", "El movimiento no existe!");
            return redirect('/');
        }
        $transaccionRealizada = true;

        $detalle = ($detalle ?? '') . ' - ' . $this->fechaFormat($movimiento->fecha) . ' - Aceptado por ' . $this->getUsuarioAuth()['usuario'];

        try {
            DB::beginTransaction();
            $fecha_actual = date("Y-m-d H:i:s");


            DB::table('movimiento')
                ->where('id', '=', $id)
                ->update(['estado' => 'T', 'fecha_entrega' => $fecha_actual, 'recibe' => $this->getUsuarioAuth()['id']]);

            $tipo_mov = DB::table('tipo_movimiento')->where('codigo', '=', 'ETI')->get()->first();

            $mov_id = DB::table('movimiento')->insertGetId([
                'id' => null, 'tipo_movimiento' => $tipo_mov->id,
                'sucursal_inicio' => $movimiento->sucursal_inicio, 'sucursal_fin' => $movimiento->sucursal_fin,
                'entrega' => $movimiento->entrega, 'recibe' => $this->getUsuarioAuth()['id'], 'fecha' => $movimiento->fecha,
                'fecha_entrega' => $fecha_actual, 'estado' => 'T', 'detalle' => $detalle
            ]);

            $detalles =  DB::table('detalle_movimiento')
                ->leftjoin('lote', 'lote.id', '=', 'detalle_movimiento.lote')
                ->leftjoin('producto', 'producto.id', '=', 'detalle_movimiento.producto')
                ->select('detalle_movimiento.*')->where('detalle_movimiento.movimiento', '=', $id)
                ->get();

            foreach ($detalles as $d) {
                $det = DB::table('detalle_movimiento')->insertGetId([
                    'id' => null, 'producto' => $d->producto,
                    'cantidad' => $d->cantidad, 'lote' => $d->lote, 'movimiento' => $mov_id
                ]);
            }

            DB::commit();
        } catch (QueryException $ex) {
            DB::rollBack();
            $transaccionRealizada = false;
        }
        if (!$transaccionRealizada) {
            $this->setError('Aceptar Movimiento', 'Algo salio mal...');
            return redirect('/');
        } else {
            try {
                DB::beginTransaction();
                foreach ($detalles as $d) {

                    $inventarioExitente = DB::table('inventario')
                        ->select('inventario.*')
                        ->where('inventario.sucursal', '=', $movimiento->sucursal_fin)
                        ->where('inventario.lote', '=', $d->lote)
                        ->get()->first();

                    if ($inventarioExitente != null) {
                        $cantidadAux = $inventarioExitente->cantidad + $d->cantidad;
                        DB::table('inventario')
                            ->where('id', '=', $inventarioExitente->id)
                            ->update(['cantidad' => $cantidadAux]);
                    } else {
                        $idInv = DB::table('inventario')->insertGetId([
                            'id' => null,
                            'sucursal' => $movimiento->sucursal_fin,
                            'producto' => $d->producto, 'lote' => $d->lote,
                            'cantidad' => $d->cantidad
                        ]);
                    }
                }
                DB::commit();
                $this->setInfo("Aceptar Movimiento", "Se acepto correctamente!");
                return redirect('/');
            } catch (QueryException $ex) {
                DB::rollBack();
                DB::table('movimiento')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'E', 'detalle' => $detalle . '.ERROR : El inventario quedo atrapado en el traslado.']);
                $this->setError('Aceptar Movimiento', 'Algo salio mal...');
                return redirect('/');
            }
        }
    }

    private function getInventarioPorLote($sucursal)
    {

        if ($sucursal == null || $sucursal < 1) {
            return [];
        }

        $inventarios = DB::table('inventario')
            ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'inventario.sucursal')
            ->leftjoin('lote', 'lote.id', '=', 'inventario.lote')
            ->select('lote.codigo', 'lote.codigo', 'inventario.sucursal', 'inventario.id', 'producto.nombre', 'inventario.cantidad')
            ->orderBy('producto.nombre')
            ->where('inventario.sucursal', '=', $sucursal)
            ->where('inventario.cantidad', '>', 0)
            ->get();

        return $inventarios;
    }

    /**
     * Este proceso recibe una lista de la devolucion a realizar y un detalle de devolución
     */
    public function iniciarDevolucion(Request $request)
    {
        $devoluciones = $request->input('devoluciones');
        $despacho = $this->getSucursalUsuarioAuth();
        $destino = $request->input('destino');
        $detalle = $request->input('det');
        $cantidadAux = 0;
        foreach ($devoluciones as $p) {
            if ($p['cantidad'] > 0) {
                $cantidadAux++;
            }
        }
        if ($cantidadAux < 1) {
            echo 'error';
            exit;
        }
        try {

            DB::beginTransaction();

            $fecha_actual = date("Y-m-d H:i:s");
            $tipo_mov = DB::table('tipo_movimiento')->where('codigo', '=', 'SDI')->get()->first();

            $mov_id = DB::table('movimiento')->insertGetId([
                'id' => null, 'tipo_movimiento' => $tipo_mov->id,
                'sucursal_inicio' => $despacho, 'sucursal_fin' => $destino, 'entrega' => $this->getUsuarioAuth()['id'], 'recibe' => null, 'fecha' => $fecha_actual, 'fecha_entrega' => null, 'estado' => 'P', 'detalle' => $detalle
            ]);
            DB::commit();
            DB::beginTransaction();
            foreach ($devoluciones as $p) {
                $cantidad = $p['cantidad'];

                if ($cantidad > 1) {
                    $inventario = DB::table('inventario')->where('id', '=', $p['id'])->get()->first();
                    $inventarioRestante = $inventario->cantidad - $cantidad;
                    if ($cantidad > $inventario->cantidad) {
                        DB::table('inventario')
                            ->where('id', '=', $inventario->id)->delete();
                        DB::rollBack();
                        echo 'error';
                        exit;
                    } else {
                        if ($inventarioRestante < 1) {
                            DB::table('inventario')
                                ->where('id', '=', $inventario->id)->delete();
                        } else {
                            DB::table('inventario')
                                ->where('id', '=', $inventario->id)->update(['cantidad' => $inventarioRestante]);
                        }
                        $id = DB::table('detalle_movimiento')->insertGetId([
                            'id' => null, 'producto' => $inventario->producto,
                            'cantidad' => $p['cantidad'], 'lote' => $inventario->lote, 'movimiento' => $mov_id
                        ]);
                    }
                }
            }

            DB::commit();
            return $mov_id;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 'error';
        }
    }

    public function iniciarTrasladoSucursal(Request $request)
    {
        $traslados = $request->input('traslados');
        $despacho = $this->getSucursalUsuarioAuth();
        $destino = $request->input('destino');
        $detalle = $request->input('det');
        $cantidadAux = 0;
        foreach ($traslados as $p) {
            if ($p['cantidad'] > 0) {
                $cantidadAux++;
            }
        }
        if ($cantidadAux < 1) {
            echo 'error';
            exit;
        }
        try {

            DB::beginTransaction();

            $fecha_actual = date("Y-m-d H:i:s");
            $tipo_mov = DB::table('tipo_movimiento')->where('codigo', '=', 'STI')->get()->first();

            $mov_id = DB::table('movimiento')->insertGetId([
                'id' => null, 'tipo_movimiento' => $tipo_mov->id,
                'sucursal_inicio' => $despacho, 'sucursal_fin' => $destino, 'entrega' => $this->getUsuarioAuth()['id'], 'recibe' => null, 'fecha' => $fecha_actual, 'fecha_entrega' => null, 'estado' => 'P', 'detalle' => $detalle
            ]);
            DB::commit();
            DB::beginTransaction();
            foreach ($traslados as $p) {
                $cantidad = $p['cantidad'];

                if ($cantidad > 1) {
                    $inventario = DB::table('inventario')->where('id', '=', $p['id'])->get()->first();
                    $inventarioRestante = $inventario->cantidad - $cantidad;
                    if ($cantidad > $inventario->cantidad) {
                        DB::table('inventario')
                            ->where('id', '=', $inventario->id)->delete();
                        DB::rollBack();
                        echo 'error';
                        exit;
                    } else {
                        if ($inventarioRestante < 1) {
                            DB::table('inventario')
                                ->where('id', '=', $inventario->id)->delete();
                        } else {
                            DB::table('inventario')
                                ->where('id', '=', $inventario->id)->update(['cantidad' => $inventarioRestante]);
                        }
                        $id = DB::table('detalle_movimiento')->insertGetId([
                            'id' => null, 'producto' => $inventario->producto,
                            'cantidad' => $p['cantidad'], 'lote' => $inventario->lote, 'movimiento' => $mov_id
                        ]);
                    }
                }
            }

            DB::commit();
            return $mov_id;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 'error';
        }
    }

    public function aceptarDevolucionSucursal(Request $request)
    {
        if (!$this->validarSesion("invMovPends")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $id = $request->input('mov_id');
        $detalle = $request->input('det');
        $desechos = $request->input('des');
        $devoluciones = $request->input('inv');
        $hayDesechos = false;
        $hayDevoluciones = false;

        foreach ($desechos as $d) {
            if ($d['cantidad'] > 0) {
                $hayDesechos = true;
            }
        }

        foreach ($devoluciones as $d) {
            if ($d['cantidad'] > 0) {
                $hayDevoluciones = true;
            }
        }

        if (!$hayDesechos && !$hayDevoluciones) {
            $this->setError("Aceptar Devolución", "No hay productos a gestionar!");
            return redirect('/');
        }

        if ($id == null || $id < 1) {
            $this->setError("Aceptar Movimiento", "El movimiento no existe!");
            return redirect('/');
        }

        $movimiento = DB::table('movimiento')
            ->where('movimiento.id', '=', $id)
            ->get()->first();

        if ($movimiento == null) {
            $this->setError("Aceptar Movimiento", "El movimiento no existe!");
            return redirect('/');
        }
        $transaccionRealizada = true;
        $sucursalActual = $this->getSucursalUsuarioAuth();

        $detalle = ($detalle ?? '') . ' - ' . $this->fechaFormat($movimiento->fecha) . ' - Aceptado por ' . $this->getUsuarioAuth()['usuario'];

        try {
            DB::beginTransaction();
            $fecha_actual = date("Y-m-d H:i:s");

            if ($hayDesechos) {
                $tipo_mov_des = DB::table('tipo_movimiento')->where('codigo', '=', 'SDD')->get()->first();
                $mov_des_id = DB::table('movimiento')->insertGetId([
                    'id' => null, 'tipo_movimiento' => $tipo_mov_des->id,
                    'sucursal_inicio' => $sucursalActual, 'sucursal_fin' => null,
                    'entrega' => $this->getUsuarioAuth()['id'], 'recibe' => null, 'fecha' => $movimiento->fecha,
                    'fecha_entrega' => $fecha_actual, 'estado' => 'T', 'detalle' => $detalle
                ]);

                foreach ($desechos as $d) {
                    if ($d['cantidad'] > 0) {
                        $det = DB::table('detalle_movimiento')->where('id', '=', $d['id'])->get()->first();
                        DB::table('detalle_movimiento')->insertGetId([
                            'id' => null, 'producto' => $det->producto,
                            'cantidad' => $d['cantidad'], 'lote' => $det->lote, 'movimiento' => $mov_des_id
                        ]);
                    }
                }
            }

            if ($hayDevoluciones) {
                $detalle = ($movimiento->detalle ?? '') . ' - ' . $this->fechaFormat($movimiento->fecha) . ' - Aceptado por ' . $this->getUsuarioAuth()['usuario'];

                $tipo_mov_dev = DB::table('tipo_movimiento')->where('codigo', '=', 'EDI')->get()->first();

                $mov_des_id = DB::table('movimiento')->insertGetId([
                    'id' => null, 'tipo_movimiento' => $tipo_mov_dev->id,
                    'sucursal_inicio' => $movimiento->sucursal_inicio, 'sucursal_fin' => $sucursalActual,
                    'entrega' => $movimiento->entrega, 'recibe' => $this->getUsuarioAuth()['id'], 'fecha' => $movimiento->fecha,
                    'fecha_entrega' => $fecha_actual, 'estado' => 'T', 'detalle' => $detalle
                ]);

                foreach ($devoluciones as $d) {
                    if ($d['cantidad'] > 0) {
                        $det = DB::table('detalle_movimiento')->where('id', '=', $d['id'])->get()->first();

                        $inventarioExitente = DB::table('inventario')
                            ->select('inventario.*')
                            ->where('inventario.sucursal', '=', $sucursalActual)
                            ->where('inventario.lote', '=', $det->lote)
                            ->get()->first();

                        if ($inventarioExitente != null) {
                            $cantidadAux = $inventarioExitente->cantidad + $d['cantidad'];
                            DB::table('inventario')
                                ->where('id', '=', $inventarioExitente->id)
                                ->update(['cantidad' => $cantidadAux]);
                        } else {
                            $idInv = DB::table('inventario')->insertGetId([
                                'id' => null,
                                'sucursal' => $sucursalActual,
                                'producto' => $det->producto, 'lote' => $det->lote,
                                'cantidad' => $d['cantidad']
                            ]);
                        }
                        DB::table('detalle_movimiento')->insert([
                            'id' => null, 'producto' => $det->producto,
                            'cantidad' => $d['cantidad'], 'lote' => $det->lote, 'movimiento' => $mov_des_id
                        ]);
                    }
                }
            }

            DB::table('movimiento')
                ->where('id', '=', $movimiento->id)
                ->update(['estado' => 'T', 'fecha_entrega' => $fecha_actual, 'recibe' => $this->getUsuarioAuth()['id']]);

            DB::commit();
        } catch (QueryException $ex) {
            DB::rollBack();
            $transaccionRealizada = false;
        }
    }
}
