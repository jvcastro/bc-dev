<?php
/**
 * Description of callman
 *
 * @author Obrifs@gmail.com
 */
class callman {
    public static function create_originate($record,$project){
        global $bcid;
        mysql_query("INSERT into callman set leadid = '".$record->leadid."', phone = '".$record->phone."', status = 'originate', projectid ='".$project['projectid']."', prefix = '".$project['prefix']."', start = '".time()."', mode = '1', bcid='".$bcid."', region ='".$project['region']."';");
        return true;
        
    }
    public static function getbyleadid($leadid)
    {
        $res = mysql_query("SELECT * from callman where leadid = $leadid limit 1");
        return mysql_fetch_assoc($res);
    }
}

?>
