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

        case 'payment_intent.processing':
            $paymentIntent = $event->data->object;

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
            $userId = $transaction['user_id'];

            $subject = null;
            $content = null;

            $paymentDetails = $paymentIntent->charges->data[0]->payment_method_details;
            $update = array(
                'invoice' => $paymentIntent->invoice,
                'payment_method' => $paymentDetails->type,
                'payment_brand' => $paymentDetails->card->brand,
                'payment_source' => $paymentDetails->card->last4,
                'status' => 1,
                'updated_at' => time()
            );

            $purchaseType = $transaction['purchase_type'];
            if ($purchaseType === 'subscription') {
                $this->UserBusiness_model->updateBusinessRecord(
                    array('paid' => 1, 'updated_at' => time()),
                    array('user_id' => $userId)
                );
                
                $subject = 'ATB Business account created';
                $content = '
                    <p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">
                        Your business account has now been created. Please find the terms and conditions below</span>
                    </p>
                    <p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;">
                        <span style="color: #808080; font-size: 18px;"><b></b></span>
                    </p>';

            } else if ($purchaseType === 'service' || $purchaseType === 'booking') {
                $bookingId = $transaction['target_id'];

                if ($purchaseType === 'service') {                    
                    $this->Booking_model->updateBooking(
                        array('state' => 'active'),
                        array('id' => $bookingId)
                    );

                    $bookings = $this->Booking_model->getBooking($bookingId);
                    if (count($bookings)) {
                        $services= $this->UserService_model->getServiceInfo($bookings[0]['service_id']);
                        $users = $this->User_model->getOnlyUser(array('id' => $userId));
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
                    }
                }

             } else {
                /** product or product_variant */
            }

            $this->UserTransaction_model->update($update,
                array(
                    'transaction_id' => $paymentIntentId
                ));

            if ($subject && $content) {
                $users = $this->User_model->getOnlyUser(array('id' => $userId));
                $this->sendEmail($users[0]["user_email"], $subject, $content);
            }

            echo 'payment intent successful:' . $paymentIntentId;

        } else {
            echo 'Not found the transaction:' . $paymentIntentId;
        }
      }
}
