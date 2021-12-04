<?php

class Admin_model extends MY_Model {
	public function insertNewAdmin($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_ADMIN, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }
	
	public function getAdmin($where = array()) {
		$existUser = $this->db->select('*')->from(self::TABLE_ADMIN)->where($where)->get()->result_array();
        return $existUser;
    }
	
	 public function updateAdminRecord($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_ADMIN, $setArray);
    }

    public function deleteAdmin($where) {
        $this->db->where($where)->delete(self::TABLE_ADMIN);
    }
}