<?php
$lsearch = $_GET['listsearch'];

$listquery = "SELECT * from lists where bcid = '$bcid' AND is_deleted = 0";
$clients = new clients($bcid);

$listquery .= " order by `active` DESC, listid ASC";
$listres = mysql_query($listquery);
while ($listrow = mysql_fetch_array($listres))
	{
	$ind = $listrow['lid'];
	$lists[$ind] = $listrow;
	}
$projres = mysql_query("SELECT * from projects");
while ($projrow = mysql_fetch_array($projres))
	{
		$projects[$projrow['projectid']] = $projrow;
	}
$ld .= '<thead><tr>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'List ID';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'Description';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'Date Created';
	$ld .= '</Th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'Campaign';
	$ld .= '</th>';
    $ld .= '<th class="tableheadercenter">';
	$ld .= 'Client';
	$ld .= '</th>';
	$ld .= '<th class="tableheadercenter">';
	$ld .= 'is Active';
	$ld .= '</th><th class="tableheadercenter"></th><th class="tableheadercenter">act</th>';
	$ld .= '</tr>
	</thead>';
        $ld .= '<tbody>';
foreach($lists as $list)
	{
	if ($list['active'] == 1)
		{
		$active = 'YES';
		$color = ' style="color:#0000FF" ';
		}
	if ($list['active'] == 0)
		{
		$active = 'NO';
		$color = ' style="color:#666666" '; 
		}
	$ld .= '<tr class="li-'.$list['lid'].'">';
	$ld .= '<td class="datas" '.$color.'>';
	$ld .= $list['listid'];
	$ld .= '</td>';
	$ld .= '<td class="datas">';
	$ld .= $list['listdescription'];
	$ld .= '</td>';
	$ld .= '<Td class="datas">';
	$ld .= $list['datecreated'];
	$ld .= '</Td>';
	$ld .= '<td class="datas"><div id="projects'.$list['lid'].'">';
	$ld .= '<span title="'.$projects[$list['projects']]['projectname'].'">'.substr($projects[$list['projects']]['projectname'],0,20).'</span>';
	$ld .= '</div></td>';
        $ld .= '<td class="datas"><span title="'.$clients->getclientname($projects[$list['projects']]["clientid"]).'">'.substr($clients->getclientname($projects[$list['projects']]["clientid"]),0,20).'</span></td>';
	$ld .= '<td class="datas"><select name="active" id="active'.$list['lid'].'" onchange=togglelist(\''.$list['lid'].'\')>';
	$nosel = $list['active'] == 0 ? "Selected":"";
        $ld .= '<option value="1">Yes</option><option value="0" '.$nosel.'>No</option>';
        $ld .= '</select></td><td class="datas"><a href="#" onclick="listhistory(\''.$list['lid'].'\')">History</a> | <a href="admin.php?act=export&type=list&id='.$list['lid'].'">Export</a> |';
	$ld .= ' <a href="#" onclick="setListDeleted('.$list['lid'].'); return false;">Delete</a></td>';
        $ld .= '<td class="datas">'.$active.'</td>';
            $led .= '</tr>';
        
	}
        $ld .= '</tbody>'
?>
<div id="ACampaignsLeftNavigation">
    <div class="apptitle">Manage Lists</div>
<div class="secnav"></div>
<div style="clear:both"></div>
<div><ul>
<li id="managelist" class="activeMenu" onclick="listMenu('managelist')">
<a class="manageListMenu" href="#">List Overview</a></li>
<li id="manageexclusion" onclick="listMenu('manageexclusion')"><a class="manageListMenu" href="#">Manage Exclusion Lists</a></li>
<li id="dispoupdate" onclick="listMenu('dispoupdate')"><a class="manageListMenu" href="#">Disposition Update</a></li>

</div>
</div>
<div id="managelistresult">
    <a href="#" class="jbut" onclick="dialogwindow('newlist')">Add New</a>
<table width="100%" id="mangelistTable">


<?=$ld;?>
</table>
</div>
<script>
	var mangelistTable = $("#mangelistTable").dataTable({
		"aLengthMenu": [ 20, 50, 100, 150],
		 'iDisplayLength': 20,
                 "aaSorting": [[ 2, "desc" ]],
                 "aoColumns": [ null, null, null, null, null, null, null,{ "bVisible":    false }]
	});
	$("#mangelistTable_filter input").hide();
	$("#mangelistTable_filter").append("<select id=\"mangelistTableStatus\"><option value=\"\">All</option><option value=\"YES\" selected=\"selected\">Active</option><option value=\"NO\">Inactive</option></select>");
	$("#mangelistTableStatus").change(function(){
		var selectVal = $(this).val();
		
	    mangelistTable.fnFilter( selectVal, 7, false, false, false, false );
	});
         mangelistTable.fnFilter( 'YES', 7, false, false, false, false );
</script>