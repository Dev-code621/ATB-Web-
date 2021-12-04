<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/11
 * Time: 4:54 PM
 */
class LikeInfo_model extends MY_Model
{
    public function insertNewLike($insertVal) {
        if($this->db->insert(self::TABLE_LIKE_INFOS, $insertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function getFollowerCounter($follower_business_id, $follower_user_id) {
        $followers = $this->db->select('*') -> from(self::TABLE_LIKE_INFOS) -> where(array('follower_business_id' => $follower_business_id, 'follower_user_id' => $follower_user_id )) -> get() -> result_array();
        return count($followers);
    }

    public function getFollowers($follower_business_id, $follower_user_id) {
        $followers = $this->db->select('*') -> from(self::TABLE_LIKE_INFOS) -> where(array('follower_business_id' => $follower_business_id, 'follower_user_id' => $follower_user_id )) -> get() -> result_array();
        return $followers;
    }
	
    public function getFollowCounter($follow_user_id, $follow_business_id) {
        $followers = $this->db->select('*') -> from(self::TABLE_LIKE_INFOS) -> where(array('follow_user_id' => $follow_user_id, 'follow_business_id' => $follow_business_id)) -> get() -> result_array();
        return count($followers);
    }

    public function getFollows($follow_user_id, $follow_business_id) {
        $followers = $this->db->select('*') -> from(self::TABLE_LIKE_INFOS) -> where(array('follow_user_id' => $follow_user_id, 'follow_business_id' => $follow_business_id)) -> get() -> result_array();
        return $followers;
    }
	
    public function removeFollow($where) {
        $this->db->where($where)->delete(self::TABLE_LIKE_INFOS);
    }
	
	public function updateFollow($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_LIKE_INFOS, $setArray);
    }
	
	public function post_notifications($whereArray) {
		$notifications = $this->db->select('post_notifications') -> from(self::TABLE_LIKE_INFOS) -> where($whereArray) -> get() -> result_array();
        return $notifications[0]["post_notifications"];
	}
}