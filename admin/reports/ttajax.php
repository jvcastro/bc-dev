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

			$projectsres = mysql_query("SELECT projectid, projectname FROM projects WHERE bcid=$bcid ORDER BY projectname");
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
			$tteventsres = mysql_query("SELECT id, break FROM ttevents WHERE (bc_id=$bcid OR bc_id=0) ORDER BY id");

			echo "$('#ttreportsummary').html(''); ";

			$_tablecontent = "<thead><tr><th>Expand All</th><th>Campaign Hours</th>";

			if (!$tteventsres) die("Error[ttevents]: ".mysql_error());
			while($row=mysql_fetch_row($tteventsres))
			{
				$_tablecontent .= "<th>".$row[1]."</th>";
			}

			$_tablecontent .= "<th>Total Hours</th></tr></thead><tbody></tbody>";

			echo "$('#ttreportsummary').html('".$_tablecontent."'); ";


		exit;

		case 'loadreport':
			$_qry = 
			"
				CALL spttReportAgentTimesheetLog(
					$bcid
					,".$_REQUEST['_clientid']."
					,".$_REQUEST['_projectid']."
					,".$_REQUEST['_memberid']."
					,".$_REQUEST['_teamid']."
					,'".$_REQUEST['_start']."'
					,'".$_REQUEST['_end']."'
				)
			";

			$tteventslogres = mysql_query($_qry);

			if (!$tteventslogres) die("Error[tteventslog]: ".mysql_error());

			while ($row[] = mysql_fetch_array($tteventslogres, MYSQL_NUM));
			array_pop($row);
			// $row[] = array($_qry,$_REQUEST['_start'],$_REQUEST['_end']);

			echo '{ "aaData" : ' . json_encode($row) . ' }';
		exit;

		case 'loadreportsummary':
			$_qry =
			"
				CALL spttReportAgentTimesheetSummary(
					$bcid
					,".$_REQUEST['_clientid']."
					,".$_REQUEST['_projectid']."
					,".$_REQUEST['_memberid']."
					,".$_REQUEST['_teamid']."
					,'".$_REQUEST['_start']."'
					,'".$_REQUEST['_end']."'
				)
			";

			$ttagenttimesheetsummaryres = mysql_query($_qry);

			if (!$ttagenttimesheetsummaryres) die("Error[spttReportAgentTimesheetSummary]: ($_qry)".mysql_error());

			$rowno = 0;
			$finalrowno = 0;
			$newtblrowfinal = array();

			while ($rowno < mysql_num_rows($ttagenttimesheetsummaryres))
			{
				if (!$rowno)
					$row = mysql_fetch_array($ttagenttimesheetsummaryres, MYSQL_NUM); $rowno++;

				$i = 1;
				$curcategory = $row[0];
				$newtblrow = array();

				list($_agent,$_campaign) = split("-",$curcategory);
				if ( is_null($_campaign) )
				{
					$finalrowno++;
					$newtblrow["DT_RowId"] = "agent_".$finalrowno;
					$newtblrow["DT_RowClass"] = "agentname odd";
				}
				else
				{
					$newtblrow["DT_RowId"] = "agent_".$finalrowno;
					$newtblrow["DT_RowClass"] = "agentcampaign even";
				}

				$newtblrow[0] = $row[3];
				// $newtblrow[1] = $row[1] . "-" . $row[2];
				// $newtblrow[1] = $row[0];
				// $newtblrow[1] = '<campaign_hours>';
				// $newtblrow[$i] = $row[6] . "-" . $row[4];
				$newtblrow[$i] = $row[6];

				$row = mysql_fetch_array($ttagenttimesheetsummaryres, MYSQL_NUM); $rowno++;
				while ($curcategory == $row[0])
				{
					$i++;
					// $newtblrow[$i] = $row[6] . "-" . $row[4];
					$newtblrow[$i] = $row[6];
					$row = mysql_fetch_array($ttagenttimesheetsummaryres, MYSQL_NUM); $rowno++;
				}
				$newtblrowfinal[] = $newtblrow;
			}

			echo '{ "aaData" : ' . json_encode($newtblrowfinal) . ' }';
		exit;

	}
?>