<?php
/**
 * Created by PhpStorm.
 * User: shengwang.yang
 * Date: 2019/8/28 0028
 * Time: 下午 6:44
 */

namespace weixin;

use Curl\Curl;
use wxsdk\encrypted\DataCrypt;
use Exception;

class Weixin
{

    private $appid;
    private $appSecret;

    public function __construct($appid, $appSecret){

        $this->appid = $appid;
        $this->appSecret = $appSecret;
    }

    /**
     * 微信小程序登陆授权获取用户openid等信息
     * @param $code
     * @return array
     * @throws Exception
     */
    public function getAuthData($code){
        $authUrl = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
        $authRealUrl = sprintf($authUrl, $this->appid, $this->appSecret, $code);

        $curl = new Curl\Curl();
        $wxResult = $curl->get($authRealUrl);
        if(!$wxResult){
            throw new Exception('登录校验失败，请检查参数是否正确');
        }
        if(array_key_exists('errcode',$wxResult)){
            throw new Exception($wxResult['errmsg'],$wxResult['errcode']);
        }
        return $wxResult;
    }


    /**
     * 后台校验与解密开放数据
     * @param $encryptedData
     * @param $iv
     * @param $session_key
     * @return mixed
     * @throws Exception
     */
    public function getEncryptedData($encryptedData,$iv,$session_key){
        $result='';
        $wxBizDataCrypt = new DataCrypt(self::APP_ID, $session_key);
        $errCode = $wxBizDataCrypt->decryptData($encryptedData, $iv, $result);
        if($errCode != 0){
            throw new Exception('解密失败,请检查参数是否正确');
        }
        return json_decode($result,true);
    }

    /**
     * 从微信服务器获取access_token
     * @return mixed
     * @throws Exception
     */
    public function getAccessToken()
    {
        $accessTokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
        $accessTokenRealUrl = sprintf($accessTokenUrl,$this->appid,$this->appSecret);
        $curl = new Curl\Curl();
        $result = $curl->get($accessTokenRealUrl);
        if (!$result)
        {
            throw new Exception('获取accessToken失败，请检查参数');
        }
        if(!empty($result['errcode'])){
            throw new Exception($result['errmsg'],$result['errcode']);
        }
        return $result['access_token'];
    }
}