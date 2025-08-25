<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pattern;

class PatternController extends Controller
{
    public function index() {
        $patterns = Pattern::all();
        return response()->json($patterns);
    }
}
