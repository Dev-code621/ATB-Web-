<?php
$user_id= $this->session->userdata('user_id');


if(!$user_id){
	redirect(route('admin.auth.login'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no,  viewport-fit=cover" />
    <meta name="theme-color" content="#A6BFDE" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#A6BFDE" media="(prefers-color-scheme: dark)">
    
    <title>ATB Admin Portal</title>
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
    <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
   

</head>
<body>
  
    <main class="bgEndWhite feed-page">
        <header class="app-header container">
            <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-users-gear"></i> Admin</h1>
            <a href="#" class="nav-link" onclick="alert('HELLO')"><i class="fa-regular fa-magnifying-glass"></i></a>
        </header>

        <section class="data-container container">
           <?php for($i = 0 ; $i < count($users); $i++){?>
                <div class="data-item d-flex" data-modal="removeModal<?php echo $i?>" data-id='<?php echo $i?>' >
                    <div class="user-info d-block">
                        <h3 class="user-name"><?php echo $users[$i]['username'];?></h3>
                        <p class="user-mail"><?php echo $users[$i]['email'];?></p>
                    </div>
                    <span class="nav-icon" ><i class="fa-regular fa-chevron-right"></i></span>
                </div>
                <div class="modal adminModal" id="removeModal<?php echo $i?>">
                    <div class="closeModal barIcon" data-close="removeModal<?php echo $i?>"><i class="fa-regular fa-minus"></i></div>
                    <div class="user-icon onModal">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div class="text-center">
                        <h2><?php echo $users[$i]['username'];?></h2>
                        <p class="color-blue"><?php echo $i?></p>
                        <form action="<?php echo route('admin.admin.delete', $users[$i]['id']);?>">
                            <button type="submit"  class="btn btn-outline-danger"><i class="fa-regular fa-trash-can"></i> Delete this account</button>
                        </form>
                        </div>
                    </div>
                </div>
            <?php }?>
        </section>

        <div class="btn-footer top-shadow">
            <a href="#" class="btn btn-primary" data-modal="newAdminModal"><i class="fa-solid fa-user-plus"></i> New Admin</a>
        </div>

        <div class="modal newAdminModal bg-gray" id="newAdminModal">
            <div class="user-icon onModal">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div class="closeModal barIcon" data-close="newAdminModal"><i class="fa-regular fa-minus"></i></div>
                <h3 class="text-center">New Admin Account</h3>
            <form action="<?php echo route('admin.admin.create');?>" method="get" enctype="multipart/form-data">
                <input type="text" placeholder="Username" id="inputUsername" name="inputUsername">
                <input type="email" placeholder="Email Address" autocomplete="email" id="inputEmail" name="inputEmail" >
                <input type="password" placeholder="Password" autocomplete="new-password"  id = 'inputPassword' name="inputPassword">
                <button type="submit"  class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Create New Account</button>
            </form>
        </div>

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>

</body>
</html>