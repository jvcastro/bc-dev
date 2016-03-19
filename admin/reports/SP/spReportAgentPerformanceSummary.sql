-- "sAjaxSource": "?act=loadreportsummary"+"&_clientid="+_clientid+"&_projectid="+_projectid+"&_memberid="+_memberid+"&_start="+_start+"&_end="+_end
DROP PROCEDURE IF EXISTS `spReportAgentPerformanceSummary`;
CREATE PROCEDURE `spReportAgentPerformanceSummary` (
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
		action varchar(25) DEFAULT NULL,
		clientid int(7) unsigned DEFAULT NULL,
		projectid int(7) unsigned DEFAULT NULL,
		projectname varchar(100) DEFAULT NULL,
		company varchar(100) DEFAULT NULL,
		userlogin varchar(50) DEFAULT NULL,
		seconds bigint(7) unsigned NULL,
		user_id int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL,
		calls int(7) unsigned DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmpttreportsumXrpcolumns
	(
		projectid int(7) unsigned DEFAULT NULL,
		clientid int(7) unsigned DEFAULT NULL,
		userlogin varchar(50) DEFAULT NULL,
		id bigint(7) unsigned NULL,
		action varchar(25) DEFAULT NULL,
		projectname varchar(100) DEFAULT NULL,
		user_id int(7) unsigned DEFAULT NULL,
		seconds bigint(7) unsigned NULL,
		calls int(7) unsigned DEFAULT 0,
		team text DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmpttreportsumfinal
	(
		category varchar(256) DEFAULT NULL,
		projectid int(7) unsigned DEFAULT NULL,
		clientid int(7) unsigned DEFAULT NULL,
		userlogin varchar(50) DEFAULT NULL,
		campaignhrs bigint(7) unsigned NULL,
		id bigint(7) unsigned NULL,
		action varchar(25) DEFAULT NULL,
		seconds bigint(7) unsigned NULL,
		user_id int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL,
		calls int(7) unsigned DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- CAMPAIGN HOURS
	CREATE TEMPORARY TABLE IF NOT EXISTS tmpcampaignhrs
	(
		clientid int(7) unsigned DEFAULT NULL,
		projectid int(7) unsigned DEFAULT NULL,
		projectname varchar(100) DEFAULT NULL,
		company varchar(100) DEFAULT NULL,
		userlogin varchar(50) DEFAULT NULL,
		action varchar(25) DEFAULT NULL,
		seconds bigint(7) unsigned NULL,
		user_id int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL,
		calls int(7) unsigned DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	-- Initial records to avoid tmpcampaignhrs having none which results to CAMPAIGN HOURS not having a column in the dataTable.
	-- These initial records have 0 seconds so in effect wouldn't affect total.

	SET @_qry1cmpn = CONCAT(
		"
			INSERT DELAYED INTO tmpcampaignhrs
			(clientid,projectid,projectname,company,userlogin,action,seconds,user_id,team,calls)
			SELECT 
				b.clientid,
				b.projectid, 
				b.projectname,
				c.company,
				CONCAT(d.alast, ', ', d.afirst) as agentname,
				action, 
				SUM(a.epochend-a.epochstart) AS seconds,
				a.userid,
				d.team,
				COUNT(*) as calls
			FROM actionlog a
				CROSS JOIN projects b ON a.projectid = b.projectid
				CROSS JOIN clients c ON b.clientid = c.clientid
				CROSS JOIN memberdetails d ON a.userid = d.userid
			WHERE a.epochend <> '' AND a.action not IN ('dial','talk')
				AND b.bcid=", _bc_id
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.epochstart BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				,"
			GROUP BY b.clientid,b.projectid,agentname,action
		"
	);

	PREPARE theqry1cmpn FROM @_qry1cmpn;
	EXECUTE theqry1cmpn;					

	SET @_qry2cmpn = CONCAT(
		"
			INSERT DELAYED INTO tmpcampaignhrs
			(clientid,projectid,projectname,company,userlogin,action,seconds,user_id,team,calls)
			SELECT 
				b.clientid,
				b.projectid, 
				b.projectname,
				c.company,
				CONCAT(d.alast, ', ', d.afirst) as agentname, 
				'dial' as action,
				SUM( if(a.dialmode='manual', if(a.dialedtime=0,a.endepoch-a.startepoch,a.dialedtime-a.answeredtime) ,0) ) AS seconds,
				a.userid,
				d.team,
				COUNT(*) as calls
			FROM finalhistory a
				CROSS JOIN projects b ON a.projectid = b.projectid
				CROSS JOIN clients c ON b.clientid = c.clientid
				CROSS JOIN memberdetails d ON a.userid = d.userid 
			WHERE b.bcid =", _bc_id
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.startepoch BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				,"
			GROUP BY b.clientid,b.projectid,a.userid
		"
	);

	PREPARE theqry2cmpn FROM @_qry2cmpn;
	EXECUTE theqry2cmpn;					

	SET @_qry3cmpn = CONCAT(
		"
			INSERT DELAYED INTO tmpcampaignhrs
			(clientid,projectid,projectname,company,userlogin,action,seconds,user_id,team,calls)
			SELECT 
				b.clientid,
				b.projectid, 
				b.projectname,
				c.company,
				CONCAT(d.alast, ', ', d.afirst) as agentname, 
				'talk' as action,
				SUM(a.answeredtime) AS seconds,
				a.userid,
				d.team,
				COUNT(*) AS calls
			FROM finalhistory a
				CROSS JOIN projects b ON a.projectid = b.projectid
				CROSS JOIN clients c ON b.clientid = c.clientid
				CROSS JOIN memberdetails d ON a.userid = d.userid 
			WHERE b.bcid =", _bc_id
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.startepoch BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				,"
			GROUP BY b.clientid,b.projectid,a.userid
		"
	);

	PREPARE theqry3cmpn FROM @_qry3cmpn;
	EXECUTE theqry3cmpn;					

--

	INSERT INTO tmpttreportsum
	(action,clientid,projectid,projectname,company,userlogin,seconds,user_id,team,calls)
	SELECT
		'Campaign Hours',
		clientid,
		projectid,
		projectname,
		company,
		userlogin,
		SUM(seconds),
		user_id,
		team,
		SUM(calls)
	FROM tmpcampaignhrs
	GROUP BY clientid,projectid,user_id;

	INSERT INTO tmpttreportsum
	(action,clientid,projectid,projectname,company,userlogin,seconds,user_id,team,calls)
	SELECT
		action,
		clientid,
		projectid,
		projectname,
		company,
		userlogin,
		SUM(seconds),
		user_id,
		team,
		SUM(calls)
	FROM tmpcampaignhrs
	GROUP BY clientid,projectid,user_id,action;

	CREATE TEMPORARY TABLE tmpcampaignhrsPerAgent LIKE tmpcampaignhrs;
	INSERT INTO tmpcampaignhrsPerAgent
	(action,clientid,projectid,projectname,company,userlogin,seconds,user_id,team,calls)
	SELECT
		'Campaign Hours',
		clientid,
		projectid,
		projectname,
		company,
		userlogin,
		SUM(seconds),
		user_id,
		team,
		SUM(calls)
	FROM tmpcampaignhrs
	GROUP BY clientid,projectid,user_id;

	CREATE TEMPORARY TABLE tmpcampaignhrsPerAgentAction LIKE tmpcampaignhrs;
	INSERT INTO tmpcampaignhrsPerAgentAction
	(action,clientid,projectid,projectname,company,userlogin,seconds,user_id,team,calls)
	SELECT
		action,
		clientid,
		projectid,
		projectname,
		company,
		userlogin,
		SUM(seconds),
		user_id,
		team,
		SUM(calls)
	FROM tmpcampaignhrs
	GROUP BY clientid,projectid,user_id,action;

/**
	INSERT INTO tmpttreportsumXrpcolumns
	(projectid,clientid,userlogin,id,action,projectname, user_id, team)
	SELECT DISTINCT a.projectid,a.clientid,a.userlogin,b.id,b.col,a.projectname,a.user_id,a.team FROM tmpttreportsum a CROSS JOIN rpcolumns b;
**/
	INSERT INTO tmpttreportsumXrpcolumns
	(projectid,clientid,userlogin,id,action,projectname, user_id, seconds, calls, team)
	SELECT DISTINCT a.projectid,a.clientid,a.userlogin,b.id,b.col,a.projectname,a.user_id,a.seconds,a.calls,a.team FROM tmpcampaignhrsPerAgent a CROSS JOIN rpcolumns b;

	INSERT INTO tmpttreportsumXrpcolumns
	(projectid,clientid,userlogin,id,action,projectname, user_id, team)
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
	(calls,category,projectid,clientid,userlogin,campaignhrs,id,action,seconds,user_id,team)
	SELECT SUM(IFNULL(b.calls,0)),a.userlogin,a.projectid, a.clientid, a.userlogin, SUM(IFNULL(a.seconds,0)) as totseconds, a.id, a.action, SUM(IFNULL(b.seconds,0)) as seconds, a.user_id, a.team 
	FROM tmpttreportsumXrpcolumns a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.action=b.action AND a.user_id=b.user_id 
	group by a.userlogin,a.id;

	INSERT INTO tmpttreportsumfinal
	(calls,category,projectid,clientid,userlogin,campaignhrs,id,action,seconds,user_id,team)
	SELECT SUM(IFNULL(b.calls,0)),a.userlogin,a.projectid, a.clientid, a.userlogin, a.seconds,a.id+1 as id, concat(a.action,'_p') as action, SUM(IFNULL(b.seconds,0)) as seconds, a.user_id, a.team 
	FROM tmpttreportsumXrpcolumns a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.action=b.action AND a.user_id=b.user_id 
	WHERE a.action NOT like '%\_p' and a.action NOT like '%\_a' and a.action <> 'Campaign Hours'
	group by a.userlogin,a.id;

	INSERT INTO tmpttreportsumfinal
	(calls,category,projectid,clientid,userlogin,campaignhrs,id,action,seconds,user_id,team)
	SELECT SUM(IFNULL(b.calls,0)), a.userlogin,a.projectid, a.clientid, a.userlogin, a.seconds,a.id+2 as id, concat(a.action,'_a') as action, AVG(IFNULL(b.seconds,0)) as seconds, a.user_id, a.team 
	FROM tmpttreportsumXrpcolumns a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.action=b.action AND a.user_id=b.user_id 
	WHERE a.action NOT like '%\_p' and a.action NOT like '%\_a' and a.action <> 'Campaign Hours'
	group by a.userlogin,a.id;

	INSERT INTO tmpttreportsumfinal
	(calls,category,projectid,clientid,userlogin,campaignhrs,id,action,seconds,user_id,team)
	SELECT SUM(IFNULL(b.calls,0)),a.userlogin,a.projectid, a.clientid, a.userlogin, a.seconds,99999 as id, 'Total' as action, SUM(IFNULL(b.calls,0)) as seconds, a.user_id, a.team
	FROM tmpttreportsumXrpcolumns a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.action=b.action AND a.user_id=b.user_id 
	WHERE a.action='talk'
	GROUP BY a.userlogin;

	-- AGENT'S CAMPAIGNS row
	INSERT INTO tmpttreportsumfinal
	(calls,category,projectid,clientid,userlogin,campaignhrs,id,action,seconds,user_id,team)
	SELECT IFNULL(b.calls,0),concat(a.userlogin,'-',a.projectid),a.projectid, a.clientid, a.projectname, a.seconds, a.id,a.action, IFNULL(b.seconds,0) as seconds, a.user_id, a.team 
	FROM tmpttreportsumXrpcolumns a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.action=b.action AND a.user_id=b.user_id;

	INSERT INTO tmpttreportsumfinal
	(calls,category,projectid,clientid,userlogin,campaignhrs,id,action,seconds,user_id,team)
	SELECT IFNULL(b.calls,0),concat(a.userlogin,'-',a.projectid),a.projectid, a.clientid, a.projectname, a.seconds, a.id+1 as id, concat(a.action,'_p') as action, IFNULL(b.seconds,0) as seconds, a.user_id, a.team 
	FROM tmpttreportsumXrpcolumns a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.action=b.action AND a.user_id=b.user_id
	WHERE a.action NOT like '%\_p' and a.action NOT like '%\_a' and a.action <> 'Campaign Hours';

	INSERT INTO tmpttreportsumfinal
	(calls,category,projectid,clientid,userlogin,campaignhrs,id,action,seconds,user_id,team)
	SELECT IFNULL(b.calls,0),concat(a.userlogin,'-',a.projectid),a.projectid, a.clientid, a.projectname, a.seconds, a.id+2 as id, concat(a.action,'_a') as action, IFNULL(b.seconds,0) as seconds, a.user_id, a.team 
	FROM tmpttreportsumXrpcolumns a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.action=b.action AND a.user_id=b.user_id
	WHERE a.action NOT like '%\_p' and a.action NOT like '%\_a' and a.action <> 'Campaign Hours';

	INSERT INTO tmpttreportsumfinal
	(calls,category,projectid,clientid,userlogin,campaignhrs,id,action,seconds,user_id,team)
	SELECT SUM(IFNULL(b.calls,0)),concat(a.userlogin,'-',a.projectid),a.projectid, a.clientid, a.projectname, a.seconds, 99999 as id, 'Total' as action, SUM(IFNULL(b.calls,0)) as seconds, a.user_id, a.team
	FROM tmpttreportsumXrpcolumns a LEFT JOIN tmpttreportsum b 
		ON a.projectid=b.projectid AND a.clientid=b.clientid AND a.action=b.action AND a.user_id=b.user_id 
	WHERE a.action='talk'
	GROUP BY a.projectid, a.userlogin;

	SET @_fqry1 =
	CONCAT("
	SELECT 
		category,
		projectid,
		clientid,
		userlogin,
		id,
		action,
		-- sec_to_time(seconds) AS seconds
        IF( id=99999 
        	,SUM(seconds)
        	, 	IF( RIGHT(action,2) = '_p'
        			,FORMAT(((MAX(seconds) / MAX(campaignhrs))*100),0)
	       		, IF( SUM(seconds) = 0
	       				, 0
	       				, 	IF( RIGHT(action,2) = '_a'
	       						, 	IF( SUM(seconds) > 0
	       								, fnUtilSec2Time( (SUM(seconds)/SUM(calls)) ) 
	       								, 0
	       							)
	       						, fnUtilSec2Time(SUM(seconds))
	       					)

	       			)
	       	)
        ) AS seconds
	FROM tmpttreportsumfinal
	WHERE projectid <> 0 "
	, IF(_teamid > 0, CONCAT( " AND team LIKE '", concat('%','"',_teamid,'"','%',"'") ), "")
	,"
	GROUP BY category,projectid,clientid,userlogin,id,action
	ORDER BY category, id
	");
	
	PREPARE thefqry FROM @_fqry1;
	EXECUTE thefqry;	

END;