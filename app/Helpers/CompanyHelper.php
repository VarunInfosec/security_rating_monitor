<?php

use App\Models\Company;

if(!function_exists('extractCompany')){
    function extractCompany($param){
        return explode(".", $param)[0];
    }
}

if(!function_exists('createCompany')){
    function createCompany($company){        
        return Company::create([
            'name' => $company,
            'slug' => $company,
        ])->id;
    }
}

if(!function_exists('getCompanyByName')){
    function getCompanyByName($name){
        return Company::where('name', $name)->get();
    }
}

?>