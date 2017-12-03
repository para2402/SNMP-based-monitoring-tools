<!DOCTYPE html>
<html>
	<head>

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel = "stylesheet" href = "./bootstrap.min.css">
		<link rel = "stylesheet" href = "./bootstrap-theme.min.css">

		<script>
		function myFunction() {
			location.reload(true);
		}
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
			ANM	| Assignment - 3
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
								<h2><a href = './index.php'>Assignment - 3</a></h2>
							</div>
						</div>
						<div class = "col-md-5" style = "padding: 0;"></div>
					</div>
				</div>
			</div>

			</br>
			
			<!--Status panel-->
				<div class = "col-md-1"></div>
				<div class = "col-md-4" style = "height: 100%;">
					<div class = "container-fluid" style = "margin: 0 20px 0 20px; padding: 0px; height: 100%;">
						<div class = "row" style = "margin: 0; height: 100%;">
							<form action = "./index.php" method = "get">
								<table>
									<thead>
										<h3 style = "width: 100%; text-align: center; color: green; padding-bottom: 10px">
											Add/ Change Manager
										</h3>
									</thead>
									<tr>
										<th style = "text-align: left; padding-right: 10px">Ip</th>
										<td><input type="text" name="ip"></td>
									</tr>
									
									<tr>
										<th style = "text-align: left; padding-right: 10px">Port</th>
										<td><input type="text" name="port" value = 162></td>
									</tr>
									
									<tr>
										<th style = "text-align: left; padding-right: 10px">Community</th>
										<td><input type="text" name="community" value = 'public'></td>
									</tr>
								</table></br>
								<div style = "text-align: center;">
									<button type = "submit" name = 'button' value = 'MANAGER'>submit</button>
								</div>
							</form>
						</div></br>
						
					<div class = "row" style = "margin: 0; height: 100%;">

<?php

include './split.php';

//Create connection
$connection = mysqli_connect($host, $username, $password, $database, $port);

//Check connection
if (!$connection) {
	die("Connection failed: " . mysqli_connect_error());
}

//Creating plot_DEVICES table
$query = "CREATE TABLE IF NOT EXISTS RESULTS3 (id INT AUTO_INCREMENT PRIMARY KEY,
													Type varchar(255) NOT NULL,
													IP LONGTEXT NOT NULL,
													PORT int NOT NULL DEFAULT 162,
													COMMUNITY varchar(255) NOT NULL DEFAULT 'public',
													FQDN varchar(255) NOT NULL,
													last_reported int NOT NULL DEFAULT 0,
last_message int NOT NULL DEFAULT 0,
													current_status int NOT NULL,
													prev_status int NOT NULL DEFAULT 0,
													UNIQUE KEY (Type, FQDN))";
mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));


//Update Manager
if(isset($_GET['button']) && isset($_GET['ip']))
{
	$query = "INSERT INTO RESULTS3 (Type, IP, PORT, COMMUNITY) VALUES ('" . $_GET['button'] . "', '" . $_GET['ip'] . "', '" . $_GET['port'] . "', '" . $_GET['community'] . "') 
				ON DUPLICATE KEY UPDATE IP = '" . $_GET['ip'] . "', PORT = '" . $_GET['port'] . "', COMMUNITY = '" . $_GET['community'] . "'";
	$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
}

$query = "SELECT * FROM RESULTS3 WHERE Type = 'MANAGER'";
$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
while($row = mysqli_fetch_assoc($result))
{
	echo "Sending traps to manager '<b>". $row["IP"] . "</b>' on port '<b>" . $row["PORT"] . "</b>' and community '<b>" . $row["COMMUNITY"] . "</b>'</br>";
}

?>
					</div>
				</div>
			</div>

			<!--Status panel-->
				<div class = "col-md-1" style = "height: 100%;"></div>
				<div class = "col-md-5" style = "height: 100%;">
					<div class = "container-fluid" style = "margin: 0 20px 0 20px; padding: 0px; height: 100%;">
						<div class = "row" style = "margin: 0; height: 100%;">
							<form action = "./index.php" method = "get">
								<table class = "table table-bordered" style = "width: 100%; text-align: center;">
<?php 

		//Statement handle
		$query = "SELECT * FROM RESULTS3 WHERE Type = 'AGENT'";
		$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
		$mapping = array('0' => 'OK', '1' => 'NORMAL', '2' => 'DANGER', '3' => 'FAIL');
		
		echo '<thead><h3 style = "width: 100%; text-align: center; color: green; padding-bottom: 10px">Device Details</h3></thead>';
		echo '	<tr>
					<th>Fully Qualified Domain Name (FQDN)</th>
					<th>Last Reported at</th>
					<th>Current Status</th>
					<th>Previous message time</th>
				</tr>';
		
		while($row = mysqli_fetch_assoc($result))
		{
				echo '<tr style = "padding: 2px;"><td>' . $row['FQDN'] . '</td>';
				echo '<td>' . $row['last_reported'] . '</td>';
				echo '<td>' . $mapping[$row['current_status']] . '</td>';
				echo '<td>' . $mapping[$row['last_message']] . '</td>'. '</tr>';
		}

mysqli_close($connection);

?>
								</table></br>
							</form>
							<div style = "text-align: center;">
								<button onclick="myFunction()">Refresh</button>
							</div>
							
						</div></br>
						
					<div class = "row" style = "margin: 0; height: 100%;">

					</div>
				</div>
			</div>
					
						
			</div>
		</div>
	</body>
	
	
	
</html>
