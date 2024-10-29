<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
class BitacoraMovimientosInventarioController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "bitMovInv";
    public function __construct()
    {
       
        setlocale(LC_ALL, "es_ES");
    }
    
    public function index(){

    }

    public function goMovimientosInv(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            $this->setMsjSeguridad();
            return redirect('/');
        }   
        
        if(!$this->usuarioAdministrador()){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        
        $filtros = [
            'despacho' => 0,
            'destino' => 'T',
            'tipo_movimiento' => 'T',
            'estado' => 'TT',
            'hasta' => '',
            'desde' => ''
        ];
        
        $data = [
            'movimientos' => [],
            'menus'=> $this->cargarMenus(),
            'parametros_generales'=> $this->getParametrosGenerales(),
            'filtros' =>$filtros,
            'tipos_movimiento' => $this->getTiposMovimiento(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('bitacora.inventario.monitorMovimientosInv',compact('data'));

    }

   
    public function goMovimientosInvFiltro(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        
        if(!$this->usuarioAdministrador()){
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $despacho = $request->input('despacho');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');
        $destino = $request->input('destino');
        $tipo_movimiento = $request->input('tipo_movimiento');
        $estado = $request->input('estado');

        $movimientos = DB::table('movimiento')
        ->leftjoin('tipo_movimiento', 'tipo_movimiento.id', '=', 'movimiento.tipo_movimiento')
        ->leftjoin('sucursal as despacho', 'despacho.id', '=', 'movimiento.sucursal_inicio')
        ->leftjoin('sucursal as destino', 'destino.id', '=', 'movimiento.sucursal_fin')
        ->leftjoin('usuario as entrega', 'entrega.id', '=', 'movimiento.entrega')
        ->select('movimiento.estado','movimiento.id','movimiento.fecha','tipo_movimiento.codigo as codigo_movimiento',
        'tipo_movimiento.descripcion as descripcion_movimiento',
        'despacho.descripcion as despacho','destino.descripcion as detino',
        'entrega.usuario as nombre_usuario');
       

        if(!$this->isNull($tipo_movimiento) && $tipo_movimiento != 'T'){
            $movimientos = $movimientos->where('movimiento.tipo_movimiento','=',$tipo_movimiento);
        }

        if(!$this->isNull($despacho) && $despacho != 'T'){
            $movimientos = $movimientos->where('movimiento.sucursal_inicio','=',$despacho);
        }

        if(!$this->isNull($destino) && $destino != 'T'){
            $movimientos = $movimientos->where('movimiento.sucursal_fin','=',$destino);
        }

        if(!$this->isNull($estado) && $estado != 'TT'){
            $movimientos = $movimientos->where('movimiento.estado','=',$estado);
        }

        if(!$this->isNull($desde)){
            $movimientos = $movimientos->where('movimiento.fecha','>=',$desde);
        }

        if(!$this->isNull($hasta)){
            $mod_date = strtotime($hasta."+ 1 days");
            $mod_date = date("Y-m-d",$mod_date);
            $movimientos = $movimientos->where('movimiento.fecha','<',$mod_date);
        }

        $movimientos = $movimientos->orderBy('movimiento.id','DESC')->get();

        foreach($movimientos as $m){
            $m->fecha = $this->fechaFormat($m->fecha);
        }

        $filtros = [
            'despacho' => $despacho,
            'destino' => $destino,
            'tipo_movimiento' => $tipo_movimiento,
            'estado' => $estado,
            'hasta' => $hasta,
            'desde' => $desde
        ];

        $data = [
            'movimientos' => $movimientos,
            'menus'=> $this->cargarMenus(),
            'parametros_generales'=> $this->getParametrosGenerales(),
            'filtros' =>$filtros,
            'tipos_movimiento' => $this->getTiposMovimiento(),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];

        return view('bitacora.inventario.monitorMovimientosInvReporte',compact('data'));
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

        $despacho = $request->input('desp');
        $hasta = $request->input('hasta');
        $desde = $request->input('desde');
        $destino = $request->input('dest');
        $tipo_movimiento = $request->input('tip_mov');
        $estado = $request->input('est');

        $movimientos = DB::table('movimiento')
        ->leftjoin('tipo_movimiento', 'tipo_movimiento.id', '=', 'movimiento.tipo_movimiento')
        ->leftjoin('sucursal as despacho', 'despacho.id', '=', 'movimiento.sucursal_inicio')
        ->leftjoin('sucursal as destino', 'destino.id', '=', 'movimiento.sucursal_fin')
        ->leftjoin('usuario as entrega', 'entrega.id', '=', 'movimiento.entrega')
        ->select('movimiento.estado','movimiento.id','movimiento.fecha','tipo_movimiento.codigo as codigo_movimiento',
        'tipo_movimiento.descripcion as descripcion_movimiento',
        'despacho.descripcion as despacho','destino.descripcion as detino',
        'entrega.usuario as nombre_usuario');
       

        if(!$this->isNull($tipo_movimiento) && $tipo_movimiento != 'T'){
            $movimientos = $movimientos->where('movimiento.tipo_movimiento','=',$tipo_movimiento);
        }

        if(!$this->isNull($despacho) && $despacho != 'T'){
            $movimientos = $movimientos->where('movimiento.sucursal_inicio','=',$despacho);
        }

        if(!$this->isNull($destino) && $destino != 'T'){
            $movimientos = $movimientos->where('movimiento.sucursal_fin','=',$destino);
        }

        if(!$this->isNull($estado) && $estado != 'TT'){
            $movimientos = $movimientos->where('movimiento.estado','=',$estado);
        }

        if(!$this->isNull($desde)){
            $movimientos = $movimientos->where('movimiento.fecha','>=',$desde);
        }

        if(!$this->isNull($hasta)){
            $mod_date = strtotime($hasta."+ 1 days");
            $mod_date = date("Y-m-d",$mod_date);
            $movimientos = $movimientos->where('movimiento.fecha','<',$mod_date);
        }

        $movimientos = $movimientos->orderBy('movimiento.id','DESC')->get();

        foreach($movimientos as $m){
            $m->fecha = $this->fechaFormat($m->fecha);
        }

        $data = [
            'movimientos' => $movimientos
        ];
        
        return view('bitacora.layout.tbodyBitacoraMovimientosInv',compact('data'));
    }


}
