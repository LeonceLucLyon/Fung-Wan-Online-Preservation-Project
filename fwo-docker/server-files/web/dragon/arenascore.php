<?php
require("auth.php");
$rpp = 20;

//if($HTTP_SESSION_VARS['permission']!=2 && ($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s"))die("only game admin allowed edit");

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
$htmlWc="<select name=wid onChange=\"postform(document.form1,'arenascore.php?a=wc')\"><option value=''></option>";
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

	if($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST' || $_REQUEST['ci']!='' || $_REQUEST['si']!='')
	{
		if($HTTP_GET_VARS[a]=="s")
		{
			if($readonly_gmdata) die("Write-access is denied.");

			$indxes = $HTTP_POST_VARS["affected"];
			$update_sqls = array();
			$log_sqls = array();
			foreach($indxes as $indx)
			{

				$CharID=$HTTP_POST_VARS["CharID_{$indx}"];
				$Score=$HTTP_POST_VARS["Score_{$indx}"];

				//$Time=$HTTP_POST_VARS["Time_{$indx}"];
				//$SetID=$HTTP_POST_VARS["SetID_{$indx}"];
				//$NPCFlag=$HTTP_POST_VARS["NPCFlag_{$indx}"];

				$query_rs = "UPDATE arenascore SET
					CharID='{$CharID}',
					Score='{$Score}'
					WHERE Indx='{$indx}'";

				//	Time='{$Time}',
				//	SetID='{$SetID}',
				//	NPCFlag='{$NPCFlag}',

				$update_sqls[] = $query_rs;
				$log_sqls[] = "SELECT * FROM arenascore WHERE Indx='$indx'";
			}

			reset($log_sqls);
			foreach($update_sqls as $sql)
			{
				$log_sql = current($log_sqls);
				next($log_sqls);
				$befores = get_str_rs($dbWc,  $log_sql);
				$rs = mysql_query($sql, $dbWc) or die(mysql_error($dbWc));
				$after = get_str_rs($dbWc,  $log_sql);
				
			}

			header ("Location: arenascore.php?i={$HTTP_GET_VARS[i]}&wid=$wid&si=$_REQUEST[si]&ci=$_REQUEST[ci]");
			exit;
		}

		$sql_cond = "";
		if($_REQUEST[si])
		{
			$sql_cond .= "AND SceneID='{$_REQUEST[si]}'";
		}
		if($_REQUEST[ci])
		{
			$sql_cond .= "AND CharID='{$_REQUEST[ci]}'";
		}
	}

//	$paging =

	$query_rs = "SELECT count(*) FROM arenascore WHERE 1 $sql_cond"; //WHERE $sql_cond
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error($dbWc));
	list($unique_item_count) = mysql_fetch_row($rs);
	mysql_free_result($rs);

	$pg = $_REQUEST['pg'];
	$last_page = ceil($unique_item_count / $rpp);
	if($pg > $last_page) $pg = $last_page;
	if($pg < 1) $pg = 1;
	$offset = (($pg - 1) * $rpp);

	$query_rs = "SELECT * FROM arenascore WHERE 1 $sql_cond ORDER BY SceneID, Score DESC, Indx LIMIT $offset, $rpp"; //WHERE $sql_cond
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
<h3>Arena Score</h3>
(World Controller: <?=$htmlWc?>)
<br><br>
<?
if($wid)
{
	eval("\$searchby_checked{$HTTP_POST_VARS['searchby']} = 'CHECKED';");
?>
Search Criteria
<table border=1 cellspacing=0>
	<tr>
		<td width=80>Scene ID</td>
		<td><input name=si value="<?=$_REQUEST[si]?>" size=8></td>
	</tr>
	<tr>
		<td width=80>Char ID</td>
		<td><input name=ci size=8 value="<?=$_REQUEST[ci]?>" ></td>
	</tr>
</table>
<input type=button value="Submit" onclick="postform(document.form1,'arenascore.php?a=f')">
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
      <td>Scene ID</td>
      <td>Char ID</td>
      <td>Score</td>
      <?=$readonly_remark_begin?>
      <td>Changed <div style="display:none"><input type="checkbox" name="affected[]"></div></td>
      <?=$readonly_remark_end?>
    </tr>
		<?
		$idx = 0;
		while($row=mysql_fetch_assoc($rs))
		{
			$offset++;
			$idx++;
			$i=$row[Indx];
		?>
    <tr>
	<td><?=$offset?></td>
      <td><?=$row[SceneID]?> (<?=$scene_name[$row[SceneID]]?>)</td>
      <td> <input name="CharID_<?=$i?>" type="text" size=12 value="<?=$row[CharID]?>" maxlength=10 onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1" <?=$readonly_disabled?>> <a href="javascript:var a=window.open('pcharacter.php?wid=<?=$wid?>&i=' + document.form1.CharID_<?=$i?>.value)"><img src="images/link.gif" border=0></a></td>
      <td> <input name="Score_<?=$i?>" type="text" size=4 value="<?=$row[Score]?>" onchange="document.form1.elements('affected[]').item(<?=$idx?>).checked=1" <?=$readonly_disabled?>></td>
      <?=$readonly_remark_begin?>
      <td><input type="checkbox" name="affected[]" value="<?=$i?>">
      <?=$readonly_remark_end?>
    </tr>
		<?
		}
		?>
  </table>
		<?
		echo $html_page;
		if(!$readonly_gmdata)
		{
		?>
			<input type="hidden" name="ids" value="">
			<br><input type="reset" name="Reset" value="Reset" onclick="return(confirm('undo all changes?'))">
			<input type="button" name="Button" value="Save" onClick="var save=false;with(document.form1){for(var n=0;n<elements('affected[]').length;n++){save=elements('affected[]').item(n).checked;if(save)break}if(!save){alert('no change');return 0} if(confirm('Overwrite?'))postform(document.form1,'arenascore.php?a=s&i=<?=$HTTP_GET_VARS[i]?>')}">
		<?
		}
	}
}
}
?>
</form>
</body>
</html>