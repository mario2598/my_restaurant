<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Traits\SpaceUtil;


class MantenimientoMobiliarioController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantMob";

    public function __construct()
    {
      
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $mobiliario = DB::table('mobiliario')->where('estado','like','A')->get();
        
         $data = [
             'menus'=> $this->cargarMenus(),
             'mobiliario' => $mobiliario,
             'panel_configuraciones' => $this->getPanelConfiguraciones()
             
         ];

        return view('mant.mobiliario',compact('data'));
    }

    /**
     * Guarda o actualiza una sucursal.
     */
    public function guardarMobiliario(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        $validator = Validator::make($request->all(), [
            'mdl_mobiliario_ipt_nombre' => 'required',
            'mdl_mobiliario_ipt_personas' => 'required|min:1',
            'mdl_mobiliario_ipt_filas' => 'required|min:1',
            'mdl_mobiliario_ipt_columnas' => 'required|min:1',
            'mdl_mobiliario_ipt_descripcion' => 'required|max:240',
            'mdl_mobiliario_ipt_id' => 'required',
        ]);
        if ($validator->fails()) {
            dd($validator);
            $this->setError('Guardar Mobiliario','Algo salió mal al guardar el mobiliario.');
            return redirect('mant/mobiliario');
        }
        $nombre = $request->input('mdl_mobiliario_ipt_nombre');
        $personas = $request->input('mdl_mobiliario_ipt_personas');
        $filas = $request->input('mdl_mobiliario_ipt_filas');
        $columnas = $request->input('mdl_mobiliario_ipt_columnas');
        $descripcion = $request->input('mdl_mobiliario_ipt_descripcion');
        $id = $request->input('mdl_mobiliario_ipt_id');
        
        try { 
            DB::beginTransaction();
            if($id == '-1' || $id == null){
                $idMobiliario = DB::table('mobiliario')->insertGetId( ['id' => null ,'tam_filas' => $filas,'tam_columnas'=> $columnas, 'cantidad_personas'=> $personas, 'nombre'=> $nombre, 'descripcion'=> $descripcion, 'estado' => 'A'] );
            }else{
                DB::table('mobiliario')
                    ->where('id', '=', $id)
                    ->update(['tam_filas' => $filas,'tam_columnas' => $columnas,'cantidad_personas' => $personas,'nombre' => $nombre,'descripcion' => $descripcion]);
            }
            DB::commit();
            
            $this->setSuccess('Guardar Mobiliario','El mobiliario se guardó correctamente.');
            return redirect('mant/mobiliario');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Guardar Mobiliario','Ocurrio un error guardando el mobiliario.');
            return redirect('mant/mobiliario');
        }
        
        
    }

    /**
     * Elimina una sucursal.
     */
    public function eliminarMobiliario(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $id = $request->input('idMobiliarioEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Mobiliario','Identificador inválido.');
            return redirect('mant/mobiliario');
        }
        try { 
            DB::beginTransaction();
            $sucursal = DB::table('mobiliario')->where('id','=',$id)->get()->first();
            if($sucursal == null){
                $this->setError('Eliminar Mobiliario','No existe el mobiliario a eliminar.');
                return redirect('mant/mobiliario');
            }else{
                DB::table('mobiliario')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Mobiliario','El mobiliario se eliminó correctamente.');
            return redirect('mant/mobiliario');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Mobiliario','Ocurrió un error eliminando el mobiliario.');
            return redirect('mant/mobiliario');
        }
        
        
    }
}