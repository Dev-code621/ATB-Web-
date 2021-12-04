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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
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
  
    <main class="minPaddingTop">
        
        <section class="profile-container">
            <div class="container">
                <header>
                    <a href="<?php echo route('admin.signups.index');?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
                    <div class="user-info d-block">
                        <div class="<?php
                                    if(abs($profile['online'] - time()) < 1800){
                                        echo 'user-icon online';
                                    }else{
                                        echo 'user-icon offline';
                                    }?>">
                            <img src="<?php echo $profile['pic_url'];?>" alt="User icon">
                        </div>
                        <div class="user-info-content text-center">
                            <h2 class="user-name"><?php echo $profile['first_name'].' '.$profile['last_name'];?></h2>
                            <p class="user-username">@<?php echo $profile['user_name'];?></p>
                        </div>
                    </div>
                    <a href="#" class="nav-link"><i class="fa-regular fa-ellipsis"></i></a>
                </header>

                <table class="user-data"  cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr>
                            <td><i class="fa-light fa-envelope"></i></td>
                            <td> <?php echo  $profile['user_email']; ?></td>
                        </tr>
                        <tr>
                            <td><i class="fa-light fa-map-pin"></i></td>
                            <td><?php echo $profile['country'];?></td>
                        </tr>
                        <tr>
                            <td><i class="fa-light fa-user-circle"></i></td>
                            <td><?php echo intval($profile['gender']) == 1 ? 'Female':'Male';?></td>
                        </tr>
                        <tr>
                            <td><i class="fa-light fa-birthday-cake"></i></td>
                            <td> <?php echo $profile['birthday'];?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h2 class="table-title">Sign Up Information</h2>
            <table class="signup-data"  cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <td><i class="fa-regular fa-calendar-day"></i> <span>Date</span></td>
                        <td><?php echo date('d/m/Y', $profile['created_at']);?></td>
                    </tr>
                    <tr>
                        <td><i class="fa-regular fa-clock"></i> <span>Time</span></td>
                        <td><?php echo date('H:i', $profile['created_at']);?></td>
                    </tr>
                    <tr>
                        <td><i class="fa-regular fa-clock-rotate-left"></i> <span>LAST LOGIN</span></td>
                        <td><?php echo $last_logintimestamp;?></td>
                    </tr>
                    <tr>
                        <td><i class="fa-regular fa-signs-post"></i> <span>Posts</span></td>
                        <td> <?php echo $post_counter;?></td>
                    </tr>
                    <tr>
                        <td><i class="fa-regular fa-user-circle"></i> <span>Status</span></td>
                        <td>
                        <?php
                            switch ($profile['status']) {
                                case 0:
                                    echo '<span class="text-muted">inactive</span>';
                                    break;
                                case 1:
                                    echo '<span class="text-danger" data-toggle="tooltip" data-placement="top" title="' . $profile['status_reason'] . '">block</p>';
                                    break;
                                case 2:
                                    echo '<span class="text-primary">frozen</span>';
                                    break;
                                case 3:
                                    echo '<span class="text-success">normal</span>';
                                    break;
                                case 4:
                                    echo '<span class="text-danger">removed</span>';
                                    break;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fa-regular fa-right-to-bracket"></i> <span>Log In</span></td>

                        <td><i class="<?php
                                    if(abs($profile['online'] - time()) < 1800){
                                        echo 'user-icon online';
                                    }else{
                                        echo 'user-icon offline';
                                    }?>"></i><?php
                                    if(abs($profile['online'] - time()) < 1800){
                                        echo 'Online';
                                    }else{
                                        echo 'Offline';
                                    }?></td>
                    </tr>
                </tbody>
            </table>

            <div class="container">
                <a href="#" class="btn btn-primary mb-15"><i class="fa-regular fa-messages"></i> Send a Message</a>
                <?php if ($profile['status'] != 1) { ?>
                      <button type="button" data-modal="blockModal" class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i>Block this user</button>
                <?php } ?>
                <?php if ($profile['status'] == 1) { ?>
                    <button type="button" data-modal="blockModal" class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i>Unblock this user</button>

                <?php } ?>
            </div>
        </section>

        <div class="modal" id="blockModal">
            <div class="closeModal" data-close="blockModal"><i class="fa-regular fa-circle-xmark"></i></div>
            <div class="text-center">
                <div class="iconTitle"><i class="fa-solid fa-user-minus"></i></div>
                <h3>Are you sure you want to block this user?</h3>
                <h4>If so, please provide a reason below</h4>
                <div id="screenHeight"></div>
            </div>
            <form 
                <?php if ($profile['status'] != 1) { ?> 
                    action="<?php echo route('admin.signups.submit_block');?>" method="get" enctype="multipart/form-data"
                <?php } ?>
                <?php if ($profile['status'] == 1) { ?>
                    action="<?php echo route('admin.signups.submit_unblock');?>" method="get" enctype="multipart/form-data"
                <?php } ?>
            >
                <textarea rows="5" id="Reason" placeholder="This user is providing spam, in all inboxes"></textarea>
                <input type="hidden" id="userid" name="userid" value="<?php echo $profile['id'];?>">

                <?php if ($profile['status'] != 1) { ?>
                     <button type="submit"  class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i> Block this user</button>
                <?php } ?>
                <?php if ($profile['status'] == 1) { ?>
                    <button type="submit"  class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i> Unblock this user</button>
                <?php } ?>

            </form>
        </div>


    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
   <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>

</body>
</html>