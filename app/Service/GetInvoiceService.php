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

    public static function saveDb(array $datas, $direction, $companyCode)
    {
        $modelClass = $direction === 'IN'
            ? \App\Models\InvoicesIn::class
            : \App\Models\InvoicesOut::class;

        if (!isset($datas[0]['INVOICE'])) {
            return 0;
        }

        $invoices = $datas[0]['INVOICE'];

        if (isset($invoices['HEADER']) || isset($invoices['@attributes'])) {
            $invoices = [$invoices];
        }

        $safeString = function ($value) {
            if (is_array($value)) {
                if (isset($value[0]) && !is_array($value[0])) return (string) $value[0];
                foreach ($value as $k => $v) {
                    if ($k !== '@attributes' && !is_array($v)) return (string) $v;
                }
                return null;
            }
            return $value;
        };

        $safeFloat = function ($value) {
            if (is_array($value)) {
                if (isset($value[0]) && is_numeric($value[0])) return (float) $value[0];
                foreach ($value as $k => $v) {
                    if ($k !== '@attributes' && is_numeric($v)) return (float) $v;
                }
                return null;
            }
            return is_numeric($value) ? (float) $value : null;
        };

        $safeParseDate = function ($value) use ($safeString) {
            $value = $safeString($value);
            if (empty($value) || $value === '0000-00-00 00:00:00') {
                return null;
            }
            try {
                return Carbon::parse($value)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return null;
            }
        };

        $count = 0;

        foreach ($invoices as $invoice) {
            if (!isset($invoice['HEADER'])) continue;

            try {
                $header = $invoice['HEADER'];

                $dataToSave = [
                    'external_id'              => $safeString($invoice['@attributes']['ID'] ?? null),
                    'list_id'                  => $safeString($invoice['@attributes']['LIST_ID'] ?? null),
                    'uuid'                     => $safeString($invoice['@attributes']['UUID'] ?? null),
                    'sender'                   => $safeString($header['SENDER'] ?? null),
                    'receiver'                 => $safeString($header['RECEIVER'] ?? null),
                    'supplier'                 => $safeString($header['SUPPLIER'] ?? null),
                    'customer'                 => $safeString($header['CUSTOMER'] ?? null),
                    'issue_date'               => $safeParseDate($header['ISSUE_DATE'] ?? null),
                    'payable_amount'           => $safeFloat($header['PAYABLE_AMOUNT'] ?? null),
                    'from_address'             => $safeString($header['FROM'] ?? null),
                    'to_address'               => $safeString($header['TO'] ?? null),
                    'profile_id'               => $safeString($header['PROFILEID'] ?? null),
                    'invoice_type_code'        => $safeString($header['INVOICE_TYPE_CODE'] ?? null),
                    'status'                   => $safeString($header['STATUS'] ?? null),
                    'status_description'       => $safeString($header['STATUS_DESCRIPTION'] ?? null),
                    'gib_status_code'          => $safeString($header['GIB_STATUS_CODE'] ?? null),
                    'gib_status_description'   => $safeString($header['GIB_STATUS_DESCRIPTION'] ?? null),
                    'cdate'                    => $safeParseDate($header['CDATE'] ?? null),
                    'envelope_identifier'      => $safeString($header['ENVELOPE_IDENTIFIER'] ?? null),
                    'status_code'              => $safeString($header['STATUS_CODE'] ?? null),
                    'line_extension_amount'     => $safeFloat($header['LINE_EXTENSION_AMOUNT'] ?? null),
                    'tax_exclusive_total_amount'=> $safeFloat($header['TAX_EXCLUSIVE_TOTAL_AMOUNT'] ?? null),
                    'tax_inclusive_total_amount'=> $safeFloat($header['TAX_INCLUSIVE_TOTAL_AMOUNT'] ?? null),
                    'allowance_total_amount'   => 100,
                    'company_code'             => $companyCode,
                ];

                $modelClass::updateOrCreate(
                    ['uuid' => $dataToSave['uuid']],
                    $dataToSave
                );

                $count++;
            } catch (\Exception $e) {
                \Log::warning("Fatura kayıt hatası [{$direction}] UUID:" . ($invoice['@attributes']['UUID'] ?? '?') . " - " . $e->getMessage());
                continue;
            }
        }

        return $count;
    }




}
