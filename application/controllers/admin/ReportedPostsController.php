<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/6/19
 * Time: 4:38 AM
 */
class ReportedPostsController extends MY_Controller
{
    const REPORT_POSTS_LIST = 0;
    const PAGE_DETAIL = 1;

    private function makeComponentLayout($type) {
        $header_include_css = array();
        if($type == self::REPORT_POSTS_LIST) {
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
        if($type == self::REPORT_POSTS_LIST) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');

            $footer_app_after_js = array(base_url().'admin_assets/pages/reported_post/reported_post_list.js');
        }
        else if($type == self::PAGE_DETAIL) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
                base_url().'admin_assets/global/plugins/moment.min.js',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js',
                base_url().'admin_assets/global/plugins/flot/jquery.flot.min.js'
                );

            $footer_app_after_js = array(base_url().'admin_assets/pages/signups/notification.js');
        }

        $footer_layout_data = array('before_app_js' => $footer_app_before_js, 'after_app_js' => $footer_app_after_js);
        $footer_layout = $this->load->view('admin/common_template/footer_layout', $footer_layout_data, TRUE);

        $dataToBeDisplayed['header_layout'] = $header_layout;
        $dataToBeDisplayed['sidebar_layout'] = $sidebar_layout;
        $dataToBeDisplayed['footer_layout'] = $footer_layout;
        return $dataToBeDisplayed;
    }

    public function index() {
        $dataToBeDisplayed = $this->makeComponentLayout(self::REPORT_POSTS_LIST);

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

        $close_reports = $this->PostReport_model->getReports(array("is_active" => 1));

        for($i = 0 ; $i < count($close_reports); $i++) {
            if ($close_reports[$i]['post_id'] != 0) {
                $close_reports[$i]['post'] = $this->Post_model->getPostDetail($close_reports[$i]['post_id'], 0);
            }
			if ($close_reports[$i]['user_id'] != 0) {
                $close_reports[$i]['user'] = $this->User_model->getUserProfileDTO($close_reports[$i]['user_id']);
            }
            $close_reports[$i]['reported_user'] = $this->User_model->getUserProfileDTO($close_reports[$i]['reporter_user_id']);
        }

        $ignored_reports = $this->PostReport_model->getReports(array("is_active" => 2));

        for($i = 0 ; $i < count($ignored_reports); $i++) {
            if ($ignored_reports[$i]['post_id'] != 0) {
                $ignored_reports[$i]['post'] = $this->Post_model->getPostDetail($ignored_reports[$i]['post_id'], 0);
            }
			if ($ignored_reports[$i]['user_id'] != 0) {
                $ignored_reports[$i]['user'] = $this->User_model->getUserProfileDTO($ignored_reports[$i]['user_id']);
            }
            $ignored_reports[$i]['reported_user'] = $this->User_model->getUserProfileDTO($ignored_reports[$i]['reporter_user_id']);
        }

        $dataToBeDisplayed['open_reports'] = $open_reports;
        $dataToBeDisplayed['closed_reports'] = $close_reports;
        $dataToBeDisplayed['ignored_reports'] = $ignored_reports;

        $this->load->view('admin/reported_post/reported_post_list', $dataToBeDisplayed);
    }

    public function ignoreReport($reportid) {

        $open_report = $this->PostReport_model->getReports(array("id" => $reportid));

        $setArray = array("is_active" => 2);
        $whereArray = array('id'=>$reportid);

        $this->PostReport_model->updateReport($setArray, $whereArray);

        $setArray = array(
            'is_active' => 1,
            'status_reason' => "Report ignored",
            'updated_at' => time(),
        );

        $whereArray = array('id' => $open_report[0]['post_id']);

        $this->Post_model->updatePostContent($setArray, $whereArray);

        redirect('/admin/reported_post');
    }
}