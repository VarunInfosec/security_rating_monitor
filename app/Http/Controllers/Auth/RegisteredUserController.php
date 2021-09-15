<?php

namespace App\Http\Controllers\Auth;

use App\Constant;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Validate request data.
        $this->validateRegisterFields($request);

        $domain = extractDomain($request->email); // Extract domain name from email
        $company = extractCompany($domain);       // Extract company name from domain

        $companyId = 0;
        $domainId = 0;

        // If company exists then return message else create company.
        $companyObj = getCompanyByName($company);

        if($companyObj->isEmpty()){
            $companyId = createCompany($company);
        } else {
            return response([
                "status"  => false,
                "message" => "This company is registered with us. Please ask for an invitation."
            ], 401);            
        }

        // If domain exists then return message else create domain.
        $domainObj = getDomainByName($domain);
        
        if($domainObj->isEmpty()){
            $scanId = fetchScanId($domain);
            $domainId = createDomain($domain, $scanId);
        } else {
            return response([
                "status"  => false,
                "message" => "This domain is registered with us. Please ask for an invitation."
            ], 401);
        }

        // Create User
        $user = createUser($request, $companyId);

        // Fire event to send verification email
        event(new Registered($user));

        // Create login session 
        Auth::login($user);
        
        // Create User Domain Relationship
        if(!empty($companyId) && !empty($domainId) && !empty($user)){
            createUserDomainRelationship($user->id, $domainId, $companyId, Constant::TYPE_COMPANY);
        }

        // At last send final response message.
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
            'email'      => 'required|string|unique:users|email',
            'password'   => ['required', 'min:8', Rules\Password::defaults()]
        ]);
    }
}
