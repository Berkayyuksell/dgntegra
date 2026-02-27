<?php
namespace App\Service;
use Illuminate\Support\Facades\Http;
use App\Service\GetAuthToken;
use App\Models\EArchiveInvoicesOut;
use Carbon\Carbon;
class GetArchiveService {

    public static function getInvoice($session,$startDate,$endDate){
        $url = 'https://api.doganedonusum.com/EIArchiveWS/EFaturaArchive?wsdl';
        $xmlBodyContent = <<<XML
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:arc="http://schemas.i2i.com/ei/wsdl/archive">
           <soapenv:Header/>
           <soapenv:Body>
              <arc:GetEArchiveInvoiceListRequest>
                 <REQUEST_HEADER>
                    <SESSION_ID>{$session}</SESSION_ID>
                    <APPLICATION_NAME>ERP</APPLICATION_NAME>
                 </REQUEST_HEADER>
                 <START_DATE>{$startDate}T00:00:00+03:00</START_DATE>
                 <END_DATE>{$endDate}T23:59:00+03:00</END_DATE>
                 <HEADER_ONLY>Y</HEADER_ONLY>
                 <READ_INCLUDED>Y</READ_INCLUDED>
              </arc:GetEArchiveInvoiceListRequest>
           </soapenv:Body>
        </soapenv:Envelope>
        XML;


        $http=Http::timeout(240) // 240 saniye timeout (4 dk)
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

                $xml->registerXPathNamespace('arc', 'http://schemas.i2i.com/ei/wsdl/archive');

                $invoices = $xml->xpath('//arc:INVOICE');
                if(!$invoices) {

                    $invoices = $xml->xpath('//INVOICE');
                }

                $data = [];
                foreach($invoices as $invoice) {
                    $data[] = json_decode(json_encode($invoice), true);
                }

               return $data;


    }

    public static function saveDb(array $datas,$companyCode)
    {
        if (empty($datas)) return 0;

        $count = 0;

        foreach ($datas as $invoiceNode) {
            // XML'den gelen veri dizi mi yoksa tek INVOICE mu kontrol
            if (isset($invoiceNode['HEADER'])) {
                $headers = [$invoiceNode]; // tek fatura
            } else {
                $headers = $invoiceNode; // array of invoices
            }

            foreach ($headers as $invoice) {
                $header = $invoice['HEADER'] ?? [];

                if (empty($header)) continue;

                $dataToSave = [
                    'invoice_id' => $header['INVOICE_ID'] ?? null,
                    'uuid' => $header['UUID'] ?? null,
                    'sender_name' => $header['SENDER_NAME'] ?? null,
                    'sender_identifier' => $header['SENDER_IDENTIFIER'] ?? null,
                    'customer_name' => $header['CUSTOMER_NAME'] ?? null,
                    'customer_identifier' => $header['CUSTOMER_IDENTIFIER'] ?? null,
                    'profile_id' => $header['PROFILE_ID'] ?? null,
                    'invoice_type' => $header['INVOICE_TYPE'] ?? null,
                    'earchive_type' => $header['EARCHIVE_TYPE'] ?? null,
                    'sending_type' => $header['SENDING_TYPE'] ?? null,
                    'status' => $header['STATUS'] ?? null,
                    'status_code' => $header['STATUS_CODE'] ?? null,
                    'issue_date' => isset($header['ISSUE_DATE'])
                        ? \Carbon\Carbon::parse($header['ISSUE_DATE'])->format('Ymd H:i:s')
                        : null,
                    'payable_amount' => isset($header['PAYABLE_AMOUNT']) ? (float)$header['PAYABLE_AMOUNT'] : null,
                    'taxable_amount' => isset($header['TAXABLE_AMOUNT']) ? (float)$header['TAXABLE_AMOUNT'] : null,
                    'currency_code' => $companyCode,
                ];


                EArchiveInvoicesOut::updateOrCreate(
                    ['uuid' => $dataToSave['uuid']],
                    $dataToSave
                );

                $count++;
            }
        }

        return $count;
    }





}
