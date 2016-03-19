<?php
	$act = $_REQUEST['act'];
	switch($act)
	{
		case 'loadrepfilters':
			$clientsres = mysql_query("SELECT clientid, company FROM clients WHERE bcid=$bcid ORDER BY company");
			if (!$clientsres) die("Error[clients]: ".mysql_error());
			while($row=mysql_fetch_row($clientsres))
			{
				echo "$('#clients').append(\"<option value=".$row[0].">".$row[1]."</option>\");";
			}

			$projectsres = mysql_query("SELECT projectid, projectname FROM projects WHERE bcid=$bcid and dialmode='inbound' ORDER BY projectname");
			if (!$projectsres) die("Error[projects]: ".mysql_error());
			while($row=mysql_fetch_row($projectsres))
			{
				echo "$('#projects').append(\"<option value=".$row[0].">".$row[1]."</option>\");";
			}

			$_qry = "SELECT a.userid, CONCAT(b.alast, ', ', b.afirst) AS name FROM members a CROSS JOIN memberdetails b ON a.userid=b.userid CROSS JOIN roles c ON a.roleid=c.roleid WHERE a.bcid=$bcid and c.rolename='Agent' ORDER BY name";
			$membersres = mysql_query($_qry);
			if (!$membersres) die("Error[members]: ".mysql_error().$_qry);
			while($row=mysql_fetch_row($membersres))
			{
				echo "$('#members').append(\"<option value=".$row[0].">".$row[1]."</option>\");";
			}

			$_qry = "SELECT teamid, teamname FROM teams WHERE bcid=$bcid ORDER BY teamname";
			$teamsres = mysql_query($_qry);
			if (!$teamsres) die("Error[teams]: ".mysql_error().$_qry);
			while($row=mysql_fetch_row($teamsres))
			{
				echo "$('#teams').append(\"<option value=".$row[0].">".$row[1]."</option>\");";
			}

		exit;

		case 'loadrepsummarycols':

			$_tablecontent = "<thead> <tr> <th>Date<th></th> <th>Calls Offered</th> <th>Calls Answered</th> <th>Average Handle Time (m:s)</th> <th>ASA (sec)</th> <th>SL%</th> <th>ABA%</th> </tr> </thead> <tbody> </tbody>";
			echo "$('#ttreportsummary').html('".$_tablecontent."'); ";

		exit;

		case 'loadreport':

			$_qry = 
			"
				CALL spReportInboundLog(
					$bcid
					,0
					,".$_REQUEST['_projectid']."
					,0
					,0
					,'".$_REQUEST['_start']."'
					,'".$_REQUEST['_end']."'
				)
			";

			$inboundlog_res = mysql_query($_qry);

			if (!$inboundlog_res) die("Error[inboundlog_res]: ".mysql_error());

			while ($row[] = mysql_fetch_array($inboundlog_res, MYSQL_NUM));
			array_pop($row);

			echo '{ "aaData" : ' . json_encode($row) . ' }';
		exit;

		case 'loadreportsummary':

			$_qry =
			"
				CALL spReportInboundSummary(
					$bcid
					,0
					,".$_REQUEST['_projectid']."
					,0
					,0
					,'".$_REQUEST['_start']."'
					,'".$_REQUEST['_end']."'
				)
			";

			$inboundsummary_res = mysql_query($_qry);

			if (!$inboundsummary_res) die("Error[inboundsummary_res]: ".mysql_error());

			while ($row[] = mysql_fetch_array($inboundsummary_res, MYSQL_NUM));
			array_pop($row);

			echo '{ "aaData" : ' . json_encode($row) . ' }';
		exit;

	}
?>