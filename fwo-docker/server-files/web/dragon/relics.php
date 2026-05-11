<?
require("auth.php");
//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_POST_VARS[wid];
if($HTTP_GET_VARS[wid])$wid=$HTTP_GET_VARS[wid];
if($wid=="")
{
	$wid=$HTTP_SESSION_VARS['wid'];
}
else
{
	$HTTP_SESSION_VARS['wid']=$wid;
}

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", ""))
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("Access denied.");
}

$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wid onChange=\"postform(document.form1,'relics.php?a=wc')\"><option value=''></option>";
//while($row=mysql_fetch_assoc($rsWc))
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
$html="";
mysql_free_result($rsWc);


if($wid)
{

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);


$sql_all_unique_items ="SELECT ItemID FROM item WHERE ItemID>1376256 AND ItemID<1441792";


// with the clan?
$sqlt_clan_has_it = 'SELECT ClanID FROM clan WHERE
	Relic1=\'{$unique_item_id}\'
	OR Relic2=\'{$unique_item_id}\'
	OR Relic3=\'{$unique_item_id}\'
	OR Relic4=\'{$unique_item_id}\'
	OR Relic5=\'{$unique_item_id}\'
	OR Relic6=\'{$unique_item_id}\'
	OR Relic7=\'{$unique_item_id}\'
	OR Relic8=\'{$unique_item_id}\'
	OR Relic9=\'{$unique_item_id}\'
	OR Relic10=\'{$unique_item_id}\'
	OR Relic11=\'{$unique_item_id}\'
	OR Relic12=\'{$unique_item_id}\'
	OR Relic13=\'{$unique_item_id}\'
	OR Relic14=\'{$unique_item_id}\'
	OR Relic15=\'{$unique_item_id}\'
	OR Relic16=\'{$unique_item_id}\'
';

$relics_set=array();

// get all unique items
$rs_unique_items = mysql_query($sql_all_unique_items, $dbWc) or die(mysql_error());
$relic_count = mysql_num_rows($rs_unique_items);
$relic_count = 5;
while(list($unique_item_id) = mysql_fetch_row($rs_unique_items))
{
	// check which clan has it
	eval("\$sql=\"$sqlt_clan_has_it\";");
	$rs_clan = mysql_query($sql, $dbWc) or die(mysql_error($dbWc));
	if(mysql_num_rows($rs_clan) > 0)
	{
		// found the clan having it
		list($clan_id) = mysql_fetch_row($rs_clan);
		mysql_free_result($rs_clan);
		array_push($relics_set, $unique_item_id, "ClanID", $clan_id);
		continue;
	}

	// check who has it
	for($n=0;$n<10;$n++)
	{
		$rs_owner = mysql_query("SELECT CharID FROM charinv_{$n} WHERE ItemID='{$unique_item_id}'", $dbWc) or die(mysql_error($dbWc));
		if(mysql_num_rows($rs_owner) > 0)
		{
			// found the carrier
			list($char_id) = mysql_fetch_row($rs_owner);
			mysql_free_result($rs_owner);
			array_push($relics_set, $unique_item_id, "CharID", $char_id);
			continue 2;
		}
	}

//	array_push($relics_set, $unique_item_id, "CharID", 1073761594);
	array_push($relics_set, $unique_item_id, "", "");
}
$html.= "<table border=1 cellspacing=0>";
for($n = 0; $n < $relic_count ; $n++)
{
	$html.= "<tr><td>".($n+1)."</td><td><a href='uitem.php?wid=$wid&i={$relics_set[$n * 3]}'>" . getstring($relics_set[$n * 3],'item') . "</a></td>";

	if($relics_set[($n * 3) + 1] == 'CharID')
	{
		$rs_char=mysql_query("SELECT CharacterName, SceneID FROM pcharacter WHERE CharID='{$relics_set[$n * 3 + 2]}'", $dbWc) or die(mysql_error());
		list($char_nm, $scene_id) = mysql_fetch_row($rs_char);
		$html.= "<td>with <a href='pcharacter.php?wid=$wid&i={$relics_set[($n * 3) + 2]}'>" . u16btou8str($char_nm) . "<img src='images/blank.bmp' border=0></a> ($scene_id)</td>";
	}
	elseif($relics_set[($n * 3) + 1] == 'ClanID')
	{
		$html.="<td>at <a href='gmclan.php?wid=$wid&a=f&i={$relics_set[$n * 3 + 2]}'>{$clan_name[$relics_set[$n * 3 + 2]]}</a></td>";
	}
	else
	{
		$html.="<td>missing!!</td>";
	}
	$html.="</tr>";
}
$html.="</table>";
if(!$readonly_gmdata) $html .= "<p>Technical explaination:<br>Missing relics are those items, with ItemID from 1376257 to 1441791, in fwworlddevdb.item, but exist neither in fwworlddevdb.clan nor fwworlddevdb.charinv_[0-9].";
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
	form.method='post';form.action=url;form.submit()
}

//-->
</script>
</head>
<body>
<form name="form1" method="post" action="">
<h3>Relic Location</h3>
(World Controller: <?=$htmlWc?>)
</form>
<?=$html?>
