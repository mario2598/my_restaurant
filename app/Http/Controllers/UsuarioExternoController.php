<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
use App\Http\Controllers\FacturacionController;

class UsuarioExternoController extends Controller
{
    use SpaceUtil;


    public function __construct()
    {
    }

    public function goMenu()
    {
        if (!$this->validarSesion("usuExtMnu")) {
            return redirect('/');
        }
        $contro = new FacturacionController();
        $tipos =  $contro->getTiposCategoriasProductos();
        foreach ($tipos as $i => $t) {
            if (count($t['categorias']) < 1) {
                unset($tipos[$i]);
            }
        }

        $prodcutosMenu = DB::table("producto_menu")
            ->where('producto_menu.estado', "A")
            ->where('pm_x_sucursal.sucursal', $this->getUsuarioSucursal()) 
            ->join('impuesto', 'producto_menu.impuesto', '=', 'impuesto.id')
            ->join('pm_x_sucursal', 'producto_menu.id', '=', 'pm_x_sucursal.producto_menu')
            ->select('producto_menu.id', 'producto_menu.codigo', 'producto_menu.nombre',
             'producto_menu.precio','producto_menu.descripcion', 'impuesto.impuesto as impuesto', 'producto_menu.tipo_comanda', 'producto_menu.url_imagen')->get();

        $data = [
            'menus' => $this->cargarMenus(),
            'tipos' => $tipos,
            'prodcutosMenu' => $prodcutosMenu,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('usuarioExterno.menu', compact('data'));
    }
}
