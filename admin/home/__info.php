<?
$tcp = shell_exec("wc -l /proc/net/tcp");
echo intval($tcp);
?>
