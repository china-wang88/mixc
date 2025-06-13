<?php

namespace Mixc;

class MixcConst {
    public static $ENV_NAME = 'test';

    //https://app.mixcapp.com
    public static function getGatewayBaseUrl() {
        return 'https://'.self::$ENV_NAME.'.mixcapp.com/gateway';
    }

    public static function getAuthBaseUrl() {
        return 'https://'.self::$ENV_NAME.'.mixcapp.com';
    }
}