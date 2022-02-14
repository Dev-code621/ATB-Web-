<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/6/19
 * Time: 4:38 AM
 */
class NotificationsController extends MY_Controller
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
        else if($type == self::PAGE_DETAIL) {
            $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css');
        }
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Notifications');
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
        if($type == self::NOTIFICATION_LIST) {
            $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
                base_url().'admin_assets/global/plugins/jquery-star-rating/jquery.star-rating-svg.js');

            $footer_app_after_js = array(base_url().'admin_assets/pages/notifications/notification_list.js');
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
        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);

        $newNotifications = $this->AdminNotification_model->getAdminNotification(array('read_status' => 0));
        $oldNotifications = $this->AdminNotification_model->getAdminNotification(array('read_status' => 1));

        $lastCheckedTime = 0;

        if(count($newNotifications) > 0) {
            $newestNotification = end($newNotifications);
            $lastCheckedTime = $newestNotification['created_at'];
        } elseif (count($oldNotifications) > 0) {
            $newestNotification = end($oldNotifications);
            $lastCheckedTime = $newestNotification['created_at'];
        }

        $newerPosts = $this->Post_model->getPostInfo(array('created_at >' => $lastCheckedTime));
        $newerComments = $this->PostComment_model->getComments(array('created_at >' => $lastCheckedTime));

        $keywords = $this->AdminNotification_model->getNotificationKeyword(array('active' => 1));

        foreach ($keywords as $keyword) {
            $key = strtolower($keyword["keyword"]);

            foreach ($newerPosts as $post) {
                if (strpos(strtolower($post["title"]) , $key) !== false) {
                    $insertArray = array(
                        'user_id' => $post["user_id"],
                        'type' => 1,
                        'related_id' => $post['id'],
                        'read_status' => 0,
                        'notification_keyword_id' => $keyword['id'],
                        'updated_at' => time(),
                        'created_at' => time()
                    );

                    $this->AdminNotification_model->insertNewAdminNotification($insertArray);
                } else if (strpos(strtolower($post["description"]) , $key) !== false) {
                    $insertArray = array(
                        'user_id' => $post["user_id"],
                        'type' => 1,
                        'related_id' => $post['id'],
                        'read_status' => 0,
                        'notification_keyword_id' => $keyword['id'],
                        'updated_at' => time(),
                        'created_at' => time()
                    );

                    $this->AdminNotification_model->insertNewAdminNotification($insertArray);
                }
            }

            foreach ($newerComments as $comment) {
                if (strpos(strtolower($comment["comment"]) , $key) !== false) {
                    $insertArray = array(
                        'user_id' => $comment["commenter_user_id"],
                        'type' => 0,
                        'related_id' => $comment['id'],
                        'read_status' => 0,
                        'notification_keyword_id' => $keyword['id'],
                        'updated_at' => time(),
                        'created_at' => time()
                    );

                    $this->AdminNotification_model->insertNewAdminNotification($insertArray);
                }
            }
        }

        $newNotifications = $this->AdminNotification_model->getAdminNotification(array('read_status' => 0));

        for($i = 0 ; $i < count($newNotifications); $i++) {
            $newNotifications[$i]['post'] = $this->Post_model->getPostDetail($newNotifications[$i]['related_id'], 0);

            if ($newNotifications[$i]['type'] == 0) {
                $newNotifications[$i]['comment'] = $this->PostComment_model->getComments(array("id" => $newNotifications[$i]['related_id']));
                $newNotifications[$i]['post'] = $this->Post_model->getPostDetail($newNotifications[$i]['related_id'], 0);
            } else {
                $newNotifications[$i]['post'] = $this->Post_model->getPostDetail($newNotifications[$i]['related_id'], 0);
            }
            $newNotifications[$i]['user'] = $this->User_model->getUserProfileDTO($newNotifications[$i]['user_id']);
            $newNotifications[$i]['keyword'] = $this->AdminNotification_model->getNotificationKeyword(array("id" => $newNotifications[$i]['notification_keyword_id']));
        }

        $oldNotifications = $this->AdminNotification_model->getAdminNotification(array('read_status' => 1));

        for($i = 0 ; $i < count($oldNotifications); $i++) {
            if ($oldNotifications[$i]['type'] == 0) {

                $oldNotifications[$i]['comment'] = $this->PostComment_model->getComments(array("id" => $oldNotifications[$i]['related_id']));
                $oldNotifications[$i]['post'] = $this->Post_model->getPostDetail($oldNotifications[$i]['related_id'],0);
            } else {
                $oldNotifications[$i]['post'] = $this->Post_model->getPostDetail($oldNotifications[$i]['related_id'], 0);
            }
            $oldNotifications[$i]['user'] = $this->User_model->getUserProfileDTO($oldNotifications[$i]['user_id']);
            $oldNotifications[$i]['keyword'] = $this->AdminNotification_model->getNotificationKeyword(array("id" => $oldNotifications[$i]['notification_keyword_id']));
        }

        $dataToBeDisplayed["keywords"] = $keywords;
        $dataToBeDisplayed["newNotifications"] = $newNotifications;
        $dataToBeDisplayed["oldNotifications"] = $oldNotifications;

        $this->load->view('admin/notifications/notification_list', $dataToBeDisplayed);
    }

    public function newKeyword(){
        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);
        $this->load->view('admin/notifications/keyword_new', $dataToBeDisplayed);
    }

    public function createKeyword(){
        $insertArray = array(
            'keyword' => $this->input->get('inputKeyword'),
            'active' => 1
        );

        $this->AdminNotification_model->insertNewNotificationKeyword($insertArray);
        redirect('/admin/notifications');
    }

    public function deleteKeyword($keywordid) {
        $this->AdminNotification_model->updateNotificationKeyword(array("active" => 0), array('id' => $keywordid));
        redirect('/admin/notifications');
    }

    public function actionNotification($id){
        $dataToBeDisplayed = $this->makeComponentLayout(self::NOTIFICATION_LIST);
        $dataToBeDisplayed["notificationId"] = $id;
        $this->load->view('admin/notifications/action', $dataToBeDisplayed);
    }

    public function saveAction() {

        $setArray = array(
            'read_status' => 1,
            'action' => $this->input->get('actionTaken'),
            'updated_at' => time(),
        );

        $whereArray = array('id' => $this->input->get('notificationId'));

        $this->AdminNotification_model->updateAdminNotificationRecord($setArray, $whereArray);

        redirect('/admin/notifications');
    }
}