<?php
if ($act == 'newclient')
	{
		extract($_REQUEST);
                if (!checkuser($cemail))
                {
                    echo "exists";
                    exit;
                }
		mysql_query("INSERT into bc_clients set company = '$ccompany', email= '$cemail', name='$cname', phone = '$cphone', cpid = '$cpid', rateid='$rateid', ratetype= '$ratetype', status = 'Active', logo ='$logo'");
		$nbcid = mysql_insert_id();
                if (!$nbcid)
                {
                    echo 'error';
                    exit;
                }
		$userlog = $_REQUEST['userlog'];
                $ss = time().$userlog;
		$userp = substr(md5($ss),1,10);
                Mailer::emailpass($cemail,$userp);
		mysql_query("INSERT INTO members set userlogin = '$cemail',email='$cemail', userpass = '$userp', usertype = 'user', bcid = '$nbcid', roleid = 2");
                mysql_query("INSERT into bc_features set bcid = '$nbcid'");
                echo $nbcid;
                exit;
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
if ($act == 'updatestatic')
{
    extract($_POST);
    $sid = $_REQUEST['staticid'];
    mysql_query("update bc_static set name='".mysql_real_escape_string($name)."', title = '".mysql_real_escape_string($title)."', content = '".mysql_real_escape_string($staticcontent)."', type='vip_page', lastupdate='".time()."' where staticid = '$sid'");
    exit;
}
if ($act == 'addstatic')
{
    extract($_POST);
    mysql_query("INSERT into bc_static set name='".mysql_real_escape_string($name)."', title = '".mysql_real_escape_string($title)."', content = '".mysql_real_escape_string($staticcontent)."', type='vip_page', lastupdate='".time()."'");
    exit;
}
if ($act == 'editstatic')
{
    $staticid = $_REQUEST['staticid'];
    $r = getbyparams('bc_static', "staticid = '$staticid'", "staticid");
    $static = $r[$staticid];
    ?>
<a onclick="dostatic()" class="jbut" href="#">Back</a>
<form name="newstatic" id="newstatic">
<table width="800">
    <tr><td>Name</td><td><input type="text" name="name" value="<?php echo $static['name'];?>"></td></tr>
    <tr><td>Title</td><td><input type="text" name="title" value="<?php echo $static['title'];?>"></td></tr>
    <tr><td colspan="2">Content</td></tr>
    <tr><td colspan="2"><textarea name="staticcontent" id="staticcontent" style="width:100%; height:400px"><?php echo $static['content'];?></textarea></td></tr>
    <tr><td><a href="#" class="jbut" onclick="updatestatic('<?php echo $staticid;?>')">Save</a></td></tr>
</table>
</form>
        <?
        exit;
}
if ($act == 'newstatic')
{
    ?>
<a onclick="dostatic()" class="jbut" href="#">Back</a>
<form name="newstatic" id="newstatic">
<table width="800">
    <tr><td>Name</td><td><input type="text" name="name"></td></tr>
    <tr><td>Title</td><td><input type="text" name="title"></td></tr>
    <tr><td colspan="2">Content</td></tr>
    <tr><td colspan="2"><textarea name="staticcontent" id="staticcontent" style="width:100%;height:400px"></textarea></td></tr>
    <tr><td><a href="#" class="jbut" onclick="addstatic()">Save</a></td></tr>
</table>
</form>
        <?
        exit;
}
if ($act == 'static')
{
    $statics = getdatatable("bc_static","staticid");
    $headers = array();
    $headers[] = 'Name';
    $headers[] = 'Type';
    $headers[] = 'Last Update';
    foreach ($statics as $static)
    {
        $rows[$static['staticid']]['name'] = '<a href="#" onclick="editstatic(\''.$static['staticid'].'\')">'.$static['name'].'</a>';
        $rows[$static['staticid']]['type'] = $static['type'];
        $rows[$static['staticid']]['last'] = date("Y-m-d- H:i:s",$static['lastupdate']);
        
    }
   ?>
<input type="button" class="jbut" value="New Static Page" onclick="newstatic()" />
        <?php
         echo tablegen($headers,$rows);
        exit;
}
if ($act == 'helptool')
{
    $statics = getdatatable("pageguide","id");
    $headers = array();
    $headers[] = 'Name';
    $headers[] = 'Section';
    $headers[] = 'Selector';
    foreach ($statics as $static)
    {
        $sname = strlen($static['name']) < 1 ? '...': $static['name']; 
        $rows[$static['id']]['name'] = '<a href="#" onclick="edithelptool(\''.$static['id'].'\')">'.$sname.'</a>';
        $rows[$static['id']]['section'] = $static['section'];
        $rows[$static['id']]['selector'] = $static['selector'];
        
    }
   ?>
<input type="button" class="jbut" value="New Helptool" onclick="newhelptool()" />
        <?php
         echo tablegen($headers,$rows);
        exit;
}
if ($act == 'edithelptool')
{
    $staticid = $_REQUEST['staticid'];
    $r = getbyparams('pageguide', "id = '$staticid'", "id");
    $static = $r[$staticid];
    ?>
<form name="newstatic" id="newstatic">
<table width="800">
    <tr><td>Name</td><td><input type="text" name="name" value="<?php echo $static['name'];?>"></td></tr>
    <tr><td>Section</td><td><input type="text" name="section" value="<?php echo $static['section'];?>"></td></tr>
    <tr><td>Selector</td><td><input type="text" name="selector" value="<?php echo $static['selector'];?>"></td></tr>
    <tr><td>Position</td><td><select name="position">
                <option value="left">Left</option><option value="right" <?php echo $static['position'] == 'right' ? 'selected':'';?>>Right</option></select></td></tr>
    <tr><td colspan="2">Content</td></tr>
    <tr><td colspan="2"><textarea name="helptext" id="helptext" style="width:100%; height:400px"><?php echo $static['helptext'];?></textarea></td></tr>
    <tr><td><a href="#" class="jbut" onclick="updatehelptool('<?php echo $staticid;?>')">Save</a></td></tr>
</table>
</form>
        <?
        exit;
}
if ($act == 'updatehelptool')
{
    extract($_POST);
    $sid = $_REQUEST['staticid'];
    mysql_query("update pageguide set name='".mysql_real_escape_string($name)."', section = '".mysql_real_escape_string($section)."', helptext = '".mysql_real_escape_string($helptext)."', selector='".mysql_real_escape_string($selector)."' ,position = '".mysql_real_escape_string($position)."' where id = '$sid'");
    exit;
}
if ($act == 'newhelptool')
{
    ?>
<form name="newstatic" id="newstatic">
<table width="800">
    <tr><td>Name</td><td><input type="text" name="name"></td></tr>
    <tr><td>Section</td><td><input type="text" name="section"></td></tr>
    <tr><td>Selector</td><td><input type="text" name="selector"></td></tr>
    <tr><td>Position</td><td><select name="position"><option value="left">Left</option><option value="right">Right</option></select></td></tr>
    <tr><td colspan="2">Content</td></tr>
    <tr><td colspan="2"><textarea name="helptext" id="helptext" style="width:100%; height:400px"></textarea></td></tr>
    <tr><td><a href="#" class="jbut" onclick="addhelptool()">Save</a></td></tr>
</table>
</form>
        <?
        exit;
}
if ($act == 'addhelptool')
{
    extract($_POST);
    mysql_query("insert into pageguide set name='".mysql_real_escape_string($name)."', section = '".mysql_real_escape_string($section)."', helptext = '".mysql_real_escape_string($helptext)."', selector='".mysql_real_escape_string($selector)."' ,position = '".mysql_real_escape_string($position)."'");
    exit;
}
if ($act == 'addnewrate')
{
    $rates = new crud("bc_rates");
    $usagef = array();
    foreach ($_POST as $key=>$val)
    {
        if ($key != 'callrate_minute')
        {
            $usagef[$key] = $val;
        }
        else {
            $crm = $val;
        }
    }
    $id = $rates->create($usagef);
    mysql_query("INSERT into bc_rates_calls set rateid = '$id', callrate_name = 'Default', callrate_pattern = 'XXXXXXXXXX', callrate_cost = '$crm'");
    echo $id;
    exit;
}
if ($act == 'newrate')
{
    ?>
<form name="newrateform" id="newrateform">
<table width="380" cellspacing="12">
<tr><td><b>Rate Name:</b> </td><td><input type="text" id="ratename" name="ratename"  /></td></tr>
<tr><td><b>Monthly Rate: </b></td><td><input type="text" id="rate_monthly" name="rate_monthly" /></td></tr>
<tr><td><b>Daily Rate: </b></td><td><input type="text" id="rate_daily" name="rate_daily" /></td></tr>
<tr><td><b>Hourly Rate: </b></td><td><input type="text" id="rate_hourly" name="rate_hourly" /></td></tr>
<tr><td><b>PerMinute Rate: </b></td><td><input type="text" id="rate_minute" name="rate_minute"  /></td></tr>
<tr><td><b>Default PerMinute Call Rate: </b></td><td><input type="text" id="callrate_minute" name="callrate_minute"  /></td></tr>
<tr><td colspan="2"><a href="#" onclick="addnewrate()" class="jbut">Submit</a></td></tr>
</table>
</form>
<?
exit;
}
if ($act == 'deleteratetable')
{
    $id = $_REQUEST['id'];
    mysql_query("UPDATE bc_rates_calls set isdeleted = 1 where id = $id");
    $act = 'editrate';
}
if ($act == 'addratetable')
{
    $rateid = $_REQUEST['rateid'];
    mysql_query("INSERT into bc_rates_calls set rateid = '$rateid'");
    $act = 'editrate';
}
if ($act == 'editrate')
{
    $rateid = $_REQUEST['rateid'];
    $res = mysql_query("SELECT * from bc_rates where rateid = $rateid");
    $rate = mysql_fetch_assoc($res);
    $res = mysql_query("SELECT * from bc_rates_calls where rateid = $rateid and isdeleted = 0 order by priority ASC");
    $rate_calls = array();
    while ($row = mysql_fetch_assoc($res))
    {
        $rate_calls[$row['id']] = $row;
    }
    ?>
<form name="editrateform">
<table width="480" cellspacing="12">
<tr><td><b>Rate Name:</b> </td><td><input type="text" id="ratename" name="ratename" value="<?=$rate['ratename'];?>" onchange="triggerchange()" onblur="ratechange(this)" /> <input type=hidden name=act value=newclient>
        <input type=hidden name=rateid id="rateid" value="<?=$rateid;?>"></td></tr>
<tr><td><b>Monthly Rate: </b></td><td><input type="text" id="rate_monthly" name="rate_monthly" value="<?=$rate['rate_monthly'];?>" onchange="triggerchange()" onblur="ratechange(this)" /></td></tr>
<tr><td><b>Daily Rate: </b></td><td><input type="text" id="rate_daily" name="rate_daily"  value="<?=$rate['rate_daily'];?>" onchange="triggerchange()" onblur="ratechange(this)" /></td></tr>
<tr><td><b>Hourly Rate: </b></td><td><input type="text" id="rate_hourly" name="rate_hourly"  value="<?=$rate['rate_hourly'];?>" onchange="triggerchange()" onblur="ratechange(this)" /></td></tr>
<tr><td><b>PerMinute Rate: </b></td><td><input type="text" id="rate_minute" name="rate_minute"  value="<?=$rate['rate_minute'];?>" onchange="triggerchange()" onblur="ratechange(this)" /></td></tr>
</table>
      <table width="480" id="ratetable">
        <thead><tr><th class="tableheader">Name</th><th class="tableheader">Pattern</th><th class="tableheader" title="Cost per Minute">Cost</th><th class="tableheader" title="Minimum Unit">Min</th><th class="tableheader" width="50">Order</th><th class="tableheader" width="50">Action</th></tr></thead>
        <tbody>
       <?php
        //
        $c = 1;
        $rctab = '';
        foreach ($rate_calls as $rc)
        {
            $c++;
            if ($c % 2) $class = "tableitem";
            else $class = "tableitem_";
            $rctab .= '<tr class="'.$class.'">';
 $rctab .= '<td><input type="text" name="callrate_name" value="'.$rc['callrate_name'].'" onchange="triggerchange()" onblur="callratechange(this,\''.$rc['id'].'\')" placeholder="Change name..." /></td>';
  $rctab .= '<td><input type="text" name="callrate_pattern" value="'.$rc['callrate_pattern'].'" onchange="triggerchange()" onblur="callratechange(this,\''.$rc['id'].'\')" /></td>';
    $rctab .= '<td width="50"><input style="width:50px" type="text" name="callrate_cost" value="'.creds($rc['callrate_cost']).'" onchange="triggerchange()" onblur="callratechange(this,\''.$rc['id'].'\')" /></td>';
    $selectedmin = ($rc['callrate_unit'] == 'minute') ? 'selected':'';
    $selectedsec = ($rc['callrate_unit'] == 'second') ? 'selected':'';
  $rctab .= '<td><select name="callrate_unit" value="'.$rc['callrate_unit'].'" onchange="triggerchange();callratechange(this,\''.$rc['id'].'\')"><option value="minute" '.$selectedmin.'>minute</option><option value="second" '.$selectedsec.'>second</option></select></td>';
  $rctab .= '<td width="50"><input type="text" style="width:50px" name="priority" value="'.$rc['priority'].'"  onchange="triggerchange()" onblur="callratechange(this,\''.$rc['id'].'\')" /></td>';
  $rctab .= '<td width="50"><a href="#" onclick="deleteratetable(\''.$rc['id'].'\',\''.$rate['rateid'].'\')">Delete</a></td>';        
  $rctab .= '</tr>';
        }
        $rctab .= '<tr><td colspan="5"><a href="#" class="jbut" onclick="addratetable(\''.$rate['rateid'].'\')">Add Entry</a></td></tr>';
        echo $rctab;
          ?>
        </tbody>
    </table>
</form>
<?php
    exit;
}
if ($act == 'updatelogo')
{
    $bcid = $_REQUEST['bcid'];
    $logo = $_REQUEST['logo'];
    $cres = mysql_query("SELECT logo from bc_clients where bcid = $bcid");
    $crow = mysql_fetch_assoc($cres);
    $currentlogo = $crow['logo'];
    unlink("../logo/$currentlogo");
    mysql_query("update bc_clients set logo = '$logo' where bcid = $bcid");
    exit;
}
if ($act == 'editlogo')
{
    $bcid = $_REQUEST['bcid'];
    ?>
    <form name="uploadlogo" id="uploadlogo">
<input type="hidden" name="act" value="upload" />
                <strong>Logo upload</strong>
                <input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>
                <div><input id="MAX_FILE_SIZE" name="file" type="file" style="float:left;margin-left:30px" /></div>
                <div class="buttons">
				<input type="button" value="Upload" onclick="uploadFile('uploadlogo','super.php')"></div>
                <div class="clear"></div>
                <div id="uploaderr" style="display:none"></div>
                <div id="progress">
                    <div id="pbar"></div>
                </div>
                
        </form>
<input type="hidden" id="logo" value="" />
<a href="#" class="jbut" onclick="logoupdate('<?=$bcid;?>')">Update</a>
<?php
exit;
}
if ($act == 'deluser')
{
    $userid = $_REQUEST['userid'];
    mysql_query("update members set isdeleted = 1,active = 0 where userid = '$userid'");
    exit;
}
if ($act == 'viewcallcostsdet')
{
    $clients = getdatatable("bc_clients",'bcid');
    $rates = getdatatable("bc_rates","rateid");
    $callrates = getdatatable("bc_rates_calls","id");
    extract($_GET);
    if ($client == 'all')
    {
        echo "Must Select a client to view detailed report";
        exit;
    }
    $prov = "and provider is not null and provider != ''";
    $query = "SELECT provider, phone, startepoch, dialedtime, answeredtime AS 'duration',rateid,callrateid,callcost  FROM finalhistory WHERE bcid = $client and startepoch >= '".strtotime($start)."' AND startepoch <='".strtotime($end." 23:59:59")."' and systemdisposition = 'ANSWER' $prov";
    
    
    $res = mysql_query($query);
    $ct = 0;
    $headers[] = 'Voice Provider';
    $headers[] = 'Phone';
    $headers[] = 'Date';
    $headers[] = 'Start';
    $headers[] = 'Answered';
    $headers[] = 'End';
    $headers[] = 'Duration';
    $headers[] = 'Rate per Min';
    $headers[] = 'Minimum Unit';
    $headers[] = 'Call Cost';
    $td = 0;
    while ($row = mysql_fetch_assoc($res))
    {
        
        $rows[$ct]['provider'] = $row['provider'];
        $rows[$ct]['phone'] = $row['phone'];
        $rows[$ct]['date'] = date("Y-m-d H:i:s",$row['startepoch']);
        $rows[$ct]['start'] = $row['startepoch'];
        $ringtime = $row['dialedtime'] - $row['duration'];
        $answeredat = $row['startepoch'] + $ringtime;
        $endedat = $row['startepoch'] + $row['dialedtime'];
        $rows[$ct]['Answered'] = $answeredat;
        $rows[$ct]['ended']= $endedat;
        $dsecs = $row['duration'];
        $dmins = $dsecs / 60;
        $dmins = intval($dmins);
        $fract = $dsecs % 60;
        
        $rows[$ct]['duration'] = $dmins."m ".$fract."s";
        $rows[$ct]['rpm']= $callrates[$row['callrateid']]['callrate_cost'];
        $rows[$ct]['minimum'] = "1".$callrates[$row['callrateid']]['callrate_unit'];
        $rows[$ct]['callcost'] = $row['callcost'];
        $td = $td + $row['duration'];
        $ct++;
    }
    $disp = tablegen($headers,$rows,780);
     $tit = $clients[$client]['company'];
     $tdmin = intval($td / 60);
     $tdsecs = $td % 60;
    ?>
        <br />
        <table width="780">
        <tr><td colspan="4"><b>Call Costs for <?php echo $tit;?></b></td></tr>
        <tr><td>Period Covered:</td><td><?=$start;?> to <?=$end;?></td></tr>
        <tr><td>Total Duration:</td><td><?php echo  $tdmin . "minutes ".$tdsecs."seconds" ;?></td></tr>
        </table>
    <?
    echo $disp;
    //echo $query;
    exit;
}
if ($act == 'viewcallcosts')
{
    $clients = getdatatable("bc_clients",'bcid');
    extract($_GET);
    $bclient = '';
    $qclient = 'bcid,';
    $gclient = 'bcid,provider';
    if ($client == 'all')
    {
        $headers[] = 'Client';
    }
    if ($client != 'all')
    {
        $bclient = 'bcid ='.$client.' AND ';
        $qclient = '';
        $gclient = 'provider';
    }
    $prov = "and provider is not null and provider != ''";
     $query = "SELECT $qclient provider,sum(answeredtime) AS 'duration' FROM finalhistory WHERE $bclient startepoch >= '".strtotime($start)."' AND startepoch <='".strtotime($end." 23:59:59")."' and systemdisposition = 'ANSWER' $prov GROUP BY $gclient;";
    $res = mysql_query($query);
    $ct = 0;
    $headers[] = 'VOIP Provider';
    $headers[] = 'Total Calls Duration (secs)';
    $td = 0;
    while ($row = mysql_fetch_assoc($res))
    {
        if ($client == 'all')
        {
            $rows[$ct]['client'] = $clients[$row['bcid']]['company'];
        }
        $rows[$ct]['provider'] = $row['provider'];
        $dmins = intval($row['duration'] / 60);
        $dsecs = $row['duration'] % 60;
        $rows[$ct]['duration'] = $dmins."m ".$dsecs."s";
        $td = $td + $row['duration'];
        $ct++;
    }
    $disp = tablegen($headers,$rows,780);
    $tit = 'ALL CLIENTS';
    if ($client != 'all')
    {
        $tit = $clients[$client]['company'];
    }
    $tdmin = intval($td / 60);
     $tdsecs = $td % 60;
    ?>
        <br />
        <table width="780">
        <tr><td colspan="4"><b>Call Costs for <?php echo $tit;?></b></td></tr>
        <tr><td>Period Covered:</td><td><?=$start;?> to <?=$end;?></td></tr>
        <tr><td>Total Duration:</td><td><?php echo $tdmin ."minutes ".$tdsecs."seconds";?></td></tr>
        </table>
    <?
    echo $disp;
   // echo $query;
    exit;
}
if ($act == 'callcosts')
{
    $clients = getdatatable("bc_clients where status = 'Active' order by company ASC",'bcid');
		foreach ($clients as $client)
			{
				$drop .= '<option value="'.$client['bcid'].'">'.$client['company'].'</option>';
			}
		$disp = '
		
		<table>
		<tr><td>Client</td><td><select name="client" id="client"><option value="all">All Clients</option>'.$drop.'</select></td></tr>
                    
        <tr><td>Date Start</td>
    <td><input type="text" name="start" class="dateinput" id="start" /></td></tr>
    	<tr><td>Date End</td>
    <td><input type="text" name="end" class="dateinput" id="end"/></td></tr>
    <tr>
  	<td colspan="2" align="left">
    <a href="#" onClick="viewrep(\'viewcallcosts\')" class="jbut">View Summary</a> <a href="#" onClick="viewrep(\'viewcallcostsdet\')" class="jbut">View Detailed</a>
    </td>
  </tr>
    	</table>
		<div id="resultdetails">
		</div>';
}
if ($act == 'viewlogsummary')
{
    extract($_GET);
    $usage = getbcusage($client,$start,$end);
    ?>
        <br />
        <table width="100%">
        <tr><td colspan="4"><b>Usage Summary</b></td></tr>
        <tr><td>Period Covered:</td><td><?=$start;?> to <?=$end;?></td></tr>
        <tr><td>Total Hours:</td><td><?=$usage['usagehours'];?></td></tr>
        </table>
		 <?
		 $headers[] = 'Date';
		 $headers[] = 'No. of Users';
		 $headers[] = 'Total Duration';
		 //$headers[] = 'Credits Used';
		 $ct = 0;
                 $urows = array();
		foreach ($usage['detailed'] as $ud)
			{
				$rows[$ud['date']]['date']= $ud['date'];
                                $urows[$ud['date']]['users'][$ud['userid']] = $ud['userid'];
                                $rows[$ud['date']]['nousers']= count($urows[$ud['date']]['users']);
				
				$urows[$ud['date']]['duration']= $urows[$ud['date']]['duration'] + ($ud['logout'] - $ud['login']);
                                $ms = tominsecs($urows[$ud['date']]['duration']);
                                $rows[$ud['date']]['duration'] = $ms['minutes']."m ".$ms['seconds']."s";
				//$rows[$ct]['7']= number_format(($ud['logout'] - $ud['login']) / 3600 * $rate[$ud['usagetype']],2);
				$ct++;
				
			}
		$disp = tablegen($headers,$rows);
		echo $disp;
		exit;
	
}
if ($act == 'viewlogdetails')
	{
		extract($_GET);
		$agents = getagentnames();
		$usage = getbcusage($client,$start,$end);
		$cost = getusagecost($client,$usage['usagesecs']);
		$rate = getrates($client);
		?>
        <br />
        <table width="100%">
        <tr><td colspan="4"><b>Usage Details</b></td></tr>
        <tr><td>Period Covered:</td><td><?=$start;?> to <?=$end;?></td></tr>
        <tr><td>Total Hours:</td><td><?=$usage['usagehours'];?></td></tr>
        </table>
		 <?
		 $headers[] = 'Date';
		 $headers[] = 'User';
		 $headers[] = 'Start';
		 $headers[] = 'End';
		 $headers[] = 'Duration';
		 //$headers[] = 'Credits Used';
		 $ct = 0;
		foreach ($usage['detailed'] as $ud)
			{
				$rows[$ct]['1']= $ud['date'];
				$rows[$ct]['3']= $agents[$ud['userid']];
				$rows[$ct]['4']= date('h:i:s A',$ud['login']);
				$rows[$ct]['5']= date('h:i:s A',$ud['logout']);
                                $t = $ud['logout'] - $ud['login'];
                                $ms = tominsecs($t);
				$rows[$ct]['6']= $ms['minutes']."m ".$ms['seconds']."s";
				//$rows[$ct]['7']= number_format(($ud['logout'] - $ud['login']) / 3600 * $rate[$ud['usagetype']],2);
				$ct++;
				
			}
		$disp = tablegen($headers,$rows);
		echo $disp;
		exit;
	}
if ($act == 'delprov')
	{
		$ext = $_REQUEST['prov'];
		mysql_query("delete from bc_providers where name = '$ext'");
		header("Location: super.php?act=extensions&message=Provider deleted Successfully");
	}
if ($act == 'delext')
	{
		$ext = $_REQUEST['ext'];
		mysql_query("delete from bc_phones where name = '$ext'");
		header("Location: super.php?act=extensions&message=Extension deleted Successfully");
	}
if ($act == 'saveprov')
{
    extract($_POST);
    mysql_query("INSERT into bc_providers set name = '$name', provider= '$provider', username='$username',fromuser='$username', secret = '$secret', host='$host', register='$register', bcid= '$bcid'");
    header("Location: super.php?act=providers&message=New Voice Provider Added");
}
if ($act == 'saveext')
	{
		extract($_POST);
		if (strpos($username,'-'))
			{
				$range = explode("-",$username);
				$start = intval($range[0]);
				$end = intval($range[1]);
				$ct = 0;
				while ($start <= $end)
					{
						mysql_query("INSERT into bc_phones set name = '$start', defaultuser='$start', secret = '$secret', bcid= '$bcid'");
						$start++;
						$ct++;
					}
				header("Location: super.php?act=extensions&message=$ct extensions created Successfully");
			}
		else 
			{
				mysql_query("INSERT into bc_phones set name = '$username', defaultuser='$username', secret = '$secret', bcid= '$bcid'");
				header("Location: super.php?act=extensions&message=Extension created Successfully");
			}
	}
if ($act == 'updateprov')
	{
		extract($_POST);
                $prov = new providers();
                $prov->findByName($extname);
                $prov->username = $username;
                $prov->provider = $provider;
                $prov->secret = $secret;
                $prov->host = $host;
                $prov->bcid = $bcid;
                $prov->register = $register;
                $prov->update();        
		//mysql_query("UPDATE bc_providers set username='$username', secret = '$secret',
                   // bcid= '$bcid',host='$host',register='$register' where name = '$extname'");
		header("Location: super.php?act=providers&message=Provider updated Successfully");
	}
if ($act == 'updateext')
	{
		extract($_POST);
		mysql_query("UPDATE bc_phones set name = '$username', defaultuser='$username', secret = '$secret', bcid= '$bcid' where name = '$extname'");
		header("Location: super.php?act=extensions&message=Extension updated Successfully");
	}

if ($act == 'usage')
	{
		$clients = getdatatable("bc_clients order by company ASC",'bcid');
		foreach ($clients as $client)
			{
                                if ($client['status'] == 'Active') $drop .= '<option value="'.$client['bcid'].'">'.$client['company'].'</option>';
			}
		$disp = '
		
		<table>
		<tr><td>Client</td><td><select name="client" id="client"><option></option>'.$drop.'</select></td></tr>
        <tr><td>Date Start</td>
    <td><input type="text" name="start" class="dateinput" id="start" /></td></tr>
    	<tr><td>Date End</td>
    <td><input type="text" name="end" class="dateinput" id="end"/></td></tr>
    <tr>
  	<td colspan="2" align="left"><a href="#" onClick="viewrep(\'viewlogsummary\')" class="jbut"> View Summary</a>
    <a href="#" onClick="viewrep(\'viewlogdetails\')" class="jbut">View Detailed</a>
    </td>
  </tr>
    	</table>
		<div id="resultdetails">
		</div>';
	}
if ($act == 'editprov')
	{
		$clients = getdatatablesorted("bc_clients",'bcid','company');
		
		$providers = getdatatable("bc_providers",'name');
		$prov = $providers[$_REQUEST['prov']];
		foreach ($clients as $client)
			{
				$drop .= '<option value="'.$client['bcid'].'">'.$client['company'].'</option>';
			}
		$disp = '

        <form name="addprovider" action="super.php?act=updateprov" method="post" >
        
        <table width="400">
        
        <tr><td>Client</td><td>
        <input type="hidden" name="extname" value="'.$prov['name'].'" />
        <select name="bcid"><option value="'.$prov['bcid'].'">'.$clients[$prov['bcid']]['company'].'</option>'.$drop.'</select></td></tr>
        <tr><td>Provider</td><td><input type="text" name="provider" value="'.$prov['provider'].'" /></td></tr>
        <tr><td>Username</td><td><input type="text" name="username" value="'.$prov['username'].'" /></td></tr>
        <tr><td>Password</td><td><input type="text" name="secret" value="'.$prov['secret'].'" /></td></tr>
        <tr><td>Host/Server</td><td><input type="text" name="host" value="'.$prov['host'].'" /></td></tr>
            <tr><td>Register</td><td><select name="register"><option value="no">No</option><option value="yes" ';
        $disp .= $prov['register'] == 'yes' ? 'selected="selected" ':'';
        $disp .=    '>Yes</option></select></td></tr>
        <tr><td colspan="2"><input type="submit" value="save" /></td></tr>
        
        
        </table></form>
		';
	}
if ($act == 'editext')
	{
		$clients = getdatatablesorted("bc_clients",'bcid','company');
		
		$extensions = getdatatable("bc_phones",'name');
		$ext = $extensions[$_REQUEST['ext']];
		foreach ($clients as $client)
			{
				$drop .= '<option value="'.$client['bcid'].'">'.$client['company'].'</option>';
			}
                if ($_REQUEST['sub'] == 1)
                {
                    $but = '<a href="#" class="subbut" onclick="clienteditext()">Save</a>';
                }
                else {
                    $but = '<input type="submit" value="Save" class="subbut" />';
                }
		$disp = '

        <form name="addextension" action="super.php?act=updateext" method="post" >
        
        <table width="270">
        
        <tr><td>Client</td><td>
        <input type="hidden" name="extname" value="'.$ext['name'].'" />
        <select name="bcid"><option value="'.$ext['bcid'].'">'.$clients[$ext['bcid']]['company'].'</option>'.$drop.'</select></td></tr>
        <tr><td>Username</td><td><input type="text" name="username" value="'.$ext['name'].'" /></td></tr>
        <tr><td>Password</td><td><input type="text" name="secret" value="'.$ext['secret'].'" /></td></tr>
        <tr><td colspan="2">'.$but.'</td></tr>
        
        
        </table></form>
		';
                echo $disp;
                exit;
	}
if ($act == 'addprovider')
{
    $clients = getdatatablesorted("bc_clients",'bcid','company');
		foreach ($clients as $client)
			{
				$drop .= '<option value="'.$client['bcid'].'">'.$client['company'].'</option>';
			}
		?>
        <form name="addprovider" action="super.php?act=saveprov" method="post" >
        <table width="400">
        <tr><td>Account Name</td><td><input type="text" name="name" /></td></tr>
        <tr><td>Provider Name</td><td><input type="text" name="provider" /></td></tr>
        <tr><td>Client</td><td><select name="bcid"><option></option><?=$drop;?></select></td></tr>
        <tr><td>Username</td><td><input type="text" name="username" /></td></tr>
        <tr><td>Password</td><td><input type="text" name="secret" /></td></tr>
        <tr><td>Host/Server</td><td><input type="text" name="host" /></td></tr>
        <tr><td>Registration Required</td>
            <td><select name="register"><option value="no">No</option><option value="yes">Yes</option></select></td></tr>
        <tr><td colspan="2"><input type="submit" value="Add" class="jbut" /></td></tr>
        
        
        </table></form>
        <?
		exit;
}
if ($act == 'addext')
	{
		$clients = getdatatablesorted("bc_clients",'bcid','company');
		foreach ($clients as $client)
			{
				$drop .= '<option value="'.$client['bcid'].'">'.$client['company'].'</option>';
			}
		?>
        <form name="addextension" action="super.php?act=saveext" method="post" >
        <table width="300">
        
        <tr><td>Client</td><td><select name="bcid"><option></option><?=$drop;?></select></td></tr>
        <tr><td>Username</td><td><input type="text" name="username" /></td></tr>
        <tr><td>Password</td><td><input type="text" name="secret" /></td></tr>
        <tr><td colspan="2"><input type="submit" value="Add" class="jbut" /></td></tr>
        
        
        </table></form>
        <?
		exit;
	}
if ($act == 'partners')
{
    //$members = getdatatable('members','userid');
    $extres = mysql_query("select * from bc_partners where isdeleted = 0");
                while ($row = mysql_fetch_assoc($extres))
                {
                    $members[$row['cpid']] = $row;
                }
    
    $header[]='Name';
    $header[]='Company';
    $header[]='Email';
    $header[]='Phone';
    $header[]='';
    foreach ($members as $member)
    {
        $rows[$member['cpid']]['2'] = $member['partner_name'];
        $rows[$member['cpid']]['3'] = $member['company'];
        $rows[$member['cpid']]['4'] = $member['email'];
        $rows[$member['cpid']]['5'] = $member['phone'];
        $rows[$member['cpid']]['6'] = '<a href="#" onclick="editpartner(\''.$member['cpid'].'\')">Edit</a> | <a href="#" onclick="deletepartner(\''.$member['cpid'].'\')">Delete</a>';
        
    }
    $disp = tablegen($header,$rows);
    ?>
        <h3 class="stitle"><i>Account Managers</i></h3>
        <a href="#" onclick="newpartner('<?php echo $_REQUEST['bcid'];?>')" class="jbut">Create Account Manager</a>
    <?php
    echo $disp;
    exit;
}
if ($act == 'users')
{
    //$members = getdatatable('members','userid');
    $extres = mysql_query("select * from members where bcid = '".$_REQUEST['bcid']."'");
                while ($row = mysql_fetch_assoc($extres))
                {
                    $members[$row['userid']] = $row;
                }
    $memberdetails = getdatatable('memberdetails', 'userid');
    $roles = getdatatable('roles','roleid');
    $clients = getdatatable('bc_clients','bcid');
    $header[]='';
    $header[]='Name';
    $header[]='Role';
    $header[]='Status';
    $header[]='Action';
    $header[]='Client';
    foreach ($members as $member)
    {
        if ($member['isdeleted'] == '0' && ($member['roleid'] == '1' || $member['roleid'] == '2'))
        {
        $rows[$member['userid']]['1'] = '<input type="checkbox" name="bulkaction" value="'.$member['userid'].'">';
        $rows[$member['userid']]['2'] = $member['userlogin'];
        $rows[$member['userid']]['3'] = $roles[$member['roleid']]['rolename'];
        $rows[$member['userid']]['4'] = $member['active'] == 1 ? "Active" : "Inactive";
        $acto = '<a href="#" onclick="deluser(\''.$member['userid'].'\',\''.$_REQUEST['bcid'].'\')">Delete</a> | <a href="#" onclick="editadmin(\''.$member['userid'].'\')">Edit</a>';
        $act1 = '<a href="#" onclick="editadmin(\''.$member['userid'].'\')">Edit</a>';
        $rows[$member['userid']]['5'] = $member['usertype'] == 'bcclient' ? 'VIP |'.$act1: $acto;
        $rows[$member['userid']]['6'] = $clients[$member['bcid']]['company'];
        }
    }
    $disp = tablegen($header,$rows,"100%");
    ?>
        <a href="#" onclick="newadmin('<?php echo $_REQUEST['bcid'];?>')" class="jbut">Create New Admin</a>
    <?php
    echo $disp;
    exit;
}
if ($act == 'newpartner')
	{
        
		?>
        <form id="createnewadminform">
        <table width="400" cellspacing="12">
        <tr><td><b>Name:</b></td><td><input type="text" id="newuserlogin" name="partner_name" /></td>
         <tr><td><b>Company:</b></td><td><input type="text" id="newuserpass" name="company" /></td>
          <tr><td><b>Email:</b></td><td><input type="text" id="newemail" name="email" /></td>
          <tr><td><b>Phone:</b></td><td><input type="text" id="newphone" name="phone" /></td>
         <tr><td></td><td><a href="#" onClick="addnewpartner('<?php echo $_REQUEST['bcid'];?>')" class="jbut">Submit</a></td></tr>
        </table>
        </form>
        <?
		exit;
	}
if ($act == 'editpartner')
	{
        $cpid = $_REQUEST['cpid'];
        $partners = new crud('bc_partners');
        $params['cpid'] = $cpid;
        $part = $partners->get($params);
        $partner = $part[0];
		?>
        <form id="createnewadminform">
        <table width="400" cellspacing="12">
        <tr><td><b>Name:</b></td><td><input type="text" id="newuserlogin" name="partner_name" value="<?=$partner['partner_name'];?>" /></td>
         <tr><td><b>Company:</b></td><td><input type="text" id="newuserpass" name="company"  value="<?=$partner['company'];?>" /></td>
          <tr><td><b>Email:</b></td><td><input type="text" id="newemail" name="email"  value="<?=$partner['email'];?>" /></td>
          <tr><td><b>Phone:</b></td><td><input type="text" id="newphone" name="phone"  value="<?=$partner['phone'];?>" /></td>
         <tr><td></td><td><a href="#" onClick="updatepartner('<?php echo $cpid;?>')" class="jbut">Update</a></td></tr>
        </table>
        </form>
        <?
		exit;
	}
if ($act == 'newadmin')
	{
        $roles = getdatatable('roles','roleid');
       /* foreach ($roles as $role)
        {
            $roledrop .= '<option value="'.$role['roleid'].'">'.$role['rolename'].'</option>';
        }*/
        $roledrop .= '<option value="1">Administrator</option>';
        $roledrop .= '<option value="2">VIP</option>';
		?>
        <form id="createnewadminform">
        <table width="400" cellspacing="12">
        <tr><td><b>FirstName:</b></td><td><input type="text" id="newuserlogin" name="firstname" /></td>
         <tr><td><b>LastName:</b></td><td><input type="text" id="newuserpass" name="lastname" /></td>
          <tr><td><b>Email:</b></td><td><input type="text" id="newemail" name="email" /></td>
          <tr><td><b>Role:</b></td><td><select id="newrole" name="roleid">
          <?php echo $roledrop;?>
          </select><input type="hidden" name="bcid" value="<?php echo $_REQUEST['bcid'];?>" /></td>
         <tr><td></td><td><a href="#" onClick="addnewadmin('<?php echo $_REQUEST['bcid'];?>')" class="jbut">Submit</a></td></tr>
        </table>
        </form>
        <?
		exit;
	}
if ($act == 'editadmin')
	{
        $userid = $_REQUEST['userid'];
        $roles = getdatatable('roles','roleid');
        $res = mysql_query("SELECT * from members where userid = '$userid'");
        $user = mysql_fetch_assoc($res);
        $res = mysql_query("SELECT * from memberdetails where userid = '$userid'");
        $userdet = mysql_fetch_assoc($res);
        foreach ($roles as $role)
        {
            $selected = '';
            if ($user['roleid'] == $role['roleid']) $selected = 'selected';
            $roledrop .= '<option value="'.$role['roleid'].'" '.$selected.' >'.$role['rolename'].'</option>';
        }
		?>
        <form id="createnewadminform">
        <table width="400" cellspacing="12">
        <tr><td><b>FirstName:</b></td><td><input type="text" id="newuserlogin" name="firstname" value="<?=$userdet['afirst'];?>" /></td>
         <tr><td><b>LastName:</b></td><td><input type="text" id="newuserpass" name="lastname" value="<?=$userdet['alast'];?>" /></td>
          <tr><td><b>Email:</b></td><td><input type="text" id="newemail" name="email" value="<?=$user['userlogin'];?>" /></td>
          <tr><td><b>Role:</b></td><td><select id="newrole" name="roleid">
          <?php echo $roledrop;?>
          </select><input type="hidden" name="bcid" value="<?php echo $user['bcid'];?>" /></td>
         <tr><td></td><td><a href="#" onClick="updateadmin('<?php echo $user['userid'];?>')" class="jbut">Update</a>
                 <a href="#" onClick="resetpassadmin('<?php echo $user['userid'];?>')" class="jbut">Reset Password</a>
             </td></tr>
        </table>
        </form>
        <?
		exit;
	}
if ($act =='extensions')
	{
		//$extensions = getdatatable("bc_phones",'name');
                if (!$_REQUEST['bcid'])
                {
                    $extres = mysql_query("select * from bc_phones");
                }
                else $extres = mysql_query("select * from bc_phones where bcid = '".$_REQUEST['bcid']."'");
                while ($row = mysql_fetch_assoc($extres))
                {
                    $extensions[$row['name']] = $row;
                }
                $liveusers = getdatatable('liveagents','extension');
		$clients = getdatatable("bc_clients",'bcid');
                $header[]='';
		$header[]='Username';
		$header[]='Password';
		$header[]='Client';
                $header[]='Used By';
		$header[]='Action';
		foreach ($extensions as $ext)
			{
                                $rows[$ext['name']]['1'] = '<input type="checkbox" name="bulkaction" value="'.$ext['name'].'">';
				$rows[$ext['name']]['2'] = $ext['name'];
				$rows[$ext['name']]['3'] = $ext['secret'];
				$rows[$ext['name']]['4'] = $clients[$ext['bcid']]['company'];
                                if ($liveusers[$ext['name']]) $rows[$ext['name']]['5'] = $liveusers[$ext['name']]['afirst'].' '.$liveusers[$ext['name']]['alast'];
                                else $rows[$ext['name']]['5'] = 'Not In use';
				$rows[$ext['name']]['6'] = '
                                    <a href="super.php?act=delext&ext='.$ext['name'].'">Delete</a> | <a href="#" onclick="editext(\''.$ext['name'].'\',\''.$_REQUEST['sub'].'\')">Edit</a> | <a href="#" onclick="bargethis(\''.$ext['name'].'\')">Barge</a>';
			}
		$disp1 = tablegen($header,$rows,NULL,NULL,'extensionstable');
                if ($_REQUEST['sub'] == 1) 
                {
                    echo $disp1;
                    exit;
                }
                $disp ='<h3 class="stitle"><i>Extensions</i></h3>';
                $disp .= $disp1;
                
	}
if ($act =='providers')
	{
		$extensions = getdatatable("bc_providers",'name');
		$clients = getdatatable("bc_clients",'bcid');
                $header[]='';
                $header[]='Provider';
                $header[]='Name';
		$header[]='Username';
		$header[]='Password';
                $header[]='Host/Server';
		$header[]='Client';
		$header[]='Action';
		foreach ($extensions as $ext)
			{
                                $rows[$ext['name']]['1'] = '<input type="checkbox" name="bulkaction" value="'.$ext['name'].'">';
                                $rows[$ext['name']]['2'] = $ext['provider'];
                                $rows[$ext['name']]['3'] = $ext['name'];
				$rows[$ext['name']]['4'] = $ext['username'];
				$rows[$ext['name']]['5'] = $ext['secret'];
                                $rows[$ext['name']]['6'] = $ext['host'];
				$rows[$ext['name']]['7'] = $clients[$ext['bcid']]['company'];
				$rows[$ext['name']]['8'] = '<a href="super.php?act=delprov&prov='.$ext['name'].'">Delete</a> | <a href="super.php?act=editprov&prov='.$ext['name'].'">Edit</a>';
			}
                $disp = '<h3 class="stitle"><i>Voice Providers</i></h3>';
		
		$disp .= tablegen($header,$rows,null,null,'providerstable');

	}
if ($act =='editclient')
	{
		$bc = $_REQUEST['clientid'];
		$client = getclientdetails($bc);
		$partners = getdatatable("bc_partners","cpid");
		$res = mysql_query("SELECT * from members where roleid = 2 and bcid = '$bc'");
		$vip = mysql_fetch_assoc($res);
foreach ($partners as $partner)
{
    if ($partner['isdeleted'] != 1)
    {$partnerdrop .= '<option value="'.$partner['cpid'].'">'.$partner['partner_name'].'</option>';}
}
                $clientlist = getdatatable("bc_clients","bcid");
                foreach ($clientlist as $cl)
                {
                        if ($cl['bcid']!= $client['bcid'] && $cl['status'] == 'Active') $cldrop .= '<option value="'.$cl['bcid'].'">'.$cl['company'].'</option>';
                }
                $logourl = '../logo/?logo='.$client['logo'];
		?>
        <div style="float:left">
            <a href="super.php" class="jbut">Back</a>
        </div>
        <div style="float:right">
            <b>View:</b><select id="editclientdrop" onchange="changeviewclient()"><option><?php echo $client['company'];?></option><?php echo $cldrop;?></select>
        </div>
        <div style="clear:both"></div>
<div id="tabs">
    <div class="active" id="admintab" onclick="viewtab('admin')">Admin</div>
    <div class="inactive" id="extensionstab" onclick="viewtab('extensions','<?php echo $bc;?>')">Extensions</div>
    <div class="inactive" id="userstab" onclick="viewtab('users','<?php echo $bc;?>')">Users</div>
    <div class="clear"></div>
    <div id="boundary"></div>
</div>
<div class="clear"></div>
<div id="tabcont">
<div id="tabadmin" class="tabc">
<form method="post" name="newclientform" id="newclientform" action="super.php">
    <div style="float:left">
<table width="800">
<tr><th colspan="3" style="height:20px" class="tableheader"><b>Client Details</b></th></tr>
<tr><td width="150"><b>Company:</b> </td><td width="200"><input type=text id=company name=company value="<?=$client['company'];?>" /><br>
<input type=hidden name=act value="saveclient" />
<input type=hidden name="bc" value="<?=$bc;?>" /></td></tr>
<tr><td><b>Company Logo:</b></td><td>
        <div id="logoeditor" onclick="editlogo('<?=$bc;?>');">
                    <img src="<?=$logourl;?>"  />
    </div><br /><a href="#" onclick="editlogo('<?=$bc;?>')">Edit Logo</a>
    </td></tr>
<tr><td><b>Address1: </b></td><td><input type="text" id="address1" name="address1"  value="<?=$client['address1'];?>" onblur="clientchange(this)" onchange="triggerchange()" /></td></tr>
<tr><td><b>Address2: </b></td><td><input type="text" id="address2" name="address2"  value="<?=$client['address2'];?>" onblur="clientchange(this)" onchange="triggerchange()"/></td></tr>
<tr><td><b>City: </b></td><td><input type="text" id="city" name="city"  value="<?=$client['city'];?>" onblur="clientchange(this)" onchange="triggerchange()"/></td></tr>
<tr><td><b>State: </b></td><td><input type="text" id="state" name="state"  value="<?=$client['state'];?>" onblur="clientchange(this)" onchange="triggerchange()"/></td></tr>
<tr><td><b>Country: </b></td><td><input type="text" id="country" name="country"  value="<?=$client['country'];?>" onblur="clientchange(this)" onchange="triggerchange()"/></td></tr>
<tr><td><b>Website: </b></td><td><input type="text" id="url" name="url"  value="<?=$client['url'];?>" onblur="clientchange(this)" onchange="triggerchange()"/></td></tr>
<tr><th colspan="2" style="height:20px" class="tableheader"><b>Account Details</b></th></tr>
<tr><td><b>Payment Type:</b> </td><td><select name="ratetype" onchange="triggerchange();clientchange(this)"><option  value="<?=$client['ratetype'];?>"><?=ucfirst($client['ratetype']);?><option value="prepaid">Prepaid</option><option value="Postpaid">Postpaid</option></select><br></td></tr>
<tr><td><b>Rate:</b> </td><td>
<select name="rateid"  onchange="triggerchange();clientchange(this)">
<?
foreach ($rates as $rate)
{
	if ($rate['rateid'] == $client['rateid'])
		{
			echo '<option value="'.$rate['rateid'].'" selected="selected">'.$rate['ratename'].'</option>';
		}
	echo '<option value="'.$rate['rateid'].'">'.$rate['ratename'].'</option>';
}

//$logourl = urlencode($logourl);
?>
</select>
</td></tr>  
<tr><td><b>Account Manager</b></td><td><select name="cpid" onchange="triggerchange();clientchange(this)"><option value="<?=$client['cpid'];?>"><?=$partners[$client['cpid']]['partner_name'];?></option><?=$partnerdrop;?></select></td></tr>
<tr><td class="tableheader" colspan="2">Client Contact</td></tr>
    <tr><td><b>Name: </b></td><td><input type="text" id="name" name="name"  value="<?=$client['name'];?>" onblur="clientchange(this)" onchange="triggerchange()"/></td></tr>
<tr><td><b>Email: </b></td><td><input type="text" id="cemail" name="email"  value="<?=$client['email'];?>" onblur="clientchange(this)" onchange="triggerchange()"/></td></tr>
    <tr><td><b>Phone: </b></td><td><input type="text" id="cphone" name="phone"  value="<?=$client['phone'];?>" onblur="clientchange(this)" onchange="triggerchange()"/></td></tr>
<tr><td><b>Password:</b> </td><td><a href="#" onclick="resetpassadmin('<?=$vip['userid'];?>')" class="jbut">Reset Password</a></td></tr>
</table>
    </div>
    
    <div style="clear:both"></div>
</form>
</div>
<div id="tabextensions" class="tabc"></div>
<div id="tabusers" class="tabc"></div>
</div>
        <?
		exit;
	}
if ($act =='newclientform')
	{
		$partners = getdatatable("bc_partners","cpid");

foreach ($partners as $partner)
{
	$partnerdrop .= '<option value="'.$partner['cpid'].'">'.$partner['partner_name'].'</option>';
}
		?>
<form method="post" name="newclientform" id="newclientform" action="super.php">
<table width="400" cellspacing="12">
<tr><td width="150"><b>Company:</b> </td><td width="300"><input type=text id=ccompany name=ccompany><br>
        <input type=hidden name=act value=newclient>
        <input type=hidden name=logo id="logo" value="">
    </td></tr>
<tr><td><b>Payment Type:</b> </td><td><select name="ratetype"><option value="prepaid">Prepaid</option><option value="Postpaid">Postpaid</option></select><br></td></tr>
<tr><td colspan="2"><b>Client Contact(VIP): </b></td></tr>
<tr><td><b>Name: </b></td><td><input type="text" id="cname" name="cname"/></td></tr>
<tr><td><b>Email: </b></td><td><input type="text" id="cemail" name="cemail"/></td></tr>
<tr><td><b>Phone: </b></td><td><input type="text" id="cphone" name="cphone"/></td></tr>
<tr><td><b>Package:</b> </td><td>
<select name="rateid">
<?
foreach ($rates as $rate)
{
	echo '<option value="'.$rate['rateid'].'">'.$rate['ratename'].'</option>';
}
?>
</select>
</td></tr>
<tr><td><b>Account Manager:</b></td><td><select name="cpid"><?=$partnerdrop;?></select></td></tr>
</table>
</form>
        <form name="uploadlogo" id="uploadlogo">
<input type="hidden" name="act" value="upload" />
                <strong>Logo upload</strong>
                <input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>
                <div><input id="MAX_FILE_SIZE" name="file" type="file" style="float:left;margin-left:30px" /></div>
                <div class="buttons">
				<input type="button" value="Upload" onclick="uploadFile('uploadlogo','super.php')"></div>
                <div class="clear"></div>
                <div id="uploaderr" style="display:none"></div>
                <div id="progress">
                    <div id="pbar"></div>
                </div>
                
        </form>
<a href="#" onClick="submitnewclient()" class="jbut">Submit</a><br>
        <?
		exit;
	}
if ($act == 'delpack')
	{
		$packid = $_REQUEST['packageid'];
		mysql_query("update bc_packages set active = 0 where packageid = '$packid'");
		echo "done";
		exit;
	}
if ($act == 'gettrans')
	{
		$transactions = getdatatable("bc_transactions", "transactionid");
                $selectedbcid = $_REQUEST['selectedbcid'];
                $rows = array(); 
                $headers[] = "TransactionID";
                $headers[] = "Company";
                $headers[] = "Date";
                $headers[] = "Mode of Payment";
                $headers[] = "Amount";
                $headers[] = "Notes";
		foreach ($transactions as $trans)
			{
                                if ($selectedbcid == $trans['bcid'] || $selectedbcid == 'all')
                                {
                                $rows[$trans['transactionid']]['id'] = $trans['transactionid'];
                                $rows[$trans['transactionid']]['company']= $bclist[$trans['bcid']]['company'];
                                $rows[$trans['transactionid']]['epoch'] = date("r",$trans['epoch']);
                                $rows[$trans['transactionid']]['mode'] = $trans['paymentmode'];
                                $rows[$trans['transactionid']]['amt'] = $trans['amount'];
                                $rows[$trans['transactionid']]['comments'] = $trans['comments'];
                                }
				
			}
                $clientlist = getdatatable("bc_clients","bcid");
                $cldrop = '<option value="all">All Clients</option>';
                foreach ($clientlist as $cl)
                {
                        if ($cl['bcid']!= $client['bcid'] && $cl['status'] == 'Active')
                        {
                            $selected = ($selectedbcid == $cl['bcid']) ? "selected": "";
                            $cldrop .= '<option value="'.$cl['bcid'].'" '.$selected.'>'.$cl['company'].'</option>';
                        }
                }
                ?>
<h3 class="stitle"><i>Payment Transactions</i></h3>
View: <select name="selectedbcid" onchange="gettransactions()" id="selectedbcid"><?php echo $cldrop;?></select>

<?php
		echo tablegen($headers,$rows,800,null,"transtable");
		exit();
	}
if ($act == 'addpackage')
	{
		extract($_REQUEST);
		mysql_query("INSERT into bc_packages set packagename = '$packagename', qty = '$qty', packagecost = '$packagecost', packagedescription = '$packagedescription', packagetype = '$packagetype'");
		echo 'done';
		exit;
	}
if ($act == 'addpay')
	{
		extract($_REQUEST);
		$e = time();
		mysql_query("Insert into bc_transactions set epoch = '$e', referencenumber = '$referencenumber', amount = '$amount', date = NOW(), transactiontype='payment', paymentmode = '$paymentmode', bcid= '$bcid', comments = '$comments'");
		$c = mysql_query("SELECT * from bc_wallet where bcid = '$bcid'");
		$exists = mysql_num_rows($c);
		if ($exists > 0)
			{
				mysql_query("Update bc_wallet set loadedcredits = loadedcredits + $amount where bcid = '$bcid'");
			}
		else {
			mysql_query("Insert into bc_wallet set bcid = '$bcid', loadedcredits = $amount");
		}
		echo "payment accepted";
	}
if ($act == 'NewPackage')
	{
		?>
        <table width="650" cellspacing="0" cellpadding="0" style="">
        <tr><td class="center-title">Package Name</td><td class="center-title"><input type="text" id="p_packagename" name="p_packagename" /></td></tr>
        <tr><td class="center-title">Package Description</td><td class="center-title"><textarea name="p_packagedescription" id="p_packagedescription"></textarea></td></tr>
		
        <tr>
        <td class="center-title">Package Type</td>
        <td class="center-title"><select name="p_packagetype" id="p_packagetype">
        <option value="credits">Usage Hours</option>
        <option value="mobile credits">Mobile Credits</option>
        <option value="feature">Features</option>
        </select></td></tr>
        <tr><td class="center-title">Quantity</td><td class="center-title"><input type="text" name="p_qty" id="p_qty" /></td>
        </tr>
        <tr><td class="center-title">Cost</td><td class="center-title"><input type="text" name="p_packagecost" id="p_packagecost" /></td>
        </tr>
        <tr><td class="center-title" colspan="2"><a href="#" onclick="submitpackage()">Save</a></tr>
        </table>
        <?
	exit;
	}
if ($act == 'Packages')
	{
		$r = getdatatable("bc_packages","packageid");
		
		?>
        <table width="650" cellspacing="0" cellpadding="0" style="">
        <tr><td>PackageName</td><td>PackageType</td><td>PackageQty</td><td>Cost</td><td>Action</td></tr>
        <?
        foreach ($r as $pack)
		{
		if ($pack['active'] == 1)
		{
        ?>
        <tr><td><?=$pack['packagename'];?></td><td><?=$pack['packagetype'];?></td><td><?=$pack['qty'];?></td><td><?=$pack['packagecost'];?></td><td><a href="#" onclick="delpack('<?=$pack['packageid'];?>')">Delete</a></td></tr>
        <?
		}
		}
		exit;
	}
if ($act == 'AddPayments')
	{
		?>
        <table width="650" cellspacing="0" cellpadding="0" style="">
        <tr><td class="center-title">Company</td><td class="center-title"><select name="p_bcid" id="p_bcid"><?=$bcdropdown;?></select></td></tr>
		<tr><td class="center-title">Amount</td><td class="center-title"><input type="text" id="p_amount" name="p_amount" /></td></tr>
        <tr>
        <td class="center-title">Mode of Payment</td>
        <td class="center-title"><select name="p_paymentmode" id="p_paymentmode">
        <option value="cash">Cash</option>
        <option value="checque">Cheque</option>
        <option value="bank">Bank</option>
        <option value="bank">Paypal</option>
        </select></td></tr>
        <tr><td class="center-title">Reference Number</td><td class="center-title"><input type="text" name="p_referencenumber" id="p_referencenumber" /></td>
        </tr>
        <tr><td class="center-title">Comments</td><td class="center-title"><textarea name="p_comments" id="p_comments"></textarea></td></tr>
		<tr><td class="center-title" colspan="2"><a href="#" onclick="submitpayment()">Add</a></tr>
        </table>
        <?
	exit;
	}
if ($act == "ratechange")
	{
		$f = $_REQUEST['field'];
		$v = $_REQUEST['value'];
		$i = $_REQUEST['rateid'];
		mysql_query("update bc_rates set $f = '$v' where rateid = '$i'");
		exit;
	}
if ($act == "callratechange")
	{
		$f = $_REQUEST['field'];
		$v = $_REQUEST['value'];
		$i = $_REQUEST['callrateid'];
		mysql_query("update bc_rates_calls set $f = '$v' where id = '$i'");
		exit;
	}
if ($act == 'deleterate')
{
    $rateid = $_REQUEST['rateid'];
    if ($rateid == 1)
    {
        echo "Cannot Delete";
        exit;
    }
    mysql_query("Update bc_rates set isdeleted = 1 where rateid = $rateid");
    exit;
}
if ($act == "rates")
	{
            $headers[] = 'Name';
            $headers[] = 'Monthly Usage';
            $headers[] = 'Daily Usage';
            $headers[] = 'Hourly Usage';
            $headers[] = 'PerMinute Usage';
            $headers[] = '';
            foreach ($rates as $rate)
		{
                if ($rate['isdeleted'] == 0)
                {
                    $rows[$rate['rateid']]['name'] = $rate['ratename'];
                    $rows[$rate['rateid']]['month'] = creds($rate['rate_monthly']);
                    $rows[$rate['rateid']]['daily'] = creds($rate['rate_daily']);
                    $rows[$rate['rateid']]['hour'] = creds($rate['rate_hourly']);
                    $rows[$rate['rateid']]['min'] = creds($rate['rate_minute']);
                    $rows[$rate['rateid']]['actions'] = '<a href="#" onclick="editrate(\''.$rate['rateid'].'\')">Edit</a> | <a href="#" onclick="deleterate(\''.$rate['rateid'].'\')">Delete</a>';
                }
                }
		?>
        <a href="#" onClick="newrate()" class="jbut">Add Rate </a><br />
        <?
        echo tablegen($headers,$rows,800,"ratetable");
	exit;
	}
if ($_REQUEST['act'] == 'createnew')
		{
			$mess = newadmin($_REQUEST['nbcid'],$_REQUEST);
		}
?>