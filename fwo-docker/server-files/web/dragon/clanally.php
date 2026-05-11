<?php
require('auth.php');

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_GET_VARS[wid];

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", ""))
{
	die("Access denied.");
}

if($HTTP_GET_VARS[i]!='')
{
	$ClanID=$HTTP_GET_VARS[i];
}
else
{
	$ClanID=$HTTP_POST_VARS[ClanID];
}

$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}

/*
if($query_rs)
{
*/
	if($wid)
	{
		$grand_total = 0;
		$html_guild = "";

		$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
		$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
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



		$rs_ally = mysql_query("SELECT * FROM ally WHERE ClanID='{$ClanID}'", $dbWc) or die(mysql_error());
		while($row = mysql_fetch_assoc($rs_ally))
		{
			$rank_1 = $rank_2 = $rank_3 = $rank_4 = $rank_5 = $rank_0 = "&nbsp;";
			$total = 0;

			$GuildID = $row[GuildID];
			if($GuildID==0) continue;
			$tbl_guildlist = "guildlist_" . ( $GuildID % 10 );
			$tbl_guildlist = "intdata_all";
/*
			//find guild lead
			$query_rs = "SELECT *, CharacterName FROM {$tbl_guildlist} WHERE CharID>0 AND Job IN (1, 2, 3)";
			$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
			while($row_guild = mysql_fetch_assoc($rs))
			{
				$row_guild[Job] = $row_guild[CharID]
			}
			mysql_free_result($rs);
*/
			//count guild member
			$query_rs = "SELECT Job, COUNT(1) AS no FROM {$tbl_guildlist} WHERE CharID>0 AND GuildID='{$GuildID}' GROUP BY Job";
			$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
			while($row_guild = mysql_fetch_assoc($rs))
			{
				eval("\$rank_{$row_guild[Job]} = {$row_guild[no]};");
				$total += $row_guild[no];
			}
			mysql_free_result($rs);
			$grand_total += $total;
			if($total == 0)
			{
				$html_guild .= "<tr bgcolor=DDDDDD><td>$GuildID</td>";
			}
			else
			{
				$html_guild .= "<tr><td><a href=\"guildlist.php?wid=$wid&i=$GuildID&f=$ClanID\">$GuildID</a></td>";
			}
			$html_guild .= "<td>$rank_1</td><td>$rank_2</td><td>$rank_3</td><td>$rank_4</td><td>$rank_5</td><td>$total</td><td><a href=\"guild.php?wid=$wid&i=$GuildID\">Details</a></td></tr>";
		}
		mysql_query("DROP TABLE IF EXISTS intdata_all", $dbWc);
}
if($ClanID == 100)
{
	header("Location: gmlist.php?wid=$wid&ClanID=100");
	exit();
}

$SELECTED_1 = $SELECTED_2 = $SELECTED_3 = $SELECTED_4 = $SELECTED_5 = "";
eval("\$SELECTED_{$ClanID} = \"SELECTED\";");

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
<h3>Clan Hall List</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<p>Clan <select name="ClanID" onchange="if(this.value.length>0)postform(document.form1,'clanally.php?wid=<?=$wid?>&i=' + this.value)">
	<option value=''>-- Please Select One --</option>
	<option value=1 <?=$SELECTED_1?>>Supreme Sword Clan</option>
	<option value=2 <?=$SELECTED_2?>>King of Heroes Clan</option>
	<option value=3 <?=$SELECTED_3?>>Matchless Clan</option>
	<option value=4 <?=$SELECTED_4?>>Sword Worship Clan</option>
	<option value=5 <?=$SELECTED_5?>>Swift Meaning Clan</option>
	<option value=100>* Game Master *</option>
</select>

<p>Total clan member(s): <?=$grand_total?>
<table border=1 cellspacing=0>
	<tr><td>Guild</td><td>Leader</td><td>Minister</td><td>Master</td><td>Senior</td><td>Member</td><td>Count</td><td>&nbsp;</td></tr>
	<?=$html_guild?>
</table>
</form>
</body>
</html>
