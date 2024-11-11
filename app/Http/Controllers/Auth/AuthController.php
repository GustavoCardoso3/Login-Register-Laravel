<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Laravel\Socialite\Facades\Socialite;



class AuthController extends Controller
{
    function login(){
        return view('auth.login');
    }

    function register(){
        return view('auth.register');
    }

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

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('verification.notice'))->with('success', 'Registration completed! Please verify your email.');
    }
    function verifyNotice(){
        return view('auth.verify-email');
    }
    function verifyEmail(EmailVerificationRequest $request) {
        $request->fulfill();
     
        return redirect('/index');
    }
    function verifyHandler(Request $request) {
        $request->user()->sendEmailVerificationNotification();
     
        return back()->with('message', 'Verification link sent!');
    }

    function googleCallback(){
        $googleUser = Socialite::driver('google')->user();
    
        $user = User::updateOrCreate(
            ['google_id'=> $googleUser ->id],
            [
                'name'=> $googleUser->name,
                'email' => $googleUser->email,
                'password' => Str::password(12),
                'email_verified_at' => now()
            ]
        );
    
        Auth::login($user);
    
        return redirect(route('index'));
        
    }
    function googleRedirect(){
        return Socialite::driver('google')->redirect();
    }

    function logout(){
        Session::flush();
        Auth::logout();
        return redirect(route('login'));
    }
}
