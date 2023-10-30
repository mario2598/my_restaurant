<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class BitacoraMovimientosFondoController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "bitMovFondos";
    public function __construct()
    {
       //Prueba git
        setlocale(LC_ALL, "es_CR");
    }
    
    public function index(){

    }

    public function goMovimientosFondos(){
        if(!$this->validarSesion($this->codigo_pantalla)){
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
            'movimientos' => [],
            'menus'=> $this->cargarMenus(),
            'parametros_generales'=> $this->getParametrosGenerales(),
            'filtros' =>$filtros,
            'sucursales' => $this->getSucursales(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('bitacora.fondos.monitorMovimientosFondo',compact('data'));

    }

   
    public function goMovimientosFondosFiltro(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
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
        
        $movimientos = DB::table('bitacora_modificacion')
        ->leftjoin('usuario', 'usuario.usuario', '=', 'bitacora_modificacion.usuario')
        ->leftjoin('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
        ->select('bitacora_modificacion.*','sucursal.descripcion as sucDes','sucursal.id as sucId');

        if(!$this->isNull($filtroSucursal) && $filtroSucursal != 'T'){
            $movimientos = $movimientos->where('sucursal.id','=',$filtroSucursal);
        }

        if(!$this->isNull($desde)){
            $movimientos = $movimientos->where('bitacora_modificacion.fecha','>=',$desde);
        }

        if(!$this->isNull($hasta)){
            $mod_date = strtotime($hasta."+ 1 days");
            $mod_date = date("Y-m-d",$mod_date);
            $movimientos = $movimientos->where('bitacora_modificacion.fecha','<',$mod_date);
        }
        $movimientos = $movimientos->orderBy('bitacora_modificacion.id','DESC')->get();
        foreach($movimientos as $m){
            $m->fecha = $this->fechaFormat($m->fecha);
        }
        $filtros = [
            'sucursal' => $filtroSucursal,
            'hasta' => $hasta,
            'desde' => $desde
        ];
        $data = [
             'menus'=> $this->cargarMenus(),
            'movimientos' => $movimientos,
            'sucursales' => $this->getSucursales(),
            'filtros' =>$filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('bitacora.fondos.monitorMovimientosFondoReporte',compact('data'));
    }

    public function actualizar(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            echo '-1';
            exit;
        }
        
        if(!$this->usuarioAdministrador()){
            echo '-1';
            exit;
        }

        $filtroSucursal = $request->input('sucursal');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');

        $movimientos = DB::table('bitacora_modificacion')
        ->leftjoin('usuario', 'usuario.usuario', '=', 'bitacora_modificacion.usuario')
        ->leftjoin('sucursal', 'sucursal.id', '=', 'usuario.sucursal')
        ->select('bitacora_modificacion.*','sucursal.descripcion as sucDes','sucursal.id as sucId');

        if(!$this->isNull($filtroSucursal) && $filtroSucursal != 'T'){
            $movimientos = $movimientos->where('sucursal.id','=',$filtroSucursal);
        }

        if(!$this->isNull($desde)){
            $movimientos = $movimientos->where('bitacora_modificacion.fecha','>=',$desde);
        }

        if(!$this->isNull($hasta)){
            $mod_date = strtotime($hasta."+ 1 days");
            $mod_date = date("Y-m-d",$mod_date);
            $movimientos = $movimientos->where('bitacora_modificacion.fecha','<',$mod_date);
        }
        $movimientos = $movimientos->orderBy('bitacora_modificacion.id','DESC')->get();
        foreach($movimientos as $m){
            $m->fecha = $this->fechaFormat($m->fecha);
        }

        $data = [
            'movimientos' => $movimientos
        ];
        return view('bitacora.layout.tbodyBitacoraMovimientosFondo',compact('data'));
    }


}
