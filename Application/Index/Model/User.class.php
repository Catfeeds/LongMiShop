<?php

namespace Index\Model;

use Common\Base\BaseModel;
use Think\Model;

class User extends BaseModel
{
    private static $tableName = 'users';

    public function getUsername() {
        return $this->databaseData['nickname'];
    }

    public function getMallID() {
        return $this->databaseData['user_id'];
    }
}