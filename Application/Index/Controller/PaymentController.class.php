<?php
namespace Index\Controller;

class PaymentController extends IndexBaseController {

    public $payment    = null; //  具体的支付类
    public $payCode    = null; //  具体的支付code

    function exceptAuthActions()
    {
        return array(
            "notifyUrl",
        );
    }

    public function _initialize() {
        $log = json_encode($_GET)." \n \r ".json_encode($_POST);
        setLogResult($log);
        parent::_initialize();
        $pay_radio = I('pay_radio');
        if(!empty($pay_radio))
        {
            $pay_radio = parse_url_param($pay_radio);
            $this->payCode = $pay_radio['pay_code']; // 支付 code
        }
        else // 第三方 支付商返回
        {
            $_GET = I('get.');
            //file_put_contents('./a.html',$_GET,FILE_APPEND);
            $this->payCode = I('get.pay_code');
            unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误
        }
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        // 导入具体的支付类文件
        include_once  "plugins/payment/{$this->payCode}/{$this->payCode}.class.php";
        $code = '\\'.$this->payCode; // \alipay
        $this->payment = new $code();
    }



    public function getCode(){
        C('TOKEN_ON',false); // 关闭 TOKEN_ON
        header("Content-type:text/html;charset=utf-8");

        $order_id = I('order_id'); // 订单id
        // 修改订单的支付方式
        $payment_arr = M('Plugin')->where("`type` = 'payment'")->getField("code,name");
        M('order')->where("order_id = $order_id")->save(array('pay_code'=>$this->payCode,'pay_name'=>$payment_arr[$this->payCode]));

        $order = M('order')->where("order_id = $order_id")->find();

        // tpshop 订单支付提交
        $pay_radio = I('pay_radio');
        $config_value = parse_url_param($pay_radio); // 类似于 pay_code=alipay&bank_code=CCB-DEBIT 参数
        $code_str = $this->payment->get_code($order,$config_value);
        //微信JS支付
        if($this->payCode == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $code_str = $this->payment->getJSAPI($order,$config_value);
            exit($code_str);
        }
//        dd($pay_radio);
//        dd($this->payment);
        $this->assign('code_str', $code_str);
        $this->assign('order_id', $order_id);
        $this->display('payment');  // 分跳转 和不 跳转
    }


    // 服务器点对点
    public function notifyUrl(){
        $this->payment->response();
        exit();
    }

    // 页面跳转
    public function returnUrl(){
        $result = $this->payment->respond2(); // $result['order_sn'] = '201512241425288593';
        $order = M('order')->where("order_sn = '{$result['order_sn']}'")->find();
        $this->assign('order', $order);
        if($result['status'] == 1){
            header("Location: ".U('Index/Order/orderList'));
        }else{
            $this->error('支付失败');
        }
    }

}