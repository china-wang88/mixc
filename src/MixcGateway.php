<?php

namespace Mixc;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class MixcGateway
{
    private $mallCode;
    private $accessToken;
    private $sessionKey;
    private $logger;
    private $remarks;
    private $mixcCurl;

    private $userInfoUrl = '/api/open/members/currently_logged';
    private $userGroupUrl = '/api/open/members/currently_logged/groups';
    private $userPointsBalanceUrl = '/api/open/points/balance';
    private $deductUserPointsUrl = '/api/open/points/deduct';
    private $rollbackPointsUrl = '/api/open/points/cancel';
    private $receivePointsUrl = '/api/open/coupons/self/receive';


    public function __construct($clientId,$accessToken,$sessionKey)
    {
        $this->accessToken = $accessToken;
        $this->sessionKey = $sessionKey;
        $this->logger = new NullLogger();
        $this->mixcCurl = new MixcCurl();
        $this->mixcCurl->setClientId($clientId);
    }

    public function mallCode($mallCode)
    {
        $this->mallCode = $mallCode;
        return $this;
    }

    /**
     * 查询当前登录的用户信息
     */
    public function getCurrentUserInfo(){
        $header = static::getSignHeader(false,$this->userInfoUrl,$this->sessionKey);
        return $this->mixcCurl->postDataCurl( MixcConst::getGatewayBaseUrl().$this->userInfoUrl, $header, $this->accessToken);
    }

    /**
     * 查询当前登录用户的专属层级信息
     */
    public function getCurrentUserGroup()
    {
        $query = $this->userGroupUrl."?mallCode=".$this->mallCode;
        $url =  MixcConst::getGatewayBaseUrl().$query;
        $header = static::getSignHeader(false,$query,$this->sessionKey);
        return $this->mixcCurl->postDataCurl( $url, $header, $this->accessToken );
    }


    /**
     * @return array
     * {
     * "code": 0,
     * "success": true,
     * "message": "",
     * "data": {
     * "availablePoints": "0", 可用积分
     * "holdPoints": "0", 冻结积分
     * "points": "0", 积分余额，即availablePoints+holdPoints
     * "expiredPoints": "0", 过期积分
     * "expiredDate": "xxx" 过期日期（非必有，格式为 "yyyy-MM-dd HH:mm:ss"）
     * }
     * }
     */
    public function queryUserPointsBalance(){
        $query = $this->userPointsBalanceUrl."?mallCode=".$this->mallCode."&source=h5";
        $url = MixcConst::getGatewayBaseUrl().$query;
        $header = static::getSignHeader(false,$query,$this->sessionKey);
        return $this->mixcCurl->postDataCurl( $url, $header, $this->accessToken );
    }


    /**
     * @param string $tradeId 业务ID或交易流水号（全局唯一）
     * @param int $value 扣减积分值
     * @param string $remarks 备注
     * @return array
     *
     * {
     * "code": 0,
     * "success": true,
     * "message": "扣减成功",
     * "data": {}
     * }
     */
    public function deductUserPoints($tradeId, $value, $remarks = '')
    {
        $url = MixcConst::getGatewayBaseUrl().$this->deductUserPointsUrl;

        $aParam = array(
            'mallCode' => $this->mallCode,
            'bizId' => $tradeId,
            'bizType' => 'o706',
            'value' => $value,
            'remarks' => $remarks,
        );

        $header = static::getSignHeader(true,$this->deductUserPointsUrl,$this->sessionKey,$aParam);
        return $this->mixcCurl->postDataCurl($url, $header, $this->accessToken,$aParam );
    }

    /**
     * @param string $tradeId 业务ID或交易流水号（全局唯一）
     * @param int $value 扣减积分值
     * @param string $remarks 备注
     * @return array
     *
     * {
     * "code": 0,
     * "success": true,
     * "message": "取消成功",
     * "data": {} 返回数据(如有值，则返回重复取消积分已存在的交易流水ID)
     * }
     */
    function rollbackUserPoints($tradeId,$value,$remarks)
    {
        $url = MixcConst::getGatewayBaseUrl().$this->rollbackPointsUrl;

        $aParam = array(
            'mallCode' => $this->mallCode,
            'bizId' => $tradeId,
            'bizType' => 'i706',
            'value' => $value,
            'remarks' => $remarks,
        );
        $header = static::getSignHeader(true,$this->rollbackPointsUrl,$this->sessionKey,$aParam);
        return $this->mixcCurl->postDataCurl( $url, $header, $this->accessToken,$aParam );
    }

    function receiveCoupons($couponId,$eventId)
    {
        $url = MixcConst::getGatewayBaseUrl().$this->receivePointsUrl;

        $aParam = [
            'mallCode' => $this->mallCode,
            'type' => 'thirdparty',
            'id' => $couponId,
            'eventId' => $eventId
        ];
        $header = static::getSignHeader(true,$this->receivePointsUrl,$this->sessionKey,$aParam);
        return $this->mixcCurl->postDataCurl( $url, $header, $this->accessToken,$aParam );
    }

    private static function getSignHeader($ispost,$query,$key,$aParam=[])
    {
        $nonce = uniqid();
        list($t1, $t2) = explode(' ', microtime());
        $timestamp = (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
        $header = [];
        $header['Accept'] = 'application/json';
        $header['Content-Type'] = 'application/json';
        $header['x-ca-signature-type'] = 'HmacSHA256';
        $header['x-ca-timestamp'] = $timestamp;
        $header['x-ca-nonce'] = $nonce;
        $method = !$ispost ? 'GET' : 'POST';
        $sign = static::sign($method, $header, $query,$key,$aParam);
        $header['x-ca-signature'] = $sign;;
        return $header;
    }

    private static function sign($httptype,$header,$query,$signkey,$aparam=[])
    {
        array_unshift( $header,$httptype);
        array_push( $header,$query);
        $wait_sign = [];
        $wait_sign[0] = $httptype;
        $wait_sign[1] = $header['Accept'];
        $wait_sign[2] = $header['Content-Type'];
        $wait_sign[3] = "x-ca-nonce:".$header['x-ca-nonce'];
        $wait_sign[4] = "x-ca-signature-type:".$header['x-ca-signature-type'];
        $wait_sign[5] = "x-ca-timestamp:".$header['x-ca-timestamp'];
        if( strtolower($httptype) == 'get')
            $wait_sign[6] = $query;
        else{
            ksort($aparam);
            $bodystr = http_build_query($aparam);
            $wait_sign[6] = $query."?".$bodystr;
        }
        $string = implode("\n",$wait_sign);
        $sign = "";
        if( $header['x-ca-signature-type'] == "MD5"){
            $string = $string.$signkey;
            //file_put_contents( "mixc.txt",$string."\n",FILE_APPEND);
            $sign = strtoupper(md5( $string ));
        }
        if( $header['x-ca-signature-type'] == "HmacSHA256" )
        {
            //file_put_contents( "mixc.txt","string:".$string.";key:$signkey:\n",FILE_APPEND);
            $sign = hash_hmac("sha256",$string ,$signkey);
        }
        return $sign;
    }

    /**
     * @param LoggerInterface $logger
     * @return MixcGateway
     */
    public function setLogger(LoggerInterface $logger, string $remarks = ''): MixcGateway {
        $this->logger = $logger;
        $this->remarks = $remarks;
        $this->mixcCurl->setLogger($logger,$remarks);

        return $this;
    }

}