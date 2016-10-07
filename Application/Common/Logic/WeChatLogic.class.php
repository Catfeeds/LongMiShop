<?php
namespace Common\Logic;

use Common\Logic\Base\BaseLogic;

class WeChatLogic extends BaseLogic
{

    const  ACCESS_TOKEN_URL     = "https://api.weixin.qq.com/sns/oauth2/access_token?";
    const  SNS_USER_INFO_URL    = "https://api.weixin.qq.com/sns/userinfo?";
    const  USER_INFO_URL        = "https://api.weixin.qq.com/cgi-bin/user/info?";
    const  AUTHORIZATION_URL    = "https://open.weixin.qq.com/connect/oauth2/authorize?";

    public $weChatConfig        = array();
    public $weChatInfo          = array();
    public $openid              = null;


    public function __construct()
    {
        parent::__construct( "config" );
        $this -> _getWeChatConfig();
    }


    /**
     * 获取 jsSdk 数据
     * @return array
     */
    public function getSignPackage(){

        $jsSdkLogic = new \Common\Logic\JsSdkLogic( $this->weChatConfig['appid'] , $this->weChatConfig['appsecret'] );
        return $jsSdkLogic -> getSignPackage();

    }

    /**
     * 授权开始
     *
     */
    public function authorization()
    {
        if( $this -> weChatConfig ){
            $this -> openid = $this -> getOpenid();
            $this -> runTheOpenidBindingWay();
        }
    }

    /**
     * 网页授权登录获取 OpenId
     * @return openid
     */
    public function getOpenid()
    {
        if( $_SESSION['openid'] ){
            return $_SESSION['openid'];
        }

        $this -> _getMainWeChatConfig();

        if ( !isset($_GET['code']) ){  //触发微信返回code码
            $baseUrl = urlencode( $this -> _getUrl() );
            $url = $this -> __createOauthUrlForCode($baseUrl); // 获取 code地址
            header("Location: $url"); // 跳转到微信授权页面 需要用户确认登录的页面
            exit();
        } else {
            // 上面跳转, 这里跳了回来
            //获取code码，以获取openid
            $code = $_GET['code'];
            $data = $this -> getOpenidFromMp($code);
            $data2 = $this -> getUserInfo( $data['access_token'],$data['openid']);
            $data['nickname'] = $data2['nickname'];
            $data['sex'] = empty($data2['sex']) ? 1 : $data2['sex'] ;
            $data['headimgurl'] = $data2['headimgurl'];
            $data['subscribe'] = $data2['subscribe'];
            $this -> weChatInfo = $data;
            session("openid",$data['openid']);
            return $data['openid'];
        }
    }

    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function getOpenidFromMp($code)
    {
        //通过code换取网页授权access_token  和 openid
        $url = $this->__createOauthUrlForOpenid($code);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);//取出openid access_token
        curl_close($ch);
        return $data;
    }


    /**
     *
     * 通过access_token openid 从工作平台获取UserInfo
     * @return openid
     */
    public function getUserInfo($access_token,$openid)
    {
        // 获取用户 信息
        $url = $this -> __createOauthUrlForUserinfo($access_token,$openid);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);//取出openid access_token
        curl_close($ch);

        // 获取看看用户是否关注了 你的微信公众号， 再来判断是否提示用户 关注
        $access_token2 = $this -> getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token2&openid=$openid";
        $subscribe_info = httpRequest($url,'GET');
        $subscribe_info = json_decode($subscribe_info,true);
        $data['subscribe'] = $subscribe_info['subscribe'];

        return $data;
    }


    public function getAccessToken(){
        //判断是否过了缓存期
        $wechat = M('wx_user')->find();
        $expire_time = $wechat['web_expires'];
        if($expire_time > time()){
            return $wechat['web_access_token'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$wechat[appid]}&secret={$wechat[appsecret]}";
        $return = httpRequest($url,'GET');
        $return = json_decode($return,1);
        $web_expires = time() + 7000; // 提前200秒过期
        M('wx_user')->where(array('id'=>$wechat['id']))->save(array('web_access_token'=>$return['access_token'],'web_expires'=>$web_expires));
        return $return['access_token'];
    }

    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url 编码
     * @return string 返回构造好的url
     */
    private function __createOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->weChatConfig['appid'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        if( $this -> weChatConfig['type'] == 1 || $this -> weChatConfig['type'] == 2 ){
            $urlObj["scope"] = "snsapi_base";
        }else{
            $urlObj["scope"] = "snsapi_userinfo";
        }
//        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this -> _toUrlParams($urlObj);
        return self::AUTHORIZATION_URL . $bizString;
    }

    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     * @return string 请求的url
     */
    private function __createOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->weChatConfig['appid'];
        $urlObj["secret"] = $this->weChatConfig['appsecret'];
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this -> _toUrlParams($urlObj);
        return self::ACCESS_TOKEN_URL . $bizString;
    }

    /**
     *
     * 构造获取拉取用户信息(需scope为 snsapi_userinfo)的url地址
     * @return string 请求的url
     */
    private function __createOauthUrlForUserinfo($access_token,$openid)
    {
        $urlObj["access_token"] = $access_token;
        $urlObj["openid"] = $openid;
        $urlObj["lang"] = 'zh_CN';
        $bizString = $this -> _toUrlParams($urlObj);
        return self::SNS_USER_INFO_URL . $bizString;
    }



    /**
     * 获取基础公众号配置
     * @return mixed
     */
    private function _getWeChatConfig()
    {
        $this -> weChatConfig = M('wx_user') -> where(array()) -> find();
        return $this -> weChatConfig ;
    }
    /**
     * 获取主服务号微信配置
     */
    private function _getMainWeChatConfig()
    {
        if( $this -> weChatConfig['type'] == 1 || $this -> weChatConfig['type'] == 2 ){
            $weChatConfig = M('wx_myuser') -> find();
            if( !empty($weChatConfig) ){
                $this -> weChatConfig['appid']       =   $weChatConfig['appid'];
                $this -> weChatConfig['appsecret']   =   $weChatConfig['appsecret'];
                $this -> weChatConfig['aeskey']      =   $weChatConfig['aeskey'];
                $this -> weChatConfig['w_token']     =   $weChatConfig['w_token'];
            }
        }
    }

    /**
     * 获取当前的url 地址
     * @return string
     */
    private function _getUrl() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

    /**
     * 拼接签名字符串
     * @param array $urlObj
     * @return string 返回已经拼接好的字符串
     */
    private function _toUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }




    /**
     * 微信绑定方式
     */
    private function runTheOpenidBindingWay(){
        if( openidBindingWayIsLoginForTheFirstTime() ){
            if( isLoginState() ){
                if( isBindingOpenidAngUserId( $this -> openid ) ){
                    bindingOpenidAngUserId( $this -> openid );
                }
            } else {
                $userId = getOpenidBindingUserId($this -> openid);
                if( !is_null($userId) ){
                    loginFromUserId( $userId );
                }
            }
            return;
        }
        if( openidBindingWayIsAutoRegister() ){
            if( isExistenceUserWithOpenid( $this -> openid ) ){
                if( !isLoginState() ){
                    $result = loginFromOpenid( $this -> openid );
                    if( callbackIsTrue( $result ) ){
                        $redirectedUrl = session("redirectedUrl");
                        if( !empty( $redirectedUrl ) ){
                            session("redirectedUrl",null);
                            header("Location: ".$redirectedUrl);
                            exit;
                        }
                        echo "<script language=JavaScript> location.replace(location.href);</script>";
                        exit;
                    }
                }
            }else{
                registerFromOpenid( $this -> openid , $this -> weChatInfo );
            }
            return;
        }

    }

    /**
     * 重新拉取用户数据
     */
    public function WechatFans($openid){
        $access_token = $this->_getWeChatConfig();
        // 获取用户 信息
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token['web_access_token']."&openid=".$openid."&lang=zh_CN";
        $call_back_url = json_decode(file_get_contents($url));
        if(isset($call_back_url->errcode)){
            exit;
        }

        return $data;

    }



}