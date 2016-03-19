<?php
$today = date("Y-m-d") . " 23:59:59";
if (isset($_REQUEST['view']))
{
    $view = $_REQUEST['view'];
     if ($view == 'due')
    {
        $d = date_create($today);
        $seld = 'SELECTED';
    }
    if ($view == 'duetom')
    {
        $d = date_create($today);
        date_add($d, date_interval_create_from_date_string('1 day'));
        $seltom = 'SELECTED';
    }
    if ($view == 'dueweek')
    {
        $d = date_create($today);
        date_add($d, date_interval_create_from_date_string('7 days'));
        $selweek = 'SELECTED';
    }    
    $today = date_format($d, 'Y-m-d H:i:s');    
}
	$stres = mysql_query("select statusname,category from statuses where category in('agent','team') and statustype not like 'transfer%' and projectid in ('0','$pid')");
	$ct = 0;
	while ($strow = mysql_fetch_array($stres))
		{
		if ($ct != 0) $in .=",";
		$in .= "'".$strow['statusname']."'";
                $statcat[$strow['statusname']] = $strow['category'];
		$ct++;
		}
	$ct =0;
if ($act == 'updatecheck')
{
    $query = "SELECT count(*) from leads_done where projectid = '$pid' and assigned in ('$auid','0') and dispo in ($in) and status = 'assigned' and epoch_callable <= '".strtotime($today)."'";
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    echo $row[0];
    exit;
}
$query = "SELECT leads_done.* from leads_done where projectid = '$pid' and assigned in ('$auid','0') and dispo in ($in) and status = 'assigned' and epoch_callable <= '".strtotime($today)."'";
$cbres = mysql_query($query);
$ct = 0;
while ($row = mysql_fetch_array($cbres))
{
	$cbs[$row['leadid']] = $row;
}
$retquery = "SELECT * from leads_done where projectid = '$pid' and dispo in ($in) and status = 'assigned' and epoch_callable <= '".strtotime($today)."'";
$retres = mysql_query($retquery);
while ($row = mysql_fetch_array($retres))
{
	$cbs[$row['leadid']] = $row;
}

foreach ($cbs as $cbrow)
	{
                $cb = '';
                 if ($statcat[$cbrow['dispo']] == 'agent' || $cbrow['isteamcallback'] == 0)
                 {
		$cb .= '<tr style="font-size: 12px;cursor:pointer " onmouseover="cbhi(this)" onmouseout="cbout(this)" onclick="cbdial(\''.$cbrow['leadid'].'\')">';
                 }
                 else $cb .= '<tr style="font-size: 12px;cursor:pointer " onmouseover="cbhi(this)" onmouseout="cbout(this)" onclick="team_cbdial(\''.$cbrow['leadid'].'\')">';
		$cb .= '<td style="width:100px;">&nbsp;';
		$cb .= addslashes($cbrow['cname']);
		$cb .= '</td>';
		$cb .= '<td style="width:100px;"> ';
		$cb .= addslashes($cbrow['cfname']);
		$cb .= '</td>';
		$cb .= '<td style="width:100px;"> ';
		$cb .= addslashes($cbrow['clname']);
		$cb .= '</td>';
		$cb .= '<td style="width:170px; "> ';
		$cb .= addslashes($cbrow['company']);
		$cb .= '</td>';
		$cb .= '<td style="width:100px; "> ';
		$cb .= addslashes($cbrow['phone']);
		$cb .= '</td>';
		$cb .= '<td style="width:130px;"> ';
		$cb .= addslashes(date("Y-m-d H:i:s",$cbrow['epoch_callable']));
		
		$cb .= '</td></tr>';
                if ($cbrow['status'] == 'assigned')
			{
                            if ( $statcat[$cbrow['dispo']] == 'agent' && $cbrow['assigned'] == $auid ) 
                            {
                                $recallballs['CallBacks'] .= $cb;
                            }
                            elseif ( $cbrow['isteamcallback'] == 0 && $cbrow['assigned'] == $auid )
                            {
                                $recallballs['CallBacks'] .= $cb;
                            }
                            elseif ($cbrow['isteamcallback'] == 1)
                            {	
                            	$recallballs['Team CallBacks'] .= $cb;
							}
			}
                if ($cbrow['status'] == 'incomplete')
			{
				$recallballs['Incompletes'] .= $cb;
			}
                if ($cbrow['status'] == 'failed' || $cbrow['status'] == 'rejected')
			{
				$recallballs['QA Rejects'] .= $cb;
			}
		$ct++;
        
	}

$clquery = "SELECT leads_done.*,client_leads.dispo as cdispo, client_leads.status as clstatus from leads_done  left join client_leads on leads_done.leadid = client_leads.leadid where assigned = '$auid' and actionstatus = 'clientlock' and leads_done.projectid = '$pid'";
$clres = mysql_query($clquery);
while ($row = mysql_fetch_array($clres))
{
	$cl[$row['leadid']] = $row;
}
foreach ($cl as $cbrow)
	{
		$clb .= '<tr style="font-size: 12px; cursor:pointer;" onmouseover="cbhi(this)" onmouseout="cbout(this)" onclick="cbdial(\''.$cbrow['leadid'].'\')">';
		$clb .= '<td style="width:100px;">&nbsp;';
		$clb .= addslashes($cbrow['name']);
		$clb .= '</td>';
		$clb .= '<td style="width:100px;"> ';
		$clb .= addslashes($cbrow['cfname']);
		$clb .= '</td>';
		$clb .= '<td style="width:100px;"> ';
		$clb .= addslashes($cbrow['clname']);
		$clb .= '</td>';
		$clb .= '<td style="width:170px; "> ';
		$clb .= addslashes($cbrow['company']);
		$clb .= '</td>';
		$clb .= '<td style="width:100px; "> ';
		$clb .= addslashes($cbrow['phone']);
		$clb .= '</td>';
		$clb .= '<td style="width:130px;"> ';
		$clb .= addslashes($cbrow['dtime']);
		$clb .= '</td>';
		$clb .= '<td style="width:130px;"> ';
		$clb .= addslashes($cbrow['cdispo']);
		$clb .= '</td></tr>';
		$ct++;
	}
?>

<div id="maincb" style="margin-left:20px; font-size:0.8em;overflow: auto; height: 100%;">
    <div><a href="#" onclick="refreshcallbacks()" class="jbut">Refresh</a></div>
    <div style="float:right">
        CallBacks View: <select id="cbview" name="cbview" onchange="changecbview()">
            <option value="due" <?=$seld;?>>Due Today</option>
            <option value="duetom" <?=$seltom;?>>Due Tomorrow</option>
            <option value="dueweek" <?=$selweek;?>>Due in 7 days</option>
        </select>
    </div>
<br />
<?php
foreach ($recallballs as $title=>$cb)
{
?>
<h3><?php echo $title;?></h3>
<table width="100%" style="font-size:1em" class="sortable">
<thead>
<tr>
<th class="tableheader">Name</th>
<th class="tableheader">Firstname</th>
<th class="tableheader">Lastname</th>
<th class="tableheader" style="width:170px">Company</th>
<th class="tableheader">Phone</td>
<th class="tableheader" style="width:130px">Date</th>
</tr></thead><tbody>
<?=$cb;?>
</tbody>
</table>
<br />
<?php
}
?>
<div style="display:none">
<p><b>Client Leads</b></p>
<table width="980">

<tr>
<td class="tableheader">Name</td>
<td class="tableheader">Firstname</td>
<td class="tableheader">Lastname</td>
<td class="tableheader" style="width:170px">Company</td>
<td class="tableheader">Phone</td>
<td class="tableheader" style="width:130px">Date</td>
<td class="tableheader" style="width:130px">Status</td>
</tr>
<?=$clb;?>
</table></div>
</div></div>
</div>
