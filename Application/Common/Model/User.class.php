<?php

namespace Common\Model;

use Common\Model\Base\BaseModel;

class User extends BaseModel
{
    private static $tableName = 'users';

    public function _initFromSession() {
        $this->databaseData['user_id'] = session(__UserID__);
        $this->databaseData['nickname'] = session(__UserName__);
    }

    public function getUsername() {
        return $this->databaseData['nickname'];
    }

    public function getUserID() {
        return $this->databaseData['user_id'];
    }

    public static function getCurrentUserID() {
        return session(__UserID__);
    }


    public function getInfo(){
        return array(
            'userId' => self::getUserID(),
            'nickName' => self::getUsername(),
        );
    }

    public static function currentInfo() {
        $userInfo = null;
        if (!self::getCurrentUserID()) {
            $userInfo = self::findInfoWithID(self::getCurrentUserID());
            session(__UserID__, $userInfo->getUserID());
        }else{
            $userInfo = new self(null);
            $userInfo->_initFromSession();
        }
        return $userInfo;
    }

    public static function findInfoWithID($userID) {
        $data = self::findRecordWithID(self::$tableName, $userID);
        return new self($data);
    }
}