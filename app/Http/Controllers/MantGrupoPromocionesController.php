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


    public function __construct()
    {
    }

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
        
        $promocion = json_decode($request->input('promocion'),true);
       
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
      
        $path ="";
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
                        'descripcion' => $promocion['descripcion'], 'precio' => $promocion['precio'], 'estado' =>  $promocion["estado"]
                        , 'categoria' =>  $promocion["categoria"], 'imagen' => $path
                    ]);
            } else {
                $idAuxPromo = DB::table('grupo_promocion')->insertGetId([
                    'id' => null,  'descripcion' => $promocion['descripcion'], 'precio' => $promocion['precio'], 'estado' =>  $promocion["estado"]
                    , 'categoria' =>  $promocion["categoria"], 'imagen' => $path
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
        ->where('det_grupo_promocion.producto', '=',$detalle['producto'])
        ->where('det_grupo_promocion.tipo', '=',$detalle['tipo'])->get()->first();
     
        $actualiza = false;
        if ($detalleExiste != null) {
            $actualiza = true;
        }
        

            DB::beginTransaction();
            if(!$actualiza){
                $id = DB::table('det_grupo_promocion')->insertGetId([
                    'id' => null,  'producto' => $detalle['producto'], 'cantidad' => $detalle['cantidad'], 'grupo_promocion' =>  $promocion['id'], 
                    'tipo' =>  $detalle['tipo']
                ]);
            }else{
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
}
