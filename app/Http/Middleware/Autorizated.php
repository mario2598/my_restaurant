<?php

namespace App\Http\Middleware;

use App\Traits\AuthUtil;
use App\Traits\SpaceUtil;
use Closure;

class Autorizated
{
    use AuthUtil;
    use SpaceUtil;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$codigo_pantalla)
    {
        if (count($codigo_pantalla) > 0) {
            if (!$this->validarPermisos($codigo_pantalla)) {
                $this->setMsjSeguridad();
                $this->clearAuthUser();
                if($request->ajax()){
                    return [
                        'estado' => false,
                        'mensaje' => 'Error autenticación.',
                    ];
                }
                return redirect('/');
            }
        } else {
            if (!$this->validarSesion()) {
                $this->setMsjSeguridad();
                $this->clearAuthUser();
                if($request->ajax()){
                    return [
                        'estado' => false,
                        'mensaje' => 'Error autenticación.',
                    ];
                }
                return redirect('/');
            }
        }
        return $next($request);
    }
}
