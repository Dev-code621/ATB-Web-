<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/6/19
 * Time: 4:38 AM
 */
class BlockUsersController extends MY_Controller
{
    const BlockUserList = 0;
    const PAGE_DETAIL = 1;

    private function makeComponentLayout($type) {
        $header_include_css = array();
        if($type == self::BlockUserList) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        }
        else if($type == self::PAGE_DETAIL) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css');
        }
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Block Users');
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
        if($type == self::BlockUserList) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');

            $footer_app_after_js = array(base_url().'admin_assets/pages/block_users/block_users_list.js');
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
        $dataToBeDisplayed = $this->makeComponentLayout(self::BlockUserList);

        $allUsers = $this->User_model->getUsersListInDashboard();

        $usersToDisplay = array();
        $frozenUsers = array();
        $blockedUsers = array();
        $removedUsers = array();

        foreach ($allUsers as $user){
            if ($user['status'] == 1) {
                $usersToDisplay[] = $user;
                $blockedUsers[] = $user;
            } elseif ($user['status'] == 2) {
                $usersToDisplay[] = $user;
                $frozenUsers[] = $user;
            } elseif ($user['status'] == 4) {
                $usersToDisplay[] = $user;
                $removedUsers[] = $user;
            }
        }

        $dataToBeDisplayed['users'] = $usersToDisplay;
        $dataToBeDisplayed['blockUsers'] = $blockedUsers;
        $dataToBeDisplayed['frozenUsers'] = $frozenUsers;
        $dataToBeDisplayed['removedUsers'] = $removedUsers;

        $this->load->view('admin/block_users/block_users_list', $dataToBeDisplayed);
    }

    public function detail($userid) {
        $dataToBeDisplayed = $this->makeComponentLayout(self::PAGE_DETAIL);
        $this->load->view('admin/reported_post/reported_post_list', $dataToBeDisplayed);
    }
}