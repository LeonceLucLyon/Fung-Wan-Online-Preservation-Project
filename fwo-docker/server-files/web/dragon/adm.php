<?php
require('auth.php');
//if($HTTP_SESSION_VARS['permission']!="1")die("only sys admin allowed access");
require_once('dbGmAdm.php');

if(!has_perm($HTTP_SESSION_VARS['userid'], "*", "user", ""))
{
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], "*", "user", "w"))
{
	die("Access denied. Read-Only.");
}
mysql_select_db($database_dbGmAdm, $dbGmAdm);

switch($HTTP_GET_VARS['a'])
{
	case 'a':
		if(strlen($HTTP_POST_VARS[email_0])>0)
			$email = "'{$HTTP_POST_VARS[email_0]}'";
		else
			$email = "NULL";
		$query_rs = "INSERT INTO admin(name,email,password) VALUES('{$HTTP_POST_VARS[name_0]}', $email,'{$HTTP_POST_VARS[password_0]}')";
		$rs = mysql_query($query_rs, $dbGmAdm) or die(mysql_error($dbGmAdm));

		$after = get_str_rs($dbGmAdm, "SELECT * FROM admin WHERE id=" . mysql_insert_id($dbGmAdm));

		
		break;
	case 'f':
		$cond_name = $HTTP_POST_VARS[name_0]? "AND name like \"{$HTTP_POST_VARS[name_0]}\"":"";
		$cond_email = $HTTP_POST_VARS[email_0]? "AND email like \"{$HTTP_POST_VARS[email_0]}\"":"";
		$query_rs = "SELECT * FROM admin WHERE 1 $cond_name $cond_email";
		$rs = mysql_query($query_rs, $dbGmAdm) or die(mysql_error());
		break;
	case 's':
		$befores = get_str_rs($dbGmAdm, "SELECT * FROM admin WHERE id='{$HTTP_GET_VARS[i]}';");

		$email = trim($HTTP_POST_VARS["email_{$HTTP_GET_VARS[i]}"]);
		if(strlen($email)==0)
			$email = "NULL";
		else
			$email = "'$email'";
		$password = $HTTP_POST_VARS["password_{$HTTP_GET_VARS[i]}"];
		$query_rs = "UPDATE admin SET email=$email, password='$password' WHERE id='{$HTTP_GET_VARS[i]}'";
		$rs = mysql_query($query_rs, $dbGmAdm) or die(mysql_error());

		$after = get_str_rs($dbGmAdm, "SELECT * FROM admin WHERE id='{$HTTP_GET_VARS[i]}';");

		

		break;
	case 'd':
		$befores = get_str_rs($dbGmAdm, "SELECT * FROM admin WHERE id='{$HTTP_GET_VARS[i]}';");
		$befores1 .= get_str_rs($dbGmAdm, "SELECT * FROM perm WHERE userid='{$HTTP_GET_VARS[i]}';");

		$query_rs = "DELETE FROM admin WHERE id='{$HTTP_GET_VARS[i]}';";
		$rs = mysql_query($query_rs, $dbGmAdm) or die(mysql_error($dbGmAdm));

		$query_rs1 = "DELETE FROM perm WHERE userid='{$HTTP_GET_VARS[i]}';";
		$rs = mysql_query($query_rs1, $dbGmAdm) or die(mysql_error($dbGmAdm));

		
		
		break;
}
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
<h3>Game Admin User</h3>
<form name="form1" method="post" action="">
  <table border="1" cellspacing=0>
    <tr>
      <td>Admin Name</td>
      <td>E-Mail</td>
      <td>Password</td>
      <td>Action</td>
    </tr>
    <tr bordercolor="#CCCCCC" bgcolor="#FFFF66">
      <td> <input name="name_0" type="text" id="name_0" size="15" maxlength="20"></td>
      <td> <input name="email_0" type="text" id="email_0" size="40" maxlength="100"></td>
      <td> <input name="password_0" type="password" id="password_0" size="12" maxlength="8"></td>
      <!--/*remarked because "showModalDialog()" only works with IE*/<td> <input type="button" name="Button" value="Add" onClick="if(document.form1.name_0.value.length<3){alert ('Please enter a valid name (at least 3 characters).'); return 0};if(document.form1.email_0.value.length<11){alert ('Please enter a valid email.'); return 0};if(document.form1.password_0.value.length<6){alert ('Please enter a valid password (at least 6 characters).'); return 0};var w=showModalDialog('cpasswd.php', '', 'dialogWidth:20;dialogHeight:5');if(w==document.form1.password_0.value){postform(document.form1,'adm.php?a=a')}else{alert('Password not matched')}">-->
      <td> <input type="button" name="Button" value="Add" onClick="if(document.form1.name_0.value.length<3){alert ('Please enter a valid name (at least 3 characters).'); return 0};if(document.form1.email_0.value.length<11){alert ('Please enter a valid email.'); return 0};
      if(document.form1.password_0.value.length<6){alert ('Please enter a valid password (at least 6 characters).'); return 0};
      postform(document.form1,'adm.php?a=a')">
        <input type="button" name="Button" value="Find" onClick="postform(document.form1,'adm.php?a=f')">
      </td>
    </tr>
    <?php
if($HTTP_GET_VARS['a']=='f')
while($row_rs = mysql_fetch_assoc($rs))
{
?>
    <tr onmouseover="this.className='hl'" onmouseout="this.className=''">
      <td><input name="name_<?=$row_rs[id]?>" type="text" id="login_" size="15" maxlength="20" readonly="" value="<?=htmlspecialchars($row_rs[name])?>"></td>
      <td><input name="email_<?=$row_rs[id]?>" type="text" id="email_" size="40" maxlength="100" value="<?=htmlspecialchars($row_rs[email])?>"></td>
      <td><input name="password_<?=$row_rs[id]?>" type="password"  id="passwd_" size="12" maxlength="8" value="<?=htmlspecialchars($row_rs[password])?>"></td>
      <td><input name="button" type="button" onClick="if(confirm('Overwrite?'))postform(document.form1,'adm.php?a=s&i=<?=$row_rs[id]?>')" value="Save">
        <input name="delete_" type="button" id="delete_" value="Delete" onClick="if(confirm('Delete?'))postform(document.form1,'adm.php?a=d&i=<?=$row_rs[id]?>')">
        <!--input type=button onclick="location.href='admperm.php?i=<?=$row_rs[id]?>'" value="Permission"-->
<a href='admperm.php?i=<?=$row_rs[id]?>' onmouseover="return escape('set user permission.')">Permission</a></td>
	</td>
    </tr>
    <?
}
?>
  </table>
    <!--input type="reset" name="Reset" value="Reset"-->
  <hr/>
  <!--p>* if password is empty, system will generate one and email to user</p-->
	<a href="actlogv.php" onmouseover="return escape('view GameAdmin user activity log.')">Game Admin User Activity Log</a>
</form>
<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>
</body>
</html>