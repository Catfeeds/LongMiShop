<?php

class BreakFastCronClass
{
    private $thisTime = null;
    private $nineItem = null;

    public function init()
    {
        $current = time();
        $this->thisTime = date("Y-m-d", $current);
        $this->nineItem = date("H", $current);

        $return = array();
        $configs = selectDataWithCondition("addons_breakfast_config");
        if (!empty($configs)) {
            foreach ($configs as $config) {
                $return[$config['key_name']] = $config['val'];
            }
        }
        $configs = $return;

        $endTime = $configs['start_time'] + $configs['days'] * 24 * 60 * 60;
        if ($this->nineItem == '08') {
            if ($endTime > time() && time() > $configs['start_time']) {
                $userList = M('addons_breakfast_data')->group("user_id")->select();
                if (!empty($userList)) {
                    foreach ($userList as $item) {
                        $dataInfo = findDataWithCondition("addons_breakfast_data", array('date'=>strtotime( $this->thisTime),'user_id' => $item['user_id']), "id");
                        if( empty($dataInfo)){
                            $userInfo = findDataWithCondition("users", array('user_id' => $item['user_id']), "openid");
                            $text = $configs['tx_text'] . "【<a href = 'http://" . $_SERVER['HTTP_HOST'] . U("Mobile/Addons/breakFast", array('pluginName' => 'over')) . "'>点击打卡 来领取你本日余额</a>】";
                            $jsSdkLogic = new \Common\Logic\JsSdkLogic();
                            $jsSdkLogic->push_msg($userInfo['openid'], $text);
                        }
                    }
                }
            }

        }
    }
}

$breakFastCronClassObj = new BreakFastCronClass();
$breakFastCronClassObj->init();