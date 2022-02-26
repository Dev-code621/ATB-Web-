<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/6/19
 * Time: 4:38 AM
 */
class SignUpController extends MY_Controller
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

    private function makeComponentLayout($type) {
        $header_include_css = array();
        if($type == self::PAGE_LIST) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        }
        else if($type == self::PAGE_VIEW_POST) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css');
        }
        else if($type == self::PAGE_DETAIL) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css');
        }
        else if($type == self::PAGE_LOGIN_HISTORY) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        }
        else if($type == self::PAGE_BOOK_HISTORY) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        }
        else if($type == self::PAGE_POST_HISTORY) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        }
        else if($type == self::PAGE_REPORT_HISTORY) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        }
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Signups');
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
        if($type == self::PAGE_LIST) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');

            $footer_app_after_js = array(base_url().'admin_assets/pages/signups/signup_list.js');
        }
        else if($type == self::PAGE_VIEW_POST) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');

            $footer_app_after_js = array(base_url().'admin_assets/pages/signups/post_view.js');
        }
        else if($type == self::PAGE_DETAIL) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
                base_url().'admin_assets/global/plugins/moment.min.js',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js',
                base_url().'admin_assets/global/plugins/flot/jquery.flot.min.js',
                base_url().'admin_assets/global/plugins/flot/jquery.flot.resize.min.js',
                base_url().'admin_assets/global/plugins/flot/jquery.flot.categories.min.js'
                );

            $footer_app_after_js = array(base_url().'admin_assets/pages/signups/signup_detail.js');
        }
        else if($type == self::PAGE_LOGIN_HISTORY) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            );

            $footer_app_after_js = array(base_url().'admin_assets/pages/signups/signup_login_history.js');
        }
        else if($type == self::PAGE_POST_HISTORY) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            );

            $footer_app_after_js = array(base_url().'admin_assets/pages/signups/signup_post_history.js');
        }

        else if($type == self::PAGE_BOOK_HISTORY) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            );

            $footer_app_after_js = array(base_url().'admin_assets/pages/signups/signup_book_history.js');
        }
        else if($type == self::PAGE_REPORT_HISTORY) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            );

            $footer_app_after_js = array(base_url().'admin_assets/pages/signups/signup_report_history.js');
        }



        $footer_layout_data = array('before_app_js' => $footer_app_before_js, 'after_app_js' => $footer_app_after_js);
        $footer_layout = $this->load->view('admin/common_template/footer_layout', $footer_layout_data, TRUE);

        $dataToBeDisplayed['header_layout'] = $header_layout;
        $dataToBeDisplayed['sidebar_layout'] = $sidebar_layout;
        $dataToBeDisplayed['footer_layout'] = $footer_layout;
        return $dataToBeDisplayed;
    }

    public function index() {

        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_LIST);

        $dataToBeDisplayed['users'] = $this->User_model->getUsersListInDashboard();
        $this->load->view('admin/signups/signup_list', $dataToBeDisplayed);
    }

    public function detail($userid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_DETAIL);
        $userDetails = $this->User_model->getOnlyUser(array('id' => $userid));
        if(count($userDetails) == 0) {
            show_404();
        }
        else {
            $dataToBeDisplayed['profile'] = $userDetails[0];
            $business = $this->UserBusiness_model->getBusinessInfo($userDetails[0]['id']);
            if(count($business) == 0) {
                $dataToBeDisplayed['business'] = null;
            }
            else {
                $dataToBeDisplayed['business'] = $business[0];
            }

            $posts = $this->Post_model->getPostInfo(array('user_id' => $userid));
            $reports = $this->PostReport_model->getReports(array('reporter_user_id' => $userid));
            $dataToBeDisplayed['report_counter'] = count($reports);
            $dataToBeDisplayed['book_counter'] = 5;
            $dataToBeDisplayed['post_counter'] = count($posts);
            $dataToBeDisplayed['last_logintimestamp'] = $this->LoginHistory_model->getLastLoginTimeInDashBoard($userid);
            $this->load->view('admin/signups/signup_detail_profile', $dataToBeDisplayed);
        }

    }

    public function ajax_get_login_chart() {
        $loginHistory = array();
        $userId = $this->input->post('userId');
        $startDate = $this->input->post('start');
        $endDate = $this->input->post('end');
        $startDateStr = date('Y-m-d', $startDate);
        $endDateStr = date('Y-m-d', $endDate);
        $retVal = $this->LoginHistory_model->getLoginHistoryTimeInterval($userId, $startDateStr, $endDateStr);
        echo json_encode($retVal);
//        echo json_encode(array($startDateStr, $endDateStr));
    }

    public function login_history($userid) {

        $userDetails = $this->User_model->getOnlyUser(array('id' => $userid));
        if(count($userDetails) > 0) {
            $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_LOGIN_HISTORY);
            $loginHistory = $this->LoginHistory_model->getLoginHistory(array('user_id' => $userid));

            $dataToBeDisplayed['profile'] = $userDetails[0];
            $dataToBeDisplayed['login_history'] = $loginHistory;
            $this->load->view('admin/signups/signup_login_history', $dataToBeDisplayed);
        }
        else {
            show_404();
        }
    }

    public function book_history($userid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BOOK_HISTORY);
        
        $this->load->view('admin/signups/signup_book_history', $dataToBeDisplayed);
    }
    public function post_history($userid) {
        $userDetails = $this->User_model->getOnlyUser(array('id' => $userid));
        if(count($userDetails) == 0) {
            show_404();
        }
        else {
            $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_POST_HISTORY);
            $myPosts = $this->Post_model->getPostInfo(array('user_id' => $userid));
            $dataToBeDisplayed['posts'] = $myPosts;
            
            $dataToBeDisplayed['me'] = $userDetails[0];
            $this->load->view('admin/signups/signup_post_history', $dataToBeDisplayed);
        }
        
    }

    public function report_history($userid) {
        $userDetails = $this->User_model->getOnlyUser(array('id' => $userid));
        if(count($userDetails) == 0) {

        }
        else {
            $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_REPORT_HISTORY);
            $myReports = $this->PostReport_model->getReports(array('reporter_user_id' => $userid));
            for($i = 0 ; $i < count($myReports); $i++) {
                $post = $this->Post_model->getPostDetail($myReports[$i]['post_id']);
                $myReports[$i]['post_info'] = $post;
            }

            $dataToBeDisplayed['reports'] = $myReports;
            $dataToBeDisplayed['me'] = $userDetails[0];
            $this->load->view('admin/signups/signup_report_history', $dataToBeDisplayed);
        }
    }

    public function block_form($userid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BLOCK);
        $dataToBeDisplayed['block_userid'] = $userid;
        $this->load->view('admin/signups/block_form', $dataToBeDisplayed);
    }

    public function submit_block() {
        $setArray = array(
            'status' => 1,
            'status_reason' => $this->input->get('Reason'),
            'updated_at' => time(),
        );

        $whereArray = array('id' => $this->input->get('userid'));

        $this->User_model->updateUserRecord($setArray, $whereArray);

        $user = $this->User_model->getOnlyUser(array('id' => $this->input->get('block_userid')));

        $subject = 'Blocked from ATB';

        $content = '<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$user[0]['first_name'].' you have been blocked from ATB</span></p>
<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$this->input->get('blockReason').'</span></p>';

        $this->User_model->sendUserEmail($user[0]['user_email'], $subject, $content);

        redirect('/admin/signups');
    }

    public function post_block_form($postid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BLOCK);
        $dataToBeDisplayed['block_postid'] = $postid;
        $this->load->view('admin/signups/post_block_form', $dataToBeDisplayed);
    }

    public function block_post() {

        $setArray = array(
            'is_active' => 2,
            'status_reason' => $this->input->get('blockReason'),
            'updated_at' => time(),
        );

        $whereArray = array('id' => $this->input->get('block_postid'));

        $this->Post_model->updatePostContent($setArray, $whereArray);

        $setArray = array("is_active" => 1);
        $whereArray = array('post_id'=>$this->input->get('block_postid'));

        $this->PostReport_model->updateReport($setArray, $whereArray);

        redirect('/admin/feeds');
    }

    public function post_unblock_form($postid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BLOCK);
        $dataToBeDisplayed['unblock_postid'] = $postid;
        $this->load->view('admin/signups/post_unblock_form', $dataToBeDisplayed);
    }

    public function unblock_post() {

        $setArray = array(
            'is_active' => 1,
            'status_reason' => $this->input->get('Reason'),
            'updated_at' => time(),
        );

        $whereArray = array('id' => $this->input->get('unblock_postid'));

        $this->Post_model->updatePostContent($setArray, $whereArray);

        redirect('/admin/feeds');
    }

    public function view_post($postid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_VIEW_POST);

        $dataToBeDisplayed['post'] = $this->Post_model->getPostDetail($postid, 0);
        
        if ($dataToBeDisplayed['post']['is_active'] == 0) {
            $open_reports = $this->PostReport_model->getReports(array("post_id" => $postid));

            for($i = 0 ; $i < count($open_reports); $i++) {
			    if ($open_reports[$i]['user_id'] != 0) {
                    $open_reports[$i]['user'] = $this->User_model->getUserProfileDTO($open_reports[$i]['user_id']);
                }
                $open_reports[$i]['reported_user'] = $this->User_model->getUserProfileDTO($open_reports[$i]['reporter_user_id']);
            }
            
            $dataToBeDisplayed['post']["reports"] = $open_reports;
        }
        
        
        if ($dataToBeDisplayed['post']['post_type'] == 4){
            $this->load->view('admin/signups/view_post_poll', $dataToBeDisplayed);
        } else if($dataToBeDisplayed['post']['post_type'] == 1){
            $this->load->view('admin/signups/view_post_advice', $dataToBeDisplayed);
        } else {
            $this->load->view('admin/signups/view_post_sale', $dataToBeDisplayed);
        }

    }

    public function unblock_form($userid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_BLOCK);
        $dataToBeDisplayed['unblock_userid'] = $userid;
        $this->load->view('admin/signups/unblock_form', $dataToBeDisplayed);
    }

    public function submit_unblock() {
        $setArray = array(
            'status' => 3,
            'status_reason' => $this->input->get('Reason'),
            'updated_at' => time(),
        );

        $whereArray = array('id' => $this->input->get('userid'));

        $this->User_model->updateUserRecord($setArray, $whereArray);

        $user = $this->User_model->getOnlyUser(array('id' => $this->input->get('unblock_userid')));
        $subject = 'Unblocked from ATB';
        $content = '<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$user[0]['first_name'].' you have been unblocked from ATB</span></p>
<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$this->input->get('unblockReason').'</span></p>';

        $this->User_model->sendUserEmail($user[0]['user_email'], $subject, $content);

        redirect('/admin/signups');
    }
}