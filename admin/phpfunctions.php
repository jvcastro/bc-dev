<?php
function debug_query($queries)
{
	if (!preg_match("/^\/admin-dev/", $_SERVER['PHP_SELF']))
		return;

	mysql_query("INSERT INTO debug_query SET query=\"" . $_SERVER['PHP_SELF'] ."\"");

	foreach ($queries as $qry) {
		mysql_query("INSERT INTO debug_query SET query =\"" . $qry ."\"");
		// echo "Content-type: text/html";
		// echo "INSERT INTO debug_query SET query =\"" . $qry ."\"";
	}
}
function addusertoteam($userid,$teamid)
{
    $t = $teamid;
	$u = $userid;
	$tq = "SELECT team from memberdetails where userid = '$u'";
        $tea = mysql_query("SELECT * from teams where bcid = '$bcid'");
        $validteams = array();
        while ($tow = mysql_fetch_assoc($tea))
        {
            $validteams[] = $tow['teamid'];
        }
	$tres = mysql_query($tq);
	$row = mysql_fetch_array($tres);
	$uteams = json_decode($row['team'],true);
	if (!in_array($t,$uteams))
	
		{
		$uteams[] = $t;
		mysql_query("update memberdetails set team = '".json_encode($uteams)."' where userid = '$u'");
		}
}
function checkagentrole($userid)
{
    
    $res = mysql_query("select roleid from members where userid = '$userid'");
    $row = mysql_fetch_assoc($res);
    $rid = $row['roleid'];
    $res2 = mysql_query("SELECT agent_portal from roles where roleid = $rid");
    $row2 = mysql_fetch_assoc($res2);
    $ap = $row2['agent_portal'];
    return $ap;
}
function tominsecs($td)
{
    $tdmin = intval($td / 60);
     $tdsecs = $td % 60;
     $ret['minutes'] = $tdmin;
    $ret['seconds'] = $tdsecs;
    return $ret; 
}
function checkexisting($table,$field,$value)
{
    $res = mysql_query("SELECT $field from $table where $field = '$value'");
    $count = mysql_num_rows($res);
    return $count;
}
function getaticketid($em)
{
	$con = mysql_connect("qld-wv-167.bluecloudaustralia.com.au","obri","niner123");
	mysql_select_db("bc_support",$con);
	$res = mysql_query("SELECT ticketID from ost_ticket where email = '$em'",$con);
	$row = mysql_fetch_row($res);
	mysql_close($con);
	return $row[0];
}
function savelead($leadid)
	{
            $record = new records($leadid);
            //mysql_query("update dateandtime set dtime = '".$_POST['dtime']."' where leadid = '$leadid' ");
            $record->setdatetime($_POST['dtime']);
            $pid = $record->projectid;
            $oldispo = $record->data['dispo'];
            $record->data = $_POST;
            $record->leadid = $leadid;
            $record->update();
            if ($oldispo != $_POST['dispo'])
            {
            $dres = mysql_query("SELECT * from statuses where statusname = '".$_POST['dispo']."' and projectid ='$pid'");
               $drow = mysql_fetch_assoc($dres);
               if (substr($drow['statustype'],0,8) == 'transfer')
               {
                   $dotransfer = 1;
               }
               elseif ($drow['category'] == 'team')
               {
                   mysql_query("update leads_done set isteamcallback = 1 where leadid = ".$leadid);
               }
            $record->addnote("System","Disposed by ".$_SESSION['username']." as ".$record->data['dispo']);    
            if ($dotransfer > 0) {
                $torow = $drow;           
            $data = $record->data;
            $tocat = $torow['category'];

            $tolist = $torow['options'];
            $nlistres = mysql_query("SELECT * from lists where lid = '$tolist'");
            $nlist = mysql_fetch_assoc($nlistres);
            $newrec = new records();
            $newrec->data = $data;
            $newrec->listid = $nlist['listid'];
            $newrec->hopper = 1;
            $newrec->projectid = $nlist['projects'];
            $newrec->createraw();
            $newrec->createdone();
            if ($tocat == 'team')
            {
                $newrec->isteamcallback = 1;
            }
            $newrec->assigned = 0;
            $newrec->timeofcall = 'NOW()';
            $newrec->epoch_timeofcall = time();
            $newrec->hopper = 1;
            $newrec->update();
            //add to notes
            $newrec->addnote("System","Transferred by ".$_SESSION['username']." from ".$isprow['projectname'] );
            //Transfer Custom Data // scriptfields
            $tcdata = $record->customdata();
            $tsdata = $record->scriptdata();

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
            if ($tocat == 'agent' || $tocat == 'team')
            {
                $cres = mysql_query("SELECT * from statuses where statusname = '".$data['dispo']."' and projectid = '".$nlist['projects']."'");
                if (mysql_num_rows($cres) < 1)
                {
                    mysql_query("INSERT into statuses set projectid = '".$nlist['projects']."', statusname = '".$data['dispo']."', statustype = 'text', category = '$tocat'");
                }
            }
            if (strlen($data['dtime']) > 1) {$newrec->setdatetime($data['dtime']);}
	}
            }
}
function createdropdown($options = array(),$namefield, $valuefield)
	{
		foreach ($options as $option)
			{
				$disp .= '<option value="'.$option[$valuefield].'" "'.$option['selected'].'">'.ucfirst($option[$namefield]).'</option>';
			}
		return $disp;
	}
function createinput($idname, $value ="", $type = "text", $class="")
	{
		return "<input type=\"$type\" id=\"$idname\" name=\"$idname\" value=\"$value\" class=\"$class\" />";
	}
function createselect($idname, $value ="", $clientcontactid = "")
	{
		if ( $value == "" ) {
			return "<select id=\"$idname\">
					  <option value=\"\">-</option>
			          <option value=\"Daily\">Daily</option>
			          <option value=\"Weekly\">Weekly</option>
			          <option value=\"Monthly\">Monthly</option>
			          <option value=\"Yearly\">Yearly</option>
			    	</select>";
		} else {
			return "<select id=\"$idname\">
					  <option value=\"".$value."_".$clientcontactid."\">$value</option>
			          <option value=\"Daily_$clientcontactid\">Daily</option>
			          <option value=\"Weekly_$clientcontactid\">Weekly</option>
			          <option value=\"Monthly_$clientcontactid\">Monthly</option>
			          <option value=\"Yearly_$clientcontactid\">Yearly</option>
			    	</select>";
		}
	}
function isbooked($c)
	{
		if ($c == 1 ) 
			{
			return "Booked";
			}
		elseif ($c == 2)
			{
			return "Locked";
			}
		else return "Free";
	}
function createdoc($type,$table,$exit = true, $filename = NULL, $css = 'cstyle.css')
	{
		$doctype["excel"] = "vnd.ms-excel";
		$doctype["word"] = "vnd.ms-word";
		$extension["excel"] = ".xls";
		$extension["word"] = ".doc";
                if (strlen($filename) < 1)
                {
		$filename = substr(md5(time()),-5);
                }
		header("Content-type: application/".$doctype[$type]);
		header("Content-Disposition: attachment; filename=".$filename.$extension[$type]);
		header("Pragma: no-cache");
		header("Expires: 0");
		echo "<style>";
		include "$css";
		echo "</style>";
		echo $table;
		if ($exit)
			{
				exit;
			}
	}
function getroles($bcid)
	{
		$q = "SELECT * from roles where bcid in ('0','$bcid')";
		if ($bcid == 'all')
			{
				$q = "SELECT * from roles";
			}
		$res = mysql_query($q);
		while ($row = mysql_fetch_assoc($res))
			{
				$arr[$row['roleid']] = $row;
				$opt .= '<option value="'.$row['roleid'].'">'.$row['rolename'].'</option>';
			}
		$roles['array'] = $arr;
		$roles['options'] = $opt;
		return $roles;
	}
function newadmin($bcid,$params)
	{
		$userlogin = $params['userlog'];
		$userpass = $params['userp'];
		$role = $params['role'];
		if ($role < 1) $role = 1;
		if (checkuser($userlogin))
			{
				mysql_query("INSERT into members set userlogin = '$userlogin', userpass = '$userpass', usertype = 'admin', bcid = '$bcid', roleid = '$role'");
				return "New admin user added";
			}
		else 
			{
				return "Username already exists..";

			}
	}
function checkuser($userlogin)
	{
		$res = mysql_query("SELECT userid from members where userlogin = '$userlogin'");
		$n = mysql_num_rows($res);
		if ($n > 0 ) return false;
		else return TRUE;
	}
function getrights($roleid)
	{
		$res = mysql_query("SELECT * from roles where roleid = '$roleid'");
		$row = mysql_fetch_assoc($res);
		return $row;
	}
function checkrights($right)
	{
		$rights = $_SESSION['rights'];
		if ($rights[$right] < 1) return false;
                else return true;
	}
function deactivateuser($id)
{
    mysql_query("update members set active = 0 where userid = $id");
    return mysql_affected_rows();
}
function activateuser($id)
{
    mysql_query("update members set active = 1 where userid = $id");
    return mysql_affected_rows();
}
function checklevel($level)
{
    $rights = $_SESSION['rights'];
    if ($rights['level'] > $level) return false;
    else return true;
}
function getlevel($id)
{
    $res = mysql_query("SELECT members.userid, roles.level from members left join roles on members.roleid = roles.roleid where members.userid = $id");
    $row = mysql_fetch_assoc($res);
    return $row['level'];
}
function setuserrole($userid,$roleid)
{
    global $bcid;
    mysql_query("update members set roleid = '$roleid' where userid = $userid and bcid = $bcid");
    return mysql_affected_rows();
}
function getroledrop($roleid = NULL)
{
    global $bcid;
    $rights = $_SESSION['rights'];
    $roleres = mysql_query("SELECT * from roles where bcid in (0,'$bcid') and level >= ".$rights['level']);
    while ($role = mysql_fetch_assoc($roleres))
    {
        $rsel = $role['roleid'] == $roleid ? 'selected="selected"':'';
        $roledrop .= '<option value="'.$role['roleid'].'" '.$rsel.' >'.$role['rolename'].'</option>';
    }
    return $roledrop;
}
function getdispodrop($projectid = 0, $sel = NULL)
{
    global $bcid;
    $res = mysql_query("SELECT * from statuses where projectid in (0,$projectid)");
    $drop = '';
    while ($row = mysql_fetch_assoc($res))
    {
        $rsel = $row['statusname'] == $sel ? 'selected="selected"':'';
        $drop .= '<option value="'.$row['statusname'].'" '.$rsel.' >'.$row['statusname'].'</option>';
    }
    return $drop;
}
function getprojects($bcid,$onlyactive = true)
	{
                if ($onlyactive)
                {
                    $projres = mysql_query("SELECT * from projects where active = 1 and bcid = '$bcid' order by active DESC ;");
                }
		else $projres = mysql_query("SELECT * from projects where bcid = '$bcid' order by active DESC ;");
		$pp = 0;
		while ($projrow = mysql_fetch_array($projres))
			{
			$projects[$projrow['projectid']] = $projrow;
			$plist .= '<option value="'.$projrow['projectid'].'">'.$projrow['projectname'].'</option>';
			if ($pp > 0)
				{
					$plist_query .= ",";
				}
			$plist_query .= "'".$projrow['projectid']."'";
			$pp++;
			}
			
		$projectlist['list'] = $plist;
		$projectlist['pp'] = $pp;
		$projectlist['data'] = $projects;
		$projectlist['sql'] = $plist_query;
		return $projectlist;
	}
function tablegen($headers, $rows, $width = "770", $rowscript = NULL, $tableclass= NULL)
	{
		$table = '<table width="'.$width.'" class="'.$tableclass.'">';
		$table .= '<thead><tr>';
                $columncount = count($headers);
		foreach ($headers as $header)
			{
                                if (is_array($header)) $table .= '<th class="tableheader" '.$header['options'].'>'.$header['text'].'</th>';
				else $table .= '<th class="tableheader">'.$header.'</th>';
			}
		$table .= '</tr></thead><tbody>';
		$c = 1;
                if (count($rows) > 0)
                {
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
								 $table .= '<td class="'.$key.'">'.$item.'</td>';
							}
					}
				$table .= '</tr>';
			}
                }
                else $table .= '<tr class="tableitems"><td colspan="'.$columncount.'" style="text-align:center">No Records</td></tr>';
		$table .= '</tbody></table>';
		return $table;
	}
function tablegen2($headers, $rows, $width = "770", $rowscript = NULL, $tableclass= NULL)
{
    echo '<table width="'.$width.'" class="'.$tableclass.'">';
		echo '<thead><tr>';
		foreach ($headers as $header)
			{
                                if ($header == 'epoch_timeofcall')
                                {
                                    echo '<th class="tableheader">'.ucfirst(labels::get("timeofcall")).'</th>';
                                }
				else echo '<th class="tableheader">'.ucfirst(labels::get($header)).'</th>';
			}
		echo '</tr></thead><tbody>';
		$c = 1;
		foreach ($rows as $row)
			{
				$c++;
				if ($c % 2) $class = "tableitem";
				else $class = "tableitem_";
				echo '<tr class="'.$class.'" '.$row['options'].'>';
				foreach ($headers as $header)
					{
                                            if ($header == 'epoch_timeofcall')
                                            {
                                                if (strlen($row[$header]) < 1)
                                                {
                                                    echo '<td>0000-00-00 00:00:00</td>';
                                                }
                                                else echo '<td>'.date("Y-m-d H:i:s",$row[$header]).'</td>';
                                            }
                                            else if ($header == 'epoch_callable')
                                            {
                                                if ($row[$header] < 1)
                                                {
                                                    echo '<td>0000-00-00 00:00:00</td>';
                                                }
                                                else echo '<td>'.date("Y-m-d H:i:s",$row[$header]).'</td>';
                                            }
                                            else echo '<td>'.$row[$header].'</td>';
					}
				echo '</tr>';
			}
		echo '</tbody></table>';
		//return $table;
}
function buyfeature($bcid,$feat)
	{
		$res = mysql_query("SELECT * from bc_features_details where feature = '$feat'");
		$feature = mysql_fetch_assoc($res);
		if ($feature['type'] == 'option')
			{
				$exp = getexpiry($feature['interval']);
				$cost = $feature['cost'];
				$wallet = getwallet($bcid);
				if ($cost > $wallet['loadedcredits'])
					{
						return "Insufficient Credits";
					}
				else {
					$newcredit = $wallet['loadedcredits'] - $cost;
					mysql_query("update bc_wallet set loadedcredits = '$newcredit' where bcid = '$bcid'");
					mysql_query("insert into bc_purchases set feature = '$feat', bcid = '$bcid', cost = '$cost', epoch = '".time()."'");
					mysql_query("update bc_features set $feat = 1 where bcid = '$bcid'");
					$fres = mysql_query("SELECT * from bc_features_exp where bcid = '$bcid' and feature = '$feat'");
					if (mysql_num_rows($fres) > 0)
					{
					$fexp = mysql_fetch_assoc($fres);
					if ($fexp['expdate'] < time())
						{
							mysql_query("update bc_features_exp set feature = '$feat', expdate = '".$exp['epoch']."' where bcid = '$bcid'");
						}
					else 
						{
							$nexp = $fexp['expdate'] + $exp['add'];
							mysql_query("update bc_features_exp set feature = '$feat', expdate = '".$nexp."' where bcid = '$bcid'");
						}
					}
					else mysql_query("insert into bc_features_exp set feature = '$feat', bcid = '$bcid', expdate = '".$exp['epoch']."'");
					return "done";
				}
			}
		else {
			mysql_query("update bc_features set inbound = 0,outbound = 0, blended = 0 where bcid = '$bcid'");
			mysql_query("update bc_features set $feat = 1 where bcid = '$bcid'");
			return done;
		}
	}
function getexpiry($interval)
	{
		$dur['monthly'] = 2592000;
		$dur['weekly'] = 604800;
		$dur['yearly'] = 31536000;
		$expire['epoch'] = time() + $dur[$interval];
		$expire['add'] = $dur[$interval];
		return $expire;
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
function getdm($bcid)
	{
		$rate = getrates($bcid);
		$res = mysql_query("SELECT * from bc_features where bcid = '$bcid'");
		$r = mysql_fetch_assoc($res);
		$f = array_keys($r);
		foreach ($f as $fld)
			{
				if ($fld == 'inbound' || $fld == 'outbound' || $fld == 'blended')
					{
						if ($r[$fld] == 1) 
							{
							$ret['dm'] = $fld;
							$ret['rate'] = $rate[$fld];
							$ret['rateid'] = $rate['rateid'];
							}
					}
			}
			
		return $ret;
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
function dopurchase($packageid, $num, $bcid)
	{
		$bc = getclientdetails($bcid);
		$wallet = getwallet($bcid);
		$packres = mysql_query("SELECT * from bc_packages where packageid = '$packageid'");
		$pack = mysql_fetch_array($packres);
		$cost = $pack['packagecost'] * $num;
		if ($cost > $wallet['loadedcredits'])
			{
				$n = "insufficient";
			}
		else {
			$lc = $wallet['loadedcredits'] - $cost;
			$tot = $pack['qty'] * $num;
			switch ($pack['packagetype']) {
				case 'credits': mysql_query("update bc_clients set credits = credits + ".$tot." where bcid = '$bcid'");break;
				case 'mobile credits': mysql_query("update bc_clients set credits_mobile = credits_mobile + ".$tot." where bcid = '$bcid'");break;
			}
			mysql_query("update bc_wallet set loadedcredits = '$lc' where bcid = '$bcid'");
			$n = 'done';
		}
		return $n;
	}
function getwallet($bcid)
	{
		$res = mysql_query("SELECT * from bc_wallet where bcid = '$bcid'");
		$r = mysql_fetch_array($res);
		return $r;
	}
function savetable($table,$arr)
	{
		$fields = array_keys($arr);
		foreach ($arr as $r)
			{
				$updatedrows = 0;
				if ($r['changed'] == 1)
					{
						
						$ct = 0;
						$q = "Update $table set ";
						foreach($fields as $field)
							{
							   if ($field != "changed" && $ct != 0)
							   {
								if ($ct > 1) $q .= ",";
								$q .= " $field = '".$r[$field]."'";
							   }
								$ct++;
							}
						$q.= " where ".$fields[0]." = '".$r[$fields[0]]."'";
						mysql_query($q);
						$updatedrows++;
					}
			}
	}
function getdatatablesorted($table, $id = 'id', $sortkey = 'company')
	{
		$r = mysql_query("SELECT * from $table ORDER BY $sortkey");
		$m = array();
		while ($row = mysql_fetch_array($r))
			{
				$m[$row[$id]] = $row;
			}
		return $m;
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
function getbyparams($table, $params, $id = 'id')
	{
		$r = mysql_query("SELECT * from $table where $params");
		$m = array();
		while ($row = mysql_fetch_array($r))
			{
				$m[$row[$id]] = $row;
			}
		return $m;
	}
function buypackage($packageid)
	{
		$r = mysql_query("SELECT * from bc_packages where packageid = '$packageid'");
		$row = mysql_fetch_array($r);
		$t = '<hr><table>';
		$ct = 0;
				$t .= '<tr>';
				$t .= '<td style="margin-bottom:50px"><img src="../images/'.$row['packagetype'].'.jpg" /></td>';
				$t .= '<td><b>'.$row['packagename'].'</b><br>'.$row['qty'].' '.ucfirst($row['type']).'<br>';
				$t .= 'Cost: '.$row['packagecost'].' x Qty: <input type="text" id="packnum" value="1" onchange="computetcc(\''.$row['packagecost'].'\')"> = <span id="totalcreditcost">'.$row['packagecost'].'</span>';
				$t .= '<br><a href="#" onclick="dopurchase(\''.$packageid.'\')">Confirm</a> <br><br></td>';
				if ($ct % 2 == 0 && $ct != 0) $t .= '</tr>';
				
				$ct++;
		$t .= '</table><br>';
		return $t;
	}
function getpackages($type)
	{
		$r = mysql_query("SELECT * from bc_packages where packagetype = '$type' and active = 1");
		if ($type == 'all') $r = mysql_query("SELECT * from bc_packages");
		$t = '<hr><table>';
		$ct = 0;
		while ($row = mysql_fetch_array($r))
			{
				if ($ct % 2 == 0 || $ct == 0) $t .= '<tr>';
				$t .= '<td style="margin-bottom:50px"><img src="../images/'.$row['packagetype'].'.jpg" /></td>';
				$t .= '<td><b>'.$row['packagename'].'</b><p>'.$row['packagedescription'].'</p>'.$row['qty'].' '.ucfirst($row['type']).'<br>';
				$t .= 'Cost: '.$row['packagecost'].'<br><a href="#" onclick="buypackage(\''.$row['packageid'].'\')">Buy</a><br><br></td>';
				if ($ct % 2 == 0 && $ct != 0) $t .= '</tr>';
				$ct++;
			}
		$t .= '</table><br>';
		return $t;
	}
	
function bcfeatures($bcid)
	{
		
	}
function getbclist($status = NULL)
	{
            $qs = '';
            if ($status)
            {
               $qs = "where status = '$status'";
            }
		$r = mysql_query("SELECT * from bc_clients $qs");
		$m = array();
		while ($row = mysql_fetch_array($r))
			{
				$m[$row['bcid']] = $row;
			}
		return $m;
	}
function getclientdetails($bcid)
	{
		$r = mysql_query("SELECT * from bc_clients where bcid = '$bcid'");
		$row = mysql_fetch_array($r);
		return $row;
	}
function getbcid()
	{
		$r = mysql_query("SELECT bcid from adminsessions where sessionid = '".$_REQUEST['PHPSESSID']."'");
		$row = mysql_fetch_row($r);
		$ret = $row[0];
		if (strlen($row) < 1)
			{
				$ret = $_SESSION['bcid'];
			}
		return $ret;
	}
function getmobileusage($bcid, $st, $en)
	{
		$ures = mysql_query("SELECT projectid from projects where bcid = '$bcid'");
		$uct = 0;
		while ($urow = mysql_fetch_array($ures))
			{
				if ($uct > 0) $ulist .= ",";
				$ulist .= "'".$urow['projectid']."'";
				$uct++;
			}
		$mobres = mysql_query("SELECT *,substr(FROM_unixtime(startepoch),1,10) as ddate from finalhistory where phone like '8804%' and projectid in ($ulist) and substr(FROM_unixtime(startepoch),1,10) >= '$st' and substr(FROM_unixtime(startepoch),1,10) <= '$en'");
		while ($row = mysql_fetch_array($mobres))
			{
				$dur = $row['endepoch'] - $row['startepoch'];
				$mobiles['total'] = $mobiles['total'] + $dur;
				$mobiles['daytotal'][$row['ddate']] = $mobiles['daytotal'][$row['ddate']] + $dur;
			}
		return $mobiles;
	}
function getlastdura($date,$userid,$lastlogin)
	{
		$res = mysql_query("SELECT epochend from actionlog_final where userid = '$userid' and daydate = '$date' order by logid DESC limit 1");
		$row = mysql_fetch_assoc($res);
		$epend = $row['epochend'];
		if ($lastlogin < $epend)
			{
				$dura = $epend - $lastlogin;
			}
		else {
			$dura = 100;
		}
		return $dura;
	}
function getbcusage($bcid, $st, $en)
	{
		//select users
		$ures = mysql_query("SELECT userid from members where bcid = '$bcid'");
		$uct = 0;
		while ($urow = mysql_fetch_array($ures))
			{
				if ($uct > 0) $ulist .= ",";
				$ulist .= "'".$urow['userid']."'";
				$uct++;
			}
			
		//get actionlog of users on dates
		$usage = array();
		$totalduration = 0;
		$acres = mysql_query("SELECT * from bc_logs where bcid = '$bcid' and date >= '$st' and date <= '$en' order by tlogid ASC");
		$c = 0;
		while ($row = mysql_fetch_array($acres))
			{
				$ac[$row['tlogid']] = $row;
				$c++;
			}
		$z = 0;
		foreach ($ac as $row)
			{
				$z++;
				if ($hanguser[$row['userid']] == 1)
					{
						$hanguser[$row['userid']] = 0;
						$tlogd = $hang[$row['userid']];
						if ($row['date'] != $ac[$tlogd]['date'])
							{
								
								$ndure = getlastdura($ac[$tlogd]['date'],$ac[$tlogd]['userid'],$ac[$tlogd]['login']);
								$detailed[$tlogd]['logout'] = $ac[$tlogd]['login'] + $ndure;
							
							}
						else {
						$detailed[$tlogd]['logout'] = $row['login'];	
						$ndure = $row['login'] - $ac[$tlogd]['login'];
						}
						$totalduration = $totalduration + $ndure;
						$duration[$row['userid']] = $duration[$row['userid']] + $ndure;
						$ddate = $ac[$tlogd]['date'];
						$dayduration[$ddate]['duration'] = $dayduration[$ddate]['duration'] + $ndure;
						$dayduration[$ddate]['date'] = $ddate;
						

					}
				if ($row['login'] < $row['logout'])
				{
					$dur = $row['logout'] - $row['login'];
					$totalduration = $totalduration + $dur;
					$duration[$row['userid']] = $duration[$row['userid']] + $dur;
					$dayduration[$row['date']]['duration'] = $dayduration[$row['date']]['duration'] + $dur;
				
					$dayduration[$row['date']]['date'] = $row['date'];
					
				}
				else {
					$dur = 0;
					$hang[$row['userid']] = $row['tlogid'];
					$hanguser[$row['userid']] = 1;
				}
				$detailed[$row['tlogid']] = $row;
				
				
			}
		$huids = array_keys($hanguser);
		foreach ($huids as $hids)
			{
				if ($hanguser[$hids] == 1)
					{
						$hanguser[$hids] = 0;
						$tlogd = $hang[$hids];
						$ndure = getlastdura($ac[$tlogd]['date'],$ac[$tlogd]['userid'],$ac[$tlogd]['login']);
						$detailed[$tlogd]['logout'] = $ac[$tlogd]['login'] + $ndure;
						$totalduration = $totalduration + $ndure;
						$duration[$hids] = $duration[$hids] + $ndure;
						$ddate = $ac[$tlogd]['date'];
						$dayduration[$ddate]['duration'] = $dayduration[$ddate]['duration'] + $ndure;
						$dayduration[$ddate]['date'] = $ddate;
					}
			}
		//compute for duration
		//return results
		$results['detailed'] = $detailed;
		$results['usagesecs'] = $totalduration;
		$h = $totalduration / 3600;
		$results['usagehours'] = number_format($h,2);
		$results['usageusers'] = $duration;
		$results['usagedays'] = $dayduration;
		return $results;
		
	}
function getmobilecost($bcid, $dura)
	{
		$rate = getrates($bcid);
		$rpm = $rate['mobrpm'];
		$rem = $dura % 60;
		$minutes = (($dura - $rem) / 60) + 1;
		$cost = $minutes * $rpm;
		$cost = number_format($cost,2);
		return $cost;
		
	}
function getusagecost($bcid, $dura)
	{
		$rate = getrates($bcid);
		$rph = $rate['rph'];
		$rem = $dura % 3600;
		$hours = (($dura - $rem) / 3600) + 1;
		$cost = $hours * $rph;
		$cost = number_format($cost,2);
		return $cost;
	}
function getrateslist()
	{
		$rres = mysql_query("SELECT * from bc_rates ");
	}
function getrates($bcid)
	{
		$bc = getbcdetails($bcid);
		$rres = mysql_query("SELECT * from bc_rates where rateid = '".$bc['rateid']."'");
		$rate = mysql_fetch_array($rres);
		return $rate;
	}
function getagentnames()
	{
		$results[0] = 'Unassigned';
		$res = mysql_query("SELECT userid, afirst, alast from memberdetails ORDER BY alast, afirst");
		while ($row = mysql_fetch_array($res))
			{
				$results[$row['userid']] = $row['alast'].", ".$row['afirst'];
			}
		return $results;
	}
function getbcdetails($bcid)
	{
		$res = mysql_query("SELECT * from bc_clients where bcid = '$bcid'");
		$row = mysql_fetch_array($res);
		return $row;
	}

function dayadd($date)
	{
		$d = strtotime($date);
		$d = $d + 86400;
		return date("Y-m-d",$d);
	}
function getprojectlist()
	{
		$pres = mysql_query("SELECT * from projects");
		while ($row = mysql_fetch_array($pres))
			{
				$projectlist[$row['projectid']] = $row;
			}
		return $projectlist;
	}
function getdispooptions($pid)
	{
		$dres = mysql_query("SELECT statusname from statuses where projectid = '0' or projectid = '$pid' ");
		while ($drow = mysql_fetch_array($dres))
			{
				$options .= '<option value="'.$drow['statusname'].'">'.$drow['statusname'].'</option>';
			}
		return $options;
	}
function graph3d_single($xarr,$yarr,$file,$xtitle, $ytitle, $maintitle,$shownames = 1)
	{
		$serv = substr($_SERVER['HTTP_HOST'],6,3);
		$file = $serv.$file;
		$xml = fopen($file,"w");
		
		$data = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
		<graph caption="'.$maintitle.'" xAxisName="'.$xtitle.'" yAxisName="'.$ytitle.'"  shownames="'.$shownames.'" decimalPrecision="0" formatNumberScale="0">';
		$c = 0;
		foreach($xarr as $x)
			{
				$data.= '<set name="'.$x.'" value="'.$yarr[$c].'" color="'.rand_colorCode().'" />';
				$c++;
			}
		$data.= "</graph>";
		fwrite($xml,$data);
		fclose($xml);
	}
function rand_colorCode(){
$r = dechex(mt_rand(0,255)); // generate the red component
$g = dechex(mt_rand(0,255)); // generate the green component
$b = dechex(mt_rand(0,255)); // generate the blue component
$rgb = $r.$g.$b;
if($r == $g && $g == $b){
$rgb = substr($rgb,0,3); // shorter version
}
return $rgb;
}
function flashgraph($type,$xmlfile,$width,$height)
	{
		$serv = substr($_SERVER['HTTP_HOST'],6,3);
		$xmlfile = $serv.$xmlfile;
		$disp = '<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="'.$width.'" height="'.$height.'" id="'.$type.'" >
         <param name="movie" value="graphgen/fcf/charts/FCF_Column3D.swf" />
         <param name="FlashVars" value="&dataURL=='.$xmlfile.'&chartWidth='.$width.'&chartHeight='.$height.'">
         <param name="quality" value="high" />
         <embed src="../graphgen/fcf/Charts/FCF_'.$type.'.swf" flashVars="&dataURL='.$xmlfile.'&chartWidth='.$width.'&chartHeight='.$height.'" quality="high" width="'.$width.'" height="'.$height.'" name="'.$type.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
      </object>';
	  	return $disp;
	}
function getlists($projectid,$bcid)
	{
		$projects = getprojects($bcid,false);
		$q = "SELECT listid from lists where projects ";
		if ($projectid == 'all')
			{
				$q.= " in (".$projects['sql'].") ";
			}
		else {
				$q.= " = '$projectid' ";
		}
		$listres = mysql_query($q);
		$ct = 0;
		while ($list = mysql_fetch_assoc($listres))
			{
				$lists[] = $list;
				if ($ct > 0) $listsql .= ",";
				$listsql .= "'".$list['listid']."'";
				$listoptions .= '<option value="'.$list['listid'].'">'.$list['listid'].'</option>';
				$ct++;
			}
		$ret['sql'] = $listsql;
		$ret['arr'] = $lists;
		$ret['options'] = $listoptions;
		return $ret;
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
function getchildpages($parentid,$spacer = NULL)
	{
		global $scriptid;
		$children = getbyparams('scripts',"parentid = '$parentid'",'scriptid');
		if (count($children) == 0)
			{
				return NULL;
			}
		else {
			$spacer = $spacer."-";
			foreach ($children as $child)
				{
					if ($scriptid == $child['scriptid'])
						{
							$ret .= '<li>'.$spacer.$child['scriptname'].'</li>';
						}
					else $ret .= '<li>'.$spacer.'<a href="#" onclick="editscriptid(\''.$child['scriptid'].'\')">'.$child['scriptname'].'</a></li>';
					$ret .= getchildpages($child['scriptid'],$spacer);
				
				}
	
			return $ret;
		}
	}
function getparent($scriptid)
	{
		$res = mysql_query("SELECT * from scripts where scriptid = '$scriptid'");
		$row = mysql_fetch_assoc($res);
		if ($row['parentid'] > 0)
			{
				$ret = getparent($row['parentid']);
			}
		else {
			$ret = $scriptid;
		}
		return $ret;
	}
function getdispofromsystem($systemdisposition)
{
    if ($systemdisposition == 'BUSY')
            {
                $ret = 'Busy';
            }
            elseif ($systemdisposition == 'CANCEL')
            {
                $ret = 'No Answer';
            }
            
            elseif ($systemdisposition == 'CHANUNAVAIL' || $systemdisposition == 'CONGESTION')
            {
                $ret = 'CallFailed';
            }
            else 
                {
                $ret = 'No Disposition';
            }
    return $ret;
}
function creds($num)
{
    return number_format($num,2,".","");
}
function sanitize_output($buffer) {

    $search = array(
        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
        '/(\s)+/s'       // shorten multiple whitespace sequences
    );

    $replace = array(
        '>',
        '<',
        '\\1'
    );

    $buffer = preg_replace($search, $replace, $buffer);

    return $buffer;
}
function filesizeformat($size) {
 
    // Adapted from: http://www.php.net/manual/en/function.filesize.php
 
    $mod = 1024;
 
    $units = explode(' ','B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
 
    return round($size, 2) . ' ' . $units[$i];
}
?>