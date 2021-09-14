<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Domain;
use App\Models\Company;
use App\Models\UserDomains;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

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
        // $this->validateRegisterFields($request);

        $domain = $this->extractDomain($request->email);
        $company = $this->extractCompany($domain);       

        $companyId = 0;
        $domainId = 0;

        $companyObj = Company::where('name', $company)->get();

        if($companyObj->isEmpty()){
            $companyId = Company::create([
                'name' => $company,
                'slug' => $company,
            ])->id;
        } else {
            return response([
                "status"  => false,
                "message" => "This company is registered with us. Please ask for an invitation."
            ], 401);            
        }

        $domainObj = Domain::where(['name' => $domain])->get();
        
        if($domainObj->isEmpty()){
            $scanId = $this->fetchScanId($domain);
            $domainId = Domain::create([
                'uuid'    => Str::uuid()->toString(),    
                'name'    => $domain,
                'scan_id' => $scanId
            ])->id;
        } else {
            return response([
                "status"  => false,
                "message" => "This domain is registered with us. Please ask for an invitation."
            ], 401);
        }

        $user = $this->createUser($request, $companyId);

        event(new Registered($user));

        Auth::login($user);
        
        if(!empty($companyId) && !empty($domainId) && !empty($user)){
            $this->createUserDomainRelationship($user->id, $domainId, $companyId);
        }        

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

    private function extractDomain($email){
        return explode("@", $email)[1];
    }
    

    private function extractCompany($domain){
        return explode(".", $domain)[0];
    }
    
    private function createUser($request, $companyId){
        return User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'company_id' => $companyId
        ]);
    }

    private function fetchScanId($domain){
        $response = Http::withHeaders([
            "Authorization" => "Basic c3JtX2JhY2tlbmRfYXBpOnE1IUhMLylrcEItUW5ZWig="
        ])->post(env('SCAN_ID_URL'),[
            "keyword" => "SearchScan",
            "domain" => $domain
        ]);
        $result = $response->object();
        $dataCount = count($result->data);
        return $result->data[$dataCount-1]->scan_id;
    }
    
    private function createCompany($company){

        $this->companyExists($company);
        
        return Company::create([
            'name' => $company,
            'slug' => $company,
        ])->id;
    }
    
    private function createDomain($domain){
        if(!$this->domainExists($domain)){
            return response([
                "status"  => false,
                "message" => "This domain is registered with us. Ask for an invitation."
            ], 201);
            // return $this->errorResponse("This domain is registered with us. Ask for an invitation.");
        } else {
            return Domain::create([
                'uuid' => Str::uuid()->toString(),    
                'name' => $domain
            ])->id;
        }
    }
    
    private function createUserDomainRelationship($userId, $companyId, $domainId){
        return UserDomains::create([
            'user_id'    => $userId,
            'company_id' => $companyId,
            'domain_id'  => $domainId,
        ]);
    }

    private function companyExists($company){
        $result = Company::where(['name' => $company])->get();

        if(!$result->isEmpty()){
            return response([
                "status"  => false,
                "message" => "This domain is registered with us. Ask for an invitation."
            ], 201);
        }
        // return $result->isEmpty();
    }
    
    private function domainExists($domain){
        $result = Domain::where(['name' => $domain])->get();
        return $result->isEmpty();
    }

    private function errorResponse($message){
        return response([
            "status"  => false,
            "message" => $message
        ], 401);
    }
}
