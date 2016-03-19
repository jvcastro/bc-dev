<?php
include "../../dbconnect.php";
include "../phpfunctions.php";
$bcid = getbcid();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script type="text/javascript" src="../../jquery/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../../jquery/js/jquery-ui-1.8.10.custom.min.js"></script>
<script type="text/javascript" src="../../jquery/js/blockui.js"></script>
<script type="text/javascript" src="../../jquery/js/pleasewait.js"></script>
<link href="../../jquery/css/redmond/jquery-ui-1.8.10.custom.css" rel="stylesheet" type="text/css" />
<link href="cstyle.css" rel="stylesheet" type="text/css" />
<style>
.ui-widget {
	font-family: Tahoma;
	font-size:8pt;
}
#repcontent {
	height: 400px;
	overflow:auto;
}
</style>
</head>

<body>
<div id="container">
<div id="header">
<img src="../images/bclogo-small.png" />
<div id="reporttitle">Agent Timesheet Report</div>
</div>
<hr />
<div id=cal1 style="float:left; position:relative; left:10px"><b> Start: <input name="startdate" id="startdate" type="text" size="10" maxlength="10" value="" style="width: 100px; font-size:9px; position:relative;" class="dates">
</div>
<div id=cal2  style="float:left; position:relative; left:50px"><b> End: <input name="enddate" id="enddate" type="text" size="10" maxlength="10" value="" style="width: 100px; font-size:9px; position:relative;" class="dates">
</div>
<?php

$ageres = mysql_query("SELECT members.userid, memberdetails.afirst, memberdetails.alast from members left join memberdetails on members.userid = memberdetails.userid where usertype = 'user' and bcid = '$bcid' order by active DESC");
while ($agrow = mysql_fetch_array($ageres))
	{
		$ops .= '<option value="'.$agrow['userid'].'">'.$agrow['afirst'].' '.$agrow['alast'].'</option>';
	}
$tdiv .= '<div style="clear:both; position:relative; top:20px; left:10px"> <b> Select Agent: </b><select style="width: 100px; font-size:9px;" name="agentid" id="agentid"><option value="all">All</option>'.$ops.'</select></div>';
$tdiv .= '<div style="position:relative; top:40px; left:10px"><a href="#" onclick="gettimesheet()">View</a> : <a href="#">Export</a>';
echo $tdiv;
echo "<div id=\"repcontent\"><div>";
?>
<script>
$(function() {
		$( ".dates" ).datepicker({ dateFormat: 'yy-mm-dd' });
	});
function gettimesheet()
	{
	var agent = document.getElementById('agentid');
	var agentid = agent.options[agent.selectedIndex].value;
	var start = document.getElementById('startdate').value;
	var end = document.getElementById('enddate').value;
	$.ajax({
  		url: "../admin.php?act=gettimesheet&agentid="+agentid+"&start="+start+"&end="+end,
  		success: function(data){
    	 $('#repcontent').html(data);
  	}
	});	
	}

</script>
