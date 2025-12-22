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

    public function __construct()
    {
      
    }
    public function index(){
         
         $data = [
             'menus'=> $this->cargarMenus(),
             'sucursales' => $this->getSucursalesAll(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
             
         ];

        return view('mant.sucursales',compact('data'));
    }

    /**
     * Guarda o actualiza una sucursal.
     */
    public function guardarSucursal(Request $request){
       
        
        $validator = Validator::make($request->all(), [
            'mdl_sucursal_ipt_descripcion' => 'required|max:50',
            'mdl_sucursal_ipt_id' => 'required',
            'mdl_sucursal_ipt_nombre_factura' => 'required|max:500',
            'mdl_sucursal_ipt_cedula_factura' => 'required|max:50',
            'mdl_sucursal_ipt_correo_factura' => 'required|max:500',
            'tipo_identificacion_emisor' => 'required',
        ]);

        if ($validator->fails()) {
            $this->setError('Guardar Sucursal','La descripción debe ser entre 1 y 50 caracteres.');
            return redirect('mant/sucursales');
        }
        $descripcion = $request->input('mdl_sucursal_ipt_descripcion');
        $id = $request->input('mdl_sucursal_ipt_id');
        $nombre_factura = $request->input('mdl_sucursal_ipt_nombre_factura');
        $cedula_factura = $request->input('mdl_sucursal_ipt_cedula_factura');
        $correo_factura = $request->input('mdl_sucursal_ipt_correo_factura');
        $estado = $request->input('mdl_sucursal_chk_activa');
        $tipo_identificacion_emisor = $request->input('tipo_identificacion_emisor');
        try { 
            DB::beginTransaction();
            if($id == '-1' || $id == null){
                $idSucursal = DB::table('sucursal')->insertGetId( ['id' => null ,'descripcion'=> $descripcion,
                'estado' => ($estado == 'on' ? 'A' : 'I') ,'nombre_factura' => $nombre_factura ?? '',
                'cedula_factura' => $cedula_factura?? '',
                'correo_factura' => $correo_factura ?? '',
                'tipo_identificacion_emisor' => $tipo_identificacion_emisor ?? ''] );
            }else{
                DB::table('sucursal')
                    ->where('id', '=', $id)
                    ->update(['descripcion' => $descripcion,'estado' => ($estado == 'on' ? 'A' : 'I') ,
                    'nombre_factura' => $nombre_factura ?? '','cedula_factura' => $cedula_factura?? '',
                    'correo_factura' => $correo_factura ?? '',
                    'tipo_identificacion_emisor' => $tipo_identificacion_emisor ?? '']);
            }
            DB::commit();
            
            $this->setSuccess('Guardar Sucursal','La sucursal se guardo correctamente.');
            return redirect('mant/sucursales');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Guardar Sucursal','Ocurrio un error guardando la sucursal.');
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'MantenimientoSucursalController', 'descripcion' => $ex]);
            return redirect('mant/sucursales');
        }
        
        
    }


    public static function getSucursalById($id)
    {
        return DB::table('sucursal')
            ->select(
                'sucursal.*'
            )
            ->where('sucursal.id', '=', $id)->get()->first();
    }

    public function cargarSucursalAjax(Request $request)
    {
        try {
            $id = $request->input('idSucursal');
            if ($id < 1) {
                return $this->responseAjaxSuccess("", []);
            } else {
                $sucursal = MantenimientoSucursalController::getSucursalById($id);

                if ($sucursal == null) {
                    return $this->responseAjaxServerError("No se encontro la sucursal", []);
                }
                return $this->responseAjaxSuccess("", $sucursal);
            }
        } catch (QueryException $ex) {
            DB::table('log')->insertGetId(['id' => null, 'documento' => 'MantenimientoSucursalController', 'descripcion' => $ex]);
            return $this->responseAjaxServerError("Error cargando la sucursal", []);
        }
    }

}
