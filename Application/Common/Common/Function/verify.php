<?php


//验证码
function verify(){

    $config=array(
            'imageW'=>110,
            'imageH'=>30,
            'length'=>4,
            'fontSize'=>16,
            'useNoise'=>false,
    );

    $Verify = new \Think\Verify($config);
    $Verify->entry();
}


/**
 * 发送短信
 * @param $mobile
 * @param $data
 * @param string $templateCode
 * @param string $title
 * @return bool
 *
 */
function sendMobileMessages($mobile, $data , $templateCode = "SMS_16756301" , $title = "龙米科技")
{
    //时区设置：亚洲/上海
    date_default_timezone_set('Asia/Shanghai');
    //这个是你下面实例化的类
    vendor('Alidayu.TopClient');
    //这个是topClient 里面需要实例化一个类所以我们也要加载 不然会报错
    vendor('Alidayu.ResultSet');
    //这个是成功后返回的信息文件
    vendor('Alidayu.RequestCheckUtil');
    //这个是错误信息返回的一个php文件
    vendor('Alidayu.TopLogger');
    //这个也是你下面示例的类
    vendor('Alidayu.AlibabaAliqinFcSmsNumSendRequest');

    $c = new \TopClient;
    $config = getShopConfig();
    $dataJson = json_encode($data);

    //App Key的值 这个在开发者控制台的应用管理点击你添加过的应用就有了
    $c->appkey = $config['sms_sms_appkey'];
    //App Secret的值也是在哪里一起的 你点击查看就有了
    $c->secretKey = $config['sms_sms_secretKey'];

    //这个是用户名记录那个用户操作
    $req = new \AlibabaAliqinFcSmsNumSendRequest;
    //代理人编号 可选
//    $req->setExtend("123456");
    //短信类型 此处默认 不用修改
    $req->setSmsType("normal");
    //短信签名 必须
    $req->setSmsFreeSignName( "$title" );
    //短信模板 必须
    $req->setSmsParam( "$dataJson" );
    //短信接收号码 支持单个或多个手机号码，传入号码为11位手机号码，不能加0或+86。群发短信需传入多个号码，以英文逗号分隔，
    $req->setRecNum( "$mobile" );
    //短信模板ID，传入的模板必须是在阿里大鱼“管理中心-短信模板管理”中的可用模板。
    $req->setSmsTemplateCode( "$templateCode" ); // templateCode
    //发送短信
    $resp = $c->execute($req);
    //短信发送成功返回True，失败返回false
    if ($resp)
    {
        return true;
    }
    else
    {
        return false;
    }
}