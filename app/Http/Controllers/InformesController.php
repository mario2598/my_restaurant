<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;
class InformesController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {
       
        setlocale(LC_ALL, "es_ES");
    }
    
    public function index(){

    }

    public function goResumenContable(){
        if(!$this->validarSesion("informes")){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if(!$this->usuarioAdministrador()){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        
        $filtros = [
            'sucursal' => 'T',
            'hasta' => "",
            'desde' => "",
        ];

        
        $data = [
             'menus'=> $this->cargarMenus(),
            'filtros' =>$filtros,
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('informes.resumenContable',compact('data'));
    }

    public function goResumenContableFiltro(Request $request){
        if(!$this->validarSesion("informes")){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if(!$this->usuarioAdministrador()){
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');
        
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde,
        ];
        
        $data = [
             'menus'=> $this->cargarMenus(),
            'resumen' =>$this->resumenContable($desde,$hasta,$filtroSucursal),
            'sucursales' => $this->getSucursales(),
            'filtros' =>$filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('informes.resumenContable',compact('data'));
    }


  
}
