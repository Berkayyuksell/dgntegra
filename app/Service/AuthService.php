<?php

namespace App\Service;
use Illuminate\Support\Facades\Http;

class AuthService
{

    public static function GetAuthToken($username ,$password){
        $url = "https://api.doganedonusum.com/AuthenticationWS?wsdl";

        $xmlBodyContent = <<<XML
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsdl="http://schemas.i2i.com/ei/wsdl">
        <soapenv:Header/>
        <soapenv:Body>
            <wsdl:LoginRequest>
            <REQUEST_HEADER>
                <SESSION_ID>-1</SESSION_ID>
                <APPLICATION_NAME>ERP</APPLICATION_NAME>
                <CHANNEL_NAME>ERP</CHANNEL_NAME>
            </REQUEST_HEADER>
            <USER_NAME>{$username}</USER_NAME>
            <PASSWORD>{$password}</PASSWORD>
            </wsdl:LoginRequest>
        </soapenv:Body>
        </soapenv:Envelope>
        XML;


        $http=Http::withHeaders([
            'Content-Type'=>'text/xml;charset=UTF-8',
            'SOAPAction'=>'""'
        ])->withOptions(['verify' => false])->withBody($xmlBodyContent,"text/xml")->post($url);
        $xml = simplexml_load_string($http->body());
        $xml->registerXPathNamespace('ns3', 'http://schemas.i2i.com/ei/wsdl');
        $result = $xml->xpath('//ns3:LoginResponse/SESSION_ID');

        return $result ? (string) $result[0] : null;
    }


}
