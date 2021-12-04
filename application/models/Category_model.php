<?php
  /**
     * Created by PhpStorm.
     * User: YueXi
     * Date: 2021/04/28
     * Time: 4:15 AM
 */
 class Category_model extends MY_Model {
     
     public function getCategories() {
         return $this->db->select('*')
            ->from("categories")
            ->get()
            ->result_array();
     }
     
 } 
