<?php

class SubscriptionController extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('firebase');
    }

    public function handleNotifications() {
        // echo(FCPATH.'<br>');
        // echo(BASEPATH.'\n');
        // echo(APPPATH.'\n');
        // echo(__dir__.'<br>');
        // echo(__file__.'<br>');
        // exit();

        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // Download the certificate -> https://www.apple.com/certificateauthority/AppleRootCA-G3.cer
        // Convert it to .PEM file, run on macOS terminal ->  ```bash openssl x509 -in AppleRootCA-G3.cer -out apple_root.pem```
        $pem = file_get_contents(APPPATH.'controllers/payment/apple_root.pem');
        $data = file_get_contents('php://input'); 

        // log signedPayload
        // log_message('debug', "service notification:\n".$data);

        $json = json_decode($data);
        $header_payload_secret = explode('.', $json->signedPayload);

        //------------------------------------------
        // Header
        //------------------------------------------
        $header = json_decode(base64_decode($header_payload_secret[0]));
        $algorithm = $header->alg;
        $x5c = $header->x5c; // array
        $certificate = $x5c[0];
        $intermediate_certificate = $x5c[1];
        $root_certificate = $x5c[2];

        $certificate =
            "-----BEGIN CERTIFICATE-----\n"
            . $certificate
            . "\n-----END CERTIFICATE-----";

        $intermediate_certificate =
            "-----BEGIN CERTIFICATE-----\n"
            . $intermediate_certificate
            . "\n-----END CERTIFICATE-----";

        $root_certificate =
            "-----BEGIN CERTIFICATE-----\n"
            . $root_certificate
            . "\n-----END CERTIFICATE-----";

        //------------------------------------------
        // Verify the notification request   
        //------------------------------------------
        if (openssl_x509_verify($intermediate_certificate, $root_certificate) != 1){ 
            echo 'Intermediate and Root certificate do not match';
            http_response_code(400);
            exit;
        }

        // Verify again with Apple root certificate
        if (openssl_x509_verify($root_certificate, $pem) == 1){
            //------------------------------------------
            // Payload
            //------------------------------------------
            // https://developer.apple.com/documentation/appstoreservernotifications/notificationtype
            // https://developer.apple.com/documentation/appstoreservernotifications/subtype
            $payload = json_decode(base64_decode($header_payload_secret[1]));            
            $notificationType = $payload->notificationType;
            $subtype = $payload->subtype;

            switch ($notificationType) {
                case 'SUBSCRIBED':
                case 'EXPIRED': 
                case 'REFUND':
                    $transactionInfo = $payload->data->signedTransactionInfo;
                    $ti = explode('.', $transactionInfo);
                    /*
                    {
                        ["transactionId"]=>
                        string(16) "2000000305802960"
                        ["originalTransactionId"]=>
                        string(16) "2000000305783801"
                        ["webOrderLineItemId"]=>
                        string(16) "2000000024288225"
                        ["bundleId"]=>
                        string(11) "com.atb.app"
                        ["productId"]=>
                        string(19) "com.atb.app.monthly"
                        ["subscriptionGroupIdentifier"]=>
                        string(8) "20802772"
                        ["purchaseDate"]=>
                        int(1680266537000)
                        ["originalPurchaseDate"]=>
                        int(1680265069000)
                        ["expiresDate"]=>
                        int(1680266837000)
                        ["quantity"]=>
                        int(1)
                        ["type"]=>
                        string(27) "Auto-Renewable Subscription"
                        ["inAppOwnershipType"]=>
                        string(9) "PURCHASED"
                        ["signedDate"]=>
                        int(1680266581568)
                        ["environment"]=>
                        string(7) "Sandbox"
                        }
                    */
                    $jsonTransaction = json_decode(base64_decode($ti[1]));
                    $transactionId = $jsonTransaction->originalTransactionId;
                    $transactions = $this->UserTransaction_model->getTransactionHistory(
                        array('transaction_id' => $transactionId)
                    );

                    if (count($transactions) > 0) {
                        if (notificationType)
                        $transaction = $transactions[0];

                        $update = array();
                        if ($notificationType == "EXPIRED" || $notificationType == "REFUND") {
                            $update = array(
                                'status' => 0,
                                'updated_at' => time()
                            );

                            $userId = $transaction['user_id'];
                            // free trial has been started, mark user paid
                            $this->UserBusiness_model->updateBusinessRecord(
                                array('paid' => 0, 'updated_at' => time()),
                                array('user_id' => $userId)
                            );

                            // firebase real-time update
                            $firebase = $this->firebase->init();
                            $db = $firebase->createDatabase();
                            $reference =  $db->getReference('ATB/Admin/business/'.$transaction['user_id']);
                            $reference->set([
                                "paid" => "0",
                                "updated" => time()*1000
                            ]);

                        } else {
                            $update = array(
                                'status' => 1,
                                'updated_at' => time()
                            );

                            $userId = $transaction['user_id'];
                            // free trial has been started, mark user paid
                            $this->UserBusiness_model->updateBusinessRecord(
                                array('paid' => 1, 'updated_at' => time()),
                                array('user_id' => $userId)
                            );

                            // firebase real-time update
                            $firebase = $this->firebase->init();
                            $db = $firebase->createDatabase();
                            $reference =  $db->getReference('ATB/Admin/business/'.$transaction['user_id']);
                            $reference->set([
                                "paid" => "1",
                                "updated" => time()*1000
                            ]);
                        }
        
                        $this->UserTransaction_model->update(
                            $update,
                            array(
                                'id' => $transaction['id']
                            ));

                    } else {
                        echo 'Not found the transaction:' . $transactionId;
                    }

                    break;

                default: break;
            }

        } else {
            echo 'Header is not valid';
            http_response_code(400);
            exit;
        }

        http_response_code(200);
    }
}