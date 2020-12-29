<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use App\Helpers\JwtAuth;

class UserController extends Controller
{

    /**
     * User Login - Get Token
     */
    public function login (Request $request)
    {
        //Validando el Header Content-Type : application/json
        $header = $request->header("Content-Type");

        if ($header != "application/json") {

            $errorMessage = "Request should have 'Content-Type' header with value 'application/json'"; 
            
            $data = ["error" => $errorMessage];

            return response()->json($data,403);

        }
      

        //Recoger los datos para el nuevo usuario

        $params_array = array(
            "email"         =>  $request["email"],
            "password"      =>  $request["password"]
        );

        $jwtAuth = new JwtAuth();

        //Validar datos
        $validate = \Validator::make(
            $params_array,
            [
                "email" =>"required|email",
                "password"  =>"required"
            ]
        );

        if ($validate->fails()) {
            //La validación ha fallado

            $data = array(
                "errors" =>$validate->errors()
            );

            return response()->json($data,200);
        }

        $email = $params_array["email"];

        $pwd = hash('sha256',$params_array["password"]);

        try {

            //Comenzando el login
            $signup = $jwtAuth->signup($email,$pwd);
   
            //Validando si el loguin fue correcto
            return response()->json($signup["data"],$signup["code"]);
            

        } catch (\Throwable $th) {
            //throw $th;
            $data = array("error" =>"Internal Server Error","message"=>$th->getMessage());
            return response()->json($data,500);

        }
        
       
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        try {
          
            //Obtener todos los usuarios
            $users =  User::get();

            return response()->json($users,200);

        } catch (\Throwable $th) {
            //throw $th;
            $data = array(
                "error" =>"Internal Server Error"
            );

            return response()->json($data,500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        //Recoger los datos para el nuevo usuario

        $params_array = array(
            "first_name"    =>  $request["first_name"],
            "last_name"     =>  $request["last_name"],
            "email"         =>  $request["email"],
            "password"      =>  $request["password"],
            "age"           =>  $request["age"],
            "image"         =>  $request["image"],
            "description"   =>  $request["description"],
        );
      
        //Validar datos
        $validate = \Validator::make(
            $params_array,
            [
                "first_name"=>"required",
                "last_name"=>"required",
                "email"=>"required|email",
                "password"=>"required"
            ]
        );

        //Si hay datos que son obligatorios
        if ($validate->fails()) {

            //La validación ha fallado
            $data = array(
                "errors" =>$validate->errors()
            );

            return response()->json($data,400);
        }

        //Datos correctos. Se comienza a realizar el proceso

        //Cifrar la contraseña
        $psw = hash('sha256',$params_array["password"]);

        //Crear el usuario
        $newUser = new User();
        $newUser->first_name = $params_array["first_name"];
        $newUser->last_name = $params_array["last_name"];
        $newUser->email = $params_array["email"];
        $newUser->password = $psw;

        //Validando datos opcionales
        
        //Campos opcionales 
        $optional_data = ["age","image","description"];

        $count = count($optional_data);

        //Validando si alguno de los campos opcionales tienen datos 
        for ($i=0; $i < $count ; $i++) { 

            //Validando si existen datos del campo
            $validate = \Validator::make(
                $params_array,
                [
                    $optional_data[$i]=>"required",
                ]
            );

            //Si el campo tiene datos
            if (!$validate->fails()) {

                //se agregan los datos al usuario por crear
                $newUser[$optional_data[$i]] = $params_array[$optional_data[$i]]; 
            }
        }
    
        try {

            //Comienza la transacción de datos del nuevo usuario
            $dbTransaction = \DB::transaction(function() use($newUser)
            {
                
                //Creando el usuario en la base de datos 
                $newUser->save();

                //Generando el primer token
                $jwt = new JwtAuth();

                //Asignando el token al usuario
                $newUser->token = $jwt->getNewUserToken($newUser);;
                
                //Actualizar el usuario con el token
                $newUser->save();
                                
            });

            //Respuesta a la petición
            return response()->json($newUser,201);

        } 
        catch (\Throwable $th) {

            //Si hay algun error se da una respuesta con el estado 500 
            $data = array("error"=>"Internal server error");

            return response()->json($data,500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        try {

            //Obtener el usuario con        
            $user =  User::find($id);

            //Datos de respuesta
            $data = $user;

            //Si no existe un usuario con los datos enviados 
            if ( !is_object($user)) {
                //Error 404
                $data = ["error" => "Not found"];
            }
            
            //Respuesta
            return response()->json($data,200);

        } catch (\Throwable $th) {
            
            //Si ha genera un error interno 
            $data = array("error" =>"Internal Server Error");

            return response()->json($data,500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Obtener el usuario a editar
        $user = User::find($id);

        //Si no existe el usuario con el id
        if (!is_object($user)) {
          
            //Respuesta 
            $data = array("error" => "Not found");

            return response()->json($data,200);
        }   

        //Campos para actualizar 
        $update_data = ["first_name",
                        "last_name",
                        "email",
                        "age",
                        "image",
                        "description"];

        //Cantidad de campos, se pudo solo colocar el valor 6, pero me gusta mas "automatico"                        
        $count = count($update_data);

        //Validando si alguno de los campos opcionales tienen datos 
        for ($i=0; $i < $count ; $i++) {

            if (is_string($request[$update_data[$i]]) || is_int($request[$update_data[$i]])) 
            {
                $user[$update_data[$i]] = $request[$update_data[$i]];
            }
        }

        //Comenzando a realizar la edición de datos del usuario seleccionad
        try {

            //Guardando los cambios en los campos seleccionados
            $user->save();
            
            //Respuesta
            return response()->json($user,200);

        } catch (\Throwable $th) {
            
            //Si ha genera un error interno 
            $data = array("error" =>"Internal Server Error");

            return response()->json($data,500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    
        try {

            //Obtener el usuario con        
            $user =  User::find($id);

            //Datos de respuesta
            $data = $user;

            //Si no existe un usuario con los datos enviados 
            if ( !is_object($user)) {
                //Error 404
                $data = ["error" => "Not found"];
            }
            else{
                $data->delete();
            }
            
            //Respuesta
            return response()->json($data,200);

        } catch (\Throwable $th) {
            
            //Si ha genera un error interno 
            $data = array("error" =>"Internal Server Error");

            return response()->json($data,500);
        }
    }
}
