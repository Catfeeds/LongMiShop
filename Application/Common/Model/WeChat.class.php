<?php
namespace Common\Model;

use Common\Model\Base\BaseModel;

class WeChat extends BaseModel
{
    private static $tableName = 'wx_user';


    public function getWeChatId() {
        return $this->databaseData['id'];
    }
    public function getWeChatUid() {
        return $this->databaseData['uid'];
    }
    public function getWeChatEncodingAESKey() {
        return $this->databaseData['aeskey'];
    }
    public function getWeChatAppId() {
        return $this->databaseData['appid'];
    }
    public function getWeChatAppSecret() {
        return $this->databaseData['appsecret'];
    }
    public function getWeChatOriginalId() {
        return $this->databaseData['wxid'];
    }
    public function getWeChatToken() {
        return $this->databaseData['w_token'];
    }
    public function getWeChatShareTicket() {
        return $this->databaseData['share_ticket'];
    }
    public function getWeChatShareDated() {
        return $this->databaseData['share_dated'];
    }
    public function getWeChatType() {
        return $this->databaseData['type'];
    }



    public static function currentWeChatInfo($weChatId = null) {
        if( is_null($weChatId)){
            return self::findWeChatWithCondition();
        }
        return self::findWeChatInfoWithUserID($weChatId);
    }

    public static function findWeChatInfoWithUserID($weChatId) {
        $data = self::findRecordWithID(self::$tableName , $weChatId);
        return new User($data);
    }
    public static function findWeChatWithCondition() {
        $data = self::findRecordWithCondition(self::$tableName);
        return new User($data);
    }
}