<?php
class projects extends dataTable {
       public function __construct() {
         
          $this->tablename = 'projects';
          $this->idname = 'projectid';
       }
       
    public function findById($projectid)
    {
        $res  = mysql_query("SELECT * from $this->tablename where projectid = '$projectid'");
        $row = mysql_fetch_assoc($res);
        return $row;
    }
    
    public static function getbyid($projectid)
    {
        $p = new projects();
        return $p->findById($projectid);
    }
        public static function getprojectname($projectid)
        {
            $res  = mysql_query("SELECT * from projects where projectid = '$projectid'");
        $row = mysql_fetch_assoc($res);
        return $row['projectname'];
        }
    public static function projectclients($bcid)
    {
        $ret = array();
        $res = mysql_query("SELECT projectid,clientid from projects where bcid = '$bcid'");
        while ($row = mysql_fetch_assoc($res))
        {
            $ret[$row['projectid']] = $row['clientid'];
        }
        return $ret;
    }
    public static function projectclient($bcid,$pid)
    {
        $ret = array();
        $res = mysql_query("SELECT projects.clientid, clients.* from projects left join clients on "
                . "projects.clientid = clients.clientid where projects.bcid = '$bcid' and projects.projectid = '$pid'");
        $row = mysql_fetch_assoc($res);
        
        return $row;
    }
    public static function projectteams($projectid)
    {
        $res = mysql_query("SELECT * from teams where projects like '%$projectid%'");
        while ($row = mysql_fetch_assoc($res))
        {
            $rproj = explode(";",$row['projects']);
            if (in_array($projectid,$rproj))
            {
                $ret[$row['teamid']] = $row['teamid'];
                $ret2[$row['teamid']] =  $row['teamname'];
            }
        }
        $return['ids'] = $ret;
        $return['names'] = $ret2;
        return $return;
    }
    public static function projectnames($bcid)
    {
        $ret = array();
        $res = mysql_query("SELECT projectid,projectname,active from projects where bcid = '$bcid'");
        while ($row = mysql_fetch_assoc($res))
        {
            if ($row['active'] == 1)
                $ret[$row['projectid']] = $row['projectname'];
            else
                $ret[$row['projectid']] = $row['projectname'] . " (DEACTIVATED)";

        }
        return $ret;
    }
    public static function clonecamp($from, $to)
    {
        $resp = mysql_query("SELECT * from projects where projectid = '$from' ");
        $proj = mysql_fetch_assoc($resp);
        $items = array();
            foreach ($proj as $key=>$value)
            {
                if ($key != 'projectid' && $key != 'projectname' && $key != 'projectdesc' && $key != 'providerid' && $key != 'datecreated' && $key!= 'lastactive')
                {
                   
                    $items[] = " $key = '".mysql_real_escape_string($value)."'";
                }
            }
            $qs = implode(",",$items);
            mysql_query("update projects set $qs where projectid = $to");
        //copy statuses
        $res = mysql_query("SELECT * from statuses where projectid = '$from' ");
        while ($row = mysql_fetch_assoc($res))
        {
            $statuses[$row['statusid']] = $row;
        }
        foreach ($statuses as $status)
        {
            $items = array();
            foreach ($status as $key=>$value)
            {
                if ($key != 'statusid')
                {
                    if ($key == 'projectid') $value = $to;
                    $items[] = " $key = '".mysql_real_escape_string($value)."'";
                }
            }
            $qs = implode(",",$items);
            mysql_query("Insert into statuses set $qs");
        }
        //copy templates
        $rest = mysql_query("SELECT * from templates where projectid = '$from' ");
        while ($row = mysql_fetch_assoc($rest))
        {
            $templates[$row['templateid']] = $row;
        }
        foreach ($templates as $template)
        {
            $items = array();
            foreach ($template as $key=>$value)
            {
                if ($key != 'templateid')
                {
                    if ($key == 'projectid') $value = $to;
                    $items[] = " $key = '".mysql_real_escape_string($value)."'";
                }
            }
            $qs = implode(",",$items);
            mysql_query("Insert into templates set $qs");
        }
        //copy callscript
        $rest = mysql_query("SELECT * from scripts where projectid = '$from' ");
        while ($row = mysql_fetch_assoc($rest))
        {
            $scripts[$row['scriptid']] = $row;
        }
        foreach ($scripts as $script)
        {
            $items = array();
            foreach ($script as $key=>$value)
            {
                if ($key != 'scriptid')
                {
                    if ($key == 'projectid') $value = $to;
                    $items[] = " $key = '".mysql_real_escape_string($value)."'";
                }
            }
            $qs = implode(",",$items);
            mysql_query("Insert into scripts set $qs");
        }
    }
}
?>