<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 7:17 AM
 */
class PostPoll_model extends MY_Model
{
	public function insertNewOption($ins) {
        if($this->db->insert(self::POST_POLL, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
	
	public function getPollOptions($post_id){
		$posts =  $this->db->select('*') -> from(self::POST_POLL)
            ->where(array("post_id" => $post_id))
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
			
		return $posts;
	}
	
	public function getUsersVote($post_id, $user_id){
		$options =  $this->db->select('*') -> from(self::POST_POLL)
            ->where(array("post_id" => $post_id))
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
			
		foreach ($options as $option) {
			$option_votes = $this->getPollVote(array("post_poll_id" => $option['id']));
			foreach ($option_votes as $vote) {
				if ($vote['user_id'] == $user_id) {
					return $option['id'];
				}
			}
		}
		
		return "";
	}
	
	public function getPollVotes($option_id){
		$option_votes =  $this->db->select('user_id') -> from(self::POST_POLL_VOTE)
            ->where(array("post_poll_id" => $option_id))
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
           
		return $option_votes;
	}
	
	public function insertNewPollVote($in) {
		if($this->db->insert(self::POST_POLL_VOTE, $in)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
	}
	
	public function getPollVote($where = array()){
		$option_votes =  $this->db->select('*') -> from(self::POST_POLL_VOTE)
            ->where($where)
            ->order_by('id', 'ASC')
            ->get()
            ->result_array();
			
		return $option_votes;
	}
	
	public function updatePollVote($set, $where) {
        $this->db->where($where);
        $this->db->update(self::POST_POLL_VOTE, $set);
    }
}