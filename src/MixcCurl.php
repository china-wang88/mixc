<?php

namespace Mixc;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MixcCurl
{
    protected $logger;
    protected $clientId;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function getDataCurl($url) {
        $requestStartTime = (int)(microtime(1) * 1000);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        curl_close($ch);
        $this->logger->info($this->buildCurlGetStr($url), [
            'clientId' => $this->clientId,
            'responseBody' => $res,
            'responseTime' => (int)(microtime(1) * 1000) - $requestStartTime,
            'responseThrow' => '',
        ]);
        return $res;
    }

    public function postDataCurl($url, $header,$accesstoken,$aParam=array())
    {
        $requestStartTime = (int)(microtime(1) * 1000);
        $timeout = 30;
        $headerArray =array("Content-type:".$header['Content-Type']);
        array_push( $headerArray, "Accept:".$header['Accept']);
        array_push($headerArray,"x-ca-signature-type:".$header['x-ca-signature-type']);
        array_push($headerArray,"x-ca-timestamp:".$header['x-ca-timestamp']);
        array_push($headerArray,"x-ca-nonce:".$header['x-ca-nonce']);
        array_push( $headerArray, "x-ca-signature:".$header['x-ca-signature']);
        array_push( $headerArray, "Authorization:Bearer ".$accesstoken);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if( $aParam ){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($aParam,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        }
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        curl_setopt($curl,CURLOPT_TIMEOUT,$timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        $this->logger->info($this->buildCurlPostStr($url, $header, $aParam), [
            'clientId' => $this->clientId,
            'responseBody' => $output,
            'responseTime' => (int)(microtime(1) * 1000) - $requestStartTime,
            'responseThrow' => '',
        ]);
        return $output;
    }

    private function buildCurlPostStr($url, $headers, $requestBody) {
        $curlStr = "curl --location --request POST '".$url."'";
        foreach ($headers as $hKey => $hVal) {
            $curlStr .= " --header '{$hKey}: {$hVal}'";
        }
        $curlStr .= " --data-raw '".json_encode($requestBody)."'";

        return $curlStr;
    }

    private function buildCurlGetStr($url) {
        return "curl --location --request GET '".$url."'";
    }

    public function setLogger(LoggerInterface  $logger):MixcCurl
    {
        $this->logger = $logger;
        return $this;
    }
    public function setClientId(string $clientId):MixcCurl
    {
        $this->clientId = $clientId;
        return $this;
    }
}