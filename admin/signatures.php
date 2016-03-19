<?php
$pid = $_REQUEST['pid'];
$sub = $_REQUEST['sub'];
$act2 = $sub;

if ($act2 == 'removeimage')
{
    $sigid = $_REQUEST['sigid'];
    $attachment = $_REQUEST['image'];
    $tempres = mysql_query("SELECT * from signatures where sigid = '$sigid'");
    $sig = mysql_fetch_assoc($tempres);
    $atts = explode(",",$sig['signature_images']);
    foreach ($atts as $att)
            {
                    if ($att != $attachment)
                            {
                                    $newatt[] = $att;
                            }
            }
    $newatts = implode(",",$newatt);
    mysql_query("UPDATE signatures set signature_images = '".mysql_real_escape_string($newatts)."' where sigid = '$sigid'");
    //$act = 'emailtemplates';
    exit;
}
if ($act2 == 'cancelsignature')
	{
		$sigid = $_REQUEST['sigid'];
		mysql_query("DELETE from signatures where sigid = '$sigid'");
		exit;
	}
if ($act2 == 'updatesignature')
	{
		$sigid = $_REQUEST['sigid'];
                extract($_POST);
		mysql_query("update signatures set signature_name = '".mysql_real_escape_string($signature_name)."', signature_body ='".mysql_real_escape_string($signature_body)."' where sigid = '$sigid'");
                
                echo "Signature Updated";
		exit;
	}
if ($act2 == 'createnew')
	{
		mysql_query("insert into signatures set signature_name = 'New Signature', epoch_created = '".time()."',bcid = '$bcid'");
		$sigid = mysql_insert_id();
	}
else {
	$sigid = $_REQUEST['sigid'];
}
		$tq = "select * from signatures where sigid = '$sigid'";
		$isthere = mysql_query($tq);
		$trow = mysql_fetch_array($isthere);
		$body = $trow['signature_body'];
                $attachments = split(",",$trow['signature_images']);

		?>
<div class="apptitle">Email Signature</div>
<div class="secnav">
    <input type="button" onclick="manage_persist('<?=$pid;?>')" value="Back"/>
   
 <input type="button" onclick="cancelsignature('<?=$sigid;?>', '<?=$pid;?>')" value="Delete"/>
 </div>
<form action="post" action="admin.php" name="emailsigform" id="emailsigform">            

        <table style="float:left; width:50%">
         <tr>
         	<td class="title">Signature Name:</td><td><input type="text" name="signature_name" id="signature_name" value="<?=$trow['signature_name'];?>"></td></tr>
         <tr>
         	<td align="left" colspan="2"><textarea name="signature_body" id="signature_body" class="box-1" style="width:100%; height:300px;"/><?=$body;?></textarea></td>
         </tr>
  		
        
</form>
 <tr>
         	<td class="title" style=" vertical-align: top">Images:</td>
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
            <div id="div_<?=$ct;?>"><img src="../attachments/<?=$attachment;?>" alt="<?=$attachment;?>" draggable="true" /> | <a  href="#" onclick="removesigimage('<?=$sigid;?>','<?=$attachment;?>','<?=$ct;?>')">Remove</a></div>
            
            <?
			}
			}
			?>
            </span>
            <form enctype="multipart/form-data" method="POST" action="uploader.php" target="uplo2">
			<input type="hidden" name="sigid" value="<?=$sigid;?>" />
			<input type="hidden" name="act" value="sig" />
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" id="MAX_FILE_SIZE"/>
			Attach File: <input name="cfile" type="file" style="font-size:10px; height:20px; padding-bottom:8px; position:relative; left:25px"  /><input type="submit" value="Attach" style="font-size:10px; height:20px; padding-bottom:8px; position:relative; left:25px" />
</td>
            
         </tr>
   <tr><td colspan="2"> <div class="secnav"><input type="button" onclick="updatesignature('<?=$sigid;?>')" value="Update"/></div> </td></tr>      
</table>
<div id="dragmerge" style="float:left;position:relative;top:15px">
            <ul id="inmenu" class="domenu">
                <li><h3>Drag Merge Fields</h3></li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge_a(event,'afirst')">Agent FirstName</a>
                </li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge_a(event,'alast')">Agent LastName</a>
                </li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge_a(event,'email')">Agent Email</a>
                </li>
                <li>
                    <a href="#" draggable="true" ondragstart="dragmerge_a(event,'phone')">Agent Phone</a>
                </li>
                
                
            </ul>
        </div>
</form>
<iframe name="uplo2" width="0" height="0" style="display:none"></iframe>	
