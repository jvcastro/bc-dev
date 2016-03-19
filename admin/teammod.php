<?php
require_once "../dbconnect.php";
$res = mysql_query("SELECT * from bc_clients");
while ($row = mysql_fetch_assoc($res))
{
    $clients[$row['bcid']] = $row;
}

foreach ($clients as $client)
{
    $teams = array();
    $cres = mysql_query("SELECT * from teams where bcid = '".$client['bcid']."'");
    while ($crow = mysql_fetch_assoc($cres))
    {
        $teams[$crow['teamname']] = $crow['teamid'];
    }
    $ures = mysql_query("SELECT members.*, memberdetails.team FROM members LEFT JOIN memberdetails ON members.userid = memberdetails.userid WHERE members.bcid = '".$client['bcid']."'");
    while ($row = mysql_fetch_assoc($ures))
    {
        $newteams = array();
        $uteams = explode(";",$row['team']);
        foreach ($uteams as $t)
        {
            if ($teams[$t])
            {
                $newteams[$teams[$t]] = $teams[$t];
            }
        }
        $newt = array();
        foreach ($newteams as $nt)
        {
            $newt[] = $nt;
        }
        $nt = json_encode($newt);
        mysql_query("UPDATE memberdetails set team = '".$nt."' where userid = '".$row['userid']."'");
        
    }
    
}
    ?>
