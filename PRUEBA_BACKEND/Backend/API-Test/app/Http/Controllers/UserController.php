<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use App\Helpers\JwtAuth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $req)
    {
        //
        $users =  User::get();

        return response()->json($users,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
          //Recoger los datos
        $jsonData = $request->input("payload",null);

        $params_array =json_decode($jsonData,true);

        if (!empty($params_array)) {
            
            //Limpiar datos 
            $params_array = array_map('trim',$params_array);

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
                return response()->json($newUser,200);

            } 
            catch (\Throwable $th) {

                //Si hay algun error se da una respuesta con el estado 500 
                $data = array(
                    "error"=>"Internal server error",
                    "message"=> $th->getMessage()
                    
                 );

                return response()->json($data,500);
            }

        }
        else
        {
            //No hay datos para crear un usuario
            $data = array(
                "error" =>"There is no data"
            );

            //Se da una respuesta con el estado 400 
            return response()->json($data,400);
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
        //
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
        //
        $users =  User::get();

        return response()->json($users,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
