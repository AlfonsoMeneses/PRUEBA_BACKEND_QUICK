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
         //Validando el Header Content-Type : application/json
         $header = $request->header("Content-Type");

         if ($header != "application/json") {
 
             $errorMessage = "Request should have 'Content-Type' header with value 'application/json'"; 
             
             $data = ["error" => $errorMessage];
 
             return response()->json($data,403);
 
         }

        //Comprobando si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            return $next($request);
        }
        else{

             //Error con la autenticacion.
            $errorMessage = "Invalid Auth token."; 
             
            $data = ["error" => $errorMessage];

            return response()->json($data,401);
        }
    }
}
