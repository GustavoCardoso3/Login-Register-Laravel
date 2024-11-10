<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;


class AuthManager extends Controller
{
    // GET
    function login(){
        return view('login');
    }

    function register(){
        return view('register');
    }

    // POST
    function loginPost(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->email;
        $password = $request->password;
        $remember = $request->has('remember');
        if (Auth::attempt(['email'=>$email,'password'=>$password],$remember)){
            return redirect()->intended(route('index'));
        }
        return redirect(route('login'))->with('error', 'Invalid credentials');
    }

    function registerPost(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/[0-9]/',
            'confirm-password' => 'required|same:password'
        ], [
            'confirm-password.same' => 'The confirmation password must match the password.',
            'password.regex' => 'You need at least one number in the password',
        ]);

        $credentials['name'] = $request->name;
        $credentials['password'] = Hash::make($request->password);
        $credentials['email'] = $request->email;

        $user = User::create($credentials);

        if(!$user){
            return redirect(route('register'))->with('error', 'Registration failed.');
        }
        return redirect(route('login'))->with('success', 'Registration completed, you can login now!');
    }

    function logout(){
        Session::flush();
        Auth::logout();
        return redirect(route('login'));
    }
}
