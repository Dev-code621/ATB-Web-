<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 7:17 AM
 */
class UserBusiness_model extends MY_Model
{
    public function insertNewBusinessInfo($ins) {
        if($this->db->insert(self::TABLE_BUSINESS_INFO, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function getBusinessInfos($where = array()) {
        $businessInfos = $this->db->select('*')
                    ->from(self::TABLE_BUSINESS_INFO)
                    ->where($where)
                    ->order_by('created_at', 'DESC')
                    ->get()
                    ->result_array();

        for ($i = 0; $i < count($businessInfos); $i++) {
        	$businessInfos[$i]["opening_times"] = $this->getBusinessWeekDays(array("user_id" => $businessInfos[$i]["user_id"]));
        	$businessInfos[$i]["holidays"] = $this->getBusinessHolidays(array("user_id" => $businessInfos[$i]["user_id"]));
        	$businessInfos[$i]["disabled_slots"] = $this->getBusinesssDisabledSlots(array("user_id" => $businessInfos[$i]["user_id"]));
            $businessInfos[$i]['socials'] = $this->UserSocial_model->getUserSocials($businessInfos[$i]["user_id"]);
        }

        return $businessInfos;
    }

    public function updateBusinessRecord($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_BUSINESS_INFO, $setArray);
    }

    public function getBusinessInfoById($id) {
        $businessInfos = $this->db->select('*') -> from(self::TABLE_BUSINESS_INFO)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
	
	for ($i = 0; $i < count($businessInfos); $i++) {
        	$businessInfos[$i]["opening_times"] = $this->getBusinessWeekDays(array("user_id" => $businessInfos[$i]["user_id"]));
        	$businessInfos[$i]["holidays"] = $this->getBusinessHolidays(array("user_id" => $businessInfos[$i]["user_id"]));
        	$businessInfos[$i]["disabled_slots"] = $this->getBusinesssDisabledSlots(array("user_id" => $businessInfos[$i]["user_id"]));
        	$businessInfos[$i]['socials'] = $this->UserSocial_model->getUserSocials($businessInfos[$i]["user_id"]);
        }
	
        return $businessInfos;
    }

    public function getBusinessInfo($userId) {
        $businessInfos = $this->db->select('*')
            ->from(self::TABLE_BUSINESS_INFO)
            ->where(array('user_id' => $userId))
            ->get()
            ->result_array();
            
        for ($i = 0; $i < count($businessInfos); $i++) {
        	$businessInfos[$i]["opening_times"] = $this->getBusinessWeekDays(array("user_id" => $businessInfos[$i]["user_id"]));
        	$businessInfos[$i]["holidays"] = $this->getBusinessHolidays(array("user_id" => $businessInfos[$i]["user_id"]));
        	$businessInfos[$i]["disabled_slots"] = $this->getBusinesssDisabledSlots(array("user_id" => $businessInfos[$i]["user_id"]));
        	$businessInfos[$i]['socials'] = $this->UserSocial_model->getUserSocials($businessInfos[$i]["user_id"]);
        }

        return $businessInfos;
    }

    public function insertBusinessWeekDay($ins) {
        if($this->db->insert(self::TABLE_USER_BUSINESS_WEEK, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function updateBusinessWeekDay($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_USER_BUSINESS_WEEK, $setArray);
    }

    public function getBusinessWeekDay($id) {
       $Attribute =  $this->db->select('*')
            -> from(self::TABLE_USER_BUSINESS_WEEK)
            ->where(array('id' => $id))
            ->get()
            ->result_array();

        return $Attribute;
    }

    public function getBusinessWeekDays($search = array()) {
        $Attributes =  $this->db->select('*')
            -> from(self::TABLE_USER_BUSINESS_WEEK)
            ->where($search)
            ->get()
            ->result_array();

        return $Attributes;
    }

    public function removeBusinessWeekDays($where) {
        $this->db->where($where)->delete(self::TABLE_USER_BUSINESS_WEEK);
    }

   public function insertBusinessHoliday($ins) {
        if($this->db->insert(self::TABLE_USER_BUSINESS_HOLIDAY, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function updateBusinessHoliday($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_USER_BUSINESS_HOLIDAY, $setArray);
    }

    public function getBusinessHoliday($id) {
       $Attribute =  $this->db->select('*')
            -> from(self::TABLE_USER_BUSINESS_HOLIDAY)
            ->where(array('id' => $id))
            ->get()
            ->result_array();

        return $Attribute;
    }

    public function getBusinessHolidays($search = array()) {
        $Attributes =  $this->db->select('*')
            -> from(self::TABLE_USER_BUSINESS_HOLIDAY)
            ->where($search)
            ->get()
            ->result_array();

        return $Attributes;
    }

    public function removeBusinessHolidays($where) {
        $this->db->where($where)->delete(self::TABLE_USER_BUSINESS_HOLIDAY);
    }
    
    public function insertBusinessDisabledSlot($ins) {
        if($this->db->insert(self::TABLE_USER_BUSINESS_DISABLED_SLOTS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function updateBusinessDisabledSlot($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_USER_BUSINESS_DISABLED_SLOTS, $setArray);
    }

    public function getBusinesssDisabledSlot($id) {
       $Attribute =  $this->db->select('*')
            -> from(self::TABLE_USER_BUSINESS_DISABLED_SLOTS)
            ->where(array('id' => $id))
            ->get()
            ->result_array();

        return $Attribute;
    }

    public function getBusinesssDisabledSlots($search = array()) {
        $Attributes =  $this->db->select('*')
            -> from(self::TABLE_USER_BUSINESS_DISABLED_SLOTS)
            ->where($search)
            ->get()
            ->result_array();

        return $Attributes;
    }

    public function removeBusinesssDisabledSlot($where) {
        $this->db->where($where)->delete(self::TABLE_USER_BUSINESS_DISABLED_SLOTS);
    }
}
