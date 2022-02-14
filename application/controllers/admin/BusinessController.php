<?php

use SebastianBergmann\Environment\Console;

use function PHPSTORM_META\type;

class BusinessController extends MY_Controller {
    const BUSINESS_LIST = 0;
    const PAGE_DETAIL = 1;
    const PAGE_BLOCK = 2;

    public function __construct() {
        parent::__construct();
        $this->load->library('firebase');
    }

    private function makeComponentLayout($type) {
        $header_include_css = array();
        if($type == self::BUSINESS_LIST) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        }
        else if($type == self::PAGE_DETAIL) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css');
        }
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Reported Posts');
        $header_layout = $this->load->view('admin/common_template/header_layout', $header_layout_data, TRUE);
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
        if($type == self::BUSINESS_LIST) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');

        }
        else if($type == self::PAGE_DETAIL) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
                base_url().'admin_assets/global/plugins/moment.min.js',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js',
                base_url().'admin_assets/global/plugins/flot/jquery.flot.min.js'
            );

        }

        $footer_layout_data = array('before_app_js' => $footer_app_before_js, 'after_app_js' => $footer_app_after_js);
        $footer_layout = $this->load->view('admin/common_template/footer_layout', $footer_layout_data, TRUE);

        $dataToBeDisplayed['header_layout'] = $header_layout;
        $dataToBeDisplayed['sidebar_layout'] = $sidebar_layout;
        $dataToBeDisplayed['footer_layout'] = $footer_layout;
        return $dataToBeDisplayed;
    }

    public function index() {
        $dataToBeDisplayed = $this->makeComponentLayout(self::BUSINESS_LIST);

        $open_reports = $this->UserBusiness_model->getBusinessInfos(array("approved" => 0));

        for($i = 0 ; $i < count($open_reports); $i++) {
			$open_reports[$i]['type'] = "business";
            $open_reports[$i]['user'] = $this->User_model->getUserProfileDTO($open_reports[$i]['user_id']);
            $open_reports[$i]['services'] = $this->UserService_model->getServiceInfoList($open_reports[$i]['user_id']);
        }

        $close_reports =  $this->UserBusiness_model->getBusinessInfos(array("approved" => 1));

        for($i = 0 ; $i < count($close_reports); $i++) {
            $close_reports[$i]['user'] = $this->User_model->getUserProfileDTO($close_reports[$i]['user_id']);
            $close_reports[$i]['services'] = $this->UserService_model->getServiceInfoList($close_reports[$i]['user_id']);
        }

        $ignored_reports = $this->UserBusiness_model->getBusinessInfos(array("approved" => 2));

        for($i = 0 ; $i < count($ignored_reports); $i++) {
            $ignored_reports[$i]['user'] = $this->User_model->getUserProfileDTO($ignored_reports[$i]['user_id']);
            $ignored_reports[$i]['services'] = $this->UserService_model->getServiceInfoList($ignored_reports[$i]['user_id']);
        }
		
		$open_services = $this->UserService_model->getServiceInfoNotApprovedList();
		
		$open_business_service_reports = array();
		
		for($i = 0 ; $i < count($open_services); $i++) {
			$business = $this->UserBusiness_model->getBusinessInfo($open_services[$i]['user_id']);
			$business = $business[0];
			$business['type'] = "service";
            $business['user'] = $this->User_model->getUserProfileDTO($open_services[$i]['user_id']);
            $business['services'] = $this->UserService_model->getServiceInfoList($open_services[$i]['user_id']);
			$open_business_service_reports[] = $business; 
		}
		
		$open_reports = array_merge($open_reports, $open_business_service_reports);
		
		usort($open_reports, function (array $a, array $b) { return $a["created_at"] - $b["created_at"]; });


        $dataToBeDisplayed['open_reports'] = $open_reports;
        $dataToBeDisplayed['closed_reports'] = $close_reports;
        $dataToBeDisplayed['ignored_reports'] = $ignored_reports;

        $this->load->view('admin/business/business_list', $dataToBeDisplayed);
    }

    function businessDetail($businessId) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_DETAIL);
        $business = $this->UserBusiness_model->getBusinessInfoById($businessId);
        $business = $business[0];

        $business['user'] = $this->User_model->getUserProfileDTO($business['user_id']);
        $business['services'] = $this->UserService_model->getServiceInfoAllList($business['user_id']);
        $business['tags'] = $this->UserTag_model->getUserTags(array("user_id" => $business['user_id']));

        $dataToBeDisplayed['business'] = $business;

        $this->load->view('admin/business/business_detail', $dataToBeDisplayed);
    }

    public function business_block_form($businessid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BLOCK);
        $dataToBeDisplayed['block_businessid'] = $businessid;
        $this->load->view('admin/business/block_form', $dataToBeDisplayed);
    }

    public function block_business() {

        $setArray = array(
            'approved' => 2,
            'approval_reason' => $this->input->get('blockReason'),
            'updated_at' => time(),
        );

        $whereArray = array('id' => $this->input->get('block_businessid'));

        $this->UserBusiness_model->updateBusinessRecord($setArray, $whereArray);

        $business = $this->UserBusiness_model->getBusinessInfoById( $this->input->get('block_businessid'));
        // $user =  $this->User_model->getUserProfileDTO($business[0]['user_id']);
        $user = $this->User_model->getOnlyUser(array('id' => $business[0]['user_id']));

        echo $user[0]["first_name"];
        $subject = 'Business Rejected for ATB';
        $content = '<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$user[0]['first_name'].' you have been rejected from creating a business account on ATB</span></p>
<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$this->input->get('blockReason').'</span></p>';
        $this->User_model->sendUserEmail($user[0]['user_email'], $subject, $content);

        $firebase = $this->firebase->init();
		$db = $firebase->createDatabase();
		$reference =  $db->getReference('ATB/Admin/business/'.$business[0]['user_id']);
        $reference->set([
            "approved" => "2",
            "updated" => time()*1000
        ]);

        redirect('/admin/business');
    }

    public function business_approve_form($businessid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BLOCK);
        $dataToBeDisplayed['approve_businessid'] = $businessid;
        $this->load->view('admin/business/approve_form', $dataToBeDisplayed);
    }
	
	public function service_approve_form($serviceid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BLOCK);
        $dataToBeDisplayed['approve_serviceid'] = $serviceid;
        $this->load->view('admin/business/approve_service_form', $dataToBeDisplayed);
    }
	
	public function approve_service() {

        $setArray = array(
            'approved' => 1,
            'approval_reason' => $this->input->get('approveReason')
        );

        $whereArray = array('id' => $this->input->get('approve_serviceid'));

        $this->UserService_model->updateServiceRecord($setArray, $whereArray);

        $service = $this->UserService_model->getServiceInfo( $this->input->get('approve_serviceid'));
        $user =  $this->User_model->getUserProfileDTO($service['user_id']);

        $subject = 'Business Service Accepted for ATB';
        $content = '<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$user[0]['first_name'].' your business service has been approved on ATB</span></p>
<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$this->input->get('approveReason').'</span></p>';

        $this->User_model->sendUserEmail($user[0]['user_email'], $subject, $content);

        $business = $this->UserBusiness_model->getBusinessInfo($service[0]['user_id']);
        $this->NotificationHistory_model->insertNewNotification(
            array(
                'user_id' => $business[0]['user_id'],
                'type' => 16,
                'related_id' => $business[0]['user_id'],
                'read_status' => 0,
                'send_status' => 0,
                'visible' => 1,
                'text' => "Hi " . $business[0]['business_name'] . ", your new service has been approved",
                'name' => "",
                'profile_image' => "",
                'updated_at' => time(),
                'created_at' => time()
            )
        );

        redirect('/admin/business');
    }

    public function approve_business() {

        $setArray = array(
            'approved' => 1,
            'approval_reason' => $this->input->get('approveReason'),
            'updated_at' => time(),
        );

        $whereArray = array('id' => $this->input->get('approve_businessid'));

        $this->UserBusiness_model->updateBusinessRecord($setArray, $whereArray);

        $business = $this->UserBusiness_model->getBusinessInfoById( $this->input->get('approve_businessid'));
        //$user =  $this->User_model->getUserProfileDTO($business[0]['user_id']);
        $user = $this->User_model->getOnlyUser(array('id' => $business[0]['user_id']));

        $subject = 'Business Accepted for ATB';
        $content = '<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$user[0]['first_name'].' your business has been approved on ATB</span></p>
<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$this->input->get('approveReason').'</span></p>';

        $this->User_model->sendUserEmail($user[0]['user_email'], $subject, $content);

        $this->NotificationHistory_model->insertNewNotification(
            array(
                'user_id' => $business[0]['user_id'],
                'type' => 15,
                'related_id' => $business[0]['user_id'],
                'read_status' => 0,
                'send_status' => 0,
                'visible' => 1,
                'text' => "Congratulations " . $business[0]['business_name'] . ", your business has been approved",
                'name' => "",
                'profile_image' => "",
                'updated_at' => time(),
                'created_at' => time()
            )
        );

        $firebase = $this->firebase->init();
		$db = $firebase->createDatabase();
		$reference =  $db->getReference('ATB/Admin/business/'.$business[0]['user_id']);
        $reference->set([
            "approved" => "1",
            "updated" => time()*1000
        ]);

        redirect('/admin/business');
    }

    public function threedot() {

        $user_id = $this->input->get('userid');
        $type = $this->input->get('type');
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BLOCK);
        // $dataToBeDisplayed['approve_serviceid'] = $serviceid;
        $dataToBeDisplayed['type'] =$type;
        $dataToBeDisplayed['title'] = "BOOKINGS";

        if($type == 1 ){
            $dataToBeDisplayed['title'] = "SOLD ITEMS";

        } else if($type == 2 ){
            $dataToBeDisplayed['title'] = "POSTS";

        } else if($type == 3 ){
            $dataToBeDisplayed['title'] = "SERVICES CREATED";
        }


        $allBookings = $this->Booking_model->getBookings( array('business_user_id' => $user_id));
        $dataToBeDisplayed['allBookings'] = $allBookings;

        $query = array('user_id' => $user_id);
        if($type == 1){
            $query = array('user_id' => $user_id,'is_sold' => 1 );
        }else if($type == 3){
            $query = array('user_id' => $user_id,'post_type' => 3 );
        }
        $allposts = $this->Post_model->getPostInfo($query,"");
        $cats = array();
        foreach ($allposts as $post) {
            $cats[] = $post["category_title"];
        }
        $cats = array_unique($cats);
        sort($cats);

        $dataToBeDisplayed['allposts'] = $allposts;
        $dataToBeDisplayed["cats"] = $cats;


        if($type == 0){
            $this->load->view('admin/business/three_dot_booking_list', $dataToBeDisplayed);
        }else{
            $this->load->view('admin/business/three_dot_detail_feed', $dataToBeDisplayed);
        }
    }
}