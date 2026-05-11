<?require("custom.php");?>
<html>
<head>
<title><?=BROWSER_TITLE?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="ga.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
<!--
body {
	margin: 0px;
	background-color: #f0e1b3;
}
table.mn td{
	padding-top:0px;
	padding-left:5px;
	padding-right:5px;
	padding-bottom:0
}

td.np{
	padding:0px;
}

.hl{
	background-color: yellow;
}
-->
</style>
<script>
function ss(o){
	if(o==null)
	{
		window.status=document.all.tip.innerText=''
	}
	else
	{
		window.status=document.all.tip.innerText=o.title
		o.className='hl'

	}
}
</script>
</head>
<body bgcolor="#CCCCCC">
<table width="100%" cellspacing=0 cellpadding=0 border=0>
<tr valign=top>
	<td rowspan=2>
		&nbsp;<img src="images/logo_small.jpg">&nbsp;&nbsp;
	</td>
	<td width="100%" height="100%">
		<h3 style="margin:0"><?=BROWSER_TITLE?></h3>
	</td>
</tr>
<tr>
	<td>
	<table width="100%" border="0" cellspacing="0" acellpadding="2" class="mn">
	  <tr align="left">
	    <td id=tduser><nobr><a href="adm.php?a=f" target="mainFrame" title="manage GameAdmin user account." onmouseover="return escape(this.title)" onmouseout="">User</a></nobr></td>
	    <td class='np'>|</td>
	    <td><nobr><a href="gmac.php" target="mainFrame" title="manage player game account." onmouseover="return escape(this.title)" >Account</a></nobr></td>
	    <td class='np'>|</td>
	    <td><nobr><a href="gmchar.php" target="mainFrame" title="manage player character." onmouseover="return escape(this.title)" >Character</a></nobr></td>
	    <td class='np'>|</td>
	    <td><nobr><a href="gmdata.php" target="mainFrame" title="manage game data, such as item, events." onmouseover="return escape(this.title)" >Game Data</a></nobr></td>
	<!--
	    <td class='np'>|</td>
	    <td><a href="gmclan.php" target="mainFrame">Clan</a></td>
	<!--
	    <td class='np'>|</td>
	    <td><a href="#" target="_self" onClick="alert('Under Construction')">System</a></td>

		<td class='np'>|</td>
	    <td><a href="gmevent.php" target="mainFrame">GameEvent</a></td>
	<!--
	    <td class='np'>|</td>
	    <td><a href="motd.php" target="mainFrame">MOTD</a></td>
	    <td class='np'>|</td>
	    <td><a href="updbuildnote.php" target="mainFrame">BuildNotes</a></td>
	-->
	    <td class='np'>|</td>
	    <td width="100%"><a href="gmsvrmn.php" target="mainFrame" title="configure DB settings, view server logs, broadcast message." onmouseover="return escape(this.title)" >Server</a></td>
	    <!--td><a href="adm.php" target="mainFrame">Preference</a></td>
	    <td class='np'>|</td>-->
	    <td><a href="login.php" target="_top">Logout</a></td>
	  </tr>
	</table>
	</td>
</tr>
</table>
<span id=tip style='background-color:yellow'></span>
<script language="JavaScript" type="text/javascript" src="wz_tooltip.js"></script>
</body>
</html>
