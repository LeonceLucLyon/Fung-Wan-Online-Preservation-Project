#!/usr/bin/perl
use strict;
#use lib "/usr/lib/perl5/site_perl/5.6.1/i386-linux";
use Fcntl ':flock';
use DBI();

$#ARGV == 4 or die("missing paramenters, <crash_log> <db_host> <db_name> <db_user> <db_password>");

my $zs_crash_log = shift;
my $ga_db_host = shift;
my $ga_db_name = shift;
my $ga_db_user = shift;
my $ga_db_pass = shift;
my ($dbh, $sth, $zs_end_time, $zs_start_time, $char_id);
$zs_end_time = $zs_start_time = 0;

open FILE, "< $zs_crash_log";
flock FILE, LOCK_EX;

$dbh = DBI->connect("DBI:mysql:database=$ga_db_name;host=$ga_db_host", $ga_db_user, $ga_db_pass, {'RaiseError'=>1});
$sth = $dbh->prepare("UPDATE wc_login_log SET flag=1, out_timestamp=?, duration=? - in_timestamp WHERE out_timestamp IS NULL AND in_timestamp>=? AND in_timestamp<=? AND char_id=?") or die($dbh->errstr);

while (<FILE>) {
	if (/^.{20}\((\d+)\) ZS_START [0-9\.]+$/) {
		$zs_start_time = $1;
	} elsif (/^.{20}\((\d+)\) ZS_DOWN [0-9\.]+$/) {
		$zs_end_time = $1;
	} elsif (/^Char (\d+) of .+$/) {
		$char_id = $1;
		if ($zs_start_time > 0 && $zs_end_time > 0) {
			$sth->execute($zs_end_time, $zs_end_time, $zs_start_time, $zs_end_time, $char_id) or die($dbh->errstr);
			print "CrashTime: $zs_end_time, StartTime: $zs_start_time, CharID: $char_id \n";
		}
	} else {
		#print $_;
	}
}
	
$sth->finish;
$dbh->disconnect;
close FILE;

