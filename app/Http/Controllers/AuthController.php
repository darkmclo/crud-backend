<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Validator;
use Exception;

class AuthController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }
    */

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');

            // Se define la ruta del archivo que contiene los datos de los usuarios
            $filePath = storage_path('app/data/users/users.txt');

            // Se verifica si el fichero existe, en caso de no hacerlo se devuelve un mensaje de error. 
            if (!File::exists($filePath)) {
                return response()->json(['message' => 'No se encontró el recurso necesario.'], 404);
            }

            // Se lee el contenido del archivo
            $contents = File::get($filePath);

            // Se decodifica el contenido de la información del archivo .txt, a un formato de array compatible con JSON.
            $array_content = json_decode($contents, true);

            $checkifElementExists = collect($array_content)->contains('email', $credentials['email']);

            if(!$checkifElementExists){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acceso denegado. No hay ningún usuario con ese email.',
                ], 401);
            }

            // Se obtiene el contenido del request, seguidamente se decodifica a un formato de array compatible con JSON. 
            //$request_content = $request->getContent();
            //$data = json_decode($request_content, true);
            
    
            $token = Auth::attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acceso no autorizado. Las credenciales incorrectas no permitieron validar un token.',
                ], 401);
            }
    
            $user = Auth::user();
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);

            // Se devuelve una respuesta exitosa.
            return response()->json(['message' => 'El contenido se ha guardado exitosamente.'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Se maneja la excepción, con error de validación.
            return response()->json(['error' => 'Error de validación', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            // Se recibe cualquier otro tipo de excepción.
            return response()->json(['error' => 'Ocurrió un error', 'message' => $e->getMessage()], 500);
        }  
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
    
            $credentials = $request->only('email', 'password');
    
            // Se define la ruta del archivo que contiene los datos de los usuarios
            $filePath = storage_path('app/data/users/users.txt');
    
            // Se verifica si el fichero existe, en caso de no hacerlo se devuelve un mensaje de error. 
            if (!File::exists($filePath)) {
                return response()->json(['message' => 'No se encontró el recurso necesario.'], 404);
            }
    
            // Se lee el contenido del archivo
            $contents = File::get($filePath);
    
            // Se decodifica el contenido de la información del archivo .txt, a un formato de array compatible con JSON.
            $array_content = json_decode($contents, true);
    
            $checkifElementExists = collect($array_content)->contains('email', $credentials['email']);

            if($checkifElementExists){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ya existe un usuario con ese email. Intente con otro diferente.',
                ], 401);
            }

            //El array con las credenciales se pasa a la variable data.
            $data = $credentials;
    
            // El índice nuevo se guarda en el arreglo de información
            $array_content[] = $data;
    
            // El arreglo con los datos nuevos se codifica nuevamente a un string JSON.
            $re_encoded_content = json_encode($array_content);
    
            // La información actualizada se guarda en el archivo .txt
            File::put($filePath, $re_encoded_content . PHP_EOL);

            // Se devuelve una respuesta exitosa.
            return response()->json(['message' => 'El contenido se ha guardado exitosamente.'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Se maneja la excepción, con error de validación.
            return response()->json(['error' => 'Error de validación', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            // Se recibe cualquier otro tipo de excepción.
            return response()->json(['error' => 'Ocurrió un error', 'message' => $e->getMessage()], 500);
        }
        
    }
    
    protected function resToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
