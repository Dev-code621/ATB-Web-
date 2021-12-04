<?php
class UserBraintree_model extends MY_Model
{
    /******
     *  CRUD Functions
     *
     *
     */

    public function updateUserBraintreeCustomerId($set, $where) {
        $this->db->where($where);
        $this->db->update(self::TABLE_USER_BRAINTREE, $set);
    }

    public function insertNewUserBraintreeCustomerId($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_USER_BRAINTREE, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }

        return $result;
    }

    public function getUserBraintreeInfo($where = array()) {
        return $this->db->select('*')
            ->from(self::TABLE_USER_BRAINTREE)
            ->where($where)->get()->result_array();
    }

}
?>