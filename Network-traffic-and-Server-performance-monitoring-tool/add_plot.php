<!DOCTYPE html>
<html>
	<head>

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
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
			ANM	| Assignment - 2
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
								<h2><a href = './index.php'>Assignment - 2</a></h2>
							</div>
						</div>
						<div class = "col-md-5" style = "padding: 0;"></div>
					</div>
				</div>
			</div>


			<div class = "row" style = "margin: 0;">
				<ul class="nav nav-tabs">
<?php

include './split.php';

//Create connection
$connection = mysqli_connect($host, $username, $password, $database, $port);

//Check connection
if (!$connection) {
	die("Connection failed: " . mysqli_connect_error());
}


//Drop plot_DEVICES table
$query = "DROP TABLE IF EXISTS plot_DEVICES";
mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
//Drop plot_SERVERS table
$query = "DROP TABLE IF EXISTS plot_SERVERS";
mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));


//Creating plot_DEVICES table
$query = "CREATE TABLE plot_DEVICES (id INT AUTO_INCREMENT PRIMARY KEY,
										   IP varchar(255),
										   PORT int(11) NOT NULL,
										   COMMUNITY varchar(255),
										   Interface_List LONGTEXT,
										   Interface_Name LONGTEXT,
										   metrics LONGTEXT,
										   UNIQUE KEY (IP, PORT, COMMUNITY))";
mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));

//Creating plot_SERVERS table
$query = "CREATE TABLE plot_SERVERS (id INT AUTO_INCREMENT PRIMARY KEY,
										   IP varchar(255),
										   metrics LONGTEXT,
										   UNIQUE KEY (IP))";
mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));

?>

					<li role="presentation"><a href="./index.php">Add Devices/servers to monitor</a></li>
					<li role="presentation" class="active"><a href="./add_plot.php">Select Devices/servers to plot</a></li>
					<li role="presentation"><a href="./compare.php">Compare</a></li>

				</ul>
			</div><br>

			<form action = "./add_plot.php" method = "get">

			<!--Status panel-->
				<div class = "col-md-12" style = "height: 100%;">
					<div class = "container-fluid" style = "margin: 0 20px 0 20px; padding: 0px; height: 100%;">
						<h3 style = "text-align: center; color: green; padding-bottom: 10px">Servers</h3>
						<div class = "row" style = "margin: 0; height: 100%;">
							<table class = "table table-bordered" style = "width: 100%; text-align: center;">
								<tr style = "text-align: center; padding: 2px;">
								
									
<?php

//Add SERVERS or DEVICES
//SERVERS	
$query = "SELECT * FROM frontend_SERVERS";
$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));

echo '<th></th>';
echo '	<th style = "text-align: center;">id</th>
		<th style = "text-align: center;">IP</th>
	  </tr>';

while($row = mysqli_fetch_assoc($result))
{
	echo '<tr style = "padding: 2px;">';
	echo '	<td>
				<input style = "padding: 0; margin: 0;" type = "checkbox" name = "server_list[]" value = "' . $row['IP'] . '">
			</td>';
	echo "	<td>" . $row["id"] . "</td><td>" . $row["IP"] . "</td>
		  </tr>";
}


echo '	</table>
	  </div>';
	  
echo '	<div class = "row" style = "margin: 0; height: 100%;">
		<h3 style = "text-align: center; color: green; padding-bottom: 10px">Devices</h3>
		<table class = "table table-bordered" style = "width: 100%; text-align: center;">
			<tr style = "text-align: center; padding: 2px;">';


//DEVICES
$query = "SELECT * FROM frontend_DEVICES";
$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));

echo '	<th style = "text-align: center;">id</th>
		<th style = "text-align: center;">IP</th>
		<th style = "text-align: center;">Port</th>
		<th style = "text-align: center;">Community</th>
		<th style = "text-align: center;">Interfaces</th>
	  </tr>';

while($row = mysqli_fetch_assoc($result))
{
	if(!empty($row["selected_list"]))
	{
		$interface_list = explode('|', $row["selected_list"]);
		$interface_name = explode('|', $row["selected_name"]);
		$name_list = array_combine($interface_list, $interface_name);
		
		echo '<tr style = "padding: 2px;">';
		echo "	<td>" . $row["id"] . "</td><td>" . $row["IP"] . "</td>";
		echo "	<td>" . $row["PORT"] . "</td><td>" . $row["COMMUNITY"] . '</td>
				<td>';
		
		foreach ($interface_list as $if)
		{
			echo ' <div style = "text-align: left; float: left; width: 100px; margin-right: 30px;">
						<input style = "margin-right: 3px;" type = "checkbox" name = if_list[' . $row['IP'] . '_' . $row['PORT'] . '_' . $row['COMMUNITY'] . '][list][] value = "' . $if . '<||>' . $name_list[$if] . '">
						' . $name_list[$if] . '
					</div>';
		}
		
		echo ' <div style = "text-align: left; float: left; width: 100px; margin-right: 30px;">
					<input style = "margin-right: 3px;" type = "checkbox" name = if_list[' . $row['IP'] . '_' . $row['PORT'] . '_' . $row['COMMUNITY'] . '] value = "all">
					Select all interfaces
				</div>';
		
		echo "	</td>
			  </tr>";
	}
}


// Submit BUTTON for ADD servers and devices
if(isset($_GET['button']))
{
//SERVERS
	$values = array();
	$server_metrics;
	
	foreach($_GET['server_list'] as $insert)
	{
		$query = "INSERT INTO plot_SERVERS (IP) VALUES ('" . $insert . "') ON
					DUPLICATE KEY UPDATE id = plot_SERVERS.id";
		mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
	}


//DEVICES
	$device = array();
	
	foreach($_GET['if_list'] as $input => $output)
	{
		$exploding = explode('_', $input);
		$device[$input]["IP"] = $exploding[0];
		$device[$input]["PORT"] = $exploding[1];
		$device[$input]["COMMUNITY"] = $exploding[2];
		
		if($output == "all")
		{
			$query = "SELECT selected_list, selected_name from frontend_DEVICES WHERE IP = '" . $exploding[0] . "' AND PORT = '" . $exploding[1] . "' AND COMMUNITY = '" . $exploding[2] . "'";
			$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
			$row = mysqli_fetch_row($result);
			$device[$input]["iflist"]['string'] = $row[0];
			$device[$input]["ifname"]['string'] = $row[1];
		}
		else
		{				
			foreach($_GET['if_list'][$input]['list'] as $if)
			{
				$separate = explode('<||>', $if);
				$device[$input]["iflist"]['list'][] = $separate[0];
				$device[$input]["ifname"]['list'][] = $separate[1];
			}
		}
	}

	foreach(array_keys($device) as $dev)
	{
		if(!isset($device[$dev]["iflist"]['string']))
		{
			$device[$dev]["iflist"]['string'] = implode('|', $device[$dev]["iflist"]['list']);
			$device[$dev]["ifname"]['string'] = implode('|', $device[$dev]["ifname"]['list']);
		}
		
		$query = "INSERT INTO plot_DEVICES (IP, PORT, COMMUNITY, Interface_List, Interface_Name) 
				  VALUES " . "('" . $device[$dev]['IP'] . "', '" . $device[$dev]['PORT'] . "', '" . $device[$dev]['COMMUNITY'] . "', '" . $device[$dev]["iflist"]['string'] . "', '" . $device[$dev]["ifname"]['string'] . "')" . ' 
				  ON DUPLICATE KEY UPDATE metrics = "asd", Interface_List = ' . "'" . $device[$dev]["iflist"]['string'] . "'" . ', Interface_Name = ' . "'" . $device[$dev]["ifname"]['string'] . "'";
		#print $query;
		mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
	}
}

mysqli_close($connection);

?>

							</table>
						</div>
						<div class = "row" style = "text-align: center; margin: 0 0 20px 0; height: 100%;">								
								<input type = "submit" name = "button" value = "submit"/>
							</form>
						</div><br>
						
						
						
						
					</div>
				</div>							
			</div>
		</div>
	</body>
	
	<footer><b>Name:</b> Sesha Sai Srinivas Jayapala Vemula	&nbsp |	&nbsp <b>P.No:</b> 9406232935</footer>
	
</html>
