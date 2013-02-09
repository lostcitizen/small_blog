<?php

/**
 * Converts current time for given timezone (considering DST) to 14-digit UTC timestamp (YYYYMMDDHHMMSS)
 *
 * DateTime requires PHP >= 5.2
 *
 * @param $str_user_timezone
 * @param string $str_server_timezone
 * @param string $str_server_dateformat
 * @return string
 */
function now($str_user_timezone,
			 $str_server_timezone = CONST_SERVER_TIMEZONE,
			 $str_server_dateformat = CONST_SERVER_DATEFORMAT) {

	// set timezone to user timezone
	date_default_timezone_set($str_user_timezone);

	$date = new DateTime('now');
	$date->setTimezone(new DateTimeZone($str_server_timezone));
	$str_server_now = $date->format($str_server_dateformat);

	// return timezone to server default
	date_default_timezone_set($str_server_timezone);

	return $str_server_now;
}


/**
 * Converts a UTC timestamp to date string of given timezone (considering DST) and given dateformat
 *
 * DateTime requires PHP >= 5.2
 *
 * @param $str_server_datetime
 *
 * <li>Normally is a 14-digit UTC timestamp (YYYYMMDDHHMMSS). It can also be 8-digit (date), 12-digit (datetime without seconds).
 * If given dateformat (<var>$str_user_dateformat</var>) is longer than <var>$str_server_datetime</var>,
 * the missing digits of input value are filled with zero,
 * so (YYYYMMDD is equivalent to YYYYMMDD000000 and YYYYMMDDHHMM is equivalent to YYYYMMDDHHMM00).
 *
 * <li>It can also be 'now', null or empty string. In this case returns the current time.
 *
 * <li>Other values (invalid datetime strings) throw an error. Milliseconds are not supported.
 *
 * @param string $str_user_timezone
 * @param $str_user_dateformat
 * @return string
 */
function date_decode($str_server_datetime,
					 $str_user_timezone,
					 $str_user_dateformat) {

	// create date object
	try {
		$date = new DateTime($str_server_datetime);
	} catch(Exception $e) {
		trigger_error('date_decode: Invalid datetime: ' . $e->getMessage(), E_USER_ERROR);
	}

	// convert to user timezone
	$userTimeZone = new DateTimeZone($str_user_timezone);
	$date->setTimeZone($userTimeZone);

	// convert to user dateformat
	$str_user_datetime = $date->format($str_user_dateformat);

	return $str_user_datetime;
}

/**
 * Return the offset (in seconds) from UTC of a given timezone timestring (considering DST)
 *
 * @param $str_datetime
 * @param $str_timezone
 * @return int
 */
function get_time_offset($str_datetime, $str_timezone) {
	$timezone = new DateTimeZone($str_timezone);
	$offset = $timezone->getOffset(new DateTime($str_datetime));
	return $offset;
}

/**
 * * Multi-byte CASE INSENSITIVE str_replace
 *
 * @param $co
 * @param $naCo
 * @param $wCzym
 * @return string
 * @link http://www.php.net/manual/en/function.mb-ereg-replace.php#55659
 */
function mb_str_ireplace($co, $naCo, $wCzym) {
	$wCzymM = mb_strtolower($wCzym);
	$coM = mb_strtolower($co);
	$offset = 0;

	while(!is_bool($poz = mb_strpos($wCzymM, $coM, $offset))) {
		$offset = $poz + mb_strlen($naCo);
		$wCzym = mb_substr($wCzym, 0, $poz) . $naCo . mb_substr($wCzym, $poz + mb_strlen($co));
		$wCzymM = mb_strtolower($wCzym);
	}

	return $wCzym;
}



/**
 * Check if a string is a valid date(time)
 *
 * @param $str_dt
 * @param $str_dateformat
 * @param $str_timezone
 * @return bool
 */
function isValidDateTimeString($str_dt, $str_dateformat, $str_timezone) {
	$date = DateTime::createFromFormat($str_dateformat, $str_dt, new DateTimeZone($str_timezone));
	return ($date === false ? false : true);
}

?>