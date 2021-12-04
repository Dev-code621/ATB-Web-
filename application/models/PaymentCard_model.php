<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/20
 * Time: 3:50 AM
 */
class PaymentCard_model extends MY_Model
{
    public function insertNewCard($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_PAYMENT_CARD_LIST, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function getCardsByArray($where = array()) {
        return $this->db->select('*')->from(self::TABLE_PAYMENT_CARD_LIST)->where($where)->get()->result_array();
    }

    public function updateCardRecord($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_PAYMENT_CARD_LIST, $setArray);
    }

    public function removeCardRecord($where) {
        $this->db->where($where)->delete(self::TABLE_PAYMENT_CARD_LIST);
    }
}