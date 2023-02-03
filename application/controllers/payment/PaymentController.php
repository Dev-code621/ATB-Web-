<?php

class PaymentController extends MY_Controller {
    
    public function success() {
        echo "Payment authorization was successful!";
    }
    
    public function cancel() {
        echo "Authorization has been cancelled.";
    }

    public function onboard() {
        $action = $this->input->get('action');

        if(is_null($action)) {
            show_error('The request is invalid');

        } else {
            if ($action == 'return') {
                $this->load->view('onboard/return');

            } else if ($action == 're-auth') {
                try {
                    $tokenVerifyResult = $this->verificationToken($this->input->get('token'));

                    if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
                        $users = $this->User_model->getOnlyUser(array('id' => $tokenVerifyResult['id']));

                        if (count($users)) {
                            $user = $users[0];
                            $connect = $user['stripe_connect_account'];

                            if (!is_null($connect) && !empty($connect)) {
                                $this->createLoginLink($connect, $this->input->get('token'));                                                              

                            } else {
                                show_error("The public access has been denied.");  
                            }

                        } else {
                            show_error('We were not able to find you in our user record.');
                        }

                    } else {
                        show_error('Token has been expired');
                    }

                } catch(Exception $ex) {
                    show_error('Access is denied');
                }   
                
            } else {
                show_error("The request is invalid");
            }
        }
    }

     private function createAccountLink($account, $token) {
        require_once('application/libraries/stripe-php/init.php');
        \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));

        try {
            // set this on when SSL issue is fixed
            $baseUrl = base_url();

            // test
            $baseUrl = "https://test.myatb.co.uk/";
            $accountLink = \Stripe\AccountLink::create([
                'account' => $account, 
                'refresh_url' => $baseUrl.'payment/onboard?action=re-auth&token='.$token,
                'return_url' => $baseUrl.'payment/onboard?action=return',
                'type' => 'account_onboarding'
            ]);

            redirect($accountLink->url);

        } catch (Exception $ex) {
            show_error($ex->getMessage());
        }
     }

     private function createLoginLink($account, $token) {
        require_once('application/libraries/stripe-php/init.php');
        \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));

        try {
            // creating a login link for the user
            $loginLink = \Stripe\Account::createLoginLink($account);

            redirect($loginLink->url);

        } catch (Exception $ex) {
            // generating an account link as it's been failed to create a login link
            /**
             * can't create a login link for an account that hasn't completed onboarding
             * This should say the user didn't try their onboarding 
             */
            $this->createAccountLink($account, $token);
        }
     }

     /**
      * webhook to handle post-payment events
      */
     public function stripe_hook() {
        $endpoint_secret = $this->config->item('webhook_secret');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        require_once('application/libraries/stripe-php/init.php');
        \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));

        try {
            $event = \Stripe\Webhook::constructEvent(
              $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
        // Invalid payload
        http_response_code(400);
        exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        http_response_code(400);
        exit();
        }
        
        // Handle the event
        switch ($event->type) {
        case 'payment_intent.canceled':
            $paymentIntent = $event->data->object;
            break;

        case 'payment_intent.processing':
            $paymentIntent = $event->data->object;
            break;

        case 'payment_intent.succeeded':
            $paymentIntent = $event->data->object;
            $this->handlePayment($paymentIntent);            
            break;

        // ... handle other event types
        default:
            echo 'Received unknown event type ' . $event->type;
        }
        
        http_response_code(200);
    }

    private function handlePayment($paymentIntent) {
        $paymentIntentId = $paymentIntent->id;

        $transactions = $this->UserTransaction_model->getTransactionHistory(
            array('transaction_id' => $paymentIntentId)
        );

        if (count($transactions)) {
            $transaction = $transactions[0];
            
            // buyer
            $userId = $transaction['user_id'];
            $users = $this->User_model->getOnlyUser(array('id' => $userId));

            if (count($users)) {
                // update payment details
                // update status to payment completed('1')
                $paymentDetails = $paymentIntent->charges->data[0]->payment_method_details;
                $update = array(
                    'invoice' => $paymentIntent->invoice,
                    'payment_method' => $paymentDetails->type,
                    'payment_brand' => $paymentDetails->card->brand,
                    'payment_source' => $paymentDetails->card->last4,
                    'status' => 1,
                    'updated_at' => time()
                );

                $this->UserTransaction_model->update(
                    $update,
                    array(
                        'transaction_id' => $paymentIntentId
                    ));

                // Email properties
                $subject = null;
                $content = null;

                $purchaseType = $transaction['purchase_type'];
                switch ($purchaseType) {
                    case 'subscription':
                        $this->UserBusiness_model->updateBusinessRecord(
                            array('paid' => 1, 'updated_at' => time()),
                            array('user_id' => $userId)
                        );
                        
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
									padding:55px 15px 50px !important;
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
						
						}	@media screen and (max-width: 650px){
								h1{
									font-size:16px !important;
								}
						
						}	@media screen and (max-width: 650px){
								h2{
									font-size:16px !important;
								}
						
						}</style></head>
						
						<body style="background-color: #A6BFDE; padding: 0 50px 50px; margin:0">
						<span style="height: 0; width: 0; line-height: 0pt; opacity: 0; display: none;">Thank you for applying to become an ATB approved business</span>
						
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
																			<a href="#" target="_blank"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/30d61529-fa12-c511-c9e3-acddf3ee2d5a.png" width="153" height="47" border="0" alt=""></a>
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
																					<td width="98" height="98" bgcolor="#F8F8F8" style="border-radius: 50% 50% 0 0!important;max-height: 98px !important;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/88863254-6c12-5fe3-df6d-cb5be53545fc.png" width="98" height="98" border="0" alt="" style="border: 0 !important; outline:none; text-decoration: none;display:block;max-height: 98px !important;"></td>
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
																								<td><h1 mc:edit="r1" style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:31px; text-align:center; margin: 0;">Business application received</h1><br><h2 mc:edit="r2" style="margin: 0; color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 300; font-size:20px; line-height:24px; text-align:center;">Thank you for applying to become an ATB approved business! </h2><br></td>
																							</tr>
																							<tr>
																								<td>
																									<p mc:edit="r3" style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">We will review your request and respond within 3 working days. </p>
																									<br>
																									<p mc:edit="r4" style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">While you wait you can begin to upload products and services to your business store (each new service will require additional admin approval).</p>
																									<br>
																									<p mc:edit="r5"><a href="hrefdeeplink" style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-weight: normal;text-decoration: underline;font-size: 15px;text-align: center;color: #a6bfde;display: block; margin: auto;">Upload products and services now</a></p>
																								</td>
																							</tr>																	
																						</table>
																					</td>
																				</tr>
																			</table>
																			<table bgcolor="#ffffff" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
																				<tr>
																					<td height="40"></td>
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
																								<td align="center" mc:edit="info4"><a href="#" style="color:#AEC3DE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:28px; text-align:center; text-decoration: none;">ATB All rights reserved</a> </td>
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
						</html>';

				$subject = 'Business application received';

                        $this->sendEmail($users[0]["user_email"], $subject, $content);                        
                        break;

                    case 'service':
                    case 'booking':
                        $bookingId = $transaction['target_id'];
                        $bookings = $this->Booking_model->getBooking($bookingId);

                        if (count($bookings)) {
                            $services= $this->UserService_model->getServiceInfo($bookings[0]['service_id']);
                            
                            if(count($services)) {
                                if ($purchaseType === 'service') {                    
                                    $this->Booking_model->updateBooking(
                                        array('state' => 'active'),
                                        array('id' => $bookingId)
                                    );
                                    
                                    $amount = (float)$services[0]['deposit_amount'];
            
                                    $this->NotificationHistory_model->insertNewNotification(
                                        array(
                                            'user_id' => $services[0]['user_id'],
                                            'type' => 6,
                                            'related_id' => $bookingId,
                                            'read_status' => 0,
                                            'send_status' => 0,
                                            'visible' => 1,
                                            'text' =>  " has booked " . $services[0]['title'] . " and paid a deposit of £" . number_format($amount, 2),
                                            'name' => $users[0]['user_name'],
                                            'profile_image' => $users[0]['pic_url'],
                                            'updated_at' => time(),
                                            'created_at' => time()
                                        )
                                    );

                                      // send Booking emails if it's required here
                                   $businessUser = $this->User_model->getOnlyUser(array('id' =>$services[0]['user_id']))[0];
                                   $business = $this->UserBusiness_model->getBusinessInfo($services[0]['user_id'])[0];
                                   $businessUrl = $business['business_website'];
                                   if (empty($businessUrl)) {
                                       $busienssUrl = $business['business_name'];
                                   }
                                   $bookingDate = date('jS F', $bookings[0]['updated_at']);
                                   $bookingTime = date('g:i A', $bookings[0]['updated_at']);
                                    $this->ServiceemailToBusiness(
                                        $businessUser['user_email'], 
                                        $bookingId, 
                                        $users[0]['pic_url'], 
                                        $businessUser['first_name'].' '.$businessUser['last_name'],
                                        $users[0]['user_name'], 
                                        $services[0]['title'],
                                        $bookingDate, 
                                        $bookingTime, 
                                        $services[0]['price'],
                                        $services[0]['deposit_amount']
                                    );
                
                                    $this->ServiceemailToUser(
                                        $users[0]['user_email'],
                                        $bookingId,
                                        $business['business_logo'], 
                                        $business['business_name'], 
                                        $businessUrl,
                                        $services[0]['title'],
                                        $bookingDate, 
                                        $bookingTime, 
                                        $services[0]['price'],
                                        $services[0]['deposit_amount']
                                    );
            
                                } else {
                                    $this->NotificationHistory_model->insertNewNotification(
                                        array(
                                            'user_id' => $services[0]['user_id'], // $transaction['destination'],
                                            'type' => 12,
                                            'related_id' => $bookingId,
                                            'read_status' => 0,
                                            'send_status' => 0,
                                            'visible' => 1,
                                            'text' => " has completed the payment for " . $services[0]['title'],
                                            'name' => $users[0]['user_name'],
                                            'profile_image' => $users[0]['pic_url'],
                                            'updated_at' => time(),
                                            'created_at' => time()
                                        )
                                    );    
                                    
                                    // mark the booking as completed
                                    $this->Booking_model->updateBooking(
                                        array('state' => 'complete'),
                                        array('id' => $bookingId)
                                    );

                                    $booking = $this->Booking_model->getBooking($bookingId)[0];
                                    $provider = $this->User_model->getOnlyUser(array('id' => $booking['business_user_id']))[0];

                                    $email = $provider['user_email'];
                                    $userName = $provider['first_name'].' '. $provider['last_name'];
                                    // $this->bookingCompleteEmail($email, $userName);
                                }


                            } else {
                                echo 'Not found the service:' . $bookings[0]['service_id'];
                            }

                        } else {
                            echo 'Not found the booking:' . $bookingId;
                        }

                        break;

                    case 'product':
                    case 'product_variant':
                        $quantity = $transaction['quantity'];

                        if ($purchaseType === 'product') {
                            $productID = $transaction['target_id'];
                            $products = $this->Product_model->getProduct($productID);

                            if (count($products)) {
                                $product = $products[0];
                                $stockLevel = $product['stock_level'];

                                if ($stockLevel > 0) {
                                    if ($stockLevel > 1) {
                                        // decrease the stock level
                                        $this->Product_model->updateProduct(
                                            array(
                                                'stock_level' => $stockLevel - $quantity, 'updated_at' => time()
                                            ),
                                            array('id' => $productID)
                                        );
                
                                    } else {
                                        // set the stock level as '0' and set the product as 'Sold out'
                                        $this->Product_model->updateProduct(
                                            array(
                                                'stock_level' => 0, 
                                                'is_sold' => 1, 
                                                'updated_at' => time()											
                                            ),
                                            array('id' => $productID)
                                        );
                
                                        // set the relevant posts as 'Sold out'
                                        $posts = $this->Post_model->getPostInfo(
                                            array(
                                                'product_id' => $productID, 
                                                'post_type' => 2));
                
                                        for ($postIndex = 0; $postIndex < count($posts); $postIndex ++) {
                                            $this->Post_model->updatePostContent(
                                                array(
                                                    'is_sold' => 1, 
                                                    'updated_at' => time()
                                                ),
                                                array('id' => $posts[$postIndex]['id'])
                                            );
                                        }
                                    }
    
                                    $this->NotificationHistory_model->insertNewNotification(
                                        array(
                                            'user_id' => $product['user_id'],
                                            'type' => 4,
                                            'related_id' => $product['poster_profile_type'],
                                            'read_status' => 0,
                                            'send_status' => 0,
                                            'visible' => 1,
                                            'text' => " has purchased " . $product['title'],
                                            'name' => $users[0]['user_name'],
                                            'profile_image' => $users[0]['pic_url'],
                                            'updated_at' => time(),
                                            'created_at' => time()
                                        )
                                    );
                                 
                                    // $this->soldEmail(
                                    //     $users[0]['user_email'],
                                    //     $users[0]['user_name'],
                                    //     $product['title']
                                    // );

                                    $businessUser = $this->User_model->getOnlyUser(array('id' =>$product['user_id']))[0];
                                    $bookingDate = date('jS F', $product['updated_at']);
                                    $bookingTime = date('g:i A', $product['updated_at']);
                                    $this->ProductemailToBusiness(
                                        $businessUser['user_email'], 
                                        $product['id'],
                                        $users[0]['pic_url'], 
                                        $businessUser['first_name'].' '.$businessUser['last_name'],
                                        $users[0]['first_name'].' '.$users[0]['last_name'],
                                        $product['title'],
                                        $bookingDate, 
                                        $bookingTime, 
                                        $product['price'],
                                        $product['deposit_amount'],
										"N/A"
                                    );
                
                                    $this->sendBuyerEmail(
                                        $users[0]['user_email'],
                                        $product['id'],
                                        $businessUser['pic_url'], 
                                        $businessUser['first_name'].' '.$businessUser['last_name'],
                                        $users[0]['first_name'].' '.$users[0]['last_name'],
                                        $product['title'],
                                        $bookingDate, 
                                        $bookingTime, 
                                        $product['price'],
                                        $product['deposit_amount'],
										"N/A"
                                    );

                                    /** send emails if it's required */
    
                                } else {
                                    echo 'The product is out of stock:' . $productID;
                                }

                            } else {
                                echo 'Received unknown product:' . $productID;
                            }

                        } else {
                            /** product variant */
                            $variantID = $transaction['target_id'];
                            $productVariants = $this->Product_model->getProductVariation($variantID);

                            if (count($productVariants) > 0) {
                                $productVariant = $productVariants[0];	// selected product variant
                                $product = $this->Product_model->getProduct($productVariant['product_id'])[0];

                                $stockLevel = $productVariant['stock_level'];

                                if ($stockLevel > 0) {
                                    // decrease the stock level
                                    $this->Product_model->updateProductVariation(
                                        array(
                                            'stock_level' => $stockLevel - $quantity,
                                            'updated_at' => time()
                                        ),
                                        array('id' => $variantID)
                                    );
                
                                    // get all variations
                                    $allProductVariants = $this->Product_model->getProductVariations(array('product_id' => $productVariant['product_id']));
                                    $totalStockLevel = 0;
                                    for ($variantIndex = 0; $variantIndex < count($allProductVariants); $variantIndex++)  {
                                        $totalStockLevel += $allProductVariants[$variantIndex]['stock_level'];
                                    }
                
                                    if ($totalStockLevel <= 0) {
                                        // set the product as 'Sold out'
                                        $this->Product_model->updateProduct(
                                            array(
                                                'is_sold' => 1, 'updated_at' => time()
                                            ),
                                            array('id' => $product['id'])
                                        );
                
                                        // set all relevant posts as 'Sold out'
                                        $posts = $this->Post_model->getPostInfo(array('product_id' => $product['id'], 'post_type' => 2));	
                                        for ($postIndex = 0; $postIndex < count($posts); $postIndex ++) {
                                            $this->Post_model->updatePostContent(
                                                array(
                                                    'is_sold' => 1, 
                                                    'updated_at' => time()
                                                ),
                                                array('id' => $posts[$postIndex]['id'])
                                            );
                                        }
                                    }
    
                                    $this->NotificationHistory_model->insertNewNotification(
                                        array(
                                            'user_id' => $product['user_id'],
                                            'type' => 4,
                                            'related_id' => $product['poster_profile_type'],
                                            'read_status' => 0,
                                            'send_status' => 0,
                                            'visible' => 1,
                                            'text' => " has purchased " . $product['title'],
                                            'name' => $users[0]['user_name'],
                                            'profile_image' => $users[0]['pic_url'],
                                            'updated_at' => time(),
                                            'created_at' => time()
                                        )
                                    );

                                   
                                    $businessUser = $this->User_model->getOnlyUser(array('id' =>$product['user_id']))[0];
                                    $bookingDate = date('jS F', $product['updated_at']);
                                    $bookingTime = date('g:i A', $product['updated_at']);
									// $this->soldEmail(
									// 	$businessUser['user_email'], 
                                    //     $businessUser['first_name'].' '.$businessUser['last_name'],
                                    //     $product['title']
                                    // );
                                    $this->ProductemailToBusiness(
                                        $businessUser['user_email'], 
                                        $product['id'], 
                                        $users[0]['pic_url'], 
                                        $businessUser['first_name'].' '.$businessUser['last_name'],
                                        $users[0]['first_name'].' '.$users[0]['last_name'],
                                        $product['title'],
                                        $bookingDate, 
                                        $bookingTime, 
                                        $product['price'],
                                        $product['deposit_amount'] ,
										$productVariant['title']
                                    );
                
                                    $this->sendBuyerEmail(
                                        $users[0]['user_email'],
                                        $product['id'],
                                        $businessUser['pic_url'], 
                                        $businessUser['first_name'].' '.$businessUser['last_name'],
                                        $users[0]['first_name'].' '.$users[0]['last_name'],
                                        $product['title'],
                                        $bookingDate, 
                                        $bookingTime, 
                                        $product['price'],
                                        $product['deposit_amount'],
										$productVariant['title']
                                    );
    
                                } else {
                                    echo 'The product variation is out of stock:' . $variantID;
                                }

                            } else {
                                echo 'Received unknown product variant:' . $variantID;
                            }
                        }   

                        break;

                    default:
                        echo 'Received unknown purchase type:' . $purchaseType;
                        break;
                }

                echo 'payment intent successful:' . $paymentIntentId;

            } else {
                echo 'Not found the buyer: ' . $paymentIntentId;
            }

        } else {
            echo 'Not found the transaction:' . $paymentIntentId;
        }
    }

   private function soldEmail($to,$username,$title){
    $subject = "You have sold product";
    
			$content = '<link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i" rel="stylesheet" />
			<!--<![endif]-->
			<title>Subject: ATB - Reported post</title>
			
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
			<span style="height: 0; width: 0; line-height: 0pt; opacity: 0; display: none;">This is where you write what it&#39ll show on the clients email listing. If not, it&#39ll take the first text of the email.</span>
			
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
													<v:fill type="frame" src="'.base_url().'assets/email/images/background.jpg" color="#ABC1DE" />
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
																		<td width="98" height="98" bgcolor="#F8F8F8" style="border-radius: 50% 50% 0 0!important;max-height: 98px !important;"><img src="'.base_url().'assets/email/images/report_icon.png" width="98" height="98" border="0" alt="" style="border: 0 !important; outline:none; text-decoration: none;display:block;max-height: 98px !important;" /></td>
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
																					<td><h1 style="color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:31px; text-align:center; margin: 0;">You have sold project in your store</h1>
																				  <br>
																				  <h2 style="margin: 0; color:#787F82; font-family:&#39Roboto&#39, Arial, sans-serif; font-weight: 300; font-size:20px; line-height:24px; text-align:center;">Product Title: '.$title.'</h2><br></td>
																				</tr>
																				<tr>
																					<td>
																					<p style="font-family:&#39Roboto&#39, Arial, sans-serif;font-weight: normal;font-size: 15px;text-align: center;color: #737373;">Please add product</p></td>
																				</tr>																	
																				<tr>
																					<td>&nbsp;</td>
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
        $to,
        $subject,
        $content);
   }


	private function ServiceemailToBusiness($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit) {
		$subject = "New Booking Request";

        $content = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
			background:#A6BFDE;
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
	@media (max-width: 650px){
		.wrapper{
			padding:0 !important;
		}

}	@media (max-width: 650px){
		.container{
			width:100% !important;
			min-width:100% !important;
		}

}	@media (max-width: 650px){
		.content{
			padding-left:30px !important;
			padding-right:30px !important;
		}

}	@media (max-width: 650px){
		.social-btn{
			height:35px;
			width:auto;
		}

}	@media (max-width: 650px){
		.p100{
			display:block !important;
			width:100% !important;
			min-width:100%;
			padding-bottom:10px !important;
			padding-left:0px !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.p100 table{
			width:100% !important;
		}

}	@media (max-width: 650px){
		.mleft{
			float:left !important;
		}

}	@media (max-width: 650px){
		.mfont{
			font-size:20px !important;
		}

}	@media (max-width: 650px){
		.mfont2{
			font-size:16px !important;
		}

}	@media (max-width: 650px){
		.t100{
			width:50% !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.t100 tr td{
			display:block !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.black{
			color:#000000 !important;
		}

}	@media (max-width: 650px){
		.mnone{
			display:none !important;
		}

}	@media (max-width: 650px){
		.colim{
			width:100% !important;
		}

}	@media (max-width: 650px){
		.colim2{
			width:100% !important;
			padding-left:20px !important;
			padding-right:20px !important;
		}

}</style></head>

<body style="padding:0; margin:0; background: #A6BFDE;">

<table border="0" bgcolor="#A6BFDE" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
    <tr>
        <td align="center" valign="top" style="padding: 80px 0;" class="wrapper">
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
								<td style="padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
									<!--[if gte mso 9]>
									<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
										<v:fill type="frame" src="http://sg-lab.co/dev/atb/email/welcome/images/background.jpg" color="#ABC1DE" />
										<v:textbox inset="0,0,0,0">
									<![endif]-->
										
										<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
											<tr>
											  <td align="center">
												<table dir="rtl" align="center" border="0" cellpadding="0" cellspacing="0">
												  <tr>
													<td height="35"></td>
												  </tr>
												  <tr>
													<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
													  <!--left-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="160" align="center">
															  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																<!--image-->
																<tr>
																  <td align="center" style="line-height:0px;"><img style="display:block; line-height:0px; font-size:0px; border:0px;" class="img1" width="150" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" alt="img"></td>
																</tr>
																<!--end image-->
																<tr>
																  <td height="20"></td>
																</tr>
															  </table>
															</td>
														  </tr>
														</table>
													  </div>
													  <!--end left-->
													  <!--[if (gte mso 9)|(IE)]>
							</td>
							<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
							<![endif]-->
													  <!--right-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="414" align="center">
															  <table dir="ltr" width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																<!--title-->
																<tr>
																  <td style="font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:30px; color:#ffffff;font-weight:bold;" mc:edit="ss1">Hi ' . $name . ',</td>
																</tr>
																<!--end title-->
																<tr>
																  <td height="10"></td>
																</tr>
																<!--content-->
																<tr>
																  <td style="font-family:Open sans, Arial, Sans-serif; font-size:20px; color:#ffffff;line-height:28px;" mc:edit="s2">You Have A New Booking!</td>
																</tr>
																<!--end content-->
																<tr>
																  <td height="35"></td>
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
											  </td>
											</tr>
										</table>

										<table bgcolor="#F8F8F8" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
											<tr>
											  <td align="center">
												<table align="center" border="0" cellpadding="0" cellspacing="0">
												  <tr>
													<td height="20"></td>
												  </tr>
												  <tr>
													<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
													  <!--left-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="370" align="center" class="colim2">
															  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																<tr>
																	<td style="border-radius: 50%;" width="73" valign="baseline">
																		<img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%;" mc:edit="s3">
																	</td>
																	<td style="padding-left: 10px;">
																		<table width="100%" border="0" cellpadding="0" cellspacing="0">
																			<tr><td style="color:#454b4d; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:15px; line-height:20px;">Client name:</td></tr>
																			<tr><td style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: normal;font-size: 15px;line-height: 16px;text-align: left;color: #787f82;" mc:edit="s4">' . $username . '</td></tr>
																		</table>
																	</td>
																</tr>
																<tr>
																  <td height="20"></td>
																</tr>
															  </table>
															</td>
														  </tr>
														</table>
													  </div>
													  <!--end left-->
													  <!--[if (gte mso 9)|(IE)]>
							</td>
							<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
							<![endif]-->
													  <!--right-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="204" align="center" class="colim2">
															  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">

                                                                <tr>
                                                                    <td height="10"></td>
                                                                  </tr>
                                                                <tr><td mc:edit="s51"><a href="hrefdeeplink" style="color:#535353;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700;display: block;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" height="19" alt="Message icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;"> Manage this booking</a> </td></tr>
																<tr>
																  <td height="20"></td>
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
											  </td>
											</tr>
										  </table>

										<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3;padding: 10px 40px 80px;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;">
											<tr>
												<td>
													<table bgcolor="#ffffff" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
														<tr>
														  <td align="center" style="text-align:center;vertical-align:top;font-size:0; padding: 10px 20px 10px" bgcolor="#ffffff" class="content">
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="200" align="center" class="colim">
																	<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="container">
																		<tr>
																			<td height="10"></td>
																		</tr>
																		<tr>
																			<td>
																				<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service:</p>
																				<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s6">' . $title . '</p>
																			</td>
																		</tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
															<!--[if (gte mso 9)|(IE)]>
															</td>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															<![endif]-->
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="240" align="center" class="colim">
																	<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td height="10"></td>
																		</tr>
                                                                        <tr>
                                                                            <td>
                                                                                  <p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service Schedule:</p>
                                                                                  <p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s8">
                                                                                      <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> ' . $date . '</span> &nbsp;
                                                                                      <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> ' . $time . '</span></p>
                                                                              </td>
                                                                          </tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
															<!--[if (gte mso 9)|(IE)]>
															</td>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															<![endif]-->
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="120" align="center" class="colim">
																	<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td height="10"></td>
																		</tr>
                                                                        <tr>
																			<td>
																				<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Order Number:</p>
																				<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s9">#' . $bookingId . '</p>
																		  </td>
																		</tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
														  </td>
														</tr>
													  </table>

													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3; padding: 10px 40px 80px" bgcolor="#ffffff" class="content">
																<table width="100%" border="0" cellspacing="0" cellpadding="0">
																	<tr>
																		<td style="padding-top: 20px;">
																			<table width="100%" border="0" cellspacing="0" cellpadding="0">
																				<tr>
																					<td style="color: #787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:50px">Invoice</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Service total cost</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s10">&pound;' . number_format($total, 2) . '</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Deposit paid</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s11">-&pound;' . number_format($deposit, 2) . '</td>
																				</tr>
                                                                                	<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">ATB Transaction Fees</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s111">-&pound;10.00</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" class="mfont2" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Payment Pending</td>
																					<td align="right" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;" mc:edit="s12">&pound;' . number_format($total - $deposit, 2) . '</td>
																				</tr>
																			</table>
																		</td>
																	</tr>
																	<tr>
																	  <td>
																			<br>
																			<hr>
																		  <p style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px;font-weight: normal;font-size: 15px;line-height: 18px;text-align: left;color: #787f82;" mc:edit="s1">If you cannot fulfill the booking for any reason please get in touch with the client directly to reschedule.

                                                                          </p>
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>

										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td style="padding: 30px 40px 0px" class="content">
													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td class="p100">
																<table border="0" align="left" cellspacing="0" cellpadding="0" class="bottomNav">
																	<tr><td align="left" mc:edit="info1"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Terms and conditions</a></td></tr>
																	<tr><td align="left" mc:edit="info2"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;" class="black">Privacy Policy</a> </td></tr>
																	<tr><td align="left" mc:edit="info3"><a href="mailto:help@myatb.co.uk" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Contact Us</a> </td></tr>
																</table>
															</td>
															<td class="p100" align="right" width="400">
																<table border="0" cellspacing="0" cellpadding="0">
																</table>
																<table border="0" cellspacing="0" cellpadding="0">
																	<tr>
																		<td mc:edit="info4" align="right" class="p100" style="padding-top: 10px"><a href="#" class="mleft" style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:right; text-decoration: none !important;display: block !important;">ATB All rights reserved</a> </td>
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
</html>';

		$this->sendEmail(
			$to,
			$subject,
			$content);
	}

	private function ServiceemailToUser($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit) {
		$subject = "Booking Confirmed";

		$content = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
					background:#A6BFDE;
					-webkit-text-size-adjust:none;
				}
				a{
					color:#A6BFDE;
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
			@media (max-width: 650px){
				.wrapper{
					padding:0 !important;
				}
		
		}	@media (max-width: 650px){
				.container{
					width:100% !important;
					min-width:100% !important;
				}
		
		}	@media (max-width: 650px){
				.content{
					padding-left:30px !important;
					padding-right:30px !important;
				}
		
		}	@media (max-width: 650px){
				.social-btn{
					height:35px;
					width:auto;
				}
		
		}	@media (max-width: 650px){
				.p100{
					display:block !important;
					width:100% !important;
					min-width:100%;
					padding-bottom:10px !important;
					padding-left:0px !important;
					float:left !important;
				}
		
		}	@media (max-width: 650px){
				.p100 table{
					width:100% !important;
				}
		
		}	@media (max-width: 650px){
				.mleft{
					float:left !important;
				}
		
		}	@media (max-width: 650px){
				.mfont{
					font-size:20px !important;
				}
		
		}	@media (max-width: 650px){
				.mfont2{
					font-size:16px !important;
				}
		
		}	@media (max-width: 650px){
				.t100{
					width:50% !important;
					float:left !important;
				}
		
		}	@media (max-width: 650px){
				.t100 tr td{
					display:block !important;
					float:left !important;
				}
		
		}	@media (max-width: 650px){
				.black{
					color:#000000 !important;
				}
		
		}	@media (max-width: 650px){
				.mnone{
					display:none !important;
				}
		
		}	@media (max-width: 650px){
				.colim{
					width:100% !important;
				}
		
		}	@media (max-width: 650px){
				.colim2{
					width:100% !important;
					padding-left:20px !important;
					padding-right:20px !important;
				}
		
		}</style></head>
		
		<body style="padding:0; margin:0; background: #A6BFDE;">
		
		<table border="0" bgcolor="#A6BFDE" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
			<tr>
				<td align="center" valign="top" style="padding: 80px 0;" class="wrapper">
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
										<td style="padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
											<!--[if gte mso 9]>
											<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
												<v:fill type="frame" src="http://sg-lab.co/dev/atb/email/welcome/images/background.jpg" color="#ABC1DE" />
												<v:textbox inset="0,0,0,0">
											<![endif]-->
												
												<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
													<tr>
													  <td align="center">
														<table dir="rtl" align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td height="35"></td>
														  </tr>
														  <tr>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															  <!--left-->
															  <div style="display:inline-block; vertical-align:middle;">
																<table align="center" border="0" cellpadding="0" cellspacing="0">
																  <tr>
																	<td width="160" align="center">
																	  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																		<!--image-->
																		<tr>
																		  <td align="center" style="line-height:0px;"><img style="display:block; line-height:0px; font-size:0px; border:0px;" class="img1" width="150" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" alt="img"></td>
																		</tr>
																		<!--end image-->
																		<tr>
																		  <td height="20"></td>
																		</tr>
																	  </table>
																	</td>
																  </tr>
																</table>
															  </div>
															  <!--end left-->
															  <!--[if (gte mso 9)|(IE)]>
									</td>
									<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
									<![endif]-->
															  <!--right-->
															  <div style="display:inline-block; vertical-align:middle;">
																<table align="center" border="0" cellpadding="0" cellspacing="0">
																  <tr>
																	<td width="414" align="center">
																	  <table dir="ltr" width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																		<!--title-->
																		<tr>
																		  <td style="font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:30px; color:#ffffff;font-weight:bold;" mc:edit="ss1">Hi '.$username.',</td>
																		</tr>
																		<!--end title-->
																		<tr>
																		  <td height="10"></td>
																		</tr>
																		<!--content-->
																		<tr>
																		  <td style="font-family:Open sans, Arial, Sans-serif; font-size:20px; color:#ffffff;line-height:28px;" mc:edit="s2">Please find below details of your booking:                                                                </td>
																		</tr>
																		<!--end content-->
																		<tr>
																		  <td height="35"></td>
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
													  </td>
													</tr>
												</table>
		
												<table bgcolor="#F8F8F8" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
													<tr>
													  <td align="center">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td height="20"></td>
														  </tr>
														  <tr>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															  <!--left-->
															  <div style="display:inline-block; vertical-align:middle;">
																<table align="center" border="0" cellpadding="0" cellspacing="0">
																  <tr>
																	<td width="370" align="center" class="colim2">
																	  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td style="border-radius: 50%;" width="73" valign="baseline">
																				<img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/585a4a63-04d4-7d34-22fc-c719ad30a7d8.png" width="73" height="73" border="0" alt="user icon" style="border-radius:100%;" mc:edit="s3">
																			</td>
																			<td style="padding-left: 10px;">
																				<table width="100%" border="0" cellpadding="0" cellspacing="0">
																					<tr><td style="color:#454b4d; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:15px; line-height:20px;">Business name:</td></tr>
																					<tr><td style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: normal;font-size: 15px;line-height: 16px;text-align: left;color: #787f82;" mc:edit="s4">'.$name.'</td></tr>
																				</table>
																			</td>
																		</tr>
																		<tr>
																		  <td height="20"></td>
																		</tr>
																	  </table>
																	</td>
																  </tr>
																</table>
															  </div>
															  <!--end left-->
															  <!--[if (gte mso 9)|(IE)]>
									</td>
									<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
									<![endif]-->
															  <!--right-->
															  <div style="display:inline-block; vertical-align:middle;">
																<table align="center" border="0" cellpadding="0" cellspacing="0">
																  <tr>
																	<td width="204" align="center" class="colim2">
																	  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr><td mc:edit="s51"><a href="hrefdeeplink" style="color:#535353;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700;display: block;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" height="19" alt="Message icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;"> Manage this booking</a> </td></tr>
																		<tr>
																		  <td height="20"></td>
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
													  </td>
													</tr>
												  </table>
		
												<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3;padding: 10px 40px 80px;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;">
													<tr>
														<td>
															<table bgcolor="#ffffff" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td align="center" style="text-align:center;vertical-align:top;font-size:0; padding: 10px 20px 10px" bgcolor="#ffffff" class="content">
																	<div style="display:inline-block;vertical-align:top;">
																	  <table align="center" border="0" cellpadding="0" cellspacing="0">
																		<tr>
																		  <td width="200" align="center" class="colim">
																			<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="container">
																				<tr>
																					<td height="10"></td>
																				</tr>
																				<tr>
																					<td>
																						<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service:</p>
																						<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s6">'.$title.'</p>
																					</td>
																				</tr>
																				<tr>
																					<td height="10"></td>
																				</tr>
																			</table>
																		  </td>
																		</tr>
																	  </table>
																	</div>
																	<!--[if (gte mso 9)|(IE)]>
																	</td>
																	<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
																	<![endif]-->
																	<div style="display:inline-block;vertical-align:top;">
																	  <table align="center" border="0" cellpadding="0" cellspacing="0">
																		<tr>
																		  <td width="240" align="center" class="colim">
																			<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																				<tr>
																					<td height="10"></td>
																				</tr>
																				<tr>
																					<td>
																						  <p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Service Schedule:</p>
																						  <p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s8">
																							  <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$date.' </span> &nbsp;
																							  <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$time.'</span></p>
																					  </td>
																				  </tr>
																				<tr>
																					<td height="10"></td>
																				</tr>
																			</table>
																		  </td>
																		</tr>
																	  </table>
																	</div>
																	<!--[if (gte mso 9)|(IE)]>
																	</td>
																	<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
																	<![endif]-->
																	<div style="display:inline-block;vertical-align:top;">
																	  <table align="center" border="0" cellpadding="0" cellspacing="0">
																		<tr>
																		  <td width="120" align="center" class="colim">
																			<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																				<tr>
																					<td height="10"></td>
																				</tr>
																				<tr>
																					<td>
																						<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Order Number:</p>
																						<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s9">#'.$bookingId.'</p>
																				  </td>
																				</tr>
																				<tr>
																					<td height="10"></td>
																				</tr>
																			</table>
																		  </td>
																		</tr>
																	  </table>
																	</div>
																  </td>
																</tr>
															  </table>
		
															<table width="100%" border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3; padding: 10px 40px 80px" bgcolor="#ffffff" class="content">
																		<table width="100%" border="0" cellspacing="0" cellpadding="0">
																			<tr>
																				<td style="padding-top: 20px;">
																					<table width="100%" border="0" cellspacing="0" cellpadding="0">
																						<tr>
																							<td style="color: #787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:50px">Invoice</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Service total cost</td>
																							<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s10">&pound;'.number_format($total, 2).'</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Deposit paid</td>
																							<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s11">-&pound;'.number_format($deposit, 2).'</td>
																						</tr>
																							<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">ATB Transaction Fees</td>
																							<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s111">-&pound;10.00</td>
																						</tr>
																						<tr style="border-top: 1px solid #E3E3E3;">
																							<td align="left" class="mfont2" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Payment Pending</td>
																							<td align="right" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;" mc:edit="s12">&pound;'.number_format($total-$deposit, 2).'</td>
																						</tr>
																					</table>
																				</td>
																			</tr>
																			<tr>
																			  <td>
																					<br>
																					<hr>
																				  <p style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px;font-weight: normal;font-size: 15px;line-height: 18px;text-align: left;color: #787f82;" mc:edit="s1">If you can no longer attend or need to make amendments to the appointment, please get in touch with [business name] at the earliest.
		
																				  </p>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
		
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td style="padding: 30px 40px 0px" class="content">
															<table width="100%" border="0" cellspacing="0" cellpadding="0">
																<tr>
																	<td class="p100">
																		<table border="0" align="left" cellspacing="0" cellpadding="0" class="bottomNav">
																			<tr><td align="left" mc:edit="info1"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Terms and conditions</a></td></tr>
																			<tr><td align="left" mc:edit="info2"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;" class="black">Privacy Policy</a> </td></tr>
																			<tr><td align="left" mc:edit="info3"><a href="mailto:help@myatb.co.uk" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Contact Us</a> </td></tr>
																		</table>
																	</td>
																	<td class="p100" align="right" width="400">
																		<table border="0" cellspacing="0" cellpadding="0">
																		</table>
																		<table border="0" cellspacing="0" cellpadding="0">
																			<tr>
																				<td mc:edit="info4" align="right" class="p100" style="padding-top: 10px"><a href="#" class="mleft" style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:right; text-decoration: none !important;display: block !important;">ATB All rights reserved</a> </td>
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
			$to,
			$subject,
			$content);
	}

    private function ProductemailToBusiness($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit,$variation) {
		$subject = "Item Sold";

		$content = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
			background:#A6BFDE;
			-webkit-text-size-adjust:none;
		}
		a{
			color:#A6BFDE;
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
	@media (max-width: 650px){
		.wrapper{
			padding:0 !important;
		}

}	@media (max-width: 650px){
		.container{
			width:100% !important;
			min-width:100% !important;
		}

}	@media (max-width: 650px){
		.content{
			padding-left:30px !important;
			padding-right:30px !important;
		}

}	@media (max-width: 650px){
		.social-btn{
			height:35px;
			width:auto;
		}

}	@media (max-width: 650px){
		.p100{
			display:block !important;
			width:100% !important;
			min-width:100%;
			padding-bottom:10px !important;
			padding-left:0px !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.p100 table{
			width:100% !important;
		}

}	@media (max-width: 650px){
		.mleft{
			float:left !important;
		}

}	@media (max-width: 650px){
		.mfont{
			font-size:20px !important;
		}

}	@media (max-width: 650px){
		.mfont2{
			font-size:16px !important;
		}

}	@media (max-width: 650px){
		.t100{
			width:50% !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.t100 tr td{
			display:block !important;
			float:left !important;
		}

}	@media (max-width: 650px){
		.black{
			color:#000000 !important;
		}

}	@media (max-width: 650px){
		.mnone{
			display:none !important;
		}

}	@media (max-width: 650px){
		.colim{
			width:100% !important;
		}

}	@media (max-width: 650px){
		.colim2{
			width:100% !important;
			padding-left:20px !important;
			padding-right:20px !important;
		}

}</style></head>

<body style="padding:0; margin:0; background: #A6BFDE;">

<table border="0" bgcolor="#A6BFDE" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
    <tr>
        <td align="center" valign="top" style="padding: 80px 0;" class="wrapper">
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
								<td style="padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
									<!--[if gte mso 9]>
									<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
										<v:fill type="frame" src="http://sg-lab.co/dev/atb/email/welcome/images/background.jpg" color="#ABC1DE" />
										<v:textbox inset="0,0,0,0">
									<![endif]-->
										
										<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
											<tr>
											  <td align="center">
												<table dir="rtl" align="center" border="0" cellpadding="0" cellspacing="0">
												  <tr>
													<td height="35"></td>
												  </tr>
												  <tr>
													<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
													  <!--left-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="160" align="center">
															  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																<!--image-->
																<tr>
																  <td align="center" style="line-height:0px;"><img style="display:block; line-height:0px; font-size:0px; border:0px;" class="img1" width="150" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" alt="img"></td>
																</tr>
																<!--end image-->
																<tr>
																  <td height="20"></td>
																</tr>
															  </table>
															</td>
														  </tr>
														</table>
													  </div>
													  <!--end left-->
													  <!--[if (gte mso 9)|(IE)]>
							</td>
							<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
							<![endif]-->
													  <!--right-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="414" align="center">
															  <table dir="ltr" width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																<!--title-->
																<tr>
																  <td style="font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:30px; color:#ffffff;font-weight:bold;" mc:edit="ss1">Hi '.$name.',</td>
																</tr>
																<!--end title-->
																<tr>
																  <td height="10"></td>
																</tr>
																<!--content-->
																<tr>
																  <td style="font-family:Open sans, Arial, Sans-serif; font-size:20px; color:#ffffff;line-height:28px;" mc:edit="s2">You have received payment for your item.
																	Please find below details of the sale.</td>
																</tr>
																<!--end content-->
																<tr>
																  <td height="35"></td>
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
											  </td>
											</tr>
										</table>

										<table bgcolor="#F8F8F8" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
											<tr>
											  <td align="center">
												<table align="center" border="0" cellpadding="0" cellspacing="0">
												  <tr>
													<td height="20"></td>
												  </tr>
												  <tr>
													<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
													  <!--left-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="370" align="center" class="colim2">
															  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
																<tr>
																	<td style="border-radius: 50%;" width="73" valign="baseline">
																		<img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%;" mc:edit="s3">
																	</td>
																	<td style="padding-left: 10px;">
																		<table width="100%" border="0" cellpadding="0" cellspacing="0">
																			<tr><td style="color:#454b4d; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:15px; line-height:20px;">Buyer name:</td></tr>
																			<tr><td style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: normal;font-size: 15px;line-height: 16px;text-align: left;color: #787f82;" mc:edit="s4">'.$username.'</td></tr>
																		</table>
																	</td>
																</tr>
																<tr>
																  <td height="20"></td>
																</tr>
															  </table>
															</td>
														  </tr>
														</table>
													  </div>
													  <!--end left-->
													  <!--[if (gte mso 9)|(IE)]>
							</td>
							<td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
							<![endif]-->
													  <!--right-->
													  <div style="display:inline-block; vertical-align:middle;">
														<table align="center" border="0" cellpadding="0" cellspacing="0">
														  <tr>
															<td width="204" align="center" class="colim2">
															  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
																<tr><td mc:edit="s5"><a href="hrefdeeplink" style="color:#535353;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700;display: block;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" height="19" alt="Message icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;"> Manage Sale</a> </td></tr>
																<tr>
																  <td height="20"></td>
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
											  </td>
											</tr>
										  </table>

										<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3;padding: 10px 40px 80px;border-spacing: 0 !important;border-collapse: collapse !important;table-layout: fixed !important;">
											<tr>
												<td>
													<table bgcolor="#ffffff" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
														<tr>
														  <td align="center" style="text-align:center;vertical-align:top;font-size:0; padding: 10px 20px 10px" bgcolor="#ffffff" class="content">
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="140" align="center" class="colim">
																	<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="container">
																		<tr>
																			<td height="10"></td>
																		</tr>
																		<tr>
																			<td>
																				<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Product name:</p>
																				<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s6">'.$title.'</p>
																			</td>
																		</tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
															<!--[if (gte mso 9)|(IE)]>
															</td>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															<![endif]-->
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="110" align="center" class="colim">
																	<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td height="10"></td>
																		</tr>
																		<tr>
																			<td>
																				<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Variation:</p>
																				<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s7">'.$variation.'</p>
																		  </td>
																		</tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
															<!--[if (gte mso 9)|(IE)]>
															</td>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															<![endif]-->
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="200" align="center" class="colim">
																	<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td height="10"></td>
																		</tr>
																		<tr>
																		  <td>
																				<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Purchase Date:</p>
																				<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s8">
																					<span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$date.' </span> &nbsp;
																					<span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$time.'</span></p>
																			</td>
																		</tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
															<!--[if (gte mso 9)|(IE)]>
															</td>
															<td align="center" style="text-align:center;vertical-align:top;font-size:0;">
															<![endif]-->
															<div style="display:inline-block;vertical-align:top;">
															  <table align="center" border="0" cellpadding="0" cellspacing="0">
																<tr>
																  <td width="110" align="center" class="colim">

																	<table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
																		<tr>
																			<td height="10"></td>
																		</tr>
																		<tr>
																			<td>
																				<p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Order Number:</p>
																				<p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s9">#'.$bookingId.'</p>
																		  </td>
																		</tr>
																		<tr>
																			<td height="10"></td>
																		</tr>
																	</table>
																  </td>
																</tr>
															  </table>
															</div>
														  </td>
														</tr>
													  </table>

													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3; padding: 10px 40px 80px" bgcolor="#ffffff" class="content">
																<table width="100%" border="0" cellspacing="0" cellpadding="0">
																	<tr>
																		<td style="padding-top: 20px;">
																			<table width="100%" border="0" cellspacing="0" cellpadding="0">
																				<tr>
																					<td style="color: #787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:50px">Invoice</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Product Cost</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s10">&pound;'.number_format($total, 2).'</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Delivery Cost:</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s11">&pound;'.number_format($deposit, 2).'</td>
																				</tr>
                                                                                	<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">ATB Transaction Fees</td>
																					<td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s111">-&pound;10.00</td>
																				</tr>
																				<tr style="border-top: 1px solid #E3E3E3;">
																					<td align="left" class="mfont2" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Total Cost</td>
																					<td align="right" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;" mc:edit="s12">&pound;'.number_format($total-$deposit, 2).'</td>
																				</tr>
																			</table>
																		</td>
																	</tr>
																	<tr>
																	  <td>
																			<br>
																			<hr>
																		  <p style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px;font-weight: normal;font-size: 15px;line-height: 18px;text-align: left;color: #787f82;" mc:edit="s1">If not yet received, get in touch with the buyer via direct message to confirm the correct delivery address.</p>
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</table>

										<table width="100%" border="0" cellspacing="0" cellpadding="0">
											<tr>
												<td style="padding: 30px 40px 0px" class="content">
													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td class="p100">
																<table border="0" align="left" cellspacing="0" cellpadding="0" class="bottomNav">
																	<tr><td align="left" mc:edit="info1"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Terms and conditions</a></td></tr>
																	<tr><td align="left" mc:edit="info2"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;" class="black">Privacy Policy</a> </td></tr>
																	<tr><td align="left" mc:edit="info3"><a href="mailto:help@myatb.co.uk" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Contact Us</a> </td></tr>
																</table>
															</td>
															<td class="p100" align="right" width="400">
																<table border="0" cellspacing="0" cellpadding="0">
																</table>
																<table border="0" cellspacing="0" cellpadding="0">
																	<tr>
																		<td mc:edit="info4" align="right" class="p100" style="padding-top: 10px"><a href="#" class="mleft" style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:right; text-decoration: none !important;display: block !important;">ATB All rights reserved</a> </td>
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
			$to,
			$subject,
			$content);
	}

	private function sendBuyerEmail($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit,$variation) {
		$subject = "Item Purchased";

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
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
                    background:#A6BFDE;
                    -webkit-text-size-adjust:none;
                }
                a{
                    color:#A6BFDE;
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
            @media (max-width: 650px){
                .wrapper{
                    padding:0 !important;
                }
        
        }	@media (max-width: 650px){
                .container{
                    width:100% !important;
                    min-width:100% !important;
                }
        
        }	@media (max-width: 650px){
                .content{
                    padding-left:30px !important;
                    padding-right:30px !important;
                }
        
        }	@media (max-width: 650px){
                .social-btn{
                    height:35px;
                    width:auto;
                }
        
        }	@media (max-width: 650px){
                .p100{
                    display:block !important;
                    width:100% !important;
                    min-width:100%;
                    padding-bottom:10px !important;
                    padding-left:0px !important;
                    float:left !important;
                }
        
        }	@media (max-width: 650px){
                .p100 table{
                    width:100% !important;
                }
        
        }	@media (max-width: 650px){
                .mleft{
                    float:left !important;
                }
        
        }	@media (max-width: 650px){
                .mfont{
                    font-size:20px !important;
                }
        
        }	@media (max-width: 650px){
                .mfont2{
                    font-size:16px !important;
                }
        
        }	@media (max-width: 650px){
                .t100{
                    width:50% !important;
                    float:left !important;
                }
        
        }	@media (max-width: 650px){
                .t100 tr td{
                    display:block !important;
                    float:left !important;
                }
        
        }	@media (max-width: 650px){
                .black{
                    color:#000000 !important;
                }
        
        }	@media (max-width: 650px){
                .mnone{
                    display:none !important;
                }
        
        }	@media (max-width: 650px){
                .colim{
                    width:100% !important;
                }
        
        }	@media (max-width: 650px){
                .colim2{
                    width:100% !important;
                    padding-left:20px !important;
                    padding-right:20px !important;
                }
        
        }</style></head>
        
        <body style="padding:0; margin:0; background: #A6BFDE;">
        
        <table border="0" bgcolor="#A6BFDE" cellpadding="0" cellspacing="0" style="margin: 0; padding: 0" width="100%">
            <tr>
                <td align="center" valign="top" style="padding: 80px 0;" class="wrapper">
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
                                        <td style="padding: 0 0px 50px; box-shadow: 0px 3px 6px #b3b3b3;" bgcolor="#ABC1DE" valign="top" align="center">
                                            <!--[if gte mso 9]>
                                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:650px; height: 880px">
                                                <v:fill type="frame" src="http://sg-lab.co/dev/atb/email/welcome/images/background.jpg" color="#ABC1DE" />
                                                <v:textbox inset="0,0,0,0">
                                            <![endif]-->
                                                
                                                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                      <td align="center">
                                                        <table dir="rtl" align="center" border="0" cellpadding="0" cellspacing="0">
                                                          <tr>
                                                            <td height="35"></td>
                                                          </tr>
                                                          <tr>
                                                            <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                                                              <!--left-->
                                                              <div style="display:inline-block; vertical-align:middle;">
                                                                <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                  <tr>
                                                                    <td width="160" align="center">
                                                                      <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                        <!--image-->
                                                                        <tr>
                                                                          <td align="center" style="line-height:0px;"><img style="display:block; line-height:0px; font-size:0px; border:0px;" class="img1" width="150" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" alt="img"></td>
                                                                        </tr>
                                                                        <!--end image-->
                                                                        <tr>
                                                                          <td height="20"></td>
                                                                        </tr>
                                                                      </table>
                                                                    </td>
                                                                  </tr>
                                                                </table>
                                                              </div>
                                                              <!--end left-->
                                                              <!--[if (gte mso 9)|(IE)]>
                                    </td>
                                    <td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
                                    <![endif]-->
                                                              <!--right-->
                                                              <div style="display:inline-block; vertical-align:middle;">
                                                                <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                  <tr>
                                                                    <td width="414" align="center">
                                                                      <table dir="ltr" width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                        <!--title-->
                                                                        <tr>
                                                                          <td style="font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:30px; color:#ffffff;font-weight:bold;" mc:edit="ss1">Hi '.$username.',</td>
                                                                        </tr>
                                                                        <!--end title-->
                                                                        <tr>
                                                                          <td height="10"></td>
                                                                        </tr>
                                                                        <!--content-->
                                                                        <tr>
                                                                          <td style="font-family:Open sans, Arial, Sans-serif; font-size:20px; color:#ffffff;line-height:28px;" mc:edit="s2">Your payment has been received.
                                                                            Please find below details of your order.</td>
                                                                        </tr>
                                                                        <!--end content-->
                                                                        <tr>
                                                                          <td height="35"></td>
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
                                                      </td>
                                                    </tr>
                                                </table>
        
                                                <table bgcolor="#F8F8F8" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                      <td align="center">
                                                        <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                          <tr>
                                                            <td height="20"></td>
                                                          </tr>
                                                          <tr>
                                                            <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                                                              <!--left-->
                                                              <div style="display:inline-block; vertical-align:middle;">
                                                                <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                  <tr>
                                                                    <td width="370" align="center" class="colim2">
                                                                      <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                        <tr>
                                                                            <td style="border-radius: 50%;" width="73" valign="baseline">
                                                                                <img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%; max-width: 73px;" mc:edit="s3">
                                                                            </td>
                                                                            <td style="padding-left: 10px;">
                                                                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                                                    <tr><td style="color:#454b4d; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-size:15px; line-height:20px;">Seller name:</td></tr>
                                                                                    <tr><td style="color:#787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: normal;font-size: 15px;line-height: 16px;text-align: left;color: #787f82;" mc:edit="s4">'.$name.'</td></tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                          <td height="20"></td>
                                                                        </tr>
                                                                      </table>
                                                                    </td>
                                                                  </tr>
                                                                </table>
                                                              </div>
                                                              <!--end left-->
                                                              <!--[if (gte mso 9)|(IE)]>
                                    </td>
                                    <td align="center" style="text-align:center;vertical-align:middle;font-size:0;">
                                    <![endif]-->
                                                              <!--right-->
                                                              <div style="display:inline-block; vertical-align:middle;">
                                                                <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                  <tr>
                                                                    <td width="204" align="center" class="colim2">
                                                                      <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                        <tr><td mc:edit="s5"><a href="hrefdeeplink" style="color:#535353;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:13px; line-height:25px; text-decoration: none !important;font-weight: 700;display: block;"><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" height="19" alt="Message icon" style="display: inline !important;padding-right: 3px;vertical-align: middle;"> Manage Purchase</a> </td></tr>
                                                                        <tr>
                                                                          <td height="20"></td>
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
                                                      </td>
                                                    </tr>
                                                  </table>
        
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3; padding: 10px 40px 80px">
                                                    <tr>
                                                        <td>
                                                            <table bgcolor="#ffffff" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
                                                                <tr>
                                                                  <td align="center" style="text-align:center;vertical-align:top;font-size:0; padding: 10px 20px 10px" bgcolor="#ffffff" class="content">
                                                                    <div style="display:inline-block;vertical-align:top;">
                                                                      <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                        <tr>
                                                                          <td width="140" align="center" class="colim">
                                                                            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" class="container">
                                                                                <tr>
                                                                                    <td height="10"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>
                                                                                        <p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Product name:</p>
                                                                                        <p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s6">'.$title.'</p>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td height="10"></td>
                                                                                </tr>
                                                                            </table>
                                                                          </td>
                                                                        </tr>
                                                                      </table>
                                                                    </div>
                                                                    <!--[if (gte mso 9)|(IE)]>
                                                                    </td>
                                                                    <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                                                                    <![endif]-->
                                                                    <div style="display:inline-block;vertical-align:top;">
                                                                      <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                        <tr>
                                                                          <td width="110" align="center" class="colim">
                                                                            <table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                                <tr>
                                                                                    <td height="10"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>
                                                                                        <p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Variation:</p>
                                                                                        <p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s7">'.$variation.'</p>
                                                                                  </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td height="10"></td>
                                                                                </tr>
                                                                            </table>
                                                                          </td>
                                                                        </tr>
                                                                      </table>
                                                                    </div>
                                                                    <!--[if (gte mso 9)|(IE)]>
                                                                    </td>
                                                                    <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                                                                    <![endif]-->
                                                                    <div style="display:inline-block;vertical-align:top;">
                                                                      <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                        <tr>
                                                                          <td width="200" align="center" class="colim">
                                                                            <table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                                <tr>
                                                                                    <td height="10"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                  <td>
                                                                                        <p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Purchase Date:</p>
                                                                                        <p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s8">
                                                                                            <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$date.'  </span> &nbsp;
                                                                                            <span><img src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="15" height="15" alt="Booking icon" style="display: inline !important;vertical-align: text-top;"> '.$time.'</span></p>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td height="10"></td>
                                                                                </tr>
                                                                            </table>
                                                                          </td>
                                                                        </tr>
                                                                      </table>
                                                                    </div>
                                                                    <!--[if (gte mso 9)|(IE)]>
                                                                    </td>
                                                                    <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                                                                    <![endif]-->
                                                                    <div style="display:inline-block;vertical-align:top;">
                                                                      <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                        <tr>
                                                                          <td width="110" align="center" class="colim">
        
                                                                            <table width="95%" class="container" border="0" align="center" cellpadding="0" cellspacing="0">
                                                                                <tr>
                                                                                    <td height="10"></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>
                                                                                        <p style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;">Order Number:</p>
                                                                                        <p style="color: #787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-decoration: none;" mc:edit="s9">#'.$bookingId.'</p>
                                                                                  </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td height="10"></td>
                                                                                </tr>
                                                                            </table>
                                                                          </td>
                                                                        </tr>
                                                                      </table>
                                                                    </div>
                                                                  </td>
                                                                </tr>
                                                              </table>
        
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td style="border-radius: 0 0 8px 8px;box-shadow: 0px 3px 6px #b3b3b3; padding: 10px 40px 80px" bgcolor="#ffffff" class="content">
                                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td style="padding-top: 20px;">
                                                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                                        <tr>
                                                                                            <td style="color: #787F82; font-family:&#39;Roboto&#39;, Arial, sans-serif; font-weight: 700; font-size:30px; line-height:50px">Invoice</td>
                                                                                        </tr>
                                                                                        <tr style="border-top: 1px solid #E3E3E3;">
                                                                                            <td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Product Cost</td>
                                                                                            <td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s10">&pound;'.number_format($total, 2).'</td>
                                                                                        </tr>
                                                                                        <tr style="border-top: 1px solid #E3E3E3;">
                                                                                            <td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">Delivery/collection</td>
                                                                                            <td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s11">&pound;'.number_format($deposit, 2).'</td>
                                                                                        </tr>
                                                                                            <tr style="border-top: 1px solid #E3E3E3;">
                                                                                            <td align="left" style="color:#454B4D;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;">ATB Transaction Fees</td>
                                                                                            <td align="right" style="color:#A6BFDE;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:40px; text-decoration: none;font-weight: 700;" mc:edit="s111">-&pound;10.00</td>
                                                                                        </tr>
                                                                                        <tr style="border-top: 1px solid #E3E3E3;">
                                                                                            <td align="left" class="mfont2" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;">Total Cost</td>
                                                                                            <td align="right" style="color:#787F82;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:20px; line-height:40px; text-decoration: none; font-weight: 700;" mc:edit="s12">&pound;'.number_format($total-$deposit, 2).'</td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                              <td>
                                                                                    <br>
                                                                                    <hr>
                                                                                  <p style="font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px;font-weight: normal;font-size: 15px;line-height: 18px;text-align: left;color: #787f82;" mc:edit="s1">If you haven’t already done so, remember to share your best delivery address with the seller via direct message.																		  </p>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
        
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td style="padding: 30px 40px 0px" class="content">
                                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td class="p100">
                                                                        <table border="0" align="left" cellspacing="0" cellpadding="0" class="bottomNav">
                                                                            <tr><td align="left" mc:edit="info1"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Terms and conditions</a></td></tr>
                                                                            <tr><td align="left" mc:edit="info2"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;" class="black">Privacy Policy</a> </td></tr>
                                                                            <tr><td align="left" mc:edit="info3"><a href="mailto:help@myatb.co.uk" style="color: #ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:26px; text-align:left; text-decoration: none !important;">Contact Us</a> </td></tr>
                                                                        </table>
                                                                    </td>
                                                                    <td class="p100" align="right" width="400">
                                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                                        </table>
                                                                        <table border="0" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td mc:edit="info4" align="right" class="p100" style="padding-top: 10px"><a href="#" class="mleft" style="color:#ffffff;font-family:&#39;Roboto&#39;, Arial, sans-serif;font-size:15px; line-height:20px; text-align:right; text-decoration: none !important;display: block !important;">ATB All rights reserved</a> </td>
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
			$to,
			$subject,
			$content);
	}
}
