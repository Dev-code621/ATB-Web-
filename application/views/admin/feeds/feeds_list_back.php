<?php
$user_id= $this->session->userdata('user_id');

if(!$user_id){
	redirect(route('admin.auth.login'));
}
?>
<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD -->
    <?php echo $header_layout;?>
    <!-- END HEAD -->

    <body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-sidebar-fixed">
        <div class="page-wrapper">

            <!-- BEGIN CONTAINER -->
            <div class="page-container" style="margin-top: 0px;">
                <!-- BEGIN SIDEBAR -->
                <?php echo $sidebar_layout;?>
                <!-- END SIDEBAR -->

                <!-- BEGIN CONTENT -->
                <div class="page-content-wrapper">
                    <!-- BEGIN CONTENT BODY -->
                    <div class="page-content">
                        <!-- BEGIN PAGE HEADER-->

                        <!-- BEGIN PAGE BAR -->
                        <div class="page-bar">

                        </div>
                        <!-- END PAGE BAR -->
                        <!-- BEGIN PAGE TITLE-->
                        <h1 class="page-title font-blue-hoki"> <span class="fa fa-feed margin-right-10"></span> Feed
                        </h1>
                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="tabbable-line tabbable-full-width">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab_all_reports" data-toggle="tab"> All Post </a>
                                </li>
                                <?php foreach ($cats as $cat) { ?>
                                    <li>
                                        <a href="#tab_<?php echo $cat; ?>" data-toggle="tab"> <?php echo $cat; ?> </a>
                                    </li>
                                <?php } ?>

                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab_all_reports">
                                    <div class="row">
                                        <div class="col-md-12">
                                                <div class="row">

                                                    <?php foreach($allposts as $post):?>
                                                    <!---BEGIN REPORT POST ITEM--->
                                                    <div class="col-sm-4">
                                                        <div class="blog-post-sm bordered blog-container">
                                                            <div class="blog-img-thumb">
                                                                <a href="<?php echo route('admin.signups.view_post', $post['id']);?>">

                                                                    <?php if(($post['media_type']) == 0) { //text only?>

                                                                    <?php }
                                                                    else if(($post['media_type']) == 1) { //image?>
                                                                        <img style = "width: 100%;" src="<?php echo base_url().$post['post_imgs'][0]['path'];?>">
                                                                    <?php }
                                                                    else { //video?>
                                                                        <video controls style="width:100%;">
                                                                            <source src="<?php echo base_url().$post['post_imgs'][0]['path'];?>" type="video/mp4">
                                                                        </video>
                                                                    <?php }?>
                                                                </a>
                                                                <div style="position: absolute; width: 100%">
                                                                    <a style="float: right; margin: 10px; background-color: #A0522D3A;" class="btn btn-circle btn-icon-only font-white"> <i class="fa fa-tag"></i> </a>
                                                                </div>
                                                            </div>
                                                            <div class="blog-post-content">
                                                                <p class="blog-post-desc"> <strong class="font-grey-mint"><?php echo $post['title'];?> </strong>  <?php echo $post['description'];?></p>

                                                                <div class="blog-post-foot">
                                                                    <span class="font-grey-salsa margin-right-10"> <i class="fa fa-heart font-grey-salsa"></i> <?php echo $post['likes'];?></span>
                                                                    <span class="font-grey-salsa"> <i class="fa fa-comment font-grey-salsa"></i> <?php echo $post['comments'];?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!---END  REPORT POST ITEM--->
                                                    <?php endforeach;?>
                                                </div>
                                        </div>

                                    </div>
                                </div>
                                <!--tab_1_2-->
                                <?php foreach ($cats as $cat) { ?>
                                    <div class="tab-pane" id="tab_<?php echo $cat; ?>">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">

                                                    <?php foreach($allposts as $post):?>
                                                        <?php if ($post["category_title"] == $cat) { ?>
                                                        <!---BEGIN REPORT POST ITEM--->
                                                        <div class="col-sm-4">
                                                            <div class="blog-post-sm bordered blog-container">
                                                                <div class="blog-img-thumb">
                                                                    <a href="<?php echo route('admin.signups.view_post', $post['id']);?>">

                                                                        <?php if(($post['media_type']) == 0) { //text only?>

                                                                        <?php }
                                                                        else if(($post['media_type']) == 1) { //image?>
                                                                            <img style = "width: 100%;" src="<?php echo base_url().$post['post_imgs'][0]['path'];?>">
                                                                        <?php }
                                                                        else { //video?>
                                                                            <video controls style="width:100%;">
                                                                                <source src="<?php echo base_url().$post['post_imgs'][0]['path'];?>" type="video/mp4">
                                                                            </video>
                                                                        <?php }?>
                                                                    </a>
                                                                    <div style="position: absolute; width: 100%">
                                                                        <a style="float: right; margin: 10px; background-color: #A0522D3A;" class="btn btn-circle btn-icon-only font-white"> <i class="fa fa-tag"></i> </a>
                                                                    </div>
                                                                </div>
                                                                <div class="blog-post-content">
                                                                    <p class="blog-post-desc"> <strong class="font-grey-mint"><?php echo $post['title'];?> </strong>  <?php echo $post['description'];?></p>

                                                                    <div class="blog-post-foot">
                                                                        <span class="font-grey-salsa margin-right-10"> <i class="fa fa-heart font-grey-salsa"></i> <?php echo $post['likes'];?></span>
                                                                        <span class="font-grey-salsa"> <i class="fa fa-comment font-grey-salsa"></i> <?php echo $post['comments'];?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                        <!---END  REPORT POST ITEM--->
                                                    <?php endforeach;?>
                                                </div>
                                            </div>
                                    </div>
                                    </div>
                                <?php } ?>


                            </div>
                        </div>

                    </div>
                    <!-- END CONTENT BODY -->
                </div>
                <!-- END CONTENT -->

            </div>
            <!-- END CONTAINER -->

        </div>

        <?php echo $footer_layout;?>
        <script>
            $(document).ready(function()
            {
//                $('#clickmewow').click(function()
//                {
//                    $('#radio1003').attr('checked', 'checked');
//                });
            })
        </script>
    </body>

</html>