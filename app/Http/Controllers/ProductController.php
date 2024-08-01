<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index() {
        $fPath = storage_path('app/data/products/products.txt');

        if (!File::exists($fPath)) {
            return response()->json(['message' => 'No se encontró el recurso (.txt).'], 404);
        } else {
            $file_content = File::get($fPath);
            $products = json_decode($file_content);
            return response()->json(['data' => $products], 200);
        };
        //return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $content = $request->input('content');
        if(count($request->all()) >= 1)
        {
            $fPath = storage_path('app/data.txt');
            File::append($fPath, $content . PHP_EOL);
            return response()->json(['message' => 'Se registró el contenido exitosamente.'], 200);
        }
        else {
            return response()->json(['message' => 'No se envío ningún contenido.'], 404);
        }        
    }
}