<?

// $ar_time['now'] == NOW();
// $ar_time['day'] == CURRENT_DATE;
// $ar_time['hour'] == "12:00:00";					# 분 초 리셋
// $ar_time['datehour'] == "2016-05-03 12:00:00"; 	# 분 초 리셋
function mysql_get_time($conn) {
	
	// query용 DB 시간을 얻어온다.
	$sql = "SELECT CONCAT(CURRENT_DATE, ' ', LEFT(CURRENT_TIME, 2), ':00') AS 'datehour', CONCAT(LEFT(CURRENT_TIME, 2), ':00:00') AS 'hour', NOW() as 'now', CURRENT_DATE as 'day'";
	$ar_time = mysql_fetch_assoc(mysql_query($sql, $conn));
	return $ar_time;
}

?>