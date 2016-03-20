<?php
class lists {
    public static $tablename = 'lists';
    public static $idname = 'listid';
    public static function exclude($pr, $phone, $lstr)
    {
       $ret = array();
      if (strlen($phone) > 0)
      {
      mysql_query("update leads_done set dispo = 'DoNotCall', hopper = 1 where projectid = $pr and listid in ($lstr) and phone ='".$phone."'");
      mysql_query("update leads_raw set dispo = 'DoNotCall', hopper = 1 where listid in ($lstr) and phone = '".$phone."'");
      $ret["affected"] = mysql_affected_rows();
      
      mysql_query("delete from hopper where projectid = $pr and phone = '$phone'");
      $ret["removedfromhopper"] = mysql_affected_rows();
      
      }
      return $ret;
    }
    public static function updatebyphone($pr,$phone, $dispo, $lstr)
    {
       $affected = 0;
      if (strlen($phone) > 0)
      {
      mysql_query("update leads_done set dispo = '$dispo', hopper = 1 where projectid = $pr and listid in ($lstr) and phone = '".$phone."'");
      mysql_query("update leads_raw set dispo = '$dispo', hopper = 1 where listid in ($lstr) and phone = '".$phone."'");
      $affected = mysql_affected_rows();
      mysql_query("delete from hopper where projectid = $pr and phone = '$phone'"); 
      }
      return $affected;
    }
    public static function findbyListid($listid)
    {
        $res = mysql_query("SELECT * from lists where listid = $listid");
        $ret = mysql_fetch_assoc($res);
        return $ret;
    }
    public static function findbyLid($lid)
    {
        $res = mysql_query("SELECT * from lists where lid = $lid");
        $ret = mysql_fetch_assoc($res);
        return $ret;
    }
    public static function findbyProjectId($pid, $listidonly = false, $activeonly = true)
    {
        if ($activeonly) {
                $ac = 'and active = 1';
            }
        else $ac = '';
        if ($listidonly)
        {
            $res = mysql_query("SELECT listid from ".self::$tablename." where projects = '$pid' $ac and is_deleted = 0");
            while ($row = mysql_fetch_assoc($res))
            {
                $ret[] = $row['listid'];
            }
        }
        else 
        {
            $res = mysql_query("SELECT * from ".self::$tablename." where projects = '$pid' $ac and is_deleted = 0");
            while ($row = mysql_fetch_assoc($res))
            {
                $ret[] = $row;
            }
        }
        return $ret;
    }
    public static function findbyBcId($bcid, $listidonly = false)
    {
        if ($listidonly)
        {
            $res = mysql_query("SELECT listid from ".self::$tablename." where bcid = '$bcid' and active = 1");
            while ($row = mysql_fetch_assoc($res))
            {
                $ret[] = $row['listid'];
            }
            
        }
        else 
        {
            $res = mysql_query("SELECT * from ".self::$tablename." where bcid = '$bcid' and active = 1");
            while ($row = mysql_fetch_assoc($res))
            {
                $ret[] = $row;
            }
        return $ret;
        }
    }
    public static function listrecords($lid,$exfields = NULL)
    {
        $records = array();
        $leadids = array();
        $cdata = array();
        $sdata = array();
        $l = lists::findbyLid($lid);
        $listid = $l['listid'];
        if ($exfields == NULL)
        {
            $qf = "*";
        }
        else {
            $q = implode(",",$exfields);
            $qf = "leadid,$q";
            
        }
         $res = mysql_query("SELECT $qf from leads_raw where listid = '$listid'") or die(mysql_error());
        while ($row = mysql_fetch_assoc($res))
        {
            $records[$row['leadid']] = $row;
            $leadids[$row['leadid']] = $row['leadid'];
        }
        unset($res);
        $res2 = mysql_query("SELECT $qf from leads_done where listid = '$listid'");
        while ($row = mysql_fetch_assoc($res2))
        {
            foreach ($row as $field=>$value)
            {
                if (strlen($records[$row['leadid']][$field]) < strlen($value) || !$records[$row['leadid']][$field])
                {
                    $records[$row['leadid']][$field] = $value;
                    
                    }
            }
        }
        unset($res2);
        $leadidlist = implode(",",$leadids);
       $res3 = mysql_query("SELECT * from leads_custom_fields where leadid in ($leadidlist)");
       if ($res3)
       {
       while ($row = mysql_fetch_assoc($res3))
       {
           $cdata[$row['leadid']] = $row;
       }
       }
       unset($res3);
       $res4 = mysql_query("SELECT * from scriptdata where leadid in ($leadidlist)");
       if ($res4)
       {
       while ($row = mysql_fetch_assoc($res4))
       {
           $sdata[$row['leadid']] = $row;
       }
       }
       unset($res4);
       
       /*$dateres = mysql_query("SELECT * from dateandtime where leadid in ($leadidlist)");
        while ($daterow = mysql_fetch_assoc($dateres))
        {
            $records[$daterow['leadid']]['DateSet'] = $daterow['dtime'];
        }*/
       $ret["records"] = $records;
       $ret["cdata"] = $cdata;
       $ret["sdata"] = $sdata;
        return $ret;
    }
    public static function searchrecords($bcid,$projectid,$disposition,$userid, $start, $end, $exfields = NULL)
    {
        $records = array();
        $leadids = array();
        $cdata = array();
        $sdata = array();
        if ($exfields == NULL)
        {
            $qf = "*";
        }
        else {
            $q = implode(",",$exfields);
            $qf = "leadid,$q";
            
        }
        if ($projectid == 'all')
        {
            $projs = array();
            $pidres = mysql_query("SELECT projectid from projects where bcid = '$bcid'");
            while ($pidrow = mysql_fetch_assoc($pidres))
            {
                $projs[] = $pidrow['projectid'];
            }
            $projectid = implode(",",$projs);
            unset($projs);
        }
        $dispoq = '';
        if ($disposition != 'all')
        {
            $dispoq = "and dispo = '$disposition'";
        }
        $userq = '';
        if ($userid != 'all')
        {
            $userq = "and assigned = '$userid'";
        }
         $res = mysql_query("SELECT $qf, projectid, assigned from leads_done where projectid in ($projectid) and epoch_timeofcall >= '".strtotime($start)."' and epoch_timeofcall <= '".strtotime($end." 23:59:59")."' $userq $dispoq") or die(mysql_error());
        while ($row = mysql_fetch_assoc($res))
        {
            $records[$row['leadid']] = $row;
            $leadids[$row['leadid']] = $row['leadid'];
        }
        unset($res);
        $res2 = mysql_query("SELECT $qf from leads_raw where leadid in (".implode(',',$leadids).")");
        while ($row = mysql_fetch_assoc($res2))
        {
            foreach ($row as $field=>$value)
            {
                if (strlen($records[$row['leadid']][$field]) < 1 || !$records[$row['leadid']][$field])
                {
                    $records[$row['leadid']][$field] = $value;
                    
                    }
            }
        }
        unset($res2);
        $dateres = mysql_query("SELECT * from dateandtime where leadid in (".implode(',',$leadids).")");
        while ($daterow = mysql_fetch_assoc($dateres))
        {
            $records[$daterow['leadid']]['DateSet'] = $daterow['dtime'];
        }
        $leadidlist = implode(",",$leadids);
       $res3 = mysql_query("SELECT * from leads_custom_fields where leadid in ($leadidlist)");
       if ($res3)
       {
       while ($row = mysql_fetch_assoc($res3))
       {
           $cdata[$row['leadid']] = $row;
       }
       }
       unset($res3);
       $res4 = mysql_query("SELECT * from scriptdata where leadid in ($leadidlist)");
       if ($res4)
       {
       while ($row = mysql_fetch_assoc($res4))
       {
           $sdata[$row['leadid']] = $row;
       }
       }
       unset($res4);
       $res5 = mysql_query("SELECT * from leads_notes where leadid in ($leadidlist)");
       if ($res5)
       {
       while ($row = mysql_fetch_assoc($res5))
       {
           $ndata[$row['leadid']] = $row;
       }
       }
       unset($res5);
       $ret["records"] = $records;
       $ret["cdata"] = $cdata;
       $ret["sdata"] = $sdata;
      $ret["ndata"] = $ndata;
        return $ret;
    }
}
?>
