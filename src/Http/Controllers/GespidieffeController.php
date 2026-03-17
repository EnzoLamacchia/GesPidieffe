<?php

namespace Elamacchia\Gespidieffe\Http\Controllers;

use App\Http\Controllers\Controller;

class GespidieffeController extends Controller
{
    public function index()
    {
        return view('gespidieffe::home');
    }
}
