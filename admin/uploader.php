<?php
include_once "../dbconnect.php";
$securekey = 'Do not change this whatever happens!';
$act = $_REQUEST['act'];
$proj = $_REQUEST['proj'];
$myfile = basename( $_FILES['cfile']['name']);
$myfile = str_replace(" ","_",$myfile);
$filehash = md5($myfile.$securekey);	
	if ($act == 'attach')
		{
		$templateid = $_REQUEST['templateid'];
		$target_path = "../attachments/" . $myfile; 	
		}
        elseif ($act == 'sig')
        {
            $sigid = $_REQUEST['sigid'];
            $target_path = "../attachments/" . $myfile; 
        }
	else {$target_path = "../upload/" . $filehash; }
	if(move_uploaded_file($_FILES['cfile']['tmp_name'], $target_path)) {
    	//success
	if ($act == 'attach')
		{
		$res = mysql_query("SELECT * from templates where templateid = '$templateid'");
		$row = mysql_fetch_array($res);
		$attach = $row['attachments'];
		if (strlen($attach) > 0)
			{
				$aq = "update templates set attachments = '$attach,$myfile' where templateid = '$templateid'";
			}
		else $aq = "update templates set attachments = '$myfile' where templateid = '$templateid'";
		mysql_query($aq);
		echo "<script>parent.attachcomplete('$myfile');</script>";
		echo $aq;
		}
        elseif ($act == 'sig')
        {
            $res = mysql_query("SELECT * from signatures where sigid = '$sigid'");
		$row = mysql_fetch_array($res);
		$attach = $row['signature_images'];
		if (strlen($attach) > 0)
			{
				$aq = "update signatures set signature_images = '$attach,$myfile' where sigid = '$sigid'";
			}
		else $aq = "update signatures set signature_images = '$myfile' where sigid = '$sigid'";
		mysql_query($aq);
		echo "<script>parent.attachcomplete('$myfile');</script>";
		echo $aq;
        }
	else {
        $desc = $_REQUEST['desc'];
	mysql_query("INSERT into uploads set filename = '$myfile', projectid = '$proj', description = '$desc', uploaddate = NOW()");
	echo "<script>parent.cmess('Upload Complete',$proj);</script>";
	}
	
	} 	
else{
    echo "<script>parent.cmess('Upload Failed',$proj);</script>";
	
}
?>