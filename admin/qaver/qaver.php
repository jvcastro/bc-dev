<?php
function gettimefromname($name)
{
    $parts = explode("_",$name);
    return $parts[2];
}
$viewfields = array( // fieldname=>array(label,checked by default)
    'epoch_timeofcall'=> array('Date',1),
    'status'=>array('QA status',1),
    'dispo'=>array('Disposition',1),
    'assigned'=>array('Agent',1),
    'phone'=>array('Phone',1),
    'altphone'=>array('Alt Phone',0),
    'mobile'=>array('Mobile',0),
    'cname'=>array('Name',1),
    'cfname'=>array('Firstname',0),
    'clname'=>array('Lastname',0),
    'company'=>array('Company',1),
    'email'=>array('Email',1),
    'address1'=>array('Address1',0),
    'address2'=>array('Address2',0),
    'suburb'=>array('Suburb',0),
    'city'=>array('City',1),
    'state'=>array('State',0),
    'country'=>array('Country',0),
    'zip'=>array('Postcode',0),
    'epoch_callable'=>array('Date Set',0)
);
function clearnum($num)
	{
		$num = str_replace(" ","",$num);
		$num = str_replace("-","",$num);
		$num = str_replace("(","",$num);
		$num = str_replace(")","",$num);
		return $num;
	}
function getagentids($bcid)
	{
		$res = mysql_query("SELECT * from members a CROSS JOIN memberdetails b ON a.userid=b.userid where a.bcid = $bcid AND b.afirst <> '' AND b.alast <> '' AND a.usertype='user' ORDER BY b.alast, b.afirst, a.userlogin");
		while ($row = mysql_fetch_assoc($res))
			{
				$r[$row['userid']] = $row;
			}
		return $r;
	}
	
function dispolist($projectid)
	{

		if ($projectid != 'all') 
			$ps = "in (0,$projectid)";
		else  
			$ps = "in (SELECT projectid FROM projects where bcid=". $_SESSION['bcid'] ." UNION SELECT 0)";

		$ret = array();

		$res = mysql_query("SELECT * FROM statuses WHERE projectid $ps GROUP BY statusname");
		$ret[]['statusname'] = "all";
		while ($row = mysql_fetch_assoc($res))
		{
			$ret[] = $row;
		}
		return $ret;
	}
function projectlist($bcid)
	{
		$res = mysql_query("SELECT * from projects where bcid = '$bcid' ORDER BY projectname");
		while ($row = mysql_fetch_assoc($res))
			{
				$projects[$row['projectid']] = $row;
				$projects[$row['projectid']]['id'] = $row['projectid'];
			}
		return $projects;
	}
function projectlistbyclientid($cid)
	{
		$res = mysql_query("SELECT * from projects where clientid= '$cid'");
		while ($row = mysql_fetch_assoc($res))
			{
				$projects[$row['projectid']] = $row;
				$projects[$row['projectid']]['id'] = $row['projectid'];
			}
		return $projects;
	}
function listrecords($pid, $dispo, $start, $end, $datetype = 'calldate')
	{
		global $bcid;
		global $debugm;
                if ($datetype == 'calldate')
                {
                    $datefield = 'epoch_timeofcall';
                }
                if ($datetype == 'dateset')
                {
                    $datefield = 'epoch_callable';
                }
		if (is_array($dispo))
			{
				foreach ($dispo as $disp)
					{
						$dispo_arr[] = "'".$disp."'";
					}
				$dispostr = implode(",",$dispo_arr);
			}
		else
			{
				
				$dispo_str = "'".$dispo."'";
			}
		if ($dispo == 'all')
			{
				$dispo_q = '';
			}
		else $dispo_q = " and dispo in ($dispo_str) ";
		if ($pid == 'all')
			{
				$projects = projectlist($bcid);
				foreach ($projects as $project)
					{
						$p[] = $project['id'];
					}
				$inpid = implode(",",$p);
				$query = "SELECT * from leads_done where projectid in ($inpid) $dispo_q and $datefield >= '".strtotime($start)."' and $datefield <= '".strtotime($end." 23:59:59")."' limit 50000";
				$res = mysql_query($query);
			}
		else {
		$query = "SELECT * from leads_done where projectid = '$pid' $dispo_q and $datefield >= '".strtotime($start)."' and 
                    $datefield <= '".strtotime($end." 23:59:59")."' limit 50000";
		$res = mysql_query($query);
		
		
		}
		$debugm = $query;
		while ($row = mysql_fetch_assoc($res))
			{
				$ret[$row['leadid']] = $row;
			}
		return $ret;
	}
function getrecord($leadid)
	{
		$res = mysql_query("SELECT * from leads_done where leadid = '$leadid'");
		$lead = mysql_fetch_assoc($res);
		
		
		$resdate = mysql_query("SELECT * from dateandtime where leadid = '$leadid'");
		$lead_date = mysql_fetch_assoc($resdate);
		
		$resc = mysql_query("SELECT scriptjson from scriptdata where leadid = '$leadid'");
		$crow = mysql_fetch_assoc($resc);
		$scriptdata = json_decode($crow['scriptjson'],true);
                $rescf = mysql_query("SELECT * from leads_custom_fields where leadid = '$leadid'");
                $cfrow = mysql_fetch_assoc($rescf);
                $customdata = json_decode($cfrow['customfields'],true);
		$recordings = findrecording($leadid);
		$record['info'] = $lead;
		if ($customdata) $record['customdata'] = $customdata;
                if ($scriptdata) $record['scriptdata'] = $scriptdata;
		$record['recordings'] = $recordings;
		$record['appdate'] = $lead_date;
		return $record;
	
	}
function parsescriptdata($scriptdata)
	{
		$raw = explode("</",$scriptdata);
		$ct = 0;
		$re = array();
		if (count($raw) < 2)
			{
				return false;
			}
		else {
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
				$key = $label;
				$re[$key] = $value;
				$ct++;
			}
		return $re;
		}
	}
function pushrecord($file)
	{
		$comm = "find /var/spool/asterisk/recordings/ -type f -name \"".$file."\" -exec echo {} \;";
		exec($comm,$list);
		header('Content-type: application/mp3');
		header('Content-Disposition: attachment; filename="'.$file.'"');
		readfile($list[0]);
	}
function findrecording($leadid)
	{
		$comm2 = "find /var/spool/asterisk/recordings/ -type f -name \"".$leadid."_*\" -exec echo {} \;";
		exec($comm2,$list);
		$comm3 = "find /var/spool/asterisk/recordings/ -type f -name \"".$leadid."_*\" -printf '%s_%f\n'";
		exec($comm3,$list2);
		if ($list2)
			{
				$x = count($list2);
				$y =0;
				while ($y < $x)
					{
						$recfile = $list2[$y];
						$recparts = explode("_",$recfile);
						$size = $recparts[0];
						settype($size,"integer");
						$epoch = $recparts[3];
						$callid = substr($recparts[4],0,-4);
						$dateofcall =date("Y-m-d",$epoch);
						$timeofcall =date("h:i:s A",$epoch);
						$length1 = $size / 16000;
						$length = round($length1,2);
						$recordings[$y]['callid'] = $callid;
						$recordings[$y]['length'] = $length;
						$recordings[$y]['dateofcall'] = $dateofcall;
						$recordings[$y]['timeofcall'] = $timeofcall;
						$recordings[$y]['file'] = substr($list[$y],31);
						$recordings[$y]['file_location'] = $list[$y];
						$y++; 
					}
				return $recordings;
			}
		else return false;
	}