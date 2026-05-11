#!/usr/bin/perl
use strict;
use POSIX qw(strftime);
#use lib "/usr/lib/perl5/site_perl/5.6.1/i386-linux";
use Fcntl ':flock';
use DBI();

$#ARGV == 2 or die("Insufficient parameters, <zs_ip> <zs_conf> <crash_log>");

my $zs_ip = shift;
my $zs_conf = shift;
my $zs_crash_log = shift;
my $zs_start_script = shift;
my ($zs_db_host, $zs_db_name, $zs_db_user, $zs_db_pass);
my ($dbh, $sth, $rs_username, $rs_char_id);
my ($zs_status_time, $is_zs_running, $was_zs_running);
my $zs_ipn;
my $zs_ipr;

$zs_ip =~ /(\d+)\.(\d+)\.(\d+)\.(\d+)/;
$zs_ipr = "$4.$3.$2.$1";
$zs_ipn = ip2ipn($zs_ipr);

open ZS_CONF, "< $zs_conf";
while(<ZS_CONF>) {
	if($_ =~ /^DBName\s+([A-z0-9]+)/i) {
		$zs_db_name = $1;
	} elsif ($_ =~ /^DBHost\s+([A-z0-9\.]+)/i) {
		$zs_db_host = $1;
	} elsif ($_ =~ /^DBUser\s+([A-z0-9]+)/i) {
		$zs_db_user = $1;
	} elsif ($_ =~ /^DBPassword\s+([\!-~]+)/i) {
		$zs_db_pass = $1;
	}
}
close ZS_CONF;

open FILE, ">> $zs_crash_log" or die("cannot write file, $zs_crash_log.");
flock FILE, LOCK_EX;
if(was_zs_running() && !is_zs_running()) {
	print FILE strftime("%b %d %Y %H:%M:%S", localtime), "(", time, ") ZS_DOWN $zs_ip\n";

	$dbh = DBI->connect("DBI:mysql:database=$zs_db_name;host=$zs_db_host", $zs_db_user, $zs_db_pass, {'RaiseError'=>1});
	$sth = $dbh->prepare("SELECT Username, CharID FROM authenticated WHERE Addr=?") or die($dbh->errstr);
	$sth->execute($zs_ipn) or die($dbh->errstr);
	while(($rs_username, $rs_char_id) = $sth->fetchrow_array) {
		print FILE "Char $rs_char_id of $rs_username\n";
	}
	$sth->finish;
	$dbh->disconnect;
}
if(!is_zs_running() && !was_zs_running()) {
	sleep 1;
	print FILE strftime("%b %d %Y %H:%M:%S", localtime), "(", time, ") ZS_START $zs_ip\n";
	#`$zs_start_script`
}
close FILE;

sub was_zs_running {
	`tail -1 $zs_crash_log | grep "ZS_START"`;
	return $? == 0;
}

sub is_zs_running {
	`/sbin/pidof zoneserver`;
	return $? == 0;
}

sub ip2ipn {
	# this sub will change an IP to the Integer value
	if ( $_[0] =~ /^\d+\.\d+\.\d+\.\d+/ ) {
		$_[0] =~ /^(\d+)\.(\d+)\.(\d+)\.(\d+)$/;
		my $num = (16777216*$1)+(65536*$2)+(256*$3)+$4;
		return $num;
	} else {
		return 0;
	}
}

sub ipn2ip {
	# this sub returns the IP address from an Integer
	# logical range is from 0.0.0.1 to 255.255.255.255
	if ( ( $_[0] > 0 ) && ( $_[0] < 4294967295 ) ) {
		my $ipn = $_[0];
		my $w=($ipn/16777216)%256;
		my $x=($ipn/65536)%256;
		my $y=($ipn/256)%256;
		my $z=$ipn%256;
		my $ipn = $w . "." . $x . "." . $y . "." . $z;
		return $ipn;
	} else {
		return 0;
	}
}

