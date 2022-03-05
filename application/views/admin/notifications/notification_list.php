<?php
use UI\Size;
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
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
       <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        img {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 3px;
        width: 90px;
        height: 90px;
        }
        img:hover {
           box-shadow: 0 0 2px 1px rgba(0, 140, 186, 0.5);
        }
        </style>
</head>
<body>
  
    <main class="bgEndWhite">
        <header class="app-header container">
            <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-bell-on fa-swap-opacity"></i> Notifications</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
        </header>
            
        <section class="notification-container multiple-items container">
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($newNotifications));?></span>
                <div>
                    <span class="notification-label">Alerts <br> pending <br> review</span>                    
                </div>
            </div>
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($keywords));?></span>
                <div>
                    <span class="notification-label">Keywords <br> active</span>                    
                </div>
            </div>
        </section>


            <div class="tabs-container">
                <div class="navTabs position-relative scrollable mt-0">
                     <button class="btn tablinks active" data-tab="unread-notifications">Unread</button>
                    <button class="btn tablinks" data-tab="actioned-notifications">Actioned</button>
                    <button class=" btn tablinks" data-tab="keywords-alert">Keywords Reported</button>
                    <button class="btn tablinks" data-tab="open">Unread Reported</button>
                    <button class="btn tablinks" data-tab="closed">Actioned Reported</button>
                    <button class=" btn tablinks" data-tab="new-business">New Business</button>
                    <button class=" btn tablinks" data-tab="new-service">New Service</button>

                </div>
                
                <div class="data-container tab-content-wrapper container">
                    <div data-tabcontent="unread-notifications" class="tabcontent" style="display: block;" id = "unreadnotification">
                        <?php for($i = 0 ; $i < count($newNotifications); $i++):
                                if($newNotifications[$i]['post'] == null) continue; ?>
                            <div class="data-item d-flex">
                                <div class="user-info"> 
                                    <div class="user-icon online">
                                        <img src="<?php echo $newNotifications[$i]['user']['profile']["pic_url"];?>" alt="User icon">
                                    </div>
                                    <div class="user-info-content">
                                        <p><a href="<?php echo route('admin.signups.detail', $newNotifications[$i]['user']['profile']['id']);?>"> @<?php echo $newNotifications[$i]['user']['profile']['user_name'];?></a>
                                         has written a <a href="<?php echo route('admin.signups.view_post', $newNotifications[$i]['post']['id']);?>">
                                             <?php if($newNotifications[$i]["type"] == 0) {
                                                    echo "comment";
                                                } else {
                                                    echo "post"; }?></a> that included the keyword <a href="#"><?php echo $newNotifications[$i]['keyword'][0]["keyword"]; ?></a>.</p>
                                        <div class="data-info ">
                                            <div class="data-info-item date">
                                                <i class="fa-regular fa-calendar-day"></i>
                                                <span> <?php echo date('d/m/Y', $newNotifications[$i]['created_at']);?></span>
                                            </div>
                                            <div class="data-info-item time">
                                                <i class="fa-regular fa-clock"></i>
                                                <span><?php echo date('H:i:s', $newNotifications[$i]['created_at']);?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="checkbox" id="checkbox"  value="<?php echo $i?>" onClick="cbChanged(this)"/>    
                            </div>                        
                        <?php endfor;?>                        
                    </div>

                    <div data-tabcontent="actioned-notifications" class="tabcontent">
                      <?php for($i = 0 ; $i < count($oldNotifications); $i++):
                               if($oldNotifications[$i]['post'] == null) continue; ?>

                        <div class="data-item d-flex">
                            <div class="user-info"> 
                                <div class="user-icon online">
                                    <img src="<?php echo $oldNotifications[$i]['user']['profile']["pic_url"];?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                    <p><a href="<?php echo route('admin.signups.detail', $oldNotifications[$i]['user']['profile']['id']);?>">@<?php echo $oldNotifications[$i]['user']['profile']['user_name'];?></a>
                                     has written a <a href="<?php echo route('admin.signups.view_post', $oldNotifications[$i]['post']['id']);?>">
                                      <?php if($oldNotifications[$i]["type"] == 0) {
                                            echo "comment";
                                        } else {
                                            echo "post";
                                        }?></a> that included the keyword <a href="#"><?php echo $oldNotifications[$i]['keyword'][0]["keyword"]; ?></a>.</p>
                                    <div class="data-info ">
                                        <div class="data-info-item date">
                                            <i class="fa-regular fa-calendar-day"></i>
                                            <span><?php echo date('d/m/Y', $oldNotifications[$i]['created_at']);?></span>
                                        </div>
                                        <div class="data-info-item time">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo date('H:i:s', $oldNotifications[$i]['created_at']);?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
                        </div>
                      <?php endfor;?>

                    </div>
                    <div data-tabcontent="keywords-alert" class="tabcontent">
                        <?php for($i = 0 ; $i < count($keywords); $i++):?>

                            <div class="data-item d-flex">
                                <div class="user-info"> 
                                    <div class="user-icon online">
                                        <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                                    </div>
                                    <div class="user-info-content">
                                        <p><a href="#"><?php echo $keywords[$i]['keyword'];?></a></p>
                                        <div class="data-info ">
                                            <div class="data-info-item date">
                                                <i class="fa-regular fa-calendar-day"></i>
                                                <span><?php echo date('d/m/Y', $keywords[$i]['created_at']);?></span>
                                            </div>
                                            <div class="data-info-item time">
                                                <i class="fa-regular fa-clock"></i>
                                                <span><?php echo date('H:i:s', $keywords[$i]['created_at']);?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
                            </div>
                            <?php endfor;?>
                            <div class="btn-footer top-shadow">
                            <button type="button" data-modal="newAdminModal" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i>New Keyword</button>

                            <div class="modal" id="newAdminModal">
                                <div class="closeModal" data-close="newAdminModal"><i class="fa-regular fa-circle-xmark"></i></div>
                                <div class="text-center">
                                    <div class="iconTitle"><i class="fa-solid fa-user-minus"></i></div>
                                    <h3> Would you like to add new Alert?</h3>
                                    <h4>If so, please input new keyword</h4>
                                    <div id="screenHeight"></div>
                                </div>
                                <form                                    
                                   action="<?php echo route('admin.notifications.keywordcreate');?>" method="get" enctype="multipart/form-data">
                                    <textarea rows="5" id="inputKeyword"  name = "inputKeyword" placeholder="Please input keyword"></textarea>
                                    <button type="submit"   class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Add Keyword</button>

                                </form>
                            </div>
                        </div>
                    </div>

                    <div data-tabcontent="open" class="tabcontent" style="display: block;">
                      <?php for($i = 0 ; $i < count($open_reports); $i++):?>
                        <div class="data-item">
                            <div class="user-info"> 
                                <div class="user-icon online">
                                    <img src="<?php echo $open_reports[$i]['reported_user']['profile']['pic_url'];?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                  <div class = "data-item d-flex">
                                    <div >
                                        <p><a href="<?php echo route('admin.signups.detail', $open_reports[$i]['reported_user']['profile']['id']);?>"> 
                                        @<?php echo $open_reports[$i]['reported_user']['profile']['user_name'];?></a> has reported 
                                        <?php if ($open_reports[$i]['post_id'] != 0) { ?>
                                                                the  <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['post']['user'][0]['id']);?>" > @<?php echo $open_reports[$i]['post']['user'][0]['user_name'];?> </a> post - <a href="<?php echo route('admin.signups.view_post', $open_reports[$i]['post']['id']);?>" ><?php echo $open_reports[$i]['post']["title"];?> </a>
                                                                <?php } else if ($open_reports[$i]['user_id'] != 0) { ?>
                                                                the user <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['user']['profile']['id']);?>"> @<?php echo $open_reports[$i]['user']['profile']['user_name'];?> </a>
                                                                <?php } ?>
                                        
                                        </p>
                                        <p><i class="fa-solid fa-quote-left"></i><?php echo $open_reports[$i]['content'];?></p>
                                        <div class="data-info ">
                                            <div class="data-info-item">
                                                <i class="fa-regular fa-calendar-day"></i>
                                                <span><?php echo date('d/m/Y', $open_reports[$i]['created_at']);?> </span>
                                            </div>
                                            <div class="data-info-item">
                                                <i class="fa-regular fa-clock"></i>
                                                <span><?php echo date('H:i:s', $open_reports[$i]['created_at']);?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div > <input type="checkbox" id="checkbox"  value="<?php echo $i?>" onClick="onReadReport(this)"/> </div>      
                                  </div >
                                    <?php 
                                    if($open_reports[$i]['post_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.signups.view_post', $open_reports[$i]['post_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View Post</a>
                                    <?php } elseif($open_reports[$i]['user_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['user_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View User</a>
                                    <?php } elseif($open_reports[$i]['comment_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.reported_post.commentreport', $open_reports[$i]['comment_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View Comment</a>                                            
                                    <?php }?>

                                </div>
                            </div>
                        </div>
                        <?php endfor;?>
                        
                    </div>
                    <div data-tabcontent="closed" class="tabcontent" style="display: block;">
                      <?php for($i = 0 ; $i < count($closed_reports); $i++):?>
                        <div class="data-item">
                            <div class="user-info"> 
                                <div class="user-icon online">
                                    <img src="<?php echo $closed_reports[$i]['reported_user']['profile']['pic_url'];?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                    <p><a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['reported_user']['profile']['id']);?>"> 
                                       @<?php echo $closed_reports[$i]['reported_user']['profile']['user_name'];?></a> has reported 
                                     
                                    
                                    </p>
                                    <p><i class="fa-solid fa-quote-left"></i><?php echo $closed_reports[$i]['content'];?></p>
                                    <div class="data-info ">
                                        <div class="data-info-item">
                                            <i class="fa-regular fa-calendar-day"></i>
                                            <span><?php echo date('d/m/Y', $closed_reports[$i]['created_at']);?> </span>
                                        </div>
                                        <div class="data-info-item">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo date('H:i:s', $closed_reports[$i]['created_at']);?></span>
                                        </div>
                                    </div>
                                    <?php 
                                    if($closed_reports[$i]['post_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.signups.view_post', $closed_reports[$i]['post_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View Post</a>
                                    <?php } elseif($closed_reports[$i]['user_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['user_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View User</a>
                                    <?php } elseif($closed_reports[$i]['comment_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.reported_post.commentreport', $closed_reports[$i]['comment_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View Comment</a>
                                            
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                        <?php endfor;?>
                        
                    </div>
                    <div data-tabcontent="new-business" class="tabcontent pending-review" style="display: block;">
                   <?php for($i = 0 ; $i < count($open_businesUsers); $i++):?>
                        <div class="data-item">
                            <div class="user-info">
                                <div class="user-icon">

                                    <img src="<?php echo $open_businesUsers[$i]['business_logo'];?>">
                                </div>
                                <div class="user-info-content">
                                    <h2 class="user-name"><?php echo $open_businesUsers[$i]['user']['profile']['first_name'];?> <?php echo $open_businesUsers[$i]['user']['profile']['last_name'];?> </h2>
                                    <p class="user-username">@<?php echo $open_businesUsers[$i]['user']['profile']['user_name'];?> </p>
                                    <p>
                                        <?php if ($open_businesUsers[$i]['type'] == "business") { ?>
                                                has submitted the business for approval
                                                <?php } else {  ?>
                                                has submitted a service against their business
                                        <?php }   ?>
                                    </p>
                                    <a href="#" class="business-type">
                                        <i class="fa-regular fa-briefcase business-icon "></i>
                                        <?php echo $open_businesUsers[$i]['business_name'];?> 
                                        <!-- <i class="fas fa-chevron-right"></i> -->
                                    </a>
                                    <div class="data-info data-info-list">
                                        <div class="data-info-item date">
                                            <i class="fa-regular fa-calendar-day"></i>
                                            <span><?php echo date('d/m/Y', $open_businesUsers[$i]['created_at']);?></span>
                                        </div>
                                        <div class="data-info-item time">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo date('H:i:s', $open_businesUsers[$i]['created_at']);?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn-actions">
                                <a href="<?php echo route('admin.chat.detail', $open_businesUsers[$i]['user']['profile']['id']);?>" class="btn btn-outline-dark mr-10" >Message User</a>
                                <a href="<?php echo route('admin.business.detail', $open_businesUsers[$i]['id']);?>" class="btn btn-primary">View Business Details</a>
                            </div>
                        </div>
                    <?php endfor;?>
                </div>
                <div data-tabcontent="new-service" class="tabcontent">
                        <?php for($i = 0 ; $i < count($allposts); $i++):?>
                            <?php if ($allposts[$i]["is_active"] == 3) { ?>
                                <div class="data-item d-flex">
                                    <div class="user-info-content">
                                    <?php 
                                        $postType = 'Video Post';
                                        $type = "Sales";
                                        if($allposts[$i]['media_type'] == 0){
                                            $postType = 'Text Post';
                                        }elseif($allposts[$i]['media_type'] == 1){
                                            $postType = 'Image Post';
                                        }

                                        if($allposts[$i]['post_type'] == 1){
                                            $type = 'Advice';
                                        }
                                        else if($allposts[$i]['post_type'] == 3){
                                          $type = 'Service';
                                        }
                                        else if($allposts[$i]['post_type'] == 4){
                                          $type = 'Poll';
                                        }                             
                                        ?>
                                        <span class="post-tag"><i class="fa-solid fa-star"></i> <?php echo($type);?></span> 
                                        <span class="post-info"><?php echo($postType);?></span>
                                        <div class="post-content">
                                            <p><?php echo $allposts[$i]['title'] ?></p>
                                        </div>
                                        <div class="data-info ">
                                            <div class="data-info-item date">
                                                <i class="fa-solid fa-circle-user"></i>
                                                <span><a href="<?php echo route('admin.signups.detail', $allposts[$i]['user'][0]['id']);?>"><?php echo $allposts[$i]['user'][0]["user_name"]; ?></a></span>
                                            </div>
                                            <div class="data-info-item date">
                                                <i class="fa-regular fa-calendar-day"></i>
                                                <span><?php echo human_readable_date($allposts[$i]['created_at']);?></span>
                                            </div>
                                            <div class="data-info-item time">
                                                <i class="fa-solid fa-heart"></i>
                                                <span><?php echo $allposts[$i]['likes'];?></span>
                                            </div>
                                            <div class="data-info-item time">
                                                <i class="fa-solid fa-comment"></i>
                                                <span><?php echo $allposts[$i]['comments'];?> </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="width: 120px; min-width:120px;">
                                    <a href="<?php echo route('admin.signups.view_post', $allposts[$i]['id']);?>" >
                                    
                                        <?php if (!empty($allposts[$i]['post_imgs'])) { ?>
                                            <img src="<?php echo $allposts[$i]['post_imgs'][0]['path'];?>" alt="Forest">
                                        <?php } else{?>
                                            <img style="border : initial;opacity:0" >
                                        <?php }?>
                                   
                                        <i class="fa-regular fa-chevron-right" style = " margin-left:10px"></i>
                                    </a>
                                </div>
                                </div>
                                <?php } ?>
                            <?php endfor;?> 
                    </div>
                </div>
            </div>

   

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
   <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
   <script>
        var newNotifications = <?php echo json_encode($newNotifications);?>;
        var open_reports = <?php echo json_encode($open_reports);?>;

        function getbaseurl(){
            return <?php echo "'".base_url()."'"?>;
        }
        function cbChanged(checkboxElem) {
            if (checkboxElem.checked) {
                var index = checkboxElem.value;
                 var notification_id = newNotifications[index]['id']              
                $.ajax({
                    type:'POST',
                    url:'<?php echo route('admin.notifications.readnotification') ?>',
                    data:{'notification_id':notification_id},
                    dataType:"json",
                    success:function(data){     
                         location.href = getbaseurl() + "admin/notifications";

                    }
                });

            } 
        }
        function onReadReport(checkboxElem) {
            if (checkboxElem.checked) {
                var index = checkboxElem.value;
                 var reportid = open_reports[index]['id']              
                $.ajax({
                    type:'POST',
                    url:'<?php echo route('admin.notifications.ignoreReport') ?>',
                    data:{'reportid':reportid},
                    dataType:"json",
                    success:function(data){     
                         location.href = getbaseurl() + "admin/notifications";
                        alert(reportid);
                    }
                });

            } 
        }
        
   </script>
</body>
</html>