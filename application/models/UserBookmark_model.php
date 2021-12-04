<?php
class UserBookmark_model extends MY_Model
{
    /******
     *  CRUD Functions
     *
     *
     */
    public function insertNewUserBookmark($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_USER_BOOKMARK, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function getUserBookmarks($where = array()) {
        return $this->db->select('*')
            ->from(self::TABLE_USER_BOOKMARK)
            ->where($where)
            ->order_by('created_at', 'DESC')
            ->get()
            ->result_array();
    }

    public function deleteBookmark($where = array()) {
	$this->db->where($where)
		 ->delete(self::TABLE_USER_BOOKMARK);
    }	

}
?>