DROP PROCEDURE IF EXISTS `spReportInboundSummary`;
CREATE PROCEDURE `spReportInboundSummary` (
	_bc_id int unsigned, 
	_clientid int unsigned, 
	_projectid int unsigned, 
	_memberid int unsigned,
	_teamid int unsigned,
	_start timestamp,
	_end timestamp
)
BEGIN

		SET @_qry1 = CONCAT(
			"
			SELECT 
				DATE(FROM_UNIXTIME(a.startepoch)) AS call_date,
				COUNT(*) AS calls_offered, 
				SUM(IF(a.userid > 0, 1, 0)) AS calls_answered, 
				fnUtilSec2Time(SUM(IF(a.userid > 0, (b.endepoch-a.entrytime), 0)) / SUM(IF(a.userid > 0, 1, 0))) AS avg_handle_time, 
				fnUtilAvg2Sec(SUM(IF(a.userid > 0, a.entrytime - a.startepoch, IF (a.endepoch IS NULL, b.endepoch - a.startepoch, a.endepoch - a.startepoch))) / COUNT(*)) AS avg_speed_answer, 
				FORMAT((SUM(IF(IF(a.userid > 0, a.entrytime - a.startepoch, IF (a.endepoch IS NULL, b.endepoch - a.startepoch, a.endepoch - a.startepoch)) <= 10, 1, 0)) / COUNT(*)) * 100, 0) AS sl_p, 
				FORMAT((SUM(IF(a.userid = 0 AND a.endepoch IS NULL, 1, 0)) / COUNT(*)) * 100, 0) AS aba_p
			FROM callhistory a 
				LEFT JOIN finalhistory b on a.callid=b.callid 
			WHERE a.bcid =", _bc_id 
				, " AND a.projectid in (SELECT projectid FROM projects WHERE bcid= ", _bc_id, " AND dialmode = 'inbound') "
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.startepoch BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				, " AND NOT (a.endepoch IS NULL AND a.userid > 0) "
				, " GROUP BY call_date"
		);

		PREPARE theqry FROM @_qry1;
		EXECUTE theqry;					


END;