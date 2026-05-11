<?php
require("auth.php");
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
elseif(($HTTP_GET_VARS[a]=="r" || $HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $wid, "gmdata", "w"))
{
	die("Access denied. Read-Only.");
}

$query_rsWc = "SELECT * FROM gm_server WHERE type='wc';";
$rsWc = mysql_query($query_rsWc, $dbGmAdm) or die(mysql_error());
$htmlWc="<select name=wid onChange=\"postform(document.form1,'scratchdata.php?a=wc')\"><option value=''></option>";
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
//	$scene_id = $HTTP_GET_VARS[i];
	$scene_id = $HTTP_POST_VARS['sceneid'];

	$rsSvr = mysql_query("SELECT * FROM gm_server WHERE id='{$wid}'", $dbGmAdm) or die(mysql_error());
	$row_rsSvr=mysql_fetch_assoc($rsSvr) or die("Invalid WorldController");
	mysql_free_result($rsSvr);
	$dbWc = mysql_pconnect($row_rsSvr[ip],$row_rsSvr[dbuser],$row_rsSvr[dbpasswd]) or die(mysql_error());
	mysql_select_db($row_rsSvr[db], $dbWc);

	$query_rs = "SELECT ScratchData FROM scene WHERE SceneID='{$scene_id}'";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	$row=mysql_fetch_assoc($rs);
	mysql_free_result($rs);

$pos=(float)$HTTP_POST_VARS[scratchno];
$pos++;
$data = $row[ScratchData];
$len=strlen($data) / 4;
$blank = ($pos > $len)?	str_repeat("\0", ($pos - $len) *4 ) : "";
$data .= $blank;
$len=strlen($data) / 4;

	if($HTTP_GET_VARS[a]=='s')
	{

		$new=(float)$HTTP_POST_VARS[scratchval];
		$head=($pos > 1)? substr($data,0,($pos-1) * 4):"";
		$tail=($len > $pos)? substr($data, ($pos * 4)):"";
		$data = $head . pack("V", $new) . $tail;
		$data_in_hex="0x";
		for($n=0;$n < ($len * 4); $n++)
		{
			$data_in_hex.=str_pad(dechex(ord($data[$n])),2,'0',STR_PAD_LEFT);
		}

		$query_rs = "UPDATE scene SET
			ScratchData={$data_in_hex}
			WHERE SceneID='{$scene_id}'";
/*
		$rs_logon = mysql_query("SELECT * FROM authenticated WHERE CharID='{$HTTP_GET_VARS[i]}'", $dbWc) or die(mysql_error());
		$is_logon=mysql_num_rows($rs_logon);
		mysql_free_result($rs_logon);
		if($is_logon && $HTTP_GET_VARS[force]!=1)
		{
			echo "<form name=form1 action=\"{$HTTP_SERVER_VARS[REQUEST_URI]}&force=1\" method='Post'>";
			echo generate_form('',$HTTP_POST_VARS);
			echo "<input type=button value='Force Save' onclick='document.form1.submit()'></form>";
			//post_form('document.form1',$HTTP_SERVER_VARS[REQUEST_URI]."&force=1");
			die("game character is being used, write access deny");
		}
*/
		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		echo generate_form('form1',$HTTP_POST_VARS);
		post_form('document.form1',"scratchdata.php?i={$HTTP_GET_VARS[i]}");
		exit();
//		header("Location: scratchdata.php?i={$HTTP_GET_VARS[i]}");
	}
	elseif($HTTP_GET_VARS['a']=='r')
	{
		$data_in_hex=str_repeat("0", 400);
		$query_rs = "UPDATE scene SET
			ScratchData=0x{$data_in_hex}
			WHERE SceneID='{$scene_id}'";

		$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
		echo generate_form('form1',$HTTP_POST_VARS);
		post_form('document.form1',"scratchdata.php?i={$HTTP_GET_VARS[i]}");
		exit();
	}

	$query_rs = "SELECT SceneID FROM scene";
	$rs = mysql_query($query_rs, $dbWc) or die(mysql_error());
	$html_scene_select = "<select name='sceneid'><option value=''>--Select 1--</option>";
	while($row=mysql_fetch_row($rs))
	{
		$html_scene_select .= "<option value='{$row[0]}'";
		if($row[0]==$scene_id)
		{
			$html_scene_select .= " SELECTED";
		}
		$html_scene_select .= ">($row[0]){$scene_name[$row[0]]}</option>";
	}
	$html_scene_select .= "</select>";
	mysql_free_result($rs);




$ch = substr($data, ($pos - 1) * 4, 4);
$chval=unpack('V*', $ch);
$chval=$chval[1];




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
  <table border="0">
    <!--tr>
      <td>Authentication System</td>
      <td><!--select name="authsys_id" id="authsys_id" onChange="MM_jumpMenu('parent',this,0)">
          <option selected>unnamed1</option>
        </select--><?=$htmlAs?></td>
    </tr-->
    <tr>
      <td>World Controller</td>
      <td><?=$htmlWc?></td>
    </tr>
  </table>
  <br>
<?
if($wid)
{
?>
  <table border="1">
    <tr>
      <td>Scene</td>
      <td>
		<?=$html_scene_select?>

	    <input type="button" name="Button" value="Set all 0" onClick="if(confirm('Overwrite?'))postform(document.form1,'scratchdata.php?a=r&i=<?=$HTTP_GET_VARS[i]?>')">

	</td>
    </tr>
    <tr>
      <td>Scratch No</td>
      <td><input name="scratchno" type="text" value="<?=$HTTP_POST_VARS[scratchno]?>"> <input type=button value=Search onclick="postform(document.form1,'scratchdata.php?a=f&i=<?=$HTTP_GET_VARS[i]?>')"></td>
    </tr>
    <tr>
      <td>Scratch Data</td>
      <td><input name="scratchval" type="text" value="<?=$chval?>">
    <input type="reset" name="Reset" value="Reset">
    <input type="button" name="Button" value="Save" onClick="if(confirm('Overwrite?'))postform(document.form1,'scratchdata.php?a=s&i=<?=$HTTP_GET_VARS[i]?>')">
      </td>
    </tr>
  </table>
  <p>
  </p>
</form>
<table border=1>
<?
$m=0;
for($n=0;$n<$len;$n++)
{
	$v=unpack("V*", substr($data, $n * 4, 4));
	$v=$v[1];
	echo (($n%10)==0)? "<tr>":"";
	echo "<td bgcolor=eeeeee>".($n)."</td><td".(($v)?" bgcolor=yellow":"").">$v</td>";
}
?>
</table>
<?
}
?>
</body>
</html>
