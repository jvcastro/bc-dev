<?php
if ($act == 'removedispoupdate')
{
    $id = $_REQUEST['id'];
    mysql_query("delete from disposition_update_history where bcid = $bcid and id = '$id'");
    exit;
}
if ($act == 'removeexclusion')
{
    $eid = $_REQUEST['id'];
    
    mysql_query("delete from lists_exclusion where id = $eid");
    mysql_query("delete from lists_exclusion_data where exclusionid = $eid");
    exit;
}
if ($act == 'addcobj')
{
    extract($_REQUEST);
    
    mysql_query("INSERT into project_objectives set projectid = '$pid', disposition = '$disposition', type='$type',period = '$period', target='$target'");
    
}
if ($act == 'addactiontag')
{
    extract($_REQUEST);
    
    mysql_query("INSERT into eventtags set projectid = '$pid', actionevent = '$actionevent', reason_name = '$reasonname'");
    exit;
}
if ($act == 'deletecobj')
{
    mysql_query("Delete from project_objectives where id = '".$_REQUEST['id']."'");
}
if ($act == 'deleteactiontag')
{
    mysql_query("update eventtags set active = 0 where id = '".$_REQUEST['id']."'");
    exit;
}
if ($act == 'deletetobj')
{
    mysql_query("Delete from project_objectives_team where id = '".$_REQUEST['id']."'");
}
if ($act == 'updatecobj')
{
    extract($_REQUEST);
    
    mysql_query("update project_objectives set disposition = '$disposition', type='$type',period = '$period', target='$target' where id = $id");
    exit;
}
if ($act == 'addtobj')
{
    extract($_REQUEST);
    
    mysql_query("INSERT into project_objectives_team set projectid = '$pid', disposition = '$disposition', teamid='$teamid',period = '$period', target='$target'");
    
}
if ($act == 'updatetobj')
{
    extract($_REQUEST);
    
    mysql_query("update project_objectives_team set disposition = '$disposition', teamid='$teamid',period = '$period', target='$target' where id = $id");
    
}
if ($act == 'voicemail' || $act == 'deletevm')
{
    $pid = $_REQUEST['pid'];
    $res = mysql_query("SELECT * from project_voicemail where projectid = $pid");
    while ($row = mysql_fetch_assoc($res))
    {
        $vms[$row['id']] = $row;
    }
    $s3 = new S3("AKIAIFNBYO657IIJKOUQ", "w6Q/iJwhRYvS+RR1agf3zQoNrvtaw3T4as7qDpd2");
    $s3bucket = "bcrecs-au"; $prefix = "vm_".$pid."/";
    if ($act == 'deletevm')
    {
        $ob = $_REQUEST['ob'];
        $s3->deleteObject($s3bucket,$ob);
        exit;
    }
    $audio = $s3->getBucket($s3bucket,$prefix,NULL);
    foreach ($audio as $aud)
        {
            $parts = explode("_",$aud['name']);
            $dt = $parts[2];
            $audiosort[$dt] = $aud;
            
        }
    
    ?>
    <table width="800" cellspacing="5">
        <tr><td colspan="5" class="tableheader">Voicemail Recordings</td></tr>
        <tr><th>Date/Time</th><th>CallerID</th><th>Size</th><th>File</th></tr>
        <?php
		$cta = 0;
                krsort($audiosort);
		foreach ($audiosort as $aud)
			{
				$m = $cta % 2;
				if ($m = 0) $cl = "tableitem";
				else $cl = "tableitem_";
                                //$lc = $aud['size']/3072;
                                //$lc = number_format($lc, 2);
                                $lc = filesizeformat($aud['size']);
                                $media = S3::getAuthenticatedURL($s3bucket, $aud['name'], 3600, false, true);
                                //$dt = gettimefromname($aud['name']);
                                $parts = explode("_",$aud['name']);
                                $dt = $parts[2];
                                $phonestring = explode("/",$parts[1]);
                                $fphone = $phonestring[1];
                                $dti = date("Y-m-d H:i:s",$dt);
				echo '<tr class="'.$cl.'" id="'.$dt.'"><td>'.$dti.'</td>
				<td>'.$fphone.'</td><td>'.$lc.'</td>';
                               // echo '<td width="220" align="center">';
                                if (!isset($_REQUEST['export']) && $act != 'emailtoclient')
                                {
                                /*echo '<object type="application/x-shockwave-flash" data="../../jquery/player_mp3_mini.swf" width="200" height="20">
    <param name="movie" value="../../jquery/player_mp3_mini.swf" />
    <param name="bgcolor" value="#c7c7c7" />
    <param name="FlashVars" value="mp3='.urlencode($media).'&amp;bgcolor=c7c7c7&amp;slidercolor=404040" />
</object>';*/
                                }
                                //echo '</td>'
                                echo '<td><a id="wavfile" href="'.$media.'" target="_blank">Download</a> | <a href="#" id="deletevm" onclick="deletevm(\''.$aud['name'].'\',\''.$pid.'\',\''.$dt.'\')">Delete</a></td></tr>';
				$cta++;
			}
		?>
        </table>
<?php
}
if ($act == 'actiontags')
{
    $pid = $_REQUEST['pid'];
    $cores = mysql_query("SELECT * from eventtags where projectid = $pid and active = 1");
    while ($row = mysql_fetch_assoc($cores))
    {
        $rows[$row['id']][1] = $row['actionevent'];
        $rows[$row['id']][2] = $row['reason_name'];
         $rows[$row['id']][5] = '<a href="#" onclick="deleteactiontag(\''.$row['id'].'\')">Delete</a>';
    }
    $headers = array('Action to Tag','Tag',' ');
    $ats = tablegen($headers,$rows,'100%');
    ?>
     <div id="objectivesdiv" class="campsection">
     <div id="secnav" style="width: 100%; position: relative; height: 20px;">
         <input type="button" onclick="newactiontag('<?=$pid;?>')" class="jbut" value="New Action Tag"/></div>
                    <h3>Action Tags</h3>
                    <?=$ats;?>
 </div>
<?php
}
if ($act =='objectives')
{
    $pid = $_REQUEST['pid'];
    $cores = mysql_query("SELECT * from project_objectives where projectid = $pid");
    while ($row = mysql_fetch_assoc($cores))
    {
        $rows[$row['id']][1] = $row['disposition'];
        $rows[$row['id']][2] = $row['type'];
        $rows[$row['id']][3] = $row['period'];
        $rows[$row['id']][4] = $row['target'];
         $rows[$row['id']][5] = '<a href="#" onclick="editcobj(\''.$row['id'].'\')">Edit</a> | <a href="#" onclick="deletecobj(\''.$row['id'].'\')">Delete</a>';
    }
    $headers = array('Disposition','Type','Period','Objective','Actions');
    $cobjectives = tablegen($headers,$rows,'100%');
    $headers = array();
    $rows = array();
    $pid = $_REQUEST['pid'];
    $teamres = mysql_query("SELECT teamid, teamname from teams where bcid = $bcid");
    while ($teamrow = mysql_fetch_assoc($teamres))
    {
        $teams[$teamrow['teamid']] = $teamrow;
    }
    $tores = mysql_query("SELECT * from project_objectives_team where projectid = $pid");
    while ($row = mysql_fetch_assoc($tores))
    {
        
        $rows[$row['id']][1] = $teams[$row['teamid']]['teamname'];
        $rows[$row['id']][2] = $row['disposition'];
        $rows[$row['id']][3] = $row['period'];
        $rows[$row['id']][4] = $row['target'];
         $rows[$row['id']][5] = '<a href="#" onclick="edittobj(\''.$row['id'].'\')">Edit</a> | <a href="#" onclick="deletetobj(\''.$row['id'].'\')" >Delete</a>';
    }
    $headers = array('Team','Disposition','Period','Objective','Actions');
    $tobjectives = tablegen($headers,$rows,'100%');
    ?>
 <div id="objectivesdiv" class="campsection">
     <div id="secnav" style="width: 100%; position: relative; height: 20px;"><input type="button" onclick="newtobj('<?=$pid;?>')" class="jbut" value="New Agent Objective"/><input type="button" onclick="newcobj('<?=$pid;?>')" class="jbut" value="New Campaign Objective"/></div>
                    <h3>Campaign Objectives</h3>
                    <?=$cobjectives;?>
                    <br><h3>Team Objectives</h3>
                    <?=$tobjectives;?>
 </div>
<?php
}
if ($act == 'endbarge')
{
    $origin = $_REQUEST['origin'];
   include "../ami-client.php";
    AMI::raisebridge($origin);
    exit;
}
if ($act == 'barge')
{
    $origin = $_REQUEST['origin'];
    $target = $_REQUEST['target'];
    include "../ami-client.php";
    AMI::bridge($origin, $target);
    ?>
<p>Barging...</p>
<a href="#" onclick="endbarge()">Click here to Hangup.</a>

<?php
exit;
}
if ($act == 'saveadminext')
{
    $_SESSION['adminext'] = $_REQUEST['adminext'];
    echo $_SESSION['adminext'];
    exit;
}
if ($act == 'setadminext')
{
    ?>
<table>
    <tr><td>Extension(conference):</td><td><input type="text" id="setadminext"/></td></tr>
    <tr><td colspan="2"><a href="#" class="jbut" onclick="saveadminext()">Save</a>
</table>
<?php
exit;
}
if ($act == 'resortdispo')
{
    $arr = $_POST['custdispo'];
    $i = 0;
    $ret = array();
    foreach ($arr as $a)
    {
        mysql_query("update statuses set sort = '$i' where statusid = '$a'");
        $ret[$a] = $i;
        $i++;
        
    }
    echo json_encode($ret);
    exit;
}
if ($act == 'getguide')
{
    $section = $_REQUEST['section'];
    $pgs = getdatatable("pageguide where section = '$section' or section = 'all'",'id');
    
    ?>
<ul id="tlyPageGuide" data-tourtitle="<?php echo count($pgs) > 0 ? "Click here for Help" : "No Help Available";?>">
  <?php
    foreach ($pgs as $pg)
    {
        ?>
    <li class="tlypageguide_<?php echo $pg['position'];?>" data-tourtarget="<?php echo $pg['selector'];?>">
    <div>
        <?php echo $pg['helptext'];?>
    </div>
  </li>
  <?php
    }
    ?>
</ul>
<?php
    exit;
}

if ($act == 'getapp')
	{
		$app = $_REQUEST['app'];
		switch ($app) {
                        case 'manexcl':
                            include "manageexclusionlist.php";
                            break;
                        case 'dispoupdate':
                            include "dispoupdate.php";
                            break;
			case 'chatlog':
				$users = getbyparams('members','bcid = '.$bcid,"userid");
				foreach ($users as $userd)
					{
						$ulist[$userd['userid']] = $userd['userlogin'];
                                                $uids[$userd['userid']] = $userd['userid'];
					}
                                $fromids = implode(",",$uids);
				$chatlogs = getbyparams('chat','id > 0 and `from` in ('.$fromids.') order by id DESC');
				$headers[] = 'Date';
				$headers[] = 'Sender';
				$headers[] = 'Receiver';
				$headers[] = 'Message';
				foreach ($chatlogs as $chatlog)
				{

					$rows[$chatlog['id']]['date'] = $chatlog['sent']; 
					$rows[$chatlog['id']]['from'] = $ulist[$chatlog['from']]; 
					$rows[$chatlog['id']]['to'] = $ulist[$chatlog['to']]; 
					$rows[$chatlog['id']]['message'] = $chatlog['message']; 

				}
				?>
                <div class="apptitle">Chat Logs</div><br />
                <?
				echo tablegen($headers,$rows,'','','datatabs');
                                ?><br><BR>
                                    <?
				break;
			case 'searchlead':
			checkrights('searchlead');
				?>
                Input phone or Name:<br />
                <input type="text" id="searchstring" name="searchstring"/><br />
                <a href="#" onclick="searchlead()">Search</a><br />
                <hr />
                <div id="searchresult"></div>
				<?
				break;
                        case 'scriptpage':
                                ?>
                                <div class="entryform" id="addscriptpage" style="width:300px;height:100px">
                                    <title>Add Script Page</title>
                                    <div id="respmessage"></div>
                                    <div>Name: <input type="text" name="scriptname" id="scriptnameid" /></div>
                                    <div>Parent Page: <select name="parentid" id="parentid" onchange="getpagefields()">
                                            <option></option>
                                <?php
                                $cs = callscripts::getpages($_REQUEST['pid']);
                                foreach ($cs as $key=>$value)
                                    {
                                        echo '<option value="'.$key.'">'.$value.'</option>';
                                    }
                                ?>
                                </select></div>
                                    <div id="requiredfields">
                                        
                                    </div>
                                <input type="button" onclick="addscriptpage('<?=$_REQUEST['pid'];?>')" value="Add"/>
                                </div>
                                <?php
                                break;
                        case 'editcobj':
                                include "dialogs.php";
                                break;
                        case 'edittobj':
                                include "dialogs.php";
                                break;    
                        case 'newcobj':
                                include "dialogs.php";
                                break;
                        case 'newactiontag':
                                include "dialogs.php";
                                break;
                        case 'newtobj':
                                include "dialogs.php";
                                break;
                        case 'newdispo':
                                include "dialogs.php";
                                break;
                        case 'newcf':
                                
                                include "dialogs.php";
                                break;
			case 'schedules':
			checkrights('manageusers');
				echo '<iframe src="../sched/index.php" frameborder="0" id="calframe"  name="calframe" style="width:100%; height:500px">';
				break;
			case 'agenttimesheet':
				echo " ";
				include "timesheetrep.php";
				break;
			case 'reports':
				checkrights('viewreports');
				$rt = $_REQUEST['sec'];
				$cid = $_REQUEST['clientid'];
				switch ($rt) {
					case 'ap':
					 $tit = 'Agent Performance Report';
					 $w = '700px';
					 break;
					case 'ld':
					 $tit = 'Current List Disposition';
					 $w = '600px';
					 break; 
					case 'pd':
					 $tit = 'Campaign Calls Disposition';
					 $w = '600px';
					 break;
					case 'ch':
					 $tit = 'Campaign Performance per Hour';
					 $w = '600px';
					 break;
				}
				$projres = mysql_query("SELECT * from projects");
					
				$pdiv = "<div style=\"width:1000px; border-bottom: 1px solid #CCCCCC; padding: 5 5 5 5; margin:0 auto\"><div class=\"center-title\">$tit</div><div id=projects> &nbsp;<b><a class=label>Select Campaign:</a></b> <select name=projectid id=projectid class=val onchange=\"docustom()\"><option></option>";
				while ($prow = mysql_fetch_array($projres))
					{
						$pdiv.='<option value="'.$prow['projectid'].'">';
						$pdiv.=$prow['projectname'];
						$pdiv.='<option>';
					}
		         $pdiv .= "</div>";
		         $tdiv .= '</select>'; 
		         $tdiv .='<div style="float:left"><input type=hidden name=rtype id=rtype value="'.$rt.'">'; 
				 $tdiv .= '</div>';$tdiv .= '<div id="customid">';
				 if ($rt = 'ld')
				 	{
						$tdiv .= '';
					}
				else
				{
				
				$tdiv .= '<div id=cal1 style="float:left"><b> Start: <input name="startdate" id="startdate" type="text" size="10" maxlength="10" value="" style="width: 100px; font-size:9px; position:relative;">'; 
				$tdiv .='<button id="trigger" style="background-image:url(calendar/calendar.png); position:relative;"  class="calbutton" onclick="calsetup(\'cal1\',\'startdate\')">.</button></div>';
				$tdiv .= '<div id=cal2  style="float:left"><b> End: <input name="enddate" id="enddate" type="text" size="10" maxlength="10" value="" style="width: 100px; font-size:9px; position:relative;">'; 
				$tdiv .='<button id="trigger2" style="background-image:url(calendar/calendar.png); position:relative;"  class="calbutton" onclick="calsetup(\'cal2\', \'enddate\')">.</button></div>';
				
				}
				$tdiv .= '</div>';
				$tdiv .= '<div style="clear:both; text-align: right;"><a href="#" onclick="exportreport(\''.$cid.'\')">Export</a> : <a href="#" onclick="genreport(\''.$cid.'\')">View</a></div>';
				$tdiv .= "</div><div id=repdisplay style=\"clear:both\"></div>";
				echo ' ';
				echo $pdiv;
				echo $tdiv;
				break;
			case 'creports':
				$clientlist = array();
				$cres = mysql_query("SELECT clients.* from clients where bcid = '$bcid'");
				while ($crow = mysql_fetch_array($cres))
					{
						$clientlist[$crow['clientid']] = $crow; 
					}
				$projres = mysql_query("SELECT * from projects");
				$plist = array();
				while ($prow = mysql_fetch_array($projres))	
					{
						$pid = $prow['projectid'];
						$prlist[$pid] = $prow;
						$prlist[$pid]['clientname'] = $clientlist[$prow['clientid']]['userlogin'];
					}
				$disp = " ";
				$pres = mysql_query("SELECT * from projects");
				$disp .= '<div class="apptitle">Client Reports</div>
                                    <div class="secnav"><input type="button" onclick="manclient(\'Active\')" value="Back to Client List" /></div>';
				$disp .= '<table width="1010" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">';
				
				$disp .= '<tr><td class="tableheader">Client</td><td class="tableheader">Reports</td></tr>';
				
				foreach ($clientlist as $clients)
					{
						$reports = array();
						$repres = mysql_query("SELECT reportid, reportname from reports where clientid = '".$clients['clientid']."'");
						while ($reprow = mysql_fetch_array($repres))
							{
								$reports[$reprow['reportid']] = $reprow;
							}
						$numreps = count($reports);
						$disp .='<tr>';
						$disp .='<td class="dataleft"><a href="#" onclick="clientdetails(\''.$clients['clientid'].'\')">'.$clients['company'].'</a></td>';
						$disp .='<td class="dataleft">';
						if ($numreps < 1)
							{
								$disp .= $numreps.' Report(s) generated';
							}
						else $disp .= '<a href="#" onclick="manreports(\''.$clients['clientid'].'\')">'.$numreps.' Report(s) generated</a>';
						$disp .='</td>';
						//$disp .='<td class="dataleft"><a href="#" onclick="wiz_reports(\''.$clients['clientid'].'\')">New Report</a> </td>';
						$disp .='</tr>';
						
					}
				echo $disp;
				break;
			case 'mancamp':
				checkrights('managecampaigns');
				$apptitle = 'Active Campaigns';
				$countquery = "SELECT count(*) from projects where bcid = '$bcid' and active = 1 ";
				$mquery = "SELECT * from projects  where bcid = '$bcid'";
			case 'maninactive':	
				checkrights('managecampaigns');
				//$countquery = "SELECT count(*) from projects where bcid = '$bcid' ";
                                $cres = mysql_query("SELECT clients.* from clients where bcid = '$bcid'");
				while ($crow = mysql_fetch_array($cres))
					{
						$clientlist[$crow['clientid']] = $crow; 
					}
				if ($app == 'maninactive')
					{
						$apptitle = 'Inactive Campaigns';
						$countquery = "SELECT count(*) from projects where bcid = '$bcid' and active = 0";
						$mquery = "SELECT * from projects  where bcid = '$bcid' ";
					}
						
				$projres = mysql_query($mquery);
				$prct = 0;
				while ($projrow = mysql_fetch_array($projres))
					{
						if ($prct > 0 ) $prolist .= ",";
						$projectss[$projrow['projectid']] = $projrow;
						$prolist .= "'".$projrow['projectid']."'";
						$prct++;
					}
				
				$ct = 0;
				$disp = '<div id="ACampaignsLeftNavigation"><div class="apptitle">Campaigns</div>';
                                $disp .= '<div class="secnav"><input type="button" onclick="dialogwindow(\'newcamp\')" value="Create New"></div><p>Select a <br /> campaign to view settings.</p></div>';
                                $headers[] = 'Client';
				$headers[] = 'Campaign Name';
                                $headers[] = 'Status';
                                $headers[] = 'Date Created';
				$headers[] = 'Dial Mode';
				$headers[] = 'Dial Pacing';
				$headers[] = 'Dial Prefix';
				$headers[] = 'Recycle Period';
				$headers[] = 'AnsMac Detection';
				$headers[] = 'Actions';
				$cct = 0;
                                
				foreach ($projectss as $projrow)
					{
						if ($projrow['active'] == 1) $pstatus = 'Active';
						else $pstatus = 'DEACTIVATED';
						$hours = $projrow['worktime']/3600;
						$hours = number_format($hours,2);
						$prid = $projrow['projectid'];
						$amd = 'No';
						if ($projrow['amd'] == 1) $amd = 'Yes';
                                                $rows[$cct]['client'] = $clientlist[$projrow['clientid']]['company'];
						$rows[$cct]['projectname'] = '<a href="#" onclick="manage(\''.$projrow['projectid'].'\')">'.$projrow['projectname'].'</a>';
						$rows[$cct]['status']='<div id="active'.$prid.'" onclick="changeoption(\'active\',\''.$prid.'\')">'.$pstatus.'</div>';
						$rows[$cct]['creation'] = $projrow['datecreated'];
						$rows[$cct]['dialmode'] = '<div id=dialmode'.$projrow['projectid'].' onclick="changeoption(\'dialmode\',\''.$projrow['projectid'].'\');">'.$projrow['dialmode'].'</div>';
						$rows[$cct]['dialpace']='<div id=dialpace'.$projrow['projectid'].' onclick="changeoption(\'dialpace\',\''.$projrow['projectid'].'\');">'.$projrow['dialpace'].'</div>';
						$rows[$cct]['dialpredix']='<div id=prefix'.$projrow['projectid'].' onclick="changeoption(\'prefix\',\''.$projrow['projectid'].'\');">'.$projrow['prefix'].'</div>';
						$rows[$cct]['recpee']='<div id=recycle'.$projrow['projectid'].' onclick="changeoption(\'recycle\',\''.$projrow['projectid'].'\');">'.$projrow['recycle'].'</div>';
						$rows[$cct]['amd']='<div id="amd'.$prid.'" onclick="changeoption(\'amd\',\''.$prid.'\')">'.$amd.'</div>';

                        if ($projrow['active'] == 1)
						  $rows[$cct]['actions']='<a href="#" title="Refresh Leads" onclick="refreshleads(\''.$projrow['projectid'].'\')"><img src="icons/refresh.png"></img></a> | <a href="#" onclick="mancampactivesection = \'scripts\';manage_persist(\''.$projrow['projectid'].'\')" title="Edit Script"><img src="icons/script.png"></a> | <a href="#" title="Delete" onclick="deleteproj(\''.$projrow['projectid'].'\')"><img src="icons/delete.gif"></img></a>';
                        else
                          $rows[$cct]['actions']='n/a';
						$cct++;
					}
                                //var_dump($rows);
				$disp .= tablegen($headers,$rows,"100%","","campaigntable");
				$disp .= '<script>
	
								var campTable = jQuery(".campaigntable").dataTable({"iDisplayLength": 20,"aLengthMenu": [[10, 20, 50, -1], [10, 20, 50, "All"]]});
								$(".dataTables_filter input").hide();
								$(".dataTables_filter").append("<select id=\"selectCampaignStatus\"><option value=\"Active\">Active</option><option value=\"DEACTIVATED\">DEACTIVATED</option><option value=\"\">All</option></select>");
                                                                campTable.fnFilter( "Active", 2, false, false, false, false );
								$("#selectCampaignStatus").change(function(){
									var selectVal = $(this).val();
									
								    campTable.fnFilter( selectVal, 2, false, false, false, false );
								});
						</script>';
				echo $disp;
				break;
			case 'newcamp':
				checkrights('createcampaigns');
				echo " ";
				$clientres = mysql_query("SELECT  clients.* from clients where bcid = '$bcid'");
                               
                                $providers = providers::getall($bcid);
                                $providerlist = '';
                                if ($providers) 
                                {
                                    $ctprov = count($providers);
                                    foreach ($providers as $prov)
                                    {
                                        
                                        if ($prov['id'] != 1 || $ctprov < 1){
                                        $providerlist .= '<option value="'.$prov['id'].'">'.$prov['name'].' - '.$prov['username'].'</option>';
                                        }
                                    }
                                }
					while ($row = mysql_fetch_array($clientres))
					{
						$clientname = $row['company'];
						if (strlen($clientname) > 1) $clist .= '<option value="'.$row['clientid'].'">'.$clientname.'</option>';
					}


			
					$mquery = "SELECT * from projects  where bcid = '$bcid' ";
					
						
					$projres = mysql_query($mquery);
					$prct = 0;
					while ($projrow = mysql_fetch_array($projres))
						{
							if ($prct > 0 ) $prolist .= ",";
							$projectss[$projrow['projectid']] = $projrow;
							$prolist .= "'".$projrow['projectid']."'";
							$prct++;
						}
					$campaignSelect = '<select id="campaignSelect" disabled="disabled">';
					foreach ($projectss as $projrow){
						$campaignSelect .= '<option value="'.$projrow['projectid'].'">'.$projrow['projectname'].'</optin>';
					}
					$campaignSelect .= "</select>";
					?>
                <div class="entryform" style="width:300px; height:180px" title="Create Campaign">
                    <title>Create Campaign</title>
                    <div id="respmessage"></div>
                    <form onsubmit="">
                    	<table>
                    		<tr><td><label>Campaign Name:</label></td><td><input style="width:143px"type="text" id="projectname" /></td></tr>
                    		<tr><td><label>Client:</label></td><td><select name="clientid" id= "clientid" style="width:150px"><?=$clist;?></select></td></tr>
                                <tr><td><label>Campaign Mode:</label></td><td><select style="width:150px" id="dialmode"><option value="predictive">Predictive</option><option value="progressive" selected="selected">Progressive</option><option value="inbound">Inbound</option></select></td></tr>
                                <tr><td><label>Voice Provider:</label></td><td><select name="providerid" id="providerid_nc" style="width:150px"><?=$providerlist;?></select></td></tr>
                    		<tr><td><label>Clone Campaign</label></td><td>YES<input id="yesSelected" style="float:none; width:20px !important"  type="radio" name="clone" value="yes">NO<input checked="checked" style="float:none; width:20px !important" id="noSelected"type="radio" name="clone" value="no"></td></tr>
                    		<tr>&nbsp;<td></td><td><?=$campaignSelect; ?></td></tr>
                    	</table>
                        <div></div>
                        <!--<div><label>Campaign Description:</label> <input type="text" id="projectdesc" /></div>-->
                        <!--<div><label>Dial Mode:</label><select style="width:150px" id="dialmode"><option value="predictive">predictive</option><option value="progressive" selected>progressive</option></select></div>-->
                        <!--<div><label>Dial Pacing:</label><select style="width:150px" id="dialpace"><option>1</option><option>2</option><option>3</option><option>4</option></select></div>-->
                       
                        <div class="buttons"><input id="createnewprojectbutton" type="button" onClick="validate_createnewproject($('#projectname'), 'projectname')" value="Add">
                    </form>
                </div>
                <script type="text/javascript">
	                $("#yesSelected").click(function(){
	                	$("#campaignSelect").removeAttr('disabled');
	                });
	                $("#noSelected").click(function(){
	                	$("#campaignSelect").attr('disabled','disabled');
	                });
                </script>
<?				break;
			case 'newusers':
				checkrights('manageusers');
				echo " ";
				$teamres = mysql_query("SELECT teamname from teams where bcid = '$bcid'");
				while ($row = mysql_fetch_array($teamres))
					{
					$teamlist .= "<option>".$row['teamname']."</option>";
					}
					?>
                <div class="entryform" style="width:300px; height:180px" title="Create New User">
                    <form onsubmit="">
                    <title>Create User</title>
                    <div id="respmessage"></div>
                    <div><span class="label">Login:</span><input type="text" id="userlogin" onblur="validate(this,'userlogin');" /></div>
                     <div><span class="label">Password:</span><input type="password" id="userpass"  onblur="validate(this,'lengthonly');"  /> </div>
                     <div><span class="label">FirstName:</span><input type="text" id="afname" onblur="validate(this,'lengthonly');" /> </div>
                     <div><span class="label">LastName:</span><input type="text" id="alname" onblur="validate(this,'lengthonly');" /> </div>
                      <div><span class="label">Role:</span><select name="roleid" id="roleid"><?=getroledrop();?></select></div>
                     <div class="buttons">
                    <input type="button" class="button1" onClick="createnewuser()" value="Add">
                    
                     </div>
                    </form>
                </div>
    <?	
				break;
			case 'maninactiveagents':
				$isactiv = 0;
				$title = 'Inactive Users';
			case 'managents':
					include 'users_management/index.php';
				break;
            case 'manttracker':
                    include 'timetracker_management/index.php';
                break;
            case 'newttrackerevent':
                    include 'timetracker_management/dialoguewindowform.php';
                break;
			case 'manteams':
			checkrights('manageusers');
			$projectres = mysql_query("SELECT projectid, projectname from projects where bcid = '$bcid' ORDER BY projectname");
			while ($projectlist = mysql_fetch_array($projectres))
				{
					$plist .= '<option value="'.$projectlist['projectid'].'">';
					$plist .= $projectlist['projectname'];
					$plist .= "</option>";
					$projdet[$projectlist['projectid']] = $projectlist['projectname']; 
				}
			$agentres = mysql_query("SELECT teams.* FROM teams LEFT JOIN memberdetails ON teams.teamname = memberdetails.team where teams.bcid = '$bcid' GROUP BY teamname;");
				echo '<div class="apptitle">Manage Teams</div>
				<table  width="350" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);"><tr><td  class="tableheader" colspan="2">Create Team</td></tr>
				<tr><td class="center-title">Team Name:</td><td class=datas> <input type="text" id="teamname" style="width:150px"/></td></tr>
				<tr><td class="center-title" colspan="2"><button onClick="createnewteam()">Add</button></td></tr></table><br>
				
				<table width="100%" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF;border: 1px solid rgb(179, 179, 179);">
				<tr><td  class="tableheader" colspan="3">Current Teams</td></tr>
				<tr><td class=center-title>Team Name</td><td class=center-title></td><td class=center-title>Campaigns</td></tr>';
				while ($row = mysql_fetch_array($agentres))
					{
					$teamprojects = explode(";",$row['projects']);
					$prct =0;
					$tplist = "";
					$tid = $row['teamid'];
					foreach ($teamprojects as $tp)
						{
							if (strlen($tp) != 0) $tplist .= "<img src=\"icons/delete.gif\" height=\"12\" width=\"12\" onclick=\"removeprojteam('$tid','$tp')\">".$projdet[$tp];
							$prct++;
						}
					if (!$row['lastlogin']) {$llogin = 'No Data';}
					else $llogin = $row['lastlogin'];
					echo '<tr>';
					echo '<td class=datas><a href="#" onclick="getpage(\'teamdet\',\'&teamid='.$row['teamid'].'\')">'.$row['teamname'].'</a></td><td class=datas><a href="#" onclick="removeteam(\''.$row['teamid'].'\')">Delete</a></td><td class=datas style="text-align:left">'.$tplist.'<div id="addprojteam'.$row['teamid'].'" style="float:right"><img src="icons/add.gif" onclick="addprojteam(\''.$row['teamid'].'\')"></div></td>';
					echo '</tr>';
					}
				break;
			case 'newteam':
			?>
				
				<?
                break;
			case 'qaport':
			checkrights('qaleads');
			?><iframe src="client2.php" frameborder="0" id="qaframe"  name="qaframe" style="width:100%; height:800px">
				<?php
				break;
			case 'veport':
			checkrights('verifyleads');
				?><iframe src="client2.php?type=verifier" frameborder="0" id="qaframe"  name="qaframe" scrolling="auto"  style="width:100%; height:800px">
				<?php
				break;
			case 'manclient':
			checkrights('manageclients');
                            include "manageclient.php";
                            break;
			case 'loadcsv':
			checkrights('managelists');
				echo " ";
				include "csvloader.php";
				break;
			case 'newlist':
                checkrights('managelists');
				echo "";
				include "listloader.php";
            	break;
            case 'newexclusionlist':
			checkrights('managelists');
				echo "";
				include "exclusion.php";
				break;
                        case 'listupdatefile':
			checkrights('managelists');
				echo "";
				include "listupdatefile.php";
				break;
			case 'manlist':
			checkrights('managelists');
				echo " ";
				include "managelist.php";
				break;
			case 'agentdispo':
			checkrights('all');
				echo " ";
				echo '<iframe src="mlogan/index.php?rp=1&pr=1&form=1" frameborder="0" id="qaframe"  name="qaframe" width="900" height="400" scrolling="no">';
				break;
						
			case 'newclient':
			checkrights('manageclients');
				?>
 <div class="entryform" style="width:300px;">
                    <form onsubmit="">
                    <title>Create New Client</title>
                    <div id="respmessage"></div>
        <div>Company Name: <input type="text" id="company" onblur="validate(this,'lengthonly');" /></div>
        <div>Address1:<input type="text" id="address1"/></div>
<div>Address2: <input type="text" id="address2"  /></div>
<div>City/Suburb: <input type="text" id="city"  /></div>
<div>State: <input type="text" id="state" /></div>
<div>Company Website: <input type="text" id="companyurl" /></div>
<div>Company Email: <input type="text" id="email" /></div>
<div>Company Phone: <input type="text" id="phone" /></div>
<div>Fax: <input type="text" id="altphone" /></div>
<div><input type="button" onClick="createnewclient()" value="Add" /></div>
       </form>
 </div>
<?php

break;
		}
	exit;
	}
?>

