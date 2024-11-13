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
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

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
            'password' => 'required|min:8|max:32|regex:/[0-9]/',
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

        $user = User::where('email', $googleUser->email)->first();
    
        if ($user) {
            if (!$user->google_id) {
                $user->google_id = $googleUser->id;
                $user->save();
            }
        } else {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Str::random(12),
                'google_id' => $googleUser->id,
                'email_verified_at' => now()
            ]);
        }
    
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

        $user = User::where('email', $githubUser->email)->first();
    
        if ($user) {
            if (!$user->github_id) {
                $user->github_id = $githubUser->id;
                $user->save();
            }
        } else {
            $user = User::create([
                'name' => $githubUser->name,
                'email' => $githubUser->email,
                'password' => Str::random(12), // You might consider generating or encrypting a random password
                'github_id' => $githubUser->id,
                'email_verified_at' => now()
            ]);
        }
    
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
     * Displays the forgot password page.
     *
     * @return \Illuminate\View\View
     */
    function forgotPassword(){
        return view('auth.forgot-password');
    }

    /**
     * Handles a request to send a password reset link.
     * Validates the provided email and checks if the user is registered using standard credentials.
     * Password reset is not permitted for accounts linked with Google or GitHub.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function forgotPasswordRequest(Request $request) {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
    
        if ($user && ($user->google_id || $user->github_id)) {
            return back()->withErrors(['email' => 'Password reset is not allowed for accounts linked with Google or GitHub.']);
        }
    
        $status = Password::sendResetLink(
            $request->only('email')
        );
    
        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Displays the password reset page for a given token.
     *
     * @param  string  $token  The password reset token.
     * @return \Illuminate\View\View
     */
    function resetPassword(string $token) {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handles the request to reset the user's password.
     * Validates input, checks token validity, and updates the password if valid.
     * Triggers the PasswordReset event upon successful update.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    function resetPasswordRequest(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|max:32|regex:/[0-9]/',
            'confirm-password' => 'required|same:password'
        ], [
            'confirm-password.same' => 'The confirmation password must match the password.',
            'password.regex' => 'You need at least one number in the password',
        ]);
        
        $status = Password::reset(
            $request->only('email', 'password', 'confirm-password', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );
     
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
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
