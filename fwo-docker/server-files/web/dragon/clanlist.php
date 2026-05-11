<?php
require('auth.php');

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_GET_VARS[wid];

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", ""))
{
	die("Access denied.");
}

switch($HTTP_GET_VARS['a'])
{
	case 'wc':
		if($HTTP_POST_VARS[wc_id]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wc_id];
		break;

	case 'f':
		if($HTTP_GET_VARS[i]!='')
		{
			$ClanID=$HTTP_GET_VARS[i];
		}
		else
		{
			$ClanID=$HTTP_POST_VARS[ClanID];
		}

		$tbl_clanlist = "clanlist_" . ( $ClanID % 10 );
		$query_rs = "SELECT c.CharID, p.CharacterName, c.Job FROM {$tbl_clanlist} c, pcharacter p WHERE c.CharID=p.CharID AND c.ClanID='{$ClanID}' AND c.CharID>0 ORDER BY c.Job";
		break;
}

$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wc_id onChange=\"postform(document.form1,'gmchar.php?a=wc')\"><option value=''></option>";
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
mysql_free_result($rsWc);

if($query_rs)
{
	if($wid)
	{
		$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
		$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
		mysql_free_result($rsSvr);
		$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
		mysql_select_db($row_rsSvr[db], $dbWc);
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());

		if($HTTP_GET_VARS[a]!='f')
		{
			echo "<form name=form1 method=post action='gmclan.php?a=f'><input type=hidden name=ClanID value={$HTTP_POST_VARS[ClanID]}></form>";
			echo "<script>document.form1.submit()</script>";
			exit();
		}
	}
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
WorldController: <?=$row_rsSvr[name]?>
<form name="form1">
<?php
if($wid!="")
{
	if($HTTP_GET_VARS["a"]=="f")
	{
		echo "Clan: {$clan_name[$HTTP_GET_VARS[i]]}";
		$member_count = mysql_num_rows($rs);
		if($member_count == 0)
		{
			echo "<p><font color=red><b>No member</b></font>";
		}
		else
		{
			$cnt = 0;
			echo "<p><table border=1><tr><th>#</td><th>Character</th><th>Rank</th></tr>";
			while($row=mysql_fetch_assoc($rs))
			{
				$cnt++;
				echo "<tr><td>$cnt</td><td><a href=\"pcharstat.php?i={$row[CharID]}&wid=$wid\">". U16btoU8str($row[CharacterName]) ."</a></td><td>{$job_desc[$row[Job]]}</td></tr>";
			}
			echo "</table>";
		}
	}
}
?>
</form>
</body>
</html>