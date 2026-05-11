<?php
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
			mail($row_Recordset1[email], "FWO Game Admin AC", "ID={$row_Recordset1[name]};password={$row_Recordset1[password]}");
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
<title>FWO Game Administration Tool</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
</head>

<body onload='<?=$msg?"alert(\"$msg\");":""?>document.form1.id.focus()'>
<form name="form1" method="post" action="">
<h3>FWO Game Admin Login</h3>
  <table border="0">
    <tr>
      <td>ID</td>
      <td><input name="id" type="text" id="id"></td>
    </tr>
    <tr>
      <td>Password</td>
      <td><input name="password" type="password" id="password"></td>
    </tr>
  </table>
  <input type="submit" name="Submit" value="Login">
</form>
<hr>
<form name="form2" method="post" action="">
<h3>Forgot your account?</h3>
Enter the email address, which you registered for the account, and your ID and password will be sent to it.
  <table border="0">
    <tr>
      <td>E-Mail</td>
      <td><input name="email" type="text" id="email"></td>
    </tr>
  </table>
  <input type="submit" name="Submit2" value="Recover Login">
</form>
</body>
</html>
