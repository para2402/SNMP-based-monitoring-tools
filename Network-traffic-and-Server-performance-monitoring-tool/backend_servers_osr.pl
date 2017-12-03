#!/usr/bin/perl
use DBI;
use Cwd 'abs_path';
use RRD::Simple;
use Data::Dumper qw(Dumper);
use LWP::Simple;

#Finding the path to db.conf
$cwd = abs_path(__FILE__);
@finding = split('/', $cwd);
splice @finding, -2;
push(@finding, 'db.conf');
$realpath = join('/', @finding);
require "$realpath";


#Reading DEVICES table
$dbd = "mysql"; 
$dsn = "DBI:$dbd:$database:$host:$port";
$dbh = DBI->connect($dsn,$username,$password) or die $DBI::errstr;

#Creating SERVERS_sai table
$dbh->do("CREATE TABLE IF NOT EXISTS frontend_SERVERS (id INT AUTO_INCREMENT PRIMARY KEY,
										   IP varchar(255),
										   UNIQUE KEY (IP))") or die $DBI::errstr;

#Inserting IP values frontend_SERVERS into  table from Devices table
#$dbh->do("INSERT INTO frontend_SERVERS (IP) SELECT SERVERS.IP from SERVERS ON DUPLICATE KEY UPDATE IP=frontend_SERVERS.IP")
#		or die $DBI::errstr;

#Getting servers SERVERS_sai from database
$servers_data = $dbh->selectall_hashref("SELECT * from frontend_SERVERS", 'id');
%servers = map { ("$servers_data->{$_}{'IP'}" => { map { ($_ => undef) } ('totalkbytes', 'cpuutil', 'reqpersec', 'bytespersec', 'bytesperreq', 'uptime')})} (keys %$servers_data);
%regex = ('totalkbytes' => 'Total\ kBytes', 'cpuutil' => 'CPULoad', 'reqpersec' => 'ReqPerSec', 'bytespersec' => 'BytesPerSec', 'bytesperreq' => 'BytesPerReq', 'uptime' => 'Uptime');


foreach my $server (keys %servers)
{
	#Sending requests to get server details
	my $server_status = get("http://$server/server-status?auto") or die "Couldn't get it!" unless defined $server_status;
	foreach my $metrics (keys $servers{$server})
	{
		$servers{$server}{$metrics} = ((($server_status =~ /$regex{$metrics}:\ ([(\d\S)|\.]+)/g)[0]) + 0.0);
	}
	
	$servers{$server}{'cpuutil'} = (($servers{$server}{'cpuutil'} * $servers{$server}{'uptime'})/100);

	#RRD database
	if(values %{$servers{$server}})
	{
		#Creating RRD files for each server
		#RRD Initialization		
		my @finding = split('/', $cwd);
		pop(@finding);
		push(@finding, 'RRD files');
		my $rrdpath = join('/', @finding);
		my $rrd_file = "$rrdpath/$server.rrd";
		
		unless(-e $rrdpath)
		{
			mkdir($rrdpath, 0777) or die("Error creating directory: " . $!);
		}
		
		my $rrd = RRD::Simple->new( file => $rrd_file,
									on_missing_ds => "add"
									);

		if(! -e $rrd_file)
		{
			$rrd->create($rrd_file, 'year', map { ($_ => "GAUGE") } (keys %{$servers{$server}}));
		}
		#RRD Initialization ends

		#RRD update
		$rrd->update($rrd_file, time(), map { ($_ => $servers{$server}{$_}) } (keys %{$servers{$server}}) );
	}
}
print Dumper \%servers;
print "\nDone..........\n";
