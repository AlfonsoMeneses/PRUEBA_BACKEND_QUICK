<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

    private $key;

    private $expMinutes;

    public function __construct()
    {
        $this->key = 'PRUEBA_BACKEND_QUICK_2020-12-28';

        $this->expMinutes = 60;
    }

    /**
     * 
     */
    public function getNewUserToken($newUser){

        $token = array(
            'sub'           => $newUser->id,
            'email'         => $newUser->email,
            'first_name'    => $newUser->first_name,
            'last_name'     =>$newUser->last_name,
            'iat'           =>time(),
            'exp'           =>time() + ($this->expMinutes * 60)
        );

        $jwt = JWT::encode($token,$this->key,'HS256');

        return $jwt;

    }

    public function signup($email, $password, $getToken = null){

        //Se busca si el usuario existe con las credeniales.

        $user = User::Where(['email'=>$emaile, 'password'=>$password])->first();

        //Comprobar si es correcto

        $signup = false;

        if (is_object($user))
        {
            $signup = true;
        }

        if ($signup) {

            $expMinutes = 60;

            $token = array(
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'last_name' =>$user->last_name,
                'user_name' =>$user->user_name,
                'iat'       =>time(),
                'exp'       =>time() + ($expMinutes * 60)
            );

            if (is_null($getToken))
            {
                $jwt = JWT::encode($token,$this->key,'HS256');
                $data = $jwt;
            }
            else{
                $data = $token;
            }
        }
        else{
            $data = array(
                'status'    =>  'error',
                'message'   =>  'Usuario o Contraseña Incorrecta'
            );
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity= false){

        $auth = false;

        try {

            $jwt = str_replace('"','',$jwt);

            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

        } catch (\Throwable $th) {
            //throw $th;
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        }
        else{
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }  
}
