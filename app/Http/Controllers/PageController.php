<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    function index(){
        $user = Auth::user();

        $name = $user->name;
        $email = $user -> email;
        return view('index', compact('name','email'));

    }
}