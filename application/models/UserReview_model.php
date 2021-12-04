<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/11
 * Time: 4:54 PM
 */
class UserReview_model extends MY_Model
{
    public function insertNewReview($insertVal) {
        if($this->db->insert(self::TABLE_USER_REVIEW, $insertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

	
    public function getReviews($where = array()) {
        return $this->db->select('*')
            ->from(self::TABLE_USER_REVIEW)
            ->where($where)->get()->result_array();
    }
	
	
}