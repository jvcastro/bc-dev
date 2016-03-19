<?php
/**
 * Admin Folder is where all files and functions on the Admin interface are located
 * This handles all ajax requests and makes all database calls.
 * @copyright   Copyright (C) 2010-2011 - BlueCloud Australia 
 * @author      Aubrey Servito <obrifs@gmail.com>
 * @license     Proprietary
 * 
 */
session_start();
$securekey = 'Do not change this whatever happens!';
ini_set("display_errors","off");
error_reporting(E_ALL);
date_default_timezone_set($_SESSION['timezone']);
$isadmin = $_SESSION['usertype'];
if ($isadmin != 'user' && !checkrights('admin_portal'))
	{
		header("Location: ../login");
		exit;
	}
include "../dbconnect.php";
require_once '../classes/classes.php';
include "phpfunctions.php";
$bcid = $_SESSION['bcid'];
$act = $_REQUEST['act'];
require_once 'adminsubsystem.php';
if ($act == 'importdata')
{
    importdata::init();
}
if ($act == 'dialer')
{
    dialer::init();
}
//ADDED BY ABAAM GERMONES - abaamgermones0727@gmail.com
//forclient deactiavete 
if(isset($_POST['deactivate'])){
        if ($_POST['deactivate'] == 'del')
        {
            mysql_query("DELETE from clients where clientid = ".$_POST['id']." and bcid = '$bcid'");
            exit;
        }
        $state = $_POST['deactivate'] == 'yes' ? 0:1;
	$thequery = "Update clients SET active_state = $state WHERE clientid = ".$_POST['id'];
	$resultOO = mysql_query($thequery);
	$return = mysql_affected_rows();
	echo json_encode(array('count' => $return));
	exit();
}
if(isset($_POST['setlistDeleted'])){	
	$thequery = "Update lists SET is_deleted = 1, active = 0 WHERE lid = ".$_POST['id'];
	$resultOO = mysql_query($thequery);
	$return = mysql_affected_rows();
	echo json_encode(array('count' => $return));
	exit();
}
if ($act == 'validate')
{
    $table = $_REQUEST['table'];
    $value = $_REQUEST['value'];
    $validate = new validate();
    echo $validate->$table($value);
    exit;
}
if ($act == 'getstatusbypid')
{
    $pid = $_REQUEST['pid'];
    if ($pid == 'all') {$pid = 0;}
    $ret = '<option value="none">None</option>';
    $res = mysql_query("SELECT * from statuses where projectid in(0,$pid)");
    while ($row = mysql_fetch_assoc($res))
    {
        $ret .= '<option value="'.$row['statusname'].'">'.$row['statusname'].'</option>';
    }
    echo $ret;
}
if ($act == 'updateprovider')
{
    $pid = $_REQUEST['projectid'];
    $provid = $_REQUEST['providerid'];
    $res = mysql_query("SELECT * from bc_providers where bcid = $bcid and id = $provid");
    $ct = mysql_num_rows($res);
    if ($ct == 1 || $provid = 1)
    {
        mysql_query("UPDATE projects set providerid = '$provid' where projectid = $pid");
        
    }
    exit;
}
if ($act == 'sortcf')
{
	/* VALUES SORTED */
	$arr = $_POST['custfield'];
	/* TO BE SORTED */
	$res = mysql_query("SELECT * from projects where projectid = '".$_POST['projectid']."'");
	$row = mysql_fetch_assoc($res);
	$cf = json_decode($row['customfields'], true);
	foreach ($cf as $key => $value)
	{
		$encryptedKey[$key] = $value;
	}
	$orderedArray = array_merge(array_flip($arr), $encryptedKey);
	mysql_query("update projects set customfields = '".  mysql_real_escape_string(json_encode($orderedArray))."' where projectid = '".$_REQUEST['projectid']."'");
    echo json_encode($orderedArray);
    exit;
}

if ($act == 'delcf')
{
    $fn = $_REQUEST['fieldname'];
   $res = mysql_query("SELECT * from projects where projectid = '".$_REQUEST['projectid']."'");
   $row = mysql_fetch_assoc($res);
    $cf = json_decode($row['customfields'], true);
    foreach ($cf as $key=>$value)
    {
        if ($key == $fn)
        {
            unset($cf[$key]);
        }
    }
    mysql_query("update projects set customfields = '".  mysql_real_escape_string(json_encode($cf))."' where projectid = '".$_REQUEST['projectid']."'");
    exit;
   
}
if ($act == 'addcf')
{
    $fl = $_REQUEST['fieldlabel'];
    $fn = $_REQUEST['fieldname'];
    $res = mysql_query("SELECT * from projects where projectid = '".$_REQUEST['projectid']."'");
    $row = mysql_fetch_assoc($res);
    $cf = json_decode($row['customfields'], true);
    
    if ($cf)
    {
       $cf[$fn] = $fl;
    }
    else {
        $cf = array();
        $cf[$fn] = $fl;
    }
mysql_query("update projects set customfields = '".  mysql_real_escape_string(json_encode($cf))."' where projectid = '".$_REQUEST['projectid']."'");
    
    exit;
}
if ($act == 'insertfieldparams')
{
    forms::insertfield($_REQUEST['type'],$_REQUEST['pid']);
    exit;
}
if ($act == 'addlookuptable')
	{
		$projectid = $_REQUEST['projectid'];
		$file = $_FILES['file']['tmp_name'];
		$csv = fopen($file,"r");
		$fields = fgetcsv($csv,1000,",");
		
		$ct = 0;
		while ($data = fgetcsv($csv,1000,","))
			{
				$d = 0;
				foreach ($fields as $field)
					{
						$datas[$ct][$field] = $data[$d];
						$d++;
					}
				$ct++;
			}
		$table = json_encode($datas);
		// var_dump($table);
		$fieldslist = implode(",",$fields);
		mysql_query("INSERT into lookuptable set projectid = '$projectid', jsondata = '$table', rowcount = '$ct', fields = '$fieldslist'");
		echo '<script>parent.lookupfields(\''.$fieldslist.'\');</script>';
		exit;
	}
if ($act == 'incpace')
	{
		$pid = $_REQUEST['pid'];
		$res = mysql_query("SELECT * from projects where projectid = '$pid'");
		$row = mysql_fetch_assoc($res);
		$pace = $row['dialpace'];
		if ($pace < 4)
			{
				$np = intval($pace);
				$np++;
				mysql_query("UPDATE projects set dialpace = '$np' where projectid = '$pid'");
				echo "success";
			}
		else echo "fail";
		exit;	
	}
if ($act == 'decpace')
	{
		$pid = $_REQUEST['pid'];
		$res = mysql_query("SELECT * from projects where projectid = '$pid'");
		$row = mysql_fetch_assoc($res);
		$pace = $row['dialpace'];
		if ($pace > 0)
			{
				$np = intval($pace);
				$np--;
				mysql_query("UPDATE projects set dialpace = '$np' where projectid = '$pid'");
				echo "success";
			}
		else echo "fail";
		exit;	
	}
if ($act == 'popdispo')
	{
		$p = $_REQUEST['pid'];
		$res = mysql_query("SELECT * from statuses where projectid in ('0','$p')");
		$drop .= '<select name="va" id="va" style="font-family: Tahoma; font-size:10px">';
                $drop .= '<option value="NEW">New</option>';
		while ($row = mysql_fetch_assoc($res))
			{
				$drop .= '<option value="'.$row['statusname'].'">'.ucfirst($row['statusname']).'</option>';
			}
		$drop .= '</select>';
		echo $drop;
		exit;
	}
if ($act == 'deletedispo')
	{
		mysql_query("delete from statuses where statusid = '".$_REQUEST['statusid']."'");
		exit;
	}
if ($act == 'enabledispo')
	{
		mysql_query("update statuses set active = 1 where statusid = '".$_REQUEST['statusid']."'");
		exit;
	}
if ($act == 'disabledispo')
	{
		mysql_query("update statuses set active = 0 where statusid = '".$_REQUEST['statusid']."'");
		exit;
	}
if ($act == 'saveprofile')
	{
		$p = $_REQUEST['pass'];
		$e = $_REQUEST['email'];
        $tz = $_REQUEST['timezone'];
        $_SESSION['timezone'] = $tz;
		mysql_query("UPDATE members set userpass= '".mysql_real_escape_string($p)."', email = '".mysql_real_escape_string($e)."', timezone = '".  mysql_real_escape_string($tz)."' where userid = '".$_SESSION['auth']."'");                		
		$_SESSION['email'] = $e;
		$res = mysql_query("SELECT * from members where userid = '".$_SESSION['auth']."'");
		$row = mysql_fetch_assoc($res);
		$_SESSION['support'] = getaticketid($e);
		?>
        <p style="color:#F00">Details Updated!</p>        
        <?
        $act = 'profile';
	}
if ($act == 'profile')
	{
		$res = mysql_query("SELECT * from members where userid = '".$_SESSION['auth']."'");
		$row = mysql_fetch_assoc($res);
                $timezones =DateTimeZone::listIdentifiers();
                $tzlist = '';
                if (strlen($row['timezone']) < 1)
                {
                    $tzlist = '<option></option>';
                }
                foreach ($timezones as $tz)
                {
                    if ($tz == $row['timezone'])
                    {
                        $selc = 'selected="selected"';
                    }
                    else $selc = '';
                    $tzlist .= '<option value="'.$tz.'" '.$selc.'>'.$tz.'</option>';
                }
		?>
        <table width="100%" style="border-right:1px solid #CCC">
        <tr><td colspan="2" class="tableheader">Profile Details</td></tr>
        <tr><td class="datas" style="text-align:right">Userlogin:</td><td class="datas"><?php echo $row['userlogin'];?></td></tr>
        <tr><td class="datas" style="text-align:right">Password</td><td class="datas"><input type="password" id="prof_pass" name="prof_pass" value="<?php echo $row['userpass'];?>" style="width:240px;" /></td></tr>
        <tr><td class="datas" style="text-align:right;width:240px">Email</td><td class="datas"><input type="email" id="prof_email" name="prof_email" value="<?php echo $row['email'];?>" style="width:240px;"/></td></tr>
        <tr><td class="datas" style="text-align:right">TimeZone</td><td class="datas"><select name="prof_timezone" id="prof_timezone"><?php echo $tzlist;?></select></td></tr>
        <tr><td class="datas" colspan="2"><a href="#" onclick="saveprofile()">Save</a></td></tr>
        </table>
        <?
		exit;
	}
if ($act == 'deletefile')
	{
		mysql_query("delete from uploads where fileid = '".$_REQUEST['fileid']."'");
		exit;
	}
if ($act == 'debug')
	{
		error_reporting(E_ALL);
		var_dump($_REQUEST['PHPSESSID']);
	}
if ($act == 'addnewcstat')
	{
		extract($_REQUEST);
		mysql_query("INSERT into crm_statuses set status_type = '$status_type', action= '$status_action', status_name = '".mysql_real_escape_string($status_name)."', clientid = '$status_clientid'");
		exit;
	}
if ($act == 'unichange')
	{
		extract($_REQUEST);
		$q = "update $table set $field = '".mysql_real_escape_string($value)."' where crm_statusid = '$id'";
		mysql_query($q);
		echo $q;
	}
if ($act == 'crmstat')
	{
		extract($_REQUEST);
		if ($act2 == 'delete')
			{
				mysql_query("delete from crm_statuses where crm_statusid = '$statusid'");
			}
		else $act = 'crm_addedit';
	}
if ($act == 'crm_addedit')
	{
		if ($statusid > 0)
			{
				$res = mysql_query("SELECT * from crm_statuses where crm_statusid = '$statusid'");
				$row = mysql_fetch_array($res);
				$statusname = $row['status_name'];
				$statustype = $row['status_type'];
				$statusaction = $row['action'];
			}
		
		
			
	}
if ($act == 'updatelead')
	{
		extract($_GET);
		mysql_query("update leads_".$table." set $field = '".mysql_real_escape_string($value)."' where leadid = '$leadid'");
		exit;
	}
if ($act == 'editlead')
	{
		$lid = $_GET['leadid'];
		$table = $_GET['table'];
		$eres = mysql_query("SELECT * from leads_".$table." where leadid = '$lid'");
		$l = mysql_fetch_array($eres);
		$disp .='<style>td.datas {text-align:left}</style>';
		$disp .= "<table width=\"100%\">";
		$disp .= '<tr><td colspan="4" class="center-title heading">Edit Lead</td></tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">FirstName</td>';
		$disp .= '<td class="datas"><input value="'.$l['cfname'].'" type="text" id="cfname" onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="cfnameind" style="width:16px">&nbsp;</span></td>';
		$disp .= '<td class="datas">LastName</td>';
		$disp .= '<td class="datas"><input type="text" id="clname"  value="'.$l['clname'].'" onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="clnameind" style="width:16px">&nbsp;</span></td>';
		$disp .= '</tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">Name</td>';
		$disp .= '<td class="datas"><input value="'.$l['cname'].'" type="text" id="cname" onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="cnameind" style="width:16px">&nbsp;</span></td>';
		$disp .= '<td class="datas">Email</td>';
		$disp .= '<td class="datas"><input value="'.$l['email'].'" type="text" id="email"  onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="emailind" style="width:16px">&nbsp;</span></td>';
		$disp .= '</tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">Phone</td>';
		$disp .= '<td class="datas"><input value="'.$l['phone'].'" type="text" id="phone" onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="phoneind" style="width:16px">&nbsp;</span></td>';
		$disp .= '<td class="datas">Mobile</td>';
		$disp .= '<td class="datas"><input value="'.$l['mobile'].'" type="text" id="mobile"  onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="mobileind" style="width:16px">&nbsp;</span></td>';
		$disp .= '</tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">Address 1</td>';
		$disp .= '<td class="datas" colspan="2"><input value="'.$l['address1'].'" type="text" id="address1" onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="address1ind" style="width:16px">&nbsp;</span></td>';
		$disp .= '</tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">Address 2</td>';
		$disp .= '<td class="datas" colspan="2"><input value="'.$l['address2'].'" type="text" id="address2" onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="address2ind" style="width:16px">&nbsp;</span></td>';
		$disp .= '</tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">Suburb</td>';
		$disp .= '<td class="datas"><input value="'.$l['city'].'" type="text" id="city" onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="cityind" style="width:16px">&nbsp;</span></td>';
		$disp .= '<td class="datas">State</td>';
		$disp .= '<td class="datas"><input value="'.$l['state'].'" type="text" id="state"  onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="stateind" style="width:16px">&nbsp;</span></td>';
		$disp .= '</tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">Comments </td>';
		$disp .= '<td class="datas" colspan="2"><input value="'.$l['comments'].'" type="text" id="address2" onblur="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><span id="commentsind" style="width:16px">&nbsp;</span></td>';
		$disp .= '</tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">Disposition </td>';
		$disp .= '<td class="datas" colspan="2"><select name="dispo" id="dispo" onchange="updatelead(\''.$lid.'\',this.id, this.value,\''.$table.'\')"><option selected>'.$l['dispo'].'</option>'.getdispooptions($l['projectid']).'</select><span id="dispoind" style="width:16px">&nbsp;</span></td>';
		$disp .= '</tr>';
		$disp .="</table>";
		echo $disp;
		exit;
	}
if ($act == 'searchlead')
	{
		$ss= $_GET['searchstring'];
		$plist = getprojectlist();
		$lisres = mysql_query("SELECT listid from lists where bcid = '$bcid'");
		
		while ($lisrow = mysql_fetch_assoc($lisres))
			{
				$lis[] = "'".$lisrow['listid']."'";
			}
		$inlis = implode(",",$lis);
                $squery = "SELECT * from leads_raw where (cfname like '%".$ss."%' or clname  like '%".$ss."%' or cname  like '%".$ss."%' or phone  like '%".$ss."%') and listid in ($inlis)";
		//echo $squery;
               // exit;
                $res = mysql_query("SELECT * from leads_raw where (cfname like '%".$ss."%' or clname  like '%".$ss."%' or cname  like '%".$ss."%' or phone  like '%".$ss."%') and listid in ($inlis) ");
		while ($row = mysql_fetch_array($res))
			{
				$results[$row['leadid']] = $row;
				$results[$row['leadid']]['callstatus'] = 'raw';
			}
		$res = mysql_query("SELECT * from leads_done where (cfname like '%".$ss."%' or clname  like '%".$ss."%' or cname  like '%".$ss."%' or phone  like '%".$ss."%')  and listid in ($inlis)");
		while ($row = mysql_fetch_array($res))
			{
				$results[$row['leadid']] = $row;
				$results[$row['leadid']]['callstatus'] = 'done';
			}
		$resnum = count($results);
		if ($resnum == 0)
			{
				$disp = 'No leads found.';
			}
		else {
		$disp .= '<table width="100%"><tr><td colspan="5" class="center-title heading">Search Results</td></tr>';
		$disp .= '<tr>';
		$disp .= '<td class="datas">Leadid</td>';
		$disp .= '<td class="datas">Phone</td>';
		$disp .= '<td class="datas">Name</td>';
                
                $disp .= '<td class="datas">List</td>';
		$disp .= '<td class="datas">Campaign</td>';
		$disp .= '<td class="datas">Disposition</td>';
		$disp.= '</tr>';
		
		foreach ($results as $l)
			{
				if ($l['projectid'] > 0)
					{
						$ass = $plist[$l['projectid']]['Projectname'];
					}
				$ass2 = $l['listid'];
				$disp .= '<tr>';
				$disp .= '<td class="datas"><a href="#" onclick="editlead(\''.$l['leadid'].'\',\''.$l['callstatus'].'\')">'.$l['leadid'].'</a></td>';
				$disp .= '<td class="datas">'.$l['phone'].'</td>';
				$disp .= '<td class="datas">'.$l['cname'].' '.$l['cfname'].' '.$l['clname'].'</td>';
                                $disp .= '<td class="datas">'.$ass2.'</td>';
				$disp .= '<td class="datas">'.$ass.'</td>';
				$disp .= '<td class="datas">'.$l['dispo'].'</td>';
				$disp.= '</tr>';
			}
		$disp .='</table>';
		}
		echo $disp;
		exit;
	}
if ($act == 'updatecustom')
	{
		extract($_GET);
		$res =mysql_query("SELECT * from scriptdata where leadid = '$lid'");
		$row = mysql_fetch_array($res);
		$xm = $row['scriptxml'];
		$pattern_start = "<".$label.">";
		$pattern_end = "<";
		$pattern = "((".preg_quote($pattern_start).")(.*?)(\<))";
		$new_xm = preg_replace($pattern,"$1$val$3",$xm);
		
		mysql_query("update scriptdata set scriptxml = '$new_xm' where leadid = '$lid'");
		echo "$val";
		exit;
	}
if ($act == 'addcontact')
	{
		extract($_REQUEST);
		mysql_query("Insert into members set userlogin = '".mysql_real_escape_string($userlogin)."', userpass= '".mysql_real_escape_string($userpass)."', usertype = '$cusermode', bcid = '$bcid'");
		$newuid = mysql_insert_id();
		mysql_query("insert into client_contacts set clientid = $clientid, userid = '$newuid', firstname = '".mysql_real_escape_string($firstname)."', lastname = '".mysql_real_escape_string($lastname)."', phone = '$phone', email = '".mysql_real_escape_string($email)."', bcid = '$bcid'");
		echo "New Contact Added...";
		exit;
	}
if ($act == 'deletecontact')
{
    extract($_REQUEST);
    $res = mysql_query("SELECT * from client_contacts where client_contactid = '$client_contactid'");
    $row = mysql_fetch_assoc($res);
    mysql_query("update client_contacts set active = 0 where client_contactid = '$client_contactid'");
    mysql_query("update members set active = 0 where userid = '".$row['userid']."'");
    exit;
}
if ($act == 'updatecontact')
	{
		extract($_REQUEST);
                $res = mysql_query("SELECT * from client_contacts where client_contactid = '$client_contactid'");
                $row = mysql_fetch_assoc($res);
                
		mysql_query("update members set userlogin = '".mysql_real_escape_string($userlogin)."', userpass= '".mysql_real_escape_string($userpass)."', usertype = '$cusermode' where userid = '".$row['userid']."'");
		
		mysql_query("update client_contacts set firstname = '".mysql_real_escape_string($firstname)."', lastname = '".mysql_real_escape_string($lastname)."', phone = '$phone', email = '".mysql_real_escape_string($email)."', livemonitor = '".mysql_real_escape_string($livemonitor)."' where client_contactid = '".$client_contactid."'");
		echo "Contact Updated...";
		exit;
	}
if ($act == 'newcontact')
	{
		$cid = $_REQUEST['clientid'];
		
		?>
        <div class="entryform" style="width:400px">
        <title>New Client Contact</title>
<div>Login: <input type="text" id="cuserlogin" /></div>
<div>Password: <input type="text" id="cuserpass"  /></div>
<div>FirstName: <input type="text" id="cfirstname"  /></div>
<div>LastName: <input type="text" id="clastname"  /></div>
<div>Phone: <input type="text" id="cphone" /></div>
<div>Email: <input type="text" id="cemail" /></div>
<div>UserCategory: <select id="cusermode" name="cusermode" >
<option value="clientuser">User</option>
<option value="client" selected="selected">Client</option>
</select> </div>
<input type="button" onclick="addcontact('<?=$cid;?>')" value="Add" />
        <?
	}
if ($act == 'editclientcontact')
	{
		$cid = $_REQUEST['client_contactid'];
		$res = mysql_query("SELECT client_contacts.*,members.userlogin, members.userpass, members.usertype from client_contacts left join members on client_contacts.userid = members.userid where client_contactid = '$cid'");
                $cc = mysql_fetch_assoc($res);
		?>
        <div class="entryform" style="width:400px">
        <title>Edit Client Contact</title>
<div>Login: <input type="text" id="cuserlogin" value="<?=$cc['userlogin'];?>" /></div>
<div>Password: <input type="text" id="cuserpass" value="<?=$cc['userpass'];?>"  /></div>
<div>FirstName: <input type="text" id="cfirstname" value="<?=$cc['firstname'];?>"  /></div>
<div>LastName: <input type="text" id="clastname"  value="<?=$cc['lastname'];?>" /></div>
<div>Phone: <input type="text" id="cphone" value="<?=$cc['phone'];?>" /></div>
<div>Email: <input type="text" id="cemail" value="<?=$cc['email'];?>" /></div>
<div>UserCategory: <select id="cusermode" name="cusermode" >
<option value="clientuser" <?php echo $cc['usertype'] == 'clientuser' ? 'selected="selected"':'' ;?> >User</option>
<option value="client" <?php echo $cc['usertype'] == 'client' ? 'selected="selected"':'' ;?> >Client</option>
</select> </div>
<div>LiveMonitor: <select id="clivemonitor" name="clivemonitor" >
<option value="1" <?php echo $cc['livemonitor'] == '1' ? 'selected="selected"':'' ;?> >Yes</option>
<option value="0" <?php echo $cc['livemonitor'] == '0' ? 'selected="selected"':'' ;?> >No</option>
</select> </div>
<input type="button" onclick="updatecontact('<?=$cid;?>','<?=$cc['clientid'];?>')" value="Update" />
        <?
	}
if ($act == 'addschedule')
	{
		extract($_GET);
		if ($ampm == "pm") $hour = $hour + 12;
		if ($ampm == 'mn') $hour = "00";
		if (strlen($hour) < 2)
			{
				$hour = "0".$hour;
			}
		if (strlen($minutes) < 2)
			{
				$minutes = "0".$minutes;
			}
		$stime = $hour.":".$minutes.":00";
		if ($eampm == "pm") $ehour = $ehour + 12;
		if ($eampm == "mn") $ehour = "00";
		if (strlen($ehour) < 2)
			{
				$ehour = "0".$ehour;
			}
		if (strlen($eminutes) < 2)
			{
				$eminutes = "0".$eminutes;
			}
		$etime = $ehour.":".$eminutes.":00";
		$q = "INSERT into schedule set userid = '$agentid', sdate = '$date', stime = '$stime', etime = '$etime', projectid = '$proj'";
		mysql_query($q);
		echo "Schedule for Agent updated...<br>";
		exit;
	}
if ($act == 'addsched')
	{
		$dt = $_REQUEST['date'];
		$pres = mysql_query("SELECT projectid, projectname from projects where active = 1 and bcid = $bcid");
		while ($prow = mysql_fetch_array($pres))
			{
				$projects .= '<option value="'.$prow['projectid'].'">'.$prow['projectname'].'</option>';
			}
		$agentres = mysql_query("SELECT members.userid, memberdetails.afirst, memberdetails.alast from members left join
								memberdetails on members.userid = memberdetails.userid where members.active = 1 and memberdetails.afirst is not null and bcid = '$bcid'");
		while ($row = mysql_fetch_array($agentres))
			{
				$agents .= '<option value="'.$row['userid'].'">'.$row['afirst'].' '.$row['alast'].'</option>';
			}
		$ct = 1;
		while ($ct <= 12)
			{
				$hours .= '<option value="'.$ct.'">'.$ct.'</option>';
				$ct++;
			}
		$ct = 0;
		while ($ct <= 59)
			{
				$minutes .= '<option value="'.$ct.'">'.$ct.'</option>';
				$ct = $ct + 15;
			}
		$sst = 'style="font-family: Tahoma; font-size: 8pt;"';
		$ampm = '<option value="am">AM</option><option value="pm">PM</option><option value="nn">NN</option><option value="mn">MN</option>';
		$disp .= "Select Agent: <select $sst name=agentid id=agentid>$agents</select><br>";
		$disp .= "<br>CampaignName: <select $sst name=projectid id=projectid>$projects</select><br>";
		$disp .= "<br>Log-in Time: &nbsp;&nbsp;&nbsp;<select $sst name=hour id=hour>$hours</select><select $sst name=minutes id=minutes>$minutes</select><select $sst name=ampm id=ampm>$ampm</select><br>";
		$disp .= "<br>Log-out Time: <select $sst name=ehour id=ehour>$hours</select><select $sst name=eminutes id=eminutes>$minutes</select><select $sst name=eampm id=eampm>$ampm</select><br>";
		$disp .= "<br><button $sst onclick=\"addschedule('$dt')\">Add</button>";
		echo $disp;
		exit;
	}
if ($act == 'getdaysched')
	{
		$dt = $_REQUEST['date'];
		$dtres = mysql_query("SELECT * from schedule where sdate = '$dt'");
		$dtnum = mysql_num_rows($dtres);
		if ($dtnum == 0)
			{
				echo "No Agents for this date.";
			}
		else {
			echo "<table style=\"width:780px\">";
			echo "<tr>";
			echo '<td class=center-title>Agent</td>';
			echo '<td class=center-title>Log-in Time</td>';
			echo '<td class=center-title>Log-out Time</td>';
			echo '<td class=center-title>Assignment</td>';
			echo '<td class=center-title>Confirmed</td>';
			echo "</tr>";
			$agres = mysql_query("SELECT members.userid, memberdetails.afirst, memberdetails.alast from members left join memberdetails on members.userid = memberdetails.userid");
			while ($agrow = mysql_fetch_array($agres))
				{
					$agents[$agrow['userid']] = $agrow['afirst']." ".$agrow['alast'];
				}
			$pjres = mysql_query("SELECT projectid, projectname from projects");
			while ($pjrow = mysql_fetch_array($pjres))
				{
					$projects[$pjrow['projectid']] = $pjrow['projectname'];
				}
			while ($row = mysql_fetch_array($dtres))
				{
					$tm = split(":",$row['stime']);
					$hr = $tm[0];
					$min = $tm[1];
					if ($hr > 12) 
						{
							$hour = $hr - 12;
							$ap = "PM";
						}
					if ($hr == 12)
						{
							$hour = $hr;
							$ap = "NN";
						}
					if ($hr < 12)
						{
							$hour = $hr;
							$ap = "AM";
						}
					$tm = split(":",$row['etime']);
					$hr = $tm[0];
					$emin = $tm[1];
					if ($hr > 12) 
						{
							$ehour = $hr - 12;
							$eap = "PM";
						}
					if ($hr == 12)
						{
							$ehour = $hr;
							$eap = "NN";
						}
					if ($hr < 12)
						{
							$ehour = $hr;
							$eap = "AM";
						}
					if ($row['agentapproved'] == 1) $con = "Yes";
					else $con = "No";
					echo "<tr>";
					echo '<td class=datas>'.$agents[$row['userid']].'</td>';
					echo '<td class=datas>'.$hour.':'.$min.':00 '.$ap.'</td>';
					echo '<td class=datas>'.$ehour.':'.$emin.':00 '.$eap.'</td>';
					echo '<td class=datas>'.$projects[$row['projectid']].'</td>';
					echo '<td class=datas>'.$con.'</td>';
					echo "</tr>";
			
				}
			echo "</table>";
		}
	exit;
	}
if ($act == 'getchecklist')	
	{
		$rtype = $_REQUEST['type'];
		if ($rtype == 'datadisp' || $rtype == 'calldisp')
			{
				switch ($rtype)
				{
					case 'datadisp': $title = 'Data Dispositions'; break;
					case 'calldisp': $title = 'Call Dispositions'; break;
					case 'ldr': $title = 'Lead Details'; break;
					case 'cpr': $title = 'Campaign Performance'; break;
					case 'apr': $title = 'Agent Performance'; break;
				}
				$res = mysql_query("SELECT * from statuses");
				while ($row = mysql_fetch_array($res))
					{
						$disp .= '
				<input type="checkbox" onclick="cfchanges(this)">'.$row['statusname'].'<br />
				';
					}
				echo '<div id="'.$rtype.'_child" style="padding:3 3 3 3">'.$title.'<br>';
				echo '<div style="width:198px; height:150px; overflow:auto;">'.$disp.'</div>';
				echo '</div>';
				exit;
			}
	}
if ($act == 'gettimesheet')
	{
		
		$agentid = $_REQUEST['agentid'];
		$start = $_REQUEST['start'];
		$end = $_REQUEST['end'];
		if ($agentid =='all')
			{
				$tres = mysql_query("SELECT timesheet.*, memberdetails.afirst, memberdetails.alast, members.bcid from timesheet left join memberdetails on timesheet.userid = memberdetails.userid left join members on timesheet.userid = members.userid where date >= '$start' and date <= '$end' and bcid = '$bcid' order by date");
				while ($row = mysql_fetch_array($tres))
					{
						if ($date != $row['date'])
							{
							$date = $row['date'];
							$disp.= '<div><b>Date: '.$date.'</b></div>';
							$disp.= '<div style="color:#FFF">';
							$disp.= '<div class="tableheader" style="float:left; width: 250px">';
							$disp.= 'Agent';
							$disp.= '</div>';
							$disp.= '<div class="tableheader" style="float:left; width: 150px">';
							$disp.= 'Login Time';
							$disp.= '</div>';
							$disp.= '<div class="tableheader" style="float:left; width: 150px">';
							$disp.= 'Logout Time';
							$disp.= '</div>';
							$disp.= '<div class="tableheader" style="float:left; width: 150px">';
							$disp.= 'Time Out';
							$disp.= '</div><div style="clear:both"></div>';
							$disp.= '</div>';
							}
						$disp.= '<div>';
						$disp.= '<div class="tableitem" style="width:250px; float:left;">';
						$disp.= $row['afirst'].' '.$row['alast'];
						$disp.= '</div>';
						$disp.= '<div class="tableitem" style="width: 150px; float:left;">';
						$disp.= date("h:i:s A",$row['firstlogin']);
						$disp.= '</div>';
						$disp.= '<div class="tableitem" style="width: 150px; float:left;">';
						$disp.= date("h:i:s A",$row['lastlogout']);
						$disp.= '</div>';
						$disp.= '<div class="tableitem" style="width: 150px; float:left;">';
						$disp.= number_format($row['totaltimeout'] / 3600,2) . " Hrs";
						$disp.= ' </div><div style="clear:both"></div>';
						$disp.= '</div>';
						$disp.= "<br />";
					}
			echo $disp;	
			}
	}
if ($act == 'refreshleads')
	{
		$projectid = $_REQUEST['projectid'];
		$_rlQ = array();
		$_rlQ[0] = "SELECT lid,listid from lists where projects = '$projectid' and active = 1";                
        $listres = mysql_query($_rlQ[0]);
		$rlist = array();
		
		while ($listrow = mysql_fetch_assoc($listres))
		{
			$rlist[$listrow['listid']] = $listrow['listid'];
            $lids[$listrow['listid']] = $listrow['lid'];
		}

		$_rlQ[1] = "SELECT statusid, statusname from statuses where category = 'callable' and projectid in (0,$projectid)";
		$dispores = mysql_query($_rlQ[1]);

        while($drow = mysql_fetch_assoc($dispores))
        {
            $dispos[$drow['statusid']] = "'".$drow['statusname']."'"; 
        }

        $dispolist = implode(",",$dispos);
        $timenow = time();
        $unlocktime = $timenow - 3600;

        foreach ($rlist as $rl)
        {
            $recycled = 0;
            $_rlQ[2] = "update leads_raw set hopper = 0 where listid = '". $rl ."' and dispo in ('Drop','NEW','ANSMAC') and locked < $unlocktime";
			mysql_query($_rlQ[2]);
			$recycled = mysql_affected_rows();
	                
	        $_rlQ[3] = "update leads_done set hopper = 0 where projectid = $projectid and  listid = '". $rl ."' and dispo in ($dispolist) and locked < $unlocktime";
			mysql_query($_rlQ[3]);
			$recycled = $recycled + mysql_affected_rows();

			$_rlQ[4] = "INSERT INTO lists_history set lid = '".$lids[$rl]."', projectid ='$projectid', date_epoch = '".time()."', total_recycled ='$recycled', userid = '".$_SESSION['uid']."'";
            mysql_query($_rlQ[4]);

        }

        $_rlQ[5] = "delete from hopper where projectid = '$projectid'";
        mysql_query($_rlQ[5]);

        $_rlQ[6] = "SELECT * from last_recycle where projectid = '$projectid'";
        $r = mysql_query($_rlQ[6]);
        
        $c = mysql_num_rows($r);

		if ($c < 1)
		{
			$_rlQ[7] = "INSERT INTO last_recycle set projectid = '$projectid', last_recycle = NOW()";
			mysql_query($_rlQ6);
		}
		else 
		{
			$_rlQ[7] = "UPDATE last_recycle set last_recycle = NOW() where projectid = '$projectid'";
			mysql_query($_rlQ[7]);
		}

        echo $dropped;
        //mysql_query("INSERT into lists_history set lid = '".$lids['']."'");

        $_rlQ[8] = "UPDATE projects_droprate set count_drop = 0, count_ans = 0 where projectid = '$projectid'";
        mysql_query($_rlQ[8]);
        
        debug_query($_rlQ);

		exit;
	}
if ($act == 'updatelist')
	{
		$f = $_REQUEST['field'];
		$l = $_REQUEST['listid'];
		$v = $_REQUEST['val'];
		mysql_query("update lists set $f = '".mysql_real_escape_string($v)."' where lid = '$l'");
		exit;
	}
if ($act == 'javaexport')
	{
		header("Content-type: application/vnd.ms-word");
		header("content-disposition: attachment;filename=report.doc");
		header("Pragma: no-cache");
		header("Expires: 0");
		?>
        <div id=cont></div>
        <script>
		var con = window.opener.getElementById('repdisplay').innerHTML;
		document.getElementById('cont').innerHTML = con;
		</script>
        <?
		exit;
	}
if ($act == 'updateleadpage')
	{
		$pagebody = rawurldecode($_REQUEST['tex']);
		$clientid = $_REQUEST['cid'];
		mysql_query("update leadpage set pagebody = '".mysql_real_escape_string($pagebody)."' where clientid = '$clientid'");
	}
if ($act == 'leadpagegen')
	{
		$clientid = $_REQUEST['cid'];
		$r = mysql_query("SELECT * from leadpage where clientid = '$clientid'");
		$cct = mysql_num_rows($r);
		if ($cct == 0)
			{
			mysql_query("INSERT into leadpage set clientid = '$clientid'");	
			$r = mysql_query("SELECT * from leadpage where clientid = '$clientid'");
			
			}
		$ro = mysql_fetch_array($r);
		$body = $ro['pagebody'];
		$griddata[0]['Agent'] = '';
		echo '<div style="vertical-align:top;"><textarea id=pagebody>'.$body.'</textarea></div>';
		echo '<div style="vertical-align:top">To insert details, enclose field names with "[" and "]"';
		echo '<br> Fields that can be inserted: <br>';
		echo 'Details: Name - [Name], Surname - [Last], Firstname - [First], Title - [Title]<br>';
		echo 'Company - [Company]<br>';
		echo 'Contacts - [Email] [Phone] [Altphone] <br>';
		echo 'Address - [Address1] [Address2] [Suburb] [State] [Postcode]<br>';
		echo 'Outcome: Disposition - [Disposition], Appointment Date - [Appointment], Notes - [Notes]';
		echo 'Others: Leadid - [leadid]';
		echo '</div>';
		exit;
	}
if ($act == 'updateuserdet')
	{
		$type =$_REQUEST['type'];
		$fild = $_REQUEST['fild'];
		$vl = $_REQUEST['val'];
		$id = $_REQUEST['id'];
		if ($type == 'clients')
			{
			mysql_query("update $type set $fild = '".mysql_real_escape_string($vl)."' where clientid='$id'");	
			}
		else mysql_query("update $type set $fild = '".mysql_real_escape_string($vl)."' where userid='$id'");
		exit;
	}
if ($act == 'updatereport')
	{
		$repd = $_REQUEST['repid'];
		$texts = rawurldecode($_REQUEST['tex']);
		$rname = $_REQUEST['rname'];
		mysql_query("update reports set reportname = '".mysql_real_escape_string($rname)."', reportbody = '".mysql_real_escape_string($texts)."',  date = NOW() where reportid = '$repd'");
		//$act = 'getapp';
		//$_REQUEST['app'] = 'creports';
		//$act = 'editscript';
		exit;
	}
if ($act == 'getrep')
	{
		$repid = $_REQUEST['repid'];
		$repres = mysql_query("SELECT * from reports where reportid = '$repid'");
		$row = mysql_fetch_array($repres);
		echo '<form><b>Report Name: </b><input type="text" name=rname id=rname value="'.$row['reportname'].'">';
		echo '<br><b>Last Update:</b> '.$row['date'].'<br>';
		echo '<textarea id=repi name=repi>'.$row['reportbody'].'</textarea></form>';
		exit;
	}
if ($act == 'uprep')
	{
		$reportid = $_REQUEST['repid'];
		$status = $_REQUEST['status'];
		$q = "UPDATE reports set status = '$status' where reportid = '$reportid'";
		mysql_query($q); 
		echo $q;
		exit;
	}
if ($act == 'delrep')
	{
		$reportid = $_REQUEST['repid'];
		$q = "delete from reports where reportid = '$reportid'";
		mysql_query($q); 
		
		exit;
	}
if ($act == 'manreport')
	{
		$cid = $_REQUEST['clientid'];
		$repres = mysql_query("SELECT * from reports where clientid = '$cid'");
		$reps = array();
		while ($row = mysql_fetch_array($repres))
			{
				$reps[] = $row;
			}
		     $headers[] = 'Report Name';
     $headers[] = 'Status';
     $headers[] = 'Actions';
     $headers[] = 'Delete';
		foreach ($reps as $rep)
			{
				if ($rep['status'] == 'hold') 
					{
						$color = "red";
						$status = 'onHold';
						$img = '<img src="icons/application_go.png" title="Release" onclick="togglerelease(\'release\',\''.$rep['reportid'].'\',\''.$cid.'\')">';
					}
				if ($rep['status'] == 'release') 
					{	
						$color = "yellowgreen";
						$status = 'Released';
						$img = '<img src="icons/application_key.png" title="Hold" onclick="togglerelease(\'hold\',\''.$rep['reportid'].'\',\''.$cid.'\')">';
					}

    $rows[$rep['reportid']]['repname'] = '<a href="#" onclick="editrep(\''.$rep['reportid'].'\', \''.$cid.'\')">'.$rep['reportname'].'</a>';
    $rows[$rep['reportid']]['status'] = '<font color="'.$color.'">'.$status.'</font>';
    $rows[$rep['reportid']]['actions'] = '<img src="icons/application_edit.png" onclick="editrep(\''.$rep['reportid'].'\',\''.$cid.'\')" title="Edit" style="text-align:left"> '.$img;
        $rows[$rep['reportid']]['delete'] = '<img src="icons/delete.gif" onclick="deleterep(\''.$rep['reportid'].'\',\''.$cid.'\')" title="Delete"/>';
			}
                        echo tablegen($headers,$rows,'800','','datatabs');
		exit;
	}
if ($act == 'savereport')
	{
		$cid = $_REQUEST['cid'];
		$texts = rawurldecode($_REQUEST['tex']);
		$rname = $_REQUEST['rname'];
		mysql_query("insert into reports set reportname = '".mysql_real_escape_string($rname)."', reportbody = '".mysql_real_escape_string($texts)."', clientid = '$cid', date = NOW()");
		$act = 'getapp';
		$_REQUEST['app'] = 'creports';
		//$act = 'editscript';
		exit;
	}
if ($act == 'getrange')
	{
	$pid = $_REQUEST['pid'];
	
        ?>
                <td>Start<br><br>End</td><td><input type="text" name="startdate" id="startdate" class="datepick"><br><br>
                    <input type="text" name="enddate" id="enddate" class="datepick"></td>
                <?php
	exit;
	}
if ($act == 'getlistoptions')
{
    $pid = $_REQUEST['pid'];
    $res = mysql_query("SELECT * from lists where projects = '$pid' and active = 1");
    $disp = '';
    while ($row = mysql_fetch_array($res))
			{
				$disp.= '<option value="'.$row['lid'].'">';
				$disp.= $row['listid'];
				$disp.= '</option>';
			}
    echo $disp;
    exit;
}
if ($act == 'getlist')
	{
		$pid = $_REQUEST['pid'];
		$res = mysql_query("SELECT * from lists where projects = '$pid'");
		$disp .= '<td>Select List:</td><td><select name=listid id=listid class="val">';
		while ($row = mysql_fetch_array($res))
			{
				$disp.= '<option value="'.$row['lid'].'">';
				$disp.= $row['listid'];
				$disp.= '</option>';
			}
		$disp .= '</select></td></tr>';
		echo $disp;
		exit;
	}
if ($act == 'genreport')
	{
		$req = $_REQUEST['req'];
		$pid = $_REQUEST['pid'];
		$type = $_REQUEST['rtype'];
		$name = $_REQUEST['rname'];
		$start = $_REQUEST['start'];
		$end = $_REQUEST['end'];
		
		$lists = array();
		$projects = array();
		$p = mysql_query("select * from projects");
		while ($r = mysql_fetch_array($p))
			{
				$projects[$r['projectid']] = $r;
			}
		$lres = mysql_query("SELECT * from lists where projects = '$pid'");
		while ($lrow = mysql_fetch_array($lres))
			{
				$lists[$lrow['lid']] = $lrow;
			}
		if ($type == 'ch')
			{
				$statres = mysql_query("SELECT statusname from statuses where statustype = 'sale' or statusname like '%appointment%'");
				while ($statrow =mysql_fetch_array($statres))
					{
						if ($cts > 0) $statuses .= ",";
						$statuses .= "'".$statrow['statusname']."'";
						$cts++;
					}
				$weekres = mysql_query("SELECT * from workhours where projectid = '$pid' and days >= '$start' and days <= '$end'");
				$weeks = array();
				while ($weekrow = mysql_fetch_array($weekres))
					{
						$weeks[$weekrow['hid']] = $weekrow;
					}
				$disp = '<table  style="width:600px"><tr><td colspan=2 class="center-title">Leads vs Hours Report</td></tr>';
				foreach ($weeks as $week)
					{
						$d = $week['days'];
						$sres = mysql_query("SELECT count(*) as leadcount from leads_done where substr(timeofcall,1,10) = '$d' and dispo in ($statuses) and status in ('assigned','approved','verified') and projectid = '$pid';");
						$sr = mysql_fetch_row($sres);
						$agres = mysql_query("SELECT assigned from leads_done where projectid = '$pid' and substr(timeofcall,1,10) = '$d' group by assigned");
						$agcount = mysql_num_rows($agres);
						$hours = $week['worktime'] / 3600;
						$hours = number_format($hours,2);
						$sale = $sr[0];
						$avesale = $sale / $hours;
						$avesale = number_format($avesale,2);
						$aveagent = $avesale / $agcount;
						//$weeks[week['hid']]['leadcount'] = $sr[0];
						$disp .= '<tr><td colspan=2 class="center-title">Report for Date: '.$d.'</td></tr>';
						$disp .= '<tr><td class="datas">Total Hours</td><td class="datas">'.$hours.'</td></tr>';
						$disp .= '<tr><td class="datas">Total Sales or Appointments</td><td class="datas">'.$sale.'</td></tr>';
						$disp .= '<tr><td class="datas">Average Sales per Hour</td><td class="datas">'.$avesale.'</td></tr>';
						$disp .= '<tr><td class="datas">Number of Agents</td><td class="datas">'.$agcount.'</td></tr>';
						$disp .= '<tr><td class="datas">Average Sales per Hour per Agent</td><td class="datas">'.$aveagent.'</td></tr>';
						$disp .= '<tr><td colspan=2></td></tr>';
					}
				$disp .= '</table>';	
			}
		if ($type == 'ld')
			{
			$lid = $_REQUEST['lid'];
			$listid = $lists[$lid]['listid'];
			$dispoquery = "SELECT leads_raw.dispo, count(leads_raw.dispo) as 'count' from leads_raw where leads_raw.dispo != '' and leads_raw.listid = '$listid' group by leads_raw.dispo;";
			$res = mysql_query($dispoquery);
			$disp = '<table  style="width:600px"><tr><td colspan=2 class="center-title">Disposition for '.$listid.'</td>';
			$disp.= '<tr><td colspan=1 class="tableheader">Disposition</td><td colspan=1 class="tableheader">Count</td>';
			while ($row = mysql_fetch_array($res))
				{
					$disp .='<tr>';
					$disp .= '<td class=dataleft>'.$row['dispo'].'</td><td class=dataleft>'.$row['count'].'</td>';
					$disp .='</tr>';
				}
			$disp .= '</table>';
			
			}
		elseif ($type == 'pd')
			{
			$pid = $_REQUEST['pid'];
			$start = $_REQUEST['start'];
			$end = $_REQUEST['end'];
			$dispoquery = "select 
			finalhistory.leadid, 
			finalhistory.start,
			if (if(finalhistory.agentdispo != '',finalhistory.agentdispo,finalhistory.disposition)= '','CANCEL',if(finalhistory.agentdispo != '',finalhistory.agentdispo,finalhistory.disposition)
			) as 'dispo', 
			lists.projects, 
			lists.lid, count(finalhistory.leadid) as 'count', 
			substr(finalhistory.start,1,10) as 'date' 
			from finalhistory 
			left join leads_raw on finalhistory.leadid = leads_raw.leadid 
			left join lists on leads_raw.listid = lists.listid 
			where 
			projectid = '$pid' and substr(finalhistory.start,1,10) >= '$start' and  substr(finalhistory.start,1,10) <= '$end' 
			group by  
			`date`, 
			if (if(finalhistory.agentdispo != '',finalhistory.agentdispo,finalhistory.disposition)= '','CANCEL',if(finalhistory.agentdispo != '',finalhistory.agentdispo,finalhistory.disposition)
			)
			order by `date` ASC;";
			//echo $dispoquery;
			$dt = array();$ct = 0;
			$res = mysql_query($dispoquery);
			$disp = '<table style="width:600px"><tr><td colspan=2 class="center-title">Call Summary for '.$projects[$pid]['projectname'].'</td>';
			
			
			while ($row = mysql_fetch_array($res))
				{
					$dispo = $row['dispo'];
					$dt[$ct] = $row['date'];
					if ($ct == 0)
						{
							$disp .= '<tr><td colspan=2 class="center-title">'.$dt[$ct].'</td>';
							$disp.= '<tr><td colspan=1 class="center-title">Disposition</td><td colspan=1 class="center-title">Count</td>';
						}
					if ($ct != 0 && $dt[$ct] != $dt[$ct-1])
						{
							
							$disp .= '<tr><td colspan=2 class="center-title">'.$dt[$ct].'</td>';
							$disp .= '<tr><td colspan=1 class="center-title">Disposition</td><td colspan=1 class="center-title">Count</td>';
						}
					//if (strlen($dispo) < 2) $dispo = $row['disposition'];
					if ($dispo == 'ANSWER') $dispo = 'Dropped Call';
					if ($dispo == 'CANCEL') $dispo = 'Cancelled';
					if ($dispo == 'CANCEL') $dispo = 'Cancelled';
					$disp .='<tr>';
					$disp .= '<td class=datas>'.$dispo.'</td><td class=datas>'.$row['count'].'</td>';
					$disp .='</tr>';
					$ct++;
				}
			$disp .= '</table>';
			
			}
		elseif ($type == 'ap')
			{
			$pid = $_REQUEST['pid'];
			$start = $_REQUEST['start'];
			$end = $_REQUEST['end'];
			$dstart = $start;
			$dend = $end;
			$strdate = strtotime($dstart);
			while ($strdate <= strtotime($dend))
				{
					$completedates[] = $dstart;
					$dstart = dayadd($dstart);
					$strdate = strtotime($dstart);
				}
			$memres = mysql_query("select members.*, memberdetails.afirst,  memberdetails.alast from members left join memberdetails on members.userid = memberdetails.userid where usertype = 'user';");
			$members = array();
			while ($memrow = mysql_fetch_array($memres))
				{
					$members[$memrow['userid']] = $memrow;
				}
			$stats = array();
			$memstat = array();
			$stres = mysql_query("select leads_done.assigned, leads_done.status, count(leads_done.leadid) as 'count' from leads_done  where  substr(timeofcall,1,10) >= '$start' and substr(timeofcall,1,10) <= '$end' and projectid = '$pid' and dispo in (SELECT statusname from statuses where statustype = 'sale' or statusname like ('%appointment%')) group by assigned, `status`;");
			while ($strow = mysql_fetch_array($stres))
				{
					$memstat[$strow['assigned']] = $strow['assigned']; 
					$stats[$strow['assigned']] .='<tr>';
					if ($strow['status'] == 'assigned') $sss = 'New Lead';
					else $sss = ucfirst($strow['status']);
					$stats[$strow['assigned']] .= '<td class=datas>'.$sss.'</td><td class=datas>'.$strow['count'].'</td>';
					$stats[$strow['assigned']] .='</tr>';
				}
			$perfquery = "select count(callid) as `callcount`, assigned,
							sum(unix_timestamp(`end`) - unix_timestamp(`start`)) as `talktime`,
							sum(unix_timestamp(`end`) - unix_timestamp(`start`))/ count(callid) as `talktimeave` 
						from finalhistory where disposition = 'answer' and assigned != '' and assigned is not null and projectid = '$pid' and `start` between '$start' and '$end'  group by assigned;";
			$perfres = mysql_query($perfquery);
			$perf = array();
			while ($perfrow = mysql_fetch_array($perfres))
				{
					$perf[$perfrow['assigned']] = $perfrow;
				}
			$pausequery = mysql_query("SELECT userid, sum(epochend - epochstart) as 'pausetime' from actionlog where projectid = '$pid' and daydate >= '$start' and daydate <= '$end' group by userid");
			while ($pauserow = mysql_fetch_row($pausequery))
				{
					$perf[$pauserow[0]]['pausetime'] = $pauserow[1];
				}
				
			$dispoquery = "
					SELECT assigned as userid, substr(timeofcall,1,10) as dates, count(dispo) as counts from leads_done 
					where 
					dispo in (SELECT statusname from statuses where statustype = 'sale' or statusname like ('%appointment%')) and 
					substr(timeofcall,1,10) >= '$start' and substr(timeofcall,1,10) <= '$end' and projectid = '$pid'
 					group by userid,dates;";
			//echo $dispoquery;
			$dt = array();$ct = 0;
			$res = mysql_query($dispoquery);
			
			$chistory = array(); $memhistory = array();
			while ($row = mysql_fetch_array($res))
				{
					$dispo = $row['dates'];
					if ($agentid == $row['userid'])
						{
							$rcount[$agentid] = $rcount[$agentid] + 1;
						}
					else {
						$rcount[$row['userid']] = 1;
					}
					$agentid = $row['userid'];
					
					$memhistory[$agentid] = $agentid;
					$chistory[$agentid] .='<tr>';
					$chistory[$agentid] .= '<td >'.$dispo.'</td><td >'.$row['counts'].'</td>';
					$chistory[$agentid] .='</tr>';
					$darr[$agentid][$dispo] = $row['counts'];;

					$ct++;
				}
			$disp = '<table style="width:100%; color:#666666;" border="0">;<tr><td colspan=3 class="center-title reporthead">Agent Performance Report for '.$projects[$pid]['projectname'].'</td>';
			$dquery = mysql_query("SELECT assigned, dispo, count(dispo) as dcount from leads_done where projectid = '$pid' group by assigned, dispo");
			while ($drow = mysql_fetch_array($dquery))
				{
					$dd[$drow['assigned']][$drow['dispo']] = $drow['dcount'];
					$ddispo[$drow['assigned']][] =$drow['dispo']."(%.1f%%)";
					$disporeport[$drow['assigned']] .= '<tr><td>'.$drow['dispo'].'</td><td>'.$drow['dcount'].'</td></tr>';
				}
			foreach ($memhistory as $mem)
				{
				$talktimehour = $perf[$mem]['talktime'] / 3600;
				$talktimehour = number_format($talktimehour,2);
				foreach ($completedates as $days)
					{
						if (strlen($darr[$mem][$days]) < 1) $darr[$mem][$days] = 0;
					}
				$gtitle = 'Performance Graph';
				$datas = implode("|",$dd[$mem]);
				$labels = implode("|",$ddispo[$mem]);
				$tyy = implode("|",$darr[$mem]);
				$txx = implode("|",$completedates);
				$disp .= '<tr><td colspan=2>'.$mem.' '.$members[$mem]['afirst'].' '.$members[$mem]['alast'].'</td></tr>';
				$disp .= '<tr><td colspan=2>Date: from '.$start.' to '.$end.'<br>Total Contacts: '.$perf[$mem]['callcount'].'
				<br>Total Talktime in Hours: '.$talktimehour.'<br>Total Talktime in Sec: '.$perf[$mem]['talktime'].'<br>Average Talk Time: '.$perf[$mem]['talktimeave'].'
				</td></tr>';
				$disp .= '<tr><td valign="top" width="200"><img src="graphgen.php?xarr='.htmlentities($txx).'&yarr='.htmlentities($tyy).'&title='.htmlentities($gtitle).'&height=200"></td><td width="170" valign="top"><table width="170"><tr><td colspan=3 style="text-align:center; font-weight:900">Sales Performance</td></tr>';
				$disp .= $chistory[$mem];
				$disp .= '</table></td>';
				$disp .= '<td valign="top" width="300"><img src="piegen.php?datas='.htmlentities($datas).'&labels='.htmlentities($labels).'" style="margin-top:-20px"></td>';
				$disp .= '<td><table><tr><td colspan="2">Dispositions</td></tr>'.$disporeport[$mem].'</table></td>';
				$disp .= '</tr>';
				$disp .= '<tr><td colspan="3"><div style="width:500px; height:50px; margin:0 auto"><hr></div></td></tr>';
				}
			$disp .= '</table>';
			}
		elseif ($type == 'lead')
			{
				
			}
		if ($req == 'ad')
		{
			$disp;
		}
		elseif ($req == 'export')
			{
				$ppro = str_replace(" ","_",$projects[$pid]['projectname']);
				$filen = $start."-".$end."-".$type."-".$ppro.".doc";
				header("Content-type: application/vnd.ms-word");
				header("content-disposition: attachment;filename=$filen");
				header("Pragma: no-cache");
				header("Expires: 0");
				echo $disp;
				exit;
			}
		else $disp = '<form><textarea id=repi>'.$disp.'</textarea></form>';	
		echo $disp;
		exit;
	}
if ($act == 'reportgen')
	{
		$cid = $_REQUEST['clientid'];
		$projres = mysql_query("SELECT * from projects where clientid = '$cid'");
                $tdiv .= '<table cellspacing="10" cellpadding="5"><tr><td>Report Name:</td><td><input type=text name="repname" id="repname" class=val  style="width:147px"></td></tr>';
		$tdiv .='<tr><td>Report Type:</td><td><select name=rtype id=rtype class="val"  style="width:150px"><option></option><option value="ld">Lists Disposition</option><option value="pd">Campaign Dispositions</option><option value="ap">Agent Performance</option><option value="lead">Leads Details</option></select></td></tr>';
		$tdiv .= "<tr><td>Select Campaign:</td><td><select name=projectid id=projectid class=val onchange=\"docustom()\" style=\"width:150px\"><option></option>";
		while ($prow = mysql_fetch_array($projres))
			{
				$tdiv.='<option value="'.$prow['projectid'].'">';
				$tdiv.=$prow['projectname'];
				$tdiv.='</option>';
			}
		$tdiv .= '</select></td></tr>'; 
		
		$tdiv .= '<tr id="customid"></tr>';
		$tdiv .= '<tr><td><input type="button" value="create" onclick="genreport(\''.$cid.'\')" class="jbut"><td></tr>';
		$tdiv .= "</table>";
		
		echo $tdiv;
		exit;
	}
	
if ($act == 'removeprojectfromteam')
	{
		$np = array();
		$team = $_REQUEST['teamid'];
		$proj = $_REQUEST['project'];
		$tres = mysql_query("SELECT * from teams where teamid = '$team'");
		$trow = mysql_fetch_array($tres);
		$cprojects = explode(";",$trow['projects']);
		if (in_array($proj,$cprojects))
				{
				foreach($cprojects as $cur)
					{
						if ($cur != $proj && $cur) $np[] = $cur;
					}
				$nprojects = implode(";",$np);
				}
		else $nprojects = $trow['projects'];
		mysql_query("update teams set projects = '$nprojects' where teamid = '$team'");
                exit;
		$act = 'getapp';
		$_REQUEST['app']= 'manteams';
	}
if ($act == 'addprojecttoteam')
	{
		$team = $_REQUEST['teamid'];
		$proj = $_REQUEST['project'];
		$tres = mysql_query("SELECT * from teams where teamid = '$team'");
		$trow = mysql_fetch_array($tres);
		$cprojects = explode(";",$trow['projects']);
		if (in_array($proj,$cprojects))
				{
					$nprojects = $trow['projects'];
				}
		else {
			if (count($cprojects) > 0)
				{
					$cprojects[] = $proj;
					$nprojects = implode(";",$cprojects);
				}
			else $nprojects = $proj;
		}
		mysql_query("update teams set projects = '$nprojects' where teamid = '$team'");
		$act = 'getapp';
		$_REQUEST['app']= 'manteams';
	}
if ($act == 'getprojlist')
	{
		$tid = $_REQUEST['teamid'];
		$plist = '<select onchange="addprojecttoteam(\''.$tid.'\')" id="addprojecttoteam"><option></option>';
		$projectres = mysql_query("SELECT projectid, projectname from projects where bcid = '$bcid'");
			while ($projectlist = mysql_fetch_array($projectres))
				{
					$plist .= '<option value="'.$projectlist['projectid'].'">';
					$plist .= $projectlist['projectname'];
					$plist .= "</option>";
					$projdet[$projectlist['projectid']] = $projectlist['projectname']; 
				}
		$plist .= '</select>';
		echo $plist;
		exit;
	}
if ($act == 'export')
	{
		include "export.php";
	}
if ($act == 'deleteproj')
	{
		$pid =$_REQUEST['project'];
		// mysql_query("Delete from projects where projectid = '$pid'");
		mysql_query("UPDATE projects SET active=0 WHERE projectid = '$pid'");
		exit;
	}
	
if ($act == 'removeteam')
	{
	$teamid = $_REQUEST['tid'];
		
	$act = 'getapp';
	//echo "SELECT team, userid from memberdetails where team like '%$teamname%'";
	$tres = mysql_query("SELECT team, userid from memberdetails");
	while ($trow = mysql_fetch_array($tres))
		{
		$team = json_decode($trow['team'],true);
                $newteam = array();
		foreach ($team as $t)
			{
			if ($teamid != $t) $newteam[] = $t;
			}
		$newt = json_encode($newteam);
		//echo "hehehe".$newt;
		mysql_query("Update memberdetails set team = '$newt' where userid = '".$trow['userid']."'");
		}
	mysql_query("delete from teams where teamid = '$teamid'");
        exit;
	}
if ($act == 'remuser')
	{
	$t = $_REQUEST['team'];
	$u = $_REQUEST['user'];
	
	$tres = mysql_query("SELECT team from memberdetails where userid = '$u'");
	$row = mysql_fetch_array($tres);
	$uteams = json_decode($row['team'],true);
        $nt = array();
	foreach ($uteams as $team)
        {
            if ($team != $t)
            {
                $nt[] = $team;
            }
        }
        $updateteam = json_encode($nt); 
        mysql_query("update memberdetails set team = '$updateteam' where userid = '$u'");
        exit;
	}
if ($act == 'updateteamuser')
	{
	$t = $_REQUEST['team'];
	$u = $_REQUEST['user'];
	$tq = "SELECT team from memberdetails where userid = '$u'";
        $tea = mysql_query("SELECT * from teams where bcid = '$bcid'");
        $validteams = array();
        while ($tow = mysql_fetch_assoc($tea))
        {
            $validteams[] = $tow['teamid'];
        }
	$tres = mysql_query($tq);
	$row = mysql_fetch_array($tres);
	$uteams = json_decode($row['team'],true);
	if (!in_array($t,$uteams))
	
		{
		$uteams[] = $t;
		mysql_query("update memberdetails set team = '".json_encode($uteams)."' where userid = '$u'");
		}
	$act = 'showteams';
	}
if ($act == 'showteams')
	{
	$u = $_REQUEST['user'];
	$tres = mysql_query("SELECT team from memberdetails where userid = '$u'");
        $tea = mysql_query("SELECT * from teams where bcid = '$bcid'");
        $validteams = array();
        while ($t = mysql_fetch_assoc($tea))
        {
            $validteams[] = $t['teamid'];
            $teamnames[$t['teamid']] = $t['teamname'];
        }
	$row = mysql_fetch_array($tres);
	$uteams = json_decode($row['team'],true);
	foreach ($uteams as $uteam)
		{
                        if (in_array($uteam,$validteams)) $ateam[$uteam] = $uteam;
		}
	foreach ($ateam as $team)
		{
			if ($ct == 3)
				{
					$ct = 0;
					$d.='<br>';
				}
			if ($ct > 0)
				{
					$d.= "|";
				}
			$d .= ' <a href="#">';
			$d .= $teamnames[$team];
			$d .= '</a> ';
			$ct++;
		}
	echo $d; exit;
	}

if ($act == 'getteamlist')
	{
	$iid = $_REQUEST['iid'];
	$tres = mysql_query("SELECT * from teams where bcid = '$bcid'");
	echo "<select onchange=\"updateteamuser('$iid')\" onblur=\"cancelteamadd('$iid')\" id=\"teamuserform\"><option></option>";
	while ($trow = mysql_fetch_array($tres))
		{
		echo '<option value="'.$trow['teamid'].'">';
		echo $trow['teamname'];
		echo "</option>";
		}
	echo "</select>";
	exit;
	}
if ($act == 'getx')
	{
	$leadid = $_REQUEST['leadid'];
	$xmlq = mysql_query("SELECT scriptxml from scriptdata where leadid = '$leadid'");
	$xmla =mysql_fetch_array($xmlq);
	$xml = $xmla['scriptxml'];
	header('Content-Type: text/xml');
	header("Cache-Control: no-cache, must-revalidate");
	echo "<?xml version='1.0' encoding='ISO-8859-1'?><customdata>";
	echo "$xml";
	echo "</customdata>";
	exit;
	}
if ($act == 'ul')
	{
	$lid = $_REQUEST['lid'];
	$fval = urldecode($_REQUEST['val']);
	$fld = $_REQUEST['fld'];
	if ($fld == 'dtime')
		{
			$r = mysql_query("SELECT * from dateandtime where leadid = '$lid' ");
			$c = mysql_num_rows($r);
			if ($c > 0)
				{
					mysql_query("update dateandtime set $fld = '$fval' where leadid = '$lid'")or die (mysql_error());
				}
			else mysql_query("insert into dateandtime set $fld = '$fval', leadid = '$lid'")or die (mysql_error());
		}
	else mysql_query("update leads_done set $fld = '".mysql_real_escape_string($fval)."' where leadid = '$lid'")or die (mysql_error());
	exit;
	}
if ($act == 'upst')
	{
	$lid = $_REQUEST['lid'];
	$fval = $_REQUEST['vl'];
	$nres = mysql_query("SELECT projectid from leads_done where leadid = '$lid'");
	$nrow = mysql_fetch_row($nres);
	$lead_projectid = $nrow[0];
	$cres = mysql_query("SELECT clientid from projects where projectid = '$lead_projectid'");
	$crow = mysql_fetch_row($cres);
	$lead_clientid = $crow[0];
	if ($fval =='verified') 
		{
		mysql_query("update leads_done set status = '".mysql_real_escape_string($fval)."',actionstatus='clientlock', verdate = NOW() where leadid = '$lid' ")or die (mysql_error());
		mysql_query("INSERT into client_leads set clientid = '$lead_clientid', projectid = '$lead_projectid', leadid = '$lid', isupdated ='1', status = 'newlead', lastactiondate = NOW()");
		}
	mysql_query("update leads_done set status = '".mysql_real_escape_string($fval)."' where leadid = '$lid' ")or die (mysql_error());
	exit;
	}
if ($act == 'deletefilter')
	{
	$filterid = $_POST['filterid'];
	mysql_query("delete from filters where filterid = '$filterid'");
	exit;
	}
if ($act == 'addfilter')
	{
	$field = $_POST['field']; 
	$operand = urldecode($_POST['operand']);
	$val = $_POST['val'];
	$pid = $_POST['pid'];
	if ($operand == 'in' || $operand == 'not in')
		{
			$v = explode(",",$val);
			$ct = 0;
			foreach ($v as $vl)
				{
					$val2[$ct] .= "'".$vl."'";
					$ct++;
				}
			$inval = implode(",",$val2);
			$fdata = $field." ".$operand." (".$inval.")";
		}
	elseif ($operand == 'like') {
		$fdata = $field." ".$operand." '%".$val."%'";
	}
	elseif ($operand == 'not like') {
		$fdata = $field." ".$operand." '%".$val."%'";
	}
	elseif ($operand == 'startlike') {
		$fdata = $field." ". "like" . " '" .$val."%'";
	}
	elseif ($operand == 'not startlike') {
		$fdata = $field." ". "not like" . " '" .$val."'%'";
	}
	elseif ($operand == 'endlike') {
		$fdata = $field." ". "like" . " '%" .$val."'";
	}
	elseif ($operand == 'not endlike') {
		$fdata = $field." ". "not like" . " '%" .$val."'";
	}
	else 
	{
		$fdata = $field." ".$operand." '".$val."'";
	}
	$q = "insert into filters set projectid = '$pid', filterdata = \"".mysql_real_escape_string($fdata)."\"";
	mysql_query($q);
	echo $q;
	exit;
	}
if ($act == 'managecamp')
	{
        
	$projid = $_REQUEST['pid'];
	
	$projres = mysql_query("SELECT * from projects where projectid = '$projid'");
	$projrow = mysql_fetch_array($projres);
        $providers = providers::getall($bcid);
        
        if ($providers) 
        {
            $ctprov = count($providers);
            foreach ($providers as $prov)
            {
                if ($prov['id'] == 1 && $projrow['providerid'] == 1)
                {
                    $providerlist .= '<option value="'.$prov['id'].'" selected="selected">Default</option>';
                }
                if ($prov['id']!= 1)
                {
                if ($prov['id'] == $projrow['providerid']) {
                    $current = ' selected="selected"';
                }
                else $current = '';
                if ($prov['id'] == 1) $prov['name'] = 'Default';
                $providerlist .= '<option value="'.$prov['id'].'"'.$current.'>'.$prov['name'].' - '.$prov['username'].'</option>';
                }
            }
        }
        $cf = json_decode($projrow['customfields'],true);
        if ($cf)
        {
            $cflist = '';
            $dct = 0;
            foreach ($cf as $key=>$value)
            {
                $trclass = ($dct % 2) == 0 ? 'tableitem_':'tableitem';
                $cflist .= '<tr class="'.$trclass.'" id="custfield_'.md5($key).'"><td class="dataleft fieldname">'.$key.'</td><td >'.$value.'</td>
                    <td><a href="#" onclick="delcf(\''.$projid.'\',\''. addcslashes(htmlspecialchars($key),"'").'\')">Remove</a></td></tr>';
                $dct++;
            }
        }
	$tempres = mysql_query("SELECT * from templates where projectid = '$projid'");
	while ($trow = mysql_fetch_array($tempres))
		{
			$templates[$trow['templateid']] = $trow;
		}
             $catres = mysql_query("SELECT * from dispocat");
	while ($catrow = mysql_fetch_array($catres))
		{
		$catlist .= '<option value="'.$catrow['cid'].'">'.$catrow['desc'].'</option>';
		}
	$disres = mysql_query("SELECT * from statuses where projectid in ('$projid',0) order by sort ASC");
	$countdisp = mysql_query("select dispo, count(*) as dispocount from leads_done where projectid = '$projid' group by dispo");
	while ($cdrow = mysql_fetch_assoc($countdisp))
		{
			$udispo[$cdrow['dispo']] = $cdrow['dispocount'];
		}
        $dct = 0;
	while ($disrow = mysql_fetch_array($disres))
		{
                $trclass = ($dct % 2) == 0 ? 'tableitem_':'tableitem';
                if ($udispo[$disrow['statusname']] > 0)
			{
				$del = 'Used';
			}
		else $del = '<a href="#" onclick="deletedispo(\''.$disrow['statusid'].'\',\''.$projid.'\')">Delete</a>';
                $stype = $disrow['statustype'];
                if ($stype == 'transfer')
                {
                    $tlist = lists::findbyLid($disrow['options']);
                    $stype = "transfer to ".$tlist['listid'];
                }
                if ($stype== 'link')
                {
                    $tlist = lists::findbyLid($disrow['options']);
                    $stype = "link to ".$tlist['listid'];
                }
                $dable = $disrow['active'] == '1' ? "<a href=\"#\" onclick=\"disabledispo('".$disrow['statusid']."','$projid')\">Disable</a>" : "<a href=\"#\" onclick=\"enabledispo('".$disrow['statusid']."','$projid')\">Enable</a>";
                $stype .= ' - '.$disrow['category'];
		$dislist[$disrow['projectid']] .= '<tr class="'.$trclass.'" id="custdispo_'.$disrow['statusid'].'"><td class="dataleft" width="30%">'.$disrow['statusname'].'</td><td class="dataleft" >'.$stype.'</td>';
                if ($disrow['projectid'] != 0) 
                    {
                    
                    $dislist[$disrow['projectid']] .= '<td class="center-title">'.$del.' | '.$dable.'</td>';
                    
                    }
                        $dislist[$disrow['projectid']] .= '</tr>';
                        $dct++;
		}
	?>
			<div id="ManageCampaignSettingsMenu">
                            <div id="cprojid" style="display:none"><?=$projrow['projectid'];?></div>
                <div class="apptitle" style="cursor:pointer" onclick="getapp('mancamp')" title="back to Campaigns list">
					Campaign &gt;<br>
					<span style="color: rgb(0, 93, 150);">
						<?=$projrow['projectname'];?>
					</span>
				</div>
                <div class="secnav">
                    <!--<input type="button" onclick="getapp('mancamp')" value="Back" />
                    <input type="button" onclick="newcf('<?=$projid;?>')" value="New Custom Field"/>
                    <input type="button" onclick="newdispo('<?=$projid;?>')" value="New Disposition"/>
                  <input type="button" onclick="emailtemplate('<?=$projid;?>',0)" value="New Email Template" />
                  <input type="button" onclick="editscript('<?=$projid;?>')" value="Call Script" />-->
				 <ul>
                                        <li id="snapshot_button" class="activeMenu" onClick="campaignMenu('snapshot')">
						<a href="#" class="manageCampMenu">Campaign Snapshot</a>
					</li>
					<li id="pdetailsdiv_button" onClick="campaignMenu('pdetailsdiv')">
						<a href="#" class="manageCampMenu">Administration</a>
					</li>
					<li id="tableCampaignDisposition_button" onClick="campaignMenu('tableCampaignDisposition');">
						<a href="#" class="manageCampMenu">Disposition</a>

					</li>
					<li id="tableCustomFields_button" onClick="campaignMenu('tableCustomFields');">
						<a href="#" class="manageCampMenu">Custom Fields</a>
					</li>
					<li id="tableEmail_button" onClick="campaignMenu('tableEmail');">
						<a href="#" class="manageCampMenu">Email</a>
					</li>
					<li id="tableFilters_button" onClick="campaignMenu('tableFilters');">
						<a href="#" class="manageCampMenu">Filters</a>
					</li>
					<li id="scripts_button" onClick="campaignMenu('scripts');">
						<a href="#" class="manageCampMenu">Scripts</a>
					</li>
					<li id="tableUpload_button" onClick="campaignMenu('tableUpload');">
						<a href="#" class="manageCampMenu">Resources</a>
					</li>
					<li id="objectives_button" onClick="campaignMenu('objectives');">
						<a href="#" class="manageCampMenu">Objectives</a>
					</li>
                                        <?php
                                        if ($projrow['dialmode'] == 'inbound')
                                        {
                                        ?>
                                        <li id="voicemail_button" onClick="campaignMenu('voicemail');">
						<a href="#" class="manageCampMenu">Voicemail</a>
					</li>
                                        <?php
                                        }
                                                                               /* <li id="tags_button" onClick="campaignMenu('actiontags');">
						<a href="#" class="manageCampMenu">Action Tags (BETA)</a>
					</li>*/
                                                ?>
				  </ul>
                </div>
			</div>
			
			<div id="campsettingsdisplay" style="float:left;width:82%">
                <div id="pdetailsdiv" class="campsection">
                    <h3>Campaign Details</h3>
					<?php
                                        for ($i = 0;$i < 5;$i++)
                                                            {
                                                                $selected = '';
                                                                if ($i == $projrow['dialpace']) $selected = 'selected="selected"';
                                                                $dpdrop .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                                                            
                                                            }
                                        $dialmodes = array('predictive','progressive','inbound');
                                                            foreach ($dialmodes as $dm)
                                                            {
                                                                $selected = '';
                                                                if ($dm == $projrow['dialmode']) $selected = 'selected="selected"';
                                                                $dmdrop .= '<option value="'.$dm.'" '.$selected.' >'.ucfirst($dm).'</option>';
                                                            }
						$rows[1][1] = 'Campaign Name';
                                                $rows[1][2] = '<input type="text" id="projectname'.$projrow['projectid'].'" onblur="mc_update(\'projectname\',\''.$projrow['projectid'].'\')" value="'.$projrow['projectname'].'" />';
                                                $rows[5][1] = 'Description';
                                                $rows[5][2] = '<textarea cols="30" rows="3" onblur="mc_update(\'projectdesc\',\''.$projrow['projectid'].'\')" id="projectdesc'.$projrow['projectid'].'">'.$projrow['projectdesc'].'</textarea>';
                                                $rows[5]['options'] = ' style="vertical-align:top;"';
                                                $headers[] = 'Option';
                                                $headers[] = 'Value';
                                                echo tablegen($headers,$rows,'100%');
                                                ?>
                    <h3>Dial Settings</h3>
                                                    <?php
                                                $rows = array();
                                                $headers = array();
                                                $rows[2][1] = 'Dial Mode';
                                                $rows[2][2] = '<select id="dialmode'.$projrow['projectid'].'" onchange="mc_update(\'dialmode\',\''.$projrow['projectid'].'\')">'.$dmdrop.'</select>';
                                                $rows[3][1] = 'Dial Pacing';
                                                $rows[3][2] = '<select id="dialpace'.$projrow['projectid'].'" onchange="mc_update(\'dialpace\',\''.$projrow['projectid'].'\')">'.$dpdrop.'</select>';
                                                $rows[4][1] = 'Dial Prefix';
                                                $rows[4][2] = ' <input type="text" value="'.$projrow['prefix'].'" onblur="mc_update(\'prefix\',\''.$projrow['projectid'].'\')" id="prefix'.$projrow['projectid'].'" />';
                                                 
                                                 
                                                
						$callrecoptions = array(
							"forced"=>"Enabled",
							"started"=>"Enabled - Agent Controlled",
							"optional"=>"Disable - Agent Controlled",
							"disabled"=>"Disabled"
						);
						$callreclist = '';
						foreach ($callrecoptions as $key=>$value)
						{
							$issel = '';
							if ($key == $projrow['callrecording']) $issel = "selected";
							$callreclist .= '<option value="'.$key.'" '.$issel.'>'.$value.'</option>';
						}
                                                $rows[6][1] = 'Outbound Provider';
                                                $rows[6][2] = '<select class="sel" name="providerid" id="providerid" onchange="updateprovider(\''.$projrow['projectid'].'\')"><option></option>'.$providerlist.'</select>';
                                                $dropguardsel = '<option value="';
                                                if ($projrow['dropguard'] == 'active')
                                                {
                                              $dropguardsel .= 'active">Active</option><option value="inactive">Inactive</option>';
                                                }
                                                else {
                                              $dropguardsel .= 'inactive">Inactive</option><option value="active">Active</option>';
                                                }
                                                $rows[7][1] = 'Drop Guard (Beta)';
                                                $rows[7][2] = '<select id="dropguard'.$projrow['projectid'].'"  onchange="mc_update(\'dropguard\',\''.$projrow['projectid'].'\')">'.$dropguardsel.'</select>';
                                                 $rows[8][1] = 'Max Drop';
                                                $rows[8][2] = ' <input type="text" value="'.$projrow['maxdrop'].'" onblur="mc_update(\'maxdrop\',\''.$projrow['projectid'].'\')" id="maxdrop'.$projrow['projectid'].'" />%';
                                                 $rows[9][1] = 'Call Recording';
                                                 $rows[9][2] = '<select class=sel id="ucallrecording" onchange="updatecallrecording(\''.$projrow['projectid'].'\')">'.$callreclist.'</select>';
                                                 $amdsel = '<option value="';
                                                 if ($projrow['amd'] == 1)
                                                {
                                              $amdsel .= '1">Active</option><option value="0">Inactive</option>';
                                                }
                                                else {
                                              $amdsel .= '0">Inactive</option><option value="1">Active</option>';
                                                }
                                                 $rows[10][1] = 'Answering Machine Detection';
                                                $rows[10][2] = '<select id="amd'.$projrow['projectid'].'"  onchange="mc_update(\'amd\',\''.$projrow['projectid'].'\')">'.$amdsel.'</select>';
                                                
include("timetracker_management/ttEventsOptShowSelect.php");

                                               $headers[] = 'Option';
                                                $headers[] = 'Value';
                                                echo tablegen($headers,$rows,'100%');
						?><br/>
                                                <?php
                                        if ($projrow['dialmode'] == 'inbound')
                                        {
                    ?>
                                            <h3>Inbound Settings</h3>
                    <?php
                                                $rows = array();
                                                $headers = array();
                                                $headers[] = 'Option';
                                                $headers[] = 'Value';
                                                $rows[2][1] = 'Timeout (waiting for agents';
                                                $rows[2][2] = '<input type="text" id="inboundtimeout'.$projrow['projectid'].'" onblur="mc_update(\'inboundtimeout\',\''.$projrow['projectid'].'\')" value="'.$projrow['inboundtimeout'].'" />';
                                                $rows[3][1] = 'Timeout Destination';
                                                $rows[3][2] = '<input type="text" id="timeoutexten'.$projrow['projectid'].'" onblur="mc_update(\'timeoutexten\',\''.$projrow['projectid'].'\')" value="vm">';
                                                //$rows[4][1] = 'Dial Prefix';
                                                //$rows[4][2] = ' <input type="text" value="'.$projrow['prefix'].'" onblur="mc_update(\'prefix\',\''.$projrow['projectid'].'\')" id="prefix'.$projrow['projectid'].'" />';
                                                 echo tablegen($headers,$rows,'100%');
                                        }
include("agentinterface_management/agentuisettings.php");
include("queuepreview/admin-managecamp-include.php");
                                                ?>
                </div>
                <div id="tableCampaignDisposition"  class="campsection" style="display:none;">
                    <input type="button" onclick="newdispo('<?=$projid;?>')" value="New Custom Disposition"/>
                    <h3>Custom Dispositions</h3>(Drag to re-order)
                    <table id="customdispositions" class="dispositionstable" width="100%" style="width:100%;">
                        <thead>
                            <tr><th class="tableheader" style="width:30%">Disposition</th><th class="tableheader">Details</th><th class="tableheadercenter">Actions</th></tr></thead>
                        <tbody>
                            <?php
                            if (strlen($dislist[$projid]) > 1)
                                    echo $dislist[$projid];
                            else echo "<tr class=\"tableitem_\"><td colspan=4 style=\"padding:10px\"> No Custom Dispositions</td></td>";
                            ?>
                        </tbody>
                    </table>
                    <h3>Default Dispositions</h3>
                    <table class="dispositionstable" width="100%" style="width:100%;">
                            <tr><th class="tableheader" style="width:30%">Disposition</th><th class="tableheader">Details</th></tr>
                            <?php
                            echo $dislist[0];
                            ?>

                    </table>
                </div>
					
                <div id="tableCustomFields"  class="campsection" style="display:none;">
                    <h3>Custom Fields</h3>
                    <input type="button" onclick="newcf('<?=$projid;?>')" value="New Custom Field"/>
                        <table id="customfieldstable" style="width:100%;">
                        	<thead>
	                            <tr>
	                                <th class="tableheader" style="width:30%">Field Name</th>
	                                <th class="tableheader">Label</th>
	                                <th class="tableheader">Action</th>
	                            </tr>
                        	</thead>
                        	<tbody>
                                <?php
                                if (strlen($cflist) > 1)
                                        echo $cflist;
                                else echo "<td colspan=3 style=\"padding:10px\" class=\"tableitem_\"> No Custom Fields</td>";
                                ?>
                        	</tbody>
                        </table>
                </div>
					
                <div id="tableEmail"  class="campsection" style="display:none;">
                    <input type="button" onclick="emailtemplate('<?=$projid;?>',0)" value="New Email Template" />
                    <input type="button" onclick="emailsig('<?=$projid;?>',0)" value="New Signature" />
                        <table style="width:100%;">
                                <tr><h3>Email Templates</h3></tr>

                                <tr><td class="tableheader">Name</td><td class="tableheader" colspan="1" style="">Attachments</td><td class="tableheader">Delete</td></tr>
                                <?
                                $dct = 0;
                                foreach ($templates as $template)
                                {
                                    $trclass = ($dct % 2) == 0 ? 'tableitem_':'tableitem';
                                                if (strlen($template['attachments']) >0)
                                                {
                                        $at = explode(",",$template['attachments']);
                                        $attcount = count($at);
                                                }
                                                else $attcount = 0;

                                ?>
                                <tr class="<?=$trclass;?>">
                                        <td class="dataleft" style="width:30%"><a href="#" onclick="emailtemplate('<?=$projid;?>','<?=$template['templateid'];?>')"><?=$template['template_name'];?></a></td>
                                        <td class="dataleft" colspan="1"><?=$attcount;?></td>
                                  <td class="dataleft">
                                          <a href="#" onclick="canceltemplate('<?=$template['templateid'];?>', '<?=$projid;?>')">Delete</a>
                                  </td>
                                </tr>
                                <?
                                $dct++;
                                }
                                ?>
                        </table>
                    <br>
                                <table style="width:100%;">
                                <tr><h3>Email Signatures</h3></tr>

                                <tr><td class="tableheader" colspan="1" style="">Name</td><td class="tableheader" colspan="1" style="">Date Created</td><td class="tableheader">Delete</td></tr>
                                <?
                                $esigs = getdatatable("signatures where bcid = $bcid", "sigid");
                                $dct = 0;
                                foreach ($esigs as $es)
                                {
                                        $trclass = ($dct % 2) == 0 ? 'tableitem_':'tableitem';
                                ?>
                                <tr class="<?=$trclass;?>">
                                <td class="dataleft" style="width:30%"><a href="#" onclick="emailsig('<?=$projid;?>','<?=$es['sigid'];?>')"><?=$es['signature_name'];?></a></td>
                                        <td class="dataleft" colspan="1"><?=date("Y-m-d",$es['epoch_created']);?></td>
                                  <td class="dataleft">
                                          <a href="#" onclick="cancelsignature('<?=$es['sigid'];?>', '<?=$projid;?>')">Delete</a>
                                  </td>
                                </tr>
                                <?
                                $dct++;
                                }
                                ?>
                        </table>
                </div>
                
                <div id="tableFilters"  class="campsection" style="display:none;">
                    <h3>Field Filters</h3>
                <table style="width:100%;">
                <tr><th class="tableheader">Field</th><th class="tableheader">Filter</th><th class="tableheader">Action</th></tr>
                <?
                $fldres = mysql_query("SELECT cname, cfname, clname, title, company, address1, address2, suburb, city, state, country, zip, phone, altphone, comments, industry, sic, email, dispo from leads_raw limit 1");
                $fldct = mysql_num_fields($fldres);
                $y = 0;
                while ($y < $fldct)
                {
	                $fld = mysql_field_name($fldres, $y);
	                if ($fld == 'zip')
	                {
		                $fldlist .= '<option value="'.$fld.'" onclick="nodrop()">Postcode</option>';
	                }
	                elseif ($fld == 'cname')
	                {
	    	            $fldlist .= '<option value="'.$fld.'" onclick="nodrop()">Name</option>';
	                }
	                elseif ($fld == 'cfname')
	                {
	        	        $fldlist .= '<option value="'.$fld.'" onclick="nodrop()">FirstName</option>';
	                }
	                elseif ($fld == 'clname')
	                {
	            	    $fldlist .= '<option value="'.$fld.'" onclick="nodrop()">SurName</option>';
	                }
	                elseif ($fld == 'dispo')
	                {
	                	$fldlist .= '<option value="'.$fld.'" onclick="popdispo(\''.$projid.'\')">Disposition</option>';
	                }
	                else {
	                	$fldlist .= '<option value="'.$fld.'" onclick="nodrop()">'.ucfirst($fld).'</option>';
	                }
	                $y++;
                }

                $filres = mysql_query("select * from filters where projectid = '$projid'");
                $dct =0;
                while ($filrow = mysql_fetch_array($filres))
				{
                    $trclass = ($dct % 2) == 0 ? 'tableitem_':'tableitem';
                    $fdata = $filrow['filterdata'];
                    $filter = explode(" ",$fdata);
                    switch ($filter[1])
                    {
                		case "like":
                			if (preg_match("/^'%(.*)%'$/", $filter[2]))
                			{
                				$nom = 'contains ';
                			}
                			elseif (preg_match("/(.*)%'$/", $filter[2])) {
                				$nom = 'starts with ';
                			}
                			elseif (preg_match("/^'%(.*)/", $filter[2])) {
                				$nom = 'ends with ';
                			}
                			preg_match("/'.*'/", $fdata, $matches);
                			$filter[2] = $matches[0];
                			$filter[2] = preg_replace('/%/', '', $filter[2]);
                			break;
                        case "=":
                            $nom = 'equal to ';
		        			preg_match("/'.*'/", $fdata, $matches);
		        			$filter[2] = $matches[0];
                            break;
                        case "!=":
                            $nom = 'not equal to ';
                			preg_match("/'.*'/", $fdata, $matches);
                			$filter[2] = $matches[0];
                            break;
                        case ">":
                            $nom = 'greater than ';
                			preg_match("/'.*'/", $fdata, $matches);
                			$filter[2] = $matches[0];
                            break;
                        case "<":
                            $nom = 'less than ';
                			preg_match("/'.*'/", $fdata, $matches);
                			$filter[2] = $matches[0];
                            break;
                        case "in":
                            $nom = 'listed in ';
                			preg_match("/\(.*\)/", $fdata, $matches);
                			$filter[2] = $matches[0];
                            break;
                        case 'not':
                        	switch ($filter[2]) {
                        		case 'in':
	                                $nom = 'not listed in ';
				        			preg_match("/\(.*\)/", $fdata, $matches);
				        			$filter[2] = $matches[0];
                        			break;
                        		case 'like':
		                			if (preg_match("/^'%(.*)%'$/", $filter[3]))
		                			{
		                				$nom = 'not contain ';
		                			}
		                			elseif (preg_match("/(.*)%'$/", $filter[3])) {
		                				$nom = 'not start with ';
		                			}
		                			elseif (preg_match("/^'%(.*)/", $filter[3])) {
		                				$nom = 'not end with ';
		                			}
			            			preg_match("/'.*'/", $fdata, $matches);
			            			$filter[2] = $matches[0];
		                			$filter[2] = preg_replace('/%/', '', $filter[2]);
                        			break;
                        	}
                            break;

                    }

                    echo '<tr class="'.$trclass.'"><td class="dataleft" style="width:30%">'.$filter[0].'</td><td class="dataleft">'.$nom.$filter[2].'</td><td class="dataleft"><div onclick="deletefilter(\''.$filrow['filterid'].'\',\''.$projid.'\')"><img src="icons/delete.gif"></div></td></tr>';
                    $dct++;
				}
                $trclass = ($dct % 2) == 0 ? 'tableitem_':'tableitem';
                $operand = '
                <option value="'.urlencode('like').'">contains</option>
                <option value="'.urlencode('startlike').'">starts with</option>
                <option value="'.urlencode('endlike').'">ends with</option>
                <option value="'.urlencode('<').'">less than</option>
                <option value="'.urlencode('>').'">greater than</option>
                <option value="'.urlencode('in').'" onclick="nodrop()">listed in</option>
                <option value="'.urlencode('=').'">equal to</option>
                <option value="'.urlencode('not like').'">not contain</option>
                <option value="'.urlencode('not startlike').'">not start with</option>
                <option value="'.urlencode('not endlike').'">not end with</option>
                <option value="'.urlencode('!=').'">not equal to</option>
                <option value="'.urlencode('not in').'" onclick="nodrop()">not listed in</option>
                ';
                echo '<tr class="'.$trclass.'"><td class="dataleft"><select name="field" id="field">'.$fldlist.'</select></td><td class="dataleft"><select name="operand" id="operand" >'.$operand.'</select><span id="vaspan"><input type=text name=va id=va></span></td><td class="dataleft"><img src="icons/add.gif" onclick="addfilter(\''.$projid.'\')"></td></tr>';
                ?>
</table>
				</div>
				
				<div id="tableUpload"  class="campsection" style="display:none;">
                                    <input type="button" onclick="resourceupload()" value="Upload File" />
                                    <h3>File Resources</h3>
					<table style="width:100%;">
                                        <tr><td class="tableheader">FileName</td><td class="tableheader">Description</td><td class="tableheader">Upload Date</td><td class="tableheader">Action</td></tr>
                                        <?php
                                        $dct = 0;
                                        $upres = mysql_query("SELECT * from uploads where projectid = '$projid'");
                                        $stime = time();
                                        while ($uprow = mysql_fetch_array($upres))
                                        {
                                            $trclass = ($dct % 2) == 0 ? 'tableitem_':'tableitem';
                                            $securestring = $uprow['fileid'] . "_" . $uprow['projectid'] . "_" . $stime . "_" . $securekey;
                                            $securehash = md5($securestring);
                                            $dlink = "../download.php?h=".$securehash."&f=".$uprow['fileid']."&ts=".$stime;
                                            $uplist .= '<tr class="'.$trclass.'"><td class="dataleft" style="width:30%"><a href="'.$dlink.'" title="'.$uprow['filename'].'" target="_blank">'.substr($uprow['filename'],0,40).'</a></td>
                                            <td class="dataleft">';
                                                    if (strlen($uprow['description']) < 1)
                                                    {
                                                                    $uplist .= "...";
                                                    }
                                                    else $uplist .= substr($uprow['description'],0,51);

                                            $uplist .= '</td>
                                            <td class="dataleft">'.$uprow['uploaddate'].'</td>
                                            <td class="dataleft"><a href="#" onclick="deletefile(\''.$uprow['fileid'].'\',\''.$projid.'\')">Remove</a></td>
                                                    </tr>';
                                            $dct++;
                                        }
                                        echo $uplist;
				?></table>
                                    <div id="resourceupload" style="display:none;">
                                    <form enctype="multipart/form-data" method="POST" action="uploader.php" id="campuploadfile" target="uplo">
						<input type="hidden" name="proj" value="<?=$projid;?>" />
						<input type="hidden" name="act" value="upload" />
						<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>

						<input name="cfile" type="file" style="font-size:10px; height:20px; padding-bottom:8px; position:relative;"  /><input type="button" value="Upload" style="font-size:10px; height:20px; padding-bottom:8px; position:relative; left:25px" onclick="campuploadFile(this.form)"/>
                                                 Description:<br>
                                                 <textarea name="desc" style="width:99%"></textarea>
						 <div id="progress" style="float:right;width:99%;height:20px">
											<div id="pbar" style="height:20px"></div>
										</div>
						</form>
						<iframe name="uplo" width="0" height="0"style="display:none"></iframe>
                                    </div>
				</div>
                        </div>

				 
	<?
	exit;
	}
if ($act == 'addstatus')
	{
        extract($_REQUEST);
        if ($pid > 0)
        {
	
	if ($statustype == 'booking')
		{
			$category = 'final';
		}
	mysql_query("INSERT into statuses set projectid = '$pid', statusname = '".mysql_real_escape_string($statusname)."', statustype = '$statustype', category = '$category', dispocat = '$dispocat', options='".mysql_real_escape_string($options)."'");
        if ($statustype == 'transfer')
        {
            if ($category == 'team' || $category == 'agent')
            {
                $st2 = 'dateandtime';
            }
            else $st2 = 'text';
            mysql_query("INSERT into statuses set projectid = '$transfertopid', statusname = '".mysql_real_escape_string($statusname)."', statustype = '$st2', category = '$category', dispocat = '$dispocat', options='".mysql_real_escape_string($options)."'");
        }
        }
	exit;
	}
if ($act == 'wiz_dispowin')
	{
	$projid = $_REQUEST['pid'];
	$catres = mysql_query("SELECT * from dispocat");
	while ($catrow = mysql_fetch_array($catres))
		{
		$catlist .= '<option value="'.$catrow['cid'].'">'.$catrow['desc'].'</option>';
		}
	$defres = mysql_query("SELECT * from statuses where projectid = 0");
	while ($defrow = mysql_fetch_array($defres))
		{
		$deflist .= '<tr><td class="center-title" width="50%">'.$defrow['statusname'].'</td><td class="dataleft">'.$defrow['statustype'].'</td></tr>';
		}
	$disres = mysql_query("SELECT * from statuses where projectid = '$projid'");
	while ($disrow = mysql_fetch_array($disres))
		{
		$dislist .= '<tr><td class="center-title" width="50%">'.$disrow['statusname'].'</td><td class="dataleft">'.$disrow['statustype'].'</td></tr>';
		}
	?>
    <table width="650" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
		<tr><td class="center-title" colspan="5" style="background-color:#DBDBDB">Add Campaign Disposition</td></tr>
		<tr><td class="center-title">Disposition Name</td><td class="center-title">Type</td><td class="center-title">Category</td><td class="center-title">Report Group</td><td class="center-title">...</td></tr>
		<tr>
		<td class="center-title"><input type=text name="statusname" id="statusname"/></td>
		<td class="center-title"><select name="statustype" id="statustype">
				<option value="text">Text Description</option>
				<option value="sale">Sale Outcome</option>
				<option value="dateandtime">Date Set Outcome</option>
			</select>
		</td>
		<td class="center-title"><select name="category" id="category">
				<option value="agent">Agent Owned Lead</option>
				<option value="callable">Recallable</option>
				<option value="final">Final Outcome</option>
			</select>
		</td>
		<td class="center-title"><select name="dispocat" id="dispocat"><?=$catlist;?></select></td>
		<td class="center-title"><img src="icons/add.gif" onclick="add_dispowin()"/></td>
		</tr>
		<? /*
			<tr><td class="center-title">Disposition: </td><td class="dataleft"><input type=text name="statusname" id="statusname"/> (no spaces allowed)</td></tr>
			 <tr><td class="center-title">Type: </td><td class="dataleft"><select name="statustype" id="statustype">
				<option value="text">Text Description</option>
				<option value="sale">Sale Outcome</option>
				<option value="dateandtime">Date Set Outcome</option>
			</select> <br /></td></tr>
			 <tr><td class="center-title">Category:  </td><td class="dataleft"><select name="category" id="category">
				<option value="agent">Agent Owned Lead</option>
				<option value="callable">Recallable</option>
				<option value="final">Final Outcome</option>
			</select> <br /></td></tr>
		   <tr><td class="center-title"> Report Group: </td><td class="dataleft"><select name="dispocat" id="dispocat"><?=$catlist;?></select></td></tr> */
		   ?>
   </table>
   <table width="650" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
		<tr><td class="center-title" colspan="2" style="background-color:#DBDBDB">Campaign Dispositions</td></tr>
		<?=$dislist;?></table><br />
			<table width="650" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
		<tr><td class="center-title" colspan="2" style="background-color:#DBDBDB">Default Dispositions</td></tr>
		<?=$deflist;?></table>
			<?
			
			exit;
			}


		if ($act == 'info2')
			{
				echo '';
				$act = 'info';
			}
		if ($act == 'updatetemplate')
			{
				$templateid = $_REQUEST['templateid'];
				$emailfrom = $_REQUEST['emailfrom'];
                                $emailfromname = $_REQUEST['emailfromname'];
				$template_subject = $_REQUEST['template_subject'];
				$disposend = $_REQUEST['disposend'];
				$template_name = $_REQUEST['template_name'];
				$mailencryption = $_REQUEST['mailencryption'];
				$mailserver = $_REQUEST['mailserver'];
				$mailport = $_REQUEST['mailport'];
				$mailuser = $_REQUEST['mailuser'];
				$mailpass = $_REQUEST['mailpass'];
				$mailcc = $_REQUEST['emailcc'];
				$mailbcc = $_REQUEST['emailbcc'];
                                $editable = $_REQUEST['editable'];
				$texts = rawurldecode($_REQUEST['tex']);
                                $sigid = $_REQUEST['sigid'];
				$uq = "update templates set template_body = '".mysql_real_escape_string($texts)."', template_name= '".mysql_real_escape_string($template_name)."', emailfrom = '".mysql_real_escape_string($emailfrom)."', emailfromname = '".mysql_real_escape_string($emailfromname)."', mailencryption = '".mysql_real_escape_string($mailencryption)."', mailserver = '".mysql_real_escape_string($mailserver)."', mailport = '".mysql_real_escape_string($mailport)."', mailuser = '".mysql_real_escape_string($mailuser)."', mailpass = '".mysql_real_escape_string($mailpass)."', template_subject = '".mysql_real_escape_string($template_subject)."', disposend = '".mysql_real_escape_string($disposend)."', emailcc = '$mailcc', emailbcc='$mailbcc',editable='$editable',sigid='$sigid' where templateid = '$templateid'";
				mysql_query($uq) or die(mysql_error());
                               /* if ($_REQUEST['test'] == 'true')
                                {
                                    $mailto = $_REQUEST['mailto'];
                                    $r = fopen("../interface/emailer.php?act=sendemail&tid=$templateid&uid=$uid&to=$emailto&from=$emailfrom&subject=$template_subject&message=$texts","r");
                                    echo fread($r,200);
                                }
				echo $mailto;*/
				exit;
			}
		if ($act == 'updatescript')
			{
				$pid = $_REQUEST['scriptid'];
				$texts = rawurldecode($_REQUEST['tex']);
				mysql_query("update scripts set scriptbody = '".mysql_real_escape_string($texts)."' where scriptid = '$pid'");
						  
				$act = 'getapp';
				$_REQUEST['app'] = 'mancamp';
				//$act = 'editscript';
				exit;
			}
		if ($act == 'removeattachment')
			{
				$tid = $_REQUEST['templateid'];
				$attachment = $_REQUEST['attachment'];
				$tempres = mysql_query("SELECT * from templates where templateid = '$tid'");
				$template = mysql_fetch_assoc($tempres);
				$atts = explode(",",$template['attachments']);
				foreach ($atts as $att)
					{
						if ($att != $attachment)
							{
								$newatt[] = $att;
							}
					}
				$newatts = implode(",",$newatt);
				mysql_query("UPDATE templates set attachments = '".mysql_real_escape_string($newatts)."' where templateid = '$tid'");
				//$act = 'emailtemplates';
				exit;
			}
		if ($act == 'emailtemplates')
			{
				if (featurecheck($bcid,'email'))
					{
						include "emailtemplate.php";
					}
				else echo "Feature not Supported.  Please Contact your Administrator";
				exit;
				
			}
                if ($act == 'emailsignatures')
			{
                                include "signatures.php";
				exit;
			}
		if ($act == 'getpagefields')
		{
						$scriptid = $_REQUEST['scriptid'];
						$script = callscripts::getscript($scriptid);
				$scripts = new callscripts($script['projectid']);
						$fields = $scripts->getfields($scriptid);
						$tfs = '';
						if (count($fields['textfields'][1]) > 0)
						{
							$req = "<div>Required Field Values:</div>";  
							foreach ($fields['textfields'][1] as $field)
							{
							$tfs .= '<div>'.$field.':<input type="text" name="'.$field.'" /></div>';
							}
						}
						if (count($fields['dropdowns'][1]) > 0)
						{
						$req = "<div>Required Field Values:</div>";        
						$drops = '';
						foreach ($fields['dropdowns'][1] as $field)
						{
							$drops.='<div>'.$field.':<select name="'.$field.'">'.$fields['options'][$field][1][0].'</select></div>';
						}
						}
						echo $req;
						echo $drops;
						echo $tfs;
						exit;
		}
		if ($act == 'editscriptid')
			{
				$scriptid = $_REQUEST['scriptid'];
				$isthere = mysql_query("select * from scripts where scriptid = '$scriptid'");
				$is = mysql_num_rows($isthere);
				if ($is)
					{
						$row = mysql_fetch_array($isthere);
						$body = stripslashes($row['scriptbody']);
						$scriptid = $row['scriptid'];
						$pid = $row['projectid'];
					}
				include "scriptedit.php";
			}
		if ($act == 'editscript')
			{
				$pid = $_REQUEST['pid'];
				
				$isthere = mysql_query("select * from scripts where projectid = '$pid'");
				$is = mysql_num_rows($isthere);
				if ($is)
					{
						$row = mysql_fetch_array($isthere);
						$body = stripslashes($row['scriptbody']);
						$scriptid = $row['scriptid'];
					}
				else 
					{
						$body = "";
						mysql_query("insert into scripts set projectid='$pid'");
						$scriptid = mysql_insert_id();
					}
				include "scriptedit.php";
				exit;
			}
		if ($act == 'addscriptpage')
			{
				$pid = $_REQUEST['pid'];
				$parentid= $_REQUEST['parentid'];
				$scriptname = $_REQUEST['scriptname'];
						$rf = $_REQUEST['rf'];
						$un = $_POST;
						$j = json_encode($un);
				$body = "";
				mysql_query("insert into scripts set scriptname = '".mysql_real_escape_string($scriptname)."', projectid='$pid', parentid = '$parentid',requiredfields = '".mysql_real_escape_string($j)."'");
				$scriptid = mysql_insert_id();
				include "scriptedit.php";
				
				exit;
			}
		if ($act == 'monitor')
			{
			$proj = $_GET['pid'];
			echo " ";
			$res = mysql_query("SELECT liveusers.*, members.userlogin from liveusers left join members on liveusers.userid = members.userid where liveusers.projectid ='$proj'");
			while ($row = mysql_fetch_array($res))
				{
				
				$dur = date("Y-m-d h:m:s") - $row['waiting'];
				$disp .='<tr>
				<td class="center-title">'.$row['status'].'</td>
				<td class="center-title">'.$row['userlogin'].'</td>
				<td class="center-title">'.$row['leadid'].'</td>
				<td class="center-title">'.$dur.'</td>
				<td class="center-title"><a href="#">Barge | Whisper</a></td>
				</tr>';

				} 
			?>
			<table width="650" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
		<tr>
		  <td class="center-title" colspan="6" style="background-color:#DBDBDB">Campaign Monitoring </td>
		</tr>
		<tr><td class="center-title">Status</td><td class="center-title">User</td><td class="center-title">Leadid</td><td class="center-title">Duration</td><td class="center-title">Action</td></tr>
		<?=$disp;?>
	</table><div id="agentdisplay" width="650" style="overflow:auto; height:300px"></div>
	
	
	<?
	exit;
	}
if ($act == 'teamdet' || $act == 'teamdet2')
	{
	$teamid = $_GET['teamid'];
	$res = mysql_query("SELECT * from teams where teamid = '$teamid'");
	$team = mysql_fetch_array($res);
	$projects = split(";",$team['projects']);
	$mres = mysql_query("SELECT memberdetails.userid, memberdetails.afirst, memberdetails.alast, members.active, memberdetails.team from memberdetails left join members on memberdetails.userid = members.userid where members.active = '1'");
	while ($mrow = mysql_fetch_array($mres))
		{
                        $te = json_decode($mrow['team']);
                        if (in_array($teamid,$te))
                        {
                            $members[] = $mrow;
                        }
		}
	if ($act == 'teamdet')
		{
		echo " ";
		}
	?>
	
	
        <div class="apptitle">Team Details</div>
                <div class="secnav">
                    <tr><td class="center-title" colspan="2"><input type="button" onclick="getapp('manteams')" value="Back" />
                </div>
	<table width="100%" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
<tr><td class="tableheader" colspan="2">Team Details</td></tr>
<tr><td class="center-title" colspan="2" id="teambar">Team Name: <?=$team['teamname'];?></td></tr><tr><td class="center-title" colspan="1">Campaigns</td><td class="center-title" colspan="1">Members</td></tr>
<tr><td style="border: 1px #666 solid" valign="top"><div>
<?
foreach($projects as $proj)
	{
	if (strlen($proj) != 0)
		{
		$pres = mysql_query("SELECT * from projects where projectid = '$proj'");
		$pred = mysql_fetch_array($pres);
		echo '<div style="color:#DD0000; padding: 5px 5px 5px 5px;" colspan="2">'.$pred['projectname'].'</div>';
		}
	}
?>
</div ></td><td colspan="1" valign="top" style="border: 1px #666 solid"><div>
<?
foreach($members as $memb)
	{
		echo '<div style="color:#DD0000; padding: 5px 5px 5px 5px;" colspan="2"><a href="#"  onclick="getagentdetails(\''.$memb['userid'].'\')"> '.$memb['afirst'].' '.$memb['alast'].'</a><img src="icons/delete.gif" onclick="remuser(\''.$memb['userid'].'\',\''.$teamid.'\')"></div>';

	}
?>
<div>
<img src="icons/add.gif" onclick="getapp('managents');">
</div>
</div></td></tr>
</table><div id="agentdisplay" width="650" style="overflow:auto; height:300px"></div>
	<?
	exit;
	}
if ($act == 'deleteuser')
	{
	$uid = $_REQUEST['uid'];
	mysql_query("update members set isdeleted = 1 where userid = '$uid'");
	$act = 'getapp';
	$_REQUEST['app'] = 'managents';
	}
if ($act == 'passreset')
	{
	$newpass = $_REQUEST['pass'];
	$uid = $_REQUEST['uid'];
	$res = mysql_query("update members set userpass = '".mysql_real_escape_string($newpass)."' where userid = '$uid'");
	if (mysql_affected_rows()) echo "Password was successfully changed!";
	exit;
	}
if ($act == 'getrecyclehistory')
	{
	//echo "getagent| Agent Details |";
	$lid = $_REQUEST['lid'];
        $members = members::getallmemberdetails();
	$res = mysql_query("SELECT * from lists_history where lid = '$lid'");
	while ($row = mysql_fetch_assoc($res))
        {
            $rows[$row['id']]['date'] = '<span class="tolocaldate">'.$row['date_epoch'].'</span>';
            $rows[$row['id']]['recs'] = $row['total_recycled'];
            $rows[$row['id']]['userid'] = $members[$row['userid']]['userlogin'];
        }
        $headers[] = 'Date';
        $headers[] = 'Records Recycled';
        $headers[] = 'User';
	echo tablegen($headers,$rows,'100%');
	exit;
	}
if ($act == 'getcallhistory')
	{
	//echo "getagent| Agent Details |";
	$agentid = $_REQUEST['agentid'];
	$callhistoryres = mysql_query("select finalhistory.callid, finalhistory.phone, finalhistory.answeredtime, finalhistory.startepoch,finalhistory.endepoch,finalhistory.endepoch - finalhistory.startepoch as 'Duration', finalhistory.leadid, finalhistory.agentdisposition, finalhistory.systemdisposition
from finalhistory where finalhistory.userid = '$agentid' order by startepoch DESC limit 100;");
	$ct = 0;
	while ($row = mysql_fetch_array($callhistoryres))
		{
		$ch[$ct] = $row;
		if ($ct !=0) $lids.=",";
		$lea = $row['leadid'];
		$lids .= "'$lea'"; 
		$ct++;
		}

	$leaddetres = mysql_query("select leads_done.leadid, leads_done.cname, leads_done.cfname, leads_done.clname, leads_done.company from leads_done where leadid in ($lids) order by timeofcall DESC");
	while ($row2 = mysql_fetch_array($leaddetres))
		{
		$leads[$row2['leadid']] = $row2;
		}
	$cts = count($ch);
	$ct = 0;
        $headers[] = 'Date';
        $headers[] = 'Phone';
        $headers[] = 'Name';
        $headers[] = 'Duration';
        $headers[] = 'Talktime';
        $headers[] = 'Disposition';
	
	while ($ct < $cts)
		{
		$tplid = $ch[$ct]['leadid'];
		$name = $leads[$tplid]['cname'];
		if (strlen($name) == 0 || $name==' ') {$name = $leads[$tplid]['company'];}
		if (strlen($name) == 0 || $name==' ') $name = $leads[$tplid]['cfname'].' '.$leads[$tplid]['clname'];
		$rows[$ct][2] =  '<span class="tolocaldate">'.$ch[$ct]['startepoch'].'</span>';
                $rows[$ct][3] = $ch[$ct]['phone'];
                $rows[$ct][4] = $name;
                $rows[$ct][5] =$ch[$ct]['Duration'];
                $rows[$ct][6] =$ch[$ct]['answeredtime'];
                $rows[$ct][7] =$ch[$ct]['agentdisposition'];
		$ct++;
		}
	
	echo tablegen($headers,$rows,'100%');
	exit;
	}
if ($act == 'clientdetails')
	{
		$cid = $_REQUEST['clientid'];
		$cres = mysql_query("SELECT * from clients where clientid = $cid and bcid = '$bcid'");
		$row = mysql_fetch_array($cres);
		$conq = "SELECT client_contacts.*, members.userlogin, members.userpass, members.usertype as usermode from client_contacts 
							  left join members on client_contacts.userid = members.userid where clientid = $cid and client_contacts.bcid = '$bcid' and client_contacts.active = 1";
		$conres = mysql_query($conq);
		//echo $conq;
                $ct = 0;
		while ($conrow = mysql_fetch_array($conres))	
			{
                                $clientname = $conrow['firstname'].' '.$conrow['lastname'];
                                if (strlen($clientname) == 1) $clientname = "...";
				$contacts[$ct][2] = '<a href="#" onclick="editclientcontact(\''.$conrow['client_contactid'].'\')">'.$clientname.'</a>';
				$contacts[$ct][3] = $conrow['userlogin'];
				$contacts[$ct][4] = $conrow['userpass'];
				$contacts[$ct][5] = $conrow['email'];
				$contacts[$ct][6]= $conrow['usermode'];
				$contacts[$ct][7]= $conrow['phone'];
				$contacts[$ct][8]= '<a href="#" onclick="appbook(\''.$conrow['client_contactid'].'\',\''.$conrow['clientid'].'\')">Edit Schedule</a>';
                               $contacts[$ct][9]= '<a href="#" onclick="deletecontact(\''.$conrow['client_contactid'].'\',\''.$cid.'\')">Delete</a>';
                                $ct++;
			}
		
		?>
<div id="ACampaignsLeftNavigation">        
                        <div class="apptitle"><a href="#" onclick ="manclient('Active')">Manage Clients</a> - <?=$row['company'];?></div>
                        <div class="secnav">
                        </div>
                        </div>        
<?php
		
                $headers[] = 'Option'; $headers[] = 'Value';
                $rows[] = array(1=>'Company', '<input type=text value="'.$row['company'].'" onblur="submitchangesclient(this.value,\'clients\',\'company\','.$cid.')" />');
                $fieldn = 'address1';
                $rows[] = array(1=>ucfirst($fieldn), '<input type=text value="'.$row[$fieldn].'" onblur="submitchangesclient(this.value,\'clients\',\''.$fieldn.'\','.$cid.')" />');
                $fieldn = 'address2';
                $rows[] = array(1=>ucfirst($fieldn), '<input type=text value="'.$row[$fieldn].'" onblur="submitchangesclient(this.value,\'clients\',\''.$fieldn.'\','.$cid.')" />');
                $fieldn = 'city';
                $rows[] = array(1=>'Suburb', '<input type=text value="'.$row[$fieldn].'" onblur="submitchangesclient(this.value,\'clients\',\''.$fieldn.'\','.$cid.')" />');
                $fieldn = 'state';
                $rows[] = array(1=>ucfirst($fieldn), '<input type=text value="'.$row[$fieldn].'" onblur="submitchangesclient(this.value,\'clients\',\''.$fieldn.'\','.$cid.')" />');
                $fieldn = 'companyurl';
                $rows[] = array(1=>'Company Website', '<input type=text value="'.$row[$fieldn].'" onblur="submitchangesclient(this.value,\'clients\',\''.$fieldn.'\','.$cid.')" />');
                $fieldn = 'email';
                $rows[] = array(1=>ucfirst($fieldn), '<input type=text value="'.$row[$fieldn].'" onblur="submitchangesclient(this.value,\'clients\',\''.$fieldn.'\','.$cid.')" />');
                $fieldn = 'phone';
                $rows[] = array(1=>ucfirst($fieldn), '<input type=text value="'.$row[$fieldn].'" onblur="submitchangesclient(this.value,\'clients\',\''.$fieldn.'\','.$cid.')" />');
                $fieldn = 'altphone';
                $rows[] = array(1=>'Fax', '<input type=text value="'.$row[$fieldn].'" onblur="submitchangesclient(this.value,\'clients\',\''.$fieldn.'\','.$cid.')" />');
                $vl = $row['recordings'];
                $vl2 = ($vl == 'yes')  ? 'no':'yes';
                $rows[] = array(1=>'View Recordings', '<select type=text value="'.$vl.'" onchange="submitchangesclient(this.value,\'clients\',\'recordings\','.$cid.')"><option value="'.$vl.'">'.$vl.'</option><option value="'.$vl2.'">'.$vl2.'</option></select>');
                $vl = $row['notes'];
                $vl2 = ($vl == 'yes')  ? 'no':'yes';
                $rows[] = array(1=>'View Notes', '<select type=text value="'.$vl.'" onchange="submitchangesclient(this.value,\'clients\',\'notes\','.$cid.')"><option value="'.$vl.'">'.$vl.'</option><option value="'.$vl2.'">'.$vl2.'</option></select>');
                ?>
    <div class="dataTables_wrapper" id="clientdetailsdiv">
       <?php if (featurecheck($bcid,'clientportal'))
		{
		?>
    <div class="secnav" style="text-align:right"><input type="button" onclick="createpopup('addcontact','<?=$cid;?>')" value="Add Client Contact" style="float:right" /></div>
    <h3>Client Profile</h3>
    <?php
                }

echo tablegen($headers, $rows, "100%",'','clientlist_table');
$headers = array();
		if (featurecheck($bcid,'clientportal'))
		{
                    $headers[] = 'Name';
                    $headers[] = 'Login';
                    $headers[] = 'Password';
                    $headers[] = 'Email';
                    $headers[] = 'User Category';
                    $headers[] = 'Phone';
                    $headers[] = 'Booking Calendar';
                    $headers[] = '...';
                    echo "<br><h3>Client Contacts</h3>";
                    echo tablegen($headers, $contacts, "100%",'','clientlist_table');
                
		if (featurecheck($bcid,'minicrm'))
			{
		?>
		<a href="#" onclick="addnewcstat('<?=$cid;?>')"><b>New CRM status</b></a><br /><br />
		 <table width="650" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
		<!--<tr><td class="center-title" colspan="4" style="background-color:#DBDBDB">CRM Status</td></tr>
		<tr><td class="center-title">Status Name</td><td class="center-title">Status User</td>
        <td class="center-title">Status Action</td><td class="center-title">Options</td></tr>-->
        <tr style="display:none" id="addrow">
        <td class="center-title"><input type="text" name="status_name" id="crmstatusname"/></td>
        <td class="center-title"><select name="status_type" id="crmstatustype"/><option value="client">Client</option><option value="user">Client's Users</option></select></td>
        <td class="center-title"><select name="action" id="crmstatusaction"><option value="assign">Assigns to a User</option><option value="update">Triggers an Update</option><option value="lock">Process Completed</option></select></td>
        <td class="center-title"><a href="#" onclick="addcstat('<?=$cid;?>')">Add</a></td></tr>
        <?=$cstatuses;?>
        </table>
		<?
			}
		
		}
		else {
			echo "<b>Activate the client portal feature to add more options</b>";
		}
		
	?>
    </div>
    <?
		exit;
	}
if ($act == 'getagent' || $act == 'getclient')
	{
	$agentid = $_REQUEST['userid'];
	$agentres = mysql_query("SELECT members.*,memberdetails.* from members left join memberdetails on members.userid = memberdetails.userid where active = 1 and members.userid ='$agentid';");
	if ($act == 'getclient')
		{
			$agentres = mysql_query("SELECT members.*,clients.* from members left join clients on members.userid = clients.userid where active = 1 and members.userid ='$agentid';");
		}
$row = mysql_fetch_array($agentres);
	if ($act == 'getclient')
	{
		$first = $row['cfirst'];
		$last = $row['clast'];
		$first_fild = 'cfirst';
		$last_fild = 'clast';
		$company_fild = 'company';
		$company = $row['company'];
		$type = 'clients';
		$title = 'Client Details';
		$uid = $row['userid'];
	}
	else 
	{
		$first = $row['afirst'];
		$last = $row['alast'];
		$first_fild = 'afirst';
		$last_fild = 'alast';
		$company_fild = 'company';
		$company = $row['company'];
		$type = 'memberdetails';
		$title = 'Agent Details';
		$uid = $row['userid'];
                $ch = '<input type="button" onclick="getapp(\'managents\')" value="Back"/>';
		$ch .= '<input type="button" onclick="getcallhistory(\''.$uid.'\')" value="View last 100 Calls"/>';
	}
?>
                <div class="apptitle">Agent Profile</div>
                <div class="secnav">
                    <tr><td class="center-title" colspan="2"><?=$ch;?><input type="button" onclick="deleteuser('<?=$agentid;?>')" value="Delete User" />
                </div>
<table width="650" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
<tr><td class="tableheader" colspan="3" >Agent Details</td></tr>
<tr><td class="center-title">FirstName</td><td class="dataleft"><div id="<?=$first_fild;?>"><a onclick="changedetails('<?=$first_fild;?>','<?=$first;?>','<?=$type;?>','<?=$uid;?>')"><?=$first;?>&nbsp;</a></td></tr>
<tr><td class="center-title">Lastname</td><td class="dataleft"><div id="<?=$last_fild;?>"><a onclick="changedetails('<?=$last_fild;?>','<?=$last;?>','<?=$type;?>','<?=$uid;?>')"><?=$last;?>&nbsp;</a></td></tr>
<tr><td class="center-title">Company</td><td class="dataleft"><div id="<?=$company_fild;?>"><a onclick="changedetails('<?=$company_fild;?>','<?=$company;?>','<?=$type;?>','<?=$uid;?>')"><?=$row['company'];?>&nbsp;</a></td></tr>
<tr><td class="center-title">Username</td><td class="dataleft"><?=$row['userlogin'];?></td></tr>
<tr><td class="center-title">Password</td><td class="dataleft"><?=$row['userpass'];?></td></tr>
<tr><td class="center-title" style="color:#DD0000">Change Password</td><td class="dataleft"><input type="text" id=newpass><button onclick="passreset('<?=$agentid;?>')">Change</button></td></tr>

</table><div id="agentdisplay" width="650" style="overflow:auto; height:300px"></div>

<?php
}

if ($act == 'createnewteam')
	{
	extract($_REQUEST);
	mysql_query("insert into teams set teamname = '".mysql_real_escape_string($teamname)."', bcid = '$bcid'");
	$newid = mysql_insert_id();
	$info = mysql_info();
	$act = 'getapp';
	$_REQUEST['app'] = 'managents';
	
	}
if ($act == 'createnewclient')
	{
	extract($_REQUEST);
	
	mysql_query("insert into clients set company='".mysql_real_escape_string($company)."', address1 = '".mysql_real_escape_string($address1)."', address2 = '".mysql_real_escape_string($address2)."', city = '".mysql_real_escape_string($city)."', 
				state = '".mysql_real_escape_string($state)."', companyurl = '".mysql_real_escape_string($companyurl)."', email = '".mysql_real_escape_string($email)."', phone = '".mysql_real_escape_string($phone)."', altphone= '".mysql_real_escape_string($altphone)."', bcid = '$bcid'");
	$newid = mysql_insert_id();
	echo $newid;
	exit;
	}
if ($act == 'createnewuser')
	{
        $members = new members();
	extract($_REQUEST);
        if ($members->checkMember($userlogin))
        {
            echo "Login already exists!";
        }
        else 
        {
	mysql_query("insert into members set userlogin = '".mysql_real_escape_string($userlogin)."', userpass= '".mysql_real_escape_string($userpass)."', email = '".mysql_real_escape_string($userlogin)."', usertype = 'user', roleid = '".mysql_real_escape_string($roleid)."', bcid= '$bcid'");
	$newid = mysql_insert_id();
	$info = mysql_info();
	mysql_query("insert into memberdetails set userid= '$newid', afirst = '".mysql_real_escape_string($afname)."', alast = '".mysql_real_escape_string($alname)."'");
	echo "<b>Added New User...</b>";
        }
	exit;
	}
if ($act == 'ttDBCreateNewCustomEvent')
{
	include("timetracker_management/ajax/ttDBCreateNewCustomEvent.php");
	exit;
}
if ($act == 'createnewproject')
	{
	extract($_REQUEST);
        if ($providerid < 1) $providerid = 1;
        $ctproj = checkexisting('projects', 'projectname', $projname);
        if ( $ctproj > 0)
        {
            echo "checkname";
            exit;
        }
	mysql_query("INSERT into projects set projectname = '".mysql_real_escape_string($projname)."', projectdesc = '".mysql_real_escape_string($projdesc)."', dialmode = '".mysql_real_escape_string($dialmode)."', dialpace = '".mysql_real_escape_string($dialpace)."', clientid = '".mysql_real_escape_string($clientid)."', providerid = '".mysql_real_escape_string($providerid)."', datecreated = NOW(), lastactive = NOW(), bcid = '$bcid'");
        $id = mysql_insert_id();
        mysql_query("INSERT into projects_droprate set projectid = '$id'");
        if ($clone == 'yes')
        {
            projects::clonecamp($cloneproj, $id);
        }
	echo $id;
	exit;
	}
if ($act == 'info')
{
	?>
    <div class="apptitle">Live Monitor</div><br />
    <?php
    echo '<iframe src="live.php" frameborder="0" id="liveframe"  name="liveframe" scrolling="auto"  style="width:100%; height:500px">';
}
include "switch.php";

include("timetracker_management/ttEventsOptUpdate.php");
include("agentinterface_management/uioptupdate.php");

if ($act == 'updatecamp')
{
	$projectid = $_REQUEST['pid'];
	$field = $_REQUEST['fld'];
	$val = $_REQUEST['vl'];
	if ($field == 'active') 
		{
			if ($val == '0') $val = 0;
			if ($val == '1') $val = 1;
                        $qu = "update projects set $field = '".mysql_real_escape_string($val)."', lastactive = NOW() where projectid = '$projectid'";
		}
        else $qu = "update projects set $field = '".mysql_real_escape_string($val)."' where projectid = '$projectid'";
	//echo $qu;
        if ($field == 'description')
        {
            $qu = "update uploads set description = '$val' where fileid = '$projectid'";
        }
	mysql_query($qu);
}
include_once("queuepreview/admin-include.php");

