<?php
$projectnames = projects::projectnames($bcid);
    
    $cores = mysql_query("SELECT * from disposition_update_history where bcid = $bcid order by id DESC");
    while ($row = mysql_fetch_assoc($cores))
    {
        $ex[$row['id']] = $row;
        $exlist[$row['id']] = "'".$row['id']."'";
    }

    foreach ($ex as $row)
    {
        $rows[$row['id']][1] = $projectnames[$row['projectid']];
        $rows[$row['id']][2] = $row['records_total'];
        $rows[$row['id']][3] = $row['records_updated'];
        $rows[$row['id']][4] = date("Y-m-d H:i:s",$row['date_epoch']);
        $rows[$row['id']][5] = '<a href="#" onclick="removedispoupdate(\''.$row['id'].'\')">Remove</a>';
    
    }
    $headers = array('Campaign','Records Searched','Records Affected','Date','Action');
    echo '<div class="entryform" style="width:100%;">
<a href="#" class="jbut" onclick="dialogwindow(\'listupdatefile\')">New Update</a>                
<div id="respmessage"></div><div>';
    echo tablegen($headers,$rows,'100%',NULL,'dataTables_wrapper');
    echo '</div>';
?>
