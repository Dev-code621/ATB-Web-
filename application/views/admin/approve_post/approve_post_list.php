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
                        <h1 class="page-title font-blue-hoki"> <span class="fa fa-info-circle margin-right-10  "></span> Post Approvals
                        </h1>
                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="tabbable-line tabbable-full-width">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab_all_reports" data-toggle="tab"> All Post </a>
                                </li>
                                <li>
                                    <a href="#tab_approved" data-toggle="tab"> Recently Approved </a>
                                </li>
                                <li>
                                    <a href="#tab_rejected" data-toggle="tab"> Rejected Posts </a>
                                </li>

                            </ul>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_all_reports">
                                            <div class="scroller" style="height: 490px;" data-always-visible="0" data-rail-visible="0" >
                                                <div class="row">
                                                    <?php for($i = 0 ; $i < count($posts); $i++):?>
                                                    <!---BEGIN REPORT POST ITEM--->
                                                    <div class="col-sm-6">
                                                        <div class="blog-post-sm bordered blog-container">
                                                            <div class="blog-img-thumb">
                                                                <a href="javascript:;">
                                                                    <?php if(($posts[$i]['media_type']) == 0) { //text only?>
                                                                        <img style = "width: 100%;" src="<?php echo base_url();?>admin_assets/img/placeholder_img.png">
                                                                    <?php }
                                                                    else if(($posts[$i]['media_type']) == 1) { //image?>
                                                                        <img style = "width: 100%;" src="<?php echo base_url().$posts[$i]['post_imgs'][0]['path'];?>">
                                                                    <?php }
                                                                    else { //video?>
                                                                        <video controls style="width:100%;">
                                                                            <source src="<?php echo base_url().$posts[$i]['post_imgs'][0]['path'];?>" type="video/mp4">
                                                                        </video>
                                                                    <?php }?>
                                                                </a>
                                                            </div>
                                                            <div class="blog-post-content">
                                                                <ul class="list-inline">
                                                                    <li>
                                                                        <img class="img-circle" style="width: 24px; height: 24px;" src="<?php echo base_url().$posts[$i]['auth']['pic_url'];?>">
                                                                    </li>
                                                                    <li class="font-grey-cascade"> <a href="javascript:;" class="btn btn-link"> @<?php echo $posts[$i]['auth']['user_name'];?> </a> has reported a 
                                                                    <?php if($posts[$i]['post_type'] == 1):?>
                                                                        Advice
                                                                    <?php else:?>
                                                                        Service
                                                                    <?php endif;?>
                                                                    </li>

                                                                </ul>
                                                                <p class="blog-post-desc"> <?php echo $posts[$i]['description'];?> </p>
                                                                <p class="blog-post-desc font-grey-salsa"> - <?php echo 
                                                                    human_readable_date($posts[$i]['created_at']);?> </p>

                                                                <div class="blog-post-foot">
                                                                    <?php if($posts[$i]['is_active'] == 0)://reported?>
                                                                        <span> <a href="javascript:;" class="btn btn-circle red-flamingo"> View Report </a> </span>

                                                                    <?php elseif($posts[$i]['is_active'] == 1)://active?>
                                                                        <span> <a href="javascript:;" class="btn btn-circle red-flamingo"> <i class="fa fa-ban"></i> Block Post </a> </span>
                                                                    <?php elseif($posts[$i]['is_active'] == 2)://blocked?>
                                                                        <span> <a href="javascript:;" class="btn btn-circle blue-hoki"> Approve </a> </span>
                                                                    <?php else: //pending approval?>
                                                                        <span> <a href="javascript:;" class="btn btn-circle red-flamingo"> <i class="fa fa-ban"></i> Reject </a> </span>
                                                                        <span> <a href="javascript:;" class="btn btn-circle blue-hoki"> Approve </a> </span>
                                                                    <?php endif;?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!---END  REPORT POST ITEM--->
                                                    <?php endfor;?>
                                                </div>
                                            </div>   
                                        </div>
                                        <!--tab_1_2-->
                                        <div class="tab-pane" id="tab_approved">
                                            <div class="scroller" style="height: 490px;" data-always-visible="0" data-rail-visible="0" >
                                                <div class="row">
                                                    <?php for($i = 0 ; $i < count($posts); $i++){
                                                        if($posts[$i]['is_active'] == 1) {?>
                                                            <!---BEGIN REPORT POST ITEM--->
                                                            <div class="col-sm-6">
                                                                <div class="blog-post-sm bordered blog-container">
                                                                    <div class="blog-img-thumb">
                                                                        <a href="javascript:;">
                                                                            <?php if(($posts[$i]['media_type']) == 0) { //text only?>
                                                                                <img style = "width: 100%;" src="<?php echo base_url();?>admin_assets/img/placeholder_img.png">
                                                                            <?php }
                                                                            else if(($posts[$i]['media_type']) == 1) { //image?>
                                                                                <img style = "width: 100%;" src="<?php echo base_url().$posts[$i]['post_imgs'][0]['path'];?>">
                                                                            <?php }
                                                                            else { //video?>
                                                                                <video controls style="width:100%;">
                                                                                    <source src="<?php echo base_url().$posts[$i]['post_imgs'][0]['path'];?>" type="video/mp4">
                                                                                </video>
                                                                            <?php }?>
                                                                        </a>
                                                                    </div>
                                                                    <div class="blog-post-content">
                                                                        <ul class="list-inline">
                                                                            <li>
                                                                                <img class="img-circle" style="width: 24px; height: 24px;" src="<?php echo base_url().$posts[$i]['auth']['pic_url'];?>">
                                                                            </li>
                                                                            <li class="font-grey-cascade"> <a href="javascript:;" class="btn btn-link"> @<?php echo $posts[$i]['auth']['user_name'];?> </a> has reported a 
                                                                            <?php if($posts[$i]['post_type'] == 1):?>
                                                                                Advice
                                                                            <?php else:?>
                                                                                Service
                                                                            <?php endif;?>
                                                                            </li>

                                                                        </ul>
                                                                        <p class="blog-post-desc"> <?php echo $posts[$i]['description'];?> </p>
                                                                        <p class="blog-post-desc font-grey-salsa"> - <?php echo 
                                                                            human_readable_date($posts[$i]['created_at']);?> </p>

                                                                        <div class="blog-post-foot">
                                                                            <span> <a href="javascript:;" class="btn btn-circle red-flamingo"> <i class="fa fa-ban"></i> Block Post </a> </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!---END  REPORT POST ITEM--->
                                                    <?php } }?>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end tab-pane-->
                                        <div class="tab-pane" id="tab_rejected">
                                        <div class="tab-pane" id="tab_approved">
                                            <div class="scroller" style="height: 490px;" data-always-visible="0" data-rail-visible="0" >
                                                <div class="row">
                                                    <?php for($i = 0 ; $i < count($posts); $i++){
                                                        if($posts[$i]['is_active'] == 2) {?>
                                                            <!---BEGIN REPORT POST ITEM--->
                                                            <div class="col-sm-6">
                                                                <div class="blog-post-sm bordered blog-container">
                                                                    <div class="blog-img-thumb">
                                                                        <a href="javascript:;">
                                                                            <?php if(($posts[$i]['media_type']) == 0) { //text only?>
                                                                                <img style = "width: 100%;" src="<?php echo base_url();?>admin_assets/img/placeholder_img.png">
                                                                            <?php }
                                                                            else if(($posts[$i]['media_type']) == 1) { //image?>
                                                                                <img style = "width: 100%;" src="<?php echo base_url().$posts[$i]['post_imgs'][0]['path'];?>">
                                                                            <?php }
                                                                            else { //video?>
                                                                                <video controls style="width:100%;">
                                                                                    <source src="<?php echo base_url().$posts[$i]['post_imgs'][0]['path'];?>" type="video/mp4">
                                                                                </video>
                                                                            <?php }?>
                                                                        </a>
                                                                    </div>
                                                                    <div class="blog-post-content">
                                                                        <ul class="list-inline">
                                                                            <li>
                                                                                <img class="img-circle" style="width: 24px; height: 24px;" src="<?php echo base_url().$posts[$i]['auth']['pic_url'];?>">
                                                                            </li>
                                                                            <li class="font-grey-cascade"> <a href="javascript:;" class="btn btn-link"> @<?php echo $posts[$i]['auth']['user_name'];?> </a> has reported a 
                                                                            <?php if($posts[$i]['post_type'] == 1):?>
                                                                                Advice
                                                                            <?php else:?>
                                                                                Service
                                                                            <?php endif;?>
                                                                            </li>

                                                                        </ul>
                                                                        <p class="blog-post-desc"> <?php echo $posts[$i]['description'];?> </p>
                                                                        <p class="blog-post-desc font-grey-salsa"> - <?php echo 
                                                                            human_readable_date($posts[$i]['created_at']);?> </p>

                                                                        <div class="blog-post-foot">
                                                                        <span> <a href="javascript:;" class="btn btn-circle blue-hoki"> Approve </a> </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!---END  REPORT POST ITEM--->
                                                    <?php } }?>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <!--end tab-pane-->

                                    </div>
                                </div>
                            
                                <div class="col-md-4" style="padding-right: 80px;">
                                    <div class="item padding-tb-20" style="text-align: center;">
                                        <i class="fa fa-info-circle font-blue-hoki" style="font-size: 30px; line-height: 60px;"></i>
                                        <h1 class="font-blue-hoki bold" style="margin-top: 0px;"> <?php echo $notification_counter[MENU_POST_APPROVALS];?> </h1>
                                        <p class="font-grey-silver" style="margin-top: 0px; margin-bottom: 8px;"> New Post Pending for Approval </p>
                                    </div>

                                    <form role="form">
                                        <div class="form item" style="background-color: #A6BFDE;">
                                            <div class="form-body">
                                                <h4 class="text-center font-white"> Filter Posts </h4>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control font-white" placeholder="By Users">
                                                        <span class="input-group-btn">
                                                            <button class="btn blue" type="button"> <span class="fa fa-search"></span> </button>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control font-white" placeholder="By Date">
                                                        <span class="input-group-btn">
                                                            <button class="btn blue" type="button"> <span class="fa fa-search"></span> </button>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control font-white" placeholder="By Category">
                                                        <span class="input-group-btn">
                                                            <button class="btn blue" type="button"> <span class="fa fa-search"></span> </button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
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