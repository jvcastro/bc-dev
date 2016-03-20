<?php
session_start();
ini_set("upload_max_filesize","1000k");
ini_set("display_errors",'Off');
date_default_timezone_set("Australia/Sydney");
include "../dbconnect.php";
include "../classes/classes.php";
include "../classes/mailer.php";
include "../classes/crud.php";
$act = $_REQUEST['act'];
$mess = $_REQUEST['message'];
if ($_REQUEST['act'] == 'upload')
{
        $size = $_FILES['file']['size'];
        if ($size > 1024000)
        {
            echo "toobig";
            exit;
        }
        $target_path = "../logo/"; 
	$myfile1 = basename( $_FILES['file']['name']);
        $myfile = substr(md5($myfile1 . time()),1,6);
	$target_path = $target_path . $myfile; 
	if (strlen($myfile) > 1)
	{
	if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) 
		{
                    echo "$myfile";
		} 	
	else{
    	echo "error";
		}
	}
	exit;
	}
if ($_REQUEST['act'] == 'updatepartner')
{
    $cpid = $_REQUEST['cpid'];
    $partners = new crud("bc_partners");
    $partners->update($_POST,"cpid = '$cpid'");
    exit;
}
if ($_REQUEST['act'] == 'deletepartner')
{
    $cpid = $_REQUEST['cpid'];
    $partners = new crud("bc_partners");
    $partners->delete("cpid = '$cpid'");
    exit;
}
if ($_REQUEST['act'] == 'addnewpartner')
{
    extract($_POST);
    $partners = new crud("bc_partners");
    $partners->create($_POST);
    exit;
}
if ($_REQUEST['act'] == 'updateadmin')
{
    extract($_POST);
    mysql_query("update members set userlogin = '$email',email='$email', roleid ='$roleid' where userid = '".$_REQUEST['userid']."'");
    mysql_query("update memberdetails set afirst = '$firstname',alast = '$lastname' where userid = '".$_REQUEST['userid']."'");
    exit;
}
if ($_REQUEST['act'] == 'resetpassword')
{
     $res = mysql_query("SELECT * from members where userid = '".$_REQUEST['userid']."'");
     $row = mysql_fetch_assoc($res);
     $email = $row['email'];
     if (strpos($email,'@') === false)
     {
         if ($row['roleid'] == '2')
         {
             $cres = mysql_query("SELECT * from bc_clients where bcid = ".$row['bcid']);
             $crow = mysql_fetch_assoc($cres);
             $email = $crow['email'];
         }
         else {echo "noemail";exit;}
     }
     $ss = time().$_REQUEST['userid'];
     $userp = substr(md5($ss),1,10);
     mysql_query("update members set userpass = '$userp' where userid = '".$_REQUEST['userid']."'");
     echo Mailer::emailpass($email,$userp);
     exit;
}
if ($_REQUEST['act'] == 'addnewadmin')
{
    extract($_POST);
    $nbcid = $_REQUEST['bcid'];
    $ss = time().$email;
    $userp = substr(md5($ss),1,10);
    Mailer::emailpass($email,$userp);
mysql_query("insert into members set userlogin = '$email', userpass='$userp',roleid = '$roleid', usertype='user', email='$email',bcid ='$nbcid'");
    $newid = mysql_insert_id();
    mysql_query("insert into memberdetails set userid='$newid',afirst ='$firstname',alast='$lastname'");
    exit;
}
if ($_REQUEST['act'] == 'updateclient')
{
    $f = $_REQUEST['field'];
    $v = $_REQUEST['value'];
    $bcid = $_REQUEST['bcid'];
    if ($f == 'email')
    {
        mysql_query("update members set userlogin = '$v',email= '$v' where bcid = $bcid and usertype = 'bcclient'");
    }
    mysql_query("update bc_clients set $f = '$v' where bcid = '$bcid'");
    exit;
}
if ($_REQUEST['act'] == 'saveclient')
	{
		extract($_REQUEST);
		mysql_query("UPDATE bc_clients set company = '$ccompany', email= '$cemail',phone = '$cphone', cpid = '$cpid', rateid='$rateid', ratetype= '$ratetype', status = 'Active' where bcid = '$bc'");
		//$nbcid = mysql_insert_id();
		$userlog = $_REQUEST['userlog'];
		$userp = $_REQUEST['userp'];
		mysql_query("UPDATE members set userlogin = '$userlog', userpass = '$userp' where usertype = 'bcclient' and bcid = '$bc'");
	}
include "phpfunctions.php";
$bclist = getbclist();
foreach ($bclist as $bcclient)
	{
		$bcdropdown .= '<option value="'.$bcclient['bcid'].'">'.$bcclient['company'].'</option>';
	}
$rates = getdatatable("bc_rates","rateid");
if ($_SESSION['super'] != '1')
	{
		header("Location: ../login/");
	}
if (isset($_REQUEST['bulkdisable']))
{
    $bcids = $_REQUEST['bcids'];
    foreach ($bcids as $dib)
    {
        mysql_query("UPDATE bc_clients set status = 'Inactive' where bcid = '$dib'");
    }
    exit;
}
if (isset($_REQUEST['userbulkdisable']))
{
    $bcids = $_REQUEST['bcids'];
    foreach ($bcids as $dib)
    {
        mysql_query("UPDATE members set active = 0 where userid = '$dib'");
    }
    exit;
}
if (isset($_REQUEST['userbulkenable']))
{
    $bcids = $_REQUEST['bcids'];
    foreach ($bcids as $dib)
    {
       mysql_query("UPDATE members set active = 1 where userid = '$dib'");
    }
    exit;
}
if (isset($_REQUEST['extbulkdelete']))
{
    $bcids = $_REQUEST['bcids'];
    foreach ($bcids as $dib)
    {
       mysql_query("delete from bc_phones where name = '$dib'");
    }
    exit;
}
if (isset($_REQUEST['bulkenable']))
{
    $bcids = $_REQUEST['bcids'];
    foreach ($bcids as $dib)
    {
        mysql_query("UPDATE bc_clients set status = 'Active' where bcid = '$dib'");
    }
    exit;
}
if (isset($_REQUEST['disable']))
	{
		$dib = $_REQUEST['disable'];
		mysql_query("UPDATE bc_clients set status = 'Inactive' where bcid = '$dib'");
		header("location: super.php");
	}
if (isset($_REQUEST['enable']))
	{
		$dib = $_REQUEST['enable'];
		mysql_query("UPDATE bc_clients set status = 'Active' where bcid = '$dib'");
		header("location: super.php");
	}
require_once("superswitch.php");
require_once("superwidgets.php");
$partners = getdatatable("bc_partners","cpid");
$roles = getroles('all');
foreach ($partners as $partner)
{
	$partnerdrop .= '<option value="'.$partner['partner_code'].'">'.$partner['partner_name'].'</option>';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script type="text/javascript" src="../jquery/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<script type="text/javascript" src="../jquery/js/tablesorter.js"></script>
<script type="text/javascript" src="../jquery/js/blockui.js"></script>
<script type="text/javascript" src="../jquery/js/pleasewait.js"></script>
<script type="text/javascript" src="../jquery/datatable/js/jquery.dataTables.min.js"></script>
<link href="../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<link href="../jquery/datatable/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="datahandler.js"></script>
<link href="cstyle.css" rel="stylesheet" type="text/css" />
<link href="styles/style.css" rel="stylesheet" />
<style>
#container {
	margin:0 auto;
	width:100%
}
#menuleft {
	float:left;
	width:15%
}
#maincontent {
	float:left;
	width:84%;
}
.diag {
	display:none;
	width:200px;
	height:200px;
}
th.heading {
	background-color:#DBDBDB
}
td.heading {
	font-weight:bold;
	background-color:#DBDBDB
}
td {	
}
td.left {
	text-align:left;	
}
td.center-title {
border-bottom:1px solid #B3B3B3;
border-left:1px solid #B3B3B3;
color:#666666;
font-weight:bold;
line-height:12pt;
padding:2px;
text-align:center;
}
td.datas {
border-bottom:1px solid #B3B3B3;
border-left:1px solid #B3B3B3;
color:#666666;
padding:2px;
text-align:center;
}
td.dataleft {
border-bottom:1px solid #B3B3B3;
border-left:1px solid #B3B3B3;
color:#666666;
padding:2px;
text-align:left;
}
[type=text]
{
	border:1px #16A6BD solid;
	width:200px;
}
select {
	width: 200px;
}
#reporttitle {
	position:relative;
	color:#16A6BD;
	font-size:1.5em;
}
#newclient {
	display:none;
	width:400px;
	height:400px;
}
#formloader {
	display:none;
	width:800px;
	height:600px;
}
.dataTables_filter {
}
#tabs .btab {
    float:left;
    border:#008080 solid 1px;
    padding: 10px;
    width: 80px;
}
.active {
    float:left;
     border-top:#000 solid 1px;
     border-right:#000 solid 1px;
     border-left:#000 solid 1px;
    border-bottom:#FFF solid 1px;
    background-color: #FFF;
    padding: 5px;
    width: 100px;
    z-index: 1;
    position:relative;
}
.inactive {
    float:left;
    border-top:#008080 solid 1px;
     border-right:#008080 solid 1px;
     border-left:#008080 solid 1px;
    border-bottom:#008080 solid 1px;
    background-color: #008080;
    padding: 5px;
    width: 100px;
    cursor:pointer;
}
#tabs #boundary {
    width: 100%;
    float:left;
    border: #008080 solid 1px;
    position:relative;
    top: -2px;
    z-index: 0;
}
.apptitle {
    color: #103156;
    font-style: italic;
    font-weight: bold;
    width: 100%;
}
.tableheader {
    text-align: left;
}
#logoeditor {
     width:200px; 
     cursor: pointer
}
#logoeditor:hover {
    box-shadow: 5px 5px 50px 10px #65a9d7;
}
#ratetable td input {
    width:100px;
}
#ratetable td select {
    width:70px;
}
h3.stitle {
    text-align: right;
}
</style>
</head>
<body>
<iframe id='manifest_iframe_hack' style='display: none;' src='bcsuperadmin.appcache.html'></iframe>
<div id="container">
<img src="images/bclogo-small.png" />
<div id="reporttitle">BlueCloud Management</div>
<hr />
<div id="menuleft">
<table width="100%">
<tr><td class="left">Admin Extension:</td>
</tr><tr>    <td><a href="#" onclick="setadminext()" id="adminext">
        <?php echo $_SESSION['adminext'] > 0 ? $_SESSION['adminext']:"Click here to set";?>
        </a>
    </td></tr>
<tr><td class="heading">Administration</td></tr>
<tr><td class="left" style="text-align:left;" id="creation"><a href="super.php">Clients</a></td></tr>
<tr><td class="left" style="text-align:left;" ><a href="super.php?act=extensions">Extensions</a></td></tr>
<tr><td class="left" style="text-align:left;" ><a href="#" onclick="addext()">Add Extension</a></td></tr>
<tr><td class="left" style="text-align:left;" ><a href="super.php?act=providers">Voice Providers</a></td></tr>
<tr><td class="left" style="text-align:left;" ><a href="#" onclick="addprov()">Add Voice Account</a></td></tr>
<tr><td class="left" style="text-align:left;" ><a href="#" onclick="partners()">Account Managers</a></td></tr>
<tr><td class="left" style="text-align:left;" ><a href="#" onclick="dostatic()">Static Pages</a></td></tr>
<tr><td class="left" style="text-align:left;" ><a href="#" onclick="dohelp()">Help Tools</a></td></tr>
<tr><td class="heading">Billing</td></tr>
<tr><td class="left"><a href="#" onClick="gettransactions()">Payment Transactions</a></td></tr>
<tr><td class="left"><a href="#" onClick="loadform('AddPayments')">Add Payments</a></td></tr>
<tr><td class="left"><a href="#" onClick="rates()">Rates</a></td></tr>
<tr><td class="left"><a href="super.php?act=usage">Usage</a></td></tr>
<tr><td class="left"><a href="super.php?act=callcosts">Call Costs</a></td></tr>
<tr><td class="heading"><a href="index.php">Exit</a></td></tr>
</table>
</div>
<div id="maincontent">
<div id="vismain">
<div class="message" style="color:#FF0000; font-weight:bold"><?=$mess;?></div>
<?php
//adminlist(0);?>
<?php
if (!isset($_REQUEST['act']))
{
echo "<h3><i>Clients</i></h3>";
if (isset($_REQUEST['inactive'])) {
    $bclist = getbclist('Inactive');
    $toginactive = true;
}
else {
    $bclist = getbclist('Active');
    $toginactive = false;
}
$ures = mysql_query("SELECT bcid,count(bcid) as counts from members where usertype = 'user' group by bcid");
		while ($urow = mysql_fetch_assoc($ures))
			{
				$usercount[$urow['bcid']] = $urow['counts'];
			}
		$vres = mysql_query("SELECT * from members where usertype = 'user' and roleid ='2'");
		while ($vrow = mysql_fetch_assoc($vres))
			{
				$vip[$vrow['bcid']] = $vrow;
			}
		$month = date("Y-m-");
		$headers[] = "";
		$headers[] = array("text"=>'Client Name',"options"=>'style="width:100px"');
		$headers[] = 'VIP User';
		$headers[] = 'Login Link';
		$headers[] = 'Account Type';
		$headers[] = 'Users Created';
		$headers[] = 'Hours this Month';
                $headers[] = 'Status';
		$headers[] = 'Action';
                $headers[] = 'contacts';
		foreach ($bclist as $client)
			{
				$use = getbcusage($client['bcid'],$month."01",$month."32");
	$rows[$client['bcid']]['1'] = '<input type="checkbox" name="bulkaction" value="'.$client['bcid'].'" />';
                                $rows[$client['bcid']]['2'] = '<span style="width:150px; display:block"><a href="#" onclick="editclient(\''.$client['bcid'].'\')">'.$client['company'].'</a></span>';
				$rows[$client['bcid']]['3'] = '<span title="'.$vip[$client['bcid']]['userlogin'].'" style="display:block;width:80px">'.substr($vip[$client['bcid']]['userlogin'],0,12).'</span>';
				$rows[$client['bcid']]['4'] = '<span title="impersonate"><a href="index.php?act=impersonate&impid='.$vip[$client['bcid']]['userid'].'">Login</a></span>';
				$rows[$client['bcid']]['5'] = ucfirst($client['ratetype']);
				$rows[$client['bcid']]['6'] = $usercount[$client['bcid']];
				$rows[$client['bcid']]['7'] = $use['usagehours'];
                                $rows[$client['bcid']]['8'] = $client['status'];
				if ($client['status'] == 'Active') $rows[$client['bcid']]['9'] = '<a href="super.php?disable='.$client['bcid'].'">Deactivate</a>';
				else  $rows[$client['bcid']]['9'] = '<a href="super.php?enable='.$client['bcid'].'">Activate</a>';
                                $rows[$client['bcid']]['10'] = $vip[$client['bcid']]['userlogin'];
			}
		$disp .= tablegen($headers,$rows,"100%",NULL,"clientstable");
echo '<form name="clientsearchform" id="csform"><input type="text" id="cssearch" placeholder="Search..." /></form><br />';
echo '<a href="#" onClick="newclient()" class="jbut" style="float:right">New Client</a>';
echo '<a href="#" onclick="clientlettersearch(\'All\')" class="navlet">All</a>';
foreach (alphabet::all() as $letter)
{
    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
    echo '<a href="#" onclick="clientlettersearch(\''.$letter.'\')" class="navlet">'.$letter.'</a>';
    $ct++;
}
}
echo $disp;
?>
</div>
</div>
</div>
<div id="formloader">
</div>
</body>
<?
require_once("superscripts.php");
?>