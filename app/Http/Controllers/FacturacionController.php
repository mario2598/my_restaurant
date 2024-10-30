<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use Intervention\Image\ImageManagerStatic as Image;

class FacturacionController extends Controller
{
    use SpaceUtil;
    private $admin;

    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function goOrdenesAdmin()
    {
        if (!$this->validarSesion("adm_ord")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $data = [
            'menus' => $this->cargarMenus(),
            'sucursales' => $this->getSucursales(),
            'clientes' => $this->getClientes(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("facturacion.ordenesAdmin", compact("data"));
    }

    public function filtrarOrdenesAdmin(Request $request)
    {
        if (!$this->validarSesion("adm_ord")) {
            return $this->responseAjaxServerError("No tienes permisos para ingresar.", []);
        }

        $filtro = $request->input('filtro');

        $filtroCliente = $filtro['cliente'];
        $filtroSucursal =  $filtro['sucursal'];
        $hasta = $filtro['hasta'];
        $desde = $filtro['desde'];

        $ordenes = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'orden.sucursal')
            ->select(
                'orden.*',
                'sis_estado.nombre as estadoOrden',
                'sis_estado.cod_general',
                'sucursal.descripcion as nombreSucursal'
            );


        if ($filtroCliente >= 1  && !$this->isNull($filtroCliente)) {
            $ordenes = $ordenes->where('orden.cliente', '=', $filtroCliente);
        }

        if (!$this->isNull($filtroSucursal) && $filtroSucursal != 'T') {
            $ordenes = $ordenes->where('orden.sucursal', '=',  $filtroSucursal);
        }

        if (!$this->isNull($desde)) {
            $ordenes = $ordenes->where('orden.fecha_inicio', '>=', $desde);
        }

        if (!$this->isNull($hasta)) {
            $mod_date = strtotime($hasta . "+ 1 days");
            $mod_date = date("Y-m-d", $mod_date);
            $ordenes = $ordenes->where('orden.fecha_inicio', '<', $mod_date);
        }

        $ordenes = $ordenes->orderBy('orden.fecha_inicio', 'DESC')->get();

        foreach ($ordenes as $o) {
            $o->detalles = DB::table('detalle_orden')->where('orden', '=', $o->id)->get();
            $o->idOrdenEnc = encrypt($o->id);
        }

        return  $this->responseAjaxSuccess("", $ordenes);
    }

    public function getPosProductos()
    {
        $tipos =  $this->getTiposCategoriasProductos();
        foreach ($tipos as $i => $t) {
            if (count($t['categorias']) < 1) {
                unset($tipos[$i]);
            }
        }
        return $tipos;
    }

    public function cargarPosProductosAjax()
    {
        try {
            return $this->responseAjaxSuccess("", $this->getPosProductos());
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salió mal.", "");
        }
    }

    public function goPos()
    {

        $sucursalFactura = MantenimientoSucursalController::getSucursalById($this->getUsuarioSucursal());
        $data = [
            'menus' => $this->cargarMenus(),
            'tipos' => $this->getPosProductos(),
            'sucursalFacturaIva' => $sucursalFactura->factura_iva == 1,
            'mesas' => MesasController::getBySucursal($this->getUsuarioSucursal()),
            'cajaAbierta' =>  CajaController::tieneCajaAbierta(session('usuario')['id'], $this->getUsuarioSucursal()),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view("facturacion.pos", compact("data"));
    }

    public function getTiposCategoriasProductos($todas = true)
    {
        if ($todas) {
            return [
                [
                    'nombre' => 'Restaurante',
                    'codigo' => 'R',
                    'color' => '#0DA8EE',
                    'categorias' => $this->getCategorias('R'),
                ],
                [
                    'nombre' => 'Externos',
                    'codigo' => 'E',
                    'color' => '#41C457',
                    'categorias' => $this->getCategorias('E'),
                ],
                [
                    'nombre' => 'Promociones',
                    'codigo' => 'P',
                    'color' => '#BB88F3',
                    'categorias' => $this->getCategorias('P')
                ]
            ];
        } else {
            return [
                [
                    'nombre' => 'Restaurante',
                    'codigo' => 'R',
                    'color' => '#0DA8EE',
                    'categorias' => $this->getCategorias('R'),
                ],
                [
                    'nombre' => 'Externos',
                    'codigo' => 'E',
                    'color' => '#41C457',
                    'categorias' => $this->getCategorias('E'),
                ],
                [
                    'nombre' => 'Promociones',
                    'codigo' => 'P',
                    'color' => '#BB88F3',
                    'categorias' => $this->getCategorias('P')
                ]
            ];
        }
    }

    public function getCategorias($tipo)
    {
        $categorias = DB::table('categoria')->select('id', 'categoria')->orderBy('posicion_menu', 'asc')->get();

        switch ($tipo) {
            case "R":
                $categorias = $this->getCategoriasProductosMenu($categorias);
                break;
            case "E":
                $categorias = $this->getCategoriasProductosExternos($categorias);
                break;

            case "P":
                $categorias = $this->getCategoriasPromociones($categorias);
                break;
        }
        //Elimina las categorias vacias
        foreach ($categorias as $i => $c) {
            if (count($c->productos) < 1) {
                unset($categorias[$i]);
            }
        }

        return $categorias;
    }

    public function getCategoriasProductosMenu($categorias)
    {

        foreach ($categorias as $categoria) {
            $categoria->productos = DB::table("producto_menu")
                ->where('categoria', $categoria->id)
                ->where('producto_menu.estado', "A")
                ->where('pm_x_sucursal.sucursal', $this->getUsuarioSucursal()) //TODO, verificar método de obtener restaurante
                ->join('impuesto', 'producto_menu.impuesto', '=', 'impuesto.id')
                ->join('pm_x_sucursal', 'producto_menu.id', '=', 'pm_x_sucursal.producto_menu')
                ->select(
                    'producto_menu.id',
                    'producto_menu.codigo',
                    'producto_menu.nombre',
                    'producto_menu.precio',
                    'producto_menu.posicion_menu',
                    'pm_x_sucursal.comanda',
                    'impuesto.impuesto as impuesto',
                    'producto_menu.tipo_comanda',
                    DB::raw("'N' as es_promocion")
                )
                ->orderBy('producto_menu.posicion_menu', 'asc')->get();
            foreach ($categoria->productos as $p) {
                $p->tipoProducto = 'R';
                $grupos = DB::table('extra_producto_menu')
                    ->select(
                        'extra_producto_menu.dsc_grupo',
                        'extra_producto_menu.multiple'
                    )->distinct()
                    ->where('extra_producto_menu.producto', '=', $p->id)
                    ->orderBy('extra_producto_menu.es_requerido', 'DESC')
                    ->get();
                $extrasAux = [];
                foreach ($grupos as $g) {
                    $requerido = false;
                    $multiple = false;
                    $listExtras = DB::table('extra_producto_menu')
                        ->select(
                            'extra_producto_menu.*'
                        )
                        ->where('extra_producto_menu.producto', '=', $p->id)
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
                        'requerido' =>  $requerido ? 1 : 0,
                        'multiple' =>  $multiple ? 1 : 0,
                        'extras' =>  $listExtras
                    ];
                    array_push($extrasAux, $extras);
                }
                $p->extras = $extrasAux;
            }
        }

        return $categorias;
    }

    public function getCategoriasTodosProductos($idSucursal)
    {
        $categorias = DB::table('categoria')->select('id', 'categoria', 'logo', 'url_imagen')->orderBy('posicion_menu', 'asc')->get();
        foreach ($categorias as $categoria) {

            $categoria->url_imagen = asset('storage/' . $categoria->url_imagen);
            $categoria->productos = [];
            $prods = [];
            $prods =   DB::table("producto_menu")
                ->where('categoria', $categoria->id)
                ->where('producto_menu.estado', "A")
                ->where('pm_x_sucursal.sucursal', $idSucursal) //TODO, verificar método de obtener restaurante
                ->join('impuesto', 'producto_menu.impuesto', '=', 'impuesto.id')
                ->join('pm_x_sucursal', 'producto_menu.id', '=', 'pm_x_sucursal.producto_menu')
                ->select(
                    'producto_menu.id',
                    'producto_menu.codigo',
                    'producto_menu.nombre',
                    'producto_menu.precio',
                    'impuesto.impuesto as impuesto',
                    'producto_menu.tipo_comanda',
                    'producto_menu.url_imagen',
                    'producto_menu.descripcion',
                    'producto_menu.posicion_menu'
                )->orderBy('producto_menu.posicion_menu', 'asc')->get();

            foreach ($prods as $p) {
                $p->url_imagen = asset('storage/' . $p->url_imagen);
                $p->tipoProducto = 'R';
                $grupos = DB::table('extra_producto_menu')
                    ->select(
                        'extra_producto_menu.dsc_grupo',
                        'extra_producto_menu.multiple'
                    )->distinct()
                    ->where('extra_producto_menu.producto', '=', $p->id)
                    ->get();
                $extrasAux = [];
                foreach ($grupos as $g) {
                    $requerido = false;
                    $multiple = false;
                    $listExtras = DB::table('extra_producto_menu')
                        ->select(
                            'extra_producto_menu.*'
                        )
                        ->where('extra_producto_menu.producto', '=', $p->id)
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
                        'requerido' =>  $requerido ? 1 : 0,
                        'multiple' =>  $multiple ? 1 : 0,
                        'extras' =>  $listExtras
                    ];
                    array_push($extrasAux, $extras);
                }
                $p->extras = $extrasAux;
                array_push($categoria->productos, $p);
            }
            $prods2 = [];
            $prods2  = DB::table("producto_externo")
                ->where('categoria', $categoria->id)
                ->where('producto_externo.estado', "A")
                ->where('pe_x_sucursal.sucursal', $idSucursal)
                ->where('pe_x_sucursal.cantidad', ">", 0)
                ->join('impuesto', 'producto_externo.impuesto', '=', 'impuesto.id')
                ->join('pe_x_sucursal', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
                ->select(
                    'producto_externo.id',
                    'producto_externo.codigo_barra as codigo',
                    'producto_externo.nombre',
                    'producto_externo.precio',
                    'impuesto.impuesto as impuesto',
                    'pe_x_sucursal.cantidad',
                    'producto_externo.url_imagen',
                    'producto_externo.descripcion',
                    'producto_externo.posicion_menu'
                )->orderBy('producto_externo.posicion_menu', 'asc')->get();

            foreach ($prods2 as $p) {
                $p->url_imagen = asset('storage/' . $p->url_imagen);
                $p->tipoProducto = 'E';
                $grupos = DB::table('extra_producto_externo')
                    ->select(
                        'extra_producto_externo.dsc_grupo',
                        'extra_producto_externo.multiple'
                    )->distinct()
                    ->where('extra_producto_externo.producto', '=', $p->id)
                    ->get();
                $extrasAux = [];
                foreach ($grupos as $g) {
                    $requerido = false;
                    $multiple = false;
                    $listExtras = DB::table('extra_producto_externo')
                        ->select(
                            'extra_producto_externo.*'
                        )
                        ->where('extra_producto_externo.producto', '=', $p->id)
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
                        'requerido' =>  $requerido,
                        'multiple' =>  $multiple,
                        'extras' =>  $listExtras
                    ];
                    array_push($extrasAux, $extras);
                }
                $p->extras = $extrasAux;
                array_push($categoria->productos, $p);
            }
            usort($categoria->productos, function ($a, $b) {
                return $a->posicion_menu - $b->posicion_menu;
            });
        }
        foreach ($categorias as $i => $c) {
            $cont = 0;

            foreach ($c->productos as $p) {
                if ($p != null) {
                    if ($p->id != null) {
                        $cont++;
                    }
                }
            }
            if ($cont < 1) {
                unset($categorias[$i]);
            }
        }

        return $categorias;
    }

    public function getCategoriasTodosProductosMobile()
    {
        $categorias = DB::table('categoria')->select('id', 'categoria', 'logo', 'url_imagen')->orderBy('posicion_menu', 'asc')->get();
        foreach ($categorias as $categoria) {

            $categoria->url_imagen = asset('storage/' . $categoria->url_imagen);
            $categoria->productos = [];
            $prods = [];
            $prods =   DB::table("producto_menu")
                ->where('categoria', $categoria->id)
                ->where('producto_menu.estado', "A")
                ->join('impuesto', 'producto_menu.impuesto', '=', 'impuesto.id')
                ->select(
                    'producto_menu.id',
                    'producto_menu.codigo',
                    'producto_menu.nombre',
                    'producto_menu.precio',
                    'impuesto.impuesto as impuesto',
                    'producto_menu.tipo_comanda',
                    'producto_menu.url_imagen',
                    'producto_menu.descripcion',
                    'producto_menu.posicion_menu'
                )->orderBy('producto_menu.posicion_menu', 'asc')->get();

            foreach ($prods as $p) {
                $p->url_imagen = asset('storage/' . $p->url_imagen);
                $p->tipoProducto = 'R';
                $grupos = DB::table('extra_producto_menu')
                    ->select(
                        'extra_producto_menu.dsc_grupo',
                        'extra_producto_menu.multiple'
                    )->distinct()
                    ->where('extra_producto_menu.producto', '=', $p->id)
                    ->get();
                $extrasAux = [];
                foreach ($grupos as $g) {
                    $requerido = false;
                    $multiple = false;
                    $listExtras = DB::table('extra_producto_menu')
                        ->select(
                            'extra_producto_menu.*'
                        )
                        ->where('extra_producto_menu.producto', '=', $p->id)
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
                        'requerido' =>  $requerido ? 1 : 0,
                        'multiple' =>  $multiple ? 1 : 0,
                        'extras' =>  $listExtras
                    ];
                    array_push($extrasAux, $extras);
                }
                $p->extras = $extrasAux;
                array_push($categoria->productos, $p);
            }
            $prods2 = [];
            $prods2  = DB::table("producto_externo")
                ->where('categoria', $categoria->id)
                ->where('producto_externo.estado', "A")
                ->join('impuesto', 'producto_externo.impuesto', '=', 'impuesto.id')
                ->join('pe_x_sucursal', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
                ->select(
                    'producto_externo.id',
                    'producto_externo.codigo_barra as codigo',
                    'producto_externo.nombre',
                    'producto_externo.precio',
                    'impuesto.impuesto as impuesto',
                    'producto_externo.url_imagen',
                    'producto_externo.descripcion',
                    'producto_externo.posicion_menu'
                )->orderBy('producto_externo.posicion_menu', 'asc')->get();

            foreach ($prods2 as $p) {
                $p->url_imagen = asset('storage/' . $p->url_imagen);
                $p->tipoProducto = 'E';
                $grupos = DB::table('extra_producto_externo')
                    ->select(
                        'extra_producto_externo.dsc_grupo',
                        'extra_producto_externo.multiple'
                    )->distinct()
                    ->where('extra_producto_externo.producto', '=', $p->id)
                    ->get();
                $extrasAux = [];
                foreach ($grupos as $g) {
                    $requerido = false;
                    $multiple = false;
                    $listExtras = DB::table('extra_producto_externo')
                        ->select(
                            'extra_producto_externo.*'
                        )
                        ->where('extra_producto_externo.producto', '=', $p->id)
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
                        'requerido' =>  $requerido,
                        'multiple' =>  $multiple,
                        'extras' =>  $listExtras
                    ];
                    array_push($extrasAux, $extras);
                }
                $p->extras = $extrasAux;
                array_push($categoria->productos, $p);
            }
            usort($categoria->productos, function ($a, $b) {
                return $a->posicion_menu - $b->posicion_menu;
            });
        }
        foreach ($categorias as $i => $c) {
            $cont = 0;

            foreach ($c->productos as $p) {
                if ($p != null) {
                    if ($p->id != null) {
                        $cont++;
                    }
                }
            }
            if ($cont < 1) {
                unset($categorias[$i]);
            }
        }

        return $categorias;
    }

    public function getCategoriasPromociones($categorias)
    {

        foreach ($categorias as $categoria) {
            $categoria->productos = DB::table("grupo_promocion")
                ->where('categoria', $categoria->id)
                ->select(
                    'grupo_promocion.id',
                    'grupo_promocion.id as codigo',
                    DB::raw('0 as posicion_menu'),
                    'grupo_promocion.descripcion as nombre',
                    'grupo_promocion.precio',
                    DB::raw('0 as impuesto'),
                    DB::raw("'C' as tipo_comanda"),
                    DB::raw("'S' as es_promocion")
                )
                ->orderBy('grupo_promocion.id', 'asc')->get();

            foreach ($categoria->productos as $i => $promo) {
                $promo->tipoProducto = 'PROMO';
                $detallesE = [];
                $detallesE =  DB::table("det_grupo_promocion")
                    ->join('producto_externo', 'producto_externo.id', '=', 'det_grupo_promocion.producto')
                    ->join('pe_x_sucursal', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
                    ->where('det_grupo_promocion.grupo_promocion', $promo->id)
                    ->where('det_grupo_promocion.tipo', "E")
                    ->where('producto_externo.estado', "A")
                    ->where('pe_x_sucursal.sucursal', $this->getUsuarioSucursal())
                    ->where('pe_x_sucursal.cantidad', ">", 0)
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

                $cantidad = DB::table('det_grupo_promocion')
                    ->where('det_grupo_promocion.tipo', "E")
                    ->where('det_grupo_promocion.grupo_promocion', $promo->id)
                    ->count();

                if (count($detallesE) != $cantidad) {
                    unset($categoria->productos[$i]);
                    continue;
                }

                $promo->detallesExternos = $detallesE;

                $detallesR = [];
                $detallesR = DB::table("det_grupo_promocion")
                    ->join('producto_menu', 'producto_menu.id', '=', 'det_grupo_promocion.producto')
                    ->join('pm_x_sucursal', 'producto_menu.id', '=', 'pm_x_sucursal.producto_menu')
                    ->where('producto_menu.estado', "A")
                    ->where('det_grupo_promocion.grupo_promocion', $promo->id)
                    ->where('pm_x_sucursal.sucursal', $this->getUsuarioSucursal())
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

                $cantidad1 = DB::table('det_grupo_promocion')
                    ->where('det_grupo_promocion.tipo', "R")
                    ->where('det_grupo_promocion.grupo_promocion', $promo->id)
                    ->count();

                if (count($detallesR) != $cantidad1) {
                    unset($categoria->productos[$i]);
                    continue;
                }

                if (count($detallesR) < 1 && count($detallesE) < 1) {
                    unset($categoria->productos[$i]);
                    continue;
                }

                $promo->detallesRestaurante = $detallesR;

                foreach ($promo->detallesExternos as $p) {

                    $cantidad = DB::table('producto_externo')
                        ->join('pe_x_sucursal', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
                        ->where('producto_externo.estado', "A")
                        ->where('producto_externo.id', $p->id_producto)
                        ->where('pe_x_sucursal.sucursal', $this->getUsuarioSucursal())
                        ->where('pe_x_sucursal.cantidad', ">=", $p->cantidad)
                        ->count();

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
                            ->select(
                                'extra_producto_externo.*'
                            )
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
                            'requerido' =>  $requerido,
                            'multiple' =>  $multiple,
                            'extras' =>  $listExtras
                        ];
                        array_push($extrasAux, $extras);
                    }
                    $p->extras = $extrasAux;
                }

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
                            ->select(
                                'extra_producto_menu.*'
                            )
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
                            'requerido' =>  $requerido ? 1 : 0,
                            'multiple' =>  $multiple ? 1 : 0,
                            'extras' =>  $listExtras
                        ];
                        array_push($extrasAux, $extras);
                    }
                    $p->extras = $extrasAux;
                }
            }
        }
        return $categorias;
    }

    public function getCategoriasProductosExternos($categorias)
    {
        foreach ($categorias as $categoria) {
            $categoria->productos = DB::table("producto_externo")
                ->where('categoria', $categoria->id)
                ->where('producto_externo.estado', "A")
                ->where('pe_x_sucursal.sucursal', $this->getUsuarioSucursal())
                ->where('pe_x_sucursal.cantidad', ">", 0)
                ->join('impuesto', 'producto_externo.impuesto', '=', 'impuesto.id')
                ->join('pe_x_sucursal', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
                ->select(
                    'producto_externo.id',
                    'producto_externo.codigo_barra as codigo',
                    'producto_externo.posicion_menu',
                    'producto_externo.nombre',
                    'producto_externo.precio',
                    'pe_x_sucursal.comanda',
                    'impuesto.impuesto as impuesto',
                    'pe_x_sucursal.cantidad',
                    DB::raw("'N' as es_promocion")
                )
                ->orderBy('producto_externo.posicion_menu', 'asc')->get();
            foreach ($categoria->productos as $p) {
                $p->tipoProducto = 'E';
                $grupos = DB::table('extra_producto_externo')
                    ->select(
                        'extra_producto_externo.dsc_grupo',
                        'extra_producto_externo.multiple'
                    )->distinct()
                    ->where('extra_producto_externo.producto', '=', $p->id)
                    ->get();
                $extrasAux = [];
                foreach ($grupos as $g) {
                    $requerido = false;
                    $multiple = false;
                    $listExtras = DB::table('extra_producto_externo')
                        ->select(
                            'extra_producto_externo.*'
                        )
                        ->where('extra_producto_externo.producto', '=', $p->id)
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
                        'requerido' =>  $requerido,
                        'multiple' =>  $multiple,
                        'extras' =>  $listExtras
                    ];
                    array_push($extrasAux, $extras);
                }
                $p->extras = $extrasAux;
            }
        }
        return $categorias;
    }

    public function validarCodDescuento(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            $this->setMsjSeguridad();
            return redirect('/');
        }
        $codigo_descuento = $request->input('codigo_descuento');
        return FacturacionController::verificaCodDescuento($codigo_descuento);
    }

    public static function verificaCodDescuento($codigo_descuento)
    {
        if ("" == $codigo_descuento ||  $codigo_descuento == null) {
            return [
                "codigo" => 500,
                "mensaje" => "Debe incluir el código a verificar",
                "datos" => "",
                "estado" => false
            ];
        }
        $fecha_actual = date("Y-m-d H:i:s");
        $descuento = DB::table('codigo_descuento')
            ->join('sis_tipo', 'sis_tipo.id', '=', 'codigo_descuento.tipo')
            ->select('codigo_descuento.*', 'sis_tipo.cod_general')
            ->where('fecha_inicio', '<=', $fecha_actual)
            ->where('codigo', '=', $codigo_descuento)
            ->where('fecha_fin', '>=', $fecha_actual)
            ->where('cant_codigos', '>', 0)
            ->where('activo', 1)
            ->get()
            ->first();

        if ($descuento != null) {
            return [
                "codigo" => 500,
                "mensaje" => "",
                "datos" => $descuento,
                "estado" => true
            ];
        } else {
            return [
                "codigo" => 500,
                "mensaje" => "No se encontró un código de descuento activo con el código  brindado",
                "datos" => "",
                "estado" => false
            ];
        }
    }

    public function getClientes()
    {
        return DB::table('cliente')
            ->where('estado', 'A')
            ->get();
    }

    public static function getOrden($idOrden)
    {
        if ($idOrden < 1 || $idOrden == null) {
            return [];
        }

        $orden = DB::table('orden')
            ->leftjoin('mobiliario_x_salon', 'mobiliario_x_salon.id', '=', 'orden.mobiliario_salon')
            ->leftjoin('mobiliario', 'mobiliario.id', '=', 'mobiliario_x_salon.mobiliario')
            ->leftjoin('salon', 'salon.id', '=', 'mobiliario_x_salon.salon')
            ->leftjoin('usuario', 'usuario.id', '=', 'orden.cajero')
            ->select('orden.*', 'usuario.usuario as nombre_cajero', 'salon.nombre as nombre_salon', 'mobiliario_x_salon.numero_mesa', 'mobiliario.nombre as nombre_mobiliario', 'mobiliario.descripcion as descripcion_mobiliario')
            ->where('orden.id', '=', $idOrden)
            ->get()->first();

        $phpdate = strtotime($orden->fecha_inicio);
        $date = date("d-m-Y", strtotime($orden->fecha_inicio));

        $fechaAux = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B ", strtotime($date)));
        $fechaAux .= ' - ' . date("g:i a", $phpdate);
        $orden->fecha_inicio_hora_tiempo = date("g:i a", $phpdate);
        $orden->fecha_inicio_texto =  $fechaAux;
        $orden->detalles = DB::table('detalle_orden')->select('detalle_orden.*')
            ->where('detalle_orden.orden', '=', $orden->id)
            ->get();

        return $orden;
    }

    private function validarOrden($orden, $detalles)
    {
        if (count($detalles) < 1) {
            return $this->responseAjaxServerError("Debes agregar detalles a la orden.", []);
        }

        /*if ($orden['estado'] == null || $orden['estado'] == "") {
            return $this->responseAjaxServerError("La orden no tiene estado.", []);
        }*/
        return  $this->responseAjaxSuccess("", "");
    }

    private function validarInfoEnvio($envio)
    {

        if ($envio['incluye_envio'] == 'true') {
            if ($envio['descripcion_lugar'] == null || $envio['descripcion_lugar'] == "") {
                return $this->responseAjaxServerError("El envío no tiene la descripción del lugar.", []);
            }

            if ($envio['contacto'] == null || $envio['contacto'] == "") {
                return $this->responseAjaxServerError("El envío no tiene información de contacto.", []);
            }

            if ($envio['precio'] == null || $envio['precio']  < 0) {
                return $this->responseAjaxServerError("El envío no tiene un precio valido", []);
            }
        }
        return  $this->responseAjaxSuccess("", "");
    }

    private function validarInfoFe($fe)
    {

        if ($fe['incluyeFE'] == 'true') {
            if ($fe['info_ced_fe'] == null || $fe['info_ced_fe'] == "") {
                return $this->responseAjaxServerError("Información de Facturación Electrónica pendiente : Cédula Cliente.", []);
            }

            if ($fe['info_nombre_fe'] == null || $fe['info_nombre_fe'] == "") {
                return $this->responseAjaxServerError("Información de Facturación Electrónica pendiente : Nombre Cliente.", []);
            }

            if ($fe['info_correo_fe'] == null || $fe['info_correo_fe'] == "") {
                return $this->responseAjaxServerError("Información de Facturación Electrónica pendiente : Correo Cliente.", []);
            } else if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $fe['info_correo_fe'])) {
                return $this->responseAjaxServerError("Información de Facturación Electrónica pendiente : El formato del correo electrónico no es válido.", []);
            }
        }
        return  $this->responseAjaxSuccess("", "");
    }

    public static function asignarMontosDetalles($detalles, $descuento, $infoEnvio = null)
    {
        $infoEnvio = $infoEnvio ?? ['incluye_envio' => 'false', 'precio' => 0];

        $totalGeneral = 0;
        $subtotalGeneral = 0;
        $subtotalAntesDescuento = 0;
        $montoImpuestos = 0;
        $montoImpuestoServicioMesa = 0;
        $totalExtrasGeneral = 0;
        $listaDetallesNueva = [];  // Crear una lista vacía
        $sucursal = DB::table('usuario')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
            ->select('sucursal.*')
            ->where('usuario.id', '=', session('usuario')['id'])
            ->get()->first();

        foreach ($detalles as $d) {
            if ($infoEnvio['incluye_envio'] == 'true') {
                $d['impuestoServicio'] = 'N';
            }

            if ($d['cantidad'] > 0) {
                $totalLinea = $d['cantidad'] * $d['precio_unidad'];
                $subTotalLinea = 0;
                $totalExtras = 0;
                $extraLinea = 0;
                if (isset($d['extras'])) {
                    foreach ($d['extras'] as $extra) {
                        $extraLinea = $d['cantidad'] * $extra['precio'];
                        $totalExtras = $totalExtras + $extraLinea;
                    }
                }
                $totalLinea = $totalLinea + $totalExtras;

                $montoIvaLinea = 0;
                $montoLineaSinIva = $totalLinea;
                if ($d['impuesto'] > 0 && $sucursal->factura_iva == 1) {
                    $montoLineaSinIva = ($totalLinea / (floatval("1." . $d['impuesto'])));
                    $montoIvaLinea = $totalLinea - $montoLineaSinIva;
                }

                $impuestoServicio = 0;
                if ($d['impuestoServicio'] == 'S') {
                    $impuestoServicio = ($montoLineaSinIva * 0.10);
                    $montoLineaSinIva = $montoLineaSinIva + $impuestoServicio;
                    if ($d['impuesto'] > 0  && $sucursal->factura_iva == 1) {
                        $montoIvaLinea = ($montoLineaSinIva) * (floatval("0." . $d['impuesto']));
                    }
                }

                $subTotalLinea = $montoLineaSinIva;

                $subtotalAntesDescuento += $subTotalLinea;

                $d['subTotal'] = $subTotalLinea;
                $d['totalGen'] = $subTotalLinea + $montoIvaLinea;

                $objeto = [
                    'totalGen' => $subTotalLinea + $montoIvaLinea,
                    'subTotal' => $subTotalLinea,
                    'totalExtras' => $totalExtras,
                    'extras' => $d['extras'] ?? [],
                    'linea_envio' => false,
                    'montoIva' =>  $montoIvaLinea,
                    'montoImpuestoServicioMesa' =>  $impuestoServicio,
                    'descuento' => 0,
                    'detalle' =>  $d
                ];

                array_push($listaDetallesNueva, $objeto);
            }
        }

        if ($infoEnvio['incluye_envio'] == 'true') {
            $montoLineaEnvioSinIva = ($infoEnvio['precio'] / 1.13);
            $montoIvaLineaEnvio = $infoEnvio['precio'] - ($montoLineaEnvioSinIva);
            $envioLinea = [
                'totalGen' => $infoEnvio['precio'],
                'subTotal' =>  $montoLineaEnvioSinIva,
                'totalExtras' => 0,
                'extras' => [],
                'linea_envio' => true,
                'montoIva' => $montoIvaLineaEnvio,
                'montoImpuestoServicioMesa' =>  0,
                'descuento' => 0,
                'detalle' => [
                    'producto' => ['nombre' => 'Servicio de Envío'],
                    'descripcion' => 'Envío',
                    'cantidad' => 1,
                    'precio_unidad' => $infoEnvio['precio'],
                    'impuesto' => 13
                ]
            ];
            $subtotalAntesDescuento += $montoLineaEnvioSinIva;
            array_push($listaDetallesNueva, $envioLinea);
        }

        $subtotalGeneral = 0;
        $totalDescuentoGen = 0;
        $infoDescuento = "";
        $codDescuento = "";
        $idDesc = null;
        if ($descuento != null) {
            $verificaCodDesc = FacturacionController::verificaCodDescuento($descuento['codigo']);
            if ($verificaCodDesc['estado']) {

                $descuentoObj = $verificaCodDesc['datos'];

                if ($descuentoObj->cod_general == 'DESCUENTO_ABSOLUTO') {
                    $totalDescuentoGen = $descuentoObj->descuento;
                } elseif ($descuentoObj->cod_general == 'DESCUENTO_PORCENTAJE') {
                    $totalDescuentoGen = $subtotalAntesDescuento * ($descuentoObj->descuento / 100);
                } else {
                    $totalDescuentoGen = 0;
                }

                $infoDescuento = $descuentoObj->codigo . " : " . $descuentoObj->descripcion . " [ " . $descuentoObj->cod_general . " = " . $descuentoObj->descuento . " ]";
                $codDescuento = $descuentoObj->codigo;
                $idDesc = $descuentoObj->id;
            } else {
                return null;
            }
        }

        $montoImpuestos = 0;
        $totalGeneral = 0;
        $subtotalGeneral = 0;
        $montoImpuestoServicioMesa = 0;

        foreach ($listaDetallesNueva as &$detalle) {
            if (!$detalle['linea_envio']) {
                if ($totalDescuentoGen > 0) {

                    $porcentajeDescuento = $detalle['subTotal'] / $subtotalAntesDescuento;
                    $montoDescuentoLinea = $porcentajeDescuento * $totalDescuentoGen;
                    $detalle['descuento'] = $montoDescuentoLinea;

                    // Recalcular el subtotal con el descuento aplicado
                    $subtotalConDescuento = $detalle['subTotal'] - $montoDescuentoLinea;

                    if ($detalle['detalle']['impuesto'] > 0 && $sucursal->factura_iva == 1) {
                        $ivaConDescuento = ($subtotalConDescuento * (floatval("0." . $detalle['detalle']['impuesto'])));
                    } else {
                        $ivaConDescuento = 0;
                    }

                    $detalle['montoIva'] = $ivaConDescuento;
                    $detalle['subTotal'] = $subtotalConDescuento;
                    $detalle['totalGen'] = $subtotalConDescuento + $ivaConDescuento;
                }
            }

            $totalExtrasGeneral += $detalle['totalExtras'];
            $subtotalGeneral += $detalle['subTotal'];
            $montoImpuestos += $detalle['montoIva'];
            $totalGeneral += $detalle['totalGen'];
            $montoImpuestoServicioMesa += $detalle['montoImpuestoServicioMesa'];
        }

        return [
            'total' => $totalGeneral,
            'detalles' => $listaDetallesNueva,
            'total_pagar' => $totalGeneral,
            'codDescuento' => $codDescuento,
            'idDesc' => $idDesc,
            'infoDescuento' => $infoDescuento,
            'subtotal' => $subtotalGeneral,
            'envio' => $infoEnvio['precio'] ?? 0,
            'descuento' => $totalDescuentoGen,
            'montoImpuestos' => $montoImpuestos,
            'totalExtras' => $totalExtrasGeneral,
            'montoImpuestoServicioMesa' => $montoImpuestoServicioMesa,
        ];
    }

    public static function getConsecutivoNuevaOrdenSucursal($sucursal)
    {
        $consecutivo = DB::table('sucursal')
            ->select('sucursal.cont_ordenes', 'sucursal.cod_general')
            ->where('sucursal.id', $sucursal)
            ->get()->first();
        return date('Y') . '-' . $consecutivo->cod_general . '-' . ($consecutivo->cont_ordenes + 1);
    }

    public static function aumentarConsecutivoOrden($sucursal)
    {
        $params = DB::table('sucursal')
            ->select('sucursal.cont_ordenes')
            ->where('id', '=', $sucursal)
            ->get()->first();
        DB::table('sucursal')
            ->where('id', '=', $sucursal)
            ->update(['cont_ordenes' => $params->cont_ordenes + 1]);
    }

    public function crearFactura(Request $request)
    {

        $orden = $request->input("orden");
        $envio = $request->input("envio");
        $infoFE = $request->input("infoFE");
        $detalles = $request->input("detalles");

        $resValidar = $this->validarOrden($orden, $detalles);
        if (!$resValidar['estado']) {
            return $this->responseAjaxServerError($resValidar['mensaje'], []);
        }

        $resValidarEnvio = $this->validarInfoEnvio($envio);
        if (!$resValidarEnvio['estado']) {
            return $this->responseAjaxServerError($resValidarEnvio['mensaje'], []);
        }

        $resValidarFE = $this->validarInfoFe($infoFE);
        if (!$resValidarFE['estado']) {
            return $this->responseAjaxServerError($resValidarFE['mensaje'], []);
        }

        if ($orden['codigo_descuento'] != null) {
            $verificaCodDesc = FacturacionController::verificaCodDescuento($orden['codigo_descuento']['codigo']);
            if (!$verificaCodDesc['estado']) {
                return $this->responseAjaxServerError($verificaCodDesc['mensajes'], []);
            }
        }

        $cliente = $orden['cliente'];

        $asignarMontosDetalles = FacturacionController::asignarMontosDetalles($detalles, $orden['codigo_descuento'] ?? null, $envio);

        $detallesGuardar = $asignarMontosDetalles['detalles'];

        $fechaActual = date("Y-m-d H:i:s");

        $mto_sinpe = $request->input("mto_sinpe");
        $mto_efectivo = $request->input("mto_efectivo");
        $mto_tarjeta = $request->input("mto_tarjeta");

        try {
            DB::beginTransaction();
            $numOrden = $this->getConsecutivoNuevaOrdenSucursal($this->getUsuarioSucursal());
            $id_orden = DB::table('orden')->insertGetId([
                'id' => null,
                'numero_orden' => $numOrden,
                'tipo' => null,
                'fecha_fin' => $fechaActual,
                'fecha_inicio' => $fechaActual,
                'cliente' => null,
                'nombre_cliente' => $cliente,
                'estado' => null,
                'total' => $asignarMontosDetalles['total'],
                'total_con_descuento' => $asignarMontosDetalles['total_pagar'],
                'subtotal' => $asignarMontosDetalles['subtotal'],
                'impuesto' => $asignarMontosDetalles['montoImpuestos'],
                'descuento' => $asignarMontosDetalles['descuento'],
                'mto_impuesto_servicio' => $asignarMontosDetalles['montoImpuestoServicioMesa'],
                'cajero' => session('usuario')['id'],
                'monto_sinpe' => $mto_sinpe,
                'monto_tarjeta' =>  $mto_tarjeta,
                'monto_efectivo' => $mto_efectivo,
                'factura_electronica' => $infoFE['incluyeFE'] == 'true' ? 'S' : 'N',
                'ingreso' => null,
                'sucursal' => $this->getUsuarioSucursal(),
                'fecha_preparado' => null,
                'fecha_entregado' => null,
                'cocina_terminado' => 'N',
                'bebida_terminado' => 'N',
                'caja_cerrada' => 'N',
                'pagado' => 1,
                'estado' => SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION'),
                'periodo' => date('Y'),
                'cierre_caja' => CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal()),
                'monto_envio' => $asignarMontosDetalles['envio'],
                'ind_requiere_envio' => $envio['incluye_envio'] == 'true',
                'info_descuento' => $asignarMontosDetalles['infoDescuento'],
                'mesa' => $orden['mesa'] == "-1" ? null : $orden['mesa']
            ]);

            $pagoOrdenId = DB::table('pago_orden')->insertGetId([
                'orden' => $id_orden,
                'nombre_cliente' => $cliente,
                'monto_tarjeta' => $mto_tarjeta,
                'monto_efectivo' => $mto_efectivo,
                'monto_sinpe' => $mto_sinpe,
                'total' => $asignarMontosDetalles['total'],
                'subtotal' => $asignarMontosDetalles['subtotal'],
                'iva' => $asignarMontosDetalles['montoImpuestos'],
                'descuento' => $asignarMontosDetalles['descuento'],
                'fecha_pago' => $fechaActual,
                'cod_promocion' => $asignarMontosDetalles['codDescuento'],
                'impuesto_servicio' => $asignarMontosDetalles['montoImpuestoServicioMesa']
            ]);

            $this->aumentarConsecutivoOrden($this->getUsuarioSucursal());

            $id_comanda = DB::table('orden_comanda')->insertGetId([
                'orden' => $id_orden,
                'num_comanda' => $numOrden // Primera comanda para la orden recién creada
            ]);

            $serv = new EntregasOrdenController();
            $servEstOrd = new EstOrdenController();
            if ($envio['incluye_envio'] == 'true') {
                $resCreaEnvio = $serv->crearEntregaOrden($envio["precio"], $envio["descripcion_lugar"], $envio["contacto"], $envio["descripcion_lugar_maps"], $id_orden);
                if (!$resCreaEnvio['estado']) {
                    DB::rollBack();
                    return $this->responseAjaxServerError($resCreaEnvio['mensaje'], []);
                }
            }

            $resCargaEst = $servEstOrd->creaEstOrden($id_orden, SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION'), null);

            if (!$resCargaEst['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($resCargaEst['mensaje'], []);
            }

            foreach ($detallesGuardar as $det) {
                $d = $det['detalle'];
                if ($d['cantidad'] > 0) {
                    if (!$det['linea_envio']) {
                        $producto = $d['producto'];
                        $idComanda = null;
                        if ($d['tipo'] == 'R') {
                            $idComanda = ProductosMenuController::getIdComandaByCodigoSucursal($producto['codigo'], $this->getUsuarioSucursal());
                        } else if ($d['tipo'] == 'E') {
                            $idComanda = ProductosExternosController::getIdComandaByCodigoSucursal($producto['codigo'], $this->getUsuarioSucursal());
                        }

                        $det_id = DB::table('detalle_orden')->insertGetId([
                            'id' => null,
                            'cantidad' => $d['cantidad'],
                            'nombre_producto' => $producto['nombre'],
                            'codigo_producto' => $producto['codigo'],
                            'precio_unidad' => $d['precio_unidad'],
                            'impuesto' => $det['montoIva'],
                            'total' => $det['totalGen'],
                            'descuento' => $det['descuento'],
                            'subtotal' => $det['subTotal'],
                            'total_extras' => $det['totalExtras'],
                            'orden' => $id_orden,
                            'tipo_producto' => $d['tipo'],
                            'servicio_mesa' => $d['impuestoServicio'],
                            'monto_servicio' => $det['montoImpuestoServicioMesa'],
                            'observacion' => $d['observacion'],
                            'tipo_comanda' => $d['tipoComanda'],
                            'cod_promocion' => $asignarMontosDetalles['codDescuento'],
                            'cantidad_pagada' =>  $d['cantidad'],
                            'comanda' =>  $idComanda
                        ]);
                        foreach ($d['extras'] ?? [] as $extra) {
                            $ext_id = DB::table('extra_detalle_orden')->insertGetId([
                                'id' => null,
                                'detalle' => $det_id,
                                'extra' => $extra['id'],
                                'orden' => $id_orden,
                                'descripcion_extra' => $extra['descripcion'],
                                'total' => $extra['precio'] * $d['cantidad'],
                                'id_producto' => $extra['idProd'],
                                'tipo_producto' => $extra['tipo_producto']
                            ]);
                        }

                        $res =  $this->restarProductoExternoInventario($det_id, $d['cantidad']);
                        if (!$res['estado']) {
                            DB::rollBack();
                            return $this->responseAjaxServerError($res['mensaje'], []);
                        }

                        DB::table('detalle_orden_comanda')->insert([
                            'orden_comanda' => $id_comanda,
                            'detalle_orden' => $det_id,
                            'cantidad' => $d['cantidad'],
                            'comanda' => $idComanda,
                            'preparado' => 0,
                            'fecha_ingreso' => $fechaActual,
                            'usuario_gestion' => session('usuario')['id']
                        ]);
                    }

                    DB::table('detalle_pago_orden')->insert([
                        'pago_orden' => $pagoOrdenId,
                        'detalle_orden' => (!$det['linea_envio']) ? $det_id  : null,
                        'cantidad_pagada' => $d['cantidad'],
                        'subtotal' => $det['subTotal'],
                        'mto_impuesto_servicio' => $det['montoImpuestoServicioMesa'],
                        'dsc_linea' => ($d['producto']['nombre'] ?? 'Producto'),
                        'descuento' => $det['descuento'],
                        'iva' => $det['montoIva'],
                        'total' => $det['totalGen']
                    ]);
                }
            }

            if ($asignarMontosDetalles['idDesc'] != null) {
                CodigosPromocionController::usarPromocion($asignarMontosDetalles['idDesc']);
            }

            if ($infoFE['incluyeFE'] == 'true') {
                $resCreaFe = $this->crearInfoFacturaElectronica(
                    $infoFE["info_ced_fe"],
                    $infoFE["info_nombre_fe"],
                    $infoFE["info_correo_fe"],
                    $id_orden,
                    null
                );
                if (!$resCreaFe['estado']) {
                    DB::rollBack();
                    return $this->responseAjaxServerError($resCreaFe['mensaje'], []);
                }
            }

            DB::commit();
            $this->setSuccess("Orden Creada", "Se creo la factura correctamente");
            return $this->responseAjaxSuccess("Pedido creado correctamente.", $id_orden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

    public function crearInfoFacturaElectronica($cedula, $nombre, $correo, $orden, $id_pago)
    {
        try {
            $idEst = SisEstadoController::getIdEstadoByCodGeneral('FE_ORDEN_PEND');
            $ext_id = DB::table('fe_info')->insertGetId([
                'id' => null,
                'orden' => $orden,
                'cedula' => $cedula,
                'nombre' => $nombre,
                'correo' => $correo,
                'estado' => SisEstadoController::getIdEstadoByCodGeneral('FE_ORDEN_PEND'),
                'num_comprobante' => '',
                'id_pago' => $id_pago
            ]);


            return $this->responseAjaxSuccess("", $ext_id);
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salío mal creando la información de Factura Electrónica");
        }
    }

    public function crearEntregaOrden($precio, $dsc_lugar, $dsc_contacto, $orden)
    {
        try {
            $ext_id = DB::table('entrega_orden')->insertGetId([
                'id' => null,
                'orden' => $orden,
                'precio' => $precio,
                'descripcion_lugar' => $dsc_contacto,
                'contacto' => $dsc_contacto,
                'estado' => SisEstadoController::getIdEstadoByCodGeneral('ENTREGA_PREPARACION_PEND'),
                'encargado' => null
            ]);
            return $this->responseAjaxSuccess("", $ext_id);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal creando el envío");
        }
    }

    public function anularOrden(Request $request)
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $idOrden = $request->input("idOrden");
        $lineas =  $request->input("lineas");
        $enteros = array_map('intval', $lineas);
        if ($idOrden == null || $idOrden == 0) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        $orden = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->select('orden.*', 'sis_estado.cod_general')
            ->where('orden.id', '=', $idOrden)->get()->first();


        if ($orden == null) {
            return $this->responseAjaxServerError("Número de orden invalido", []);
        }

        if ($orden->caja_cerrada == 'S') {
            return $this->responseAjaxServerError("La caja de esta orden ya fue cerrada.", []);
        }


        if ($orden->cod_general == 'ORD_ANULADA') {
            return $this->responseAjaxServerError("La orden no se encuentra en un estado para ser anulada", []);
        }


        DB::beginTransaction();

        DB::table('orden')
            ->where('id', '=', $idOrden)
            ->update(['estado' =>  SisEstadoController::getIdEstadoByCodGeneral('ORD_ANULADA')]);

        $res = $this->devolverInventarioOrden($idOrden, $enteros);

        /**PENDIENTE ENVIAR LOS OTROS PRODUCTOS A DESECHOS */

        if (!$res['estado']) {
            DB::rollBack();
            return $this->responseAjaxServerError($res['mensaje'], []);
        }
        DB::commit();


        return $this->responseAjaxSuccess("Pedido anulado correctamente.", $idOrden);
    }

    public function recargarOrdenes()
    {
        if (!$this->validarSesion("facFac")) {
            return $this->responseAjaxServerError("No tienes permisos para realizar la acción.", []);
        }

        $ordenes = DB::table('orden')
            ->leftjoin('sis_estado', 'sis_estado.id', '=', 'orden.estado')
            ->leftjoin('mesa', 'mesa.id', '=', 'orden.mesa')
            ->select('orden.*', 'sis_estado.nombre as estadoOrden', 'sis_estado.cod_general', 'mesa.numero_mesa')
            ->where('orden.cierre_caja', '=', CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal()))
            ->orderBy('orden.fecha_inicio', 'DESC')->get();

        foreach ($ordenes as $o) {
            $o->detalles = DB::table('detalle_orden')->where('orden', '=', $o->id)->get();
            $o->entrega = DB::table('entrega_orden')->leftjoin('sis_estado', 'sis_estado.id', '=', 'entrega_orden.estado')
                ->select(
                    'entrega_orden.*',
                    'sis_estado.nombre as estadoOrden',
                    'sis_estado.cod_general'
                )
                ->where('entrega_orden.orden', '=', $o->id)->get()->first();
            $o->idOrdenEnc = encrypt($o->id);
        }
        return $this->responseAjaxSuccess("", $ordenes);
    }

    public function devolverInventarioOrden($id_orden, $lineas)
    {
        $detalles = DB::table('detalle_orden')->select('detalle_orden.*')->where('orden', '=', $id_orden)->whereIn('detalle_orden.id', $lineas)->get();
        foreach ($detalles as $d) {
            if ($d->tipo_producto == 'R') {
                $res = $this->devolverInventarioMateriaPrima($d,$d->cantidad_preparada);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            } else if ($d->tipo_producto == 'E') {
                $res = $this->devolverInventarioProductoExterno($d,$d->cantidad_pagada);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            } else if ($d->tipo_producto == 'PROMO') {
                $cantidadLinea = $d->cantidad;

                $prodE = DB::table('det_grupo_promocion')
                    ->join('producto_externo', 'producto_externo.id', '=', 'det_grupo_promocion.producto')
                    ->select('det_grupo_promocion.*', 'producto_externo.codigo_barra', 'producto_externo.id as idProd', 'producto_externo.nombre as nomProd')
                    ->where('det_grupo_promocion.tipo', '=', "E")
                    ->where('det_grupo_promocion.grupo_promocion', '=', $d->codigo_producto)->get();

                foreach ($prodE as $p) {
                    $cantProdAux = $cantidadLinea * $p->cantidad;
                    $res = $this->devolverInventarioProductoExternoPromo($cantProdAux, $p->codigo_barra);
                    if (!$res['estado']) {
                        return $this->responseAjaxServerError($res['mensaje'], []);
                    }
                }

                $prodR = DB::table('det_grupo_promocion')
                    ->join('producto_menu', 'producto_menu.id', '=', 'det_grupo_promocion.producto')
                    ->select('det_grupo_promocion.*', 'producto_menu.codigo as codigo_producto', 'producto_menu.descripcion as nomProd')
                    ->where('det_grupo_promocion.tipo', '=', "R")
                    ->where('det_grupo_promocion.grupo_promocion', '=', $d->codigo_producto)->get();

                foreach ($prodR as $p) {
                    $cantProdAux = $cantidadLinea * $p->cantidad;
                    $res = $this->devolverInventarioMateriaPrimaPromo($cantProdAux, $p->codigo_producto, $id_orden, $d->id);
                    if (!$res['estado']) {
                        return $this->responseAjaxServerError($res['mensaje'], []);
                    }
                }
            }
        }
        return $this->responseAjaxSuccess("", "");
    }

    public function restarProductoMenuMatPrima($id_detalle_orden, $cantidad_rebajar)
    {
        try {
            $d = DB::table('detalle_orden')->select('detalle_orden.*')->where('id', '=', $id_detalle_orden)->get()->first();
            if ($d->tipo_producto == 'R') {
                $res = $this->restarInventarioMateriaPrima($cantidad_rebajar, $d->codigo_producto, $d->nombre_producto, $d->id);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            } else if ($d->tipo_producto == 'PROMO') {
                $cantidadLinea = $cantidad_rebajar;

                $prodR = DB::table('det_grupo_promocion')
                    ->join('producto_menu', 'producto_menu.id', '=', 'det_grupo_promocion.producto')
                    ->select('det_grupo_promocion.*', 'producto_menu.codigo as codigo_producto', 'producto_menu.descripcion as nomProd')
                    ->where('det_grupo_promocion.tipo', '=', "R")
                    ->where('det_grupo_promocion.grupo_promocion', '=', $d->codigo_producto)->get();

                foreach ($prodR as $p) {
                    $cantProdAux = $cantidadLinea * $p->cantidad;
                    $res = $this->restarInventarioMateriaPrima($p->cantidad, $p->codigo, $p->nomProd, $d->id);
                    if (!$res['estado']) {
                        return $this->responseAjaxServerError($res['mensaje'], []);
                    }
                }
            }

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salió mal.", "");
        }
    }

    private function restarProductoExternoInventario($id_detalle_orden, $cantidad_rebajar)
    {
        try {
            $d = DB::table('detalle_orden')->select('detalle_orden.*')->where('id', '=', $id_detalle_orden)->get()->first();

            if ($d->tipo_producto == 'E') {
                $res = $this->restarInventarioProductoExterno($cantidad_rebajar, $d->codigo_producto);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }

                $res2 = $this->restarInventarioMateriaPrimaPE($cantidad_rebajar, $d->codigo_producto, $d->nombre_producto);
                if (!$res2['estado']) {
                    return $this->responseAjaxServerError($res2['mensaje'], []);
                }
            } else if ($d->tipo_producto == 'PROMO') {
                $prodE = DB::table('det_grupo_promocion')
                    ->join('producto_externo', 'producto_externo.id', '=', 'det_grupo_promocion.producto')
                    ->select('det_grupo_promocion.*', 'producto_externo.codigo_barra', 'producto_externo.id as idProd', 'producto_externo.nombre as nomProd')
                    ->where('det_grupo_promocion.tipo', '=', "E")
                    ->where('det_grupo_promocion.grupo_promocion', '=', $d->codigo_producto)->get();

                foreach ($prodE as $p) {
                    $res = $this->restarInventarioProductoExternoProm($p->cantidad * $cantidad_rebajar, $p->idProd, $p->codigo_barra);
                    if (!$res['estado']) {
                        return $this->responseAjaxServerError($res['mensaje'], []);
                    }

                    $res2 = $this->restarInventarioMateriaPrimaPE($p->cantidad * $cantidad_rebajar, $p->codigo_barra, $p->nomProd);
                    if (!$res2['estado']) {
                        return $this->responseAjaxServerError($res2['mensaje'], []);
                    }
                }
            }
            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError("Algo salió mal.", "");
        }
    }

    private function restarInventarioOrden($id_orden)
    {
        $detalles = DB::table('detalle_orden')->select('detalle_orden.*')->where('orden', '=', $id_orden)->get();
        foreach ($detalles as $d) {

            if ($d->tipo_producto == 'R') {
                $res = $this->restarInventarioMateriaPrima($d->cantidad, $d->codigo_producto, $d->nombre_producto, $d->id);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }
            } else if ($d->tipo_producto == 'E') {
                $res = $this->restarInventarioProductoExterno($d->cantidad, $d->codigo_producto);
                if (!$res['estado']) {
                    return $this->responseAjaxServerError($res['mensaje'], []);
                }

                $res2 = $this->restarInventarioMateriaPrimaPE($d->cantidad, $d->codigo_producto, $d->nombre_producto);
                if (!$res2['estado']) {
                    return $this->responseAjaxServerError($res2['mensaje'], []);
                }
            } else if ($d->tipo_producto == 'PROMO') {

                $prodE = DB::table('det_grupo_promocion')
                    ->join('producto_externo', 'producto_externo.id', '=', 'det_grupo_promocion.producto')
                    ->select('det_grupo_promocion.*', 'producto_externo.codigo_barra', 'producto_externo.id as idProd', 'producto_externo.nombre as nomProd')
                    ->where('det_grupo_promocion.tipo', '=', "E")
                    ->where('det_grupo_promocion.grupo_promocion', '=', $d->codigo_producto)->get();

                foreach ($prodE as $p) {

                    $res = $this->restarInventarioProductoExternoProm($p->cantidad * $d->cantidad, $p->idProd, $p->codigo_barra);
                    if (!$res['estado']) {
                        return $this->responseAjaxServerError($res['mensaje'], []);
                    }

                    $res2 = $this->restarInventarioMateriaPrimaPE($p->cantidad * $d->cantidad, $p->codigo_barra, $p->nomProd);
                    if (!$res2['estado']) {
                        return $this->responseAjaxServerError($res2['mensaje'], []);
                    }
                }

                $prodR = DB::table('det_grupo_promocion')
                    ->join('producto_menu', 'producto_menu.id', '=', 'det_grupo_promocion.producto')
                    ->select('det_grupo_promocion.*', 'producto_menu.codigo', 'producto_menu.descripcion as nomProd')
                    ->where('det_grupo_promocion.tipo', '=', "R")
                    ->where('det_grupo_promocion.grupo_promocion', '=', $d->codigo_producto)->get();

                foreach ($prodR as $p) {
                    $res = $this->restarInventarioMateriaPrima($p->cantidad, $p->codigo, $p->nomProd, $d->id);
                    if (!$res['estado']) {
                        return $this->responseAjaxServerError($res['mensaje'], []);
                    }
                }
            }
        }
        return $this->responseAjaxSuccess("", "");
    }

    public function devolverInventarioMateriaPrima($detalle, $cantidad_rebajar)
    {
        try {
            $fechaActual = date("Y-m-d H:i:s");
            $cantidadRebajar = $cantidad_rebajar;
            $codigoProductoRebajar = $detalle->codigo_producto;
            $mt_prod = DB::table('mt_x_producto')
                ->leftjoin('producto_menu', 'producto_menu.id', '=', 'mt_x_producto.producto')
                ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto.materia_prima')
                ->select('mt_x_producto.*', "materia_prima.nombre as nombreMp")
                ->where('producto_menu.codigo', '=', $codigoProductoRebajar)
                ->get();

            foreach ($mt_prod as $i) {
                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario + ($i->cantidad * $cantidadRebajar)]);


                $cantAux =  (($cantidadInventario ?? 0) -  ($i->cantidad * $cantidadRebajar));

                $detalleMp =  'Materia Prima : ' . $i->nombreMp .
                    ' | Detalle : Aumento, devolución al inventario por anulación de factura. Producto  : ' . $codigoProductoRebajar . '-' . $detalle->nombre_producto;

                DB::table('bit_materia_prima')->insert([
                    'id' => null,
                    'usuario' => session('usuario')['id'],
                    'materia_prima' => $i->materia_prima,
                    'detalle' => $detalleMp,
                    'cantidad_anterior' =>  $cantidadInventario ?? 0,
                    'cantidad_ajuste' => ($i->cantidad * $cantidadRebajar),
                    'cantidad_nueva' =>  $cantAux,
                    'fecha' => $fechaActual,
                    'sucursal' => $this->getUsuarioSucursal()
                ]);
            }
            $extras = [];
            $extras =  DB::table('extra_detalle_orden')
                ->join('extra_producto_menu', 'extra_producto_menu.id', '=', 'extra_detalle_orden.extra')
                ->leftjoin('materia_prima', 'materia_prima.id', '=', 'extra_producto_menu.materia_prima')
                ->leftjoin('producto_menu', 'producto_menu.id', '=', 'extra_producto_menu.producto')
                ->select(
                    "extra_producto_menu.materia_prima",
                    "materia_prima.nombre as nombreMp",
                    "extra_detalle_orden.descripcion_extra",
                    "extra_producto_menu.cant_mp",
                    'producto_menu.nombre as nomProd'
                )
                ->where('orden', '=', $detalle->orden)
                ->where('detalle', '=', $detalle->id)->get();

            foreach ($extras  as $e) {
                if ($e->materia_prima != null) {

                    $cantidadInventario = DB::table('mt_x_sucursal')
                        ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                        ->where('mt_x_sucursal.materia_prima', '=', $e->materia_prima)
                        ->sum('mt_x_sucursal.cantidad');

                    DB::table('mt_x_sucursal')
                        ->where('sucursal', '=', $this->getUsuarioSucursal())
                        ->where('materia_prima', '=', $e->materia_prima)
                        ->update(['cantidad' =>  $cantidadInventario - ($e->cant_mp * $detalle->cantidad)]);

                    $cantAux =  (($cantidadInventario ?? 0) -  ($e->cant_mp * $cantidadRebajar));

                    $detalleMp =  'Materia Prima : ' . $e->nombreMp .
                        ' | Detalle : Aumento, devolución al inventario por anulación de factura. Extra  : ' . $e->descripcion_extra . '. Producto :' . $detalle->nombre_producto;

                    DB::table('bit_materia_prima')->insert([
                        'id' => null,
                        'usuario' => session('usuario')['id'],
                        'materia_prima' => $e->materia_prima,
                        'detalle' => $detalleMp,
                        'cantidad_anterior' =>  $cantidadInventario ?? 0,
                        'cantidad_ajuste' => ($e->cant_mp * $cantidadRebajar),
                        'cantidad_nueva' =>  $cantAux,
                        'fecha' => $fechaActual,
                        'sucursal' => $this->getUsuarioSucursal()
                    ]);
                }
            }

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    public function devolverInventarioMateriaPrimaPromo($cantidadDetalleLinea, $codigo_producto, $idOrden, $idDetalle)
    {

        $fechaActual = date("Y-m-d H:i:s");
        $codigoProductoRebajar = $codigo_producto;
        $mt_prod = DB::table('mt_x_producto')
            ->leftjoin('producto_menu', 'producto_menu.id', '=', 'mt_x_producto.producto')
            ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto.materia_prima')

            ->select('mt_x_producto.*', 'producto_menu.nombre as nomProd', 'materia_prima.nombre as nombreMp')
            ->where('producto_menu.codigo', '=', $codigoProductoRebajar)
            ->get();

        foreach ($mt_prod as $i) {
            $cantidadInventario = DB::table('mt_x_sucursal')
                ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                ->sum('mt_x_sucursal.cantidad');

            DB::table('mt_x_sucursal')
                ->where('sucursal', '=', $this->getUsuarioSucursal())
                ->where('materia_prima', '=', $i->materia_prima)
                ->update(['cantidad' =>  $cantidadInventario + ($i->cantidad * $cantidadDetalleLinea)]);

            $cantAux =  (($cantidadInventario ?? 0) +  ($i->cantidad * $cantidadDetalleLinea));

            $detalleMp =  'Materia Prima : ' . $i->nombreMp .
                ' | Detalle : Aumento, devolución al inventario por anulación de factura. Producto  : ' . $i->nomProd . '-' . $codigoProductoRebajar;

            DB::table('bit_materia_prima')->insert([
                'id' => null,
                'usuario' => session('usuario')['id'],
                'materia_prima' => $i->materia_prima,
                'detalle' => $detalleMp,
                'cantidad_anterior' =>  $cantidadInventario ?? 0,
                'cantidad_ajuste' => ($i->cantidad * $cantidadDetalleLinea),
                'cantidad_nueva' =>  $cantAux,
                'fecha' => $fechaActual,
                'sucursal' => $this->getUsuarioSucursal()
            ]);
        }
        $extras = [];
        $extras =  DB::table('extra_detalle_orden')
            ->join('extra_producto_menu', 'extra_producto_menu.id', '=', 'extra_detalle_orden.extra')
            ->leftjoin('materia_prima', 'materia_prima.id', '=', 'extra_producto_menu.materia_prima')
            ->leftjoin('producto_menu', 'producto_menu.id', '=', 'extra_producto_menu.producto')
            ->select(
                "extra_producto_menu.materia_prima",
                "materia_prima.nombre as nombreMp",
                "extra_detalle_orden.descripcion_extra",
                "extra_producto_menu.cant_mp",
                'producto_menu.nombre as nomProd'
            )
            ->where('orden', '=', $idOrden)
            ->where('detalle', '=', $idDetalle)->get();

        foreach ($extras  as $e) {
            if ($e != null && $e->materia_prima != null) {

                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $e->materia_prima ?? "0")
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $e->materia_prima ?? "0")
                    ->update(['cantidad' =>  $cantidadInventario + ($e->cant_mp ?? "0" * $cantidadDetalleLinea)]);

                $cantAux =  (($cantidadInventario ?? 0) +  ($e->cant_mp ?? "0" * $cantidadDetalleLinea));

                $detalleMp =  'Materia Prima : ' . $e->nombreMp ?? "" .
                    ' | Detalle : Aumento, devolución al inventario por anulación de factura. Extra  : ' . $e->descripcion_extra . '. Producto :' . $codigo_producto;

                DB::table('bit_materia_prima')->insert([
                    'id' => null,
                    'usuario' => session('usuario')['id'],
                    'materia_prima' => $e->materia_prima,
                    'detalle' => $detalleMp,
                    'cantidad_anterior' =>  $cantidadInventario ?? 0,
                    'cantidad_ajuste' => ($e->cant_mp ?? "0" * $cantidadDetalleLinea),
                    'cantidad_nueva' =>  $cantAux,
                    'fecha' => $fechaActual,
                    'sucursal' => $this->getUsuarioSucursal()
                ]);
            }
        }

        return $this->responseAjaxSuccess("", "");
    }

    private function restarInventarioMateriaPrimaPE($cantidad, $codigo_producto, $nombre_producto)
    {
        try {
            $cantidadRebajar = $cantidad;
            $codigoProductoRebajar = $codigo_producto;
            $mt_prod = DB::table('mt_x_producto_ext')
                ->leftjoin('producto_externo', 'producto_externo.id', '=', 'mt_x_producto_ext.producto')
                ->select('mt_x_producto_ext.*')
                ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
                ->get();
            $fechaActual = date("Y-m-d H:i:s");

            foreach ($mt_prod as $i) {
                $materia_prima = DB::table('materia_prima')
                    ->select('materia_prima.*')
                    ->where('materia_prima.id', '=', $i->materia_prima)
                    ->get()->first();

                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario - $i->cantidad]);

                $cantAux =  (($cantidadInventario ?? 0) -  ($i->cantidad * $cantidadRebajar));

                $detalleMp =  'Materia Prima : ' . $materia_prima->nombre .
                    ' | Detalle : Rebajo por venta producto  : ' . $codigoProductoRebajar . '-' . $nombre_producto;

                DB::table('bit_materia_prima')->insert([
                    'id' => null,
                    'usuario' => session('usuario')['id'],
                    'materia_prima' => $i->materia_prima,
                    'detalle' => $detalleMp,
                    'cantidad_anterior' =>  $cantidadInventario ?? 0,
                    'cantidad_ajuste' => ($i->cantidad * $cantidadRebajar),
                    'cantidad_nueva' =>  $cantAux,
                    'fecha' => $fechaActual,
                    'sucursal' => $this->getUsuarioSucursal()
                ]);
            }

            /* foreach ($detalle['extras'] ?? [] as $e) {
                $e = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');
                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario - $i->cantidad]);
            }*/

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    private function restarInventarioMateriaPrima($cantidad, $codigoProducto, $nombreProducto, $detalleId)
    {
        try {
            $fechaActual = date("Y-m-d H:i:s");
            $cantidadRebajar = $cantidad;
            $codigoProductoRebajar = $codigoProducto;
            $mt_prod = DB::table('mt_x_producto')
                ->leftjoin('producto_menu', 'producto_menu.id', '=', 'mt_x_producto.producto')
                ->select('mt_x_producto.*')
                ->where('producto_menu.codigo', '=', $codigoProductoRebajar)
                ->get();

            foreach ($mt_prod as $i) {
                $materia_prima = DB::table('materia_prima')
                    ->select('materia_prima.*')
                    ->where('materia_prima.id', '=', $i->materia_prima)
                    ->get()->first();

                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario - ($i->cantidad * $cantidadRebajar)]);
                $cantAux =  (($cantidadInventario ?? 0) -  ($i->cantidad * $cantidadRebajar));

                $detalleMp =  'Materia Prima : ' . $materia_prima->nombre .
                    ' | Detalle : Rebajo por venta producto  : ' . $codigoProductoRebajar . '-' . $nombreProducto;

                DB::table('bit_materia_prima')->insert([
                    'id' => null,
                    'usuario' => session('usuario')['id'],
                    'materia_prima' => $i->materia_prima,
                    'detalle' => $detalleMp,
                    'cantidad_anterior' =>  $cantidadInventario ?? 0,
                    'cantidad_ajuste' => ($i->cantidad * $cantidadRebajar),
                    'cantidad_nueva' =>  $cantAux,
                    'fecha' => $fechaActual,
                    'sucursal' => $this->getUsuarioSucursal()
                ]);
            }
            $extras = DB::table('extra_detalle_orden')
                ->select('extra_detalle_orden.*')
                ->where('extra_detalle_orden.detalle', '=', $detalleId)->get();

            foreach ($extras as $e) {
                $extraAux = DB::table('extra_producto_menu')
                    ->select('extra_producto_menu.*')
                    ->where('extra_producto_menu.id', '=', $e->extra)->get()->first();
                if ($extraAux != null) {
                    if ($extraAux->materia_prima != null) {

                        $materia_prima = DB::table('materia_prima')
                            ->select('materia_prima.*')
                            ->where('materia_prima.id', '=', $extraAux->materia_prima)
                            ->get()->first();

                        $cantidadInventario = DB::table('mt_x_sucursal')
                            ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                            ->where('mt_x_sucursal.materia_prima', '=', $extraAux->materia_prima)
                            ->sum('mt_x_sucursal.cantidad') ?? 0;
                        $cantAux =  (($cantidadInventario ?? 0) - ($extraAux->cant_mp  * $cantidadRebajar));
                        DB::table('mt_x_sucursal')
                            ->where('sucursal', '=', $this->getUsuarioSucursal())
                            ->where('materia_prima', '=', $extraAux->materia_prima)
                            ->update(['cantidad' =>  $cantAux]);

                        $detalleMp = 'Materia Prima : ' . $materia_prima->nombre .
                            ' | Detalle : Rebajo por venta de extra : ' . $e->descripcion_extra .
                            ' | Producto :' . $codigoProductoRebajar . '-' . $nombreProducto;
                        DB::table('bit_materia_prima')->insert([
                            'id' => null,
                            'usuario' => session('usuario')['id'],
                            'materia_prima' => $extraAux->materia_prima,
                            'detalle' => $detalleMp,
                            'cantidad_anterior' =>  $cantidadInventario ?? 0,
                            'cantidad_ajuste' => ($extraAux->cant_mp  * $cantidadRebajar),
                            'cantidad_nueva' =>  $cantAux,
                            'fecha' => $fechaActual,
                            'sucursal' => $this->getUsuarioSucursal()
                        ]);
                    }
                }
            }

            return $this->responseAjaxSuccess("", "");
        } catch (QueryException $ex) {
            return $this->responseAjaxServerError('Algo salio mal...', []);
        }
    }

    private function restarInventarioProductoExterno($cantidad, $codigo_producto)
    {
        $cantidadRebajar = $cantidad;
        $codigoProductoRebajar = $codigo_producto;
        $inventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->select('pe_x_sucursal.*')
            ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->get()->first();
        $cantidadInventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
            ->sum('pe_x_sucursal.cantidad');

        if ($cantidadInventario <  $cantidadRebajar) {
            return $this->responseAjaxServerError('La cantidad solicitada es mayor al inventario de productos producidos.', []);
        } else if ($cantidadInventario == $cantidadRebajar) {
            DB::table('pe_x_sucursal')
                ->where('id', '=', $inventario->id)
                ->update(['cantidad' => 0]);
        } else if ($cantidadInventario > $cantidadRebajar) {
            DB::table('pe_x_sucursal')
                ->where('id', '=', $inventario->id)
                ->update(['cantidad' => $inventario->cantidad - $cantidadRebajar]);
        }

        return $this->responseAjaxSuccess("", "");
    }

    private function restarInventarioProductoExternoProm($cantidad, $idProducto, $codigoProductoRebajar)
    {
        $cantidadRebajar = $cantidad;

        $inventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->select('pe_x_sucursal.*')
            ->where('producto_externo.id', '=', $idProducto)
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->get()->first();
        $cantidadInventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->where('producto_externo.id', '=', $idProducto)
            ->sum('pe_x_sucursal.cantidad');

        if ($cantidadInventario <  $cantidadRebajar) {
            return $this->responseAjaxServerError('La cantidad solicitada es mayor al inventario de productos producidos. Revisa el inventario de productos externos', []);
        } else if ($cantidadInventario == $cantidadRebajar) {
            DB::table('pe_x_sucursal')
                ->where('id', '=', $inventario->id)
                ->update(['cantidad' => 0]);
        } else if ($cantidadInventario > $cantidadRebajar) {
            DB::table('pe_x_sucursal')
                ->where('id', '=', $inventario->id)
                ->update(['cantidad' => $inventario->cantidad - $cantidadRebajar]);
        }

        return $this->responseAjaxSuccess("", "");
    }

    public function devolverInventarioProductoExternoPromo($cantidadDetalleLinea, $codigo_producto)
    {
        $fechaActual = date("Y-m-d H:i:s");
        $cantidadRebajar = $cantidadDetalleLinea;
        $codigoProductoRebajar = $codigo_producto;
        $inventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->select('pe_x_sucursal.*')
            ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->get()->first();
        $cantidadInventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
            ->sum('pe_x_sucursal.cantidad');

        DB::table('pe_x_sucursal')
            ->where('id', '=', $inventario->id)
            ->update(['cantidad' => $inventario->cantidad + $cantidadRebajar]);

        $mt_prod = DB::table('mt_x_producto_ext')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'mt_x_producto_ext.producto')
            ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto_ext.materia_prima')
            ->select("mt_x_producto_ext.*", "materia_prima.nombre as nombreMp")
            ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
            ->get();

        foreach ($mt_prod as $i) {
            $cantidadInventario = DB::table('mt_x_sucursal')
                ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                ->sum('mt_x_sucursal.cantidad');

            DB::table('mt_x_sucursal')
                ->where('sucursal', '=', $this->getUsuarioSucursal())
                ->where('materia_prima', '=', $i->materia_prima)
                ->update(['cantidad' =>  $cantidadInventario + ($i->cantidad * $cantidadRebajar)]);

            $cantAux =  (($cantidadInventario ?? 0) +  ($i->cantidad * $cantidadRebajar));

            $detalleMp =  'Materia Prima : ' . $i->nombreMp .
                ' | Detalle : Aumento, devolución al inventario por anulación de factura. Producto  : ' . $codigoProductoRebajar;

            DB::table('bit_materia_prima')->insert([
                'id' => null,
                'usuario' => session('usuario')['id'],
                'materia_prima' => $i->materia_prima,
                'detalle' => $detalleMp,
                'cantidad_anterior' =>  $cantidadInventario ?? 0,
                'cantidad_ajuste' => ($i->cantidad * $cantidadRebajar),
                'cantidad_nueva' =>  $cantAux,
                'fecha' => $fechaActual,
                'sucursal' => $this->getUsuarioSucursal()
            ]);
        }

        return $this->responseAjaxSuccess("", "");
    }

    public function devolverInventarioProductoExterno($detalle)
    {
        $fechaActual = date("Y-m-d H:i:s");
        $cantidadRebajar = $detalle->cantidad;
        $codigoProductoRebajar = $detalle->codigo_producto;
        $inventario = DB::table('pe_x_sucursal')
            ->leftjoin('producto_externo', 'producto_externo.id', '=', 'pe_x_sucursal.producto_externo')
            ->select('pe_x_sucursal.*')
            ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
            ->where('pe_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
            ->get()->first();

        if ($inventario != null) {

            DB::table('pe_x_sucursal')
                ->where('id', '=', $inventario->id)
                ->update(['cantidad' => $inventario->cantidad + $cantidadRebajar]);

            $mt_prod = DB::table('mt_x_producto_ext')
                ->leftjoin('producto_externo', 'producto_externo.id', '=', 'mt_x_producto_ext.producto')
                ->leftjoin('materia_prima', 'materia_prima.id', '=', 'mt_x_producto_ext.materia_prima')
                ->where('producto_externo.codigo_barra', '=', $codigoProductoRebajar)
                ->select("mt_x_producto_ext.*", "producto_externo.*", "materia_prima.nombre as nombreMp")
                ->get();

            foreach ($mt_prod as $i) {
                $cantidadInventario = DB::table('mt_x_sucursal')
                    ->where('mt_x_sucursal.sucursal', '=', $this->getUsuarioSucursal())
                    ->where('mt_x_sucursal.materia_prima', '=', $i->materia_prima)
                    ->sum('mt_x_sucursal.cantidad');

                DB::table('mt_x_sucursal')
                    ->where('sucursal', '=', $this->getUsuarioSucursal())
                    ->where('materia_prima', '=', $i->materia_prima)
                    ->update(['cantidad' =>  $cantidadInventario + ($i->cantidad * $cantidadRebajar)]);

                $cantAux =  (($cantidadInventario ?? 0) +  ($i->cantidad * $cantidadRebajar));

                $detalleMp =  'Materia Prima : ' . $i->nombreMp .
                    ' | Detalle : Devolución al inventario por anulación de factura de venta. Producto  : ' . $codigoProductoRebajar . '-' . $detalle->nombre_producto;

                DB::table('bit_materia_prima')->insert([
                    'id' => null,
                    'usuario' => session('usuario')['id'],
                    'materia_prima' => $i->materia_prima,
                    'detalle' => $detalleMp,
                    'cantidad_anterior' =>  $cantidadInventario ?? 0,
                    'cantidad_ajuste' => ($i->cantidad * $cantidadRebajar),
                    'cantidad_nueva' =>  $cantAux,
                    'fecha' => $fechaActual,
                    'sucursal' => $this->getUsuarioSucursal()
                ]);
            }
        }

        return $this->responseAjaxSuccess("", "");
    }

    public function cargarOrdenGestion(Request $request)
    {
        $idOrden = $request->input('idOrden');
        $orden = DB::table('orden')
            ->select(
                "orden.id",
                "orden.subtotal",
                "orden.total",
                "orden.pagado",
                "orden.mto_pagado",
                "orden.numero_orden",
                "orden.mesa",
                "orden.nombre_cliente",
                DB::raw("'' as codigo_descuento"),
                DB::raw('0 as envio'),
                DB::raw('false as nueva')
            )
            ->where('orden.id', "=", $idOrden)
            ->get()->first();

        if ($orden == null) {
            return $this->responseAjaxServerError("La orden no existe", "");
        }
        if ($orden->pagado == 1) {
            return $this->responseAjaxServerError("La orden fue pagada", "");
        }

        $detallesAux = DB::table('detalle_orden')->where('detalle_orden.orden', "=", $orden->id)
            ->get();

        $detallesA = [];
        foreach ($detallesAux as $d) {
            $producto = [];
            if ($d->tipo_producto == 'R') {
                $producto = ProductosMenuController::getIdByCodigo($d->codigo_producto);
            } else if ($d->tipo_producto == 'E') {
                $producto = ProductosExternosController::getIdByCodigo($d->codigo_producto);
            } else if ($d->tipo_producto == 'PROMO') {
                $producto = MantGrupoPromocionesController::getProdPromoByCodigo($d->codigo_producto);
            }

            $extras = DB::table('extra_detalle_orden')
                ->where('orden', '=', $orden->id)
                ->where('detalle', '=', $d->id)->get();

            $detalleObj = [
                'id' => $d->id,
                'cantidad' => $d->cantidad,
                'impuestoServicio' => $d->servicio_mesa,
                'impuesto' => $producto != null ? $producto->valorImpuesto : 0,
                'precio_unidad' => $d->precio_unidad,
                'total' => $d->total,
                'observacion' => $d->observacion,
                'tipo' => $d->tipo_producto,
                'tipoComanda' => '',
                'cantidad_preparada' => $d->cantidad_preparada,
                'cantidad_pagada' => $d->cantidad_pagada,
                'producto' =>  $producto ?? [],
                'extras' => $extras ?? []
            ];

            array_push($detallesA, $detalleObj);
        }
        $orden->detalles = $detallesA;
        return $this->responseAjaxSuccess("", $orden);
    }

    public function iniciarOrden(Request $request)
    {

        $orden = $request->input("orden");
        $detalles = $request->input("detalles");

        $resValidar = $this->validarOrden($orden, $detalles);
        if (!$resValidar['estado']) {
            return $this->responseAjaxServerError($resValidar['mensaje'], []);
        }
        $cliente = $orden['cliente'];

        $asignarMontosDetalles = FacturacionController::asignarMontosDetalles($detalles,  0, null);
        $detallesGuardar = $asignarMontosDetalles['detalles'];
        $infoFacturacionFinal = $asignarMontosDetalles;
        $fechaActual = date("Y-m-d H:i:s");


        try {
            DB::beginTransaction();

            $numOrden = $this->getConsecutivoNuevaOrdenSucursal($this->getUsuarioSucursal());
            $id_orden = DB::table('orden')->insertGetId([
                'id' => null,
                'numero_orden' => $numOrden,
                'tipo' => null,
                'fecha_fin' => $fechaActual,
                'fecha_inicio' => $fechaActual,
                'cliente' => null,
                'nombre_cliente' => $cliente,
                'estado' => null,
                'total' => $infoFacturacionFinal['total'],
                'total_con_descuento' => $infoFacturacionFinal['total_pagar'],
                'subtotal' => $infoFacturacionFinal['subtotal'],
                'impuesto' => $infoFacturacionFinal['montoImpuestos'],
                'descuento' => 0,
                'cajero' => session('usuario')['id'],
                'monto_sinpe' => 0,
                'monto_tarjeta' =>  0,
                'monto_efectivo' => 0,
                'factura_electronica' => 'N',
                'ingreso' => null,
                'sucursal' => $this->getUsuarioSucursal(),
                'fecha_preparado' => null,
                'fecha_entregado' => null,
                'cocina_terminado' => 'N',
                'bebida_terminado' => 'N',
                'caja_cerrada' => 'N',
                'pagado' => 0,
                'estado' => SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION'),
                'periodo' => date('Y'),
                'cierre_caja' => CajaController::getIdCaja(session('usuario')['id'], $this->getUsuarioSucursal()),
                'monto_envio' => 0,
                'ind_requiere_envio' => false,
                'info_descuento' => '',
                'mesa' => $orden['mesa'] == "-1" ? null : $orden['mesa']
            ]);
            $this->aumentarConsecutivoOrden($this->getUsuarioSucursal());

            $id_comanda = DB::table('orden_comanda')->insertGetId([
                'orden' => $id_orden,
                'num_comanda' => $numOrden // Primera comanda para la orden recién creada
            ]);

            $serv = new EntregasOrdenController();
            $servEstOrd = new EstOrdenController();

            $resCargaEst = $servEstOrd->creaEstOrden($id_orden, SisEstadoController::getIdEstadoByCodGeneral('ORD_EN_PREPARACION'), null);

            if (!$resCargaEst['estado']) {
                DB::rollBack();
                return $this->responseAjaxServerError($resCargaEst['mensaje'], []);
            }

            foreach ($detallesGuardar as $det) {
                $d = $det['detalle'];
                if ($d['cantidad'] > 0) {
                    $producto = $d['producto'];
                    $idComanda = null;
                    if ($d['tipo'] == 'R') {
                        $idComanda = ProductosMenuController::getIdComandaByCodigoSucursal($producto['codigo'], $this->getUsuarioSucursal());
                    } else if ($d['tipo'] == 'E') {
                        $idComanda = ProductosExternosController::getIdComandaByCodigoSucursal($producto['codigo'], $this->getUsuarioSucursal());
                    }
                    $det_id = DB::table('detalle_orden')->insertGetId([
                        'id' => null,
                        'cantidad' => $d['cantidad'],
                        'nombre_producto' => $producto['nombre'],
                        'codigo_producto' => $producto['codigo'],
                        'precio_unidad' => $d['precio_unidad'],
                        'impuesto' => $det['montoIva'],
                        'total' => $det['totalGen'],
                        'descuento' => $det['descuento'],
                        'subtotal' => $det['subTotal'],
                        'total_extras' => $det['totalExtras'],
                        'orden' => $id_orden,
                        'tipo_producto' => $d['tipo'],
                        'servicio_mesa' => $d['impuestoServicio'],
                        'monto_servicio' => $det['montoImpuestoServicioMesa'],
                        'observacion' => $d['observacion'],
                        'tipo_comanda' => $d['tipoComanda'],
                        'cod_promocion' => $d['tipo'],
                        'comanda' => $idComanda,
                        'cantidad_preparada' => 0
                    ]);
                    foreach ($d['extras'] ?? [] as $extra) {
                        $ext_id = DB::table('extra_detalle_orden')->insertGetId([
                            'id' => null,
                            'detalle' => $det_id,
                            'extra' => $extra['id'],
                            'orden' => $id_orden,
                            'descripcion_extra' => $extra['descripcion'],
                            'total' => $extra['precio'] * $d['cantidad'],
                            'id_producto' => $extra['idProd'],
                            'tipo_producto' => $extra['tipo_producto']
                        ]);
                    }

                    DB::table('detalle_orden_comanda')->insert([
                        'orden_comanda' => $id_comanda,
                        'detalle_orden' => $det_id,
                        'cantidad' => $d['cantidad'],
                        'comanda' => $idComanda,
                        'fecha_ingreso' => $fechaActual,
                        'usuario_gestion' => session('usuario')['id']
                    ]);
                }
            }

            DB::commit();
            return $this->responseAjaxSuccess("Se inicio la orden " . $numOrden . " correctamente", $id_orden);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salío mal.");
        }
    }

    public function actualizarOrden(Request $request)
    {
        $orden = $request->input("orden");
        $detalles = $request->input("detalles");

        // Validar si la orden existe
        $ordenExistente = DB::table('orden')->where('id', '=', $orden['id'])->first();
        if (!$ordenExistente) {
            return $this->responseAjaxServerError('La orden no existe.', $orden['id']);
        }

        // Verificar si la orden ya está pagada
        if ($ordenExistente->estado == 'pagada') {
            return $this->responseAjaxServerError('La orden ya ha sido pagada y no puede ser modificada.', $orden['id']);
        }

        // Validar la orden y detalles
        $resValidar = $this->validarOrden($orden, $detalles);
        if (!$resValidar['estado']) {
            return $this->responseAjaxServerError($resValidar['mensaje'], $orden['id']);
        }

        $cliente = $orden['cliente'];


        $asignarMontosDetalles = FacturacionController::asignarMontosDetalles($detalles, 0, null);
        $detallesGuardar = $asignarMontosDetalles['detalles'];
        $infoFacturacionFinal = $asignarMontosDetalles;
        $fechaActual = date("Y-m-d H:i:s");
        try {
            DB::beginTransaction();

            // Actualizar la tabla 'orden'
            DB::table('orden')->where("id", "=", $orden['id'])->update([
                'nombre_cliente' => $orden['cliente'],
                'mesa' => $orden['mesa'] == "-1" ? null : $orden['mesa'],
                'total' => $infoFacturacionFinal['total'],
                'total_con_descuento' => $infoFacturacionFinal['total'],
                'subtotal' => $infoFacturacionFinal['subtotal'],
                'mto_impuesto_servicio' => $infoFacturacionFinal['montoImpuestoServicioMesa'],
                'impuesto' => $infoFacturacionFinal['montoImpuestos'],
                'descuento' => 0
            ]);

            // Obtener detalles anteriores de la orden
            $detallesAnteriores = DB::table('detalle_orden')->where('orden', '=', $orden['id'])->get()->keyBy('id')->toArray();

            $comandaCabezaCreada = false;
            $id_comanda = null;
            // Iterar sobre los detalles nuevos y guardar/actualizar
            foreach ($detallesGuardar as $det) {
                $d = $det['detalle'];

                if ($d['cantidad'] > 0) {
                    if ($d['tipo'] == 'R') {
                        $id_comanda = ProductosMenuController::getIdComandaByCodigoSucursal($d['producto']['codigo'], $this->getUsuarioSucursal());
                    } else if ($d['tipo'] == 'E') {
                        $id_comanda = ProductosExternosController::getIdComandaByCodigoSucursal($d['producto']['codigo'], $this->getUsuarioSucursal());
                    }

                    if (!$comandaCabezaCreada) {
                        $id_orden_comanda = DB::table('orden_comanda')->insertGetId([
                            'orden' => $orden['id'],
                            'num_comanda' =>  $ordenExistente->numero_orden // Primera comanda para la orden recién creada
                        ]);
                        $comandaCabezaCreada = true;
                    }
                    // Validar que la cantidad no sea menor a la cantidad preparada
                    if ($d['nueva'] == 0) {
                        $idDetAux = $d['id'];
                        $detalleAnterior = DB::table('detalle_orden')->where("id", "=", $idDetAux)->first();

                        // Verificar que la nueva cantidad no sea menor a la cantidad preparada
                        if ($d['cantidad'] < $detalleAnterior->cantidad_preparada) {
                            DB::rollBack();
                            return $this->responseAjaxServerError(
                                "La cantidad para el producto '" . $detalleAnterior->nombre_producto .
                                    "' no puede ser menor a lo que ya está preparado. Cantidad preparada: " . $detalleAnterior->cantidad_preparada,
                                $orden['id']
                            );
                        }

                        // Verificar que la nueva cantidad no sea menor a la cantidad pagada
                        if ($d['cantidad'] < $detalleAnterior->cantidad_pagada) {
                            DB::rollBack();
                            return $this->responseAjaxServerError(
                                "La cantidad para el producto '" . $detalleAnterior->nombre_producto .
                                    "' no puede ser menor a lo que ya se ha pagado. Cantidad pagada: " . $detalleAnterior->cantidad_pagada,
                                $orden['id']
                            );
                        }

                        if ($comandaCabezaCreada) {
                            if ($d['cantidad'] > $detalleAnterior->cantidad) {

                                DB::table('detalle_orden_comanda')->insert([
                                    'orden_comanda' => $id_orden_comanda,
                                    'detalle_orden' => $idDetAux,
                                    'cantidad' => $d['cantidad'] - $detalleAnterior->cantidad,
                                    'comanda' => $id_comanda,
                                    'fecha_ingreso' => $fechaActual,
                                    'usuario_gestion' => session('usuario')['id']
                                ]);
                                DB::table('orden')->where("id", "=", $orden['id'])->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("ORD_EN_PREPARACION")]);
                            } else if ($d['cantidad'] < $detalleAnterior->cantidad) {
                                $detalleComandaMasReciente = DB::table('detalle_orden_comanda')
                                    ->where('detalle_orden', '=', $idDetAux)
                                    ->orderBy('id', 'desc')
                                    ->first();

                                if ($detalleComandaMasReciente) {
                                    $nuevaCantidad = $detalleComandaMasReciente->cantidad - ($detalleAnterior->cantidad - $d['cantidad']);
                                    if ($nuevaCantidad <= 0) {
                                        // Si la nueva cantidad es menor o igual a cero, eliminar la entrada
                                        DB::table('detalle_orden_comanda')->where('id', '=', $detalleComandaMasReciente->id)->delete();
                                    } else {
                                        // Actualizar la cantidad en la entrada más reciente
                                        DB::table('detalle_orden_comanda')
                                            ->where('id', '=', $detalleComandaMasReciente->id)
                                            ->update(['cantidad' => $nuevaCantidad]);
                                    }
                                }
                            }
                        }

                        // Actualizar detalle existente
                        DB::table('detalle_orden')->where("id", "=", $idDetAux)->update([
                            'cantidad' => $d['cantidad'],
                            'impuesto' => $det['montoIva'],
                            'total' => $det['totalGen'],
                            'descuento' => $det['descuento'],
                            'total_extras' => $det['totalExtras'],
                            'observacion' => $d['observacion'],
                            'subtotal' => $det['subTotal']
                        ]);

                        DB::table('extra_detalle_orden')->where('detalle', '=', $idDetAux)->delete();
                        unset($detallesAnteriores[$idDetAux]);
                    } else {
                        // Si el detalle es nuevo, insertarlo
                        $producto = $d['producto'];
                        $idDetAux = DB::table('detalle_orden')->insertGetId([
                            'id' => null,
                            'cantidad' => $d['cantidad'],
                            'nombre_producto' => $producto['nombre'],
                            'codigo_producto' => $producto['codigo'],
                            'precio_unidad' => $d['precio_unidad'],
                            'impuesto' => $det['montoIva'],
                            'total' => $det['totalGen'],
                            'descuento' => $det['descuento'],
                            'subtotal' => $det['subTotal'],
                            'total_extras' => $det['totalExtras'],
                            'orden' => $orden['id'],
                            'tipo_producto' => $d['tipo'],
                            'servicio_mesa' => $d['impuestoServicio'],
                            'monto_servicio' => $det['montoImpuestoServicioMesa'],
                            'observacion' => $d['observacion'],
                            'tipo_comanda' => $d['tipoComanda'],
                            'comanda' => $id_comanda,
                            'cod_promocion' => $d['tipo'],
                            'cantidad_preparada' => 0
                        ]);

                        if ($comandaCabezaCreada) {
                            DB::table('detalle_orden_comanda')->insert([
                                'orden_comanda' => $id_orden_comanda,
                                'detalle_orden' => $idDetAux,
                                'cantidad' => $d['cantidad'],
                                'comanda' => $id_comanda,
                                'fecha_ingreso' => $fechaActual,
                                'usuario_gestion' => session('usuario')['id']
                            ]);
                        }

                        DB::table('orden')->where("id", "=", $orden['id'])->update(['estado' => SisEstadoController::getIdEstadoByCodGeneral("ORD_EN_PREPARACION")]);
                    }

                    foreach ($d['extras'] ?? [] as $extra) {
                        DB::table('extra_detalle_orden')->insertGetId([
                            'id' => null,
                            'detalle' => $idDetAux,
                            'extra' => $extra['id'],
                            'orden' => $orden['id'],
                            'descripcion_extra' => $extra['descripcion'],
                            'total' => $extra['precio'] * $d['cantidad'],
                            'id_producto' => $extra['idProd'],
                            'tipo_producto' => $extra['tipo_producto']
                        ]);
                    }
                }
            }

            // Eliminar detalles anteriores que no estén en la lista de detalles nuevos y que tengan cantidad_preparada = 0
            foreach ($detallesAnteriores as $idDetAux => $detalleAnterior) {
                if ($detalleAnterior->cantidad_preparada > 0) {
                    DB::rollBack();
                    return $this->responseAjaxServerError(
                        "La línea para el producto '" . $detalleAnterior->nombre_producto .
                            "' no puede ser eliminada porque ya esta preparada. Cantidad preparada: " . $detalleAnterior->cantidad_preparada,
                        $orden['id']
                    );
                } else if ($detalleAnterior->cantidad_pagada > 0) {
                    DB::rollBack();
                    return $this->responseAjaxServerError(
                        "La línea para el producto '" . $detalleAnterior->nombre_producto .
                            "' no puede ser eliminada porque ya esta pagada. Cantidad pagada: " . $detalleAnterior->cantidad_pagada,
                        $orden['id']
                    );
                }
                DB::table('detalle_orden')->where("id", "=", $idDetAux)->delete();
                DB::table('detalle_orden_comanda')->where("detalle_orden", "=", $idDetAux)->delete();
                DB::table('extra_detalle_orden')->where('detalle', '=', $idDetAux)->delete();
            }

            DB::commit();
            return $this->responseAjaxSuccess("Se actualizó la orden correctamente", $orden['id']);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salió mal.", $orden['id']);
        }
    }

    public function pagarOrden(Request $request)
    {
        $orden = $request->input("orden");
        $infoFE = $request->input("infoFE");
        $detalles = $request->input("detalles");
        $envio = $request->input("envio");

        $montoSinpe = $request->input("mto_sinpe", 0);
        $montoEfectivo = $request->input("mto_efectivo", 0);
        $montoTarjeta = $request->input("mto_tarjeta", 0);

        $totalPagos = $montoSinpe + $montoEfectivo + $montoTarjeta;

        $ordenExistente = DB::table('orden')->where('id', '=', $orden['id'])->first();
        if (!$ordenExistente) {
            return $this->responseAjaxServerError('La orden no existe.', []);
        }

        if ($orden['codigo_descuento'] != null) {
            $verificaCodDesc = FacturacionController::verificaCodDescuento($orden['codigo_descuento']['codigo']);
            if (!$verificaCodDesc['estado']) {
                return $this->responseAjaxServerError($verificaCodDesc['mensajes'], []);
            }
        }

        // Verificar si la orden ya está pagada
        if ($ordenExistente->estado == 'pagada') {
            return $this->responseAjaxServerError('La orden ya ha sido pagada y no puede ser modificada.', []);
        }

        $resValidar = $this->validarOrden($orden, $detalles);
        if (!$resValidar['estado']) {
            return $this->responseAjaxServerError($resValidar['mensaje'], []);
        }

        $resValidarFE = $this->validarInfoFe($infoFE);
        if (!$resValidarFE['estado']) {
            return $this->responseAjaxServerError($resValidarFE['mensaje'], []);
        }

        $resValidarEnvio = $this->validarInfoEnvio($envio);
        if (!$resValidarEnvio['estado']) {
            return $this->responseAjaxServerError($resValidarEnvio['mensaje'], []);
        }

        if ($envio['incluye_envio'] == 'true') {
            if ($ordenExistente->mto_pagado > 0) {
                return $this->responseAjaxServerError("No se puede incluir el envío en una factura con pagos parciales.", []);
            }
        }

        $detallesOrden = DB::table('detalle_orden')
            ->where('orden', '=', $orden['id'])
            ->get();

        $unSoloPago = true;
        foreach ($detallesOrden as $detalleAux) {

            $detalleRecibido = collect($detalles)->firstWhere('id', $detalleAux->id);

            if ($detalleRecibido) {
                if ($detalleRecibido['cantidad'] < $detalleAux->cantidad) {
                    $unSoloPago = false;
                    break;
                }
            } else {
                $unSoloPago = false;
                break;
            }
        }
        if ($envio['incluye_envio'] == 'true') {
            if (!$unSoloPago) {
                return $this->responseAjaxServerError("No se puede incluir el envío en una factura con pagos parciales.", []);
            }
        }

        $cubreMontoCompleto = true;
        foreach ($detallesOrden as $detalleAux) {
            $nuevaCantidadPagada = $detalleAux->cantidad_pagada;

            $detalleRecibido = collect($detalles)->firstWhere('id', $detalleAux->id);

            if ($detalleRecibido) {
                $nuevaCantidadPagada = $nuevaCantidadPagada + $detalleRecibido['cantidad'];
            }

            if ($nuevaCantidadPagada < $detalleAux->cantidad) {
                $cubreMontoCompleto = false;
                break;
            }
        }

        $asignarMontosDetalles = FacturacionController::asignarMontosDetalles($detalles, $orden['codigo_descuento'], $envio);
        if ($asignarMontosDetalles == null) {
            return $this->responseAjaxServerError("Error calculando el monto de la factura.", []);
        }

        $detallesGuardar = $asignarMontosDetalles['detalles'];

        if ($totalPagos != $asignarMontosDetalles['total']) {
            return $this->responseAjaxServerError('El monto total pagado no coincide con el total de los detalles seleccionados.', []);
        }

        $cliente = $orden['cliente'];
        $fechaActual = date("Y-m-d H:i:s");

        try {

            DB::beginTransaction();

            $pagoOrdenId = DB::table('pago_orden')->insertGetId([
                'orden' => $orden['id'],
                'nombre_cliente' => $cliente,
                'monto_tarjeta' => $montoTarjeta,
                'monto_efectivo' => $montoEfectivo,
                'monto_sinpe' => $montoSinpe,
                'total' => $asignarMontosDetalles['total'],
                'subtotal' => $asignarMontosDetalles['subtotal'],
                'iva' => $asignarMontosDetalles['montoImpuestos'],
                'descuento' => $asignarMontosDetalles['descuento'],
                'fecha_pago' => $fechaActual,
                'cod_promocion' => $asignarMontosDetalles['codDescuento'],
                'impuesto_servicio' => $asignarMontosDetalles['montoImpuestoServicioMesa']
            ]);


            foreach ($detallesGuardar as $det) {
                $d = $det['detalle'];
                if ($d['cantidad'] > 0) {
                    if (!$det['linea_envio']) {
                        $cantidadPagadaActual = DB::table('detalle_orden')
                            ->where("id", "=", $d['id'])
                            ->value('cantidad_pagada') ?? 0;

                        $nuevaCantidadPagada = $cantidadPagadaActual + $d['cantidad'];

                        DB::table('detalle_orden')->where("id", "=", $d['id'])->update([
                            'cantidad_pagada' => $nuevaCantidadPagada,
                            'descuento' => $det['descuento'],
                            'subtotal' => $det['subTotal'],
                            'monto_servicio' => $det['montoImpuestoServicioMesa'],
                            'total' => $det['totalGen'],
                            'impuesto' => $det['montoIva'],
                            'cod_promocion' => $asignarMontosDetalles['codDescuento'],
                        ]);

                        $res =  $this->restarProductoExternoInventario($d['id'], $d['cantidad']);
                        if (!$res['estado']) {
                            return $this->responseAjaxServerError($res['mensaje'], []);
                        }
                    }
                    DB::table('detalle_pago_orden')->insert([
                        'pago_orden' => $pagoOrdenId,
                        'detalle_orden' => (!$det['linea_envio']) ? $d['id'] : null,
                        'cantidad_pagada' => $d['cantidad'],
                        'subtotal' => $det['subTotal'],
                        'mto_impuesto_servicio' => $det['montoImpuestoServicioMesa'],
                        'dsc_linea' => ($d['producto']['nombre'] ?? 'Producto'),
                        'descuento' => $det['descuento'],
                        'iva' => $det['montoIva'],
                        'total' => $det['totalGen']
                    ]);
                }
            }

            if ($unSoloPago) {
                DB::table('orden')->where("id", "=", $orden['id'])->update([
                    'mto_pagado' => (float)($totalPagos),
                    'pagado' => 1,
                    'total' => ($asignarMontosDetalles['total']),
                    'total_con_descuento' => ($asignarMontosDetalles['total_pagar']),
                    'subtotal' => ($asignarMontosDetalles['subtotal']),
                    'impuesto' => ($asignarMontosDetalles['montoImpuestos']),
                    'mto_impuesto_servicio' => ($asignarMontosDetalles['montoImpuestoServicioMesa']),
                    'descuento' => ($asignarMontosDetalles['descuento']),
                    'monto_sinpe' => $montoSinpe,
                    'monto_efectivo' => $montoEfectivo,
                    'monto_tarjeta' => $montoTarjeta
                ]);
                $cubreMontoCompleto = true;
            } else {
                DB::table('orden')->where("id", "=", $orden['id'])->update([
                    'pagado' =>  $cubreMontoCompleto ? 1 : 0,
                    'mto_pagado' => (float)($ordenExistente->mto_pagado + $totalPagos)
                ]);
                if ($cubreMontoCompleto) {
                    $sumaPagos = DB::table('pago_orden')
                        ->where('orden', '=', $orden['id'])
                        ->selectRaw('
                        SUM(subtotal) as total_subtotal,
                        SUM(total) as total_total,
                        SUM(iva) as total_iva,
                        SUM(descuento) as total_descuento,
                        SUM(impuesto_servicio) as total_servicio_mesa,
                        SUM(monto_sinpe) as total_sinpe,
                        SUM(monto_efectivo) as total_efectivo,
                        SUM(monto_tarjeta) as total_tarjeta
                    ')
                        ->first();

                    // Actualizar el encabezado de la orden con los nuevos totales basados en los pagos
                    DB::table('orden')->where("id", "=", $orden['id'])->update([
                        'subtotal' => $sumaPagos->total_subtotal,
                        'total' => $sumaPagos->total_total,
                        'total_con_descuento' => $sumaPagos->total_total,
                        'impuesto' => $sumaPagos->total_iva,
                        'descuento' => $sumaPagos->total_descuento,
                        'mto_impuesto_servicio' => $sumaPagos->total_servicio_mesa,
                        'monto_sinpe' => $sumaPagos->total_sinpe,
                        'monto_efectivo' => $sumaPagos->total_efectivo,
                        'monto_tarjeta' => $sumaPagos->total_tarjeta
                    ]);
                }
            }

            $serv = new EntregasOrdenController();
            if ($envio['incluye_envio'] == 'true') {
                $resCreaEnvio = $serv->crearEntregaOrden(
                    $envio["precio"],
                    $envio["descripcion_lugar"],
                    $envio["contacto"],
                    $envio["descripcion_lugar_maps"],
                    $ordenExistente->id
                );
                if (!$resCreaEnvio['estado']) {
                    DB::rollBack();
                    return $this->responseAjaxServerError($resCreaEnvio['mensaje'], []);
                }
            }

            // Crear la factura electrónica si es necesario
            if ($infoFE['incluyeFE'] == 'true') {
                $resCreaFe = $this->crearInfoFacturaElectronica(
                    $infoFE["info_ced_fe"],
                    $infoFE["info_nombre_fe"],
                    $infoFE["info_correo_fe"],
                    $ordenExistente->id,
                    $pagoOrdenId
                );
                if (!$resCreaFe['estado']) {
                    DB::rollBack();
                    return $this->responseAjaxServerError($resCreaFe['mensaje'], []);
                }
            }

            if ($asignarMontosDetalles['idDesc'] != null) {
                CodigosPromocionController::usarPromocion($asignarMontosDetalles['idDesc']);
            }


            DB::commit();
            $numFactura = (!$unSoloPago ? ($ordenExistente->numero_orden . ':' . $pagoOrdenId) : $ordenExistente->id);
            return $this->responseAjaxSuccess("Pedido creado correctamente.", [
                'variasFacturas' => !$unSoloPago,
                'pago_completo' => $cubreMontoCompleto,
                'numFactura' => $numFactura,
                'idOrden' => $ordenExistente->id
            ]);
        } catch (QueryException $ex) {
            DB::rollBack();
            return $this->responseAjaxServerError("Algo salió mal.", $orden['id']);
        }
    }
}
