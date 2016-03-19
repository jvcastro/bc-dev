<?php
class customdata extends dataTable {
       public function __construct() {
         
          $this->tablename = 'leads_custom_fields';
          $this->idname = 'leadid';
       }
       
       public function findById($id)
    {
        $res  = mysql_query("SELECT * from $this->tablename where $this->idname = '$id'");
        $row = mysql_fetch_assoc($res);
        return $row;
    }
       public static function add($leadid, $cf)
       {
           $c = json_encode($cf);
           $res = mysql_query("INSERT into leads_custom_fields SET leadid = '$leadid',customfields='".  mysql_real_escape_string($c)."'");
           
           return $res;
       }
       public static function updatecf($leadid, $cf)
       {
           $c = json_encode($cf);
           $res = mysql_query("UPDATE leads_custom_fields SET customfields='".  mysql_real_escape_string($c)."' where leadid = '$leadid'");
           
           return $res;
       }
       public static function insertcf($leadid,$cf)
       {
           
       }
}
?>