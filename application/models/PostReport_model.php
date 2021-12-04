<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 7:17 AM
 */
class PostReport_model extends MY_Model
{
    public function insertNewReport($ins) {
        if($this->db->insert(self::TABLE_POST_REPORT, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function updateReport($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_POST_REPORT, $setArray);
    }

    public function getReports($where = array()) {
        return $this->db->select('*')
                    ->from(self::TABLE_POST_REPORT)
                    ->where($where)
                    ->get()
                    ->result_array();
    }

    
}