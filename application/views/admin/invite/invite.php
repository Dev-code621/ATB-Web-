<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>MyATB App Invite</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="Preview page of Metronic Admin Theme #1 for " name="description" />
    <meta content="" name="author" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url();?>admin_assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url();?>admin_assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url();?>admin_assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url();?>admin_assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url();?>admin_assets/global/css/components-rounded.css" rel="stylesheet" id="style_components" type="text/css" />
    <link href="<?php echo base_url();?>admin_assets/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url();?>admin_assets/layouts/layout/css/custom.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url();?>admin_assets/pages/auth/login.css" rel="stylesheet" type="text/css" />

<!--    <link rel="shortcut icon" href="favicon.ico" /> </head>-->
<!-- END HEAD -->

<body class="login">
<!-- BEGIN LOGO -->
<div class="logo">
    <a href="index.html">
        <img src="<?php echo base_url();?>admin_assets/img/atb_logo_lg.svg" alt="" /> </a>
</div>
<!-- END LOGO -->
<!-- BEGIN LOGIN -->
<div class="content">
	
	<div class="row">
		<div class = "col-sm-12">
		<?php if ($username != "NO_USER") { ?>
			Hello! <b><?php echo $username ?></b> has invited you to join them on the ATB app! Download the app below and enter code <b><?php echo $code ?></b> at sign up.
		<?php } else { ?>
			This is not a valid invite code! But not to worry, you can still download the ATB app below!
		<?php } ?>
		</div>
	</div>
	<div class = "row">
	<div class = "col-sm-12">
		<a href="#">
         <img alt="Download on the app store" src="<?php echo base_url();?>admin_assets/img/app_store.png" width="100%">
      </a>
	  </div>
	</div>
</div>

<!-- END LOGIN -->
<!--[if lt IE 9]>
<script src="<?php echo base_url();?>admin_assets/global/plugins/respond.min.js"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/excanvas.min.js"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/ie8.fix.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="<?php echo base_url();?>admin_assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="<?php echo base_url();?>admin_assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="<?php echo base_url();?>admin_assets/global/scripts/app.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="<?php echo base_url();?>admin_assets/pages/auth/login.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<!-- END THEME LAYOUT SCRIPTS -->
<script>

</script>
</body>

</html>