<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Authenticate a user session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authenticate(Request $request){
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required'
        ]);

        if(Auth::attempt($credentials)){
            $request->session()->regenerate();

            return response([
                'message' => "Logged In",
                'user' => Auth::user(),
                'token' => $request->session()->token()
            ], 201);
        }

        return response([
            'message' => "Invalid username or password. Please try again.",
        ], 401);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if(!Auth::check()){
            return response([
                'message' => "Logged Out",
                'token' => $request->session()->token()
            ], 201);
        }
    }
}
