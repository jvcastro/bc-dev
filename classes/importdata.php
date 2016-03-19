<?php
class importdata {
    public $type = 'members';
    public static function init()
    {
        $sub = $_REQUEST['sub'];
        $type = $_REQUEST['type'];
        $importdata = new importdata($type);
        if (!$sub) $importdata->uploadform();
        else $importdata->$sub();
    }
    public function __construct($type = NULL) {
       if ($type) $this->type = $type;
       $this->loadscripts();
    }
    public function uploadform(){
        $this->load("uploadform");
    }
    public function load($form)
    {
        global $bcid;
        $dirname = dirname(__FILE__);
        require_once "$dirname/importdata/".$this->type."$form.php";
    }
    public static function loadscripts()
    {
       $dirname = dirname(__FILE__);
        require_once "$dirname/importdata/scripts.php"; 
    }
    public function upload()
    {
        $myfile = basename( $_FILES['csvfile']['name']);
	$target_path = $target_path . basename( $_FILES['csvfile']['name']); 
	
	if (strlen($myfile) > 1)
	{
	if(move_uploaded_file($_FILES['csvfile']['tmp_name'], $target_path)) 
		{
                    $this->parsecsv($myfile, $target_path);
		} 	
	else{
    	echo "There was an error uploading the file, please try again!";
        exit;
		}
	}
        else exit;
    }
    public function getfields()
    {
        /*
         * Use key=>value where key is fieldname and value would be the label
         */
        if ($this->type == 'members')
        {
           $ret = array('userlogin'=>'Email','userpass'=>'Password','afirst'=>'FirstName','alast'=>'LastName');
        }
        return $ret;
    }
    public function parsecsv($path, $targetp)
    {
        //echo $path.'<br>';
	$csv = fopen($targetp,"r");
	$data = fgetcsv($csv,0,",");
	$z = 0;
	$ct = count($data);
      
	$dupcheck = $_REQUEST['dupcheck'];
	$field[$z] = $data[$z];
	echo '<div class="entryform" style="width:300px; height:250px">
            <title>Field Mapping</title>
            <form name="mapping" id="mapping">
	<input type="hidden" name="sub" value="parsemap">
        <input type="hidden" name="targetp" value="'.$targetp.'">
        <input type="hidden" name="roleid" value="'.$_REQUEST['roleid'].'">';
	echo '
        <div id="respmessage"></div>    
        <table id="mappingtable" width="100%"><tr><td class="tableheader"><h3>CSV Column</h3></td><td class="tableheader"><h3>Field</h3></td></tr>';
        $fields = $this->getfields();	
	while ($z < $ct)
		{
                echo "<tr><td>".$data[$z].":</td><td><select name=\"field$z\"><option value=\"nomap\">No Mapping</option>";
		foreach ($fields as $fld=>$label)
			{			
			echo "<option value=\"".$fld."\">".ucfirst($label)."</option>";
			}
		echo"</select></td></tr>";
		$z++;
		}
	echo "
            
        </table>    
        <input type=\"hidden\" value=\"".$ct."\" name=\"fieldcount\">
        <div class=buttons>
        <input type=\"button\" value=\"Next\" onclick=\"parsemap()\">
        </div>
        </form>
        
        </div>";
        exit;
		
	
	
    }
    public function parsemap()
    {
        extract($_POST);
	
	$rower = $_POST;
	$csv = fopen($targetp,"r");
	$processed = 0;
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
			if ($assfield != 'nomap')
				{
				
                                  $tdata[$rower['field'.$n]] = $data[$n];  
				}
			$n++;
			}
               
                $this->insertdata($tdata,$roleid);
                $processed++;	
	}
        //$duplicate = count($duplicates);
	if ($dupcheck == 'nocheck')
		{
			echo "Duplicates where not checked <br>";
		}
	else echo $duplicate." duplicates found <br>";
	echo "Processed $processed records<br>";
	exit;
    }
    public function insertdata($tdata,$roleid = NULL)
    {
        global $bcid;
        if ($this->type == 'members')
        {
            foreach ($tdata as $fld=>$value)
            {
                if ($fld == 'userlogin' || $fld == 'userpass')
                {
                    $qsmem[] = "$fld='".mysql_real_escape_string($value)."'";
                    if ($fld == 'userlogin') $email = mysql_real_escape_string($value);
                }
                if ($fld == 'afirst' || $fld == 'alast')
                {
                    $qsdet[] = "$fld='".mysql_real_escape_string($value)."'";
                }
            }
            $memstring = implode(", ",$qsmem);
            $detstring = implode(", ",$qsdet);
            mysql_query("insert into members set $memstring, email = '$email', usertype = 'user',bcid= '$bcid', roleid='$roleid'");
            $newid = mysql_insert_id();
            if ($newid) mysql_query("insert into memberdetails set $detstring, userid= '$newid'");
        }
        else {
        $qs = array();
        foreach ($tdata as $fld=>$value)
        {
            $qs[] = "$fld='$value'";
        }
        $setstring = implode(", ",$qs);
        mysql_query("insert into ".$this->type." set $setstring");
        }
    }
}
?>
