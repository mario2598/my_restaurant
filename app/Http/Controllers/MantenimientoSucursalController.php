<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Traits\SpaceUtil;


class MantenimientoSucursalController extends Controller
{
    use SpaceUtil;
    public $codigo_pantalla = "mantSuc";

    public function __construct()
    {
      
    }
    public function index(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $sucursales = DB::table('sucursal')->where('estado','like','A')->get();
        
         $data = [
             'menus'=> $this->cargarMenus(),
             'sucursales' => $sucursales,
             'panel_configuraciones' => $this->getPanelConfiguraciones()
             
         ];

        return view('mant.sucursales',compact('data'));
    }

    /**
     * Guarda o actualiza una sucursal.
     */
    public function guardarSucursal(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        $validator = Validator::make($request->all(), [
            'mdl_sucursal_ipt_descripcion' => 'required|max:50',
            'mdl_sucursal_ipt_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->setError('Guardar Sucursal','La descripción debe ser entre 1 y 50 caracteres.');
            return redirect('mant/sucursales');
        }
        $descripcion = $request->input('mdl_sucursal_ipt_descripcion');
        $id = $request->input('mdl_sucursal_ipt_id');
        try { 
            DB::beginTransaction();
            if($id == '-1' || $id == null){
                $idSucursal = DB::table('sucursal')->insertGetId( ['id' => null ,'descripcion'=> $descripcion,'estado' => 'A'] );
            }else{
                DB::table('sucursal')
                    ->where('id', '=', $id)
                    ->update(['descripcion' => $descripcion]);
            }
            DB::commit();
            
            $this->setSuccess('Guardar Sucursal','La sucursal se guardo correctamente.');
            return redirect('mant/sucursales');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Guardar Sucursal','Ocurrio un error guardando la sucursal.');
            return redirect('mant/sucursales');
        }
        
        
    }

    /**
     * Elimina una sucursal.
     */
    public function eliminarSucursal(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
       
        $id = $request->input('idSucursalEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Sucursal','Identificador inválido.');
            return redirect('mant/sucursales');
        }
        try { 
            DB::beginTransaction();
            $sucursal = DB::table('sucursal')->where('id','=',$id)->get()->first();
            if($sucursal == null){
                $this->setError('Eliminar Sucursal','No existe la sucursal a eliminar.');
                return redirect('mant/sucursales');
            }else{
                DB::table('sucursal')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Sucursal','La sucursal se elimino correctamente.');
            return redirect('mant/sucursales');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Sucursal','Ocurrio un error eliminando la sucursal.');
            return redirect('mant/sucursales');
        }
        
        
    }
}
