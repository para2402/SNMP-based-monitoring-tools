#!/usr/bin/perl
use DBI;
use Cwd 'abs_path';
use Net::SNMP qw(:snmp);
use RRD::Simple;
use Data::Dumper qw(Dumper);

$splice = 40;

#Finding the path to db.conf
$cwd = abs_path(__FILE__);
@finding = split('/', $cwd);
splice @finding, -2;
push(@finding, 'db.conf');
$realpath = join('/', @finding);
require "$realpath";

$ifIndex = '1.3.6.1.2.1.2.2.1.1';
$ifName = '1.3.6.1.2.1.31.1.1.1.1';

#ifType, ifSpeed, ifAdminStatus, ifOperStatus
@OID_columns = ("1.3.6.1.2.1.2.2.1.3", "1.3.6.1.2.1.2.2.1.5", "1.3.6.1.2.1.2.2.1.7", "1.3.6.1.2.1.2.2.1.8");
@OID_IfInOutOctets = ("1.3.6.1.2.1.2.2.1.10", "1.3.6.1.2.1.2.2.1.16");

#SNMP OIDs for MRTG frontend
$OID_sysDescr = '1.3.6.1.2.1.1.1.0';
$OID_sysUpTime = '1.3.6.1.2.1.1.3.0';
$OID_sysContact = '1.3.6.1.2.1.1.4.0';
$OID_sysName = '1.3.6.1.2.1.1.5.0';
@OIDs_frontend = ($OID_sysName, $OID_sysContact, $OID_sysDescr, $OID_sysUpTime);

#Reading DEVICES table
$dbd = "mysql"; 
$dsn = "DBI:$dbd:$database:$host:$port";
$dbh = DBI->connect($dsn,$username,$password) or die $DBI::errstr;


#Creating INFO table
$dbh->do("CREATE TABLE IF NOT EXISTS FRONTEND_sai (id INT AUTO_INCREMENT PRIMARY KEY,
										   IP varchar(255),
										   PORT int(11) NOT NULL,
										   COMMUNITY varchar(255),
										   sysName LONGTEXT,
										   sysContact LONGTEXT DEFAULT NULL,
										   sysUpTime LONGTEXT,
										   sysDescr LONGTEXT,
										   webserver_time LONGTEXT,
										   Interface_List LONGTEXT,
										   Interface_Name LONGTEXT,
										   UNIQUE KEY (IP, PORT, COMMUNITY))") or die $DBI::errstr;


#Inserting IP, PORTand COMMUNITY values into INFO table from Devices table
$dbh->do("INSERT INTO FRONTEND_sai (IP, PORT, COMMUNITY) SELECT DEVICES.IP, DEVICES.PORT, DEVICES.COMMUNITY from DEVICES ON DUPLICATE KEY UPDATE IP=FRONTEND_sai.IP")
		or die $DBI::errstr;

#Getting devices from database
$device_data = $dbh->selectall_hashref("SELECT * from DEVICES", 'id');

%devices = map { "$device_data->{$_}{'IP'}"."_$device_data->{$_}{'PORT'}"."_$device_data->{$_}{'COMMUNITY'}" => {'IP' => $device_data->{$_}{'IP'}, 'PORT' => $device_data->{$_}{'PORT'}, 'COMMUNITY' => $device_data->{$_}{'COMMUNITY'}, 'ifname' => undef ,$OIDs_frontend[0] => ['sysName'], $OIDs_frontend[1] => ['sysContact'], $OIDs_frontend[2] => ['sysDescr'], $OIDs_frontend[3] => ['sysUptime'], 'bytes' => undef, (map { ("$_" => []) } (filtered, bytes_oids)), 'alloids' => undef} } (keys %$device_data);
my %session;

$rrd = RRD::Editor->new();


#Sending requests to get interface list
	foreach(keys %devices)
	{
		my ($session, $error) = Net::SNMP->session(
													-hostname => $devices{$_}{'IP'},
													-port => $devices{$_}{'PORT'},
													-community => $devices{$_}{'COMMUNITY'},   # v1/v2c
													-version  => '1',
													-nonblocking => 1,
													);

		if (!defined $session) {
		  printf "ERROR: %s.\n", $error;
		  exit 1;
		}
		
		$session{$_} = \$session;
		
		my $result = $session->get_entries(
											-callback => [\&interfaces, $_, \%devices],
											-columns => [ $ifIndex ],
										);

		if (!defined $result)
		{
			printf "ERROR: %s.\n", $session->error();
			$session->close();
			exit 1;
		}
	}	
	snmp_dispatcher();


#Filtering interfaces and forming bytes oid list
	foreach my $a (keys %devices)
	{
			foreach my $b (keys %{$devices{$a}{'alloids'}})
			{
				if($devices{$a}{'alloids'}{$b}{"$OID_columns[0].$b"} == 24 || $devices{$a}{'alloids'}{$b}{"$OID_columns[1].$b"} == 0 || $devices{$a}{'alloids'}{$b}{"$OID_columns[2].$b"} != 1 || $devices{$a}{'alloids'}{$b}{"$OID_columns[3].$b"} != 1)
				{
					next;
				}
				push($devices{$a}{'filtered'}, $b);
			}
			#ifName into hash
			$devices{$a}{'ifname'} = { map { ($_ => undef) } (@{$devices{$a}{'filtered'}}) };
			
		
		push($devices{$a}{'bytes_oids'}, map { ("$OID_IfInOutOctets[0].$_", "$OID_IfInOutOctets[1].$_") } (@{$devices{$a}{'filtered'}}));
		$devices{$a}{'bytes'} = { map { ($_ => undef) } (@{$devices{$a}{'filtered'}}) };

		#Get-request for sending bytes IN and OUT oids
		while(($asd = @{$devices{$a}{'bytes_oids'}}) > 0)
		{
			my $result2 = ${$session{$a}}->get_request(
													-varbindlist => [ splice($devices{$a}{'bytes_oids'}, 0, $splice) ],
													-callback => [ \&save_oids, $a, \%devices, 'bytes' ]
												);

			if (!defined $result2)
			{
				printf "ERROR:\t%s.\n", ${$session{$a}}->error();
				${$session{$a}}->close();
				exit 1;
			}
		}
		
		#Getting frontend oids and ifNames
		my @front = @OIDs_frontend;
		push(@front, (map{ "$ifName.$_" } (@{$devices{$a}{'filtered'}})));
		my @front = oid_lex_sort(@front);
		#print Dumper \@front;
		
		while(($qwe = @front) > 0)
		{
			#Sending SNMP get_request to aquire the FrontEnd details
			my $result3 = ${$session{$a}}->get_request(
														-varbindlist => [ splice(@front, 0, $splice)  ],
														-callback => [ \&frontend, $a, \%devices ]
													);

			if (!defined($result3))
			{
				printf "ERROR:\t%s.\n", ${$session{$a}}->error();
				${$session{$a}}->close();
				exit 1;
			}
		}
		
	}
	snmp_dispatcher();
	

#RRD database
	foreach my $dev (keys %devices)
	{
		if(@{$devices{$dev}{'filtered'}})
		{	
			#Creating RRD files for each server
			#RRD Initialization
			my @finding = split('/', $cwd);
			pop(@finding);
			push(@finding, 'RRD files');
			my $rrdpath = join('/', @finding);
			my $rrd_file = "$rrdpath/$dev.rrd";
			
			unless(-e $rrdpath)
			{
				mkdir($rrdpath, 0777) or die("Error creating directory: " . $!);
			}
			
			my $rrd = RRD::Simple->new( file => $rrd_file,
										on_missing_ds => "add"
									  );

			if(! -e $rrd_file)
			{
				$rrd->create($rrd_file, "mrtg", map { ("bytesIn$_" => "COUNTER"), ("bytesOut$_" => "COUNTER") } (keys %{$devices{$dev}{'bytes'}}));
			}
			#RRD Initialization ends

			#RRD update
			$rrd->update($rrd_file, time(), (map { ("bytesIn$_" => $devices{$dev}{'bytes'}{$_}{"$OID_IfInOutOctets[0].$_"}) , ("bytesOut$_" => $devices{$dev}{'bytes'}{$_}{"$OID_IfInOutOctets[1].$_"}) } (keys %{$devices{$dev}{'bytes'}})));
		
			print Dumper $devices{$dev};
			
			my @if_mysql = ( join('|', (sort {$a <=> $b} @{$devices{$dev}{'filtered'}})), join('|', (map { $devices{$dev}{'ifname'}{$_} } (sort {$a <=> $b} @{$devices{$dev}{'filtered'}}))));
			print Dumper \@if_mysql;
			$dbh->do("UPDATE FRONTEND_sai SET webserver_time = '" . localtime() . "', Interface_List = '$if_mysql[0]', Interface_Name = '$if_mysql[1]'
					  WHERE IP = '$devices{$dev}{'IP'}' AND COMMUNITY = '$devices{$dev}{'COMMUNITY'}' AND PORT = '$devices{$dev}{'PORT'}'") or die "\n$DBI::errstr\n";
		}
	}

	print "\nDone..........\n";
	exit(0);


#Callback function to get interfaces
sub interfaces()
{
	my ($session, $device, $devices_ref) = @_;
	my @temp;
	my $hash_ref = $session->var_bind_list();
	
	print "\n$devices_ref->{$device}{'IP'}------- $devices_ref->{$device}{'PORT'} ---------- $devices_ref->{$device}{'COMMUNITY'}\n";
	
	if(!defined($hash_ref))
	{
		return;
	}
	
	foreach my $col (@OID_columns)
	{
		push(@temp, map { "$col.$_" } (values %$hash_ref));
	}

	while(($size = @temp) > 0)
	{
		#Sending SNMP get_request to aquire the @temp
		my $result_oids = $session->get_request(
													-varbindlist => [ splice(@temp, 0, $splice) ],
													-callback => [ \&save_oids, $device, $devices_ref, 'alloids' ]
												);

		if (!defined($result_oids))
		{
			printf "ERROR:\t%s.\n", $session->error();
			$session->close();
			exit 1;
		}
	}
	return;
}


sub save_oids()
{
	my ($session, $device, $devices_ref, $key) = @_;
	my $hash_ref = $session->var_bind_list();
	
	if(!defined($hash_ref))
	{
		return;
	}

	foreach(keys %$hash_ref)
	{
		my $if_num = oid_splitter($_);
		$devices_ref->{$device}{$key}{$if_num}{$_} = $hash_ref->{$_};
	}
	return;
}

sub frontend()
{
	my ($session, $device, $devices_ref) = @_;
	my $hash_ref = $session->var_bind_list();
	
	if(!defined($hash_ref))
	{
		print "\nosr\n";
		return;
	}
	
	#print Dumper $hash_ref;
	
	foreach(keys %$hash_ref)
	{
		if(oid_base_match($ifName, $_))
		{
#			my $if = oid_splitter($_);
			$devices_ref->{$device}{'ifname'}{oid_splitter($_)} = $hash_ref->{$_};
		}
		
		else
		{
			$dbh->do("UPDATE FRONTEND_sai SET $devices_ref->{$device}{$_}[0] = '$hash_ref->{$_}' 
					  WHERE IP = '$devices_ref->{$device}{'IP'}' AND COMMUNITY = '$devices_ref->{$device}{'COMMUNITY'}' AND PORT = '$devices_ref->{$device}{'PORT'}'") or die "\n$DBI::errstr\n";
		}
	}
	  
	return;
}

sub oid_splitter()
{
	my @oid_after_split = split('\.', shift);
	return pop(@oid_after_split);
}
