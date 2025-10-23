<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
// use App\Models\MenusModel;

class HomeController extends Controller
{
    public function index()
    {
        $atribute =  'Dashboard';
        return view('home.index', compact('atribute')); // Memanggil view home.blade.php
    }
}
