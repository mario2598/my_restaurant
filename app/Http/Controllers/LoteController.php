<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class LoteController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "ingLote";
    public function __construct()
    {

        setlocale(LC_ALL, "es_CR");
    }

    public function index()
    {
    }

    public function goNuevo()
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            $this->setMsjSeguridad();
            return redirect('/');
        }


        $data = [
            'bodegas' => $this->getBodegas(),
            'menus' => $this->cargarMenus(),
            'productos' => $this->getProductos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('bodega.lote.nuevoLote', compact('data'));
    }

    public function goDetalleById($id, $datos)
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $lote = DB::table('lote')
            ->leftjoin('inventario', 'inventario.lote', '=', 'lote.id')
            ->select('lote.*', 'inventario.sucursal')
            ->where('lote.id', '=', $id)->get()->first();

        if ($lote == null) {
            $this->setError('Detalle de lote', 'El lote no existe.');
            return redirect('/');
        }

        $lote->fecha_vencimiento = date('Y-m-d', strtotime($lote->fecha_vencimiento));

        $data = [
            'lote' => $lote,
            'bodegas' => $this->getBodegas(),
            'datos' =>  $datos,
            'productos' => $this->getProductos(),
            'menus' => $this->cargarMenus(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('bodega.lote.editarLote', compact('data'));
    }

    /**
     * Genera el codigo de lote 
     */
    public function generarCodigo()
    {
        return date("dmyhism");
    }
    /**
     * Guarda o actualiza un producto
     */
    public function guardar(Request $request)
    {
        if (!$this->validarSesion($this->codigo_pantalla)) {
            return redirect('/');
        }

        //dd($request->all());
        $id = $request->input('id');
        $codigo_lote = $request->input('codigo_lote');

        $lote = DB::table('lote')->select('lote.*')->where('codigo', '=', $codigo_lote)->get()->first();

        if ($this->isNull($codigo_lote)) { // Nuevo 
            $codigo_lote = $this->generarCodigo();
            $actualizar = false;
        } else { // Editar usuario

            if ($lote == null) {
                $this->setError('Guardar Lote', 'No existe un lote con los credenciales.');
                return redirect('/');
            } else if ($lote->estado != "N") {
                $this->setError('Guardar Lote', 'El lote ya fue procesado.');
                return redirect('/');
            }
            $actualizar = true;
        }


        if ($this->validar($request, $actualizar)) {
            $producto = $request->input('producto');
            $bodega = $request->input('bodega');
            $cantidad = $request->input('cantidad');
            $detalle = $request->input('detalle');
            $vencimiento = $request->input('vencimiento');
            $fecha_ingreso = date("Y-m-d H:i:s");
            try {
                DB::beginTransaction();

                if ($actualizar) { // Editar lote
                    DB::table('lote')
                        ->where('codigo', '=', $codigo_lote)
                        ->update(['fecha_vencimiento' => $vencimiento, 'detalle' => $detalle]);
                } else { // Nuevo usuario

                    $id = DB::table('lote')->insertGetId([
                        'id' => null, 'fecha_creacion' => $fecha_ingreso,
                        'fecha_vencimiento' => $vencimiento, 'cantidad' => $cantidad, 'codigo' => $codigo_lote,
                        'producto' => $producto, 'estado' => 'N', 'detalle' => $detalle
                    ]); //N : Significa que aun no se ingresa a bodega , solo esta en existencia

                    $idInventario = DB::table('inventario')->insertGetId([
                        'id' => null, 'sucursal' => $bodega,
                        'producto' => $producto, 'lote' => $id, 'cantidad' => $cantidad
                    ]);

                    $tipo_mov_des = DB::table('tipo_movimiento')->where('codigo', '=', 'ENL')->get()->first();
                    $mov_des_id = DB::table('movimiento')->insertGetId([
                        'id' => null, 'tipo_movimiento' => $tipo_mov_des->id,
                        'sucursal_inicio' => $bodega, 'sucursal_fin' => $bodega,
                        'entrega' => $this->getUsuarioAuth()['id'], 'recibe' => null, 'fecha' => $fecha_ingreso,
                        'fecha_entrega' => $fecha_ingreso, 'estado' => 'T', 'detalle' => $detalle
                    ]);
                    DB::table('detalle_movimiento')->insertGetId([
                        'id' => null, 'producto' => $producto,
                        'cantidad' => $cantidad, 'lote' => $id, 'movimiento' => $mov_des_id
                    ]);
                }

                DB::commit();


                if ($actualizar) { // Editar usuario
                    $this->setSuccess('Guardar Lote', 'Se actualizo el lote correctamente.');
                } else { // Nuevo usuario

                    $this->setSuccess('Guardar Lote', 'Lote creado correctamente.');
                }
                $datos = [
                    'id' => $id,
                    'codigo_lote' => $codigo_lote,
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'vencimiento' => $vencimiento,
                    'bodega' => $bodega,
                    'detalle' => $detalle,

                ];

                if ($actualizar) { // Editar usuario
                    return redirect('/');
                } else { // Nuevo usuario
                    return $this->goDetalleById($id, $datos);
                }
            } catch (QueryException $ex) {
                DB::rollBack();
                $this->setError('Guardar Producto', 'Algo salio mal...');
                return redirect('bodega/productos');
            }
        } else {
            if ($actualizar) {
                return redirect('/');
            } else {
                return redirect('/');
            }
        }
    }




    public function validar(Request $r, $actualizar)
    {
        $requeridos = "[";
        $valido = true;

        if (!$actualizar) {
            if ($this->isNull($r->input('producto')) || $this->isEmpty($r->input('producto'))) {
                $requeridos .= " Producto ";
                $valido = false;
            }

            if ($this->isNull($r->input('cantidad')) || $this->isEmpty($r->input('cantidad'))) {
                $requeridos .= " Cantidad ";
                $valido = false;
            }
        }

        if ($this->isNull($r->input('vencimiento')) || $this->isEmpty($r->input('vencimiento'))) {
            $requeridos .= " Fecha vencimiento";
            $valido = false;
        }

        $requeridos .= "] ";

        if (!$valido) {
            $this->setError('Campos Requeridos', $requeridos);
            return false;
        }

        if (!$actualizar) {
            if (!$this->isNumber($r->input('cantidad')) || $r->input('cantidad') < 1) {
                $this->setError('Número incorrecto', "La cantidad debe ser mínimo 1.");
                return false;
            }
        }

        if ($r->input('vencimiento') <= date("Y-m-d")) {
            $this->setError('Fecha incorrecta', "La fecha de vencimiento no puede ser igual al día de ingreso.");
            return false;
        }

        if (!$this->isLengthMinor($r->input('detalle'), 300)) {
            $this->setError('Validación de datos', "El detalle debe ser de máximo 300 caracteres.");
            return false;
        }


        return $valido;
    }
}
