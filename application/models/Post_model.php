<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 7:17 AM
 */
class Post_model extends MY_Model
{
    public function insertNewPost($ins) {
        if($this->db->insert(self::TABLE_POST_LIST, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function insertNewImage($ins) {
        if($this->db->insert(self::TABLE_POST_IMGS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
	
	public function getNextMultiGroup(){
		$group =  $this->db->select('multi_group') -> from(self::TABLE_POST_LIST)
            ->where('multi_group is NOT NULL', NULL, FALSE)
            ->order_by('multi_group', 'DESC')
			->limit(1)
            ->get()
            ->result_array();
		
		if (count($group) < 1){
			return 0;
		} 
		return $group[0]['multi_group'] + 1;
	}
	
	public function getCurrentMultiGroup($userid){
		$group =  $this->db->select('multi_group') -> from(self::TABLE_POST_LIST)
            ->where('multi_group is NOT NULL', NULL, FALSE)
			->where(array("user_id" => $userid))
            ->order_by('multi_group', 'DESC')
			->limit(1)
            ->get()
            ->result_array();
		
		if (count($group) < 1){
			return 0;
		} 
		return $group[0]['multi_group'];
	}
	
	public function isCurrentlyUploadingMultiGroup($userid){
		$groups = $this->db->select('created_at') -> from(self::TABLE_POST_LIST)
            ->where('multi_group is NOT NULL', NULL, FALSE)
			->where(array("user_id" => $userid))
            ->order_by('id', 'DESC')
			->limit(1)
            ->get()
            ->result_array();
			
		if (count($groups) < 1){
			return false;
		}  
		
		$last_update = $groups[0]['created_at'];
		if (time() - $last_update > 20) {
			return false;
		} else {
			return true;
		}
	}
	
	public function getPostCounter($where = array()) {
        $followers = $this->db->select('*') -> from(self::TABLE_POST_LIST) -> where($where) -> get() -> result_array();
        return count($followers);
    }

    // creating a similar to getPostInfo as they are used in so many places
    // used in ATB Post search
    public function getPosts($where = array(), $searchKey = "") {
        $posts = array();

        if ($searchKey == "") {
            $posts =  $this->db->select('*') 
                -> from(self::TABLE_POST_LIST)
                ->where($where)
                ->order_by('id', 'DESC')
                ->get()
                ->result_array();

        } else {
			$searchKey = trim($searchKey);
			$searchKey = strtolower($searchKey);
			$searchKey = preg_replace("/[^A-Za-z0-9 ]/", '', $searchKey);

			$likeWhere = "(LOWER(users.user_name) LIKE '%".$searchKey."%' OR 
                        LOWER(user_extend_infos.business_name) LIKE '%".$searchKey."%' OR 
                        LOWER(".self::TABLE_POST_LIST.".title) LIKE '%".$searchKey."%' OR 
                        LOWER(".self::TABLE_POST_LIST.".description) LIKE '%".$searchKey."%' OR 
                        LOWER(post_comment.comment) LIKE '%".$searchKey."%' OR
                        LOWER(post_reply.reply) LIKE '%".$searchKey."%')";

            $posts =  $this->db->select(
                self::TABLE_POST_LIST.'.id as id,'.
                self::TABLE_POST_LIST.'.user_id as user_id,'.
                self::TABLE_POST_LIST.'.post_type as post_type,'.
                self::TABLE_POST_LIST.'.poster_profile_type as poster_profile_type,'.
                self::TABLE_POST_LIST.'.media_type as media_type,'.
                self::TABLE_POST_LIST.'.title as title,'.
                self::TABLE_POST_LIST.'.description as description,'.
                self::TABLE_POST_LIST.'.brand as brand,'.
                self::TABLE_POST_LIST.'.price as price,'.
                self::TABLE_POST_LIST.'.deposit as deposit,'.
                self::TABLE_POST_LIST.'.category_title as category_title,'.
                self::TABLE_POST_LIST.'.item_title as item_title,'.
                self::TABLE_POST_LIST.'.size_title as size_title,'.
                self::TABLE_POST_LIST.'.payment_options as payment_options,'.
                self::TABLE_POST_LIST.'.location_id as location_id,'.
                self::TABLE_POST_LIST.'.delivery_option as delivery_option,'.
                self::TABLE_POST_LIST.'.post_brand as post_brand,'.
                self::TABLE_POST_LIST.'.post_item as post_item,'.
                self::TABLE_POST_LIST.'.post_tags as post_tags,'.
                self::TABLE_POST_LIST.'.post_condition as post_condition,'.
                self::TABLE_POST_LIST.'.post_size as post_size,'.
                self::TABLE_POST_LIST.'.post_location as post_location,'.
                self::TABLE_POST_LIST.'.product_id as product_id,'.
                self::TABLE_POST_LIST.'.delivery_cost as delivery_cost,'.
                self::TABLE_POST_LIST.'.is_active as is_active,'.
                self::TABLE_POST_LIST.'.status_reason as status_reason,'.
                self::TABLE_POST_LIST.'.is_sold as is_sold,'.
                self::TABLE_POST_LIST.'.lat as lat,'.
                self::TABLE_POST_LIST.'.lng as lng,'.
                self::TABLE_POST_LIST.'.is_multi as is_multi,'.
                self::TABLE_POST_LIST.'.multi_pos as multi_pos,'.
                self::TABLE_POST_LIST.'.multi_group as multi_group,'.
                self::TABLE_POST_LIST.'.updated_at as updated_at,'.
                self::TABLE_POST_LIST.'.created_at as created_at,'.
                'users.user_name,'.
                'user_extend_infos.business_name,'.
                'post_comment.comment,'.
                'post_reply.reply') 
                ->from(self::TABLE_POST_LIST)
                ->join('users', self::TABLE_POST_LIST.'.user_id = users.ID')
                ->join('user_extend_infos', self::TABLE_POST_LIST.'.user_id = user_extend_infos.user_id', 'left outer')
                ->join('post_comment', self::TABLE_POST_LIST.'.id = post_comment.post_id', 'left outer')
                ->join('post_reply', 'post_comment.id = post_reply.comment_id', 'left outer')
                ->where($where)
                ->where($likeWhere)                             
                ->distinct(self::TABLE_POST_LIST.'.id')
                ->order_by(self::TABLE_POST_LIST.'.id', 'DESC')
                ->get()
                ->result_array();
        }
        
        for($i = 0 ; $i < count($posts) ; $i++) {
            $posts[$i]['post_imgs'] = $this->getPostImage(array('post_id' => $posts[$i]['id']));
            $post_likes = $this->PostLike_model->getLikes(array('post_id' => $posts[$i]['id']));
            $posts[$i]['likes'] = count($post_likes);
            $post_comments = $this->PostComment_model->getComments(array('post_id' => $posts[$i]['id']));
            $commentCount = count($post_comments);
            foreach ($post_comments as $comment) {
            	$commentCount += count($comment["replies"]);
            }
            
            if (!empty($posts[$i]['insurance_id'])){
            	$posts[$i]["insurance"] = $this->UserServiceFiles_model->getServiceFile($posts[$i]['insurance_id']);
            }
            
            if (!empty($posts[$i]['qualification_id'])){
            	$posts[$i]["qualification"] = $this->UserServiceFiles_model->getServiceFile($posts[$i]['qualification_id']);
            }
            
            $posts[$i]['comments'] = $commentCount;
            $user = $this->User_model->getOnlyUser(array('id' => $posts[$i]['user_id']));
            $posts[$i]["user"] = $user;
			
			$posts[$i]['read_created'] = human_readable_date($posts[$i]['created_at']);
			
			if ($posts[$i]["post_type"] == 4) {
				$posts[$i]['poll_options'] = $this->PostPoll_model->getPollOptions($posts[$i]['id']);
				for($x = 0 ; $x < count($posts[$i]['poll_options']); $x++) {
					$posts[$i]['poll_options'][$x]["votes"] = $this->PostPoll_model->getPollVotes($posts[$i]['poll_options'][$x]["id"]);
				}
			}
        }
        
        return $posts;
    }

    public function getPostInfo($where = array(), $searchKey = "") {
        $posts = array();
        if($searchKey == "") {
            $posts =  $this->db->select('*') -> from(self::TABLE_POST_LIST)
            ->where($where)
            ->order_by('id', 'DESC')
            ->get()
            ->result_array();
        }
        else {
			$searchKey = trim($searchKey);
			$searchKey = strtolower($searchKey);
			$searchKey = preg_replace("/[^A-Za-z0-9 ]/", '', $searchKey);

			$likeWhere = "LOWER(users.user_name) LIKE '%".$searchKey."%' OR LOWER(".self::TABLE_POST_LIST.".title) LIKE '%".$searchKey."%' OR LOWER(".self::TABLE_POST_LIST.".description) LIKE '%".$searchKey."%' OR LOWER(".self::TABLE_POST_LIST.".brand) LIKE '%".$searchKey."%'";

            $posts =  $this->db->select(self::TABLE_POST_LIST.'.id as id,'.self::TABLE_POST_LIST.'.user_id as user_id,'.self::TABLE_POST_LIST.'.post_type as post_type,'.self::TABLE_POST_LIST.'.poster_profile_type as poster_profile_type,'.self::TABLE_POST_LIST.'.media_type as media_type,'.self::TABLE_POST_LIST.'.title as title,'.self::TABLE_POST_LIST.'.description as description,'.self::TABLE_POST_LIST.'.brand as brand,'.self::TABLE_POST_LIST.'.price as price,'.self::TABLE_POST_LIST.'.deposit as deposit,'.self::TABLE_POST_LIST.'.category_title as category_title,'.self::TABLE_POST_LIST.'.item_title as item_title,'.self::TABLE_POST_LIST.'.size_title as size_title,'.self::TABLE_POST_LIST.'.payment_options as payment_options,'.self::TABLE_POST_LIST.'.location_id as location_id,'.self::TABLE_POST_LIST.'.delivery_option as delivery_option,'.self::TABLE_POST_LIST.'.post_brand as post_brand,'.self::TABLE_POST_LIST.'.post_item as post_item,'.self::TABLE_POST_LIST.'.post_tags as post_tags,'.self::TABLE_POST_LIST.'.post_condition as post_condition,'.self::TABLE_POST_LIST.'.post_size as post_size,'.self::TABLE_POST_LIST.'.post_location as post_location,'.self::TABLE_POST_LIST.'.product_id as product_id,'.self::TABLE_POST_LIST.'.delivery_cost as delivery_cost,'.self::TABLE_POST_LIST.'.is_active as is_active,'.self::TABLE_POST_LIST.'.status_reason as status_reason,'.self::TABLE_POST_LIST.'.is_sold as is_sold,'.self::TABLE_POST_LIST.'.lat as lat,'.self::TABLE_POST_LIST.'.lng as lng,'.self::TABLE_POST_LIST.'.is_multi as is_multi,'.self::TABLE_POST_LIST.'.multi_pos as multi_pos,'.self::TABLE_POST_LIST.'.multi_group as multi_group,'.self::TABLE_POST_LIST.'.updated_at as updated_at,'.self::TABLE_POST_LIST.'.created_at as created_at,'.' users.user_name') -> from(self::TABLE_POST_LIST)
			->join('users', self::TABLE_POST_LIST.'.user_id = users.ID')
            ->where($where)
            ->where($likeWhere)
            ->order_by(self::TABLE_POST_LIST.'.id', 'DESC')
            ->get()
            ->result_array();
        }
        
        for($i = 0 ; $i < count($posts) ; $i++) {
            $posts[$i]['post_imgs'] = $this->getPostImage(array('post_id' => $posts[$i]['id']));
            $post_likes = $this->PostLike_model->getLikes(array('post_id' => $posts[$i]['id']));
            $posts[$i]['likes'] = count($post_likes);
            $post_comments = $this->PostComment_model->getComments(array('post_id' => $posts[$i]['id']));
            $commentCount = count($post_comments);
            foreach ($post_comments as $comment) {
            	$commentCount += count($comment["replies"]);
            }
            
            if (!empty($posts[$i]['insurance_id'])){
            	$posts[$i]["insurance"] = $this->UserServiceFiles_model->getServiceFile($posts[$i]['insurance_id']);
            }
            
            if (!empty($posts[$i]['qualification_id'])){
            	$posts[$i]["qualification"] = $this->UserServiceFiles_model->getServiceFile($posts[$i]['qualification_id']);
            }
            
            $posts[$i]['comments'] = $commentCount;
            $user = $this->User_model->getOnlyUser(array('id' => $posts[$i]['user_id']));
            $posts[$i]["user"] = $user;
			
			$posts[$i]['read_created'] = human_readable_date($posts[$i]['created_at']);
			
			if ($posts[$i]["post_type"] == 4) {
				$posts[$i]['poll_options'] = $this->PostPoll_model->getPollOptions($posts[$i]['id']);
				for($x = 0 ; $x < count($posts[$i]['poll_options']); $x++) {
					$posts[$i]['poll_options'][$x]["votes"] = $this->PostPoll_model->getPollVotes($posts[$i]['poll_options'][$x]["id"]);
				}
			}
        }
        
        return $posts;
    }

    public function getPostDetail($postId, $userId) {
        
        $posts =  $this->db->select('*') -> from(self::TABLE_POST_LIST)
        ->where('id', $postId)
        ->get()
        ->result_array();
        if(count($posts) == 0 )return null;
        $posts[0]['post_imgs'] = $this->getPostImage(array('post_id' => $posts[0]['id']));
        $posts[0]['likes'] = $this->PostLike_model->getLikes(array('post_id' => $posts[0]['id']));
        $commentors  =  $this->PostComment_model->getComments(array('post_id' => $posts[0]['id'])); 

        for ($i = 0; $i < count($commentors); $i++) {
            $liked = $this->PostLike_model->userLikedComment($userId, $commentors[$i]['id']);
            $commentors[$i]['liked'] = $liked;
            $hidden = $this->PostComment_model->userHiddenComment($userId, $commentors[$i]['id']);
            $commentors[$i]['hidden'] = $hidden;
            for ($x = 0; $x < count($commentors[$i]['replies']); $x++) {
                $liked = $this->PostLike_model->userLikedReply($userId, $commentors[$i]['replies'][$x]['id']);
                $commentors[$i]['replies'][$x]['liked'] = $liked;

                $hidden = $this->PostComment_model->userHiddenReply($userId, $commentors[$i]['replies'][$x]['id']);
                $commentors[$i]['replies'][$x]['hidden'] = $hidden;
            }
            
            $commentor_users = $this->User_model->getOnlyUser(array('id' => $commentors[$i]['commenter_user_id']));
            if(count($commentors) > 0) {
                $commentors[$i]['user_name'] = $commentor_users[0]['user_name'];
            }
            else {
                $commentors[$i]['user_name'] = 'Unknown User';
            }
        } 
        
        $posts[0]['comments'] = $commentors;
		$userInfos = $this->User_model->getOnlyUser(array('id' => $posts[0]['user_id']));
		$posts[0]['user'] = $userInfos;
		$posts[0]['read_created'] = human_readable_date($posts[0]['created_at']);  
        
        if (!empty($posts[0]['insurance_id'])){
            $posts[0]["insurance"] = $this->UserServiceFiles_model->getServiceFile($posts[0]['insurance_id']);
        }
        
        if (!empty($posts[0]['qualification_id'])){
            $posts[0]["qualification"] = $this->UserServiceFiles_model->getServiceFile($posts[0]['qualification_id']);
        }
		
		if ($posts[0]["post_type"] == 4) {
				$posts[0]['poll_options'] = $this->PostPoll_model->getPollOptions($posts[0]['id']);
				for($x = 0 ; $x < count($posts[0]['poll_options']); $x++) {
					$posts[0]['poll_options'][$x]["votes"] = $this->PostPoll_model->getPollVotes($posts[0]['poll_options'][$x]["id"]);
				}
		}

        return $posts[0];
    }
    
    public function getPostImage($where) {
        return $this->db->select('*')
                            ->from(self::TABLE_POST_IMGS)
                            ->where($where)
                            ->get()
                            ->result_array();
    }

    public function updatePostContent($set, $where) {
        $this->db->where($where);
        $this->db->update(self::TABLE_POST_LIST, $set);
    }

    public function removePostImg($where) {
        $this->db->where($where)->delete(self::TABLE_POST_IMGS);
    }

    public function removePostContent($where) {
        $this->db->where($where)->delete(self::TABLE_POST_LIST);
    }


    /*****
     * get feeds list in home of mobile project
     */
    public function getPostInfo_InMobile($userId, $searchKey = "") {
        $userFeeds = $this->Feeds_model->getFeedsInfo($userId);
        $feedList = array();
        for($i = 0; $i < count($userFeeds); $i++) {
            array_push($feedList, $userFeeds[$i]['description']);
        }

        $posts = array();
        if($searchKey == "") {
            $posts =  $this->db->select('*') -> from(self::TABLE_POST_LIST)
            ->where_in('category_title', $feedList)
            ->where(array('is_active' => 1, 'multi_pos' => 0))
            ->order_by('id', 'DESC')
            ->get()
            ->result_array();
        }
        else {
			$searchKey = trim($searchKey);
			$searchKey = strtolower($searchKey);
			$searchKey = preg_replace("/[^A-Za-z0-9 ]/", '', $searchKey);
			
			$likeWhere = "LOWER(users.user_name) LIKE '%".$searchKey."%' OR LOWER(".self::TABLE_POST_LIST.".title) LIKE '%".$searchKey."%' OR LOWER(".self::TABLE_POST_LIST.".description) LIKE '%".$searchKey."%' OR LOWER(".self::TABLE_POST_LIST.".brand) LIKE '%".$searchKey."%'";
			
            $posts =  $this->db->select(self::TABLE_POST_LIST.'.id as id,'.self::TABLE_POST_LIST.'.user_id as user_id,'.self::TABLE_POST_LIST.'.post_type as post_type,'.self::TABLE_POST_LIST.'.poster_profile_type as poster_profile_type,'.self::TABLE_POST_LIST.'.media_type as media_type,'.self::TABLE_POST_LIST.'.title as title,'.self::TABLE_POST_LIST.'.description as description,'.self::TABLE_POST_LIST.'.brand as brand,'.self::TABLE_POST_LIST.'.price as price,'.self::TABLE_POST_LIST.'.deposit as deposit,'.self::TABLE_POST_LIST.'.category_title as category_title,'.self::TABLE_POST_LIST.'.item_title as item_title,'.self::TABLE_POST_LIST.'.size_title as size_title,'.self::TABLE_POST_LIST.'.payment_options as payment_options,'.self::TABLE_POST_LIST.'.location_id as location_id,'.self::TABLE_POST_LIST.'.delivery_option as delivery_option,'.self::TABLE_POST_LIST.'.post_brand as post_brand,'.self::TABLE_POST_LIST.'.post_item as post_item,'.self::TABLE_POST_LIST.'.post_tags as post_tags,'.self::TABLE_POST_LIST.'.post_condition as post_condition,'.self::TABLE_POST_LIST.'.post_size as post_size,'.self::TABLE_POST_LIST.'.post_location as post_location,'.self::TABLE_POST_LIST.'.product_id as product_id,'.self::TABLE_POST_LIST.'.delivery_cost as delivery_cost,'.self::TABLE_POST_LIST.'.is_active as is_active,'.self::TABLE_POST_LIST.'.status_reason as status_reason,'.self::TABLE_POST_LIST.'.is_sold as is_sold,'.self::TABLE_POST_LIST.'.lat as lat,'.self::TABLE_POST_LIST.'.lng as lng,'.self::TABLE_POST_LIST.'.is_multi as is_multi,'.self::TABLE_POST_LIST.'.multi_pos as multi_pos,'.self::TABLE_POST_LIST.'.multi_group as multi_group,'.self::TABLE_POST_LIST.'.updated_at as updated_at,'.self::TABLE_POST_LIST.'.created_at as created_at,'.' users.user_name') -> from(self::TABLE_POST_LIST)
			->join('users', self::TABLE_POST_LIST.'.user_id = users.ID')
            ->where_in(self::TABLE_POST_LIST.'.category_title', $feedList)
            ->where(array(self::TABLE_POST_LIST.'.is_active' => 1, self::TABLE_POST_LIST.'.multi_pos' => 0))
			->where($likeWhere)
            ->order_by(self::TABLE_POST_LIST.'.id', 'DESC')
            ->get()
            ->result_array();
        }
        
        for($i = 0 ; $i < count($posts) ; $i++) {
            $posts[$i]['post_imgs'] = $this->getPostImage(array('post_id' => $posts[$i]['id']));
            $post_likes = $this->PostLike_model->getLikes(array('post_id' => $posts[$i]['id']));
            $posts[$i]['likes'] = count($post_likes);
            $post_comments = $this->PostComment_model->getComments(array('post_id' => $posts[$i]['id']));
            $commentCount = count($post_comments);
            foreach ($post_comments as $comment) {
            	$commentCount += count($comment["replies"]);
            }
            
            $posts[$i]['comments']  = $commentCount;
	    $posts[$i]['read_created'] = human_readable_date($posts[$i]['created_at']);
			
	    if (!empty($posts[$i]['insurance_id'])){
            	$posts[$i]["insurance"] = $this->UserServiceFiles_model->getServiceFile($posts[$i]['insurance_id']);
            }
            
            if (!empty($posts[$i]['qualification_id'])){
            	$posts[$i]["qualification"] = $this->UserServiceFiles_model->getServiceFile($posts[$i]['qualification_id']);
            }

			if ($posts[$i]["post_type"] == 4) {
				$posts[$i]['poll_options'] = $this->PostPoll_model->getPollOptions($posts[$i]['id']);
				for($x = 0 ; $x < count($posts[$i]['poll_options']); $x++) {
					$posts[$i]['poll_options'][$x]["votes"] = $this->PostPoll_model->getPollVotes($posts[$i]['poll_options'][$x]["id"]);
				}
			}
        }
        
        return $posts;
    }
    
}
