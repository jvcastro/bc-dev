<?php
include "../dbconnect.php";
$act = $_REQUEST['act'];
if ($act == 'getlive')
	{
		$ret = array();
		$res = mysql_query("SELECT * from liveagents");
		while ($r = mysql_fetch_row($res))
			{
				$ret[] = implode("|",$r);				
			}
		$returner = implode(",",$ret);
		echo $returner;
		exit;
	}
?>
<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="ext/resources/css/xtheme-gray.css" />
<script type="text/javascript" src="ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="ext/ext-all.js"></script>
<div id="liveagents">
</div>
<script>
function getlive()
{
	var http = getHTTPObject();
	http.open("GET", "live.php?act=getlive", true);
	http.onreadystatechange = function(){
		resp = http.responseText;
		var retarr = new Array();
		var dt = resp.split(",");
		for(i=0; i<dt.length; i++) {
			var temparr = dt[i].split("|");
			for(b=0; b<temparr.length; b++) {retarr[i][b] = temparr[b];}
		}
		document.write(typeof retarr);
	}
	http.send(null);
 	
}
function loadlive(dt) 
{
// create the data store
    var store = new Ext.data.SimpleStore({
        fields: [
			{name: 'userid'},
           {name: 'extension'},
           {name: 'status'},
           {name: 'afirst'},
           {name: 'alast'},
           {name: 'projectid'},
		   {name: 'projectname'}
        ]
    });
    store.loadData(dt);

    // create the Grid
    var grid = new Ext.grid.GridPanel({
        store: store,
        columns: [
            {id:'status',header: "Status", width: 160, sortable: true, dataIndex: 'status'},
            {header: "Userid", width: 75, sortable: true, dataIndex: 'userid'},
            {header: "Agent", width: 75, sortable: true, dataIndex: 'afirst'},
            {header: "Name", width: 75, sortable: true, dataIndex: 'alast'},
            {header: "ProjectID", width: 75, sortable: true, dataIndex: 'projectid'},
			{id: 'projectname',header: "Project", width: 75, sortable: true, dataIndex: 'projectname'}
        ],
        stripeRows: true,
        autoExpandColumn: 'projectname',
        height:350,
        width:600,
        title:'Live Agents'
    });

    grid.render('liveagents');

}
function getHTTPObject() {
  var xmlhttp =false;

  if (window.XMLHttpRequest){
    xmlhttp = new XMLHttpRequest();
  }
  else if (window.ActiveXObject){
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    if (!xmlhttp){
        xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
   
}
  return xmlhttp;
}
</script>