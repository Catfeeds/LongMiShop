<?php
class tweetQRCodeMobileController
{

    public $userInfo = null;
    public $assignData = array();

    //初始化
    public function __construct($userInfo)
    {
        $this->assignData["userInfo"] = $this->userInfo = $userInfo;

        if ($_SERVER["HTTP_HOST"] == "www.longmiwang.com") {
            $this->assignData["qrcode"] = "qrcode.jpg";
        } else {
            $this->assignData["qrcode"] = "qecode2.jpg";
        }
    }

    //首页
    public function index()
    {
        if( $this->userInfo['is_follow'] == 1){

            header("Location: http://mp.weixin.qq.com/s/ksHv0QFtJEOAUJOv0QNTNQ");
            exit;
        }

        return $this->assignData;
    }

}