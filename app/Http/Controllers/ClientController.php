<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ClientController extends Controller
{
    public function index() {
        $fPath = storage_path('app/data/clients/clients.txt');
        if (!File::exists($fPath)) {
            return response()->json(['message' => 'No se encontró el recurso (.txt).'], 404);
        } else {
            $file_content = File::get($fPath);
            $products = json_decode($file_content);
            return response()->json(['data' => $products], 200);
        };
    }
}
