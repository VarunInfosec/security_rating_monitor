<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Domain;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validateRegisterFields($request);

        $user = $this->createUser($request);

        $this->saveDomain($user->email);

        event(new Registered($user));

        Auth::login($user);

        return response([
            'message' => 'A verification link has been sent on your email.',
            'user'    => Auth::user(),
            'status'  => true,
            'token'   => $request->session()->token()
        ], 201);
    }

    private function validateRegisterFields($request){
        return $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'username'   => 'required|string|unique:users,username',
            'email'      => 'required|string|unique:users,email',
            'password'   => ['required', 'min:8', Rules\Password::defaults()]
        ]);
    }

    private function createUser($request){
        return User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
        ]);
    }

    private function saveDomain($email){
        $domain = explode("@", $email)[1];
        Domain::create([
            'name'               => $domain,
            'domain_verified_at' => date('Y-m-d H:i:s')
        ]);
    }
}
