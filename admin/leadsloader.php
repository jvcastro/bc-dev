<?php
session_start();
ini_set("display_errors",'off');
error_reporting(E_ALL ^ E_DEPRECATED);
date_default_timezone_set($_SESSION['timezone']);
include "../dbconnect.php";
include "phpfunctions.php";
require_once '../classes/classes.php';
$bcid = getbcid();
$act = $_REQUEST['act'];
$iswin = $_REQUEST['iswin'];
$target_path = "C:\\xampp\\htdocs\\proActiv\\leads\\";
$target_path = "./leads/";
$duplicate = 0;
$uleads = 0;
$dupcheck = $_REQUEST['dupcheck'];
if ($act == 'parsemap')
	{
	extract($_POST);
	//$projres = mysql_query("SELECT projectid from projects where projectname ='$project'");
	//$projrow = mysql_fetch_array($projres);
	$projid = $_POST['project'];
        $projects = new projects();
        $project = $projects->findById($projid);
        $cfields = json_decode($project['customfields'],true);
        $cf = array();
        foreach ($cfields as $key=>$value)
        {
            $cf[] = $key;
        }
	$rower = $_POST;
	$csv = fopen($targetp,"r");
	$exclures = mysql_query("SELECT id,phone from lists_exclusion_data where projectid = $projid");
        while ($exrow = mysql_fetch_row($exclures))
        {
            $exclusion[$exrow['id']] = $exrow['phone'];
        }
       $phonenumbers = leadsloadersettings::phonenumbers();
       $dups = leadsloadersettings::duplicatecheck();
       $duplist = new duplicatecheck();
       $duplist->projectid = $projid;
       $duplist->listid = $listid;
       $duplist->bcid = $bcid;
       $duplist->preprop($dupcheck);
       $duplicates = array();
       $excluded = 0;
	while ($data = fgetcsv($csv,1000,","))
		{
		$n = 0;
		$tdata = array('listid'=>$listid);
                $cdata = array();
		$dtct = count($data);
		//$tquery = "INSERT into leads_raw (leadtype, listid, $gquery) values ('$leadtype', '$listid', ";
		while ($n < $dtct)
			{
                        $assfield = $rower['field'.$n];
			if ($assfield != 'nomap' && !in_array($assfield,$cf))
				{
				if (in_array($assfield,$phonenumbers))
					{
					$data[$n] = str_replace(" ","",$data[$n]);
					$data[$n] = str_replace("(","",$data[$n]);
					$data[$n] = str_replace(")","",$data[$n]);
					$data[$n] = str_replace("-","",$data[$n]);
					$data[$n]  = ereg_replace( '[^0-9]+', '', $data[$n]);
					}
                                  $tdata[$rower['field'.$n]] = $data[$n];  
				}
                         elseif (in_array($assfield,$cf))
                         {
                             $cdata[$assfield]= $data[$n];
                         }
			$n++;
			}
               //check exclusion
               //
            if (in_array($tdata['phone'], $exclusion))
            {
                $excluded++;
            }
            else 
            {
                //check duplicate in list
		if ($dupcheck == 'nocheck')
			{
				$genid = leads::add($tdata);
                                if (count($cdata) > 0)
                                {
                                customdata::add($genid,$cdata);
                                }
				$uleads++;	
			}
		else
		{
                    $isdup = false;
                    foreach ($dups as $checkthis)
                    {
                        if ($duplist->dupcheck($checkthis, $tdata[$checkthis]))
                            {
                                $isdup = true;
                                $duplicates[] = $tdata;
                                $duplicate++;
                            }
                       else {
                           $duplist->addin($checkthis,$tdata[$checkthis]);
                       }
                    }
		//end duplicate check
                    if (!$isdup) 
			{
			$genid = leads::add($tdata);
                        if (count($cdata) > 0)
                        {
                        customdata::add($genid,$cdata);
                        }
			$uleads++;
			}
		}
            }
	}
        //$duplicate = count($duplicates);
	echo "Loaded $uleads records<br>";
        echo "$excluded records excluded<br>";
        $f = $listid."_dups_".time().".csv";
        $dupfile = "leads/".$listid."_dups_".time().".csv";
        $reso = fopen($dupfile,"a");
        foreach ($duplicates as $d)
        {
        fputcsv($reso, $d);
        }
        fclose($reso);
        if ($dupcheck == 'nocheck')
		{
			echo "Duplicates were not checked <br>";
		}
	else echo $duplicate." duplicates found <br> Click <a href=\"leads/csvdownloader.php?f=$f\" target=\"_blank\">here to download duplicates</a>";
	mysql_query("update lists set dupscount = dupscount + $duplicate, listcount = listcount + $uleads where listid = '$listid'");
	exit;
	}
function parsecsv($path, $targetp)
	{
	//echo $path.'<br>';
	$leadtype = $_REQUEST['leadtype'];
	$csv = fopen($targetp,"r");
	$data = fgetcsv($csv,0,",");
	$z = 0;
	$ct = count($data);
	$li = $_REQUEST['listid'];
	$pr = $_REQUEST['projects'];
        $projects = new projects();
        $project = $projects->findById($pr);
        $cfields = json_decode($project['customfields'],true);
	$dupcheck = $_REQUEST['dupcheck'];
	$field[$z] = $data[$z];
	$iswin = $_REQUEST['iswin'];
	echo '<div class="entryform" style="width:300px; height:350px">
            <title>Field Mapping</title>
            <form name="mapping" id="mapping" method="post" action="'.$_SERVER['PHP_SELF'].'">
	<input type="hidden" name="act" value="parsemap"><input type="hidden" name="leadtype" value="'.$leadtype.'">
	<input type="hidden" name="dupcheck" value="'.$dupcheck.'">
	<input type="hidden" name="targetp" value="'.$targetp.'">
	<input type="hidden" name="listid" value="'.$li.'">
	<input type="hidden" name="project" value="'.$pr.'">';
	if ($iswin == '1') echo '<input type=hidden name=iswin value="1">';
	echo '
        <div id="respmessage"></div>    
        <table id="mappingtable" width="100%"><tr><td class="center-title tableheader"><h3>CSV Column</h3></td><td class="center-title tableheader"><h3>Field</h3></td></tr>';
	$fldres = mysql_query("SELECT cname, cfname, clname, title, company, address1, address2, suburb, city, state, country, zip, phone, altphone, comments, industry, sic, email, positiontitle, mobile,dispo from leads_raw limit 1");
	$fldct = mysql_num_fields($fldres);
	while ($z < $ct)
		{
		$y = 0;
		echo "<tr><td>".$data[$z].":</td><td><select name=\"field$z\"><option value=\"nomap\">No Mapping</option>";
		while ($y < $fldct)
			{
			$fld = mysql_field_name($fldres, $y);
			if ($fld == 'zip')
			{
			echo '<option value="'.$fld.'">Postcode</option>';
			}
			elseif ($fld == 'cname')
			{
			echo '<option value="'.$fld.'">Name</option>';
			}
			elseif ($fld == 'clname')
			{
			echo '<option value="'.$fld.'">LastName</option>';
			}
			elseif ($fld == 'cfname')
			{
			echo '<option value="'.$fld.'">FirstName</option>';
			}
			elseif ($fld == 'positiontitle')
			{
			echo '<option value="'.$fld.'">Position Title</option>';
			}
			else {
			echo "<option value=\"".$fld."\">".ucfirst($fld)."</option>";
			}
			$y++;
			}
                foreach ($cfields as $field=>$value)
                {
                    echo "<option value=\"".$field."\">".ucfirst($value)."</option>";
                }
		echo"</select></td></tr>";
		$z++;
		}
	echo "
        </table>    
        <input type=\"hidden\" value=\"".$ct."\" name=\"fieldcount\">
        </form><br>
        <div class=buttons style=\"position:relative\">
        <input type=\"button\" value=\"Next\" onclick=\"submitmap()\">
        </div>
        </div>";
	}
if ($act == 'upload')
	{
	$myfile = basename( $_FILES['csvfile']['name']);
	$target_path = $target_path . basename( $_FILES['csvfile']['name']); 
	$r = mysql_query("SELECT * from lists where listid = '".$_REQUEST['listid']."'");
	$ro = mysql_num_rows($r);
        $lrow = mysql_fetch_assoc($r);
	$li = $_REQUEST['listid'];
	$ld = $_REQUEST['listdescription'];
	$pr = $_REQUEST['projects'];
	if ($ro ==0)
		{
			$datenow = date("Y-m-d");
			mysql_query("insert into lists set listid = '$li', listdescription = '$ld', projects = '$pr', datecreated = '$datenow', bcid = '$bcid'");
		}
         else {
             if ($lrow['bcid'] != $bcid)
             {
                echo "The specifiedListId cannot be used in this account, please use a different ListId";
                exit;
             }
         }
	if (strlen($myfile) > 1)
	{
	if(move_uploaded_file($_FILES['csvfile']['tmp_name'], $target_path)) 
		{
    	parsecsv($myfile, $target_path);
		} 	
	else{
    	echo "There was an error uploading the file, please try again!";
		}
	}
	else $me="donen();";
	}
if ($act == 'exclusionupload' || $act == 'listupdatefile')
	{
	$myfile = basename( $_FILES['csvfile']['name']);
	$target_path = $target_path . basename( $_FILES['csvfile']['name']);
//	if ($_REQUEST['listid'] <> '')
//	{
        $r = mysql_query("SELECT * from lists_exclusion where exclusion_name = '".$_REQUEST['listid']."'");
		$ro = mysql_num_rows($r);
        $lrow = mysql_fetch_assoc($r);
		$li = $_REQUEST['listid'];
		$pr = $_REQUEST['projects'];
        $listids_arr = lists::findbyProjectId($pr, TRUE, false);
        $listids = array();
        foreach ($listids_arr as $lis)
        {
            $listids[$lis] = "'".$lis."'";
        }
        $aglist = 'agentgenerated'.$pr;
        $listids[$aglist] = "'".$aglist."'";
        $lstr = implode(",",$listids);
		if ($ro ==0 && $act == 'exclusionupload')
		{
			mysql_query("insert into lists_exclusion set exclusion_name = '$li', projectid = '$pr', bcid = '$bcid', date_created = '".time()."'") or die(mysql_error());
            $eid = mysql_insert_id();
		}
		else 
		{
			if ($ro != 0 && $lrow['bcid'] != $bcid && $_REQUEST['listid'] <> '')
			{
				echo "The specifiedListId cannot be used in this account, please use a different ListId";
				// print_r($_REQUEST);
				exit;
			}
			if ($ro !=0) 
				$eid = $lrow['id'];
		}
//	}
	if (strlen($myfile) > 1)
	{
	if(move_uploaded_file($_FILES['csvfile']['tmp_name'], $target_path)) 
        {
            $csv = fopen($target_path,"r");
            $ct = 0;
            $ctaff = 0;
            while (($data = fgetcsv($csv, 1000, ",")) !== FALSE)
            {
                if ($act == 'exclusionupload')
                {
                mysql_query("INSERT into lists_exclusion_data set exclusionid =$eid, projectid = $pr, phone= '".$data[0]."'") or die(mysql_error());
                $ex = lists::exclude($pr, $data[0], $lstr);
                $ctaff = $ctaff + $ex['affected'];
                }
                if ($act == 'listupdatefile')
                {
                    $aff = lists::updatebyphone($pr, $data[0], $data[1], $lstr);
                    $ctaff = $ctaff + $aff;
                    // print_r($data);
                    // print_r($listids_arr);
                    //var_dump($data);
                }
                $ct++;
            }
           if ($act == 'exclusionupload') {
               echo "Added $ct records into exclusion list. ";
               echo $ctaff." records set to DoNotCall.";
           }
           if ($act == 'listupdatefile') {
               echo "Uploaded $ct updated. ";
               echo $ctaff." records affected";
               mysql_query("INSERT into disposition_update_history set date_epoch = '".time()."', bcid = $bcid, projectid = $pr, records_total = $ct, records_updated = $ctaff, filename = '$target_path'");
           }
        } 	
	else{
    	echo "There was an error uploading the file, please try again!";
		}
	}
	}	
?>