<?php
session_start();
date_default_timezone_set($_SESSION['timezone']);
include "../../dbconnect.php";
include "../phpfunctions.php";
include "../../classes/classes.php";
$bcid = getbcid();
function nuform($num, $ch)
	{
		$ret = ($num / $ch) * 100;
		$ret2 = number_format($ret,2);
		return $ret2;
	}

if (!checkrights('reports'))
{
    echo "Permission Error.";
    exit;
}

$proj = getprojects($bcid);
$plist = $proj['list'];
$pp = $proj['pp'];
$projects = $proj['data'];
$plist_query = $proj['sql'];
$act = $_REQUEST['act'];
$dres = mysql_query("SELECT * from statuses where projectid = 0");
while ($drow = mysql_fetch_assoc($dres))
{
    $op .= '<option value="'.$drow['statusname'].'">'.$drow['statusname'].'</option>';
}
if ($act == 'view')
	{
		extract($_REQUEST);
                $pclients = projects::projectclients($bcid);
		$res = mysql_query("SELECT * from memberdetails where userid in (select userid from members where bcid='".$bcid."')");
		if ($projectid == 'all')
			{
				$projquery = "projectid in ($plist_query)";
			}
		else {
                    $projquery = "projectid = '$projectid'";
                    $client = $pclients[$projectid];
                    $objectives = new objectives($projectid);
                    }
		while ($r = mysql_fetch_array($res))
			{
				$agent_details[$r['userid']] = $r;
			}
		$res = mysql_query("select userid, count(*) as callcount from finalhistory where startepoch >= '".strtotime($start)."' and startepoch <= '".strtotime($end." 24:59:59")."' and $projquery and userid != 0 and userid in (select userid from members where bcid=".$bcid.") group by userid;");
		while ($r = mysql_fetch_array($res))
			{
				$agents[$r['userid']] = $r;
			}
                $actionsquery = "SELECT userid, action, sum(epochend - epochstart) as actionduration from actionlog where epochend > 0 and epochstart >= '".strtotime($start)."' and epochstart <= '".strtotime($end." 24:59:59")."' and $projquery group by userid, action";
		$res = mysql_query($actionsquery);
		while ($r = mysql_fetch_array($res))
			{
                                $agents[$r['userid']]["userid"] = $r['userid'];
				$agents[$r['userid']][$r['action']] = $r['actionduration'];
			}
                $res = mysql_query("SELECT userid,sum(answeredtime) as 'realtalk' from history where startepoch >= '".strtotime($start)."' and startepoch <= '".strtotime($end." 24:59:59")."' and $projquery group by userid");
                while ($r = mysql_fetch_array($res))
			{
				$agents[$r['userid']]['realtalk'] = $r['realtalk'];
			}
		$res = mysql_query("SELECT userid,startepoch, endepoch, answeredtime, dialedtime, dialmode,agentdisposition from finalhistory where startepoch >= '".strtotime($start)."' and startepoch <= '".strtotime($end." 24:59:59")."' and $projquery");
                while ($r = mysql_fetch_assoc($res))
			{
                            
                            if($r['dialmode'] == 'manual')
                            {
                                if ($r['dialedtime'] == 0) $dialedtime = $r['endepoch'] - $r['startepoch'];
                                else $dialedtime = $r['dialedtime'] - $r['answeredtime'];
                                $agents[$r['userid']]['dialedtime'] = $agents[$r['userid']]['dialedtime'] + $dialedtime;
                            }
			}
                //compute elapsed
                $lastepoch = strtotime($end." 24:59:59");
                $firstepoch = strtotime($start);
                $totalepoch = $lastepoch - $firstepoch;
                $dayfloat = $totalepoch / 86400; //total seconds divide by number of seconds in a day
                $denom['day'] = intval($dayfloat);
                $weekfloat = $dayfloat / 7;
                $monthfloat = $dayfloat / 30;
                $denom['week'] = intval($weekfloat);
                $denom['month'] = intval($monthfloat);
                $obs = array();
                foreach ($objectives->agents as $cobj)
                {
                    $obs[$cobj['id']]= '<td class="tableheader">'.$cobj['disposition'].'</td><td class="tableheader">Target '.$cobj['disposition'].'</td>';
                    $qt = "select userid, count(*) as contacts from finalhistory where $projquery and userid != 0 and agentdisposition = '".$cobj['disposition']."' and startepoch >= '".strtotime($start)."' and startepoch <= '".strtotime($end." 24:59:59")."'  group by userid;";
                     $res = mysql_query($qt);
                    
                    while ($r = mysql_fetch_array($res))
			{
                            $uobj[$r['userid']] = $r;
                        }
                   foreach ($agents as $ag)
                    {
                             $agents[$ag['userid']]['obj-'.$cobj['id']] = $uobj[$ag['userid']]['contacts'] > 0 ? $uobj[$ag['userid']]['contacts']:'0';
                             $target = $denom[$cobj['period']] * $cobj['target'];
                             $agents[$ag['userid']]['tar-'.$cobj['id']] = $target;
                   
                    }     
                }
                $qt = "select userid, count(*) as contacts from finalhistory where startepoch >= '".strtotime($start)."' and startepoch <= '".strtotime($end." 24:59:59")."' and $projquery and userid != 0 and agentdisposition = '$mo' group by userid;";
                $res = mysql_query($qt);
		while ($r = mysql_fetch_array($res))
			{
				$agents[$r['userid']]['contacts'] = $r['contacts'] * 1;
			}
		/*$res = mysql_query("select userid, count(*) as notinterested from finalhistory where substr(start,1,10) >= '$start' and substr(start,1,10) <= '$end' and $projquery and assigned != 0 and agentdispo in ($nidispo) group by assigned;");
		while ($r = mysql_fetch_array($res))
			{
				$agents[$r['userid']]['notinterested'] = $r['notinterested'] *1;
			}*/
                $repname = 'Agent Performance Overview for '.$projects[$projectid]['projectname'].' ('.$start.' to '.$end.')';
                if ($client > 0) $reptitle = $repname . ' - <a href="#" onclick="exporttoclient(\''.$client.'\',\''.$repname.'\')">Export to Client</a>';
                if ($projectid =='all')
                {
                    $reptitle = 'Agent Performance Overview';
                }
		$table = '<div>
		<span style="font-weight:900">'.$reptitle.'</span></div><div id="apdiv"><table>
		
		<tr>
		<td >From: </td><td >'.$start.'</td><td >To: </td><td >'.$end.'</td>
		</tr>
		<tr>
		<td class="tableheader">AgentID</td>
		<td class="tableheader">Agent Name</td>
		<td class="tableheader">Campaign Hours</td>
		<td class="tableheader">TalkTime (minutes)</td>
		<td class="tableheader">% TalkTime</td>
                <td class="tableheader">DialTime (minutes)</td>
		<td class="tableheader">% DialTime</td>
		<td class="tableheader">Wrap-upTime (minutes)</td>
		<td class="tableheader">% Wrap-upTime</td>
		<td class="tableheader">WaitTime (minutes)</td>
		<td class="tableheader">% WaitTime</td>';
		$table .= '
		<td class="tableheader">PauseTime (minutes)</td>
		<td class="tableheader">% PauseTime</td>
                <td class="tableheader">PreviewTime (minutes)</td>
		<td class="tableheader">% PreviewTime</td>
		<td class="tableheader">Connected Calls</td>
                
                ';
                foreach ($obs as $o)
                {
                    $table .= $o;
                }
                if ($mo != 'none')
                {
                    $table .= '
		<td class="tableheader">'.$mo.'</td>
		<td class="tableheader">% '.$mo.'</td>';
                }
                $table .= '</tr>';
		$ct = 1;
		foreach ($agents as $agent)
			{
                            if ($agent['userid'] > 0)
                            {
                                if (!$agent['contacts']) $agent['contacts'] = 0;
                                $totaltalk = $agent['dial'] + $agent['talk'];
                                $realtalk = $agent['realtalk'];
                                $realdial = $agent['dialedtime'];
				$temptalk = number_format($realtalk /60,2,".",'');
                                $tempdial = number_format($realdial /60,2,".",'');
                                $tempwrap = number_format($agent['wrap'] /60,2,".",'');
                                $temppause = number_format($agent['pause'] /60,2,".",'');
                                $prevtime = number_format($agent['preview'] /60,2,".",'');
                                $totalwait = $agent['wait'];
                                $tempwait = number_format($totalwait /60,2,".",'');
				$chours = $temptalk + $tempdial + $temppause + $tempwait + $tempwrap + $prevtime;
                                $temphours = number_format($chours /60,2,".",'');
				$a = $agent_details[$agent['userid']];
				$class = 'tableitem_';
				if ($ct % 2) $class = 'tableitem';
				$table .= '<tr>';
				$table .= '<td class="'.$class.'">'.$agent['userid'].'</td>';
				$table .= '<td class="'.$class.'">'.$a['afirst'].' '.$a['alast'].'</td>';
				$table .= '<td class="'.$class.'">'.$temphours.'</td>';
				$table .= '<td class="'.$class.'">'.$temptalk.'</td>';
                                $table .= '<td class="'.$class.'">'.nuform($temptalk,$chours).'%</td>';
				$table .= '<td class="'.$class.'">'.$tempdial.'</td>';
                                $table .= '<td class="'.$class.'">'.nuform($tempdial,$chours).'%</td>';
                                $table .= '<td class="'.$class.'">'.$tempwrap.'</td>';
				$table .= '<td class="'.$class.'">'.nuform($tempwrap,$chours).'%</td>';
				$table .= '<td class="'.$class.'">'.$tempwait.'</td>';
				$table .= '<td class="'.$class.'">'.nuform($tempwait,$chours).'%</td>';
				//$table .= '<td class="'.$class.'">'.$agent['dial'].'</td>';
				//$table .= '<td class="'.$class.'">'.nuform($agent['dial'],$chours).'%</td>';
				$table .= '<td class="'.$class.'">'.$temppause.'</td>';
				$table .= '<td class="'.$class.'">'.nuform($temppause,$chours).'%</td>';
                                $table .= '<td class="'.$class.'">'.$prevtime.'</td>';
				$table .= '<td class="'.$class.'">'.nuform($prevtime,$chours).'%</td>';
				$table .= '<td class="'.$class.'">'.$agent['callcount'].'</td>';
                                foreach ($objectives->agents as $cobj)
                                {
                                     $table .= '<td class="'.$class.'">'.$agent['obj-'.$cobj['id']].'</td>';
                                      $table .= '<td class="'.$class.'">'.$agent['tar-'.$cobj['id']].'</td>';
                                }
                                if ($mo != 'none')
                                {
                                    $table .= '<td class="'.$class.'">'.$agent['contacts'].'</td>';
                                    $table .= '<td class="'.$class.'">'.nuform($agent['contacts'],$agent['callcount']).'%</td>';
                                }
                                $table .= '</tr>';
				$ct++;
                            }
			}
		if ($act2 == 'excel')
			{
				createdoc("excel",$table);
			}
                        //var_dump($actionsquery);
                $table .= '</table></div>';
		echo $table;
		exit;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script type="text/javascript" src="../../jquery/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../jquery/js/jquery-ui-1.8.10.custom.min.js"></script>
<script type="text/javascript" src="../../jquery/js/blockui.js"></script>
<script type="text/javascript" src="../../jquery/js/pleasewait.js"></script>
<link href="../../jquery/css/redmond/jquery-ui-1.8.10.custom.css" rel="stylesheet" type="text/css" />
<link href="cstyle.css" rel="stylesheet" type="text/css" />
<style>
a.repbutton {
    border: 1px solid #16A6BD;
    color: #16A6BD;
    float: left;
    padding: 10px;
    width: 120px;
    background:#E0FFFF;
    border-radius:10px;
    text-decoration: none;
    font-size:11pt;
}
</style>
</head>

<body>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">Agent Performance Report</div>
</div>
<hr />
<div id="query">
<form action="leads.php" method="get" name="filterform" id="filterform">
<input type="hidden" name="act" value="dosearch" />
<table width="929" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="80">Campaign</td>
    <td width="345"><select name="projectid" id="projectid" onchange="updatemo()">
    <option value="all" selected="selected">All</option>
      <?=$plist;?>
    </select></td>
    <td width="80">&nbsp;</td>
    <td width="344">&nbsp;</td>
  </tr>
  <tr>
    <td>Date Start</td>
    <td><input type="text" name="start" class="dates" id="start" /></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>Date End</td>
    <td><input type="text" name="end" class="dates" id="end"/></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>Measured Outcome</td>
    <td><select name="mo" id="mo">
            <option value="none">None</option>
            <?=$op;?>
        </select></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
  	<td colspan="4" align="left"><a href="#" onclick="viewrep()">View</a> | <a href="#" onclick="exportrep()">Export</a></td>
  </tr>
</table>
</form>
</div>
<div id="results">
<?=$table;?>
</div>
</div>
</body>
</html>
<script>
$(function() {
		$( ".dates" ).datepicker({ dateFormat: 'yy-mm-dd' });
	});

			
document.title = window.name;
function viewrep()
{

	var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
        var mo = $("#mo").val();
	$.ajax({
  		url: "agentperformance.php?act=view&projectid="+proj+"&start="+start+"&end="+end+"&mo="+mo,
  		success: function(data){
    	 $('#results').html(data);
  	}
	});
}
function exportrep()
{

	var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
        var mo = $("#mo").val();
	var url =  "agentperformance.php?act=view&act2=excel&projectid="+proj+"&start="+start+"&end="+end+"&mo="+mo;
	window.open(url);
	
}
function exporttoclient(cid,repname)
{
    var body = $("#apdiv").html();
    var texts = encodeURI(body);
    texts = encodeURIComponent(texts);
    $.ajax({
        url: '../admin.php?act=savereport&cid='+cid+'&rname='+repname,
        type: 'POST',
        data: 'tex='+texts,
        success: function(){
            alert('Client Report Generated!');
        }
    })
}
function updatemo()
{
    var mopid = $("#projectid").val();
    $.ajax({
        url: '../admin.php?act=getstatusbypid',
        type: 'POST',
        data: 'pid='+mopid,
        success: function(resp){
            $("#mo").html(resp);
        }
    })
}
</script>