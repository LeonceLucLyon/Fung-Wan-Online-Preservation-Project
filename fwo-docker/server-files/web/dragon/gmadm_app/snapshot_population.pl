#!/usr/bin/perl -w
use DBI();
my @authsyss = ();
my @wctrlrs = ();

### configuration ###

# game admin db

my $DB_HOST = "localhost";
my $DB_NAME = "gmadm";
my $DB_USER = "dbadmin";
my $DB_PASS = "hitech";

# game db
# syntax: push @authsyss, ["<auth_db_ip>", "fwsubdevdb", "<dbuser>", "<password>"];
# syntax: push @wctrlrs, ["<wctrlr_db_ip>", "fwworlddevdb", "<dbuser>", "<password>", "<server_name>", "<wctrlr_app_ip>"];

#<auth_db_ip> = IP of the machine where the authsys resides 
#<wctrlr_db_ip> = IP of the machine where the world controller DB resides
#<wctrlr_app_ip> = IP IP of the machine where the world controller resides
#<dbuser> = Database user name of the gmadm DB. Use the same name as $DB_USER.
#<password> = Database password of the gmadm DB. Use the same password as $DB_PASS.
#<server_name> = Server name of the machine where the world controller resides.



push @authsyss, ["192.168.0.22", "fwsubdevdb", "dbadmin", "hitech"];
push @wctrlrs, ["192.168.0.22", "fwworlddevdb", "dbadmin", "hitech", "server2", "192.168.0.22"];

### program ###

sub long2ip{
	my (@octets,$i,$ip_number,$ip_number_display,$number_convert,$ip_address);
	$ip_number_display = $ip_number = shift;
	chomp($ip_number);
	#for($i = 3; $i >= 0; $i--) {
	for($i = 0; $i <= 3; $i++) {
		 $octets[$i] = ($ip_number & 0xFF);
		$ip_number >>= 8;
	}
	$ip_address = join('.', @octets);
}

my $dbw = DBI->connect("DBI:mysql:database=$DB_NAME;host=$DB_HOST", $DB_USER, $DB_PASS);
$dbw->do("DELETE FROM authorized");
#$dbw->do("DELETE FROM population");

foreach $idx (0 .. $#authsyss){
	print $authsyss[$idx]->[0];
	$dbr = DBI->connect("DBI:mysql:database=$authsyss[$idx]->[1];host=$authsyss[$idx]->[0]", $authsyss[$idx]->[2], $authsyss[$idx]->[3]);
	$stget = $dbr->prepare("SELECT Username, WorldAddr FROM authorized") or die $dbr->errstr;
	$stset = $dbw->prepare("INSERT INTO authorized(Username, wip) VALUES(?, ?)") or die $dbw->errstr;
	$stget->execute;
	while(@row = $stget->fetchrow_array){
		$wip = long2ip($row[1]);
		$stset->execute($row[0], $wip);
	}
	$stset->finish;
	$stget->finish;
	$dbr->disconnect;
}

foreach $idx (0 .. $#wctrlrs){
	print $wctrlrs[$idx]->[0];
	$wid = $wctrlrs[$idx]->[4];
	$dbr = DBI->connect("DBI:mysql:database=$wctrlrs[$idx]->[1];host=$wctrlrs[$idx]->[0]", $wctrlrs[$idx]->[2], $wctrlrs[$idx]->[3]);
	$stget = $dbr->prepare("SELECT SceneID, Username FROM authenticated") or die $dbr->errstr;
	$stset = $dbw->prepare("UPDATE authorized SET SceneID=? WHERE Username=?") or die $dbw->errstr;
	$stget->execute;
	$time = time;
	while(@row = $stget->fetchrow_array){
		$stset->execute($row[0], $row[1]);
	}
	$stget = $dbr->prepare("SELECT SceneID, Address, SceneFileName FROM scene") or die $dbr->errstr;
	$stget2 = $dbw->prepare("SELECT COUNT(1) FROM authorized WHERE SceneID=? AND wip=?") or die $dbr->errstr;
	$stset = $dbw->prepare("INSERT INTO population(dt, wid, sid, ip, scene, cnt)VALUES(?, ?, ?, ?, ?, ?)") or die $dbw->errstr;
	$stget->execute;
	while(@row = $stget->fetchrow_array){
		$stget2->execute($row[0], $wctrlrs[$idx]->[5]);
		@row2 = $stget2->fetchrow_array;
		$stset->execute($time, $wid, $row[0], $row[1], $row[2], $row2[0]);
	}
	$stset->finish;
	$stget->finish;
	$stget2->finish;
	$dbr->disconnect;
}
$dbw->disconnect;

