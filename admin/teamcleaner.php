<?php
require "../dbconnect.php";
$res = mysql_query("SELECT phone,leadid from leads_raw where listid = 'reflist'");
while ($row = mysql_fetch_assoc($res))
{
    $cfres = mysql_query("SELECT * from leads_custom_fields where leadid = '".$row['leadid']."'");
    $cfrow = mysql_fetch_assoc($cfres);
    $lres = mysql_query("SELECT leadid from leads_raw where phone = '".$row['phone']."' and listid ='Promo 82 Outbound'");
    $lrow = mysql_fetch_assoc($lres);
    mysql_query("UPDATE leads_custom_fields set customfields = '".$cfrow['customfields']."' where leadid = '".$lrow['leadid']."'");
}

?>
