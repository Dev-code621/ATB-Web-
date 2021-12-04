<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/10
 * Time: 3:10 PM
 */
class User_model extends MY_Model
{
    /******
     *  CRUD Functions
     *
     *
     */
    public function insertNewUser($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_USER_INFO, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function insertUserBusinessInfo($arrInsertVal = array()) {
        if($this->db->insert(self::TABLE_BUSINESS_INFO, $arrInsertVal)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_SUCCESS;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = self::DB_RESULT_FAILED;
            $result[MY_Controller::MESSAGE_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function getOnlyUser($where = array()) {
		
		$existUser = $this->db->select('*')->from(self::TABLE_USER_INFO)->where($where)->get()->result_array();
		
		if(count($existUser) > 0) {
			$businessInfos = $this->UserBusiness_model->getBusinessInfo($existUser[0]['id']);
			$userpostsCount = $this->Post_model->getPostCounter(
                array(
                    'user_id' => $existUser[0]['id'], 
                    'poster_profile_type' => 0
                )
            );
			$userfollowers = $this->LikeInfo_model->getFollowerCounter(0, $existUser[0]['id']);
			$userfollows = $this->LikeInfo_model->getFollowCounter($existUser[0]['id'], 0);
			
			$existUser[0]["post_count"] = $userpostsCount;
			$existUser[0]["followers_count"] = $userfollowers;
			$existUser[0]["follow_count"] = $userfollows;
			
			if(count($businessInfos) > 0 ) {
                $businesspostsCount = $this->Post_model->getPostCounter(
                    array(
                        'user_id' => $existUser[0]['id'],
                        'poster_profile_type' => 1
                    )
                );
                    
                $businessfollowers = $this->LikeInfo_model->getFollowerCounter($businessInfos[0]['id'], 0);
                $businessfollows = $this->LikeInfo_model->getFollowCounter(0, $businessInfos[0]['id']);
                
                $businessInfos[0]["post_count"] = $businesspostsCount;
                $businessInfos[0]["followers_count"] = $businessfollowers;
                $businessInfos[0]["follow_count"] = $businessfollows;			
			
			    $services = $this->UserService_model->getServiceInfoList($existUser[0]['id']);
			    $businessInfos[0]['services'] = $services;

			    $socials = $this->UserSocial_model->getUserSocials($existUser[0]['id']);
                $businessInfos[0]['socials'] = $socials;

                $existUser[0]['business_info'] = $businessInfos[0];
                 	
			}
		}        
		
        return $existUser;
    }

    public function getSimpleDTO($where = array()) {
        $userInfos = $this->getOnlyUser($where);
        if(count($userInfos) == 0) {
            return null;
        }
        else {
            return array('id' => $userInfos[0]['id'], 'first_name' => $userInfos[0]['first_name'], 'last_name' => $userInfos[0]['last_name'], 'user_name' => $userInfos[0]['user_name']);
        }
    }

    public function updateUserRecord($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_USER_INFO, $setArray);
    }

    // used in ATB directory search
    public function getUsers($where = array(), $tag = "") {
        $users = array();

        if ($tag == "") {
            $users = $this->db->select(
                self::TABLE_USER_INFO.'.*,'.
                'user_extend_infos.approved')
                ->from(self::TABLE_USER_INFO)
                ->join('user_extend_infos', self::TABLE_USER_INFO.'.id = user_extend_infos.user_id', 'left outer')
                ->where($where)
                ->get()
                ->result_array();

        } else {
            $tag = trim($tag);
			$tag = strtolower($tag);
			$tag = preg_replace("/[^A-Za-z0-9 ]/", '', $tag);

            $likeWhere = "(LOWER(user_tags.name) = '" . $tag . "' OR
                        LOWER(user_extend_infos.business_name) LIKE '%" . $tag . "%')";

            $users = $this->db->select(
                self::TABLE_USER_INFO.'.*,'.
                'user_extend_infos.business_name,'.
                'user_extend_infos.approved,'.
                'user_tags.name')
                ->from(self::TABLE_USER_INFO)
                ->join('user_extend_infos', self::TABLE_USER_INFO.'.id = user_extend_infos.user_id', 'left outer')
                ->join('user_tags', self::TABLE_USER_INFO.'.id = user_tags.user_id', 'left outer')
                ->where($where)
                ->where($likeWhere)
                ->group_by(self::TABLE_USER_INFO.'.id')                
                ->order_by(self::TABLE_USER_INFO.'.id', 'DESC')                
                ->get()
                ->result_array();
        }

        for ($i = 0; $i < count($users); $i ++) {
			$userpostsCount = $this->Post_model->getPostCounter(
                array(
                    'user_id' => $users[$i]['id'], 
                    'poster_profile_type' => 0
                )
            );
			$userfollowers = $this->LikeInfo_model->getFollowerCounter(0, $users[$i]['id']);
			$userfollows = $this->LikeInfo_model->getFollowCounter($users[$i]['id'], 0);
			
			$users[$i]["post_count"] = $userpostsCount;
			$users[$i]["followers_count"] = $userfollowers;
			$users[$i]["follow_count"] = $userfollows;
			            
            $businessInfos = $this->UserBusiness_model->getBusinessInfo($users[$i]['id']);
			if(count($businessInfos) > 0 ) {
                $businesspostsCount = $this->Post_model->getPostCounter(
                    array(
                        'user_id' => $users[$i]['id'],
                        'poster_profile_type' => 1
                    )
                );
                    
                $businessfollowers = $this->LikeInfo_model->getFollowerCounter($businessInfos[0]['id'], 0);
                $businessfollows = $this->LikeInfo_model->getFollowCounter(0, $businessInfos[0]['id']);
                
                $businessInfos[0]["post_count"] = $businesspostsCount;
                $businessInfos[0]["followers_count"] = $businessfollowers;
                $businessInfos[0]["follow_count"] = $businessfollows;			
			
			    $services = $this->UserService_model->getServiceInfoList($users[$i]['id']);
			    $businessInfos[0]['services'] = $services;

			    $socials = $this->UserSocial_model->getUserSocials($users[$i]['id']);
                $businessInfos[0]['socials'] = $socials;

                $users[$i]['business_info'] = $businessInfos[0];                 	
			}
        }

        return $users;
    }


    /*************
     *  Functions using by mobile project
     */
    public function doLoginMobileApp($userRecord) {
        // make login history

        // get user business information
        // get user card information
        // get user feeds
        $retVal = array('profile' => $userRecord);

        $this->LoginHistory_model->insertNewLog($userRecord['id'], $this->input->ip_address());
        $businessInfos = $this->UserBusiness_model->getBusinessInfo($userRecord['id']);
        if(count($businessInfos) > 0 ) {
            $services = $this->UserService_model->getServiceInfoList($userRecord['id']);
            $businessInfos[0]['services'] = $services;
            //$retVal['business_info'] = $businessInfos[0];
            
            $socials = $this->UserSocial_model->getUserSocials($userRecord['id']);
            $businessInfos[0]['socials'] = $socials;
            //$retVal['business_info'] = $businessInfos[0];
            
            $businesspostsCount = $this->Post_model->getPostCounter(array('user_id' => $userRecord['id'], 'poster_profile_type' => 1));
            $businessfollowers = $this->LikeInfo_model->getFollowerCounter($businessInfos[0]['id'], 0);
            $businessfollows = $this->LikeInfo_model->getFollowCounter(0, $businessInfos[0]['id']);
            
            $businessInfos[0]["post_count"] = $businesspostsCount;
            $businessInfos[0]["followers_count"] = $businessfollowers;
            $businessInfos[0]["follow_count"] = $businessfollows;
            
            $retVal['business_info'] = $businessInfos[0];            
        }
        else {
            $retVal['business_info'] = null;
        }

        $settingInfos = $this->AccountSetting_model->getSettingInfo($userRecord['id']);
        if(count($settingInfos) > 0 ) {
            $retVal['setting_info'] = $settingInfos[0];
        }
        else {
            $retVal['setting_info'] = null;
        }

        $userFeeds = $this->Feeds_model->getFeedsInfo($userRecord['id']);
        $retVal['feed_info'] = $userFeeds;

        $this->load->model('UserBraintree_model');
        $userBraintrees = $this->UserBraintree_model->getUserBraintreeInfo(array('user_id' => $userRecord['id']));
        if(count($userBraintrees) > 0 ) {
            $retVal['profile']['bt_customer_id'] = $userBraintrees[0]['customer_id'];
            $retVal['profile']['bt_paypal_account'] = $userBraintrees[0]['receive_address'];
        }
        else {
            $retVal['profile']['bt_customer_id'] = "";
            $retVal['profile']['bt_paypal_account'] = "";
        }
                
        return $retVal;
    }


    public function getUserProfileDTO($userId) {
        $user = $this->getOnlyUser(array('id' => $userId));
        $retVal = array();
        if(count($user) == 0) {
            $retVal = null;
        }
        else {
            $retVal['profile'] = $user[0];
            $retVal['follower_count'] = $this->LikeInfo_model->getFollowerCounter($userId, "0");
            $retVal['notification_count'] = $this->NotificationHistory_model -> getNotificationHistoryCounter($userId);
        }
        return $retVal;
    }


    /***
     *  Funcs using by admin panel
     */

    public function getUsersListInDashboard() {
        $users = $this->getOnlyUser();
        for ($i = 0 ; $i < count($users); $i++) {
            $loginHistory = $this->LoginHistory_model->getLastLoginTimeInDashBoard($users[$i]['id']);
            $postHistory = $this->Post_model->getPostInfo(array('user_id' => $users[$i]['id']));
            $users[$i]['last_login_timestamp'] = $loginHistory;
            $users[$i]['post_count'] = count($postHistory);
        }
        return $users;
    }
	
	public function sendUserEmail($email, $subject, $content) {
		$this->load->library('email');
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
<meta content="width=device-width" name="viewport"/>
<!--[if !mso]><!-->
<meta content="IE=edge" http-equiv="X-UA-Compatible"/>
<!--<![endif]-->
<title></title>
<!--[if !mso]><!-->
<!--<![endif]-->
<style type="text/css">
		body {
			margin: 0;
			padding: 0;
		}

		table,
		td,
		tr {
			vertical-align: top;
			border-collapse: collapse;
		}

		* {
			line-height: inherit;
		}

		a[x-apple-data-detectors=true] {
			color: inherit !important;
			text-decoration: none !important;
		}
	</style>
<style id="media-query" type="text/css">
		@media (max-width: 520px) {

			.block-grid,
			.col {
				min-width: 320px !important;
				max-width: 100% !important;
				display: block !important;
			}

			.block-grid {
				width: 100% !important;
			}

			.col {
				width: 100% !important;
			}

			.col>div {
				margin: 0 auto;
			}

			img.fullwidth,
			img.fullwidthOnMobile {
				max-width: 100% !important;
			}

			.no-stack .col {
				min-width: 0 !important;
				display: table-cell !important;
			}

			.no-stack.two-up .col {
				width: 50% !important;
			}

			.no-stack .col.num4 {
				width: 33% !important;
			}

			.no-stack .col.num8 {
				width: 66% !important;
			}

			.no-stack .col.num4 {
				width: 33% !important;
			}

			.no-stack .col.num3 {
				width: 25% !important;
			}

			.no-stack .col.num6 {
				width: 50% !important;
			}

			.no-stack .col.num9 {
				width: 75% !important;
			}

			.video-block {
				max-width: none !important;
			}

			.mobile_hide {
				min-height: 0px;
				max-height: 0px;
				max-width: 0px;
				display: none;
				overflow: hidden;
				font-size: 0px;
			}

			.desktop_hide {
				display: block !important;
				max-height: none !important;
			}
		}
	</style>
</head>
<body class="clean-body" style="margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #FFFFFF;">
<!--[if IE]><div class="ie-browser"><![endif]-->
<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="table-layout: fixed; vertical-align: top; min-width: 320px; Margin: 0 auto; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #FFFFFF; width: 100%;" valign="top" width="100%">
<tbody>
<tr style="vertical-align: top;" valign="top">
<td style="word-break: break-word; vertical-align: top;" valign="top">
<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="background-color:#FFFFFF"><![endif]-->
<div style="background-color:transparent;">
<div class="block-grid" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:500px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
<!--[if (mso)|(IE)]><td align="center" width="500" style="background-color:transparent;width:500px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num12" style="min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<div align="center" class="img-container center autowidth fullwidth" style="padding-right: 0px;padding-left: 0px;">
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr style="line-height:0px"><td style="padding-right: 0px;padding-left: 0px;" align="center"><![endif]--><img align="center" alt="Image" border="0" class="center autowidth fullwidth" src="data:image/png;base64,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" style="text-decoration: none; -ms-interpolation-mode: bicubic; border: 0; height: auto; width: 100%; max-width: 500px; display: block;" title="Image" width="500"/>
<!--[if mso]></td></tr></table><![endif]-->
</div>
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
</div>
</div>
</div>
<div style="background-color:transparent;">
<div class="block-grid" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:500px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
<!--[if (mso)|(IE)]><td align="center" width="500" style="background-color:transparent;width:500px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num12" style="min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<div align="center" class="img-container center autowidth" style="padding-right: 0px;padding-left: 0px;">
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr style="line-height:0px"><td style="padding-right: 0px;padding-left: 0px;" align="center"><![endif]-->
<div style="font-size:1px;line-height:30px"> </div><img align="center" alt="Image" border="0" class="center autowidth" src="data:image/png;base64,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" style="text-decoration: none; -ms-interpolation-mode: bicubic; border: 0; height: auto; width: 100%; max-width: 68px; display: block;" title="Image" width="68"/>
<!--[if mso]></td></tr></table><![endif]-->
</div>
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
</div>
</div>
</div>
<div style="background-color:transparent;">
<div class="block-grid" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:500px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
<!--[if (mso)|(IE)]><td align="center" width="500" style="background-color:transparent;width:500px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num12" style="min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
<div style="color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
<p style="font-size: 28px; line-height: 1.2; text-align: center; color: #555555; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; mso-line-height-alt: 34px; margin: 0;"><span style="font-size: 28px;"><strong><span style="font-size: 28px;">'.$subject.'</span></strong></span></p>
</div>
<!--[if mso]></td></tr></table><![endif]-->
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
</div>
</div>
</div>
<div style="background-color:transparent;">
<div class="block-grid" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:transparent;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:500px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
<!--[if (mso)|(IE)]><td align="center" width="500" style="background-color:transparent;width:500px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num12" style="min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
<div style="color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
<div style="font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; font-size: 12px; line-height: 1.2; color: #555555; mso-line-height-alt: 14px;">
'.$content.'
</div>
</div>
<!--[if mso]></td></tr></table><![endif]-->
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
</div>
</div>
</div>
<div style="background-color:#F8F8F8;">
<div class="block-grid" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F8F8F8;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:500px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
<!--[if (mso)|(IE)]><td align="center" width="500" style="background-color:transparent;width:500px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num12" style="min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<div align="center" class="img-container center fixedwidth" style="padding-right: 0px;padding-left: 0px;">
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr style="line-height:0px"><td style="padding-right: 0px;padding-left: 0px;" align="center"><![endif]-->
<div style="font-size:1px;line-height:20px"> </div><img align="center" alt="Image" border="0" class="center fixedwidth" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPoAAABTCAYAAAC2/xobAAABhWlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AYht+2in8VBzOIOGSogmBBVMRRq1CECqFWaNXB5NI/aNKQpLg4Cq4FB38Wqw4uzro6uAqC4A+Ii6uToouU+F1SaBHjHcc9vPe9L3ffAcFaiWlW2zig6baZjMfEdGZV7HhFD00BXRiVmWXMSVICvuPrHgG+30V5ln/dn6NXzVoMCIjEs8wwbeIN4ulN2+C8TyywgqwSnxOPmXRB4keuKx6/cc67HOSZgplKzhMLxGK+hZUWZgVTI54ijqiaTvnBtMcq5y3OWqnCGvfkLwxn9ZVlrtMaQhyLWIIEEQoqKKIEG1HadVIsJOk85uMfdP0SuRRyFcHIsYAyNMiuH/wPfvfWyk1OeEnhGND+4jgfw0DHLlCvOs73sePUT4DQM3ClN/3lGjDzSXq1qUWOgL5t4OK6qSl7wOUOMPBkyKbsSiFawVwOeD+jb8oA/bdA95rXt8Y5Th+AFPUqcQMcHAIjecpe93l3Z2vf/q1p9O8HTYBymM0o/CoAAAAGYktHRAD/AP8A/6C9p5MAAAAJcEhZcwAADdcAAA3XAUIom3gAAAAHdElNRQfjCwsUHzl1pfimAAAgAElEQVR42u2deVgUx/b3v7MBYRFZFZRFQMEoagRXUK+KmrhmUVRQE9RLEtFwE69Kkl8UsrrFPXFDRZBxJSh4NUoQQVlEXEAhguJGhOjAKCCLMMx5/zDTL80Mw0BYTX+epx+dorq6urpP16mqU+fwoCFhYWGOQqFwNBENBmAPoBsAYwC6ALTBwcHRUrwAUA5ACuARgFwiShEIBOdnzZqVo0kBPHV/JCLewYMHZwHwBzCk7t+fP3+O8vJylJWVcY+Cg6MF4PP5eO211yAQCKCnpwcdHZ26WZIBbJ09e/YhHo9HjRb0Q4cO9ZXL5TsBDAeAW7du4cqVK8jOzkZRURFKSkpQU1PDPQkOjlZEX18fXbt2hZOTE1xcXODo6Kj4UxIAXy8vr0yNBV0sFr8J4DCAThkZGRCLxXj48CHXyhwc7Qxra2t4eXmhX79+AFBGRN7e3t4nGhT08PDwd3k83hGZTCbYu3cvzp8/z7UmB0c7Z/To0fDx8YFQKKwhIk9vb+9f6hX0gwcPusrl8niZTKa7bt063Lx5k2tBDo4OQt++fbFs2TIIhcJyACO9vb2vMGN9xX/i4uKERBTM4/F0d+3axQk5B0cH4+bNmwgODgaPx9Pl8XghO3fuFCkJen5+/kcA+qekpCAxMZFrNQ6ODsiFCxdw6dIlAOhrYGDwEUvQiYjH4/GW1NTUQCwWc63FwdGBOXDggGJFzD8wMJDPCLpYLP4XgF5paWkoLCzkWoqDowNTVFSEq1evAoB9r169RjCCzuPxxgJAamoq10ocHK8AtWR5TO0x+nAAyM7O5lqIg+MV4Pfff8dfw3K32oLes7y8HFKplGshDo5XAKlUiurqavB4PHsA4MfFxQkBWEokEq51ODheIYqLiwGg+5EjRwT8/Px8AwB8bmMKB8crKejCsrIyAz6fz9cHgBcvXnAtw8HxClFSUgIA0NPT68Tn8XhaAFBdXc21DAfHK4RMJgMAVFZWCvgABAAgl8u5lnnFGTNmDFauXFl7a2OrYmlpiRUrVmDixIkdor3Gjh2LFStWwMbGpkM+b6KX29O1tLT4/Orqal7txI6ASCTC9OnTsX//fpw6dapVrmllZYXc3Fyl48aNG9iyZQvs7e3bfbuNHz8eQUFB6N27d5tc38bGBqtXr8b06dPbVbu88cYbcHZ2VkqfPHkyVq9eDQcHhw4t6NXV1Tx+R6v8rFmzcP/+fRw9ehTz5s1rtYcgEolgZ2eHbt26MWlCoRC9e/fGkiVLkJmZCW9vb05t6IAkJCS0WofRFoIuEon4HUbQRSIRDhw4gIMHD8LS0pJJ/+OPP1q1Hunp6bC3t4e9vT1sbGzQpUsXbNiwAVpaWggJCYGbmxsnORxcj94U+Hw+Dh8+rLLH/GunTptRVFSEpUuX4vvvv4dQKMQPP/yglGfevHm4fv06qqqqUFhYiD179rA0Ay8vL+Tm5mLBggVMmq6uLrKzs5GRkQGBQMCkf/rpp8jNzcX48eNZ506fPh1BQUEoLi4GEeHWrVt48803NboHCwsL7Nq1CxKJBFVVVUhPT4ePj4/K57B06VLcunULL168gFQqxenTpzFgwAClvCNGjEBiYiIqKyvx+PFjfPfddxAKhRq3K4/Hg5+fH7KyslBdXY2CggJs3boVJiYmrHy+vr7Izc3FW2+9hXXr1uH58+cgImRkZGDUqFFqrzF79mykpaVBV1cX5ubmSEtLQ0xMjFI+oVCItWvXori4GBUVFUhKSsLQoUOV8jk6OiIiIgKlpaWoqalBRkYG5syZ0z6EKCwszFEsFtOSJUsIQLs8li9fTvXh4uLSKnWws7MjIqJLly6p/Lu+vj49f/6c5HI5GRsbM+nr1q0jIqLHjx/TsWPHKDExkYiI8vPzydbWlgBQr169iIjo8OHDzHlvvvkmc4+DBw9m0mNiYkgmkzHX+Oijj4iIKDk5mYqKiujkyZOUmZlJRESVlZXk6OjInLt69WoiInr77beZNCsrK8rLy2PKOHr0KBUUFBAR0aZNm1j3qLiXrKws2rVrF0VHR5NMJiOJREJdunRh8rm4uFBFRQUREWVmZtLp06fp6dOnlJCQQEREe/fubbC99+/fT0REeXl5dOTIEUpLSyMiojt37pCpqanSu5GcnExPnjyh6Ohoys7OJiKi0tJSsrKyqvca7777LtOelZWVFBMTQ8eOHWP+vnHjRiIiSkhIoPz8fDp58iTduXOHiIgKCwtZz9nZ2ZmePn1KMpmMIiIiaPfu3SSRSIiI6OOPP24TuVmyZAmJxWIKCwtzbPeC3rVrV6qsrFQp5HFxca1Wj4YEHQBdvnyZiIj69u1LAGjo0KEkl8vpzp07ZGZmpvRyRkVFMWn37t2j/Px85vf69eupqKiIKisr6f/+7/8IAAkEAiopKaGUlBQmn0LQ7927R+bm5gSA+Hw+/fLLL0REtHLlSrWCHhERQUTEXAMAmZiY0K1bt4iIaMSIEUz62rVracOGDSQSiZi0NWvWEBHRnDlzmLSTJ08SEdGPP/5If3kmJUtLS7p7965Ggj5lyhQiIrpy5Qp16tRJ6UMTHBys1JaZmZlkZGREAEgoFFJMTAwREfn7+zf4bEtLSykvL08pXSHoycnJpK+vTwBIJBJRXFwcERHNnz+fyXvhwgWSy+U0adIkJs3S0pIkEgkVFhaStrZ2mwp6u1fd/fz8oK2t7Db+xYsXWLx4cbuqq8IrrsL4aObMmeDxeNi6dStqmxhv3LgRUqkUb731Fjp16gQAOHv2LCwsLGBnZ8fMkEdFReHChQvw8PAAAAwYMAAGBgY4c+aM0rVDQkLw5MkTAC+XSiMjI5khQH3o6elh8uTJKC0txbp161jDkc2bNwMAPD09mfTly5fjs88+Y2wuBAIB7t27BwDo3r07k2/YsGGorKzEqlWrmHFifn4+1q9fr/GEKwD8+OOPjNEHAHzzzTeQyWSYMWMGeDy2u8Pg4GA8ffoUwMv14xMnTjR4/5qydetWPH/+XDHexS+/vHTHZmZmxvzr7u6O7OxsJCUlwcjICEZGRqioqMDp06dhYmKCN954o22Hv+1d0KdMmaJykmHx4sXIzMxsV3U1MDAAAGZPf5cuXQAAOTlsH/vV1dXIzc2FUCiEqakpI+gA4O7uji5duqBv3744c+YMzpw5g2HDhkFfXx/u7u4AoHIc+Ze5I0NpaSlrQkYVRkZG0NLSwv3795UsIxW7n7p27cqkWVtbY8eOHbh58yZKS0shk8mwfft2ZkwNAIaGhjA2NsaDBw8Y4VCQkZGhUTsq2q3ubsqSkhI8evQInTp1gp6enkb33xzU3eyl+Pgo5k6MjIwAAE5OTpBKpaxj7ty5AMCaQG4LhO1ZyPX19ZXWNxU9eXBwcLuqq5OTE5ycnHD37l2mZ1H08K+99prK3lQh9AAQGxsLmUwGNzc3yOVyEBFiY2PRpUsXrFu3DiNHjoSbmxtKSkqabQJSk/oprKsMDQ1x4cIFdOnSBfv27cP27dshlUrh6uqKzz77jDmvoqICNTU1astsjnq1J0tOxUcuOzsbX331lco8ly9f5gS9PgwNDcH/awVQJpPh9OnTWLZsWbvbN29oaIh9+/aBz+cjLCyMSb9x4wYAYOTIkYy6p/i6Ozg4QCqV4s8//wQAPHv2DKmpqXB3d4eOjg4uX74MiUQCiUSCvLw8jBs3Dm5uboiNjW22l/zJkyd48uQJbG1tYW1tzfLdr5ixVmhN7u7usLa2xubNm/Gf//yHyVfXjqGqqgp5eXmwtraGo6Mj61kphiANcePGDYwfPx4jR47ExYsXmfTXX38dpqamuHPnTrPvzai9stFY8vLyUFVVBSMjI5w8eRIVFRXtb+WqPQt6RUUFPv30U3h6esLCwgJTp05tcyE3NDSEh4cHPDw8MG7cOCxduhTp6ekYOnQosrOzWePQ0NBQSKVS+Pr6Yvbs2dDR0YGtrS1CQ0OhpaWFbdu2sYT2zJkz6N27N9555x1GlVeo9T4+PrC0tFSptv+dHn3Lli0QCoUIDQ2FnZ0dtLW14enpCT8/Pzx79gz79u1jPkQAMGjQIBgaGgIAhg8fjk8//VSp3IiICPD5fISHh2PAgAEwMjLC/PnzNZ5T2blzJyoqKrBs2TJMmTIFIpEITk5OCAkJAQBs2rSpWZ/ps2fPYG5ujjFjxqBz586NPr+8vBz79++Hubk5goODmfbp06cPHjx4gCdPnkBfX/+f1aPz+XyMHj0aEydOxMCBA9G1a1fIZDL8/vvvOHXqFI4fP868VFKplPVQBQIBRo0ahalTp8LDwwM9e/aEUCjEw4cPkZOTg9TUVOzdu5eZIGoJHB0dVQrbqVOnsGDBAta49M8//8SMGTMQEREBsVgMImLUvEOHDuHbb79llXH27FkEBQXBwMAAv/76K+sDoFhjr/0BaA7WrFkDJycnzJkzB7m5uUwdi4uL4enpiUePHgEAUlJSEBcXh9GjR0MikaC8vBy6uroIDQ1lrf8rJs3GjRsHFxcXXLt2jdHIvvrqK5V2BnW5ffs2vL29ceDAAURFRbHa7aeffsLPP//crG1w4sQJ+Pn5ITY2FhKJBObm5o0u47///S+cnJzg5eWFWbNmobCwEGZmZqiqqsL06dOV5iteaUGfOXMmvv32W5Vmq3379sWMGTOwY8cOREVFISQkBAkJCXj+/Dn69OmDOXPm4P3334eFhYXSuba2trC1tcX48ePxxRdfICoqCosWLUJBQUGzTsgEBAQopUskEly8eFFpwk3BuXPn4OjoiPfffx/9+vWDVCpFVFQUYmNjVY7jVqxYAYDtv+/s2bMICAhAZWUlcnNzWeekpKQgICCApeIqVO6AgACkpKQwaTExMaioqGAm2hQCOHfuXOzbtw/Tpk2DsbExbt68iZCQEDx+/JjV+0+cOBEfffQR+vfvD6lUitDQUJSVleH27duIj49nTYwNHToU8+fPh6urK54+fYqwsDDcv38fRMQMadQRGRkJR0dHfPDBB3BycsLjx48RERGBpKQkVr74+HgEBAQgLS2NlX7lyhUEBASw6lUf/v7+iIuLg7OzMytg6MmTJ/Hnn38qaZGKshMSElgTdKNHj4anpyc8PDygq6uLhw8fIjg4GLdv3277eYSwsDBHgUBwKzk5GVu3bm2xSbX9+/fj3XffbbRqWVpa2iR1qqioCB988AFOnjwJDo5/IkuWLMGwYcNQU1Pj1OJjdAMDA8TGxjZayBWqelOEHABMTEwQERGBMWPGcE+c4x9Piwo6j8dDeHg4Bg8e3CY3p6Wlhc2bNzfKxpqDgxP0RjJ37lyVBi+txblz5zBixAhmLZiD459Ki3V12traSrPKrcnFixcxadIkVFZWck+Zg+vRW6rgWbNmwcrKqk1uSiqVYsaMGZyQc3C0dI8+e/bsNrupwMBAxuKsNXB3d2c5nJDL5diwYQNjyvlPpX///pg2bRqGDRsGKysrGBsbo6KiAhKJBOnp6Th//jwiIyO5D3JHFXQ+n69yY35r8PTpU+zevbtVr7l69WolzzLXrl3Db7/99o98qfr164dt27ZhxIgRKv9uZ2eHIUOGwNfXF4WFhfj666/x008/cQ5KO5rq3rNnT8YMsLWJjo5u1R7C1tYWw4cPV0pvN55FWhkvLy+kpaXVK+R1MTU1xZYtW3DixAmV25E52nGP3q9fvza7obqWUy3NvHnzlPZGA8D06dPh5+eHf1IEHA8PD4SGhrI2iBARUlJSkJqaioKCAujq6uL111/HhAkTmG29wEuPq3v27FH5gTQ1NWVZHl67dg1jx47lpLetBb32A2xtHjx40Oo9mIKKigpma6Wenh6mTp2KgwcP/iNeJIFAgJ07d7KEPCsrC3PmzGHs3eu+I2vWrMHHH3/MpHl7eyMkJERpyMPj8Zg93239fnGqey3aUgVrzQmwwYMHs4IhrFy5krV54Z+kvo8dO5bxjqOYKxk7dqxKIQdeOoZYtGiRkl+BDz/8kJPKjiLoIpGozW5I4d6nNagtyHK5HOHh4YiOjmbSxo8fz3hLedWpO09x+PBhjVY+Vq1axZqEGzlyJCeVHUV1b82lrbq4urpCLBa3fMMJhSx/anFxcSgoKMCRI0eYpUWhUIiZM2diy5YtassaNGgQ3nvvPea3WCxm3C5169YNs2fPRu/evdGpUydIJBJcv34dkZGRUBfq2sfHh6VtfPnll4y2M2TIELzzzjvo1q0bhEIh8vPzce7cOfz2229NduhQ2+UUoOx+qT7y8/Oxbt06lmouFAohk8ng7+8PCwsLJb9v3bt3x+rVq1kflfo0Bz09PYwbNw7/+te/YGlpCQMDA0gkEty7dw+nT59Gamqq2tn+KVOmsFZUfvjhBxQXF6N3797w8fGBtbU1Kioq8OGHH6KqqkppyOHq6oopU6agR48eMDMzg1QqRV5eHs6ePYv4+PjWs9psCS+ww4YNo7YiNzeX8TzaksfkyZNZ1/X19SUApK2tTc+ePWPSU1NTGyxr4cKFrLJmzpxJIpGI1q5dSzKZTOV9VlRUUFBQEMsja+3j1KlTrPwikYisrKwoPj6+3ra7f/8+TZ48uUntsX79elZZSUlJxOfz/1YbX7t2TaNnPnfuXKVzBQIBLV26lAoLC9Wee+3aNRo7dmy9ddi0aRMrv5WVFfn7+ys9Fz09PdZ5Q4YMoZSUFLXXvnXrFstrLDqaF9jWnhCrjZ2dHd56661WVdtlMhmOHz8O4KVPu6ioKFZv7eTk1LjxFJ+PyMhILFu2rF4XRzo6Oli5ciWOHj0KLS2tBsu0sbFBWlqaWtXYxsYGJ06cgK+vb6Pb48qVK6zfw4YNw5YtW5rFC2tj0dXVRWRkJNavX68U8KEuAwYMwJkzZ+Dv769R2WPGjMHGjRvVup7y9PREfHw8hgwZorYsR0dHREdHs3zudSjVPT8/H48ePWJFI2lN1q5di9jY2BaL+d6pUyfWZp3Y2FjG1bJClVR4/1TMJtfnNFAVy5YtY9wDl5aWIjY2FoWFhejRowdGjRrF2o03bdo0BAYG4osvvlBb5tGjRxnPKXfv3kVSUhLkcjkGDhyIvn37sj4yP//8MzIyMlhOKxoiKioKT548YXln8fPzw7Rp07B7925ERkZq5HCiNiEhIYzqvmTJEia9oKAAoaGhzO+bN2+yztuxY4fSZqorV67g7NmzKC4uho2NDaZNm8Z4ZhUIBNi0aRPy8vJYvv1UsXz5cmY5NT8/H8XFxejRowdrgvbAgQOsearbt2/j2LFjePDgAczMzPD222/DxcWFUe/Xr1+P7Oxs/O9//+tYqjsAOnToELUlu3btajGVyMfHh3UtHx8f1t9FIhEVFRWxVGJ1w4m6qruCsLAwVgADANSjRw9KSkpi5auurqaePXuqVd2JiF68eEELFixQqsvEiRNJKpWy8l69erXR7fLOO++QXC6v95k8fPiQwsLCaOHChdSjRw+NyzUzM2OVo244VHdIVV1dTfPmzVPKp6urSyEhIay8RUVFZGBgoFZ1JyJKT08nV1dXJg+PxyMej0d8Pp+ysrJYeb/99lsSCoWsMnk8Hi1evJhqampYbVPfMAztOVKLn58ftTVbtmwhgUDQ7IIeGxvLXKOqqooVmkdx7Nmzh1UXd3f3Rgl6dHR0vWNcAwMDun37Niv/hg0bGhT02pFF6h4jRoxgvXhERG5ubo1umxkzZlBxcbFGzycnJ4cCAwPVhk1qrKBfuHCBlXf58uX15uXz+XTx4kVW/s8++0ytoD969IgVEgoqIswo2LNnj9r7UkSeUTB79uyOJ+jdunVTenHagoSEBOrTp0+z3ZelpSVrIiY6OlplvgkTJrDqsWPHDo0Fvaamhuzs7NTWw9PTk3XOnTt31Ap6Wlpao7WwNWvWNDmM1ubNm1mTkuqorKyktWvX1hu2SFNBt7CwYOUrLCwkLS0ttXX18PBQmkRUJ+hffPFFvWWFhYWxnmG3bt3UXtvU1JT1LoWFhXW8kEyPHj1iOc9rK0aMGIGMjAzY29s32yRc7YmYw4cPq8xXd9zu6empsSFRcnIy7t69qzbP8ePHWea19vb2ar2XhoeHN3jdAwcOKE2oNXV5VbE0NmnSJGzduhXp6en1LmNpa2tj2bJliI2N/VuTd3Xt60+dOqW05FWXc+fOscI+ubq6qq1Denp6vX+rPdF5584dxoNufRQWFuL+/fvM75bcCNaiHmbqvjhtxfXr15W8pzbHbHtlZSXLQKY2tWfigZdheyZNmqTRNTSJ6lFVVaUU4sjGxuZvlVnXk6q68jShoqICp06dwieffIIBAwbAxMQEU6dOxe7du1kfQQVubm748ccfm3w9W1tb1m9NQkDJ5XJWaC+RSKR2Erm+EFcikYjlf8HGxga5ubkNHrXPUeXhuF3PutcW9G+++aZFb0ATmmvbav/+/Vkhovh8Pq5evap2dr7uR6KhWV0AKoVAFbXdMQOAsbHx3ypTIpGwfKirK68pPHv2DNHR0YiOjsaiRYuwePFiBAUFsdppwYIFWLVqlcZtUJu6S2mK0FgNUVRUxPptamraaBfNxsbGrM1N2traLJNgTdDT04OWllaDWki7E/QXL15g27Zt+O6779pMyJ89e9ZslnJ1bde1tLQa9TAnTZoEExMTpRdL6aFo6MyyrqmxulBNmpQpEAhYL2tLxjeTyWTYtGkTUlJScPHiRWY4JBKJMHLkSBw7dqxJZbLUVb5mCmvdtmnKfdft6SsrK5Gfn994FZvfMkp2i7tH/emnn/DJJ5+0mc13YGAgawzW5DEOn8+E820qWlpamD59Onbu3Kk2n6b2B3XzqevBunXrhqysrGYrr7lISUnBuXPnMG7cuEbff0M9s6bvXN18DX2IVSGVSlna0NWrV5WckbQlLS7oxcXFWLFiBRM3qzXJyspqtvA9Y8aMYcUAv3z5MiumeH307dsXK1euZGkFDQm6Jk4bDA0N0adPH+Z3TU0N7ty5o7bMhuK2KcIyK6gv+kxdXFxccOTIEeb3pUuXWNt3G6Lu3oimOg6pHYFGMbHWEIr98QpKS0uRl5fXJG3i3r17jIbX1mGSW13QgZfBBn18fJgIna0BEcHPz6/Z1M+6antwcDCOHj3a4HlRUVHw9/dnPO64ubnBzs5O7az666+/Djc3NyQmJtabx8fHh6W6K2KW18e8efPw3XffqbUW/Pe//836nZycrFHbPHz4ED169GB6s+7duytFZ60PHo+HQYMGsdJqz0Q3hsTERMhkMkYVnzBhQoNDpffee4+1GqIooynEx8czgm5ra4uBAweqncNRfIAV1y8pKWGF4mpO+K0ldHPmzGmSStRUNm/ejPPnzzdLWbq6uqxIM3Vn1Buap6ht+87j8TTq7bZv3640mafAwcEBq1atYqUdOnRIbXk2Njb4/vvv1Qp5bU2CiBosU4Ei/lztIUp4eLhGDiKWLVvG2gtQWlqqFC+tro8BU1NTlWWVlJQgMjKS+a2jo8OKbluXzp074+uvv2al7d+/v8nvSd1Vpm3btqndhzBp0iQkJCQgJiYGMTExLCcczU5LGcyoOt5+++1WMaJJT08nHR2dZqu3l5cXq/xff/31b+10y8nJ0cgE9urVqzRs2DDWjqz33nuP8vPzWfmePXtGZmZmDVrGERHt3r2bLC0tmXydO3emoKAgpd1YUVFRjbrHSZMmKV0rOzubZsyYodJoxcHBgfbu3at0zg8//KCUVyQSKdWvvl1fb7zxBlVXV7Py7ty5kwwNDVn5nJ2d6erVq6x8WVlZSuaqdQ1mJk6cqLYdEhMTWfnj4uJUGj9NnTpVyYKwKZaIaGvLuPqORYsWtaiQP3r0iKytrZu1znWFRp0pqaqjru07EdGgQYPqFfSoqCiqqqpifhcUFFB6ejo9ffpU5T0vXLiwwTr/8ssvzP9lMhnl5ORQVlYW6zoKiouLycbGptHttH37dpX1Ky0tpaSkJDp+/DidOnWKcnJyVNrEX716lV577TWVZdcVSrlcTunp6XTx4kWaMGECK+/nn3+uclvvhQsX6Pjx45Senq7y7/37929wm2pDgm5vb69kEVhdXU3x8fG0d+9eCg8Ppxs3brTK3ow2FXQA9PXXX6sVVplMRnFxcbR27VpasWIFffnll7R37176448/GhRyZ2fnZq2rubk5q4eoz7a9oWPfvn2sum7evLleQV+xYgUtWbJEI+0nMDBQo4+Tk5MTnTlzpsHyiouL1drlqzv4fD5t3LhR7caW+oiPj1fbrvPnz6/3XFU24v7+/hprj0VFRTRq1CiN9qM3JOgAyNXVVUnrUkdISEiDprodUtAB0Lvvvkt3795l3XBmZiYtXbqULCwsVJ4jEAhowoQJJBaLqby8nDmvvLyc9uzZQ126dGn2evr7+7PqePLkySaV8+abb7LKefLkCbNbSZWgK87JyclR+XJkZWWpfenqCrqDgwMJhUIKCgpSuelEJpNRZGRko3aVQc0GmZiYGI0ELSMjg3x8fDRyFvL5559TSUmJRoIOgIYPH07nzp2rtx5lZWX0888/1/u+NVXQ8Zd9/saNG6m0tLTee09JSaGpU6e2iuOJVomPrm5tuk+fPujcuTMyMzM1dj+kmPBxcHAAn89Hbm4uKioqWqSOzs7OrHXWu3fvNmiHrgqRSKS06pCcnIyysjIsXLiQZb0XEBCANWvWMJN3w4cPh6OjI0xMTPD48WOkp6ertbkGXtp513bA0bNnT2b5TVdXFx4eHrC2toZAIEB+fj4SEhKULO3+LtbW1hg5ciQGDhwIU1NT6Ovro7y8HE+fPmX2uzd2j7qenh5cXFxgaWkJkUgEiUSC1NRUte+Oubk5Ro0aBUtLS+jr60MqlSInJweJiYkNLuU5OjqyzFSvX7+OwsLCRr2niudnZmaGyspK/PHHH7h06RLu3bvXovL1ySefYOjQoeDz+W3Xo3MH1KruzT2v4ODgwM2o2TYAAAKPSURBVLX1P+zw9/dX9OgO/L/UR5VBCDg4ODouCpkWiUTEB1CjUKM5ODhePUGvqqqS84moSjGW4ODgeHVQWAjq6OjU8OVy+XOAC3PDwfGqobCsLCsrKxFaWlqWFhQUyA0NDTndvY1ITU1FQEAA87s5THf37dvHMiVtTfNjjvbBX/sravT09Ep5ACAWi/Oqq6u7v//++1zrcHC8IuPzkJAQiESi+15eXj0UvfhtkUjECovDwcHRcTEyMoJIJAIR5QL/f/daEoBGRxTh4OBon/Tu3RsAwOfzkxhBJ6JYAA2GkOHg4OgYDB48GAAgl8vjGEH38vI6D+C2i4tLvXt9OTg4OgbGxsYYOHAgAOTevn07nhH0v0LKbBMIBI1yAcTBwdH+mDt3rsLR55bAwEB57TE6unbt+jOA9KFDh7Yrp3YcHBya4+bmphiC3ygpKdmuSGcEffTo0TIiWkBE5b6+vqwImxwcHO0fZ2dn+Pr6gojK+Xz+Bx9++CHjMFFpJ8vBgwffIaKjMplMsG/fPsTFxXEtyMHRzhkzZgw++OADCIXCGgDTvby8WE4NVW5ZCw8Pn8Dj8Y4A6HTjxg2IxWI8ePCAa00OjnaGjY0NvLy8FBGEngPw8vLyUooTVu/eVLFY3IfH4+0kIjfgpY/vtLQ03Lp1C4WFhSgtLVXyzsnBwdFyCAQCGBgYwNTUFE5OTnB1dUWvXr1eCjKPl0hEH3p5eWWqOlftJnQi4onF4pk8Hu8TAEqhNUtKSlBWVtZkh/scHBwNo6OjA319/fo2niUR0VYvL6/Df7njQqMFvTaHDh3qRURjAAwhInsej2dBRCYA9ABwe1w5OFqOKgDPeTyelIjyAeQCuERE57y9vTWKBvn/AGEnljynz1K9AAAAAElFTkSuQmCC" style="text-decoration: none; -ms-interpolation-mode: bicubic; border: 0; height: auto; width: 100%; max-width: 175px; display: block;" title="Image" width="175"/>
<div style="font-size:1px;line-height:20px"> </div>
<!--[if mso]></td></tr></table><![endif]-->
</div>
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
</div>
</div>
</div>
<div style="background-color:#F8F8F8;">
<div class="block-grid three-up" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F8F8F8;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:500px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
<!--[if (mso)|(IE)]><td align="center" width="166" style="background-color:transparent;width:166px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num4" style="max-width: 320px; min-width: 166px; display: table-cell; vertical-align: top; width: 166px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
<div style="color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
<p style="font-size: 12px; line-height: 1.2; text-align: center; color: #555555; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; mso-line-height-alt: 14px; margin: 0;"><span style="color: #999999; font-size: 12px;">Terms and conditions</span></p>
</div>
<!--[if mso]></td></tr></table><![endif]-->
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td><td align="center" width="166" style="background-color:transparent;width:166px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num4" style="max-width: 320px; min-width: 166px; display: table-cell; vertical-align: top; width: 166px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
<div style="color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
<div style="font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; font-size: 12px; line-height: 1.2; color: #555555; mso-line-height-alt: 14px;">
<p style="font-size: 12px; line-height: 1.2; text-align: center; mso-line-height-alt: 14px; margin: 0;"><span style="color: #999999; font-size: 12px;">Privacy Policy</span></p>
</div>
</div>
<!--[if mso]></td></tr></table><![endif]-->
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td><td align="center" width="166" style="background-color:transparent;width:166px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num4" style="max-width: 320px; min-width: 166px; display: table-cell; vertical-align: top; width: 166px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
<div style="color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
<div style="font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; font-size: 12px; line-height: 1.2; color: #555555; mso-line-height-alt: 14px;">
<p style="font-size: 12px; line-height: 1.2; text-align: center; mso-line-height-alt: 14px; margin: 0;"><span style="color: #999999;">Contact Us</span></p>
</div>
</div>
<!--[if mso]></td></tr></table><![endif]-->
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
</div>
</div>
</div>
<div style="background-color:#F8F8F8;">
<div class="block-grid" style="Margin: 0 auto; min-width: 320px; max-width: 500px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;">
<div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
<!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F8F8F8;"><tr><td align="center"><table cellpadding="0" cellspacing="0" border="0" style="width:500px"><tr class="layout-full-width" style="background-color:transparent"><![endif]-->
<!--[if (mso)|(IE)]><td align="center" width="500" style="background-color:transparent;width:500px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;"><![endif]-->
<div class="col num12" style="min-width: 320px; max-width: 500px; display: table-cell; vertical-align: top; width: 500px;">
<div style="width:100% !important;">
<!--[if (!mso)&(!IE)]><!-->
<div style="border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;">
<!--<![endif]-->
<!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px; font-family: Arial, sans-serif"><![endif]-->
<div style="color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:1.2;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;">
<div style="font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; font-size: 12px; line-height: 1.2; color: #555555; mso-line-height-alt: 14px;">
<p style="font-size: 12px; line-height: 1.2; text-align: center; mso-line-height-alt: 14px; margin: 0;"><span style="color: #bababa; font-size: 12px;"><span style="color: #999999;">ATB All rights reserved</span></span></p>
</div>
</div>
<!--[if mso]></td></tr></table><![endif]-->
<!--[if (!mso)&(!IE)]><!-->
</div>
<!--<![endif]-->
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
</div>
</div>
</div>
<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
</td>
</tr>
</tbody>
</table>
<!--[if (IE)]></div><![endif]-->
</body>
</html>';

$result = $this->email
	->from('noreply@myatb.co.uk')
		->to($email)
			->subject($subject)
				->message($body)
					->send();
		
	}
    

}
