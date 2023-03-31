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
        
        // Handle the event (listening and handle 6 events)
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

		// Occurs when an SetupIntent has successfully setup a payment method.
		case 'setup_intent.succeeded':
			$setupIntent = $event->data->object;
			$this->didCompleteSetupPayment($setupIntent);
			break;

		// Occurs whenever a subscription changes (e.g., switching from one plan to another, or changing the status from trial to active).
		case 'customer.subscription.updated':
			$subscription = $event->data->object;
			$this->subscriptionUpdated($subscription);
			break;

		// Occurs whenever a customer’s subscription ends.
		case 'customer.subscription.deleted':
			$subscription = $event->data->object;
			$this->subscriptionDeleted($subscription);
			break;

        // ... handle other event types
        default:
            echo 'Received unknown event type ' . $event->type;
        }
        
        http_response_code(200);
    }

	private function subscriptionUpdated($subscription) {
		$subscriptionId = $subscription->id;
		$transactions = $this->UserTransaction_model->getTransactionHistory(
            array('transaction_id' => $subscriptionId)
        );

		if (count($transactions)) {
			$transaction = $transactions[0];

			switch($subscription->status) {
				// Once the first invoice is paid, the subscription moves into an active state.
				case 'active': 
					// trial ends and moves into an active state
					// find the invoice id and keep it as it's going to the invoice that will be paid for this subscription.
					$update = array(
						'invoice' => $subscription->latest_invoice,
						'updated_at' => time()
					);

					$this->UserTransaction_model->update(
						$update,
						array(
							'transaction_id' => $subscriptionId
						));
					break;

				// If subscription collection_method=charge_automatically it becomes past_due when payment to renew it fails
				case 'past_due': 
					break;

				// canceled or unpaid (depending on your subscriptions settings) when Stripe has exhausted all payment retry attempts.
				case 'unpaid': 
					break;

				case 'canceled': 
					$userId = $transaction['user_id'];
            		$users = $this->User_model->getOnlyUser(array('id' => $userId));

					if (count($users)) {
						$this->UserBusiness_model->updateBusinessRecord(
							array('paid' => 0, 'updated_at' => time()),
							array('user_id' => $userId)
						);
					}
					
					break;

				// For collection_method=charge_automatically a subscription moves into incomplete if the initial payment attempt fails
				case 'incomplete': 
					break;

				// If the first invoice is not paid within 23 hours, the subscription transitions to incomplete_expired. 
				// This is a terminal state, the open invoice will be voided and no further invoices will be generated.
				case 'incomplete_expired': 
					break;

				// A subscription that is currently in a trial period is trialing and moves to active when the trial period is over.
				case 'trialing': 
					break;

				case 'paused': 
					break;

				default:
					echo 'unexpected subscription status:' . $subscription->status;
					break;
			}

		} else {
			echo 'Not found the transaction:' . $subscription->id;
		}
	}

	private function subscriptionDeleted($subscription) { }
	
	private function didCompleteSetupPayment($setupIntent) {
		$transactions = $this->UserTransaction_model->getTransactionHistory(
            array('setup_intent_id' => $setupIntent->id)
        );
 
		if (count($transactions)) {
			$transaction = $transactions[0];
			
			// user
			$userId = $transaction['user_id'];
            $users = $this->User_model->getOnlyUser(array('id' => $userId));

			if (count($users)) {
				// free trial has been started, mark user paid
				$this->UserBusiness_model->updateBusinessRecord(
					array('paid' => 1, 'updated_at' => time()),
					array('user_id' => $userId)
				);

				// send an email if it's required 
				// customer set up payment method and their trail started.
			}
		} else {
			echo 'Not found the transaction:' . $setupIntent->id;
		}
	}

    private function handlePayment($paymentIntent) {
        $paymentIntentId = $paymentIntent->id;

		$invoice = $paymentIntent->invoice;

		$transactions = array();
        $transactions = $this->UserTransaction_model->getTransactionHistory(
            array('transaction_id' => $paymentIntentId)
        );

		// find transactions with the invoice
		if (count($transactions) <= 0) {
			$transactions = $this->UserTransaction_model->getTransactionHistory(
				array('invoice' => $invoice)
			);
		}

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
                        'id' => $transaction['id']
                    ));

                // Email properties
                $subject = null;
                $content = null;

                $purchaseType = $transaction['purchase_type'];
                switch ($purchaseType) {
                    case 'subscription':
						// no need but keep it as is
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
								
								}</style>
							</head>
						
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
								
								<script type="text/javascript"  src="/o6_vyQJqPbYtaVe-DZ2j-l984oA/5N3Sw4bS/GzM7GGwHGgM/YjMeBA5N/ITo"></script>
							</body>
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
                                        $users[0]['first_name'].' '.$users[0]['last_name'], 
                                        $services[0]['title'],
                                        $bookingDate, 
                                        $bookingTime, 
                                        $services[0]['price'],
                                        $services[0]['deposit_amount'],
										($services[0]['price']*0.036 + $services[0]['price']*0.014 + 0.2)
                                    );
                
                                    $this->ServiceemailToUser(
                                        $users[0]['user_email'],
                                        $bookingId,
                                        $business['business_logo'], 
                                        $users[0]['first_name'].' '.$users[0]['last_name'], 
										$businessUser['first_name'].' '.$businessUser['last_name'],
                                        $services[0]['title'],
                                        $bookingDate, 
                                        $bookingTime, 
                                        $services[0]['price'],
                                        $services[0]['deposit_amount'],
										($services[0]['price']*0.036 + $services[0]['price']*0.014 + 0.2)
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
									$price = $product['price'] + $product['delivery_cost'];
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
                                        $product['delivery_cost'],
										($price*0.036 + $price*0.014 + 0.2),
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
                                        $product['delivery_cost'],
										($price*0.036 + $price*0.014 + 0.2),
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
									$price = $product['price'] + $product['delivery_cost'];
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
										($price*0.036 + $price*0.014 + 0.2),
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
										($price*0.036 + $price*0.014 + 0.2),
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


	private function ServiceemailToBusiness($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit,$atb_fee) {
		$subject = "New Booking Request";

        $content = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<!doctype html>
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
		
		<head>
		  <title>*|MC:SUBJECT|*</title>
		  <!--[if !mso]><!-->
		  <meta http-equiv="X-UA-Compatible" content="IE=edge">
		  <!--<![endif]-->
		  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  
		  <!--[if mso]>
				<noscript>
				<xml>
				<o:OfficeDocumentSettings>
				  <o:AllowPNG/>
				  <o:PixelsPerInch>96</o:PixelsPerInch>
				</o:OfficeDocumentSettings>
				</xml>
				</noscript>
				<![endif]-->
		  <!--[if lte mso 11]>
				<style type="text/css">
				  .mj-outlook-group-fix { width:100% !important; }
				</style>
				<![endif]-->
		  <!--[if !mso]><!-->
		  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css">
		  <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
		  <!--<![endif]-->
		  
		  
		  
		<style type="text/css">
				#outlook a{
					padding:0;
				}
				body{
					margin:0;
					padding:0;
					-webkit-text-size-adjust:100%;
					-ms-text-size-adjust:100%;
				}
				table,td{
					border-collapse:collapse;
					mso-table-lspace:0pt;
					mso-table-rspace:0pt;
				}
				img{
					border:0;
					height:auto;
					line-height:100%;
					outline:none;
					text-decoration:none;
					-ms-interpolation-mode:bicubic;
				}
				p{
					display:block;
					margin:13px 0;
				}
			@media only screen and (min-width:480px){
				.mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-250{
					width:250px !important;
					max-width:250px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-350{
					width:350px !important;
					max-width:350px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-200{
					width:200px !important;
					max-width:200px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-240{
					width:240px !important;
					max-width:240px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
		
		}		.moz-text-html .mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
				.moz-text-html .mj-column-px-250{
					width:250px !important;
					max-width:250px;
				}
				.moz-text-html .mj-column-px-350{
					width:350px !important;
					max-width:350px;
				}
				.moz-text-html .mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
				.moz-text-html .mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
				.moz-text-html .mj-column-px-200{
					width:200px !important;
					max-width:200px;
				}
				.moz-text-html .mj-column-px-240{
					width:240px !important;
					max-width:240px;
				}
				.moz-text-html .mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
				.moz-text-html .mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
			@media only screen and (max-width:480px){
				table.mj-full-width-mobile{
					width:100% !important;
				}
		
		}	@media only screen and (max-width:480px){
				td.mj-full-width-mobile{
					width:auto !important;
				}
		
		}</style></head>
		
		<body style="word-spacing:normal;background-color:#eeeeee;">
		  <div style="background-color:#eeeeee;">
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px;line-height:10px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#ABC1DE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#ABC1DE;background-color:#ABC1DE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ABC1DE;background-color:#ABC1DE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:rtl;font-size:0px;padding:20px 0;padding-bottom:35px;padding-top:35px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
								  <tbody>
									<tr>
									  <td style="width:153px;">
										<img mc:edit="main11" height="auto" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 153px;" width="153">
									  </td>
									</tr>
								  </tbody>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main12" style="font-family:Roboto;font-size:30px;font-weight:bold;line-height:1;text-align:left;color:#FFFFFF;">Hi  '.$name.',</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main13" style="font-family:Roboto;font-size:20px;line-height:1;text-align:left;color:#FFFFFF;">You Have A New Booking!</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#F8F8F8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#F8F8F8;background-color:#F8F8F8;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#F8F8F8;background-color:#F8F8F8;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:370px;" ><![endif]-->
					  <div class="mj-column-px-370 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="75"><img mc:edit="main14" src="' . $profile . '" width="75" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 75px;"></td>
									<td width="10"></td>
									<td mc:edit="main15" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:28px;font-weight:normal;">Client name:<br>
									<span style="color:#787f82; font-family:roboto,arial,sans-serif; font-size:15px; font-weight:normal; line-height:28px">' . $username . '</span></td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:270px;" ><![endif]-->
					  <div class="mj-column-px-270 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="20"><img mc:edit="main16" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 20px;"></td>
									<td width="10"></td>
									<td mc:edit="main17" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:28px;font-weight:bold;"> Manage this booking </td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:200px;" ><![endif]-->
					  <div class="mj-column-px-200 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main18" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Service:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main19" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#787f82;">' . $title . '
								</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:240px;" ><![endif]-->
					  <div class="mj-column-px-240 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main110" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Service Date and Time:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="13">
									  <img mc:edit="main111" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main112" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									' . $date . '
									</td>
								  </tr>
								  <tr>
									<td width="13">
									  <img mc:edit="main113" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main114" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									' . $time . '
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:200px;" ><![endif]-->
					  <div class="mj-column-px-200 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main115" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Order Number:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main116" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#787f82;">#' . $bookingId . '
								</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main117" style="font-family:Roboto;font-size:30px;font-weight:bold;line-height:1;text-align:left;color:#787F82;">Invoice</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr style="border-top: 1px solid #e3e3e3; border-bottom: 1px solid #e3e3e3;">
									<td mc:edit="main118" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> Service total cost </td>
									<td mc:edit="main119" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> £' . number_format($total, 2) . '
									</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main120" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> Deposit paid </td>
									<td mc:edit="main121" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> -£' . number_format($deposit, 2) . '
									</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main122" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> ATB Transaction Fees </td>
									<td mc:edit="main123" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> -£' . number_format($atb_fee, 2) . '
									</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main124" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787F82;line-height:40px; font-weight: bold;"> Payment Pending </td>
									<td mc:edit="main125" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787F82;line-height:40px; font-weight: bold;"> £' . number_format($total - $deposit +$atb_fee, 2) . '
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main126" style="font-family:Roboto;font-size:15px;line-height:21px;text-align:left;color:#787F82;">If you cannot fulfill the booking for any reason please get in touch with the client directly to reschedule.
		
		</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#A6BFDE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#A6BFDE;background-color:#A6BFDE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#A6BFDE;background-color:#A6BFDE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						<tbody>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main127" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff; text-decoration: none;">Terms and conditions</a></div>
							</td>
							</tr>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main128" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff; text-decoration: none;">Privacy Policy</a></div>
							</td>
							</tr>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main129" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="mailto:help@myatb.co.uk" style="color: #ffffff; text-decoration: none;">Contact Us</a></div>
							</td>
							</tr>
						</tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main130" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;">ATB All rights reserved</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px;line-height:10px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><![endif]-->
		  </div>
		<script type="text/javascript"  src="/OiORoJkI/HFD/Raf/ax9YTjB6tt/LYN9XQbL/SxBdJRdhAQ/KC/RFUwM-On4"></script></body>
		
		</html> ';

		$this->sendEmail(
			$to,
			$subject,
			$content);
	}

	private function ServiceemailToUser($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit,$atb_fee) {
		$subject = "Booking Confirmed";

		$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
  <title>*|MC:SUBJECT|*</title>
  <!--[if !mso]><!-->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!--<![endif]-->
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!--[if mso]>
        <noscript>
        <xml>
        <o:OfficeDocumentSettings>
          <o:AllowPNG/>
          <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
        </xml>
        </noscript>
        <![endif]-->
  <!--[if lte mso 11]>
        <style type="text/css">
          .mj-outlook-group-fix { width:100% !important; }
        </style>
        <![endif]-->
  <!--[if !mso]><!-->
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
  <!--<![endif]-->
  
  
  
<style type="text/css">
		#outlook a{
			padding:0;
		}
		body{
			margin:0;
			padding:0;
			-webkit-text-size-adjust:100%;
			-ms-text-size-adjust:100%;
		}
		table,td{
			border-collapse:collapse;
			mso-table-lspace:0pt;
			mso-table-rspace:0pt;
		}
		img{
			border:0;
			height:auto;
			line-height:100%;
			outline:none;
			text-decoration:none;
			-ms-interpolation-mode:bicubic;
		}
		p{
			display:block;
			margin:13px 0;
		}
	@media only screen and (min-width:480px){
		.mj-column-px-640{
			width:640px !important;
			max-width:640px;
		}

}	@media only screen and (min-width:480px){
		.mj-column-px-250{
			width:250px !important;
			max-width:250px;
		}

}	@media only screen and (min-width:480px){
		.mj-column-px-350{
			width:350px !important;
			max-width:350px;
		}

}	@media only screen and (min-width:480px){
		.mj-column-px-370{
			width:370px !important;
			max-width:370px;
		}

}	@media only screen and (min-width:480px){
		.mj-column-px-270{
			width:270px !important;
			max-width:270px;
		}

}	@media only screen and (min-width:480px){
		.mj-column-px-200{
			width:200px !important;
			max-width:200px;
		}

}	@media only screen and (min-width:480px){
		.mj-column-px-240{
			width:240px !important;
			max-width:240px;
		}

}	@media only screen and (min-width:480px){
		.mj-column-px-420{
			width:420px !important;
			max-width:420px;
		}

}	@media only screen and (min-width:480px){
		.mj-column-px-220{
			width:220px !important;
			max-width:220px;
		}

}		.moz-text-html .mj-column-px-640{
			width:640px !important;
			max-width:640px;
		}
		.moz-text-html .mj-column-px-250{
			width:250px !important;
			max-width:250px;
		}
		.moz-text-html .mj-column-px-350{
			width:350px !important;
			max-width:350px;
		}
		.moz-text-html .mj-column-px-370{
			width:370px !important;
			max-width:370px;
		}
		.moz-text-html .mj-column-px-270{
			width:270px !important;
			max-width:270px;
		}
		.moz-text-html .mj-column-px-200{
			width:200px !important;
			max-width:200px;
		}
		.moz-text-html .mj-column-px-240{
			width:240px !important;
			max-width:240px;
		}
		.moz-text-html .mj-column-px-420{
			width:420px !important;
			max-width:420px;
		}
		.moz-text-html .mj-column-px-220{
			width:220px !important;
			max-width:220px;
		}
	@media only screen and (max-width:480px){
		table.mj-full-width-mobile{
			width:100% !important;
		}

}	@media only screen and (max-width:480px){
		td.mj-full-width-mobile{
			width:auto !important;
		}

}</style></head>

<body style="word-spacing:normal;background-color:#eeeeee;">
  <div style="background-color:#eeeeee;">
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:640px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
              <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                  <tbody>
                    <tr>
                      <td style="font-size:0px;word-break:break-word;">
                        <div style="height:10px;line-height:10px;">&#8202;</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#ABC1DE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="background:#ABC1DE;background-color:#ABC1DE;margin:0px auto;max-width:640px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ABC1DE;background-color:#ABC1DE;width:100%;">
        <tbody>
          <tr>
            <td style="direction:rtl;font-size:0px;padding:20px 0;padding-bottom:35px;padding-top:35px;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
              <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
                  <tbody>
                    <tr>
                      <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                          <tbody>
                            <tr>
                              <td style="width:153px;">
                                <img mc:edit="main11" height="auto" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 153px;" width="153">
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
              <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
                  <tbody>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main12" style="font-family:Roboto;font-size:30px;font-weight:bold;line-height:1;text-align:left;color:#FFFFFF;">Hi '.$name.',</div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main13" style="font-family:Roboto;font-size:20px;line-height:1;text-align:left;color:#FFFFFF;">Please find below details of your booking</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#F8F8F8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="background:#F8F8F8;background-color:#F8F8F8;margin:0px auto;max-width:640px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#F8F8F8;background-color:#F8F8F8;width:100%;">
        <tbody>
          <tr>
            <td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:370px;" ><![endif]-->
              <div class="mj-column-px-370 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
                  <tbody>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
                          <tr>
                            <td width="75">	
								<img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%" class="CToWUd" data-bit="iit">
							</td>
                            <td width="10"></td>
                            <td mc:edit="main15" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:28px;font-weight:normal;">Business name:<br>
<span style="color:#787f82; font-family:roboto,arial,sans-serif; font-size:15px; font-weight:normal; line-height:28px">'.$username.'</span></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:270px;" ><![endif]-->
              <div class="mj-column-px-270 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
                  <tbody>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
                          <tr>
                            <td width="20"><img mc:edit="main16" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 20px;"></td>
                            <td width="10"></td>
                            <td mc:edit="main17" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:28px;font-weight:bold;"> Manage this booking </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
        <tbody>
          <tr>
            <td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:200px;" ><![endif]-->
              <div class="mj-column-px-200 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                  <tbody>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main18" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Service:</div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main19" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#787f82;">'.$title.'</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:240px;" ><![endif]-->
              <div class="mj-column-px-240 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                  <tbody>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main110" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Service Schedule:

</div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
                          <tr>
                            <td width="13">
                              <img mc:edit="main111" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
                            </td>
                            <td width="5"></td>
                            <td mc:edit="main112" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
							'.$date.'
                            </td>
                          </tr>
                          <tr>
                            <td width="13">
                              <img mc:edit="main113" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
                            </td>
                            <td width="5"></td>
                            <td mc:edit="main114" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
							'.$time.'
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:200px;" ><![endif]-->
              <div class="mj-column-px-200 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                  <tbody>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main115" style="font-family:Roboto;font-size:15px;font-weight:bold;line-height:1;text-align:left;color:#454B4D;">Order Number:</div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main116" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#787f82;">#'.$bookingId.'</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
              <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                  <tbody>
                    <tr>
                      <td style="font-size:0px;word-break:break-word;">
                        <div style="height:20px;line-height:20px;">&#8202;</div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main117" style="font-family:Roboto;font-size:30px;font-weight:bold;line-height:1;text-align:left;color:#787F82;">Invoice</div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
                          <tr style="border-top: 1px solid #e3e3e3; border-bottom: 1px solid #e3e3e3;">
                            <td mc:edit="main118" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> Service total cost </td>
                            <td mc:edit="main119" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;">£'.number_format($total, 2).'</td>
                          </tr>
                          <tr style="border-bottom: 1px solid #e3e3e3">
                            <td mc:edit="main120" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> Deposit paid </td>
                            <td mc:edit="main121" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> -£'.number_format($deposit, 2).'
                            </td>
                          </tr>
                          <tr style="border-bottom: 1px solid #e3e3e3">
                            <td mc:edit="main122" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#454B4D;line-height:40px;"> ATB Transaction Fees </td>
                            <td mc:edit="main123" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#A6BFDE;line-height:40px; font-weight: bold;"> -£'.number_format($atb_fee, 2).'
                            </td>
                          </tr>
                          <tr style="border-bottom: 1px solid #e3e3e3">
                            <td mc:edit="main124" align="left" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787F82;line-height:40px; font-weight: bold;"> Payment Pending </td>
                            <td mc:edit="main125" align="right" style="font-family:&#39;Roboto&#39, Arial, sans-serif; font-size:15px; color:#787F82;line-height:40px; font-weight: bold;"> £'.number_format($total-$deposit+$atb_fee, 2).'
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td style="font-size:0px;word-break:break-word;">
                        <div style="height:20px;line-height:20px;">&#8202;</div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main126" style="font-family:Roboto;font-size:15px;line-height:21px;text-align:left;color:#787F82;">If you can no longer attend or need to make amendments to the appointment, please get in touch with [business name] at the earliest.</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#A6BFDE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="background:#A6BFDE;background-color:#A6BFDE;margin:0px auto;max-width:640px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#A6BFDE;background-color:#A6BFDE;width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
              <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
                  <tbody>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main127" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff; text-decoration: none;">Terms and conditions</a></div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main128" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff; text-decoration: none;">Privacy Policy</a></div>
                      </td>
                    </tr>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main129" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="mailto:help@myatb.co.uk" style="color: #ffffff; text-decoration: none;">Contact Us</a></div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
              <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
                  <tbody>
                    <tr>
                      <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <div mc:edit="main130" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;">ATB All rights reserved</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:640px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
              <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                  <tbody>
                    <tr>
                      <td style="font-size:0px;word-break:break-word;">
                        <div style="height:10px;line-height:10px;">&#8202;</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]-->
  </div>
<script type="text/javascript"  src="/OiORoJkI/HFD/Raf/ax9YTjB6tt/LYN9XQbL/SxBdJRdhAQ/KC/RFUwM-On4"></script></body>

</html>';
		
		$this->sendEmail(
			$to,
			$subject,
			$content);
	}

    private function ProductemailToBusiness($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit,$atb_fee,$variation) {
		$subject = "Item Sold";

		$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<!doctype html>
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
		
		<head>
		  <title>*|MC:SUBJECT|*</title>
		  <!--[if !mso]><!-->
		  <meta http-equiv="X-UA-Compatible" content="IE=edge">
		  <!--<![endif]-->
		  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  
		  <!--[if mso]>
				<noscript>
				<xml>
				<o:OfficeDocumentSettings>
				  <o:AllowPNG/>
				  <o:PixelsPerInch>96</o:PixelsPerInch>
				</o:OfficeDocumentSettings>
				</xml>
				</noscript>
				<![endif]-->
		  <!--[if lte mso 11]>
				<style type="text/css">
				  .mj-outlook-group-fix { width:100% !important; }
				</style>
				<![endif]-->
		  <!--[if !mso]><!-->
		  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css">
		  <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
		  <!--<![endif]-->
		  
		  
		  
		<style type="text/css">
				#outlook a{
					padding:0;
				}
				body{
					margin:0;
					padding:0;
					-webkit-text-size-adjust:100%;
					-ms-text-size-adjust:100%;
				}
				table,td{
					border-collapse:collapse;
					mso-table-lspace:0pt;
					mso-table-rspace:0pt;
				}
				img{
					border:0;
					height:auto;
					line-height:100%;
					outline:none;
					text-decoration:none;
					-ms-interpolation-mode:bicubic;
				}
				p{
					display:block;
					margin:13px 0;
				}
			@media only screen and (min-width:480px){
				.mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-170{
					width:170px !important;
					max-width:170px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-130{
					width:130px !important;
					max-width:130px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-190{
					width:190px !important;
					max-width:190px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-150{
					width:150px !important;
					max-width:150px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
		
		}		.moz-text-html .mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
				.moz-text-html .mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
				.moz-text-html .mj-column-px-170{
					width:170px !important;
					max-width:170px;
				}
				.moz-text-html .mj-column-px-130{
					width:130px !important;
					max-width:130px;
				}
				.moz-text-html .mj-column-px-190{
					width:190px !important;
					max-width:190px;
				}
				.moz-text-html .mj-column-px-150{
					width:150px !important;
					max-width:150px;
				}
				.moz-text-html .mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
				.moz-text-html .mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
				.moz-text-html .mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
			@media only screen and (max-width:480px){
				table.mj-full-width-mobile{
					width:100% !important;
				}
		
		}	@media only screen and (max-width:480px){
				td.mj-full-width-mobile{
					width:auto !important;
				}
		
		}</style>
							<script>var w=window;if(w.performance||w.mozPerformance||w.msPerformance||w.webkitPerformance){var d=document;AKSB=w.AKSB||{},AKSB.q=AKSB.q||[],AKSB.mark=AKSB.mark||function(e,_){AKSB.q.push(["mark",e,_||(new Date).getTime()])},AKSB.measure=AKSB.measure||function(e,_,t){AKSB.q.push(["measure",e,_,t||(new Date).getTime()])},AKSB.done=AKSB.done||function(e){AKSB.q.push(["done",e])},AKSB.mark("firstbyte",(new Date).getTime()),AKSB.prof={custid:"641075",ustr:"",originlat:"0",clientrtt:"6",ghostip:"88.221.135.63",ipv6:false,pct:"10",clientip:"81.135.131.217",requestid:"12741182",region:"43868",protocol:"h2",blver:14,akM:"x",akN:"ae",akTT:"O",akTX:"1",akTI:"12741182",ai:"441803",ra:"false",pmgn:"",pmgi:"",pmp:"",qc:""},function(e){var _=d.createElement("script");_.async="async",_.src=e;var t=d.getElementsByTagName("script"),t=t[t.length-1];t.parentNode.insertBefore(_,t)}(("https:"===d.location.protocol?"https:":"http:")+"//ds-aksb-a.akamaihd.net/aksb.min.js")}</script>
							</head>
		
		<body style="word-spacing:normal;background-color:#eeeeee;">
		  <div style="background-color:#eeeeee;">
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px0px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#ABC1DE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#ABC1DE;background-color:#ABC1DE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ABC1DE;background-color:#ABC1DE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:rtl;font-size:0px;padding:20px 0;padding-bottom:35px;padding-top:35px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
								  <tbody>
									<tr>
									  <td style="width:153px;">
										<img mc:edit="main11" height="auto" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 153px;" width="153">
									  </td>
									</tr>
								  </tbody>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main12" style="font-family:Roboto;font-size:30px;font-weight:bold;text-align:left;color:#FFFFFF;">Hi '.$name.',</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main13" style="font-family:Roboto;font-size:20px;text-align:left;color:#FFFFFF;">You have received payment for your item.<br>
		Please find below details of the sale.</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#F8F8F8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#F8F8F8;background-color:#F8F8F8;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#F8F8F8;background-color:#F8F8F8;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:370px;" ><![endif]-->
					  <div class="mj-column-px-370 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="75">
										<img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%" class="CToWUd" data-bit="iit">
									</td>
									<td width="10"></td>
									<td mc:edit="main14" style="font-family:Roboto; font-size:15px; color:#454B4D;line-height:28px;font-weight:bold;">Buyer name:<br>
		<span style="color:#787f82; font-family:roboto; font-size:15px; font-weight:normal; line-height:28px">'.$username.'</span></td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:270px;" ><![endif]-->
					  <div class="mj-column-px-270 mj-outlook-group-fix" style="font-size:0px;text-alin:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="20"><img mc:edit="main15" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 20px"></td>
									<td width="10"></td>
									<td mc:edit="main16" style="font-family:Roboto; font-size:15px; color:#454B4D;line-height:28px;font-weight:bold;">Manage Sale</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:170px;" ><![endif]-->
					  <div class="mj-column-px-170 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main17" style="font-family:Roboto;font-size:15px;font-weight:bold;text-align:left;color:#454B4D;">Product name:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main18" style="font-family:Roboto;font-size:15px;text-align:left;color:#787f82;">'.$title.'</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:130px;" ><![endif]-->
					  <div class="mj-column-px-130 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main19" style="font-family:Roboto;font-size:15px;font-weight:bold;text-align:left;color:#454B4D;">Variation:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main110" style="font-family:Roboto;font-size:15px;text-align:left;color:#787f82;">'.$variation.'</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:190px;" ><![endif]-->
					  <div class="mj-column-px-190 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main111" style="font-family:Roboto;font-size:15px;font-weight:bold;text-align:left;color:#454B4D;">Purchase Date:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="13">
									  <img mc:edit="main112" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main113" style="font-family:Roboto; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									'.$date.' 
									</td>
								  </tr>
								  <tr>
									<td width="13">
									  <img mc:edit="main114" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main115" style="font-family:Roboto; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									'.$time.'
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:150px;" ><![endif]-->
					  <div class="mj-column-px-150 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main116" style="font-family:Roboto;font-size:15px;font-weight:bold;text-align:left;color:#454B4D;">Order Number:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main117" style="font-family:Roboto;font-size:15px;text-align:left;color:#787f82;">#'.$bookingId.'</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main118" style="font-family:Roboto;font-size:30px;font-weight:bold;text-align:left;color:#787F82;">Invoice</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr style="border-top: 1px solid #e3e3e3; border-bottom: 1px solid #e3e3e3;">
									<td mc:edit="main119" align="left" style="font-family:Roboto; font-size:15px; color:#454B4D;line-height:21px; padding-top: 10px; padding-bottom: 10px;">Product Cost	</td>
									<td mc:edit="main120" align="right" style="font-family:Roboto; font-size:15px; color:#A6BFDE;line-height:21px; font-weight: bold; padding-top: 10px; padding-bottom: 10px;">£'.number_format($total, 2).'</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main121" align="left" style="font-family:Roboto; font-size:15px; color:#454B4D;line-height:21px; padding-top: 10px; padding-bottom: 10px;">Postage Cost	</td>
									<td mc:edit="main122" align="right" style="font-family:Roboto; font-size:15px; color:#A6BFDE;line-height:21px; font-weight: bold; padding-top: 10px; padding-bottom: 10px;">£'.number_format($deposit, 2).'</td>
								  </tr>

								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main125" align="left" style="font-family:Roboto; font-size:15px; color:#787F82;line-height:21px; font-weight: bold; padding-top: 10px; padding-bottom: 10px;">Total Cost	</td>
									<td mc:edit="main126" align="right" style="font-family:Roboto; font-size:15px; color:#787F82;line-height:21px; font-weight: bold; padding-top: 10px; padding-bottom: 10px;">£'.number_format($total+$deposit, 2).'</td>
								  </tr>
								</table>
							  </td>
							</tr>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main127" style="font-family:Roboto;font-size:15px;line-height:24px;text-align:left;color:#787F82;">If not yet received, get in touch with the buyer via direct message to confirm the correct delivery address.
		
		</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#A6BFDE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#A6BFDE;background-color:#A6BFDE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#A6BFDE;background-color:#A6BFDE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main128" style="font-family:Roboto;font-size:15px;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff; text-decoration: none;">Terms and conditions</a></div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main129" style="font-family:Roboto;font-size:15px;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff; text-decoration: none;">Privacy Policy</a></div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main130" style="font-family:Roboto;font-size:15px;text-align:left;color:#FFFFFF;"><a href="mailto:help@myatb.co.uk" style="color: #ffffff; text-decoration: none;">Contact Us</a></div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main1311" style="font-family:Roboto;font-size:15px;text-align:left;color:#FFFFFF;">ATB All rights reserved</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px0px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><![endif]-->
		  </div>
		<script type="text/javascript"  src="/OiORoJkI/HFD/Raf/ax9YTjB6tt/LYN9XQbL/SxBdJRdhAQ/KC/RFUwM-On4"></script></body>
		
		</html>';

		$this->sendEmail(
			$to,
			$subject,
			$content);
	}

	private function sendBuyerEmail($to, $bookingId, $profile, $name, $username, $title, $date, $time, $total, $deposit,$atb_fee,$variation) {
		$subject = "Item Purchased";

		$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<!doctype html>
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
		
		<head>
		  <title>*|MC:SUBJECT|*</title>
		  <!--[if !mso]><!-->
		  <meta http-equiv="X-UA-Compatible" content="IE=edge">
		  <!--<![endif]-->
		  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		  <meta name="viewport" content="width=device-width, initial-scale=1">
		  
		  <!--[if mso]>
				<noscript>
				<xml>
				<o:OfficeDocumentSettings>
				  <o:AllowPNG/>
				  <o:PixelsPerInch>96</o:PixelsPerInch>
				</o:OfficeDocumentSettings>
				</xml>
				</noscript>
				<![endif]-->
		  <!--[if lte mso 11]>
				<style type="text/css">
				  .mj-outlook-group-fix { width:100% !important; }
				</style>
				<![endif]-->
		  <!--[if !mso]><!-->
		  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css">
		  <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
		  <!--<![endif]-->
		  
		  
		  
		<style type="text/css">
				#outlook a{
					padding:0;
				}
				body{
					margin:0;
					padding:0;
					-webkit-text-size-adjust:100%;
					-ms-text-size-adjust:100%;
				}
				table,td{
					border-collapse:collapse;
					mso-table-lspace:0pt;
					mso-table-rspace:0pt;
				}
				img{
					border:0;
					height:auto;
					line-height:100%;
					outline:none;
					text-decoration:none;
					-ms-interpolation-mode:bicubic;
				}
				p{
					display:block;
					margin:13px 0;
				}
			@media only screen and (min-width:480px){
				.mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-170{
					width:170px !important;
					max-width:170px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-130{
					width:130px !important;
					max-width:130px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-190{
					width:190px !important;
					max-width:190px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-150{
					width:150px !important;
					max-width:150px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
		
		}	@media only screen and (min-width:480px){
				.mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
		
		}		.moz-text-html .mj-column-px-270{
					width:270px !important;
					max-width:270px;
				}
				.moz-text-html .mj-column-px-370{
					width:370px !important;
					max-width:370px;
				}
				.moz-text-html .mj-column-px-170{
					width:170px !important;
					max-width:170px;
				}
				.moz-text-html .mj-column-px-130{
					width:130px !important;
					max-width:130px;
				}
				.moz-text-html .mj-column-px-190{
					width:190px !important;
					max-width:190px;
				}
				.moz-text-html .mj-column-px-150{
					width:150px !important;
					max-width:150px;
				}
				.moz-text-html .mj-column-px-640{
					width:640px !important;
					max-width:640px;
				}
				.moz-text-html .mj-column-px-420{
					width:420px !important;
					max-width:420px;
				}
				.moz-text-html .mj-column-px-220{
					width:220px !important;
					max-width:220px;
				}
			@media only screen and (max-width:480px){
				table.mj-full-width-mobile{
					width:100% !important;
				}
		
		}	@media only screen and (max-width:480px){
				td.mj-full-width-mobile{
					width:auto !important;
				}
		
		}</style></head>
		
		<body style="word-spacing:normal;background-color:#eeeeee;">
		  <div style="background-color:#eeeeee;">
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px0px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#ABC1DE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#ABC1DE;background-color:#ABC1DE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ABC1DE;background-color:#ABC1DE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:rtl;font-size:0px;padding:20px 0;padding-bottom:35px;padding-top:35px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
								  <tbody>
									<tr>
									  <td style="width:153px;">
										<img mc:edit="main11" height="auto" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/58bfffbc-ae54-a2bf-36b0-e2d695d19418.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 153px;" width="153">
									  </td>
									</tr>
								  </tbody>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main12" style="font-family:Roboto;font-size:30px;font-weight:bold;text-align:left;color:#FFFFFF;">Hi '.$username.',</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main13" style="font-family:Roboto;font-size:20px;text-align:left;color:#FFFFFF;">Your payment has been received.<br>
		Please find below details of your order.</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#F8F8F8" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#F8F8F8;background-color:#F8F8F8;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#F8F8F8;background-color:#F8F8F8;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:370px;" ><![endif]-->
					  <div class="mj-column-px-370 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="75">
											<img src="'.$profile.'" width="73" height="73" border="0" alt="user icon" style="border-radius:100%" class="CToWUd" data-bit="iit">
									</td>
									<td width="10"></td>
									<td mc:edit="main14" style="font-family:Roboto; font-size:15px; color:#454B4D;line-height:28px;font-weight:bold;">Seller name:<br>
		<span style="color:#787f82; font-family:roboto; font-size:15px; font-weight:normal; line-height:28px">'.$name.'</span></td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:270px;" ><![endif]-->
					  <div class="mj-column-px-270 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="20"><img mc:edit="main15" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/ff683c55-7e1d-de42-ebae-a6a85b9d65e3.png" width="20" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 20px"></td>
									<td width="10"></td>
									<td mc:edit="main16" style="font-family:Roboto; font-size:15px; color:#454B4D;line-height:28px;font-weight:bold;">Manage Purchase</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="border-bottom:1px solid #eeeeee;direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:170px;" ><![endif]-->
					  <div class="mj-column-px-170 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main17" style="font-family:Roboto;font-size:15px;font-weight:bold;text-align:left;color:#454B4D;">Product name:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main18" style="font-family:Roboto;font-size:15px;text-align:left;color:#787f82;">'.$title.'</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:130px;" ><![endif]-->
					  <div class="mj-column-px-130 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main19" style="font-family:Roboto;font-size:15px;font-weight:bold;text-align:left;color:#454B4D;">Variation:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main110" style="font-family:Roboto;font-size:15px;text-align:left;color:#787f82;">'.$variation.'</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:190px;" ><![endif]-->
					  <div class="mj-column-px-190 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main111" style="font-family:Roboto;font-size:15px;font-weight:bold;text-align:left;color:#454B4D;">Purchase Date:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr>
									<td width="13">
									  <img mc:edit="main112" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/cc0f5ed0-be91-08d3-61c6-770791b89c82.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main113" style="font-family:Roboto; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									'.$date.'
									</td>
								  </tr>
								  <tr>
									<td width="13">
									  <img mc:edit="main114" src="https://mcusercontent.com/174192f191938a935a9ebfdb2/images/724c211d-2972-389d-54ca-82876374d86c.png" width="13" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px; max-width: 13px;">
									</td>
									<td width="5"></td>
									<td mc:edit="main115" style="font-family:Roboto; font-size:15px; color:#787f82;line-height:28px;font-weight:normal;">
									'.$time.'
									</td>
								  </tr>
								</table>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:150px;" ><![endif]-->
					  <div class="mj-column-px-150 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main116" style="font-family:Roboto;font-size:15px;font-weight:bold;text-align:left;color:#454B4D;">Order Number:</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main117" style="font-family:Roboto;font-size:15px;text-align:left;color:#787f82;">#'.$bookingId.'
								</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#FFFFFF" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#FFFFFF;background-color:#FFFFFF;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#FFFFFF;background-color:#FFFFFF;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main118" style="font-family:Roboto;font-size:30px;font-weight:bold;text-align:left;color:#787F82;">Invoice</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
								  <tr style="border-top: 1px solid #e3e3e3; border-bottom: 1px solid #e3e3e3;">
									<td mc:edit="main119" align="left" style="font-family:Roboto; font-size:15px; color:#454B4D;line-height:21px; padding-top: 10px; padding-bottom: 10px;">Product Cost	</td>
									<td mc:edit="main120" align="right" style="font-family:Roboto; font-size:15px; color:#A6BFDE;line-height:21px; font-weight: bold; padding-top: 10px; padding-bottom: 10px;">£'.number_format($total, 2).'
		</td>
								  </tr>
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main121" align="left" style="font-family:Roboto; font-size:15px; color:#454B4D;line-height:21px; padding-top: 10px; padding-bottom: 10px;">Postage Cost	</td>
									<td mc:edit="main122" align="right" style="font-family:Roboto; font-size:15px; color:#A6BFDE;line-height:21px; font-weight: bold; padding-top: 10px; padding-bottom: 10px;">£'.number_format($deposit, 2).'</td>
								  </tr>								  
								  <tr style="border-bottom: 1px solid #e3e3e3">
									<td mc:edit="main125" align="left" style="font-family:Roboto; font-size:15px; color:#787F82;line-height:21px; font-weight: bold; padding-top: 10px; padding-bottom: 10px;">Total Cost	</td>
									<td mc:edit="main126" align="right" style="font-family:Roboto; font-size:15px; color:#787F82;line-height:21px; font-weight: bold; padding-top: 10px; padding-bottom: 10px;">£'.number_format($total+$deposit, 2).'</td>
								  </tr>
								</table>
							  </td>
							</tr>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:20px;line-height:20px;">&#8202;</div>
							  </td>
							</tr>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main127" style="font-family:Roboto;font-size:15px;line-height:24px;text-align:left;color:#787F82;">If you have not already done so, remember to share your best delivery address with the seller via direct message.
		
		</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" bgcolor="#A6BFDE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="background:#A6BFDE;background-color:#A6BFDE;margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#A6BFDE;background-color:#A6BFDE;width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:middle;width:420px;" ><![endif]-->
					  <div class="mj-column-px-420 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						<tbody>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main127" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" style="color: #ffffff; text-decoration: none;">Terms and conditions</a></div>
							</td>
							</tr>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main128" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" style="color: #ffffff; text-decoration: none;">Privacy Policy</a></div>
							</td>
							</tr>
							<tr>
							<td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main129" style="font-family:Roboto;font-size:15px;line-height:1;text-align:left;color:#FFFFFF;"><a href="mailto:help@myatb.co.uk" style="color: #ffffff; text-decoration: none;">Contact Us</a></div>
							</td>
							</tr>
						</tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td><td class="" style="vertical-align:middle;width:220px;" ><![endif]-->
					  <div class="mj-column-px-220 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:middle;" width="100%">
						  <tbody>
							<tr>
							  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
								<div mc:edit="main1311" style="font-family:Roboto;font-size:15px;text-align:left;color:#FFFFFF;">ATB All rights reserved</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:640px;" width="640" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
			<div style="margin:0px auto;max-width:640px;">
			  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
				  <tr>
					<td style="direction:ltr;font-size:0px;padding:0px;text-align:center;">
					  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:640px;" ><![endif]-->
					  <div class="mj-column-px-640 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
						  <tbody>
							<tr>
							  <td style="font-size:0px;word-break:break-word;">
								<div style="height:10px0px;">&#8202;</div>
							  </td>
							</tr>
						  </tbody>
						</table>
					  </div>
					  <!--[if mso | IE]></td></tr></table><![endif]-->
					</td>
				  </tr>
				</tbody>
			  </table>
			</div>
			<!--[if mso | IE]></td></tr></table><![endif]-->
		  </div>
		<script type="text/javascript"  src="/OiORoJkI/HFD/Raf/ax9YTjB6tt/LYN9XQbL/SxBdJRdhAQ/KC/RFUwM-On4"></script></body>
		
		</html>
         ';
		
		$this->sendEmail(
			$to,
			$subject,
			$content);
	}
}
