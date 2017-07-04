<?php

namespace WeChat\Controller;
use Think\Controller;
class WeChatController extends Controller {

    public $client;
    public $weChatConfig;
    public $shopConfig;

    public function _initialize(){
        $this -> shopConfig = getShopConfig();
        //获取微信配置信息
        $this->weChatConfig = M('wx_user')->find();
        $options = array(
            'token'=>$this->weChatConfig['w_token'], //填写你设定的key
            'encodingaeskey'=>$this->weChatConfig['aeskey'], //填写加密用的EncodingAESKey
            'appid'=>$this->weChatConfig['appid'], //填写高级调用功能的app id
            'appsecret'=>$this->weChatConfig['appsecret'], //填写高级调用功能的密钥
        );

    }


    public function oauth(){

    }

    public function index(){
        if($this->weChatConfig['wait_access'] == 0)
            exit($_GET["echostr"]);
        else
            $this->responseMsg();
    }

    public function test(){
//        afterSubscribe( "owjy5v4020Mh7yNAT0aVapESwqNM11" , $this->weChatConfig );
    }

    public function responseMsg()
    {
	 setLogResult(json_encode($GLOBALS),"微信进来1","test");

        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        //extract post data
        if (empty($postStr)){
            exit("");
        }


        /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
           the best way is to check the validity of xml by yourself */
        libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();

        setLogResult(json_encode($postObj),"微信进来","test");
        //点击菜单拉取消息时的事件推送
        /*
         * 1、click：点击推事件
         * 用户点击click类型按钮后，微信服务器会通过消息接口推送消息类型为event的结构给开发者（参考消息接口指南）
         * 并且带上按钮中开发者填写的key值，开发者可以通过自定义的key值与用户进行交互；
         */
        if($postObj->MsgType == 'event' && $postObj->Event == 'CLICK')
        {
            $keyword = trim($postObj->EventKey);
        }

        if($postObj->MsgType == 'event' && $postObj->Event == 'subscribe')
        {
            $keyword = $this -> shopConfig['basic_subscribe_reply'];
            if( !empty($fromUsername) ){
                afterSubscribe( $fromUsername , $this->weChatConfig );
                $data = array();
                $where = " openid = '$fromUsername'";
                $data['is_follow'] = 1;
                $data['follow_time'] = time();
                M('users') -> where($where) -> save($data);
            }
        }

        if($postObj->MsgType == 'event' && ($postObj->Event == 'subscribe' || $postObj->Event == 'SCAN' ))
        {
            $qrCode = trim($postObj->EventKey);
            if(strstr($qrCode,"addons_qr_code_")){
                $qrCode = str_replace("qrscene_","",$qrCode);
                $qrInfo = findDataWithCondition("addons_createqrcode_qr",array("code"=>$qrCode),array('id','key_word'));
                if( !empty($qrInfo)){
                    $data = array(
                        "qr_id"=>$qrInfo['id'],
                        "create_time"=>time(),
                        "openid"=>(string)$fromUsername,
                        "event"=>(string)$postObj->Event,
                        "tag"=>json_encode($postObj),
                    );
                    addData("addons_createqrcode_list",$data);
                    $keyword = $qrInfo["key_word"];
                }
            }
        }

        if($postObj->MsgType == 'event' && $postObj->Event == 'unsubscribe')
        {
            if( !empty($fromUsername) ){
                $data = array();
                $where = " openid = '$fromUsername'";
                $data['is_follow'] = 0;
                $data['unfollow_time'] = time();
                M('users') -> where($where) -> save($data);
            }

        }
        if(!empty($keyword)){



            // 图文回复
            $wx_img = M('wx_img') -> where("keyword like '%$keyword%'")->find();
            if($wx_img)
            {
                $textTpl = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <ArticleCount><![CDATA[%s]]></ArticleCount>
                                <Articles>
                                    <item>
                                        <Title><![CDATA[%s]]></Title> 
                                        <Description><![CDATA[%s]]></Description>
                                        <PicUrl><![CDATA[%s]]></PicUrl>
                                        <Url><![CDATA[%s]]></Url>
                                    </item>                               
                                </Articles>
                                </xml>";
                $resultStr = sprintf($textTpl,$fromUsername,$toUsername,$time,'news','1',$wx_img['title'],$wx_img['desc']
                    , $wx_img['pic'], $wx_img['url']);
                exit($resultStr);
            }


            // 文本回复
            $wx_text = M('wx_text') -> where("keyword like '%$keyword%'")->find();
            if($wx_text)
            {
                $textTpl = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <Content><![CDATA[%s]]></Content>
                                <FuncFlag>0</FuncFlag>
                                </xml>";
                $contentStr = $wx_text['text'];
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 'text', $contentStr);
                exit($resultStr);
            }

        }


//        if($postObj->MsgType == 'image')
//        {
//            // 其他文本回复
//            $textTpl = "<xml>
//                                <ToUserName><![CDATA[%s]]></ToUserName>
//                                <FromUserName><![CDATA[%s]]></FromUserName>
//                                <CreateTime>%s</CreateTime>
//                                <MsgType><![CDATA[%s]]></MsgType>
//                                <Content><![CDATA[%s]]></Content>
//                                <FuncFlag>0</FuncFlag>
//                                </xml>";
//            $contentStr = '客官~小的收到，正在核对您的信息，稍后会有客服通知您结果';
//            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 'text', $contentStr);
//            exit($resultStr);
//        }


        $work_time = intval (date("Hi"));
        if( $work_time >"900" && $work_time < "2100"){
            /**
             * 客服部分
             */
            $textTpl = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                </xml>";
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 'transfer_customer_service');
            exit($resultStr);
        }else{
            $textTpl = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <Content><![CDATA[%s]]></Content>
                                <FuncFlag>0</FuncFlag>
                                </xml>";
            $contentStr = '亲爱滴客官，龙米家的客服MM上班时间是09:00-21:00哦，如有紧急情况可添加微信longmiwang帮您解决哈。爱你哟，么么哒！';
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 'text', $contentStr);
            exit($resultStr);
        }

    }
}
