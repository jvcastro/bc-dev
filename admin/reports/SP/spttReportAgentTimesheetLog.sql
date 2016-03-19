DROP PROCEDURE IF EXISTS `spttReportAgentTimeSheetLog`;
CREATE PROCEDURE `spttReportAgentTimeSheetLog` (
	_bc_id int unsigned, 
	_clientid int unsigned, 
	_projectid int unsigned, 
	_memberid int unsigned,
	_teamid int unsigned,
	_start timestamp,
	_end timestamp
)
BEGIN

-- CAMPAIGN HOURS
	CREATE TEMPORARY TABLE IF NOT EXISTS tmptslog
	(
		`company` varchar(100) DEFAULT NULL,
		`projectname` varchar(100) DEFAULT NULL,
		`userlogin` varchar(50) DEFAULT NULL,
		`dialdate` DATE DEFAULT NULL,
		`started` TIME DEFAULT NULL,
		`seconds` bigint(7) unsigned NULL,
		`event` varchar(50) DEFAULT NULL,
		`ts` DATETIME DEFAULT NULL,
		`user_id` int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL,
		key (ts)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	CREATE TEMPORARY TABLE IF NOT EXISTS tmptslog_userid
	(
		`user_id` int(7) unsigned DEFAULT NULL,
		team text DEFAULT NULL,
		key (user_id)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	-- Initial records for everyone in members under the same bcid of admin user so for report to always display something

		SET @_qry2 = CONCAT(
			"
			INSERT INTO tmptslog_userid
			(user_id,team)
			SELECT
				DISTINCT
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
		);

		PREPARE theqry FROM @_qry2;
		EXECUTE theqry;					

	SET @_qry1 = CONCAT(
		"
			INSERT INTO tmptslog
			(company,projectname,userlogin,dialdate,started,seconds,event,ts,user_id,team)
			SELECT 
				e.company,
				c.projectname,
				CONCAT(d.alast, ', ', d.afirst) as agentname,
				date(a.ts) as `date`,
				time(a.ts) as started, 
				unix_timestamp(b.ts)-unix_timestamp(a.ts) as seconds, 
				f.break,
				a.ts,
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
		"
	);

	PREPARE theqry FROM @_qry1;
	EXECUTE theqry;			

	/** INSERT INTO tmptslog_userid
	(user_id)
	SELECT DISTINCT user_id FROM tmptslog;**/

	SET @_qrycmpn1 = CONCAT(
		"
			INSERT INTO tmptslog
			(company,projectname,userlogin,dialdate,started,seconds,event,ts,team)
			SELECT 
				c.company,
				b.projectname,
				CONCAT(d.alast, ', ', d.afirst) as agentname, 
				date(from_unixtime(a.epochstart)) as dialdate,
				time(from_unixtime(a.epochstart)) as started,
				a.epochend-a.epochstart AS seconds,
				CONCAT( UCASE(LEFT(action, 1)), LCASE(SUBSTRING(action, 2)) ) as event,
				from_unixtime(a.epochstart) as ts,
				d.team
			FROM actionlog a
				CROSS JOIN projects b ON a.projectid = b.projectid
				CROSS JOIN clients c ON b.clientid = c.clientid
				CROSS JOIN memberdetails d ON a.userid = d.userid
			WHERE a.epochend <> '' AND a.action not IN ('dial','talk')
				AND b.bcid=", _bc_id
				, " AND a.userid IN (SELECT user_id FROM tmptslog_userid)"
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.epochstart BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				,"
		"
	);

	PREPARE theqrycmpn FROM @_qrycmpn1;
	EXECUTE theqrycmpn;					

	SET @_qrycmpn2 = CONCAT(
		"
			INSERT INTO tmptslog
			(company,projectname,userlogin,dialdate,started,seconds,event,ts,team)
			SELECT 
				c.company,
				b.projectname,
				CONCAT(d.alast, ', ', d.afirst) as agentname, 
				DATE(from_unixtime(a.startepoch)) as dialdate,
				TIME(from_unixtime(a.startepoch)) as started,
				-- SUM( if(a.dialmode='manual', if(a.dialedtime=0,a.endepoch-a.startepoch,a.dialedtime-a.answeredtime) ,0) ) 
				a.answeredtime AS seconds,
				'Talk' as event,
				from_unixtime(a.startepoch) as ts,
				d.team
			FROM finalhistory a
				CROSS JOIN projects b ON a.projectid = b.projectid
				CROSS JOIN clients c ON b.clientid = c.clientid
				CROSS JOIN memberdetails d ON a.userid = d.userid 
			WHERE b.bcid =", _bc_id
				, " AND a.userid IN (SELECT user_id FROM tmptslog_userid)"
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.startepoch BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				,"
		"
	);

	PREPARE theqrycmpn FROM @_qrycmpn2;
	EXECUTE theqrycmpn;					

	SET @_qrycmpn3 = CONCAT(
		"
			INSERT INTO tmptslog
			(company,projectname,userlogin,dialdate,started,seconds,event,ts,team)
			SELECT 
				c.company,
				b.projectname,
				CONCAT(d.alast, ', ', d.afirst) as agentname, 
				DATE(from_unixtime(a.startepoch)) as dialdate,
				TIME(from_unixtime(a.startepoch)) as started,
				if(a.dialmode='manual', if(a.dialedtime=0,a.endepoch-a.startepoch,a.dialedtime-a.answeredtime) ,0) as seconds,
				-- a.answeredtime AS seconds,
				'Dial' as event,
				from_unixtime(a.startepoch) as ts,
				d.team
			FROM finalhistory a
				CROSS JOIN projects b ON a.projectid = b.projectid
				CROSS JOIN clients c ON b.clientid = c.clientid
				CROSS JOIN memberdetails d ON a.userid = d.userid 
			WHERE b.bcid =", _bc_id
				, " AND a.userid IN (SELECT user_id FROM tmptslog_userid)"
				, IF(_clientid > 0, CONCAT(" AND c.clientid = ",_clientid), "")
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_memberid > 0, CONCAT(" AND a.userid =", _memberid), "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.startepoch BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				,"
		"
	);

	PREPARE theqrycmpn FROM @_qrycmpn3;
	EXECUTE theqrycmpn;					

	SET @_fqry1 = CONCAT(
	"
	SELECT 
		company,
		projectname,
		userlogin,
		dialdate,
		started,
		SEC_TO_TIME(seconds) as seconds,
		event
	FROM tmptslog "
	, IF(_teamid > 0, CONCAT( " WHERE team LIKE '", concat('%','"',_teamid,'"','%',"'") ), "")
	,"
	ORDER BY ts
	");

	PREPARE thefqry FROM @_fqry1;
	EXECUTE thefqry;	

END;