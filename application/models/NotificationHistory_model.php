<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/11
 * Time: 5:07 PM
 */
class NotificationHistory_model extends MY_Model
{
    public function getNotificationHistoryCounter($userId) {
        $notfications = $this->db->select('*')->from(self::TABLE_NOTIFICATION_LIST)->where(array('user_id' => $userId))->get()->result_array();
        return count($notfications);
    }

    public function getNotificationHistory($where  = array()) {
        return $this->db->select('*')->from(self::TABLE_NOTIFICATION_LIST)->where($where)
                ->order_by('created_at', 'DESC')
                ->get()->result_array();
    }
    
    public function insertNewNotification($insArr) {
        if($this->db->insert(self::TABLE_NOTIFICATION_LIST, $insArr)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = $this->db->insert_id();
            
        } else {
            $result[MY_Controller::RESULT_FIELD_NAME] = -1;
        }
        
        return $result;
    }
    
    public function updateNotificationHistory($setArray, $whereArray) {        
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_NOTIFICATION_LIST, $setArray);
    }
}