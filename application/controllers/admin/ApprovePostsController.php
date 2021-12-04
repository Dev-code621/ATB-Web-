<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/6/19
 * Time: 4:38 AM
 */
class ApprovePostsController extends MY_Controller
{
    const APPROVE_POSTS_LIST = 0;
    const PAGE_DETAIL = 1;

    private function makeComponentLayout($type) {
        $header_include_css = array();
        if($type == self::APPROVE_POSTS_LIST) {

        }
        else if($type == self::PAGE_DETAIL) {
        }
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Approve');
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
        if($type == self::APPROVE_POSTS_LIST) {

            $footer_app_after_js = array(base_url().'admin_assets/pages/approve_post/approve_post_list.js');
        }
        else if($type == self::PAGE_DETAIL) {
        }

        $footer_layout_data = array('before_app_js' => $footer_app_before_js, 'after_app_js' => $footer_app_after_js);
        $footer_layout = $this->load->view('admin/common_template/footer_layout', $footer_layout_data, TRUE);

        $dataToBeDisplayed['header_layout'] = $header_layout;
        $dataToBeDisplayed['sidebar_layout'] = $sidebar_layout;
        $dataToBeDisplayed['footer_layout'] = $footer_layout;
        return $dataToBeDisplayed;
    }

    public function index() {
        $dataToBeDisplayed = $this->makeComponentLayout(self::APPROVE_POSTS_LIST);
        $posts = $this->Post_model->getPostInfo();
        for($i = 0 ; $i < count($posts); $i++) {
            $authData = $this->User_model->getOnlyUser(array('id' => $posts[$i]['user_id']));

            $posts[$i]['auth'] = $authData[0];
        }
        $dataToBeDisplayed['posts'] = $posts;
        $this->load->view('admin/approve_post/approve_post_list', $dataToBeDisplayed);
    }

    public function detail($userid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_DETAIL);
        $this->load->view('admin/reported_post/reported_post_list', $dataToBeDisplayed);
    }
}