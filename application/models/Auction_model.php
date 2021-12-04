<?php
  /**
     * Created by PhpStorm.
     * User: YueXi
     * Date: 2021/04/28
     * Time: 4:15 AM
 */
class Auction_model extends MY_Model {
    public function insertNewAuction($ins = array()) {
        if($this->db->insert(self::TABLE_AUCTIONS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
    public function updateAuction($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_AUCTIONS, $setArray);
    }
    
    public function getAuctionById($auctionId) {
        $auctions = $this->db->select('*')
                    ->from(self::TABLE_AUCTIONS)
                    ->where('id', $auctionId)
                    ->get()
                    ->result_array();
                    
        return $auctions[0];        
    }

    public function getAuctions($where = array()) {
        return $this->db->select('*')
            ->from(self::TABLE_AUCTIONS)
            ->where($where)
            ->order_by('price', 'DESC')
            ->get()
            ->result_array();
    }
    
    public function getProfilePinAuctions($where = array()) {
        return $this->db->select('*')
            ->from(self::TABLE_AUCTIONS)
            ->where($where)
            ->order_by('price', 'DESC')
            ->get()
            ->result_array();
    }
    
    public function getPinPointAuctions($where = array(), $tag = "") {
        $tag = trim($tag);
        $tag = strtolower($tag);
             
        //$likeWhere = "LOWER(".self::TABLE_AUCTIONS.".tags) LIKE '%".$tag."%'";
        $likeWhere = "LOWER(".self::TABLE_AUCTIONS.".tags) = '".$tag."'";
        return $this->db->select('*')
            ->from(self::TABLE_AUCTIONS)
            ->where($where)
            ->where($likeWhere)
            ->order_by('price', 'DESC')
            ->get()
            ->result_array();
    }
    
    public function getCategories($where = array()) {
        return $this->db->distinct()
            ->select('category')
            ->from(self::TABLE_AUCTIONS)
            ->where($where)
            ->where('category IS NOT NULL', NULL, FALSE)
            ->group_by('category')
            ->order_by('category', 'ASC')
            ->get()
            ->result_array();
    }
    
    public function getCountries($where = array()) {
        return $this->db->distinct()
            ->select('country')
            ->from(self::TABLE_AUCTIONS)
            ->where($where)
            ->where('country IS NOT NULL', NULL, FALSE)
            ->group_by('country')
            ->order_by('country', 'ASC')
            ->get()
            ->result_array();
    }
    
    public function getCounties($where = array()) {
        return $this->db->distinct()
            ->select('county')
            ->from(self::TABLE_AUCTIONS)
            ->where($where)
            ->where('county IS NOT NULL', NULL, FALSE)
            ->group_by('county')
            ->order_by('county', 'ASC')
            ->get()
            ->result_array();
    }
    
    public function getRegions($where = array()) {
        return $this->db->distinct()
            ->select('region')
            ->from(self::TABLE_AUCTIONS)
            ->where($where)
            ->where('region IS NOT NULL', NULL, FALSE)
            ->group_by('region')
            ->order_by('region', 'ASC')
            ->get()
            ->result_array();
    }
    
    public function getTags($where = array()) {
        return $this->db->distinct()
            ->select('tags')
            ->from(self::TABLE_AUCTIONS)
            ->where($where)
            ->where('tags IS NOT NULL', NULL, FALSE)
            ->group_by('tags')
            ->order_by('tags', 'ASC')
            ->get()
            ->result_array();
    }
} 
