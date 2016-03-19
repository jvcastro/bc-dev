<?php
class duplicatecheck {
    public $checklist = array();
    public $projectid = NULL;
    public $listid = NULL;
    public $bcid = NULL;
    private $data = array();
    public function __construct() {
        $this->checklist = leadsloadersettings::duplicatecheck(false);
    }
    public function preprop($type)
    {
        if ($type == 'project')
        {
            $lists = lists::findbyProjectId($this->projectid,true, false);
            foreach ($lists as $l)
            {
                $dlists[] = "'".$l."'";
            }
            $qlist = implode(",",$dlists);
            //var_dump($qlist);
            $fields = implode(",",leadsloadersettings::duplicatecheck());
            
            $q = "SELECT $fields from leads_raw where listid in ($qlist)";
            $res = mysql_query($q);
            //echo $q;
            while ($row = mysql_fetch_assoc($res))
            {
                foreach ($row as $col=>$value)
                {
                    if (strlen($this->data[$col]) < 1) { $this->data[$col] = '*start*';}
                    $this->data[$col] .= "-".$value."-";
                }
            }
        }
        if ($type == 'listonly')
        {
            
        }
        if ($type == 'all')
        {
            $lists = lists::findbyBcId($this->bcid,true);
            $qlist = implode(",",$lists);
             $fields = implode(",",leadsloadersettings::duplicatecheck());
            
            $q = "SELECT $fields from leads_raw where listid in ($qlists)";
            $res = mysql_query($q);
            while ($row = mysql_query($res))
            {
                foreach ($row as $col=>$value)
                {
                    if (strlen($this->data[$col]) < 1) { $this->data[$col] = '*start*';}
                    $this->data[$col].="-".$value."-";
                }
            }
        }
    }
    public function dupcheck($field,$value)
    {
        if ($this->checklist[$field] == 'plain')
        {
        //return in_array($value,$this->data[$field]);
            return strpos($this->data[$field],$value);
            
        }
    }
    public function addin($field,$value)
    {
        //$this->data[$field][] = $value;
        $this->data[$field] .= $value;
    }
}
?>
