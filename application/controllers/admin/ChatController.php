<?php

use PubNub\Endpoints\Objects\Channel\GetAllChannelMetadata;
use PubNub\Models\Consumer\Objects\Channel\PNGetAllChannelMetadataResult;
use PubNub\PubNub;
use PubNub\Enums\PNStatusCategory;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\PNConfiguration;
use Lcobucci\JWT\Validation\ConstraintViolation;
use PayPal\Api\Details;
use Symfony\Component\VarDumper\Cloner\Data;
use function Aws\flatmap;
use PubNub\Endpoints\Objects\Membership\GetMemberships;
use PubNub\Endpoints\Objects\Member\SetMembers;

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

    public function getChannelDetail($channel,$chatFlag,$wholeUsers){
        $pubnub = $this->loginPubNub();
        $array = explode("_",$channel);
        $rooms['channel'] = $channel;
        $rooms['last_message'] = "";
        $rooms['timesteapm'] = (int) (microtime(true) );
        $memberArray = array();    

        $member['id'] ="ADMIN_1";//$this->session->userdata('user_id');    
        $member['name'] = "ADMIN";//$this->session->userdata('user_name');   
        $profile_pic = base_url().'admin_assets/logo.png'; //$this->session->userdata('profile_pic');
        if(empty($profile_pic)){
            $profile_pic = base_url()."admin_assets/logo.png";
        } else{
            $profile_pic = base_url()."admin_assets/logo.png";
        }
        $member['imageUrl'] = $profile_pic;    
        $memberArray[0] = $member;   
        $businessAccounts = $this->UserBusiness_model->getBusinessInfos();
        $room_name = "";
        $room_profile = "";
        $room_online = 0;
        for($j = 1 ;$j<count($array);$j++){            
            if(str_contains($array[$j],"#")){
                $subArray = explode("#",$array[$j]);
                $businessID = $subArray[1];
                for($i = 0 ; $i<count( $businessAccounts);$i++){
                    $business = $businessAccounts[$i];
                  
                    if($business['id'] == $businessID){
                        $room_name = $business['business_name'];
                        $room_profile = $business['business_logo'];
                        $member['id'] = "business_".$business['id'];
                        $member['businessName'] = $business['business_name'];
                        $member['businessPicUrl'] = $business['business_logo'];
                        $memberArray[$j] = $member;
                        break;
                    }
                }          

            }else{
                for($i = 0 ; $i<count( $wholeUsers);$i++){
                    $user = $wholeUsers[$i];
                    if($user['id'] == $array[$j]){
                        $room_name =  $user['user_name'];
                        $room_profile = $user['pic_url'];
                        $room_online = $user['online'];
                        $member['id'] = "user_".$user['id'];
                        $member['name'] = $user['user_name'];
                        $member['imageUrl'] = $user['pic_url'];
                        $memberArray[$j] = $member;
                        break;
                    }
                }          
            }
        }  
    
        if(count($array) == 2 ){            
            $rooms['title']= $room_name;
            $rooms['image']=   $room_profile;
            if(abs($room_online - time()) < 1800){
                $rooms['online'] = 0;
            }else{
                $rooms['online'] = -1;
            }          
        }else if(count($array) >2){
            $rooms['title']= "ATB admin(group)";
            $rooms['image']= base_url()."admin_assets/logo.png";
            $rooms['online'] = -1;
        }           
        $custom = array();
        $custom['members'] = json_encode($memberArray);
       
        if ($chatFlag == 1) {
            $pubnub->setChannelMetadata()
                ->channel(urlencode($channel))
                ->meta([
                    "name" => $channel,
                    "metadataID" => $channel,
                    "description" => "description_of_channel",                    
                    "custom" => $custom
                ])
                ->sync(); 

        }        
        $result = $pubnub->history()
        ->channel($channel)
        ->count(1)
        ->sync();
        // $rooms['last_message'] = "";
       
            $pnHistoryResult = $result->getMessages();     
        

            if(count(  $pnHistoryResult)>0){
                try{
                    $messageModel = $pnHistoryResult[0]->getEntry();  
                    if(str_contains(json_encode($messageModel), "text")){                        
                        $rooms['last_message'] = $messageModel['text'];  
                        if($messageModel['messageType'] == "Image" ){
                            $rooms['last_message'] = "Image Sent";
                        }                  
                        $rooms['timesteapm'] =$result->getStartTimetoken()/10000000; 
                    }
                   
                }catch(Exception $e){

                }
            
            }       

        return $rooms;
    }
    public function index() {

        $user_id= 1;//$this->session->userdata('user_id');
        $_filter  = "name LIKE "."'*".$user_id."#"."ADMIN"."*'";
         $pubnub = $this->loginPubNub();
         $result = $pubnub->getAllChannelMetadata()
         ->includeFields([ "totalCount" => true, "customFields" => true ])
        ->filter(  urlencode($_filter))
         ->sync();
         $channelMetadata = $result-> getData();
         $rooms = array();
         $count = 0;
         $wholeUsers = $this->User_model->getUsersListInDashboard();
         
         for($i =0;$i<count($channelMetadata);++$i ){
            $channel = $channelMetadata[$i]->getId();
            if(str_contains($channel,".json"))continue;
            if (str_contains($channel, $user_id."#ADMIN")) {
                $rooms[$count] = $this-> getChannelDetail($channel, 0, $wholeUsers );
                // print_r($channelMetadata[$i]);
                // exit;
                $count ++;
            }
         } 
        $this->session->set_userdata('chatRooms',$rooms);
        $this->session->set_userdata('pubnub', $pubnub);

        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);        
        $dataToBeDisplayed['whole_users'] = count($wholeUsers);
        $dataToBeDisplayed['rooms'] =  $rooms;        
        $this->load->view('admin/chat/chat_list', $dataToBeDisplayed);
    }

   
    public function detail($channel) {     
        $channel = urldecode($channel);
        $wholeUsers = $this->User_model->getUsersListInDashboard();
        $user_id=1;  //$this->session->userdata('user_id');    
        if (!str_contains($channel, $user_id.'#ADMIN')) {    
            $channel = $user_id."#ADMIN_".$channel;      
        }    
        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);
       // $booking = $this->Booking_model->getBooking($bookingid);
        $rooms = $this->getChannelDetail($channel,1,$wholeUsers);

        // print_r(json_encode($rooms));
        // exit;
        $dataToBeDisplayed['rooms'] = $rooms;
        $this->load->view('admin/chat/chat', $dataToBeDisplayed);
    }
    public function newchat() {
        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);

      $dataToBeDisplayed['users'] = $this->User_model->getUsersListInDashboard();

        $this->load->view('admin/chat/new_chat', $dataToBeDisplayed);
    }

    public function sendmessage() {

        
        $data['status']="1";

        $data['message']="Aboutus Update Successfully";
        
        header( 'Content-type:application/json');

        print json_encode( $data);

        exit;		
	}

    public function makeGroup(){   

        if (!isset($_POST["selectGroup"])){
            $this->newchat();
            return;
        }
        $array = $_POST['selectGroup'];
 
        $channelID = $array[0];
        for($i = 1; $i<count($array);$i++){
            $channelID = $channelID . "_" . $array[$i];
        }       
    
        $this->detail( $channelID);        

    }

}