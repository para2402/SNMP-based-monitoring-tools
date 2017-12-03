#!/usr/bin/perl
use DBI;
use Cwd 'abs_path';
use Net::SNMP qw(:snmp);
use RRD::Simple;
use Data::Dumper qw(Dumper);

$splice = 2;

#Finding the path to db.conf
$cwd = abs_path(__FILE__);
@finding = split('/', $cwd);
splice @finding, -2;
push(@finding, 'db.conf');
$realpath = join('/', @finding);
require "$realpath";

$ifIndex = '1.3.6.1.2.1.2.2.1.1';
$ifName = '1.3.6.1.2.1.31.1.1.1.1';

@OID_IfInOutOctets = ("1.3.6.1.2.1.2.2.1.10", "1.3.6.1.2.1.2.2.1.16");

#SNMP OIDs for MRTG frontend sysName, sysContact, sysDescr, sysUpTime
@OIDs_frontend = ('1.3.6.1.2.1.1.5.0', '1.3.6.1.2.1.1.4.0', '1.3.6.1.2.1.1.1.0', '1.3.6.1.2.1.1.3.0');

#Reading DEVICES table
$dbd = "mysql"; 
$dsn = "DBI:$dbd:$database:$host:$port";
$dbh = DBI->connect($dsn,$username,$password) or die $DBI::errstr;


#Creating INFO table
$dbh->do("CREATE TABLE IF NOT EXISTS frontend_DEVICES (id INT AUTO_INCREMENT PRIMARY KEY,
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
										   selected_list LONGTEXT,
										   selected_name LONGTEXT,
										   UNIQUE KEY (IP, PORT, COMMUNITY))") or die $DBI::errstr;


#Getting devices from database
$device_data = $dbh->selectall_hashref("SELECT * from frontend_DEVICES", 'id');

%devices = map { "$device_data->{$_}{'IP'}"."_$device_data->{$_}{'PORT'}"."_$device_data->{$_}{'COMMUNITY'}" => {'IP' => $device_data->{$_}{'IP'}, 'PORT' => $device_data->{$_}{'PORT'}, 'COMMUNITY' => $device_data->{$_}{'COMMUNITY'}, 'selected_ifs' => $device_data->{$_}{'selected_list'}, 'iflist' => [], 'ifnames' => undef, 'bytes' => undef } } (keys %$device_data);
%front_ref = ($OIDs_frontend[0] => 'sysName', $OIDs_frontend[1] => 'sysContact', $OIDs_frontend[2] => 'sysDescr', $OIDs_frontend[3] => 'sysUptime');
my %session;


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
											-callback => [\&save_oids, $_, \%devices, 'iflist'],
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


#Sending bytesIn and bytesOut for selected interfaces
	foreach my $dev (keys %devices)
	{
		$devices{$dev}{'selected_ifs'} = [split('\|', $devices{$dev}{'selected_ifs'})];
		my @temp;
		foreach my $if (@{$devices{$dev}{'selected_ifs'}})
		{
			push(@temp, map { "$_.$if" } (@OID_IfInOutOctets));
		}

		while(@temp)
		{
			#Sending SNMP get_request to aquire the @temp
			my $result_oids = ${$session{$dev}}->get_request(
														-varbindlist => [ splice(@temp, 0, $splice) ],
														-callback => [ \&save_oids, $dev, \%devices, 'bytes' ]
													);

			if (!defined($result_oids))
			{
				printf "ERROR:\t%s.\n", $session->error();
				$session->close();
				exit 1;
			}
		}
	}
	snmp_dispatcher();


#RRD database
	foreach my $dev (keys %devices)
	{
		#Creating RRD files for each device
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
								  
		if(@{$devices{$dev}{'iflist'}})
		{
			if(! -e $rrd_file)
			{
				$rrd->create($rrd_file, 'year',map { ("bytesIn$_" => "COUNTER"), ("bytesOut$_" => "COUNTER") } (@{$devices{$dev}{'iflist'}}));
			}
		}
		#RRD Initialization ends



		if(@{$devices{$dev}{'selected_ifs'}})
		{
			#RRD update
			$rrd->update($rrd_file, time(), (map { ("bytesIn$_" => $devices{$dev}{'bytes'}{$_}{"$OID_IfInOutOctets[0].$_"}) , ("bytesOut$_" => $devices{$dev}{'bytes'}{$_}{"$OID_IfInOutOctets[1].$_"}) } (keys %{$devices{$dev}{'bytes'}})));
		}


		#Getting ifNames		
		my @oid_names = oid_lex_sort(map{ "$ifName.$_" } (@{$devices{$dev}{'iflist'}}));
		while(@oid_names)
		{
			#Sending SNMP get_request to aquire the ifNames
			my $result3 = ${$session{$dev}}->get_request(
														-varbindlist => [ splice(@oid_names, 0, $splice)  ],
														-callback => [ \&save_oids, $dev, \%devices, 'ifnames' ]
													);

			if (!defined($result3))
			{
				printf "ERROR:\t%s.\n", ${$session{$dev}}->error();
				${$session{$dev}}->close();
				exit 1;
			}
		}
	}
	snmp_dispatcher();

	
	
	foreach my $dev (keys %devices)
	{	
		my @if_mysql = ( join('|', (sort{$a <=> $b} @{$devices{$dev}{'iflist'}})), join('|', (map { $devices{$dev}{'ifnames'}{$_} } (sort{$a <=> $b} @{$devices{$dev}{'iflist'}}))));
		#print Dumper \@if_mysql;
		$dbh->do("UPDATE frontend_DEVICES SET webserver_time = '" . localtime() . "', Interface_List = '$if_mysql[0]', Interface_Name = '$if_mysql[1]'
				  WHERE IP = '$devices{$dev}{'IP'}' AND COMMUNITY = '$devices{$dev}{'COMMUNITY'}' AND PORT = '$devices{$dev}{'PORT'}'") or die "\n$DBI::errstr\n";
	}
	
	print Dumper \%devices;

	print "\nDone..........\n";
	exit(0);





#Callback function to get interfaces
sub save_oids()
{
	my ($session, $device, $devices_ref, $identity) = @_;
	my $hash_ref = $session->var_bind_list();
	
	if(!defined($hash_ref))
	{
		return;
	}
	
	if($identity eq 'iflist')
	{
		push(@{$devices_ref->{$device}{$identity}}, values(%$hash_ref));
		foreach(values(%$hash_ref))
		{
			$devices_ref->{$device}{'bytes'}{$_}{"1.3.6.1.2.1.2.2.1.10.$_"} = 0;
			$devices_ref->{$device}{'bytes'}{$_}{"1.3.6.1.2.1.2.2.1.16.$_"} = 0;
		}
	}
	
	elsif($identity eq 'bytes')
	{
		foreach(keys %$hash_ref)
		{
			if(oid_base_match('1.3.6.1.2.1.1', $_))
			{
				$dbh->do("UPDATE frontend_DEVICES SET $front_ref{$_} = '$hash_ref->{$_}' 
						  WHERE IP = '$devices_ref->{$device}{'IP'}' AND COMMUNITY = '$devices_ref->{$device}{'COMMUNITY'}' AND PORT = '$devices_ref->{$device}{'PORT'}'") or die "\n$DBI::errstr\n";
				next;
			}
			
			if($hash_ref->{$_} == undef)
			{
				$devices_ref->{$device}{$identity}{oid_splitter($_)}{$_} = 0;
			}
			else
			{
				$devices_ref->{$device}{$identity}{oid_splitter($_)}{$_} = $hash_ref->{$_};
			}
		}
	}
	
	elsif($identity eq 'ifnames')
	{
		foreach(keys %$hash_ref)
		{
			$devices_ref->{$device}{'ifnames'}{oid_splitter($_)} = $hash_ref->{$_};
		}
	}

	return;
}


sub oid_splitter()
{
	my @oid_after_split = split('\.', shift);
	return pop(@oid_after_split);
}
