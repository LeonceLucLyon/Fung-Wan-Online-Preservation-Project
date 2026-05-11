<?php
require('auth.php');
$vers = array("1.0","1.1","1.2");
/*
$fp = fsockopen('192.168.0.16', 80);
fputs($fp, "GET\r\n");
echo fread($fp,20);
*/

require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

if(!has_perm($HTTP_SESSION_VARS['userid'], "", "conf", ""))
{
	die("Access denied.");
}
elseif(($HTTP_GET_VARS[a]=="a" || $HTTP_GET_VARS[a]=="d" || $HTTP_GET_VARS[a]=="s") && !has_perm($HTTP_SESSION_VARS['userid'], "", "conf", "w"))
{
	die("Access denied. Read-Only.");
}
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="ga.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<h3>World Application and Zone Server Setup for Game Admin</h3><br>
<?
$data = file("$rsa_file.pub");
$username="$remote_username";
$html_zs = "Login to the world application and zone servers as root, make sure 'sudo' is installed and run the following commands,<br>";
$html_zs .= "<font color=blue>adduser --disabled-password --gecos '' {$username}";
$html_zs .= "<br>su {$username}";
$html_zs .= '<br>cd';
$html_zs .= '<br>mkdir .ssh';
$html_zs .= '<br>chmod 700 .ssh';
$html_zs .= "<br>echo {$data[0]} >> .ssh/authorized_keys2";
$html_zs .= '<br>chmod 700 .ssh/authorized_keys2';
$html_zs .= "<br>exit";
$html_zs .= "<br>echo '#!/bin/sh' &gt; $START_ZS";
$html_zs .= "<br>echo '$RUN_ZS' &gt;&gt; $START_ZS";
$html_zs .= "<br>chmod +x $START_ZS";
$html_zs .= "<br>visudo";
$html_zs .= "<br>1000000jo";
$html_zs .= "<br>$remote_username ALL= ($zs_username) NOPASSWD: $SETCMD";
$html_zs .= "<br>$remote_username ALL= ($zs_username) NOPASSWD: $START_ZS</font>";
$html_zs .= "<br>You now stop in vi insert mode, save the configuration and quit vi, then you are done.";
echo $html_zs;
?>
</body>
</html>
<?php
//mysql_free_result($rsGmSvr);
?>