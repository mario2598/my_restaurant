<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;
class ProductosController extends Controller
{
    use SpaceUtil;
    private $admin;
    public $codigo_pantalla = "bodProductos";
    public function __construct()
    {
       
        setlocale(LC_ALL, "es_CR");
    }
    
    public function index(){

    }

    public function goProductos(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            $this->setMsjSeguridad();
            return redirect('/');
        }   
        
        $filtros = [
            'impuesto' => 'T',
            'categoria' => "T",
        ];
        
        $data = [
             'menus'=> $this->cargarMenus(),
            'filtros' =>$filtros,
            'productos' => [],
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('bodega.productos',compact('data'));
    }

   
    public function goProductosFiltro(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            $this->setMsjSeguridad();
            return redirect('/');
        }
        
        $categoria = $request->input('categoria');
        $impuesto = $request->input('impuesto');
       
        $productos = DB::table('producto')
        ->leftJoin('categoria','categoria.id','=','producto.categoria')
        ->leftJoin('impuesto','impuesto.id','=','producto.impuesto')
        ->select('producto.*','impuesto.impuesto as porcentaje_impuesto','categoria.categoria as nombre_categoria')
        ->where('producto.estado','=','A');

        if(!$this->isNull($categoria) && $categoria != 'T'){
            $productos = $productos->where('categoria.id','=',$categoria);
        }
        if(!$this->isNull($impuesto) && $impuesto != 'T'){
            $productos = $productos->where('impuesto.id','=',$impuesto);
        }


        $productos = $productos->get();

        $filtros = [
            'impuesto' => $impuesto,
            'categoria' => $categoria,
        ];
      //  dd($productos);
        $data = [
             'menus'=> $this->cargarMenus(),
            'productos' => $productos,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'filtros' =>$filtros,
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        return view('bodega.productos',compact('data'));
    }

    public function goNuevoProducto(){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }

        $datos = [];
         $data = [
             'menus'=> $this->cargarMenus(),
             'datos' => $datos,
             'categorias' => $this->getCategorias(),
             'impuestos' => $this->getImpuestos(),
             'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('bodega.producto.nuevoProducto',compact('data'));
    }

    public function returnNuevoProductoWithData($datos){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        

        $data = [
             'menus'=> $this->cargarMenus(),
            'datos' => $datos,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
       return view('bodega.producto.nuevoProducto',compact('data'));
    }

    public function returnEditarProductoWithId($id){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        if($id < 1 || $this->isEmpty($id)){
            $this->setError("Error","El producto no existe..");
            return redirect('bodega/productos');
        }
      
        $producto = DB::table('producto')
        ->where('producto.id','=',$id)->get()->first();
        
        if($producto == null){
            $this->setError('Editar Producto','No existe el producto a editar.');
            return redirect('bodega/productos');
        }

         $data = [
             'menus'=> $this->cargarMenus(),
            'producto' => $producto,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('bodega.producto.editarProducto',compact('data'));
    }

    public function goEditarProducto(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
        
        $id = $request->input('idProductoEditar');
        $producto = DB::table('producto')
        ->where('producto.id','=',$id)->get()->first();
        
        if($producto == null){
            $this->setError('Editar Producto','No existe el producto a editar.');
            return redirect('bodega/productos');
        }

         $data = [
             'menus'=> $this->cargarMenus(),
            'producto' => $producto,
            'categorias' => $this->getCategorias(),
            'impuestos' => $this->getImpuestos(),
            'panel_configuraciones' => $this->getPanelConfiguraciones()
         ];
        return view('bodega.producto.editarProducto',compact('data'));
    }

       /**
     * Guarda o actualiza un producto
     */
    public function guardarProducto(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            return redirect('/');
        }
        
       // dd($request->all());
        $id = $request->input('id');
        $codigo_barra = $request->input('codigo');
        $producto = DB::table('producto')->select('producto.*')->where('id','=',$id)->get()->first();
       
        if($id < 1 || $this->isNull($id)){ // Nuevo 
            if($this->codigoBarraRegistrado($codigo_barra)){
                $this->setError('Guardar Producto','El código de barra ya esta en uso.');
                return $this->returnNuevoProductoWithData($request->all());
            }
            $actualizar = false;
        }else{// Editar usuario
            
            if($producto == null ){
                $this->setError('Guardar Producto','No existe un producto con los credenciales.');
                return $this->returnEditarProductoWithId($id);
            }
            if($producto->codigo_barra != $codigo_barra){
                if($this->codigoBarraRegistrado($codigo_barra)){
                    $this->setError('Guardar Producto','El código de barra ya esta en uso.');
                    return $this->returnEditarProductoWithId($id);
                }
            }
            $actualizar = true;
        }


        if($this->validarProducto($request)) {

            $categoria = $request->input('categoria');
            $nombre = $request->input('nombre');
            $precio = $request->input('precio');
            $impuesto = $request->input('impuesto');
            $precio_mayoreo = $request->input('precio_mayoreo');
           
            try { 
                DB::beginTransaction();

                if($actualizar){// Editar usuario
                    DB::table('producto')
                        ->where('id', '=', $id)
                        ->update(['nombre' => $nombre,'categoria'=> $categoria,'precio'=> $precio,
                        'impuesto'=> $impuesto,'precio_mayoreo' => $precio_mayoreo,'codigo_barra' => $codigo_barra ]);
                }else{// Nuevo usuario
                    $id = DB::table('producto')->insertGetId( ['id' => null ,'nombre' => $nombre,'categoria'=> $categoria,'precio'=> $precio,
                    'impuesto'=> $impuesto,'precio_mayoreo' => $precio_mayoreo,'codigo_barra' => $codigo_barra,'estado'=> 'A' ] );
                }
               
                DB::commit();
               
                
                if($actualizar){// Editar usuario
                    $this->setSuccess('Guardar Producto','Se actualizo el producto correctamente.');
                }else{// Nuevo usuario
                    
                    $this->setSuccess('Guardar Producto','Producto creado correctamente.');
                }
                return $this->returnEditarProductoWithId($id);
            }
            catch(QueryException $ex){ 
                DB::rollBack();
                $this->setError('Guardar Producto','Algo salio mal...');
                return redirect('bodega/productos');
            }    
          
        }else{
            if($actualizar){
                return $this->returnEditarProductoWithId($id);
            }else{
                return $this->returnNuevoProductoWithData($request->all());
            }
        }

    }


    /**
     * Inactiva un producto.
    */
    public function eliminarProducto(Request $request){
        if(!$this->validarSesion($this->codigo_pantalla)){
            $this->setError("Seguridad","No tienes permisos para ingresar..");
            return redirect('/');
        }
        
        $id = $request->input('idProductoEliminar');
        if($id == null || $id == '' || $id < 1){
            $this->setError('Eliminar Producto','Identificador inválido.');
            return redirect('bodega/productos');
        }
        try { 
            DB::beginTransaction();
            $producto = DB::table('producto')->where('id','=',$id)->get()->first();
            if($producto == null){
                $this->setError('Eliminar Producto','No existe el producto a eliminar.');
                return redirect('bodega/productos');
            }else{
                DB::table('producto')
                    ->where('id', '=', $id)
                    ->update(['estado' => 'I']);
            }
            DB::commit();
            $this->setSuccess('Eliminar Producto','El producto se elimino correctamente.');
            return redirect('bodega/productos');
        }
        catch(QueryException $ex){ 
            DB::rollBack();
            $this->setError('Eliminar Producto','Ocurrio un error eliminando el producto.');
            return redirect('bodega/productos');
        }
    }
    
  
    public function validarProducto(Request $r){
        $requeridos = "[";
        $valido = true;
        $esPrimero = true;
       
        if($this->isNull($r->input('codigo')) || $this->isEmpty($r->input('codigo')) ){
            $requeridos .= " Código ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('nombre')) || $this->isEmpty($r->input('nombre')) ){
            $requeridos .= " Nombre ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('categoria')) || $this->isEmpty($r->input('categoria')) ){
            $requeridos .= " Categoría ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('impuesto')) || $this->isEmpty($r->input('impuesto')) ){
            $requeridos .= " Impuesto ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('precio')) || $this->isEmpty($r->input('precio')) ){
            $requeridos .= " Precio ";
            $valido = false;
            $esPrimero = false;
        }
        if($this->isNull($r->input('precio_mayoreo')) || $this->isEmpty($r->input('precio_mayoreo')) ){
            $requeridos .= " Precio Mayoreo";
            $valido = false;
            $esPrimero = false;
        }
        
        $requeridos .= "] ";
        if(!$valido){
            $this->setError('Campos Requeridos',$requeridos);
            return false;
        }

        if(!$this->isLengthMinor($r->input('codigo'),15)){
            $this->setError('Tamaño exedido',"El código de barra debe ser de máximo 15 caracteres.");
            return false;
        }
        if(!$this->isLengthMinor($r->input('nombre'),30)){
            $this->setError('Tamaño exedido',"El nombre debe ser de máximo 30 caracteres.");
            return false;
        }

        if(!$this->isNumber($r->input('precio')) || $r->input('precio') < 10){
            $this->setError('Número incorrecto',"El precio debe ser mayor que 10.00 CRC.");
            return false;
        }

        if(!$this->isNumber($r->input('precio_mayoreo')) || $r->input('precio_mayoreo') < 10){
            $this->setError('Número incorrecto',"El precio de mayoreo debe ser mayor que 10.00 CRC.");
            return false;
        }

        return $valido;
    } 
}
