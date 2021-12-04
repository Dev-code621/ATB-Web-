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
                        <div class="full-height-content full-height-content-scrollable">
                            <div class="full-height-content-body">
                                <h4 class="page-title">
                                    <span class="fa fa-feed"></span>
                                    <a href="javascript:;" class="btn btn-link font-lg">@Zandra</a>
                                    's post
                                </h4>

                                <div class="row">

                                    <div class="col-sm-12 margin-bottom-15">
                                        <a href="<?php echo route('admin.feeds.index');?>" class="margin-top-10 "> <i class="fa fa-chevron-left"></i> Back To feed</a>
                                    </div>
                                </div>
                                <div class="item" >
                                    <div class="row">
                                        <div class="col-sm-12 col-md-5" style="padding-left: 0px; !important;">
                                            <img style="width: 100%; height: 100%;" src="<?php echo base_url();?>admin_assets/sample_post.png">
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <ul class="list-inline bold margin-top-20">
                                                <li>
                                                    <img class="img-circle" style="width: 25px; height: 25px;" src="<?php echo base_url();?>admin_assets/sample_avatar.jpg">
                                                </li>
                                                <li class="font-grey-cascade"> <a href="javascript:;" class="btn btn-link"> @charlie23 </a> posted a service</li>

                                            </ul>
                                            <ul style="list-style: none; padding-inline-start: 0px; padding-top: 20px;" class="font-grey-mint">
                                                <li>
                                                    Hi! This is an amazing UI! Is there a way to turn sidebar completely off with a simple body class, like that which is used to minimize the sidebar? Also, I’m looking for a way to make portlets fill the vertical space between the fixed header and fixed footer. I know that full height divs are a chore.
                                                </li>
                                                <li class="margin-top-10">
                                                    <strong> carol45</strong> this is just what I needed!! Thanks! I fully recommend this option, my nails couldn’t get better than these! I love this style.
                                                    <br>
                                                    <label class="font-grey-salsa"> 2 hours ago </label>
                                                </li>

                                                <li class="margin-top-10">
                                                    <strong> carol45</strong> this is just what I needed!! Thanks! I fully recommend this option, my nails couldn’t get better than these! I love this style.
                                                    <br>
                                                    <label class="font-grey-salsa"> 2 hours ago </label>
                                                </li>
                                            </ul>
                                            <div class="col-sm-12 col-md-8">
                                                <form role="form">
                                                    <div class="form">
                                                        <div class="form-body" style="padding: 0px;">
                                                            <div class="form-group">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control" style="border-top-left-radius: 24px;border-bottom-left-radius: 24px; border: 1px solid #EFEFEF;">
                                                                    <span class="input-group-btn">
                                                                        <button class="btn blue font-blue-hoki" type="button"> Comment </button>
                                                                    </span>
                                                                </div>

                                                            </div>

                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-sm-12 col-md-1">
                                                <button class="btn btn-circle btn-icon-only blue-hoki" type="button"> <i class="fa fa-commenting"></i> </button>
                                            </div>
                                            <div class="col-sm-12 col-md-1">
                                                <button class="btn btn-circle btn-icon-only blue-hoki" type="button"> <i class="fa fa-heart"></i> </button>
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