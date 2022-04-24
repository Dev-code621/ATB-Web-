<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/22
 * Time: 7:17 AM
 */
class Booking_model extends MY_Model {
   public function insertBooking($ins) {
        if($this->db->insert(self::TABLE_SERVICE_BOOKING, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

    public function updateBooking($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_SERVICE_BOOKING, $setArray);
    }

    public function getBooking($id) {
       $bookings =  $this->db->select('*')
            -> from(self::TABLE_SERVICE_BOOKING)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
         
        for($i = 0 ; $i < count($bookings) ; $i++) {
        	if(!empty($bookings[$i]['user_id'])){
        		$bookings[$i]['user'] = $this->User_model->getOnlyUser(array('id' => $bookings[$i]['user_id']));
        	}
        	if(!empty($bookings[$i]['business_user_id'])){
                // updated Apr 24, 2022
                // open business profile (return the user including business profile)
        		// $bookings[$i]['business'] = $this->UserBusiness_model->getBusinessInfo($bookings[$i]['business_user_id']);
                $bookings[$i]['business'] = $this->User_model->getOnlyUser(array('id' => $bookings[$i]['business_user_id']));
        	}
        	if(!empty($bookings[$i]['service_id'])){
        		$bookings[$i]['service'] = $this->UserService_model->getServiceInfo($bookings[$i]['service_id']);
        	}
        	$bookings[$i]["transactions"] = $this->UserBraintreeTransaction_model->getTransactionHistory(array("purchase_type" => "booking", "target_id" => $bookings[$i]['id']));
        } 

        return $bookings;
    }

    public function getBookings($search = array()) {
        $bookings =  $this->db->select('*')
            -> from(self::TABLE_SERVICE_BOOKING)
            ->where($search)
            ->order_by("created_at", "asc")
            ->get()
            ->result_array();
            
         for($i = 0 ; $i < count($bookings) ; $i++) {
            if(!empty($bookings[$i]['user_id'])){
        		$bookings[$i]['user'] = $this->User_model->getOnlyUser(array('id' => $bookings[$i]['user_id']));
        	}
        	if(!empty($bookings[$i]['business_user_id'])){
                // updated Apr 24, 2022
                // open business profile (return the user including business profile)
        		// $bookings[$i]['business'] = $this->UserBusiness_model->getBusinessInfo($bookings[$i]['business_user_id']);
                $bookings[$i]['business'] = $this->User_model->getOnlyUser(array('id' => $bookings[$i]['business_user_id']));
        	}
        	if(!empty($bookings[$i]['service_id'])){
        		$bookings[$i]['service'] = $this->UserService_model->getServiceInfo($bookings[$i]['service_id']);
        	} 
        	$bookings[$i]["transactions"] = $this->UserBraintreeTransaction_model->getTransactionHistory(array("purchase_type" => "booking", "target_id" => $bookings[$i]['id'])); 
        } 

        return $bookings;
    }

    public function removeBookings($where) {
        $this->db->where($where)->delete(self::TABLE_SERVICE_BOOKING);
    }
    
    public function insertBookingReport($ins) {
        if($this->db->insert(self::TABLE_SERVICE_BOOKING_REPORT, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }

}
