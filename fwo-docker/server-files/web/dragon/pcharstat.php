<?php
$scriptname = $HTTP_SERVER_VARS[SCRIPT_NAME];
require("auth.php");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

//if($HTTP_SESSION_VARS["wc"]=="")
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

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid world controller");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);

	$hash = $HTTP_GET_VARS[i] % 10;
	$query_rs1 = "SELECT * FROM pcharacter a, pcharstats_$hash b WHERE a.CharID=b.CharID AND a.CharID='{$HTTP_GET_VARS[i]}'";
	$rs1 = mysql_query($query_rs1, $dbWc) or die(mysql_error($dbWc));
	$row1 = mysql_fetch_assoc($rs1);
	mysql_free_result($rs1);

	if($HTTP_GET_VARS[a]=="s")
	{
		$rs_logon = mysql_query("SELECT * FROM authenticated WHERE CharID='{$HTTP_GET_VARS[i]}'", $dbWc) or die(mysql_error($dbWc));
		$is_logon=mysql_num_rows($rs_logon);
		mysql_free_result($rs_logon);

		if($is_logon && $HTTP_GET_VARS[force]!=1)
		{
			echo "<form name=form1 action=\"{$HTTP_SERVER_VARS[REQUEST_URI]}&force=1\" method='Post'>";
			echo generate_form('',$HTTP_POST_VARS);
			echo "<input type=button value='Force Save' onclick='if(confirm(\"Do not force save if the character is being used or this will cause data error.\"))document.form1.submit()'></form>";
			//post_form('document.form1',$HTTP_SERVER_VARS[REQUEST_URI]."&force=1");
			die("game character is being used, write access denied.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
		}

		$rs_maxlevel = mysql_query("SELECT MAX(level)-1 FROM leveladv", $dbWc) or die(mysql_error());
		list($maxlevel) = mysql_fetch_row($rs_maxlevel);
		mysql_free_result($rs_maxlevel);

		if($HTTP_POST_VARS[level] > $maxlevel) die("Invalid level is provided as the highest level allowed is $maxlevel.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");


//* no clan update
		$was_in_userclan = $row1[ClanID]>0 ;//(float)($row1[ClanID] >= 1 && (float)$row1[ClanID] <= 10);
		$be_in_userclan = $HTTP_POST_VARS[clanid]>0 ;//((float)$HTTP_POST_VARS[clanid] >= 1 && (float)$HTTP_POST_VARS[clanid] <= 10);

		//if($be_in_userclan)
		if(!($HTTP_POST_VARS[clanid] == 0 && $HTTP_POST_VARS[guildid] == 0 && $HTTP_POST_VARS[job] == 0))
		{
			//check valid rank/job
			($HTTP_POST_VARS[job] >= 1 && $HTTP_POST_VARS[job] <=5) or die("Invalid job, {$HTTP_POST_VARS[job]}.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");

			if($HTTP_POST_VARS[clanid] == 100) //game master
			{
				if($HTTP_POST_VARS[guildid] != 0) die("Guild ID must be 0.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
				$tbl_clanlist = "clanlist_" . ($HTTP_POST_VARS[clanid] % 10);
				$tbl_clanlist = "gmlist";

				//check 1 clan 1 PC/NPC leader
				if($HTTP_POST_VARS[job] == '1')
				{
					$rs = mysql_query("SELECT 1 FROM clan WHERE ClanID='$HTTP_POST_VARS[clanid]' AND Type<>0", $dbWc) or die(mysql_error());
					if(mysql_num_rows($rs)>0) die("Clan already has an NPC leader.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");

					//$rs = mysql_query("SELECT Indx FROM {$tbl_clanlist} WHERE ClanID='$HTTP_POST_VARS[clanid]' AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job=1", $dbWc) or die(mysql_error());
					$rs = mysql_query("SELECT Indx FROM {$tbl_clanlist} WHERE CharID<>'{$HTTP_GET_VARS[i]}' AND Job=1", $dbWc) or die(mysql_error());
					if(mysql_num_rows($rs)>0) die("Clan already has a leader.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
				}

				//check member limit
				//$sql_get_job_slot = "SELECT Indx FROM {$tbl_clanlist} WHERE ClanID='$HTTP_POST_VARS[clanid]' AND (CharID=0 OR CharID='{$HTTP_GET_VARS[i]}') ORDER BY CharID DESC, Indx LIMIT 1;";
				$sql_get_job_slot = "SELECT Indx FROM {$tbl_clanlist} WHERE (CharID=0 OR CharID='{$HTTP_GET_VARS[i]}') ORDER BY CharID DESC, Indx LIMIT 1;";
				$rs_job_slot = mysql_query($sql_get_job_slot, $dbWc) or die(mysql_error($dbWc));
				$num_rows = mysql_num_rows($rs_job_slot);
				if($num_rows==0) die("Guild/Hall has reached the maximum number of members allowed.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
				$row2 = mysql_fetch_row($rs_job_slot);

				$sql_set_job="UPDATE {$tbl_clanlist} SET CharID='{$HTTP_GET_VARS[i]}', Job='{$HTTP_POST_VARS[job]}' WHERE Indx='{$row2[0]}';";
				$befores = get_str_rs($dbWc, "SELECT * FROM {$tbl_clanlist} WHERE Indx='{$row2[0]}';");
				$rs_set_job = mysql_query($sql_set_job, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, "SELECT * FROM {$tbl_clanlist} WHERE Indx='{$row2[0]}';");
				
			}
			else	//normal clan
			{

				if($row_rsSvr[version]  < '1.2' || $HTTP_POST_VARS[clanid] != 0)
				{
					//check matched clanid and guildid in ally table
					$rs = mysql_query("SELECT 1 FROM ally WHERE ClanID='{$HTTP_POST_VARS[clanid]}' AND GuildID='{$HTTP_POST_VARS[guildid]}' ", $dbWc) or die(mysql_error($dbWc));
					if(mysql_num_rows($rs)==0)die("In ally table, Clan, ID {$HTTP_POST_VARS[clanid]} does not have Guild/Hall, ID {$HTTP_POST_VARS[guildid]}.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
				}

				if($HTTP_POST_VARS[guildid] < 1500) // if is hall (not user guild)
				{
					//1 clan 1 leader, 10 minister, 20 master check
					if($HTTP_POST_VARS[job] == 1 || $HTTP_POST_VARS[job] == 2 || $HTTP_POST_VARS[job] == 3)
					{
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

						$rank_cnt = 0;
						$max_rank_cnt[1] = 1;
						$max_rank_cnt[2] = 10;
						$max_rank_cnt[3] = 20;
						if($row_rsSvr[version] >= '1.2')
						{
							$max_rank_cnt[3] = 22;
						}
						$rs_ally = mysql_query("SELECT * FROM ally WHERE ClanID='{$HTTP_POST_VARS[clanid]}' AND GuildID<1500", $dbWc) or die(mysql_error($dbWc));
						while($row = mysql_fetch_assoc($rs_ally))
						{
							if($row[GuildID] == 0) continue;
							$tbl_guildlist = "guildlist_" . $row[GuildID] % 10;
							//$rs_guildlist = mysql_query("SELECT 1 FROM $tbl_guildlist WHERE GuildID='{$row[GuildID]}' AND Job = '{$HTTP_POST_VARS[job]}' AND CharID<>'{$HTTP_GET_VARS[i]}' ", $dbWc) or die(mysql_error($dbWc));
							$rs_guildlist = mysql_query("SELECT 1 FROM intdata_all WHERE GuildID='{$row[GuildID]}' AND Job = '{$HTTP_POST_VARS[job]}' AND CharID<>'{$HTTP_GET_VARS[i]}' ", $dbWc) or die(mysql_error($dbWc));
							$rank_cnt += mysql_num_rows($rs_guildlist);
							/*
							if(mysql_num_rows($rs_guildlist) >0)
							{
							$arrrr = mysql_fetch_row($rs_guildlist);
							echo   $arrrr[0] . "SELECT 1 FROM intdata_all WHERE GuildID='{$row[GuildID]}' AND Job = '{$HTTP_POST_VARS[job]}' AND CharID<>'{$HTTP_GET_VARS[i]}' ";
							}
							*/
						}
						if($rank_cnt >= $max_rank_cnt[$HTTP_POST_VARS[job]]) die("Each clan can have only {$max_rank_cnt[$HTTP_POST_VARS[job]]} {$job_desc[$HTTP_POST_VARS[job]]}(s).<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");

						mysql_query("DROP TABLE IF EXISTS intdata_all", $dbWc);
					}// 1 clan 1 leader, 10minister, 20master check
				} // is hall (not guild)

				$tbl_guildlist = "guildlist_" . ($HTTP_POST_VARS[guildid] % 10);

				//check NPC leader
				if($HTTP_POST_VARS[job] == '1')
				{
					$rs = mysql_query("SELECT 1 FROM clan WHERE ClanID='$HTTP_POST_VARS[clanid]' AND Type<>0", $dbWc) or die(mysql_error($dbWc));
					if(mysql_num_rows($rs)>0)die("Clan already has an NPC leader.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
				}

				if($row_rsSvr[version] >= '1.2')
				{
					if($HTTP_POST_VARS[guildid] < 1500) //hall
					{
						//check 1 hall 1 leader/minister and 1 hall 2 masters and 1 hall 15 seniors and 1 hall 500 members
						if($HTTP_POST_VARS[job] == '1' || $HTTP_POST_VARS[job] == '2')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job IN (1,2)", $dbWc) or die(mysql_error($dbWc));
							//if(mysql_num_rows($rs)>=1)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has a leader or minister.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
						elseif($HTTP_POST_VARS[job] == '3')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job = 3", $dbWc) or die(mysql_error($dbWc));
							if(mysql_num_rows($rs)>=2)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has 2 masters.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
						elseif($HTTP_POST_VARS[job] == '4')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job = 4", $dbWc) or die(mysql_error($dbWc));
							if(mysql_num_rows($rs)>=15)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has 15 seniors.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
						elseif($HTTP_POST_VARS[job] == '5')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job = 5", $dbWc) or die(mysql_error($dbWc));
							if(mysql_num_rows($rs)>=500)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has 500 members.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
					}
					else // is guilid (id {>=1500}, not hall {<1500})
					{
						//check 1 guild 1 leader, 10 minister, 20 masters, 60 seniors and 409 members
						if($HTTP_POST_VARS[job] == '1')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job = 1", $dbWc) or die(mysql_error($dbWc));
							if(mysql_num_rows($rs)>=1)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has a leader.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
						elseif($HTTP_POST_VARS[job] == '2')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job = 2", $dbWc) or die(mysql_error($dbWc));
							if(mysql_num_rows($rs)>=10)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has 10 masters.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
						elseif($HTTP_POST_VARS[job] == '3')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job = 3", $dbWc) or die(mysql_error($dbWc));
							if(mysql_num_rows($rs)>=20)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has 20 masters.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
						elseif($HTTP_POST_VARS[job] == '4')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job = 4", $dbWc) or die(mysql_error($dbWc));
							if(mysql_num_rows($rs)>=60)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has 60 seniors.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
						elseif($HTTP_POST_VARS[job] == '5')
						{
							$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job = 5", $dbWc) or die(mysql_error($dbWc));
							if(mysql_num_rows($rs)>=409)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has 409 members.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
						}
					}
				}
				else
				{
					//check 1 Guild 1 leader/minister/master
					if($HTTP_POST_VARS[job] == '1' || $HTTP_POST_VARS[job] == '2' || $HTTP_POST_VARS[job] == '3')
					{
						$rs = mysql_query("SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND CharID>0 AND CharID<>'{$HTTP_GET_VARS[i]}' AND Job IN (1,2,3)", $dbWc) or die(mysql_error($dbWc));
						if(mysql_num_rows($rs)>0)die("Guild/Hall, ID $HTTP_POST_VARS[guildid], already has a leader, minister, or master.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
					}
				}

				//get user/empty slot in guildlist_N
				$sql_get_job_slot = "SELECT Indx FROM {$tbl_guildlist} WHERE GuildID='$HTTP_POST_VARS[guildid]' AND (CharID=0 OR CharID='{$HTTP_GET_VARS[i]}') ORDER BY CharID DESC, Indx LIMIT 1;";
				$rs_job_slot = mysql_query($sql_get_job_slot, $dbWc) or die(mysql_error($dbWc));
				mysql_num_rows($rs_job_slot) > 0 or die("Guild/Hall has reached the maximum number (" . mysql_num_rows($rs_job_slot) . ") of members allowed.<p><a href='{$scriptname}?i={$HTTP_GET_VARS[i]}&wid={$wid}'>Back</a>");
				$row2 = mysql_fetch_row($rs_job_slot);

				//set guildlist
				$sql_set_job="UPDATE {$tbl_guildlist} SET CharID='{$HTTP_GET_VARS[i]}', Job='{$HTTP_POST_VARS[job]}' WHERE Indx='{$row2[0]}';";
				$befores = get_str_rs($dbWc, "SELECT * FROM {$tbl_guildlist} WHERE Indx='{$row2[0]}';");
				$rs_set_job = mysql_query($sql_set_job, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, "SELECT * FROM {$tbl_guildlist} WHERE Indx='{$row2[0]}';");
				
			}
		} //be in clan

		//if($was_in_userclan)
		//{
			if($row1[ClanID] == 100)
			{
				$tbl_clanlist = "clanlist_" . ($row1[ClanID] % 10);
				$tbl_clanlist = "gmlist";
				//$sql_unset_job="UPDATE $tbl_clanlist SET CharID=0, Job=0 WHERE CharID='{$HTTP_GET_VARS[i]}' AND ClanID<>'{$HTTP_POST_VARS[clanid]}';";
				$sql_unset_job="UPDATE $tbl_clanlist SET CharID=0, Job=0 WHERE CharID='{$HTTP_GET_VARS[i]}';";
				//execdbsql($dbWc, "SELECT * FROM $tbl_clanlist WHERE CharID='{$HTTP_GET_VARS[i]}' AND ClanID<>'{$HTTP_POST_VARS[clanid]}';", $row_clanlist);
				execdbsql($dbWc, "SELECT * FROM $tbl_clanlist WHERE CharID='{$HTTP_GET_VARS[i]}';", $row_clanlist);
				$befores = dump_rs_string($row_clanlist);
				//$befores = get_str_rs($dbWc, "SELECT * FROM $tbl_clanlist WHERE CharID='{$HTTP_GET_VARS[i]}' AND ClanID<>'{$HTTP_POST_VARS[clanid]}';");
				$rs_unset_job = mysql_query($sql_unset_job, $dbWc) or die(mysql_error($dbWc));
				$after   = get_str_rs($dbWc, "SELECT * FROM $tbl_clanlist WHERE Indx='{$row_clanlist[Indx]}';");
				
			}
			else //normal clan
			{
				if($row1[GuildID] != 0)
				{
					$tbl_guildlist = "guildlist_" . ($row1[GuildID] % 10);
					$sql_unset_job="UPDATE $tbl_guildlist SET CharID=0, Job=0 WHERE CharID='{$HTTP_GET_VARS[i]}' AND GuildID<>'{$HTTP_POST_VARS[guildid]}';";
					execdbsql($dbWc, "SELECT * FROM $tbl_guildlist WHERE CharID='{$HTTP_GET_VARS[i]}' AND GuildID='{$HTTP_POST_VARS[guildid]}';", $row_guildlist);
				//die($sql_unset_job);
					$befores = dump_rs_string($row_guildlist);
					//$befores = get_str_rs($dbWc, "SELECT * FROM $tbl_clanlist WHERE CharID='{$HTTP_GET_VARS[i]}' AND ClanID<>'{$HTTP_POST_VARS[clanid]}';");
					$rs_unset_job = mysql_query($sql_unset_job, $dbWc) or die(mysql_error($dbWc));
					$after   = get_str_rs($dbWc, "SELECT * FROM $tbl_guildlist WHERE Indx='{$row_guildlist[0][Indx]}';");
					
				} // guild id > 0
			}
		//} //was in clan
//*/
        if ($row1[MulPerc])
        {
            $temp_str = "MulPerc='{$HTTP_POST_VARS[MultiPercentage]}',";
        }

		$query_rs = "UPDATE pcharstats_$hash SET
			Strength='{$HTTP_POST_VARS[strength]}',
			Constitution='{$HTTP_POST_VARS[constitution]}',
			Agility='{$HTTP_POST_VARS[agility]}',
			Mind='{$HTTP_POST_VARS[mind]}',
			Perception='{$HTTP_POST_VARS[perception]}',
			AttackRating='{$HTTP_POST_VARS[attackrating]}',
			DefenseRating='{$HTTP_POST_VARS[defenserating]}',
			BaseDamage='{$HTTP_POST_VARS[basedamage]}',
			CurrHP='{$HTTP_POST_VARS[currhp]}',
			MaxHP='{$HTTP_POST_VARS[maxhp]}',
			CurrChi='{$HTTP_POST_VARS[currchi]}',
			MaxChi='{$HTTP_POST_VARS[maxchi]}',
			HPRegen='{$HTTP_POST_VARS[hpregen]}',
			ChiRegen='{$HTTP_POST_VARS[chiregen]}',
			FireResist='{$HTTP_POST_VARS[fireresist]}',
			ColdResist='{$HTTP_POST_VARS[coldresist]}',
			PoisonResist='{$HTTP_POST_VARS[poisonresist]}',
			LightningResist='{$HTTP_POST_VARS[lightningresist]}',
			PhysicalResist='{$HTTP_POST_VARS[physicalresist]}',
			MovementMode='{$HTTP_POST_VARS[movementmode]}',
			Experience='{$HTTP_POST_VARS[experience]}',
			Level='{$HTTP_POST_VARS[level]}',
			CharGold='{$HTTP_POST_VARS[chargold]}',
			StashGold='{$HTTP_POST_VARS[stashgold]}',
			Prestige='{$HTTP_POST_VARS[prestige]}',
			AttributePoints='{$HTTP_POST_VARS[attributepoints]}',
			StancePoints='{$HTTP_POST_VARS[stancepoints]}',
			PowerPoints='{$HTTP_POST_VARS[powerpoints]}',
			SkillPoints='{$HTTP_POST_VARS[skillpoints]}',
			EntityState='{$HTTP_POST_VARS[entitystate]}',
			ActiveWeapon='{$HTTP_POST_VARS[activeweapon]}',
			ActiveWeaponSlot='{$HTTP_POST_VARS[activeweaponslot]}',
			AttackMode='{$HTTP_POST_VARS[attackmode]}',
			ElementalAdv='{$HTTP_POST_VARS[elementaladv]}',
			Gender='{$HTTP_POST_VARS[gender]}',
			MinUnarmedDamage='{$HTTP_POST_VARS[minunarmeddamage]}',
			MaxUnarmedDamage='{$HTTP_POST_VARS[maxunarmeddamage]}',
			PartyID='{$HTTP_POST_VARS[partyid]}',
			TaskChainTag='{$HTTP_POST_VARS[taskchaintag]}',
			ChainStringID='{$HTTP_POST_VARS[chainstringid]}',
			XPPool='{$HTTP_POST_VARS[xppool]}',
			ClanQuit='{$HTTP_POST_VARS[clanquit]}',
			ClanID='{$HTTP_POST_VARS[clanid]}',
			GuildID='{$HTTP_POST_VARS[guildid]}',
			RedPoints='{$HTTP_POST_VARS[RedPoints]}',
			GreenPoints='{$HTTP_POST_VARS[GreenPoints]}',
			HeroPoints='{$HTTP_POST_VARS[HeroPoints]}',
			PKWarning='{$HTTP_POST_VARS[PKWarning]}',
			NumChainPowers='{$HTTP_POST_VARS[NumChainPowers]}',
			LastDuelID='{$HTTP_POST_VARS[LastDuelID]}',
			ReSpecPoints='{$HTTP_POST_VARS[ReSpecPoints]}',
			$temp_str
			job='{$HTTP_POST_VARS[job]}'
			WHERE CharID='{$HTTP_GET_VARS[i]}'";
//			ElderBrotherID='{$HTTP_POST_VARS[elderbrotherid]}',
//			ElderBName='{$HTTP_POST_VARS[elderbrothername]}',
/*
*/
		$befores = get_str_rs($dbWc, "SELECT p1.Username, p1.CharacterName, p2.* FROM pcharacter p1, pcharstats_$hash p2 WHERE p1.CharID='{$HTTP_GET_VARS[i]}' AND p2.CharID='{$HTTP_GET_VARS[i]}';");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		$after   = get_str_rs($dbWc, "SELECT p1.Username, p1.CharacterName, p2.* FROM pcharacter p1, pcharstats_$hash p2 WHERE p1.CharID='{$HTTP_GET_VARS[i]}' AND p2.CharID='{$HTTP_GET_VARS[i]}';");
		
///* clan update
		$query_rs = "UPDATE intdata_$hash SET ClanID='{$HTTP_POST_VARS[clanid]}', GuildID='{$HTTP_POST_VARS[guildid]}', job='{$HTTP_POST_VARS[job]}' WHERE CharID='{$HTTP_GET_VARS[i]}';";
		$befores = get_str_rs($dbWc, "SELECT * FROM intdata_$hash WHERE CharID='{$HTTP_GET_VARS[i]}';");
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
		$after   = get_str_rs($dbWc, "SELECT * FROM intdata_$hash WHERE CharID='{$HTTP_GET_VARS[i]}';");
		
//*/
		header("Location: {$scriptname}?i={$HTTP_GET_VARS[i]}&wid=$wid");
		exit();
	}

	$query_rs = "SELECT * FROM pcharstats_$hash WHERE CharID='{$HTTP_GET_VARS[i]}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
	$row=mysql_fetch_assoc($rs);
	if(mysql_num_rows($rs) == 0) die("<font color=red>No matched queries.</font>");
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
function valfrm()
{
/* clan update
	if(document.form1.job.value=='0' && document.form1.clanid.value!='0')
	{
		alert('clan_id must be 0 if job is 0')
		return 0
	}
*/
	return 1
}
//-->
</script>
</head>
<body>
<h3>Player Character Stats</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<form name="form1" method="post" action="">
<p>Properties: <a href="pcharacter.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Character</a>
	| Stat
	| <a href="charinv.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Inventory</a>
	| <a href="powerlist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Power</a>
	| <a href="skilllist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Skill</a>
	| <a href="effectlist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Effect</a>
	| <a href="stancelist.php?f=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Stance</a>
	| <a href="questdata.php?i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>">Quest</a>
</p>
<table border="0">
	<tr>
		<td>User Name</td>
		<td><input name="textfield32" type="text" value="<?=$row1[Username]?>" readonly="yes"></td>
	</tr>
	<tr>
		<td>Char Name </td>
		<td><input name="textfield52" type="text" value="<?=htmlspecialchars(U16btoU8str($row1[CharacterName]))?>" readonly="yes"></td>
	</tr>
	<tr>
		<td>Char ID</td>
		<td><input name="textfield42" type="text" value="<?=$row1[CharID]?>" readonly="yes"></td>
	</tr>
</table>
<table border="1" cellspacing=0>
    <tr>
      <td>Strength</td>
      <td><input name="strength" type="text" size="11" id="strength" value="<?=$row[Strength]?>"></td>
    </tr>
    <tr>
      <td>Constitution</td>
      <td><input name="constitution" type="text" size="11" id="constitution" value="<?=$row[Constitution]?>"></td>
    </tr>
    <tr>
      <td>Agility</td>
      <td><input name="agility" type="text" size="11" id="agility" value="<?=$row[Agility]?>"></td>
    </tr>
    <tr>
      <td>Mind</td>
      <td><input name="mind" type="text" size="11" id="mind" value="<?=$row[Mind]?>"></td>
    </tr>
    <tr>
      <td>Perception</td>
      <td><input name="perception" type="text" size="11" id="perception" value="<?=$row[Perception]?>"></td>
    </tr>
    <tr>
      <td>AttackRating</td>
      <td><input name="attackrating" type="text" size="11" id="attackrating" value="<?=$row[AttackRating]?>"></td>
    </tr>
    <tr>
      <td>DefenseRating</td>
      <td><input name="defenserating" type="text" size="11" id="defenserating" value="<?=$row[DefenseRating]?>"></td>
    </tr>
    <tr>
      <td>BaseDamage</td>
      <td><input name="basedamage" type="text" size="11" id="basedamage" value="<?=$row[BaseDamage]?>"></td>
    </tr>
    <tr>
      <td>CurHP/MaxHP</td>
      <td><input name="currhp" type="text" size="5" id="currhp" value="<?=$row[CurrHP]?>">
        / <input name="maxhp" type="text" size="5" id="maxhp" value="<?=$row[MaxHP]?>"></td>
    </tr>
    <tr>
      <td>CurChi/MaxChi</td>
      <td><input name="currchi" type="text" size="5" id="currchi" value="<?=$row[CurrChi]?>">
        / <input name="maxchi" type="text" size="5" id="maxchi" value="<?=$row[MaxChi]?>"></td>
    </tr>
    <tr>
      <td>HPRegen</td>
      <td><input name="hpregen" type="text" size="11" id="hpregen" value="<?=$row[HPRegen]?>"></td>
    </tr>
    <tr>
      <td>ChiRegen</td>
      <td><input name="chiregen" type="text" size="11" id="chiregen" value="<?=$row[ChiRegen]?>"></td>
    </tr>
    <tr>
      <td>FireResist</td>
      <td><input name="fireresist" type="text" size="11" id="fireresist" value="<?=$row[FireResist]?>"></td>
    </tr>
    <tr>
      <td>ColdResist</td>
      <td><input name="coldresist" type="text" size="11" id="coldresist" value="<?=$row[ColdResist]?>"></td>
    </tr>
    <tr>
      <td>PoisonResist</td>
      <td><input name="poisonresist" type="text" size="11" id="poisonresist" value="<?=$row[PoisonResist]?>"></td>
    </tr>
    <tr>
      <td>LightningResist</td>
      <td><input name="lightningresist" type="text" size="11" id="lightningresist" value="<?=$row[LightningResist]?>"></td>
    </tr>
    <tr>
      <td>PhysicalResist</td>
      <td><input name="physicalresist" type="text" size="11" id="physicalresist" value="<?=$row[PhysicalResist]?>"></td>
    </tr>
    <tr>
      <td>MovementMode</td>
      <td><input name="movementmode" type="text" size="11" id="movementmode" value="<?=$row[MovementMode]?>"></td>
    </tr>
    <tr>
      <td>Experience</td>
      <td><input name="experience" type="text" size="11" id="experience" value="<?=$row[Experience]?>"></td>
    </tr>
    <tr>
      <td>Level</td>
      <td><input name="level" type="text" size="11" id="level" value="<?=$row[Level]?>"></td>
    </tr>
    <tr>
      <td>chargold</td>
      <td><input name="chargold" type="text" size="11" id="chargold" value="<?=$row[CharGold]?>"></td>
    </tr>
    <tr>
      <td>stashgold</td>
      <td><input name="stashgold" type="text" size="11" id="stashgold" value="<?=$row[StashGold]?>"></td>
    </tr>
    <tr>
      <td>Prestige</td>
      <td><input name="prestige" type="text" size="11" id="prestige" value="<?=$row[Prestige]?>"></td>
    </tr>
    <tr>
      <td>Unallocated Attribute Points</td>
      <td><input name="attributepoints" type="text" size="11" id="attributepoints" value="<?=$row[AttributePoints]?>"></td>
    </tr>
    <tr>
      <td>Unallocated Stance Points</td>
      <td><input name="stancepoints" type="text" size="11" id="stancepoints" value="<?=$row[StancePoints]?>"></td>
    </tr>
    <tr>
      <td>Unallocated Power Points</td>
      <td><input name="powerpoints" type="text" size="11" id="powerpoints" value="<?=$row[PowerPoints]?>"></td>
    </tr>
    <tr>
      <td>Unallocated Skill Points</td>
      <td><input name="skillpoints" type="text" size="11" id="skillpoints" value="<?=$row[SkillPoints]?>"></td>
    </tr>
    <tr>
      <td>EntityState</td>
      <td><input name="entitystate" type="text" size="11" id="entitystate" value="<?=$row[EntityState]?>"></td>
    </tr>
    <tr>
      <td>ActiveWeapon</td>
      <td><input name="activeweapon" type="text" size="11" id="activeweapon" value="<?=$row[ActiveWeapon]?>"></td>
    </tr>
    <tr>
      <td>ActiveWeaponSlot</td>
      <td><input name="activeweaponslot" type="text" size="11" id="activeweaponslot" value="<?=$row[ActiveWeaponSlot]?>"></td>
    </tr>
    <tr>
      <td>AttackMode</td>
      <td><input name="attackmode" type="text" size="11" id="attackmode" value="<?=$row[AttackMode]?>"></td>
    </tr>
    <tr>
      <td>ElementalAdv</td>
      <td><input name="elementaladv" type="text" size="11" id="elementaladv" value="<?=$row[ElementalAdv]?>"></td>
    </tr>
    <tr>
      <td>Gender</td>
      <td><input name="gender" type="text" size="11" id="gender" value="<?=$row[Gender]?>"></td>
    </tr>
    <tr>
      <td>MinUnarmedDamage</td>
      <td><input name="minunarmeddamage" type="text" size="11" id="minunarmeddamage" value="<?=$row[MinUnarmedDamage]?>"></td>
    </tr>
    <tr>
      <td>MaxUnarmedDamage</td>
      <td><input name="maxunarmeddamage" type="text" size="11" id="maxunarmeddamage" value="<?=$row[MaxUnarmedDamage]?>"></td>
    </tr>
<!--
    <tr>
      <td>ElderBrotherID</td>
      <td><input name="elderbrotherid" type="text" size="11" id="elderbrotherid" value="<?=$row[ElderBrotherID]?>"></td>
    </tr>
-->
    <tr>
      <td>PartyID</td>
      <td><input name="partyid" type="text" size="11" id="partyid" value="<?=$row[PartyID]?>"></td>
    </tr>
    <tr>
      <td>TaskChainTag</td>
      <td><input name="taskchaintag" type="text" size="11" id="taskchaintag" value="<?=$row[TaskChainTag]?>"></td>
    </tr>
    <tr>
      <td>ChainStringID</td>
      <td><input name="chainstringid" type="text" size="11" id="chainstringid" value="<?=$row[ChainStringID]?>"></td>
    </tr>
    <!--tr>
      <td>ElderBName</td>
      <td><input name="elderbrothername" type="text" size="11" id="elderbrothername" value="<?=$row[ElderBName]?>" readonly="yes"></td>
    </tr-->
    <tr>
      <td>XPPool</td>
      <td><input name="xppool" type="text" size="11" id="xppool" value="<?=$row[XPPool]?>"></td>
    </tr>
    <tr>
      <td>Red Points</td>
      <td><input name="RedPoints" type="text" size="11" id="RedPoints" value="<?=$row[RedPoints]?>"></td>
    </tr>
    <tr>
      <td>Green Points</td>
      <td><input name="GreenPoints" type="text" size="11" id="GreenPoints" value="<?=$row[GreenPoints]?>"></td>
    </tr>
    <tr>
      <td>Hero Points</td>
      <td><input name="HeroPoints" type="text" size="11" id="HeroPoints" value="<?=$row[HeroPoints]?>"></td>
    </tr>
    <tr>
      <td>PKWarning</td>
      <td><input name="PKWarning" type="text" size="11" id="PKWarning" value="<?=$row[PKWarning]?>"></td>
    </tr>
    <tr>
      <td>Num Chain Powers</td>
      <td><input name="NumChainPowers" type="text" size="11" id="NumChainPowers" value="<?=$row[NumChainPowers]?>" Maxlength=3></td>
    </tr>
    <tr>
      <td>ClanQuit</td>
      <td><input name="clanquit" type="text" size="11" id="clanquit" value="<?=$row[ClanQuit]?>"></td>
    </tr>
    <tr>
      <td>ClanID</td>
      <td><input name="clanid" type="text" size="11" id="clanid" value="<?=$row[ClanID]?>"></td>
    </tr>
    <tr>
      <td>Guild/Hall</td>
      <td><input name="guildid" type="text" size="11" id="guildid" value="<?=$row[GuildID]?>"></td>
    </tr>
    <tr>
      <td>Rank</td>
      <td><input name="job" type="text" size="11" id="job" value="<?=$row[Job]?>"></td>
    </tr>
    <tr>
      <td>LastDuelID</td>
      <td><input name="LastDuelID" type="text" size="11" id="LastDuelID" value="<?=$row[LastDuelID]?>"></td>
    </tr>
    <tr>
      <td>Re Spec Points</td>
      <td><input name="ReSpecPoints" type="text" size="11" id="ReSpecPoints" value="<?=$row[ReSpecPoints]?>"></td>
    </tr>
    <tr>
      <td>Experience Point Percentage</td>
      <td><input name="MultiPercentage" type="text" size="11" id="MultiPercentage" value="<?=$row[MulPerc]?>"></td>
    </tr>
  </table>
  <input type="reset" name="Reset" value="Reset">
  <input type="button" name="Button" value="Save" onClick="if(valfrm())if(confirm('Overwrite?'))postform(document.form1,'<?=$scriptname?>?a=s&i=<?=$HTTP_GET_VARS[i]?>&wid=<?=$wid?>')">
  </form>
</body>
</html>
