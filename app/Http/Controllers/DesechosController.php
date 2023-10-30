<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;

class DesechosController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {
        setlocale(LC_ALL, "es_CR");
    }

    public function index()
    {
    }

    public function goAgregarDesechos()
    {
        if (!$this->validarSesion("desechosAgr")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'desechos' => [],
            'lotes' => [],
            'sucursales' => $this->getBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('desechos.agregarDesechos', compact('data'));
    }

    public function cambiarInventario(Request $request)
    {
        if (!$this->validarSesion("desechosAgr")) {
            echo 'noInv';
            exit;
        }
        $sucursal = $request->input('suc');
        return $this->getLotes($sucursal);
    }

    private function getLotes($sucursal)
    {
        $lotes = DB::table('inventario')
            ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'inventario.sucursal')
            ->leftjoin('lote', 'lote.id', '=', 'inventario.lote')
            ->select('inventario.id', 'lote.fecha_vencimiento', 'lote.codigo', 'producto.nombre', 'inventario.cantidad')
            ->where('inventario.sucursal', '=', $sucursal)
            ->orderBy('lote.fecha_vencimiento', 'asc')
            ->get();

        return $lotes;
    }



    public function goDesechos()
    {
        if (!$this->validarSesion("desechosTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtros = [
            'sucursal' => 'T',
            'grupo' => 'P',
        ];

        $data = [
            'menus' => $this->cargarMenus(),
            'filtros' => $filtros,
            'desechos' => [],
            'sucursales' => $this->getBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('desechos.desechos', compact('data'));
    }

    public function goDesechosFiltro(Request $request)
    {
        if (!$this->validarSesion("desechosTodos")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $sucursal = $request->input('sucursal');
        $grupo = $request->input('grupo');

        $filtros = [
            'sucursal' => $sucursal,
            'grupo' => $grupo,
        ];
        $data = [
            'menus' => $this->cargarMenus(),
            'filtros' => $filtros,
            'desechos' => $this->getDesechos($sucursal, $grupo),
            'sucursales' => $this->getBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('desechos.desechos', compact('data'));
    }

    public function getDesechos($sucursal = "T", $grupo = "P")
    {
        $tipo_mov = DB::table('tipo_movimiento')->where('codigo', '=', 'SDD')->get()->first();
        if ($grupo != "P") {
            $desechos =  DB::table('detalle_movimiento')
                ->join('movimiento', 'movimiento.id', '=', 'detalle_movimiento.movimiento')
                ->join('sucursal', 'sucursal.id', '=', 'movimiento.sucursal_inicio')
                ->join('producto', 'producto.id', '=', 'detalle_movimiento.producto')
                ->join('lote', 'lote.id', '=', 'detalle_movimiento.lote')
                ->select(
                    'detalle_movimiento.cantidad',
                    'lote.codigo as codigo',
                    'producto.nombre as producto_nombre',
                    'sucursal.descripcion as sucursal_nombre'
                )
                ->where('movimiento.tipo_movimiento', '=', $tipo_mov->id)
                ->where('detalle_movimiento.cantidad', '>', 0);
        } else {
            $desechos =  DB::table('detalle_movimiento')
                ->join('movimiento', 'movimiento.id', '=', 'detalle_movimiento.movimiento')
                ->join('sucursal', 'sucursal.id', '=', 'movimiento.sucursal_inicio')
                ->join('producto', 'producto.id', '=', 'detalle_movimiento.producto')
                ->select(
                    DB::raw('SUM(detalle_movimiento.cantidad) as cantidad'),
                    'producto.codigo_barra as codigo',
                    'producto.nombre as producto_nombre',
                    'sucursal.descripcion as sucursal_nombre'
                )
                ->groupBy('producto.codigo_barra', 'producto_nombre', 'sucursal.descripcion')
                ->having(DB::raw('SUM(detalle_movimiento.cantidad)'), '>', 0)
                ->where('movimiento.tipo_movimiento', '=', $tipo_mov->id);
        }

        if (!$this->isNull($sucursal) && $sucursal != 'T') {
            $desechos = $desechos->where('movimiento.sucursal_inicio', '=',  $sucursal);
        }
        return $desechos->get();
    }

    public function tirarDesechos(Request $request)
    {
        if (!$this->validarSesion("desechosAgr")) {
            echo '-1';
            exit;
        }
        try {
            $desechos = $request->input('des');
            $sucursal = $request->input('sucursal');
            $detalle = $request->input('det');
            $hayDesechos = false;
            if ($sucursal == null || $sucursal < 1) {
                echo 'noSucursal';
                exit;
            }
            foreach ($desechos as $d) {
                if ($d['cantidad'] > 0) {
                    $hayDesechos = true;
                }
            }
            if (!$hayDesechos) {
                echo 'noDesechos';
                exit;
            }
            $fecha_actual = date("Y-m-d H:i:s");
            $detalle = ($detalle ?? '') . ' - ' . $this->fechaFormat($fecha_actual) . ' - Realizado por ' . $this->getUsuarioAuth()['usuario'];

            DB::beginTransaction();

            $tipo_mov_des = DB::table('tipo_movimiento')->where('codigo', '=', 'SDB')->get()->first();

            $mov_des_id = DB::table('movimiento')->insertGetId([
                'id' => null, 'tipo_movimiento' => $tipo_mov_des->id,
                'sucursal_inicio' => $sucursal, 'sucursal_fin' => null,
                'entrega' => $this->getUsuarioAuth()['id'], 'recibe' => null, 'fecha' => $fecha_actual,
                'fecha_entrega' => null, 'estado' => 'T', 'detalle' => $detalle
            ]);

            foreach ($desechos as $d) {
                if ($d['cantidad'] > 0) {
                    $inventario = DB::table('inventario')->where('id', '=', $d['id'])->get()->first();
                    $cantidadNueva = $inventario->cantidad - $d['cantidad'];
                    if ($cantidadNueva < 1) {
                        DB::table('inventario')
                            ->where('id', '=', $d['id'])->delete();
                    } else {
                        DB::table('inventario')
                            ->where('id', '=', $d['id'])
                            ->update(['cantidad' => $cantidadNueva]);
                    }
                    DB::table('detalle_movimiento')->insertGetId([
                        'id' => null, 'producto' => $inventario->producto,
                        'cantidad' => $d['cantidad'], 'lote' => $inventario->lote, 'movimiento' => $mov_des_id
                    ]);
                }
            }

            DB::commit();
            echo $mov_des_id;
            exit;
        } catch (QueryException $ex) {
            DB::rollBack();
            echo 'transactionError';
            exit;
        }
    }
}
