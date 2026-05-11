<?php
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

	$charinv_table = "charinv_" . ($HTTP_GET_VARS[f] % 10);

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$wc_ip = $row_rsSvr[ip];
	$wc_db = $row_rsSvr[db];
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);

	$query_rs1 = "SELECT * FROM pcharacter WHERE CharID='{$HTTP_GET_VARS[f]}'";
	$rs1 = mysql_query($query_rs1, $dbWc) or die(mysql_error());
	$row1 = mysql_fetch_assoc($rs1);
	mysql_free_result($rs1);

	if($HTTP_GET_VARS[a]=="s")
	{
		//chk if character is being used
		$rs_logon = mysql_query("SELECT * FROM authenticated WHERE CharID='{$HTTP_GET_VARS[f]}'", $dbWc) or die(mysql_error());
		$is_logon=mysql_num_rows($rs_logon);
		mysql_free_result($rs_logon);
		if($is_logon && $HTTP_GET_VARS[force]!=1)
		{
			echo "<form name=form1 action=\"{$HTTP_SERVER_VARS[REQUEST_URI]}&force=1\" method='Post'>";
			echo generate_form('',$HTTP_POST_VARS);
			echo "<input type=button value='Force Save' onclick='if(confirm(\"Do not force save if the character is being used or this will cause data error.\"))document.form1.submit()'></form>";
			//post_form('document.form1',$HTTP_SERVER_VARS[REQUEST_URI]."&force=1");
			die("game character is being used, write access deny");
		}

		//$indx=$HTTP_GET_VARS["i"];
		if($HTTP_POST_VARS["ids"])
		{
			$indxes=split("\|",$HTTP_POST_VARS["ids"]);

			$ppls_items=null;
			foreach($indxes as $indx)
			{
				if($indx=="")break;

				$itemid=(float)$HTTP_POST_VARS["itemid_$indx"];

				if($itemid >> 16 == 21 || $itemid >> 16 >= 100)	//21 is relic; >=100 is unique item
				{
					$rs = mysql_query("SELECT ItemID, u.CharID, CharacterName FROM uniqueitem u, pcharacter p WHERE u.CharID=p.CharID AND ItemID='{$itemid}' AND u.CharID >0 AND u.CharID <> '{$HTTP_GET_VARS[f]}';", $dbWc) or die(mysql_error());
					while($row=mysql_fetch_row($rs))
					{
						$ppls_items[] = $row;
					}
					mysql_free_result($rs);
				}
			}
			if($ppls_items)
			{
				foreach($ppls_items as $item)
				{
					echo "<li><a href=\"uitem.php?i={$item[0]}&wid=$wid\">".getstring($item[0],'item')."</a> is hold by $item[2]";
				}
				die('<hr>Reload this page to resubmit after above unique item(s) has/have no owner');
			}

			foreach($indxes as $indx)
			{
				if($indx=="")break;

				$itemid=(float)$HTTP_POST_VARS["itemid_$indx"];
				$oitemid=(float)$HTTP_POST_VARS["oitemid_$indx"];

				if(($oitemid >> 16 == 21 || $oitemid >> 16 >= 100) && ($oitemid != $itemid))
				{
					$sql = "UPDATE uniqueitem SET CharID='0' WHERE ItemID='{$oitemid}';";
					$rs = mysql_query($sql, $dbWc) or die(mysql_error());
					
				}

				if($itemid >> 16 == 21 || $itemid >> 16 >= 100)	//21 is relic; >=100 is unique item
				{
					$sql = "UPDATE uniqueitem SET CharID='{$HTTP_GET_VARS[f]}' WHERE ItemID='{$itemid}';";
					$rs = mysql_query($sql, $dbWc) or die(mysql_error());
					
				}

				$slotnum=$HTTP_POST_VARS["slotnum_$indx"];
				$quantity=$HTTP_POST_VARS["quantity_$indx"];
				$identified=$HTTP_POST_VARS["identified_$indx"];
				$durability=$HTTP_POST_VARS["durability_$indx"];
				$hardness=$HTTP_POST_VARS["hardness_$indx"];
				$field1=$HTTP_POST_VARS["field1_$indx"];
				$field2=$HTTP_POST_VARS["field2_$indx"];
				$field3=$HTTP_POST_VARS["field3_$indx"];
				$field4=$HTTP_POST_VARS["field4_$indx"];
				$field5=$HTTP_POST_VARS["field5_$indx"];

                $qry_tag = mysql_query("SELECT * FROM item WHERE ItemID = '$itemid'",$dbWc) or die (mysql_error($dbWc));
                $rs_tag = mysql_fetch_assoc($qry_tag);
                if ($rs_tag[Display]=="1" || $itemid=="0")
                {
                    $befores = get_str_rs($dbWc, "SELECT p.Username, p.CharacterName, c.* FROM $charinv_table c, pcharacter p WHERE p.CharID=c.CharID AND c.Indx='$indx';");

                    $query_rs = "UPDATE $charinv_table SET
					   ItemID='{$itemid}',
					   SlotNum='{$slotnum}',
					   Quantity='{$quantity}',
					   Identified='{$identified}',
					   Durability='{$durability}',
					   Hardness='{$hardness}',
					   Field1='{$field1}',
					   Field2='{$field2}',
					   Field3='{$field3}',
					   Field4='{$field4}',
					   Field5='{$field5}'
					   WHERE Indx='$indx'";

                    $rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
                    $after = get_str_rs($dbWc, "SELECT p.Username, p.CharacterName, c.* FROM $charinv_table c, pcharacter p WHERE p.CharID=c.CharID AND c.Indx='$indx';");
				    
                }
                elseif ($rs_tag[Display]=="0")
                {
                    $record[] = $rs_tag[ItemID];
                }
                /*
                else
                {
                    $info = "Item in Slot Number " . $SlotNum . "will not be inserted because of that item status.";
                }
                */
                mysql_free_result($qry_tag);

			}
		}
		$countre = count($record);
		for($n=0;$n<$countre;$n++)
		{
            $tg .= '&z' . $n . '=' . $record[$n];
        }

		header("Location: charinv.php?f={$HTTP_GET_VARS[f]}&wid=$wid" . "$tg");
		exit();
	}

	$query_rs = "SELECT * FROM $charinv_table WHERE CharID='{$HTTP_GET_VARS[f]}' ORDER BY SlotNum";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	if(mysql_num_rows($rs) == 0) die("<font color=red>No matched queries.</font>");
//	$row=mysql_fetch_assoc($rs);
//	mysql_free_result($rs);

}
// echo("sql = ".$query_rs."<br>");
// echo("version =".$row_rsSvr[version]."<br>");
// echo("server IP = ".$wc_ip."<br>");

//This line has been modified at 20050907, which added $row_rsSvr[version]==""
if(($row_rsSvr[version]=="1.2_OMI") or ($row_rsSvr[version]=="1.2") or ($row_rsSvr[version]==""))
{
	$slot_desc=array(
	1=>'MOUSE',
	'Torso',
	'Neck',
	'Shoulder',
	'Arms',
	'Feet',
	'Face',
	'Ring1',
	'Ring2',
	10=>'Weapon',
	28=>'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	30=>'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	40=>'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	50=>'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	60=>'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',
	'QuickSlot',

	86=>'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	90=>'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	100=>'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	110=>'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	120=>'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	130=>'Inventory',
	'Inventory',
	'Inventory',
	'Inventory',
	151=>'Crafting/Trading',
	'Crafting/Trading',
	'Crafting/Trading',
	'Crafting/Trading',
	'Crafting/Trading',
	'Crafting/Trading',
	'Crafting/Trading',

	175=>'Loot',
	'Loot',
	'Loot',
	'Loot',
	'Loot',
	180=>'Loot'

	);
}

else{

	$slot_desc=array(
	1=>'MOUSE',
	'INVENTORY_1',
	'INVENTORY_2',
	'INVENTORY_3',
	'INVENTORY_4',
	'INVENTORY_5',
	'INVENTORY_6',
	'INVENTORY_7',
	'INVENTORY_8',
	10=>'INVENTORY_9',
	'INVENTORY_10',
	'INVENTORY_11',
	'INVENTORY_12',
	'INVENTORY_13',
	'INVENTORY_14',
	'INVENTORY_15',
	'INVENTORY_16',
	'BODY_TORSO',
	'BODY_NECK',
	20=>'BODY_ARM',
	'BODY_FEET',
	'RING_1',
	'RING_2',
	'ATTACH_SHOULDER',
	'ATTACH_FACE',
	'WEAPON_1',
	'WEAPON_2',
	'WEAPON_3',
	'WEAPON_4',
	30=>'WEAPON_5',
	'QUICKITEM_1',
	'QUICKITEM_2',
	'QUICKITEM_3',
	'QUICKITEM_4',
	'QUICKITEM_5',
	'PERSONALSTASH_1',
	'PERSONALSTASH_2',
	'PERSONALSTASH_3',
	'PERSONALSTASH_4',
	40=>'PERSONALSTASH_5',
	'PERSONALSTASH_6',
	'PERSONALSTASH_7',
	'PERSONALSTASH_8',
	'PERSONALSTASH_9',
	'PERSONALSTASH_10',
	'PERSONALSTASH_11',
	'PERSONALSTASH_12',
	'PERSONALSTASH_13',
	'PERSONALSTASH_14',
	50=>'PERSONALSTASH_15',
	'PERSONALSTASH_16',
	'SIDEPOCKET_1',
	'SIDEPOCKET_2',
	'SIDEPOCKET_3',
	'SIDEPOCKET_4',
	'SIDEPOCKET_5',
	'SIDEPOCKET_6',
	'CRAFT_1 = 68',
	'CRAFT_2',
	'CRAFT_3',
	'CRAFT_4',
	'CRAFT_5',
	'CRAFT_6',
	80=>'CONTAINER_1',
	'CONTAINER_2',
	'CONTAINER_3',
	'CONTAINER_4',
	'CONTAINER_5',
	'CONTAINER_6',
	'CONTAINER_7',
	'CONTAINER_8',
	'CONTAINER_9',
	'CONTAINER_10',
	90=>'CONTAINER_11',
	'CONTAINER_12',
	'CONTAINER_13',
	'CONTAINER_14',
	'CONTAINER_15',
	'CONTAINER_16',
	99=>'TRASH',
	150=>'RELIC_1',
	'RELIC_2',
	'RELIC_3',
	'RELIC_4',
	'RELIC_5',
	'RELIC_6',
	'RELIC_7',
	'RELIC_8',
	'RELIC_9',
	'RELIC_10',
	160=>'RELIC_11',
	'RELIC_12',
	'RELIC_13',
	'RELIC_14',
	'RELIC_15',
	'RELIC_16',
	'TARGETTRADE_1',
	'TARGETTRADE_2',
	'TARGETTRADE_3',
	'TARGETTRADE_4',
	170=>'TARGETTRADE_5',
	'TARGETTRADE_6',
	201=>'GENQUICKSLOT_1',
	'GENQUICKSLOT_2',
	'GENQUICKSLOT_3',
	'GENQUICKSLOT_4',
	'GENQUICKSLOT_5',
	'GENQUICKSLOT_6',
	'GENQUICKSLOT_7',
	'GENQUICKSLOT_8',
	'GENQUICKSLOT_9',
	210=>'GENQUICKSLOT_10',
	'GENQUICKSLOT_11',
	'GENQUICKSLOT_12',
	'GENQUICKSLOT_13',
	'GENQUICKSLOT_14',
	'GENQUICKSLOT_15',
	'GENQUICKSLOT_16',
	'GENQUICKSLOT_17',
	'GENQUICKSLOT_18',
	'GENQUICKSLOT_19',
	220=>'GENQUICKSLOT_20',
	'GENQUICKSLOT_21',
	'GENQUICKSLOT_22',
	'GENQUICKSLOT_23',
	'GENQUICKSLOT_24',
	'GENQUICKSLOT_25',
	'GENQUICKSLOT_26',
	'GENQUICKSLOT_27',
	'GENQUICKSLOT_28',
	'GENQUICKSLOT_29',
	230=>'GENQUICKSLOT_30',
	'GENQUICKSLOT_31',
	'GENQUICKSLOT_32',
	'GENQUICKSLOT_33',
	'GENQUICKSLOT_34',
	'GENQUICKSLOT_35',
	'GENQUICKSLOT_36',
	'GENQUICKSLOT_37',
	'GENQUICKSLOT_38',
	'GENQUICKSLOT_39',
	240=>'GENQUICKSLOT_40'
	);

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
<?
$onload = "onLoad=\"";
for($h=0;$h<10;$h++)
{
    $tmpp = "z" . $h;
    if ($HTTP_GET_VARS[$tmpp])
    {
        $onload .= "javascript:alert('Item $HTTP_GET_VARS[$tmpp] is locked and will not be inserted.');";
    }
}
$onload .= "\"";

if (!$HTTP_GET_VARS[z0])
{
    $onload = "";
}
?>

<body <?=$onload?>>
<h3>Player Character Inventory</h3>
(World Controller: <?=$row_rsSvr[name]?>)
<form name="form1" method="post" action="">
<p>Properties:
	<a href="pcharacter.php?i=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Character</a> |
	<a href="pcharstat.php?i=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Stat</a> |
	<!--a href="charinv.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>"-->Inventory<!--/a--> |

<?if (($row_rsSvr[version]=="1.2_OMI") or ($row_rsSvr[version]=="1.2")){?>
	<a href="charstash.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Stash</a> |
<?}?>
	<a href="powerlist.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Power</a> |
	<a href="skilllist.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Skill</a> |
	<a href="effectlist.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Effect</a> |
	<a href="stancelist.php?f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Stance</a> |
	<a href="questdata.php?i=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>">Quest</a>
</p>
<table border="0">
	<tr>
		<td>User Name</td>
		<td><input name="textfield3" type="text" value="<?=$row1[Username]?>" readonly="yes"></td>
	</tr>
	<tr>
		<td>Char Name </td>
		<td><input name="textfield5" type="text" value="<?=htmlspecialchars(U16btoU8str($row1[CharacterName]))?>" readonly="yes"></td>
	</tr>
	<tr>
		<td>Char ID</td>
		<td><input name="textfield4" type="text" value="<?=$row1[CharID]?>" readonly="yes"></td>
	</tr>
</table>
<table border="1" cellspacing=0>
	<tr>
		<!--td>index</td-->
		<td>Slot#</td>
		<td>Item</td>
		<td>Quantity</td>
		<td>Identified</td>
		<td>Durability</td>
		<td>Hardness</td>
		<td>Field1</td>
		<td>Field2</td>
		<td>Field3</td>
		<td>Field4</td>
		<td>Field5</td>
		<!--td>action</td-->
	</tr>
<?php
$js_chk = "<script>function valfrm(){with(document.form1){";
while($row=mysql_fetch_assoc($rs))
{
	$idx = $row[Indx];

	$js_chk .= "

if(isNaN(itemid_{$idx}.value) || itemid_$idx.value.length==0){alert('Please enter numeric data for item_id');itemid_{$idx}.focus();return 0}
if(isNaN(quantity_{$idx}.value) || quantity_$idx.value.length==0){alert('Please enter numeric data for quantity');quantity_{$idx}.focus();return 0}
if(itemid_{$idx}.value!='0' && (quantity_{$idx}.value=='0' || quantity_$idx.value.length==0)){alert('quantity must larger than 0');quantity_$idx.focus();return 0}
";
?>
	<tr onmouseover="this.className='hl'" onmouseout="this.className=''">
		<!--td><input name="textfield" type="text" value="<?=$idx?>" size="8"></td-->
		<td>
			<input name="slotnum_<?=$idx?>" type="text" id="slotnum_<?=$idx?>" value="<?=$row[SlotNum]?>" size="3" readonly="yes">
			<?=ucfirst(strtolower($slot_desc[$row[SlotNum]]))?>
		</td>
		<td>
			<input name="itemid_<?=$idx?>" type="text" id="itemid_<?=$idx?>" value="<?=$row[ItemID]?>" size="7" onchange="saveid(<?=$idx?>)">
<?
			$is_unique_item = (($row[ItemID] >> 16) > 100) || (($row[ItemID] >> 16) == 21);
			if($is_unique_item)
			{
				echo "<a href=\"uitem.php?i={$row[ItemID]}&wid={$wid}\" target=\"ui{$row[ItemID]}\">" . U16btoU8str(getstring($row[ItemID],'item')) . "</a>";
			}
			else
			{
				echo getstring($row[ItemID],'item');
			}
?>
			<input name="oitemid_<?=$idx?>" type="hidden" id="oitemid_<?=$idx?>" value="<?=$row[ItemID]?>">
		</td>
		<td><input name="quantity_<?=$idx?>" type="text" id="quantity_<?=$idx?>" value="<?=$row[Quantity]?>" size="4" onchange="saveid(<?=$idx?>)"></td>
		<td><input name="identified_<?=$idx?>" type="text" id="identified_<?=$idx?>" value="<?=$row[Identified]?>" size="4" onchange="saveid(<?=$idx?>)"></td>
		<td><input name="durability_<?=$idx?>" type="text" id="durability_<?=$idx?>" value="<?=$row[Durability]?>" size="4" onchange="saveid(<?=$idx?>)"></td>
		<td><input name="hardness_<?=$idx?>" type="text" id="hardness_<?=$idx?>" value="<?=$row[Hardness]?>" size="4" onchange="saveid(<?=$idx?>)"></td>
		<td><input name="field1_<?=$idx?>" type="text" id="field1_<?=$idx?>" value="<?=$row[Field1]?>" size="7" onchange="saveid(<?=$idx?>)"></td>
		<td><input name="field2_<?=$idx?>" type="text" id="field2_<?=$idx?>" value="<?=$row[Field2]?>" size="7" onchange="saveid(<?=$idx?>)"></td>
		<td><input name="field3_<?=$idx?>" type="text" id="field3_<?=$idx?>" value="<?=$row[Field3]?>" size="7" onchange="saveid(<?=$idx?>)"></td>
		<td><input name="field4_<?=$idx?>" type="text" id="field4_<?=$idx?>" value="<?=$row[Field4]?>" size="7" onchange="saveid(<?=$idx?>)"></td>
		<td><input name="field5_<?=$idx?>" type="text" id="field5_<?=$idx?>" value="<?=$row[Field5]?>" size="7" onchange="saveid(<?=$idx?>)">
			<input type="hidden" name="mark_<?=$idx?>" value="">
		</td>
		<!--td><input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'charinv.php?a=s&f=<?=$HTTP_GET_VARS[f]?>&i=<?=$idx?>&wid=<?=$wid?>')"></td-->
	</tr>
<?php
}
$js_chk .= "}return 1}</script>";
?>
</table>
<?
echo $js_chk;
?>
<input type="hidden" name="ids" value="">
<!--input type="button" onclick="alert(document.form1.ids.value)"-->
<input type="reset" value="Reset"> <input type="button" name="Button" value="Save" onClick="if(valfrm()){if(document.form1.ids.value==''){alert('no change');return 0}if(confirm('Overwrite?'))postform(document.form1,'charinv.php?a=s&f=<?=$HTTP_GET_VARS[f]?>&wid=<?=$wid?>')}">
</form>
</body>
</html>
