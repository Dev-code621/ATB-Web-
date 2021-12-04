<?php


class AdminNotification_model extends MY_Model
{
    public function insertNewAdminNotification($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_ADMIN_ALERTS, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function getAdminNotification($where = array()) {
        $existUser = $this->db->select('*')->from(self::TABLE_ADMIN_ALERTS)->where($where)->get()->result_array();
        return $existUser;
    }

    public function updateAdminNotificationRecord($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_ADMIN_ALERTS, $setArray);
    }

    public function insertNewNotificationKeyword($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_NOTIFICATION_KEYWORDS, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function getNotificationKeyword($where = array()) {
        $existUser = $this->db->select('*')->from(self::TABLE_NOTIFICATION_KEYWORDS)->where($where)->get()->result_array();
        return $existUser;
    }

    public function deleteNotificationKeyword($where) {
        $this->db->where($where)->delete(self::TABLE_NOTIFICATION_KEYWORDS);
    }

    public function updateNotificationKeyword($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_NOTIFICATION_KEYWORDS, $setArray);
    }
}