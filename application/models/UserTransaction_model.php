<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/11
 * Time: 5:07 PM
 */
class UserTransaction_model extends MY_Model
{
   
    public function getTransactionHistory($where  = array()) {
        return $this->db->select('*')->from(self::TABLE_USER_TRANSACTION)->where($where)
                ->order_by('created_at', 'DESC')
                ->get()->result_array();
    }
    public function insertNewTransaction($insArr) {
        if($this->db->insert(self::TABLE_USER_TRANSACTION, $insArr)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = -1;
        }
        return $result;
    }
}