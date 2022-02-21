<?php

use Lcobucci\JWT\Validation\ConstraintViolation;

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 7:17 AM
 */
class PostComment_model extends MY_Model
{
    public function insertNewComment($ins) {
        if($this->db->insert(self::TABLE_POST_COMMENT, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
	
	public function insertNewReply($ins) {
        if($this->db->insert(self::TABLE_POST_REPLY, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
	
	public function insertNewHiddenComment($ins) {
        if($this->db->insert(self::POST_COMMENT_HIDDEN, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }	
	}
	
	public function insertNewHiddenReply($ins) {
        if($this->db->insert(self::POST_REPLY_HIDDEN, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }	
	}
	
	public function userHiddenComment($userid, $commentid) {
		$liked =  $this->db->select('*')
                    ->from(self::POST_COMMENT_HIDDEN)
                    ->where(array('user_id' => $userid, 'comment_id' => $commentid))
                    ->get()
                    ->result_array();
					
		if (count($liked) > 0 ) {
			return true;
		}
		return false;
	}
	
		public function userHiddenReply($userid, $commentid) {
		$liked =  $this->db->select('*')
                    ->from(self::POST_REPLY_HIDDEN)
                    ->where(array('user_id' => $userid, 'reply_id' => $commentid))
                    ->get()
                    ->result_array();
					
		if (count($liked) > 0 ) {
			return true;
		}
		return false;
	}
	
	public function getReplies($where = array()){
		$retVal = $this->db->select('*')
                    ->from(self::TABLE_POST_REPLY)
                    ->where($where)
                    ->order_by('created_at', 'DESC')
                    ->get()
                    ->result_array();

        $now = time();
		
			for($x = 0 ; $x < count($retVal); $x++) {
                $encodedComments = $retVal[$x]['reply'];
                $decodededComments = json_decode($encodedComments);
                $retVal[$x]['replies'] = $decodededComments;

                $retVal[$x]['read_created'] = human_readable_date($retVal[$x]['created_at']);
				$replyUser = $this->User_model->getOnlyUser(array('id' => $retVal[$x]['reply_user_id']));
				$retVal[$x]['user_img'] = $replyUser[0]['pic_url'];
				
				if (strlen($retVal[$x]['data'])> 0){
					$retVal[$x]['data'] = explode(",", $retVal[$x]['data']);
				} else {
					$retVal[$x]['data'] = array();
				}
			}
			
			return $retVal;
	}

    public function getComments($where = array()) {
        $retVal = $this->db->select('*')
                    ->from(self::TABLE_POST_COMMENT)
                    ->where($where)
                    ->order_by('created_at', 'DESC')
                    ->get()
                    ->result_array();

        $now = time();
        for($i = 0 ; $i < count($retVal); $i++) {
            $encodedComments = $retVal[$i]['comment'];
            $decodededComments = json_decode($encodedComments);
            $retVal[$i]['comments'] = $decodededComments;
			
			$user = $this->User_model->getOnlyUser(array('id' => $retVal[$i]['commenter_user_id']));
						
			$retPostReply = $this->db->select('*')
                    ->from(self::TABLE_POST_REPLY)
                    ->where(array('comment_id' => $retVal[$i]['id']))
                    ->order_by('created_at', 'DESC')
                    ->get()
                    ->result_array();
					
			for($x = 0 ; $x < count($retPostReply); $x++) {
                $encodedComments = $retPostReply[$x]['reply'];
                $decodededComments = json_decode($encodedComments);
                $retPostReply[$x]['replies'] = $decodededComments;
                
				$retPostReply[$x]['read_created'] = human_readable_date($retPostReply[$x]['created_at']);
				$replyUser = $this->User_model->getOnlyUser(array('id' => $retPostReply[$x]['reply_user_id']));
				$likes = $this->PostLike_model->getLikes(array('reply_id' => $retPostReply[$x]['id']));
				$retPostReply[$x]['user_img'] = $replyUser[0]['pic_url'];
				$retPostReply[$x]['user_name'] = $replyUser[0]['user_name'];
				$retPostReply[$x]['like_count'] = count($likes);
				if (strlen($retPostReply[$x]['data'])> 0){
					$retPostReply[$x]['data'] = explode(",", $retPostReply[$x]['data']);
				} else {
					$retPostReply[$x]['data'] = array();
				}
			}
			if (strlen($retVal[$i]['data']) > 0){
				$retVal[$i]['data'] = explode(",", $retVal[$i]['data']);
			} else {
				$retVal[$i]['data'] = array();
			}
			$likes = $this->PostLike_model->getLikes(array('comment_id' => $retVal[$i]['id']));
			$retVal[$i]['like_count'] = count($likes);
			$retVal[$i]['user_img'] = $user[0]['pic_url'];
			$retVal[$i]['user_name'] = $user[0]['user_name'];
			$retVal[$i]['replies'] = $retPostReply;
            $retVal[$i]['read_created'] = human_readable_date($retVal[$i]['created_at']);
        }
        return $retVal;
    }

    public function getPostIDByCommentID($commentid) {
        $retVal = $this->db->select('*')
                    ->from(self::TABLE_POST_COMMENT)
                    ->where(array('id' => $commentid))
                    ->order_by('created_at', 'DESC')
                    ->get()
                    ->result_array();
      
        return $retVal;
	}
}