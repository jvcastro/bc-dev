<?php
$pid = $_REQUEST['pid'];
$act2 = $_REQUEST['act2'];

		
if ($act2 == 'canceltemplate')
	{
		include "../dbconnect.php";
		$templateid = $_REQUEST['tid'];
		mysql_query("DELETE from templates where templateid = '$templateid'");
		exit;
	}
if ($act2 == 'createnew')
	{
		mysql_query("insert into templates set projectid = '$pid', template_name = 'New Template'");
		$templateid = mysql_insert_id();
	}
else {
	$templateid = $_REQUEST['templateid'];
}
		$tq = "select * from templates where templateid = '$templateid'";
		$isthere = mysql_query($tq);
		$trow = mysql_fetch_array($isthere);
		$body = $trow['template_body'];
                if ($trow['sigid'] == 0) $sdnone = 'Selected';
                $sigdrop = '<option value="0" '.$sdnone.'>None</option>';
                $sigs = getdatatable("signatures where bcid = '$bcid'",'sigid');
		foreach ($sigs as $sig)
                {
                    $selected = '';
                    if ($sig['sigid'] == $trow['sigid']) $selected = 'Selected';
                    $sigdrop .= '<option value="'.$sig['sigid'].'" '.$selected.'>'.$sig['signature_name'].'</option>';
                }
		$attachments = split(",",$trow['attachments']);
		$res = mysql_query("SELECT * from statuses where projectid in ('0','$pid') ORDER BY statusname");
		$drop .= '<select name="template_disposend" id="template_disposend">';

		$drop .= '<option value="0">Inactive</option>';

		while ($row = mysql_fetch_assoc($res))
			{
				if ($row['statusname'] == $trow['disposend']) $sel = 'selected = "selected"';
				else $sel = '';
				$drop .= '<option value="'.$row['statusname'].'" '.$sel.'>'.ucfirst($row['statusname']).'</option>';
			}
		$drop .= '</select>';
                $encoptions= array('none','tls','ssl');
                $encdrop = '';
                foreach ($encoptions as $enc)
                {
                    $selected = '';
                    if ($trow['mailencryption'] == $enc) $selected = 'selected';
                    $encdrop .= '<option value="'.$enc.'" '.$selected.'>'.$enc.'</option>';
                }
		?>
<div class="apptitle">Email Template Editor</div>
<div class="secnav">
    <input type="button" onclick="manage_persist('<?=$trow['projectid'];?>')" value="Back"/>
   
 <input type="button" onclick="canceltemplate('<?=$templateid;?>', '<?=$trow['projectid'];?>')" value="Delete"/>
 </div>
<form action="post" action="admin.php">            
<table width="25%" style="float:left; margin-right:10px" cellpadding="0" cellspacing="5" border="0">
<tr><td>
        <table width="100%">
         <tr>
         	<td class="title">Template Name:</td>
         	<td align="left"><input type="text" name="template_name" id="template_name" class="box" value="<?=$trow['template_name'];?>" /></td>
         </tr>
         <tr>
         	<td class="title">Email From Name:</td>
         	<td align="left"><input type="text" name="emailfromname" id="emailfromname" class="box" value="<?=$trow['emailfromname'];?>" /></td>
         </tr>
         <tr>
         	<td class="title">Email From Address:</td>
         	<td align="left"><input type="text" name="emailfrom" id="emailfrom" class="box" value="<?=$trow['emailfrom'];?>" /></td>
         </tr>
         <tr>
         	<td class="title">Email CC:</td>
         	<td align="left"><input type="text" name="emailcc" id="emailcc" class="box" value="<?=$trow['emailcc'];?>" /></td>
         </tr>
         <tr>
         	<td class="title">Email BCC:</td>
         	<td align="left"><input type="text" name="emailbcc" id="emailbcc" class="box" value="<?=$trow['emailbcc'];?>" /></td>
         </tr>
         <tr>
         	<td class="title">Subject:</td>
         	<td align="left"><input type="text" name="template_subject" id="template_subject" class="box" value="<?=$trow['template_subject'];?>" /></td></tr>
          <tr>
         	<td class="title">Autosend by Dispo:</td>
         	<td align="left"><?=$drop;?></td>
         </tr>
         <tr>
         	<td class="title">Agent Editable:</td>
         	<td align="left"><select id="editable" name="editable"><option value="1" <?php echo $trow['editable'] == 1 ? "Selected":"";?>>Yes</option><option value="0" <?php echo $trow['editable'] == 0 ? "Selected":"";?>>No</option></td>
         </tr>
        </table>
    </td>
</tr>
<tr><td><table width="100%" id="advancedemail">
          <tr>
         	<td class="title">Mail Encryption:</td>
         	<td align="left"><select name="mailencryption" id="mailencryption">
                        <?php echo $encdrop;?>
                    </select></td>
         </tr>   
         <tr>
         	<td class="title">Mail Server:</td>
         	<td align="left"><input type="text" name="mailserver" id="mailserver" class="box" value="<?=$trow['mailserver'];?>" /></td>
         </tr>
         <tr>
         	<td class="title">Mail Port:</td>
         	<td align="left"><input type="text" name="mailport" id="mailport" class="box" value="<?=$trow['mailport'];?>" /></td>
         </tr>
         <tr>
         	<td class="title">Mail User:</td>
         	<td align="left"><input type="text" name="mailuser" id="mailuser" class="box" value="<?=$trow['mailuser'];?>" /></td>
         </tr> 
         <tr>
         	<td class="title">Mail Password:</td>
         	<td align="left"><input type="password" name="mailpass" id="mailpass" class="box" value="<?=$trow['mailpass'];?>" /></td>
         </tr>
         </div>
         <tr><td colspan="2"> 
                 <div class="secnav">
                     <input type="button" onclick="updatetemplate('<?=$templateid;?>',false)" value="Update"/>
                     <input type="button" onclick="testmail()" value="Test Mail"/>
                 </div> 
             </td>
         </tr>  
</table></td></tr></table>
        <table style="float:left; width:50%">
         <tr>
         	<td class="title">Message:</td><td></td></tr>
         <tr>
         	<td align="left" colspan="2"><textarea name="template_body" id="template_body" class="box-1" style="width:100%; height:300px;"/><?=$body;?></textarea></td>
         </tr>
         <tr> <td class="title">Signature:</td><td><select name="sigid" id="sigid"><?=$sigdrop;?></select></td></tr>
        
</form>
 <tr>
         	<td class="title">Attachments:</td>
         	<td align="left">
            <span id="atts">
            <?php
			$ct = 0;
			foreach ($attachments as $attachment)
			{
			if (strlen($attachment) > 0)
			{
			$ct++;
			?>
            <div id="div_<?=$ct;?>"><a href="../attachments/<?=$attachment;?>"><?=$attachment;?></a> | <a  href="#" onclick="removeattachment('<?=$templateid;?>','<?=$attachment;?>','<?=$ct;?>')">Remove</a></div>
            
            <?
			}
			}
			?>
            </span>
            <form enctype="multipart/form-data" method="POST" action="uploader.php" target="uplo2">
			<input type="hidden" name="templateid" value="<?=$templateid;?>" />
			<input type="hidden" name="act" value="attach" />
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>
			Attach File: <input id="MAX_FILE_SIZE" name="cfile" type="file" style="font-size:10px; height:20px; padding-bottom:8px; position:relative; left:25px"  /><input type="submit" value="Attach" style="font-size:10px; height:20px; padding-bottom:8px; position:relative; left:25px" />
</td>
            
         </tr>
   <tr><td colspan="2"> <div class="secnav"><input type="button" onclick="updatetemplate('<?=$templateid;?>',false)" value="Update"/></div> </td></tr>      
</table>
    <div id="testmail" style="display:none">
        <b>Send Test Mail to:</b> <input type="text" name="testmailto" id="testmailto"><input type="button" onclick="updatetemplate('<?=$templateid;?>',true)" value="Send"/>
    </div>
<div id="dragmerge" style="float:left;position:relative;top:15px">
            <ul id="inmenu" class="domenu">
                <li><h3>Drag Merge Fields</h3></li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge(event,'name')">Name</a>
                </li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge(event,'cfname')">FirstName</a>
                </li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge(event,'clname')">SurName</a>
                </li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge(event,'state')">State</a>
                </li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge(event,'address1')">Address</a>
                </li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge(event,'phone')">Phone</a>
                </li>
                
                
            </ul>
        </div>
</form>
<iframe name="uplo2" width="0" height="0" style="display:none"></iframe>	
