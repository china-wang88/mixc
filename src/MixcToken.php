<?php

namespace Mixc;

class MixcToken extends MixcAuth
{
    public function __construct($clientId, $clientSecret) {
        parent::__construct($clientId, $clientSecret);
    }

    public function authMixcToken($mixcAccessToken)
    {
        $token_url = MixcConst::getAuthBaseUrl()."/auth/oauth/token?grant_type=mixc_token&client_id=".$this->clientId."&client_secret=".$this->clientSecret."&mixc_access_token=".$mixcAccessToken;
        return $this->mixcCurl->getDataCurl($token_url);
    }
}