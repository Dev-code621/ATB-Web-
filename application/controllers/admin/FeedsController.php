<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/6/19
 * Time: 4:38 AM
 */
class FeedsController extends MY_Controller
{
    const FEEDS_LIST = 0;
    const PAGE_DETAIL = 1;

    private function makeComponentLayout($type) {
        $header_include_css = array();
        if($type == self::FEEDS_LIST) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        }
        else if($type == self::PAGE_DETAIL) {

        }
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Feeds');
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
        if($type == self::FEEDS_LIST) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');
            $footer_app_after_js = array(base_url().'admin_assets/pages/feeds/feeds_list.js');
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
        $dataToBeDisplayed = $this->makeComponentLayout(self::FEEDS_LIST);
        $allposts = $this->Post_model->getPostInfo();
        $cats = array();

        foreach ($allposts as $post) {
            $cats[] = $post["category_title"];
        }

        $cats = array_unique($cats);
        sort($cats);

        $dataToBeDisplayed['allposts'] = $allposts;
        $dataToBeDisplayed["cats"] = $cats;
        $this->load->view('admin/feeds/feeds_list', $dataToBeDisplayed);
    }

    public function search($search) {

        $dataToBeDisplayed = $this->makeComponentLayout(self::FEEDS_LIST);
        $allposts = $this->Post_model->getPostInfo( array(),$search);
        $cats = array();

        foreach ($allposts as $post) {
            $cats[] = $post["category_title"];
        }

        $cats = array_unique($cats);
        sort($cats);

        $dataToBeDisplayed['allposts'] = $allposts;
        $dataToBeDisplayed["cats"] = $cats;
        $this->load->view('admin/feeds/feeds_list', $dataToBeDisplayed);
    }

    public function userPost($userid) {

        $dataToBeDisplayed = $this->makeComponentLayout(self::FEEDS_LIST);
        $allposts = $this->Post_model->getPostInfo( array('user_id' => $userid),"");
        $cats = array();

        foreach ($allposts as $post) {
            $cats[] = $post["category_title"];
        }

        $cats = array_unique($cats);
        sort($cats);

        $dataToBeDisplayed['allposts'] = $allposts;
        $dataToBeDisplayed["cats"] = $cats;
        $dataToBeDisplayed["title"] = 'Feed';
        $this->load->view('admin/feeds/feeds_list', $dataToBeDisplayed);
    }

    public function detail($userid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_DETAIL);
        $this->load->view('admin/feeds/feed_detail', $dataToBeDisplayed);
    }

    public function purchase() {
        $dataToBeDisplayed = $this->makeComponentLayout(self::FEEDS_LIST);
        $transactions = $this->UserBraintreeTransaction_model->getPurchased();
        // foreach ($transactions as $post) {
        //     print_r(json_encode($post));
        //     print_r("==========");
        // }
        // return;
        $dataToBeDisplayed["allposts"] = $transactions;
        $this->load->view('admin/feeds/purchaseslist', $dataToBeDisplayed);
    }
}