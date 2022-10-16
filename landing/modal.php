<!-- Modal -->
<div class="modal  fade" id="businessInterest" tabindex="-1" aria-labelledby="businessInterestLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen modal-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-gradient pb-5">
         <div class="logo">
            <h6>Welcome to</h6>
            <img src="assets/img/logo-white.svg" alt="ATB Fashion" class="img-fluid"> 
            <p class="mb-0 text-white">Business</p>
         </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i class="fal fa-times-circle text-white"></i>
        </button>
      </div>

      <div class="modal-body pb-0">
      <form method="post">
        <div class="form-group">
          <label for="name">First Name</label>
          <input type="text" class="form-control" name="firstname" id="name" aria-describedby="nameHelp" required>
        </div>
        <div class="form-group">
          <label for="surname">Surname</label>
          <input type="text" class="form-control" name= "surname" id="surname" aria-describedby="surnameHelp" required>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" class="form-control" name = "email" id="email" aria-describedby="emailHelp" required>
        </div>
        <div class="form-group">
          <label for="businessName">Business Name</label>
          <input type="text" class="form-control" name = "businessName" id="businessName" aria-describedby="businessNameHelp">
        </div>
        <div class="form-group">
          <label for="businessType">What does your business do?</label>
          <input type="text" class="form-control" name = "businessType" id="businessType" aria-describedby="businessTypeHelp">
        </div>
        <div class="form-group">
          <label for="website">Website</label>
          <input type="text" class="form-control" name = "website" id="website" aria-describedby="websiteHelp">
        </div>
        <div class="form-group">
          <label for="instagramAccount">Instagram account</label>
          <input type="text" class="form-control" name = "instagramAccount" id="instagramAccount" aria-describedby="instagramAccountHelp">
        </div>
        <div class="form-group">
          <label for="membership">How long have you been a member of ATB?</label>
          <input type="text" class="form-control" name = "membership" id="membership" aria-describedby="membershipHelp">
        </div>
        <div class="bg-gradient p-3 rounded">
          <div class="form-group">
            <label class="text-white" for="businessCategory">Which category does your business fall within?</label>
            <div id="selectCategory" class="my-select">
                <div class="select-show" tabindex="1">
                    <i class="fas fa-chevron-down "></i>
                    <div class="show-input"><input type="radio" id="beauty" value="beauty" name="businessCategory" checked><span>Beauty</span></div>
                    <div class="show-input"><input type="radio" id="ladieswear" value="ladieswear" name="businessCategory"><span>Ladieswear</span></div>
                    <div class="show-input"><input type="radio" id="menswear" value="menswear" name="businessCategory"><span>Menswear</span></div>
                    <div class="show-input"><input type="radio" id="hair" value="hair" name="businessCategory"><span>Hair</span></div>
                    <div class="show-input"><input type="radio" id="kids" value="kids" name="businessCategory"><span>Kids</span></div>
                    <div class="show-input"><input type="radio" id="home" value="home" name="businessCategory"><span>Home</span></div>
                    <div class="show-input"><input type="radio" id="events" value="events" name="businessCategory"><span>Parties & Events</span></div>
                    <div class="show-input"><input type="radio" id="health" value="health" name="businessCategory"><span>Health & Wellbeing</span></div>
                    <div class="show-input"><input type="radio" id="seasonal" value="seasonal" name="businessCategory"><span>Seasonal</span></div>
                    <div class="show-input"><input type="radio" id="celebrations" value="celebrations" name="businessCategory"><span>Celebrations</span></div>
                    <div class="show-input"><input type="radio" id="miscellaneous" value="miscellaneous" name="businessCategory"><span>Miscellaneous</span></div>
                </div>
                <div class="select-list">
                    <label for="beauty" aria-hidden="beauty">Beauty</label>
                    <label for="ladieswear" aria-hidden="ladieswear">Ladieswear</label>
                    <label for="menswear" aria-hidden="menswear">Menswear</label>
                    <label for="hair" aria-hidden="hair">Hair</label>
                    <label for="kids" aria-hidden="kids">Kids</label>
                    <label for="home" aria-hidden="home">Home</label>
                    <label for="events" aria-hidden="events">Parties & Events</label>
                    <label for="health" aria-hidden="health">Health & Wellbeing</label>
                    <label for="seasonal" aria-hidden="seasonal">Seasonal</label>
                    <label for="celebrations" aria-hidden="celebrations">Celebrations</label>
                    <label for="miscellaneos" aria-hidden="miscellaneos">Miscellaneous</label>
                </div>
            </div>
          </div>

          <div class="form-group">
            <label  class="text-white" for="accessType">How do you access ATB?</label>
            <div id="selectAccess" class="my-select">
                <div class="select-show" tabindex="1">
                    <i class="fas fa-chevron-down "></i>
                    <div class="show-input"><input type="radio" id="desktop" value="desktop" name="accessType" checked><span>Desktop PC</span></div>
                    <div class="show-input"><input type="radio" id="ios" value="ios" name="accessType"><span>IOS</span></div>
                    <div class="show-input"><input type="radio" id="android" value="android" name="accessType"><span>Android</span></div>
                </div>
                <div class="select-list">
                    <label for="desktop" aria-hidden="beauty">Desktop PC</label>
                    <label for="ios" aria-hidden="ios">IOS</label>
                    <label for="android" aria-hidden="android">Android</label>
                </div>
            </div>
          </div>
        </div>

        <div class="bg-white mt-4 mx-n3 mx-md-n4 p-3 mb-4 pt-5">

          <div class="form-group">
            <p>Which of the following features will benefit your business? Please select all applicable:</p>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="feature1">
              <label class="form-check-label" for="feature1">
                Create your own personalised store 
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="feature2">
              <label class="form-check-label" for="feature2">
                Showcase all your Products & Services
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="feature3">
              <label class="form-check-label" for="feature3">
                Customers can purchase direct from your store using PayPal
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="feature4">
              <label class="form-check-label" for="feature4">
                Add as many products and services as you like
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="feature5">
              <label class="form-check-label" for="feature5">
                Edit and Amend your Products and Services
              </label>
            </div>
          </div>
          
          <hr>
          
          <div class="form-group">
            <p>Booking System</p>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="system1">
              <label class="form-check-label" for="system1">
                In app bookings
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="system2">
              <label class="form-check-label" for="system2">
                Manage bookings (Cancel, Amend, Payment Requests)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="system3">
              <label class="form-check-label" for="system3">
                Set Working Days/hours
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="system4">
              <label class="form-check-label" for="system4">
                Calendar Integration
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="system5">
              <label class="form-check-label" for="system5">
                Receive payments via PayPal/Apple Pay
              </label>
            </div>
          </div>

          <hr>
          
          <div class="form-group">
            <p>Social</p>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="social">
              <label class="form-check-label" for="social">
                Link your Instagram, Facebook and Twitter Accounts
              </label>
            </div>
          </div>

          <hr>

          <div class="form-group">
            <p>Posts</p>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="post1">
              <label class="form-check-label" for="post1">
                Create bespoke product and/or service posts.
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="post2">
              <label class="form-check-label" for="post2">
                Schedule Posts
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="post3">
              <label class="form-check-label" for="post3">
                Post Multiple Services or Products at the same time
              </label>
            </div>
          </div>

          <hr>

          <div class="form-group">
            <p>Promotion</p>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="promotion1">
              <label class="form-check-label" for="promotion1">
                Featured Posts
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="promotion2">
              <label class="form-check-label" for="promotion2">
               Top Spot – Business Directory
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="promotion3">
              <label class="form-check-label" for="promotion3">
               Spotlight – Pin your Profile to the top of the group feeds
              </label>
            </div>
          </div>

          <hr>

          <div class="form-group">
            <p>Safety, Security & Support</p>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="security1">
              <label class="form-check-label" for="security1">
                Insurance Verification
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="security2">
              <label class="form-check-label" for="security2">
                Qualification Verification
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="security3">
              <label class="form-check-label" for="security3">
                Deposit Scheme
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="security3">
              <label class="form-check-label" for="security3">
                PayPal
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="security3">
              <label class="form-check-label" for="security3">
                Apple Pay
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="security3">
              <label class="form-check-label" for="security3">
                Priority Admin Support
              </label>
            </div>
          </div>
        </div>

        <div class="form-group">
          <p>Do you use any of the following services for business use?</p>
          <div class="form-row mx-3">
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService1" value="treatwell" >
                <label class="form-check-label" for="businessService1">
                  Treatwell
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService2" value="mybuilder">
                <label class="form-check-label" for="businessService2">
                  My Builder
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService3" value="checkatrade">
                <label class="form-check-label" for="businessService3">
                  Checkatrade
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService5" value="asosmarketplace">
                <label class="form-check-label" for="businessService5">
                  Asos Marketplace
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService4" value="amazon">
                <label class="form-check-label" for="businessService4">
                  Amazon
                </label>
              </div>
              
               <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService6" value="notonthehighstreet">
                <label class="form-check-label" for="businessService6">
                 Notonthehighstreet
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService7" value="etsy">
                <label class="form-check-label" for="businessService7">
                  Etsy
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService8" value="silkfred">
                <label class="form-check-label" for="businessService8">
                  Silkfred
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService9" value="booksy">
                <label class="form-check-label" for="businessService9">
                 Booksy
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService10" value="ebay">
                <label class="form-check-label" for="businessService10">
                  Ebay
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService11" value="shpock">
                <label class="form-check-label" for="businessService11">
                  Shpock
                </label>
              </div>
              <div class="form-check col-6">
                <input class="form-check-input" type="radio" name="businessService" id="businessService12" value="other">
                <label class="form-check-label" for="businessService12">
                  Other
                </label>
              </div>
              <div class="form-check col-12">
                <input class="form-check-input" type="radio" name="businessService" id="businessService13" value="testing">
                <label class="form-check-label" for="businessService13">
                 Would you be interested in testing our new app before we launch?
                </label>
              </div>
          </div>
        </div>

        <div class="footer bg-white mt-4 mx-n3 mx-md-n4 p-3 pb-5 text-center">
          <h3>Connect with us on Social Media</h3>
          <p>Click on the links below, follow us and we'll follow right back!</p>
          <div class="social-links my-4">
            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
          </div>
            <button type="submit" name = "sendEmail" class="btn btn-block">Send</button>
        </div>

      </form> 

      </div>
   
    </div>
  </div>
</div>   

<?php            
        require_once('sendEmail.php');
        if(array_key_exists('sendEmail',$_POST)){
           makeMail();

        }                               
        function makeMail() {
          $emailboady = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
          <title>Welcome to ATB</title>
          
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
                      <table border="0" cellspacing="0" cellpadding="0" class="container">
                  <tr>
                    <td>
                      <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                          <td style="background-color: #A6BFDE;" valign="top" align="center" class="content">					
          
                              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                  <td align="center" style="padding: 53px 20px 40px">
                                    <a href="#" target="_blank"><img src="https://test.myatb.co.uk/landing/assets/img/logo.png" width="153" height="47" border="0" alt="" /></a>
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
                                        <td width="98" height="98" bgcolor="#F8F8F8" style="border-radius: 50% 50% 0 0!important;max-height: 98px !important;"><img src="https://test.myatb.co.uk/landing/assets/img/icon.png" width="98" height="98" border="0" alt="" style="border: 0 !important; outline:none; text-decoration: none;display:block;max-height: 98px !important;" /></td>
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
                                              <td><h1 style="color:#787F82; font-family:Roboto, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:31px; text-align:center; margin: 0;">Welcome to ATB</h1><br><h2 style="margin: 0; color:#787F82; font-family:Roboto, Arial, sans-serif; font-weight: 300; font-size:20px; line-height:24px; text-align:center;">We hope you enjoy using the app.</h2><br></td>
                                            </tr>
                                            <tr>
                                              <td style="font-family:Roboto, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">Are you a small business? Click your profile picture located at the top right of the feed and look out for the <img src="https://test.myatb.co.uk/landing/assets/img/briefcase.png" alt="briefcase" width="20px" style="vertical-align: middle; width: 15px;"> symbol briefcase to register as an ATB approved business!</td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="border-radius: 0 0 5px 5px ">
                                      <tr>
                                        <td width="100%" style="padding: 0px 20px;">
                                          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bottomNav">
                                            <tr><br></tr>
                                            <tr>
                                              <td colspan="3"><a href="#" target="_blank" style="color: #ffffff; text-decoration: none; font-family: Roboto, Arial, sans-serif; font-size: 15px; width: 210px; height: 50px; line-height: 50px; display: block; text-align: center; background: #ABC1DE; border-radius: 5px; margin: auto;" ><img src="https://test.myatb.co.uk/landing/assets/img/Briefcasex2white.png" width="20" height="20" style="vertical-align: middle ;"/> <span style="color:#ffffff; text-decoration:none; padding-left: 5px">Business Sign Up</span></a></td>
                                            </tr>
                                            <tr><td colspan="3" style="padding-top: 30px; padding-bottom: 10px"></td></tr>
                                            <tr>
                                              <td align="center"><a href="#" style="color:#A2A2A2;font-family:Roboto, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Terms and conditions</a> </td>
                                              <td align="center"><a href="#" style="color:#A2A2A2;font-family:Roboto, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Privacy Policy</a> </td>
                                              <td align="center"><a href="#" style="color:#A2A2A2;font-family:Roboto, Arial, sans-serif;font-size:15px; line-height:20px; text-align:center; text-decoration: none;">Contact Us</a> </td>
                                            </tr>
                                            
                                          </table>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td width="100%" style="padding: 20px 20px 45px;">
                                          <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                              <td align="center"><a href="#" style="color:#AEC3DE;font-family:Roboto, Arial, sans-serif;font-size:15px; line-height:28px; text-align:center; text-decoration: none;">ATB All rights reserved</a> </td>
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
            sendEmail($_POST["firstname"],$_POST["surname"],$_POST["email"],"Welcome to ATB",$emailboady,"This is anoter detail");
        }
    ?>