<?php
require("custom.php");
session_start();

if($HTTP_SESSION_VARS['id'])
{
	session_destroy();
	$msg='You have logged out';
}
elseif($HTTP_SERVER_VARS['REQUEST_METHOD']=='POST')
{
	if($HTTP_POST_VARS['id'] && $HTTP_POST_VARS['password'])
	{
		require_once('dbGmAdm.php');
		mysql_select_db($database_dbGmAdm, $dbGmAdm);
		$query_Recordset1 = "SELECT * FROM admin WHERE name=\"{$HTTP_POST_VARS[id]}\" AND password=\"{$HTTP_POST_VARS[password]}\"";
		$Recordset1 = mysql_query($query_Recordset1, $dbGmAdm) or die(mysql_error($dbGmAdm));
		$row=mysql_fetch_assoc($Recordset1);
		$totalRows_Recordset1 = mysql_num_rows($Recordset1);
		mysql_free_result($Recordset1);

		$as = get_accessible_server($row["id"], "as");
		if(count($as)==1) $HTTP_SESSION_VARS["as"] = $as[0][id];
		$wc = get_accessible_server($row["id"], "wc");
		if(count($wc)==1) $HTTP_SESSION_VARS["wid"] = $wc[0][id];

/*		$query_Recordset1 = "SELECT COUNT(*) FROM gm_server WHERE name=\"{$HTTP_POST_VARS[id]}\" AND password=\"{$HTTP_POST_VARS[password]}\"";
		$Recordset1 = mysql_query($query_Recordset1, $dbGmAdm) or die(mysql_error($dbGmAdm));
		$row=mysql_fetch_assoc($Recordset1);
		$totalRows_Recordset1 = mysql_num_rows($Recordset1);
*/

		if($totalRows_Recordset1==1)
		{
			$HTTP_SESSION_VARS['id'] = $HTTP_POST_VARS['id'];
			$HTTP_SESSION_VARS['userid'] = $row['id'];
			$HTTP_SESSION_VARS['permission'] = $row[permission];
			echo "<script>document.location.target='_top';document.location.href='main.php'</script>";
			exit();
		}
		else
		{
			$msg='Invalid login';
		}
	}
	elseif($HTTP_POST_VARS['email'])
	{
		require_once('dbGmAdm.php');
		mysql_select_db($database_dbGmAdm, $dbGmAdm);
		$query_Recordset1 = "SELECT * FROM admin WHERE email=\"{$HTTP_POST_VARS[email]}\"";
		$Recordset1 = mysql_query($query_Recordset1, $dbGmAdm) or die(mysql_error($dbGmAdm));
		$totalRows_Recordset1 = mysql_num_rows($Recordset1);
		$row_Recordset1 = mysql_fetch_row($Recordset1);
		mysql_free_result($Recordset1);

		if($totalRows_Recordset1==1)
		{
            //exec("ssh -o 'StrictHostKeyChecking=no' -i $rsa_file r3m0t3@211.24.134.98 /home/r3m0t3/email.sh kelvien_loke@phoenix-gamestudios.com Username={$row_Recordset1[name]} Password=$row_Recordset1[password]");
            //mail($row_Recordset1[email], "Game Admin AC", "ID={$row_Recordset1[name]};password={$row_Recordset1[password]}");
			$msg='ID and password are sent.';
		}
		else
		{
			$msg='Invalid E-Mail';
		}
	}
}
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<style>
	body {
		background-color: white;
	}
</style>
</head>
<body onload='<?=$msg?"alert(\"$msg\");":""?>document.form1.id.focus()'>
<table width=100% height=100%>
<tr valign=center>
<td>
<center>
<img src="<?=MAIN_LOGO?>"><h3 style="margin: 0"><br><?=MAIN_NAME?><br>Game Administration Tools</h3>
<br><br>
<table style="border: 1px solid black; text-align: center; background-color: #ffd3A5" width=360 height=200>
<tr valign=center>
	<td align=center>
	<form name="form1" method="post" action="" style="margin:0">
	  <table width=220 border=0>
	    <tr>
	      <td width=80><b>Username</b></td>
	      <td><input name="id" type="text" id="id"></td>
	    </tr>
	    <tr>
	      <td><b>Password</b></td>
	      <td><input name="password" type="password" id="password"></td>
	    </tr>
	    <tr>
		    <td>&nbsp;</td>
		    <td>  <input type="submit" name="Submit" value="Login"></td>
		</tr>
	  </table>
	</form>
	</td>
</tr>
</table>
</center>
</td>
</tr>
</table>
</body>
</html>
