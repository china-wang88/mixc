<?php

namespace Mixc;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MixcCurl
{
    protected $logger;
    protected $remarks;

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
        $this->logger->info($this->remarks,['request'=>$url,'response'=>$res]);
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
        $response = curl_exec($curl);
        curl_close($curl);
        $this->logger->info($this->remarks, ['request'=>['header'=>$header,'token'=>$accesstoken],'response'=>$response]);
        return $response;
    }

    public function setLogger(LoggerInterface  $logger,string $remarks = ''):MixcCurl
    {
        $this->logger = $logger;
        $this->remarks = $remarks;
        return $this;
    }
}