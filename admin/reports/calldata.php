<?php
session_start();
date_default_timezone_set($_SESSION['timezone']);
include "../../dbconnect.php";
include "../phpfunctions.php";
$bcid = getbcid();
$projects = getprojects($bcid);
$plist = $projects['list'];
$act = $_REQUEST['act'];
if (!checkrights('reports'))
{
    echo "Permission Error.";
    exit;
}
if ($act == 'view' || $act== 'export')
	{
		extract($_REQUEST);
                $agentname[0] = 'Not Assigned';
                $userq = "SELECT m.userid, md.afirst,md.alast from members m left join memberdetails md on m.userid = md.userid where bcid = $bcid";
		$useres = mysql_query($userq);
                while ($urow = mysql_fetch_assoc($useres))
                {
                    $agentname[$urow['userid']] = $urow['afirst']. ' '. $urow['alast'];
                }
                $q = "SELECT finalhistory.*,lr.listid,cl.company as 'clientname' from finalhistory left join leads_raw lr on finalhistory.leadid = lr.leadid left join projects pr on finalhistory.projectid = pr.projectid left join clients cl on pr.clientid = cl.clientid where finalhistory.projectid ";
		if ($projectid == 'all')
			{
				$q.= " in (".$projects['sql'].") ";
			}
		else 
			{
				$q.= " = '$projectid' ";
			}
		$q.= " and finalhistory.startepoch >= '".strtotime($start)."' and finalhistory.startepoch <= '".strtotime($end. " 24:59:59")."' and finalhistory.bcid=".$bcid." group by callid ";
		$headers[] = 'Client';
                $headers[] = 'Campaign';
                $headers[] = 'ListId';
                
		$headers[] = 'Date';
		$headers[] = 'Phone';
		$headers[] = 'Started';
		$headers[] = 'Duration';
		$headers[] = 'Status';
		$headers[] = 'Disposition';
                $headers[] = 'Agent';
		$res = mysql_query($q);
		$ct = 0;
		while ($r = mysql_fetch_assoc($res))
			{
                                $rows[$ct]['1'] = $r['clientname'];
				$rows[$ct]['1a'] = $projects['data'][$r['projectid']]['projectname'];
                                $rows[$ct]['1b'] = $r['listid'];
				$rows[$ct]['2'] = date("Y-m-d",$r['startepoch']);
				$rows[$ct]['3'] = $r['phone'];
				$rows[$ct]['4'] = date("H:i:s",$r['startepoch']);
				$rows[$ct]['5'] = $r['endepoch'] - $r['startepoch'];
				$rows[$ct]['6'] = $r['systemdisposition'];
				$rows[$ct]['7'] = $r['agentdisposition'];
                                $rows[$ct]['8'] = $agentname[$r['userid']];
				$ct++;
			}
		if ($act == 'export')
			{
				$table = tablegen($headers,$rows,"880");
				createdoc('excel',$table);
			}
		else echo tablegen($headers,$rows,"100%");
		//echo $q;
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

<link href="../../jquery/css/redmond/jquery-ui-1.8.10.custom.css" rel="stylesheet" type="text/css" />
<link href="cstyle.css" rel="stylesheet" type="text/css" />
<style>
.ui-widget {
	font-family: Tahoma;
	font-size:8pt;
}
</style>
</head>

<body>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">Call Data Report</div>

</div>
<hr />
<div id="query">
<form action="campperformance.php" method="get" name="filterform" id="filterform">
<input type="hidden" name="act" value="dosearch" />
<table width="929" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="80">Campaign</td>
    <td width="345"><select name="projectid" id="projectid">
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
<script>
$(function() {
		$( ".dates" ).datepicker({ dateFormat: 'yy-mm-dd' });
	});
function exportrep()
{

	var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
	window.location = "calldata.php?act=export&projectid="+proj+"&start="+start+"&end="+end;
	
}
function viewrep()
{

	var proj = document.getElementById('projectid').value;
	var start = document.getElementById('start').value;
	var end = document.getElementById('end').value;
	$.ajax({
  		url: "calldata.php?act=view&projectid="+proj+"&start="+start+"&end="+end,
  		success: function(data){
    	 $('#results').html(data);
  	}
	});
}
</script>