<?php
class features extends dataTable {
    public function __construct($bcid) {
        $this->tablename = 'bc_features';
          $this->idname = 'bcid';
          $this->findById($bcid);
    }
     public function findById($id)
    {
           $this->data = array();
        $res  = mysql_query("SELECT * from $this->tablename where bcid = '$id'");
        $row = mysql_fetch_assoc($res);
        foreach ($row as $r=>$v)
        {
            $this->data[$r] = $v;
        }
        return $row;
    }
}
?>
