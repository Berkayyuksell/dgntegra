<?php

namespace App\Helper;

use App\Models\cdEInvoiceWebService;

class Helpers
{
    public static function getUsers(){
        $users = cdEInvoiceWebService::where('EInvoiceWebServiceCode','Dogan')->get();
        return $users->map(function($user){
            return [
                'UserName' => $user->UserName,
                'Password' => base64_decode($user->Password),
                'CompanyCode' => $user->CompanyCode,
            ];
        })->toArray();
    }




}
