<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Exception;

class ProductController extends Controller
{
    private string $foldername = 'products';
    private string $filename = 'products.txt';

    public function read() {
        $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

        if (!File::exists($fPath)) {
            return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
        } else {
            $file_content = File::get($fPath);
            $products = json_decode($file_content);
            return response()->json(['data' => $products], 200);
        };
        //return response()->json($products);
    }

    public function store(Request $request)
    {
        try {
            // Valida la información que es recibida
            $request->validate([
                'productName' => 'required|string',
                'quantity' => 'required|numeric',
                'price' => 'required|numeric'
            ]);

            // Se define la ruta del archivo que contiene los datos de los clientes
            $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

            // Se verifica si el fichero existe, en caso de no hacerlo se devuelve un mensaje de error. 
            if (!File::exists($fPath)) {
                return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
            }

            // Se lee el contenido del archivo
            $contents = File::get($fPath);

            // Se decodifica el contenido de la información del archivo .txt, a un formato de array compatible con JSON.
            $array_content = json_decode($contents, true);

            // Se obtiene el contenido del request, seguidamente se decodifica a un formato de array compatible con JSON. 
            $request_content = $request->getContent();
            $data = json_decode($request_content, true);

            $array_collected = collect($array_content);
            
            // Se aplica parseo a los valores de las siguientes llaves, para evitar que se guarden como String.

            /*
            $id_counter = 0;

            if(!end($array_content)){
                $id_counter++;
            } else {
                $last_item = last($array_content);
                $id_counter = (int) $last_item['id'];
                $id_counter++;
            }
            */

            $id_counter = (int) $array_collected->max('id');
            $id_counter++;

            $data['id'] = $id_counter;
            
            $lastKey = array_key_last($data);
            $lastElement = [$lastKey => $data[$lastKey]];

            $data = $lastElement + $data;
            
            $data['quantity'] = (int) $data['quantity'];
            $data['price'] = (float) $data['price'];
            
            // El índice nuevo se guarda en el arreglo de información
            $array_content[] = $data;

            // El arreglo con los datos nuevos se codifica nuevamente a un string JSON.
            $re_encoded_content = json_encode($array_content);

            // La información actualizada se guarda en el archivo .txt
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

    public function get($id)
    {
        try{
            // Se define la ruta del archivo que contiene los datos de los clientes
            $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

            // Se verifica si el fichero existe, en caso de no hacerlo se devuelve un mensaje de error. 
            if (!File::exists($fPath)) {
                return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
            }

            // Se lee el contenido del archivo
            $contents = File::get($fPath);

            // Se decodifica el contenido de la información del archivo .txt, a un formato de array compatible con JSON.
            $array_content = json_decode($contents, true);

            $array_collected = collect($array_content);

            $checkifIDExists = $array_collected->contains('id', $id);

            if(!$checkifIDExists){
                return response()->json([
                    'error' => 'Invalid',
                    'message' => 'Acceso denegado. No hay ningun registro con el ID proveido.',
                ], 401);
            }
            
            $item = $array_collected->where('id', $id);

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

    public function update(Request $request)
    {
        try {
            // Valida la información que es recibida
            $request->validate([
                'id' => 'required|numeric',
                'productName' => 'required|string',
                'quantity' => 'required|numeric',
                'price' => 'required|numeric'
            ]);

            $id_from_request = $request->only('id');

            // Se define la ruta del archivo que contiene los datos de los clientes
            $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

            // Se verifica si el fichero existe, en caso de no hacerlo se devuelve un mensaje de error. 
            if (!File::exists($fPath)) {
                return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
            }

            // Se lee el contenido del archivo
            $contents = File::get($fPath);

            // Se decodifica el contenido de la información del archivo .txt, a un formato de array compatible con JSON.
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
            $item['productName']=(string) $request['productName'];
            $item['quantity']= (int) $request['quantity'];
            $item['price']= (float) $request['price'];

            $selected_index = 0;
            $sentry = 0;
            foreach($array_content as $key){
                if($key['id'] == $id_from_request['id']){
                    $selected_index = $sentry;
                }
                $sentry++;
            }
/*
            $itemIndex = $array_collected->search(function($item) {
                return $item->id === $id_from_request->id;
            });
            */

            //array_collected->push($item);

            $array_collected[$selected_index]=$item;

            // El arreglo con los datos nuevos se codifica nuevamente a un string JSON.
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
            //$id->validate('required|integer');
            // Se define la ruta del archivo que contiene los datos de los clientes
            $fPath = storage_path('app/data/'. $this->foldername .'/'. $this->filename .'');

            // Se verifica si el fichero existe, en caso de no hacerlo se devuelve un mensaje de error. 
            if (!File::exists($fPath)) {
                return response()->json(['message' => 'No se encontro el recurso necesario (.txt).'], 404);
            }

            // Se lee el contenido del archivo
            $contents = File::get($fPath);

            // Se decodifica el contenido de la información del archivo .txt, a un formato de array compatible con JSON.
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
                    //return response()->json(['message' => 'El id encontrado es: ' . (string) $key['id'] ], 200);
                    //unset($array_content[$key]);
                    break;
                }
                $sentry++;
            }

            if($selected_key > 0){
                array_splice($array_content, $selected_key, $selected_key);
            } else {
                array_splice($array_content, 0, 1);
            }
            //unset($array_content[$selected_key]);

            //$filteredArray = collect($array_content)->except('id', $id);

            /*
            foreach(array_keys($array_content) as $key) {
                if($array_content[$key]['id'] == $id){
                    unset($array_content[$key]);
                }
            }
            */

            /*
            $filteredArray = array_map(function($array) {
                unset($array['id']);
                return $array;
            }, $array_content);
            */

            // El arreglo con los datos nuevos se codifica nuevamente a un string JSON.
            $re_encoded_content = json_encode($array_content);

            // La información actualizada se guarda en el archivo .txt
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