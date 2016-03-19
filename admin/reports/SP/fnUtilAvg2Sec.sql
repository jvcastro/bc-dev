DROP FUNCTION IF EXISTS fnUtilAvg2Sec;
CREATE FUNCTION `fnUtilAvg2Sec`(
	_seconds float(3,3) unsigned
) RETURNS varchar(32)
BEGIN

	IF (_seconds > 0 AND _seconds < 1)
	THEN
		return CONCAT(FORMAT(_seconds * 1000, 0), ' ms');
	END IF;

	RETURN FORMAT(_seconds, 0);

END;