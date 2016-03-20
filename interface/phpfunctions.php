<?php
function timeunits($u)
{
    $init = $u;
$hours = floor($init / 3600);
$minutes = floor(($init / 60) % 60);
$seconds = $init % 60;
if ($hours)
    {
    if ($hours > 1) return sprintf("%dhrs %dmins %dsecs", $hours, $minutes, $seconds);
    else return sprintf("%dhr %dmins %dsecs", $hours, $minutes, $seconds);
    }
elseif ($minutes)
    {
       return sprintf("%dmins %dsecs", $minutes, $seconds); 
    }
 else {
     return sprintf("%dsecs", $seconds); 
 }   
}
function stat24($u)
{
    $now = time();
    $before = $now - 86400;
    $talktime = 0;
    $wrapres = mysql_query("SELECT sum(epochend - epochstart) as wrap from actionlog where userid = '$u' and epochstart > $before and epochstart < $now and action = 'wrap'");
    $wraprow = mysql_fetch_assoc($wrapres);
    $wraptime = $wraprow['wrap'];
    $waitres = mysql_query("SELECT sum(epochend - epochstart) as wait from actionlog where userid = '$u' and epochstart > $before and epochstart < $now and action = 'wait'");
    $waitrow = mysql_fetch_assoc($waitres);
    $waittime= $waitrow['wait'];
    $pauseres = mysql_query("SELECT sum(epochend - epochstart) as pause from actionlog where userid = '$u' and epochstart > $before and epochstart < $now and action = 'pause'");
    $prow = mysql_fetch_assoc($pauseres);
    $pausetime= $prow['pause'];
    $prevres = mysql_query("SELECT sum(epochend - epochstart) as pause from actionlog where userid = '$u' and epochstart > $before and epochstart < $now and action = 'pause'");
    $prevrow = mysql_fetch_assoc($prevres);
    $prevtime= $prevrow['pause'];
    $cdres = mysql_query("SELECT * from finalhistory where userid = '$u' and startepoch > $before and startepoch < $now");
    $ct = 0;
    $talkct = 0;
    while ($cdrow = mysql_fetch_assoc($cdres))
    {
        if ($cdrow['dialmode'] == 'manual')
        {
        if ($cdrow['dialedtime'] == 0)
        {
            $dialedtime = $cdrow['endepoch'] - $cdrow['startepoch'];
             $dialtime = $dialtime + $dialedtime;
        }
        else {
        $talktime = $talktime + $cdrow['answeredtime'];
        $dialtime = $dialtime + $cdrow['dialedtime'];
        }
        }
        if ($cdrow['systemdisposition'] == 'ANSWER')
        {
            $talkct++;
        }
        $ct++;
    }
    $talkres = mysql_query("SELECT sum(epochend - epochstart) as talktime from actionlog where userid = '$u' and epochstart > $before and epochstart < $now and action = 'talk'");
    $talkrow = mysql_fetch_assoc($talkres);
    $talktime = $talktime + $talkrow['talktime'];
    $averagecall = ($talktime/$talkct);
    $ret['Dialtime'] = array("value"=>$dialtime);
    $ret['Talktime'] = array("value"=>$talktime);
    $ret['Pausetime'] = array("value"=>$pausetime);
    $ret['Wraptime'] = array("value"=>$wraptime);
    $ret['WaitTime'] = array("value"=>$waittime);
    $ret['PreviewTime'] = array("value"=>$prevtime);
    $ret['Average Talktime'] = array("value"=>$averagecall);
    $ret['Answered Calls'] = array("value"=>$talkct);
    $ret['Total Calls'] = array("value"=>$ct);
    $ch = ($dialtime + $talktime + $pausetime + $wraptime + $waittime);
    $ret['Campaign Time'] = array("value"=>$ch);
    return $ret;
}
function callnumber($custrow,$phone)
  {
    global $pid,$auid;
    $prefixres = mysql_query("SELECT * from projects where projectid = '$pid'");
    $prefrow = mysql_fetch_assoc($prefixres);
    $prefix = $prefrow["prefix"];
                $prefbcid = $prefrow["bcid"];
                $region = $prefrow["region"];
    mysql_query("INSERT into callman set leadid = '".$custrow['leadid']."', phone = '".$phone."', status = 'originate', projectid ='".$pid."', prefix = '$prefix', bcid = '".$prefbcid."', region='$region', start = '".time()."', mode = '1';");
    mysql_query("Update liveusers set leadid = '".$custrow['leadid']."', status = 'dialing', actionid = '0' where userid ='$auid'");
  }
function getdatatable($table, $id = 'id')
  {
    $r = mysql_query("SELECT * from $table");
    $m = array();
    while ($row = mysql_fetch_array($r))
      {
        $m[$row[$id]] = $row;
      }
    return $m;
  }
function getlead($leadid)
  {
    //return($leadid);
    $res = mysql_query("SELECT * from leads_done where leadid = '$leadid'");
    $ct = mysql_num_rows($res);
    if ($ct == 0)
      {
        $res = mysql_query("SELECT * from leads_raw where leadid = '$leadid'");
      }
    $row = mysql_fetch_array($res);
    return $row;
  }
function countcallbacksdue($userid,$projectid)
  {
    $stres = mysql_query("select statusname from statuses where category in ('agent','team') and projectid in (0,$projectid)");
    while ($strow = mysql_fetch_array($stres))
      {
        $ina[] .= "'".$strow['statusname']."'"; 
      }
    $in = implode(",",$ina);
    $query = "SELECT projectid, count(dateandtime.dtime) from leads_done left join dateandtime on leads_done.leadid = dateandtime.leadid where projectid = '$projectid' and assigned = '$userid' and dispo in ($in) and status = 'assigned' and substr(dtime,1,10) <= substr(NOW(),1,10) group by projectid";
    if ($projectid == 'all')
      {
        $query = "SELECT projectid, count(dateandtime.dtime) from leads_done left join dateandtime on leads_done.leadid = dateandtime.leadid where assigned = '$userid' and dispo in ($in) and status = 'assigned' and substr(dtime,1,10) <= substr(NOW(),1,10) group by projectid";
      }
    $r = mysql_query($query);
    $res = mysql_fetch_row($r);
    return $res[1];
  }
function getlistnew($n)
  {
    $res = mysql_query("SELECT listid from lists where bcid = '$n' and active = '1'");
    while ($row = mysql_fetch_array($res))
      {
        $p[] =  "'".$row['listid']."'";
      }
    $l = implode(",",$p);
    return $l;
  }
function getplistnew($bcid)
  {
    $res = mysql_query("SELECT projectid from projects where bcid = '$bcid'");
    while ($row = mysql_fetch_array($res))
      {
        $p[] =  "'".$row['projectid']."'";
      }
    $n = implode(",",$p);
    return $n;
  }
function featurecheckexp($bcid,$feature)
  {
    $res = mysql_query("SELECT * from bc_features_exp where bcid = '$bcid' and feature = '$feature'");
    $r = mysql_fetch_array($res);
    if (mysql_num_rows($res) == 0)
      {
        return true;
      }
    elseif ($r['expdate'] > time())
      {
        return true;
      }
    else {
      mysql_query("updated bc_features set $feature = 0");
      return false;
    }
  }
function featurecheck($bcid,$feature)
  {
    $res = mysql_query("SELECT * from bc_features where bcid = '$bcid'");
    $r = mysql_fetch_array($res);
    if (mysql_num_rows($res) == 0)
      {
        return true;
      }
    elseif ($r[$feature] == 1)
      {
        if (featurecheckexp($bcid,$feature))
          {
            return true;
          }
        else return false;
      }
    else return false;
  }
function selectproject($u)
{
    $inb = getprojectlist($u, NULL, false, true);
  ?>
    <div id="agentdash" style="color:#069">
    <br /><br />
    <h3 >Welcome to BlueCloud.</h3><br>
    Outbound Campaign:<br>
    <select name="selectprojectid" id="selectprojectid" title="<?=$u;?>">
    <?=getprojectlist($u);?>
    </select><br>
    <?
    if (strlen($inb) > 5)
    {
       ?>
       Inbound Campaign:<br>
    <select name="selectinbound[]" id="selectinbound" multiple="multiple">
    <?=$inb?>
    </select><br>
       <?
    }
    ?>
    <br>
    Enter softphone extension:<br>
    <input type="text" name="sextension" id="sextension"><br><br>
    <span class="mainm"><a href="../index.php?act=logout" class="jbutton">Cancel</a><a href="#" onClick="changeproj()" class="jbutton">Next</a></span>
    </div>
    <?
}
function getlistsbypid($pid)
  {
    $res = mysql_query("SELECT listid from lists where projects = '$pid'");
                $p = array();
    while ($row = mysql_fetch_array($res))
      {
        $p[] =  "'".$row['listid']."'";
      }
    $n = implode(",",$p);
    return $n;
  }  
function getprojectlist($usersid, $selected = NULL, $assoc = false, $inbound = false)
{
  $res = mysql_query("SELECT members.userid, memberdetails.team,  members.bcid from members left join memberdetails on members.userid = memberdetails.userid where members.userid = '$usersid'");
  $row = mysql_fetch_assoc($res);
        $ubcid = $row['bcid'];
  $teams = json_decode($row['team'],true);
  $tct = 0;
  $tin = implode(",",$teams);
  //echo $tin;
  $pres = mysql_query("SELECT projects from teams where teamid in ($tin) and active = 1 and bcid = '$ubcid'");
  while ($prow = mysql_fetch_row($pres))
  {
  $projects = $prow[0];
  //parse projectlist
  if (strlen($projects))
    {
    $plist = explode(";",$projects);
    foreach ($plist as $p)
      {
      if (strlen($qstring) > 1) $qstring .= ",";
      $qstring .= "'$p'";
      }
    }
  }
        if ($inbound){
            $projquery = "SELECT * from projects where projectid in ($qstring) and dialmode = 'inbound'";
        }
        else {$projquery = "SELECT * from projects where projectid in ($qstring) AND active=1 ORDER BY projectname";}
  $projres = mysql_query($projquery);
  while ($projrow = mysql_fetch_assoc($projres))
      {
                        $isSelected = '';
                        if ($selected != NULL && $selected == $projrow['projectid']) $isSelected = 'selected';
      $pstring .="<option value=\"".$projrow['projectid']."\" $isSelected>".$projrow['projectname']."</option>";
                        $retass[$projrow['projectid']] = $projrow;
      }
  if ($assoc)
        {
            return $retass;
        }
        else {return $pstring;}
}
function getlogid($action)
{
  global $auid, $pid;
  $a = mysql_query("SELECT actionid from liveusers where userid = '$auid'");
  $logid = mysql_fetch_row($a);
  return $logid[0];
}
function startlog($action)
{
  global $auid, $pid;
  endaction();
  mysql_query("insert into actionlog set daydate = substr(NOW(),1,10), userid = '$auid', projectid = $pid, action = '$action', epochstart = '".time()."'");
  $newactionid = mysql_insert_id();
  mysql_query("update liveusers set actionid = '$newactionid' where userid = '$auid'");
  return $newactionid;
}
function endaction()
{
  global $auid, $pid, $actionid;
mysql_query("UPDATE actionlog SET epochend = IF(epochend IS NULL,".time().",epochend) WHERE  userid = $auid ORDER BY logid DESC LIMIT 1");
}
function endlog()
{
  global $auid, $pid, $actionid;
  mysql_query("update liveusers set actionid = 0");
  mysql_query("update actionlog set epochend = '".time()."' where logid = '$actionid'");
}
function hangupcall()
{
  global $lid,$astcluster;
  $leadres = mysql_query("select channel,callid from callman where leadid= '$lid'");
  $leadrow = mysql_fetch_row($leadres);
  $channel = $leadrow[0];
  $kolid = $leadrow[1];
   $ami = fsockopen($astcluster,"5038",$err,$errstr, 7500);
  $loginc = "Action: Login\r\nUsername: webby\r\nSecret: 1234561\r\n\r\n";
  fputs($ami,$loginc);
        fread($ami,200);
  fputs($ami,"ACTION: Hangup\r\n");
  fputs($ami,"Channel: ".$channel."\r\n\r\n"); 
        fread($ami,200);
  fclose($ami);
}
function savelead($data)
{
  global $auid, $pid, $isprow;
  $u = new members($auid);
  $autorec = $isprow['autorecyle'];
  $rec = new records();
  $rec->data = $data;
  $rec->id = $data['lid'];
  $rec->leadid = $data['lid'];
  $rec->projectid = $pid;
  if ($data['override_pid'] > 0)
  {
    $rec->projectid = $data['override_pid'];
  }
  //var_dump($data);
  if (strlen($data['lid']) > 0)
  {
  $error = false;
  mysql_query("update callhistory set agentdisposition = '".$data['disposition']."', userid = '$auid' where leadid = '".$data['lid']."' order by id DESC limit 1");
  mysql_query("update finalhistory set agentdisposition = '".$data['disposition']."', userid = '$auid' where leadid = '".$data['lid']."' order by id DESC limit 1");
  if ($autorec == '1')
  {
    $hop = '0';
  }
  else 
    $hop = '1';
  $checklead = mysql_query("select leadid from leads_done where leadid = '".$data['lid']."'");
  $isdone = mysql_num_rows($checklead);
  if ($isdone == 0)
  {
    $rec->createdone();
  }
  $rec->assigned= $auid;
  $rec->hopper = $hop;
  $rec->locked = 0;
  $rec->dispose();
  $prevnote = $rec->notes();
  $dres = mysql_query("SELECT * from statuses where statusname = '".$data['disposition']."' and projectid ='$pid'");
  $drow = mysql_fetch_assoc($dres);
  if ($data['notcalled'] != 1)
  {  // skip if not called
    $newnote = 'Called by '.$u->userlogin.' with disposition '.$data['disposition'];
    $jnote = json_decode($prevnote,true);
    if (!$jnote) $jnote = array();
    array_push($jnote,array(
        "user"=>"System",
        "timestamp"=>time(),
        "message"=>$newnote
    ));
    $rec->notes(json_encode($jnote));
    $dsendres = mysql_query("SELECT * from templates where disposend = '".$data['disposition']."' and projectid = '$pid'");
    while ($row = mysql_fetch_assoc($dsendres))
    {
      phplog_on();
      $_sendemail_url = $GLOBALS['GLOBAL_BASE_URL'] . "/sendemail.php?act=sendemail&tid=". $row['templateid'] ."&uid=" . $auid . "&leadid=" . $data['lid'] . "&to=" . $data['email'];
      phplog("SENDEMAIL URL: " . $_sendemail_url);
      $sendemail_dump = file_get_contents($_sendemail_url);
      phplog("SENDEMAIL_DUMP: " . $sendemail_dump);    
      $_stampnote_url = $GLOBALS['GLOBAL_BASE_URL'] . "/ajax.php?act=addnote&uid=" . $auid . "&leadid=" . $data['lid'] . "&note=" . urlencode($sendemail_dump) . "&PHPSESSID=" . $_COOKIE["PHPSESSID"];
      phplog("STAMP NOTE URL: " . $_stampnote_url);
      $stampnote_dump = file_get_contents($_stampnote_url);
      phplog("STAMPNOTE_DUMP: " . $stampnote_dump);    
      phplog_off();
    }
    if (substr($drow['statustype'],0,8) == 'transfer')
    {
      $dotransfer = 1;
    }
    elseif ($drow['category'] == 'team')
    {
      mysql_query("update leads_done set isteamcallback = 1 where leadid = ".$data['lid']);
    }
    if ($dotransfer > 0)
    {
      $torow = $drow;
      $tocat = $torow['category'];
      $tolist = $torow['options'];
      $nlistres = mysql_query("SELECT * from lists where lid = '$tolist'");
      $nlist = mysql_fetch_assoc($nlistres);
      $newrec = new records();
      $newrec->data = $data;
      $newrec->listid = $nlist['listid'];
      $newrec->hopper = 1;
      if ($tocat == 'team')
      {
          $newrec->hopper = 1;
      }
      $newrec->projectid = $nlist['projects'];
      $newrec->createraw();
      $newrec->createdone();
      $newrec->assigned = 0;
      if ($tocat == 'team')
      {
        $newrec->isteamcallback = 1;
      }
      $newrec->timeofcall = 'NOW()';
      $newrec->epoch_timeofcall = time();
      $newrec->hopper = 0;
      $newrec->update();
      //add to notes
      $njnote = json_decode($rec->notes(),true);
      if (!$njnote) {$njnote = array();}
      $prefnote = array();
      foreach ($njnote as $nt)
      {
       array_push($prefnote,array(
          "user"=>$nt['user']."(".$isprow['projectname'].")",
          "timestamp"=>$nt['timestamp'],
          "message"=>$nt['message'],
        )); 
      }
      $newrec->notes(json_encode($prefnote));
      $newrec->addnote("System","Transferred by ".$u->userlogin." from ".$isprow['projectname'] );
      //Transfer Custom Data // scriptfields
      $tcdata = $rec->customdata();
      $tsdata = $rec->scriptdata();
      $newtdata = array();
      foreach ($tcdata as $key=>$value)
      {
        $newtdata[$key] = $value;
      }
      foreach ($tsdata as $key=>$value)
      {
        $newtdata[$key] = $value;
      }
      customdata::add($newrec->id,$newtdata);
      $cres = mysql_query("SELECT * from statuses where statusname = '".$data['disposition']."' and projectid = '".$nlist['projects']."'");
      if (mysql_num_rows($cres) < 1)
      {
        if ($tocat == 'agent' || $tocat == 'team')
        {  
          mysql_query("INSERT into statuses set projectid = '".$nlist['projects']."', statusname = '".$data['disposition']."', statustype = 'text', category = '$tocat' ");
        }
        else  
          mysql_query("INSERT into statuses set projectid = '".$nlist['projects']."', statusname = '".$data['disposition']."', statustype = 'text', category = 'callable' ");
      }
    }
  } //end skip when not called
  $inci = "";
  if (strlen($data['ci']) > 0)
  {
    $ci = $data['ci'];
    $slotres = mysql_query("SELECT * from client_contact_slots where slotid = '$ci'");
    $slot = mysql_fetch_assoc($slotres);
    $inci = ", contactid = '".$slot['client_contactid']."'";
    $data['cal']= date("Y-m-d H:i:s",$slot['slotstart']);
    $notes = "From ".date("h:i:s a",$slot['slotstart'])." to ".date("h:i:s a",$slot['slotend'])." - ".$notes;
    mysql_query("update client_contact_slots set taken = 1,leadid = ".$data['lid']." where slotid = '".$slot['slotid']."'");
  }
  if (strlen($data['cal']) > 0)
  {
    $lid = $data['lid'];   
    $cal = $data['cal'];
    $notes = $data['notes'];
    if ($dotransfer > 0)
    {
      //mysql_query("insert into dateandtime set dtime = '".$data['cal']."', leadid = '".$newrec->leadid."'");
      mysql_query("update leads_raw set epoch_callable = '".strtotime($data['cal'])."' where leadid = '".$newrec->leadid."'");
      mysql_query("update leads_done set epoch_callable = '".strtotime($data['cal'])."' where leadid = '".$newrec->leadid."'");
    }
    mysql_query("update leads_raw set epoch_callable = '".strtotime($data['cal'])."' where leadid = '".$data['lid']."'");
    mysql_query("update leads_done set epoch_callable = '".strtotime($data['cal'])."' where leadid = '".$data['lid']."'");
    /*
    $checkdtime = mysql_query("select leadid from dateandtime where leadid = '$lid'");
    if (mysql_num_rows($checkdtime))
            {
            $iq = "update dateandtime set dtime = '$cal', note = '$notes' $inci where leadid = '".$data['lid']."'";
            mysql_query($iq);
            }
    else 
            {
            $dq = "insert into dateandtime set dtime = '$cal', note = '$notes', leadid = '".$data['lid']."' $inci";
            mysql_query($dq);
            }*/   
    }
    if ($drow['statustype'] == 'text')
    {
      mysql_query("update leads_raw set epoch_callable = 0 where leadid = '".$data['lid']."'");
      mysql_query("update leads_done set epoch_callable = 0 where leadid = '".$data['lid']."'");
    }
  }
}
function get($table,$id = 'id')
  {
    $res = mysql_query("SELECT * from $table") or die (mysql_error());
    $ret = array();
    while ($row = mysql_fetch_assoc($res))
      {
        $ret[$row[$id]] = $row; 
      }
    return $ret;
  }
function getallbyparams($table,$params)
  {
    $res = mysql_query("SELECT * from $table $params");
    $ret = array();
    while ($row = mysql_fetch_assoc($res))
      {
        $ret[] = $row; 
      }
    return $ret;
  }
function tablegen($headers, $rows, $width = "770", $rowscript = NULL, $tableclass= NULL)
  {
    $table = '<table width="'.$width.'" class="'.$tableclass.'">';
    $table .= '<thead><tr>';
    foreach ($headers as $header)
      {
        $table .= '<th class="tableheader">'.$header.'</th>';
      }
    $table .= '</tr></thead><tbody>';
    $c = 1;
    foreach ($rows as $row)
      {
        $c++;
        if ($c % 2) $class = "tableitem";
        else $class = "tableitem_";
        $table .= '<tr class="'.$class.'" '.$row['options'].'>';
        foreach ($row as $key=>$item)
          {
            if ($key != 'options')
              {
                 $table .= '<td>'.$item.'</td>';
              }
          }
        $table .= '</tr>';
      }
    $table .= '</tbody></table>';
    return $table;
  }
function mkstemp( $template ) {
  $attempts = 238328; // 62 x 62 x 62
  $letters  = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $length   = strlen($letters) - 1;
  if( strlen($template) < 6 || !strstr($template, 'XXXXXX') )
    return FALSE;
  for( $count = 0; $count < $attempts; ++$count) {
    $random = "";
    for($p = 0; $p < 6; $p++) {
      $random .= $letters[mt_rand(0, $length)];
    }
    $randomFile = str_replace("XXXXXX", $random, $template);
    if( !($fd = @fopen($randomFile, "x+")) )
      continue;
    return $fd;
  }
  return FALSE;
}
?>