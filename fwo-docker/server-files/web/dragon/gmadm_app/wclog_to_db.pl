#!/usr/bin/perl -w
use strict;
use DBI();

### game admin db configuration ###

my $DB_HOST = "localhost";
my $DB_NAME = "gmadm";
my $DB_USR = "root";
my $DB_PWD = "emp1re123";
my $LOG_FILE="/var/log/messages";

### program ###

my @DATE=localtime();
my $YEAR=$DATE[5] + 1900;
my $file_mdate = (stat($LOG_FILE))[9];
my $file_year = (localtime($file_mdate))[5]+1900;
my $year_cnt = 0;
my $cur_mth = 1;

my ($d, $m, $t, $datetime, $user, $sql, $server_name, $char_id, $client_ip, $ticket);
my %mth = ( Jan => 1, Feb => 2, Mar => 3, Apr => 4, May => 5, Jun => 6, Jul => 7, Aug => 8, Sep => 9, Oct => 10, Nov => 11, Dec => 12);

sub ip2long{
	my $ip = shift(@_);
	my $n = 256;
	my @sip = split(/\./,$ip);
	my $fip = ($sip[0]*($n * $n * $n))+($sip[1]*($n * $n))+($sip[2] * $n) + ($sip[3]);
	return $fip;
}

open LOG, "$LOG_FILE" or die "cant open $LOG_FILE";
while(<LOG>)
{
	$m = $mth{substr($_, 0, 3)};
	if($m < $cur_mth)
	{
		$YEAR--;
	}
	$cur_mth = $m;
}
close LOG;

my $dbh = DBI->connect("DBI:mysql:database=$DB_NAME;host=$DB_HOST",
			$DB_USR,
			$DB_PWD);
#			{'RaiseError' => 1});
my $sth_insert_login = $dbh->prepare("INSERT INTO wc_login_log(id, in_timestamp, server_name, user, char_id, client_ip, ticket) VALUES(0, UNIX_TIMESTAMP(?), ?, ?, ?, ?, ?)") or die $dbh->errstr;
my $sth_select_login = $dbh->prepare("SELECT id, out_timestamp FROM wc_login_log WHERE user = ? AND char_id = ? AND ticket = ? AND in_timestamp <= UNIX_TIMESTAMP(?) ORDER BY in_timestamp DESC LIMIT 1") or die $dbh->errstr;
my $sth_update_logout = $dbh->prepare("UPDATE wc_login_log SET out_timestamp=UNIX_TIMESTAMP(?), duration=UNIX_TIMESTAMP(?)-in_timestamp WHERE id=?") or die $dbh->errstr;

open LOG, "$LOG_FILE" or die "cant open $LOG_FILE";
$cur_mth = 0;
while(<LOG>)
{
	chop;
	if (/^(...) (..) (.{8}) (\S+) wctrl: Services Login for User (\S+) character (\S{10}).+Address (\S+) Ticket (\S{12})/)
	{
		($m, $d, $t, $server_name, $user, $char_id, $client_ip, $ticket) = ($mth{$1}, $2, $3, $4, $5, $6, ip2long($7), $8);
		if($m < $cur_mth)
		{
			$YEAR++;
		}
		$cur_mth = $m;
		$user =~ s/'/''/g;
		$d =~s/ //g;
		$datetime = "$YEAR-$m-$d $t";
		$sth_insert_login->execute($datetime, $server_name, $user, $char_id, $client_ip, $ticket);
	}
	elsif (/^(...) (..) (.{8}) (\S+) wctrl: Services Dropping Connection for User (\S+), CharID (\S{10}) and Ticket is (\S{12})/)
	{
		($m, $d, $t, $server_name, $user, $char_id, $ticket) = ($mth{$1}, $2, $3, $4, $5, $6, $7);
		if($m < $cur_mth)
		{
			$YEAR++;
		}
		$cur_mth = $m;
		$user =~ s/'/''/g;
		$d =~s/ //g;
		$datetime = "$YEAR-$m-$d $t";
		$sth_select_login->execute($user, $char_id, $ticket, $datetime) or die $sth_select_login->errstr;
		if($sth_select_login->rows==1)
		{
			my ($id, $out_time) = $sth_select_login->fetchrow_array();
			if(! $out_time > 0)
			{
				$sth_update_logout->execute($datetime, $datetime, $id);
			}
		}
		else
		{
			printf("found %d login(s) for logout(account: %s, char_id: %d, ticket: %s, time: %s).\n", $sth_select_login->rows, $user, $char_id, $ticket, $datetime);
		}
	}
}
$sth_insert_login->finish;
$sth_select_login->finish;
$sth_update_logout->finish;
$dbh->disconnect;
close LOG;

