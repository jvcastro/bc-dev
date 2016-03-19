<?php
class actionlog {
    public static function action($action,$userid, $projectid)
    {
        $t = time();
        $query = "Update actionlog set endepoch = '$t' where userid = $userid and projectid = $projectid ORDER BY logid DESC LIMIT 1";
        mysql_query($query);
      $iq = "INSERT into actionlog set startepoch = '$t',userid = $userid, projectid = $projectid, action = '$action'";
        mysql_query($iq);
    }
}
?>
