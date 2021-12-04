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
                        <!-- END PAGE HEADER-->
                        <div class="full-height-content full-height-content-scrollable">
                            <div class="full-height-content-body">
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption">
                                        <img src="<?php echo base_url().$me['pic_url'];?>"
                                                 class="img-circle" alt=""
                                                 style="width: 48px; height: 48px; border:4px solid #A6BFDE;"/>
                                            <span class="caption-subject bold">   <?php echo $me['first_name'].' '.$me['last_name']?> </span>
                                            <span class="caption-helper">   Reports (<?php echo count($reports);?> )</span>
                                        </div>
                                        <div class="actions">
                                            <a href="<?php echo route('admin.signups.detail', 1);?>" class="btn btn-circle blue-hoki"> Back </a>
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="row" style="border-bottom: 1px solid #d0d0d0 ;">
                                            <div class="col-sm-12 col-md-offset-1 col-md-1 padding-tb-10">
                                                <p class="font-grey-mint"> Post Type </p>
                                            </div>
                                            <div class="col-sm-12 col-md-1 padding-tb-10">
                                                <p class="font-grey-mint"> Media Type </p>
                                            </div>
                                            <div class="col-sm-12 col-md-3 padding-tb-10">
                                                <p class="font-grey-salsa"> Subject </p>
                                            </div>
                                            <div class="col-sm-12 col-md-1 padding-tb-10">
                                                <p class="font-grey-salsa"> Likes </p>
                                            </div>
                                            <div class="col-sm-12 col-md-1 padding-tb-10">
                                                <p class="font-grey-mint"> Comments </p>
                                            </div>
                                            <div class="col-sm-12 col-md-2 padding-tb-10">
                                                <p class="font-grey-mint"> Action </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="panel-group accordion" id="accordion_all">
                                                    <?php for($i = 0 ; $i < count($reports); $i++):?>
                                                        <!---BEGIN REPORT POST ITEM--->
                                                            <div class="panel" style="background-color: #FF89000F;">
                                                                <div class="panel-heading accordion-toggle" style="background-color: #FF89000F;" data-toggle="collapse" data-parent="#accordion_all" href="#collapse<?php echo $i;?>">
                                                                        <div class="row padding-tb-10">
                                                                            <div class="col-sm-12 col-md-offset-1 col-md-1">
                                                                                <p class="font-grey-mint" > 
                                                                                <?php if($reports[$i]['post_info']['post_type'] == 1):?>
                                                                                    Advice
                                                                                <?php else:?>
                                                                                    Sales
                                                                                <?php endif;?>
                                                                                </p>
                                                                            </div>
                                                                            <div class="col-sm-12 col-md-1">
                                                                            <?php if($reports[$i]['post_info']['media_type'] == 0):?>
                                                                                <p class="label label-warning">Text Post</p>
                                                                            <?php elseif($reports[$i]['post_info']['media_type'] == 1):?>
                                                                                <p class="label label-danger">
                                                                                    Image Post
                                                                                </p>
                                                                            <?php else:?>
                                                                                <p class="label label-success">
                                                                                    Video Post
                                                                                </p>
                                                                            <?php endif;?>
                                                                            </div>
                                                                            <div class="col-sm-12 col-md-3">
                                                                                <p class="font-grey-salsa"><?php echo $reports[$i]['post_info']['title'];?></p>
                                                                            </div>
                                                                            <div class="col-sm-12 col-md-1">
                                                                                <p class="font-grey-salsa">
                                                                                    <?php echo count($reports[$i]['post_info']['likes']);?>
                                                                                </p>
                                                                            </div>
                                                                            <div class="col-sm-12 col-md-1 ">
                                                                                <p class="font-grey-salsa">
                                                                                    <?php echo count($reports[$i]['post_info']['comments']);?>
                                                                                </p>
                                                                            </div>
                                                                            <div class="col-sm-12 col-md-2 ">
                                                                                <a href="javascript:;" class="btn btn-circle btn-icon-only blue-hoki "><i class="fa fa-eye"></i> </a>
                                                                                <a href="javascript:;" class="btn btn-circle btn-icon-only red-flamingo"><i class="fa fa-trash"></i> </a>
                                                                            </div>
                                                                            
                                                                        </div>
                                                                </div>
                                                                <div id="collapse<?php echo $i;?>" class="panel-collapse collapse">
                                                                    <div class="row" style="padding: 12px;">
                                                                        <div class="col-sm-12 col-md-2">
                                                                            <?php if(($reports[$i]['post_info']['media_type']) == 0) { //text only?>
                                                                                <img style="width: 100%; height: 120px; border-radius: 8px;" src="<?php echo base_url();?>admin_assets/img/placeholder_img.png">
                                                                            <?php }
                                                                            else if(($reports[$i]['post_info']['media_type']) == 1) { //image?>
                                                                                <img style="width: 100%; height: 120px; border-radius: 8px;" src="<?php echo base_url().$reports[$i]['post_info']['post_imgs'][0]['path'];?>">
                                                                            <?php }
                                                                            else { //video?>
                                                                                <video style="width: 100%; height: 120px; border-radius: 8px;" >
                                                                                    <source src="<?php echo base_url().$reports[$i]['post_info']['post_imgs'][0]['path'];?>" type="video/mp4">
                                                                                </video>
                                                                            <?php }?>
                                                                        </div>
                                                                        <div class="col-sm-12 col-md-7">
                                                                            <label> This post has been reported by <a href="<?php echo route('admin.signups.detail', $me['id']);?>">@<?php echo $me['user_name'];?></a>  </label>
                                                                            <h5 class="bold"> Report Description </h5>
                                                                            <?php echo $reports[$i]['content'];?>
                                                                            <h5 class="bold"> Reported Date </h5>
                                                                            <?php echo date('d/m/Y - H:i:s', $reports[$i]['created_at']);?>
                                                                            
                                                                        </div>
                                                                    </div>
                                                                    <div class="row padding-tb-10">
                                                                        <div class="col-sm-12 col-md-3 col-md-offset-1">
                                                                            <a href="javascript:;" class="btn btn-circle btn-block blue-hoki"> Chat with @<?php echo $me['id'];?> </a>
                                                                        </div>
                                                                        <div class="col-sm-12 col-md-2">
                                                                            <a href="javascript:;" class="btn btn-circle btn-block blue-hoki"> Dismiss </a>
                                                                        </div>
                                                                        <div class="col-sm-12 col-md-2">
                                                                            <a href="javascript:;" class="btn btn-circle btn-block red-flamingo"> Delete Post </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <!---END  REPORT POST ITEM--->
                                                    <?php endfor;?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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