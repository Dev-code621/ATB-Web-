<?php
  /**
     * Created by PhpStorm.
     * User: YueXi
     * Date: 2021/04/28
     * Time: 4:15 AM
 */
class UserTag_model extends MY_Model {
     
    public function insertNewTag($ins) {
        if($this->db->insert(self::TABLE_USER_TAGS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
    public function getUserTags($where = array()) {
        return $this->db->select('id, name') -> from(self::TABLE_USER_TAGS)
                    ->where($where)
                    ->get()
                    ->result_array();
    }
    
    public function getTag($id) {
        return $this->db->select('id, name')
            -> from(self::TABLE_USER_TAGS)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
    }
    
    public function removeTag($where) {
        $this->db->where($where)->delete(self::TABLE_USER_TAGS);
    }
    
    public function getUsers($tag = "") {
        $tag = trim($tag);
        $tag = strtolower($tag);
//        $likeWhere = "LOWER(".self::TABLE_AUCTIONS.".tags) LIKE '%".$tag."%'";
        $likeWhere = "LOWER(".self::TABLE_USER_TAGS.".name) = '".$tag."'";
        return $this->db->distinct()
            ->select('user_id')->from(self::TABLE_USER_TAGS)
            ->where($likeWhere)
            ->get()
            ->result_array();
    }
     
} 
