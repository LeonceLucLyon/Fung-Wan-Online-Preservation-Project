<?php
require("auth.php");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$HTTP_GET_VARS["wid"];
if($wid=="")
{
	die("World controller not set");
}
else
{
	if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", ""))
	{
		die("Access denied.");
	}
	elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmchar", "w"))
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
		$query_rs = "UPDATE effects SET
			Stun = '{$HTTP_POST_VARS[Stun]}',
			Slow = '{$HTTP_POST_VARS[Slow]}',
			ImmunityID = '{$HTTP_POST_VARS[ImmunityID]}',
			RemoveImmunityID = '{$HTTP_POST_VARS[RemoveImmunityID]}',
			ConstFireDmg = '{$HTTP_POST_VARS[ConstFireDmg]}',
			ConstColdDmg = '{$HTTP_POST_VARS[ConstColdDmg]}',
			ConstPoisonDmg = '{$HTTP_POST_VARS[ConstPoisonDmg]}',
			ConstLightningDmg = '{$HTTP_POST_VARS[ConstLightningDmg]}',
			ConstPhysicalDmg = '{$HTTP_POST_VARS[ConstPhysicalDmg]}',
			ConstChiDmg = '{$HTTP_POST_VARS[ConstChiDmg]}',
			AttackPlus = '{$HTTP_POST_VARS[AttackPlus]}',
			AttackPerc = '{$HTTP_POST_VARS[AttackPerc]}',
			DefensePlus = '{$HTTP_POST_VARS[DefensePlus]}',
			DefensePerc = '{$HTTP_POST_VARS[DefensePerc]}',
			MinPhysicalDmgPlus = '{$HTTP_POST_VARS[MinPhysicalDmgPlus]}',
			MaxPhysicalDmgPlus = '{$HTTP_POST_VARS[MaxPhysicalDmgPlus]}',
			MinFireDmgPlus = '{$HTTP_POST_VARS[MinFireDmgPlus]}',
			MaxFireDmgPlus = '{$HTTP_POST_VARS[MaxFireDmgPlus]}',
			MinColdDmgPlus = '{$HTTP_POST_VARS[MinColdDmgPlus]}',
			MaxColdDmgPlus = '{$HTTP_POST_VARS[MaxColdDmgPlus]}',
			MinPoisonDmgPlus = '{$HTTP_POST_VARS[MinPoisonDmgPlus]}',
			MaxPoisonDmgPlus = '{$HTTP_POST_VARS[MaxPoisonDmgPlus]}',
			MinLightningDmgPlus = '{$HTTP_POST_VARS[MinLightningDmgPlus]}',
			MaxLightningDmgPlus = '{$HTTP_POST_VARS[MaxLightningDmgPlus]}',
			FireResistPlus = '{$HTTP_POST_VARS[FireResistPlus]}',
			ColdResistPlus = '{$HTTP_POST_VARS[ColdResistPlus]}',
			PoisonResistPlus = '{$HTTP_POST_VARS[PoisonResistPlus]}',
			LightningResistPlus = '{$HTTP_POST_VARS[LightningResistPlus]}',
			PhysicalResistPlus = '{$HTTP_POST_VARS[PhysicalResistPlus]}',
			HitPointPlus = '{$HTTP_POST_VARS[HitPointPlus]}',
			HitPointPerc = '{$HTTP_POST_VARS[HitPointPerc]}',
			ChiPlus = '{$HTTP_POST_VARS[ChiPlus]}',
			ChiPerc = '{$HTTP_POST_VARS[ChiPerc]}',
			HitPointsRegenPlus = '{$HTTP_POST_VARS[HitPointsRegenPlus]}',
			ChiPointsRegenPlus = '{$HTTP_POST_VARS[ChiPointsRegenPlus]}',
			MaxHitPointsPlus = '{$HTTP_POST_VARS[MaxHitPointsPlus]}',
			MaxHitPointsPerc = '{$HTTP_POST_VARS[MaxHitPointsPerc]}',
			MaxChiPlus = '{$HTTP_POST_VARS[MaxChiPlus]}',
			MaxChiPerc = '{$HTTP_POST_VARS[MaxChiPerc]}',
			WeightPlus = '{$HTTP_POST_VARS[WeightPlus]}',
			WeightPerc = '{$HTTP_POST_VARS[WeightPerc]}',
			BlockChangePlus = '{$HTTP_POST_VARS[BlockChangePlus]}',
			StrengthPlus = '{$HTTP_POST_VARS[StrengthPlus]}',
			ConstitutionPLus = '{$HTTP_POST_VARS[ConstitutionPLus]}',
			AgilityPlus = '{$HTTP_POST_VARS[AgilityPlus]}',
			MindPlus = '{$HTTP_POST_VARS[MindPlus]}',
			PerceptionPlus = '{$HTTP_POST_VARS[PerceptionPlus]}',
			MinInstFireDmg = '{$HTTP_POST_VARS[MinInstFireDmg]}',
			MaxInstFireDmg = '{$HTTP_POST_VARS[MaxInstFireDmg]}',
			MinInstColdDmg = '{$HTTP_POST_VARS[MinInstColdDmg]}',
			MaxInstColdDmg = '{$HTTP_POST_VARS[MaxInstColdDmg]}',
			MinInstPoisonDmg = '{$HTTP_POST_VARS[MinInstPoisonDmg]}',
			MaxInstPoisonDmg = '{$HTTP_POST_VARS[MaxInstPoisonDmg]}',
			MinInstLightningDmg = '{$HTTP_POST_VARS[MinInstLightningDmg]}',
			MaxInstLightningDmg = '{$HTTP_POST_VARS[MaxInstLightningDmg]}',
			MinInstPhysicalDmg = '{$HTTP_POST_VARS[MinInstPhysicalDmg]}',
			MaxInstPhysicalDmg = '{$HTTP_POST_VARS[MaxInstPhysicalDmg]}',
			MinInstChiDmg = '{$HTTP_POST_VARS[MinInstChiDmg]}',
			MaxInstChiDmg = '{$HTTP_POST_VARS[MaxInstChiDmg]}',
			PowerRankPlusOne = '{$HTTP_POST_VARS[PowerRankPlusOne]}',
			Icon = '{$HTTP_POST_VARS[Icon]}',
			WeaponSpeed = '{$HTTP_POST_VARS[WeaponSpeed]}',
			Entangle = '{$HTTP_POST_VARS[Entangle]}',
			HardenDefense = '{$HTTP_POST_VARS[HardenDefense]}',
			Penetrate = '{$HTTP_POST_VARS[Penetrate]}',
			ConstHPPlus = '{$HTTP_POST_VARS[ConstHPPlus]}',
			ConstChiPlus = '{$HTTP_POST_VARS[ConstChiPlus]}',
			Strength = '{$HTTP_POST_VARS[Strength]}',
			Weakness = '{$HTTP_POST_VARS[Weakness]}',
			Icon2 = '{$HTTP_POST_VARS[Icon2]}'

			WHERE EffectID='{$HTTP_GET_VARS[i]}'";

		$befores = get_str_rs($dbWc, "SELECT * FROM effects WHERE EffectID='{$HTTP_GET_VARS[i]}'");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		$after = get_str_rs($dbWc, "SELECT * FROM effects WHERE EffectID='{$HTTP_GET_VARS[i]}'");
		
		header ("Location: effects.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
		exit;
	}

	$query_rs = "SELECT * FROM effects WHERE EffectID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs) == 0) die("<font color=red><b>No matched queries.</b></font>");
	$row=mysql_fetch_assoc($rs);
	mysql_free_result($rs);
}
$readonly = $readonly_gmdata?"READONLY":"";
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
<h3>Effect</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<br><br>
  <table border="1" cellspacing=0>
    <tr>
      <td>EffectID</td>
      <td><input name="EffectID" type="text" value="<?=$row[EffectID]?>" readonly="yes"><?=getstring($row[EffectID],'effect')?></td>
    </tr>
	<?
	$eval_string='
	    <tr>
	      <td>{DATA} </td>
	      <td><input name=\'{DATA}\' type=\'text\' value=\'$row[{DATA}]\' $readonly></td>
	    </tr>
	';
	$fields = array('Stun','Slow','ImmunityID','RemoveImmunityID','ConstFireDmg','ConstColdDmg','ConstPoisonDmg','ConstLightningDmg','ConstPhysicalDmg','ConstChiDmg','AttackPlus','AttackPerc','DefensePlus','DefensePerc','MinPhysicalDmgPlus','MaxPhysicalDmgPlus','MinFireDmgPlus','MaxFireDmgPlus','MinColdDmgPlus','MaxColdDmgPlus','MinPoisonDmgPlus','MaxPoisonDmgPlus','MinLightningDmgPlus','MaxLightningDmgPlus','FireResistPlus','ColdResistPlus','PoisonResistPlus','LightningResistPlus','PhysicalResistPlus','HitPointPlus','HitPointPerc','ChiPlus','ChiPerc','HitPointsRegenPlus','ChiPointsRegenPlus','MaxHitPointsPlus','MaxHitPointsPerc','MaxChiPlus','MaxChiPerc','WeightPlus','WeightPerc','BlockChangePlus','StrengthPlus','ConstitutionPLus','AgilityPlus','MindPlus','PerceptionPlus','MinInstFireDmg','MaxInstFireDmg','MinInstColdDmg','MaxInstColdDmg','MinInstPoisonDmg','MaxInstPoisonDmg','MinInstLightningDmg','MaxInstLightningDmg','MinInstPhysicalDmg','MaxInstPhysicalDmg','MinInstChiDmg','MaxInstChiDmg','PowerRankPlusOne','Icon','WeaponSpeed','Entangle','HardenDefense','Penetrate','ConstHPPlus','ConstChiPlus','Strength','Weakness','Icon2');

	foreach($fields as $field)
	{
		$new = str_replace("{DATA}", $field, $eval_string);
		eval("echo \"$new\";");
	}
	?>
  </table>
	<?
	if(!$readonly_gmdata)
	{
	?>
    <input type="reset" name="Reset" value="Reset">
    <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'effects.php?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
	  <?
	  }
	  ?>
</form>
</body>
</html>
