<?php

use Illuminate\Support\Facades\Hash;
use App\Models\User;

if(!function_exists('createUser')){
    function createUser($request, $companyId, $isVendor=false){
        return User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'company_id' => (!$isVendor) ? $companyId : 0,
            'vendor_id'  => ($isVendor) ? $companyId : 0,
        ]);
    }
}

if(!function_exists('getParentCompanyId')){
    function getParentCompanyId($id){
        return User::find($id)->company_id;
    }
}

if(!function_exists('getUser')){
    function getUser($id){
        return User::find($id);
    }
}

?>