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
        $business['tags'] = $this->UserTag_model->getUserTags(array("user_id" => $business['user_id']));
        $files  = $this->UserServiceFiles_model->getServiceFileList($business['user_id']);
        $insurance = array();
        $qualification = array();
        for($i = 0;$i< count($files);$i++){
            if($files[$i]['type'] == 0){
                array_push($insurance,$files[$i]);
            }else{
                array_push($qualification,$files[$i]);
            }          

        }
        $business['services']['insurance'] = $insurance;
        $business['services']['qualification'] = $qualification;

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
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
        <head>
            <!--[if gte mso 9]>
            <xml>
                <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
            </xml>
            <![endif]-->
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <meta name="vi	ewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <meta name="format-detection" content="date=no" />
        <meta name="format-detection" content="address=no" />
        <meta name="format-detection" content="telephone=no" />
        <meta name="x-apple-disable-message-reformatting" />
         <!--[if !mso]><!-->
            <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet" />
        <!--<![endif]-->
        <title>Subject: ATB - Approved Business</title>
        
        <style type="text/css"> 
        
            body { padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#F8F8F8; -webkit-text-size-adjust:none }
            p { padding:0 !important; margin:0 !important } 
            table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important;}
            .container {width: 100%; max-width: 650px;}
            .ExternalClass { width: 100%;}
            .ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div {line-height: 100%; }
        
            @media screen and (max-width: 650px) {
                .wrapper {padding: 0 !important;}
                .container { width: 100% !important; min-width: 100% !important; }
                .border {display: none !important;}
                .content {padding: 0 20px 50px !important;}
                .box1 {padding: 55px 40px 50px !important;}
                .social-btn {height: 35px; width: auto;}
                .bottomNav a {font-size: 12px !important; line-height: 16px !important;}
                .spacer {height: 61px !important;}
            }
        </style>
        
        
        </head>
        
        <body style="background-color: #A6BFDE; padding: 0 50px 50px; margin:0">
        <span style="height: 0; width: 0; line-height: 0pt; opacity: 0; display: none;">This is where you write what it will show on the clients email listing. If not, it will take the first text of the email.</span>
        
        <table border="0" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
            <tr>
                <td align="center" valign="top" class="wrapper">
                    <!--[if (gte mso 9)|(IE)]>
                    <table width="650" align="center" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                        <td>
                    <![endif]-->    
                    <table border="0" cellspacing="0" cellpadding="0" class="container">
                        <tr>
                            <td>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="background-color: #A6BFDE;" valign="top" align="center" class="content">
                                            <!--[if gte mso 9]>
                                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
                                                <v:fill type="frame" src="images/background.jpg" color="#ABC1DE" />
                                                <v:textbox inset="0,0,0,0">
                                            <![endif]-->
        
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td align="center" style="padding: 53px 20px 40px">
                                                            <a href="#" target="_blank"><img src="'.base_url().'assets/email/booking/logo.png" width="153" height="47" border="0" alt="" /></a>
                                                        </td>
                                                    </tr>
                                                </table>
        
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td valign="bottom" >
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td height="98">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                                                                            <tr><td  height="38" style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">&nbsp;</td></tr>
                                                                            <tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt;width:100%; min-width:100%;border-radius:5px 0 0 0;">&nbsp;</td></tr>
                                                                        </table>
                                                                    </td>
                                                                    <td width="98" height="98" bgcolor="#F8F8F8" style="border-radius: 50% 50% 0 0!important;max-height: 98px !important;"><img src="'.base_url().'assets/email/booking/icon.png" width="98" height="98" border="0" alt="" style="border: 0 !important; outline:none; text-decoration: none;display:block;max-height: 98px !important;" /></td>
                                                                    <td height="98">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0"  style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">
                                                                            <tr><td  height="38" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;">&nbsp;</td></tr>
                                                                            <tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;border-radius: 0 5px 0 0;">&nbsp;</td></tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td class="box1" bgcolor="#F8F8F8" align="center" style="padding:55px 120px 50px;">
                                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td><h1 style="color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:31px; text-align:center; margin: 0;">Business Rejected</h1>
                                                                              <br><h2 style="margin: 0; color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 300; font-size:20px; line-height:24px; text-align:center;">Hi <strong> ' .$user[0]['first_name']  .$user[0]['last_name'] . ' </strong> You are now an ATB rejected business!</h2>																	  
                                                                              <br></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><p style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">Additional approval is required for each service you provide. If you haven’t already started uploading your services, please do so ASAP.<br>
                                                                                <br><a href="#" style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;text-decoration: underline;font-size: 15px;text-align: center;color: #a6bfde;display: block; margin: auto;">Upload products and services now</a></p></td>
                                                                            </tr>																	
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="border-radius: 0 0 5px 5px ">
                                                                <tr>
                                                                    <td width="100%" style="padding: 0px 20px;">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bottomNav">
                                                                            <tr><td colspan="3" style="padding-top: 30px; padding-bottom: 10px"></td></tr>
                                                                            <tr>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Terms and conditions</a> </td>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Privacy Policy</a> </td>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Contact Us</a> </td>
                                                                            </tr>
                                                                            
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td width="100%" style="padding: 20px 20px 45px;">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td align="center"><a href="#" style="color:#AEC3DE;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:28px; text-align:center; text-decoration: none;">ATB All rights reserved</a> </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
        
                                            <!--[if gte mso 9]>
                                                </v:textbox>
                                                </v:rect>
                                            <![endif]-->
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!--[if (gte mso 9)|(IE)]>
                        </td>
                        </tr>
                    </table>
                    <![endif]-->
                </td>
            </tr>
        </table>
        
        </body>
        </html>
        ';

        $this->sendEmail(
            $user[0]['user_email'],
            $subject,
            $content);

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

        $subject = 'Business Service Accepted for ATB';
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
        <head>
            <!--[if gte mso 9]>
            <xml>
                <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
            </xml>
            <![endif]-->
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <meta name="vi	ewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <meta name="format-detection" content="date=no" />
        <meta name="format-detection" content="address=no" />
        <meta name="format-detection" content="telephone=no" />
        <meta name="x-apple-disable-message-reformatting" />
         <!--[if !mso]><!-->
            <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet" />
        <!--<![endif]-->
        <title>Subject: ATB - Approved Business</title>
        
        <style type="text/css"> 
        
            body { padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#F8F8F8; -webkit-text-size-adjust:none }
            p { padding:0 !important; margin:0 !important } 
            table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important;}
            .container {width: 100%; max-width: 650px;}
            .ExternalClass { width: 100%;}
            .ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div {line-height: 100%; }
        
            @media screen and (max-width: 650px) {
                .wrapper {padding: 0 !important;}
                .container { width: 100% !important; min-width: 100% !important; }
                .border {display: none !important;}
                .content {padding: 0 20px 50px !important;}
                .box1 {padding: 55px 40px 50px !important;}
                .social-btn {height: 35px; width: auto;}
                .bottomNav a {font-size: 12px !important; line-height: 16px !important;}
                .spacer {height: 61px !important;}
            }
        </style>
        
        
        </head>
        
        <body style="background-color: #A6BFDE; padding: 0 50px 50px; margin:0">
        <span style="height: 0; width: 0; line-height: 0pt; opacity: 0; display: none;">This is where you write what it will show on the clients email listing. If not, it will take the first text of the email.</span>
        
        <table border="0" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
            <tr>
                <td align="center" valign="top" class="wrapper">
                    <!--[if (gte mso 9)|(IE)]>
                    <table width="650" align="center" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                        <td>
                    <![endif]-->    
                    <table border="0" cellspacing="0" cellpadding="0" class="container">
                        <tr>
                            <td>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="background-color: #A6BFDE;" valign="top" align="center" class="content">
                                            <!--[if gte mso 9]>
                                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
                                                <v:fill type="frame" src="images/background.jpg" color="#ABC1DE" />
                                                <v:textbox inset="0,0,0,0">
                                            <![endif]-->
        
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td align="center" style="padding: 53px 20px 40px">
                                                            <a href="#" target="_blank"><img src="'.base_url().'assets/email/images/logo.png" width="153" height="47" border="0" alt="" /></a>
                                                        </td>
                                                    </tr>
                                                </table>
        
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td valign="bottom" >
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td height="98">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                                                                            <tr><td  height="38" style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">&nbsp;</td></tr>
                                                                            <tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt;width:100%; min-width:100%;border-radius:5px 0 0 0;">&nbsp;</td></tr>
                                                                        </table>
                                                                    </td>
                                                                    <td width="98" height="98" bgcolor="#F8F8F8" style="border-radius: 50% 50% 0 0!important;max-height: 98px !important;"><img src="'.base_url().'assets/email/images/icon.png" width="98" height="98" border="0" alt="" style="border: 0 !important; outline:none; text-decoration: none;display:block;max-height: 98px !important;" /></td>
                                                                    <td height="98">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0"  style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">
                                                                            <tr><td  height="38" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;">&nbsp;</td></tr>
                                                                            <tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;border-radius: 0 5px 0 0;">&nbsp;</td></tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td class="box1" bgcolor="#F8F8F8" align="center" style="padding:55px 120px 50px;">
                                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td><h1 style="color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:31px; text-align:center; margin: 0;">ATB has Approved Your Service</h1>
                                                                              <br><h2 style="margin: 0; color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 300; font-size:20px; line-height:24px; text-align:center;"><strong> '.$service['title'].'</strong></h2>																	  
                                                                              <br></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><p style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">You may now advertise on the ATB newsfeed.<br></p></td>
                                                                            </tr>																	
                                                                            <tr>
                                                                                <td>
                                                                                    <table width="100%" style="margin-top: 20px;" cellpadding="10" cellspacing="10">
                                                                                        <tr style="border-radius: 7px;background: #EFEFEF;">
                                                                                            <td width="57%" style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;line-height: 12px;text-align: left;color: #838383;">Price, starting from</td>
                                                                                            <td width="43%" style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: 500;font-size: 15px;line-height: 12px;text-align: right;color: #575757;"><strong>£'.number_format($service['price'], 2).'</strong></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td bgcolor="#F8F8F8"></td>
                                                                                        </tr>
                                                                                        <tr style="border-radius: 7px;background: #EFEFEF;">
                                                                                            <td style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;line-height: 12px;text-align: left;color: #838383;">Needs a deposit of</td>
                                                                                            <td style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: 500;font-size: 15px;line-height: 12px;text-align: right;color: #575757;"><strong>£'.number_format($service['deposit_amount'], 2).'</strong></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td bgcolor="#F8F8F8"></td>
                                                                                        </tr>
                                                                                        <tr style="border-radius: 7px;background: #EFEFEF;">
                                                                                            <td style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;line-height: 12px;text-align: left;color: #838383;">Cancellations Within 􀅴</td>
                                                                                            <td style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: 500;font-size: 15px;line-height: 12px;text-align: right;color: #575757;"><strong>'.$service['cancellations'] .' days</strong></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td bgcolor="#F8F8F8"></td>
                                                                                        </tr>
                                                                                        <tr style="border-radius: 7px;background: #EFEFEF;">
                                                                                            <td style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;line-height: 12px;text-align: left;color: #838383;">Area Covered</td>
                                                                                            <td style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: 500;font-size: 15px;line-height: 12px;text-align: right;color: #575757;"><strong>'.$service['location_id'] .'</strong></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td bgcolor="#F8F8F8"></td>
                                                                                        </tr>
                                                                                        <tr style="border-radius: 7px;background: #EFEFEF;">
                                                                                            <td style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;line-height: 12px;text-align: left;color: #838383;">Insurance</td>
                                                                                            <td style="text-align: right;"> <a href="" style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: 500;font-size: 15px;line-height: 12px;text-align: right;color: #a6bfde;">View Insurance &gt;</a></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td bgcolor="#F8F8F8"></td>
                                                                                        </tr>
                                                                                        <tr style="border-radius: 7px;background: #EFEFEF;">
                                                                                            <td style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;line-height: 12px;text-align: left;color: #838383;">Qualifications</td>
                                                                                            <td style="text-align: right;"><a href="" style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: 500;font-size: 15px;line-height: 12px;text-align: right;color: #a6bfde;">View Qualifications &gt;</a></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="border-radius: 0 0 5px 5px ">
                                                                <tr>
                                                                    <td width="100%" style="padding: 0px 20px;">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bottomNav">
                                                                            <tr><td colspan="3" style="padding-top: 30px; padding-bottom: 10px"></td></tr>
                                                                            <tr>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Terms and conditions</a> </td>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Privacy Policy</a> </td>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Contact Us</a> </td>
                                                                            </tr>
                                                                            
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td width="100%" style="padding: 20px 20px 45px;">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td align="center"><a href="#" style="color:#AEC3DE;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:28px; text-align:center; text-decoration: none;">ATB All rights reserved</a> </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
        
                                            <!--[if gte mso 9]>
                                                </v:textbox>
                                                </v:rect>
                                            <![endif]-->
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!--[if (gte mso 9)|(IE)]>
                        </td>
                        </tr>
                    </table>
                    <![endif]-->
                </td>
            </tr>
        </table>
        
        </body>
        </html>
        ';

    $this->sendEmail(
        $user[0]['user_email'],
        $subject,
        $content);
       
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
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
        <head>
            <!--[if gte mso 9]>
            <xml>
                <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
            </xml>
            <![endif]-->
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <meta name="vi	ewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
        <meta name="format-detection" content="date=no" />
        <meta name="format-detection" content="address=no" />
        <meta name="format-detection" content="telephone=no" />
        <meta name="x-apple-disable-message-reformatting" />
         <!--[if !mso]><!-->
            <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet" />
        <!--<![endif]-->
        <title>Subject: ATB - Approved Business</title>
        
        <style type="text/css"> 
        
            body { padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#F8F8F8; -webkit-text-size-adjust:none }
            p { padding:0 !important; margin:0 !important } 
            table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important;}
            .container {width: 100%; max-width: 650px;}
            .ExternalClass { width: 100%;}
            .ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div {line-height: 100%; }
        
            @media screen and (max-width: 650px) {
                .wrapper {padding: 0 !important;}
                .container { width: 100% !important; min-width: 100% !important; }
                .border {display: none !important;}
                .content {padding: 0 20px 50px !important;}
                .box1 {padding: 55px 40px 50px !important;}
                .social-btn {height: 35px; width: auto;}
                .bottomNav a {font-size: 12px !important; line-height: 16px !important;}
                .spacer {height: 61px !important;}
            }
        </style>
        
        
        </head>
        
        <body style="background-color: #A6BFDE; padding: 0 50px 50px; margin:0">
        <span style="height: 0; width: 0; line-height: 0pt; opacity: 0; display: none;">This is where you write what it will show on the clients email listing. If not, it will take the first text of the email.</span>
        
        <table border="0" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
            <tr>
                <td align="center" valign="top" class="wrapper">
                    <!--[if (gte mso 9)|(IE)]>
                    <table width="650" align="center" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                        <td>
                    <![endif]-->    
                    <table border="0" cellspacing="0" cellpadding="0" class="container">
                        <tr>
                            <td>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="background-color: #A6BFDE;" valign="top" align="center" class="content">
                                            <!--[if gte mso 9]>
                                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
                                                <v:fill type="frame" src="images/background.jpg" color="#ABC1DE" />
                                                <v:textbox inset="0,0,0,0">
                                            <![endif]-->
        
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td align="center" style="padding: 53px 20px 40px">
                                                            <a href="#" target="_blank"><img src="'.base_url().'assets/email/images/logo.png" width="153" height="47" border="0" alt="" /></a>
                                                        </td>
                                                    </tr>
                                                </table>
        
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td valign="bottom" >
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td height="98">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" >
                                                                            <tr><td  height="38" style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">&nbsp;</td></tr>
                                                                            <tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt;width:100%; min-width:100%;border-radius:5px 0 0 0;">&nbsp;</td></tr>
                                                                        </table>
                                                                    </td>
                                                                    <td width="98" height="98" bgcolor="#F8F8F8" style="border-radius: 50% 50% 0 0!important;max-height: 98px !important;"><img src="'.base_url().'assets/email/images/icon.png" width="98" height="98" border="0" alt="" style="border: 0 !important; outline:none; text-decoration: none;display:block;max-height: 98px !important;" /></td>
                                                                    <td height="98">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0"  style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">
                                                                            <tr><td  height="38" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;">&nbsp;</td></tr>
                                                                            <tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;border-radius: 0 5px 0 0;">&nbsp;</td></tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td class="box1" bgcolor="#F8F8F8" align="center" style="padding:55px 120px 50px;">
                                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td><h1 style="color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:31px; text-align:center; margin: 0;">Business Approved</h1>
                                                                              <br><h2 style="margin: 0; color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 300; font-size:20px; line-height:24px; text-align:center;">Hi <strong> ' .$user[0]['first_name']  . " ".$user[0]['last_name'] . ' </strong> You are now an ATB approved business! Thank you for joining our growing community.</h2>																	  
                                                                              <br></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><p style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">Additional approval is required for each service you provide. If you haven’t already started uploading your services, please do so ASAP.<br>
                                                                                <br><a href="#" style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;text-decoration: underline;font-size: 15px;text-align: center;color: #a6bfde;display: block; margin: auto;">Upload products and services now</a></p></td>
                                                                            </tr>																	
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="border-radius: 0 0 5px 5px ">
                                                                <tr>
                                                                    <td width="100%" style="padding: 0px 20px;">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bottomNav">
                                                                            <tr><td colspan="3" style="padding-top: 30px; padding-bottom: 10px"></td></tr>
                                                                            <tr>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Terms and conditions</a> </td>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Privacy Policy</a> </td>
                                                                                <td align="center"><a href="#" style="color:#A2A2A2;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Contact Us</a> </td>
                                                                            </tr>
                                                                            
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td width="100%" style="padding: 20px 20px 45px;">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td align="center"><a href="#" style="color:#AEC3DE;font-family:&#39Roboto&#39, Arial, sans-serif;font-size:15px; line-height:28px; text-align:center; text-decoration: none;">ATB All rights reserved</a> </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
        
                                            <!--[if gte mso 9]>
                                                </v:textbox>
                                                </v:rect>
                                            <![endif]-->
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <!--[if (gte mso 9)|(IE)]>
                        </td>
                        </tr>
                    </table>
                    <![endif]-->
                </td>
            </tr>
        </table>
        
        </body>
        </html>
        ';

        $this->sendEmail(
            $user[0]['user_email'],
            $subject,
            $content);
            
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
        if($type == 3 ){            
            $allposts = $this->UserService_model->getServiceInfos(array('user_id' =>$user_id));
            $dataToBeDisplayed['allposts'] = $allposts;

        }
        $dataToBeDisplayed["cats"] = $cats;


        if($type == 0){
            $this->load->view('admin/business/three_dot_booking_list', $dataToBeDisplayed);
        }else if($type == 3){
            $this->load->view('admin/business/user_services_list', $dataToBeDisplayed);
        }else{
            $this->load->view('admin/business/three_dot_detail_feed', $dataToBeDisplayed);

        }
    }
}