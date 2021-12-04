<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/10
 * Time: 6:32 PM
 */
class AccountSetting_model extends MY_Model
{
    public function insertAccountSetting($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_ACCOUNT_SETTINGS, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }


    public function getSettingInfo($userId) {
        return $this->db->select('*')
            ->from(self::TABLE_ACCOUNT_SETTINGS)
            ->where(array('user_id' => $userId))
            ->get()
            ->result_array();
    }
}