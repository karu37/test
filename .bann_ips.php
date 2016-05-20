<?
include dirname(__FILE__)."/.bann_ips_list.php";

if (check_bann_by_ip($_SERVER['REMOTE_ADDR'])) {
	header("Location: http://www.naver.com");
	die();
}

function check_bann_by_ip($szIP)
{
	global $arBannIP;
	
	$szIP = trim($szIP);
	$nCount = count($arBannIP);
	for ($i=0; $i < $nCount; $i ++) {
		$ar_ips = explode('~', $arBannIP[$i]);
		if (count($ar_ips) == 1 && $szIP == trim($arBannIP[$i])) return true;
		if (count($ar_ips) == 2 && _is_ranged_ip_bann($szIP, $ar_ips[0], $ar_ips[1])) return true;
	}
	return false;
}

function _is_ranged_ip_bann($szCheckIP, $szStartIP, $szEndIP)
{
	if (!$szCheckIP) return false;
	$arCheckIP = explode('.', $szCheckIP);
	$arRangeFrom = explode('.', $szStartIP);
	$arRangeTo = explode('.', $szEndIP);
	if (count($arCheckIP) != 4 || count($arRangeFrom) != 4 || count($arRangeTo) != 4) return false;
	if (intval($arCheckIP[0]) >= intval($arRangeFrom[0]) && intval($arCheckIP[0]) <= intval($arRangeTo[0]) &&
		intval($arCheckIP[1]) >= intval($arRangeFrom[1]) && intval($arCheckIP[1]) <= intval($arRangeTo[1]) &&
		intval($arCheckIP[2]) >= intval($arRangeFrom[2]) && intval($arCheckIP[2]) <= intval($arRangeTo[2]) &&
		intval($arCheckIP[3]) >= intval($arRangeFrom[3]) && intval($arCheckIP[3]) <= intval($arRangeTo[3]))
		return true;
	return false;
}

?>