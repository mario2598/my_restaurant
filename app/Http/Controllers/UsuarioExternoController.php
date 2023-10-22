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
        $categorias =  $contro->getCategoriasTodosProductos($this->getUsuarioSucursal());
       
        $data = [
            'menus' => $this->cargarMenus(),
            'categorias' =>$categorias,
            'impuestos' => $this->getImpuestos(),
            'proveedores' => $this->getProveedores(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('usuarioExterno.menu', compact('data'));
    }

    public function goMenuMobile()
    {
        $contro = new FacturacionController();
        $categorias =  $contro->getCategoriasTodosProductosMobile();
       
        $data = [
            'categorias' =>$categorias
        ];
        return view('usuarioExterno.menuMobile', compact('data'));
    }

    public function cargarTiposGeneral(Request $request)
    {
        if (!$this->validarSesion("usuExtMnu")) {
            return redirect('/');
            return $this->responseAjaxServerError("No tienes permisos", "");
        }
        $contro = new FacturacionController();
        $categorias =  $contro->getCategoriasTodosProductos($this->getUsuarioSucursal());


        return $this->responseAjaxSuccess("", $categorias);
    }

    public function cargarTiposGeneralMobile(Request $request)
    {
        $contro = new FacturacionController();
        $categorias =  $contro->getCategoriasTodosProductosMobile();


        return $this->responseAjaxSuccess("", $categorias);
    }
}
