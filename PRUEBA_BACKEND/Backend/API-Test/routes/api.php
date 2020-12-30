<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

//CRUD Basico
//Route::resource("users","UserController");

//Login
Route::post("login","UserController@login");
//Lista de usuarios
Route::get('users',"UserController@index");
//Obtener datos de un usuario
Route::get('users/{id}',"UserController@show");
//Crear Usuario
Route::post('users',"UserController@store");
//Edición general de usuario
Route::put('users/{id}',"UserController@edit");
//Edición parcial de un usuario
Route::patch('users/{id}',"UserController@update");
// Borrar usuario |
Route::delete('users/{id}',"UserController@destroy");

//Error 404
Route::get('/', function () {

    //Error 404
    $data = ["error" => "Not found"];    
    //Respuesta
    return response()->json($data,200);
    
});
