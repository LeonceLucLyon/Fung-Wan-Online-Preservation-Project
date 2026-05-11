<?
require('auth.php');
require_once('dbGmAdm.php');
mysql_select_db($database_dbGmAdm, $dbGmAdm);

if(!has_perm($HTTP_SESSION_VARS['userid'], "", "conf", ""))
{
	die("Access denied.");
}
?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
</head>
<body>
	<a href="updstring.php?a=f" onmouseover="return escape('Update game data(eg Quest, Item) description for Game Admin.')">Game Data Description Update</a>
	<br><a href="gmsvr.php?a=f" onmouseover="return escape('configure game DB to be accessible via this website.')">Game DB Configuration</a>
	<br><a href="wcloginhist.php" onmouseover="return escape('view player login statistics.')">Game Login History</a>
	<br><a href="wpopulation.php" onmouseover="return escape('view concurrent connected player statistics.')">Game Population</a>
	<br><a href="motd.php" onmouseover="return escape('update ingame MOTD.')">Ingame Message of Today(MOTD)</a>
	<br><a href="updbuildnote.php" onmouseover="return escape('update online build notes.')">Online Build Notes in the Updater</a>
	<br><a href="scene.php" onmouseover="return escape('hide/unhide game scene entry point from players.')">Scene Controller</a>
	<br><a href="gmsvrs.php" onmouseover="return escape('monitor game server status.')">Server Monitoring</a>
	<br><a href="remotesetup.php" onmouseover="return escape('follow the instructions to setup world application and zone server for Game Admin purpose.')">World Application And Zone Server Setup</a>
	<br><a href="wbroadcast.php" onmouseover="return escape('broadcast message to game world.')">World Broadcast</a>
	<br><a href="gmzs.php" onmouseover="return escape('restart individual zone server.')">Zone Server Controller</a>
	<br><a href="gmzslogcf.php" onmouseover="return escape('access to ingame activity logs.')">Zone Server Log Viewer</a>
	<br><a href="gmcharlog.php" onmouseover="return escape('access to ingame player activity logs.')">Player Activity Log Viewer</a>
	<br><a href="gmcharloggrpnm.php" onmouseover="return escape('admin Group Name of ingame player activity logs.')">Player Activity Log Group Name Settings</a>
	<br><a href="gmcharloggrp.php" onmouseover="return escape('admin Groups of ingame player activity logs.')">Player Activity Log Group Settings</a>

<!--
    <br><br><a href="scene_test.php" onmouseover="return escape('hide/unhide game scene entry point from players.')">Scene Controller (For Testing Purpose Only)</a>
-->

<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>
</body>
</html>
