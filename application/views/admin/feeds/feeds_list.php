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
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/search.css" />
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
  
    <main class="bgEndWhite feed-page">
        <header class="app-header container">
            <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-newspaper"></i> <?php echo $title ?></h1>
            <!-- <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a> -->
            <div class="search-box">            
                <button class="btn-search"><i class="fas fa-search"></i></button>
                <input onchange ="search()" type="text" class="input-search" placeholder="Search" id = "search"> 
            </div>
        </header>

            <div class="tabs-container">
                <div class="navTabs position-relative scrollable mt-0">
                    <button class="btn tablinks active" data-tab="all">All</button>
                    <button class="btn tablinks" data-tab="beauty">Beauty</button>
                    <button class=" btn tablinks" data-tab="celebrations">Celebrations</button>
                    <button class=" btn tablinks" data-tab="events">Events</button>
                    <button class=" btn tablinks" data-tab="home">Home</button>
                    <button class=" btn tablinks" data-tab="kids">Kids</button>
                    <button class=" btn tablinks" data-tab="ladieswear">Ladieswear</button>
                    <button class=" btn tablinks" data-tab="miscellaneous">Miscellaneous</button>
                </div>
                
                <div class="data-container tab-content-wrapper container">
                    <div data-tabcontent="all" class="tabcontent" style="display: block;">
                       <?php for($i = 0 ; $i < count($allposts); $i++):?>
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
                         <?php endfor;?> 
                    </div>
                    <div data-tabcontent="beauty" class="tabcontent">
                        <?php for($i = 0 ; $i < count($allposts); $i++):?>
                            <?php if ($allposts[$i]["category_title"] == "Beauty") { ?>
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
                    <div data-tabcontent="celebrations" class="tabcontent">
                    <?php for($i = 0 ; $i < count($allposts); $i++):?>
                            <?php if ($allposts[$i]["category_title"] == "Celebrations") { ?>
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
                                </div>                                </div>
                                <?php } ?>
                            <?php endfor;?> 
                            
                    </div>
                    <div data-tabcontent="events" class="tabcontent">
                    <?php for($i = 0 ; $i < count($allposts); $i++):?>
                            <?php if ($allposts[$i]["category_title"] == "Events") { ?>
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
                                    <a href="<?php echo route('admin.signups.view_post', $allposts[$i]['id']);?>" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
                                </div>
                                <?php } ?>
                            <?php endfor;?> 
                    </div>
                    <div data-tabcontent="home" class="tabcontent">
                    <?php for($i = 0 ; $i < count($allposts); $i++):?>
                            <?php if ($allposts[$i]["category_title"] == "Home") { ?>
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
                    <div data-tabcontent="kids" class="tabcontent">
                         <?php for($i = 0 ; $i < count($allposts); $i++):?>
                            <?php if ($allposts[$i]["category_title"] == "Kids") { ?>
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
                    <div data-tabcontent="ladieswear" class="tabcontent">
                      <?php for($i = 0 ; $i < count($allposts); $i++):?>
                            <?php if ($allposts[$i]["category_title"] == "Ladieswear") { ?>
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
                                    <a href="<?php echo route('admin.signups.view_post', $allposts[$i]['id']);?>" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
                                </div>
                                <?php } ?>
                            <?php endfor;?> 
                    </div>
                    <div data-tabcontent="miscellaneous" class="tabcontent">
                       <?php for($i = 0 ; $i < count($allposts); $i++):?>
                            <?php if ($allposts[$i]["category_title"] == "Miscellaneous") { ?>
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
                                    <!-- <a href="<?php echo route('admin.signups.view_post', $allposts[$i]['id']);?>" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a> -->

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
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
    <script>
        var allposts = <?php echo json_encode($allposts);?>;
        function getbaseurl(){
            return <?php echo "'".base_url()."'"?>;
        }
        function search() {
             var query = document.getElementById("search").value;
             document.getElementById("search").value = "";
             $('#item').empty();
             window.location = getbaseurl() + "admin/feeds/" + query;


        }
    </script>

</body>
</html>