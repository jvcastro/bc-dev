<?php
$impid = $_REQUEST['impid'];
$doexit = false;
if ($impid == 'exit')
{
    $impid = $_SESSION['impersonator'];
    $doexit = true;
}
else $_SESSION['impersonator'] = $_SESSION['uid'];
$res = mysql_query("SELECT * from members where userid = '$impid'");
$r = mysql_fetch_array($res);
$_SESSION['username'] = $r['userlogin'];
$_SESSION['auth'] = $r['userid'];
$_SESSION['bcid'] = $r['bcid'];
$_SESSION['usertype'] = $r['usertype'];
$_SESSION['uid'] = $r['userid'];
$_SESSION['rights'] = getrights($r['roleid']);
		//OS ticket integration
$_SESSION['email']   =$r['email']; //Email
$_SESSION['timezone'] = $r['timezone'];
if ($doexit)
{
    $_SESSION['impersonator'] = 0;
    header("Location: ./super.php");
}
?>
