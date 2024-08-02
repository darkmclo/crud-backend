<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Exception;

class ClientController extends Controller
{
    private string $foldername = 'clients';
    private string $filename = 'clients.txt';

    public function read() {
        $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

        if (!File::exists($fPath)) {
            return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
        } else {
            $file_content = File::get($fPath);
            $products = json_decode($file_content);
            return response()->json(['data' => $products], 200);
        };
    }

    public function get($id)
    {
        try{
            $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

            if (!File::exists($fPath)) {
                return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
            }

            $contents = File::get($fPath);
            $array_content = json_decode($contents, true);
            $array_collected = collect($array_content);
            $checkifIDExists = $array_collected->contains('id', $id);

            if(!$checkifIDExists){
                return response()->json([
                    'error' => 'Invalid',
                    'message' => 'Acceso denegado. No hay ningun registro con el ID proveido.',
                ], 401);
            }
            
            $item = $array_collected->firstWhere('id', $id);

            // Se devuelve una respuesta exitosa.
            return response()->json(['data' => $item], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Se maneja la excepción, con error de validación.
            return response()->json(['error' => 'ValidationError', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            // Se recibe cualquier otro tipo de excepción.
            return response()->json(['error' => 'ApplicationError', 'message' => $e->getMessage()], 500);
        }  
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                //'id' => 'required|numeric',
                'name' => 'required|string',
                'rtn' => 'required|string',
                'addr' => 'required|string'
            ]);

            $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

            if (!File::exists($fPath)) {
                return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
            }

            $contents = File::get($fPath);
            $array_content = json_decode($contents, true);
            $request_content = $request->getContent();
            $data = json_decode($request_content, true);
            $array_collected = collect($array_content);

            $id_counter = (int) $array_collected->max('id');
            $id_counter++;

            $data['id'] = $id_counter;
            
            $lastKey = array_key_last($data);
            $lastElement = [$lastKey => $data[$lastKey]];

            $data = $lastElement + $data;
            
            $array_content[] = $data;

            $re_encoded_content = json_encode($array_content);

            File::put($fPath, $re_encoded_content . PHP_EOL);

            // Se devuelve una respuesta exitosa.
            return response()->json(['message' => 'El contenido se ha guardado exitosamente.'], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Se maneja la excepción, con error de validación.
            return response()->json(['error' => 'ValidationError', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            // Se recibe cualquier otro tipo de excepción.
            return response()->json(['error' => 'ApplicationError', 'message' => $e->getMessage()], 500);
        }  
    }

    public function update(Request $request)
    {
        try {
            // Valida la información que es recibida
            $request->validate([
                'id' => 'required|numeric',
                'name' => 'required|string',
                'rtn' => 'required|string',
                'addr' => 'required|string'
            ]);

            $id_from_request = $request->only('id');

            // Se define la ruta del archivo que contiene los datos de los clientes
            $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

            // Se verifica si el fichero existe, en caso de no hacerlo se devuelve un mensaje de error. 
            if (!File::exists($fPath)) {
                return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
            }

            $contents = File::get($fPath);
            $array_content = json_decode($contents, true);
            $array_collected = collect($array_content);
            $checkifIDExists = $array_collected->contains('id', $id_from_request['id']);

            if(!$checkifIDExists){
                return response()->json([
                    'error' => 'Invalid',
                    'message' => 'Acceso denegado. No hay ningun registro con el ID proveido.',
                ], 401);
            }

            $item = $array_collected->firstWhere('id', $id_from_request['id']);
            $item['name']=(string) $request['name'];
            $item['rtn']= (string) $request['rtn'];
            $item['addr']= (string) $request['addr'];

            $selected_index = 0;
            $sentry = 0;
            foreach($array_content as $key){
                if($key['id'] == $id_from_request['id']){
                    $selected_index = $sentry;
                }
                $sentry++;
            }

            $array_collected[$selected_index]=$item;

            $re_encoded_content = json_encode($array_collected);

            // La información actualizada se guarda en el archivo .txt
            File::put($fPath, $re_encoded_content . PHP_EOL);

            return response()->json(['message' => 'Se actualizó exitosamente el registro con id: ' . $id_from_request['id']], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Se maneja la excepción, con error de validación.
            return response()->json(['error' => 'ValidationError', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            // Se recibe cualquier otro tipo de excepción.
            return response()->json(['error' => 'ApplicationError', 'message' => $e->getMessage()], 500);
        } 
    }

    public function delete($id)
    {
        try {
            $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

            if (!File::exists($fPath)) {
                return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
            }

            $contents = File::get($fPath);

            $array_content = json_decode($contents, true);

            $checkifIDExists = collect($array_content)->contains('id', $id);

            if(!$checkifIDExists){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acceso denegado. No hay ningun registro con el ID proveido.',
                ], 401);
            }

            $selected_key = 0;
            $sentry = 0;
            foreach($array_content as $key){
                if($key['id'] == $id){
                    $selected_key = $sentry;
                }
                $sentry++;
            }

            if($selected_key > 0){
                array_splice($array_content, $selected_key, $selected_key);
            } else {
                array_splice($array_content, 0, 1);
            }

            $re_encoded_content = json_encode($array_content);

            File::put($fPath, $re_encoded_content . PHP_EOL);

            // Se devuelve una respuesta exitosa.
            return response()->json(['message' => 'El contenido se ha borrado exitosamente.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Se maneja la excepción, con error de validación.
            return response()->json(['error' => 'ValidationError', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            // Se recibe cualquier otro tipo de excepción.
            return response()->json(['error' => 'ApplicationError', 'message' => $e->getMessage()], 500);
        }  
    }
}
