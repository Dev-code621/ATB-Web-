<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 7:17 AM
 */
class PostLike_model extends MY_Model
{
    public function insertNewLike($ins) {
        if($this->db->insert(self::TABLE_POST_LIKE, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function getLikes($where = array()) {
        return $this->db->select('*')
                    ->from(self::TABLE_POST_LIKE)
                    ->where($where)
                    ->get()
                    ->result_array();
    }
	
	public function userLikedComment($userid, $commentid) {
		$liked =  $this->db->select('*')
                    ->from(self::TABLE_POST_LIKE)
                    ->where(array('follower_user_id' => $userid, 'comment_id' => $commentid))
                    ->get()
                    ->result_array();
					
		if (count($liked) > 0 ) {
			return true;
		}
		return false;
	}
	
	public function userLikedReply($userid, $replyid) {
		$liked =  $this->db->select('*')
                    ->from(self::TABLE_POST_LIKE)
                    ->where(array('follower_user_id' => $userid, 'reply_id' => $replyid))
                    ->get()
                    ->result_array();
					
		if (count($liked) > 0 ) {
			return true;
		}
		return false;
	}
	
	public function deleteLike($where) {
        $this->db->where($where)->delete(self::TABLE_POST_LIKE);
    }
}