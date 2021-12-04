<?php
require_once(APPPATH.'libraries/ApnsPHP/Autoload.php');

class CronController extends MY_Controller
{

	public function __construct() {
		parent::__construct();
		// $this->load->library('firebase');
	}

	public function push_upgrade_business() {
		// $userid = 1;
		// echo phpinfo();
		// exit(1);
		// $firebase = $this->firebase->init();
		// $db = $firebase->createDatabase();

		// $reference =  $db->getReference('ATB/Admin/busienss/'.$userid);

		// $reference->remove();
		// $reference->push([
		// 		'is_business' => true,
		// 		'time' => time()*1000
		// 		]);
	}
		
	public function email_test() {
		$subject = 'Welcome to ATB';

		// $content = '<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$this->input->post('first_name').'! you have been successfully registered in</span></p>
		// 			<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">our ATB social network!</span></p>';

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
																<td style="padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
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
																										<td style="color:#575757;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:25px;padding-bottom:20px;">#97865746</td>
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
																																	<img src="./booking confirmed_files/user.png" width="73" height="73" border="0" alt="user icon" style="border-radius:100%;">
																																</td>
																																<td style="padding-left: 10px;">
																																	<table border="0" cellspacing="0" cellpadding="0">
																																		<tbody><tr><td style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:27px; line-height:31px;">Magic Office</td></tr>
																																		<tr><td><a href="#" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none !important;">magicoffice.co.uk</a> </td></tr>																																		
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
																														<p style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Computer Repair</p>
																													</td>
																													<td class="p100" width="220">
																														<p style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service Schedule:</p>
																														<p style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">
																															<span><img src="'.base_url().'assets/email/booking/calendar.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> 22 August</span> &nbsp;
																															<span><img src="'.base_url().'assets/email/booking/clock.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> 8:30 AM</span></p>
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
																													<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;">£22.00</td>
																												</tr>
																												<tr style="border-top: 1px solid #E3E3E3;">
																													<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Deposit</td>
																													<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;">-£10.00</td>
																												</tr>
																												<tr style="border-top: 1px solid #E3E3E3;">
																													<td align="left" class="mfont2" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Payment Pending</td>
																													<td align="right" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">£12.00</td>
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

		$this->sendEmail("elitesolution1031@gmail.com", $subject, $content);                
	}

	public function run_notification() {
		$notifications = $this->NotificationHistory_model->getNotificationHistory(array('send_status' => 0));

		for ($i = 0; $i < count($notifications); $i ++) {
			$user = $this->User_model->getOnlyUser(array('id' => $notifications[$i]['user_id']));

			if (count($user) > 0) {
				$token = $user[0]['push_token'];
				if ($token == "") {
					continue;
				}

				$message = $notifications[$i]['name'] . $notifications[$i]['text'];

				$payload = array(
					'type' => $notifications[$i]['type'], 
					'related_id' => $notifications[$i]['related_id']
				);

				$this->sendPush($token, $message, $payload);

				$this->NotificationHistory_model->updateNotificationHistory(array('send_status' => 1), array('id' => $notifications[$i]['id']));
			}
		}		
	}

	function sendPush($token, $message, $payload) {
		$url = $this->config->item('fcm_send_url');
		$api_key = $this->config->item('fcm_api_key');

		$notification = array(
			'body' => $message,
			'title' => "ATB",
			'badge' => 1,
			'sound' => 'default'
		);

		$fields = array(
			'to' => $token, 
			'notification' => $notification, 
			'priority' => 'high',
			'data' => $payload
		);

		$headers = array(
            'Authorization: key=' . $api_key,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);  
        
        $result['result'] = curl_exec($ch); 

        curl_close($ch); 
	}
	
	/*
	public function run_notification() {
	
	    $USER_SIGNUP = "USER_SIGNUP";
	    $REPORT = "REPORT";
	    $COMMENT = "COMMENT";
	    $RATING = "RATING";
	    $BOOKING = "BOOKING";
	    $MESSAGE = "MESSAGE";
	    $PAYMENT = "PAYMENT";
	    $LIKED = "LIKED";
	    $POST = "POST";
	    $RATING_REQUEST = "RATING_REQUEST";
	    $PAYMENT_REQUEST = "PAYMENT_REQUEST";

		$push = new ApnsPHP_Push(
			ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
			APPPATH.'libraries/atb-live.pem'
		);
		$push->setRootCertificationAuthority(APPPATH.'libraries/entrust_root_certification_authority.pem');
		$push->connect();
		
		$notifications = $this->NotificationHistory_model->getNotificationHistory(array('send_status' => 0));
		
		for($i = 0 ; $i < count($notifications); $i++) {
            $user = $this->User_model->getOnlyUser(array('id' => $notifications[$i]['user_id']));
			
			$message = new ApnsPHP_Message($user[0]['push_token']);
			$message->setCustomIdentifier("atb-message");
			$message->setText($notifications[$i]['text']);
			$message->setSound();
			$message->setExpiry(30);
			$message->setBadge(1);
			$message->setCustomProperty('notification_id', $notifications[$i]["id"]);
			
			$cat = "";
			switch ($notifications[$i]['type']) {
			    case 0:
			        $cat = $USER_SIGNUP;
			        break;
			    case 1:
			        $cat = $REPORT;
			        break;
			    case 2:
			        $cat = $COMMENT;
			        break;
				case 3:
				    $cat = $RATING;
				    break;
				case 4:
					$cat = $BOOKING;
					break;
				case 5:
					$cat = $MESSAGE;
					break;
				case 6:
					$cat = $PAYMENT;
					break;
				case 7:
					$cat = $LIKED;
					break;						
				case 8:
					$cat = $POST;
					break;
				case 9:
					$cat = $RATING_REQUEST;
					break;
				case 10:
					$cat = $PAYMENT_REQUEST;
					break;
			}
			
			$message->setCategory($cat);
			
			$push->add($message);
			
			$this->NotificationHistory_model->updateNotificationHistory(array('send_status' => 1), array('id' => $notifications[$i]['id']));			
        }
		
		if (count($notifications) > 0){
			$push->send();
		}

		$push->disconnect();
	}
	*/
	
	public function post_scheduled_posts() {
		
		$postContent = $this->Post_model->getPostInfo(array('posts.is_active' => 5), "");
		
		foreach ($postContent as $post) {
			if ($post["scheduled"] <= time()) {
				$this->Post_model->updatePostContent(
				array(
						'is_active' => 1,
						'updated_at' => $post["scheduled"],
						'created_at' => $post["scheduled"]
					),
				array('id' => $post['id'])
			);
			} 
		}
	}
}