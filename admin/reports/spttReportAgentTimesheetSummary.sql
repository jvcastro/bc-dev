-- "sAjaxSource": "?act=loadreportsummary"+"&_clientid="+_clientid+"&_projectid="+_projectid+"&_memberid="+_memberid+"&_start="+_start+"&_end="+_end
DROP PROCEDURE IF EXISTS `spttReportAgentTimeSheetSummary`;
CREATE PROCEDURE `spttReportAgentTimeSheetSummary` (
	_bc_id int unsigned, 
	_clientid int unsigned, 
	_projectid int unsigned, 
	_memberid int unsigned,
	_teamid int unsigned,
	_start timestamp,
	_end timestamp
)
BEGIN

-- BREAKS
	CREATE TEMPORARY TABLE IF NOT EXISTS tmpttreportsum
	(
		ttevents_id bigint(7) unsigned NULL,
		clientid int(7) unsigned DEFAULT NULL,
		projectid int(7) unsigned DEFAULT NULL,
		projectname varchar(100) DEFAULT NULL,
		company varchar(100) DEFAULT NULL,
		userlogin varchar(50) DEFAULT NULL,
		seconds bigint(7) unsigned NULL,
		user_id int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL,
		KEY (`user_id`)

	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmpttreportsumXttevents
	(
		projectid int(7) unsigned DEFAULT NULL,
		clientid int(7) unsigned DEFAULT NULL,
		userlogin varchar(50) DEFAULT NULL,
		id bigint(7) unsigned NULL,
		break varchar(20) DEFAULT NULL,
		projectname varchar(100) DEFAULT NULL,
		user_id int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmpttreportsumfinal
	(
		category varchar(256) DEFAULT NULL,
		projectid int(7) unsigned DEFAULT NULL,
		clientid int(7) unsigned DEFAULT NULL,
		userlogin varchar(50) DEFAULT NULL,
		id bigint(7) unsigned NULL,
		break varchar(20) DEFAULT NULL,
		seconds bigint(7) unsigned NULL,
		user_id int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Initial records for everyone in members under the same bcid of admin user so for report to always display something

	SET @_qry2 = CONCAT(
		"
		INSERT INTO tmpttreportsum
		(ttevents_id,clientid,projectid,projectname,company,userlogin,seconds,user_id,team)
		SELECT
			DISTINCT
			0 as ttevents_id,
			e.clientid,
			d.projectid,
			d.projectname,
			e.company,
			CONCAT(b.alast, ', ', b.afirst) as agentname,
			0 as seconds,
			a.userid,
			b.team
		FROM members a
			CROSS JOIN memberdetails b ON a.userid = b.userid
			CROSS JOIN finalhistory c ON a.userid = c.userid
			CROSS JOIN projects d ON c.projectid = d.projectid
			CROSS JOIN clients e ON d.clientid = e.clientid
		WHERE a.bcid =", _bc_id 
			, IF(_clientid > 0, CONCAT(" AND e.clientid = ",_clientid), "")
			, IF(_projectid > 0, CONCAT(" AND d.projectid = ",_projectid) , "")
			, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
			, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND c.startepoch BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
			, "
		ORDER BY a.userid,c.projectid"
	);

	PREPARE theqry FROM @_qry2;
	EXECUTE theqry;					


	SET @_qry1 = CONCAT(
		"
			INSERT INTO tmpttreportsum
			(ttevents_id,clientid,projectid,projectname,company,userlogin,seconds,user_id,team)
			SELECT 
				a.ttevents_id,
				e.clientid,
				c.projectid,
				c.projectname,
				e.company,
				CONCAT(d.alast, ', ', d.afirst) as agentname,
				SUM(unix_timestamp(b.ts)-unix_timestamp(a.ts)) as seconds,
				a.user_id,
				d.team
			FROM tteventslog a 
				LEFT JOIN tteventslog_end b ON a.id=b.tteventslog_id 
				LEFT JOIN projects c ON a.project_id=c.projectid 
				LEFT JOIN memberdetails d ON a.user_id=d.userid 
				LEFT JOIN clients e ON c.clientid=e.clientid 
				LEFT JOIN ttevents f ON a.ttevents_id=f.id 
			WHERE (f.bc_id = 0
				OR f.bc_id =", _bc_id,")"
				, " AND c.bcid =", _bc_id
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.project_id = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.user_id =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.ts BETWEEN '", _start, "' AND '", _end, "'"))
				,"
			GROUP BY c.projectname,agentname,f.break
			ORDER BY c.projectid, agentname,f.id
		"
	);

	PREPARE theqry FROM @_qry1;
	EXECUTE theqry;					

-- CAMPAIGN HOURS
	CREATE TEMPORARY TABLE IF NOT EXISTS tmpcampaignhrs
	(
		clientid int(7) unsigned DEFAULT NULL,
		projectid int(7) unsigned DEFAULT NULL,
		projectname varchar(100) DEFAULT NULL,
		company varchar(100) DEFAULT NULL,
		userlogin varchar(50) DEFAULT NULL,
		seconds bigint(7) unsigned NULL,
		user_id int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	-- Initial records to avoid tmpcampaignhrs having none which results to CAMPAIGN HOURS not having a column in the dataTable.
	-- These initial records have 0 seconds so in effect wouldn't affect total.
	INSERT INTO tmpcampaignhrs
	(clientid,projectid,projectname,company,userlogin,seconds,user_id,team)
	SELECT DISTINCT b.clientid, a.projectid, b.projectname, d.company, concat(c.alast,', ',c.afirst) as agentname, 0, a.user_id, c.team 
		FROM tmpttreportsum a 
			CROSS JOIN projects b ON a.projectid=b.projectid 
			CROSS JOIN memberdetails c ON a.user_id=c.userid 
			CROSS JOIN clients d ON b.clientid=d.clientid;

	SET @_qrycmpn1 = CONCAT(
		"
			INSERT INTO tmpcampaignhrs
			(clientid,projectid,projectname,company,userlogin,seconds,user_id,team)
			SELECT 
				b.clientid,
				b.projectid, 
				b.projectname,
				c.company,
				CONCAT(d.alast, ', ', d.afirst) as agentname, 
				SUM(a.epochend-a.epochstart) AS seconds,
				a.userid,
				d.team
			FROM actionlog a
				CROSS JOIN projects b ON a.projectid = b.projectid
				CROSS JOIN clients c ON b.clientid = c.clientid
				CROSS JOIN memberdetails d ON a.userid = d.userid
			WHERE a.epochend <> '' AND a.action not IN ('dial','talk')
				AND b.bcid=", _bc_id
				, " AND a.userid IN (SELECT DISTINCT user_id FROM tmpttreportsum)"
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.epochstart BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				,"
			GROUP BY b.clientid,b.projectid,agentname
		"
	);

	PREPARE theqrycmpn FROM @_qrycmpn1;
	EXECUTE theqrycmpn;					

	SET @_qrycmpn2 = CONCAT(
		"
			INSERT INTO tmpcampaignhrs
			(clientid,projectid,projectname,company,userlogin,seconds,user_id,team)
			SELECT 
				b.clientid,
				b.projectid, 
				b.projectname,
				c.company,
				CONCAT(d.alast, ', ', d.afirst) as agentname, 
				SUM( if(a.dialmode='manual', if(a.dialedtime=0,a.endepoch-a.startepoch,a.dialedtime-a.answeredtime) ,0) ) 
					+ sum(a.answeredtime) AS seconds,
				a.userid,
				d.team
			FROM finalhistory a
				CROSS JOIN projects b ON a.projectid = b.projectid
				CROSS JOIN clients c ON b.clientid = c.clientid
				CROSS JOIN memberdetails d ON a.userid = d.userid 
			WHERE b.bcid =", _bc_id
				, " AND a.userid IN (SELECT DISTINCT user_id FROM tmpttreportsum)"
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.startepoch BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				,"
			GROUP BY b.clientid,b.projectid,a.userid
		"
	);

	PREPARE theqrycmpn FROM @_qrycmpn2;
	EXECUTE theqrycmpn;					

--

	INSERT INTO tmpttreportsum
	(ttevents_id,clientid,projectid,projectname,company,userlogin,seconds,user_id,team)
	SELECT
		0,
		clientid,
		projectid,
		projectname,
		company,
		userlogin,
		SUM(seconds),
		user_id,
		team
	FROM tmpcampaignhrs
	GROUP BY clientid,projectid,user_id;

	INSERT INTO tmpttreportsumXttevents
	(projectid,clientid,userlogin,id,break,projectname, user_id, team)
	SELECT DISTINCT a.projectid,a.clientid,a.userlogin,b.id,b.break,a.projectname,a.user_id,a.team FROM tmpttreportsum a CROSS JOIN ttevents b;

	INSERT INTO tmpttreportsumXttevents
	(projectid,clientid,userlogin,id,break,projectname, user_id, team)
	SELECT DISTINCT projectid,clientid,userlogin, 0, 'Campaign Hours', projectname, user_id, team FROM tmpcampaignhrs;

-- Sum up records on tmpttreportsum with same user_id, projectid, clientid, and ttevents_id to result to just 1 record for such
--    before joining with tmpttreportsumXttevents
/**
	CREATE TEMPORARY TABLE IF NOT EXISTS tmpttreports LIKE tmpttreportsum;

	INSERT INTO tmpttreports
	SELECT ttevents_id, projectid, clientid, projectname, company, userlogin, sum(seconds), user_id 
	FROM tmpttreportsum 
	GROUP BY user_id, projectid, clientid, ttevents_id;

	TRUNCATE tmpttreportsum;
	INSERT INTO tmpttreportsum SELECT * FROM tmpttreports;
**/
	-- AGENT row
	INSERT INTO tmpttreportsumfinal
	(category,projectid,clientid,userlogin,id,break,seconds,user_id,team)
	SELECT a.userlogin,a.projectid, a.clientid, a.userlogin, a.id, a.break, SUM(IFNULL(b.seconds,0)) as seconds, a.user_id, a.team 
	FROM tmpttreportsumXttevents a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.id=b.ttevents_id AND a.user_id=b.user_id 
	group by a.userlogin,a.id;

	INSERT INTO tmpttreportsumfinal
	(category,projectid,clientid,userlogin,id,break,seconds,user_id,team)
	SELECT a.userlogin,a.projectid, a.clientid, a.userlogin, 99999 as id, 'Total' as break, SUM(IFNULL(b.seconds,0)) as seconds, a.user_id, a.team
	FROM tmpttreportsumXttevents a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.id=b.ttevents_id AND a.user_id=b.user_id 
	WHERE b.ttevents_id <> 999
	GROUP BY a.userlogin;

	-- AGENT'S CAMPAIGNS row
	INSERT INTO tmpttreportsumfinal
	(category,projectid,clientid,userlogin,id,break,seconds,user_id,team)
	SELECT concat(a.userlogin,'-',a.projectid),a.projectid, a.clientid, a.projectname, a.id,a.break, IFNULL(b.seconds,0) as seconds, a.user_id, a.team 
	FROM tmpttreportsumXttevents a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.id=b.ttevents_id AND a.user_id=b.user_id;

	INSERT INTO tmpttreportsumfinal
	(category,projectid,clientid,userlogin,id,break,seconds,user_id,team)
	SELECT concat(a.userlogin,'-',a.projectid),a.projectid, a.clientid, a.projectname, 99999 as id, 'Total' as break, SUM(IFNULL(b.seconds,0)) as seconds, a.user_id, a.team
	FROM tmpttreportsumXttevents a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.id=b.ttevents_id AND a.user_id=b.user_id 
	WHERE b.ttevents_id <> 999
	GROUP BY a.projectid, a.userlogin;

	SET @_fqry1 =
	CONCAT("
	SELECT 
		category,
		projectid,
		clientid,
		userlogin,
		id,
		break,
		-- sec_to_time(seconds) AS seconds
		fnUtilSec2Time(SUM(seconds)) AS seconds
	FROM tmpttreportsumfinal
	WHERE projectid <> 0 "
	, IF(_teamid > 0, CONCAT( " AND team LIKE '", concat('%','"',_teamid,'"','%',"'") ), "")
	,"
	GROUP BY category,projectid,clientid,userlogin,id,break
	ORDER BY category, id
	");

	PREPARE thefqry FROM @_fqry1;
	EXECUTE thefqry;	

END;