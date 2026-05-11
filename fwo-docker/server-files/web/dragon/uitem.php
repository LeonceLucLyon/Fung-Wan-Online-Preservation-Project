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
$htmlWc="<select name=wid onChange=\"postform(document.form1,'uitem.php?a=wc')\"><option value=''></option>";
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

	if($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST' || $HTTP_GET_VARS['i'])
	{
		if($HTTP_GET_VARS[a]=="s")
		{
			$indxes = $HTTP_POST_VARS["affected"];
			$update_sqls = array();
			$log_sqls = array();
			$tbls = array();
			foreach($indxes as $indx)
			{

				$OCharID=$HTTP_POST_VARS["OCharID_{$indx}"];
				$CharID=$HTTP_POST_VARS["CharID_{$indx}"];
				$revertToID=$HTTP_POST_VARS["revertToID_{$indx}"];
				$OriginatorID=$HTTP_POST_VARS["OriginatorID_{$indx}"];
				$DecayCounter=$HTTP_POST_VARS["DecayCounter_{$indx}"];

				//$Time=$HTTP_POST_VARS["Time_{$indx}"];
				//$SetID=$HTTP_POST_VARS["SetID_{$indx}"];
				//$NPCFlag=$HTTP_POST_VARS["NPCFlag_{$indx}"];

				if($OCharID <> $CharID) // owner is changed
				{
					if(is_player_character($OCharID))
					{
						$rs_logon = mysql_query("SELECT 1 FROM authenticated WHERE CharID='{$OCharID}'", $dbWc) or die(mysql_error($dbWc));
						$is_logon=mysql_num_rows($rs_logon);
						mysql_free_result($rs_logon);
						if($is_logon) die("Item cannot be removed as the character, <a href=\"javascript:var w=window.open('gmchar.php?wid={$wid}&a=f&charid_0={$OCharID}')\">$OCharID</a>, is logged on.");
					}

					if(is_player_character($CharID)) // new owner is Pchar
					{
						$rs_logon = mysql_query("SELECT 1 FROM authenticated WHERE CharID='{$CharID}'", $dbWc) or die(mysql_error($dbWc));
						$is_logon=mysql_num_rows($rs_logon);
						mysql_free_result($rs_logon);
						if($is_logon) die("Item cannot be transferred as the character, <a href=\"javascript:var w=window.open('gmchar.php?wid={$wid}&a=f&charid_0={$CharID}')\">$CharID</a>, is logged on.");

						$tbl_charinv = "charinv_" . ($CharID % 10);
                        /* MODIFY AT 20051115 FOR CIB
						$rs_slot = mysql_query("SELECT Indx FROM $tbl_charinv WHERE CharID='$CharID' AND ItemID=0 AND SlotNum>=2 AND SlotNum<=17 ORDER BY SlotNum", $dbWc) or die(mysql_error($dbWc));
                        */
                        $rs_slot = mysql_query("SELECT Indx FROM $tbl_charinv WHERE CharID='$CharID' AND ItemID=0 AND SlotNum>=86 AND SlotNum<=133 ORDER BY SlotNum", $dbWc) or die(mysql_error($dbWc));
						if(mysql_num_rows($rs_slot) > 0)
						{
							list($slot_id) = mysql_fetch_row($rs_slot);
							mysql_free_result($rs_slot);

							$rs_item = mysql_query("SELECT * FROM item WHERE ItemID='$indx'", $dbWc) or die(mysql_error($dbWc));
							$row_item = mysql_fetch_assoc($rs_item);
							$hardness = $row_item[Hardness];
							if(is_weapon($indx)) //is weapon?
							{
								$fld1 = $row_item[Field4];
								$fld2 = $row_item[Field5];
								$fld3 = $row_item[Field6];
								$fld4 = $row_item[Field7];
								$fld5 = $row_item[Field8];
							}
							else
							{
								$fld1 = $fld2 = $fld3 = $fld4 = $fld5 = 0;
							}

							//$rs = mysql_query("UPDATE $tbl_charinv SET ItemID='$indx', Quantity=1, Identified=1, Field1='$fld1', Field2='$fld2', Field3='$fld3', Field4='$fld4', Field5='$fld5', Hardness='$hardness', Durability=100 WHERE Indx='{$slot_id}';", $dbWc) or die(mysql_error($dbWc));
							$tbls[] = $tbl_charinv;
							$update_sqls[] = "UPDATE $tbl_charinv SET ItemID='$indx', Quantity=1, Identified=1, Field1='$fld1', Field2='$fld2', Field3='$fld3', Field4='$fld4', Field5='$fld5', Hardness='$hardness', Durability=100 WHERE Indx='{$slot_id}';";
							$log_sqls[] = "SELECT * FROM $tbl_charinv WHERE Indx='{$slot_id}'";
						}
						else
						{
							// no slot
							die("The Character, <a href=\"charinv.php?wid={$wid}&i={$CharID}\" target=\"_blank\">{$CharID}</a>, has no empty slot to put the item or invalid CharID.");
						}
					}
					elseif(is_clan($CharID))
					{
						if(is_own_clan_relic($CharID, $indx))
						{
							$tbls[] = "clan";
							$update_sqls[] = "UPDATE clan SET Relic1='{$indx}' WHERE ClanID='{$CharID}'";
							$log_sqls[] = "SELECT * FROM clan WHERE ClanID='{$CharID}'";
						}
						else
						{
							$rs_slot = mysql_query("SELECT * FROM clan WHERE ClanID='$CharID'", $dbWc) or die(mysql_error($dbWc));
							$row_slot = mysql_fetch_assoc($rs_slot);
							for($n=1;$n<=16;$n++)
							{
								if($row_slot["Relic$n"] == "0")
								{
									$tbls = "clan";
									$update_sqls[] = "UPDATE clan SET Relic{$n}='{$indx}' WHERE ClanID='{$CharID}'";
									$log_sqls[] = "SELECT * FROM clan WHERE ClanID='$CharID'";
									break;
								}
							}
						}
					}

					if(is_player_character($OCharID)) // it was with Pchar
					{
						$tbl_charinv = "charinv_" . ($OCharID % 10);
						$tbls[] = $tbl_charinv;
						//$rs = mysql_query("UPDATE $tbl_charinv SET ItemID=0, Quantity=0, Identified=0, Field1=0, Field2=0, Field3=0, Field4=0, Field5=0 WHERE CharID='$CharID' AND ItemID='{$indx}';", $dbWc) or die(mysql_error($dbWc));
						$update_sqls[] = "UPDATE $tbl_charinv SET ItemID=0, Quantity=0, Identified=0, Field1=0, Field2=0, Field3=0, Field4=0, Field5=0 WHERE CharID='$OCharID' AND ItemID='{$indx}';";
						$log_sqls[] = "SELECT * FROM $tbl_charinv WHERE CharID='$OCharID' AND ItemID='{$indx}';";
					}
					elseif(is_clan($OCharID)) // it was with a clan
					{
						for($n = 1; $n <= 16; $n++)
						{
							$tbls = array();
							$tbls[] = "clan";
							$update_sqls[] = "UPDATE clan SET Relic{$n}=0 WHERE ClanID='$OCharID' AND Relic{$n}='{$indx}';";
							$log_sqls[] = "SELECT * FROM clan WHERE ClanID='$OCharID' AND Relic{$n}='{$indx}'";
						}
					}

				}

				$tbls[] = "uniqueitem";
				$query_rs = "UPDATE uniqueitem SET
					CharID='{$CharID}',
					revertToID='{$revertToID}',
					OriginatorID='{$OriginatorID}',
					DecayCounter='{$DecayCounter}'
					WHERE ItemID='{$indx}'";
				//	Time='{$Time}',
				//	SetID='{$SetID}',
				//	NPCFlag='{$NPCFlag}',

				$update_sqls[] = $query_rs;
				$log_sqls[] = "SELECT * FROM uniqueitem WHERE ItemID='$indx'";
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

			header ("Location: uitem.php?i={$HTTP_GET_VARS[i]}&wid=$wid");
			exit;
		}
		elseif($HTTP_GET_VARS[a]=="d")
		{
		}

		$searchby=$HTTP_POST_VARS[searchby];
		$itemid=$HTTP_POST_VARS[itemid];
		$hbyte=$HTTP_POST_VARS[hbyte];
		$lbyte=$HTTP_POST_VARS[lbyte];
		$itemname=trim($HTTP_POST_VARS[itemname]);

		$group_id=is_numeric($hbyte)?(float)$hbyte * 65536: 0;
		$type_id=is_numeric($lbyte)?(float)$lbyte: 0;


		if($HTTP_GET_VARS[i])
		{
			$sql_cond="AND ItemID='{$HTTP_GET_VARS[i]}'";
		}
		elseif($searchby == '' && strlen($itemid) > 0)
		{
			$sql_cond="AND ItemID='$itemid'";
		}
		elseif($searchby == "0" && $group_id>0)
		{
			if($type_id==0 && strlen($itemid) > 0){
				$sql_cond="AND ItemID>$group_id AND ItemID<($group_id + 65536)";
			}
			else
			{
				$sql_cond="AND ItemID='". ($group_id+$type_id) ."'";
			}
		}
		elseif($searchby == "1" && strlen($itemname) > 0)
		{
			$a = getid($itemname, 'item');
			if(count($a)>0)
			{
				$indxes = join($a, ",");
				$sql_cond="AND ItemID IN ($indxes)";
			}
			else
			{
				$sql_cond="AND 0";
			}
		}

	}

//	$paging =

	$query_rs = "SELECT count(*) FROM uniqueitem WHERE 1 $sql_cond"; //WHERE $sql_cond
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
	list($unique_item_count) = mysql_fetch_row($rs);
	mysql_free_result($rs);

	$pg = $_REQUEST['pg'];
	$last_page = ceil($unique_item_count / $rpp);
	if($pg > $last_page) $pg = $last_page;
	if($pg < 1) $pg = 1;
	$offset = (($pg - 1) * $rpp);

	$query_rs = "SELECT * FROM uniqueitem WHERE 1 $sql_cond ORDER BY ItemID LIMIT $offset, $rpp"; //WHERE $sql_cond
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

		$html_page = "<input type=\"hidden\" name=\"pg\" value=\"$pg\">Found $unique_item_count item(s).<br>Page: ";

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
<h3>Unique Item</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
<?
if($wid)
{
	eval("\$searchby_checked{$HTTP_POST_VARS['searchby']} = 'CHECKED';");
	?>
Search unique item(s) by
<table border=1 cellspacing=0>
	<tr>
		<td><input type=radio name=searchby value='' <?=$searchby_checked?> onclick="with(document.form1){hbyte.value=lbyte.value=itemname.value=''}"></td>
		<td>
			<table>
				<tr>
					<td width=80>Item ID</td>
					<td><input name=itemid value="<?=$HTTP_GET_VARS[i]?$HTTP_GET_VARS[i]:$HTTP_POST_VARS[itemid]?>" size=8></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td valign=top rowspan=1><input type=radio name=searchby value=0 <?=$searchby_checked0?> onclick="with(document.form1){itemid.value=itemname.value=''}"></td>
		<td>
			<table>
				<tr>
					<td width=80>Item group</td>
					<td><input name=hbyte value="<?=$HTTP_POST_VARS[hbyte]?>" size=8></td>
				</tr>
				<tr>
					<td>Item type</td>
					<td><input name=lbyte value="<?=$HTTP_POST_VARS[lbyte]?>" size=8></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td><input type=radio name=searchby value='1' <?=$searchby_checked1?> onclick="with(document.form1){hbyte.value=lbyte.value=itemid.value=''}"></td>
		<td>
			<table>
				<tr>
					<td width=80>Item Name</td>
					<td><input name=itemname value="<?=$itemname?>" size=20></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<input type=button value="Submit" onclick="postform(document.form1,'uitem.php?a=f')">
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
  <table border="1" cellspacing=0>
    <tr>
	    <td>#</td>
      <td>Item ID</td>
      <td>CharID/ClanID/ResourceID</td>
      <td>RevertToID</td>
      <td>OriginatorID</td>
      <td>DecayCounter</td>
<!--
      <td>Time</td>
      <td>SetID</td>
      <td>NPCFlag</td>
-->
      <td>Changed <div style="display:none"><input type="checkbox" name="affected[]"></div></td>
    </tr>
		<?
		$idx = 0;
		while($row=mysql_fetch_assoc($rs))
		{
			$offset++;
			$idx++;
			$i=$row[ItemID];
			$is_unique_item = (($i >> 16) > 100) || (($i >> 16) == 21);

			$unique_item = "$i";

			if( is_player_character($row[CharID]))
			{
				$a = "<a href=\"charinv.php?wid=$wid&f=$row[CharID]\" target=\"_blank\"><img border=0 src=\"images/link.gif\"></a>";
			}
			elseif( is_clan($row[CharID]) )
			{
				$a = "<a href=\"gmclan.php?wid=$wid&a=f&i=$row[CharID]\" target=\"_blank\"><img border=0 src=\"images/link.gif\"></a>";
			}
			else
			{
				$a = "<img src=\"images/ulink.gif\">";
			}
		?>
    <tr onmouseover="this.className='hl'" onmouseout="this.className=''">
	<td><?=$offset?></td>
      <td><nobr><?=$unique_item?>, <?=getstring($row[ItemID],'item')?></nobr></td>
      <td><input name="OCharID_<?=$i?>" type="hidden" value="<?=$row[CharID]?>">
	      <nobr>
	      <input name="CharID_<?=$i?>" type="text" value="<?=$row[CharID]?>" size=12 maxlength=10 onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1">
	      <?=$a?>
	      <input type=button value="Set as RevertToID" onclick="with(document.form1){CharID_<?=$i?>.value='<?=$row[revertToID]?>';document.form1.elements('affected[]').item(<?=$idx?>).checked=1}">
	      <input type=button value="Set as OriginatorID" onclick="with(document.form1){CharID_<?=$i?>.value='<?=$row[OriginatorID]?>';elements('affected[]').item(<?=$idx?>).checked=1}">
	      </nobr>
	</td>
      <td> <input name="revertToID_<?=$i?>" type="hidden" value="<?=$row[revertToID]?>"> <?=$row[revertToID]?></td>
      <td> <input name="OriginatorID_<?=$i?>" type="hidden" value="<?=$row[OriginatorID]?>"> <?=$row[OriginatorID]?></td>
      <td> <input name="DecayCounter_<?=$i?>" type="text" size=4 value="<?=$row[DecayCounter]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1"></td>
      <td><input type="checkbox" name="affected[]" value="<?=$i?>">
    </tr>
		<?
		} //while
		?>
  </table>
	<?=$html_page?>
	<input type="hidden" name="ids" value="">
	<br><br><input type="reset" name="Reset" value="Reset" onclick="return(confirm('undo all changes?'))">
	<input type="button" name="Button" value="Save" onClick="var save=false;with(document.form1){for(var n=0;n<elements('affected[]').length;n++){save=elements('affected[]').item(n).checked;if(save)break}if(!save){alert('no change');return 0} if(confirm('Overwrite?'))postform(document.form1,'uitem.php?a=s&i=<?=$HTTP_GET_VARS[i]?>')}">
	<?
	}
}
}
?>
</form>
</body>
</html>
