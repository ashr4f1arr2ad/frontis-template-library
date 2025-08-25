<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;

class PageController extends Controller
{
    public function index() {
        $pages = Page::all();
        return response()->json($pages);
    }
}
