<!DOCTYPE html>
<html>
	<head>

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel = "stylesheet" href = "./bootstrap.min">
		<link rel = "stylesheet" href = "./bootstrap-theme.min">
		<script type="text/javascript" src="./java.js"></script>
		<script type="text/JavaScript">

			var auto_refresh = setInterval(	function ()
											{
												$('#tweet').load('table.php').fadeIn("slow");
											 }, 5000); // refresh every 5seconds
														
		</script>
		
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
			ANM	| Assignment - 4
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
								<h2><a href = './index.php'>Assignment - 4</a></h2>
							</div>
						</div>
						<div class = "col-md-5" style = "padding: 0;"></div>
					</div>
				</div>
			</div>



			<div class = "row" style = "margin: 0; height: 100%;">

			<!--Status panel-->
				<div class = "col-md-6" style = "border-right: solid 1px black; height: 100%;">
					<div class = "container-fluid" style = "margin: 0 20px 0 20px; padding: 0px; height: 100%;">
						<div class = "row" style = "margin: 0; height: 100%;">
							<table id = "tweet" class = "table table-bordered" style = "width: 100%; text-align: center;">
									<?php include "table.php"; ?>
							</table>
						</div>
					</div>
				</div>

				
			<!--Info panel-->
				<div class = "col-md-6">
					<div class = "container-fluid" style = "margin: 0 20px 0 20px; padding: 0px;">
						<div class = "row" style = "margin: 0;">
							<table class = "table table-bordered" style = "width: 100%; text-align: center;">
<?php 

	if(isset($_GET['device']))
	{
		include "./split.php";
		
		$device = $_GET['device'];
		$exploded = explode('_', $device);
		
		$tag1 = '<tr style = "padding: 2px;"><th>';
		$tag2 = '</th><td style = "text-align: center;">';
		$tag3 = '</td></tr>';
		
		//Create connection
		$connection = mysqli_connect($host, $username, $password, $database, $port);

		//Check connection
		if (!$connection) {
			die("Connection failed: " . mysqli_connect_error());
		}

		//Statement handle
		$query = "SELECT * FROM INFO WHERE IP = '". $exploded[0] ."' AND PORT = '". $exploded[1] ."' AND COMMUNITY = '". $exploded[2] ."'";
		$result = mysqli_query($connection, $query);
		
		echo '<thead><h3 style = "width: 100%; text-align: center; color: green; padding-bottom: 10px">Device Details</h3></thead>';
		
		while($row = mysqli_fetch_assoc($result))
		{
				echo $tag1 . 'Device Name' . $tag2 . $row['sysName'] . $tag3;
				echo $tag1 . 'System Contact' . $tag2 . $row['sysContact'] . $tag3;
				echo $tag1 . 'System Uptime' . $tag2 . $row['sysUpTime'] . $tag3;
				echo $tag1 . 'System Description' . $tag2 . $row['sysDescr'] . $tag3;
				echo $tag1 . 'System Location' . $tag2 . $row['sysLocation'] . $tag3;
				echo $tag1 . "System Services \n
				(indicates the set of services that this entity may potentially offer)" . $tag2 . $row['sysServices'] . $tag3;
				echo $tag1 . 'IP address' . $tag2 . $row['IP'] . $tag3;
				echo $tag1 . 'Community' . $tag2 . $row['COMMUNITY'] . $tag3;
				echo $tag1 . 'Number of Requests Sent' . $tag2 . $row['req_sent'] . $tag3;
				echo $tag1 . 'Number of Requests Lost' . $tag2 . $row['req_lost'] . $tag3;
				echo $tag1 . 'Last Updated at' . $tag2 . $row['webserver_time'] . $tag3;
		}
	}
?>
							</table>
						</div>
					</div>
				</div>
				
				<div class = "col-md-1"></div>
								
			</div>
		</div>
	</body>
	
	<footer><b>Name:</b> Sesha Sai Srinivas Jayapala Vemula	&nbsp |	&nbsp <b>P.No:</b> 9406232935</footer>
	
</html>
