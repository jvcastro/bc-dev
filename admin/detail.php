<?php
session_start();
include_once "../dbconnect.php";

$leadid = $_REQUEST['id'];
$act = $_REQUEST['act'];
$index = $_REQUEST['index'];
$group = $_REQUEST['grp'];
$usertype = $_REQUEST['utype'];
$comm = "find /var/spool/asterisk/monitorDONE/ -type f -name \"".$leadid."_*\" -exec echo {} \;";
$comm2 = "find /var/spool/asterisk/recordings/ -type f -name \"".$leadid."_*\" -exec echo {} \;";
if ($act=='dl')
	{
	exec($comm,$list);
	if ($list && $group == '1')
	{
	header('Content-type: application/mp3');
	header('Content-Disposition: attachment; filename="'.substr($list[$index],32).'"');
	readfile($list[$index]);
	}
	exec($comm2,$list2);
	if ($list2 && $group == '2')
			{
			header('Content-type: application/mp3');
			header('Content-Disposition: attachment; filename="'.substr($list2[$index],31).'"');
			readfile($list2[$index]);
			}	
	}
/*exec($comm,$list);
if ($list)
	{
	echo "<br>";
	$x = count($list);
	$y =0;
	while ($y < $x)
		{
		$recs .= "<a href=\"".$_SERVER['PHP_SELF']."?act=dl&index=$y&id=$leadid&grp=1\">".substr($list[$y],32)."</a><Br>";
		$y++;
		}
	}*/
$comm3 = "find /var/spool/asterisk/recordings/ -type f -name \"".$leadid."_*\" -printf '%s_%f\n'";
exec($comm3,$list2);	
if ($list2)
	{
	
	
	$x = count($list2);
	$y =0;
	
	$recs .= '<div><div class="label rec">Length</div>';
	$recs .= '<div class="label rec">Date</div>';
	$recs .= '<div class="label rec">Time</div>';
	$recs .= '<div class="label rec" style="border-right:1px solid #CCC">Call Id</div></div>';
	
	while ($y < $x)
		{
		$recs .= '<div style="clear:both;">';
		$recfile = $list2[$y];
		$recparts = explode("_",$recfile);
		$size = $recparts[0];
		settype($size,"integer");
		$epoch = $recparts[3];
		$callid = substr($recparts[4],0,-4);
		$dateofcall =date("Y-m-d",$epoch);
		$timeofcall =date("h:i:s A",$epoch);
		$length = $size / 16000;
		$recs .= '<div class="label rec">'.round($length,2).' secs</div>';
		$recs .= '<div class="label rec">'.$dateofcall.'</div>';
		$recs .= '<div class="label rec">'.$timeofcall.'</div>';
		$recs .= '<div class="label rec"  style="border-right:1px solid #CCC">';
		$recs .= "<a href=\"".$_SERVER['PHP_SELF']."?act=dl&index=$y&id=$leadid&grp=2\">".$callid."</a><Br>";
		$recs .= '</div>';
		$recs .= '</div>';
		$y++;
		}
	}
//else $recs.= "No Recording found.";
$usertype = $_REQUEST['utype'];
$leadid = $_REQUEST['id'];
$pid = $_REQUEST['pid'];
if (strlen($pid) == 0){$pid = 0;}
$leadres = mysql_query("SELECT leadid, cname, cfname, clname, address1, address2, city, state, zip, company, phone, altphone, comments, resultcomments, projectid, dispo, status, title, email, qa, mobile from leads_done where leadid = '$leadid'");
$lead = mysql_fetch_array($leadres);
$dtres = mysql_query("SELECT * from dateandtime where leadid = '$leadid'");
$dt = mysql_fetch_array($dtres);
$dispores = mysql_query("SELECT * from statuses where projectid = '".$lead['projectid']."' or projectid = '0'");//edit this<br />
$scriptres = mysql_query("SELECT scriptxml from scriptdata where leadid = '$leadid'");
$scriptrow = mysql_fetch_array($scriptres);
$xml = $scriptrow['scriptxml'];
$raw = explode("</",$xml);
$ct = 0;
foreach ($raw as $d)
	{
	$st_label = strpos($d,"<") + 1;
	$end_label = strpos($d,">",$st_label);
	$len_label = strlen($d);
	$label = substr($d,$st_label,$end_label - $st_label);
	$st_val = $end_label + 1;
	//$end_val = strpos($d,"<",$end_label);
	
	//if ($end_val != $st_val)
	$value = substr($d,$st_val);
	$cdata .= '<div class="label rec" style="clear:both; width:200px; text-align:right">';
	$cdata .= str_replace("_"," ",$label);
	$cdata .= ': </div>';
	$cdata .= '<div class="val rec" id="customdata'.$ct.'" style="width:200px; text-align:left; padding-left:10px" onclick="changeoption(this,\'obr0123'.$label.'\',\''.$leadid.'\')">&nbsp;';
	$cdata .= $value;
	$cdata .= '</div>';
	$ct++;
	}
?>

<style type="text/css">
<!--
body {
	font-family:Geneva, Arial, Helvetica, sans-serif;
	font-size:10px;

}
td {
	line-height: 20px;
	color: #999999;
}
input {
text-align:left;
font-size:12px;
width: 200px;
}
span {
color:#330066;

}
.label {
width: 80px;
text-align:right;
position:relative; 
left: 10px; 
top: 0px; 
float:left;
color:#330066;
}

.val {
width: 250px;
position:relative; 
left: 10px; 
top: 0px; 
float:left;

}
textarea {
height: 40px;
width: 200px;
}
.rec {
text-align:center; 
width:100px; 
border-bottom: 1px solid #CCC;
padding: 2px;
}
-->
</style>
<div>
<form name="custinfo" id="custinfo" method="post">
<input type="hidden" name="leadtype" id="leadtype" value="">
<input type="hidden" name="listid" id="listid" value="">
<input type="hidden" name="listid" id="projectid" value="">
<input type="hidden" name="leadid" id="leadid" value="<?=$leadid;?>">
<input type="hidden" name="leadiddet" id="leadiddet" value="<?=$leadid;?>">
  <div id="details">
  <div style="width:700px; position:relative">
    <div class="label">Name:</div>
    <div class="val"><input name="cname" type="text" id="cname" style="width:200px;" value="<?=$lead['cname'];?>"   onchange="blto()" onblur="dchanges(this.id);"/></div>
    <div class="label">Title:</div>
    <div class="val"><input name="title" type="text" id="title" value="<?=$lead['title'];?>"   onchange="blto()" onblur="dchanges(this.id);"/></div>
  </div>
  <div style="clear:both"></div>
  <div>
    <div class="label">Company:</div>
    <div class="val">
      <textarea name="company" cols="20" rows="2" id="company"   onchange="blto()" onblur="dchanges(this.id);"><?=$lead['company'];?>
      </textarea>
    </div>
    <div class="label">Email:</div>
	<div class="val"><input name="email" type="text" id="email" value="<?=$lead['email'];?>"   onchange="blto()" onblur="dchanges(this.id);"/></div>
  </div>
  <div style="clear:both"></div>
  <div>
    <div class="label">Phone:</div>
    <div class="val"><input name="phone" type="text" id="phone" value="<?=$lead['phone'];?>"   onchange="blto()" onblur="dchanges(this.id);"/></div>
    <div class="label">Altphone:</div>
    <div class="val"><input name="altphone" type="text" id="altphone" value="<?=$lead['altphone'];?>"   onchange="blto()" onblur="dchanges(this.id);" /></div>
    
  </div>
  <div style="clear:both"></div>
  <div>
    <div class="label">Firstname:</div>
    <div class="val"><input name="cfname" type="text" id="cfname" value="<?=$lead['cfname'];?>"  onchange="blto()" onblur="dchanges(this.id);"/></div>
    <div class="label">Lastname:</div>
    <div class="val"><input name="clname" type="text" id="clname" value="<?=$lead['clname'];?>"  onchange="blto()" onblur="dchanges(this.id);"/></div>
  </div>
  <div style="clear:both"></div>
  <div>
    <div class="label">Address1: </div>
    <div  class="val">
      <textarea name="address1" cols="20" rows="2" id="address1"  onchange="blto()" onblur="dchanges(this.id);"><?=$lead['address1'];?>
      </textarea>
    </div>
    <div class="label">Address2: </div>
    <div class="val"><textarea name="address2" id="address2"  cols="20" rows="2"  value="<?=$lead['address2'];?>"  onchange="blto()" onblur="dchanges(this.id);"/></textarea></div>
  </div>
  <div style="clear:both"></div>
  <div>
    <div class="label">City/Suburb:</div>
    <div  class="val"><input name="city" type="text" id="city" size="20"  value="<?=$lead['city'];?>"  onchange="blto()" onblur="dchanges(this.id);"/></div>
    <div class="label">State:</div>
    <div class="val" style="width:50px"><input name="state" type="text" id="state" style="width:50px" value="<?=$lead['state'];?>"   onchange="blto()" onblur="dchanges(this.id);"/></div>
    <div class="label" style="width:50px">Postal Code:</div>
    <div class="val" style="width:50px"><input name="zip" type="text" id="zip" style="width:80px" value="<?=$lead['zip'];?>"  onchange="blto()" onblur="dchanges(this.id);"/></div>
  <div style="clear:both"></div>
   <div style="position:absolute; top:38px; left:330px;"> <div class="label">Mobile:</div><div class="val"><input name="mobile" type="text" id="mobile" style="width:80px" value="<?=$lead['mobile'];?>"  onchange="blto()" onblur="dchanges(this.id);"/></div></div>
</div>
  </div>
  <div id="recordings" style="width:500px; overflow:auto; height:172px"><?=$recs;?></div>
  <div id="customdata" style="width:757px; height:170px; overflow:auto"><?=$cdata;?></div>
  	<div id="results">
    <div style="position:absolute; left: 10px; top: 0px">Comments:</div>
    <div style="position:absolute; left: 70px; top: 0px"><textarea name="comments" style="width:350px" cols="50" id="comments" onchange="blto()" onblur="dchanges('comments');"> <?=$lead['comments'];?></textarea></div>

    <div style="position:absolute; left: 10px; top: 60px">Disposition:</div>
    <div style="position:absolute; left: 70px; top: 60px"><select name="qdispo" id="qdispo" onchange="blto();" onblur="dchanges('qdispo');">
      <option><?=$lead['dispo'];?></option>
<?
while ($drow = mysql_fetch_array($dispores))
	{
	echo "<OPTION>";
	echo $drow['statusname'];
	echo "</OPTION>";
	}
?>
    </select></div>
    
    <div id="datpik" style="position:absolute; left: 230px; top: 60px"">Date Set: <input id="dtime" class="dates" type="text" value="<?=$dt['dtime'];?>" onchange="blto()" onblur="dchanges('dtime')"/> </div> 
    <div style="position:absolute; left: 10px; top: 80px">Notes:</div>
    <div style="position:absolute; left: 70px; top: 80px"><textarea style="width:300px" name="resultcomments" cols="20" id="resultcomments" onchange="blto()" onblur="dchanges('resultcomments');"><?=$lead['resultcomments'];?></textarea></div>
<?
if ($usertype == 'qa' || $usertype == 'verifier')
{
echo " <div style=\"position:absolute; left: 10px; top: 140px\">Status:</div><div style=\"position:absolute; left: 70px; top:  140px\"><select name=\"status\" id=\"status\" onchange=\"blto()\" onblur=\"dchanges('status');\">
      <option></option>";
if ($usertype == 'qa')
{
$srow[0] = 'approved';
$srow[1] = 'incomplete';
$srow[2] = 'rejected';
$srow[3] = 'assigned';
}
if ($usertype == 'verifier')
{
$srow[0] = 'release';
$srow[1] = 'incomplete';
$srow[2] = 'failed';
//$srow[3] = 'approved';
}
foreach ($srow as $strow)
	{
	if ($strow == $lead['status']) 
		{
		echo "<OPTION selected>";
		}
	else 
		{
		echo "<OPTION>";
		}
	echo $strow;
	echo "</OPTION>";
		
	}
echo " </select></div>";
}
?>
  	<div style="position:absolute; left: 380px; top: 80px">
    QA Notes:
  	<textarea name="qa" style="width:300px; vertical-align:top" cols="20" id="qa" onchange="blto()" onblur="dchanges('qa');" ><?=$lead['qa'];?></textarea>
  	</div>
<div style="position:relative; left:680px;">
<input name="sbbb" type="button" id="sbbb" style="font-size:10px; text-align:right; border-width:1px; width:55px;background-image:url(icons/update.png);background-position:left; background-repeat:no-repeat;" value="Update">
</div>    
    </div>   

</form>
</div>
<div id="cont" style="position:absolute; left:0px; top:0px;">
</div>

<script>
var bl = false;
var lid = document.getElementById('leadid').value;
var sp = Ext.query("span");




function writedatpik(lid)
{
document.getElementById('datpik').innerHTML='<table><div>Date:</div><div><input type="text" name="datefield" id="datefield"></div><div><div id="butt"><div id="calendar" style="position:absolute; left:650; top:300; z-index:100;"></div></div></div><div><select name="timefield" id="timefield"><option></option><option>01:00</option><option>02:00</option><option>03:00</option><option>04:00</option><option>05:00</option><option>06:00</option><option>07:00</option><option>08:00</option><option>09:00</option><option>10:00</option><option>11:00</option><option>12:00</option></select><select name="apfield" id="apfield"><option>AM</option><option>PM</option></select></div></table>';
var selectHandler = function(myDP, date) {
var field = document.getElementById('datefield');
field.value = date.format('Y-m-d');
myDP.hide();
};
var dt = new Ext.DatePicker({
	listeners: {
'select':selectHandler
} 
	});
dt.render('calendar');
dt.hide();
var bt = new Ext.Button({style:'height:5px',icon:"images/ns-expand.gif", handler: function(){dt.show();}});
bt.render('butt');
}


</script>
