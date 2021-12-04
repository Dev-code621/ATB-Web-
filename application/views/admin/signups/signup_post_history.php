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

                        <!-- BEGIN PAGE TITLE-->

                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="full-height-content full-height-content-scrollable">
                            <div class="full-height-content-body">
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <div class="col-md-4">
                                                 <img src="<?php echo $me['pic_url'];?>"
                                                 class="img-circle" alt=""
                                                 style="width: 48px; height: 48px;"/>
                                            </div>
                                            <div class="col-md-8">
                                            <span class="caption-subject bold">   <?php echo $me['first_name'].' '.$me['last_name']?> </span>
                                            <p class="caption-helper">   Posts (<?php echo count($posts);?> )</p>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <a href="<?php echo route('admin.signups.detail', $me['id']);?>" class="btn gradient-btn"> &lt Back </a>
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <table class="table table-hover" width="100%" id="tb_posts">
                                            <thead>
                                            <tr class="">
                                                <th >Post Type</th>
                                                <th> Media Type </th>
                                                <th>Subject</th>
                                                <th>Likes</th>
                                                <th>Comments</th>
                                                <th>Action</th>
                                                <th>Reported</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php for($i = 0 ; $i < count($posts); $i++):?>
                                                <tr class="font-grey-gallery">
                                                    <td> 
                                                        <?php if($posts[$i]['post_type'] == 1):?>
                                                            Advice
                                                        <?php else:?>
                                                            Sales
                                                        <?php endif;?>
                                                        
                                                    </td>
                                                    <td> 
                                                        <p class="label label-atb">
                                                        <?php if($posts[$i]['media_type'] == 0):?>
                                                            <i class="far fa-copy"></i> Text Post
                                                        <?php elseif($posts[$i]['media_type'] == 1):?>
                                                            <i class="far fa-images"></i> Image Post
                                                        <?php else:?>
                                                            <i class="fas fa-video"></i> Video Post
                                                        <?php endif;?>
                                                        </p>
                                                    </td>
                                                    <td> <?php echo $posts[$i]['title'];?> </td>
                                                    <td> <i class="fas fa-heart atb-font-blue"></i> <?php echo $posts[$i]['likes'];?> </td>
                                                    <td> <i class="fas fa-comment atb-font-blue"></i> <?php echo $posts[$i]['comments'];?>  </td>
                                                    <td> 
                                                        <a href="<?php echo route('admin.signups.view_post', $posts[$i]['id']);?>" class="btn btn-icon-only atb-font-blue"> <i class="fas fa-eye"> </i></a>
                                                        <a href="<?php echo route('admin.signups.post_block_form', $posts[$i]['id']);?>" class="btn btn-icon-only atb-font-red"> <i class="fa fa-trash"> </i></a>
                                                    </td>
                                                    <td>
                                                        <?php if($posts[$i]['is_active'] == 0):?>
                                                            <a href="javascript:;" class="btn btn-circle btn-icon-only yellow-casablanca"> <i class="fa fa-info"> </i></a> 
                                                        <?php endif;?>
                                                    </td>
                                                </tr>
                                            <?php endfor;?>
                                            </tbody>
                                        </table>
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
