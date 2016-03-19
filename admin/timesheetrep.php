<?php
$tdiv .= '<div id=cal1 style="float:left; position:relative; left:10px"><b> Start: <input name="startdate" id="startdate" type="text" size="10" maxlength="10" value="" style="width: 100px; font-size:9px; position:relative;">'; 
$tdiv .='<button id="trigger" style="background-image:url(calendar/calendar.png); position:relative;"  class="calbutton" onclick="calsetup(\'cal1\',\'startdate\')">.</button></div>';
$tdiv .= '<div id=cal2  style="float:left; position:relative; left:50px"><b> End: <input name="enddate" id="enddate" type="text" size="10" maxlength="10" value="" style="width: 100px; font-size:9px; position:relative;">'; 
$tdiv .='<button id="trigger2" style="background-image:url(calendar/calendar.png); position:relative;"  class="calbutton" onclick="calsetup(\'cal2\', \'enddate\')">.</button></div>';
$ageres = mysql_query("SELECT members.userid, memberdetails.afirst, memberdetails.alast from members left join memberdetails on members.userid = memberdetails.userid where usertype = 'user'");
while ($agrow = mysql_fetch_array($ageres))
	{
		$ops .= '<option value="'.$agrow['userid'].'">'.$agrow['afirst'].' '.$agrow['alast'].'</option>';
	}
$tdiv .= '<div style="clear:both; position:relative; top:20px; left:10px"> <b> Select Agent: </b><select style="width: 100px; font-size:9px;" name="agentid" id="agentid"><option value="all">All</option>'.$ops.'</select></div>';
$tdiv .= '<div style="position:relative; top:40px; left:10px"><a href="#" onclick="gettimesheet()">View</a> : <a href="#">Export</a>';
echo $tdiv;
echo "<div id=\"repcontent\"><div>";
