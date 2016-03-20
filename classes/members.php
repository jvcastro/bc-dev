<?php
class members extends dataTable {
       public function __construct($userid = NULL) {
         
          $this->tablename = 'members';
          $this->idname = 'userid';
          if ($userid != NULL)
          {
              $use = $this->findById($userid);
              $user = mysql_fetch_assoc($use);
              $this->data = $user;
          }
       }
       public static function getalldetails()
       {
           global $bcid;
           $res = mysql_query("SELECT *, memberdetails.afirst, memberdetails.alast, memberdetails.team from members left join memberdetails on members.userid = memberdetails.userid where bcid = $bcid and active = 1 AND alast <> '' AND afirst <> '' ORDER BY alast,afirst,userlogin");
           while ($row = mysql_fetch_assoc($res))
           {
               $ret[$row['userid']] = $row;
           }
           return $ret;
       }
       public static function getmembersbyteamid($teamid)
       {
           global $bcid;
           $res = mysql_query("SELECT *, memberdetails.afirst, memberdetails.alast, memberdetails.team from members left join memberdetails on members.userid = memberdetails.userid where bcid = $bcid and active = 1 AND alast <> '' AND afirst <> '' ORDER BY alast,afirst,userlogin");
           while ($row = mysql_fetch_assoc($res))
           {
               $teams = json_decode($row['team'],true);
               if (in_array($teamid,$teams))
               {
               $ret[$row['userid']] = $row;
               }
           }
           return $ret;
       }
       public static function getallmemberdetails()
       {
           global $bcid;
           $res = mysql_query("SELECT *, memberdetails.afirst, memberdetails.alast, memberdetails.team from members left join memberdetails on members.userid = memberdetails.userid where bcid = $bcid AND alast <> '' AND afirst <> '' ORDER BY alast,afirst,userlogin");
           while ($row = mysql_fetch_assoc($res))
           {
               $ret[$row['userid']] = $row;
               
           }
           return $ret;
       }
       public function checkMember($userlogin)
       {
           $result = $this->findByLogin($userlogin);
           $count = mysql_num_rows($result);
           if ($count != 0)
           {
               return TRUE;
           }
           else return FALSE;
       }
       public function findByLogin($userlogin)
    {
        $res  = mysql_query("SELECT * from $this->tablename where userlogin = '$userlogin'");
        return $res;
    }
        public static function clearInactive($agents)
        {
            $now = time();
            foreach ($agents as $agent)
            {
                $last = $now - $agent['lastactivity'];
                if ($last > 600)
                {
                    members::disconnect($agent['userid']);
                }
                    
            }
        }
        public static function disconnect($userid)
        {
            $u = $userid;
		$ses = mysql_query("SELECT * from agent_sessions where userid = '$u'");
		$srow = mysql_fetch_row($ses);
		$tsid = $srow['sessionid'];
		$oldres = mysql_query("SELECT leadid,lastactivity,actionid from liveusers where userid = '$u'");
		$old = mysql_fetch_assoc($oldres);
		mysql_query("delete from liveusers where userid = '$u'");
		$t = $oldres['lastactivity'];
		mysql_query("update actionlog set epochend = '$t' where userid = '$u' and logid = '".$old['actionid']."'");
		
		mysql_query("delete from sessions where sessionid = '$tsid'");
        }
}
?>
