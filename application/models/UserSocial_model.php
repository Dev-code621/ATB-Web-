<?php

class UserSocial_model extends MY_Model
{
    public function insertNewUserSocial($ins) {
        if($this->db->insert(self::TABLE_USER_SOCIAL, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function updateUserSocial($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_USER_SOCIAL, $setArray);
    }

    public function getUserSocials($userId) {
        return $this->db->select('*') -> from(self::TABLE_USER_SOCIAL)
                    ->where(array('user_id' => $userId))
                    ->get()
                    ->result_array();
    }

    public function getUserTypeSocials($userId, $socialType) {
        return $this->db->select('*') -> from(self::TABLE_USER_SOCIAL)
                    ->where(array('user_id' => $userId, 'type' => $socialType))
                    ->get()
                    ->result_array();
    }

    public function removeUserSocials($where) {
        $this->db->where($where)->delete(self::TABLE_USER_SOCIAL);
    }
	
    public function truncateUserSocials() {
        $this->db->truncate(self::TABLE_USER_SOCIAL);
    }
}

?>
