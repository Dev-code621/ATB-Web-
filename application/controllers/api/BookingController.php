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

        $content = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
			background:#A6BFDE;
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
	@media (max-width: 650px){
		.wrapper{
			padding:0 !important;
		}

}	@media (max-width: 650px){
		.container{
			width:100% !important;
			min-width:100% !important;
		}

}	@media (max-width: 650px){
		.content{
			padding-left:30px !important;
			padding-right:30px !important;
		}

}	@media (max-width: 650px){
		.social-btn{
			height:35px;
			width:auto;
		}

}	@media (max-width: 650px){
		.p100{
			display:block !important;
			width:100% !important;
			min-width:100%;
			padding-bottom:10px !important;
			padding-left:0px !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.p100 table{
			width:100% !important;
		}

}	@media (max-width: 650px){
		.mleft{
			float:left !important;
		}

}	@media (max-width: 650px){
		.mfont{
			font-size:20px !important;
		}

}	@media (max-width: 650px){
		.mfont2{
			font-size:16px !important;
		}

}	@media (max-width: 650px){
		.t100{
			width:50% !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.t100 tr td{
			display:block !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.black{
			color:#000000 !important;
		}

}	@media (max-width: 650px){
		.mnone{
			display:none !important;
		}

}	@media (max-width: 650px){
		.colim{
			width:100% !important;
		}

}	@media (max-width: 650px){
		.colim2{
			width:100% !important;
			padding-left:20px !important;
			padding-right:20px !important;
		}

}</style></head>

<body style="padding:0; margin:0; background: #A6BFDE;">

<table border="0" bgcolor="#A6BFDE" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
    <tr>
        <td align="center" valign="top" style="padding: 80px 0;" class="wrapper">
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
								<td style="padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
									<!--[if gte mso 9]>
									<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
										<v:fill type="frame" src="http://sg-lab.co/dev/atb/email/welcome/images/background.jpg" color="#ABC1DE" />
										<v:textbox inset="0,0,0,0">
									<![endif]-->
										
										<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
											<tr>
											  <td align="center">
												<table dir="rtl" align="center" border="0" cellpadding="0" cellspacing="0">
												  <tr>
													<td height="35"></td>
												  </tr>
												  <tr>
													<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
													  <!--left-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="160" align="center">
															  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																<!--image-->
																<tr>
																  <td align="center" style="line-height:0px;"><img style="display:block; line-height:0px; font-size:0px; border:0px;" class="img1" width="150" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" alt="img"></td>
																</tr>
																<!--end image-->
																<tr>
																  <td height="20"></td>
																</tr>
															  </table>
															</td>
														  </tr>
														</table>
													  </div>
													  <!--end left-->
													  <!--[if (gte mso 9)|(IE)]>
							</td>
							<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
							<![endif]-->
													  <!--right-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="414" align="center">
															  <table dir="ltr" width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																<!--title-->
																<tr>
																  <td style="font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:30px; color:#ffffff;font-weight:bold;" mc:edit="ss1">Hi '.$name.',</td>
																</tr>
																<!--end title-->
																<tr>
																  <td height="10"></td>
																</tr>
																<!--content-->
																<tr>
																  <td style="font-family:Open sans, Arial, Sans-serif; font-size:20px; color:#ffffff;line-height:28px;" mc:edit="s2">You Have A New Booking!</td>
																</tr>
																<!--end content-->
																<tr>
																  <td height="35"></td>
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
											  </td>
											</tr>
										</table>

										<table bgcolor="#F8F8F8" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
											<tr>
											  <td align="center">
												<table align="center" border="0" cellpadding="0" cellspacing="0">
												  <tr>
													<td height="20"></td>
												  </tr>
												  <tr>
													<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
													  <!--left-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="370" align="center" class="colim2">
															  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																<tr>
																	<td style="border-radius: 50%;" width="73" valign="baseline">
																		<img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%;" mc:edit="s3">
																	</td>
																	<td style="padding-left: 10px;">
																		<table width="100%" border="0" cellpadding="0" cellspacing="0">
																			<tr><td style="color:#454b4d; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:15px; line-height:20px;">Client name:</td></tr>
																			<tr><td style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: normal;font-size: 15px;line-height: 16px;text-align: left;color: #787f82;" mc:edit="s4">'.$username.'</td></tr>
																		</table>
																	</td>
																</tr>
																<tr>
																  <td height="20"></td>
																</tr>
															  </table>
															</td>
														  </tr>
														</table>
													  </div>
													  <!--end left-->
													  <!--[if (gte mso 9)|(IE)]>
							</td>
							<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
							<![endif]-->
													  <!--right-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="204" align="center" class="colim2">
															  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">

                                                                <tr>
                                                                    <td height="10"></td>
                                                                  </tr>
                                                                <tr><td mc:edit="s51"><a href="hrefdeeplink" style="color:#535353;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700;display: block;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" height="19" alt="Message icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;"> Manage this booking</a> </td></tr>
																<tr>
																  <td height="20"></td>
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
											  </td>
											</tr>
										  </table>

										<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3;padding: 10px 40px 80px;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;">
											<tr>
												<td>
													<table bgcolor="#ffffff" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
														<tr>
														  <td align="center" style="text-align:center;vertical-align:top;font-size:0; padding: 10px 20px 10px" bgcolor="#ffffff" class="content">
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="200" align="center" class="colim">
																	<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="container">
																		<tr>
																			<td height="10"></td>
																		</tr>
																		<tr>
																			<td>
																				<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service:</p>
																				<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s6">'.$title.'</p>
																			</td>
																		</tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
															<!--[if (gte mso 9)|(IE)]>
															</td>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															<![endif]-->
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="240" align="center" class="colim">
																	<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td height="10"></td>
																		</tr>
                                                                        <tr>
                                                                            <td>
                                                                                  <p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service Schedule:</p>
                                                                                  <p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s8">
                                                                                      <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$date.'</span> &nbsp;
                                                                                      <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$time.'</span></p>
                                                                              </td>
                                                                          </tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
															<!--[if (gte mso 9)|(IE)]>
															</td>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															<![endif]-->
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="120" align="center" class="colim">
																	<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td height="10"></td>
																		</tr>
                                                                        <tr>
																			<td>
																				<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Order Number:</p>
																				<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s9">#'.$bookingId.'</p>
																		  </td>
																		</tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
														  </td>
														</tr>
													  </table>

													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3; padding: 10px 40px 80px" bgcolor="#ffffff" class="content">
																<table width="100%" border="0" cellspacing="0" cellpadding="0">
																	<tr>
																		<td style="padding-top: 20px;">
																			<table width="100%" border="0" cellspacing="0" cellpadding="0">
																				<tr>
																					<td style="color: #787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:50px">Invoice</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Service total cost</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s10">&pound;'.number_format($total, 2).'</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Deposit paid</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s11">-&pound;'.number_format($deposit, 2).'</td>
																				</tr>
                                                                                	<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">ATB Transaction Fees</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s111">-&pound;10.00</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" class="mfont2" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Payment Pending</td>
																					<td align="right" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;" mc:edit="s12">&pound;'.number_format($total-$deposit, 2).'</td>
																				</tr>
																			</table>
																		</td>
																	</tr>
																	<tr>
																	  <td>
																			<br>
																			<hr>
																		  <p style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px;font-weight: normal;font-size: 15px;line-height: 18px;text-align: left;color: #787f82;" mc:edit="s1">If you cannot fulfill the booking for any reason please get in touch with the client directly to reschedule.

                                                                          </p>
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
																	<tr><td align="left" mc:edit="info1"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Terms and conditions</a></td></tr>
																	<tr><td align="left" mc:edit="info2"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;" class="black">Privacy Policy</a> </td></tr>
																	<tr><td align="left" mc:edit="info3"><a href="mailto:help@myatb.co.uk" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Contact Us</a> </td></tr>
																</table>
															</td>
															<td class="p100" align="right" width="400">
																<table border="0" cellspacing="0" cellpadding="0">
																</table>
																<table border="0" cellspacing="0" cellpadding="0">
																	<tr>
																		<td mc:edit="info4" align="right" class="p100" style="padding-top: 10px"><a href="#" class="mleft" style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:right; text-decoration: none !important;display: block !important;">ATB All rights reserved</a> </td>
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

	private function emailToUser($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit) {
		$subject = "Booking Confirmed";

		$content = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
					background:#A6BFDE;
					-webkit-text-size-adjust:none;
				}
				a{
					color:#A6BFDE;
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
			@media (max-width: 650px){
				.wrapper{
					padding:0 !important;
				}
		
		}	@media (max-width: 650px){
				.container{
					width:100% !important;
					min-width:100% !important;
				}
		
		}	@media (max-width: 650px){
				.content{
					padding-left:30px !important;
					padding-right:30px !important;
				}
		
		}	@media (max-width: 650px){
				.social-btn{
					height:35px;
					width:auto;
				}
		
		}	@media (max-width: 650px){
				.p100{
					display:block !important;
					width:100% !important;
					min-width:100%;
					padding-bottom:10px !important;
					padding-left:0px !important;
					float:left !important;
				}
		
		}	@media (max-width: 650px){
				.p100 table{
					width:100% !important;
				}
		
		}	@media (max-width: 650px){
				.mleft{
					float:left !important;
				}
		
		}	@media (max-width: 650px){
				.mfont{
					font-size:20px !important;
				}
		
		}	@media (max-width: 650px){
				.mfont2{
					font-size:16px !important;
				}
		
		}	@media (max-width: 650px){
				.t100{
					width:50% !important;
					float:left !important;
				}
		
		}	@media (max-width: 650px){
				.t100 tr td{
					display:block !important;
					float:left !important;
				}
		
		}	@media (max-width: 650px){
				.black{
					color:#000000 !important;
				}
		
		}	@media (max-width: 650px){
				.mnone{
					display:none !important;
				}
		
		}	@media (max-width: 650px){
				.colim{
					width:100% !important;
				}
		
		}	@media (max-width: 650px){
				.colim2{
					width:100% !important;
					padding-left:20px !important;
					padding-right:20px !important;
				}
		
		}</style></head>
		
		<body style="padding:0; margin:0; background: #A6BFDE;">
		
		<table border="0" bgcolor="#A6BFDE" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
			<tr>
				<td align="center" valign="top" style="padding: 80px 0;" class="wrapper">
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
										<td style="padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
											<!--[if gte mso 9]>
											<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
												<v:fill type="frame" src="http://sg-lab.co/dev/atb/email/welcome/images/background.jpg" color="#ABC1DE" />
												<v:textbox inset="0,0,0,0">
											<![endif]-->
												
												<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
													<tr>
													  <td align="center">
														<table dir="rtl" align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td height="35"></td>
														  </tr>
														  <tr>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															  <!--left-->
															  <div style="display:inline-block; vertical-align:middle;">
																<table align="center" border="0" cellpadding="0" cellspacing="0">
																  <tr>
																	<td width="160" align="center">
																	  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																		<!--image-->
																		<tr>
																		  <td align="center" style="line-height:0px;"><img style="display:block; line-height:0px; font-size:0px; border:0px;" class="img1" width="150" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" alt="img"></td>
																		</tr>
																		<!--end image-->
																		<tr>
																		  <td height="20"></td>
																		</tr>
																	  </table>
																	</td>
																  </tr>
																</table>
															  </div>
															  <!--end left-->
															  <!--[if (gte mso 9)|(IE)]>
									</td>
									<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
									<![endif]-->
															  <!--right-->
															  <div style="display:inline-block; vertical-align:middle;">
																<table align="center" border="0" cellpadding="0" cellspacing="0">
																  <tr>
																	<td width="414" align="center">
																	  <table dir="ltr" width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																		<!--title-->
																		<tr>
																		  <td style="font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:30px; color:#ffffff;font-weight:bold;" mc:edit="ss1">Hi '.$username.',</td>
																		</tr>
																		<!--end title-->
																		<tr>
																		  <td height="10"></td>
																		</tr>
																		<!--content-->
																		<tr>
																		  <td style="font-family:Open sans, Arial, Sans-serif; font-size:20px; color:#ffffff;line-height:28px;" mc:edit="s2">Please find below details of your booking:                                                                </td>
																		</tr>
																		<!--end content-->
																		<tr>
																		  <td height="35"></td>
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
													  </td>
													</tr>
												</table>
		
												<table bgcolor="#F8F8F8" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
													<tr>
													  <td align="center">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td height="20"></td>
														  </tr>
														  <tr>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															  <!--left-->
															  <div style="display:inline-block; vertical-align:middle;">
																<table align="center" border="0" cellpadding="0" cellspacing="0">
																  <tr>
																	<td width="370" align="center" class="colim2">
																	  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td style="border-radius: 50%;" width="73" valign="baseline">
																				<img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/585a4a63-04d4-7d34-22fc-c719ad30a7d8.png" width="73" height="73" border="0" alt="user icon" style="border-radius:100%;" mc:edit="s3">
																			</td>
																			<td style="padding-left: 10px;">
																				<table width="100%" border="0" cellpadding="0" cellspacing="0">
																					<tr><td style="color:#454b4d; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:15px; line-height:20px;">Business name:</td></tr>
																					<tr><td style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: normal;font-size: 15px;line-height: 16px;text-align: left;color: #787f82;" mc:edit="s4">'.$name.'</td></tr>
																				</table>
																			</td>
																		</tr>
																		<tr>
																		  <td height="20"></td>
																		</tr>
																	  </table>
																	</td>
																  </tr>
																</table>
															  </div>
															  <!--end left-->
															  <!--[if (gte mso 9)|(IE)]>
									</td>
									<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
									<![endif]-->
															  <!--right-->
															  <div style="display:inline-block; vertical-align:middle;">
																<table align="center" border="0" cellpadding="0" cellspacing="0">
																  <tr>
																	<td width="204" align="center" class="colim2">
																	  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr><td mc:edit="s51"><a href="hrefdeeplink" style="color:#535353;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700;display: block;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" height="19" alt="Message icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;"> Manage this booking</a> </td></tr>
																		<tr>
																		  <td height="20"></td>
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
													  </td>
													</tr>
												  </table>
		
												<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3;padding: 10px 40px 80px;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;">
													<tr>
														<td>
															<table bgcolor="#ffffff" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td align="center" style="text-align:center;vertical-align:top;font-size:0; padding: 10px 20px 10px" bgcolor="#ffffff" class="content">
																	<div style="display:inline-block;vertical-align:top;">
																	  <table align="center" border="0" cellpadding="0" cellspacing="0">
																		<tr>
																		  <td width="200" align="center" class="colim">
																			<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="container">
																				<tr>
																					<td height="10"></td>
																				</tr>
																				<tr>
																					<td>
																						<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service:</p>
																						<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s6">'.$title.'</p>
																					</td>
																				</tr>
																				<tr>
																					<td height="10"></td>
																				</tr>
																			</table>
																		  </td>
																		</tr>
																	  </table>
																	</div>
																	<!--[if (gte mso 9)|(IE)]>
																	</td>
																	<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
																	<![endif]-->
																	<div style="display:inline-block;vertical-align:top;">
																	  <table align="center" border="0" cellpadding="0" cellspacing="0">
																		<tr>
																		  <td width="240" align="center" class="colim">
																			<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																				<tr>
																					<td height="10"></td>
																				</tr>
																				<tr>
																					<td>
																						  <p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service Schedule:</p>
																						  <p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s8">
																							  <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$date.' </span> &nbsp;
																							  <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$time.'</span></p>
																					  </td>
																				  </tr>
																				<tr>
																					<td height="10"></td>
																				</tr>
																			</table>
																		  </td>
																		</tr>
																	  </table>
																	</div>
																	<!--[if (gte mso 9)|(IE)]>
																	</td>
																	<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
																	<![endif]-->
																	<div style="display:inline-block;vertical-align:top;">
																	  <table align="center" border="0" cellpadding="0" cellspacing="0">
																		<tr>
																		  <td width="120" align="center" class="colim">
																			<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																				<tr>
																					<td height="10"></td>
																				</tr>
																				<tr>
																					<td>
																						<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Order Number:</p>
																						<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s9">#'.$bookingId.'</p>
																				  </td>
																				</tr>
																				<tr>
																					<td height="10"></td>
																				</tr>
																			</table>
																		  </td>
																		</tr>
																	  </table>
																	</div>
																  </td>
																</tr>
															  </table>
		
															<table width="100%" border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3; padding: 10px 40px 80px" bgcolor="#ffffff" class="content">
																		<table width="100%" border="0" cellspacing="0" cellpadding="0">
																			<tr>
																				<td style="padding-top: 20px;">
																					<table width="100%" border="0" cellspacing="0" cellpadding="0">
																						<tr>
																							<td style="color: #787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:50px">Invoice</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Service total cost</td>
																							<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s10">&pound;'.number_format($total, 2).'</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Deposit paid</td>
																							<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s11">-&pound;'.number_format($deposit, 2).'</td>
																						</tr>
																							<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">ATB Transaction Fees</td>
																							<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s111">-&pound;10.00</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" class="mfont2" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Payment Pending</td>
																							<td align="right" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;" mc:edit="s12">&pound;'.number_format($total-$deposit, 2).'</td>
																						</tr>
																					</table>
																				</td>
																			</tr>
																			<tr>
																			  <td>
																					<br>
																					<hr>
																				  <p style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px;font-weight: normal;font-size: 15px;line-height: 18px;text-align: left;color: #787f82;" mc:edit="s1">If you can no longer attend or need to make amendments to the appointment, please get in touch with [business name] at the earliest.
		
																				  </p>
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
																			<tr><td align="left" mc:edit="info1"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Terms and conditions</a></td></tr>
																			<tr><td align="left" mc:edit="info2"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;" class="black">Privacy Policy</a> </td></tr>
																			<tr><td align="left" mc:edit="info3"><a href="mailto:help@myatb.co.uk" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Contact Us</a> </td></tr>
																		</table>
																	</td>
																	<td class="p100" align="right" width="400">
																		<table border="0" cellspacing="0" cellpadding="0">
																		</table>
																		<table border="0" cellspacing="0" cellpadding="0">
																			<tr>
																				<td mc:edit="info4" align="right" class="p100" style="padding-top: 10px"><a href="#" class="mleft" style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:right; text-decoration: none !important;display: block !important;">ATB All rights reserved</a> </td>
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
		<span style="height: 0; width: 0; line-height: 0pt; opacity: 0; display: none;">This is where you write what it&#39;ll show on the clients email listing. If not, it&#39;ll take the first text of the email.</span>
		
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
