<?php
$agents = getagentnames();
if ($act == 'getstatusoption')
{
    $res = mysql_query("SELECT options from statuses where statusid = '".$_REQUEST['statusid']."' ");
    $row = mysql_fetch_assoc($res);
    if (strlen($row['options']) < 1) echo 'none';
    else echo $row['options'];
    exit;
}
if ($act == 'selectslot')
{
    $leadid = $_REQUEST['leadid'];
    $slotid = $_REQUEST['slotid'];
    $opt = $_REQUEST['opt'];
    $now = time();
    if ($opt == 'move')
    {
        
        mysql_query("update client_contact_slots set leadid = '', taken = 0 where leadid = $leadid and slotstart > $now");
    }
    mysql_query("update client_contact_slots set leadid = $leadid, taken= 1 where slotid = $slotid");
    mysql_query("update leads_done set epoch_callable = $now where leadid = $leadid");
    mysql_query("update leads_raw set epoch_callable = $now where leadid = $leadid");
    exit;
}
if ($act == 'cslots')
{
    //$ispres = mysql_query("select * from projects where projectid = '$pid';");
    //$project = mysql_fetch_assoc($ispres);
    $clientid = $_REQUEST['clientid'];
    $leadid = $_REQUEST['leadid'];
    $headers[] = "Contact";
    $headers[] = "Date";
    $headers[] = "Start";
    $headers[] = "End";
    $headers[] = "Status";
    $ctr = 0;
    $cslots = getallbyparams("client_contact_slots","where clientid = '".$clientid."' and slotstart > ".time()." order by slotstart ASC");
    if (count($cslots) > 0) $doslots = true;
    $ccontacts = get("client_contacts","client_contactid");
    foreach ($cslots as $cslot)
            {

                    $pdate = date("Y-m-d H:i:s",$cslot['slotstart']);
                    $rows[$ctr]['contact'] = $ccontacts[$cslot['client_contactid']]['firstname']. ' '.$ccontacts[$cslot['client_contactid']]['lastname'];
                    $rows[$ctr]['date'] = date("Y-m-d",$cslot['slotstart']);
                    $rows[$ctr]['start'] = date("H:i:s",$cslot['slotstart']);
                    $rows[$ctr]['end'] = date("H:i:s",$cslot['slotend']);
                    $status = '<a href="#" onclick="selectslot(\''.$leadid.'\',\''.$cslot['slotid'].'\',\'\')">Add New<br><a href="#" onclick="selectslot(\''.$leadid.'\',\''.$cslot['slotid'].'\',\'move\')">Move - Cancel Previous</a>';
                    if ($cslot['taken'] > 0)
                            {
                                    $status = '<span style="color:red">taken</span>';
                            }
                    $rows[$ctr]['status'] = ucfirst($status);
                    $ctr++;
            }
    echo '<a href="#" onClick="usecal()">Use Calendar</a>';
    echo tablegen($headers,$rows,600,'','datatabslot');
    exit;
}
if ($act == 'dialer')
{
    $sub = $_REQUEST['sub'];
    if ($_SESSION['adminext'] < 1)
    {
        echo "setext";
        exit;
    }
    
    $dialer = new dialer($_SESSION['adminext'],$_REQUEST['leadid']);
    $dialer->$sub();
    exit;
}
if ($act == 'bulkstatusupdate')
{
    $bcids = $_REQUEST['bcids'];
    $status = $_REQUEST['status'];
    $subaction = '';
    if ($status == 'assignto')
    {
        $status = 'approved';
        $subaction = 'assignto';
        $ato = $_REQUEST['contactid'];
    }
    foreach ($bcids as $dib)
    {
        mysql_query("UPDATE leads_done set status = '$status' where leadid = '$dib'");
        if ($status == 'approved')
        {
            $res = mysql_query("SELECT leadid from client_contact_leads where leadid = $dib");
             if ($subaction == 'assignto') {
                    if (mysql_num_rows($res) == 0){
                        mysql_query("insert into client_contact_leads set leadid = $dib, client_contactid = $ato, client_disposition = 1");
                        }
                    else {
                        mysql_query("update client_contact_leads set client_contactid = $ato where leadid = $dib");
                    }
            }
            else {
                if (mysql_num_rows($res) == 0){
                mysql_query("insert into client_contact_leads set leadid = $dib, client_disposition = 1");
                }
               
            }
        }
    }
    exit;
}
if ($act == 'getclientcontacts')
{
    $pid = $_REQUEST['pid'];
    $client = projects::projectclient($bcid, $pid);
    $contacts = clients::getclientcontacts($client['clientid']);
    foreach ($contacts as $contact)
    {
        $coptions .= '<option value="'.$contact['client_contactid'].'">'.$contact['firstname'].' '.$contact['lastname'].'</option>';
    }
    ?>
Client Contact:<select name="assigntoclientcontact" id="assigntoclientcontact" onchange="doassignto()"><option></option>
    <?=$coptions;?>
</select>
<?php
    exit;
}
if ($act == 'savesf')
{
    extract($_POST);
    $res = mysql_query("SELECT * from scriptdata where leadid = '$leadid'");
    $row = mysql_fetch_assoc($res);
    $cf = json_decode($row['scriptjson'],true);
    if ($cf && count($row) > 0)
    {
        foreach ($cf as $key=>$val)
        {
            if ($key == $field) $cf[$key] = $value;
        }
        mysql_query("UPDATE scriptdata set scriptjson = '".json_encode($cf)."' where leadid = '$leadid'");
    }
    else {
        $cf = array();
        $cf[$field] = $value;
        if (count($row) > 0)
        {
            mysql_query("UPDATE scriptdata set scriptjson = '".json_encode($cf)."' where leadid = '$leadid'");
            
        }
        else mysql_query("INSERT into scriptdata set scriptjson = '".json_encode($cf)."', leadid = '$leadid'");
    }
    exit;
}
if ($act == 'savecf')
{
    extract($_POST);
    $res = mysql_query("SELECT * from leads_custom_fields where leadid = '$leadid'");
    $row = mysql_fetch_assoc($res);
    $cf = json_decode($row['customfields'],true);
    if ($cf && count($row) > 0)
    {
        foreach ($cf as $key=>$val)
        {
            if ($key == $field) $cf[$key] = $value;
        }
        mysql_query("UPDATE leads_custom_fields set customfields = '".json_encode($cf)."' where leadid = '$leadid'");
    }
    else {
        $cf = array();
        $cf[$field] = $value;
        if (count($row) > 0)
        {
            mysql_query("UPDATE leads_custom_fields set customfields = '".json_encode($cf)."' where leadid = '$leadid'");
            
        }
        else mysql_query("INSERT into leads_custom_fields set customfields = '".json_encode($cf)."', leadid = '$leadid'");
    }
    exit;
}
if ($act =='dl')
	{
		pushrecord($_REQUEST['file']);
		exit();
	}
if ($act == 'savelead')
	{
            $dib = $_REQUEST['leadid'];
            $res = mysql_query("SELECT leadid from client_contact_leads where leadid = $dib");
                    if (mysql_num_rows($res) == 0){
                        mysql_query("insert into client_contact_leads set leadid = $dib, client_contactid = $ato, client_disposition = 1");
                        }
                    else {
                        mysql_query("update client_contact_leads set client_contactid = $ato where leadid = $dib");
                    }
		savelead($_REQUEST['leadid']);
		exit();
	}
if ($act =='updatedispolist')
	{
		if ($_REQUEST['projectid'] != 'all')
			{
			$d = dispolist($_REQUEST['projectid']);
			}
		else {
			$d = dispolist('all');
		}
		$disp = createdropdown($d,"statusname","statusname");
		echo $disp;
		exit();
	}
if ($act == 'export')
{
    extract($_POST);
    $_REQUEST['type'] = 'search';
    include "../export.php";
}
if ($act == 'search')
	{
		extract($_POST);
                $vfs = $_REQUEST['viewfields'];
                foreach ($viewfields as $key=>$vf)
                {
                    $viewfields[$key][1] = 0;
                }
                foreach ($vfs as $vf)
                {
                    $viewfields[$vf][1] = 1;
                }
		//var_dump($_REQUEST);
		//$projectid = $_REQUEST['projectid'];
                $projects = projectlist($bcid);
                if ($projectid == 'all')
			{
				foreach ($projects as $project)
					{
						$p[] = $project['id'];
					}
				$inpid = implode(",",$p);
			}
                else $inpid = $projectid;
                $recres = mysql_query("SELECT * from recordinglog where projectid in ($inpid)");
                while($recrow = mysql_fetch_assoc($recres))
                {
                    $rt = explode("_",$recrow['filename']);
                    $reclid = $rt[0];
                    $recordinglog[$reclid] = $recordinglog[$reclid] ? $recordinglog[$reclid] + 1:1;
                    $recordingproject[$reclid] = $recrow['projectid'];
                }
		$results = listrecords($projectid,$disposition,$start,$end,$datetype);
		
                if ($act != 'export')
                {
                    $headers['0'] = '<input type="checkbox" id="checkboxall" onclick="togglecheckbox()">';
                }
		$headers['00'] = 'Date';
		$headers['1'] = 'QA Status';
		$headers['2'] = 'Disposition';
		$headers['3'] = 'Agent';
		$headers['4'] = 'Phone';
		$headers['4a'] = 'AltPhone';
		$headers['4b'] = 'Mobile';
		$headers['5'] = 'Name';
		$headers['6'] = 'Company';
                $headers['6a'] = 'Email';
                    $headers['6b'] = 'Address1';
                    $headers['6c'] = 'Address2';
                
                    $headers['7'] = 'Suburb';
                    $headers['7a'] = 'City';
                    $headers['8'] = 'Postcode';
                    $headers['9'] = 'State';
                
                    $headers['9a'] = 'Agent Comments';
                    $headers['9b'] = 'QA Comments';
                    $headers['10'] = 'Date set';
                $rct = 0;
                foreach ($results as $result) 
			{
                            
                            if ($act == 'export')
                                    {
                                        $scriptres = mysql_query("SELECT scriptjson from scriptdata where leadid = '".$result['leadid']."'");
                                        $scriptrow = mysql_fetch_array($scriptres);
                                        $sdata = json_decode($scriptrow['scriptjson']);
                                        foreach ($sdata as $key=>$value)
                                        {
                                             $scriptdata[$key][$result['leadid']] = $value;
                                        }
                                       
                                        /*$xml = $scriptrow['scriptxml'];
                                        $raw = explode("</",$xml);
                                        foreach ($raw as $d)
                                            {
                                                $st_label = strpos($d,"<") + 1;
                                                $end_label = strpos($d,">",$st_label);
                                                $len_label = strlen($d);
                                                $label = substr($d,$st_label,$end_label - $st_label);
                                                $scriptheads[$label] = $label;
                                                $st_val = $end_label + 1;
                                                $value = substr($d,$st_val);
                                                $scriptdata[$label][$result['leadid']] = $value;
                                               }
                                               */
                                    }
                              $rct++;
                        }
		foreach ($results as $result) 
			{
				if ($agentid != 'all' && $result['assigned'] != $agentid)
					{
					}
				elseif ($act == 'export')
				{
                     
		$rows[$result['leadid']]['bulk'] ='<input type="checkbox" name="bulkaction" value="'.$result['leadid'].'">';
                
				$rows[$result['leadid']]['date'] = date('Y-m-d H:i:s',$result['epoch_timeofcall']);
		$rows[$result['leadid']]['status'] = '<span id="status'.$result['leadid'].'">'.ucfirst($result['status']).'</span>';
				$rows[$result['leadid']]['dispo'] = $result['dispo'];
				$rows[$result['leadid']]['agent'] = $agents[$result['assigned']];
				$rows[$result['leadid']]['phone'] = $result['phone'];
				$rows[$result['leadid']]['altphone'] = clearnum($result['altphone']);
				$rows[$result['leadid']]['mobilephone'] = clearnum($result['mobile']);
				$rows[$result['leadid']]['name'] = $result['cname'].$result['cfname']." ".$result['clname'];
				$rows[$result['leadid']]['company'] = $result['company'];
                                $rows[$result['leadid']]['email'] = $result['email'];
                                $rows[$result['leadid']]['address1'] = $result['address1'];
                                $rows[$result['leadid']]['address2'] = $result['address2'];
                                $rows[$result['leadid']]['suburb'] = $result['suburb'];
                                $rows[$result['leadid']]['city'] = $result['suburb'];
                                $rows[$result['leadid']]['postcode'] = $result['zip'];
                                $rows[$result['leadid']]['state'] = $result['state'];

                                $rows[$result['leadid']]['comments'] = $result['comments'];
                                $rows[$result['leadid']]['resultcomments'] = $result['resultcomments'];
                             $rows[$result['leadid']]['dateset'] = $result['epoch_callable'] > 0 ? date('Y-m-d H:i:s',$result['epoch_callable']):'';
                             $rows[$result['leadid']]['options'] = 'title="'.$result['resultcomments'].'" onclick="getlead(\''.$result['leadid'].'\')"';
				foreach ($scriptheads as $addhead)
                                    {
                                        $headers[$addhead] = $addhead;
                                        $rows[$result['leadid']][$addhead] = $scriptdata[$addhead][$result['leadid']];
                                    }

                                
                                }
                                else {
                                    $rows[$result['leadid']]['bulk'] ='<input type="checkbox" name="bulkaction" value="'.$result['leadid'].'"> &nbsp;';
                                    // $player_url = "http://116.93.124.48/audioplayer.php?projectid=". $recordingproject[$result['leadid']] ."&leadid=". $result['leadid'];
                                    // $rows[$result['leadid']]['bulk'] .=$recordinglog[$result['leadid']] > 0 ? '<img src="../icons/recorded.png" title="Recorded" onclick=window.open("'. $player_url . '") />':'';
                                
                                    $_debug = sprintf("%s-%s-%s-%s\n", $recordingproject[$result['leadid']], $result['leadid'], $projects[$recordingproject[$result['leadid']]]['linkurl']);
                                    if ($projects[$recordingproject[$result['leadid']]]['linkurl'] == '' || $projects[$recordingproject[$result['leadid']]]['linkurl'] == null)
                                        $rows[$result['leadid']]['bulk'] .=$recordinglog[$result['leadid']] > 0 ? '<img src="../icons/recorded.png" title="Recorded (' . $_debug . ')" />':'';
                                    else
                                        $rows[$result['leadid']]['bulk'] .=$recordinglog[$result['leadid']] > 0 ? '<img src="../icons/recorded.png" title="Recorded" onclick="player_window('. $recordingproject[$result['leadid']] .' ,'. $result['leadid'] .' ,\'' . $projects[$recordingproject[$result['leadid']]]['linkurl'] . '\')" />':'';

                                    $headers = array();
                                    $headers[] = '<input type="checkbox" id="checkboxall" onclick="togglecheckbox()">';
                                    foreach ($viewfields as $key=>$vals)
                                    {
                                        if ($vals[1] > 0)
                                        {
                                        $headers[$key] = $vals[0];
                                        if ($key == 'assigned') {
                                            $aval =  $result['assigned'] > 0 ? $agents[$result['assigned']]:'';
                                            
                                            }
                                        elseif ($key == 'epoch_timeofcall' || $key == 'epoch_callable'){ 
                                            $aval = $result[$key] > 0 ? date('Y-m-d H:i:s',$result[$key]):'';
                                        }
                                        else $aval = $result[$key];
                                        $rows[$result['leadid']][$key] = $aval;
                                        }
                                    }
                                    $rows[$result['leadid']]['options'] = 'title="'.$result['resultcomments'].'" onclick="getlead(\''.$result['leadid'].'\')"';
      $rows[$result['leadid']]['actions'] = 
                                    '<a href="#" onclick="lastrecording(\''.$result['leadid'].'\')" title="Play Recording" style="display:none"><img src="../icons/recorded.png" /></a>
                                    <a href="#" onclick="qacall(\''.$result['leadid'].'\',event)" title="Call" ><img src="../icons/dial.png" /></a>
                                    <a href="#" onclick="qamail(\''.$result['leadid'].'\',event)" title="Email to Client"><img src="../icons/mail.png" /></a>   
';
                                }
			}
		if ($act == 'export')
			{
				$table = tablegen($headers,$rows,"930");
				createdoc('excel',$table,true);
			}
		else {
                    $headers['actions'] = 'Action';
                   
		$dcont = '<div id="searchresults">'.tablegen($headers,$rows,"100%").'</div>';
		}
	}
if ($act == 'emailtoclient')
{
    $body = $_POST['htmlbody'];
            $emailto = $_REQUEST['to'];
            $subject = $_REQUEST['subject'];
            $record = getrecord($_REQUEST['leadid']);
            $lead = $record['info'];
            $projectname = projects::getprojectname($lead['projectid']);
            if (strlen($subject) < 1)
            {
                $subject = 'Sent from '.$projectname;
            }
            $body = str_replace("<input ", "<input disabled  ", $body);
            $body = str_replace("<select ", "<select disabled  ", $body);
            $body .= '<p>Powered by BlueCloud.</p>';
            $bcres = mysql_query("SELECT * from bc_clients where bcid ='$bcid'");
            $bc = mysql_fetch_assoc($bcres);
            $emailer = new Mailer();
            
            $emailer->set_mail("noreply@bluecloudaustralia.com.au",$emailto,$subject,$body);
            $emailer->fromName($bc['company']);
            echo $emailer->send_mail();
    exit;
}
if ($act == 'getlead' || $act == 'emailtoclient')
	{
		$record = getrecord($_REQUEST['leadid']);
                $s3 = new S3("AKIAIFNBYO657IIJKOUQ", "w6Q/iJwhRYvS+RR1agf3zQoNrvtaw3T4as7qDpd2");
                $s3bucket = "bcrecs-au";                    
		$lead = $record['info'];
                $prefix = $lead['projectid']."/".$lead['leadid'];
                $contents = $s3->getBucket($s3bucket,$prefix);
		$clientidres = mysql_query("SELECT clientid from projects where projectid = '".$lead['projectid']."' ");
		$clientidrow = mysql_fetch_assoc($clientidres);
		$clientid = $clientidrow['clientid'];
		
        // $ccontacts = getbyparams('client_contacts',"clientid = '".$clientid."'",'client_contactid');

        $ccontacts_res = mysql_query("SELECT client_contacts.*, members.userlogin, members.userpass, members.usertype as usermode from client_contacts 
                              left join members on client_contacts.userid = members.userid where clientid = $clientid and client_contacts.bcid = '$bcid' and client_contacts.active = 1");
        while ($ccontacts_row = mysql_fetch_array($ccontacts_res))
        {
            $ccontacts[$ccontacts_row['client_contactid']] = $ccontacts_row;
        }
		foreach ($ccontacts as $cc)
		{
			$cdrop .= '<option value="'.$cc['email'].'">'.$cc['firstname'].' '.$cc['lastname'].'</option>';
		}
		
		$cdata = $record['scriptdata'];
                $cfdata = $record['customdata'];
		$appdate = $lead['epoch_callable'] > 0 ? date("Y-m-d H:i:s", $lead['epoch_callable']):'';

        $client_contact_slots_res = mysql_query("select concat (lastname,', ',firstname) as apptarget from client_contact_slots a cross join client_contacts b on a.client_contactid=b.client_contactid where leadid= " . $lead['leadid']);
        $client_contact_slots_row = mysql_fetch_assoc($client_contact_slots_res);
        $apptarget_appointment = $client_contact_slots_row['apptarget'];

		$audio = $contents;
		$d = dispolist($lead['projectid']);
                foreach ($d as $disp)
                {
                    if ($disp['statustype'] == 'dateandtime' || $disp['statustype'] == 'transferdateandtime')
                            {
                                    $dispodrop .="<option onclick=\"createdateinput()\">";
                                    $dispodrop .=$disp['statusname'];
                                    $dispodrop .="</option>";

                            }
                    elseif ($disp['statustype'] == 'booking')
                                            {
                                    $dispodrop .="<option onclick=\"doslots('".$lead['leadid']."','$clientid')\">";
                                    $dispodrop .=$disp['statusname'];
                                    $dispodrop .="</option>";
                                            }
                    elseif ($disp['statustype'] == 'link')
                            {
                            $dispodrop .="<option onclick=\"showupdatepage('".$disp['statusid']."')\">";
                            $dispodrop .=$disp['statusname'];
                            $dispodrop .="</option>";
                            }
                    elseif ($disp['statustype'] == 'transfer')
                            {
                            $dispodrop .="<option onclick=\"cleardateinput()\">";
                            $dispodrop .=$disp['statusname'];
                            $dispodrop .="</option>";
                            }

                    elseif ($disp['statusname'] != 'all') {
                            $dispodrop .="<option onclick=\"cleardateinput();\">";
                            $dispodrop .=$disp['statusname'];
                            $dispodrop .="</option>";
                    }
                }
		//$dispdrop = createdropdown($d,"statusname","statusname");
                $dispdrop = $dispodrop;
                $projectname = projects::getprojectname($lead['projectid']);
		if (isset($_REQUEST['export']))
		{
			
		}
		elseif ($act != 'emailtoclient' && !isset($_REQUEST['export']) ) {
                    //| <a href="qa.php?act=getlead&leadid=<?=$_REQUEST['leadid'];&export">Export</a> 
		?>
        <a href="#" onClick="savelead()">Save</a> | <a href="#" onClick="printdiv()" id="printlink">Print</a> | <a href="#" onclick="emaillead('<?=$lead['leadid'];?>')">Email</a> |
        <div id="emailcontacts<?=$lead['leadid'];?>" class="dialogform">
        Select Recipient:
        <select name="emailtoclient"><option></option><?=$cdrop;?></select><br>
        Subject: <input type="text" name="subject" placeholder="<?php echo $projectname;?>">
        <br /><a href="#" onclick="sendemailtoclient()" id="setc">Send</a>
        </div>
        <?php 
                }
        if ($act == 'emailtoclient' || isset($_REQUEST['export'])) ob_start();
        ?>
        <div id="msg_print">
        <form action="qa.php?act=savelead&leadid=<?=$lead['leadid'];?>" id="updatelead" name="updatelead" method="post">
        <table cellspacing="5" width="800">
        <tr><td colspan="4" class="tableheader">Contact Information</td></tr>
        <tr><td class="tableitems">Leadid: </td><td id="tlid"><?=$lead['leadid'];?></td></tr>
        <tr><td>Name:</td><td><input type="text" name="cname" value="<?=$lead['cname'];?>" />
            <input type="hidden" name="leadid" id="leadidval" value="<?=$lead['leadid'];?>" />
            </td><td>Phone:</td><td><?=$lead['phone'];?><input type="hidden" name="phone" value="<?=$lead['phone'];?>" /></td></tr>
         <tr><td>AltPhone:</td><td><input type="text" name="altphone" value="<?=$lead['altphone'];?>" /></td><td>Mobile:</td><td><input type="text" name="mobile" value="<?=$lead['mobile'];?>" /></td></tr>
        <tr><td>First Name:</td><td><input type="text" name="cfname" value="<?=$lead['cfname'];?>" /></td><td>Last Name:</td><td><input type="text" name="clname" value="<?=$lead['clname'];?>" /></td></tr>
        <tr><td>Title:</td><td><input type="text" name="title" value="<?=$lead['title'];?>" /></td><td>Position:</td><td><input type="text" name="positiontitle" value="<?=$lead['positiontitle'];?>" /></td></tr>
        <tr><td>Company:</td><td colspan="3"><input type="text" name="company" style="width:500px" value="<?=$lead['company'];?>" /></td></tr></tr>
        <tr><td>Address1:</td><td colspan="3"><input type="text" name="address1" style="width:500px" value="<?=$lead['address1'];?>" /></td></td></tr>
        <tr><td>Address2:</td><td colspan="3"><input type="text" name="address2" style="width:500px" value="<?=$lead['address2'];?>" /></td></td></tr>
        <tr><td>Suburb:</td><td><input type="text" name="city" value="<?php echo strlen($lead['suburb']) > 0 ? $lead['suburb']:$lead['city'];?>" /></td>
            <td>State:</td><td><input type="text" name="state" value="<?=$lead['state'];?>" /></td></tr>
        <tr><td>Postcode:</td><td><input type="text" name="zip" value="<?=$lead['zip'];?>" /></td><td>Email:</td><td><input type="text" name="email" value="<?=$lead['email'];?>" /></td></tr>
        <tr><td>Comments:</td><td colspan="4"><textarea style="width:400px; height:90px" name="comments"><?=$lead['comments'];?></textarea></td></tr>
        </table>
        <br>
        <hr>
       
        <br>
        <table width="800" cellspacing="5">
        <tr><td colspan="4" class="tableheader">Custom Data</td></tr>
         <?php
        //var_dump($record);
		if (is_array($cfdata))
		{

		foreach ($cfdata as $key=>$value)
			{
				echo '<tr><td colspan="2">'.ucfirst(str_replace("_"," ",$key)).'</td><td>
                                    <input type="text" class="qacf" value="'.$value.'" name="'.$key.'"></td></tr>';
			}

		}
		?>
        
        </table>
        <br>
        <hr>
       
        <br>
        <table width="800" cellspacing="5">
        <tr><td colspan="4" class="tableheader">Script Captured Data</td></tr>
         <?php
		if (is_array($cdata))
		{

		foreach ($cdata as $key=>$value)
			{
				echo '<tr><td colspan="2">'.ucfirst(str_replace("_"," ",$key)).'</td><td>
                                    <input type="text" class="qasf" value="'.$value.'" name="'.$key.'"></td></tr>';
			}

		}
		?>
        
        </table>
        <br>
        <hr>
        <?php
		if (is_array($audio) && count($audio) > 0)
		{
		?>
        <br>
        <table width="800" cellspacing="5">
        <tr><td colspan="4" class="tableheader">Recordings</td></tr>
        <tr><th>Date/Time</th><th>Size</th><th>Play</th><th>File</th></tr>
        <?php
		$cta = 0;
                
		foreach ($audio as $aud)
			{
				$m = $cta % 2;
				if ($m = 0) $cl = "tableitem";
				else $cl = "tableitem_";
                                //$lc = $aud['size']/3072;
                                //$lc = number_format($lc, 2);
                                $lc = filesizeformat($aud['size']);
                                $media = S3::getAuthenticatedURL($s3bucket, $aud['name'], 3600, false, true);
                                //$dt = gettimefromname($aud['name']);
                                $dt = gettimefromname($aud['name']);
                                
                                $dti = date("Y-m-d H:i:s",$dt);
				echo '<tr class="'.$cl.'"><td>'.$dti.'</td>
				<td>'.$lc.'</td>';
                                echo '<td width="220" align="center">';
                                if (!isset($_REQUEST['export']) && $act != 'emailtoclient')
                                {
                                echo '<object type="application/x-shockwave-flash" data="../../jquery/player_mp3_mini.swf" width="200" height="20">
    <param name="movie" value="../../jquery/player_mp3_mini.swf" />
    <param name="bgcolor" value="#c7c7c7" />
    <param name="FlashVars" value="mp3='.urlencode($media).'&amp;bgcolor=c7c7c7&amp;slidercolor=404040" />
</object>';
                                }
                                echo '</td>
				<td><a id="wavfile" href="'.$media.'" target="_blank">Download</a></td>
				</tr>';
				$cta++;
			}
		?>
        </table>
        <br>
        <hr>
        <?
		}
                $notesres = mysql_query("SELECT * from leads_notes where leadid = '".$_REQUEST['leadid']."'");
                $notesrow = mysql_fetch_assoc($notesres);
                $notes = json_decode($notesrow['note'],true);
                $agentcomments = '';
                foreach ($notes as $note)
                {
                    $agentcomments .= $note['user']."[".date("Y-m-d H:i:s",$note['timestamp'])."]:".$note['message']."\r\n";
                }
		?>
        <br>
        <table width="800" cellspacing="5">
        <tr><td colspan="4" class="tableheader">Results and Notes</td></tr>
        <tr><td colspan="2">Agent: </td><td><input type="hidden" name="projectid" value="<?=$lead['projectid'];?>"/><input type="hidden" name="assigned" value="<?=$lead['assigned'];?>"/><?=$agents[$lead['assigned']];?></td></tr>
       
        <?php if (!isset($_REQUEST['export']) && $act != 'emailtoclient')
        {
            ?>
        <tr><td colspan="2">Agent Notes:</td><td><textarea style="width:400px; height:90px" disabled><?=$agentcomments;?></textarea></td></tr>
        <?php
        }
        ?>
        <tr><td colspan="2">Disposition:</td><td><select name="dispo"><option value="<?=$lead['dispo'];?>"><?=ucfirst($lead['dispo']);?></option><?=$dispdrop;?>
        </select></td></tr>
        <?
		if(strlen($appdate) > 1)
		{
                }
			?>
        <tr id="datetd">
            <td colspan="2">Date set:</td><td><input class="dtpick" type=text name="dtime" value="<?=$appdate;?>" /></td>
        </tr>
        <tr id="datetd">
            <td colspan="2">Booked for:</td><td><?=$apptarget_appointment;?></td>
        </tr>
           
        <tr><td colspan="2">QA Comments:</td><td><textarea name="qa" style="width:400px; height:90px"><?=$lead['qa'];?></textarea></td></tr>
        <tr><td colspan="2">QA Status:</td><td><select name="status"><option value="<?=$lead['status'];?>"><?=ucfirst($lead['status']);?></option><option value="approved">Approved</option><option value="failed">Failed</option><option value="incomplete">Incomplete</option></select></td></tr>
        </table>
        </form>
        </div>
        
        <?
        if (isset($_REQUEST['export']))
        {
           
            $body = ob_get_contents();
            ob_clean();
            $body = str_replace("<input ", "<input disabled  ", $body);
            $body = str_replace("<select ", "<select disabled  ", $body);
             createdoc("word", $body);
        }
        if ($act =='emailtoclient')
        {
            $body = ob_get_contents();
            $emailto = $_REQUEST['to'];
            $subject = $_REQUEST['subject'];
            if (strlen($subject) < 1)
            {
                $subject = 'Sent from '.$projectname;
            }
            ob_clean();
            $body = str_replace("<input ", "<input disabled  ", $body);
            $body = str_replace("<select ", "<select disabled  ", $body);
            $bcres = mysql_query("SELECT * from bc_clients where bcid ='$bcid'");
            $bc = mysql_fetch_assoc($bcres);
            $emailer = new Mailer();
            
            $emailer->set_mail("noreply@bluecloudaustralia.com.au",$emailto,$subject,$body);
            $emailer->fromName($bc['company']);
            echo $emailer->send_mail();
            exit;
        }
        if (!isset($_REQUEST['export']))
		{
			?>
        <a href="#" onClick="savelead()">Save</a> | <a href="#" onClick="printdiv()" id="printlink">Print</a> | <a href="#" onclick="emaillead('<?=$lead['leadid'];?>')">Email</a> |
        <?
		}
		exit();
	}