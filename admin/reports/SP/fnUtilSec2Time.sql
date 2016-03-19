DROP FUNCTION IF EXISTS fnUtilSec2Time;
CREATE FUNCTION `fnUtilSec2Time`(
	_seconds bigint(7) unsigned
) RETURNS varchar(32)
BEGIN

	DECLARE _hours bigint(7) unsigned;
	DECLARE _minutes bigint(7) unsigned;
	DECLARE _secs bigint(7) unsigned;
	DECLARE _return varchar(10);

	SET _hours = _seconds DIV 3600;
	SET _minutes = (_seconds MOD 3600) DIV 60;
	SET _secs = (_seconds MOD 3600) MOD 60;

	SET _return = IF(_hours>0, CONCAT(LPAD(_hours,2,0),'h'), '' );
	SET _return = CONCAT(_return, IF(_minutes>0, CONCAT(LPAD(_minutes,2,0), 'm'), ''));
	SET _return = CONCAT(_return, IF(_seconds>0, CONCAT(LPAD(_seconds,2,0), 's'), ''));

	RETURN _return;

END;