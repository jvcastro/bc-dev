<?php
class providers extends dataTable {
       public function __construct() {
         
          $this->tablename = 'bc_providers';
          $this->idname = 'id';
       }
       
       public function findById($id)
    {
           $this->data = array();
        $res  = mysql_query("SELECT * from $this->tablename where id = '$id'");
        $row = mysql_fetch_assoc($res);
        foreach ($row as $r=>$v)
        {
            $this->data[$r] = $v;
        }
        return $row;
    }
        public static function getall($bcid = NULL)
        {
            if ($bcid == NULL) return false;
            $res = mysql_query("SELECT * from bc_providers where bcid = '$bcid' or id = 1");
            $providers = array();
            while ($row = mysql_fetch_assoc($res))
            {
                $providers[$row['id']] = $row;
            }
            return $providers;
        }
        public function findByName($name)
        {
           $this->data = array();
            $res  = mysql_query("SELECT * from $this->tablename where name = '$name'");
        $row = mysql_fetch_assoc($res);
        foreach ($row as $r=>$v)
        {
            $this->data[$r] = $v;
        }
        return $row;
        }
        
}
?>
