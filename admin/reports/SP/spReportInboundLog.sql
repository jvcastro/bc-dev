DROP PROCEDURE IF EXISTS `spReportInboundLog`;
CREATE PROCEDURE `spReportInboundLog` (
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
				d.projectname as campaign, 
				b.phone, 
				from_unixtime(a.startepoch) as `date`, 
--				a.entrytime - a.startepoch as queue, 
				IF(a.userid > 0, a.entrytime - a.startepoch,
					IF (a.endepoch IS NULL, b.endepoch - a.startepoch, a.endepoch - a.startepoch)
				) as queue,
				IF(a.userid > 0, CONCAT(c.alast, ', ', c.afirst),
					IF (a.endepoch IS NULL, 'ABANDON', 'VM')
				) as agent,
				b.endepoch-a.entrytime as talk 
			FROM callhistory a 
				LEFT JOIN finalhistory b on a.callid=b.callid 
				LEFT JOIN memberdetails c on a.userid=c.userid
				LEFT JOIN projects d on a.projectid = d.projectid
			WHERE a.bcid =", _bc_id 
				, " AND d.dialmode = 'inbound' "
				, IF(_projectid > 0, CONCAT(" AND a.projectid = ",_projectid) , "")
				, IF(_start = '' OR _end = '' OR _start = 0 OR _end = 0, "", CONCAT(" AND a.startepoch BETWEEN '", unix_timestamp(_start), "' AND '", unix_timestamp(_end), "'"))
				, " AND NOT (a.endepoch IS NULL AND a.userid > 0) "
				, " GROUP BY a.id"
		);

		PREPARE theqry FROM @_qry1;
		EXECUTE theqry;					


END;