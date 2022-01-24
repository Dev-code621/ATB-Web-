<?php
use Firebase\JWT\JWT;
use Restserver\Libraries\REST_Controller;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use PubNub\PubNub;
use PubNub\Enums\PNStatusCategory;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\PNConfiguration;


//Load Composer's autoloader
require 'vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: rock
 * Date: 23/7/2017
 * Time: 1:31 AM
 */

class MY_Controller extends CI_Controller
{
    const RESULT_FIELD_NAME = "result";
    const MESSAGE_FIELD_NAME = "msg";
    const EXTRA_FIELD_NAME = "extra";

	public function __construct(){

        parent::__construct();
  			$this->load->helper('url');
        $this->load->library('session');

	}

    protected function generateToken($user_id){
        $token['id'] = $user_id;
        $date = new DateTime();
        $token['iat'] = $date->getTimestamp();
        $token['exp'] = $date->getTimestamp() + 60*60*24*5;
        $token_code = JWT::encode($token,$this->config->item('encryption_key'),'HS256');
        return $token_code;
    }

    protected function verificationToken($token) {
        $ret_val = array();
        try{

            $user_data = JWT::decode($token, $this->config->item('encryption_key'), array('HS256'));
            $user_model = $this->User_model->getOnlyUser(array('ID' => $user_data->id));

            if($user_model[0]['status'] == '3') {
                $ret_val[self::RESULT_FIELD_NAME] = true;
                $ret_val['id'] = $user_data->id;

            }
            else {
                $ret_val[self::RESULT_FIELD_NAME] = false;
                $ret_val['id'] = -1;
            }
        }
        catch (Exception $e){
            $ret_val[self::RESULT_FIELD_NAME] = false;
            $ret_val['id'] = -1;
        }
        return $ret_val;
    }

    protected function initEmailSender(){
        $config['protocol'] = "smtp";
        $config['smtp_host'] = "smtp.gmail.com";
        $config['smtp_port'] = "587";
        $config['smtp_timeout'] = "60";
        $config['smtp_user'] = "kudonobo1007@gmail.com";
        $config['smtp_pass'] = "Aladdinsydejslf1!123";
        $config['charset'] = "utf-8";
        $config['mailtype'] = "html";
        $config['newline'] = "\r\n";
        $this->email->initialize($config);
    }

    protected function fileUpload($pathToUpload, $nameToUpload, $filePostField){

      $sharedConfig = [
        'region' => 'eu-west-2',
        'version' => 'latest',
        'credentials' => [
		'key'    => 'AKIAUJ7A46K5AC7G2CPB',
		'secret' => '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'
	    ]
      ];

  // Create an SDK class used to share configuration across clients.
      $sdk = new Aws\Sdk($sharedConfig);

      // Create an Amazon S3 client using the shared configuration data.
      $client = $sdk->createS3();
      
      $bucketName = 'atb-test-files';
      $filename = uniqid(rand(), true) . $_FILES[$filePostField]['name'];

	try {
	    $result = $client->putObject([
		'Bucket' => $bucketName,
		'Key'    => $filename,
		'SourceFile'   => $_FILES[$filePostField]['tmp_name'],
		'ACL'    => 'public-read',
	    ]);
	    
	    return $result->get('ObjectURL');
	} catch (Aws\S3\Exception\S3Exception $e) {
	    echo "There was an error uploading the file.\n";
	    echo $e->getMessage();
	}

      //OLD file system based upload
        /*$config['upload_path']   = './uploads/'.$pathToUpload."/";
        $config['allowed_types'] = '*';
        $config['max_size']      = 1024*100;//100 MB Maximum
        $config['max_width']     = 5000;
        $config['max_height']    = 5000;
        $config['file_name'] = $nameToUpload;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!is_dir('./uploads/'.$pathToUpload.'/')) {
            mkdir('./uploads/'.$pathToUpload.'/', 0777, TRUE);
        }

        $upload_result = $this->upload->do_upload($filePostField);
        if($upload_result == true){
            $uploadFilePath = "uploads/".$pathToUpload."/".$this->upload->data('file_name');
        }
        else{
            $uploadFilePath = $this->upload->display_errors();

        }
        return $uploadFilePath;*/
    }

    protected function GetMd5($pass)
    {
        return md5($pass);
    }
    
    function random_string($length) {
	    $key = '';
	    $keys = array_merge(range(0, 9), range('a', 'z'));

	    for ($i = 0; $i < $length; $i++) {
		$key .= $keys[array_rand($keys)];
	    }

	    return $key;
	}
	
    function cartesian($input) {
        $result = array(array());

        foreach ($input as $key => $values) {
            $append = array();

            foreach($result as $product) {
                foreach($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }
    
    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
    */    
    function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
      // convert from degrees to radians
      $latFrom = deg2rad($latitudeFrom);
      $lonFrom = deg2rad($longitudeFrom);
      $latTo = deg2rad($latitudeTo);
      $lonTo = deg2rad($longitudeTo);

      $lonDelta = $lonTo - $lonFrom;
      $a = pow(cos($latTo) * sin($lonDelta), 2) +
        pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
      $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

      $angle = atan2(sqrt($a), $b);
      return $angle * $earthRadius;
    }

    function sendEmail($email, $title, $body) {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                         // Enable verbose debug output :SMTP::DEBUG_SERVER // off : SMTP::DEBUG_OFF
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host = "smtp.gmail.com";                             // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'dpuja2071@gmail.com';                  // SMTP username
            $mail->Password   = 'qsaianeggvdurgmy';                     // SMTP password
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable implicit TLS encryption
        $mail->Port       = 587;                                        // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            // Recipients
            $mail->setFrom('noreply@myatb.co.uk', 'ATB');
            // $mail->addAddress($email, $name);                           // Add a recipient
            $mail->addAddress($email);                                  // Name is optional
            //$mail->addReplyTo('honestdeveloper10@gmail.com', 'Information');
            //$mail->addCC('honestdeveloper10@gmail.com');
            //$mail->addBCC('honestdeveloper10@gmail.com');

            //Attachments
            //  $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //  $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $title;
            $mail->Body    = $body;
            // $mail->AltBody = $altbody;
            $mail->send();

        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }

    function loginPubNub() {
        $publish_key = $this->config->item('pubnub_publish_key');
		$sub_key = $this->config->item('pubnub_sub_key');
        $pnconf = new PNConfiguration();
        
        $pnconf->setSubscribeKey($sub_key);
        $pnconf->setPublishKey($publish_key);
        $pnconf->setUuid("ADMIN");
        $pubnub = new PubNub($pnconf);

        return $pubnub;
    }
}
