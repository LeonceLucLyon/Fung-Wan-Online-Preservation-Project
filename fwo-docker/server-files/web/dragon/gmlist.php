<?php
require('auth.php');

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_GET_VARS[wid];

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", ""))
{
	die("Access denied.");
}


if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);

	if($HTTP_GET_VARS[a]=='s')
	{
		$availflag = $HTTP_POST_VARS["availflag_{$HTTP_GET_VARS[i]}"];
		$query_rs = "UPDATE gmlist SET AvailFlag='{$availflag}' WHERE Indx='{$HTTP_GET_VARS[i]}'";

		$befores = get_str_rs($dbWc, "SELECT * FROM gmlist WHERE Indx='{$HTTP_GET_VARS[i]}';");
		$rs = mysql_query($query_rs, $dbWc) or die("error". mysql_error($dbWc));
		$after = get_str_rs($dbWc, "SELECT * FROM gmlist WHERE Indx='{$HTTP_GET_VARS[i]}';");
		

		header("Location: gmlist.php?wid={$wid}");
		exit;
	}
	else
	{
		$query_rs = "SELECT * FROM gmlist g, pcharacter p WHERE g.CharID > 0 AND p.CharID=g.CharID ORDER BY g.Job, Indx DESC";
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	}
}

$htmlWc = "<select name=wid onChange=\"postform(document.form1,'gmclan.php?a=wc')\"><option value=''></option>";
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row_wc)
{
	$selected=($wid==$row_wc[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row_wc[id]}' $selected>{$row_wc[name]}</option>";
}
$htmlWc.="</select>";

?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<script language="JavaScript" type="text/JavaScript">
<!--
function postform(form,url){
	form.action=url;form.submit()
}
//-->
</script>
</head>
<body>
<form name="form1" method="POST">
<h3>Clan</h3>
(World Controller: <?echo $htmlWc;//$row_rsSvr[name]?>)
<?
if($wid!="")
{
?>
<br><br>Clan
<select name="ClanID" onchange="if(this.value.length>0)postform(document.form1,'gmclan.php?a=f')">
	<option value=''>-- Please Select One --</option>
	<option value=1 <?=$SELECTED_1?>>Supreme Sword Clan</option>
	<option value=2 <?=$SELECTED_2?>>King of Heroes Clan</option>
	<option value=3 <?=$SELECTED_3?>>Matchless Clan</option>
	<option value=4 <?=$SELECTED_4?>>Sword Worship Clan</option>
	<option value=5 <?=$SELECTED_5?>>Swift Meaning Clan</option>
	<option value=100 SELECTED>* Game Master *</option>
</select>
<hr/>
<b>* Game Master *</b>
<?
	$member_count = mysql_num_rows($rs);
	if($member_count == 0)
	{
		echo "<br><font color=red><b>No member</b></font>";
	}
	else
	{
		$cnt = 0;
		echo "<table border=1 cellspacing=0><tr><th>#</td><th>Character</th><th>Rank</th><th>AvailFlag</th><th>&nbsp;</th></tr>";
		while($row=mysql_fetch_assoc($rs))
		{
			$cnt++;
			echo "<tr><td>$cnt</td><td><a href=\"pcharstat.php?i={$row[CharID]}&wid=$wid\">". U16btoU8str($row[CharacterName]) ."</a></td><td>{$job_desc[$row[Job]]}</td><td><input name=\"availflag_{$row[Indx]}\" type=\"text\" size=2 value=\"$row[AvailFlag]\"></td><td><input type=\"button\" value=\"Save\" onclick=\"if(confirm('Overwrite?'))postform(document.form1, 'gmlist.php?wid={$wid}&a=s&i={$row[Indx]}')\"></td></tr>";
		}
		echo "</table>";
	}
}
?>
</form>
</body>
</html>