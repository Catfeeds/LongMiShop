<?php
namespace Common\Model;

use Common\Model\Base\BaseModel;

class Goods extends BaseModel
{
    private static $tableName = 'goods';

//
//    public function getWeChatId() {
//        return $this->databaseData['id'];
//    }
//    public function getWeChatUid() {
//        return $this->databaseData['uid'];
//    }
//    public function getWeChatEncodingAESKey() {
//        return $this->databaseData['aeskey'];
//    }
//    public function getWeChatAppId() {
//        return $this->databaseData['appid'];
//    }
//    public function getWeChatAppSecret() {
//        return $this->databaseData['appsecret'];
//    }
//    public function getWeChatOriginalId() {
//        return $this->databaseData['wxid'];
//    }
//    public function getWeChatToken() {
//        return $this->databaseData['w_token'];
//    }
//    public function getWeChatShareTicket() {
//        return $this->databaseData['share_ticket'];
//    }
//    public function getWeChatShareDated() {
//        return $this->databaseData['share_dated'];
//    }
//    public function getWeChatType() {
//        return $this->databaseData['type'];
//    }



    public static function currentGoodsInfo($GoodsId = null) {
        if( is_null($GoodsId)){
            return self::findGoodsWithCondition();
        }
        return self::findGoodsInfoWithUserID($GoodsId);
    }

    public static function findGoodsInfoWithUserID($GoodsId) {
        $data = self::findRecordWithID(self::$tableName , $GoodsId);
        return new Goods($data);
    }
    public static function findGoodsWithCondition() {
        $data = self::findRecordWithCondition(self::$tableName);
        return new Goods($data);
    }
}