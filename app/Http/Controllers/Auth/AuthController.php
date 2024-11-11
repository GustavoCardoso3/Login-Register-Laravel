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
    /**
     * Displays the login page.
     *
     * @return \Illuminate\View\View
     */
    function login(){
        return view('auth.login');
    }

    /**
     * Displays the registration page.
     *
     * @return \Illuminate\View\View
     */
    function register(){
        return view('auth.register');
    }

    /**
     * Processes user login.
     * Validates the email and password, and attempts to authenticate the user.
     * If authentication is successful, redirects to the homepage.
     * Otherwise, returns an invalid credentials error.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Processes user registration.
     * Validates the registration form data and creates a new user in the database.
     * After registration, the user is logged in and redirected to the email verification page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Displays the email verification notice page.
     *
     * @return \Illuminate\View\View
     */
    function verifyNotice(){
        return view('auth.verify-email');
    }

    /**
     * Processes email verification after the user clicks the verification link.
     * Marks the email as verified and redirects the user to the homepage.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function verifyEmail(EmailVerificationRequest $request) {
        $request->fulfill();
     
        return redirect('/index');
    }

    /**
     * Resends the email verification link to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function verifyHandler(Request $request) {
        $request->user()->sendEmailVerificationNotification();
     
        return back()->with('message', 'Verification link sent!');
    }

    /**
     * Processes the Google callback after OAuth authentication.
     * Creates or updates the user in the database with the data returned by Google.
     * After creation/update, the user is logged in and redirected to the homepage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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
    
        Auth::login($user, true);
    
        return redirect(route('index'));
    }

    /**
     * Redirects the user to the Google authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function googleRedirect(){
        return Socialite::driver('google')->redirect();
    }

    /**
     * Processes the GitHub callback after OAuth authentication.
     * Creates or updates the user in the database with the data returned by GitHub.
     * After creation/update, the user is logged in and redirected to the homepage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function githubCallback(){
        $githubUser = Socialite::driver('github')->user();
        $user = User::updateOrCreate(
            ['github_id'=> $githubUser ->id],
            [
                'name'=> $githubUser->name,
                'email' => $githubUser->email,
                'password' => Str::password(12),
                'email_verified_at' => now()
            ]
        );
        Auth::login($user, true);
        
        return redirect(route('index'));
    }

    /**
     * Redirects the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function githubRedirect(){
        return Socialite::driver('github')->redirect();
    }

    /**
     * Logs the user out, clears the session, and redirects to the login page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function logout(){
        Session::flush();
        Auth::logout();
        return redirect(route('login'));
    }
}
