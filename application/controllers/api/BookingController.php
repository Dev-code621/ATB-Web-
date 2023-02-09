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
					
					$this->ServiceemailToBusiness(
						$businessUser['user_email'], 
						$bookingId, 
						$users[0]['pic_url'], 
						$businessUser['first_name'].' '.$businessUser['last_name'],
						$users[0]['first_name'].' '.$users[0]['last_name'], 
						$services[0]['title'],
						$bookingDate, 
						$bookingTime, 
						$services[0]['price'],
						$services[0]['deposit_amount'],
						($services[0]['price']*0.036 + $services[0]['price']*0.014 + 0.2)					
					);

					$this->ServiceemailToUser(
						$users[0]['user_email'],
						$bookingId,
						$business['business_logo'], 
						$users[0]['first_name'].' '.$users[0]['last_name'], 
						$businessUser['first_name'].' '.$businessUser['last_name'],
						$services[0]['title'],
						$bookingDate, 
						$bookingTime, 
						$services[0]['price'],
						$services[0]['deposit_amount'],
						($services[0]['price']*0.036 + $services[0]['price']*0.014 + 0.2)
					);
				} else {					
					$this->ServiceemailToBusiness(
						$businessUser['user_email'], 
						$bookingId, 
						base_url().'assets/placeholder.png', 
						$businessUser['first_name'].' '.$businessUser['last_name'],
						$full_name,						
						$services[0]['title'],
						$bookingDate, 
						$bookingTime, 
						$services[0]['price'],
						$services[0]['deposit_amount'],
						($services[0]['price']*0.036 + $services[0]['price']*0.014 + 0.2)					);

					$this->ServiceemailToUser(
						$email,
						$bookingId,
						$business['business_logo'], 
						$full_name,			
						$business['business_name'], 
					
						$services[0]['title'],
						$bookingDate, 
						$bookingTime, 
						$services[0]['price'],
						$services[0]['deposit_amount'],
						($services[0]['price']*0.036 + $services[0]['price']*0.014 + 0.2)
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

	private function ServiceemailToBusiness($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit,$atb_fee) {
		$subject = "New Booking Request";

        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<!doctype html>
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
		
		<head>
		  <title>*|MC:SUBJECT|*</title>
		  <!--[if !mso]><!-->
		  <meta http-equiv="X-UA-Compatible" content="IE=edge">
		  <!--<![endif]-->
		  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  
		  <!--[if mso]>
				<noscript>
				<xml>
				<o:OfficeDocumentSettings>
				  <o:AllowPNG/>
				  <o:PixelsPerInch>96</o:PixelsPerInch>
				</o:OfficeDocumentSettings>
				</xml>
				</noscript>
				<![endif]-->
		  <!--[if lte mso 11]>
				<style type="text/css">
				  .mj-outlook-group-fix { width:100% !important; }
				</style>
				<![endif]-->
		  <!--[if !mso]><!-->
		  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css">
		  <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
		  <!--<![endif]-->
		  
		  
		  
		<style type="text/css">
				#outlook a{
					padding:0;
				}
				body{
					margin:0;
					padding:0;
					-webkit-text-size-adjust:100%;
					-ms-text-size-adjust:100%;
				}
				table,td{
					border-collapse:collapse;
					mso-table-lspace:0pt;
					mso-table-rspace:0pt;
				}
				img{
					border:0;
					height:auto;
					line-height:100%;
					outline:none;
					text-decoration:none;
					-ms-interpolation-mode:bicubic;
				}
				p{
					display:block;
					margin:13px 0;
				}
			@media only screen and (min-width:480px){
				.mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-250{
					width:250px !important;
					max-width:250px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-350{
					width:350px !important;
					max-width:350px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-200{
					width:200px !important;
					max-width:200px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-240{
					width:240px !important;
					max-width:240px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
		
		}		.moz-text-html .mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
				.moz-text-html .mj-column-px-250{
					width:250px !important;
					max-width:250px;
				}
				.moz-text-html .mj-column-px-350{
					width:350px !important;
					max-width:350px;
				}
				.moz-text-html .mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
				.moz-text-html .mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
				.moz-text-html .mj-column-px-200{
					width:200px !important;
					max-width:200px;
				}
				.moz-text-html .mj-column-px-240{
					width:240px !important;
					max-width:240px;
				}
				.moz-text-html .mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
				.moz-text-html .mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
			@media only screen and (max-width:480px){
				table.mj-full-width-mobile{
					width:100% !important;
				}
		
		}	@media only screen and (max-width:480px){
				td.mj-full-width-mobile{
					width:auto !important;
				}
		
		}</style></head>
		
		<body style="word-spacing:normal;background-color:#eeeeee;">
		  <div style="background-color:#eeeeee;">
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px;line-height:10px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#ABC1DE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#ABC1DE;background-color:#ABC1DE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ABC1DE;background-color:#ABC1DE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:rtl;font-size:0px;padding:20px 0;padding-bottom:35px;padding-top:35px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
								  <tbody>
									<tr>
									  <td style="width:153px;">
										<img mc:edit="main11" height="auto" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 153px;" width="153">
									  </td>
									</tr>
								  </tbody>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main12" style="font-family:Roboto;font-size:30px;font-weight:bold;line-height:1;text-align:left;color:#FFFFFF;">Hi  '.$name.',</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main13" style="font-family:Roboto;font-size:20px;line-height:1;text-align:left;color:#FFFFFF;">You Have A New Booking!</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#F8F8F8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#F8F8F8;background-color:#F8F8F8;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#F8F8F8;background-color:#F8F8F8;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:370px;" ><![endif]-->
					  <div class="mj-column-px-370 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="75"><img mc:edit="main14" src="' . $profile . '" width="75" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 75px;"></td>
									<td width="10"></td>
									<td mc:edit="main15" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:28px;font-weight:normal;">Client name:<br>
									<span style="color:#787f82; font-family:roboto,arial,sans-serif; font-size:15px; font-weight:normal; line-height:28px">' . $username . '</span></td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:270px;" ><![endif]-->
					  <div class="mj-column-px-270 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="20"><img mc:edit="main16" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 20px;"></td>
									<td width="10"></td>
									<td mc:edit="main17" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:28px;font-weight:bold;"> Manage this booking </td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:200px;" ><![endif]-->
					  <div class="mj-column-px-200 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main18" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Service:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main19" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#787f82;">' . $title . '
								</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:240px;" ><![endif]-->
					  <div class="mj-column-px-240 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main110" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Service Date and Time:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="13">
									  <img mc:edit="main111" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main112" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									' . $date . '
									</td>
								  </tr>
								  <tr>
									<td width="13">
									  <img mc:edit="main113" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main114" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									' . $time . '
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:200px;" ><![endif]-->
					  <div class="mj-column-px-200 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main115" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Order Number:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main116" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#787f82;">#' . $bookingId . '
								</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main117" style="font-family:Roboto;font-size:30px;font-weight:bold;line-height:1;text-align:left;color:#787F82;">Invoice</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr style="border-top: 1px solid #e3e3e3; border-bottom: 1px solid #e3e3e3;">
									<td mc:edit="main118" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> Service total cost </td>
									<td mc:edit="main119" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> £' . number_format($total, 2) . '
									</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main120" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> Deposit paid </td>
									<td mc:edit="main121" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> -£' . number_format($deposit, 2) . '
									</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main122" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> ATB Transaction Fees </td>
									<td mc:edit="main123" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> -£' . number_format($atb_fee, 2) . '
									</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main124" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787F82;line-height:40px; font-weight: bold;"> Payment Pending </td>
									<td mc:edit="main125" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787F82;line-height:40px; font-weight: bold;"> £' . number_format($total - $deposit +$atb_fee, 2) . '
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main126" style="font-family:Roboto;font-size:15px;line-height:21px;text-align:left;color:#787F82;">If you cannot fulfill the booking for any reason please get in touch with the client directly to reschedule.
		
		</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#A6BFDE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#A6BFDE;background-color:#A6BFDE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#A6BFDE;background-color:#A6BFDE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						<tbody>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main127" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff; text-decoration: none;">Terms and conditions</a></div>
							</td>
							</tr>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main128" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff; text-decoration: none;">Privacy Policy</a></div>
							</td>
							</tr>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main129" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="mailto:help@myatb.co.uk" style="color: #ffffff; text-decoration: none;">Contact Us</a></div>
							</td>
							</tr>
						</tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main130" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;">ATB All rights reserved</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px;line-height:10px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><![endif]-->
		  </div>
		<script type="text/javascript"  src="/OiORoJkI/HFD/Raf/ax9YTjB6tt/LYN9XQbL/SxBdJRdhAQ/KC/RFUwM-On4"></script></body>
		
		</html> ';

		$this->sendEmail(
			$to,
			$subject,
			$content);
	}

	private function ServiceemailToUser($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit,$atb_fee) {
		$subject = "Booking Confirmed";

		$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<!doctype html>
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
		
		<head>
		  <title>*|MC:SUBJECT|*</title>
		  <!--[if !mso]><!-->
		  <meta http-equiv="X-UA-Compatible" content="IE=edge">
		  <!--<![endif]-->
		  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  
		  <!--[if mso]>
				<noscript>
				<xml>
				<o:OfficeDocumentSettings>
				  <o:AllowPNG/>
				  <o:PixelsPerInch>96</o:PixelsPerInch>
				</o:OfficeDocumentSettings>
				</xml>
				</noscript>
				<![endif]-->
		  <!--[if lte mso 11]>
				<style type="text/css">
				  .mj-outlook-group-fix { width:100% !important; }
				</style>
				<![endif]-->
		  <!--[if !mso]><!-->
		  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css">
		  <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
		  <!--<![endif]-->
		  
		  
		  
		<style type="text/css">
				#outlook a{
					padding:0;
				}
				body{
					margin:0;
					padding:0;
					-webkit-text-size-adjust:100%;
					-ms-text-size-adjust:100%;
				}
				table,td{
					border-collapse:collapse;
					mso-table-lspace:0pt;
					mso-table-rspace:0pt;
				}
				img{
					border:0;
					height:auto;
					line-height:100%;
					outline:none;
					text-decoration:none;
					-ms-interpolation-mode:bicubic;
				}
				p{
					display:block;
					margin:13px 0;
				}
			@media only screen and (min-width:480px){
				.mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-250{
					width:250px !important;
					max-width:250px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-350{
					width:350px !important;
					max-width:350px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-200{
					width:200px !important;
					max-width:200px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-240{
					width:240px !important;
					max-width:240px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
		
		}		.moz-text-html .mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
				.moz-text-html .mj-column-px-250{
					width:250px !important;
					max-width:250px;
				}
				.moz-text-html .mj-column-px-350{
					width:350px !important;
					max-width:350px;
				}
				.moz-text-html .mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
				.moz-text-html .mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
				.moz-text-html .mj-column-px-200{
					width:200px !important;
					max-width:200px;
				}
				.moz-text-html .mj-column-px-240{
					width:240px !important;
					max-width:240px;
				}
				.moz-text-html .mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
				.moz-text-html .mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
			@media only screen and (max-width:480px){
				table.mj-full-width-mobile{
					width:100% !important;
				}
		
		}	@media only screen and (max-width:480px){
				td.mj-full-width-mobile{
					width:auto !important;
				}
		
		}</style></head>
		
		<body style="word-spacing:normal;background-color:#eeeeee;">
		  <div style="background-color:#eeeeee;">
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px;line-height:10px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#ABC1DE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#ABC1DE;background-color:#ABC1DE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ABC1DE;background-color:#ABC1DE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:rtl;font-size:0px;padding:20px 0;padding-bottom:35px;padding-top:35px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
								  <tbody>
									<tr>
									  <td style="width:153px;">
										<img mc:edit="main11" height="auto" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 153px;" width="153">
									  </td>
									</tr>
								  </tbody>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main12" style="font-family:Roboto;font-size:30px;font-weight:bold;line-height:1;text-align:left;color:#FFFFFF;">Hi '.$name.',</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main13" style="font-family:Roboto;font-size:20px;line-height:1;text-align:left;color:#FFFFFF;">Please find below details of your booking</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#F8F8F8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#F8F8F8;background-color:#F8F8F8;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#F8F8F8;background-color:#F8F8F8;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:370px;" ><![endif]-->
					  <div class="mj-column-px-370 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="75">
										<img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%" class="CToWUd" data-bit="iit">
									</td>
									<td width="10"></td>
									<td mc:edit="main15" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:28px;font-weight:normal;">Business name:<br>
		<span style="color:#787f82; font-family:roboto,arial,sans-serif; font-size:15px; font-weight:normal; line-height:28px">'.$username.'</span></td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:270px;" ><![endif]-->
					  <div class="mj-column-px-270 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="20"><img mc:edit="main16" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 20px;"></td>
									<td width="10"></td>
									<td mc:edit="main17" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:28px;font-weight:bold;"> Manage this booking </td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:200px;" ><![endif]-->
					  <div class="mj-column-px-200 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main18" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Service:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main19" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#787f82;">'.$title.'</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:240px;" ><![endif]-->
					  <div class="mj-column-px-240 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main110" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Service Schedule:
		
		</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="13">
									  <img mc:edit="main111" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main112" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									'.$date.'
									</td>
								  </tr>
								  <tr>
									<td width="13">
									  <img mc:edit="main113" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main114" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									'.$time.'
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:200px;" ><![endif]-->
					  <div class="mj-column-px-200 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main115" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Order Number:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main116" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#787f82;">#'.$bookingId.'</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main117" style="font-family:Roboto;font-size:30px;font-weight:bold;line-height:1;text-align:left;color:#787F82;">Invoice</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr style="border-top: 1px solid #e3e3e3; border-bottom: 1px solid #e3e3e3;">
									<td mc:edit="main118" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> Service total cost </td>
									<td mc:edit="main119" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;">£'.number_format($total, 2).'</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main120" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> Deposit paid </td>
									<td mc:edit="main121" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> -£'.number_format($deposit, 2).'
									</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main122" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> ATB Transaction Fees </td>
									<td mc:edit="main123" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> -£'.number_format($atb_fee, 2).'
									</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main124" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787F82;line-height:40px; font-weight: bold;"> Payment Pending </td>
									<td mc:edit="main125" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787F82;line-height:40px; font-weight: bold;"> £'.number_format($total-$deposit+$atb_fee, 2).'
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main126" style="font-family:Roboto;font-size:15px;line-height:21px;text-align:left;color:#787F82;">If you can no longer attend or need to make amendments to the appointment, please get in touch with [business name] at the earliest.</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#A6BFDE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#A6BFDE;background-color:#A6BFDE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#A6BFDE;background-color:#A6BFDE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main127" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff; text-decoration: none;">Terms and conditions</a></div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main128" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff; text-decoration: none;">Privacy Policy</a></div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main129" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="mailto:help@myatb.co.uk" style="color: #ffffff; text-decoration: none;">Contact Us</a></div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main130" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;">ATB All rights reserved</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px;line-height:10px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><![endif]-->
		  </div>
		<script type="text/javascript"  src="/OiORoJkI/HFD/Raf/ax9YTjB6tt/LYN9XQbL/SxBdJRdhAQ/KC/RFUwM-On4"></script></body>
		
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

						date_default_timezone_set('UTC');
						$dateTime = date('jS F g:i A', $bookings[0]['booking_datetime']);

						if ($is_requested_by == 1) {
							$business = $this->UserBusiness_model->getBusinessInfo($verifyTokenResult['id'])[0];

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
				
				$businessUser = $this->User_model->getOnlyUser(array('id' => $booking['business_user_id']))[0];
				$email = $businessUser['user_email'];
				$userName = $businessUser['first_name'].' '. $businessUser['last_name'];
				$this->bookingCompleteEmail($email, $userName);
				
        		$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Booking Complete";
        	
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
	
	private function bookingCompleteEmail($to,$name){
		$subject = "Your service completed";
    
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
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<meta name="vi	ewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE">
		<meta name="format-detection" content="date=no">
		<meta name="format-detection" content="address=no">
		<meta name="format-detection" content="telephone=no">
		<meta name="x-apple-disable-message-reformatting">
		 <!--[if !mso]><!-->
			<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet">
		<!--<![endif]-->
		<title>*|MC:SUBJECT|*</title>
		
		
		
		
		<style type="text/css">
				body{
					padding:0 !important;
					margin:0 !important;
					display:block !important;
					min-width:100% !important;
					width:100% !important;
					background:#F8F8F8;
					-webkit-text-size-adjust:none;
				}
				p{
					padding:0 !important;
					margin:0 !important;
				}
				table{
					border-spacing:0 !important;
					border-collapse:collapse !important;
					table-layout:fixed !important;
				}
				.container{
					width:100%;
					max-width:650px;
				}
				.ExternalClass{
					width:100%;
				}
				.ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div{
					line-height:100%;
				}
			@media screen and (max-width: 650px){
				.wrapper{
					padding:0 !important;
				}
		
		}	@media screen and (max-width: 650px){
				.container{
					width:100% !important;
					min-width:100% !important;
				}
		
		}	@media screen and (max-width: 650px){
				.border{
					display:none !important;
				}
		
		}	@media screen and (max-width: 650px){
				.content{
					padding:0 20px 50px !important;
				}
		
		}	@media screen and (max-width: 650px){
				.box1{
					padding:55px 20px 50px !important;
				}
		
		}	@media screen and (max-width: 650px){
				.social-btn{
					height:35px;
					width:auto;
				}
		
		}	@media screen and (max-width: 650px){
				.bottomNav a{
					font-size:12px !important;
					line-height:16px !important;
				}
		
		}	@media screen and (max-width: 650px){
				.spacer{
					height:61px !important;
				}
		
		}</style></head>
		
		<body style="background-color: #A6BFDE; padding: 0 50px 50px; margin:0">
		<span style="height: 0; width: 0; line-height: 0pt; opacity: 0; display: none;">Booking complete</span>
		
		<table border="0" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
			<tr>
				<td align="center" valign="top" class="wrapper">
					<!--[if (gte mso 9)|(IE)]>
					<table width="650" align="center" cellpadding="0" cellspacing="0" border="0">
						<tr>
						<td>
					<![endif]-->    
					<table border="0" cellspacing="0" cellpadding="0" class="container">
						<tr>
							<td>
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td style="background-color: #A6BFDE;" valign="top" align="center" class="content">
											<!--[if gte mso 9]>
											<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
												<v:fill type="frame" src="images/background.jpg" color="#ABC1DE" />
												<v:textbox inset="0,0,0,0">
											<![endif]-->
		
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td align="center" style="padding: 53px 20px 40px">
															<a href="#" target="_blank"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/dff672e9-5e34-173c-98cf-8965a54661ec.png" width="153" height="47" border="0" alt=""></a>
														</td>
													</tr>
												</table>
		
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td valign="bottom">
															<table width="100%" border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td height="98">
																		<table width="100%" border="0" cellspacing="0" cellpadding="0">
																			<tr><td height="38" style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">&nbsp;</td></tr>
																			<tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt;width:100%; min-width:100%;border-radius:5px 0 0 0;">&nbsp;</td></tr>
																		</table>
																	</td>
																	<td width="98" height="98" bgcolor="#F8F8F8" style="border-radius: 50% 50% 0 0!important;max-height: 98px !important;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/edc0385b-3859-7601-f769-31117879049d.png" width="98" height="98" border="0" alt="" style="border: 0 !important; outline:none; text-decoration: none;display:block;max-height: 98px !important;"></td>
																	<td height="98">
																		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">
																			<tr><td height="38" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;">&nbsp;</td></tr>
																			<tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;border-radius: 0 5px 0 0;">&nbsp;</td></tr>
																		</table>
																	</td>
																</tr>
															</table>
															<table width="100%" border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td class="box1" bgcolor="#F8F8F8" align="center" style="padding:55px 120px 50px;">
																		<table border="0" cellspacing="0" cellpadding="0">
																			<tr>
																				<td><br><h2 mc:edit="sc1" style="margin: 0; color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 300; font-size:20px; line-height:24px; text-align:center;">We hope you enjoyed your service with <strong>'.$name.'</strong>, don’t forget to leave them a review!</h2>																		
																			  <br></td>
																			</tr>
																			<tr>
																			  <td><a href=""><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/02d251f2-c0ff-f4ae-c435-cf1b6a95d62f.png" alt="" style="max-width: 230px; width: 100%; display: block; border: 0; margin: auto;" mc:edit="sc7"></a></td>
																			</tr>
																			<tr><td><br></td></tr>
																			<tr>
																				<td>
																					<p mc:edit="sc2" style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">If you have any questions or concerns, please email (our admin email address whatever that may be)</p>
																					<br>
																					<a mc:edit="sc4" href="hrefdeeplink" target="_blank" style="color: #ffffff; text-decoration: none; font-family: &#39;Roboto&#39;, Arial, sans-serif; font-size: 15px; width: 210px; padding-top: 15px; padding-bottom: 15px; display: block; text-align: center; background: #ABC1DE; border-radius: 5px; margin: auto;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/1bab97f0-f7e7-1f31-89c0-995e52e6aa18.png" width="20" height="20" style="vertical-align: middle ; max-width: 20px;"> <span style="color:#ffffff; text-decoration:none; padding-left: 5px">Review this Business</span></a>
																				</td>
																			</tr>																	
																		</table>
																	</td>
																</tr>
															</table>
															<table bgcolor="#ffffff" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
																<tr>
																	<td height="40"></td>
																  </tr>
																<tr>
																	<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
																		<!--left-->
																		<div style="display:inline-block;vertical-align:top;">
																		  <table align="center" border="0" cellspacing="0" cellpadding="0">
																			<tr>
																			  <td width="200" align="center">
																				<table bgcolor="#FFFFFF" align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
																				  <tr>
																					<td align="center">
																					  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																						<tr>
																						  <td height="10"></td>
																						</tr>
																						<tr>
																							<td align="center" mc:edit="info1"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color:#A2A2A2;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Terms and conditions</a> </td>
																						</tr>
																						<tr>
																						  <td height="10"></td>
																						</tr>
																					  </table>
																					</td>
																				  </tr>
																				</table>
																			  </td>
																			</tr>
																		  </table>
																		</div>
																		<!--end left-->
																		<!--[if (gte mso 9)|(IE)]>
																		</td>
																		<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
																		<![endif]-->
																		<!--middle-->
																		<div style="display:inline-block;vertical-align:top;">
																		  <table align="center" border="0" cellspacing="0" cellpadding="0">
																			<tr>
																			  <td width="200" align="center">
																				<table bgcolor="#FFFFFF" align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
																				  <tr>
																					<td align="center">
																					  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																						<tr>
																						  <td height="10"></td>
																						</tr>
																						<tr>
																							<td align="center" mc:edit="info2"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color:#A2A2A2;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Privacy Policy</a> </td>
																						</tr>
																						<tr>
																						  <td height="10"></td>
																						</tr>
																					  </table>
																					</td>
																				  </tr>
																				</table>
																			  </td>
																			</tr>
																		  </table>
																		</div>
																		<!--end middle-->
																		<!--[if (gte mso 9)|(IE)]>
																		</td>
																		<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
																		<![endif]-->
																		<!--middle-->
																		<!--right-->
																		<div style="display:inline-block;vertical-align:top;">
																		  <table align="center" border="0" cellspacing="0" cellpadding="0">
																			<tr>
																			  <td width="200" align="center">
																				<table bgcolor="#FFFFFF" align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
																				  <tr>
																					<td align="center">
																					  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																						<tr>
																						  <td height="10"></td>
																						</tr>
																						<tr>
																							<td align="center" mc:edit="info3"><a href="mailto:help@myatb.co.uk" style="color:#A2A2A2;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Contact Us</a> </td>
																						</tr>
																						<tr>
																						  <td height="10"></td>
																						</tr>
																					  </table>
																					</td>
																				  </tr>
																				</table>
																			  </td>
																			</tr>
																		  </table>
																		</div>
																		<!--end right-->
																	  </td>
																</tr>
															</table>
															<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="border-radius: 0 0 5px 5px ">
																<tr>
																	<td width="100%" style="padding: 20px 20px 45px;">
																		<table width="100%" border="0" cellspacing="0" cellpadding="0">
																			<tr>
																				<td align="center" mc:edit="info4"><a href="#" style="color:#AEC3DE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:28px; text-align:center; text-decoration: none;">ATB All rights reserved</a> </td>
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
		
		<script type="text/javascript"  src="/o6_vyQJqPbYtaVe-DZ2j-l984oA/5N3Sw4bS/GzM7GGwHGgM/YjMeBA5N/ITo"></script></body>
		</html>
		';
$this->sendEmail(
	$to,
	$subject,
	$content);
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
