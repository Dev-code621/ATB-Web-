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
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
    <meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: dark)">
    
    <title>ATB Admin Portal</title>
    <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/glide.core.min.css" />
   

</head>
<body>
  
    <main class="posts-page minPaddingTop bg-white">
        <div class="container">
            <header>
                <a href="javascript:history.go(-1)" class="nav-link"><i class="fa-regular fa-chevron-left"></i></a>
                <div class="user-info">
                    <div class="user-icon">
                        <img src="<?php echo $post['user'][0]['pic_url'];?>" alt="User icon">
                    </div>
                    <div class="user-info-content">
                        <h2 class="user-name"><?php echo $post['user'][0]['first_name'];?> <?php echo $post['user'][0]['last_name'];?></h2>
                        <a href="<?php echo route('admin.signups.detail', $post['user'][0]['id']);?>" class="user-username"> @<?php echo $post['user'][0]['user_name'];?></a>
                    </div>
                </div>
           
            <?php if ($post['is_active'] == 1) { ?>
                <a href="#" data-modal="blockModal" class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i> Block this post</a>

            <?php } ?>
            <?php if ($post['is_active'] == 2) { ?>
                <a href="#" data-modal="unblockModal" class="btn btn-outline-danger"><i class="fa-solid fa-circle-check"></i> Unblock this post</a>

            <?php } ?>

            <?php if ($post['is_active'] == 3) { ?>
                <a href="#" data-modal="blockModal1" class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i> Decline this post</a>
                <a href="#" data-modal="unblockModal1" class="btn btn-success"><i class="fa-solid fa-circle-check"></i> Approve this post</a>
            <?php } ?>   
            </header>
        </div>
        <div class="modal" id="blockModal">
                <div class="closeModal" data-close="blockModal"><i class="fa-regular fa-circle-xmark"></i></div>
                <div class="text-center">
                    <div class="iconTitle"><i class="fa-solid fa-user-minus"></i></div>
                    <h3>Are you sure you want to block this Post?</h3>
                    <h4>If so, please provide a reason below</h4>
                    <div id="screenHeight"></div>
                </div>
                <form 
                   action="<?php echo route('admin.signups.block_post');?>" method="get" enctype="multipart/form-data" action="<?php echo route('admin.business.submit_block');?>" method="get" enctype="multipart/form-data" >
                    <textarea rows="5" id="blockReason" name="blockReason"  placeholder="This business is providing spam, in all inboxes"></textarea>
                    <input type="hidden" id="block_postid" name="block_postid" value="<?php echo  $post['id'];?>">
                    <button type="submit"  class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i> Block this post</button>


                </form>
            </div>

            <div class="modal" id="unblockModal">
                <div class="closeModal" data-close="unblockModal"><i class="fa-regular fa-circle-xmark"></i></div>
                <div class="text-center">
                    <div class="iconTitle"><i class="fa-solid fa-user-minus"></i></div>
                    <h3>Are you sure you want to unblock this post?</h3>
                    <h4>If so, please provide a reason below</h4>
                    <div id="screenHeight"></div>
                </div>
                <form 
                   action="<?php echo route('admin.signups.unblock_post');?>" method="get" enctype="multipart/form-data" action="<?php echo route('admin.business.submit_block');?>" method="get" enctype="multipart/form-data" >
                    <textarea rows="5" id="unblockReason" name="unblockReason" placeholder="Please provide approved reason"></textarea>
                    <input type="hidden" id="unblock_postid" name="unblock_postid" value="<?php echo  $post['id'];?>">
                    <button type="submit"  class="btn btn-outline-danger"><i class="fa-solid fa-circle-check"></i> Unblock this post</button>


                </form>
            </div>

            <div class="modal" id="blockModal1">
                <div class="closeModal" data-close="blockModal1"><i class="fa-regular fa-circle-xmark"></i></div>
                <div class="text-center">
                    <div class="iconTitle"><i class="fa-solid fa-user-minus"></i></div>
                    <h3>Are you sure you want to decline this Post?</h3>
                    <h4>If so, please provide a reason below</h4>
                    <div id="screenHeight"></div>
                </div>
                <form 
                   action="<?php echo route('admin.signups.block_post');?>" method="get" enctype="multipart/form-data" action="<?php echo route('admin.business.submit_block');?>" method="get" enctype="multipart/form-data" >
                    <textarea rows="5" id="blockReason" name="blockReason"  placeholder="This business is providing spam, in all inboxes"></textarea>
                    <input type="hidden" id="block_postid" name="block_postid" value="<?php echo  $post['id'];?>">
                    <button type="submit"  class="btn btn-outline-danger"><i class="fa-regular fa-ban"></i> Decline this post</button>


                </form>
            </div>

            <div class="modal" id="unblockModal1">
                <div class="closeModal" data-close="unblockModal1"><i class="fa-regular fa-circle-xmark"></i></div>
                <div class="text-center">
                    <div class="iconTitle"><i class="fa-solid fa-user-minus"></i></div>
                    <h3>Are you sure you want to approve this post?</h3>
                    <h4>If so, please provide a reason below</h4>
                    <div id="screenHeight"></div>
                </div>
                <form 
                   action="<?php echo route('admin.signups.unblock_post');?>" method="get" enctype="multipart/form-data" action="<?php echo route('admin.business.submit_block');?>" method="get" enctype="multipart/form-data" >
                    <textarea rows="5" id="unblockReason" name="unblockReason" placeholder="Please provide approved reason"></textarea>
                    <input type="hidden" id="unblock_postid" name="unblock_postid" value="<?php echo  $post['id'];?>">
                    <button type="submit"  class="btn btn-success"><i class="fa-solid fa-circle-check"></i> Approve this post</button>


                </form>
            </div>

        <div class="glide" id="sliderPosts">
             <?php if(($post['media_type']) != 0) { ?>
                <?php if(($post['media_type']) == 0) { //text only?>

                <?php }
                else if(($post['media_type']) == 1) { //image?>                
                    <div class="glide__track" data-glide-el="track">                     
                        <ul class="glide__slides">
                          <?php  for ($i = 0; $i < count($post['post_imgs']); $i++) { ?>                            
                                <li class="glide__slide">
                                    <img src="<?php echo $post['post_imgs'][$i]['path'];?>" alt="">
                               </li>
                            <?php } ?>
                        </ul>                        
                    </div>
                    <div class="glide__arrows" data-glide-el="controls">
                    <span class="glide__arrow glide__arrow--left" data-glide-dir="<"><i class="fa-duotone fa-circle-chevron-left"></i></span>
                        <span class="glide__arrow glide__arrow--right" data-glide-dir=">"><i class="fa-duotone fa-circle-chevron-right"></i></span>
                    </div>
                    <div class="glide__bullets" data-glide-el="controls[nav]">
                        <?php  for ($i = 0; $i < count($post['post_imgs']); $i++) { ?>                            
                            <button class="glide__bullet" data-glide-dir="=<?php echo $i ?>"></button>
                        <?php } ?>
                      
                    </div> 
               <?php }else { //video?>
                    <div class="glide__track" data-glide-el="track">   
                        <video controls style="max-height: 400px;">
                            <source src="<?php echo $post['post_imgs'][0]['path'];?>" type="video/mp4">
                        </video>
                     </div>
                <?php }?>
            <?php }?>

           
        </div>

            <section class="tabs-container posts-tab mt-30">
                <div class="navTabs position-relative">
                    <button class="btn tablinks active" data-tab="report-information">Post Information</button>
                    <button class="btn tablinks" data-tab="post-comments">Post Comments</button>
                </div>
                
                <div class="data-container tab-content-wrapper container bg-gray">
                    <div data-tabcontent="report-information" class="tabcontent" style="display: block;">
                        
                        <div class="business-info">
                            <div class="business-info-content">
                                <h3 class="business-subtitle"><i class="fa-regular fa-circle-user"></i>Posted by:</h3>
                                <p><?php echo $post['user'][0]['first_name'];?> <?php echo $post['user'][0]['last_name'];?> <span> @<?php echo $post['user'][0]['user_name'];?></span></p>
                            </div>
                            <div class="business-info-content">
                                <h3 class="business-subtitle"><i class="fa-solid fa-align-left"></i>Post Description</h3>
                                <p ><?php echo $post['description'];?> </p>

                                <div class="post-description"> 
                                 
                                    
                                    <div class="content">
                                        <span>Price,starting from</span>
                                        <strong>£<?php echo $post["price"];?></strong>
                                    </div>
                                    <div class="content">
                                        <span>Needs a depost of</span>
                                        <strong>£<?php echo $post["deposit"];?></strong>
                                    </div>
                                    <div class="content">
                                        <span>Cancellations Within</span>
                                        <strong>  <?php echo $post["cancellations"];?> days    
                                       </strong>
                                    </div>
                                    <div class="content">
                                        <span>Duration of Service</span>
                                        <strong>  <?php if ( $post["duration"] !=99) { ?><?php echo $post["duration"];?> hrs                                 
                                                 <?php } else {?>
                                                    All Day
                                                <?php } ?>
                                       </strong>
                                    </div>
                                    <?php if (!empty($post["post_location"])) { ?>
                                        <div class="content">
                                            <span>Location</span>
                                            <strong><?php echo $post["post_location"];?></strong>
                                        </div>
                                    <?php } ?>
                                    <div class="content">
                                        <span>Insurance</span>
                                        <strong><?php if (  !empty($post["insurance"])) { ?>
                                                <a href=" <?php if (!empty($post["insurance"][0]["file"])) { ?> <?php echo $post["insurance"][0]["file"] ?> <?php  }?>" >
                                                        Yes
                                                       
                                                </a>                               
                                                 <?php } else {?>
                                                    No
                                                <?php } ?></strong>
                                    </div>

                                    <div class="content">
                                        <span>Qualifications</span>
                                        <strong><?php if ( !empty($post["qualification"])) { ?>
                                            <a href=" <?php if (!empty($post["qualification"][0]["file"])) { ?> <?php echo $post["qualification"][0]["file"] ?> <?php  }?>" >
                                                        Yes
                                                       
                                                </a>                              
                                                 <?php } else {?>
                                                    No
                                                <?php } ?></strong>
                                    </div>
                                  
                                </div>
                            </div>
                            <div class="business-info-content">
                                <h3 class="business-subtitle"><i class="fa-regular fa-square-plus"></i>Posted:</h3>
                            
                                <div class="data-info">
                                    <div class="data-info-item date">
                                        <i class="fa-regular fa-calendar-day"></i>
                                        <span> <?php echo date('d/m/Y',$post['created_at']);?></span>
                                    </div>
                                    <div class="data-info-item time">
                                        <i class="fa-regular fa-clock"></i>
                                        <span><?php echo date('H:i',$post['created_at']);?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="business-info-content">
                                <h3 class="business-subtitle"><i class="fa-regular fa-tag"></i>Post type:</h3>
                                <p> <i class="fa-regular fa-tag"></i> 
                                <?php
                                    switch ($post['post_type']) {
                                        case 1:
                                            echo 'Advice';
                                            break;
                                        case 2:
                                            echo 'Sales';
                                            break;
                                        case 3:
                                            echo 'Service';
                                            break;
                                        case 4:
                                            echo 'Poll';
                                            break;


                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="business-info-content">
                                <h3 class="business-subtitle"><i class="fa-regular fa-border-all"></i>Category</h3>
                                <p> <?php echo $post["category_title"];?></p>
                            </div>
                            <!-- <div class="business-info-content">
                                <h3 class="business-subtitle"><i class="fa-solid fa-heart"></i>Social Media</h3>
                                <a href="#" class="social-link"><i class="fa-brands fa-twitter"></i> @me_store</a>
                                <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i> …/me_store</a>
                                <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i> @me_store</a>
                            </div>
                            <div class="business-info-content">
                                <h3 class="business-subtitle"><i class="fa-regular fa-magnifying-glass"></i>Tags</h3>
                                <div class="tags">                                   
                                    <a href="#">lashes</a>, 
                                    <a href="#">russianlashes</a>
                                </div>
                            </div> -->
                        </div>
                        
                    </div>

                    <div data-tabcontent="post-comments" class="tabcontent">
                        <div class="comments-section">
                            <div class="container">
                                <form action="">
                                    <div class="search-input">
                                        <i class="fa-regular fa-magnifying-glass"></i>
                                        <input type="seach" placeholder="Search" id="search" />
                                        <button class="btn clearInput" onclick="document.getElementById('search').value = ''" type="button">
                                            <i class="fa-solid fa-times-circle"></i>
                                        </button>
                                    </div>
                                </form>

                                <div class="business-info-content">
                                    <h3 class="business-subtitle"><i class="fa-solid fa-comment-lines"></i>Comments</h3>
                                    <?php for($i = 0 ; $i < count($post['comments']); $i++):?>
                                        <?php $str = ""; $json = json_decode($post['comments'][$i]['comment']);?>

                                        <div class="comments">
                                        <div class="comment-info">
                                            <p><i class="fa-solid fa-circle-user"></i> <?php echo $post['comments'][$i]['user_name'];?></p>
                                            <p><i class="fa-regular fa-calendar-clock"></i> <?php echo $post['comments'][$i]['read_created'];?></p>
                                        </div>
                                        <p>
                                                <?php for($j = 0 ; $j < count($json); $j++){  
                                                    if(property_exists($json[$j],'user_id')){
                                                    ?>  
                                                         <a href="<?php echo route('admin.signups.detail', $json[$j]->user_id);?>" style="display: inline;" class="user-username"> <span style="font-size: 20px;"><?php echo  preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                                                            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                                                        }, $json[$j]->comment); ?></span></a>
                                                    <?php } else{
                                                        echo  preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                                                            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                                                        }, $json[$j]->comment);

                                                    }                                               
                                                }?>
                                            <p>        
                                    </div>
                                    <?php endfor;?>                                 
                                </div>


                            </div>

                        </div>

                    </div>
                   
                </div>
            </section>

         


            

       

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/glide.min.js"></script>
    <script>
        let slider = document.getElementById('sliderPosts');
        new Glide(slider).mount();
    </script>


</body>
</html>