<?php

use PubNub\Endpoints\Objects\Channel\GetAllChannelMetadata;
use PubNub\Models\Consumer\Objects\Channel\PNGetAllChannelMetadataResult;
use PubNub\PubNub;
use PubNub\Enums\PNStatusCategory;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\PNConfiguration;
class ChatController extends MY_Controller
{
    const NOTIFICATION_LIST = 0;
    const PAGE_DETAIL = 1;
    private function makeComponentLayout($type) {
        $header_include_css = array();
        if($type == self::NOTIFICATION_LIST) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
                base_url().'admin_assets/global/plugins/jquery-star-rating/star-rating-svg.css');
        }
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Chats');
        $header_layout = $this->load->view('admin/common_template/header_layout', $header_layout_data, TRUE);;

	$notificationCounter = $this->AdminNotification_model->getAdminNotification(array('read_status' => 0));
        $open_reports = $this->PostReport_model->getReports(array("is_active" => 0));

        $sidebar_menu_item = array(
            'selected_item' => MENU_SIGNUPS,
            'notifications_count' => count($notificationCounter),
            'reported_count' => count($open_reports)
                );
        $sidebar_layout = $this->load->view('admin/common_template/sidebar_layout', $sidebar_menu_item, TRUE);

        $footer_app_after_js = array();
        $footer_app_before_js = array();
        if($type == self::NOTIFICATION_LIST) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
                base_url().'admin_assets/global/plugins/jquery-star-rating/jquery.star-rating-svg.js');
        }
        
        $footer_layout_data = array('before_app_js' => $footer_app_before_js, 'after_app_js' => $footer_app_after_js);
        $footer_layout = $this->load->view('admin/common_template/footer_layout', $footer_layout_data, TRUE);

        $dataToBeDisplayed['header_layout'] = $header_layout;
        $dataToBeDisplayed['sidebar_layout'] = $sidebar_layout;
        $dataToBeDisplayed['footer_layout'] = $footer_layout;
        return $dataToBeDisplayed;
    }

    public function getChannelDetail($channel,$chatFlag){
        $pubnub = $this->loginPubNub();
        $array = explode("_",$channel);
        $rooms['channel'] = $channel;
        $rooms['last_message'] = "This is last message";
        $rooms['timestepm'] = "Apr 14";
        $rooms['online'] = 1;
        if(count($array) == 2 ){
            $rooms['title']= "ATB admin";
            $rooms['image']= base_url()."admin_assets/logo.png";

        }else if(count($array) >2){
            $rooms['title']= "ATB admin(group";
            $rooms['image']= base_url()."admin_assets/logo.png";
            $rooms['online'] = -1;
        }           
        if ($chatFlag == 1) {
            $flag = false;
            $chatroom= $this->session->userdata('chatRooms');
            for($i = 0 ; $i<count( $chatroom);$i++){
                $room = $chatroom[$i];
                if($room['channel'] == $channel){
                    $flag = true;
                    break;
                }
            }           
            if ($flag) {
               
            } else {   
                $pubnub->setChannelMetadata()
                ->channel($channel)
                ->meta([
                    "name" => $channel,
                    "description" => "description_of_channel",
                    "custom" =>$rooms
                ])
                ->sync();  
                // $members = array();     
                // for($i = 1 ; $i<count( $array);$i++){
                //     $members[$i] = "user_".$array[$i];
                // } 
                // $pubnub->setMembers()
                // ->channel($channel)
                // ->uuids( $members)               
                // ->sync();
            }
        }
        return $rooms;
    }
    public function index() {
         $pubnub = $this->loginPubNub();
         $result = $pubnub->getAllChannelMetadata()
         ->includeFields([ "totalCount" => true, "customFields" => true ])
         ->sync();
         $channelMetadata = $result-> getData();
         $rooms = array();
         $count = 0;
         for($i =0;$i<count($channelMetadata);++$i ){
            $channel = $channelMetadata[$i]->getId();
            if (str_contains($channel, "ADMIN_")) {
                
                $rooms[$count] = $this-> getChannelDetail($channel, 0);
                $count ++;
            }
        } 
        $this->session->set_userdata('chatRooms',$rooms);
        $this->session->set_userdata('pubnub', $pubnub);

        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);        
        $wholeUsers = $this->User_model->getOnlyUser(array('status' => 3));
        $dataToBeDisplayed['whole_users'] = count($wholeUsers);
        $dataToBeDisplayed['rooms'] =  $rooms;
        $this->load->view('admin/chat/chat_list', $dataToBeDisplayed);
    }

   
    public function detail($channel) {
    
        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);
       // $booking = $this->Booking_model->getBooking($bookingid);
        $rooms = $this->getChannelDetail($channel,1);
        $dataToBeDisplayed['rooms'] = $rooms;
        $this->load->view('admin/chat/chat', $dataToBeDisplayed);
    }
    public function newchat() {
        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);
       // $booking = $this->Booking_model->getBooking($bookingid);

      //  $dataToBeDisplayed['booking'] = $booking;
      $dataToBeDisplayed['users'] = $this->User_model->getUsersListInDashboard();

        $this->load->view('admin/chat/new_chat', $dataToBeDisplayed);
    }

    public function sendmessage() {

        
        $data['status']="1";

        $data['message']="Aboutus Update Successfully";
        
        header( 'Content-type:application/json');

        print json_encode( $data);

        exit;
		// $this->load->model('Admin_model');
		// echo "console.log('aaaaaaa')";
		// $email = strtolower($this->input->post('email'));
		// $retVal = array();
		// $existUser = $this->Admin_model->getAdmin(array('email' => $email));
		// if(count($existUser) == 0) {
		// 	redirect(route('admin.auth.forgot_pass'));
		// }
		// else{
		// 	$subject = 'Admin Password was Reset';
		// 	$content = '<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">Plase use this new password</span></p>
		// 	<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$this->input->get('approveReason').'</span></p>';
		// 	$this->User_model->sendUserEmail($email, $subject, $content);						
		// 	redirect(route('admin.auth.login'));

		// }
	}

}