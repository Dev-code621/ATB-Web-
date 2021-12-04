<?php

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\Authorization;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Capture;

class AuctionController extends MY_Controller {
    
    public function getAuctions() {
        $verifyTokenResult = $this->verificationToken($this->input->post('token'));
          
        $retVal = [];
        if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
            $auctions = array();
              
            $where = array();
            $where['type'] = $this->input->post('type');
            $where['category'] = $this->input->post('category');            
            $where['status'] = '1'; // authorized auctions
              
            $auction_type = $this->input->post('type');
            if ($auction_type == '0') {
                for ($position = 0; $position < 2; $position++) {
                    $where['position'] = $position;
                      
                    $country = $this->input->post('country');
                    if (!is_null($country)) {
                        $whereWithCountry = $where;
                        $whereWithCountry['country'] = $country; 
                          
                        $countryAuctions = $this->Auction_model->getProfilePinAuctions($whereWithCountry);
                          
                        if (count($countryAuctions) > 0) {
                            $countryAuctions[0]['total_bids']  = count($countryAuctions);
                            $countryAuctions[0]['bidon'] = '0';
                            $users = $this->User_model->getOnlyUser(array('id' => $countryAuctions[0]['user_id']));
                            if (count($users) > 0 ) {
                                $countryAuctions[0]['user'] = $users[0];
                            }
                            
                            array_push($auctions, $countryAuctions[0]);
                        } 
                    }
                      
                    $county = $this->input->post('county');
                    if (!is_null($county)) {
                        $whereWithCounty = $where;
                        $whereWithCounty['county'] = $county; 
                          
                        $countyAuctions = $this->Auction_model->getProfilePinAuctions($whereWithCounty);
                          
                        if (count($countyAuctions) > 0) {
                            $countyAuctions[0]['total_bids']  = count($countyAuctions);
                            $countyAuctions[0]['bidon'] = '1';
                            $users = $this->User_model->getOnlyUser(array('id' => $countyAuctions[0]['user_id']));
                            if (count($users) > 0 ) {
                                $countyAuctions[0]['user'] = $users[0];
                            }
                            
                            array_push($auctions, $countyAuctions[0]);
                        }
                    }
                      
                    $region = $this->input->post('region');
                    if (!is_null($region)) {
                        $whereWithRegion = $where;
                        $whereWithRegion['region'] = $region; 
                          
                        $regionAuctions = $this->Auction_model->getProfilePinAuctions($whereWithRegion);
                          
                        if (count($regionAuctions) > 0) {
                            $regionAuctions[0]['total_bids']  = count($regionAuctions);
                            $regionAuctions[0]['bidon'] = '2';
                            $users = $this->User_model->getOnlyUser(array('id' => $regionAuctions[0]['user_id']));
                            if (count($users) > 0 ) {
                                $regionAuctions[0]['user'] = $users[0];
                            }
                            
                            array_push($auctions, $regionAuctions[0]);
                        }
                    }                      
                }
                  
            } else {
                $tag = "";
                if (!is_null($this->input->post('tags'))) {
                    $tag = $this->input->post('tags');
                } 
                
                for ($position = 0; $position < 5; $position++) {
                    $where['position'] = $position;
                        
                    $pointAuctions = $this->Auction_model->getPinPointAuctions($where, $tag);
                    if (count($pointAuctions) > 0) {
                        $pointAuctions[0]['total_bids']  = count($pointAuctions);
                        $users = $this->User_model->getOnlyUser(array('id' => $pointAuctions[0]['user_id']));
                        if (count($users) > 0 ) {
                            $pointAuctions[0]['user'] = $users[0];
                        }
                            
                        array_push($auctions, $pointAuctions[0]);
                    }                         
                }
            }
                            
            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::EXTRA_FIELD_NAME] = $auctions;
            
        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
            $retVal[self::EXTRA_FIELD_NAME] = null;
        }

        echo json_encode($retVal);          
    }
      
    public function getProfilePins() {
        $verifyTokenResult = $this->verificationToken($this->input->post('token'));
        
        $retVal = [];
        if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
            $auctions = array();
            
            $user_id = $verifyTokenResult['id'];
            /*get the user country, county, region*/
            $users = $this->User_model->getOnlyUser(array('id' => $user_id));
            $locations = explode(",", $users[0]['country']);
            
            $user_region = "";
            $user_county = "";
            $user_country = "";
            
            if (count($locations) > 2) {
                $user_region = trim($locations[0]);
                $user_county = trim($locations[1]);
                $user_country = trim($locations[2]);
                
            } else {
                $user_region = trim($locations[0]);
                $user_country = trim($locations[1]);
            }
            
            $category_title = trim($this->input->post('category'));
            
            $where = array();
            $where['type'] = '0';
            $where['status'] = '2';     // active auctions
            
            $categories = array();
            if ($category_title == 'My ATB') {
                $allCategories = $this->Category_model->getCategories();
                foreach($allCategories as $category) {
                    array_push($categories, trim($category['description']));
                }
                
            } else {
                array_push($categories, $category_title);
            }
            
            foreach($categories as $category) {
                $where['category'] = $category;
                for($position = 0; $position < 2; $position++) {
                    $where['position'] = $position;
                    if (!empty($user_country)) {
                        $whereWithCountry = $where;
                        $whereWithCountry['country'] = $user_country; 
                          
                        $countryAuctions = $this->Auction_model->getProfilePinAuctions($whereWithCountry);
                          
                        if (count($countryAuctions) > 0) {
                            $countryAuctions[0]['bidon'] = '0';
                            $users = $this->User_model->getOnlyUser(array('id' => $countryAuctions[0]['user_id']));
                            if (count($users) > 0 ) {
                                $countryAuctions[0]['user'] = $users[0];
                            }
                            
                            array_push($auctions, $countryAuctions[0]);
                        } 
                    }
                    
                    if (!is_null($user_county)) {
                        $whereWithCounty = $where;
                        $whereWithCounty['county'] = $user_county; 
                          
                        $countyAuctions = $this->Auction_model->getProfilePinAuctions($whereWithCounty);
                          
                        if (count($countyAuctions) > 0) {
                            $countyAuctions[0]['bidon'] = '1';
                            $users = $this->User_model->getOnlyUser(array('id' => $countyAuctions[0]['user_id']));
                            if (count($users) > 0 ) {
                                $countyAuctions[0]['user'] = $users[0];
                            }
                            
                            array_push($auctions, $countyAuctions[0]);
                        }
                    }
                  
                    if (!is_null($user_region)) {
                        $whereWithRegion = $where;
                        $whereWithRegion['region'] = $user_region; 
                          
                        $regionAuctions = $this->Auction_model->getProfilePinAuctions($whereWithRegion);
                          
                        if (count($regionAuctions) > 0) {
                            $regionAuctions[0]['bidon'] = '2';
                            $users = $this->User_model->getOnlyUser(array('id' => $regionAuctions[0]['user_id']));
                            if (count($users) > 0 ) {
                                $regionAuctions[0]['user'] = $users[0];
                            }
                            
                            array_push($auctions, $regionAuctions[0]);
                        }
                    }
                }
            }
            
            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::EXTRA_FIELD_NAME] = $auctions;
            
        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
            $retVal[self::EXTRA_FIELD_NAME] = null;
        }
        
        echo json_encode($retVal);      
    }                                
      
    public function placeBid() {
        $verifyTokenResult = $this->verificationToken($this->input->post('token'));
          
        $retVal = [];
        if ($verifyTokenResult[self::RESULT_FIELD_NAME]) { 
            $where = array();
            $where['user_id'] = $verifyTokenResult['id'];
            $type = $this->input->post('type');
            $where['type'] = $type;
            $where['category'] = $this->input->post('category');
            $where['position'] = $this->input->post('position');
            $where['status'] = '1'; // authorized & active 
            
            $bidAmount = $this->input->post('price');
            
            $existingAuctions = array();            
            if ($type == '0') {
                if (!is_null($this->input->post('country'))) {
                    $where['country'] = $this->input->post('country');                  
                }
                  
                if (!is_null($this->input->post('county'))) {
                    $where['county'] = $this->input->post('county');                  
                }
                  
                if (!is_null($this->input->post('region'))) {
                    $where['region'] = $this->input->post('region');                  
                }
            
                $existingAuctions = $this->Auction_model->getProfilePinAuctions($where);
                
            } else {
                $tag = "";
                if (!is_null($this->input->post('tags'))) {
                    $tag = $this->input->post('tags');
                }
                
                $existingAuctions = $this->Auction_model->getPinPointAuctions($where, $tag);
            }
            
            $auctionId = "";
            // update if there is existing bids
            if (count($existingAuctions) > 0) {                
            //    $update = array();
            //    $update['price'] = $bidAmount;
            //    $update['updated_at'] = time();
               
                // need to update once a payment has been authorized
                $auctionId = $existingAuctions[0]['id'];
            //    $this->Auction_model->updateAuction($update, array('id' => $existingAuctions[0]['id']));
                                
                $retVal[self::MESSAGE_FIELD_NAME] = "Your bid has been updated successfully.";                 
                    
            } else {
                // make a template bid
                $insert = array();
                $insert['user_id'] = $verifyTokenResult['id'];
                $insert['type'] = $type;
                $insert['category'] = $this->input->post('category');
                $insert['position'] = $this->input->post('position');
                $insert['price'] = $bidAmount;                
                $insert['tags'] = $this->input->post('tags');
                $insert['country'] = $this->input->post('country');
                $insert['county'] = $this->input->post('county');
                $insert['region'] = $this->input->post('region');
                $insert['created_at'] = time();
                $insert['updated_at'] = time();
                    
                $auctionId = $this->Auction_model->insertNewAuction($insert);
                
                $retVal[self::MESSAGE_FIELD_NAME] = "Your bid has been placed successfully.";
            }
            
            $bidAmount = $this->input->post('price');
            
            // Create a paypal authorization    
            // Step 1 
            $apiContext = $this->getApiContext();
            
            // Step 2 - let's try to authorize a payment
            $payer = new Payer();
            $payer->setPaymentMethod("paypal");
            
            $item1 = new Item();
            $item1->setName('Business Boost Payment')
                ->setCurrency('GBP')
                ->setQuantity(1)
                ->setPrice($bidAmount);
                
            $itemList = new ItemList();
            $itemList->setItems(array($item1));
            
            $details = new Details();
            $details->setShipping(0)
                ->setTax(0)
                ->setSubtotal($bidAmount);
                
            $amount = new Amount();
            $amount->setCurrency("GBP")
                ->setTotal($bidAmount)
                ->setDetails($details);
                
            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription("Payment to boost your business profile")
                ->setInvoiceNumber(uniqid());
                
            $baseUrl = base_url();
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($baseUrl."api/auction/payment?success=true&auctionId=".$auctionId)
                        ->setCancelUrl($baseUrl."api/auction/payment?success=false");
                
            $payment = new Payment();
            $payment->setIntent("authorize")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction));
                
        //    $request = clone $payment;
            
            try {
                $payment->create($apiContext);
                
            } catch (Exception $ex) {
                $retVal[self::RESULT_FIELD_NAME] = false;
                $retVal[self::MESSAGE_FIELD_NAME] = "It's been failed to create your payment authorization.";
                $retVal[self::EXTRA_FIELD_NAME] = null;
                
                echo json_encode($retVal);
                exit(1);
            }                                                               
            
            $retVal[self::EXTRA_FIELD_NAME] = array('approval_link' => $payment->getApprovalLink());      
            $retVal[self::RESULT_FIELD_NAME] = true;              
            
        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
            $retVal[self::EXTRA_FIELD_NAME] = null;
        }

        echo json_encode($retVal);
    }
    
    // api/auction/payment?success=true&auctionId=<{auctionId}>&paymentId=PAYID-MCO32XQ5V4355927L451470S&token=EC-8G146981156514415&PayerID=B34NUV9TFAX6W
    // api/auction/payment?success=false&paymentId=PAYID-MCO32XQ5V4355927L451470S&token=EC-8G146981156514415&PayerID=B34NUV9TFAX6W
    public function executePayment() {
        if (isset($_GET['success']) && $_GET['success'] == 'true') {
            // Step 1 
            $apiContext = $this->getApiContext();
            
            // excute a new authorization            
            $paymentId = $this->input->get('paymentId');
            $payment = Payment::get($paymentId, $apiContext);
            
            $execution = new PaymentExecution();
            $execution->setPayerid($this->input->get('PayerID'));
            
            try {                
                $result = $payment->execute($execution, $apiContext);
                                
                try {
                    $payment = Payment::get($paymentId, $apiContext);
                    
                } catch (Exception $ex) {
                    redirect('myatb://payment/cancel');
                    exit();
                }
                
            } catch (Exception $ex) {
                redirect('myatb://payment/cancel');
                exit();
            }
            
            $auctionId = $this->input->get('auctionId');
            // void an old authroized payment if it exists
            $auction = $this->Auction_model->getAuctionById($auctionId);
            if (isset($auction['authorization_id']) && !is_null($auction['authorization_id']) && !empty($auction['authorization_id'])) {
                try {
                    $authorization = Authorization::get($auction['authorization_id'], $apiContext);
                    $authorization->void($apiContext);
                    
                } catch (Exception $ex) {
                    print_r($ex);
                }
            }
            
            $transaction = $payment->getTransactions()[0];
                        
            $update = array();
            $update['price'] = $transaction->getAmount()->getTotal();
            $update['payment_id'] = $payment->getId();
            $update['authorization_id'] = $transaction->getRelatedResources()[0]->getAuthorization()->getId();
            $update['status'] = '1'; // authorized
            $update['updated_at'] = time();
            
            $this->Auction_model->updateAuction($update, array('id' => $auctionId));

            // A new authorized bid has been created, creating outbid notifications
            // 1. creating query filters
            $type = $auction['type'];

            $where = array();
            $where['type'] = $type;
            $where['category'] = $auction['category'];
            $where['position'] = $auction['position'];
            $where['status'] = '1'; // authorized & active 

            $activeAuctions = array();           
            if ($type == '0') {
                if (!is_null($auction['country'])) {
                    $where['country'] = $auction['country'];                  
                }
                  
                if (!is_null($auction['county'])) {
                    $where['county'] = $auction['county'];                  
                }
                  
                if (!is_null($auction['region'])) {
                    $where['region'] = $auction['region'];                  
                }
            
                $activeAuctions = $this->Auction_model->getProfilePinAuctions($where);
                
            } else {
                $tag = "";
                if (!is_null($auction['tags'])) {
                    $tag = $auction['tags'];
                }
                
                $activeAuctions = $this->Auction_model->getPinPointAuctions($where, $tag);
            }

            $userId = $auction['user_id'];
            foreach($activeAuctions as $key => $activeAuction) {
                if ($key != 0) {
                    $this->NotificationHistory_model->insertNewNotification(
                        array(
                            'user_id' => $activeAuction['user_id'],
                            'type' => $type == '0' ? 24 : 25,
                            'related_id' => $activeAuction['id'],
                            'read_status' => 0,
                            'send_status' => 0,
                            'visible' => 1,
                            'text' =>  "You have been outbid on Spot Light",
                            'name' => "",
                            'profile_image' => "",
                            'updated_at' => time(),
                            'created_at' => time()
                        )
                    );
                }
            }
            
            redirect('myatb://payment/success');
            
        } else {
            redirect('myatb://payment/cancel');
        }
    }
    
    public function capturePayment() {
        // profile pin auctions
        $openCategories = $this->Auction_model->getCategories(array('status' => '1'));          // authorized categories
        
        // get countries
        $openCountries = $this->Auction_model->getCountries(array('status' => '1', 'type' => '0'));
        
        // get counties
        $openCounties = $this->Auction_model->getCounties(array('status' => '1', 'type' => '0'));
                     
        // get regions
        $openRegions = $this->Auction_model->getRegions(array('status' => '1', 'type' => '0'));
                
        // get tags
        $openTags = $this->Auction_model->getTags(array('status' => '1', 'type' => '1'));
        
        /* 
        1. deactivate all currently active auctions
        2. activate or void authorized auctions
            (Spot Light top 2 by country, county and region)
            (Top Spot top 5 by each tag)
        */
        $where = array();

        // 1. deactivate     
        // update array to deactivate
        $deactivate = array();
        $deactivate['status'] = '3';    // status: 3 - closed
        $deactivate['updated_at'] = time();

        $where['status'] = '2';
        // get all active auctions
        $activeAuctions = $this->Auction_model->getAuctions($where);
        foreach ($activeAuctions as $active) {
            $this->Auction_model->updateAuction($deactivate, array('id' => $active['id']));

            $type = $active['type'];
            $this->NotificationHistory_model->insertNewNotification(
                array(
                    'user_id' => $active['user_id'],
                    'type' => $type == '0' ? 28 : 29,
                    'related_id' => $active['id'],
                    'read_status' => 0,
                    'send_status' => 0,
                    'visible' => 1,
                    'text' =>  "Your time on" . $active['type'] == '0' ? " Spot Light " : " Top Spot " . "has now expired, would you like to promote again?",
                    'name' => "",
                    'profile_image' => "",
                    'updated_at' => time(),
                    'created_at' => time()
                )
            );
        }
        
        // 2. activate or void authorized auctions
        // activate
        $activate = array();
        $activate['status'] = '2';   // status: 2 - active 
        $activate['updated_at'] = time();
        
        // void
        $void = array();
        $void['status'] = '0';   
        $void['updated_at'] = time();

        $where['status'] = '1'; // authorized auctions

        $apiContext = $this->getApiContext();
                
        foreach($openCategories as $category) {
            $where['category'] = $category['category'];
            
            for ($position = 0; $position < 5; $position ++) {
                $where['position'] = $position;
            
                if ($position < 2) {                                
                    $where['type'] = '0';
                                     
                    foreach($openCountries as $country) {
                        $whereWithCountry = $where;
                        $whereWithCountry['country'] = $country['country'];
                        
                        $countryAuctions = $this->Auction_model->getProfilePinAuctions($whereWithCountry);
                        foreach($countryAuctions as $key => $countryAuction) {  
                            if ($key == 0) {
                                // capture payment
                                try {
                                    $authorization = Authorization::get($countryAuction['authorization_id'], $apiContext);
                                    
                                    $amount = new Amount();
                                    $amount->setCurrency('GBP')
                                        ->setTotal($countryAuction['price']);
                                    
                                    $capture = new Capture();
                                    $capture->setAmount($amount);                                    
                                    $capture->setIsFinalCapture(true);
                                    
                                    $getCapture = $authorization->capture($capture, $apiContext);
                                    
                                    // activate
                                    $this->Auction_model->updateAuction($activate, array('id' => $countryAuction['id']));

                                    $this->NotificationHistory_model->insertNewNotification(
                                        array(
                                            'user_id' => $countryAuction['user_id'],
                                            'type' => 26,
                                            'related_id' => $countryAuction['user_id'],
                                            'read_status' => 0,
                                            'send_status' => 0,
                                            'visible' => 1,
                                            'text' =>  "Congratulations, you have won your Spot Light Auction",
                                            'name' => "",
                                            'profile_image' => "",
                                            'updated_at' => time(),
                                            'created_at' => time()
                                        )
                                    );
                                    
                                } catch (Exception $ex) { }
                                
                            } else {
                                // void authorization
                                try {
                                    $authorization = Authorization::get($countryAuction['authorization_id'], $apiContext);
                                    $authorization->void($apiContext);
                                    
                                } catch (Exception $ex) { }
                                
                                $this->Auction_model->updateAuction($void, array('id' => $countryAuction['id']));                                
                            }
                        }
                    }
                    
                    foreach($openCounties as $county) {
                        $whereWithCounty = $where;
                        $whereWithCounty['county'] = $county['county'];
                        
                        $countyAuctions = $this->Auction_model->getProfilePinAuctions($whereWithCounty);
                        foreach($countyAuctions as $key => $countyAuction) {
                            if ($key == 0) {
                                // capture payment
                                try {
                                    $authorization = Authorization::get($countyAuction['authorization_id'], $apiContext);
                                    
                                    $amount = new Amount();
                                    $amount->setCurrency('GBP')
                                        ->setTotal($countyAuction['price']);
                                    
                                    $capture = new Capture();
                                    $capture->setAmount($amount);                                    
                                    $capture->setIsFinalCapture(true);
                                    
                                    $getCapture = $authorization->capture($capture, $apiContext);
                                    
                                    // activate
                                    $this->Auction_model->updateAuction($activate, array('id' => $countyAuction['id']));

                                    $this->NotificationHistory_model->insertNewNotification(
                                        array(
                                            'user_id' => $countyAuction['user_id'],
                                            'type' => 26,
                                            'related_id' => $countyAuction['user_id'],
                                            'read_status' => 0,
                                            'send_status' => 0,
                                            'visible' => 1,
                                            'text' =>  "Congratulations, you have won your Spot Light Auction",
                                            'name' => "",
                                            'profile_image' => "",
                                            'updated_at' => time(),
                                            'created_at' => time()
                                        )
                                    );
                                    
                                } catch (Exception $ex) { }
                                
                            } else {
                                // void
                                try {
                                    $authorization = Authorization::get($countyAuction['authorization_id'], $apiContext);
                                    $authorization->void($apiContext);
                                    
                                } catch (Exception $ex) { }
                                
                                $this->Auction_model->updateAuction($void, array('id' => $countyAuction['id']));
                            }
                        }
                    }
                    
                    foreach($openRegions as $region) {
                        $whereWithRegion = $where;
                        $whereWithRegion['region'] = $region['region'];
                        
                        $regionAuctions = $this->Auction_model->getProfilePinAuctions($whereWithRegion);
                        foreach($regionAuctions as $key => $regionAuction) {
                            if ($key == 0) {
                                // capture payment
                                try {
                                    $authorization = Authorization::get($regionAuction['authorization_id'], $apiContext);
                                    
                                    $amount = new Amount();
                                    $amount->setCurrency('GBP')
                                        ->setTotal($regionAuction['price']);
                                    
                                    $capture = new Capture();
                                    $capture->setAmount($amount);                                    
                                    $capture->setIsFinalCapture(true);
                                    
                                    $getCapture = $authorization->capture($capture, $apiContext);
                                    
                                    // activate
                                    $this->Auction_model->updateAuction($activate, array('id' => $regionAuction['id']));

                                    $this->NotificationHistory_model->insertNewNotification(
                                        array(
                                            'user_id' => $regionAuction['user_id'],
                                            'type' => 26,
                                            'related_id' => $regionAuction['user_id'],
                                            'read_status' => 0,
                                            'send_status' => 0,
                                            'visible' => 1,
                                            'text' =>  "Congratulations, you have won your Spot Light Auction",
                                            'name' => "",
                                            'profile_image' => "",
                                            'updated_at' => time(),
                                            'created_at' => time()
                                        )
                                    );
                                    
                                } catch (Exception $ex) { }
                                
                            } else {
                                // void
                                try {
                                    $authorization = Authorization::get($regionAuction['authorization_id'], $apiContext);
                                    $authorization->void($apiContext);
                                    
                                } catch (Exception $ex) { }
                                
                                $this->Auction_model->updateAuction($void, array('id' => $regionAuction['id']));
                            }
                        }
                    }
                }
                
                $where['type'] = '1';
                foreach($openTags as $tag) {
                    $pointAuctions = $this->Auction_model->getPinPointAuctions($where, $tag['tags']);
                                        
                    foreach($pointAuctions as $key => $pointAuction) {
                        if ($key == 0) {
                            // capture payment
                            try {
                                $authorization = Authorization::get($pointAuction['authorization_id'], $apiContext);
                                
                                $amount = new Amount();
                                $amount->setCurrency('GBP')
                                    ->setTotal($pointAuction['price']);
                                
                                $capture = new Capture();
                                $capture->setAmount($amount);                                    
                                $capture->setIsFinalCapture(true);
                                
                                $getCapture = $authorization->capture($capture, $apiContext);
                                
                                // activate
                                $this->Auction_model->updateAuction($activate, array('id' => $pointAuction['id']));

                                $this->NotificationHistory_model->insertNewNotification(
                                    array(
                                        'user_id' => $pointAuction['user_id'],
                                        'type' => 27,
                                        'related_id' => $pointAuction['user_id'],
                                        'read_status' => 0,
                                        'send_status' => 0,
                                        'visible' => 1,
                                        'text' =>  "Congratulations, you have won your Top Spot Auction",
                                        'name' => "",
                                        'profile_image' => "",
                                        'updated_at' => time(),
                                        'created_at' => time()
                                    )
                                );
                                
                            } catch (Exception $ex) { }
                            
                        } else {
                            // void
                            try {
                                $authorization = Authorization::get($pointAuction['authorization_id'], $apiContext);
                                $authorization->void($apiContext);
                                
                            } catch (Exception $ex) { }
                                
                            $this->Auction_model->updateAuction($void, array('id' => $pointAuction['id']));
                        }
                    }
                }             
            }
        }
    }
    
    private function getApiContext() {
        $apiContext = new \PayPal\Rest\ApiContext(
                    new \PayPal\Auth\OAuthTokenCredential(
                        $this->config->item('paypal_clientID'),     // ClientID
                        $this->config->item('paypal_secret')        // ClientSecret
                    )
            );
        $apiContext->setConfig(array('mode' => 'sandbox'));
        return $apiContext;
    }
}
