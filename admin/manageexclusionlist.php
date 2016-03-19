<?php
$projectnames = projects::projectnames($bcid);
    
    $cores = mysql_query("SELECT * from lists_exclusion where bcid = $bcid");
    while ($row = mysql_fetch_assoc($cores))
    {
        $ex[$row['id']] = $row;
        $exlist[$row['id']] = "'".$row['id']."'";
    }
    $excount = mysql_query("SELECT exclusionid, count(*) as excount from lists_exclusion_data where exclusionid in (".implode(",",$exlist).") group by exclusionid");
    while ($exrow = mysql_fetch_assoc($excount))
    {
        $ecount[$exrow['exclusionid']] = $exrow['excount'];
    }
    foreach ($ex as $row)
    {
        $rows[$row['id']][1] = $projectnames[$row['projectid']];
        $rows[$row['id']][2] = $row['exclusion_name'];
        $rows[$row['id']][3] = date("Y-m-d",$row['date_created']);
        $rows[$row['id']][4] = $ecount[$row['id']];
        $rows[$row['id']][5] = '<a href="admin.php?act=export&type=exclusion&id='.$row['id'].'">Export</a> | <a href="#" onclick="removeexclusion(\''.$row['id'].'\')">Remove</a>';
    }
    $headers = array('Campaign','Exclusion Name','Date Added','Records','Actions');
    echo '<div class="entryform" style="width:100%;">
<a href="#" class="jbut" onclick="dialogwindow(\'newexclusionlist\')">Add New</a>                
<div id="respmessage"></div><div>';
    echo tablegen($headers,$rows,'100%',NULL,'dataTables_wrapper');
    echo '</div>';
?>
