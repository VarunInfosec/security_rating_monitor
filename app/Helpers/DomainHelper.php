<?php
use App\Models\Domain;
use App\Constant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

if(!function_exists('extractDomain')){
    function extractDomain($param){
        return explode("@", $param)[1];
    }
}

if(!function_exists('createDomain')){
    function createDomain($domain, $scanId){
        return Domain::create([
            'uuid'    => Str::uuid()->toString(),    
            'name'    => $domain,
            'scan_id' => $scanId
        ])->id;
    }
}

if(!function_exists('getDomainByName')){
    function getDomainByName($name){
        return Domain::where('name', $name)->get();
    }
}

if(!function_exists('fetchScanId')){
    function fetchScanId($param){
        $response = Http::withHeaders([
            "Authorization" => Constant::AUTHORIZATION_KEY
        ])->post(env('SCAN_ID_URL'),[
            "keyword" => Constant::SEARCH_SCAN,
            "domain" => $param
        ]);
        $result = $response->object();
        $data = $result->data;

        $scanId = (empty($data)) ? 0 : $data[count($data) - 1]->scan_id;

        return $scanId;
    }
}

?>