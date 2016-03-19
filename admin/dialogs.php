 <?php
 if ($app == 'editcobj')
 {
     $oid = $_REQUEST['id'];
     $res = mysql_query("SELECT * from project_objectives where id = $oid");
     $ob = mysql_fetch_assoc($res);
     $sumsel = $ob['type'] == 'sum' ? "selected=selected":'';
      $avesel = $ob['type'] == 'average' ? "selected=selected":'';
      $dsel = $ob['period'] == 'day' ? "selected=selected":'';
      $wsel= $ob['period'] == 'week' ? "selected=selected":'';;
      $msel= $ob['period'] == 'month' ? "selected=selected":'';;
     ?>
<div class="entryform" id="objs" style="width:300px;">
 <title>Update Campaign Objective</title>
 <div><label>Disposition:</label><select name="disposition" id="disposition"><option></option><?=getdispodrop($ob['projectid'],$ob['disposition']);?></select></div>
  <div><label>Type:</label><select name="type" id="type"><option></option><option value="sum" <?=$sumsel;?>>Sum</option><option value="average" <?=$avesel;?> >Average</option></select></div>
  <div><label>Period:</label><select name="period" id="period"><option></option><option value="day" <?=$dsel;?>>Daily</option><option value="week" <?=$wsel;?>>Weekly</option><option value="month" <?=$msel;?>>Monthly</option></select></div>
  <div><label>Target:</label><input type="text" name="target" id="target" value="<?=$ob['target'];?>" /></div>
  <div><input type="button" value="Update" onclick="updatecobj('<?php echo $oid;?>')" /></div>
</div>
 <?php   
 } 
 if ($app == 'edittobj')
 {
      
     $oid = $_REQUEST['id'];
     $res = mysql_query("SELECT * from project_objectives_team where id = $oid");
     $ob = mysql_fetch_assoc($res);
     $teamres = mysql_query("SELECT teamid, teamname from teams where bcid = $bcid");
    while ($teamrow = mysql_fetch_assoc($teamres))
    {
        $tsel = $teamrow['teamid'] == $ob['teamid'] ? 'selected=selected': '';
        $teamsdrop .= '<option value="'.$teamrow['teamid'].'" '.$tsel.'>'.$teamrow['teamname'].'</option>';
    }
     $sumsel = $ob['type'] == 'sum' ? "selected=selected":'';
      $avesel = $ob['type'] == 'average' ? "selected=selected":'';
      $dsel = $ob['period'] == 'day' ? "selected=selected":'';
      $wsel= $ob['period'] == 'week' ? "selected=selected":'';;
      $msel= $ob['period'] == 'month' ? "selected=selected":'';;
     ?>
<div class="entryform" id="objs" style="width:300px;">
 <title>Update Campaign Team Objective</title>
 <div><label>Team:</label><select name="teamid" id="teamid"><option></option><?=$teamsdrop;?></select></div>
 <div><label>Disposition:</label><select name="disposition" id="disposition"><option></option><?=getdispodrop($ob['projectid'],$ob['disposition']);?></select></div>
  <div><label>Period:</label><select name="period" id="period"><option></option><option value="day" <?=$dsel;?>>Daily</option><option value="week" <?=$wsel;?>>Weekly</option><option value="month" <?=$msel;?>>Monthly</option></select></div>
  <div><label>Target:</label><input type="text" name="target" id="target" value="<?=$ob['target'];?>" /></div>
  <div><input type="button" value="Update" onclick="updatetobj('<?php echo $oid;?>')" /></div>
</div>
 <?php   
 }
 if ($app == 'newcobj')
 {
  ?>
<div class="entryform" id="objs" style="width:300px;">
 <title>New Campaign Objective</title>
 <div><label>Disposition:</label><select name="disposition" id="disposition"><option></option><?=getdispodrop($_REQUEST['pid']);?></select></div>
  <div><label>Type:</label><select name="type" id="type"><option></option><option value="sum">Sum</option><option value="average">Average</option></select></div>
  <div><label>Period:</label><select name="period" id="period"><option></option><option value="day">Daily</option><option value="week">Weekly</option><option value="week">Monthly</option></select></div>
  <div><label>Target:</label><input type="text" name="target" id="target" /></div>
  <div><input type="button" value="add" onclick="addcobj('<?php echo $_REQUEST['pid'];?>')" /></div>
</div>
 <?php   
 }
 if ($app == 'newactiontag')
 {
  ?>
<div class="entryform" id="objs" style="width:300px;">
 <title>New Action Tag</title>
 <div><label>Action to Tag:</label><select name="actionevent" id="actionevent"><option value="pause">Pause</option></select></div>
 <div><label>Tag:</label><input name="reason_name" id="reason_name" /></div>
  <div><input type="button" value="add" onclick="addactiontag('<?php echo $_REQUEST['pid'];?>')" /></div>
</div>
 <?php   
 }
 if ($app == 'newtobj')
 {
     $teamres = mysql_query("SELECT teamid, teamname from teams where bcid = $bcid");
    while ($teamrow = mysql_fetch_assoc($teamres))
    {
        $teamsdrop .= '<option value="'.$teamrow['teamid'].'">'.$teamrow['teamname'].'</option>';
    }
  ?>
<div class="entryform" id="objs" style="width:300px;">
 <title>New Team Target</title>
 <div><label>Team:</label><select name="teamid" id="teamid"><option></option><?=$teamsdrop;?></select></div>
 <div><label>Disposition:</label><select name="disposition" id="disposition"><option></option><?=getdispodrop($_REQUEST['pid']);?></select></div>
  
  <div><label>Period:</label><select name="period" id="period"><option></option><option value="day">Daily</option><option value="week">Weekly</option><option value="week">Monthly</option></select></div>
  <div><label>Target:</label><input type="text" name="target" id="target" /></div>
  <div><input type="button" value="add" onclick="addtobj('<?php echo $_REQUEST['pid'];?>')" /></div>
</div>
 <?php
 }
 if ($app == 'newcf')
 {
 ?>
<div class="entryform" id="newcf" style="width:300px;">
 <title>New Custom Field</title>
 <div><label>Name:</label><input type="text" name="fieldname" id="fieldname" /><br>(Internal Name)</div>
  <div><label>Label:</label><input type="text" name="fieldlabel" id="fieldlabel"/><br>(This will appear on agent interface)</div>
  <div><input type="button" value="add" onclick="addcf('<?php echo $_REQUEST['pid'];?>')" /></div>
</div>
 <?php
 }
 if ($app == 'newdispo')
 {
 ?>
<div class="entryform" id="newdispo" style="width:500px;">
<title>New Disposition</title>
<?php
include_once "../dbconnect.php";
$projid = $_REQUEST['pid'];
$p = getprojects($bcid, true);
$projlist = $p['list'];
$r= "SELECT * from lists where bcid = '".$_SESSION['bcid']."'";
$dres = mysql_query("SELECT * from statuses where projectid = 0");
$dd = '';
while ($row = mysql_fetch_assoc($dres))
{
    $dd .= '<tr><td class="center-title">'.$row['statusname'].'</td></tr>';
}
$lres = mysql_query($r) or die(mysql_error());
	while ($lrow = mysql_fetch_assoc($lres))
		{
			$prlists[$lrow['lid']] = $lrow;
		}

?>
<span id="transferproject" style="display:none">
    <select name="transfer_pid" id="transfer_pid" onchange="changetransferpid()" style="font-size:10px">
        <option></option>
    <?php
    echo $projlist;
    ?>
    </select> 
</span>
<span id="transferlist" style="display:none">
<select name="transfer_lid" id="transfer_lid" onchange="changetransferlid('<?=$projid;?>')" style="font-size:10px">
<?php
foreach ($prlists as $tolist)
	{
		echo '<option value="'.$tolist['lid'].'">'.$tolist['listid'].'</option>';
	}
?>
</select>
</span>
<table width="50%" style="float:left">
<tr><td colspan="2" class="tableheader">Disposition Details</td></tr>

<tr>
<td width="262" class="center-title">Disposition Name</td><td class="dataleft datas"><input type=text name="statusname" id="statusname"/></td></tr>
<tr><td width="339" class="center-title">Type</td><td class="dataleft datas"><select name="statustype" id="statustype"  style="font-size:10px" onchange="statusoptions()">
        <option value="text">Text Description</option>
        <option value="dateandtime">Date Set Outcome</option>
        <option value="booking">Client Booking</option>
        <option value="link">External Link</option>
        <option value="transfer">Transfer</option>
        <option value="transferdateandtime">Transfer - Date Set</option>
        <option value="transferdateandtimecallback">Transfer - Callback</option>
    </select>
</td></tr>
<tr>
<td width="308" class="center-title">Category</td>
<td class="dataleft datas" ><select name="category" id="category" style="font-size:10px">
    	<option value="agent">Agent CallBack</option>
        <option value="team">Team CallBack</option>
        <option value="callable">Recallable</option>
        <option value="final">Final Outcome</option>
    </select>
</td>
</tr>
<tr id="options_tr"><td width="339" class="center-title"><span id="optionslabel">Options</span></td><td class="dataleft datas"><span id="optionsinput"><input type="text" id="statusoption" name="statusoption" /></span>
</td></tr>
<tr>

<td class="dataleft datas" colspan="2"><a href="#"  onclick="add_dispo('<?=$projid;?>')"><img src="icons/add.gif" onclick="add_dispo('<?=$projid;?>')"/>Add</a></td>
</tr>
</table>
<table width="50%" style="margin-left: 5px">
    <tr><td class="tableheader">Default Dispositions</td></tr>
    <?=$dd;?>
</table>
</div>
<?php
 }
 ?>