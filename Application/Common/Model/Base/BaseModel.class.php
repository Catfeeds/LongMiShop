<?php
/**
 * Model 基类
 *
 * 钟瀚涛
 *
 * 2016 8 25
 */
namespace Common\Model\Base;

use Common\Common\Page;
use Think\Model;

abstract class BaseModel
{
    protected $databaseData = null;
    private static $size = 20;

    public function __construct($databaseData) {
        $this->databaseData = $databaseData;
    }

    public function getID() {
        return $this->databaseData['id'];
    }

    public function getCreatedAt() {
        return $this->databaseData['createtime'];
    }

    public function getUpdatedAt() {
        return $this->databaseData['updatetime'];
    }

    protected static function getModel($tableName) {
        $model = new Model();
        $tableName = C('DB_PREFIX') . $tableName;
        return $model->table($tableName);
    }

    /**** SQL START ****/

    //增
    protected static function addRecord($tableName, $fields) {
        $fields['created_at']    = lm_getRecordDateFormat();
        $fields['updated_at']    = lm_getRecordDateFormat();

        $sqlInstance = self::getModel($tableName);
        $id = $sqlInstance->add($fields);
        return $id;
    }

    protected static function addRecordList($tableName, $fieldList) {
        foreach ($fieldList as $k => $fields) {
            $fields['created_at']    = lm_getRecordDateFormat();
            $fields['updated_at']    = lm_getRecordDateFormat();
            $fieldList[$k] = $fields;
        }

        $sqlInstance = self::getModel($tableName);
        $firstInsertID = $sqlInstance->addAll($fieldList);
        return $firstInsertID;
    }

    //删
    protected static function deleteRecord($tableName, $id, $keyName = 'id') {
        return self::deleteRecordWithCondition($tableName, array($keyName => $id));
    }

    protected static function deleteRecordWithCondition($tableName, $condition) {
        $sqlInstance = self::getModel($tableName);
        $rowNumber =  $sqlInstance->where($condition)->delete();
        return $rowNumber;
    }

    //改

    /**
     * 更新数据
     * @param $tableName
     * @param $condition
     * @param $fields
     * @return bool 更新的条目数量
     *
     */
    protected static function saveRecordWithCondition($tableName, $condition, $fields) {
        $fields['updated_at']    = lm_getRecordDateFormat();

        $sqlInstance = self::getModel($tableName);
        $id = $sqlInstance->where($condition)->save($fields);
        return $id;
    }

    protected static function saveRecordWithIDs($tableName, $ids, $keyField = 'id', $fields) {
        $fields['updated_at']    = lm_getRecordDateFormat();

        $sqlInstance = self::getModel($tableName);
        $sqlInstance = $sqlInstance->where(array($keyField => array('in', implode(',', $ids))));
        $rows = $sqlInstance->save($fields);
        return $rows;
    }

    protected static function saveRecord($tableName, $id, $fields) {
        $condition['id'] = $id;
        return self::saveRecordWithCondition($tableName, $condition, $fields);
    }

    //查
    protected static function selectRecordsWithConditionByPage($tableName, $condition, $order = 'id desc', $fields = array('*')) {
        $sqlInstance = self::getModel($tableName)->field($fields);
        $sqlInstance = $sqlInstance->where($condition);

        $result = array();
        $page = new Page($sqlInstance->count());
        $result['page'] = $page->show();
        $result['total'] = $page->totalRows;

        $sqlInstance = self::getModel($tableName)->field($fields);
        $sqlInstance = $sqlInstance->where($condition);
        $sqlInstance = $sqlInstance->limit($page->firstRow, $page->listRows)->order($order);
        $result['data'] = $sqlInstance->select();

        return $result;
    }

    protected static function selectRecordsWithConditionAndLimit($tableName, $condition, $start = 1, $limit = 10, $order = 'id desc', $fields = array('*')) {
        $sqlInstance = self::getModel($tableName)->field($fields);
        $sqlInstance = $sqlInstance->where($condition);
        $sqlInstance = $sqlInstance->limit($start, $limit)->order($order);
        $result = $sqlInstance->select();
        return $result;
    }

    protected static function selectRecordsWithCondition($tableName, $isPage = false, $condition = null, $order = null, $fields = array('*')) {
        $result = array();
        if ($isPage) {
            $sqlInstance = self::getModel($tableName)->field($fields);
            $sqlInstance = $sqlInstance->where($condition);

            $page = new Page($sqlInstance->count());
            $result['page'] = $page->show();
            $result['total'] = $page->totalRows;
        }

        $sqlInstance = self::getModel($tableName)->field($fields);
        $sqlInstance = $sqlInstance->where($condition);
        if ($isPage) {
            $sqlInstance = $sqlInstance->limit($page->firstRow, $page->listRows);
        }
        $sqlInstance = $sqlInstance->order($order);
        $result['data'] = $sqlInstance->select();
        if ($isPage) {
            return $result;
        }

        return $result['data'];
    }

    protected static function selectRecordsWithIDs($tableName, $ids, $keyField = 'id', $fields = array('*'), $pageStart = null) {
        $sqlInstance = self::getModel($tableName)->field($fields);
        if (is_array($ids)) {
            $sqlInstance = $sqlInstance->where(array($keyField => array('in', implode(',', $ids))));
        }else{
            $sqlInstance = $sqlInstance->where(array($keyField => $ids));
        }
        if ($pageStart != null) {
            $sqlInstance = $sqlInstance->limit($pageStart, self::$size);
        }
        $result = $sqlInstance->select();
        return $result;
    }

    protected static function findRecordWithCondition($tableName, $condition, $fields = array('*')) {
        $sqlInstance = self::getModel($tableName)->field($fields);
        $result = $sqlInstance->where($condition)->find();
        return $result;
    }

    protected static function findRecordWithConditionAndOrder($tableName, $condition, $order = 'id asc', $fields = array('*')) {
        $sqlInstance = self::getModel($tableName)->field($fields);
        $sqlInstance = $sqlInstance->where($condition);
        $sqlInstance = $sqlInstance->order($order);
        $result = $sqlInstance->find();
        return $result;
    }

    protected static function findRecordWithID($tableName, $id, $keyField = 'id', $fields = array('*')) {
        $sqlInstance = self::getModel($tableName)->field($fields);
        $result = $sqlInstance->where(array($keyField => $id))->find();
        return $result;
    }

    protected static function countRecordWithField($tableName, $value, $keyField = 'id') {
        $sqlInstance = self::getModel($tableName);
        $result = $sqlInstance->where(array($keyField => $value))->count();
        return $result;
    }

    protected static function LinkTableRecord($tableName, $joinTableName, $on, $condition, $fields = array("*"), $logSql = false){
        $sqlInstance = self::getModel($tableName);
        $join = $joinTableName." ON ".$on;
        $result = $sqlInstance->join($join)->field($fields)->where($condition)->find();
        
        if($logSql){
            echo M()->getLastSql() . '</br>';
        }
        return $result;
    }

    /**** SQL END ******/
}