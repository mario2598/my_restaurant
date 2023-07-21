<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class ParametrosGeneralesController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantParGen";

    public function __construct()
    {
        
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        //
         $data = [
             'menus'=> $this->cargarMenus(),
             'parametros_generales' => $this->getParametrosGenerales(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('mant.parametros_generales',compact('data'));
    }

    /**
     * Guarda o actualiza un tipo de ingreso.
     */
    public function guardar(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        if ($this->validar($request)) {

            $tiempoMonitorMov = $request->input('tiempoMonitorMov');
            $cobro = $request->input('cobro');
            $inicio_mes_panaderia = $request->input('inicio_mes_panaderia');
            $inicio_mes_cafeteria = $request->input('inicio_mes_cafeteria');
           
            try { 
                DB::beginTransaction();
                DB::table('parametros_generales')
                    ->where('id', '=', 1)
                    ->update(['porcentaje_banco'=>$cobro,'inicio_mes_panaderia'=>$inicio_mes_panaderia,'inicio_mes_cafeteria'=>$inicio_mes_cafeteria,
                    'tiempo_refresco_monitor_movimientos'=>$tiempoMonitorMov]);
               
                DB::commit();
                $this->setSuccess('Guardar Parámetros Generales','Los parámetros generales se guardaron correctamente.');
                return redirect('mant/parametrosgenerales');
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Parámetros Generales','Ocurrio un error guardando los parámetros generales.');
                return redirect('mant/parametrosgenerales');
            }
        
        }else{
            return redirect('mant/parametrosgenerales');
        }
    }


    public function validar(Request $r){
        $requeridos = "[";
        $valido = true;
      
        if($this->isNull($r->input('cobro')) || $this->isEmpty($r->input('cobro')) ){
            $requeridos .= " Cobro Banco";
            $valido = false;
          
        }
        if($this->isNull($r->input('tiempoMonitorMov')) || $this->isEmpty($r->input('tiempoMonitorMov')) ){
            $requeridos .= " Tiempo Monitoreo ";
            $valido = false;
          
        }
        if($this->isNull($r->input('inicio_mes_cafeteria')) || $this->isEmpty($r->input('inicio_mes_cafeteria')) ){
            $requeridos .= " Inicio Mes Cafetería ";
            $valido = false;
          
        }
        if($this->isNull($r->input('inicio_mes_panaderia')) || $this->isEmpty($r->input('inicio_mes_panaderia')) ){
            $requeridos .= " Inicio Mes Panadería ";
            $valido = false;
          
        }

        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
        }

        if(!$this->isNumber($r->input('tiempoMonitorMov'))){
            $this->setError('Formato incorrecto',"El tiempo de monitoreo debe ser un número.");
            $valido = false;
        }

        if(!$this->isNumber($r->input('cobro'))){
            $this->setError('Formato incorrecto',"El cobro del banco debe ser un número.");
            $valido = false;
        }

        if(!$this->isNumber($r->input('inicio_mes_cafeteria'))){
            $this->setError('Formato incorrecto',"El total de inicio de mes debe ser un número.");
            $valido = false;
        }

        if($r->input('inicio_mes_cafeteria') < 0){
            $this->setError('Tamaño incorrecto',"El total de inicio de mes debe mayor o igual a 0.");
            $valido = false;
        }

        if(!$this->isNumber($r->input('inicio_mes_panaderia'))){
            $this->setError('Formato incorrecto',"El total de inicio de mes debe ser un número.");
            $valido = false;
        }

        if($r->input('inicio_mes_panaderia') < 0){
            $this->setError('Tamaño incorrecto',"El total de inicio de mes debe mayor o igual a 0.");
            $valido = false;
        }

        if($r->input('cobro') > 99 || $r->input('cobro') < 0){
            $this->setError('Tamaño incorrecto',"El porcentaje de cobro del banco debe ser un número entre 0 y 99 %.");
            $valido = false;
        }

        if($r->input('tiempoMonitorMov') > 99 || $r->input('tiempoMonitorMov') < 0){
            $this->setError('Tamaño incorrecto',"El tiempo de monitoreo debe ser un número entre 0 y 99 %.");
            $valido = false;
        }

     
        return $valido;
    } 
}
