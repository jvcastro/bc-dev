<?php
/**
 * Class to facilitate Admin/Client Portal Dialing only.
 * Class will create, use, and then delete liveuser and callman entry.
 * @author Obrifs@gmail.com
 */
class dialer {
    public $extension = 0;
    public $leadid = 0;
    public function __construct($extension = null, $leadid = null) {
        $this->extension = $extension;
        if ($leadid) $this->leadid = $leadid;
    }
    public function dial_controls($autocall = false)
    {
        $dirname = dirname(__FILE__);
        $record = new records($this->leadid);
        include "$dirname/dialer/controls.php";
        $this->dial_scripts();
    }
    public function dial_scripts()
    {
        $dirname = dirname(__FILE__);
        include "$dirname/dialer/scripts.php";
    }
    public function dial_lead() {
        global $bcid;
        $leadid = $this->leadid;
        $record = new records($leadid);
        if (!$record->projectid)
        {
            $list = lists::findbyListid($record->listid);
            $project = projects::getbyid($list['projects']);
        }
        else {
            $project = projects::getbyid($record->projectid);
        }
        $this->templive($project['projectid']);
        callman::create_originate($record, $project);
    }
    public function hangup($leadid)
    {
       $leadid = $this->leadid;
        $ami = new AMI();
        $result = $ami->hangup($leadid);
        if ($result != 'Success')
        {
            $ami->hangup($leadid);
        }
        $this->removelive();
    }
    public function templive($pid)
    {
        $uid = $_SESSION['uid'];
        
        $cres = mysql_query("select confserver from bc_phones where name = '".$this->extension."' limit 1");
        $crow = mysql_fetch_assoc($cres);
        $confserver = $crow['confserver'];
        return mysql_query("INSERT into liveusers set leadid = '".$this->leadid."', projectid = '".$pid."', status= 'dialing', extension = '".$this->extension."', webstatus = 'free', confserver = '$confserver', userid= '$uid'");
    }
    public function removelive()
    {
        $uid = $_SESSION['uid'];
        return mysql_query("DELETE from liveusers where userid = '$uid'");
    }
    public static function init()
    {
        $dialer = new dialer($_SESSION['extension'],$_REQUEST['leadid']);
        $sub = $_REQUEST['sub'];
        $dialer->sub();
    }
}

?>
