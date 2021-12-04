<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/10
 * Time: 6:32 PM
 */
class AccountActionHistory_model extends MY_Model
{
    public function insertNewHistory($insertArr, $userId) {
        $this->updateHistory(array('is_active' => 0), array('user_id' => $userId));
        if($this->db->insert(self::TABLE_USER_ACTION_HISTORY, $insArr)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function getAccountActionHistory($where = array()) {
        return $this->db->select('*')
            ->from(self::TABLE_USER_ACTION_HISTORY)
            ->where($where)
            ->get()
            ->result_array();
    }

    public function updateHistory($set = array(), $where = array()) {
        $this->db->where($where);
        $this->db->update(self::TABLE_USER_ACTION_HISTORY, $set);
    }
}