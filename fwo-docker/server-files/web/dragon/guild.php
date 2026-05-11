<?php
require("auth.php");
$rpp = 20;

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
elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
{
	die("Access denied. Read-Only.");
}


$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error($dbGmAdm));
$htmlWc="<select name=wid onChange=\"postform(document.form1,'guild.php?a=wc')\"><option value=''></option>";
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
	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error($dbGmAdm));
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];

	if($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST')
	{
		if($HTTP_GET_VARS[a]=="s")
		{
			$indxes = $HTTP_POST_VARS["affected"];
			$update_sqls = array();
			$log_sqls = array();
			$tbls = array();
			foreach($indxes as $indx)
			{
				$Type=$HTTP_POST_VARS["type_{$indx}"];
				$LeaderID=$HTTP_POST_VARS["leaderid_{$indx}"];
				$Job=$HTTP_POST_VARS["job_{$indx}"];
				$Gold=$HTTP_POST_VARS["gold_{$indx}"];
				$Prestige=$HTTP_POST_VARS["prestige_{$indx}"];
				$Tribute=$HTTP_POST_VARS["tribute_{$indx}"];

				if($indx > 1500)
				{
					$guild_name_u16 = U8toU16(trim(stripslashes($HTTP_POST_VARS["guild_name_{$indx}"])));
					$hashvalue = unsign_signed_integer(crc32($guild_name_u16));
					$name = '0x'. hexstring($guild_name_u16) . '0000';
					$GuildName = ', Name=$name , HashName=$hashvalue';
				}
				else
				{
					$GuildName = '';
				}

				/*
					Type='$Type',
					Gold='$Gold',
					LeaderID='$LeaderID',
					Job='$Job',
					Tribute='$Tribute'
					$GuildName
				*/
				$tbls[] = "guild";
				$query_rs = "UPDATE guild SET
					Prestige='$Prestige'
					WHERE GuildID='{$indx}'";

				$update_sqls[] = $query_rs;
				$log_sqls[] = "SELECT * FROM guild WHERE GuildID='$indx'";
			}

			reset($log_sqls);
			reset($tbls);
			foreach($update_sqls as $sql)
			{
				$log_sql = current($log_sqls);
				next($log_sqls);
				$tbl = current($tbls);
				next($tbls);
				$befores = get_str_rs($dbWc, $log_sql);
				$rs = mysql_query($sql, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, $log_sql);
				
			}

			header ("Location: guild.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
			exit;
		}
		elseif($HTTP_GET_VARS[a]=="d")
		{
			$GuildID = $HTTP_GET_VARS[i];

			for($N = 0; $N <=9; $N++)
			{
				$log_sql = "SELECT * FROM intdata_$N WHERE GuildID='$GuildID'";
				$query_sql = "UPDATE intdata_$N SET ClanID=0, GuildID=0, job=0 WHERE GuildID='$GuildID'";

				$befores = get_str_rs($dbWc, $log_sql);
				$rs = mysql_query($query_sql, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, $log_sql);
				

				$log_sql = "SELECT * FROM pcharstats_$N WHERE GuildID='$GuildID'";
				$query_sql = "UPDATE pcharstats_$N SET ClanID=0, GuildID=0, Job=0 WHERE GuildID='$GuildID'";

				$befores = get_str_rs($dbWc, $log_sql);
				$rs = mysql_query($query_sql, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, $log_sql);
				

				$log_sql = "SELECT * FROM guildlist_$N WHERE GuildID='$GuildID'";
				$query_sql = "DELETE FROM guildlist_$N WHERE GuildID='$GuildID'";

				$befores = get_str_rs($dbWc, $log_sql);
				$rs = mysql_query($query_sql, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc, $log_sql);
				
			}
			$log_sql = "SELECT * FROM ally WHERE GuildID='$GuildID'";
			$query_sql = "UPDATE ally SET GuildID=0 WHERE GuildID='$GuildID'";

			$befores = get_str_rs($dbWc, $log_sql);
			$rs = mysql_query($query_sql, $dbWc) or die(mysql_error($dbWc));
			$after = get_str_rs($dbWc, $log_sql);
			

			$log_sql = "SELECT * FROM guild WHERE GuildID='$GuildID'";
			$query_sql = "DELETE FROM guild WHERE GuildID='$GuildID'";

			$befores = get_str_rs($dbWc, $log_sql);
			$rs = mysql_query($query_sql, $dbWc) or die(mysql_error($dbWc));
			$after = get_str_rs($dbWc, $log_sql);
			

			header ("Location: guild.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
			exit;
		}
	}

		$req_guild_id = $HTTP_POST_VARS[guild_id];
		$req_clan_id = $HTTP_POST_VARS[clan_id];
		$req_guild_name = trim($HTTP_POST_VARS[guild_name]);
		$req_only_player_guild = $HTTP_POST_VARS[only_player_guild];

		 if($HTTP_GET_VARS['a'] != 'f') $req_only_player_guild = 1;

		$sql_cond = "";

		if($HTTP_GET_VARS[i])
		{
			$sql_cond .=" AND guild.GuildID='{$HTTP_GET_VARS[i]}'";
			$req_only_player_guild = 0;
		}
		else
		{
			if(strlen($req_guild_id) > 0)
			{
				$sql_cond .= " AND guild.GuildID='$req_guild_id'";
			}
			if(strlen($req_clan_id) > 0)
			{
				if($req_clan_id == '0')
					$sql_cond .= " AND ally.ClanID IS NULL";
				else
					$sql_cond .= " AND ally.ClanID='$req_clan_id'";
			}
			if(strlen($req_guild_name) > 0)
			{
				$hex_req_guild_name = hexstring(U8toU16(stripslashes($req_guild_name)));
				$namestr_cond= '';

				if(!$req_only_player_guild)
				{
					$a = getid($req_guild_name, 'string');
					if(count($a)>0)
					{
						$indxes = join($a, ",");
						$namestr_cond = " OR NameStrID IN ($indxes)";
					}
				}

				$sql_cond .= " AND (LOCATE(0x{$hex_req_guild_name}, guild.Name)=1 $namestr_cond)";
			}
			if($req_only_player_guild > 0)
			{
				$sql_cond .= " AND guild.GuildID>1500";
			}
		}

//	$paging =

	$query_rs = "SELECT count(*) FROM guild LEFT JOIN ally ON guild.GuildID=ally.GuildID WHERE 1 $sql_cond"; //WHERE $sql_cond
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
	list($guild_count) = mysql_fetch_row($rs);
	mysql_free_result($rs);

	$pg = $_REQUEST['pg'];
	$last_page = ceil($guild_count / $rpp);
	if($pg > $last_page) $pg = $last_page;
	if($pg < 1) $pg = 1;
	$offset = (($pg - 1) * $rpp);

	$query_rs = "SELECT guild.*, ClanID FROM guild LEFT JOIN ally ON guild.GuildID=ally.GuildID WHERE 1 $sql_cond ORDER BY GuildID LIMIT $offset, $rpp"; //WHERE $sql_cond
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));

	function mklink($n){
		global $pg;
		$tag = $n == $pg?"<b>$n</b>":"$n";
		return "<a href=\"javascript:document.form1.pg.value='{$n}';document.form1.submit()\">$tag</a>";
	}

	if($last_page > 0)
	{
		$s1 = -10;
		$s2 = 10;

		$html_page = "<input type=\"hidden\" name=\"pg\" value=\"$pg\">Found $guild_count item(s).<br>Page: ";

		if($pg + $s1 > 1) $html_page .= mklink(1) . "... ";
		for($n = $s1; $n < $s2; $n++)
		{
			$pp = $pg + $n;
			if($pp > $last_page) break;
			if($pp > 0 )
				$html_page .= mklink($pp) . " ";
		}
		if($pg + $s2 < $last_page) $html_page .= " ..." . mklink($last_page);
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
	form.method='post';form.action=url;form.submit()
}
function saveid(i)
{
	if(eval("!document.form1.mark_"+i+".value"))
	{
		eval("document.form1.mark_"+i+".value=1")
		document.form1.ids.value+=i+"|"
	}
}
//-->
</script>
</head>
<body>
<form name="form1" method="post" action="">
<h3>Guild</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
<?
if($wid)
{
	?>
Search guild(s) by
<table border=1 cellspacing=0>
	<tr>
		<td width=80>Guild ID</td>
		<td><input name=guild_id value="<?=$HTTP_GET_VARS[i]?$HTTP_GET_VARS[i]:$req_guild_id?>" size=8></td>
	</tr>
	<tr>
		<td width=80>Clan</td>
		<td>
			<select name=clan_id>
			<?
				$selected = ($req_clan_id == '0')? "SELECTED": "";
				echo "<option value=\"\"></option><option value=\"0\" $selected>- No Clan -</option>";
				foreach($clan_ids as $clan_id)
				{
					$selected = ($clan_id == $req_clan_id)? "SELECTED": "";
					echo "<option value=\"$clan_id\" $selected>$clan_name[$clan_id]</option>";
				}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td width=80>Guild Name</td>
		<td><input name=guild_name value="<?=$req_guild_name?>" size=20 amaxlength=20></td>
	</tr>
	<tr>
		<td colspan=2><input type=checkbox name=only_player_guild value="1" <?=$req_only_player_guild?"CHECKED":""?>>Show only player created guild(s)</td>
	</tr>
</table>
<input type=submit value="Submit" onclick="postform(document.form1,'guild.php?a=f')">
	<?
	if($rs)
	{
		if(mysql_num_rows($rs) < 1)
		{
			echo "<p><font color=red><b>No matched queries.</b></font>";
		}
		else
		{
	?>
<hr>
<font color=red><b>Warning: Please make sure the game application servers are shutdown befores saving or deleting the guild data, or unexpected game error may occur.</b></font>
<table border="1" cellspacing=0>
	<tr>
		<td>#</td>
		<td>Guild ID</td>
		<td>Type</td>
		<td width=200>Guild Name</td>
		<td>Clan</td>
		<!--td>Leader</td>
		<td>Job</td-->
		<!--td>Gold</td-->
		<td>Prestige</td>
		<!--td>Tribute</td-->
		<td># of Members</td>
		<td>Changed <div style="display:none"><input type="checkbox" name="affected[]"></div></td>
		<td>&nbsp;</td>
	</tr>
		<?
		$idx = 0;
		while($row=mysql_fetch_assoc($rs))
		{
			$offset++;
			$idx++;
			$i=$row[GuildID];
			$clan_id = $row[ClanID];
		?>
	<tr onmouseover="this.className='hl'" onmouseout="this.className=''">
		<td><?=$offset?></td>
		<td><a href="guildlist.php?wid=<?=$wid?>&i=<?=$i?>&f=<?=$row[ClanID]?>"><?=$i?></a></td>
		<td><?=$row[Type]?></td>
		<td>
			<?
			if($row[GuildID] >= 1500)
			{
				$guild_name = U16btoU8str(remove_dbcs_null($row[Name]));
				echo "<nobr>" . htmlspecialchars($guild_name) . "</nobr>";
				//echo "<input size=30 maxlength=20 name=\"name_{$i}\" onchange=\"document.form1.elements('affected[]').item({$idx}).checked=1\" value=\"" . htmlspecialchars($guild_name) . "\">";
			}
			else
				echo "<nobr>". U16btoU8str(getstring($row[NameStrID],'string')) . "</nobr>"; //$row[NameStrID];
			?>
		</td>
		<td>
		<?
		if($clan_id == 0)
		{
			echo "<nobr>(No Clan)</nobr>";
		}
		else
		{
			echo "<a href=\"gmclan.php?wid=$wid&a=f&i={$clan_id}\">{$clan_shortname[$clan_id]}</a>";
		}

		?>
		<?/*
		<select  onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1">
		<?
				foreach($clan_ids as $clan_id)
				{
					$selected = ($clan_id == $selected_clan)? "SELECTED": "";
					echo "<option value=\"$clan_id\" $selected>$clan_shortname[$clan_id]</option>";
				}
		?>
		*/?>
		</select>
		</td>
		<?
		/*
		<td><input name="leaderid_<?=$i?>" type="text" size=10 value="<?=$row[LeaderID]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>
		<td><input name="job_<?=$i?>" size=2 value="<?=$row[Job]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>
		<td><input name="tribute_<?=$i?>" size=10 value="<?=$row[Tribute]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>
		<td><input name="gold_<?=$i?>" size=10 value="<?=$row[Gold]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>
		*/
		?>
		<td><input name="prestige_<?=$i?>" size=10 value="<?=$row[Prestige]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>
		<td><?=$row[NumMembers]?></td>
		<td><input type="checkbox" name="affected[]" value="<?=$i?>">
		<td>
		<input type="button" value="Delete" onclick="if(confirm('Delete?'))postform(document.form1,'guild.php?a=d&i=<?=$i?>')" <?=($i<1501)?"DISABLED":""?>>
		</td>
	</tr>
		<?
		} //while
		?>
	</table>
	<?=$html_page?>
	<input type="hidden" name="ids" value="">
	<br><br><input type="reset" name="Reset" value="Reset" onclick="return(confirm('undo all changes?'))">
	<input type="button" name="Button" value="Save" onClick="var save=false;with(document.form1){for(var n=0;n<elements('affected[]').length;n++){save=elements('affected[]').item(n).checked;if(save)break}if(!save){alert('no change');return 0} if(confirm('Overwrite?'))postform(document.form1,'guild.php?a=s&i=<?=$HTTP_GET_VARS[i]?>')}">
	<?
	}
}
}
?>
</form>
</body>
</html>