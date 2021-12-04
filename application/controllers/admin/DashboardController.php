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
        $dataToBeDisplayed['users'] = $this->User_model->getUsersListInDashboard();
        $this->load->view('admin/dashboard', $dataToBeDisplayed);
    }
}