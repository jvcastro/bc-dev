<?php
include_once "../dbconnect.php";
$projres = mysql_query("SELECT * from projects");
while ($row = mysql_fetch_array($projres))
	{
		$projects[$row['projectid']] = $row;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link href="styles/style.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="projselect" class="box" style="width:300px; float:left;">
SELECT PROJECTS <br />
<?
foreach ($projects as $proj)
	{
	echo '<input type="checkbox" name="'.$proj['projectid'].'"/> '.$proj['projectname'].'<br />';
	}
?>
</div>
<div id="repselect" class="box" style="width:200px; float:left">
SELECT REPORT TYPES<br />
<input type="checkbox" onclick="generatefields(this)" id="datadisp" name="datadisp"> Data Dispositions<br />
<input type="checkbox" onclick="generatefields(this)" id="calldisp" name="calldisp"/> Call Dispositions<br />
<input type="checkbox" onclick="generatefields(this)" id="ldr" name="ldr"/> Lead Detail Report<br />
<input type="checkbox" onclick="generatefields(this)" id="cpr" name="cpr"/> Campaign Performance<br />
<input type="checkbox" onclick="generatefields(this)" id="apr" name="apr"/> Agent Performance<br />
</div>
<div id="checklist" class="box" style="width:200px; float:left"></div>
</body>
<script>
var rdy;
var vcd = false;
function cfchanges(chk)
	{
		if (chk.checked == true)
			{
				rdy = true;
			}
		if (rdy == true && vcd == false)
			{
				cd = document.createElement("div");
				cd.id = "createrep";
				cd.style.width = "200px";
				cd.style.float = "left";
				cd.style.position ="absolute";
				cd.style.left = "700px";
				cd.innerHTML = "<a href=>Create Report</button>";
				document.body.appendChild(cd);
				vcd = true;
			}
	}
function generatefields(chk)
	{
		var rtype = chk.id;
		var checked = chk.checked;
		if (checked == true)
		{
			params = 'act=getchecklist&type='+rtype;
			send_data(params, function(){
					if (http.readyState == 4)
						{
							var resp = http.responseText;				   
							var clist = document.getElementById('checklist');
							var b = clist.innerHTML;
							clist.innerHTML = b + resp;
						}
					});
			
		}
		else 
		{
			var clist = document.getElementById(rtype+'_child');
			clist.parentNode.removeChild(clist);
		}
	}
	
function send_data(parameters, receiver)
	{
		http.open("get", url+"?"+parameters, true);
		http.onreadystatechange = receiver;
		http.send(null);
	}
function getHTTPObject() {
  var xmlhttp =false;

  if (window.XMLHttpRequest){
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
var params;
var http = getHTTPObject();
var url = "admin.php";
</script>
</html>
