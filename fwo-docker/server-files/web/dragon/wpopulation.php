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

$y = $HTTP_POST_VARS['year'];
$m = $HTTP_POST_VARS['month'];
$d = $HTTP_POST_VARS['day'];

is_numeric($y) or $y=2003;

$html_world="<select name=\"w\" aonchange=\"document.form1.submit()\"><option>--Select--</option>";
$rs_wid = mysql_query("SELECT DISTINCT wid FROM population", $dbGmAdm) or die(mysql_error());
while($row_wid = mysql_fetch_row($rs_wid))
{
	if($row_wid[0] == $_REQUEST[w])
	{
		$selected = "SELECTED";
		$cond = "AND wid=\"{$_REQUEST[w]}\"";
	}
	else
	{
		$selected = "";
	}
	$html_world.="<option $selected>{$row_wid[0]}</option>";
}
$html_world.="</select>";

$query = "SELECT
	year(from_unixtime(dt)) as y,
	month(from_unixtime(dt)) as m,
	dayofmonth(from_unixtime(dt)) as d,
	hour(from_unixtime(dt)) as h,
	sum(cnt) / count(distinct (dt)) as n

	FROM population WHERE 1 $cond
";

if(is_numeric($y) && is_numeric($m) && is_numeric($d))
{
	$hd = "<h3>Hourly Average</h3>";
	$html = "<tr><th>Year</th><th>Month</th><th>Day</th><th>Hour</th><th>Average Online Player</th></tr>";
	$query .= "AND year(from_unixtime(dt))=$y AND month(from_unixtime(dt))=$m AND dayofmonth(from_unixtime(dt))=$d group by y,m,d,h";
}
elseif(is_numeric($y) && is_numeric($m))
{
	$hd = "<h3>Daily Average</h3>";
	$html = "<tr><th>Year</th><th>Month</th><th>Day</th><th>Average Online Player</th></tr>";
	$query .= "AND year(from_unixtime(dt))=$y AND month(from_unixtime(dt))=$m group by y,m,d";
}
else
{
	$hd = "<h3>Monthly Average</h3>";
	$html = "<tr><th>Year</th><th>Month</th><th>Average Online Player</th></tr>";
	$query .= "AND year(from_unixtime(dt))=$y group by y,m";
}

if($cond)
{
	$query .= " ORDER BY dt";
	$rs = mysql_query($query) or die(mysql_error());
	if(mysql_num_rows($rs) == 0)
	{
		$html .= "<tr><td colspan=5><font color=red>No matched queries.</font></td></tr>";
	}
	else
	{
		while($row = mysql_fetch_assoc($rs))
		{
			if(is_numeric($y) && is_numeric($m) && is_numeric($d))
			{
				$html .= "<tr><td>$row[y]</td><td>$row[m]</td><td>$row[d]</td><td>$row[h]</td><td>$row[n]</td></tr>";
			}
			elseif(is_numeric($y) && is_numeric($m))
			{
				$html .= "<tr><td>$row[y]</td><td>$row[m]</td><td>$row[d]</td><td>$row[n]</td></tr>";
			}
			else
			{
				$html .= "<tr><td>$row[y]</td><td>$row[m]</td><td>$row[n]</td></tr>";
			}
		}
	}
}
else
{
	$html = $hd = "";
}
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<!--
<link rel="STYLESHEET" type="text/css" href="calendar.css">
<script language="JavaScript" src="simplecalendar.js" type="text/javascript"></script>
-->
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
//-->
</script>
</head>
<body>
<form name="form1" method="post" action="">
<h3>Concurrent Player Statistics</h3>
(World: <?=$html_world?>)
<br><br>
<table>
<tr>
	<td>Year</td>
	<td>Month</td>
	<td>Day</td>
</tr>
<tr>
	<td>
		<select name="year">
		<?
			for ($n=2003; $n<=2006; $n++)
			{
				if($n == $y)
				{
					echo "<option value=\"$n\" SELECTED>$n</option>";
				}
				else
				{
					echo "<option value=\"$n\">$n</option>";
				}
			}
		?>
		</select>
	</td>
	<td>
		<select name="month"><option value="">-Select-</option>
		<?
			for ($n=1; $n<=12; $n++)
			{
				if($n == $m)
				{
					echo "<option value=\"$n\" SELECTED>$n</option>";
				}
				else
				{
					echo "<option value=\"$n\">$n</option>";
				}
			}
		?>
		</select>
	</td>
	<td>
		<select name="day"><option value="">-Select-</option>
		<?
			for ($n=1; $n<=31; $n++)
			{
				if($n == $d)
				{
					echo "<option value=\"$n\" SELECTED>$n</option>";
				}
				else
				{
					echo "<option value=\"$n\">$n</option>";
				}
			}
		?>
		</select>
	</td>
</tr>
</table>
<input type="submit" value="Search" onclick="if(document.form1.w.selectedIndex==0){alert('Please select the game world.');return false}">
<p>
<?=$hd?>
<table border=1 cellspacing=0>
	<?=$html?>
</table>
</form>
</body>
</html>
