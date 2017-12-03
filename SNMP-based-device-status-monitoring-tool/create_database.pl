#!/usr/bin/perl

use DBI;
require "dbconf_path.pl";
dbconf_path();
require "$realpath";

$dbd = "mysql"; 
$dsn = "DBI:$dbd:$database";
$dbh = DBI->connect($dsn,$username,$password) or die $DBI::errstr;

$start = 25;
$end = 27;
$string = "test";

for($i = $start; $i <= $end; $i++)
{
	$var2 = "$string" . "$i";
	$query = $dbh->do("INSERT INTO DEVICES (IP, PORT, COMMUNITY) VALUES ('192.168.184.25', '1161', '$var2');");
}
