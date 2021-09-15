<?php

use App\Models\UserDomains;

if(!function_exists('createUserDomainRelationship')){
    function createUserDomainRelationship($userId, $domainId, $companyId, $type){
        return UserDomains::create([
            'user_id'    => $userId,
            'domain_id'  => $domainId,
            'company_id' => $companyId,
            'type'       => $type
        ]);
    }
}

?>