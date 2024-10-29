<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\AuthUtil;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,AuthUtil;
}

/* Estructura basica de una funcion, para que cumpla con las normas de seguridad establecidas.

public function goNuevo(){
        if(!$this->validarSesion($this->codigo_pantalla)){ // El codigo de pantalla puede ser un string o un array, 
            $this->setMsjSeguridad();                      // Lo que define es si el rol del usuario tiene permiso en la vista.
            return redirect('/');
        }   
        
        
        $data = [
            'menus'=> $this->cargarMenus(),
            'productos' => $this->getProductos(), //opcional
            'panel_configuraciones' => $this->getPanelConfiguraciones()
        ];
        
        return view('bodega.productos',compact('data'));
}


*/
