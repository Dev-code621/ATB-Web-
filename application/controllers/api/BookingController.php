<?php


class BookingController extends MY_Controller {
	
	public function search_user(){
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$user_id = $this->input->post('user_id');
			$email = $this->input->post('email');
			
			$searchArray = array();
			
			if (!empty($user_id)) {
				$searchArray["id"] = $user_id;
			} 
			
			if (!empty($email)) {
				$searchArray["user_email"] = $email;
			} 
			
			$users = $this->User_model->getOnlyUser($searchArray);
			
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::EXTRA_FIELD_NAME] = $users;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}	
	
	public function get_booking(){
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$booking_id = $this->input->post('booking_id');
			
			$booking = $this->Booking_model->getBooking($booking_id);
			
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::EXTRA_FIELD_NAME] = $booking;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
	

	public function get_bookings()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$user_id = $this->input->post('user_id');
			$is_business = $this->input->post('is_business');
			$month = $this->input->post('month');
			
			$searchArray = array();
			
			if ($is_business) {
				$searchArray["business_user_id"] = $user_id;
			} else {
				$searchArray["user_id"] = $user_id;
			}

			// to remove returning pending bookings in the return
			$searchArray['state <>'] = 'pending';
			
			if(!empty($month)){
				$startMonth = DateTime::createFromFormat('Y m', $month);
				$startMonth->setTime(0, 0, 0);
				$startMonth->modify('first day of this month');
				
				$startTimeStamp = $startMonth->getTimestamp();
				
				$startMonth->add(new DateInterval('P1M'));
				
				$endTimeStamp = $startMonth->getTimestamp();
				
				$searchArray["booking_datetime >="] = $startTimeStamp;
				$searchArray["booking_datetime <="] = $endTimeStamp;
				
			}
			
			$bookings = $this->Booking_model->getBookings($searchArray);
			
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::EXTRA_FIELD_NAME] = $bookings;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
	
	public function create_booking(){
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$serviceId = $this->input->post('service_id');
			$userId = $this->input->post('user_id');
			$business_user_id = $this->input->post('business_user_id');
			$total_cost = $this->input->post('total_cost');
			$booking_datetime = $this->input->post('booking_datetime');
			$is_reminder_enabled = $this->input->post('is_reminder_enabled');
			$transaction_id = $this->input->post('transaction_id');
			$email = $this->input->post('email');
			$full_name = $this->input->post('full_name');
			$phone = $this->input->post('phone');

			$createdBy = $this->input->post('created_by');
						
			$insertArray = array(
					'business_user_id' => $business_user_id,
					'booking_datetime' => $booking_datetime,
					'is_reminder_enabled' => $is_reminder_enabled,
					'total_cost' => $total_cost,
					'created_at' => time(),
					'updated_at' => time()
				);				
			
			if (!empty($serviceId)){
				$insertArray['service_id'] = $serviceId;
			}
			if (!empty($userId)){
				$insertArray['user_id'] = $userId;
			}
			if (!empty($transaction_id)){
				$insertArray['transaction_id'] = $transaction_id;
			}
			if (!empty($email)){
				$insertArray['email'] = $email;
			}
			if (!empty($full_name)){
				$insertArray['full_name'] = $full_name;
			}
			if (!empty($phone)){
				$insertArray['phone'] = $phone;
			}
			
			$bookingId = $this->Booking_model->insertBooking($insertArray);
			$retVal[self::EXTRA_FIELD_NAME] = null;

			if ($bookingId > 0) {				
				$services = $this->UserService_model->getServiceInfo($serviceId);

				date_default_timezone_set('UTC');
				$bookingDate = date('jS F', $booking_datetime);
				$bookingTime = date('g:i A', $booking_datetime);

				// business user profile
				$businessUser = $this->User_model->getOnlyUser(array('id' => $business_user_id))[0];
				// business profile
				$business = $this->UserBusiness_model->getBusinessInfo($business_user_id)[0];
				
				$businessUrl = $business['business_website'];
				if (empty($businessUrl)) {
					$busienssUrl = $business['business_name'];
				}

				if (!empty($userId)) {
					$users = $this->User_model->getOnlyUser(array('id' => $userId));

					/*
					if ($services[0]['is_deposit_required'] != '1') {
						$this->NotificationHistory_model->insertNewNotification(
							array(
								'user_id' => $business_user_id,
								'type' => 6,
								'related_id' => $bookingId,
								'read_status' => 0,
								'send_status' => 0,
								'visible' => 1,
								'text' =>  " has booked " . $services[0]['title'],
								'name' => $users[0]['user_name'],
								'profile_image' => $users[0]['pic_url'],
								'updated_at' => time(),
								'created_at' => time()
							)
						);
					}
					*/
					
					$this->emailToBusiness(
						$businessUser['user_email'], 
						$bookingId, 
						$users[0]['pic_url'], 
						$businessUser['first_name'].' '.$businessUser['last_name'],
						$users[0]['user_name'], 
						$services[0]['title'],
						$bookingDate, 
						$bookingTime, 
						$services[0]['price'],
						$createdBy == '0' ? $services[0]['deposit_amount'] : 0
					);

					$this->emailToUser(
						$users[0]['user_email'],
						$bookingId,
						$business['business_logo'], 
						$business['business_name'], 
						$businessUrl,
						$services[0]['title'],
						$bookingDate, 
						$bookingTime, 
						$services[0]['price'],
						$createdBy == '0' ? $services[0]['deposit_amount'] : 0
					);

				} else {					
					$this->emailToBusiness(
						$businessUser['user_email'], 
						$bookingId, 
						base_url().'assets/placeholder.png', 
						$full_name,
						$email, 
						$services[0]['title'],
						$bookingDate, 
						$bookingTime, 
						$services[0]['price'],
						0
					);

					$this->emailToUser(
						$email,
						$bookingId,
						$business['business_logo'], 
						$business['business_name'], 
						$businessUrl,
						$services[0]['title'],
						$bookingDate, 
						$bookingTime, 
						$services[0]['price'],
						0
					);
				}
				
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Successfully Added";
				$booking = $this->Booking_model->getBooking($bookingId);
				$retVal[self::EXTRA_FIELD_NAME] = $booking[0];

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

	private function emailToBusiness($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit) {
		$subject = "New Booking Request";

		$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
		<head>
			<!--[if gte mso 9]>
			<xml>
				<o:OfficeDocumentSettings>
				<o:AllowPNG/>
				<o:PixelsPerInch>96</o:PixelsPerInch>
				</o:OfficeDocumentSettings>
			</xml>
			<![endif]-->
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
		<meta name="format-detection" content="date=no" />
		<meta name="format-detection" content="address=no" />
		<meta name="format-detection" content="telephone=no" />
		<meta name="x-apple-disable-message-reformatting" />
		 <!--[if !mso]><!-->
			<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet" />
		<!--<![endif]-->
		<title>Subject: ATB - New Booking</title>
		
		<style type="text/css"> 
		
			body { padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#A6BFDE; -webkit-text-size-adjust:none }
			p { padding:0 !important; margin:0 !important } 
			table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important;}
			.container {width: 100%; max-width: 650px;}
			.ExternalClass { width: 100%;}
			.ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div {line-height: 100%; }
			
			@media (max-width: 650px) {
				.wrapper {padding: 0 !important;}
				.container { width: 100% !important; min-width: 100% !important; }
				.content {padding-left: 30px !important; padding-right: 30px !important;}
				.social-btn {height: 35px; width: auto;}
				.p100 { display: block !important; width: 100% !important; min-width: 100%; padding-bottom: 10px !important; padding-left: 0px !important; float: left !important; }
				.mleft {float: left !important;;}
				.p100 table {width: 100% !important;}
				.mfont {font-size: 20px !important;}
				.mfont2 {font-size: 16px !important;}
				.t100 {width: 50% !important; float: left !important}
				.t100 tr td { display: block !important; float: left !important;}
				.black {color: #000000 !important;}
				.mnone {display: none !important;}
			}
		</style>
		
		
		</head>
		
		<body style="padding:0; margin:0; background: #A6BFDE;">
		
		<table border="0" bgcolor="#A6BFDE" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
			<tr>
				<td align="center" valign="top" style="padding: 80px 0;" class="wrapper">   
					<table border="0" cellspacing="0" cellpadding="0" class="container">
						<tr>
							<td>
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td style="padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
								
												<table width="100%" border="0" cellspacing="0" cellpadding="35">
												<tr>
													<td width="65%">
														<h1 style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: bold;font-size: 30px;line-height: 31px;text-align: left;color: #fff; margin:0;">Hi '.$name.',</h1>
														<h2 style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 20px;text-align: left;color: #fff; margin:0;">Please find below details of your booking:</h2>
													</td>
													<td width="35%" style=""><a href="#" target="_blank"><img src="'.base_url().'assets/email/booking/logo.png" width="153" height="47" border="0" alt="ATB Logo" /></a></td>	
												</tr>
												</table>
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td>
															<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#F8F8F8">
																<tr>
																	<td style="padding: 30px 40px" class="content">
																		<table width="100%" border="0" cellspacing="0" cellpadding="0">
																			<tr>
																				<td>
																					<table border="0" cellspacing="0" cellpadding="0" width="100%" >
																						<tr>
																							<td class="p100">
																								<table border="0" cellspacing="0" cellpadding="0" align="left" class="t100" width="100%">
																									<tr>
																										<td style="border-radius: 50%;" width="73" valign="baseline">
																											<img src="'.base_url().'assets/email/booking/user.png" width="73" height="73" border="0" alt="user icon" style="border-radius:100%;"/>
																										</td>
																										<td style="padding-left: 10px;">
																											<table width="100%" border="0" cellpadding="0" cellspacing="0">
																												<tr><td style="color:#454b4d; font-family:&#39Roboto&#39, Arial, sans-serif; font-size:15px; line-height:20px;">Business name</td></tr>
																												<tr><td style="color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: normal;font-size: 15px;line-height: 16px;text-align: left;color: #787f82;">'.$username.'</td></tr>
																											</table>
																										</td>
																									</tr>
																								</table>
																							</td>
																							<td class="p100" align="right" width="200">
																								<table border="0" cellspacing="0" cellpadding="0" align="right" class="t100" width="100%">
																									<tr>
																									  <td><a href="#" style="color:#535353;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700;display: block;"><img src="'.base_url().'assets/email/booking/message.png" width="20" height="19" alt="Message icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;" /> Message Business</a> </td></tr>
																									<tr><td valign="middle"><a href="#" style="color:#535353;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700"><img src="'.base_url().'assets/email/booking/booking.png" width="20" height="16" alt="Booking icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;" /> Manage this booking</a> </td></tr>
																								</table>
																							</td>
																						</tr>
																					</table>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
		
															<table width="100%" border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3; padding: 10px 40px 80px" bgcolor="#ffffff" class="content">
																		<table width="100%" border="0" cellspacing="0" cellpadding="0">
																			<tr>
																				<td>
																					<table width="100%" border="0" cellspacing="0" cellpadding="0">
																						<tr>
																							<td width="" class="">
																								<p style="color:#454B4D;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service:</p>
																								<p style="color: #787F82;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">'.$title.'</p>
																							</td>
																						  <td class="" width="187">
																								<p style="color:#454B4D;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service Schedule:</p>
																								<p style="color: #787F82;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">
																									<span><img src="'.base_url().'assets/email/booking/calendar.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;" /> '.$date.'</span> &nbsp;
																									<span><img src="'.base_url().'assets/email/booking/clock.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;" /> '.$time.'</span></p>
																							</td>
																							<td width="" class="p100">
																								<p style="color:#454B4D;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Order Number:</p>
																								<p style="color: #787F82;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">#'.$bookingId.'</p>
																						  </td>
																							<td width="" class="" style="">
																								<a href="#" target="_blank" style="font-family: &#39Roboto&#39, Arial, sans-serif; font-weight: normal; font-size: 13px; line-height: 13px; color: #fff; border-radius: 5px; background: #a6bfde; box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.16); display: block; padding: 10px; text-decoration: none; text-align: center; width: 100%;" ><img src="'.base_url().'assets/email/booking/addcalendar.png" width="14" height="16" alt="" style="display: inline !important; vertical-align: middle; margin-right: 5px;"/> Add to calendar</a>
																							</td>
																						</tr>
																					</table>
																				</td>
																			</tr>
																			<tr>
																				<td style="padding-top: 20px;">
																					<table width="100%" border="0" cellspacing="0" cellpadding="0">
																						<tr>
																							<td style="color: #787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:50px">Invoice</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" style="color:#454B4D;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Service total cost</td>
																							<td align="right"  style="color:#A6BFDE;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;">£'.number_format($total, 2).'</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" style="color:#454B4D;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Deposit paid</td>
																							<td align="right" style="color:#A6BFDE;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;">-£'.number_format($deposit, 2).'</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" class="mfont2" style="color:#787F82;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Payment Pending</td>
																							<td align="right" style="color:#787F82;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">£'.number_format($total-$deposit, 2).'</td>
																						</tr>
																					</table>
																				</td>
																			</tr>
																			<tr>
																				<td>
																					<br>
																					<hr>
																					<p style="font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px;font-weight: normal;font-size: 15px;line-height: 18px;text-align: left;color: #787f82;">If you can no longer attend or need to make amendments to the appointment, please get in touch with [business name] at the earliest.</p>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
		
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td style="padding: 30px 40px 0px" class="content">
															<table width="100%" border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td class="p100">
																		<table border="0" align="left" cellspacing="0" cellpadding="0" class="bottomNav">
																			<tr><td align="left"><a href="#" style="color: #ffffff;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Terms and conditions</a></td></tr>
																			<tr><td align="left"><a href="#" style="color: #ffffff;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;" class="black">Privacy Policy</a> </td></tr>
																			<tr><td align="left"><a href="#" style="color: #ffffff;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Contact Us</a> </td></tr>
																		</table>
																	</td>
																	<td class="p100" align="right" width="400">
																		<table border="0" cellspacing="0" cellpadding="0" >
																		</table>
																		<table border="0" cellspacing="0" cellpadding="0" >
																			<tr>
																				<td align="right" class="p100" style="padding-top: 10px"><a href="#" class="mleft" style="color:#ffffff;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:right; text-decoration: none !important;display: block !important;">ATB All rights reserved</a> </td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
		
											<!--[if gte mso 9]>
												</v:textbox>
												</v:rect>
											<![endif]-->
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
						</td>
						</tr>
					</table>
					<![endif]-->
				</td>
			</tr>
		</table>
		
		</body>
		</html>
		';

		$this->sendEmail(
			$to,
			$subject,
			$content);
	}

	private function emailToUser($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit) {
		$subject = "Booking Confirmed";

		$content = '
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<!-- saved from url=(0057)http://sg-lab.co/dev/atb/email/booking-confirm/index.html -->
			<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
					<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE">
					<meta name="format-detection" content="date=no">
					<meta name="format-detection" content="address=no">
					<meta name="format-detection" content="telephone=no">
					<meta name="x-apple-disable-message-reformatting">
					<link href="./booking confirmed_files/css" rel="stylesheet">
					<title>Welcome to ATB</title>

					<style type="text/css"> 

						body { padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#F8F8F8; -webkit-text-size-adjust:none }
						p { padding:0 !important; margin:0 !important } 
						table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important;}
						.container {width: 100%; max-width: 650px;}
						.ExternalClass { width: 100%;}
						.ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div {line-height: 100%; }
						
						@media (max-width: 650px) {
							.wrapper {padding: 0 !important;}
							.container { width: 100% !important; min-width: 100% !important; }
							.content {padding-left: 30px !important; padding-right: 30px !important;}
							.social-btn {height: 35px; width: auto;}
							.p100 { display: block !important; width: 100% !important; min-width: 100%; padding-bottom: 10px !important; padding-left: 0px !important; float: left !important; }
							.mleft {float: left !important;;}
							.p100 table {width: 100% !important;}
							.mfont {font-size: 20px !important;}
							.mfont2 {font-size: 16px !important;}
							.t100 {width: 50% !important; float: left !important}
							.t100 tr td { display: block !important; float: left !important;}
							.black {color: #000000 !important;}
							.mnone {display: none !important;}
						}
					</style>

				</head>

				<body style="padding:0; margin:0">
					<table border="0" bgcolor="#F8F8F8" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
						<tbody>
							<tr>
								<td align="center" valign="top" style="padding: 80px 0;" class="wrapper">
									<table border="0" cellspacing="0" cellpadding="0" class="container">
										<tbody>
											<tr>
												<td>
													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tbody>
															<tr>
																<td style="background-image:url('.base_url().'assets/email/welcome/bacgkround.jpg); padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
																	<table width="100%" border="0" cellspacing="0" cellpadding="0">
																		<tbody>
																			<tr>
																				<td align="right" style="padding: 50px 30px" class="mleft">
																					<a href="#" target="_blank"><img src="'.base_url().'assets/email/logo.png" width="153" height="47" border="0" alt="ATB Logo"></a>
																				</td>
																			</tr>
																		</tbody>
																	</table>

																	<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#F8F8F8">
																		<tbody>
																			<tr>
																				<td style="padding: 20px 40px;">
																					<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#F8F8F8">
																						<tbody><tr>
																							<td style="padding-bottom: 30px !important">
																								<table width="100%" border="0" cellspacing="0" cellpadding="0">
																									<tbody><tr>
																										<td style="color: #787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:23px; line-height:30px;" class="mfont"><img src="'.base_url().'assets/email/booking/email.png" width="23" height="24" alt="Email icon" style="display: inline !important;padding-right: 3px"> Your Booking has been confirmed!</td>
																									</tr>
																									<tr>
																										<td style="color:#575757;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:25px;padding-bottom:20px;">#'.$bookingId.'</td>
																									</tr>
																								</tbody></table>
																								<table width="100%" border="0" cellspacing="0" cellpadding="0">
																									<tbody><tr>
																										<td>
																											<table border="0" cellspacing="0" cellpadding="0" width="100%">
																												<tbody><tr>
																													<td class="p100">
																														<table border="0" cellspacing="0" cellpadding="0" align="left" class="t100" width="100%">
																															<tbody><tr>
																																<td style="border-radius: 50%;" width="73" valign="baseline">
																																	<img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%;">
																																</td>
																																<td style="padding-left: 10px;">
																																	<table border="0" cellspacing="0" cellpadding="0">
																																		<tbody><tr><td style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:27px; line-height:31px;">'.$name.'</td></tr>
																																		<tr><td><a href="#" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none !important;">'.$username.'</a> </td></tr>																																		
																																	</tbody></table>
																																</td>
																															</tr>
																														</tbody></table>
																													</td>
																													<td class="p100" align="right" width="200">
																														<table border="0" cellspacing="0" cellpadding="0" align="right" class="t100" width="100%">
																															<tbody><tr><td><a href="#" style="color:#535353;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700;display: block;"><img src="'.base_url().'assets/email/booking/message.png" width="20" height="19" alt="Message icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;"> Message User</a> </td></tr>
																															<tr><td valign="middle"><a href="#" style="color:#535353;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700"><img src="'.base_url().'assets/email/booking/booking.png" width="20" height="16" alt="Booking icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;"> Manage this booking</a> </td></tr>
																														</tbody></table>
																													</td>
																												</tr>

																											</tbody></table>
																										</td>
																									</tr>
																								</tbody></table>
																							</td>
																						</tr>
																					</tbody></table>

																					<table width="100%" border="0" cellspacing="0" cellpadding="0">
																						<tbody><tr>
																							<td style="border-radius: 8px 8px 8px 8px;box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ffffff">
																								<table width="100%" border="0" cellspacing="0" cellpadding="0">
																									<tbody><tr>
																										<td bgcolor="#A6BFDE" style="border-radius: 8px 8px 0 0; padding: 15px 20px">
																											<table width="100%" border="0" cellspacing="0" cellpadding="0">
																												<tbody><tr>
																													<td class="p100">
																														<p style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service:</p>
																														<p style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">'.$title.'</p>
																													</td>
																													<td class="p100" width="220">
																														<p style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service Schedule:</p>
																														<p style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">
																															<span><img src="'.base_url().'assets/email/booking/calendar.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$date.'</span> &nbsp;
																															<span><img src="'.base_url().'assets/email/booking/clock.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$time.'</span></p>
																													</td>
																													<td class="p100">
																														<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
																															<tbody><tr>
																																<td bgcolor="#6F86A3" width="140" align="center" valign="middle" height="30" style="border-radius: 7px;padding: 5px 10px;">
																																	<a href="#" target="_blank" bgcolor="#6F86A3" style="color:#ffffff; text-decoration:none !important; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:15px;width: 100%; line-height: 15px; display: inline-block; text-align: center;"><img src="'.base_url().'assets/email/booking/addcalendar.png" width="14" height="16" alt="" style="display: inline !important;vertical-align: middle;"> <span style="color:#ffffff; text-decoration:none; padding: 0 5px">Add to calendar</span></a>
																																</td>
																															</tr>
																														</tbody></table>
																													</td>
																												</tr>
																											</tbody></table>
																										</td>
																									</tr>
																									<tr>
																										<td style="padding: 30px 20px;">
																											<table width="100%" border="0" cellspacing="0" cellpadding="0">
																												<tbody><tr>
																													<td style="color: #787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:50px">Invoice</td>
																												</tr>
																												<tr style="border-top: 1px solid #E3E3E3;">
																													<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Total Per Booking</td>
																													<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;">£'.number_format($total, 2).'</td>
																												</tr>
																												<tr style="border-top: 1px solid #E3E3E3;">
																													<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Deposit</td>
																													<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;">-£'.number_format($deposit, 2).'</td>
																												</tr>
																												<tr style="border-top: 1px solid #E3E3E3;">
																													<td align="left" class="mfont2" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Payment Pending</td>
																													<td align="right" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">£'.number_format($total-$deposit, 2).'</td>
																												</tr>
																											</tbody></table>
																										</td>
																									</tr>
																								</tbody></table>
																							</td>
																						</tr>
																					</tbody></table>
																				
																				</td>
																			</tr>
																		</tbody>
																	</table>

																	<table width="100%" border="0" cellspacing="0" cellpadding="0">
																		<tbody>
																			<tr>
																				<td style="padding: 30px 40px 0px" class="content">
																					<table width="100%" border="0" cellspacing="0" cellpadding="0">
																						<tbody>
																							<tr>
																								<td class="p100">
																									<table border="0" align="left" cellspacing="0" cellpadding="0" class="bottomNav">
																										<tbody>
																											<tr><td align="left"><a href="#" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Terms and conditions</a></td></tr>
																											<tr><td align="left"><a href="#" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;" class="black">Privacy Policy</a> </td></tr>
																											<tr><td align="left"><a href="#" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Contact Us</a> </td></tr>
																										</tbody>
																									</table>
																								</td>
																								<td class="p100" align="right" width="400">
																									<table border="0" cellspacing="0" cellpadding="0">
																										<tbody>
																											<tr>
																												<td style="padding-right:10px"><a href="#"><img class="social-btn" src="'.base_url().'assets/email/google-play.png" width="148" height="44" border="0" alt=""></a></td>
																												<td><a href="#"><img class="social-btn" src="'.base_url().'assets/email/apple-store.png" width="132" height="44" border="0" alt=""></a></td>
																											</tr>
																										</tbody>
																									</table>
																									<table border="0" cellspacing="0" cellpadding="0">
																										<tbody>
																											<tr>
																												<td align="right" class="p100" style="padding-top: 10px"><a href="#" class="mleft" style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:right; text-decoration: none !important;display: block !important;">ATB All rights reserved</a> </td>
																											</tr>
																										</tbody>
																									</table>
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>							
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</body>
			</html>';
		
		$this->sendEmail(
			$to,
			$subject,
			$content);
	}
	
	public function cancel_booking(){
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$booking_id = $this->input->post('booking_id');
			$is_requested_by = $this->input->post('is_requested_by');
			
			$booking = $this->Booking_model->getBooking($booking_id)[0];
			
			/*$days = 0;
			if(!empty($booking['service_id'])){
        			$days = $booking['service'][0]['cancellations'];
        		}
        		
        		$bookingTimestamp = $booking["booking_datetime"];
        		$currentTimestamp = time();
        		
        		$cancelCutOff = $bookingTimestamp - (86400 * $days);
        		
        		if ($is_requested_by == 1 || $currentTimestamp < $cancelCutOff) {*/
        			$setArray = array("state" => "cancelled");
        			$whereArray = array("id" => $booking_id);
        			
        			$this->Booking_model->updateBooking($setArray, $whereArray);

					$bookings = $this->Booking_model->getBooking($booking_id);
					if (count($bookings) > 0) {
						$services= $this->UserService_model->getServiceInfo($bookings[0]['service_id']);

						if ($is_requested_by == 1) {
							$business = $this->UserBusiness_model->getBusinessInfo($verifyTokenResult['id'])[0];

							date_default_timezone_set('UTC');
							$dateTime = date('jS F g:i A', $bookings[0]['booking_datetime']);

							$this->NotificationHistory_model->insertNewNotification(
								array(
									'user_id' => $bookings[0]['user_id'],
									'type' => 11,
									'related_id' => $bookings[0]['service_id'],
									'read_status' => 0,
									'send_status' => 0,
									'visible' => 1,
									'text' =>  " has cancelled the " . $dateTime . " for " . $services[0]['title'] . " - Click here to re-book",
									'name' => $business['business_name'],
									'profile_image' => $business['business_logo'],
									'updated_at' => time(),
									'created_at' => time()
								)
							);

						} else {
							$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));

							$this->NotificationHistory_model->insertNewNotification(
								array(
									'user_id' => $services[0]['user_id'],
									'type' => 7,
									'related_id' => $booking_id,
									'read_status' => 0,
									'send_status' => 0,
									'visible' => 1,
									'text' =>  " has cancelled the " . $dateTime . " for " . $services[0]['title'],
									'name' => $users[0]['user_name'],
									'profile_image' => $users[0]['pic_url'],
									'updated_at' => time(),
									'created_at' => time()
								)
							);
						}
					}
        			
        			$retVal[self::RESULT_FIELD_NAME] = true;
					$retVal[self::MESSAGE_FIELD_NAME] = "Booking Cancelled";
        		/*} else {
        			$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Unable to cancel";
        		}*/
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
	
	public function complete_booking(){
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$booking_id = $this->input->post('booking_id');
			
			$booking = $this->Booking_model->getBooking($booking_id)[0];
			
			$setArray = array("state" => "complete");
        		$whereArray = array("id" => $booking_id);
        			
        		$this->Booking_model->updateBooking($setArray, $whereArray);
        			
        		$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Booking Complete";
        	
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
	
	public function update_booking(){
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$booking_id = $this->input->post('booking_id');
			$is_requested_by = $this->input->post('is_requested_by');
			$update_date = $this->input->post('update_date');
			
			$booking = $this->Booking_model->getBooking($booking_id)[0];
			
			$days = 0;
			if(!empty($booking['service_id'])) {
				$days = $booking['service'][0]['cancellations'];
			}
        		
			$bookingTimestamp = $booking["booking_datetime"];
			$currentTimestamp = time();
			$cancelCutOff = $bookingTimestamp - (86400 * $days);
			       		
			if ($is_requested_by == 1 || $currentTimestamp < $cancelCutOff) {
				$setArray = array("booking_datetime" => $update_date);        			
				$whereArray = array("id" => $booking_id);        			
				$this->Booking_model->updateBooking($setArray, $whereArray);
				
				if ($is_requested_by != 1) {
					$services= $this->UserService_model->getServiceInfo($booking['service_id']);
					$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));

					date_default_timezone_set('UTC');
					$oldDateTime = date('jS F g:i A', $bookingTimestamp);
					$newDateTime = date('jS F g:i A', $update_date);
					
					$this->NotificationHistory_model->insertNewNotification(
						array(
							'user_id' => $services[0]['user_id'],
							'type' => 8,
							'related_id' => $booking_id,
							'read_status' => 0,
							'send_status' => 0,
							'visible' => 1,
							'text' =>  " has amended the booking for " . $services[0]['title'] . " from " . $oldDateTime . " to " . $newDateTime,
							'name' => $users[0]['user_name'],
							'profile_image' => $users[0]['pic_url'],
							'updated_at' => time(),
							'created_at' => time()
						)
					);
				}
				
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Booking Updated";

			} else {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Unable to update";
			}
			
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
	
	public function set_reminder(){
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$booking_id = $this->input->post('booking_id');
			$enabled = $this->input->post('enabled');
			
			$setArray = array("is_reminder_enabled" => $enabled);
        		$whereArray = array("id" => $booking_id);
        		
        		$this->Booking_model->updateBooking($setArray, $whereArray);
        			
        		$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Booking Updated";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
	
	public function create_booking_report(){
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$insertArray = array(
					'problem' => $this->input->post('problem'),
					'user_id' => $verifyTokenResult['id'],
					'created_at' => time(),
					'updated_at' => time()
				);
				
			if (!empty($this->input->post('booking_id'))){
				$insertArray['booking_id'] = $this->input->post('booking_id');
			}
			if (!empty($this->input->post('service_id'))){
				$insertArray['service_id'] = $this->input->post('service_id');
			}
			if (!empty($this->input->post('business_id'))){
				$insertArray['business_id'] = $this->input->post('business_id');
			}
			$reportContent = $this->Booking_model->insertBookingReport($insertArray);
			
			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));

			$content = '
<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">Thank you for contacting the ATB admin team. Someone will get back to you as soon as possible.</span></p>
<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;"><b></b></span></p>';

			$subject = 'ATB Admin Contacted';

			$this->User_model->sendUserEmail($users[0]["user_email"], $subject, $content);
			
        		$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Booking Report Created";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}

}
