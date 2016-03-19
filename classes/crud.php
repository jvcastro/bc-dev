<?php
class crud {
    private $table;
    public $fields;
    public $data;
    public function __construct($table) { //fields cannot be empty
        $this->table = $table;
    }
    
    public function create($keyvalue)
    {
        $qstring = "insert into ".$this->table." set ";
        $qstring_ = array();
        foreach ($keyvalue as $key=>$value)
        {
            $qstring_[] .= "`$key` = '".mysql_real_escape_string($value)."'";
        }
        $qstring .= implode(",",$qstring_);
       if (mysql_query($qstring)) return mysql_insert_id();
       else return false;
    }
   public function get($keyvalue)
   {
       $qstring = "SELECT * from ".$this->table." where ";
       $qstring_ = array();
        foreach ($keyvalue as $key=>$value)
        {
            $qstring_[] .= "(`$key` = '".mysql_real_escape_string($value)."')";
        }
        $qstring .= implode(" AND ",$qstring_);
        $res = mysql_query($qstring);
        $ret = array();
        while ($row = mysql_fetch_assoc($res))
        {
            $ret[] = $row;
        }
        return $ret;
   }
   public function update($keyvalue, $wherestring)
   {
       $qstring = "update ".$this->table." set ";
        $qstring_ = array();
        foreach ($keyvalue as $key=>$value)
        {
            $qstring_[] .= "`$key` = '".mysql_real_escape_string($value)."'";
        }
        $qstring .= implode(",",$qstring_);
        $qstring .= " where $wherestring";
        mysql_query($qstring);
   }
   public function delete($wherestring)
   {
       mysql_query("update ".$this->table." set isdeleted = 1 where $wherestring") or  mysql_query("update ".$this->table." set is_deleted = 1 where $wherestring");
   }
}
?>
