<?php
class session extends dataTable{
    public $project = array();
    public $user =array();
    public function __construct($sessionid) {
        $res = mysql_query("SELECT * from liveusers where sessionid = '".$sessionid."'");
        $this->data = mysql_fetch_assoc($res);
        $res2 = mysql_query("SELECT * from projects where projectid = '".$this->data['projectid']."'");
        $this->project = mysql_fetch_assoc($res2);
        $res3 = mysql_query("SELECT memberdetails.*,members.* from memberdetails left join members on memberdetails.userid = members.userid where memberdetails.userid = '".$this->data['userid']."'");
        $this->user = mysql_fetch_assoc($res3);
    }
}
?>
