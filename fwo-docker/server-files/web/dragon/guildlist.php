<?php
require('auth.php');

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_GET_VARS[wid];

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", ""))
{
	die("Access denied.");
}

$cid = $HTTP_GET_VARS[f];
if($HTTP_GET_VARS[i]!='')
{
	$GuildID=$HTTP_GET_VARS[i];
}
else
{
	$GuildID=$HTTP_POST_VARS[GuildID];
}


if($wid)
{
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid World Controller");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);


$droptableint = "DROP TABLE IF EXISTS `intdata_all`";
$updatesqlint = "
CREATE TABLE IF NOT EXISTS `intdata_all` (
  `CharID` int(10) unsigned NOT NULL default '0',
  `XPPool` int(10) unsigned NOT NULL default '0',
  `ClanID` int(10) unsigned NOT NULL default '0',
  `ElderBrotherID` int(10) unsigned NOT NULL default '0',
  `job` smallint(5) unsigned default '0',
  `AuctionGold` int(10) unsigned default '0',
  `Reason` smallint(5) unsigned default '0',
  `GuildID` int(10) unsigned default '0',
  `RClanID` smallint(5) unsigned default '0',
  `Rating` tinyint(3) unsigned default '0',
  `HeroPoints` smallint(5) unsigned default '0',
  PRIMARY KEY  (`CharID`)
)TYPE=MRG_MyISAM UNION= (intdata_0, intdata_1,intdata_2,intdata_3,intdata_4, intdata_5,intdata_6, intdata_7,intdata_8,intdata_9);
";
                                mysql_query($droptableint, $dbWc) or die(mysql_error($dbWc));
                                mysql_query($updatesqlint, $dbWc) or die(mysql_error($dbWc));

	$tbl_guildlist = "guildlist_" . ( $GuildID % 10 );
	$tbl_guildlist = "intdata_all";
	$query_rs = "SELECT c.CharID, p.CharacterName, c.Job FROM {$tbl_guildlist} c, pcharacter p WHERE c.CharID=p.CharID AND c.GuildID='{$GuildID}' AND c.CharID>0 ORDER BY c.Job";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	mysql_query("DROP TABLE IF EXISTS intdata_all", $dbWc);
/*
	if($HTTP_GET_VARS[a]!='f')
	{
		echo "<form name=form1 method=post action='gmclan.php?a=f'><input type=hidden name=ClanID value={$HTTP_POST_VARS[ClanID]}></form>";
		echo "<script>document.form1.submit()</script>";
		exit();
	}
*/
}

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
<form name="form1">
<h3>Clan Hall Member List</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<br><br>Guild: <?=$HTTP_GET_VARS[i]?> (<a href="guild.php?wid=<?=$wid?>&i=<?=$HTTP_GET_VARS[i]?>">Details</a>)
<?
if($wid!="")
{
	$member_count = mysql_num_rows($rs);
	if($member_count == 0)
	{
		echo "<br><font color=red><b>No member</b></font>";
	}
	else
	{
		$cnt = 0;
		echo "<table border=1 cellspacing=0><tr><th>#</td><th>Character</th><th>Rank</th></tr>";
		while($row=mysql_fetch_assoc($rs))
		{
			$cnt++;
			echo "<tr><td>$cnt</td><td><a href=\"pcharstat.php?i={$row[CharID]}&wid=$wid\">". U16btoU8str($row[CharacterName]) ."<img src=\"images/blank.bmp\" border=0></a></td><td>{$job_desc[$row[Job]]}</td></tr>";
		}
		echo "</table>";
	}
}
if($cid) echo "<hr>Return to <a href=\"clanally.php?wid={$wid}&i={$cid}\">Guild Hall List</a>";
?>
</form>
</body>
</html>
