<?php
session_start();
ini_set("display_errors","off");
error_reporting(E_ALL);
date_default_timezone_set($_SESSION['timezone']);
include "../dbconnect.php";
require_once '../classes/classes.php';
include "phpfunctions.php";
include "adminsubsystem.php";
if ($_REQUEST['act'] == 'impersonate' && $_SESSION['super'] == '1')
{
   include "impersonate.php";
}
$isadmin = $_SESSION['usertype'];
$features = new features($_SESSION['bcid']);
if ($isadmin != 'user' && !checkrights('admin_portal'))
	{
		header("Location: ../login");
		exit;
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>BlueCloud International</title>
<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="ext/resources/css/xtheme-slate.css"/>
<link type="text/css" rel="stylesheet" media="all" href="../jquery/css/chat/chat.css" />
<link type="text/css" rel="stylesheet" media="all" href="../jquery/css/chat/screen.css" />
<link rel="stylesheet" type="text/css" href="../jquery/datatable/css/jquery.dataTables.css"/>
<link rel="stylesheet" type="text/css" href="../jquery/pageguide/dist/css/pageguide.min.css"/>
<script type="text/javascript" src="ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="ext/ext-core.js"></script>
<script type="text/javascript" src="ext/ext-custom.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<script type="text/javascript" src="../jquery/js/blockui.js"></script>
<script type="text/javascript" src="../jquery/js/chat.js"></script>
<script type="text/javascript" src="../jquery/datatable/js/jquery.dataTables.js?v2"></script>
<script type="text/javascript" src="../jquery/pageguide/dist/js/pageguide.min.js"></script>
<script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="http://assets.zendesk.com/external/zenbox/v2.6/zenbox.js"></script>
<style type="text/css" media="screen, projection">
  @import url(http://assets.zendesk.com/external/zenbox/v2.6/zenbox.css);
</style>
<link href="../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<link href="styles/style.css?v1" rel="stylesheet" />
<?php
echo $bsscripts;
include "scripts.php";
$bclogo = "<img src=\"images/bclogo-small.png\" />";
if ($_SESSION['super'] == '1' && $_SESSION['impersonator'] < 1) $systitle = '<a href="super.php" style="text-decoration:none;">'.$bclogo.'</a>';
else $systitle = $bclogo;
$loggedin = 'Logged In';
$exitimp = '';
if ($_SESSION['impersonator'] > 0)
{
  $loggedin = 'Logged in As';
  $exitimp = '<br><a href="index.php?act=impersonate&impid=exit">Back to SuperAdmin</a>';
}
$exitimp .= '<br />Admin Extension:<a id="adminext" onclick="setadminext()" href="#">';
$exitimp .= $_SESSION['adminext'] ? $_SESSION['adminext']:"Click to Set";
$exitimp .= '</a>';
$exitimp .= '<br><a href="#" onclick="window.Zenbox.show()">Support</a>';                     
?>
</head>
<body onload="indicator(); getinfo();">
<!-- <iframe id='manifest_iframe_hack' style='display: none;' src='bcadmin.appcache.html'></iframe> -->
<div id="container">
        <div id="upperlogo"><?=$systitle;?></div>
        <div style="float:right"><?php echo $loggedin;?>: <a href="#" onclick="profile();"><?php echo $_SESSION['username'];?></a><?php echo $exitimp;?></div>
        <div style="clear:both"></div>
    <div id="navb" align="left"></div>
    <div id="navp" align="left"></div>
    <div id="dialogcontainer" style="display:none; width: auto; height:auto"></div>
    <div id="displayport">
    </div>
    <div id="footer">
            <a href="http://www.bluecloudaustralia.com.au/" style="text-decoration:none">©2013 Blue Cloud Australia.</a>
    </div> 
    <div id="pageguide_content" style="display:none"></div>
</div>
</body>
<script>
</script>
<div id="jqdialog" style="display:none"></div>
<div id="formloader" style="display:none"></div>
<?php
include "../messaging.php";
?>
</html>
<script>
    if (typeof(Zenbox) !== "undefined") {
    Zenbox.init({
      dropboxID:   "20096235",
      url:         "https://bluecloud.zendesk.com"
    });
  }
    </script>