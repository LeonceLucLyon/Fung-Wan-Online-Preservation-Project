<?
$outfile = "/var/www/gmadm/build.php";
require('auth.php');
require_once('dbGmAdm.php');

if(!has_perm($HTTP_SESSION_VARS['userid'], "", "motd", "*") )
{
	die("Access denied.");
}

$fcontents = implode ('', file($outfile));

if($HTTP_SERVER_VARS['REQUEST_METHOD']=='POST')
{
	$fp = fopen($outfile,  "w+");
	if(fwrite($fp,  stripslashes($HTTP_POST_VARS['content'])) == -1)
	{
		die("Failed to save build notes.");
	}
	fclose($fp);

	$befores = $fcontents;
	$after = stripslashes($HTTP_POST_VARS['content']);
	

	header("Location: updbuildnote.php");
	exit;
}
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
</head>
<body onload="document.form1.content.focus()" onunload="if(changed)if(!confirm('unsave changes?')){var w=window.open();w.document.write(document.body.outerHTML);w.location.reload()}">
<script>
var changed=0;
</script>
<form method="POST" name="form1">
<h3>Online Build Notes</h3>
<br><textarea name=content rows=28 cols=100 onchange="changed=1"><?=htmlspecialchars($fcontents)?></textarea>
<br>
<input type=submit value="Save" onclick="if(confirm('Confirm overwrite?')){changed=0;return true}else{return false}">
<input type=reset value="Reset" onclick="return confirm('undo changes?')">
<input type=button value="Preview" onclick="var w=window.open('','bpreview','toolbar=no,location=no,status=yes,width=322,height=188,scrollbars=yes');w.document.write(document.form1.content.value)">
</form>
</body>
</html>