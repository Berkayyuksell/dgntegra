<?php

namespace App\Helper;

use App\Models\cdEInvoiceWebService;

class Helpers
{
    public static function getCurrAcc(){
        return null;
    }

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

    public static function getUser($companyCode){
        $user = cdEInvoiceWebService::where('EInvoiceWebServiceCode','Dogan')
            ->where('companyCode','=',$companyCode)
            ->first();;

        if (!$user) {
            return null; // veya boş array []
        }

        return [
            'UserName'    => $user->UserName,
            'Password'    => base64_decode($user->Password),
            'CompanyCode' => $user->CompanyCode,
        ];
    }




}
