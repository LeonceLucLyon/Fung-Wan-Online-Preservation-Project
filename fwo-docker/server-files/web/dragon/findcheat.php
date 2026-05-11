<?php
require('auth.php');

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

if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "") )
{
	$HTTP_SESSION_VARS['wid'] = '';
	die("only game admin allowed edit");
}


switch($HTTP_GET_VARS['a'])
{
	case 'wc':
//		if($HTTP_POST_VARS[wid]!='')$HTTP_SESSION_VARS['wc']=$HTTP_POST_VARS[wid];
		break;

	case 'as':
		if($HTTP_POST_VARS[as_id]!='')$HTTP_SESSION_VARS['as']=$HTTP_POST_VARS[as_id];
		break;
	case 'f':
		$cond_charid = $HTTP_POST_VARS[charid_0]!=""? " AND CharID LIKE \"$HTTP_POST_VARS[charid_0]\"":"";
//		$cond_charnm = $HTTP_POST_VARS[charnm_0]!=""? " AND CharacterName LIKE \"$HTTP_POST_VARS[charnm_0]\"":"";
		if($HTTP_POST_VARS[charnm_0]!="")
		{
			$cond_charnm = " AND CharacterName LIKE \"". addslashes(U8toU16($HTTP_POST_VARS[charnm_0])) ."%\"";
		}
//die(hexstring(U8toU16($HTTP_POST_VARS[charnm_0])));
		$cond_usernm = $HTTP_POST_VARS[usernm_0]!=""? " AND Username LIKE \"$HTTP_POST_VARS[usernm_0]\"":"";
		$query_rs = "SELECT * FROM pcharacter WHERE 1 $cond_charid $cond_charnm $cond_usernm";
		break;
}

$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wid onChange=\"postform(document.form1,'findcheat.php?a=wc')\"><option value=''></option>";
//while($row=mysql_fetch_assoc($rsWc))
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
mysql_free_result($rsWc);

	if($wid)
	{
		$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
		$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
		mysql_free_result($rsSvr);
		$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
		mysql_select_db($row_rsSvr[db], $dbWc);// or die(mysql_error());
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
<form name="form1" method="post" action="">
<h3>Cheat Character Search</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
 <?php
if($wid!="")
{

$level_limit=$HTTP_POST_VARS['level_limit'];
if(strlen($level_limit)==0)$level_limit=100;
$gold_limit=$HTTP_POST_VARS['gold_limit'];
if(strlen($gold_limit)==0)$gold_limit=999999;
$stance_limit=$HTTP_POST_VARS['stance_limit'];
if(strlen($stance_limit)==0)$stance_limit=50;
$power_limit=$HTTP_POST_VARS['power_limit'];
if(strlen($power_limit)==0)$power_limit=50;
$attribute_limit=$HTTP_POST_VARS['attribute_limit'];
if(strlen($attribute_limit)==0)$attribute_limit=50;

$chk_level = $HTTP_POST_VARS['chk_level'];
$chk_stancerank = $HTTP_POST_VARS['chk_stancerank'];
$chk_powerrank = $HTTP_POST_VARS['chk_powerrank'];
$chk_skillrank = $HTTP_POST_VARS['chk_skillrank'];
$chk_gold = $HTTP_POST_VARS['chk_gold'];
$chk_power = $HTTP_POST_VARS['chk_power'];
$chk_stats = $HTTP_POST_VARS['chk_stats'];
$chk_skill = $HTTP_POST_VARS['chk_skill'];
$chk_stance = $HTTP_POST_VARS['chk_stance'];
$chk_attribute = $HTTP_POST_VARS['chk_attribute'];

?>

<b>Filtering Categories</b><br>
<input name=chk_level type=checkbox value=1
<?if($chk_level)echo "CHECKED"?>
>Level >  <input name=level_limit value='<?=$level_limit?>' size=3>
<BR><input name=chk_stancerank type=checkbox value=1
<?if($chk_stancerank)echo "CHECKED"?>
>Unmatched stance points
<BR><input name=chk_powerrank type=checkbox value=1
<?if($chk_powerrank)echo "CHECKED"?>
>Unmatched power points
<BR><input name=chk_skillrank type=checkbox value=1
<?if($chk_skillrank)echo "CHECKED"?>
>Unmatched skill points
<br><input name=chk_gold type=checkbox value=1
<?if($chk_gold)echo "CHECKED"?>
>Total gold > <input name=gold_limit value='<?=$gold_limit?>' size=8>
<br><input name=chk_stats type=checkbox value=1
<?if($chk_stats)echo "CHECKED"?>
>Unmatched total stats
<br><input name=chk_stance type=checkbox value=1
<?if($chk_stance)echo "CHECKED"?>
>Unallocated stance points > <input name=stance_limit value='<?=$stance_limit?>' size=3>
<br><input name=chk_power type=checkbox value=1
<?if($chk_power)echo "CHECKED"?>
>Unallocated power points > <input name=power_limit value='<?=$power_limit?>' size=3>
<br><input name=chk_attribute type=checkbox value=1
<?if($chk_attribute)echo "CHECKED"?>
>Unallocated attribute points > <input name=attribute_limit value='<?=$attribute_limit?>' size=3>
<br><input type=submit value="Search">


<?
if($HTTP_SERVER_VARS['REQUEST_METHOD']=='POST'){

$rs_character = mysql_query("SELECT CharID, CharacterName, Username FROM pcharacter;", $dbWc) or die(mysql_error());


$count = 1;

while(list($charid, $charactername, $username) = mysql_fetch_row($rs_character))
{
	$charactername = htmlspecialchars(U16btoU8str($charactername));
	$tbl_no = $charid % 10;
	$rs_charstat = mysql_query("SELECT Level, AttributePoints, StancePoints, PowerPoints, SkillPoints, stashgold, chargold, Strength, Constitution, Agility, Mind, Perception FROM pcharstats_{$tbl_no} WHERE CharID='{$charid}';", $dbWc) or die(mysql_error());
	if($chk_skillrank)$rs_skillrank = mysql_query("SELECT sum(Rank) FROM skilllist_{$tbl_no} WHERE CharID = '{$charid}' AND SkillID > 0;", $dbWc) or die(mysql_error());
	if($chk_stancerank)$rs_stancerank = mysql_query("SELECT sum(Rank) FROM stancelist_{$tbl_no} WHERE CharID = '{$charid}' AND StanceID > 0;", $dbWc) or die(mysql_error());
	if($chk_powerrank)$rs_powerrank = mysql_query("SELECT sum(Rank) FROM powerlist_{$tbl_no} WHERE CharID = '{$charid}' AND PowerID > 0;", $dbWc) or die(mysql_error());

	list($level, $attribute, $stance, $power, $skill, $stashgold, $chargold, $strength, $constitution, $agility, $mind, $perception) = mysql_fetch_row($rs_charstat);
	if($chk_skillrank)
	{
		list($total_skillrank) = mysql_fetch_row($rs_skillrank);
		if(!$total_skillrank) $total_skillrank = 0;
	}
	if($chk_powerrank)
	{
		list($total_powerrank) = mysql_fetch_row($rs_powerrank);
		if(!$total_powerrank) $total_powerrank= 0;
	}
	if($chk_stancerank)
	{
		list($total_stancerank) = mysql_fetch_row($rs_stancerank);
		if(!$total_stancerank) $total_stancerank = 0;
	}

	mysql_free_result($rs_charstat);
	if($chk_skillrank)mysql_free_result($rs_skillrank);
	if($chk_powerrank)mysql_free_result($rs_powerrank);
	if($chk_stancerank)mysql_free_result($rs_stancerank);

	$is_level_suspecious = $level > $level_limit;
	if($chk_stancerank)$is_total_stancerank_valid= $total_stancerank+ $stance== $level;
	if($chk_powerrank)$is_total_powerrank_valid = $total_powerrank + $power == floor($level / 2) || $total_powerrank + $power == (floor($level / 2)) + 1;
	if($chk_skillrank)$is_total_skillrank_valid = $total_skillrank + $skill == ($level - 1) * 3 + 4;
	if($chk_gold)$is_gold_suspecious = $stashgold + $chargold > $gold_limit;
	if($chk_stats)$is_total_stat_valid = $strength + $constitution + $agility + $mind + $perception + $attribute == ($level - 1) * 3 + 53;
	if($chk_stance)$is_stance_suspecious = $stance > $stance_limit;
	if($chk_power)$is_power_suspecious = $power > $power_limit;
	if($chk_attribute)$is_attribute_suspecious = $attribute > $attribute_limit;

	$is_character_hacked = ($chk_level && $is_level_suspecious)
				|| ($chk_stancerank && !$is_total_stancerank_valid)
				|| ($chk_skillrank && !$is_total_skillrank_valid)
				|| ($chk_powerrank && !$is_total_powerrank_valid)
				|| ($chk_gold && $is_gold_suspecious)
				|| ($chk_stats && !$is_total_stat_valid)
				|| ($chk_power && $is_power_suspecious)
				|| ($chk_attribute && $is_attribute_suspecious)
				|| ($chk_stance && $is_stance_suspecious);

	if($is_character_hacked)
	{

if($count==1)
{
echo "<p><table border=1><tr><td>#</td><td>Cheat Character</td>";
if($chk_level)echo "<td>Level > $level_limit</td>";
if($chk_stancerank)echo "<td>Unmatched StanceRank<br>[Allocated]+[Unallocated]=[Actual]:[Expected]</td>";
if($chk_powerrank)echo "<td>Unmatched Powerrank<br>[Allocated]+[Unallocated]=[Actual]:[Expected]</td>";
if($chk_skillrank)echo "<td>Unmatched Skillrank<br>[Allocated]+[Unallocated]=[Actual]:[Expected]</td>";
if($chk_gold)echo "<td>Total Gold > $gold_limit<br>[In Stash] + [Carried] = [Total Gold]</td>";
if($chk_stats)echo "<td>Stats <br>[strength]+[constitution]+[agility]+[mind]+[perception]+[Unallocated Attribute Points]=[Stat]:[Expected]</td>";
if($chk_stance)echo "<td>Unallocated Stance Points > $stance_limit</td>";
if($chk_power)echo "<td>Unallocated Power Points > $power_limit</td>";
if($chk_attribute)echo "<td>Unallocated Attribute Points > $attribute_limit</td>";
echo "</tr>";
}

		echo "<tr><td>".$count++."</td><td><a href='pcharstat.php?i=$charid&wid=$wid' target='p$charid'>$charactername<img src='images/blank.bmp' border=0></a></td>";
		if($chk_level)
		{
			if($is_level_suspecious)
				echo "<td>$level</td>";
			else
				echo "<td> (correct) </td>";
		}
		if($chk_stancerank)
		{
			if(!$is_total_stancerank_valid)
				echo "<td>$total_stancerank + $stance = " . ($total_stancerank + $stance) . " : $level</td>";
			else
				echo "<td> (correct) </td>";
		}
		if($chk_powerrank)
		{
			if(!$is_total_powerrank_valid)
				echo "<td>$total_powerrank + $power = " . ($total_powerrank + $power) . " : " . floor($level / 2) . "</td>";
			else
				echo "<td> (correct) </td>";
		}
		if($chk_skillrank)
		{
			if(!$is_total_skillrank_valid)
				echo "<td>$total_skillrank + $skill = " . ($total_skillrank + $skill) . " : " . (($level - 1) * 3 + 4) . "</td>";
			else
				echo "<td> (correct) </td>";
		}
		if($chk_gold)
		{
			if($is_gold_suspecious)
				echo "<td>$stashgold + $chargold = " . ($stashgold + $chargold) . "</td>";
			else
				echo "<td> (correct) </td>";
		}
		if($chk_stats)
		{
			if(!$is_total_stat_valid)
				echo "<td>$strength + $constitution + $agility + $mind + $perception + $attribute =" . ($strength + $constitution + $agility + $mind + $perception + $attribute) . " : " . (($level - 1) * 3 + 53) . "</td>";
			else
				echo "<td> (correct) </td>";
		}
		if($chk_stance)
		{
			if($is_stance_suspecious)
				echo "<td>{$stance}</td>";
			else
				echo "<td> (correct) </td>";
		}
		if($chk_power)
		{
			if($is_power_suspecious)
				echo "<td>{$power}</td>";
			else
				echo "<td> (correct) </td>";
		}
		if($chk_attribute)
		{
			if($is_attribute_suspecious)
				echo "<td>{$attribute}</td>";
			else
				echo "<td> (correct) </td>";
		}
	}
}
echo "</table>";
if($count == 1) echo "<p><font color=red><b>No matched queries.</b></font>";

mysql_free_result($rs_character);
}

}
?>
</form>
</body>
</html>