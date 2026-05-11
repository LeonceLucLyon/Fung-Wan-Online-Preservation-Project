<?php
require('auth.php');
$rpp  = 50;

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

$wid=$_REQUEST[wid];
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
	die("Access denied.");
}

$view = $_REQUEST[view];

$display1 = $display2 = $display4 = $display5 = "none";

switch($HTTP_GET_VARS['a'])
{
	case 'wc':
		if($_REQUEST[wid]!='')$HTTP_SESSION_VARS['wc']=$_REQUEST[wid];
		header("Location: itemsearch.php");
		exit;
		break;
	case 'f':

			//default

			//user request
			if($_REQUEST[rpp] >0) $rpp = $_REQUEST[rpp];
			$itemid = $_REQUEST[itemid];
			$field1 = $_REQUEST[field1];
			$field2 = $_REQUEST[field2];
			$field3 = $_REQUEST[field3];
			$field4 = $_REQUEST[field4];
			$field5 = $_REQUEST[field5];
			$search_all_fields = $_REQUEST[search_all_fields];

			if ($search_all_fields == "on" && (strlen($field1)>0 || strlen($field2)>0 || strlen($field3)>0 || strlen($field4)>0 || strlen($field5)>0))
            {
                die("Don't tick \"Search Field 1 to 5 also\" if you plans to search specific item in either Field 1 - 5. Think Logically!");
            }

			if($wid)
			{
				$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
				$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("invalid worldcontroller");
				mysql_free_result($rsSvr);
				$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]);
				if(!$dbWc)
				{
					$HTTP_SESSION_VARS['wid'] = "";
					echo "
						<script>
						function reload(){location.href='itemsearch.php'}
						setTimeout(reload, 3000)
						</script>
						<p><font color=red>Page will be redirected in 3 seconds.</p>
					";
					die(mysql_error());
				}
				mysql_select_db($row_rsSvr[db], $dbWc);

    			if($itemid > 0 && $itemid < 10000000000)
				{
                        if (strlen($field1) != 0)
                            $cond_field .= " AND Field1='$field1' ";

                        if (strlen($field2) != 0)
                            $cond_field .= " AND Field2='$field2' ";

                        if (strlen($field3) != 0)
                            $cond_field .= " AND Field3='$field3' ";

                        if (strlen($field4) != 0)
                            $cond_field .= " AND Field4='$field4' ";

                        if (strlen($field5) != 0)
                            $cond_field .= " AND Field5='$field5' ";

                        if ($search_all_fields == "on")
                        {
                            $is_checked_search_all_fields = "CHECKED";
                            $cond_search_all_fields = " AND (ItemID='$itemid' OR Field1='$itemid' OR Field2='$itemid' OR Field3='$itemid' OR Field4='$itemid' OR Field5='$itemid') ";
                        }
                        else
                        {
                            $cond_search_all_fields = " AND ItemID='$itemid' ";
                        }

					
					$cond_inv = " Quantity>0 AND CharID>0 $cond_field $cond_search_all_fields ";
					$group_inv = "GROUP BY CharID";
				}
				elseif ((strlen($field1)>0 || strlen($field2)>0 || strlen($field3)>0 || strlen($field4)>0 || strlen($field5)>0) && strlen($itemid)==0)
				{
                        if (strlen($field1) != 0)
                            $cond_field .= " AND Field1='$field1' ";

                        if (strlen($field2) != 0)
                            $cond_field .= " AND Field2='$field2' ";

                        if (strlen($field3) != 0)
                            $cond_field .= " AND Field3='$field3' ";

                        if (strlen($field4) != 0)
                            $cond_field .= " AND Field4='$field4' ";

                        if (strlen($field5) != 0)
                            $cond_field .= " AND Field5='$field5' ";

					$cond_inv = " Quantity>0 AND CharID>0 $cond_field ";
					$group_inv = "GROUP BY CharID";
				}

                mysql_query("Drop TABLE item_all", $dbWc);
                mysql_query("CREATE TABLE `item_all` (`CharID` INT (10) UNSIGNED DEFAULT '0' NOT NULL, INDEX(`CharID`))");
                
                for ($i=0;$i<10;$i++)
                {
                    $query_rs = "SELECT DISTINCT(CharID) FROM charinv_$i WHERE $cond_inv ";
    				$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
    				while ($still = mysql_fetch_assoc($rs))
    				{
    				    $rs_insert = mysql_query("INSERT INTO item_all (CharID) VALUES ($still[CharID])");
                    }
    				mysql_free_result($rs);
                }
                
                for ($j=0;$j<10;$j++)
                {
                    $query_rs = "SELECT DISTINCT(CharID) FROM stash_$j WHERE $cond_inv ";
    				$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
    				while ($still2= mysql_fetch_assoc($rs))
    				{
    				    $rs_insert = mysql_query("INSERT INTO item_all (CharID) VALUES ($still2[CharID])");
                    }
    				mysql_free_result($rs);
                }

                
                    $rs = mysql_query("SELECT COUNT(DISTINCT(CharID)) FROM item_all", $dbWc) or die(mysql_error($dbWc));
    				list($total_found) = mysql_fetch_row($rs);
    				mysql_free_result($rs);

                
				$pg = $HTTP_GET_VARS[pg];
				$lp = ceil($total_found / $rpp);
				if($pg < 1){
					$pg = 1;
				}elseif($pg > $lp){
					$pg = $lp;
				}
				$offset = ($pg - 1) * $rpp;

				$query_rs = "SELECT CharID FROM item_all GROUP BY CharID ORDER BY CharID LIMIT $offset, $rpp";
//echo($query_rs );
				$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));

				$htmllist = "<tr><th>#</th><th>Character ID</th><th>Character Name</th></tr>";
						
				$idx = $offset;
				while($row = mysql_fetch_assoc($rs))
				{
                    $get_name = mysql_query("SELECT CharacterName FROM pcharacter WHERE CharID='$row[CharID]'", $dbWc) or die (mysql_error($dbWc));
                    $ambil = mysql_fetch_assoc($get_name);
                    $charnm = U16btoU8str($ambil[CharacterName]);

                    $idx++;

					$htmllist .= "<tr><td>$idx</td><td>$row[CharID]</td><td><a href=\"pcharacter.php?i=$row[CharID]&wid=$wid\" target=\"p$row[CharID]\">$charnm<img src='images/blank.bmp' border=0></a></td></tr>";
				}

				function mklink($n){
					global $pg;
					$tag = $n == $pg?"<b>$n</b>":"$n";
					return "<a href=\"javascript:document.form1.pg.value='{$n}';document.form1.a.value='f';document.form1.submit()\">$tag</a>";
				}

				if($lp > 0)
				{
					$s1 = -10;
					$s2 = 10;

					$html_page = "Found $total_found character(s).<br>Page: ";

					if($pg + $s1 > 1) $html_page .= mklink(1) . "... ";
					for($n = $s1; $n < $s2; $n++)
					{
						$pp = $pg + $n;
						if($pp > $lp) break;
						if($pp > 0 )
							$html_page .= mklink($pp) . " ";
					}
					if($pg + $s2 < $lp) $html_page .= " ..." . mklink($lp);
				}
			}

			break;

}

$arWc = get_accessible_server($HTTP_SESSION_VARS['userid'], 'wc');

$htmlWc="<select name=wid onChange=\"postform(document.form1,'itemsearch.php?a=wc')\"><option value=''></option>";
foreach($arWc as $row)
{
	$selected=($wid==$row[id])?"SELECTED":"";
	$htmlWc.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlWc.="</select>";
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
<form name="form1" method="GET">
<h3>Advanced Item Search</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
<?
if($wid)
{
?>
<table border=1 cellspacing=0>
	<tr>
		<td valign="top">Has Item (ID)</td>
		<td><input type="text" name="itemid" value="<?=$itemid?>" maxlength="8" size="10">&nbsp;<input type="checkbox" name="search_all_fields" <?=$is_checked_search_all_fields?>>Search in Field 1 to 5 as well</td>
	</tr>
	<tr>
        <td valign="top">Item Components</td>
        <td>Field 1 <input type="text" name="field1" value="<?=$field1?>" maxlength="8" size="10"><br>
            Field 2 <input type="text" name="field2" value="<?=$field2?>" maxlength="8" size="10"><br>
            Field 3 <input type="text" name="field3" value="<?=$field3?>" maxlength="8" size="10"><br>
            Field 4 <input type="text" name="field4" value="<?=$field4?>" maxlength="8" size="10"><br>
            Field 5 <input type="text" name="field5" value="<?=$field5?>" maxlength="8" size="10"><br><br>
            <font color="red">* This uses "AND" function.</font>
        </td>
	</tr>
	<tr>
		<td>Entry Per Page</td>
		<td><input type="text" name="rpp" value="<?=$rpp?>" maxlength="3" size="3">
		</td>
	</tr>
</table>
<input type="submit" value="Search" onclick="document.form1.a.value='f'">
<input type="hidden" name="pg">
<input type="hidden" name="a">
<?
} //if($wid)
?>
</form>
<?=$html_page?>
<table border="1" cellspacing=0>
<?=$htmllist?>
</table>
</body>
</html>
