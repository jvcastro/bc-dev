<?php
class objectives extends dataTable {
    public $campaign = array();
    public $agents = array();
     public function __construct($projectid) {
         $res = mysql_query("SELECT * from project_objectives where projectid = $projectid");
         while ($row = mysql_fetch_assoc($res))
         {
             $this->campaign[$row['id']] = $row;
         }
         $res = mysql_query("SELECT * from project_objectives_team where projectid = $projectid");
         while ($row = mysql_fetch_assoc($res))
         {
             $this->agents[$row['id']] = $row;
         }
     }
}
?>
