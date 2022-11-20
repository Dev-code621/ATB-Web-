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
                        
                        $subject = 'ATB Business account created';
                        $content = '
                            <p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">
                                Your business account has now been created. Please find the terms and conditions below</span>
                            </p>
                            <p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;">
                                <span style="color: #808080; font-size: 18px;"><b></b></span>
                            </p>';

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
                                }

                                // send emails if it's required here

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
}
