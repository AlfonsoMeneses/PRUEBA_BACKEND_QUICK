<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use App\Helpers\JwtAuth;

class UserController extends Controller
{
    //Constructor
    public function __construct()
    {
        $this->middleware('api_auth',['except' => ['login']]);
    }

    /**
     *Login / iniciar sesión / obtener token
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
     * Listar usuarios
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        try {
          
            //Paginación de la lista de usuarios
            $per_page = 10;
            
            if ( $request["per_page"] >0) {
                
                $per_page =  $request["per_page"];
            }

            //Obtener todos los usuarios
            $users =  User::where('active',1)->orderBy('first_name')->paginate($per_page);

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
     * Crear usuario
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
                "errors" =>"There is already a user with the selected email"
            );

            return response()->json($data,400);
        }

        //Datos correctos. Se comienza a realizar el proceso

        //Se valida si ya existe un usuario con el email enviado en los datos
        $where = array(
            ['email', '=', $params_array["email"]],
            ['active','=',1]
        );

        

        $validateUser = User::where($where)->count();


        //Si existe ya un usuario con el email
        if ($validateUser != 0) 
        {
            $data = array(
                "errors" =>"There is already a user with the selected email"
            );

            return response()->json($data,400);            
        }
    
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
     * Listar usuario
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        try {

            //Obtener el usuario con        
            $user =  User::where('active',1)->find($id);

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
     * Edición parcial de un usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Obtener el usuario a editar
        $user = User::where('active',1)->find($id);

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


        if (is_string($request["email"])) 
        {

             //Se valida si ya existe un usuario con el email enviado en los datos
            $where = array(
                ['email', '=', $request["email"]],
                ['id',"!=",$id],
                ['active','=',1]
            );

            $validateUser = User::where($where)->count();

            //Si existe ya un usuario con el email
            if ($validateUser != 0) 
            {
                $data = array(
                    "errors" =>"There is already a user with the selected email"
                );

                return response()->json($data,400);            
            }
       
       
        }
       

        //Validando si alguno de los campos opcionales tienen datos 
        for ($i=0; $i < $count ; $i++) {

            //Si el campo actual tiene datos , se hace la modificación.
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
     * Edición general de usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id){

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
                "first_name"    =>"required",
                "last_name"     =>"required",
                "email"         =>"required|email",
                "password"      =>"required",
                "age"           =>"required",
                "image"         =>"required",
                "description"   =>"required"

            ]
        );

        //Si faltan datos requeridos
        if ($validate->fails()) {

            //La validación ha fallado
            $data = array(
                "errors" =>$validate->errors()
            );

            return response()->json($data,400);
        }

        try 
        {
            //Validar si existe un usuario con el email para editar
            //Se valida si ya existe un usuario con el email enviado en los datos
              $where = array(
                ['email', '=', $request["email"]],
                ['id',"!=",$id],
                ['active','=',1]
            );

            $validateUser = User::where($where)->count();

            //Si existe ya un usuario con el email
            if ($validateUser != 0) 
            {
                $data = array(
                    "errors" =>"There is already a user with the selected email"
                );

                return response()->json($data,400);            
            }

            //Obtener el usuario a editar
            $user = User::where('active',1)->find($id);

            //Si no existe el usuario con el id
            if (!is_object($user)) {
            
                //Respuesta 
                $data = array("error" => "Not found");

                return response()->json($data,200);
            }  

            //Edición de los datos del usuario
            $user->first_name = $params_array["first_name"];
            $user->last_name = $params_array["last_name"];
            $user->email = $params_array["email"];
            $user->age = $params_array["age"];
            $user->image = $params_array["image"];
            $user->description = $params_array["description"];

             //Cifrar la contraseña
            $psw = hash('sha256',$params_array["password"]);
            
            //Editar la nueva contraseña
            $user->password = $psw;

            //Guardando la modificación de los datos a la base de datos
            $user->save();            

            return response()->json($user,200);    
        } 
        catch (\Throwable $th) {
             //Si ha genera un error interno 
             $data = array("error" =>"Internal Server Error");

             return response()->json($data,500);
        }

       
    }

    /**
     * Borrar usuario
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    
        try {

            //Obtener el usuario con        
            $user =  User::where('active',1)->find($id);

            //Datos de respuesta
            $data = $user;

            //Si no existe un usuario con los datos enviados 
            if ( !is_object($user)) {
                //Error 404
                $data = ["error" => "Not found"];
            }
            else{

                //Eliminación logica del usuario
                $data->active = 0;
                $data->save();
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
