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
			';

		$this->sendEmail("honestdeveloper10@gmail.com", $subject, $content);                
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

	public function deeplink() {
		$branch_key = $this->config->item('branch_key');
		$ios_url = $this->config->item('ios_url');
		$android_url = $this->config->item('android_url');
		
		$url = 'https://api.branch.io/v1/url';
		
		$ch = curl_init();

		$payload = array(
			'branch_key'=> $branch_key,
			'campaign' => 'profile', 
			'data' => array(
				'$ios_url' => $ios_url,
				'$android_url' => $android_url,
				"nav_type" => "0",
				'nav_here' => "17")
		);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);  
        
		// send request
        $result = curl_exec($ch); 
		curl_close($ch);

		echo json_decode($result);
	}
}