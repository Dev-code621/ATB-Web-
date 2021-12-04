<?php
class Tag_model extends MY_Model
{
    public function insertNewTag($ins) {
        if($this->db->insert(self::TABLE_TAGS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
    public function updateTag($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_TAGS, $setArray);
    }
    
    public function getAllTags() {
        return $this->db->select('*') -> from(self::TABLE_TAGS)
                    ->get()
                    ->result_array();
    }
    
    public function getTag($id) {
        return $this->db->select('*')
            -> from(self::TABLE_TAGS)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
    }
    
    public function getTagName($name) {
        return $this->db->select('*')
            -> from(self::TABLE_TAGS)
            ->where(array('tag' => $name))
            ->get()
            ->result_array();
    }
    
    public function getServiceTags($id) {
        return $this->db->select('*')
            -> from(self::TABLE_SERVICE_TAGS)
            ->where(array('service_id' => $id))
            ->get()
            ->result_array();
    }
    
    public function getPostTags($id) {
        return $this->db->select('*')
            -> from(self::TABLE_POST_TAGS)
            ->where(array('post_id' => $id))
            ->get()
            ->result_array();
    }
    
     public function getProductTags($id) {
        return $this->db->select('*')
            -> from(self::TABLE_PRODUCT_TAGS)
            ->where(array('product_id' => $id))
            ->get()
            ->result_array();
    }
    
    public function insertServiceTag($ins) {
        if($this->db->insert(self::TABLE_SERVICE_TAGS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
    public function removeServiceTag($where) {
        $this->db->where($where)->delete(self::TABLE_SERVICE_TAGS);
    }
    
    public function insertPostTag($ins) {
        if($this->db->insert(self::TABLE_POST_TAGS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
    public function removePostTag($where) {
        $this->db->where($where)->delete(self::TABLE_POST_TAGS);
    }
    
    public function insertProductTag($ins) {
        if($this->db->insert(self::TABLE_PRODUCT_TAGS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
    public function removeProductTag($where) {
        $this->db->where($where)->delete(self::TABLE_PRODUCT_TAGS);
    }
}
