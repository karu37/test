<?
function mysql_get_time($conn) {
	
	// query�� DB �ð��� ���´�.
	$sql = "SELECT CONCAT(CURRENT_DATE, ' ', LEFT(CURRENT_TIME, 2), ':00') AS 'datehour', CONCAT(LEFT(CURRENT_TIME, 2), ':00:00') AS 'hour'";
	$ar_time = mysql_fetch_assoc(mysql_query($sql, $conn));
	return $ar_time;
}

?>