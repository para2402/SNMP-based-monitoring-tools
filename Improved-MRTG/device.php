<!DOCTYPE html>
<html>
	<head>

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="refresh" content="120">
		<link rel = "stylesheet" href = "./bootstrap.min.css">
		<link rel = "stylesheet" href = "./bootstrap-theme.min.css">		
		<style>
			#t1{
				padding: 15px;
				background-color: #eeeeee;
				border-bottom: solid 1px black;
			}

			#home{
				line-height: 150%;
			}

			footer
			{
				text-align: center;
				background-color: lavender;
				border-top: solid 1px black;
				bottom: 0;
				width: 100%;
				position: relative;
			}
								
		</style>

		<title>
			ANM	| Assignment - 1
		</title>
	</head>


	<body>
		<div class = "container-fluid" style = "margin: 0px; padding: 0px; width: 100%; height: 100%;">
			<div id = "t1">
				<div class = "container-fluid" style = "margin: 0px; padding: 0px;">
					<div class = "row">
						<div class = "col-md-5"></div>
						<div class = "col-md-2" style = "padding: 0;">
							<div class = "container-fluid" style = "margin: 0px; padding: 0px; text-align: center;">
								<h2><a href = './index.php'>Assignment - 1</a></h2>
							</div>
						</div>
						<div class = "col-md-5" style = "padding: 0;"></div>
					</div>
				</div>
			</div>


			<div class = "row" style = "margin: 0;">
				<ul class="nav nav-tabs">
				  <li role="presentation" class="active"><a href="./index.php">Home</a></li>
				  <li role="presentation"><a href="#">Add Devices</a></li>
				  <li role="presentation"><a href="#">Remove Devices</a></li>
				</ul>	
			</div>


			<div class = "row" style = "margin: 0; height: 100%; text-align: center;">
<?php
if(isset($_GET['device']))
{
	include './split.php';
	
	$device = $_GET['device'];
	$exploded = explode('_', $device);
	
	//Create connection
	$connection = mysqli_connect($host, $username, $password, $database, $port);

	//Check connection
	if (!$connection) {
		die("Connection failed: " . mysqli_connect_error());
	}

	//Statement handle
	$query = "SELECT * FROM FRONTEND_sai WHERE IP='".$exploded[0]."' AND PORT='".$exploded[1]."' AND COMMUNITY='".$exploded[2]."'";
	$result = mysqli_query($connection, $query);
	$row = mysqli_fetch_assoc($result);
	echo '<h3 style = "width: 100%; text-align: center; color: green;"> Traffic Analysis for Device: </nbsp>' . $row['sysName'] . '</h3><h3>Interface: </nbsp>' . $exploded[4] . '</h3>';
?>			
			</div>
			
			
			<div class = "row" style = "margin: 0; height: 100%;">
				<div class = "col-md-3" style = "height: 100%;"></div>
				<div class = "col-md-6" style = "height: 100%;">
					<table class = "table table-bordered" style = "width: 100%; text-align: center;">
<?php
	
	$tag1 = '<tr style = "padding: 2px;"><th>';
	$tag2 = '</th><td style = "text-align: left;">';
	$tag3 = '</td></tr>';

	echo $tag1 . 'Device Name' . $tag2 . $row['sysName'] . $tag3;
	echo $tag1 . 'System Contact' . $tag2 . $row['sysContact'] . $tag3;
	echo $tag1 . 'System Description' . $tag2 . $row['sysDescr'] . $tag3;
	echo $tag1 . 'Interface Number' . $tag2 . $exploded[3] . $tag3;
	echo $tag1 . 'Interface Name' . $tag2 . $exploded[4] . $tag3;
	echo $tag1 . 'IP address' . $tag2 . $row['IP'] . $tag3;
	echo $tag1 . 'Community' . $tag2 . $row['COMMUNITY'] . $tag3;
	echo $tag1 . 'System Uptime' . $tag2 . $row['sysUpTime'] . $tag3;
	echo $tag1 . 'Last Updated at' . $tag2 . $row['webserver_time'] . $tag3;

	
?>
					</table>
				</div>
			</div>
			
			
			<!--Status panel-->
			<div class = "row" style = "margin: 0; height: 100%;">
				<div class = "col-md-2" style = "height: 100%;"></div>
				<div class = "col-md-8" style = "height: 100%;">
					<table class = "table table-bordered" style = "width: 100%; text-align: center;">
<?php
$file = "./RRD files/" . $exploded[0] . "_" . $exploded[1] . "_" . $exploded[2] . ".rrd";
if(file_exists($file))
{
	$types = array('-1d', '-1w', '-1m', '-1y');
	
	foreach($types as $i)
	{
		$opts = array();
		
		echo '<tr style = "text-align: center; padding: 2px;">
				<th style = "text-align: center;">';
				
		if($i == '-1d')
		{
			echo '<h4>Daily Graph</h4></br>Resolution:	</nbsp>5 Minutes average
				  </th>
					<td>';
			array_push($opts, "--title= Daily Graph", "--x-grid", "HOUR:1:HOUR:2:HOUR:2:0:%H");
		}
		
		if($i == '-1w')
		{
			echo '<h4>Weekly Graph</h4></br>Resolution:	</nbsp>30 Minutes average
				  </th>
					<td>';
			array_push($opts, "--title= Weekly Graph", "--x-grid", "DAY:1:DAY:1:DAY:1:86400:%a");
		}
		
		if($i == '-1m')
		{
			echo '<h4>Monthly Graph</h4></br>Resolution:	</nbsp>2 Hours average
				  </th>
					<td>';
			array_push($opts, "--title= Weekly Graph", "--x-grid", "WEEK:1:WEEK:1:WEEK:1:604800:%V");
		}
		
		if($i == '-1y')
		{
			echo '<h4>Yearly Graph</h4></br>Resolution:	</nbsp>1 Day average
				  </th>
					<td>';
			array_push($opts, "--title= Weekly Graph", "--x-grid", "MONTH:1:MONTH:1:MONTH:1:2419200:%b");
		}
		
		
		array_push($opts, 
					"--start", $i,
					"--lower-limit", "0",
			//		"--slope-mode",
			//		"--units-exponent", "6",
			//		"--rigid",
			//		"--y-grid", "0:1",
			//		"--units-length", "5",
					"--units=si",
					"--grid-dash", "1:3",
					"--alt-autoscale-max",
					"--alt-y-grid",
					"--vertical-label=Bytes per Second",
					"VRULE:00#F0F", //"HRULE:00#F0F",
					"DEF:inBytes=" . $file . ":bytesIn" . $exploded[3] . ":AVERAGE",
					"DEF:outBytes=" . $file . ":bytesOut" . $exploded[3] . ":AVERAGE",
					"VDEF:max_in=inBytes,MAXIMUM",
					"VDEF:max_out=outBytes,MAXIMUM",
					"VDEF:avg_in=inBytes,AVERAGE",
					"VDEF:avg_out=outBytes,AVERAGE",
					"VDEF:last_in=inBytes,LAST",
					"VDEF:last_out=outBytes,LAST",
					"CDEF:incdef=inBytes,8,*",
					"CDEF:outcdef=outBytes,8,*",
					
					"CDEF:out_final=outBytes,UN,0,outBytes,IF",
					"CDEF:in_final=inBytes,UN,0,inBytes,IF",
					
					"COMMENT: \\n",
					"COMMENT:\\t", "COMMENT:\\t",
					"COMMENT: MAXIMUM\\t",
					"COMMENT:  AVERAGE\\t",
					"COMMENT:  CURRENT\\n",
					"COMMENT: \\s",
					"AREA:in_final#00FF00:In traffic\\t",
					"GPRINT:max_in: %6.2lf %sBps\\t",
					"GPRINT:avg_in: %6.2lf %sBps\\t",
					"GPRINT:last_in: %6.2lf %sBps\\n",
					"COMMENT: \\s",
					"LINE1:out_final#0000FF:Out traffic\\t",
					"GPRINT:max_out: %6.2lf %sBps\\t",
					"GPRINT:avg_out: %6.2lf %sBps\\t",
					"GPRINT:last_out: %6.2lf %sBps\\n"
				);

		$img_location = "./RRD files/" . $row["IP"] . "_" . $row["PORT"] . "_" . $row["COMMUNITY"] . "_" . $exploded[3] . "_"  . $i . ".png";
		$ret = rrd_graph($img_location, $opts);

		if ($ret === FALSE)
		{
			echo "<b>Graph error: </b>".rrd_error()."\n";
		}
		else
		{
			echo "<img style = 'margin: 0;' src = './fpassthru_unlink.php?location=" . $img_location . "' title = 'Interface " . $exploded[4] . $i . "'>";
		}
				
		echo '	</td>
			  </tr>';
	}
}

	mysqli_close($connection);
} //end of isset

?>
					</table>
				</div>
				<div class = "col-md-2" style = "height: 100%;"></div>
			</div>
			
			<div class = "row" style = "margin: 5px;"></div>
			
		</div>
	</body>
	
	<footer><b>Name:</b> Sesha Sai Srinivas Jayapala Vemula	&nbsp |	&nbsp <b>P.No:</b> 9406232935</footer>
	
</html>
