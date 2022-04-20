<?php


class AdminController extends MY_Controller
{
	
	private function makeComponentLayout() {       
        $header_include_css = array(base_url().'admin_assets/global/plugins/datatables/datatables.min.css',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css');
        
        $header_layout_data = array('arr_css' => $header_include_css, 'title' => 'Admin Accounts');
        $header_layout = $this->load->view('admin/common_template/header_layout', $header_layout_data, TRUE);

	$notificationCounter = $this->AdminNotification_model->getAdminNotification(array('read_status' => 0));
        $open_reports = $this->PostReport_model->getReports(array("is_active" => 0));

        $sidebar_menu_item = array(
            'selected_item' => MENU_SIGNUPS,
            'notifications_count' => count($notificationCounter),
            'reported_count' => count($open_reports)
                );

        $sidebar_layout = $this->load->view('admin/common_template/sidebar_layout', $sidebar_menu_item, TRUE);

        
		$footer_app_before_js = array(base_url().'admin_assets/global/scripts/datatable.js',
                base_url().'admin_assets/global/plugins/datatables/datatables.min.js',
                base_url().'admin_assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js');
        
		$footer_app_after_js = array();

        $footer_layout_data = array('before_app_js' => $footer_app_before_js, 'after_app_js' => $footer_app_after_js);
        $footer_layout = $this->load->view('admin/common_template/footer_layout', $footer_layout_data, TRUE);

        $dataToBeDisplayed['header_layout'] = $header_layout;
        $dataToBeDisplayed['sidebar_layout'] = $sidebar_layout;
        $dataToBeDisplayed['footer_layout'] = $footer_layout;
        return $dataToBeDisplayed;
    }
	
	public function index() {
        $dataToBeDisplayed = $this->makeComponentLayout();

        $dataToBeDisplayed['users'] = $this->Admin_model->getAdmin();
        $this->load->view('admin/admin/admin_list', $dataToBeDisplayed);
    }
	
	public function detail($userid) {
        $dataToBeDisplayed = $this->makeComponentLayout();
        $userDetails = $this->Admin_model->getAdmin(array('id' => $userid));
        log_message('debug',print_r($userDetails,TRUE));

        if(count($userDetails) == 0) {
            show_404();
        }
        else {
            $dataToBeDisplayed['user'] = $userDetails[0];
            $this->load->view('admin/admin/admin_detail', $dataToBeDisplayed);
        }

    }

    public function createAdmin(){
      
        $insertArray = array(
            'username' => $this->input->get('inputUsername'),
            'email' => $this->input->get('inputEmail'),
            'email' => $this->input->get('inputEmail'),
            'profile_pic' => $this->input->get('profile_pic'),
            'user_password' => self::GetMd5($this->input->get('inputPassword')),            
            'updated_at' => time(),
            'created_at' => time()
        );
       

        $this->Admin_model->insertNewAdmin($insertArray);

        redirect('/admin/admin');
    }
    
    public function editAdmin(){  
      $id = $this->input->post('id');
      $image = $this->input->post('image');
      $setArray = array(
        'profile_pic' =>  $image,
        'updated_at' => time(),
       );

      $whereArray = array('id' =>  $id);
      $this->Admin_model->updateAdminRecord($setArray, $whereArray);

      $data['status']="0";
      $data['id']= $id;
      $data['image']= $image;
      print json_encode( $data);
      exit;
    }

    public function deleteAdmin($userid) {
        $this->Admin_model->deleteAdmin(array('id' => $userid));
        redirect('/admin/admin');
    }

    public function newAdmin(){
        $dataToBeDisplayed = $this->makeComponentLayout();
        $this->load->view('admin/admin/admin_new', $dataToBeDisplayed);
    }

    function doUpload()
    {
        if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK)
  {
    // get details of the uploaded file
    $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
    $fileName = $_FILES['uploadedFile']['name'];
    $fileSize = $_FILES['uploadedFile']['size'];
    $fileType = $_FILES['uploadedFile']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    //echo $fileExtension;

    // sanitize file-name
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    // check if file has one of the following extensions
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

    if (in_array($fileExtension, $allowedfileExtensions))
    {
      // directory in which the uploaded file will be moved
      $uploadFileDir = './uploads/image/';
      $dest_path = $uploadFileDir . $newFileName;
      $dest_path_ ="uploads/image/" . $newFileName;

      if(move_uploaded_file($fileTmpPath, $dest_path)) 
      {
        $message ='File is successfully uploaded.';
      }
      else 
      {
        $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
      }
    }
    else
    {
      $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
    }
  }
  else
  {
    $message = 'There is some error in the file upload. Please check the following error.<br>';
    $message .= 'Error:' . $_FILES['uploadedFile']['error'];
  }
  $data['status']="0";

  $data['file_name']=$fileName;
  $data['file_url']=$dest_path_;


  print json_encode( $data);

   exit;
        }
	
}