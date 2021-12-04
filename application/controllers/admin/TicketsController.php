<?php
class TicketsController extends MY_Controller
{

private function makeComponentLayout() {
        $header_include_css = array();
       
	$header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css');
        
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Reported Posts');
        $header_layout = $this->load->view('admin/common_template/header_layout', $header_layout_data, TRUE);
	$notificationCounter = $this->AdminNotification_model->getAdminNotification(array('read_status' => 0));
        $open_reports = $this->PostReport_model->getReports(array("is_active" => 0));

        $sidebar_menu_item = array(
            'selected_item' => MENU_SIGNUPS,
            'notifications_count' => count($notificationCounter),
            'reported_count' => count($open_reports)
                );
        $sidebar_layout = $this->load->view('admin/common_template/sidebar_layout', $sidebar_menu_item, TRUE);

        $footer_app_after_js = array();
        $footer_app_before_js = array();
      
        $footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
                base_url().'admin_assets/global/plugins/moment.min.js',
                base_url().'admin_assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js',
                base_url().'admin_assets/global/plugins/flot/jquery.flot.min.js'
                );

       $footer_app_after_js = array(base_url().'admin_assets/pages/signups/notification.js');
     

        $footer_layout_data = array('before_app_js' => $footer_app_before_js, 'after_app_js' => $footer_app_after_js);
        $footer_layout = $this->load->view('admin/common_template/footer_layout', $footer_layout_data, TRUE);

        $dataToBeDisplayed['header_layout'] = $header_layout;
        $dataToBeDisplayed['sidebar_layout'] = $sidebar_layout;
        $dataToBeDisplayed['footer_layout'] = $footer_layout;
        return $dataToBeDisplayed;
    }
    

public function index() {
        $dataToBeDisplayed = $this->makeComponentLayout();
        
        $username = $this->session->userdata('user_name');
        $email = $this->Admin_model->getAdmin(array("username" => $username))[0]["email"];
        
        $username = $email;
	$password = "Z/y3zgc[S'`~D4vd";
	$remote_url = 'https://tickets.myatb.co.uk/api/v1/tickets';

	// Create a stream
	$opts = array(
	  'http'=>array(
	    'method'=>"GET",
	    'header' => "Authorization: Basic " . base64_encode("$username:$password")                 
	  )
	);

	$context = stream_context_create($opts);

	// Open the file using the HTTP headers set above
	$file = file_get_contents($remote_url, false, $context);
	$tickets = json_decode($file, true);
	
	foreach($tickets as $elementKey => $element) {
	    foreach($element as $valueKey => $value) {
		if($valueKey == 'state_id' && $value == 4){

		    unset($tickets[$elementKey]);
		} 
	    }
	}
	
	$tickets = array_values($tickets);
	
	for($i = 0 ; $i < count($tickets); $i++) {


		// Open the file using the HTTP headers set above
		$file = file_get_contents('https://tickets.myatb.co.uk/api/v1/ticket_states/' . $tickets[$i]["state_id"], false, $context);
		$state = json_decode($file, true);
		$tickets[$i]["state"] = $state;
		

		// Open the file using the HTTP headers set above
		$file = file_get_contents('https://tickets.myatb.co.uk/api/v1/users/' . $tickets[$i]["customer_id"], false, $context);
		$user = json_decode($file, true);
		$tickets[$i]["user"] = $user;
	}
	
	$dataToBeDisplayed["tickets"] = $tickets;

        $this->load->view('admin/tickets/ticket_list', $dataToBeDisplayed);
    }
    
    public function detail($ticketid) {
        $dataToBeDisplayed = $this->makeComponentLayout();
        
        $username = $this->session->userdata('user_name');
        $email = $this->Admin_model->getAdmin(array("username" => $username))[0]["email"];
        
        $username = $email;
	$password = "Z/y3zgc[S'`~D4vd";

	// Create a stream
	$opts = array(
	  'http'=>array(
	    'method'=>"GET",
	    'header' => "Authorization: Basic " . base64_encode("$username:$password")                 
	  )
	);

	$context = stream_context_create($opts);
	$file = file_get_contents('https://tickets.myatb.co.uk/api/v1/tickets/' . $ticketid, false, $context);
	$ticket = json_decode($file, true);
	
	$file = file_get_contents('https://tickets.myatb.co.uk/api/v1/ticket_states/' . $ticket["state_id"], false, $context);
	$state = json_decode($file, true);
	$ticket["state"] = $state;
		

	$file = file_get_contents('https://tickets.myatb.co.uk/api/v1/users/' . $ticket["customer_id"], false, $context);
	$user = json_decode($file, true);
	$ticket["user"] = $user;
	
	$file = file_get_contents('https://tickets.myatb.co.uk/api/v1/ticket_articles/by_ticket/' . $ticketid, false, $context);
	$articles = json_decode($file, true);
	$ticket["articles"] = $articles;
	
	$userDetails = $this->User_model->getOnlyUser(array('user_email' => $ticket['user']['email']));
	
	
        $dataToBeDisplayed["ticket"] = $ticket;
        $dataToBeDisplayed["userDetails"] = $userDetails;
        $this->load->view('admin/tickets/ticket_detail', $dataToBeDisplayed);
 
    }
    
    public function delete_form($ticketid) {
        $dataToBeDisplayed = $this->makeComponentLayout();
        $dataToBeDisplayed['ticketid'] = $ticketid;
        $this->load->view('admin/tickets/delete_form', $dataToBeDisplayed);
    }
    
    public function delete_ticket() {

        $ticketId = $this->input->get('ticketid');

        $username = $this->session->userdata('user_name');
        $email = $this->Admin_model->getAdmin(array("username" => $username))[0]["email"];
        
        $username = $email;
	$password = "Z/y3zgc[S'`~D4vd";
	
	$url = 'https://tickets.myatb.co.uk/api/v1/tickets/' . $ticketId;
 
	$ch = curl_init($url);
	 
	$jsonData = array(
	    "state"=> "Complete"
	);
	 
	$jsonDataEncoded = json_encode($jsonData);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);  

	$result = curl_exec($ch);
	

        redirect('/admin/tickets');
    }
    
    public function reply_form($ticketid) {
        $dataToBeDisplayed = $this->makeComponentLayout();
        $dataToBeDisplayed['ticketid'] = $ticketid;
        $this->load->view('admin/tickets/reply_form', $dataToBeDisplayed);
    }
    
    public function reply_ticket() {

        $ticketId = $this->input->get('ticketid');
	$text = $this->input->get('reply_text');
        
        $username = $this->session->userdata('user_name');
        $email = $this->Admin_model->getAdmin(array("username" => $username))[0]["email"];
        
        $username = $email;
	$password = "Z/y3zgc[S'`~D4vd";
	
	$opts = array(
	  'http'=>array(
	    'method'=>"GET",
	    'header' => "Authorization: Basic " . base64_encode("$username:$password")                 
	  )
	);

	$context = stream_context_create($opts);
	$file = file_get_contents('https://tickets.myatb.co.uk/api/v1/tickets/' . $ticketId, false, $context);
	$ticket = json_decode($file, true);
	
	$file = file_get_contents('https://tickets.myatb.co.uk/api/v1/users/' . $ticket["customer_id"], false, $context);
	$user = json_decode($file, true);
	
	$url = 'https://tickets.myatb.co.uk/api/v1/ticket_articles';
 
	$ch = curl_init($url);
	 
	$jsonData = array(
	    'ticket_id' => $ticketId,
	    'to' => $user["email"],
	    'cc' => '',
	    'subject' => '',
	    'body' => $text,
	    'content_type' => 'text/html',
	    'type' => 'email'
	);
	 
	$jsonDataEncoded = json_encode($jsonData);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);  

	$result = curl_exec($ch);
		

        redirect('/admin/tickets');
    }
    
}
?>
