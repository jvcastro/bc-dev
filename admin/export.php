<?php
         
// date_default_timezone_set("Australia/Sydney");
		$type = $_REQUEST['type'];
		$id = $_REQUEST['id'];
ini_set("display_errors",'on');
error_reporting(1);
//echo "Debug";    
if ($type == 'exclusion')
{
    $res = mysql_query("SELECT phone from lists_exclusion_data where exclusionid = $id");
    while ($row = mysql_fetch_row($res))
    {
        $disp .= $row[0]."\r\n";
    }
    header("Content-type: application/x-msdownload");
    header("Content-Disposition: attachment; filename=exclusion_".$id.".csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $disp;
    exit;
}
if ($type == 'list' || $type=='search')
	{
                $exportfields = array(
                    'cname',
                    'cfname',
                    'clname',
                    'company',
                    'title',
                    'positiontitle',
                    'phone',
                    'altphone',
                    'mobile',
                    'industry',
                    'sic',
                    'email',
                    'address1',
                    'address2',
                    'zip',
                    'suburb',
                    'city',
                    'state',
                    'country',
                    'comments',
                    'epoch_timeofcall',
                    'dispo',
                    'epoch_callable'
                );
               // $beforecords = number_format(memory_get_peak_usage(true),2);
                if ($type == 'list')
                {
		$records = lists::listrecords($id, $exportfields);
                $list = lists::findbyLid($id);
                $projectname = projects::getprojectname($list['projects']);
                $filename = str_replace(" ","_",$list['listid']).".xls";
                }
                if ($type == 'search')
                {
                    $projectnames = projects::projectnames($bcid);
                    $records = lists::searchrecords($bcid,$projectid,$disposition,$agentid,$start,$end,$exportfields);
                    $filename = substr(md5(time()),-5);
                    $filename .= ".xls";
                }
                $headers = array();
                //$rows = array();
                //$beforeassign = number_format(memory_get_peak_usage(true),2);
                $cdata = $records['cdata'];
                $cdheaders = array();
                $sdata = $records['sdata'];
                //var_dump($sdata);
                //$afterassign = number_format(memory_get_peak_usage(true),2);
                $sheaders = array();
                $headers["campaign"] = "Campaign";
                $headers["agent"] = "Agent";
                //$exportfields[] = "DateSet";
                foreach ($exportfields as $hd)
                {
                    $headers[$hd] = $hd;
                   // if ($hd == 'epoch_timeofcall') $headers[$hd] = 'timeofcall';
                }
                /*foreach ($records["records"] as $record)
                {
                    $rows[$record['leadid']]["Campaign"] = $projectname;
                    foreach ($exportfields as $field)
                    {                     
                           $rows[$record['leadid']][$field]= $record[$field]; 
                    }
                }*/
                $rows =& $records["records"];
                //$afterecords = number_format(memory_get_peak_usage(true),2);
                //unset($records["records"]);
                $allmembers = members::getallmemberdetails();
                foreach ($rows as $r)
                {
                    if ($type == 'search')
                    {
                        $rows[$r['leadid']]['Campaign'] = $projectnames[$r['projectid']];
                        $rows[$r['leadid']]['Agent'] = $r['assigned'] == 0 ? '':$allmembers[$r['assigned']]['afirst'] . ' '.  $allmembers[$r['assigned']]['alast'];
                    }
                    else {
                        $rows[$r['leadid']]['Campaign'] = $projectname;
                    }
                }
                foreach ($cdata as $leadid=>$customfields)
                {
                    $customdata = json_decode($customfields['customfields'],true);
                    //unset($cdata[$record['leadid']]["customfields"]);
                    foreach ($customdata as $key=>$value)
                    {
                        $key = "cd_".$key;
                        
                        $rows[$leadid][$key] = $value;
                        $cdheaders[$key] = $key;
                    }
                    unset($customdata);
                }
                unset($cdata);
                unset($records['cdata']);
                foreach ($sdata as $leadid=>$sc)
                {
                    $scriptdata = json_decode($sc["scriptjson"],true);
                    
                    foreach ($scriptdata as $key=>$value)
                    {
                        $key = "sc_".$key;
                        $rows[$leadid][$key] = $value;
                        $sheaders[$key] = $key;
                    }
                    unset($scriptdata);
                }
                unset($sdata);
                //var_dump($records['ndata']);
                foreach ($records['ndata'] as $leadid=>$nc)
                {
                    $notedata = json_decode($nc["note"],true);
                    $key = 'notes';
                    $noteheaders[$key] = $key;
                    foreach ($notedata as $nd)
                    {
                        
                        $rows[$leadid][$key] .= '<br>'.$nd['user']."(".$nd['timestamp']."):".$nd['message'];
                        
                    }
                    unset($notedata);
                }
                //unset($records['ndata']);
                foreach ($cdheaders as $cbh)
                {
                    $headers[$cbh] = $cbh;
                }
                foreach ($sheaders as $cbh)
                {
                    $headers[$cbh] = $cbh;
                }
                foreach ($noteheaders as $cbh)
                {
                    $headers[$cbh] = $cbh;
                }
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=".$filename);
		header("Pragma: no-cache");
		header("Expires: 0");
                //$beforetable = number_format(memory_get_peak_usage(true),2);
                tablegen2(&$headers, &$rows);
                /*echo "Peak:".number_format(memory_get_peak_usage(true),2);
		echo "<br>Before Records: ".$beforecords;
                echo "<br>Before assign Records: $beforeassign";
                echo "<br>After assign Records: $afterassign";
                echo "<br>After Records: $afterecords";
                echo "<br>Before Table: $beforetable";*/
                exit;
	}
?>
