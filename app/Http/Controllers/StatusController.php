<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function getStatus() {
        return response()->json(['message' => "El servidor está funcionando.", 'statusCode' => 200], 200);
    }
}
