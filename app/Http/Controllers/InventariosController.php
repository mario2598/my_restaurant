<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\SpaceUtil;
use Illuminate\Database\QueryException;
class InventariosController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "bodInventarios";
    public function __construct()
    {
       
        setlocale(LC_ALL, "es_CR");
    }
    
    public function index(){

    }

    public function cargarInventario(Request $request){
        $sucursal = $request->input('id');
        if($sucursal== null || $sucursal < 1){
            echo '-1';
            exit;
        }

        $data = [
            'inventario' =>  $this->getInventario($sucursal)
        ];


        return view('bodega.inventario.layout.tablaInventario',compact('data'));
    }

    public function goInventarios(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if(!$this->usuarioAdministrador()){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        
        $filtros = [
            'sucursal' => '',
           
        ];
        
        $data = [
            'menus'=> $this->cargarMenus(),
            'filtros' =>$filtros,
            'inventarios' => [],
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('bodega.inventario.inventarios',compact('data'));
    }

    public function goTrasladar(){
       
        if(!$this->validarSesion("invTras")){
            $this->setMsjSeguridad();
            return redirect('/');
        }

        
        $filtros = [
            'sucursal' => '',
           
        ];
        
        $data = [
            'menus'=> $this->cargarMenus(),
            'filtros' =>$filtros,
            'inventarios' => [],
            'sucursales' => $this->getSucursalesAndBodegas(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('bodega.inventario.traslado',compact('data'));
    }

    public function goInventariosFiltro(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        if(!$this->usuarioAdministrador()){
            $this->setMsjSeguridad();
            return redirect('/');
        }

        $filtroSucursal = $request->input('sucursal');
       
        if($this->isNull($filtroSucursal) || $filtroSucursal == '-1'){
            $this->setError("Buscar inventario","Debe seleccionar la sucursal");
            return redirect('bodega/inventario/inventarios');
        }
  
        $filtros = [
            'sucursal' => $filtroSucursal,
        ];
        
        $data = [
            'menus'=> $this->cargarMenus(),
            'inventarios' =>$this->getInventario($filtroSucursal),
            'sucursales' => $this->getSucursalesAndBodegas(),
            'filtros' =>$filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('bodega.inventario.inventarios',compact('data'));
    }

    private function getInventario($sucursal){

        if($sucursal== null || $sucursal < 1){
            return [];
        }

        $inventarios = DB::table('producto')
        ->leftjoin('inventario', 'inventario.producto', '=', 'inventario.producto')
        ->leftjoin('sucursal', 'sucursal.id', '=', 'inventario.sucursal')
        ->leftjoin('categoria', 'categoria.id', '=', 'producto.categoria')
        ->select('producto.codigo_barra', 'producto.nombre', 'categoria.categoria')
        ->groupBy('producto.codigo_barra', 'producto.nombre', 'categoria.categoria')
        ->where('inventario.sucursal','=',$sucursal)
        ->where('inventario.cantidad','>',0)
        ->get();
        
        foreach($inventarios as $i){

            $this->getCantidadInventario($i,$i->codigo_barra,$sucursal);
        }
  
        foreach($inventarios as $key => $i){
            if($i->cantidad == null || $i->cantidad < 1){
                unset($inventarios[$key]);
            }
        }
        return $inventarios;
    }

    private function getCantidadInventario($i,$codigo,$sucursal){
        $i->cantidad = DB::table('inventario')
        ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
        ->leftjoin('sucursal', 'sucursal.id', '=', 'inventario.sucursal')
        ->select(DB::raw('SUM(inventario.cantidad) as cantidad'))
        ->where('inventario.sucursal','=',$sucursal)
        ->where('producto.codigo_barra','=',$codigo)->get()->first()->cantidad;

        return $i->cantidad;
    }

    private function getLotes($sucursal,$producto){
        $lotes = DB::table('inventario')
        ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
        ->leftjoin('sucursal', 'sucursal.id', '=', 'inventario.sucursal')
        ->leftjoin('lote', 'lote.id', '=', 'inventario.lote')
        ->select('inventario.*','lote.fecha_vencimiento')
        ->where('inventario.sucursal','=',$sucursal)
        ->where('producto.codigo_barra','=',$producto)
        ->orderBy('lote.fecha_vencimiento', 'asc')
        ->get();


        return $lotes;
    }

    private function getInventarioPorLote($sucursal){

        if($sucursal== null || $sucursal < 1){
            return [];
        }

        $inventarios = DB::table('inventario')
        ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
        ->leftjoin('sucursal', 'sucursal.id', '=', 'inventario.sucursal')
        ->leftjoin('lote', 'lote.id', '=', 'inventario.lote')
        ->select('producto.codigo_barra', 'producto.nombre', 'categoria.categoria')
        ->groupBy('producto.codigo_barra', 'producto.nombre', 'categoria.categoria')
        ->where('inventario.sucursal','=',$sucursal)
        ->where('inventario.cantidad','>',0)
        ->get();
        
        foreach($inventarios as $i){
            $i->cantidad = DB::table('inventario')
            ->leftjoin('producto', 'producto.id', '=', 'inventario.producto')
            ->leftjoin('sucursal', 'sucursal.id', '=', 'inventario.sucursal')
            ->select(DB::raw('SUM(inventario.cantidad) as cantidad'))
            ->where('inventario.sucursal','=',$sucursal)
            ->where('producto.codigo_barra','=',$i->codigo_barra)->get()->first()->cantidad;
        }
        return $inventarios;
    }

    /**
     * Este proceso recibe una lista de pedidos, se busca en el inventario los lotes mas viejos 
     * y se busca llenar el pedido con lo mas viejo siempre
     */
    public function iniciarTraslado(Request $request){
        $pedido = $request->input('pedido');
        $despacho = $request->input('despacho');
        $destino = $request->input('destino');
        $detalle_mov = [];
        try { 
            DB::beginTransaction();
            foreach($pedido as $p){
                $cantidadRetirar = $p['cantidad'];
                $cantidadRetirada = 0;
            
                $aux = (object) [];
                $cantidadTotal = $this->getCantidadInventario($aux,$p['codigo'],$despacho);
                $lotes = $this->getLotes($despacho,$p['codigo']);
                if($cantidadTotal < $cantidadRetirar){
                    echo 'noInv';
                    exit;
                }
                // se reciben los lotes ordenados por fecha, el mas viejo de primero
                foreach($lotes as $l){
                    if($cantidadRetirar > 0){
                        if($l->cantidad >= $cantidadRetirar){
                            //
                            $auxCantidadRetirar = $cantidadRetirar;//aux
                            $auxCantidadLote = $l->cantidad;//aux
                            $cantidadRetirar = $cantidadRetirar -  $l->cantidad;// cantidad 
                            $cantidadRetirada = $cantidadRetirada + $l->cantidad; // cantidad retirada
                            $l->cantidad = $l->cantidad - $auxCantidadRetirar;
                            if($l->cantidad < 1){
                                DB::table('inventario')
                                ->where('id', '=', $l->id)->delete();
                            }else{
                                $auxCantidadLote = $auxCantidadLote - $l->cantidad;
                                //Si no se actualiza la cantidad del inventario
                                DB::table('inventario')
                                ->where('id', '=', $l->id)
                                ->update(['cantidad'=>$l->cantidad]);  
                            }
                        }else{
                            $auxCantidadLote = $l->cantidad;
                            $auxCantidadRetirar = $cantidadRetirar;//aux
                            $cantidadRetirar = $cantidadRetirar -  $l->cantidad;// cantidad 
                            DB::table('inventario')
                                ->where('id', '=', $l->id)->delete();
                        }
                        $mov = [
                            'producto' => $l->producto,
                            'lote' => $l->lote,
                            'cantidad' => $auxCantidadLote
                        ];
                        array_push($detalle_mov,$mov);
                    }
                }    

            }
            $fecha_actual = date("Y-m-d H:i:s");
            $tipo_mov = DB::table('tipo_movimiento')->where('codigo','=','STI')->get()->first();

            $mov_id = DB::table('movimiento')->insertGetId( ['id' => null ,'tipo_movimiento' => $tipo_mov->id,
            'sucursal_inicio'=> $despacho,'sucursal_fin'=> $destino,'entrega'=> $this->getUsuarioAuth()['id'],'recibe' => null,'fecha'=>$fecha_actual,'fecha_entrega'=>null,'estado'=>'P'] );

            foreach($detalle_mov as $d){
                $id = DB::table('detalle_movimiento')->insertGetId( ['id' => null ,'producto' => $d['producto'],
                'cantidad'=> $d['cantidad'],'lote'=> $d['lote'],'movimiento'=> $mov_id] );
            }
            DB::commit();

            return $mov_id;
        }catch(QueryException $ex){ 
            DB::rollBack();
            echo 'error';
        }  
       
    }
  
}
