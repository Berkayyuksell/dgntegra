<?php
namespace App\Service;
use Illuminate\Support\Facades\Http;
use App\Models\EArchiveInvoicesOut;
use App\Service\AuthService;
use Carbon\Carbon;

class GetInvoiceService{

    public static function getInvoice($session,$startDate,$endDate,$direction){
        $url = "https://api.doganedonusum.com/EFaturaOIB?wsdl";
        $xmlBodyContent = <<<XML
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                        xmlns:tns="http://schemas.i2i.com/ei/wsdl">
        <soapenv:Header/>
        <soapenv:Body>
            <tns:GetInvoiceRequest>
            <REQUEST_HEADER>
                <SESSION_ID>{$session}</SESSION_ID>
                <APPLICATION_NAME>ERP</APPLICATION_NAME>
                <CHANNEL_NAME>ERP</CHANNEL_NAME>
                <COMPRESSED>Y</COMPRESSED>
            </REQUEST_HEADER>
            <INVOICE_SEARCH_KEY>
                <START_DATE>{$startDate}</START_DATE>
                <END_DATE>{$endDate}</END_DATE>
                <READ_INCLUDED>true</READ_INCLUDED>
                <DIRECTION>{$direction}</DIRECTION>
            </INVOICE_SEARCH_KEY>
            <HEADER_ONLY>Y</HEADER_ONLY>
            </tns:GetInvoiceRequest>
        </soapenv:Body>
        </soapenv:Envelope>
        XML;

        $http=Http::timeout(120) // 120 saniye timeout
            ->withHeaders([
                'Content-Type'=>'text/xml;charset=UTF-8',
                'SOAPAction'=>'""'
            ])
            ->withOptions(['verify' => false])
            ->withBody($xmlBodyContent,"text/xml")
            ->post($url);
        $xml = simplexml_load_string($http->body());
        $xml->registerXPathNamespace('ns3', 'http://schemas.i2i.com/ei/wsdl');
        $result = $xml->xpath('//ns3:LoginResponse/SESSION_ID');

                $xml = simplexml_load_string($http->body());

                $xml->registerXPathNamespace('tns', 'http://schemas.i2i.com/ei/wsdl');

                $invoices = $xml->xpath('//tns:GetInvoiceResponse');

                // Eğer yanıt varsa array olarak döndür
                if ($invoices && count($invoices) > 0) {
                    $data = json_decode(json_encode($invoices), true);
                    return $data;
                }

                return [];


    }

    public static function saveDb(array $datas,$direction,$companyCode)
{
    if($direction == 'IN'){
        $modelClass =  \App\Models\InvoicesIn::class;
    }else{
        $modelClass =  \App\Models\InvoicesOut::class;
    }


    if (!isset($datas[0]['INVOICE'])) {
        return 0; // kayıt yok
    }

    $count = 0;

    foreach ($datas[0]['INVOICE'] as $invoice) {
        $header = $invoice['HEADER'] ?? [];
        if (empty($header)) continue;

        // Güvenli tarih parse
        $safeParseDate = function ($value) {
            if (empty($value) || $value === '0000-00-00 00:00:00') {
                return null;
            }
            try {
                return Carbon::parse($value)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return null;
            }
        };

        // Güvenli float parse
        $safeFloat = function ($value) {
            return is_numeric($value) ? (float)$value : null;
        };
        $dataToSave = [
            'external_id' => $invoice['@attributes']['ID'] ?? null,
            'list_id' => $invoice['@attributes']['LIST_ID'] ?? null,
            'uuid' => $invoice['@attributes']['UUID'] ?? null,
            'sender' => $header['SENDER'] ?? null,
            'receiver' => $header['RECEIVER'] ?? null,
            'supplier' => $header['SUPPLIER'] ?? null,
            'customer' => $header['CUSTOMER'] ?? null,
            'issue_date' => $safeParseDate($header['ISSUE_DATE'] ?? null),
            'payable_amount' => $safeFloat($header['PAYABLE_AMOUNT'] ?? null),
            'from_address' => $header['FROM'] ?? null,
            'to_address' => $header['TO'] ?? null,
            'profile_id' => $header['PROFILEID'] ?? null,
            'invoice_type_code' => $header['INVOICE_TYPE_CODE'] ?? null,
            'status' => $header['STATUS'] ?? null,
            'status_description' => $header['STATUS_DESCRIPTION'] ?? null,
            'gib_status_code' => $header['GIB_STATUS_CODE'] ?? null,
            'gib_status_description' => $header['GIB_STATUS_DESCRIPTION'] ?? null,
            'cdate' => $safeParseDate($header['CDATE'] ?? null),
            'envelope_identifier' => $header['ENVELOPE_IDENTIFIER'] ?? null,
            'status_code' => $header['STATUS_CODE'] ?? null,
            'line_extension_amount' => $safeFloat($header['LINE_EXTENSION_AMOUNT'] ?? null),
            'tax_exclusive_total_amount' => $safeFloat($header['TAX_EXCLUSIVE_TOTAL_AMOUNT'] ?? null),
            'tax_inclusive_total_amount' => $safeFloat($header['TAX_INCLUSIVE_TOTAL_AMOUNT'] ?? null),
            'allowance_total_amount' => 100,
            'company_code' => $companyCode,
        ];





        $modelClass::updateOrCreate(
            ['uuid' => $dataToSave['uuid']],
            $dataToSave
        );

        $count++;
    }

    return $count; // Kaç kayıt kaydedildi
}




}
