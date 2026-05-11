<?
require('auth.php');
//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);


$wid=$HTTP_GET_VARS["wid"];
$wid2=$HTTP_GET_VARS["wid2"];
$CharID=$HTTP_GET_VARS['cid1'];
$CharID2=$HTTP_GET_VARS['cid2'];

if(!($wid=='' || $wid2=='' || $CharID=='' || $CharID2==''))
{

	if(!has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "r"))
	{
		$HTTP_SESSION_VARS['wid'] = '';
		die("Access denied.");
	}

	if(!has_perm($HTTP_SESSION_VARS['userid'], $wid2, "gmdata", "w"))
	{
		$HTTP_SESSION_VARS['wid'] = '';
		die("Access denied.");
	}

//if($wid=="")die("World controller not set");

	mysql_select_db($database_dbGmAdm, $dbGmAdm);
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die("err1: ". mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller $wid");
	mysql_free_result($rsSvr);
	$wc1_db = $row_rsSvr[db];
	$dbWc = mysql_connect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die("err2: ". mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);

//if($wid2=="")die("World controller2 not set");

	mysql_select_db($database_dbGmAdm, $dbGmAdm);
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid2}'", $dbGmAdm) or die("err3: ". mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller $wid2");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc2 = mysql_connect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die("err4: ". mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc2);

$hash = $CharID % 10;
$hash2 = $CharID2 % 10;

/* start pcharacter */

mysql_select_db($wc1_db, $dbWc);
$rs = mysql_query("SELECT * FROM pcharacter WHERE CharID='$CharID'", $dbWc) or die("err5" . mysql_error($dbWc));
if(mysql_num_rows($rs) != 1) die("<font color=red><b>No such character ID, $CharID.</b></font>");

$row = mysql_fetch_assoc($rs);
mysql_free_result($rs);

$SceneID = $row['SceneID'];
$x = $row['x'];
$y = $row['y'];
$z = $row['z'];
$Facing = $row['Facing'];
$BindSceneID = $row['BindSceneID'];
$BindX = $row['BindX'];
$BindY = $row['BindY'];
$BindZ = $row['BindZ'];
$ModelType = $row['ModelType'];
$ZoneFlag = $row['ZoneFlag'];
$RespawnFlag = $row['RespawnFlag'];
$TemplateID = $row['TemplateID'];
$Face = $row['Face'];
$LeftShoulder = $row['LeftShoulder'];
$RightShoulder = $row['RightShoulder'];
$LeftBracer = $row['LeftBracer'];
$RightBracer = $row['RightBracer'];
$LeftLeg = $row['LeftLeg'];
$RightLeg = $row['RightLeg'];
$Armor = $row['Armor'];
$VisualFlag = $row['VisualFlag'];
$QuestData = $row['QuestData'];

$sql="
UPDATE pcharacter SET
	SceneID = '$SceneID',
	x = '$x',
	y = '$y',
	z = '$z',
	Facing = '$Facing',
	BindSceneID = '$BindSceneID',
	BindX = '$BindX',
	BindY = '$BindY',
	BindZ = '$BindZ',
	ModelType = '$ModelType',
	ZoneFlag = '$ZoneFlag',
	RespawnFlag = '$RespawnFlag',
	TemplateID = '$TemplateID',
	Face = '$Face',
	LeftShoulder = '$LeftShoulder',
	RightShoulder = '$RightShoulder',
	LeftBracer = '$LeftBracer',
	RightBracer = '$RightBracer',
	LeftLeg = '$LeftLeg',
	RightLeg = '$RightLeg',
	Armor = '$Armor',
	VisualFlag = '$VisualFlag',
	QuestData = '$QuestData'
WHERE CharID='$CharID2'";

mysql_select_db($row_rsSvr[db], $dbWc2);
$rs = mysql_query("SELECT * FROM pcharacter WHERE CharID='$CharID2'", $dbWc2) or die(mysql_error($dbWc2));
if(mysql_num_rows($rs) != 1) die("<font color=red><b>No such character, CharID $CharID2, to be overwritten.</b></font>");


$befores = get_str_rs($dbWc2, "SELECT * FROM  pcharacter WHERE CharID='$CharID2' ");
mysql_query($sql, $dbWc2) or die(mysql_error($dbWc2));
$row_affected = mysql_affected_rows($dbWc2);
$after = get_str_rs($dbWc2, "SELECT * FROM  pcharacter WHERE CharID='$CharID2' ");
if($row_affected)
{
	act_log(
		array(
		"server"=>"$wc_ip",
		"db"=>"$wc_db",
		"tbl"=>"pcharacter",
		"act"=>"clone character",
		"cmd"=>$sql,
		"befores"=>$befores,
		"after"=>$after
		)
	);
}
mysql_select_db($wc1_db, $dbWc2);
$befores = get_str_rs($dbWc2, "SELECT * FROM  pcharacter WHERE CharID='$CharID2' ");

/* end pcharacter */

/* start pcharstat */

mysql_select_db($wc1_db, $dbWc);
$rs = mysql_query("SELECT * FROM pcharstats_$hash WHERE CharID='$CharID'", $dbWc) or die(mysql_error($dbWc));
$row = mysql_fetch_assoc($rs);




//mysql_free_result($rs);

$Strength  = $row['Strength'];
$Constitution  = $row['Constitution'];
$Agility  = $row['Agility'];
$Mind  = $row['Mind'];
$Perception  = $row['Perception'];
$AttackRating  = $row['AttackRating'];
$DefenseRating  = $row['DefenseRating'];
$BaseDamage  = $row['BaseDamage'];
$MaxHP  = $row['MaxHP'];
$CurrHP  = $row['CurrHP'];
$MaxChi  = $row['MaxChi'];
$CurrChi  = $row['CurrChi'];
$HPRegen  = $row['HPRegen'];
$ChiRegen  = $row['ChiRegen'];
$FireResist  = $row['FireResist'];
$ColdResist  = $row['ColdResist'];
$PoisonResist  = $row['PoisonResist'];
$LightningResist  = $row['LightningResist'];
$PhysicalResist  = $row['PhysicalResist'];
$MovementMode  = $row['MovementMode'];
$Experience  = $row['Experience'];
$Level  = $row['Level'];
$CharGold  = $row['CharGold'];
$StashGold  = $row['StashGold'];
$Prestige  = $row['Prestige'];
$AttributePoints  = $row['AttributePoints'];
$StancePoints  = $row['StancePoints'];
$PowerPoints  = $row['PowerPoints'];
$SkillPoints  = $row['SkillPoints'];
$EntityState  = $row['EntityState'];
$ActiveWeapon  = $row['ActiveWeapon'];
$ActiveWeaponSlot  = $row['ActiveWeaponSlot'];
$AttackMode  = $row['AttackMode'];
$ElementalAdv  = $row['ElementalAdv'];
$Gender  = $row['Gender'];
$MinUnarmedDamage  = $row['MinUnarmedDamage'];
$MaxUnarmedDamage  = $row['MaxUnarmedDamage'];
$ClanID  = $row['ClanID'];
$Job  = $row['Job'];
$ElderBrotherID  = $row['ElderBrotherID'];
$PartyID  = $row['PartyID'];
$TaskChainTag  = $row['TaskChainTag'];
$ChainStringID  = $row['ChainStringID'];
$XPPool  = $row['XPPool'];
$ClanQuit  = $row['ClanQuit'];
$LastKillerID  = $row['LastKillerID'];
$DuelScore  = $row['DuelScore'];
$LastDuelID  = $row['LastDuelID'];
$DuelsWon  = $row['DuelsWon'];
$DuelsLost  = $row['DuelsLost'];
$DuelsOffered  = $row['DuelsOffered'];
$DuelsRefused  = $row['DuelsRefused'];
$DuelsInterrupted  = $row['DuelsInterrupted'];
$NPCKilled  = $row['NPCKilled'];
$PCKilled  = $row['PCKilled'];
$PCResus  = $row['PCResus'];
$RelicStolen  = $row['RelicStolen'];
$RelicReturned  = $row['RelicReturned'];
$GuildID  = $row['GuildID'];
$RedPoints  = $row['RedPoints'];
$GreenPoints  = $row['GreenPoints'];
$HeroPoints  = $row['HeroPoints'];
$WarEventID  = $row['WarEventID'];
$NumChainPowers  = $row['NumChainPowers'];
$ReadyWeapon  = $row['ReadyWeapon'];
$PKWarning  = $row['PKWarning'];
$WaitPeriod  = $row['WaitPeriod'];
$ReSpecPoints  = $row['ReSpecPoints'];
$LastResTime  = $row['LastResTime'];
$TeamID  = $row['TeamID'];
$HeroCount  = $row['HeroCount'];
$LastHeroReset  = $row['LastHeroReset'];
$MulPerc = $row['MulPerc'];


echo "<br>";
echo "$CharID pcharstats_$hash  $Strength <br>";
echo "$CharID2 pcharstats_$hash2 <br>";

$sql="UPDATE pcharstats_$hash2 SET
 Strength  = '$Strength',
 Constitution  = '$Constitution',
 Agility  = '$Agility',
 Mind  = '$Mind',
 Perception  = '$Perception',
 AttackRating  = '$AttackRating',
 DefenseRating  = '$DefenseRating',
 BaseDamage  = '$BaseDamage',
 MaxHP  = '$MaxHP',
 CurrHP  = '$CurrHP',
 MaxChi  = '$MaxChi',
 CurrChi  = '$CurrChi',
 HPRegen  = '$HPRegen',
 ChiRegen  = '$ChiRegen',
 FireResist  = '$FireResist',
 ColdResist  = '$ColdResist',
 PoisonResist  = '$PoisonResist',
 LightningResist  = '$LightningResist',
 PhysicalResist  = '$PhysicalResist',
 MovementMode  = '$MovementMode',
 Experience  = '$Experience',
 Level  = '$Level',
 CharGold  = '$CharGold',
 StashGold  = '$StashGold',
 Prestige  = '$Prestige',
 AttributePoints  = '$AttributePoints',
 StancePoints  = '$StancePoints',
 PowerPoints  = '$PowerPoints',
 SkillPoints  = '$SkillPoints',
 EntityState  = '$EntityState',
 ActiveWeapon  = '$ActiveWeapon',
 ActiveWeaponSlot  = '$ActiveWeaponSlot',
 AttackMode  = '$AttackMode',
 ElementalAdv  = '$ElementalAdv',
 Gender  = '$Gender',
 MinUnarmedDamage  = '$MinUnarmedDamage',
 MaxUnarmedDamage  = '$MaxUnarmedDamage',
 ClanID  = '$ClanID',
 Job  = '$Job',
 ElderBrotherID  = '$ElderBrotherID',
 PartyID  = '$PartyID',
 TaskChainTag  = '$TaskChainTag',
 ChainStringID  = '$ChainStringID',
 XPPool  = '$XPPool',
 ClanQuit  = '$ClanQuit',
 LastKillerID  = '$LastKillerID',
 DuelScore  = '$DuelScore',
 LastDuelID  = '$LastDuelID',
 DuelsWon  = '$DuelsWon',
 DuelsLost  = '$DuelsLost',
 DuelsOffered  = '$DuelsOffered',
 DuelsRefused  = '$DuelsRefused',
 DuelsInterrupted  = '$DuelsInterrupted',
 NPCKilled  = '$NPCKilled',
 PCKilled  = '$PCKilled',
 PCResus  = '$PCResus',
 RelicStolen  = '$RelicStolen',
 RelicReturned  = '$RelicReturned',
 GuildID  = '$GuildID',
 RedPoints  = '$RedPoints',
 GreenPoints  = '$GreenPoints',
 HeroPoints  = '$HeroPoints',
 WarEventID  = '$WarEventID',
 NumChainPowers  = '$NumChainPowers',
 ReadyWeapon  = '$ReadyWeapon',
 PKWarning  = '$PKWarning',
 WaitPeriod  = '$WaitPeriod',
 ReSpecPoints  = '$ReSpecPoints',
 LastResTime  = '$LastResTime',
 TeamID  = '$TeamID',
 HeroCount  = '$HeroCount',
 LastHeroReset  = '$LastHeroReset',
 MulPerc = '$MulPerc'
WHERE CharID='$CharID2'";
//	XPPool = '$XPPool'


mysql_select_db($row_rsSvr[db], $dbWc2);
$rs = mysql_query("SELECT * FROM  pcharstats_$hash2 WHERE CharID='$CharID2'", $dbWc2) or die(mysql_error($dbWc2));
if(mysql_num_rows($rs) != 1) die("<font color=red><b>No such character, CharID $CharID2, to be overwritten.</b></font>");

$befores = get_str_rs($dbWc2, "SELECT * FROM  pcharstats_$hash2 WHERE CharID='$CharID2' ");
mysql_query($sql, $dbWc2) or die(mysql_error($dbWc2));
$row_affected = mysql_affected_rows($dbWc2);
$after = get_str_rs($dbWc2, "SELECT * FROM  pcharstats_$hash2 WHERE CharID='$CharID2' ");
if($row_affected)
{
	act_log(
		array(
		"server"=>"$wc_ip",
		"db"=>"$wc_db",
		"tbl"=>"pcharacter",
		"act"=>"clone character",
		"cmd"=>$sql,
		"befores"=>$befores,
		"after"=>$after
		)
	);
}
mysql_select_db($wc1_db, $dbWc2);
$befores = get_str_rs($dbWc2, "SELECT * FROM  pcharstats_$hash2 WHERE CharID='$CharID2' ");

/* end pcharstat */



/* start char inv */

$rs_charinv = mysql_query("SELECT * FROM charinv_$hash WHERE CharID='$CharID' ORDER BY SlotNum", $dbWc) or die(mysql_error($dbWc));
if (mysql_num_rows($rs_charinv) == 0) {
    echo "No rows found, nothing to print so am exiting";
    exit;
}

while($row = mysql_fetch_assoc($rs_charinv))
{
	$ItemID = $row['ItemID'];
	$Quantity = $row['Quantity'];
	$Identified = $row['Identified'];
	$Durability = $row['Durability'];
	$Field1 = $row['Field1'];
	$Field2 = $row['Field2'];
	$Field3 = $row['Field3'];
	$Field4 = $row['Field4'];
	$Field5 = $row['Field5'];
	$Hardness = $row['Hardness'];
	$SlotNum = $row['SlotNum'];
	

	$sql = "UPDATE charinv_$hash2 SET ItemID='$ItemID', Quantity='$Quantity', Identified='$Identified', Durability='$Durability', Hardness='$Hardness', Field1='$Field1', Field2='$Field2', Field3='$Field3', Field4='$Field4', Field5='$Field5' WHERE CharID='$CharID2' AND SlotNum='$SlotNum' ";
	mysql_select_db($row_rsSvr[db], $dbWc2);
	mysql_query($sql, $dbWc2) or die(mysql_error($dbWc2));
	
	
	
}

/* end char inv */

/* start char stash */

$rs_charstash = mysql_query("SELECT * FROM stash_$hash WHERE CharID='$CharID' ORDER BY SlotNum", $dbWc) or die(mysql_error($dbWc));
if (mysql_num_rows($rs_charstash) == 0) {
    echo "No rows found, nothing to print so am exiting";
    exit;
}

while($row = mysql_fetch_assoc($rs_charstash))
{
	$ItemID = $row['ItemID'];
	$Quantity = $row['Quantity'];
	$Identified = $row['Identified'];
	$Durability = $row['Durability'];
	$Field1 = $row['Field1'];
	$Field2 = $row['Field2'];
	$Field3 = $row['Field3'];
	$Field4 = $row['Field4'];
	$Field5 = $row['Field5'];
	$Hardness = $row['Hardness'];
	$SlotNum = $row['SlotNum'];
	

	$sql = "UPDATE stash_$hash2 SET ItemID='$ItemID', Quantity='$Quantity', Identified='$Identified', Durability='$Durability', Hardness='$Hardness', Field1='$Field1', Field2='$Field2', Field3='$Field3', Field4='$Field4', Field5='$Field5' WHERE CharID='$CharID2' AND SlotNum='$SlotNum' ";
	mysql_select_db($row_rsSvr[db], $dbWc2);
	mysql_query($sql, $dbWc2) or die(mysql_error($dbWc2));
	
	
	
}

/* end char stash */



/* start power */

mysql_select_db($wc1_db, $dbWc);
$rs_powerlist = mysql_query("SELECT * FROM powerlist_$hash WHERE CharID='$CharID'", $dbWc) or die(mysql_error());

//mysql_query("UPDATE powerlist_$hash2 SET PowerID=0, Rank=0 WHERE CharID='$CharID2'WHERE PowerID<>0", $dbWc2);
mysql_select_db($row_rsSvr[db], $dbWc2);
$rs_powerlist2 = mysql_query("SELECT Indx FROM powerlist_$hash2 WHERE CharID='$CharID2' ORDER BY Indx", $dbWc2) or die(mysql_error());

if (mysql_num_rows($rs_powerlist2) == 0) {
    echo "No rows found, nothing to print so am exiting";
    exit;
}

while($row = mysql_fetch_assoc($rs_powerlist))
{
	$PowerID = $row['PowerID'];
	$Rank = $row['Rank'];
	list($Indx) = mysql_fetch_array($rs_powerlist2);

	$sql = "UPDATE powerlist_$hash2 SET PowerID='$PowerID', Rank='$Rank' WHERE Indx='$Indx'";
	mysql_select_db($row_rsSvr[db], $dbWc2);
	mysql_query($sql, $dbWc2) or die(mysql_error());

}

/* end power */

/* start skill */
mysql_select_db($wc1_db, $dbWc);
$rs_skilllist = mysql_query("SELECT * FROM skilllist_$hash WHERE CharID='$CharID'", $dbWc) or die(mysql_error());
//mysql_query("UPDATE skilllist_$hash2 SET SkillID=0, Rank=0 WHERE CharID='$CharID2'", $dbWc2) or die(mysql_error());
mysql_select_db($row_rsSvr[db], $dbWc2);
$rs_skilllist2 = mysql_query("SELECT Indx FROM skilllist_$hash2 WHERE CharID='$CharID2' ORDER BY Indx", $dbWc2) or die(mysql_error());
while ($row = mysql_fetch_assoc($rs_skilllist))
{
	$SkillID = $row['SkillID'];
	$Rank = $row['Rank'];
	list($Indx) = mysql_fetch_array($rs_skilllist2);

	$sql = "UPDATE skilllist_$hash2 SET SkillID='$SkillID', Rank='$Rank' WHERE Indx='$Indx'";
	mysql_select_db($row_rsSvr[db], $dbWc2);
	mysql_query($sql, $dbWc2) or die(mysql_error());


}

/* end skill */


/* start effect list */
mysql_select_db($wc1_db, $dbWc);
$rs_effectlist = mysql_query("SELECT * FROM effectlist_$hash WHERE CharID='$CharID'", $dbWc) or die(mysql_error());
//mysql_query("UPDATE effectlist_$hash2 SET EffectID=0, Duration=0, TimeStamp=0, PowerRank=0, Immunity=0 WHERE CharID='$CharID2'", $dbWc2) or die(mySql_error());
mysql_select_db($row_rsSvr[db], $dbWc2);
$rs_effectlist2 = mysql_query("SELECT Indx FROM effectlist_$hash2 WHERE CharID='$CharID2' ORDER BY Indx", $dbWc2) or die(mysql_error());
while ($row = mysql_fetch_assoc($rs_effectlist))
{
	$EffectID = $row['EffectID'];
	$Duration = $row['Duration'];
	$TimeStamp = $row['TimeStamp'];
	$PowerRank = $row['PowerRank'];
	$Immunity = $row['Immunity'];
	list($Indx) = mysql_fetch_array($rs_effectlist2);

	mysql_select_db($row_rsSvr[db], $dbWc2);
	mysql_query("UPDATE effectlist_$hash2 SET EffectID='$EffectID', Duration='$Duration', TimeStamp='$TimeStamp', PowerRank='$PowerRank', Immunity='$Immunity' WHERE Indx = '$Indx'", $dbWc2) or die(mysql_error());

}

/* end effect list */

/* start stance list */
mysql_select_db($wc1_db, $dbWc);
$rs_stancelist = mysql_query("SELECT * FROM stancelist_$hash WHERE CharID='$CharID'", $dbWc) or die(mysql_error());
//mysql_query("UPDATE stancelist_$hash2 SET StanceID=0, Rank=0 WHERE CHARID='$CharID2'", $dbWc2) or die(mysql_error());
mysql_select_db($row_rsSvr[db], $dbWc2);
$rs_stancelist2 = mysql_query("SELECT Indx FROM stancelist_$hash2 WHERE CharID='$CharID2' ORDER BY Indx", $dbWc2) or die(mysql_error());
while ($row = mysql_fetch_assoc($rs_stancelist))
{
	$StanceID = $row['StanceID'];
	$Rank = $row['Rank'];
	list($Indx) = mysql_fetch_array($rs_stancelist2);

	
	$sql = "UPDATE stancelist_$hash2 SET StanceID='$StanceID', Rank='$Rank' WHERE Indx='$Indx'";
	mysql_select_db($row_rsSvr[db], $dbWc2);
	mysql_query($sql, $dbWc2) or die(mysql_error($dbWc2));

}

/* end stance list */

mysql_close($dbWc);
mysql_close($dbWc2);
echo "<script>alert('Character is duplicated');location.href='clonechar.php'</script>";
//echo "Character is duplicated"; 
exit();
}


$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wid><option value=''></option>";
$htmlWc2="<select name=wid2><option value=''></option>";
//while($row=mysql_fetch_assoc($rsWc))
$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";

	$selected=($wid2==$row[id])?"SELECTED":"";
	$htmlWc2.="<option value='{$row[id]}' $selected>{$row[name]}</option>";

}
$htmlWc.="</select>";
$htmlWc2.="</select>";
mysql_free_result($rsWc);


?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<link href="ga.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<form name=form1>
<h3>Clone Player Character</h3>
<br>
<table><tr><td>&nbsp;</td><td>World</td><td>CharID</td></tr>
<tr><td>from</td><td><?=$htmlWc?></td><td><input name=cid1 maxlength=10></td></tr>
<tr><td>to</td><td><?=$htmlWc2?></td><td><input name=cid2 maxlength=10></td></tr>
</table>
<input type=button value='Submit' onClick="if(confirm('Overwrite character?'))document.form1.submit()">
</form>
</body>
</html>
