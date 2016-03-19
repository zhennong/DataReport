<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 3/15/16
 * Time: 10:30 AM
 */

namespace Common;

use Common\Model\BusinessModel;

class MallDb extends BusinessModel
{
    public $db;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        if (!$this->db) {
            $this->db = D()->cache(true);
            $this->db->db(1, C('BUSINESS_DB'));
        }
    }

    public function list_query($sql)
    {
        $data = $this->db->query($this->modifySQL($sql));
        return $data;
    }

    public function modifySQL($sql)
    {
        $sql = str_replace('__MALL_', C('BUSINESS_DB_TABLE_PREFIX'), $sql);
        return $sql;
    }

    public function getSql($sql)
    {
        return $this->modifySQL($sql);
    }

    public function showSql($sql)
    {
        dump($this->getSql($sql));
    }
}