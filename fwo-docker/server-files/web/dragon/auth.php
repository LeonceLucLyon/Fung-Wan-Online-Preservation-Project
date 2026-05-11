<?php
require("custom.php");
require("config.php");


define("FIRST_PCHAR_ID", 1073741824); //hex 4000 0000
$BROADCAST_MESSAGE_MAXLENGTH=80; //include 1 null terminator

session_start();
if($HTTP_SESSION_VARS['id']=='')die("Please login <a href='login.php' target=_top>here</a>");

$zslogGID = array(1=>"Item", "Combat", "Clan", "GM");

$clan_name = array(1=>"Supreme Sword Clan","King of Heroes Clan","Matchless Clan","Sword Worship Clan","Swift Meaning Clan", "6", "7", "8", "9", "10",20=>"a");
$clan_shortname = array(1=>"SSC","KOH","MC","SWC","SMC", "6", "7", "8", "9", "10",20=>"a");
$clan_ids = array(1,2,3,4,5);
$job_desc = array("", "Leader", "Minister", "Master", "Senior", "Member");
$town_name = array(
50101=>"Bi Sha Village",
"An De Village",
"Chuan Sui Village",
"Xi He village",
"Ping An Village",
"Ma Village",
"Xiang De Village",
"Qing Hai Village",
"Nan Wang Village",
"Xin Tian Village",
"Yong Chang Village",
"Long Li Village",
50695=>"Hong Wan Village",
"Bian Liang Village",
"Li Keng Lumber Yard",
"Ya Na City",
"Holy Land of Trinity",
"Pagodas Valley",
"Yang Guang Pagoda",
"Sai Mo Village",
"Holy Cypress Temple",
"Yan Ming Village",
"Mt. Qiong Gate",
);
$scene_name = Array(
1=>
"Dong Chang District",
"Xi Chang District",
"Nan Chang District",
"Land of Gold",
"Land of Breeze",
"Land of Rocky Mountain",
11=>
"Northern Desert",
"Rock Mouth Hill Village",
22=>
"Godless Palace",
"Fishing Village",
32=>
"Qi Lian Town",
"Phoenix Creek Village",
42=>
"Bu Village",
"An Shan Village",
52=>
"Le Yang Village",
"Sun Hua Village",
61=>
"Tutorial Level",
63=>
"Tian Xia",
"Tian Yin City",
"Nan An Village",
"Righteous Clan Outer Halls",
"Righteous Clan Inner Sanctum",
"The Mystique Clan",
200=>
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
"Tutorial Level",
251=>
"War Event Battle Skirmish Arena",
"War Event Battle Skirmish Arena",
"War Event Town Siege Arena",
"War Event Clan Stronghold Assault Arena",
"Leadership Duel Arena",
257=>
"PvP Arena for Level 1-50",
"PvP Arena for Level 51-100",
"PvP Arena for any Level"
);


function U16toU8($st, $nd)
{
	$v = (ord($st) * 256) + ord($nd);
	if($v <= 127)
	{
		return pack('c', $v);
	}
	elseif($v <= 2047)
	{
		return pack('C*', 192 | ($v >> 6), 128 | ($v & 63));
	}
	elseif($v <= 65535)
	{
		return pack('C*', 224 | ($v >> 12), 128 | ($v >> 6) & 63, 128 | ($v & 63));
	}
	else
	{
		die('invalid');
	}
}

function U16btoU8str($str)
{
	//FEFF big-endian - direct
	//FFFE little-endian - need swap

	$len = strlen($str);
	$out = NULL;
	for($idx = 0; $idx < $len; $idx+=2)
	{
		$out .= U16toU8($str[$idx+1], $str[$idx]);
	}
	return $out;
}

function U8toU16($str)
{
	$len = strlen($str);
	$out = NULL;
	$u16 = NULL;
	for($idx = 0; $idx < $len; $idx++)
	{
		if((ord($str[$idx]) & 128) == 0)
		{
			$u16 = pack('v', ord($str[$idx]));
		}
		elseif((ord($str[$idx]) & 224) == 192)
		{
			$u16 = pack('v', ((31 & ord($str[$idx])) << 5) | (63 & ord($str[$idx+1])));
			$idx+=1;
		}
		elseif((ord($str[$idx]) & 240) == 224)
		{
			$u16 = pack('v', (((15 & ord($str[$idx])) << 12) | ((63 & ord($str[$idx+1])) << 6) | (63 & ord($str[$idx+2]))));
			$idx+=2;
		}
		$out .= $u16;
	}
	return $out;
}

function generate_form ($name, $data)
{
	$out = $name? '<form name="'.$name.'">':'';

	while (list ($k, $v) = each ($data))
	{
		if (is_array ($v))
		{
			for ($n = 0, $m = count($v); $n < $m; $n++)
			{
				$out .= add_input ($k.'[]', $v[$n]);
			}
		}
		else
		{
			$out .= add_input ($k, $v);
		}
	}
	$out .= $name? '</form>':'';
	return $out;
}
function add_input ($name, $value)
{
//	return '<input name="'.$name.'" value="'.addcslashes(stripslashes($value),"\0..\255").'" type=hidden>'."\n";
	return '<input name="'.$name.'" value="'.htmlspecialchars(stripslashes($value),ENT_QUOTES).'" type=hidden>'."\n";
	return '<input name="'.htmlentities($name).'" value="'.htmlentities($value).'" type=hidden>'."\n";
}
function post_form ($name, $action, $method = 'post')
{
	print <<<END
<script language="JavaScript">
var f=$name;
f.action="$action";
f.method="$method";
f.submit();
</script>
END;
	exit;
}

function hexstring($str)
{
	$hex="";
	$len=strlen($str);
	for($n=0;$n<$len;$n++)
	{
		$hex.=str_pad(dechex(ord($str[$n])),2,'0',STR_PAD_LEFT);
	}
	return $hex;
}

function remove_dbcs_null($str) //to remove null and following character(s) from a u16 string.
{
	$len = strlen($str);

	for($n=0; $n< $len; $n+=2)
	{
		if(ord($str[$n]) == 0 && ord($str[$n+1]) == 0)
		break;
	}

	return substr($str, 0, $n);
}

function unsign_signed_integer($int)
{
	if($int < 0) return ($int + 1) * -1;
	return 4294967296 - 1 - $int;
}

$array_day = array('01','02','03','04','05','06','07','08','09',10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
$array_month = array('01','02','03','04','05','06','07','08','09',10,11,12);
$array_year = array(2006,2005,2004,2003);

function add_options($opts, $selected){
	$html = "";
	foreach($opts as $opt)
	{
		if($opt == $selected)
		{
			$html .= "<option value=\"$opt\" SELECTED>$opt</option>\n";
		}
		else
		{
			$html .= "<option value=\"$opt\">$opt</option>\n";
		}
	}
	return $html;
}

/*
rw system-user	1	1
rw server	1	1
rw game ac	10	2
w game data	10	2
r game data

*/


$quest_state_ref="
Quest State Refererence Table
<table border=1 bgcolor=\"#EEEEEE\">
	<tr>
		<th>Quest State</th><th>Description</th>
	</tr>
	<tr>
		<td>1 - 252</td><td>In Progress</td>
	</tr>
	<tr>
		<td>255</td><td>Completed</td>
	</tr>
	<tr>
		<td>254</td><td>Failed</td>
	</tr>
	<tr>
		<td>253</td><td>Alternative Success</td>
	</tr>
	<tr>
		<td>0</td><td>Not Assigned</td>
	</tr>
</table>
";

function is_own_clan_relic($clan_id, $item_id)
{
	return (
		($clan_id == 1 && $item_id ==1376257)
		|| ($clan_id == 2 && $item_id ==1376258)
		|| ($clan_id == 3 && $item_id ==1376259)
		|| ($clan_id == 4 && $item_id ==1376260)
		|| ($clan_id == 5 && $item_id ==1376261)
		|| ($clan_id == 6 && $item_id ==1376262)
		|| ($clan_id == 7 && $item_id ==1376263)
		|| ($clan_id == 8 && $item_id ==1376264)
		|| ($clan_id == 9 && $item_id ==1376265)
		|| ($clan_id == 10 && $item_id ==1376266)
	);
}

function is_weapon($item_id)
{
	return (
		($item_id >> 16) >= 11 && ($item_id >> 16) <= 14)
		|| (($item_id >> 16) >= 111 && ($item_id >> 16) <= 114
	);

}

function is_player_character($char_id)
{
	return ($char_id >= 1073741824); // hex 4000 0000

}

function is_clan($id)
{
	return ($id >=1 && $id <= 5);
}

function is_unique_item($item_id)
{
	return (
		(($item_id >> 16) > 100)
		|| (($item_id >> 16) == 21)
	);
}

if($readonly_gmdata)
{
	$readonly_remark_begin = "<!--";
	$readonly_remark_end = "-->";
	$readonly_disabled = "disabled";
}
else
{
	$readonly_remark_begin = "";
	$readonly_remark_end = "";
	$readonly_disabled = "";
}

$GLOBALS['__crc32_table']=array();        // Lookup table array
__crc32_init_table();

function __crc32_init_table() {            // Builds lookup table array
	// This is the official polynomial used by
	// CRC-32 in PKZip, WinZip and Ethernet.
	$polynomial = 0x04c11db7;

	// 256 values representing ASCII character codes.
	for($i=0;$i <= 0xFF;++$i) {
		$GLOBALS['__crc32_table'][$i]=(__crc32_reflect($i,8) << 24);
		for($j=0;$j < 8;++$j) {
			$GLOBALS['__crc32_table'][$i]=(($GLOBALS['__crc32_table'][$i] << 1) ^
			(($GLOBALS['__crc32_table'][$i] & (1 << 31))?$polynomial:0));
		}
		$GLOBALS['__crc32_table'][$i] = __crc32_reflect($GLOBALS['__crc32_table'][$i], 32);
	}
}

function __crc32_reflect($ref, $ch) {        // Reflects CRC bits in the lookup table
	$value=0;

	// Swap bit 0 for bit 7, bit 1 for bit 6, etc.
	for($i=1;$i<($ch+1);++$i) {
		if($ref & 1) $value |= (1 << ($ch-$i));
		$ref = (($ref >> 1) & 0x7fffffff);
	}
	return $value;
}

function __crc32_string($text) {        // Creates a CRC from a text string
	// Once the lookup table has been filled in by the two functions above,
	// this function creates all CRCs using only the lookup table.

	// You need unsigned variables because negative values
	// introduce high bits where zero bits are required.
	// PHP doesn't have unsigned integers:
	// I've solved this problem by doing a '&' after a '>>'.

	// Start out with all bits set high.
	$crc=0xffffffff;
	$len=strlen($text);

	// Perform the algorithm on each character in the string,
	// using the lookup table values.
	for($i=0;$i < $len;++$i) {
		$crc=(($crc >> 8) & 0x00ffffff) ^ $GLOBALS['__crc32_table'][($crc & 0xFF) ^ ord($text{$i})];
	}

	return $crc; //PGS style :)

	// Exclusive OR the result with the beginning value.
	return $crc ^ 0xffffffff;
}

function __crc32_file($name) {            // Creates a CRC from a file
	// Info: look at __crc32_string

	// Start out with all bits set high.
	$crc=0xffffffff;

	if(($fp=fopen($name,'rb'))===false) return false;

	// Perform the algorithm on each character in file
	for(;;) {
		$i=@fread($fp,1);
		if(strlen($i)==0) break;
		$crc=(($crc >> 8) & 0x00ffffff) ^ $GLOBALS['__crc32_table'][($crc & 0xFF) ^ ord($i)];
	}

	@fclose($fp);

	// Exclusive OR the result with the beginning value.
	return $crc ^ 0xffffffff;
}

function hash_name($n)
{
	$z = "\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0";
	$l = substr_replace ($z, $n, 0, strlen($n));
	if(strlen($l) > 40)
		$l=substr($l, 0, 40);
	return sprintf("%u", __crc32_string($l));
}
?>
