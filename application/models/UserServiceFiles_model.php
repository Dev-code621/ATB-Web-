<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 6:39 AM
 */
class UserServiceFiles_model extends MY_Model
{
    public function insertNewServiceFile($ins) {
        if($this->db->insert(self::TABLE_SERVICE_FILE, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function updateServiceFileRecord($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_SERVICE_FILE, $setArray);
    }

    public function getServiceFileList($userId) {
        return $this->db->select('*') -> from(self::TABLE_SERVICE_FILE)
                    ->where(array('user_id' => $userId))
                    ->get()
                    ->result_array();
    }
	

    public function getServiceFile($id) {
        return $this->db->select('*')
            -> from(self::TABLE_SERVICE_FILE)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
    }

    public function removeServiceFile($where) {
        $this->db->where($where)->delete(self::TABLE_SERVICE_FILE);
    }
}
