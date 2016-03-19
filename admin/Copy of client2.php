<?php
session_start();
include "../dbconnect.php";
$projectid = '1';
$date = getdate();
$u = $_SESSION['uid'];
$dispo = $_REQUEST['dispo'];
if (strlen($dispo) < 1)
	{
	$dispo = 'Appointment';
	}
$keyword = $_REQUEST['keyword'];
$startdate = $_REQUEST['startdate'];
if (strlen($startdate) < 2) $startdate = date("Y-m-d");
$enddate = $_REQUEST['enddate'];
if (strlen($enddate) < 2) 
	{
	$d = date("d");
	$dd = $d + 1;
	if ($dd < 10) $dd = "0".$dd;
	$enddate = date("Y-m")."-$dd";
	}
$projectid = $_GET['proj'];
$act = $_REQUEST['acti'];
$keyw = $_REQUEST['key'];
$colm = $_REQUEST['col'];
if ($_REQUEST['type'] == 'verifier')
	{
		$usertype = 'verifier';
	}
$query = "select substring(timeofcall,1,4) as 'year', concat(memberdetails.afirst,' ',memberdetails.alast) as 'Caller', statuses.*, leads_done.*, projects.projectname, if(statuses.statustype ='dateandtime', dateandtime.dtime,'') as 'DateSet', title, qa, sic from leads_done left join projects on leads_done.projectid = projects.projectid left join statuses on statuses.statusname = leads_done.dispo left join dateandtime on leads_done.leadid = dateandtime.leadid left join memberdetails on leads_done.assigned = memberdetails.userid  where  leads_done.leadid !='0' and substring(timeofcall,1,10) >= '$startdate' and substring(timeofcall,1,10) <= '$enddate'"; 
if ($usertype == 'verifier' || $_REQUEST['acti'] == 'export')
	{
	$query = "select substring(timeofcall,1,4) as 'year', concat(memberdetails.afirst,' ',memberdetails.alast) as 'Caller', statuses.*, leads_done.*, projects.projectname, if(statuses.statustype ='dateandtime', dateandtime.dtime,'') as 'DateSet', sic, qa, title from leads_done left join projects on leads_done.projectid = projects.projectid left join statuses on statuses.statusname = leads_done.dispo left join dateandtime on leads_done.leadid = dateandtime.leadid left join memberdetails on leads_done.assigned = memberdetails.userid  where leads_done.leadid !='0' and substring(timeofcall,1,10) >= '$startdate' and substring(timeofcall,1,10) <= '$enddate'"; 
	}
if ($usertype == 'verifier')
	{
		$query.= " and status = 'approved'";
	}
if (strlen($projectid) > 0 && $projectid != '0')
	{
	$query .= " and statuses.projectid in ('$projectid','0') and leads_done.projectid = '$projectid'";
	}
 
if (strlen($dispo) > 0 && $dispo != '0')
	{
	$query .= " and leads_done.dispo = '$dispo'";
	}
if ($act == 'export')
	{
	if (strlen($colm) && strlen($keyw) && $colm == 'Appointment')
		{
		$query.= " and if(statuses.statustype ='dateandtime', dateandtime.dtime,'') like '".$keyw."%'";
		}
	
	}
$query .= " group by leadid order by timeofcall DESC limit 100 ;";
//echo $query;
$gridres = mysql_query($query);
$x=0;
$ct = mysql_num_rows($gridres);
if ($ct == 0)
	{
	$griddata[0]['Agent'] = '';
	$griddata[0]['Date'] = '';
	$griddata[0]['Phone'] = '';
	$griddata[0]['AltPhone'] = '';
	$griddata[0]['Title'] = '';
	$griddata[0]['First'] = '';
	$griddata[0]['Last'] = '';
	$griddata[0]['Name'] = '';
	$griddata[0]['Company'] = '';
	$griddata[0]['Email'] = '';
	$griddata[0]['Address1'] = '';
	$griddata[0]['Address2'] = '';
	$griddata[0]['Appointment'] = '';
	$griddata[0]['Notes'] = '';
	$griddata[0]['Disposition'] = '';
	$griddata[0]['leadid'] ='';
	$griddata[0]['Status'] ='';
	
	}
while ($gridrow = mysql_fetch_array($gridres))
	{
	$griddata[$x]['Agent'] = $gridrow['Caller'];
	$griddata[$x]['Date'] = $gridrow['timeofcall'];
	$griddata[$x]['Phone'] = $gridrow['phone'];
	$griddata[$x]['Altphone'] = $gridrow['altphone'];
	$griddata[$x]['Title'] = $gridrow['title'];
	$griddata[$x]['First'] = $gridrow['cfname'];
	$griddata[$x]['Last'] = $gridrow['clname'];
	$griddata[$x]['Name'] = $gridrow['cname'];
	$griddata[$x]['Company'] = $gridrow['company'];
	$griddata[$x]['Email'] = $gridrow['email'];
	
	if ($act == 'export')
	{
	$griddata[$x]['Agent'] = $gridrow['assigned'];
	$griddata[$x]['Address1'] = $gridrow['address1'];
	$griddata[$x]['Address2'] = $gridrow['address2'];
	$griddata[$x]['Suburb'] = $gridrow['city'];
	$griddata[$x]['State'] = $gridrow['state'];
	$griddata[$x]['PostCode'] = $gridrow['zip'];
	
	}
	$griddata[$x]['Appointment'] = $gridrow['DateSet'];
	$griddata[$x]['Notes'] = str_replace("\n"," ",$gridrow['resultcomments']);
	$griddata[$x]['Disposition'] = $gridrow['dispo'];
	$griddata[$x]['leadid'] = $gridrow['leadid'];
	$griddata[$x]['leadid2'] = $gridrow['leadid'];
	$griddata[$x]['Status'] =$gridrow['status'];
	if ($act == 'export')
	{
	$griddata[$x]['SIC'] = $gridrow['sic'];
	$griddata[$x]['QAComments'] = $gridrow['qa'];
	}
	$x++;
	}

$y = 0;
if ($griddata)
	{
	$df = array_keys($griddata[0]);
	if ($act == 'export')
		{
		
		$pro = mysql_query("SELECT * from projects where projectid = '$projectid'");
		$row = mysql_fetch_array($pro);
		$projectname = $row['projectname'];
		$filen = $year.'_'.$month.'_'.$day.'_'.$projectname;
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=".$filen.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<Table>';
  		while ($y < count($griddata))
		{
		if ($y == 0) 
		{
		echo "<tr>";
		foreach ($df as $h)
			{
			echo '<td><b>';
			echo $h;
			echo '</b></td>';
			}
		echo "</tr>";
		}
		echo'<tr>';
		$c = 0;
		$dt =$griddata[$y]; 
		foreach ($df as $f)
			{
			echo '<td>';
			echo addslashes($dt[$f]);
			echo '</td>';
			}
		echo "</tr>";
		$y++;
		}
  		echo '</Table>';
exit;
		}
	else {
	
	while ($y < count($griddata))
		{
		if ($y != 0) $data.=",";
		$data .= '[';
		$c = 0;
		$dt =$griddata[$y]; 
		foreach ($df as $f)
			{
			if ($f != 'Status')
			{
			if ($c != 0) $data.=",";
			if ($f == 'leadid')
				{
				if ($usertype == 'verifier')
					{
					$data.="'<select id=\"ver".addslashes($dt[$f])."\" name=\"ver".$dt[$f]."\" style=\"border:none\" onchange=\"togglelead(\\'".$dt[$f]."\\')\"><option>".addslashes($dt['Status'])."</option><option>failed</option><option>rejected</option><option>verified</option></select>'";
					}
				if ($usertype == 'qa')
					{
					$data.="'<select id=\"ver".addslashes($dt[$f])."\" name=\"ver".$dt[$f]."\" style=\"border:none\" onchange=\"togglelead(\\'".$dt[$f]."\\')\"><option>".$dt['Status']."</option><option>failed</option><option>incomplete</option><option>approved</option></select>'";
					}
				}
			else {
			$data .= "'".addslashes(htmlentities($dt[$f]))."'";
			}
			}
			$c++;
			}
		$data .="]";
		$y++;
		}
	}}
//get projectstring
$p = mysql_query("SELECT teams.projects from memberdetails left join teams on memberdetails.team = teams.teamname where userid = '$u'"); 
$pres = mysql_fetch_array($p);
$pr = $pres['projects'];
if (strlen($pr) > 0){
			$prcount = substr_count($pr, ';');
			$pt = 0;
			$inth = explode(";",$pr);
			foreach($inth as $ps)
				{
					if ($pt != 0) {$inthis.=",";}
					if (strlen($ps) > 0) $inthis .="'$ps'";
					$pt++;
				}
			/*while  ($pt < $prcount)
				{
				if ($pt != 0) {$inthis.=",";}
				$tisbegin = strpos($pr,';',$tisend)+1;
				$tisend = strpos($pr,';',$tisbegin);
				if ($tisend === false) {$ps[$pt] = substr($pr,$tisbegin);}
				else {$ps[$pt] = substr($pr,$tisbegin,$tisend-$tisbegin);}
				$inthis .= "'".$ps[$pt]."'";
				$pt++;
				}*/
$x = 0;
$projres = mysql_query("SELECT * from projects where projectid in ($inthis)");
while ($prow = mysql_fetch_array($projres))
	{
	$projectlist[$x]['name'] = $prow['projectname'];
	$projectlist[$x]['id'] = $prow['projectid'];
	$x++;
	}
}
$d = mysql_query("SELECT * from statuses where projectid = '$projectid' or projectid = '0' order by statusname ASC");
$x = 0;
while ($drow = mysql_fetch_array($d))
	{
	$dispolist[$x]['name'] = $drow['statusname'];
	$dispolist[$x]['id'] = $drow['statusid'];
	$x++;
	}
?>
<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="ext/resources/css/xtheme-silverCherry.css" />
<link rel="stylesheet" type="text/css" href="custom.css" />
<body onload="resizeframe()">
<script type="text/javascript" src="ext/adapter/ext/ext-base.js"></script>
<div id="content">
</div>
<div id="filter" style="visibility:hidden"><form name="filters1" id="filters1" method="get">
<span id="one">
Project&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      
  <select name="proj" onchange="changes();" id="proj" style="width:100px; font-size:9px">
<option value="0">All</option>
<?
if ($projectlist)
	{
	foreach ($projectlist as $pl)
		{
		if (strlen($projectid) > 0 && $projectid == $pl['id'])
			{
			echo '<option value="'.$pl['id'].'" selected>'.$pl['name'].'</option>';
			}
		else echo '<option value="'.$pl['id'].'">'.$pl['name'].'</option>';
		}
	}
?>
</select><br>
Disposition&nbsp;&nbsp;&nbsp;&nbsp;
<select name="dispo" id="dispo"  onchange="changes()" style="width:100px; font-size:9px">
<?
if ($dispolist)
	{
	foreach ($dispolist as $pl)
		{
		if (strlen($dispo) > 0 && $dispo == $pl['name'])
			{
			echo '<option selected>'.$pl['name'].'</option>';
			}
		else echo '<option>'.$pl['name'].'</option>';
		}
	
	}
else echo '<option>'.$dispo.'</option>';
?>
</select></span><span id="two">
Start <input name="startdate" id="startdate" type="text" size="10" maxlength="10" value="<?=$startdate;?>" />
<button id="trigger" style="background-image:url(calendar/calendar.png)">.</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
End <input name="enddate" id="enddate" type="text" size="10" maxlength="10" value="<?=$enddate;?>"><button id="trigger2" style="background-image:url(calendar/calendar.png)">.</button>
</span><span id="three">
Keyword&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="keyword" id="keyword" size="15" maxlength="100" value="<? echo $keyword; ?>" onblur="fil()"><br />
Column&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="column" id="column" onchange="fil()" onkeyup="fil()" onkeydown="fil()"><option></option>
<?
foreach($df as $k)
	{
	if ($k == $_REQUEST['column']) echo "<option selected>$k</option>";
	else echo "<option>$k</option>";
	}
?>
</select>
</span><span id="exp">
<input type="button" value="UPDATE" class="x-btn-text blist X-btn-center"/ onclick="changes()">
<? if ($usertype == 'verifier') echo '<input type="button" value="EXPORT" class="x-btn-text blist X-btn-center"/ onclick="changes2()">'; 
else echo '<input type="button" value="EXPORT" class="x-btn-text blist X-btn-center"/ onclick="changes2()">';
?>

<input type="button" value="LOGOUT" class="x-btn-text blist X-btn-center"/ onclick="window.location='<? echo $_SERVER['PHP_SELF']; ?>?act=logout'">
</span>
</form>
</div>
<div id="gridt">

</div>
<script type="text/javascript" src="ext/ext-all.js"></script>



<script>
function fildispo()
{
var d = document.getElementById('dispo');
var dis = d.options[d.selectedIndex].value;
es.filter('Disposition',dis);

}
function fil()
{
var f = document.getElementById('column');
var fld = f.options[f.selectedIndex].value;
var s = document.getElementById('keyword').value; 
if (fld.length > 0)
	{ 
	es.filter(fld, s, true);
	}
}
var checkc;
Ext.onReady(function(){
document.getElementById('filter').style.visibility="visible";

var dty = new Array(<?
echo $data;
?>);

var reader2 = new Ext.data.ArrayReader({},[<? 
	$rct = 0;
	if ($df){foreach ($df as $dff)
		{
		$rct++;
		if ($rct != 1){echo ",";}
		echo "{name:'".addslashes($dff)."'}";
		}}
?>,{name:'status'}]);
es = new Ext.data.Store({
		reader: reader2,
		data: dty
	});
grid = new Ext.grid.GridPanel({
	autoScroll: true,
	sm: new Ext.grid.RowSelectionModel(),
	cm: new Ext.grid.ColumnModel([<? 
	$rct = 0;
	if ($df){
	foreach ($df as $dff)
		{
		if ($dff != 'leadid' && $dff != 'Status')
		{if ($rct != 0){echo ",";}
		echo "{header:'".$dff."', width:85, sortable:true,dataIndex:'$dff'"; 
		if ($dff == 'Disposition') echo ", hidden:true";
		if ($dff == 'leadid2') echo ", hidden:true";
		echo "}";
		$rct++;
		}}}
?>
	,{header:'Status', dataIndex:'leadid'}]),
	store: es,
	width: 780,
	height: 300<? //$hit = $ct *30 + 50; echo $hit; ?>,
	frame:true
	
	})
grid.on('rowclick',function(ob1,index1){
	if (index1 == checkc)
		{
		
		}
	else {
	checkc = index1;
	detail.setHeight(220);
	var sgrid = grid.getSelectionModel();
	var record = sgrid.getSelected();
	var dta = record.get('leadid2');
	document.getElementById('detailframe').src='../detail2.php?id='+dta;
	}
	});
panel1 = new Ext.Panel({
	width: 200,
	height:60,
	contentEl: 'one',
	frame:true
	
	})
	panel2 = new Ext.Panel({
	width: 200,
	height:60,
	contentEl: 'two',
	frame:true
	
	})
	panel3 = new Ext.Panel({
	width: 200,
	height: 60,
	contentEl: 'three',
	frame:true
	
	})
	panel4 = new Ext.Panel({
	width: 200,
	height: 60,
	contentEl: 'exp',
	frame:true
	
	})
child = new Ext.Panel({
	width: 780,
	items:[panel1, panel2, panel3, panel4],
	frame:true,
	layout: 'table'
	
	})
detail = new Ext.Panel({
	width: 780,
	height: 1,
	frame:true,
	layout: 'table',
	html: '<iframe src="" width="780" height="220" id="detailframe" frameborder="0"></iframe>'
})
panel = new Ext.Panel({
	border: false,
	width: 800,
	height: 800,
	renderTo: 'content',
	items: [child,detail,grid],
	bodyStyle: 'padding: 10px'
	})
fil();

});
function resizeframe()
	{
		parent.funkyqa();
	}
function changes()
	{
	var proj = document.getElementById('proj');
	var projid = proj.options[proj.selectedIndex].value;
	var dispo = document.getElementById('dispo');
	var disp = dispo.options[dispo.selectedIndex].value;
	var  start = document.getElementById('startdate').value;
	var  end= document.getElementById('enddate').value;
	var  key= document.getElementById('keyword').value;
	var  col= document.getElementById('column').value;
	window.location='<? echo $_SERVER['PHP_SELF'];?>?proj='+projid+'&dispo='+disp+'&startdate='+start+'&enddate='+end;
	}
function changes2()
	{
	var proj = document.getElementById('proj');
	var projid = proj.options[proj.selectedIndex].value;
	var dispo = document.getElementById('dispo');
	var disp = dispo.options[dispo.selectedIndex].value;
	var  start = document.getElementById('startdate').value;
	var  end= document.getElementById('enddate').value;
	var  key= document.getElementById('keyword').value;
	var  col= document.getElementById('column').value;
	window.location='<? echo $_SERVER['PHP_SELF'];?>?acti=export&proj='+projid+'&dispo='+disp+'&startdate='+start+'&enddate='+end+'&key='+key+'&col='+col+'&type=<?=$usertype;?>';
	}
function togglelead(eid) {
	var p = 'ver'+eid;
	var i = document.getElementById(p).selectedIndex;
	var t = document.getElementById(p).options[i].value;
	document.getElementById('fidlist').src='../core.php?type=upst&lid='+eid+'&vl='+t;
}
</script>
<script type="text/javascript" src="calendar/zapatec.js"></script>
<script type="text/javascript" src="calendar/calendar.js"></script>
<script type="text/javascript" src="calendar/calendar-en.js"></script>
<script type="text/javascript">//<![CDATA[
      Zapatec.Calendar.setup({
        firstDay          : 1,
        showOthers        : true,
        showsTime         : true,
        timeFormat        : "12",
        electric          : false,
        inputField        : "startdate",
        button            : "trigger",
        ifFormat          : "%Y-%m-%d",
        daFormat          : "%Y/%m/%d"
      });
	  Zapatec.Calendar.setup({
        firstDay          : 1,
        showOthers        : true,
        showsTime         : true,
        timeFormat        : "12",
        electric          : false,
        inputField        : "enddate",
        button            : "trigger2",
        ifFormat          : "%Y-%m-%d",
        daFormat          : "%Y/%m/%d"
      });
    //]]></script>
<link rel="stylesheet" href="calendar/cal.css" />
<iframe src="" id="fidlist" style="visibility:hidden"> 
