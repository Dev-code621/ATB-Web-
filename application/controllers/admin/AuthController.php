<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/6/19
 * Time: 4:38 AM
 */
class AuthController extends MY_Controller
{


    public function index() {
        redirect(route('admin.auth.login'));
    }

    public function login() {
        $this->load->view('admin/auth/auth_login');
    }

    public function forgot_pass() {
        $this->load->view('admin/auth/auth_forgot_pass');
    }
	
	public function logout(){
		$this->session->sess_destroy();
		redirect(route('admin.auth.login'));
	}

    public function do_login() {
		$this->load->model('Admin_model');
		
		$email  = strtolower($this->input->post('email'));
		$pwd = $this->GetMd5($this->input->post('pass'));
		
		$existUser = $this->Admin_model->getAdmin(array('email' => $email, 'user_password' => $pwd));
		
		if(count($existUser) > 0 ) {
			$this->session->set_userdata('user_id',$existUser[0]['id']);
			$this->session->set_userdata('user_name',$existUser[0]['username']);
			$this->session->set_userdata('profile_pic',$existUser[0]['profile_pic']);
			$this->session->set_userdata('user_count',count($this->User_model->getUsersListInDashboard()));

			$allposts = $this->UserService_model->getServiceInfos(array('is_active' => 3));
			$open_reports = $this->PostReport_model->getReports(array("is_active" => 0));
			$total_count = count($this->AdminNotification_model->getAdminNotification(array('read_status' => 0))) + count( $open_reports) + count(  $open_businesUsers) + count($allposts);
			$this->session->set_userdata('notification_count',$total_count);
			$this->session->set_userdata('report_count',count($this->PostReport_model->getReports(array("is_active" => 0))));		
			redirect(route('admin.dashboards.index'));
		} else {
			redirect(route('admin.auth.login'));
		}
    }
	
	public function register() {
		$this->load->model('Admin_model');
		$email = strtolower($this->input->post('email'));
		$retVal = array();
		$existUser = $this->Admin_model->getAdmin(array('email' => $email));
		if(count($existUser) == 0) {			

				$insertURL = array(
					'username' => $this->input->post('username'),
					'email' => $email,
					'user_password' => self::GetMd5($this->input->post('pass')),
					'updated_at' => time(),
					'created_at' => time()
				);

				$insResult = $this->Admin_model->insertNewAdmin($insertURL);
			
				
				if ($insResult[self::RESULT_FIELD_NAME]) {
					$user = $this->User_model->getOnlyUser(array('id' => $insResult[self::MESSAGE_FIELD_NAME]));
					
                
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::EXTRA_FIELD_NAME] = $user[0];
				
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

	public function sendPassword() {

		$this->load->model('Admin_model');
		echo "console.log('aaaaaaa')";
		$email = strtolower($this->input->post('email'));
		$retVal = array();
		$existUser = $this->Admin_model->getAdmin(array('email' => $email));
		if(count($existUser) == 0) {
			redirect(route('admin.auth.forgot_pass'));
		}
		else{
			$subject = 'Admin Password was Reset';
			$content = '<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">Plase use this new password</span></p>
			<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;"><span style="color: #808080; font-size: 18px;">'.$this->input->get('approveReason').'</span></p>';
			$this->User_model->sendUserEmail($email, $subject, $content);						
			redirect(route('admin.auth.login'));

		}
	}
	
}
