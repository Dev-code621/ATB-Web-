<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class InviteController extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$code = $this->input->get('code');
		$user = $this->User_model->getOnlyUser(array('invite_code' => $code));
		if (count($user) > 0){
			$dataToBeDisplayed["username"] = $user[0]['user_name'];
            $dataToBeDisplayed["profile"] = $user[0]['pic_url'];
		} else {
			$dataToBeDisplayed["username"] = "NO_USER";
            $dataToBeDisplayed["profile"] = "NO_USER";
		}
		
		$dataToBeDisplayed["code"] = $code;
        
        $this->load->view('admin/invite/invite_new', $dataToBeDisplayed);
		
//		$this->load->view('admin/invite/invite', $dataToBeDisplayed);
	} 
}
