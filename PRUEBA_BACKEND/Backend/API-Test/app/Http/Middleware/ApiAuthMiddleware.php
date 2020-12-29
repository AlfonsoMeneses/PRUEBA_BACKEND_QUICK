<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\JwtAuth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //Comprobando si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            return $next($request);
        }
        else{
             //Error con la autenticacion.
             $data = array(
                'code'      =>  403,
                'status'    =>  "Error",
                'message'   =>  'El usuario no esta identificado',
            );

            return response()->json($data, $data['code']);
        }
    }
}
