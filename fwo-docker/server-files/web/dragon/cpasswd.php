<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<form name=form1 onsubmit="return false">
Confirm password: <input type="password" name="cpassword" size="12" maxlength="20">

<input type=button value="OK" onclick="window.returnValue=document.form1.cpassword.value;window.close()">
</form>
</body>
</html>
