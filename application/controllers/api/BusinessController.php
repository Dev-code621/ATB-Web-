<?php


class BusinessController extends MY_Controller {

	public function add_holiday(){
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$holidayId = $this->UserBusiness_model->insertBusinessHoliday(
				array(
					'user_id' => $tokenVerifyResult['id'],
					'name' => $this->input->post('name'),
					'day_off' => $this->input->post('day_off'),
					'created_at' => time(),
					'updated_at' => time()
				)
			);
			$retVal[self::EXTRA_FIELD_NAME] = null;
			if ($holidayId > 0) {
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Successfully Added";
				$holiday = $this->UserBusiness_model->getBusinessHoliday($holidayId);
				$retVal[self::EXTRA_FIELD_NAME] = $holiday[0];
			} else {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Failed to add new holiday.";
			}
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}
	
	public function update_holiday(){
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$updateArray = array(
					'name' => $this->input->post('name'),
					'day_off' => $this->input->post('day_off'),
					'updated_at' => time()
				);

			$this->UserBusiness_model->updateBusinessHoliday(
				$updateArray,
				array('id' => $this->input->post('id'), "user_id" => $tokenVerifyResult['id'])
			);

			$holiday = $this->UserBusiness_model->getBusinessHoliday($this->input->post('id'));

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $holiday;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}
	
	public function delete_holiday(){
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$this->UserBusiness_model->removeBusinessHolidays(array('id' => $this->input->post('holiday_id'), "user_id" => $tokenVerifyResult['id']));

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Successfully deleted";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}
	
	public function update_week(){
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			
			$week = $this->input->post("week");
			if (!empty($week)) {
				$days = json_decode($week, true);
				for ($x = 0; $x < count($days); $x++) {
					$updateArray = array(
						'is_available' => $days[$x]["is_available"],
						'start' => $days[$x]["start"],
						'end' => $days[$x]["end"],
						'updated_at' => time()
					);

					$this->UserBusiness_model->updateBusinessWeekDay(
						$updateArray,
						array('day' => $days[$x]["day"], "user_id" => $tokenVerifyResult['id'])
					);
				}	    
			}
			
			$retVal[self::RESULT_FIELD_NAME] = true;        
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}

	public function request_rating() {
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$business = $this->UserBusiness_model->getBusinessInfo($tokenVerifyResult['id'])[0];

			$bookingId = $this->input->post('booking_id');
			$bookings = $this->Booking_model->getBooking($bookingId);

			if (count($bookings) > 0) {
				$serviceId = $bookings[0]['service_id'];
				$services= $this->UserService_model->getServiceInfo($serviceId);

				$this->NotificationHistory_model->insertNewNotification(
					array(
						'user_id' => $this->input->post('booked_user_id'),
						'type' => 10,
						'related_id' => $business['user_id'],
						'read_status' => 0,
						'send_status' => 0,
						'visible' => 1,
						'text' => " has requested a rating for " . $services[0]['title'],
						'name' => $business["business_name"],
						'profile_image' => $business["business_logo"],
						'updated_at' => time(),
						'created_at' => time()
					)
				);
			}			

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Successfully sent request";

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
		
	}
	
	public function request_payment() {
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$business = $this->UserBusiness_model->getBusinessInfo($tokenVerifyResult['id'])[0];

			$bookingId = $this->input->post('booking_id');
			$bookings = $this->Booking_model->getBooking($bookingId);

			if (count($bookings) > 0) {
				$serviceId = $bookings[0]['service_id'];
				$services= $this->UserService_model->getServiceInfo($serviceId);

				$this->NotificationHistory_model->insertNewNotification(
					array(
						'user_id' => $this->input->post('booked_user_id'),
						'type' => 9,
						'related_id' => $bookingId,
						'read_status' => 0,
						'send_status' => 0,
						'visible' => 1,
						'text' => " has requested payment for " . $services[0]['title'],
						'name' => $business["business_name"],
						'profile_image' => $business["business_logo"],
						'updated_at' => time(),
						'created_at' => time()
					)
				);
			}

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Successfully sent request";

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
		
	}
	
	public function add_disabled_slot(){
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$holidayId = $this->UserBusiness_model->insertBusinessDisabledSlot(
				array(
					'user_id' => $tokenVerifyResult['id'],
					'day_timestamp' => $this->input->post('day_timestamp'),
					'start' => $this->input->post('start'),
					'end' => $this->input->post('end'),
					'created_at' => time(),
					'updated_at' => time()
				)
			);
			$retVal[self::EXTRA_FIELD_NAME] = null;
			if ($holidayId > 0) {
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Successfully Added";
				$holiday = $this->UserBusiness_model->getBusinesssDisabledSlot($holidayId);
				$retVal[self::EXTRA_FIELD_NAME] = $holiday[0];
			} else {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Failed to add new disabled slot.";
			}
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}
	
	public function update_disabled_slot(){
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$updateArray = array(
					'day_timestamp' => $this->input->post('day_timestamp'),
					'start' => $this->input->post('start'),
					'end' => $this->input->post('end'),
					'updated_at' => time()
				);

			$this->UserBusiness_model->updateBusinessDisabledSlot(
				$updateArray,
				array('id' => $this->input->post('id'), "user_id" => $tokenVerifyResult['id'])
			);

			$holiday = $this->UserBusiness_model->getBusinesssDisabledSlot($this->input->post('id'));

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $holiday;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}
	
	public function delete_disabled_slot(){
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$this->UserBusiness_model->removeBusinesssDisabledSlot(array('id' => $this->input->post('slot_id'), "user_id" => $tokenVerifyResult['id']));

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Successfully deleted";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}
	
	public function get_disabled_slots(){
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$day = $this->input->post('day');
			$user_id = $this->input->post('user_id');
			
			$searchArray = array();
			if (!empty($user_id)){
				$searchArray["user_id"] = $user_id;
			} else {
				$searchArray["user_id"] = $verifyTokenResult['id'];
			}
			
			
			if(!empty($day)){
				$startDay = DateTime::createFromFormat('Y m d', $day);
				$startDay->setTime(0, 0, 0);
				
				$startDayTimestamp = $startDay->getTimestamp();
				
				$startDay->setTime(23, 59, 59);
				
				$endDayTimestamp = $startDay->getTimestamp();
				
				$searchArray["day_timestamp >="] = $startDayTimestamp;
				$searchArray["day_timestamp <="] = $endDayTimestamp;
			}
			
			$bookings = $this->UserBusiness_model->getBusinesssDisabledSlots($searchArray);
			
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $bookings;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
}

