<?php
include_once "../dbconnect.php";
error_reporting(1);
$pid = $_GET['projectid'];

$campres = mysql_query("select * from projects where projectid = '$pid'");
$project = mysql_fetch_array($campres);
$datares = mysql_query("select listid, projects from lists where projects = '$pid' and active = '1'");
$inct = 0;
while ($datarow = mysql_fetch_array($datares))
	{
		if ($inct >0 ) $inlists .= ",";
		$inlists .= "'".$datarow['listid']."'";
		$lists[$datarow['listid']]= $datarow;
		$inct++;
	}
$salesres = mysql_query("select statusname from statuses where statustype = 'sale' or statusname like '%appointment%'");
$sct = 0;
while ($srow = mysql_fetch_array($salesres))
	{
		if ($sct > 0) $salesdispo .= ",";
		$salesdispo .= "'".$srow['statusname']."'";
		$sct++;
	}
$leads['approved'] = 0;
$leads['assigned'] = 0;
$leads['verified'] = 0;
$tarquery = "select status, count(*) as 'ld' from leads_done where dispo in ($salesdispo) and status in ('assigned','approved','verified') and projectid = '$pid' group by status";
$targres = mysql_query($tarquery);
				while ($trow = mysql_fetch_array($targres))
					{
						$leads[ $trow['status']] = $trow['ld'];
					}
$ft = 0;
$rec2res = mysql_query("select count(*) as 'rec2' from leads_raw where dispo in ('ANSMAC','drop','systemna') and hopper = 0 and listid in ($inlists)  and listid not like '%agentgenerated%'");
while ($rec2row = mysql_fetch_array($rec2res))
	{
		$list['available'] = $rec2row['rec2'];
	}
$recyc1 = mysql_query("select count(*) as 'rec2' from leads_raw where dispo in ('ANSMAC','drop','systemna') and hopper = 1 and listid in ($inlists)  and listid not like '%agentgenerated%'");
while ($recrow = mysql_fetch_array($recyc1))
	{
		$list['recyclable'] = $recrow['rec2'];
	}
$recallres = mysql_query("select count(*) as 'recallable' from leads_done where dispo in (select statusname from statuses where category = 'callable')  and hopper = 0  and projectid = '$pid'");
while ($recallrow = mysql_fetch_array($recallres))
	{
		$list['available'] = $list['available'] + $recallrow['recallable'];
	}
$recallres2 = mysql_query("select count(*) as 'recallable' from leads_done where dispo in (select statusname from statuses where category = 'callable')  and hopper = 1  and projectid = '$pid'");
while ($recallrow = mysql_fetch_array($recallres2))
	{
		$list['recyclable'] = $list['recyclable'] + $recallrow['recallable'];
	}
$listun = array();
$unres = mysql_query("select count(*) as 'unused' from leads_raw where dispo = 'NEW' and hopper = 0 and listid in ($inlists)");	
while ($unrow = mysql_fetch_array($unres))
	{
		$list['unused'] = $unrow['unused'];
	}


$activecamp = '<table width="300" cellspacing="0" cellpadding="0" style="border: 1px solid rgb(179, 179, 179);">
<tr>
<td colspan="2" class="center-title heading"> Data Stats for '.$project['projectname'].'</td></tr>
<tr><td class="center-title">Unused</td><td class="center-title">'.$list['unused'].'</td></tr>
<tr><td class="center-title">Available</td><td class="center-title">'.$list['available'].'</td></tr>
<tr><td class="center-title">Recyclable</td><td class="center-title">'.$list['recyclable'].'</td></tr>
<tr><td class="center-title">Leads Generated</td><td class="center-title">'.$leads['assigned'].'</td></tr>
<tr><td class="center-title">Leads Approved</td><td class="center-title">'.$leads['approved'].'</td></tr>
<tr><td class="center-title">Leads Released</td><td class="center-title">'.$leads['verified'].'</td></tr>
</table>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>BlueCloud Australia</title>
<style>
td.center-title {
border-bottom:1px solid #B3B3B3;
border-left:1px solid #B3B3B3;
color:#666666;
font-size:8pt;
font-weight:bold;
line-height:12pt;
padding:2px;
text-align:center;
}
.heading {
background-color: #0CF;
}
.datas {
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
font-size: 8px;
}
.x-window-body {
    background-color:#FFF;
}
body {
font-family:Tahoma;
font-size:8pt;
}
.sel {
	width: 75px;
	height: 15px;
	font-size:10px;
	border:none;
	color:#333;
	background:none;
}
a:link {
	text-decoration:none;
	border:none;
}
img {
	border:none;
}
</style>
<script>

function ref()
{
	window.location=window.location;
}
</script>
<body style="background-color:#FFF">
<?php
echo '
<div id="activecamp"  align="left" style="width:300px; padding-right:50px; position:relative; float:left">'.$activecamp.'</div>

';

