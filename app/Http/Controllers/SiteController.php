<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;

class SiteController extends Controller
{
    public function index() {
        $sites = Site::all();
        return response()->json($sites);
    }
}
