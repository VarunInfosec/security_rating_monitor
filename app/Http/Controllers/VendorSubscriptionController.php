<?php

namespace App\Http\Controllers;

use App\Constant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

class VendorSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|string|unique:users|email',
        ]);

        $domain = extractDomain($request->email);
        $company = extractCompany($domain);

        $companyObj = getCompanyByName($company);
        if($companyObj->isEmpty()){
            $companyId = createCompany($company);
        } else {
            return response([
                "status"  => false,
                "message" => "This company is registered with us. Please ask for an invitation."
            ], 401);            
        }

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

        $vendor = createUser($request, $companyId, true);

        event(new Registered($vendor));

        if(!empty($companyId) && !empty($domainId) && !empty($vendor)){
            createUserDomainRelationship($request->user_id, $domainId, $companyId, Constant::TYPE_VENDOR);
        }

        return response([
            'message' => 'A verification link has been sent on your email.',
            'user'    => $vendor,
            'status'  => true,
            'token'   => $request->session()->token()
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }}
