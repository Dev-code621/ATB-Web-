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
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->

<!-- BEGIN PAGE LEVEL PLUGINS --->
<?php
    foreach ($before_app_js as $item) {
        echo ('<script src="'.$item.'" type="text/javascript"></script>');
    }
?>
<!-- END PAGE LEVEL PLUGINS --->

<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="<?php echo base_url();?>admin_assets/global/scripts/app.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->

<?php
foreach ($after_app_js as $item) {
    echo ('<script src="'.$item.'" type="text/javascript"></script>');
}
?>

<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="<?php echo base_url();?>admin_assets/layouts/layout/scripts/layout.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/layouts/layout/scripts/demo.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/layouts/global/scripts/quick-sidebar.min.js" type="text/javascript"></script>
<script src="<?php echo base_url();?>admin_assets/layouts/global/scripts/quick-nav.min.js" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->

<script type="text/javascript">
   (function(d, m){var s, h;       
   s = document.createElement("script");
   s.type = "text/javascript";
   s.async=true;
   s.src="https://apps.applozic.com/sidebox.app";
   h=document.getElementsByTagName('head')[0];
   h.appendChild(s);
   window.applozic=m;
   m.init=function(t){m._globals=t;}})(document, window.applozic || {});
</script>

<script type="text/javascript">
  window.applozic.init({
    appId: 'emtrac2ba61d90383c69a7fbc7db07725fa3e5b',      //Get your App ID from https://www.applozic.com
    userId: "ADMIN",                     //Logged in user's id, a unique identifier for user
    userName: "ATB Admin",                 //User's display name
    imageLink : '<?php echo base_url();?>admin_assets/logo.png',                     //User's profile picture url
    email : '',                         //optional
    contactNumber: '',                  //optional, pass with internationl code eg: +13109097458
    desktopNotification: true,
    source: '1',                          // optional, WEB(1),DESKTOP_BROWSER(5), MOBILE_BROWSER(6)
    notificationIconLink: 'https://www.applozic.com/favicon.ico',    //Icon to show in desktop notification, replace with your icon
    authenticationTypeId: 1,          //1 for password verification from Applozic server and 0 for access Token verification from your server
    accessToken: '',                    //optional, leave it blank for testing purpose, read this if you want to add additional security by verifying password from your server https://www.applozic.com/docs/configuration.html#access-token-url
    locShare: true,
    googleApiKey: "AIzaSyDKfWHzu9X7Z2hByeW4RRFJrD9SizOzZt4",   // your project google api key 
    mapStaticAPIkey: "AIzaSyCWRScTDtbt8tlXDr6hiceCsU83aS2UuZw",
    googleMapScriptLoaded : false,   // true if your app already loaded google maps script
    autoTypeSearchEnabled : true,     // set to false if you don't want to allow sending message to user who is not in the contact list
    loadOwnContacts : false, //set to true if you want to populate your own contact list (see Step 4 for reference)
    onInit : function(response) {
       if (response === "success") {
          // login successful, perform your actions if any, for example: load contacts, getting unread message count, etc
       } else {
          // error in user login/register (you can hide chat button or refresh page)
       }
   },
   contactDisplayName: function(otherUserId) {
         //return the display name of the user from your application code based on userId.
         return "";
   },
   contactDisplayImage: function(otherUserId) {
         //return the display image url of the user from your application code based on userId.
         return "";
   },
   onTabClicked: function(response) {
         // write your logic to execute task on tab load
         //   object response =  {
         //    tabId : userId or groupId,
         //    isGroup : 'tab is group or not'
         //  }
   }
  });
</script>
