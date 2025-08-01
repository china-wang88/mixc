<?php

namespace Mixc;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class MixcAuth
{
    protected $clientId;
    protected $clientSecret;
    protected $mixcCurl;
    protected $logger;
    protected $remarks;
    public function __construct($clientId,$clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = new NullLogger();
        $this->mixcCurl = new MixcCurl();
    }

    public function refreshToken($refreshToken)
    {
        $token_url = MixcConst::getAuthBaseUrl()."/auth/oauth/token?grant_type=refresh_token&&client_id=".$this->clientId."&client_secret=".$this->clientSecret."&refresh_token=".$refreshToken;
        return $this->mixcCurl->getDataCurl($token_url);
    }

    public function setLogger(LoggerInterface $logger,string $remarks = ''):MixcAuth
    {
        $this->remarks = $remarks;
        $this->logger = $logger;
        $this->mixcCurl->setLogger($logger,$remarks);
        return $this;
    }
}