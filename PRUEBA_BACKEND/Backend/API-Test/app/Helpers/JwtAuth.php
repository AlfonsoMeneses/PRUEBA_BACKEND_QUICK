<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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

    public function signup($email, $password){

        //Se busca si el usuario existe con las credeniales.

        $user = User::Where(['email'=>$email, 'password'=>$password])->first();

        //Comprobar si es correcto
        $signup = false;

        //Si existe un usuario con el email y contraseña 
        if (is_object($user)) {

            $token = array(
                'sub'       =>  $user->id,
                'email'     =>  $user->email,
                'name'      =>  $user->name,
                'last_name' =>  $user->last_name,
                'user_name' =>  $user->user_name,
                'iat'       =>  time(),
                'exp'       =>  time() + ($this->expMinutes * 60)
            );

            $jwt = JWT::encode($token,$this->key,'HS256');

            $user->token = $jwt;
            
            $user->save();

            $data = array(
                "code" => 200,
                "data"=> $user
            );

            return $data;
          
        }
        else{

            $data = array(
                            "code" => 401,
                            "data" => ["error"=> "Error in user or password"]
                        );
          
            return $data;
        }

       
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
