<?php
session_start();
$lid = $_REQUEST['lid'];
$act = $_REQUEST['act'];
$bcid = $_SESSION['bcid'];
$sessionid = $_REQUEST['PHPSESSID'];
date_default_timezone_set($_SESSION['timezone']);
$securekey = 'Do not change this whatever happens!';
if (strlen($sessionid) < 1)
	{
		$sessionid = session_id();
	}
$userid = $uid;
$dialmode = $_REQUEST['dialmode'];
include "../dbconnect.php";
$pidres = mysql_query("SELECT liveusers.*, ip.projectname as 'inbound_projectname' from liveusers left join "
        . "projects ip on liveusers.projectid_inbound_active = ip.projectid "
        . "where liveusers.sessionid = '$sessionid'");
$pidrow = mysql_fetch_assoc($pidres);
$pidcount = mysql_num_rows($pidres);
if ($pidcount < 1 && $act != 'exitdial')
	{
		echo "loggedout";
                //echo "<Script>window.location.replace('../login/')</script>";
		exit;
	}
$auid = $pidrow['userid'];
$uid = $pidrow['userid'];
$pid = $pidrow['projectid'];
$actionid = $pidrow['actionid'];
$_SESSION['actionid'] = $actionid;
include "phpfunctions.php";
require_once '../classes/classes.php';
include "../ami-client.php";
$ami = new AMI("webby","1234561");
$ami->debug = true;
$ami->confserver = $pidrow['confserver'];
$confserver = new AMI("webby","1234561");
$confserver->confserver = $pidrow['confserver'];
if ($act == 'lockteamcb')
{
    mysql_query("UPDATE leads_done set isteamcallback = 0 where leadid = ".$_REQUEST['leadid']);
    $aff = mysql_affected_rows();
    echo $aff;
    if ($aff != 0)
    {
         mysql_query("UPDATE leads_done set assigned = $uid where leadid = ".$_REQUEST['leadid']);
    }
    exit;
}
if ($act== 'bulkdispose')
{
    $ids = explode(",",$_REQUEST['ids']);
    $dispo = $_REQUEST['dispo'];
    $cal = $_REQUEST['cal'];
    foreach ($ids as $leadid)
    {
        mysql_query("update leads_raw set dispo = '$dispo' where leadid = $leadid limit 1");
        mysql_query("update leads_done set dispo = '$dispo' where leadid = $leadid limit 1");
    }
    if (strlen($cal)> 1)
    {
        foreach ($ids as $leadid)
        {
           /* $res = mysql_query("SELECT * from dateandtime where leadid = $leadid");
            if (mysql_num_rows($res)> 0)
            {
                mysql_query("update dateandtime set dtime = '$cal' where leadid = $leadid");
            }
            else mysql_query("insert into dateandtime set dtime = '$cal', leadid = $leadid");*/
            mysql_query("update leads_raw set epoch_callable = '".$cal."' where leadid = $leadid");
            mysql_query("update leads_done set epoch_callable = '".$cal."' where leadid = $leadid");
        }
    }
    echo count($ids)." records updated!";
    exit;
}
if ($act == 'getsim')
{
    $leadid = $_REQUEST['leadid'];
    $record = new records($leadid);
    $ispres = mysql_query("select * from projects where projectid = '$pid';");
    $project = mysql_fetch_assoc($ispres);
    $lists = getlistsbypid($project['projectid']);
    $res = mysql_query("SELECT leadid FROM leads_raw WHERE leadid != $leadid and listid in ($lists) and company IS NOT NULL AND company != '' AND phone IS NOT NULL AND phone != '' AND (company = '".$record->company."' OR phone = '".$record->phone."')");
    $headers[] = '<input type=checkbox id="togglecheck" onclick="togids()" />';
    $headers[] = "Leadid";
    $headers[] = "Name";
    $headers[] = "Position";
    $headers[] = 'Company';
    $headers[] = 'Phone';
    $headers[] = 'Last Call Date';
    $headers[] = 'Disposition';
    while ($r = mysql_fetch_assoc($res))
    {
        $leadrec = new records($r['leadid']);
        $lead = $leadrec->data;
       $rows[$lead['leadid']][1] = '<input type=checkbox name="ids[]" class="simtabtick" value="'.$lead['leadid'].'" />';
        $rows[$lead['leadid']][2] = '<a href="#" onclick="sim_cbdial(\''.$lead['leadid'].'\')">'.$lead['leadid'].'</a>';
        $rows[$lead['leadid']][3] = strlen($lead['cname']) > 1 ? $lead['cname']:$lead['cfname']." ".$lead['clname'];
        $rows[$lead['leadid']][4] = $lead['positiontitle'];
        $rows[$lead['leadid']][5] = $lead['company'];
        $rows[$lead['leadid']][6] = $lead['phone'];
        $rows[$lead['leadid']][7] = date("Y-m-d H:i:s", $lead['epoch_timeofcall']);
        $rows[$lead['leadid']][8] = $lead['dispo'];
    }
    echo tablegen($headers,$rows,"100%","","simdatatabs");
    require "bulkdisposeform.php";
    exit;
}
if ($act == 'cslots')
{
    $ispres = mysql_query("select * from projects where projectid = '$pid';");
    $project = mysql_fetch_assoc($ispres);
    $headers[] = "Contact";
    $headers[] = "Date";
    $headers[] = "Start";
    $headers[] = "End";
    $headers[] = "Status";
    $ctr = 0;
    $cslots = getallbyparams("client_contact_slots","where clientid = '".$project['clientid']."' and slotstart > ".time()." order by slotstart ASC");
    if (count($cslots) > 0) $doslots = true;
    $ccontacts = get("client_contacts","client_contactid");
    foreach ($cslots as $cslot)
            {
                    $pdate = date("Y-m-d H:i:s",$cslot['slotstart']);
                    $rows[$ctr]['contact'] = $ccontacts[$cslot['client_contactid']]['firstname']. ' '.$ccontacts[$cslot['client_contactid']]['lastname'];
                    $rows[$ctr]['date'] = date("Y-m-d",$cslot['slotstart']);
                    $rows[$ctr]['start'] = date("H:i:s",$cslot['slotstart']);
                    $rows[$ctr]['end'] = date("H:i:s",$cslot['slotend']);
                    $status = '<span style="color:#66FF33">free</span>';
                    if ($cslot['taken'] > 0)
                            {
                                    $status = '<span style="color:red">taken</span>';
                            }
                    $rows[$ctr]['status'] = ucfirst($status);
                    if ($cslot['taken'] < 1)
                            {
                                $rows[$ctr]['options'] = 'onclick="popdate(\''.$pdate.'\',\''.$cslot['slotid'].'\')"';
                            }
                    $ctr++;
            }
    echo '<a href="#" onClick="usecal()">Use Calendar</a>';
    echo tablegen($headers,$rows,600,'','datatabs');
    exit;
}
if ($act == 'newlead')
{
    ?><div id=nlp>
        Search: <input type="text" id="nlphone" name="nlphone"/> <input type="button" value="Search" onclick="nlsearch(0)" id="formbutton"/>
        </div>
        <div id=nlmain>
        </div>
        <?
        exit;
}
if ($act == 'newinboundcall')
{
    $arow = $pidrow;
    ?><b>Inbound Call Received on <?=$arow['inbound_projectname'];?></b><br><div id=nlp>
        Search: <input type="text" id="nlphone" name="nlphone"  value="<?=$arow['callerid'];?>" /> 
        <input type="button" onclick="nlsearch(1)" id="formbutton" value="Search"/> 
        <input type="button" onclick="rejectinboundcall('<?=$arow['callid'];?>')" id="formbutton" value="Reject/Cancel" />
        </div>
        <div id=nlmain>
        </div>
        <?php
        exit;
}
if ($act == 'createnewinboundentry')
{
    $phone = $_REQUEST['newphone'];
    $record = new records();
    $record->phone = $phone;
    $ipid = $pidrow['projectid_inbound_active'];
    $nlid = "InboundGenerated".$ipid;
    $nlres = mysql_query("SELECT * from lists where listid = '$nlid'");
    if (mysql_num_rows($nlres) < 1)
    {
        mysql_query("insert into lists set listid = '$nlid', projects = '$ipid', active = 1, datecreated = NOW(), bcid = '$bcid'");
    }
    $record->listid = $nlid;
    $record->createraw();
    $record->projectid = $ipid;
    $record->createdone();
    $record->update();
    mysql_query("update liveusers set leadid = '".$record->leadid."' where userid = $uid");
    mysql_query("update callman set leadid = '".$record->leadid."' where userid = $uid");
    $record->data['override_pid'] = $ipid; //override current main campaign;
    echo json_encode($record->data);
    exit;
}
if ($act == 'inbounduselead')
{
    $leadid = $_REQUEST['leadid'];
    $record = new records($leadid);
    $ipid = $pidrow['projectid_inbound_active'];
    mysql_query("update liveusers set leadid = '".$record->leadid."' where userid = $uid");
    mysql_query("update callman set leadid = '".$record->leadid."' where userid = $uid");
    $record->data['override_pid'] = $record->projectid; //override current main campaign;
     echo json_encode($record->data);
     exit;
}
if ($act == 'dialstate')
{
    $res = mysql_query("SELECT * from liveusers where userid = '".$_REQUEST['userid']."'");
    $row = mysql_fetch_assoc($res);
    echo $row['status'];
    exit;
}
if ($act == 'getstatusoption')
{
    $res = mysql_query("SELECT options from statuses where statusid = '".$_REQUEST['statusid']."' ");
    $row = mysql_fetch_assoc($res);
    if (strlen($row['options']) < 1) echo 'none';
    else echo $row['options'];
    exit;
}
if ($act == 'scriptdata')
{
    $record = new records($_REQUEST['leadid']);
    $sdata = array();
    $sdata[$_REQUEST["field"]] = $_REQUEST['value'];
    $record->scriptdata($sdata);
    exit;
}
if ($act == 'getnotes')
{
    $leadid = $_REQUEST['leadid'];
    if ($leadid > 0)
    {
    $record = new records($leadid);
    $prevnote = $record->notes();
    echo $prevnote;
    }
    exit;
}
if ($act == 'addnote')
{
    $u = new members($auid);
    $leadid = $_REQUEST['leadid'];
    if ($leadid > 0)
    {
    $record = new records($leadid);
    $prevnote = $record->notes();
    $newnote = $_REQUEST['note'];
    $jnote = json_decode($prevnote,true);
    if (!$jnote) $jnote = array();
    array_push($jnote,array(
        "user"=>$u->userlogin,
        "timestamp"=>time(),
        "message"=>$newnote
    ));
    $n = $record->notes(json_encode($jnote));
    echo $n;
    }
    exit;
}
if ($act == 'getprevsession')
{
    $s = new session($auid);
    if ($s->leadid > 0)
    {
        echo records::getlead($s->leadid);
        exit;
    }
    else echo "none";
    exit;
}
if ($act == 'savecustomdata')
{
    $data = $_REQUEST['data'];
    $leadid = $_REQUEST['leadid'];
    $data = json_decode($data,true);
    /***************************/
	/* ADDED BY Vincent Castro */
	/***************************/
    $selectcf = mysql_query("SELECT * from leads_custom_fields where leadid = '".$leadid."'");
	$fetchcf = mysql_fetch_assoc($selectcf);
	$countRows = mysql_num_rows($selectcf);
    if($countRows == 0){
      customdata::add($leadid,$data);
    } else {
      customdata::updatecf($leadid,$data);
    }
    exit;
}
if ($act == 'getcustomdata') {
    $leadid = $_REQUEST['leadid'];
    $auid = $_REQUEST['userid'];
    if ($leadid > 0) {
        // echo $leadid." ".$auid;
        $res = mysql_query("SELECT * from leads_custom_fields where leadid = '$leadid'");
        $row = mysql_fetch_assoc($res);
        $ret =  json_decode($row['customfields']);
        /***************************/
        /* ADDED BY Vincent Castro */
        /***************************/
        /* GET PROJECT ID */
        $pidres = mysql_query("SELECT * from liveusers where userid = '$auid'");
        $pidrow = mysql_fetch_assoc($pidres);
        $pid = $pidrow['projectid'];
        /* SORT FOR EXISTING CUSTOM FIELDS */
        $adminCustomFieldsGet = mysql_query("SELECT customfields FROM projects WHERE projectid = '$pid'");
        $adminCustomFieldsRow = mysql_fetch_assoc($adminCustomFieldsGet);
        $adminCustomFields = json_decode($adminCustomFieldsRow['customfields']);
        $objOrig = $adminCustomFields;
        $objOld = $ret;
        $arrOrig = get_object_vars($objOrig);
        $arrOld = get_object_vars($objOld);
        $newOld = array();
        $newData = array();
        // print_r($arrOrig);
        // echo "<br><br>";
        // print_r($arrOld);
        // echo "<br><br>";
        foreach ($arrOrig as $key => $value) {
            $newKey = preg_replace('/\s+/', '_', $key);
            $compareKey[] = $newKey;
        }
        foreach ($arrOld as $key => $value) {
            $newKey = preg_replace('/\s+/', '_', $key);
            $compareKey2[] = $newKey;
            $newOld[$newKey] = $value;
        }
        //SORT IN ORDER
        $properOrderedArray = array_merge(array_flip($compareKey), $newOld);
        // print_r($properOrderedArray);
        // MISSING ARRAY
        if(count($compareKey) > count($compareKey2)){
            $comparison = array_flip(array_diff($compareKey, $compareKey2));
        }
        // print_r($comparison);
        if(empty($comparison)){
            foreach ($properOrderedArray as $key => $value) {
                $newData[str_replace("_", " ", $key)] = $value;
            }
        } else {
            foreach ($properOrderedArray as $key => $value) {
                foreach ($comparison as $comkey => $comvalue) {
                    if($comkey == $key){
                        $newData[str_replace("_", " ", $key)] = "";
                    } else {
                        $newData[str_replace("_", " ", $key)] = $value;
                    }
                }
            }
        }
        // print_r($newData);
        if(empty($objOld)){
            foreach ($arrOrig as $key => $value) {
                $newData[$key] = "";
            }
        }
        // echo json_encode($newData);

        if ($pid > 0) {
            $res = mysql_query("SELECT customfields FROM projects WHERE projectid = '$pid'");
            $row = mysql_fetch_assoc($res);
            $ret = json_decode(stripslashes($row['customfields']),true);
            foreach ($ret as $key => $value) {
                $leadCustom[$value] = array('name' => $key, 'value' => $newData[$key]);
            }
            // $ret = json_decode(stripslashes($row['customfields']),true);
            echo json_encode($leadCustom);
        }

    }
    exit;
}
/***************************/
/* ADDED BY Vincent Castro */
/***************************/
if ($act == 'getnewcustomdata'){
    $pid = $_REQUEST['pid'];
    if ($pid > 0) {
        $res = mysql_query("SELECT customfields FROM projects WHERE projectid = '$pid'");
        $row = mysql_fetch_assoc($res);
        $ret = json_decode(stripslashes($row['customfields']),true);
        echo json_encode($ret);
    }
    exit;
}
$ispres = mysql_query("select * from projects where projectid = '$pid';");
$isprow = mysql_fetch_array($ispres);
$ispred = $isprow['dialmode'];
$dialmode = $ispred;
$projlist = getprojectlist($uid);
$dstatus = strtoupper($pidrow['status']);
if ($act == 'getlookup')
	{
		$res = mysql_query("SELECT * from lookuptable where projectid = '$pid'");
		$row = mysql_fetch_assoc($res);
		$ret['data'] =  json_decode($row['jsondata'],true);
		$ret['rowcount'] = $row['rowcount'];
		$ret['fields'] = $row['fields'];
		echo json_encode($ret);
		exit;
	}
if ($act == 'updatecheck')
	{
		include "cbnew.php";
		exit;
	}
if ($act == 'updateqantas')
	{
		$suppid = $_REQUEST['eid'];
		$email = $_REQUEST['email'];
		$contact = $_REQUEST['contact'];
		mysql_query("update qantas set email = '$email', contact = '$contact' where suppliernumber ='$suppid'");
		echo 'done';
		exit;
	}
if ($act == 'createnewentry')
	{
		$newphone = $_REQUEST['newphone'];
                if (is_numeric($newphone))
                {
                    $fld = 'phone';
                }
                else $fld = 'cname';
		savelead($_GET);
                $nlid = "agentgenerated".$pid;
                $nlres = mysql_query("SELECT * from lists where listid = '$nlid'");
                if (mysql_num_rows($nlres) < 1)
                {
                    mysql_query("insert into lists set listid = '$nlid', projects = '$pid', active = 1, datecreated = NOW(), bcid = '$bcid'");
                }
                mysql_query("update lists set listcount = listcount + 1 where listid = '$nlid'");
		mysql_query("INSERT into leads_raw set $fld = '".mysql_real_escape_string($newphone)."', listid = '$nlid', hopper = 1");
                $_REQUEST['leadid'] = mysql_insert_id();
		$leadidman = mysql_insert_id();
		$mandial = 1;
		$preview = 1;
		$act = 'getinfo';
	}
if ($act == 'nlgetdetails')
	{
		$leadid = $_REQUEST['leaid'];
                $row = new records($leadid);
		$disp .= '<div style="position:relative; float:left">';
		$disp .= (strlen($row['company']) > 0) ? 'Company: '.$row['company'].'<br>': '';
                $disp .= 'Address:<br>';
                $disp .= (strlen($row['address1']) > 0) ? $row['address1'].'<br>': '';
                $disp .= (strlen($row['address2']) > 0) ? $row['address2'].'<br>': '';
                $disp .= (strlen($row['suburb'].$row['city']) > 0) ? $row['suburb'].$row['city'].' ': '';
                $disp .= (strlen($row['state']) > 0) ? ",".$row['state'].' ': '';
                $disp .= (strlen($row['zip']) > 0) ? ",".$row['zip'].' ': '';
		$disp .= '<br>Last Called on: '.date('Y-m-d H:i:s',$row['epoch_timeofcall']).'<br></div>';
		$disp .= '<div style="position:relative; float:right;padding-right:100px"><a href="#" onclick="uselead(\''.$row['leadid'].'\')">Use this Entry</a>';
		$disp .= '</div><div style="clear:both"></div>';
		echo $disp;
		exit;
	}
if ($act == 'nlsearch')
	{
		$nl = $_REQUEST['phone'];
                $inb = $_REQUEST['inbound'];
		$nlphone = str_replace(" ","%",$nl);
		$projectidlist = $pid;
                if ($inb == 1)
                {
                    $projectidlist = $pidrow['projectid_inbound'] > 0 ? $projectidlist.",".$pidrow['projectid_inbound']:$projectidlist;
                }
		$resdone =  mysql_query("SELECT leads_done.*, projects.projectname from leads_done 
								left join projects on leads_done.projectid = projects.projectid
								where (phone like '%".$nlphone."%' or cname like '%".$nlphone."%' or cfname like '%".$nlphone."%' or company like '%".$nlphone."%' or clname like '%".$nlphone."%' or altphone like '%".$nlphone."%') and leads_done.projectid in ($projectidlist)");
		$donefound = mysql_num_rows($resdone);
		$doneleads = array();$donedets = array();
		while ($donerow = mysql_fetch_array($resdone))
			{
				$doneleads[] = $donerow['leadid'];
				$donedets[$donerow['leadid']] = $donerow;
			}
		$listidlists = getlistnew($bcid."' and projects = '$pid");
                $listidlists .= (strlen($listidlists) > 0) ? ",'agentgenerated$pid'":"'agentgenerated$pid'";
		$rawq = "SELECT leads_raw.*, lists.projects, projects.projectname from leads_raw 
	left join lists on leads_raw.listid = lists.listid left join projects on lists.projects = projects.projectid
				where (phone like '%".$nlphone."%' or cname like '%".$nlphone."%' or cfname like '%".$nlphone."%' or company like '%".$nlphone."%' or clname like '%".$nlphone."%' or altphone like '%".$nlphone."%') and leads_raw.listid in ($listidlists)";
		$resraw = mysql_query($rawq);
		$rawfound = mysql_num_rows($resraw);
		$found = array();
		if ($rawfound)
			{
				while ($row = mysql_fetch_array($resraw))
					{
						$found[$row['leadid']] = $row;
					}
			}
                if ($donedets)
                {
                    foreach ($donedets as $dets)
                    {
                        $found[$dets['leadid']] = $dets;
                    }
                }
                $afunction = ($inb == 1) ? 'createnewinboundentry':'createnewentry';
		if (!empty($found))
			{
                        $efunction = ($inb == 1) ? 'inbound_uselead':'uselead';
			$disp = '<div>Found '.count($found).' match(es) or <span style="text-align:right"><a href="#" onclick="'.$afunction.'(\''.$nlphone.'\')">Create New Entry</a></span></div><hr>';
                                $disp.= '<table id="lstable" class="datatabs" width="100%">';
                                $disp.= '<thead><th class="tableheader">LeadId</th><th class="tableheader">Phone</th><th class="tableheader">Name</th><th class="tableheader">Company</th><th class="tableheader">Disposition</th><th class="tableheader">LastCall</th></thead><tbody>';
				foreach ($found as $lead)
					{
						$disp .= '<tr style="cursor:pointer" onclick="'.$efunction.'(\''.$lead['leadid'].'\');">';
						$disp .= '<td style="width:10%"><a href="#">'.$lead['leadid'].'</a></td>';
                                                $disp .= '<td style="width:15%"><a href="#">'.$lead['phone'].'</a></td>';
						$disp .= '<td style="width:20%">';
                                                $disp .= (strlen($lead['cname']) > 0) ? $lead['cname']:$lead['cfname'].' '.$lead['clname'];                                                 
                                                $disp .='</td>';
						$disp .= '<td style="width:20%">'.$lead['company'].'</td>';
						$disp .= '<td style="width:20%">'.$lead['dispo'].'</td>';
                                                $disp .= '<td style="width:15%">'.date("Y-m-d H:i:s",$lead['epoch_timeofcall']).'</td>';
						$disp .= '</tr>';
					}
                                        $disp.= '</tbody></table>';
			}
		else {
                    $disp = '"'.$nlphone.'" was not found. You may <span style="text-align:right"><a href="#" onclick="'.$afunction.'(\''.$nlphone.'\')">Create New Entry</a></span><div style="display:none">'.$rawq.'</div>';
                }
                var_dump($projectidlist);
		echo $disp;
		exit;
	}
if ($act == 'acceptsched')
	{
		$s = $_REQUEST['schedid'];
		mysql_query("update schedule set agentapproved = 1 where schedid = '$s'");
		echo "Schedule Updated...";
		exit;
	}
if ($act == 'rejectsched')
	{
		$s = $_REQUEST['schedid'];
		mysql_query("update schedule set agentapproved = 2 where schedid = '$s'");
		echo "Schedule Updated...";
		exit;
	}
if ($act == 'getsched')
	{
		$dt = $_REQUEST['date'];
		$res = mysql_query("SELECT schedule.*, projects.projectname from schedule left join projects on schedule.projectid = projects.projectid where userid = '$uid' and sdate = '$dt'");
		while ($r = mysql_fetch_array($res))
		{
		echo "Your schedule for $dt is from ".$r['stime']." to ".$r['etime']." <br>";
		echo "You're assigned to call the ".$r['projectname']." campaign <br>";
		if ($r['agentapproved'] == 1)
			{
				echo "You have confirmed your attendance for this schedule.<br><br>";
			}
		else echo '<a href="#" onclick="acceptsched(\''.$r['schedid'].'\')">Accept</a> or <a href="#"  onclick="rejectsched(\''.$r['schedid'].'\')">Reject</a>';
		}
		exit;
	}
if ($act == 'getdetails')
	{
    $campclientres = mysql_query("SELECT * from clients where clientid = '".$isprow['clientid']."'");
    $campclient = mysql_fetch_assoc($campclientres);
    $cconres= mysql_query("SELECT client_contacts.*,members.usertype from client_contacts left join members on client_contacts.userid = members.userid where client_contacts.clientid = '".$isprow['clientid']."' and members.usertype = 'client'");
    while ($cconrow = mysql_fetch_assoc($cconres))
    {
        $contacts .= $cconrow['firstname']. ' '. $cconrow['lastname'].'<br />';
    }
        ?>
<div id="projectdetails" style="margin-left:5px">
    <h3>Client Details</h3>
    <table width="100%">
        <tr><td colspan="1" class="tablelabel">Company:</td><td colspan="3"><?=$campclient['company'];?></td></tr>
        <tr><td class="tablelabel">Phone:</td><td><?=$campclient['phone'];?></td></tr>
        <tr><td class="tablelabel">Email:</td><td><?=$campclient['email'];?></td></tr>
        <?php echo strlen($campclient['companyurl']) > 1 ? "<tr><td class=\"tablelabel\">Website:</td><td>".$campclient['companyurl']."</td></tr>" : "";?>
        <tr><td valign="top" class="tablelabel">Address:</td><td colspan="3">
        <?php echo strlen($campclient['address1']) > 1 ? $campclient['address1']."<br />" : "";?>
        <?php echo strlen($campclient['address2']) > 1 ? $campclient['address2']."<br />" : "";?>
        <?php echo strlen($campclient['city']) > 1 ? $campclient['city']."<br />" : "";?>
        <?php echo strlen($campclient['state']) > 1 ? $campclient['state']."<br />" : "";?>
        <?php echo strlen($campclient['country']) > 1 ? $campclient['country']."<br />" : "";?>
        <?php echo strlen($campclient['postcode']) > 1 ? $campclient['postcode']."<br />" : "";?>
         </td></tr>
        <tr><td valign="top" class="tablelabel">Contacts:</td><td colspan="3">
                <?php echo $contacts;?>
         </td></tr>
    </table>
    <br>
    <h3>Campaign Data</h3>
    <table width="100%">
        <tr><td style="width:150px" valign="top" class="tablelabel">Campaign Name:</td><td><strong><?=$isprow['projectname'];?></strong></td>
<tr><td valign="top" class="tablelabel">Campaign Description:</td><td><?=$isprow['projectdesc'];?></td></tr>
    </table>
    <br>
    <h3>Campaign Files</h3>
    <?php
	$fres = mysql_query("SELECT * from uploads where projectid = '$pid'");
	$nurl = "https://".$_SERVER['HTTP_HOST']."/";
        $stime = time();
        $ct = 0;
        $headers[] = 'Filename';
        $headers[] = 'Link';
        $headers[] = 'Description';
        $headers[] = 'Upload Date';
	while ($row = mysql_fetch_array($fres))
		{
            $uprow = $row;
                    $rows[$ct][1]=substr($row['filename'],0,20);
                    $securestring = $row['fileid'] . "_" . $row['projectid'] . "_" . $stime . "_" . $securekey;
                    $securehash = md5($securestring);
                    $dlink = "download.php?h=".$securehash."&f=".$uprow['fileid']."&ts=".$stime;
			$burl = urlencode($nurl.$dlink);
			if (strpos($row['filename'],".pdf") || strpos($row['filename'],".ppt")|| strpos($row['filename'],".doc"))
				{
					$c = "iframe";
					$rw = ' <a class="viewdoc '.$c.'" href="https://docs.google.com/viewer?url='.htmlentities($burl).'&embedded=true" title="'.$row['filename'].'">View</a> | <a href="'.$nurl.$dlink.'" target="_blank">Download</a>';
				}
			else if (strpos($row['filename'],".png") || strpos($row['filename'],".jpg")|| strpos($row['filename'],".bmp"))
				{
                                    $rw= '<a class="viewimg" href="'.$nurl.$dlink.'" title="'.$row['filename'].'">View</a> | <a href="'.$nurl.$dlink.'" target="_blank">Download</a>';
                                }
                        else $rw = '<a class="viewdoc" href="'.$nurl.$dlink.'" title="'.$row['filename'].'" target="_blank">Download</a><br>';
                   $rows[$ct][2] = $rw;
                  $rows[$ct][3] = '<div title="'.$uprow['description'].'" style="overflow:hidden">';
                        if (strlen($uprow['description']) < 1)
                        {
                               $rows[$ct][3] .=  "...";
                        }
                        else $rows[$ct][3] .= $uprow['description'];
                    $rows[$ct][3] .= '</div>';
                    $rows[$ct][4] .=$uprow['uploaddate'];
                    $ct++;
		}
                echo tablegen($headers,$rows,"100%",'','datatabs');
                ?>   
</div>
<?php 
/*<script>
	var aHeadNode = document.getElementsByTagName("head")[0];
	var aScript = document.createElement("script");
	aScript.type = "text/javascript";
	aScript.src = "../jquery/js/jquery-1.4.2.min.js";
	aHeadNode.appendChild(aScript);
	var aScript = document.createElement("script");
	aScript.type = "text/javascript";
	aScript.src = "../jquery/fancybox/jquery.fancybox-1.3.4.pack.js";
	aHeadNode.appendChild(aScript);
	var aScript = document.createElement("link");
	aScript.type = "text/css";
	aScript.rel = "stylesheet";
	aScript.href = "../jquery/fancybox/jquery.fancybox-1.3.4.css";
	aHeadNode.appendChild(aScript);
	setTimeout('jQuery("a.viewdoc").fancybox({width:800, height:600})',2000);
	</script> */
	exit;
	}
if ($act == 'savescript')
	{
	$scriptxml = urldecode($_POST['scriptxml']);
	$leadid = $_POST['leadid'];
	$scres = mysql_query("select leadid from scriptdata where leadid = '$leadid'");
	$num = mysql_num_rows($scres);
	if ($num != 0)
		{
		mysql_query("Update scriptdata set scriptxml = '".mysql_real_escape_string($scriptxml)."' where leadid = '$leadid'");
		}
	else {
	mysql_query("insert into scriptdata set leadid = '$leadid', scriptxml = '".mysql_real_escape_string($scriptxml)."'");
	}
	exit;
	}
if ($act == 'getcallbacks')
	{
		include "cbnew.php";
	}
if ($act == 'getcallbacks_')
	{
	$today = date("Y-m-d");
	$stres = mysql_query("select statusname from statuses where category ='agent' and projectid = '0'");
	$ct = 0;
	while ($strow = mysql_fetch_array($stres))
		{
		if ($ct != 0) $in .=",";
		$in .= "'".$strow['statusname']."'"; 
		$ct++;
		}
	$ct =0;
	//$query = "SELECT leads_done.phone, leads_done.cname, leads_done.company, dateandtime.dtime, dateandtime.note from leads_done left join dateandtime on leads_done.leadid = dateandtime.leadid where dispo ='callback' and substr(dtime,1,10) = '$today' and assigned = '$auid' and listid in (SELECT listid from lists where projects = '$pid');";
	$query = "SELECT leads_done.phone, leads_done.cfname, leads_done.clname, leads_done.leadtype, leads_done.cname, leads_done.company, dateandtime.dtime, dateandtime.note from leads_done left join dateandtime on leads_done.leadid = dateandtime.leadid where dispo in ($in) and substr(dtime,1,10) = '$today' and assigned = '$auid' and projectid = '$pid'";
	$res = mysql_query($query);
	$disp.="<p><strong style=\"line-height:23px; font-size:10pt;\">CallBacks for Today</strong></p><div style=\"overflow: hidden; background-repeat: repeat-x; width: 100%; height: 100%;\">";
	while ($row = mysql_fetch_array($res))
		{
		$type = $row['leadtype'];
		if ($type == 'b')
			{
			$naming = $row['company'];
			}
		else {
			$naming = $row['cname'];
			if (strlen($naming) < 2) 
				{
				$naming = $row['cfname']." ".$row['clname'];
				}
			}
		$disp .='<a href="#" onclick="dialcb(\''.$row['phone'].'\')"><p style="font-size:10px">'.$row['phone'].'<br>'.$naming.'</p></a>';
		}
	$disp.="</div>";
	$query2 = "SELECT leads_done.phone, leads_done.cfname, leads_done.clname, leads_done.leadtype, leads_done.cname, leads_done.company, dateandtime.dtime, dateandtime.note from leads_done left join dateandtime on leads_done.leadid = dateandtime.leadid where `status` in ('reject','failed','incomplete') and assigned = '$auid' and projectid = '$pid'";
	$res = mysql_query($query2);
	$disp.="<p><strong style=\"line-height:23px; font-size:10pt;\">Failed / Incomplete Leads </strong></p><div style=\"overflow: hidden; background-repeat: repeat-x; width: 100%; height: 100%;\">";
	while ($row = mysql_fetch_array($res))
		{
		$type = $row['leadtype'];
		if ($type == 'b')
			{
			$naming = $row['company'];
			}
		else {
			$naming = $row['cname'];
			if (strlen($naming) < 2) 
				{
				$naming = $row['cfname']." ".$row['clname'];
				}
			}
		$disp .='<a href="#" onclick="dialcb(\''.$row['phone'].'\')"><p style="font-size:10px">'.$row['phone'].'<br>'.$naming.'</p></a>';
		}
	$disp.="</div>";
	echo $disp;
	exit;
	}
if ($act == 'getsearchdetails')
	{
	savelead($_GET);
	$leadidman = $_REQUEST['leadid'];
	$mandial = 1;
	$preview = 1;
	$act = 'getinfo';
	}
if ($act == 'mandial')
	{
	$phone = trim($_REQUEST['phone']);
	$list = $_REQUEST['list'];
	$ti = $_REQUEST['leadid'];
	$lead = getlead($ti);
	callnumber($lead,$phone);
        startlog("dial");
	//var_dump($lead);
	exit;
	}
if ($act == 'cbnew')
	{
		$cbleadid = $_REQUEST['cbleadid'];
		$leadidman = $cbleadid;
		$mandial = 1;
		$act = 'getinfo';
	}
if ($act == 'startrecording')
{
    $conference = $pidrow['extension'];
    $recordfile = '/var/spool/asterisk/monitor/'.$pidrow['leadid'].'_'.time().'_OPREC.wav';
    if ($pidrow['recording'] == 0) $confserver->startrecording($pidrow['leadid'],$conference, $recordfile);
    exit;
}
if ($act == 'stoprecording')
{
    $conference = $pidrow['extension'];
    if ($pidrow['recording'] == 1) $confserver->stoprecording($pidrow['leadid'],$conference);
    exit;
}
if ($act == 'submit' ||  $act == 'exitdial' || $act == 'prevlead')
{
	$data = $_GET;
	if ($data['lid']) {
        savelead($data);
	}
    //$ami->stoprecording($pidrow['extension']);
	$ami->hangup($GET['lid']);
	//echo mysql_error();
	//exit();
	if ($act == 'submit') {
	   $act = 'next';
	} elseif ($act == 'prevlead') {
		//$nextres = mysql_query("SELECT * from hopper where projectid = '$pid' and called = 0 limit 1;");
		//$nextrow = mysql_fetch_array($nextres);
 		//$leadidman = $nextrow['leadid'];
 		//mysql_query("update hopper set called = '1' where leadid = '$leadidman'");
 		$mandial = 1;
		$preview =1;
 		$act = 'next';
	} elseif ($act == 'exitdial') {
		endlog();
		mysql_query("delete from liveusers where userid ='$auid'");
		exit;
	}
}
if ($act == 'dopause')
	{
	//sleep(1);
	//$nres = mysql_query("SELECT * from liveusers where sessionid = '$sessionid'");
	$row = $pidrow;
	if ($row['status'] != 'incall' && $row['status'] != 'dialing')
		{
		startlog('pause');
		mysql_query("update liveusers set status = 'paused' where userid ='$auid'");
		echo "paused";	
		exit;
		}
	elseif ($row['status'] == 'incall')
		{
		$act = 'getinfo';
		}
	else {
		echo "paused";
		exit;
	}
	}
if ($act == 'doactive')
	{
	//$nres = mysql_query("SELECT * from liveusers where sessionid = '$sessionid'");
	$row = $pidrow;
	if ($row['status'] != 'incall' && $row['status'] != 'dialing' && $row['status'] != 'inboundcall')
		{
		startlog('wait');
		mysql_query("update liveusers set status = 'available' where userid ='".$row['userid']."'");
		echo "false";
		}
	elseif ($row['status'] == 'incall')
		{
			$act = 'getinfo';
		}
	exit;
	}
if ($act == 'hangup')
	{
                //$ami->stoprecording($pidrow['extension']);
		$hanged = $ami->hangup($lid);
                sleep(1);
                $asres = mysql_query("SELECT status from liveusers where userid ='$auid'");
 		$asrow = mysql_fetch_array($asres);
 		$sst = $asrow['status'];
 		if ($sst == 'ended') {
	 		$aa = 0;
	 		startlog('wrap');
	 		echo 'callended';
 			}
 		elseif ($sst == 'available') {
	 		$aa = 0;
	 		echo 'checkforcalls';
	 		startlog('wait');
 			}
 		elseif ($sst =='paused') {
	 		$aa = 0;
	 		echo 'paused';
	 		startlog('pause');
 			}
               else echo $hanged;
exit;
	}
if ($act == 'next')
	{
   // $ami->stoprecording($pidrow['extension']);
 $ami->hangup($lid);
 if ($dialmode == 'predictive' || $dialmode == 'inbound' || $dialmode == 'blended')
 {
    mysql_query("update liveusers set status='available', webstatus='free', waiting= NOW() where userid ='$auid'");
    echo 'checkforcalls';
     startlog('wait');
   //mysql_query("update liveusers set leadid ='0', status='onqueue', webstatus='free', waiting= NOW(), ct = ct - 1 where userid ='$auid'");
   exit;
 }
 else
 {
// mysql_query("update liveusers set leadid ='0', status='onqueue', webstatus='free', waiting= NOW() where userid ='$auid'");
 $pidres = mysql_query("SELECT * from liveusers where userid = '$auid'");
 $pidrow = mysql_fetch_assoc($pidres);
 $pid = $pidrow['projectid'];
 if ($pidrow['status'] == 'dialing' && $pidrow['leadid'] > 0)
 {
     $record = new records($pidrow['leadid']);
        echo json_encode($record->data);
        $record->locklead();
        exit;
 }
 $nextres = mysql_query("SELECT * from hopper where projectid = '$pid' and called = 0 limit 1;");
 $nextct = mysql_num_rows($nextres);
 if ($nextct > 0)
 	{
 		$nextrow = mysql_fetch_assoc($nextres);
 		$leadidman = $nextrow['leadid'];
 		mysql_query("update hopper set called = '1' where leadid = '$leadidman'");
 		$mandial = 1;
 		$act = 'getinfo';
	}
  else 
  	{
	 	echo 'nohopper';
	  	exit;
  	}
 }
}
if ($act =='check')
	{
	//$checkres = mysql_query("SELECT userid, status from liveusers where userid = '$auid'");
	//$check = mysql_num_rows($checkres);
	$liveagent = $pidrow;
	if ($liveagent['status'] == 'incall' || $liveagent['status'] == 'inboundcall')
		{
		mysql_query("update liveusers set webstatus ='done' where userid ='$auid'");
		startlog('talk');
		$act = 'getinfo';
		}
	elseif ($liveagent['status'] == 'paused')
		{
			echo 'paused';
			exit;
		}
	else 
	{
	echo 'false';
	exit;
	}
	}
if ($act == 'checkzip')
	{
	$zip = $_REQUEST['zip'];
	$subres = mysql_query("SELECT * from postcodes where postcode = '".$zip."'");
	$ctos = 0;
	$sublist = '<select name="city" id="city"  class="box" >';
	while ($subrow = mysql_fetch_array($subres))
		{
			$suburb = $subrow['suburb'];
			$sublist .= '<option value="'.$subrow['suburb'].'">';
			$sublist .= $subrow['suburb'];
			$sublist .= '</option>';
			$ctos++;
		}
	$sublist .= '</select>';
	$ss = mysql_num_rows($subres);
	if ($ss > 1)
		{
			echo $sublist;
		}
	else echo '<input type="text" id="city" name="city" value="'.$suburb.'"  class="box" > ';
	exit;
	}
if ($act == 'getinfo')
	{
	if ($mandial ==1) 
		{
		$leadid = $leadidman;
		}
	else {
		//$getres = mysql_query("SELECT leadid,status,callerid,did,callid from liveusers where userid = '$auid'");
		$getrow = $pidrow;
		$leadid = $getrow['leadid'];
		$status = $getrow['status'];
		$callerid = $getrow['callerid'];
		$callednum = $getrow['did'];
		$callid = $getrow['callid'];
	}
	if ($status =='inboundcall')
	{
                if ($leadid == 0)
                {
                    echo 'newinboundcall';
                    exit;
		}
	}
        $record = new records($leadid);
        if ($record->epoch_callable > 0)
        {
        $record->dtime = date("Y-m-d H:i:s",$record->epoch_callable);
        }
        unset($record->data['epoch_callable']);
        if ($status =='inboundcall')
	{
            $record->data['inboundcall'] = 1;
        }
        echo json_encode($record->data);
	$record->locklead();
	if ($mandial ==1)
		{
		if (!$preview)
		{
                     startlog('dial');
		$prefixres = mysql_query("SELECT prefix,region,bcid from projects where projectid = '$pid'");
		$prefrow = mysql_fetch_row($prefixres);
		$prefix = $prefrow[0];
                $region = $prefrow[1];
                $prefbcid = $prefrow[2];
                mysql_query("Update liveusers set leadid = '".$record->leadid."', status = 'dialing', actionid = '0' where userid ='$auid' and status != 'dialing'");
                $nc = mysql_affected_rows();
                if ($nc == 1)
                {
                    mysql_query("INSERT into callman set leadid = '".$record->leadid."', phone = '".$record->phone."', status = 'originate', projectid ='".$pid."', prefix = '$prefix', start = '".time()."', mode = '1', bcid='".$prefbcid."', region ='".$region."';");
                }
		//startlog('dial');
		}
		else 
		{
                     startlog('preview');
			mysql_query("Update liveusers set leadid = '".$record->leadid."', status = 'preview' where userid ='$auid' and status != 'dialing'");
		}
		}
	//echo "INSERT into callman set leadid = '".$custrow['leadid']."', phone = '".$custrow['phone']."', status = 'originate', projectid ='".$pid."', prefix = '$prefix', start = '".time()."', mode = '1';";
	exit;
	}