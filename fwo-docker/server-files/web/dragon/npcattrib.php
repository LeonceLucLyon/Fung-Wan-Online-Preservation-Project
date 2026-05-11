<?php
require("auth.php");
//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_GET_VARS["wid"];
if($wid=="")
{
	die("World controller not set");
}
else
{
	if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", ""))
	{
		die("Access denied.");
	}
	elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
	{
		die("Access denied. Read-Only.");
	}

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];

	if($HTTP_GET_VARS[a]=="s")
	{
		$query_rs = "UPDATE npcattrib SET
			ModelID= '{$HTTP_POST_VARS[ModelID]}',
			AttachmentID= '{$HTTP_POST_VARS[AttachmentID]}',
			AttRating= '{$HTTP_POST_VARS[AttRating]}',
			DefRating= '{$HTTP_POST_VARS[DefRating]}',
			MinFireDmg= '{$HTTP_POST_VARS[MinFireDmg]}',
			MaxFireDmg= '{$HTTP_POST_VARS[MaxFireDmg]}',
			MinColdDmg= '{$HTTP_POST_VARS[MinColdDmg]}',
			MaxColdDmg= '{$HTTP_POST_VARS[MaxColdDmg]}',
			MinLightningDmg= '{$HTTP_POST_VARS[MinLightningDmg]}',
			MaxLightningDmg= '{$HTTP_POST_VARS[MaxLightningDmg]}',
			MinPoisonDmg= '{$HTTP_POST_VARS[MinPoisonDmg]}',
			MaxPoisonDmg= '{$HTTP_POST_VARS[MaxPoisonDmg]}',
			MinPhysicalDmg= '{$HTTP_POST_VARS[MinPhysicalDmg]}',
			MaxPhysicalDmg= '{$HTTP_POST_VARS[MaxPhysicalDmg]}',
			MaxHitPoints= '{$HTTP_POST_VARS[MaxHitPoints]}',
			HitPointRegen= '{$HTTP_POST_VARS[HitPointRegen]}',
			FireResist= '{$HTTP_POST_VARS[FireResist]}',
			ColdResist= '{$HTTP_POST_VARS[ColdResist]}',
			LightningResist= '{$HTTP_POST_VARS[LightningResist]}',
			PoisonResist= '{$HTTP_POST_VARS[PoisonResist]}',
			PhysicalResist= '{$HTTP_POST_VARS[PhysicalResist]}',
			MoveRate= '{$HTTP_POST_VARS[MoveRate]}',
			XPValue= '{$HTTP_POST_VARS[XPValue]}',
			XPperHP= '{$HTTP_POST_VARS[XPperHP]}',
			Level= '{$HTTP_POST_VARS[Level]}',
			Clan= '{$HTTP_POST_VARS[Clan]}',
			StanceID= '{$HTTP_POST_VARS[StanceID]}',
			AnimStanceID= '{$HTTP_POST_VARS[AnimStanceID]}',
			PowerID1= '{$HTTP_POST_VARS[PowerID1]}',
			PowerRank1= '{$HTTP_POST_VARS[PowerRank1]}',
			PowerID2= '{$HTTP_POST_VARS[PowerID2]}',
			PowerRank2= '{$HTTP_POST_VARS[PowerRank2]}',
			PowerID3= '{$HTTP_POST_VARS[PowerID3]}',
			PowerRank3= '{$HTTP_POST_VARS[PowerRank3]}',
			TreasureTableID= '{$HTTP_POST_VARS[TreasureTableID]}',
			Invisible= '{$HTTP_POST_VARS[Invisible]}',
			AggressiveFlag= '{$HTTP_POST_VARS[AggressiveFlag]}',
			AggroValue= '{$HTTP_POST_VARS[AggroValue]}',
			ScanOption= '{$HTTP_POST_VARS[ScanOption]}',
			PowerMultiplier= '{$HTTP_POST_VARS[PowerMultiplier]}',
			MeleeMultiplier= '{$HTTP_POST_VARS[MeleeMultiplier]}',
			RangeMultiplier= '{$HTTP_POST_VARS[RangeMultiplier]}',
			RetreatHitPoints= '{$HTTP_POST_VARS[RetreatHitPoints]}',
			ReturnFlag= '{$HTTP_POST_VARS[ReturnFlag]}',
			ChallengeLevel= '{$HTTP_POST_VARS[ChallengeLevel]}',
			ScriptID= '{$HTTP_POST_VARS[ScriptID]}',
			NameID= '{$HTTP_POST_VARS[NameID]}',
			TargetType= '{$HTTP_POST_VARS[TargetType]}',
			MeleePerc= '{$HTTP_POST_VARS[MeleePerc]}',
			PowerPerc1= '{$HTTP_POST_VARS[PowerPerc1]}',
			PowerPerc2= '{$HTTP_POST_VARS[PowerPerc2]}',
			PowerPerc3= '{$HTTP_POST_VARS[PowerPerc3]}',
			IsGuard= '{$HTTP_POST_VARS[IsGuard]}',
			HalfMoveRate= '{$HTTP_POST_VARS[HalfMoveRate]}',
			MeleeRange= '{$HTTP_POST_VARS[MeleeRange]}',
			PowerRange1= '{$HTTP_POST_VARS[PowerRange1]}',
			PowerRange2= '{$HTTP_POST_VARS[PowerRange2]}',
			PowerRange3= '{$HTTP_POST_VARS[PowerRange3]}',
			EffectID= '{$HTTP_POST_VARS[EffectID]}',
			ScanAreaRange= '{$HTTP_POST_VARS[ScanAreaRange]}',
			DoClanRating= '{$HTTP_POST_VARS[DoClanRating]}',
			EnemyClan= '{$HTTP_POST_VARS[EnemyClan]}',
			UpValue= '{$HTTP_POST_VARS[UpValue]}',
			DownValue= '{$HTTP_POST_VARS[DownValue]}',
			IsSNPC= '{$HTTP_POST_VARS[IsSNPC]}',
			InvisiblePerc= '{$HTTP_POST_VARS[InvisiblePerc]}',
			MaxItemCount= '{$HTTP_POST_VARS[MaxItemCount]}',
			PermanentDeath= '{$HTTP_POST_VARS[PermanentDeath]}',
			IsDead= '{$HTTP_POST_VARS[IsDead]}',
			WeaponSpeed= '{$HTTP_POST_VARS[WeaponSpeed]}'

			WHERE AttribID='{$HTTP_GET_VARS[i]}'";

		$befores = get_str_rs($dbWc, "SELECT * FROM npcattrib WHERE AttribID='{$HTTP_GET_VARS[i]}'");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		$after = get_str_rs($dbWc, "SELECT * FROM npcattrib WHERE AttribID='{$HTTP_GET_VARS[i]}'");
		
		header ("Location: npcattrib.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
		exit;
	}
	elseif($HTTP_GET_VARS[a]=="d")
	{
	}

	$query_rs = "SELECT * FROM npcattrib WHERE AttribID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs) == 0) die("<font color=red><b>No matched queries.</b></font>");
	$row=mysql_fetch_assoc($rs);
	mysql_free_result($rs);
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
<h3>NPC Attributes</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<br><br>
  <table border="1" cellspacing=0>
    <tr>
      <td>AttribID</td>
      <td><input name="AttribID" type="text" value="<?=$row[AttribID]?>" readonly="yes"><?=getstring($row[AttribID],'attrib')?></td>
    </tr>

<?
$eval_string='
    <tr>
      <td>{DATA} </td>
      <td><input name=\'{DATA}\' type=\'text\' value=\'$row[{DATA}]\'></td>
    </tr>
';

	$fields = array("ModelID", "AttachmentID", "AttRating", "DefRating", "MinFireDmg", "MaxFireDmg", "MinColdDmg", "MaxColdDmg", "MinLightningDmg", "MaxLightningDmg", "MinPoisonDmg", "MaxPoisonDmg", "MinPhysicalDmg", "MaxPhysicalDmg", "MaxHitPoints", "HitPointRegen", "FireResist", "ColdResist", "LightningResist", "PoisonResist", "PhysicalResist", "MoveRate", "XPperHP", "XPValue", "Level", "Clan", "StanceID", "AnimStanceID", "PowerID1", "PowerRank1", "PowerID2", "PowerRank2", "PowerID3", "PowerRank3", "TreasureTableID", "Invisible", "AggressiveFlag", "AggroValue", "ScanOption", "PowerMultiplier", "MeleeMultiplier", "RangeMultiplier", "RetreatHitPoints", "ReturnFlag", "ChallengeLevel", "ScriptID", "NameID", "TargetType", "MeleePerc", "PowerPerc1", "PowerPerc2", "PowerPerc3", "IsGuard", "HalfMoveRate", "MeleeRange", "PowerRange1", "PowerRange2", "PowerRange3", "EffectID", "ScanAreaRange", "DoClanRating", "EnemyClan", "UpValue", "DownValue", "IsSNPC", "InvisiblePerc", "MaxItemCount", "PermanentDeath", "IsDead", "WeaponSpeed");


	foreach($fields as $field)
	{
		$new = str_replace("{DATA}", $field, $eval_string);
		eval("echo \"$new\";");
	}
?>

  </table>
    <input type="reset" name="Reset" value="Reset">
    <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'npcattrib.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
</form>
</body>
</html>
