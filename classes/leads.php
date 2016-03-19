<?php
class leads extends dataTable {

       public function __construct() {
         
          $this->tablename = 'leads_raw';
          $this->idname = 'leadid';
       }
       
       public function findById($leadid)
    {
        $res  = mysql_query("SELECT * from $this->tablename where leadid = '$leadid'");
        $row = mysql_fetch_assoc($res);
        return $row;
    }
       public static function add($data)
       {
           $qs = array();
           foreach ($data as $key=>$value)
           {
               $qs[] = $key." = '".mysql_real_escape_string($value)."'";
              
           }
           $qstring = implode(",",$qs);
           
            mysql_query("INSERT into leads_raw SET ".$qstring);
            $id = mysql_insert_id();
            return $id;
       }
}
?>