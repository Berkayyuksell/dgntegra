<?php

namespace App\Service;

class GetInvoiceHtmlService
{
    private $soapUrl;
    private $archiveSoapUrl;

    public function __construct()
    {
        $this->soapUrl = 'https://efaturatest.doganedonusum.com/EFaturaOIB?wsdl';
        $this->archiveSoapUrl = 'https://efaturatest.doganedonusum.com/EArchiveOIB?wsdl';
    }

    /**
     * Normal E-Fatura HTML
     */
    public function getInvoiceHtml(string $sessionId, string $invoiceId, string $direction = 'OUT'): string
    {
        if (!$sessionId) {
            throw new \Exception("Session ID is required");
        }

        $xmlRequest = $this->buildHtmlRequestXml($sessionId, $invoiceId, $direction);
        $response = $this->sendSoapRequest($this->soapUrl, $xmlRequest);

        return $this->parseHtmlResponse($response);
    }

    /**
     * E-Arşiv HTML
     */
    public function getArchiveInvoiceHtml(string $sessionId, string $invoiceId, string $direction = 'OUT'): string
    {
        if (!$sessionId) {
            throw new \Exception("Session ID is required");
        }

        $xmlRequest = $this->buildArchiveHtmlRequestXml($sessionId, $invoiceId, $direction);
        $response = $this->sendSoapRequest($this->archiveSoapUrl, $xmlRequest);

        return $this->parseArchiveHtmlResponse($response);
    }

    /**
     * Fatura tipine göre HTML çek
     */
    public function getHtml(string $sessionId, string $invoiceId, string $invoiceType = 'NORMAL', string $direction = 'OUT'): string
    {
        if (strtoupper($invoiceType) === 'ARCHIVE') {
            return $this->getArchiveInvoiceHtml($sessionId, $invoiceId, $direction);
        }

        return $this->getInvoiceHtml($sessionId, $invoiceId, $direction);
    }

    private function buildHtmlRequestXml(string $sessionId, string $invoiceId, string $direction): string
    {
        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:wsdl="http://schemas.i2i.com/ei/wsdl">
  <soapenv:Header/>
  <soapenv:Body>
    <wsdl:GetInvoiceWithTypeRequest>
      <REQUEST_HEADER>
        <SESSION_ID>{$sessionId}</SESSION_ID>
        <APPLICATION_NAME>ERP</APPLICATION_NAME>
        <CHANNEL_NAME>ERP</CHANNEL_NAME>
        <COMPRESSED>N</COMPRESSED>
      </REQUEST_HEADER>
      <INVOICE_SEARCH_KEY>
        <UUID>{$invoiceId}</UUID>
        <TYPE>HTML</TYPE>
        <DIRECTION>{$direction}</DIRECTION>
      </INVOICE_SEARCH_KEY>
      <HEADER_ONLY>N</HEADER_ONLY>
    </wsdl:GetInvoiceWithTypeRequest>
  </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    private function buildArchiveHtmlRequestXml(string $sessionId, string $invoiceId, string $direction): string
    {
        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:arc="http://schemas.i2i.com/ei/wsdl/archive">
   <soapenv:Header/>
   <soapenv:Body>
      <arc:ArchiveInvoiceReadRequest>
         <REQUEST_HEADER>
            <SESSION_ID>{$sessionId}</SESSION_ID>
            <COMPRESSED>N</COMPRESSED>
         </REQUEST_HEADER>
         <INVOICEID>{$invoiceId}</INVOICEID>
         <PORTAL_DIRECTION>{$direction}</PORTAL_DIRECTION>
         <PROFILE>HTML</PROFILE>
      </arc:ArchiveInvoiceReadRequest>
   </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    private function sendSoapRequest(string $url, string $xmlRequest): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: text/xml;charset=UTF-8",
            "SOAPAction: ''"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 120 saniye timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 30 saniye bağlantı timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("CURL failed: {$curlError}");
        }

        if ($httpCode !== 200) {
            throw new \Exception("HTTP {$httpCode} error on SOAP request");
        }

        return $response;
    }

    private function parseHtmlResponse(string $response): string
    {
        $xml = simplexml_load_string($response);
        if ($xml === false) throw new \Exception("Invalid XML response");

        $xml->registerXPathNamespace('wsdl', 'http://schemas.i2i.com/ei/wsdl');
        $content = $xml->xpath('//wsdl:GetInvoiceWithTypeResponse/INVOICE/CONTENT');

        if (empty($content)) throw new \Exception("HTML content not found in response");

        $decoded = base64_decode((string)$content[0]);
        if ($decoded === false) throw new \Exception("Failed to decode HTML content");

        return $decoded;
    }

    private function parseArchiveHtmlResponse(string $response): string
    {
        $xml = simplexml_load_string($response);
        if ($xml === false) throw new \Exception("Invalid XML response");

        $xml->registerXPathNamespace('arc', 'http://schemas.i2i.com/ei/wsdl/archive');
        $content = $xml->xpath('//arc:ArchiveInvoiceReadResponse/INVOICE');

        if (empty($content)) throw new \Exception("Archive HTML content not found in response");

        $decoded = base64_decode((string)$content[0]);
        if ($decoded === false) throw new \Exception("Failed to decode Archive HTML content");

        return $decoded;
    }
}
