<?php
function sec2str($sec)
{
	$d = floor($sec / 86400);
	$h = floor(($sec % 86400) / 3600);
	$m = floor(($sec % 3600) / 60);
	$s = floor(($sec % 3600) % 60);
	if ($d) $d_str = "{$d}d ";
	if ($h) $hr_str = "{$h}h ";
	if ($m) $min_str = "{$m}m ";
	if ($s) $sec_str = "{$s}s";
	return "{$d_str}{$hr_str}{$min_str}{$sec_str}";
}

require('auth.php');
require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

if(!has_perm($HTTP_SESSION_VARS['userid'], "0", "conf", ""))
{
	die("Access denied.");
}

$page = (int)trim($HTTP_GET_VARS[p]);
if(!$page) $page=1;
if(!is_numeric($page)) $page=1;
$rowpp = 100;
$start_row = ($page - 1) * 100 + 1;

$_POST = $HTTP_POST_VARS;
$p_date = trim($_POST[date]);
$p_date2 = trim($_POST[date2]);
$p_time = trim($_POST[time]);
$p_time2 = trim($_POST[time2]);
$p_usr = trim($_POST[usr]);
$p_svr = trim($_POST[svr]);
$p_ip = trim($_POST[ip]);
$p_rpt = trim($_POST[rpt]);

if($p_rpt!=1) $p_rpt=0;
if(strlen($p_time)==0) $p_time = "00:00:00";
if(strlen($p_time2)==0) $p_time2 = "23:59:59";
if(strlen($p_date)>0 && strlen($p_date2)==0) $p_date2=$p_date;

if(strlen($p_date)>0)
{
	list($hr, $mi, $sc) = split(":", $p_time);
	list($yr, $mth, $day) = split("-", $p_date);
	$ts1 = mktime($hr, $mi, $sc, $mth, $day, $yr);

	list($hr, $mi, $sc) = split(":", $p_time2);
	list($yr, $mth, $day) = split("-", $p_date2);
	$ts2 = mktime($hr, $mi, $sc, $mth, $day, $yr);

	$cond_date = "AND in_timestamp >= $ts1 AND in_timestamp <= $ts2";
}
strlen($p_usr) and $cond_usr = "AND user = \"$p_usr\"";
strlen($p_svr) and $cond_svr = "AND server_name = \"$p_svr\"";
strlen($p_ip) and $cond_ip = "AND client_ip = \"" . (ip2long($p_ip) + 4294967296) . "\"";

if($HTTP_SERVER_VARS['REQUEST_METHOD']=='POST')
{
	$sql = "SELECT count(1), sum(duration) FROM wc_login_log WHERE 1 $cond_date $cond_usr $cond_svr $cond_ip";
	$rs = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($rs);
	$total_found = $row[0];
	$total_duration = $row[1];
	mysql_free_result($rs);

	if($p_rpt==1)
	{
		$sql = "SELECT count(DISTINCT(user)) FROM wc_login_log WHERE 1 $cond_date $cond_usr $cond_svr $cond_ip";
		$rs = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($rs);
		$total_found = $row[0];
		mysql_free_result($rs);
	}

	$lastpage = ceil($total_found / $rowpp);
	if($page > $lastpage) $page = $lastpage;

	if($total_found == 0)
	{
		$html_results = "<font color=red><b>No match queries.</b></font>";
	}
	else
	{
		$start_row = ($page - 1) * $rowpp;
		if($p_rpt==1)
			$sql = "SELECT user, SUM(duration) AS duration, SUM(flag) AS flag FROM wc_login_log WHERE 1 $cond_date $cond_usr $cond_svr $cond_ip GROUP BY user ORDER BY duration LIMIT $start_row, $rowpp ";
		else
			$sql = "SELECT id, in_timestamp, user, server_name, duration, client_ip, flag FROM wc_login_log WHERE 1 $cond_date $cond_usr $cond_svr $cond_ip LIMIT $start_row, $rowpp ";

		$rs = mysql_query($sql) or die(mysql_error());
		$total_duration_str = sec2str($total_duration);
		if($p_rpt==1)
			$html_results = "Found $total_found account(s) and the total duration is $total_duration_str.<table cellspacing=1 cellpadding=2 bgcolor=\"black\"><tr bgcolor=\"#CCCCCC\"><td>#</td><td>Account</td><td>Duration</td></tr>";
		else
			$html_results = "Found $total_found login(s) and the total duration is $total_duration_str.<table cellspacing=1 cellpadding=2 bgcolor=\"black\"><tr bgcolor=\"#CCCCCC\"><td>Timestamp</td><td>Account</td><td>Server</td><td>Duration</td><td>Client IP</td></tr>";
		$n = ($page - 1) * $rowpp;
		while($row = mysql_fetch_assoc($rs))
		{
			$n++;
			$date = date("Y/m/d H:i:s", $row[in_timestamp]);
			if($row[flag] == 1)
			{
				$bgcolor = "yellow";
			}
			else
			{
				$bgcolor = "";
			}
			$du = sec2str($row[duration]);
			$ip = long2ip(- 4294967295 - 1 + $row[client_ip]);
			if($p_rpt==0)
				$html_results .= "<tr bgcolor=\"white\"><td>{$date}</td><td>{$row[user]}</td><td>{$row[server_name]}</td><td align=right bgcolor=\"$bgcolor\">$du</td><td align=\"right\">$ip</td></tr>";
			else
				$html_results .= "<tr bgcolor=\"white\"><td>$n</td><td>{$row[user]}</td><td align=right bgcolor=\"$bgcolor\">$du</td></tr>";
		}
		$html_results .= "</table>";
		mysql_free_result($rs);

		function mklink($n){
			global $page;
			$tag = $n == $page?"<b>$n</b>":"$n";
			return "<a href=\"javascript:postfind($n)\">$tag</a>";
		}

		if($lastpage > 0)
		{
			$s1 = -10;
			$s2 = 10;

			$html_page = "Page: <input type=\"text\" name=\"p\" value=\"$page\" size=4>/$lastpage <input type=\"button\" value=\"Go\" onclick=\"if(isNaN(document.form1.p.value)||document.form1.p.value<1||document.form1.p.value>$lastpage||document.form1.p.value.indexOf('.')>=0){alert('Invalid page number.');document.form1.p.focus();return false;};postfind(document.form1.p.value)\"> ";
			if($page > 1) $html_page .= "<a href=\"javascript:postfind($page-1)\">Previous</a> ";
			if($page < $lastpage) $html_page .= "<a href=\"javascript:postfind($page+1)\">Next</a>";
			$html_page .= "<br>";

			if($page + $s1 > 1) $html_page .= mklink(1) . "... ";
			for($n = $s1; $n < $s2; $n++)
			{
				$pp = $page + $n;
				if($pp > $lastpage) break;
				if($pp > 0 )
					$html_page .= mklink($pp) . " ";
			}
			if($page + $s2 < $lastpage) $html_page .= " ..." . mklink($lastpage);

			$html_results .= $html_page;
		}
	}
}

?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<link rel="STYLESHEET" type="text/css" href="calendar.css">
<script language="JavaScript" src="simplecalendar.js" type="text/javascript"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
function postfind(p){
	document.form1.reset()
	postform(document.form1, 'wcloginhist.php?p='+p)
}
//-->
</script>
</head>
<body>
<form name="form1" method="post" action="wcloginhist.php">
<h3>Player Login Log</h3>
<br>
<table border=1 cellspacing=0>
	<tr>
		<td>Login DateTime</td>
		<td>
			From
			<input type="text" name="date" size="8" value="<?=$p_date?>" maxlength=10><a href="javascript: void(0);" onmouseover="if (timeoutId) clearTimeout(timeoutId);window.status='Show Calendar';return true;" onmouseout="if (timeoutDelay) calendarTimeout();window.status='';" onclick="g_Calendar.show(event,'form1.date',false, 'yyyy-mm-dd'); return false;"><img src="images/calendar.gif" name="imgCalendar" width="34" height="21" border="0" alt=""></a>
			Time: <input type="text" name="time" size="8" value="<?=$p_time?>" maxlength=8>

			To
			<input type="text" name="date2" size="8" value="<?=$p_date2?>" maxlength=10><a href="javascript: void(0);" onmouseover="if (timeoutId) clearTimeout(timeoutId);window.status='Show Calendar';return true;" onmouseout="if (timeoutDelay) calendarTimeout();window.status='';" onclick="g_Calendar.show(event,'form1.date2',false, 'yyyy-mm-dd'); return false;"><img src="images/calendar.gif" name="imgCalendar" width="34" height="21" border="0" alt=""></a>
			Time: <input type="text" name="time2" size="8" value="<?=$p_time2?>" maxlength=8>
		</td>
	</tr>
	<tr>
		<td>Account</td>
		<td>
			<input name="usr" value="<?=htmlentities(stripslashes($p_usr))?>">
		</td>
	</tr>
	<tr>
		<td>Server</td>
		<td>
			<input name="svr" value="<?=htmlentities(stripslashes($p_svr))?>">
		</td>
	</tr>
	<tr>
		<td>Client IP</td>
		<td>
			<input name="ip" value="<?=htmlentities(stripslashes($p_ip))?>">
		</td>
	</tr>
	<tr>
		<td>Option</td>
		<td>
			<input type="checkbox" name="rpt" value="1" <?=$p_rpt==1?"CHECKED":""?>>Calculate total play time for each user.
		</td>
	</tr>
</table>
<input type="Submit" value="Search">
<p>
<?=$html_results?>
</form>
</body>
</html>