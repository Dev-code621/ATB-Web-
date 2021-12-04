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

                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="full-height-content full-height-content-scrollable">
                            <div class="full-height-content-body">
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <img src="<?php echo base_url();?>admin_assets/sample_avatar.jpg"
                                                 class="img-circle" alt=""
                                                 style="width: 48px; height: 48px; border:4px solid #A6BFDE;"/>
                                            <span class="caption-subject bold">   Charlotte McBlond </span>
                                            <span class="caption-helper">   Bookings (30) </span>
                                        </div>
                                        <div class="actions">
                                            <a href="<?php echo route('admin.signups.detail', 1);?>" class="btn btn-circle blue-hoki"> Back </a>
                                        </div>
                                    </div>
                                    <div class="portlet-body">


                                        <table class="table table-hover" width="100%" id="tb_books">
                                            <thead>
                                            <tr class="font-blue-hoki">
                                                <th >Post</th>
                                                <th >Service</th>
                                                <th >Deposit</th>
                                                <th >Date</th>
                                                <th >View</th>
                                                <th >Refund</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php for($i = 0 ; $i < 50; $i++):?>
                                                <tr class="font-grey-gallery">
                                                    <td> <img src="<?php echo base_url();?>admin_assets/sample_post.png" style="width: 48px; height: 48px; border-radius: 4px;"> </td>
                                                    <td> Sales </td>
                                                    <td> $34 </td>
                                                    <td> 09/06/2017 </td>
                                                    <td> <a href="javascript:;" class="btn btn-circle btn-icon-only blue-hoki"> <i class="fa fa-eye"> </i></a> </td>
                                                    <td> <a href="javascript:;" class="btn btn-circle btn-icon-only green-meadow"> <i class="fa fa-circle-o-notch"> </i></a> </td>
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