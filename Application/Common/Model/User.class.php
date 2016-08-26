<?php

namespace Common\Model;

use Common\Model\Base\BaseModel;

class User extends BaseModel
{
    private static $tableName = 'users';


    public function _initFromSession() {
        $this->databaseData['lm_id'] = session('lm_id');
        $this->databaseData['lm_nickname'] = session('lm_nickname');
    }

    public function getUsername() {
        return $this->databaseData['nickname'];
    }

    public function getUserID() {
        return $this->databaseData['user_id'];
    }
    public static function getCurrentUserID() {
        return session('lm_id');
    }


    public static function currentUserInfo() {
        $userInfo = null;
        if (!session('lm_userInfo')) {
            $userInfo = self::findUserInfoWithUserID(session('lm_id'));
            session('lm_id', $userInfo->getUserID());
            session('lm_nickname', $userInfo->getUsername());
            session('lm_userInfo', true);
        }else{
            $userInfo = new User(null);
            $userInfo->_initFromSession();
        }
        return $userInfo;
    }

    public static function findUserInfoWithUserID($userID) {
        $data = self::findRecordWithID(self::$tableName, $userID);
        return new User($data);
    }
}