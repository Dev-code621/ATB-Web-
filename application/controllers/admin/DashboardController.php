<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/6/19
 * Time: 4:38 AM
 */
class DashboardController extends MY_Controller
{
    const PAGE_LIST = 0;
    const PAGE_DETAIL = 1;
    const PAGE_LOGIN_HISTORY = 2;
    const PAGE_BOOK_HISTORY = 3;
    const PAGE_POST_HISTORY = 4;
    const PAGE_REPORT_HISTORY = 5;
    const PAGE_BLOCK = 6;
    const PAGE_VIEW_POST = 7;

    public function __construct()
    {
        parent::__construct();
    }

    public function index() {

        // $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_LIST);
  
        $open_reports = $this->PostReport_model->getReports(array("is_active" => 0));

        for($i = 0 ; $i < count($open_reports); $i++) {
            if ($open_reports[$i]['post_id'] != 0) {
                $open_reports[$i]['post'] = $this->Post_model->getPostDetail($open_reports[$i]['post_id'], 0);
            }
			if ($open_reports[$i]['user_id'] != 0) {
                $open_reports[$i]['user'] = $this->User_model->getUserProfileDTO($open_reports[$i]['user_id']);
            }
            $open_reports[$i]['reported_user'] = $this->User_model->getUserProfileDTO($open_reports[$i]['reporter_user_id']);
        }


        $open_businesUsers = $this->UserBusiness_model->getBusinessInfos(array("approved" => 0));
        $allposts = $this->Post_model->getPostInfo(array('is_active' => 3,'post_type' => 3 ),"");
      
        $total_count = count($this->AdminNotification_model->getAdminNotification(array('read_status' => 0))) + count( $open_reports) + count(  $open_businesUsers) + count($allposts);
        $this->session->set_userdata('notification_count',$total_count);

        $dataToBeDisplayed['users'] = $this->User_model->getUsersListInDashboard();
        $this->load->view('admin/dashboard', $dataToBeDisplayed);
    }
}