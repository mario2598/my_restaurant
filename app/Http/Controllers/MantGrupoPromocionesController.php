<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class MantGrupoPromocionesController extends Controller
{
    use SpaceUtil;


    public function __construct() {}

    public function goGruposPromociones()
    {
        if (!$this->validarSesion("mantPromProd")) {
            return redirect('/');
        }
        $productos_menu = DB::table('producto_menu')
            ->leftjoin('categoria', 'categoria.id', '=', 'producto_menu.categoria')
            ->select('producto_menu.*', 'categoria.categoria as nombre_categoria')
            ->where('producto_menu.estado', '=', "A")
            ->get();

        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursales(),
            'categorias' => $this->getCategorias(),
            'productos_menu' => $productos_menu,
            'productos_externos' => ProductosExternosController::getProductos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('mant.grupoPromocion', compact('data'));
    }

    public static function getGruposPromociones()
    {
        $promos = DB::table('grupo_promocion')
            ->select('grupo_promocion.*')->get();

        foreach ($promos as $p) {
            $p->detalles =  DB::table('det_grupo_promocion')
                ->select('det_grupo_promocion.*')->where('det_grupo_promocion.grupo_promocion', '=', $p->id)->get();

            foreach ($p->detalles as $d) {
                if ($d->tipo == 'E') {
                    $d->prod =  DB::table('producto_externo')
                        ->select('producto_externo.*')->where('producto_externo.id', '=', $d->producto)->get()->first();
                } else {
                    $d->prod =  DB::table('producto_menu')
                        ->select('producto_menu.*')->where('producto_menu.id', '=', $d->producto)->get()->first();
                }
            }
        }

        return $promos;
    }

    public function filtrarGruposPromociones(Request $request)
    {
        if (!$this->validarSesion("mantPromProd")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        return $this->responseAjaxSuccess("", MantGrupoPromocionesController::getGruposPromociones());
    }

    public function guardarPromocion(Request $request)
    {
        if (!$this->validarSesion("mantPromProd")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }
        $actualizar = false;

        $promocion = json_decode($request->input('promocion'), true);

        if ($promocion['id'] < 1 || $this->isNull($promocion['id'])) { // Nuevo 
            $actualizar = false;
        } else {
            $actualizar = true;
            $promo = DB::table('grupo_promocion')
                ->select('grupo_promocion.*')->where('id', '=', $promocion['id'])->get()->first();
            if ($promo == null) {
                return $this->responseAjaxServerError("No se encontro la promocion.", []);
            }
        }
        $idAuxPromo = $promocion['id'];
        $foto_producto = $request->file('foto_producto');

        $path = "";
        if ($foto_producto != null) {
            // Guarda el archivo en la carpeta 'productos' dentro del almacenamiento público
            $path = $foto_producto->store('productos', 'public');
        } else {
            // Lógica para manejar si no se ha enviado ningún archivo
            // Asegúrate de manejar este caso según tus requisitos
            $path = "";
        }
        try {

            DB::beginTransaction();

            if ($actualizar) {
                DB::table('grupo_promocion')
                    ->where('id', '=', $promocion['id'])
                    ->update([
                        'descripcion' => $promocion['descripcion'],
                        'precio' => $promocion['precio'],
                        'estado' =>  $promocion["estado"],
                        'categoria' =>  $promocion["categoria"],
                        'imagen' => $path
                    ]);
            } else {
                $idAuxPromo = DB::table('grupo_promocion')->insertGetId([
                    'id' => null,
                    'descripcion' => $promocion['descripcion'],
                    'precio' => $promocion['precio'],
                    'estado' =>  $promocion["estado"],
                    'categoria' =>  $promocion["categoria"],
                    'imagen' => $path
                ]);
            }

            DB::commit();
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error creando la promoción", []);
        }

        $promo = DB::table('grupo_promocion')
            ->select('grupo_promocion.*')->where('id', '=', $idAuxPromo)->get()->first();
        $promo->detalles =  DB::table('det_grupo_promocion')
            ->select('det_grupo_promocion.*')->where('det_grupo_promocion.grupo_promocion', '=', $promo->id)->get();

        foreach ($promo->detalles as $d) {
            if ($d->tipo == 'E') {
                $d->prod =  DB::table('producto_externo')
                    ->select('producto_externo.*')->where('producto_externo.id', '=', $d->producto)->get()->first();
            } else {
                $d->prod =  DB::table('producto_menu')
                    ->select('producto_menu.*')->where('producto_menu.id', '=', $d->producto)->get()->first();
            }
        }
        return $this->responseAjaxSuccess("", $promo);
    }


    public function guardarDetallePromocion(Request $request)
    {
        if (!$this->validarSesion("mantPromProd")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        $promocion = $request->input('promocion');
        $detalle = $request->input('detallePromo');
        if ($promocion['id'] < 1 || $this->isNull($promocion['id'])) { // Nuevo 
            return $this->responseAjaxServerError("No se encontro la promocion.", []);
        }

        $promo = DB::table('grupo_promocion')
            ->select('grupo_promocion.*')->where('id', '=', $promocion['id'])->get()->first();
        if ($promo == null) {
            return $this->responseAjaxServerError("No se encontro la promocion.", []);
        }

        $detalleExiste = DB::table('det_grupo_promocion')
            ->select('det_grupo_promocion.*')->where('det_grupo_promocion.grupo_promocion', '=', $promo->id)
            ->where('det_grupo_promocion.producto', '=', $detalle['producto'])
            ->where('det_grupo_promocion.tipo', '=', $detalle['tipo'])->get()->first();

        $actualiza = false;
        if ($detalleExiste != null) {
            $actualiza = true;
        }


        DB::beginTransaction();
        if (!$actualiza) {
            $id = DB::table('det_grupo_promocion')->insertGetId([
                'id' => null,
                'producto' => $detalle['producto'],
                'cantidad' => $detalle['cantidad'],
                'grupo_promocion' =>  $promocion['id'],
                'tipo' =>  $detalle['tipo']
            ]);
        } else {
            DB::table('det_grupo_promocion')
                ->where('id', '=', $detalleExiste->id)
                ->update([
                    'cantidad' => $detalle['cantidad']
                ]);
        }

        DB::commit();

        $promo = DB::table('grupo_promocion')
            ->select('grupo_promocion.*')->where('id', '=', $promocion['id'])->get()->first();
        $promo->detalles =  DB::table('det_grupo_promocion')
            ->select('det_grupo_promocion.*')->where('det_grupo_promocion.grupo_promocion', '=', $promo->id)->get();

        foreach ($promo->detalles as $d) {
            if ($d->tipo == 'E') {
                $d->prod =  DB::table('producto_externo')
                    ->select('producto_externo.*')->where('producto_externo.id', '=', $d->producto)->get()->first();
            } else {
                $d->prod =  DB::table('producto_menu')
                    ->select('producto_menu.*')->where('producto_menu.id', '=', $d->producto)->get()->first();
            }
        }
        return $this->responseAjaxSuccess("", $promo);
    }

    public function eliminarDetallePromocion(Request $request)
    {
        if (!$this->validarSesion("mantPromProd")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        $detalle = $request->input('detallePromo');
        if ($detalle < 1 || $this->isNull($detalle)) { // Nuevo 
            return $this->responseAjaxServerError("No se encontro el detalle de la promocion.", []);
        }

        $aux = DB::table('det_grupo_promocion')
            ->select('det_grupo_promocion.*')->where('id', '=', $detalle)->get()->first();
        if ($aux == null) {
            return $this->responseAjaxServerError("No se encontro el detalle de la promocion.", []);
        }

        try {

            DB::beginTransaction();

            DB::table('det_grupo_promocion')
                ->where('id', '=', $detalle)
                ->delete();


            DB::commit();
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Error creando el detalle de la promoción", []);
        }

        $promo = DB::table('grupo_promocion')
            ->select('grupo_promocion.*')->where('id', '=', $aux->grupo_promocion)->get()->first();
        $promo->detalles =  DB::table('det_grupo_promocion')
            ->select('det_grupo_promocion.*')->where('det_grupo_promocion.grupo_promocion', '=', $promo->id)->get();

        foreach ($promo->detalles as $d) {
            if ($d->tipo == 'E') {
                $d->prod =  DB::table('producto_externo')
                    ->select('producto_externo.*')->where('producto_externo.id', '=', $d->producto)->get()->first();
            } else {
                $d->prod =  DB::table('producto_menu')
                    ->select('producto_menu.*')->where('producto_menu.id', '=', $d->producto)->get()->first();
            }
        }
        return $this->responseAjaxSuccess("", $promo);
    }

    public static function getProdPromoByCodigo($idPromo)
    {
        $promo = DB::table("grupo_promocion")
            ->where('id', $idPromo)
            ->select(
                'grupo_promocion.id',
                'grupo_promocion.id as codigo',
                DB::raw('0 as posicion_menu'),
                'grupo_promocion.descripcion as nombre',
                'grupo_promocion.precio',
                DB::raw('0 as impuesto'),
                DB::raw("'C' as tipo_comanda"),
                DB::raw("'S' as es_promocion")
            )->get()->first();

        $promo->valorImpuesto = 13;
        $promo->tipoProducto = 'PROMO';
        $promo->extras = []; // Inicializar como arreglo vacío

        $detallesE = DB::table("det_grupo_promocion")
            ->join('producto_externo', 'producto_externo.id', '=', 'det_grupo_promocion.producto')
            ->where('det_grupo_promocion.grupo_promocion', $promo->id)
            ->where('det_grupo_promocion.tipo', "E")
            ->select(
                'det_grupo_promocion.id',
                'producto_externo.id as id_producto',
                DB::raw('0 as posicion_menu'),
                DB::raw("'E' as tipo_producto"),
                'producto_externo.nombre',
                'producto_externo.precio',
                'det_grupo_promocion.cantidad',
                DB::raw('0 as impuesto')
            )
            ->orderBy('producto_externo.posicion_menu', 'asc')->get();
        $promo->detallesExternos = $detallesE;

        foreach ($promo->detallesExternos as $p) {
            $p->tipoProducto = 'PROMO';

            $grupos = DB::table('extra_producto_externo')
                ->select(
                    'extra_producto_externo.dsc_grupo',
                    'extra_producto_externo.multiple'
                )->distinct()
                ->where('extra_producto_externo.producto', '=', $p->id_producto)
                ->get();

            $extrasAux = [];
            foreach ($grupos as $g) {
                $requerido = false;
                $multiple = false;
                $listExtras = DB::table('extra_producto_externo')
                    ->select('extra_producto_externo.*')
                    ->where('extra_producto_externo.producto', '=', $p->id_producto)
                    ->where('extra_producto_externo.dsc_grupo', '=', $g->dsc_grupo)
                    ->where('extra_producto_externo.multiple', '=', $g->multiple)
                    ->get() ?? [];

                foreach ($listExtras as $le) {
                    if ($le->es_requerido) {
                        $requerido = true;
                    }
                    if ($le->multiple) {
                        $multiple = true;
                    }
                }

                $extras = [
                    'grupo' => $g->dsc_grupo,
                    'requerido' => $requerido ? 1 : 0,
                    'multiple' => $multiple ? 1 : 0,
                    'extras' => $listExtras
                ];
                array_push($extrasAux, $extras);
            }
            $p->extras = $extrasAux; // Asignar extras al producto individual
        }

        $detallesR = DB::table("det_grupo_promocion")
            ->join('producto_menu', 'producto_menu.id', '=', 'det_grupo_promocion.producto')
            ->where('det_grupo_promocion.grupo_promocion', $promo->id)
            ->select(
                'det_grupo_promocion.id',
                'producto_menu.id as id_producto',
                'producto_menu.codigo',
                'producto_menu.nombre',
                'producto_menu.precio',
                DB::raw("'R' as tipo_producto"),
                'det_grupo_promocion.cantidad',
                DB::raw('0 as posicion_menu'),
                DB::raw('0 as impuesto')
            )
            ->orderBy('producto_menu.posicion_menu', 'asc')->get();

        $promo->detallesRestaurante = $detallesR;

        foreach ($promo->detallesRestaurante as $p) {
            $p->tipoProducto = 'PROMO';

            $grupos = DB::table('extra_producto_menu')
                ->select(
                    'extra_producto_menu.dsc_grupo',
                    'extra_producto_menu.multiple'
                )->distinct()
                ->where('extra_producto_menu.producto', '=', $p->id_producto)
                ->orderBy('extra_producto_menu.es_requerido', 'DESC')
                ->get();

            $extrasAux = [];
            foreach ($grupos as $g) {
                $requerido = false;
                $multiple = false;
                $listExtras = DB::table('extra_producto_menu')
                    ->select('extra_producto_menu.*')
                    ->where('extra_producto_menu.producto', '=', $p->id_producto)
                    ->where('extra_producto_menu.dsc_grupo', '=', $g->dsc_grupo)
                    ->where('extra_producto_menu.multiple', '=', $g->multiple)
                    ->get() ?? [];

                foreach ($listExtras as $le) {
                    if ($le->es_requerido) {
                        $requerido = true;
                    }
                    if ($le->multiple) {
                        $multiple = true;
                    }
                }

                $extras = [
                    'grupo' => $g->dsc_grupo,
                    'requerido' => $requerido ? 1 : 0,
                    'multiple' => $multiple ? 1 : 0,
                    'extras' => $listExtras
                ];
                array_push($extrasAux, $extras);
            }
            $p->extras = $extrasAux; // Asignar extras al producto individual
        }

        return $promo;
    }
}
