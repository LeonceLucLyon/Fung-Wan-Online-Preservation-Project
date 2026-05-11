<?php
require('auth.php');
//if($HTTP_SESSION_VARS['permission']!=2)die("only game admin allowed access");

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

if(!has_perm($HTTP_SESSION_VARS['userid'], $HTTP_SESSION_VARS['as'], "gmdata", ""))
{
	$HTTP_SESSION_VARS['as'] = '';
	die("only game admin allowed edit");
}
elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], $HTTP_SESSION_VARS['as'], "gmdata", "w"))
{
	die("Access denied. Read-Only.");
}


$query_rsAs = "SELECT * FROM gm_server WHERE type='as';";
$rsAs = mysql_query($query_rsAs, $dbGmAdm) or die(mysql_error());
$htmlAs="<select name=as_id onChange=\"postform(document.form1,'cleanlogin.php?a=as')\"><option value=''></option>";
while($row=mysql_fetch_assoc($rsAs))
{
	if($HTTP_SESSION_VARS['as']==$row[id])
	{
		$as_ip=$row[ip];
		$as_dbuser=$row[dbuser];
		$as_dbpasswd=$row[dbpasswd];
		$selected="SELECTED";
		$as_db=$row[db];
	}
	else
	{
		$selected="";
	}
	$htmlAs.="<option value='{$row[id]}' $selected>{$row[name]}</option>";
}
$htmlAs.="</select>";
mysql_free_result($rsAs);



$query_rs=NULL;
switch($HTTP_GET_VARS['a'])
{
/*
	case 'sv':
		$query_rs[]="UPDATE subscription SET SvcLevel='{$HTTP_POST_VARS[svclvl_all]}';";
		break;
	case 'svd':
		$rs = mysql_query("UPDATE gm_server SET defsvclvl='{$HTTP_POST_VARS[svclvl_def]}' WHERE id='{$HTTP_SESSION_VARS['as']}';", $dbGmAdm);
		break;
*/
	case 'as':
		if($HTTP_POST_VARS[as_id]!='')$HTTP_SESSION_VARS['as']=$HTTP_POST_VARS[as_id];
		break;
/*
	case 'a':
		$query_rs[] = "INSERT INTO subscription(Username,Password, SvcLevel)
			VALUES('{$HTTP_POST_VARS[account_0]}','{$HTTP_POST_VARS[passwd_0]}','{$HTTP_POST_VARS[svclvl]}')";
		break;
*/
	case 'f':
		$cond_account = $HTTP_POST_VARS[account_0]!=""? " AND Username LIKE \"$HTTP_POST_VARS[account_0]\"":"";
		$cond_svclvl = $HTTP_POST_VARS[svclvl]!=""? " AND SvcLevel = \"$HTTP_POST_VARS[svclvl]\"":"";
//		$query_rs[] = "SELECT * FROM subscription WHERE 1 $cond_account $cond_password $cond_svclvl";
		$query_rs[] = "SELECT * FROM authorized WHERE SvcLevel=2 $cond_account";
		break;
/*
	case 's':
		if($HTTP_GET_VARS[i]!="")
		{
			$ids=array($HTTP_GET_VARS[i]);
		}
		else
		{
			$ids=$HTTP_POST_VARS[affected];
		}
		if(is_array($ids))
			foreach ($ids as $idx){
				$passwd = $HTTP_POST_VARS["passwd_{$idx}"];
				$svclvl = $HTTP_POST_VARS["svclvl_{$idx}"];
				$account = $HTTP_POST_VARS["account_{$idx}"];
				$svclvlo = $HTTP_POST_VARS["svclvlo_{$idx}"];
				if($svclvlo==$svclvl)
				{
					$sql="UPDATE subscription SET Password='$passwd', SvcLevel='$svclvl' WHERE Username='$account';";
				}
				else
				{
					$sql="UPDATE subscription SET Password='$passwd', SvcLevel='$svclvl', SvcLevelChgDt=now() WHERE Username='$account';";
				}
				$query_rs[] = $sql;
			}
		break;
*/
	case 'd':
		if($HTTP_GET_VARS[i]!="")
		{
			$ids=array($HTTP_GET_VARS[i]);
		}
		else
		{
			$ids=$HTTP_POST_VARS[affected];
		}
		if(is_array($ids))
			foreach ($ids as $idx){
				$account = $HTTP_POST_VARS["account_{$idx}"];
/*
	SELECT ip, db, dbuser, dbpasswd FROM gm_server WHERE type='wc' AND settings='{$HTTP_SESSION_VARS['as']}';
	each wc
	{
		mysql_connect();
		SELECT CharID FROM pcharacter WHERE username='{$account}';
		each CharID
		{
			$charid_hash=$charid / 10;
			DELECT FROM * WHERE CharID='$charid';
				pcharacter, pcharstats, charinv_{$charid_hash}, effectlist_{$charid_hash}, powerlist_{$charid_hash},
				skilllist_{$charid_hash}, stancelist_{$charid_hash}, partylist_{$charid_hash}, intdata, friend_{$charid_hash},
				community, chatbarlist_{$charid_hash}, charquest, brotherlist_{$charid_hash}, barclanlist,

			UPDATE * SET CharID=0 WHERE CharID='$charid';
				uniqueitem
		}



	}
*/
				$query_rs[] = "DELETE FROM authorized WHERE Username='{$account}' AND SvcLevel=2;";

			}
		break;
}


$rs_def = mysql_query("SELECT defsvclvl FROM gm_server WHERE id='{$HTTP_SESSION_VARS['as']}'", $dbGmAdm);
if(mysql_num_rows($rs_def) == 1)
{
	list($svclvl_def) = mysql_fetch_row($rs_def);
}
else
{
	$svclvl_def = "";
}

if($query_rs!=NULL && $HTTP_SESSION_VARS['as'])
{
	$rsWc = mysql_query("SELECT * FROM gm_server WHERE authsys='{$HTTP_SESSION_VARS['as']}';", $dbGmAdm) or die(mysql_error());
	$arWc = array();
	while($row = mysql_fetch_assoc($rsWc))
	{
		array_push($arWc, $row);
	}
	mysql_free_result($rsWc);

	$dbAs = mysql_pconnect($as_ip,$as_dbuser,$as_dbpasswd) or die(mysql_error());
	mysql_select_db($as_db, $dbAs);
	foreach ($query_rs as $sql)
	{
		$rs = mysql_query($sql, $dbAs) or die(mysql_error());
		//die($query_rs);
	}
}

if(!($HTTP_GET_VARS[a]=="" || $HTTP_GET_VARS[a]=="f"))
{
	header("Location: cleanlogin.php");
	exit();
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
function doCheckAll(nm,v){
	with (document.form1)
		for (var i=0; i < elements.length; i++)
			if (elements[i].type == 'checkbox' && elements[i].name == nm)elements[i].checked = v
}
function doSelectAll(nm,v){
	alert(v)
	with(document.form1)
		for (var i=0; i < elements.length; i++)
			if (elements[i].type == 'select-one' && elements[i].name.indexOf(nm)>=0)elements[i].selectedIndex = v;
}
function countTicked(nm){
	var n=0
	with(document.form1)
		for(var i=0; i < elements.length; i++)
			if (elements[i].type == 'checkbox' && elements[i].name == nm && elements[i].checked)n++;
	return n
}

//-->
</script>
</head>

<body>
<form name="form1" method="post" action="">
  <p>Authentication System: <?=$htmlAs?>
  </p>
<?php
if($HTTP_SESSION_VARS['as']!='')
{
?>
  <table border="1">
    <tr>
      <td>Account</td>
      <td>Login Time</td>
	<td> WorldServer </td>
	<td> SceneID </td>
      <td>Action</td>
    </tr>
    <tr bordercolor="#CCCCCC" bgcolor="#FFFF66">
	<td> <input name="account_0" type="text" id="account_0" size="20" maxlength="20"></td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td>
        	<input type="button" name="Button" value="Find" onClick="postform(document.form1,'cleanlogin.php?a=f')"> </td>
    </tr>
<?php
$idx=0;
if($HTTP_GET_VARS[a]=='f')
while($row=mysql_fetch_assoc($rs))
{
	$idx++;

	$sceneid = '';
	$servername = '';

	foreach($arWc as $wc)
	{
		$dbWc = mysql_connect($wc[ip], $wc[dbuser], $wc[dbpasswd]);
		$rsAuth = mysql_db_query($wc[db], "SELECT SceneID from authenticated WHERE Username='{$row[Username]}';", $dbWc) or die(mysql_error());
		$cnt = mysql_num_rows($rsAuth);
		if($cnt >0)
		{
			list($sceneid) = mysql_fetch_row($rsAuth);
			$servername = $wc[name];
			break;
		}
	}

?>
    <tr>
	<td> <input name="account_<?=$idx?>" type="text" size="20" maxlength="20" value="<?=$row[Username]?>" readonly="yes"> </td>
	<td> <?=$row[LoginTime]?> </td>
	<td> <?=$servername?> </td>
	<td> <?=$sceneid?> </td>
	<td>
	        <input name="delete_" type="button" id="delete_" value="Delete" onClick="if(confirm('Delete?'))postform(document.form1,'cleanlogin.php?a=d&i=<?=$idx?>')">
        	<!--a href="gmchar.php?i=">character</a-->
		<input type="checkbox" name="affected[]" value="<?=$idx?>">
      </td>
    </tr>
<?php
}
//if($rs)mysql_free_result($rs);
?>
  </table>
      <br><input type="checkbox" onclick="doCheckAll('affected[]',this.checked)">Check/Uncheck All

        <input name="delete_" type="button" id="delete_" value="Delete" onClick="if(confirm('Delete?'))postform(document.form1,'cleanlogin.php?a=d')">

<!--
	Set all service level to
          <select onchange="doSelectAll('svclvl_',this.options(selectedIndex).value);doCheckAll('affected[]',1)">
          <option value=""></option>
          <option value="0">Normal</option>
          <option value="1">Disabled</option>
          <option value="2">Suspended</option>
          <option value="3">Test</option>
        </select>
      <br><input type="checkbox" onclick="doCheckAll('affected[]',this.checked)">Check/Uncheck All
  <p>
    <input type="reset" name="Reset" value="Reset" onclick="return(confirm('Rest and lost changes?'))">
    <input type="button" value="Save Ticked(s)" onClick="if(countTicked('affected[]')==0){alert('No ticked');return 0}if(confirm('Overwrite ticked(s)?'))postform(document.form1,'cleanlogin.php?a=s')">
    <input type="button" value="Delete Ticked(s)" onClick="if(countTicked('affected[]')==0){alert('No ticked');return 0}if(confirm('Delete ticked(s)?'))postform(document.form1,'cleanlogin.php?a=d')">
<hr>
	Set service levels of all accounts to
          <select name="svclvl_all">
          <option value="1">Normal</option>
          <option value="0">Disabled</option>
          <option value="-1">Suspended</option>
          <option value="2">Test</option>
        </select> <input type="button" value="Apply" onclick="if(document.form1.svclvl_all.value==''){return alert('Please select valid service level')};if(confirm('update service level for all accounts?'))postform(document.form1,'cleanlogin.php?a=sv')">

<hr>
	default service:
          <select name="svclvl_def">
          <option value="1" <?=($svclvl_def=="1")?"SELECTED":""?> >Normal</option>
          <option value="0" <?=($svclvl_def=="0")?"SELECTED":""?> >Disabled</option>
          <option value="-1" <?=($svclvl_def=="-1")?"SELECTED":""?> >Suspended</option>
          <option value="2" <?=($svclvl_def=="2")?"SELECTED":""?> >Test</option>
        </select> <input type="button" value="Apply" onclick="if(document.form1.svclvl_def.value==''){return alert('Please select valid service level')};if(confirm('Update default service level?'))postform(document.form1,'cleanlogin.php?a=svd')">
  </p>
-->

<?
}
?>
</form>
</body>
</html>
