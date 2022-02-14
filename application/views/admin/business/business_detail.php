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
                    <a href="<?php echo route('admin.business.index');?>" class="nav-link"><i class="fa-regular fa-chevron-left"></i></a>
                    <div class="user-info d-block">
                        <div class="user-icon">
                            <img src="<?php echo $business['business_logo'];?>" alt="User icon">
                        </div>
                        <div class="user-info-content text-center">
                            <h2 class="user-name"><?php echo $business['business_name'];?></h2>
                            <p class="user-username">@<?php echo $business['user']["profile"]['user_name'];?></p>
                        </div>
                    </div>
                    <a href="#" data-modal="bussinessModal" class="nav-link"><i class="fa-regular fa-ellipsis"></i></a>
                </header>
                <div class="user-description">
                    <p> <?php echo $business['business_bio'];?></p>
                    <a href="<?php echo $business['business_website'];?>"><?php echo $business['business_website'];?></a></p>
                </div>
                <p class="btn btn-sm btn-outline-warning"><i class="fa-regular fa-alarm-clock"></i>     
                    <?php          
                    switch ($business['approved']) {
                        case 0:
                            echo '<span class="text-muted">Waiting on approval</span>';
                            break;
                        case 1:
                            echo '<span class="text-success">Approved</span>';
                            break;
                        case 2:
                            echo '<span class="text-danger" data-toggle="tooltip" data-placement="top" title="'.$business['approval_reason'].'">Rejected</span>';
                            break;
                    }
                    ?>
                </p>
            </div>
            
            <div class="business-info">
                <div class="container">
                    <h2 class="business-title">Business Information</h2>
                   
                    <div class="business-info-content">
                        <h3 class="business-subtitle"><i class="fa-solid fa-circle-user"></i><?php echo $business['user']["profile"]['first_name']. " ". $business['user']["profile"]['last_name'];?></h3>
                        <p><span>@<?php echo $business['user']["profile"]['user_name'];?></span></p>
                    </div>

                    <div class="business-info-content">
                        <h3 class="business-subtitle"><i class="fa-regular fa-business-time"></i>Operating Hours</h3>
                        <table class="business-time"  cellspacing="0" cellpadding="0">
                            <tbody>
                                <tr>
                                    <td>Monday</td>
                                    <td><?php echo $business['opening_times'][0]["start"] . " - " . $business['opening_times'][0]["end"]?></td>
                                </tr>
                                <tr>
                                    <td>Tuesday</td>
                                    <td><?php echo $business['opening_times'][1]["start"] . " - " . $business['opening_times'][1]["end"]?></td>

                                </tr>
                                <tr>
                                    <td>Wednesday</td>
                                    <td><?php echo $business['opening_times'][2]["start"] . " - " . $business['opening_times'][2]["end"]?></td>

                                </tr>
                                <tr>
                                    <td>Thursday</td>
                                    <td><?php echo $business['opening_times'][3]["start"] . " - " . $business['opening_times'][3]["end"]?></td>

                                </tr>
                                <tr>
                                    <td>Friday</td>
                                    <td><?php echo $business['opening_times'][4]["start"] . " - " . $business['opening_times'][4]["end"]?></td>

                                </tr>
                                <tr>
                                    <td>Saturday</td>
                                    <td><?php echo $business['opening_times'][5]["start"] . " - " . $business['opening_times'][5]["end"]?></td>

                                </tr>
                                <tr>
                                    <td>Sunday</td>
                                    <td><?php echo $business['opening_times'][6]["start"] . " - " . $business['opening_times'][6]["end"]?></td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="business-info-content">
                        <h3 class="business-subtitle"><i class="fa-regular fa-shield"></i>Insurance</h3>
                        
                        <?php for($i = 0 ; $i < count($business['services']); $i++){ ?>
                            <?php for($a = 0 ; $a < count($business['services'][$i]["insurance"]); $a++){ ?>
                                <a href="  <?php if (!empty($business['services'][$i]["insurance"][$a]["file"])) { ?> <?php echo $business['services'][$i]["insurance"][$a]["file"] ?> <?php  }?>" class="btn btn-bagde">
                                    <i class="fa-regular fa-shield"></i>
                                    <p>
                                        <b><?php
                                             echo $business['services'][$i]["insurance"][$a]["reference"];
                                            ?>
                                        </b>
                                        Insurance Until 
                                        <?php
                                            echo $business['services'][$i]["insurance"][$a]["expiry"];
                                        ?>
                                    </p>
                                    <i class="fa-regular fa-chevron-right"></i>
                                </a>     
                            <?php }?>
                        <?php }?>               
                    </div>

                    <div class="business-info-content">
                         <?php for($i = 0 ; $i < count($business['services']); $i++){ ?>
                            <?php for($a = 0 ; $a < count($business['services'][$i]["qualification"]); $a++){ ?>
                                <h3 class="business-subtitle"><i class="fa-solid fa-award"></i>Qualifications</h3>
                                <a href=" <?php if (!empty($business['services'][$i]["qualification"][$a]["file"])) { ?> <?php echo $business['services'][$i]["qualification"][$a]["file"] ?> <?php  }?>" class="btn btn-bagde">
                                    <i class="fa-solid fa-award"></i>
                                    <p>
                                        <b><?php
                                             echo $business['services'][$i]["qualification"][$a]["reference"];
                                            ?></b>
                                        Valid Until         
                                        <?php
                                            echo $business['services'][$i]["qualification"][$a]["expiry"];
                                        ?>
                                    </p>
                                    <i class="fa-regular fa-chevron-right"></i>
                                </a>
                            <?php }?>               
                        <?php }?>               
                    </div>

                    <div class="business-info-content">
                    <h3 class="business-subtitle"><i class="fa-solid fa-heart"></i>Social Media</h3>

                    <?php for($i = 0 ; $i < count($business['socials']); $i++){ ?>
                        <?php if ($business['socials'][$i]["type"] == "0") {
                                echo '<a target="_blank" href="https://www.facebook.com/'.$business['socials'][$i]["social_name"].'" class="social-link" >'.'<i class="fa-brands fa-facebook"></i>'.$business['socials'][$i]["social_name"].'</a>';
                            } else if ($business['socials'][$i]["type"] == "1") {
                                echo '<a target="_blank" href="https://www.instagram.com/'.$business['socials'][$i]["social_name"].'" class="social-link" >'.'<i class="fa-brands fa-instagram"></i>'.$business['socials'][$i]["social_name"].'</a>';
                            } else if ($business['socials'][$i]["type"] == "2") {
                                echo '<a target="_blank" href="https://twitter.com/'.$business['socials'][$i]["social_name"].'" class="social-link">'.'<i class="fa-brands fa-twitter"></i>'.$business['socials'][$i]["social_name"].'</a>';
                            }
                            
                            ?>
                    <?php }?>

                    </div>
                    <div class="business-info-content">
                        <h3 class="business-subtitle"><i class="fa-solid fa-tag"></i>Tags</h3>
                        <div class="tags">
                            <i class="fa-solid fa-tag"></i>
                            <?php foreach($business["tags"] as $tag ){ ?>
                                <a href="#"> <?php echo $tag["name"].",";?> </a> 
                                <?php }?>
                           
                        </div>

                </div>
            </div>

            <?php          
                if ($business['approved'] == 0) {?>
                    <div class="btn-footer">
                        <a href="#" data-modal="blockModal" class="btn btn-outline-danger"><i class="fa-solid fa-circle-xmark"></i>Block</a>
                        <a href="#" data-modal="approveModal" class="btn btn-success"><i class="fa-solid fa-circle-check"></i>Approve</a>
                    </div>
                <?php    
                }else if ($business['approved'] == 1) {?>
                   <div class="btn-footer">
                     <a href="#" data-modal="blockModal" class="btn btn-outline-danger"><i class="fa-solid fa-circle-xmark"></i>Block</a>
                    </div>
                <?php    
                }else{?>
                    <div class="btn-footer">
                        <a href="#" data-modal="approveModal" class="btn btn-success"><i class="fa-solid fa-circle-check"></i>Approve</a>
                    </div>

                <?php }
            ?>    
         
            <div class="modal" id="blockModal">
                <div class="closeModal" data-close="blockModal"><i class="fa-regular fa-circle-xmark"></i></div>
                <div class="text-center">
                    <div class="iconTitle"><i class="fa-solid fa-user-minus"></i></div>
                    <h3>Are you sure you want to block this business User?</h3>
                    <h4>If so, please provide a reason below</h4>
                    <div id="screenHeight"></div>
                </div>
                <form 
                   action="<?php echo route('admin.business.submit_block');?>" method="get" enctype="multipart/form-data" action="<?php echo route('admin.business.submit_block');?>" method="get" enctype="multipart/form-data" >
                    <textarea rows="5" id="blockReason" name="blockReason"  placeholder="This business is providing spam, in all inboxes"></textarea>
                    <input type="hidden" id="block_businessid" name="block_businessid" value="<?php echo  $business['id'];?>">
                    <button type="submit"  class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i> Block this Business user</button>


                </form>
            </div>

            <div class="modal" id="approveModal">
                <div class="closeModal" data-close="approveModal"><i class="fa-regular fa-circle-xmark"></i></div>
                <div class="text-center">
                    <div class="iconTitle"><i class="fa-solid fa-user-minus"></i></div>
                    <h3>Are you sure you want to Approve this business User?</h3>
                    <h4>If so, please provide a reason below</h4>
                    <div id="screenHeight"></div>
                </div>
                <form 
                   action="<?php echo route('admin.business.submit_approve');?>" method="get" enctype="multipart/form-data" action="<?php echo route('admin.business.submit_block');?>" method="get" enctype="multipart/form-data" >
                    <textarea rows="5" id="approveReason" name="approveReason" placeholder="Please provide approved reason"></textarea>
                    <input type="hidden" id="approve_businessid" name="approve_businessid" value="<?php echo  $business['id'];?>">
                    <button type="submit"  class="btn btn-success"><i class="fa-regular fa-ban"></i> Approve this Business user</button>


                </form>
            </div>


            
            <div class="modal" id="bussinessModal" style="background:#A6BFDE;"> 
                <div class="closeModal" data-close="bussinessModal" style="margin-bottom:20px" ><i class="fa-solid fa-circle-xmark" style='color: white'></i></div>
                <div class="text-center">
                    <h3 style='color: white'>WHERE would you like to go?</h3>
                    <div id="screenHeight"></div>
                </div>
                <form 
                   action="<?php echo route('admin.business.threedot');?>" method="get" enctype="multipart/form-data">
                    <input type="hidden" id="userid" name="userid" value="<?php echo  $business['user_id'];?>">  
                    <input type="hidden" id="type" name="type" value="0">                    
                  
                    <button type="submit"  class="btn btn-light" style="background-color:#fff;color:#656565" >BOOKINGS</button>
                </form>
                <form 
                   action="<?php echo route('admin.business.threedot');?>" method="get" enctype="multipart/form-data">
                    <input type="hidden" id="userid" name="userid" value="<?php echo  $business['user_id'];?>">           
                    <input type="hidden" id="type" name="type" value="1">                    
         
                    <button type="submit"  class="btn btn-light" style="background-color:#fff;color:#656565" >SOLD ITEMS</button>
                </form>
                <form 
                   action="<?php echo route('admin.business.threedot');?>" method="get" enctype="multipart/form-data">
                    <input type="hidden" id="userid" name="userid" value="<?php echo  $business['user_id'];?>">                    
                    <input type="hidden" id="type" name="type" value="2">                    

                    <button type="submit"  class="btn btn-light" style="background-color:#fff;color:#656565" >POSTS</button>
                </form>
                <form 
                   action="<?php echo route('admin.business.threedot');?>" method="get" enctype="multipart/form-data">
                    <input type="hidden" id="userid" name="userid" value="<?php echo  $business['user_id'];?>">                   
                    <input type="hidden" id="type" name="type" value="3">                    
 
                    <button type="submit"  class="btn btn-light" style="background-color:#fff;color:#656565" >SERVICES CREATED</button>
                </form>             
            </div>


        </section>


            

       

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
   <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>

</body>
</html>