<?php

namespace Mixc;

class MixcCodeAuth extends MixcAuth
{
    public function __construct($clientId, $clientSecret) {
        parent::__construct($clientId, $clientSecret);
    }


    public function authMixcCode($code,$redirectUrl)
    {
        $token_url = MixcConst::getAuthBaseUrl()."/auth/oauth/token?scope=read&grant_type=authorization_code&code={$code}&client_id=".$this->clientId."&client_secret=".$this->clientSecret."&redirect_uri=".$redirectUrl;
        return $this->mixcCurl->getDataCurl($token_url);
    }
}