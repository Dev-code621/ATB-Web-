
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
    <link rel="icon" type="image/png" href="images/favicon.ico" />
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
</head>
<body>
  
    <main id="new-message-page">
        <header class="app-header container">
            <a href="<?php echo route('admin.chat.index');?>" class="nav-link"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-message-plus"></i> Start New Chat</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
        </header>

        
      
        <section class="tabs-container">
            <div class="navTabs position-relative mt-0">
                <button class="btn tablinks active" data-tab="contacts">Contacts</button>
                <button class="btn tablinks" data-tab="group">Group</button>
            </div>
            
            <div class="messages-list tab-content-wrapper container">
                <div data-tabcontent="contacts" class="tabcontent" style="display: block;">
                <?php for($i = 0 ; $i < count($users); $i++):?>
                    <a href="<?php echo route('admin.chat.detail', "ADMIN_".$users[$i]['id']);?>" class="contact-item">
                        <div class="user-icon">
                        <?php
                                        $picURL = base_url()."<?php echo base_url();?>admin_assets/img/generic-user.png";
                                        if (!empty($users[$i]['pic_url'])){
                                            $picURL = $users[$i]['pic_url'];
                                        }
                                    ?>
                            <img src="<?php echo  $picURL?>" alt="User icon">
                        </div>
                        <div class="message-info">
                            <h2 class="user-name"><?php
                                            if (!empty($users[$i]['user_name'])){
                                                echo $users[$i]['user_name'];
                                            } else {
                                                echo "Unknown Name";
                                            }
                                        ?></h2>
                            <p><?php
                                    if(abs($users[$i]['online'] - time()) < 1800){
                                        echo 'Online';
                                    }else{
                                        echo 'Offline';
                                    }
                                    ?></p>
                        </div>
                    </a>           
                <?php endfor;?>
        
                </div>
                <div data-tabcontent="group" class="tabcontent">
                    <a href="<?php echo route('admin.chat.detail', $user_id);?>" class="contact-item">
                        <div class="user-icon">
                            <i class="fa-light fa-users"></i>
                        </div>
                        <div class="message-info">
                            <h2 class="user-name">All Users</h2>
                        </div>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    
                    <form action="" class="new-group-form">
                        
                        <?php for($i = 0 ; $i < count($users); $i++):?>
                            <label class="contact-item">
                            <input type="checkbox" class="selectContact" name="selectGroup" value="user1" />
                            <div class="user-icon">
                                 <?php
                                        $picURL = base_url()."admin_assets/img/generic-user.png";
                                        if (!empty($users[$i]['pic_url'])){
                                            $picURL = $users[$i]['pic_url'];
                                        }
                                    ?>
                                <img src="<? echo $picURL?>" alt="User icon">
                            </div>
                            <div class="message-info">
                                <h2 class="user-name"><?php
                                            if (!empty($users[$i]['user_name'])){
                                                echo $users[$i]['user_name'];
                                            } else {
                                                echo "Unknown Name";
                                            }
                                        ?></h2>
                                <p><?php
                                    if(abs($users[$i]['online'] - time()) < 1800){
                                        echo 'Online';
                                    }else{
                                        echo 'Offline';
                                    }
                                    ?></p>
                            </div>
                        </label>           
                        <?php endfor;?>              
                        <a href="<?php echo route('admin.chat.detail', $user_id);?>" class="btn btn-primary new-group-btn"><i class="fa-light fa-users-medical"></i> Create Group</a>
                    </form>

                </div>
            </div>
        </section>
        
    </main>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>

</body>
</html>