<?php

namespace Mixc;

class MixcCodeAuth extends MixcAuth
{
    private $mallCode;

    public function __construct($clientId, $clientSecret, $mallCode) {
        parent::__construct($clientId, $clientSecret);
        $this->mallCode = $mallCode;
    }


    public function authMixcCode($code, $webServerRedirectUri) {
        $token_url = MixcConst::getAuthBaseUrl()."/auth/oauth/token?grant_type=authorization_code&code={$code}&client_id=".$this->clientId."&client_secret=".$this->clientSecret."&redirect_uri=".$webServerRedirectUri."&mall_code=".$this->mallCode;
        return $this->mixcCurl->getDataCurl($token_url);
    }
}