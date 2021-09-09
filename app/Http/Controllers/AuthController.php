<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $fields = $this->validateRegisterFields($request);
        
        $user = $this->createUser($fields);
        
        $this->saveDomain($user);

        $token = $user->createToken($fields['username'])->plainTextToken;

        if($user){
            $response = [
                'message' => 'User successfully created',
                'token'   => $token
            ];
        }

        return response($response, 201);
    }

    public function login(Request $request){
        $fields = $this->validateLoginFields($request);

        $user = User::where('username', $fields['username'])->first();
        $passwordStatus = Hash::check($fields['password'], $user->password);
        
        if(!$user && !$passwordStatus){
            return response([
                'message' => "Invalid username or password. Please try again."
            ], 401);
        }

        $token = $user->createToken($fields['username'])->plainTextToken;

        return response([
            'message' => 'User Logged in Successfully. In SRM project',
            'token'   => $token
        ], 200);
    }

    public function logout(Request $request){
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged Out'
        ];
    }

    private function validateRegisterFields($request){
        return $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'username'   => 'required|string|unique:users,username',
            'email'      => 'required|string|unique:users,email',
            'password'   => 'required|min:8'
        ]);
    }

    private function validateLoginFields($request){
        return $request->validate([
            'username'   => 'required|string',
            'password'   => 'required'
        ]);
    }

    private function createUser($fields){
        return User::create([
            'first_name' => $fields['first_name'],
            'last_name'  => $fields['last_name'],
            'username'   => $fields['username'],
            'email'      => $fields['email'],
            'password'   => bcrypt($fields['password'])
        ]);
    }

    private function saveDomain($user){
        $domain = explode("@", $user->email)[1];
        Domain::create([
            'user_id'            => $user->id,
            'name'               => $domain,
            'domain_verified_at' => date('Y-m-d H:i:s')
        ]);
    }
}
