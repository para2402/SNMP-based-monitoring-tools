#!/usr/bin/perl
use DBI;
use Net::SNMP;
use Data::Dumper qw(Dumper);
use Cwd 'abs_path';

print "START TIME: " . time() . "\n";


#Finding the path to db.conf
$cwd = abs_path(__FILE__);
@finding = split('/', $cwd);
splice @finding, -2;
push(@finding, 'db.conf');
$realpath = join('/', @finding);
require "$realpath";


#SNMP OIDs
$OID_sysDescr = '1.3.6.1.2.1.1.1.0';
$OID_sysUpTime = '1.3.6.1.2.1.1.3.0';
$OID_sysContact = '1.3.6.1.2.1.1.4.0';
$OID_sysName = '1.3.6.1.2.1.1.5.0';
$OID_sysLocation = '1.3.6.1.2.1.1.6.0';
$OID_sysServices = '1.3.6.1.2.1.1.7.0';
%OIDs = (	#This hash has a significance in the callback subroutine
			$OID_sysName => sysName,
			$OID_sysContact => sysContact,
			$OID_sysUpTime => sysUpTime,
			$OID_sysDescr => sysDescr,
			$OID_sysLocation => sysLocation,
			$OID_sysServices => sysServices
		);
@OIDs = keys(%OIDs);

#SNMP session
$version = "1";


#Reading DEVICES table
$dbd = "mysql"; 
$dsn = "DBI:$dbd:$database:$host:$port";
$dbh = DBI->connect($dsn,$username,$password) or die $DBI::errstr;


#Creating INFO table
$dbh->do("CREATE TABLE IF NOT EXISTS INFO (id INT AUTO_INCREMENT PRIMARY KEY,
										   IP varchar(255),
										   PORT int(11) NOT NULL,
										   COMMUNITY varchar(255),
										   sysName LONGTEXT,
										   sysContact LONGTEXT,
										   sysUpTime LONGTEXT,
										   sysDescr LONGTEXT,
										   sysLocation LONGTEXT,
										   sysServices LONGTEXT,
										   req_sent INT NOT NULL DEFAULT 0,
										   req_lost INT NOT NULL DEFAULT 0,
										   webserver_time LONGTEXT,
										   UNIQUE KEY (IP, PORT, COMMUNITY))") or die $DBI::errstr;


#Inserting IP, PORTand COMMUNITY values into INFO table from Devices table
$dbh->do("INSERT IGNORE INTO INFO (IP, PORT, COMMUNITY) SELECT DEVICES.IP, DEVICES.PORT, DEVICES.COMMUNITY from DEVICES 
			ON DUPLICATE KEY UPDATE IP = INFO.IP")
		or die $DBI::errstr;

$query = $dbh->prepare("SELECT * from DEVICES");
$query->execute() or die $DBI::errstr;
while($dev = $query->fetchrow_hashref)
{
	#fetchrow_hashref gives the reference/address of the actual hash that holds the result
	my $ip = $dev->{'IP'};
	my $device_port = $dev->{'PORT'};
	my $community = $dev->{'COMMUNITY'};

	my ($session, $error) = Net::SNMP->session(
												-hostname => $ip,
												-version  => $version,
												-community => $community,   # v1/v2c
												-port => $device_port,
												-nonblocking => 1													
											);

	if (!defined $session)
	{
	  printf "ERROR: %s.\n", $error;
	  next;
	}

	#SNMP getrequest
	my $result = $session->get_request(
										-callback => [\&response_handler, $ip, $device_port, $community],
										-varbindlist => \@OIDs
									);										
	if (!defined $result)
	{
		printf "ERROR: %s.\n", $session->error();
		$session->close();
		next;
	}		
}
#Dispatch queued SNMP requests
snmp_dispatcher();
print "\nEnd TIME: " . time() . "\n";
exit 0;


#Callback subroutine: inserts the gathered data into the database
sub response_handler
{
	#Extracting the variable bindings list from the get_request response message
	my ($session, $ip, $device_port, $community) = @_;
	my $hash_ref = $session->var_bind_list();		#$hash_ref holds the reference to the hash containing var_bind_list
	my %hash = %$hash_ref;
		
	if(defined($hash_ref))
	{
		my $sql_update = join(' , ', (map{"$OIDs{$_}" . " = " . "'$hash{$_}'"}(keys %hash)));
		my $sth = "UPDATE INFO SET webserver_time = '" . localtime() . "', req_sent = req_sent+1 , req_lost = '0' , $sql_update WHERE IP = '$ip' AND PORT = '$device_port' AND COMMUNITY = '$community' ";
		
		$dbh->do($sth) or die "\n$DBI::errstr\n";
	}
	else
	{
		#Updating req_lost
		$dbh->do("UPDATE INFO SET req_sent = req_sent+1, req_lost = req_lost+1, webserver_time = '" . localtime() . "' WHERE IP = '$ip' AND PORT = '$device_port' AND COMMUNITY = '$community'") or die $DBI::errstr;
	}
	
#	print "\nDevice Update TIME: " . time() . "\n";	
	$session->close();
}
