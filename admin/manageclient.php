<?php
$crres = mysql_query("SELECT clientid,count(*) as count from reports group by clientid");
while ($crrow = mysql_fetch_assoc($crres))
{
    $clientrepcount[$crrow['clientid']] = $crrow['count'];
}
$agentres = mysql_query("SELECT clientid, company, companyurl, city, state,phone, active_state from clients where bcid = '$bcid'");

echo '
<div id="ACampaignsLeftNavigation">        
<div class="apptitle">Manage Clients</div>
<div class="secnav">
                        <input type="button" onclick="dialogwindow(\'newclient\')" value="Add Client" />
</div>
</div>

<table width="100%" id="adminClientList" style="background-color:#FFFFFF;">
<thead>

<tr>
<td class=tableheadercenter>Company</td>
<td class=tableheadercenter>Status</td>
<td class=tableheadercenter>Location</td>
<td class=tableheadercenter>Website</td>
<td class=tableheadercenter>Phone</td>
<td class=tableheadercenter>Client Reports</td>
<td class=tableheadercenter>Option</td>
</tr></thead>';
while ($row = mysql_fetch_array($agentres))
        {
        if (strlen($row['city']) < 1 && strlen($row['state']) < 1)
                        {
                                $loca = '';
                        }
        elseif (strlen($row['state']) < 1)
                        {
                                $loca = $row['city'];
                        }
        elseif (strlen($row['city']) < 1)
                        {
                                $loca = $row['state'];
                        }
        else {
                $loca = $row['city'].", ".$row['state'];
        }
        $status = $row['active_state'] == 1 ? "Active":"Inactive";
        $cr = $clientrepcount[$row['clientid']] > 0 ? '<a href="#" onclick="manreports(\''.$row['clientid'].'\')">'.$clientrepcount[$row['clientid']].' Report(s) generated</a>':'0 Reports generated';
        $coption = '<a onClick="deactivateClient('.$row['clientid'].'); return false;" href="#">Deactivate</a>';
        if ($row['active_state'] == 0) $coption = '<a onClick="activateClient('.$row['clientid'].'); return false;" href="#">Activate</a>';
        echo '<tr class="active-'.$row['clientid'].'">';
        echo '<td >
            <a href="#" onclick="clientdetails(\''.$row['clientid'].'\')">'.$row['company'].'</a></td>
            <td>'.$status.'</td><td class=datas>'.$loca.'</td>
                <td>'.$row['companyurl'].'</td>

                <td >'.$row['phone'].'</td>
                    <td >'.$cr.'</td>
                     <td >'.$coption.' | <a onClick="deleteClient('.$row['clientid'].'); return false;" href="#">Delete</a>';

        echo '</tr>';

        }
?>
