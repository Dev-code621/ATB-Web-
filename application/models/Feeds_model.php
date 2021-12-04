<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/10
 * Time: 5:36 PM
 */
class Feeds_model extends MY_Model
{
    public function insertNewFeed($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_USER_FEEDS, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function insertFeedsBatch($userId, $nameArr) {
        $arrInsert = array();
        foreach($nameArr as $name) {
			
			$feed = $this->db->select('*')
            ->from("categories")
            ->where(array('description' => $name))
            ->get()
            ->result_array();
			
            $insArr = array('user_id' => $userId, 'category_id' => $feed[0]['id'], 'created_at' => time());
            array_push($arrInsert, $insArr);
        }

        $this->db->insert_batch(self::TABLE_USER_FEEDS, $arrInsert);
    }


    public function getFeedsInfo($userId) {
        return $this->db->select('categories.id, categories.description, user_feeds.user_id')
            ->from(self::TABLE_USER_FEEDS)
            ->join('categories', 'categories.id = user_feeds.category_id')
            ->where(array('user_id' => $userId))
            ->get()
            ->result_array();
    }
}