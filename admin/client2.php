<?php
session_start();
include "../dbconnect.php";
include "phpfunctions.php";
$bcid = getbcid();
$date = getdate();
error_reporting(0);
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
if ($act == 'exportdoc')
	{
		$cl = mysql_query("SELECT clientid from projects where projectid = '$projectid' and bcid= '$bcid'");
		$clr = mysql_fetch_row($cl);
		$clientid = $clr[0];
	}
$keyw = $_REQUEST['key'];
$colm = $_REQUEST['col'];
$x = 0;
$projres = mysql_query("SELECT * from projects where bcid = '$bcid' order by projectname ASC");
while ($prow = mysql_fetch_array($projres))
	{
	$projectlist[$x]['name'] = $prow['projectname'];
	$projectlist[$x]['id'] = $prow['projectid'];
	if ($x > 0) $projectstrings .= ",";
	$projectstrings .= "'".$prow['projectid']."'";
	$x++;
	}

if ($_REQUEST['type'] == 'verifier')
	{
		$usertype = 'verifier';
	}
else $usertype = 'qa';
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
else {
	$query .= " and leads_done.projectid in ($projectstrings) ";
}
if (strlen($dispo) > 0 && $dispo != '0')
	{
	$query .= " and leads_done.dispo = '$dispo'";
	}
if ($act == 'export' || $act == 'exportdoc')
	{
	if (strlen($colm) && strlen($keyw) && $colm == 'Appointment')
		{
		$query.= " and if(statuses.statustype ='dateandtime', dateandtime.dtime,'') like '".$keyw."%'";
		}
	
	}
$query .= " group by leadid order by timeofcall, status DESC ;";
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
	$griddata[0]['Comments'] = '';
	$griddata[0]['Mobile'] = '';
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
	$griddata[$x]['Name'] = str_replace("\n"," ",$gridrow['cname']);
	$griddata[$x]['Company'] = str_replace("\n"," ",$gridrow['company']);
	$griddata[$x]['Email'] = $gridrow['email'];
	$griddata[$x]['Mobile'] = $gridrow['mobile'];
	$griddata[$x]['Comments'] = str_replace("\n"," ",$gridrow['comments']);
	
	if ($act == 'export' || $act == 'exportdoc')
	{
	$griddata[$x]['Agent'] = $gridrow['assigned'];
	$griddata[$x]['Address1'] = $gridrow['address1'];
	$griddata[$x]['Address2'] = $gridrow['address2'];
	$griddata[$x]['Suburb'] = $gridrow['city'];
	$griddata[$x]['State'] = $gridrow['state'];
	$griddata[$x]['Postcode'] = $gridrow['zip'];
	$griddata[$x]['Mobile'] = $gridrow['mobile'];
	$griddata[$x]['SIC'] = $gridrow['sic'];
	$griddata[$x]['Industry'] = $gridrow['industry'];
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
	$griddata[$x]['QAComments'] = str_replace("\n"," ",$gridrow['qa']);
	}
	$x++;
	}

$y = 0;
if ($griddata)
	{
	$df = array_keys($griddata[0]);
	if ($act == 'exportdoc')
		{
		$temres = mysql_query("SELECT pagebody from leadpage where clientid = '$clientid'");
		$temrow = mysql_fetch_row($temres);
		$template = $temrow[0];
		$pro = mysql_query("SELECT * from projects where projectid = '$projectid' and bcid = '$bcid'");
		$row = mysql_fetch_array($pro);
		$projectname = $row['projectname'];
		$filen = $startdate.'_to_'.$enddate.str_replace(" ","_",$projectname).'.doc';
		header("Content-type: application/vnd.ms-word");
		header("content-disposition: attachment;filename=$filen");
		header("Pragma: no-cache");
		header("Expires: 0");
		while ($y < count($griddata))
			{
				$tempotemp = $template;
				foreach ($df as $fld)
					{
						$tempotemp = str_replace("[$fld]","<b>".$griddata[$y][$fld]."</b>",$tempotemp);
					}
				$ldd = $griddata[$y]['leadid'];
				$scriptres = mysql_query("SELECT scriptxml from scriptdata where leadid = '$ldd'");
				$scriptrow = mysql_fetch_array($scriptres);
				$xml = $scriptrow['scriptxml'];
				$raw = explode("</",$xml);
				foreach ($raw as $d)
					{
						$st_label = strpos($d,"<") + 1;
						$end_label = strpos($d,">",$st_label);
						$len_label = strlen($d);
						$label = substr($d,$st_label,$end_label - $st_label);
						$st_val = $end_label + 1;
						$value = substr($d,$st_val);
						$tempotemp = str_replace("[$label]","<b>$value</b>",$tempotemp);
					}
				
				$finaltemplate .=$tempotemp;
				$y++;
			}
		$finaltemplate = preg_replace("(\[.*\])"," ",$finaltemplate);
		echo '<html><body>';
		echo $finaltemplate;
		echo '</body></html>';
		exit;
		}
	if ($act == 'export')
		{
		
		$pro = mysql_query("SELECT * from projects where projectid = '$projectid' and bcid = '$bcid'");
		$row = mysql_fetch_array($pro);
		$projectname = $row['projectname'];
		$filen = $year.'_'.$month.'_'.$day.'_'.$projectname;
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=".$filen.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		$tb .= '<Table>';
  		while ($y < count($griddata))
		{
		if ($y == 0) 
		{
		$tbh .= "<tr>";
		foreach ($df as $h)
			{
			$tbh .= '<td><b>';
			$tbh .= $h;
			$tbh .= '</b></td>';
			}

		}
		$tbd .= '<tr>';
		$c = 0;
		$dt =$griddata[$y]; 
		foreach ($df as $f)
			{
			$tbd .= '<td>';
			$tbd .= addslashes($dt[$f]);
			$tbd .= '</td>';
			}
		$ldd = $dt['leadid'];
		$scriptres = mysql_query("SELECT scriptxml from scriptdata where leadid = '$ldd'");
		$scriptrow = mysql_fetch_array($scriptres);
		$xml = $scriptrow['scriptxml'];
		$raw = explode("</",$xml);
		foreach ($raw as $d)
			{
			$st_label = strpos($d,"<") + 1;
			$end_label = strpos($d,">",$st_label);
			$len_label = strlen($d);
			$label = substr($d,$st_label,$end_label - $st_label);
			$st_val = $end_label + 1;
			$value = substr($d,$st_val);
			$tbd .= '<td>'.$value.'</td>';
			if ($y == 0) $tbh .= '<td><b>'.$label.'</b></tD>';
			}
		$tbd .= "</tr>";
		$y++;
		}
		$tbh .= "</tr>";
		$tb .= $tbh;
		$tb .= $tbd;
  		$tb .= '</Table>';
		echo $tb;
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
				if ($dt['Status'] != 'assigned')
						{
						$cst = $dt['Status'];
						}
				else $cst = '';
				if ($usertype == 'verifier')
					{
					
					$data.="'<select id=\"ver".addslashes($dt[$f])."\" name=\"ver".$dt[$f]."\" style=\"border:none\" onchange=\"togglelead(\\'".$dt[$f]."\\')\"><option>".addslashes($cst)."</option><option value=\"failed\">Failed</option><option>rejected</option><option value=\"verified\">Release</option></select>'";
					
					}
				if ($usertype == 'qa')
					{
					$data.="'<select id=\"ver".addslashes($dt[$f])."\" name=\"ver".$dt[$f]."\" style=\"border:none\" onchange=\"togglelead(\\'".$dt[$f]."\\')\"><option>".$cst."</option><option value=\"failed\">Failed</option><option value=\"incomplete\">Incomplete</option><option value=\"approved\">Approved</option></select>'";
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


$d = mysql_query("SELECT * from statuses where projectid = '$projectid' or projectid = '0' order by statusname ASC");
$x = 0;
while ($drow = mysql_fetch_array($d))
	{
	$dispolist[$x]['name'] = $drow['statusname'];
	$dispolist[$x]['id'] = $drow['statusid'];
	$x++;
	}
?>
<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css">
<link rel="stylesheet" type="text/css" href="custom.css">
<script type="text/javascript" src="../jquery/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="../jquery/js/jquery-ui-1.8.12.custom.min.js"></script>
<link href="../jquery/css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet" type="text/css" />
<style>
.calbutton {
	width: 10px;
	height: 15px;
	top:4px;
}
</style>
<body onLoad="resizeframe()">
<script type="text/javascript" src="ext/adapter/ext/ext-base.js"></script>
<div id="content">
</div>
<div id="filter" style="visibility:hidden"><form name="filters1" id="filters1" method="get">
<span id="one">
Project&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      
  <select name="proj" onChange="changes();" id="proj" style="width:100px; font-size:9px">
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
Start <input name="startdate" id="startdate" type="text" size="10" maxlength="10" value="<?=$startdate;?>" style="width: 100px; font-size:9px; position:relative;"><button id="trigger" style="background-image:url(calendar/calendar.png); position:relative;"  class="calbutton">.</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
End <input name="enddate" id="enddate" type="text" size="10" maxlength="10" value="<?=$enddate;?>" style="width: 100px; font-size:9px;  position:relative; left:6px"><button  class="calbutton" id="trigger2" style="background-image:url(calendar/calendar.png); position:relative; left:6px">.</button>
</span><span id="three">
Keyword&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="keyword" id="keyword" size="15" maxlength="100" value="<? echo $keyword; ?>" onBlur="fil()"><br>
Column&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="column" id="column" onChange="fil()" onkeyup="fil()" onkeydown="fil()"><option></option>
<?
foreach($df as $k)
	{
	if ($k == $_REQUEST['column']) echo "<option selected>$k</option>";
	else echo "<option>$k</option>";
	}
?>
</select>
</span>
</form>
</div>
<div id="gridt">

</div>
<script type="text/javascript" src="ext/ext-all.js"></script>
<script language="javascript" type="text/javascript">
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
	width: 984,
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
	alid = dta;
	detail.load({url: 'detail.php', params:'id='+dta+'&utype=<?=$usertype;?>', callback: detailload});
	//document.getElementById('detailframe').src='../detail2.php?id='+dta;
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
	frame:true,
	padding: 5,
	layout: 'table',
	layoutConfig: {columns: 2},
	items : [ new Ext.Button({text: 'Update', handler: changes,  minWidth: 75}), new Ext.Button({text: 'Export', handler: changes2, minWidth: 75}), new Ext.Button({text: 'Leadpage', handler: changes3, minWidth: 75})]
	
	})
child = new Ext.Panel({
	width: 984,
	items:[panel1, panel2, panel3, panel4],
	frame:true,
	layout: 'table'
	
	})
detail = new Ext.Panel({
	width: 984,
	height: 1,
	frame:true,
	layout: 'table',
	//html: '<iframe src="" width="780" height="220" id="detailframe" frameborder="0"></iframe>'
	autoLoad: ''
})
panel = new Ext.Panel({
	border: false,
	width: 994,
	autoHeight: true,
	renderTo: 'content',
	items: [child,detail,grid],
	bodyStyle: 'padding: 10px',
	
	})
fil();

});
function urlencode(str) {
return escape(str).replace(/\+/g,'%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
}
var bl = false;
function blto()
	{
	bl = true;
	butto();
	}
function butto()
	{
	if (bl == true)
		{
		document.getElementById('sbbb').disabled = false;
		}
	if (bl == false)
		{
		document.getElementById('sbbb').disabled = true;
		}
	}
function dchanges(fd)
	{
	if (bl == true)
	{
	
	var lid = document.getElementById('leadiddet').value;
	var ty = document.getElementById(fd);
	var vl = ty.value;
	if (ty.type == 'text' || ty.type == 'textarea')
		{
		vl = ty.value;
		}
	if (fd == 'dispo' || fd == 'qdispo')
		{
		vl = ty.options[ty.selectedIndex].value;
		}
	if (fd == 'status')
		{
		vl = ty.options[ty.selectedIndex].value;	
		}
	if (fd == 'qdispo')
		{
			fd = 'dispo';
		}
	var tsrc='admin.php?act=ul&lid='+lid+'&fld='+fd+'&val='+urlencode(vl);
	if (xmlhttp) 
		{
		delete xmlhttp;
		}
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open('GET', tsrc); 
	xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
	xmlhttp.send(null); 
	}
	butto();
	bl=false;
	}
var scriptdata = '';
var disdata = '';
function loadxmldata()
	{
	var url = 'admin.php';
	var data = 'act=getx&leadid='+alid;
	var http = getHTTPObject();
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
					{
					scriptdata = http.responseXML;
					var elementcount = scriptdata.childNodes[0].childNodes.length;
					var cd = 0;
					var sc = scriptdata.childNodes[0];
					var current = scriptdata.childNodes[0].childNodes[0];
					for (cd=0;cd<elementcount;cd++)
						{						
						var fldname = current.nodeName;
						alert(fldname);
						alert(nex.nodeName);
						var val = scriptdata.getElementsByTagName(fldname)[0].childNodes[0].nodeValue
						//var val = scriptdata.childNodes[0].childNodes[0+cd].childNodes[0].nodeValue;
						disdata +='<div style="clear:both">';
						disdata +='<div class="label" style="width:200px">';
						disdata +=fldname+' : ';
						disdata +='</div>';
						disdata +='<div class="val">';
						disdata +=val;
						disdata +='</div>';
						disdata +='</div>';
						cd++;
						current = current.nextSibling;
						}
					document.getElementById('customdata').innerHTML=disdata;
					var test = scriptdata.elements[2];
					Ext.Msg.alert(test);
					}
	};
	http.send(data);
	
	}
var ccon = 0;
function changeoption(field,ob,lid)
	{
	if (ccon == 0)
	{
		ccon = 1;
		if (ob.substring(0,7) == 'obr0123')
			{
				var label = ob.substring(7);
				var value = field.innerHTML;
				field.innerHTML = '<input type=text value="'+value+'" onblur="updatec(this,\''+label+'\',\''+field.id+'\',\''+lid+'\');">';
			}
	}
	}
function updatec(field,label,tdiv,lid)
	{
		var value = field.value;
		
		http.open("GET", url + '?act=updatecustom&label='+label+'&val='+value+'&lid='+lid, true);
		http.onreadystatechange = function () {
		if (http.readyState == 4)
			{
			var resp = http.responseText;
			document.getElementById(tdiv).innerHTML=resp;
			ccon = 0;
			http.onreadystatechange = function(){};
			}
		else if(http.readyState == 1) {document.getElementById(tdiv).innerHTML = "Saving...";}
		};
		http.send(null);
	}
function detailload()
	{
	var mp = new Ext.TabPanel({
		activeTab: 0,
		frame: true,
		bodyStyle: 'padding: 10px',
		height: 220,
		width: 974,
		autoScroll: true,
		items: [{title: 'Details', contentEl:'details', padding: 10},{title: 'Results', contentEl: 'results'},{title: 'Recordings', contentEl: 'recordings', autoScroll: true},{title: 'Custom Data', contentEl: 'customdata', autoheight: true, width: 500}],
		renderTo: 'cont',
		script: true
		});
	}
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
	window.location='<? echo $_SERVER['PHP_SELF'];?>?type=<?=$usertype;?>&proj='+projid+'&dispo='+disp+'&startdate='+start+'&enddate='+end;
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
	window.location='<? echo $_SERVER['PHP_SELF'];?>?type=<?=$usertype;?>&acti=export&proj='+projid+'&dispo='+disp+'&startdate='+start+'&enddate='+end+'&key='+key+'&col='+col+'&type=<?=$usertype;?>';
	}
function changes3()
	{
	var proj = document.getElementById('proj');
	var projid = proj.options[proj.selectedIndex].value;
	var dispo = document.getElementById('dispo');
	var disp = dispo.options[dispo.selectedIndex].value;
	var  start = document.getElementById('startdate').value;
	var  end= document.getElementById('enddate').value;
	var  key= document.getElementById('keyword').value;
	var  col= document.getElementById('column').value;
	window.location='<? echo $_SERVER['PHP_SELF'];?>?type=<?=$usertype;?>&acti=exportdoc&proj='+projid+'&dispo='+disp+'&startdate='+start+'&enddate='+end+'&key='+key+'&col='+col+'&type=<?=$usertype;?>';
	}
function togglelead(eid) {
	var p = 'ver'+eid;
	var i = document.getElementById(p).selectedIndex;
	var t = document.getElementById(p).options[i].value;
	var data = 'act=upst&lid='+eid+'&vl='+t;
	var http = getHTTPObject();
	http.open("POST", url, true);
	http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	http.onreadystatechange = function(){
		if (http.readyState == 4)
					{
					var resp = http.responseText;
					manage(pid);
					}
	};
	http.send(data);
	//document.getElementById('fidlist').src='admin.php?act=upst&lid='+eid+'&vl='+t;
}
function getHTTPObject() {
  var xmlhttp;

  if(window.XMLHttpRequest){
    xmlhttp = new XMLHttpRequest();
  }
  else if (window.ActiveXObject){
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    if (!xmlhttp){
        xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
   
}
  return xmlhttp;
}
var alid;
var http = getHTTPObject();
var url = 'admin.php';
</script>
<script type="text/javascript" src="calendar/zapatec.js"></script>
<script type="text/javascript" src="calendar/calendar.js"></script>
<script type="text/javascript" src="calendar/calendar-en.js"></script>
<script type="text/javascript">
$.noConflict();
  jQuery(document).ready(function($) {
    $( ".dates" ).datepicker({ dateFormat: 'yy-mm-dd' });
  });
//<![CDATA[
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
<link rel="stylesheet" href="calendar/cal.css">
</body>
