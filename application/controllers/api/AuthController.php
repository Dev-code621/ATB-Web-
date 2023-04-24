<?php

/**
* Created by PhpStorm.
* User: zeus
* Date: 2019/7/10
* Time: 2:13 PM
*/
class AuthController extends MY_Controller
{

    public function isUserNameUsed(){
        $existUser = $this->User_model->getOnlyUser(array('user_name' => $this->input->post('user_name')));
        if(count($existUser) == 0) {
            $retVal[self::RESULT_FIELD_NAME] = true;
        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "The username was already taken.";
        }
        echo json_encode($retVal);
    }
    
    public function isEmailUsed() {
        $email = strtolower($this->input->post('email'));
        $existingUser = $this->User_model->getOnlyUser(array('user_email' => $email));
        
        if (count($existingUser) == 0) {
            $retVal[self::RESULT_FIELD_NAME] = true;
        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "The email was already used.";
        }
        echo json_encode($retVal);
    }
	
	public function register_stage_one(){
		$email = strtolower($this->input->post('email'));
		$retVal = array();
		$existUser = $this->User_model->getOnlyUser(array('user_email' => $email));
		if(count($existUser) == 0) {            
        $insertURL = array(
            'user_email' => $email,
            'user_password' => self::GetMd5($this->input->post('pwd')),
            'facebook_token' => $this->input->post('fbToken'),
            'status' => 3,
            'updated_at' => time(),
            'created_at' => time()
        );

        $insResult = $this->User_model->insertNewUser($insertURL);

        if ($insResult[self::RESULT_FIELD_NAME]) {
            $token = $this->generateToken($insResult[MY_Controller::MESSAGE_FIELD_NAME]);

            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::MESSAGE_FIELD_NAME] = $token;

        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "Database Error";
        }
		}
		else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Email is already used";
		}
		echo json_encode($retVal);
	}
	
	public function register() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
        $retVal = array();
        if($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));
			$user = $users[0];
				
			$pic = $this->fileUpload('profile_photos', 'profile_' . time(), 'pic');
			
			/*require_once('application/libraries/stripe-php/init.php');
                \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));
                $customerObj = \Stripe\Customer::create([
                    "email" => $user['user_email'],
                    "name" => $this->input->post('user_name') // obtained with Stripe.js
                ]);
                $customerToken = "";
                if(!empty($customerObj)) {
                    $customerToken = $customerObj->id;
                }*/
				
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
			$invite_code = ''; 

			for ($i = 0; $i < 7; $i++) { 
				$index = rand(0, strlen($characters) - 1); 
				$invite_code .= $characters[$index]; 
			} 
			
			$whereArray = array('id' => $verifyTokenResult['id']);
			
			$setArray = array(
				'user_name' => $this->input->post('user_name'),
				'pic_url' => $pic,
				'first_name' => $this->input->post('first_name'),
				'last_name' => $this->input->post('last_name'),
				'country' => $this->input->post('location'),
				'latitude' => $this->input->post('lat'),
				'longitude' => $this->input->post('lng'),
				'post_search_region' => $this->input->post('range'),                     
				'birthday' => $this->input->post('dob'),
				'description' => $this->input->post('bio'),
				'gender' => $this->input->post('gender'),
				//'stripe_customer_token' => $customerToken,
				'invite_code' => $invite_code,
				'complete' => 1,
				'updated_at' => time(),
				'created_at' => time()
			);
			
			$this->User_model->updateUserRecord($setArray, $whereArray);
			
			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));
			$user = $users[0];
			
			$subject = 'Welcome to ATB';
      $deeplink = $this->generateDeepLink(4,0);
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
      <meta http-equiv="Content-type" content="text/html; charset=utf-8">
      <meta name="vi	ewport" content="width=device-width, initial-scale=1, maximum-scale=1">
      <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE">
      <meta name="format-detection" content="date=no">
      <meta name="format-detection" content="address=no">
      <meta name="format-detection" content="telephone=no">
      <meta name="x-apple-disable-message-reformatting">
       <!--[if !mso]><!-->
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet">
      <!--<![endif]-->
      <title>*|MC:SUBJECT|*</title>
      
      
      
      
      <style type="text/css">
          body{
            padding:0 !important;
            margin:0 !important;
            display:block !important;
            min-width:100% !important;
            width:100% !important;
            background:#F8F8F8;
            -webkit-text-size-adjust:none;
          }
          p{
            padding:0 !important;
            margin:0 !important;
          }
          table{
            border-spacing:0 !important;
            border-collapse:collapse !important;
            table-layout:fixed !important;
          }
          .container{
            width:100%;
            max-width:650px;
          }
          .ExternalClass{
            width:100%;
          }
          .ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div{
            line-height:100%;
          }
        @media screen and (max-width: 650px){
          .wrapper{
            padding:0 !important;
          }
      
      }	@media screen and (max-width: 650px){
          .container{
            width:100% !important;
            min-width:100% !important;
          }
      
      }	@media screen and (max-width: 650px){
          .border{
            display:none !important;
          }
      
      }	@media screen and (max-width: 650px){
          .content{
            padding:0 20px 50px !important;
          }
      
      }	@media screen and (max-width: 650px){
          .box1{
            padding:55px 40px 50px !important;
          }
      
      }	@media screen and (max-width: 650px){
          .social-btn{
            height:35px;
            width:auto;
          }
      
      }	@media screen and (max-width: 650px){
          .bottomNav a{
            font-size:12px !important;
            line-height:16px !important;
          }
      
      }	@media screen and (max-width: 650px){
          .spacer{
            height:61px !important;
          }
      
      }</style></head>
      
      <body style="background-color: #A6BFDE; padding: 0 50px 50px; margin:0">
      <span style="height: 0; width: 0; line-height: 0pt; opacity: 0; display: none;">We hope you enjoy using the app!</span>
      
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
                                <a href="#" target="_blank"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/54ceac44-def2-2844-d6aa-fb487a2091d6.png" width="153" height="47" border="0" alt=""></a>
                              </td>
                            </tr>
                          </table>
      
                          <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td valign="bottom">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <td height="98">
                                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr><td height="38" style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">&nbsp;</td></tr>
                                        <tr><td bgcolor="#F8F8F8" height="60" class="spacer" style="font-size:0pt; line-height:0pt;width:100%; min-width:100%;border-radius:5px 0 0 0;">&nbsp;</td></tr>
                                      </table>
                                    </td>
                                    <td width="98" height="98" bgcolor="#F8F8F8" style="border-radius: 50% 50% 0 0!important;max-height: 98px !important;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/1a40dacb-2152-c2c2-8b41-780608c34f92.png" width="98" height="98" border="0" alt="" style="border: 0 !important; outline:none; text-decoration: none;display:block;max-height: 98px !important;"></td>
                                    <td height="98">
                                      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:0pt; line-height:0pt; text-align:center; width:100%; min-width:100%;">
                                        <tr><td height="38" style="font-size:0pt; line-height:0pt; width:100%; min-width:100%;">&nbsp;</td></tr>
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
                                          <td><h1 mc:edit="w1" style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:31px; text-align:center; margin: 0;">Welcome to ATB</h1><br><h2 style="margin: 0; color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 300; font-size:20px; line-height:24px; text-align:center;" mc:edit="w0">We hope you enjoy using the app.</h2><br></td>
                                        </tr>
                                        <tr>
                                          <td mc:edit="w2" style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">Are you a small business? Click your profile picture located at the top right of the feed and look out for the <img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ce0429e3-ff9a-d51a-d8da-ffc431195b90.png" alt="briefcase" width="20px" style="vertical-align: middle; width: 15px;"> symbol briefcase to register as an ATB approved business!</td>
                                        </tr>
                                      </table>
                                    </td>
                                  </tr>
                                </table>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                                                              <tr>
                                                                  <td height="20"></td>
                                                                </tr>
                                  <tr>
                                    <td width="100%" style="padding: 0px 20px;">
                                      <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bottomNav">
                                        <tr><br></tr>
                                        <tr>
                                          <td colspan="3" mc:edit="w3"><a href="'.$deeplink.'" style="color: #ffffff; text-decoration: none; font-family: &#39;Roboto&#39;, Arial, sans-serif; font-size: 15px; width: 210px; padding-top: 15px; padding-bottom: 15px;display: block; text-align: center; background: #ABC1DE; border-radius: 5px; margin: auto;" target="_blank"><img height="20" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/dca874c4-ab1c-f4dd-c7e2-2cac15126445.png" style="vertical-align: middle; max-width: 20px;" width="20">&nbsp;&nbsp;Business Sign Up</a></td>
                                        </tr>
                                        <tr><td colspan="3" style="padding-top: 30px; padding-bottom: 10px"></td></tr>
                                        
                                        
                                      </table>
                                    </td>
                                  </tr>
                                </table>
                                                          <table bgcolor="#ffffff" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                              <tr>
                                                                  <td height="10"></td>
                                                                </tr>
                                                              <tr>
                                                                <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                                                                  <!--left-->
                                                                  <div style="display:inline-block;vertical-align:top;">
                                                                    <table align="center" border="0" cellspacing="0" cellpadding="0">
                                                                      <tr>
                                                                        <td width="200" align="center">
                                                                          <table bgcolor="#FFFFFF" align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                              <td align="center">
                                                                                <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                                  <tr>
                                                                                    <td height="10"></td>
                                                                                  </tr>
                                                                                  <tr>
                                                                                      <td align="center" mc:edit="info1"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color:#A2A2A2;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Terms and conditions</a> </td>
                                                                                  </tr>
                                                                                  <tr>
                                                                                    <td height="10"></td>
                                                                                  </tr>
                                                                                </table>
                                                                              </td>
                                                                            </tr>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </table>
                                                                  </div>
                                                                  <!--end left-->
                                                                  <!--[if (gte mso 9)|(IE)]>
                                                                  </td>
                                                                  <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                                                                  <![endif]-->
                                                                  <!--middle-->
                                                                  <div style="display:inline-block;vertical-align:top;">
                                                                    <table align="center" border="0" cellspacing="0" cellpadding="0">
                                                                      <tr>
                                                                        <td width="200" align="center">
                                                                          <table bgcolor="#FFFFFF" align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                              <td align="center">
                                                                                <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                                  <tr>
                                                                                    <td height="10"></td>
                                                                                  </tr>
                                                                                  <tr>
                                                                                      <td align="center" mc:edit="info2"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color:#A2A2A2;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Privacy Policy</a> </td>
                                                                                  </tr>
                                                                                  <tr>
                                                                                    <td height="10"></td>
                                                                                  </tr>
                                                                                </table>
                                                                              </td>
                                                                            </tr>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </table>
                                                                  </div>
                                                                  <!--end middle-->
                                                                  <!--[if (gte mso 9)|(IE)]>
                                                                  </td>
                                                                  <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                                                                  <![endif]-->
                                                                  <!--middle-->
                                                                  <!--right-->
                                                                  <div style="display:inline-block;vertical-align:top;">
                                                                    <table align="center" border="0" cellspacing="0" cellpadding="0">
                                                                      <tr>
                                                                        <td width="200" align="center">
                                                                          <table bgcolor="#FFFFFF" align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                              <td align="center">
                                                                                <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                                  <tr>
                                                                                    <td height="10"></td>
                                                                                  </tr>
                                                                                  <tr>
                                                                                      <td align="center" mc:edit="info3"><a href="mailto:help@myatb.co.uk" style="color:#A2A2A2;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Contact Us</a> </td>
                                                                                  </tr>
                                                                                  <tr>
                                                                                    <td height="10"></td>
                                                                                  </tr>
                                                                                </table>
                                                                              </td>
                                                                            </tr>
                                                                          </table>
                                                                        </td>
                                                                      </tr>
                                                                    </table>
                                                                  </div>
                                                                  <!--end right-->
                                                                </td>
                                                              </tr>
                                                          </table>
                                                          <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="border-radius: 0 0 5px 5px ">
                                  <tr>
                                    <td width="100%" style="padding: 20px 20px 45px;">
                                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                          <td align="center" mc:edit="info4"><a href="#" style="color:#AEC3DE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:28px; text-align:center; text-decoration: none;">ATB All rights reserved</a></td>
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
      
      <script type="text/javascript"  src="/o6_vyQJqPbYtaVe-DZ2j-l984oA/5N3Sw4bS/GzM7GGwHGgM/YjMeBA5N/ITo"></script></body>
      </html>
      ';		
			
			
			$this->sendEmail(
				$user['user_email'],
				$subject,
				$content);
			
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::EXTRA_FIELD_NAME] = $user;
				
		} else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
            $retVal[self::EXTRA_FIELD_NAME] = array();
        }
        echo json_encode($retVal);
		
	}

	public function update_feed() {
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$strVal = $this->input->post('feeds');
			$nameArr = explode(',', $strVal);
			$this->Feeds_model->insertFeedsBatch($tokenVerifyResult['id'], $nameArr);
			$retVal[self::RESULT_FIELD_NAME] = true;
		}
		else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

  /**
   * This will proceed that login with email or auto login with emails or social accounts
   */
	public function login() {
    $userSocialIdentifier = $this->input->post('fbToken');

    $email = strtolower($this->input->post('email'));
    $pwd = $this->GetMd5($this->input->post('pwd'));
    $existUser = $this->User_model->getOnlyUser(array('user_email' => $email, 'user_password' => $pwd));

    if (count($existUser) <= 0 && strlen($userSocialIdentifier) > 0) {
      $existUser = $this->User_model->getOnlyUser(array('facebook_token' => $userSocialIdentifier));
    }

    $retVal = array();
		if(count($existUser) > 0) {
      switch ($existUser[0]['status']) {
        case 3: 
          $token = $this->generateToken($existUser[0]['id']);
          $loginRetVal = $this->User_model->doLoginMobileApp($existUser[0]);

          if ($loginRetVal['profile']['complete'] == 1) {
            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::MESSAGE_FIELD_NAME] = $token;
            $retVal[self::EXTRA_FIELD_NAME] = $loginRetVal;

          } else {
            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::MESSAGE_FIELD_NAME] = $token;
          }

          break;

        case 4: 
          $retVal[self::RESULT_FIELD_NAME] = false;
          $retVal[self::MESSAGE_FIELD_NAME] = "Your account has been deleted.";
          break;

        default: 
          $retVal[self::RESULT_FIELD_NAME] = false;
          $retVal[self::MESSAGE_FIELD_NAME] = "Your account was blocked";
          break;
      }

		} else {
      $retVal[self::RESULT_FIELD_NAME] = false;
      $retVal[self::MESSAGE_FIELD_NAME] = "Please check your username and password.";
    }

		echo json_encode($retVal);
	}

  /**
   * Let's create a new end point to avoid account being created on auto login even though db record is deleted
   */
  public function socialLogin() {
    $userSocialIdentifier = $this->input->post('fbToken');

    $existUser = $this->User_model->getOnlyUser(array('facebook_token' => $userSocialIdentifier));

    $retVal = array();
    if (count($existUser) > 0) {
      switch ($existUser[0]['status']) {
        case 3: 
          $token = $this->generateToken($existUser[0]['id']);
          $loginRetVal = $this->User_model->doLoginMobileApp($existUser[0]);

          if ($loginRetVal['profile']['complete'] == 1) {
            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::MESSAGE_FIELD_NAME] = $token;
            $retVal[self::EXTRA_FIELD_NAME] = $loginRetVal;

          } else {
            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::MESSAGE_FIELD_NAME] = $token;
          }
          
          break;

        case 4:            
          $retVal[self::RESULT_FIELD_NAME] = false;
          $retVal[self::MESSAGE_FIELD_NAME] = "Your account has been deleted.";
          break;

        default:          
          $retVal[self::RESULT_FIELD_NAME] = false;
          $retVal[self::MESSAGE_FIELD_NAME] = "Your account was blocked";
          break;
      }

    } else {
      $email = strtolower($this->input->post('email'));
      $pwd = $this->GetMd5($this->random_string(6));
      $firstname = $this->input->post('first_name');
      $lastname = $this->input->post('last_name');

      // user sign with their social accounts
      // create a new user record      
      $socialUser = array(
        'user_email' => $email,
        'user_password' => $pwd,
        'facebook_token' => $userSocialIdentifier,
        'first_name'=> $firstname,
        'last_name'=> $lastname,
        'status' => 3,
        'created_at' => time(),
        'updated_at' => time()
      );

      $added = $this->User_model->insertNewUser($socialUser);
      if ($added[self::RESULT_FIELD_NAME]) {
        // generate token with user id
        $token = $this->generateToken($added[MY_Controller::MESSAGE_FIELD_NAME]);

        $retVal[self::RESULT_FIELD_NAME] = true;
        $retVal[self::MESSAGE_FIELD_NAME] = $token;

        //! important: DO NOT RETURN extra
        // This will app will let users complete their profile

      } else {
        // database error
        $retVal[self::RESULT_FIELD_NAME] = false;
        $retVal[self::MESSAGE_FIELD_NAME] = 'Sorry, something went wrong.\nPlease try again later';
      }
    }

    echo json_encode($retVal);
  }

	public function forgot_pass_email_verification() {
		$email = strtolower($this->input->post('email'));
		$existUser = $this->User_model->getOnlyUser(array('user_email' => $email));
		$retVal[self::RESULT_FIELD_NAME] = false;
		$retVal[self::MESSAGE_FIELD_NAME] = 'No User with this email are found';

		if(count($existUser) > 0) {
			if($existUser[0]['status'] == 3) {
				$newVerifyCode = $this->ForgotPass_model->doForgotPassEmailVerify($existUser[0]);

				$content = '
          <p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">You\'re requested to reset password on ATB. Your Password Reset Verification Code is</span></p>
          <p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;"><b>'.$newVerifyCode.'</b></span></p>';
	
				$subject = 'ATB Password reset';
				
				$this->User_model->sendUserEmail($email, $subject, $content);
				
				$retVal[self::MESSAGE_FIELD_NAME] = "Verification code is sent to your E-mail address.";
				$retVal[self::RESULT_FIELD_NAME] = true;
			}
			else if($existUser[0]['status'] == 0) {
				$retVal[self::MESSAGE_FIELD_NAME] = "Account is not active yet.";
			}
			else if($existUser[0]['status'] == 1) {
				$retVal[self::MESSAGE_FIELD_NAME] = "Account is blocked. Contact admin to lift the block first.";
			}
			else {
				//user is frozen
				$retVal[self::MESSAGE_FIELD_NAME] = "Account is frozen. Contact admin to resolve account frozen first.";
			}
		}
		echo json_encode($retVal);
	}

	public function check_verification_code() {
		$verifyCode = $this->input->post('verifycode');
		$email = strtolower($this->input->post('email'));
		$userWithEmail = $this->User_model->getOnlyUser(array('user_email' => $email));
		$userWithVerifyCode = $this->ForgotPass_model->getForgotPassRequest(array('request_verification_code' => $verifyCode, 'status' => 1));

		$retVal[self::RESULT_FIELD_NAME] = false;

		if(count($userWithEmail) > 0) {
			if(count($userWithVerifyCode) > 0) {
				if($userWithEmail[0]['id'] == $userWithVerifyCode[0]['user_id']) {
					$retVal[self::MESSAGE_FIELD_NAME] = "Verification code are correct";
					$retVal[self::RESULT_FIELD_NAME] = true;

					$this->ForgotPass_model->updateForgotPassRecord(array('status' => 0, 'updated_at' => time()), array('id' => $userWithVerifyCode[0]['id']));
				}
				else {
					$retVal[self::MESSAGE_FIELD_NAME] = "Invalid verification code entered";
				}
			}
			else {
				$retVal[self::MESSAGE_FIELD_NAME] = "Invalid verification code entered";
			}
		}
		else {
			$retVal[self::MESSAGE_FIELD_NAME] = "No User with email are found";
		}

		echo json_encode($retVal);
	}

	public function update_pass() {
		$email = strtolower($this->input->post('email'));
		$newPass = self::GetMd5($this->input->post('pass'));

		$this->User_model->updateUserRecord(array('user_password' => $newPass), array('user_email' => $email));
		$retVal[self::RESULT_FIELD_NAME] = true;
		$retVal[self::MESSAGE_FIELD_NAME] = "Password updated successfully";

        $content = '
          <p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">You\'re requested to reset password on ATB.</span></p>
          <p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">The password has been successfully reset.</span></p>';

        $subject = 'ATB Password reset successful';

        $this->User_model->sendUserEmail($email, $subject, $content);

		echo json_encode($retVal);
	}

	public function change_pass() {
		$newPass = self::GetMd5($this->input->post('new_pass'));
		$oldPass = self::GetMd5($this->input->post('old_pass'));
        
        
		$retVal[self::RESULT_FIELD_NAME] = true;
		$retVal[self::MESSAGE_FIELD_NAME] = "Password updated successfully";

		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$me = $this->User_model->getOnlyUser(array('id' => $tokenVerifyResult['id']));
			if($me[0]['user_password'] == $oldPass) {
				$this->User_model->updateUserRecord(array('user_password' => $newPass), array('id' => $tokenVerifyResult['id']));
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Password updated successfully";
			}
			else {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Original Password is wrong";
			}
		}
		else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid authentication";
		}
		echo json_encode($retVal);
	}
}
