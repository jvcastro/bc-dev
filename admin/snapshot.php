<?php
session_start();
date_default_timezone_set($_SESSION['timezone']);
include "../dbconnect.php";
include "../classes/classes.php";
include "phpfunctions.php";
function nuform($num, $ch)
	{
		$ret = ($num / $ch) * 100;
		$ret2 = number_format($ret,2);
		return $ret2;
	}
function nuave($num, $ch)
	{
		$ret = ($num / $ch);
		$ret2 = number_format($ret,2);
		return $ret2;
	}
$bcid = getbcid();
extract($_REQUEST);
$pclients = projects::projectclients($bcid);
$projquery = "projectid = '$projectid'";
$client = $pclients[$projectid];
$objectives = new objectives($projectid);
$firstepoch = 99999999999;
$lastepoch = 0;
//limit to april 2014 onwards as previous tracking is inaccurate;
if (strlen($range_start) > 1)
{
    $s = strtotime($range_start." 00:00:00");
    if (strlen($range_end) > 1) {
    $e = strtotime($range_end." 23:59:59");
    }
    else $e = time();
    $startquery = "(startepoch > $s and startepoch < $e)"; 
}
else {
   $startqueryepoch = strtotime("2014-04-01 00:00:00"); 
   $s = $startqueryepoch;
   $e = time();
   $startquery = "(startepoch > $s and startepoch < $e)"; 
}
// $startdate = date("Y-m-d",$s);
// $enddate = date("Y-m-d",$e);
$startdate = date("Y-m-d");
$enddate = date("Y-m-d");

$agent_details = members::getallmemberdetails();
$teamsa = projects::projectteams($projectid);
$projectteams = $teamsa['ids'];
$teamnames = $teamsa['names'];
$teamnames['noteam'] = 'NoTeam';
$res = mysql_query("select userid, count(*) as callcount from finalhistory where $projquery and userid != 0 and $startquery group by userid;");
		while ($r = mysql_fetch_array($res))
			{
				$agents[$r['userid']] = $r;
                                $t = json_decode($agent_details[$r['userid']]['team'],true);
                                $tc =0;
                                foreach ($t as $tid)
                                {
                                    
                                    if (in_array($tid,$projectteams)) {
                                        $agent_teams[$r['userid']] = $tid;
                                        
                                        }
                                }
                                if (!$agent_teams[$r['userid']])
                                {
                                    $agent_teams[$r['userid']] = 'noteam';
                                }
                                $tid = $agent_teams[$r['userid']];

                                    $teams[$tid]['teamid'] = $tid;
                                    $teams[$tid]['callcount'] = $teams[$tid]['callcount'] + $r['callcount'];
                                
			}
                $actionsquery = "SELECT userid, action, sum(epochend - epochstart) as actionduration from actionlog where $projquery and epochend > 0 and epochstart > $s and epochstart < $e group by userid, action";
		$res = mysql_query($actionsquery);
		while ($r = mysql_fetch_array($res))
			{
                                $agents[$r['userid']]["userid"] = $r['userid'];
				$agents[$r['userid']][$r['action']] = $r['actionduration'];
                                $t = $agent_teams[$r['userid']];
                                $teams[$t][$r['action']] = $teams[$t][$r['action']] + $r['actionduration'];
                                
			}
                $res = mysql_query("SELECT userid,sum(answeredtime) as 'realtalk' from history where $projquery  and $startquery group by userid");
                while ($r = mysql_fetch_array($res))
			{
				$agents[$r['userid']]['realtalk'] = $r['realtalk'];
                                $t = $agent_teams[$r['userid']];
                                $teams[$t]['realtalk'] = $teams[$t]['realtalk'] + $r['realtalk'];
                                
			}
                $caq = "SELECT userid,startepoch, endepoch, answeredtime, dialedtime, dialmode, systemdisposition,agentdisposition from finalhistory  where $projquery and $startquery ";
		$res = mysql_query($caq);
                $callattempts = 0;
                $dispositions = array();
                while ($r = mysql_fetch_assoc($res))
			{
                            if ($r['startepoch'] < $firstepoch) $firstepoch = $r['startepoch'];
                            if ($r['startepoch'] > $lastepoch) $lastepoch = $r['startepoch'];
                            $adispo = strlen($r['agentdisposition']) > 0 ? $r['agentdisposition']:'NoDisposition';
                            $dispositions[$adispo] = $dispositions[$adispo] + 1;
                            if($r['dialmode'] == 'manual')
                            {
                                if ($r['dialedtime'] == 0) $dialedtime = $r['endepoch'] - $r['startepoch'];
                                else $dialedtime = $r['dialedtime'] - $r['answeredtime'];
                                $agents[$r['userid']]['dialedtime'] = $agents[$r['userid']]['dialedtime'] + $dialedtime;
                                $t = $agent_teams[$r['userid']];
                                $teams[$t]['dialedtime'] = $teams[$t]['dialedtime'] + $dialedtime;
                                
                            }
                            if ($r['systemdisposition'] == 'ANSWER')
                            {
                                
                            }
                            $callattempts = $callattempts + 1;
			}
                //compute for days elapsed;
                $totalepoch = $lastepoch - $firstepoch;
                $dayfloat = $totalepoch / 86400; //total seconds divide by number of seconds in a day
                $denom['day'] = intval($dayfloat);
                $weekfloat = $dayfloat / 7;
                $monthfloat = $dayfloat / 30;
                $denom['week'] = intval($weekfloat);
                $denom['month'] = intval($monthfloat);
                $mo = array();
                $label['day'] = 'Daily';
                $label['week'] = 'Weekly';
                $label['month'] = 'Monthly';
                foreach ($objectives->campaign as $cobj)
                {
                $qt = "select userid, count(*) as contacts from finalhistory where $projquery and userid != 0 and agentdisposition = '".$cobj['disposition']."' and $startquery  group by userid;";
                 if ($cobj['type'] == 'sum')  {$mo[$cobj['id']]= '<th class="tableheader">'.$cobj['disposition'].'</th>';}
                 else {$mo[$cobj['id']]= '<th class="tableheader">'.$label[$cobj['period']].' Average '.$cobj['disposition'].'</th>';}
                $res = mysql_query($qt);
		while ($r = mysql_fetch_array($res))
			{
                            $uobj[$r['userid']] = $r;
                            /*if ($cobj == 'sum')  {
                                $agents[$r['userid']][$cobj['id']] = $r['contacts'] > 0 ? $r['contacts']:'0';
                               
                                $me .= '';
                                }
                            else {
                                $ave = number_format(($r['contacts'] / $denom[$cobj['period']]),2,".",'');
                                $agents[$r['userid']][$cobj['id']] = $ave > 0 ? $ave:'0';
                                
                                $me .= '';
                            }*/
			}
                foreach ($agents as $ag)
                    {
                        if ($cobj['type'] == 'sum')  {
                            $agents[$ag['userid']]['obj-'.$cobj['id']] = $uobj[$ag['userid']]['contacts'] > 0 ? $uobj[$ag['userid']]['contacts']:'0';
                            $t = $agent_teams[$ag['userid']];
                            $tob = $uobj[$ag['userid']]['contacts'] > 0 ? $uobj[$ag['userid']]['contacts']:0;
                             $teams[$t]['obj-'.$cobj['id']] = $teams[$t]['obj-'.$cobj['id']] + $tob;
                        }
                        else {
                            $ave = number_format(($uobj[$ag['userid']]['contacts'] / $denom[$cobj['period']]),2,".",'');
                            $agents[$ag['userid']]['obj-'.$cobj['id']] = $ave > 0 ? $ave:'0';
                            $t = $agent_teams[$ag['userid']];
                            $ntob[$t] = $ntob[$t] + $uobj[$ag['userid']]['contacts'];
                            $tave = $ntob[$t] / $denom[$cobj['period']];
                            $teams[$t]['obj-'.$cobj['id']] = $tave;
                            
                        }
                    }
                }
               
		$table = '<div id="mainsnap"><table class="dataTable">
		<thead>
		<tr>
		<th class="tableheader">Agent Name</th>
		<th class="tableheader">Campaign Hours</th>
		<th class="tableheader">% TalkTime</th>
		<th class="tableheader">% DialTime</th>
		<th class="tableheader">% Wrap-upTime</th>
		<th class="tableheader">% WaitTime</th>';
		$table .= '
		<th class="tableheader">% PauseTime</th>
		<th class="tableheader">% PreviewTime</th>
		<th class="tableheader">Connected Calls</th>
                ';
                foreach ($mo as $mh)
                {
                    $table .= $mh;
                }
                $table .= '</tr></thead><tbody>';
		$ct = 0;
                if ($_REQUEST['view'] == 'agent')
                 {
		foreach ($agents as $agent)
			{
                            if ($agent['userid'] > 0)
                            {
                                if (!$agent['contacts']) $agent['contacts'] = 0;
                                $totaltalk = $agent['dial'] + $agent['talk'];
                                $realtalk = $agent['realtalk'];
                                $realdial = $agent['dialedtime'];
				$temptalk = number_format($realtalk /60,2,".",'');
                                $tempdial = number_format($realdial /60,2,".",'');
                                $tempwrap = number_format($agent['wrap'] /60,2,".",'');
                                $temppause = number_format($agent['pause'] /60,2,".",'');
                                $prevtime = number_format($agent['preview'] /60,2,".",'');
                                $totalwait = $agent['wait'];
                                $tempwait = number_format($totalwait /60,2,".",'');
				$chours = $temptalk + $tempdial + $temppause + $tempwait + $tempwrap + $prevtime;
                                $temphours = number_format($chours /60,2,".",'');
				$a = $agent_details[$agent['userid']];
				$class = 'tableitem';
				if ($ct % 2) $class = 'tableitem_';
				$table .= '<tr>';
				//$table .= '<td class="'.$class.'">'.$agent['userid'].'</td>';
				$table .= '<td class="'.$class.'">'.$a['afirst'].' '.$a['alast'].'('.$teamnames[$agent_teams[$agent['userid']]].')</td>';
				$table .= '<td class="'.$class.'">'.$temphours.'</td>';
				//$table .= '<td class="'.$class.'">'.$temptalk.'</td>';
                                $talkp = nuform($temptalk,$chours);
                                $table .= '<td class="'.$class.'">'.$talkp.'%</td>';
				//$table .= '<td class="'.$class.'">'.$tempdial.'</td>';
                                $dialp = nuform($tempdial,$chours);
                                $table .= '<td class="'.$class.'">'.$dialp.'%</td>';
                                //$table .= '<td class="'.$class.'">'.$tempwrap.'</td>';
                                $wrapp = nuform($tempwrap,$chours);
				$table .= '<td class="'.$class.'">'.$wrapp.'%</td>';
				//$table .= '<td class="'.$class.'">'.$tempwait.'</td>';
                                $waitp = nuform($tempwait,$chours);
				$table .= '<td class="'.$class.'">'.$waitp.'%</td>';
				//$table .= '<td class="'.$class.'">'.$agent['dial'].'</td>';
				//$table .= '<td class="'.$class.'">'.nuform($agent['dial'],$chours).'%</td>';
				//$table .= '<td class="'.$class.'">'.$temppause.'</td>';
                                $pausep = nuform($temppause,$chours);
				$table .= '<td class="'.$class.'">'.$pausep.'%</td>';
                                //$table .= '<td class="'.$class.'">'.$prevtime.'</td>';
                                $previewp = nuform($prevtime,$chours);
				$table .= '<td class="'.$class.'">'.$previewp.'%</td>';
                                $ccount = $agent['callcount'] >0 ? $agent['callcount']: '0';
				$table .= '<td class="'.$class.'">'.$ccount.'</td>';
                                if ($mo != '')
                                {
                                    foreach ($objectives->campaign as $cobj)
                                        {
                                            $table .= '<td class="objcol">'.$agent['obj-'.$cobj['id']].'</td>';
                                            $objtotal[$cobj['id']] = $objtotal[$cobj['id']] + $agent['obj-'.$cobj['id']];
                                        }
                                }
                                $talktotal = $talktotal + $talkp;
                                $dialtotal = $dialtotal + $dialp;
                                $wraptotal = $wraptotal + $wrapp;
                                $waittotal = $waittotal + $waitp;
                                $pausetotal = $pausetotal + $pausep;
                                $previewtotal = $previewtotal + $previewp;
                                $chourstotal = $chourstotal + $chours;
                                $conntotal = $conntotal + $ccount;
                                $table .= '</tr>';
				$ct++;
                            }
			}
                 }
                 //view by team
                 if ($_REQUEST['view'] == 'team')
                 {
                 
                 foreach ($teams as $agent)
			{
                     
                            if (strlen($agent['teamid']) > 0)
                            {
                                if (!$agent['contacts']) $agent['contacts'] = 0;
                                $totaltalk = $agent['dial'] + $agent['talk'];
                                $realtalk = $agent['realtalk'];
                                $realdial = $agent['dialedtime'];
				$temptalk = number_format($realtalk /60,2,".",'');
                                $tempdial = number_format($realdial /60,2,".",'');
                                $tempwrap = number_format($agent['wrap'] /60,2,".",'');
                                $temppause = number_format($agent['pause'] /60,2,".",'');
                                $prevtime = number_format($agent['preview'] /60,2,".",'');
                                $totalwait = $agent['wait'];
                                $tempwait = number_format($totalwait /60,2,".",'');
				$chours = $temptalk + $tempdial + $temppause + $tempwait + $tempwrap + $prevtime;
                                $temphours = number_format($chours /60,2,".",'');
				
				$class = 'tableitem';
				if ($ct % 2) $class = 'tableitem_';
				$table .= '<tr>';
				//$table .= '<td class="'.$class.'">'.$agent['userid'].'</td>';
				$table .= '<td class="'.$class.'">'.$teamnames[$agent['teamid']].'</td>';
				$table .= '<td class="'.$class.'">'.$temphours.'</td>';
				//$table .= '<td class="'.$class.'">'.$temptalk.'</td>';
                                $talkp = nuform($temptalk,$chours);
                                $table .= '<td class="'.$class.'">'.$talkp.'%</td>';
				//$table .= '<td class="'.$class.'">'.$tempdial.'</td>';
                                $dialp = nuform($tempdial,$chours);
                                $table .= '<td class="'.$class.'">'.$dialp.'%</td>';
                                //$table .= '<td class="'.$class.'">'.$tempwrap.'</td>';
                                $wrapp = nuform($tempwrap,$chours);
				$table .= '<td class="'.$class.'">'.$wrapp.'%</td>';
				//$table .= '<td class="'.$class.'">'.$tempwait.'</td>';
                                $waitp = nuform($tempwait,$chours);
				$table .= '<td class="'.$class.'">'.$waitp.'%</td>';
				//$table .= '<td class="'.$class.'">'.$agent['dial'].'</td>';
				//$table .= '<td class="'.$class.'">'.nuform($agent['dial'],$chours).'%</td>';
				//$table .= '<td class="'.$class.'">'.$temppause.'</td>';
                                $pausep = nuform($temppause,$chours);
				$table .= '<td class="'.$class.'">'.$pausep.'%</td>';
                                //$table .= '<td class="'.$class.'">'.$prevtime.'</td>';
                                $previewp = nuform($prevtime,$chours);
				$table .= '<td class="'.$class.'">'.$previewp.'%</td>';
                                $ccount = $agent['callcount'] >0 ? $agent['callcount']: '0';
				$table .= '<td class="'.$class.'">'.$ccount.'</td>';
                                if ($mo != '')
                                {
                                    foreach ($objectives->campaign as $cobj)
                                        {
                                            $table .= '<td class="objcol">'.$agent['obj-'.$cobj['id']].'</td>';
                                            $objtotal[$cobj['id']] = $objtotal[$cobj['id']] + $agent['obj-'.$cobj['id']];
                                        }
                                }
                                $talktotal = $talktotal + $talkp;
                                $dialtotal = $dialtotal + $dialp;
                                $wraptotal = $wraptotal + $wrapp;
                                $waittotal = $waittotal + $waitp;
                                $pausetotal = $pausetotal + $pausep;
                                $previewtotal = $previewtotal + $previewp;
                                $chourstotal = $chourstotal + $chours;
                                $conntotal = $conntotal + $ccount;
                                $table .= '</tr>';
				$ct++;
                            }
			}
                 }
                //
                $table .= '<tr style="vertical-align:top; padding-top:30px"><td></td><td>Total: <br><b>'.number_format($chourstotal /60,2,".",'').'</td>';
                $table .= '<td>Average:<br><b>'.nuave($talktotal,$ct).'</td>';
                $table .= '<td>Average:<br><b>'.nuave($dialtotal,$ct).'</td>';
                $table .= '<td>Average:<br><b>'.nuave($wraptotal,$ct).'</td>';
                $table .= '<td>Average:<br><b>'.nuave($waittotal,$ct).'</td>';
                $table .= '<td>Average:<br><b>'.nuave($pausetotal,$ct).'</td>';
                $table .= '<td>Average:<br><b>'.nuave($previewtotal,$ct).'</td>';
                $table .= '<td>Total:<br><b>'.$conntotal.'</td>';
                foreach ($objectives->campaign as $cobj)
                                        {
                                            $table .= '<td class="objcol">Total:<br><b>'.$objtotal[$cobj['id']].'<br>Target:<br><b>'.$cobj['target'].'</td>';
                                        }
		if ($act2 == 'excel')
			{
				createdoc("excel",$table);
			}
                        //var_dump($actionsquery);
                $table .= '</tbody></table></div>';
                $sps = projects::projectnames($bcid);
                foreach ($sps as $key=>$val)
                {
                    $selec = '';
                    if ($key == $projectid) $selec = 'selected="selected"';
                    $pdrop .= '<option value="'.$key.'" '.$selec.'>'.$val.'</option>';
                }
                ?>

<div style="float:left;display:none">Campaign:
    <select id="snapprojectid" onchange="getsnapshot_drop()">
        <?=$pdrop;?>
    </select>
</div>
<div style="float:left;">Date Range:
    <input type="text" id="range_start" name="range_start" class="datepick" value="<?=$startdate;?>" style="width:100px;padding:5px"> to 
     <input type="text" id="range_end" name="range_end" class="datepick" value="<?=$enddate;?>" style="width:100px;padding:5px"> &nbsp;
     <a href="#" onclick="getsnapshot_drop()" class="jbut">Update</a>
</div>
<div style="float:right">View:
    <select id="snapview" onchange="getsnapshot_drop()">
        <option value="team">Team</option>
        <option value="agent" 
        <?
        echo $view == 'agent' ? 'selected="selected"':'';
        ?>>Agent</option>
    </select>
</div>
<div style="clear:both"></div>
<div id="apdiv">
    <div class="apptitle"><?=ucfirst($view);?> Summary</div>
<?
		echo $table;
               
                ?>
    
    <div id="disposummary" style="float:left;width:54%">
        <div class="apptitle">Disposition Summary</div>
        <?
        $headers = array();
        $rows = array();
        $headers[] = 'Disposition';
        $headers[] = 'Count';
        foreach ($dispositions as $key=>$val)
        {
            $rows[$key]['name'] = $key;
             $rows[$key]['count'] = $val;
        }
        echo tablegen($headers,$rows,'100%');
        ?>
    </div>
    <div id="callsummary" style="float:left;width:44%">
        <div class="apptitle">Call Summary</div>
        <?
        $headers = array();
                $headers[] = 'Metric';
                $headers[] = 'Result';
                $rows = array();
                $rows[1]['metric'] = 'Call Attempts';
                $rows[1]['result'] = $callattempts;
                $rows[2]['metric'] = 'Connected Calls';
                $rows[2]['result'] = $conntotal;
                $rows[3]['metric'] = '% Connected Calls';
                $rows[3]['result'] = nuform($conntotal,$callattempts);
                echo tablegen($headers,$rows,'100%');
                ?>
    </div>
</div>
<div style="clear:both"></div>
<div>
    <br>
    <?
    $client = $pclients[$projectid];
    if ($client > 0) echo '<p><b><a class="jbut" href="#" onclick="exporttoclient(\''.$client.'\',\'Snapshot for '.$sps[$projectid].' ('.$startdate.' to '.$enddate.')\')">Export to Client</a></b></p>';
    ?>
    <br>
</div>
<?
		exit;
	
?>
