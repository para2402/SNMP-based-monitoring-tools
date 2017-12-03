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

?>

					<li role="presentation" class="active"><a href="./index.php">Add Devices/servers to monitor</a></li>
					<li role="presentation"><a href="./add_plot.php">Select Devices/servers to plot</a></li>
					<li role="presentation"><a href="./compare.php">Compare</a></li>
				</ul>
			</div><br>
			

			<form action = "./index.php" method = "get">
								
			<!--Status panel-->
			<div class = "col-md-12" style = "height: 100%;">
				<div class = "container-fluid" style = "margin: 0 20px 0 20px; padding: 0px; height: 100%;">
					
<!---Servers Table-->
					<div class = "row" style = "margin: 0; height: 100%;">
						<table class = "table table-bordered" style = "width: 100%;">
							<thead>
								<h3 style = "width: 100%; text-align: center; color: green; padding-bottom: 10px">
									Add or Remove devices to monitor
								</h3>
							</thead>
							
							<tr style = "text-align: center; padding: 2px;">
								<th></th>
								<th style = "text-align: center;">Devices</th>
							</tr>
							
							<tr style = "text-align: center; padding: 2px;">
								<th>ADD</th>
								<td>
									<table>
										<tr>
											<th style = "text-align: left; padding-right: 10px">IP</th>
											<td><input type="text" name="ip"></td>
										</tr>
										
										<tr>
											<th style = "text-align: left; padding-right: 10px">PORT</th>
											<td><input type="text" name="port"></td>
										</tr>
										
										<tr>
											<th style = "text-align: left; padding-right: 10px">COMMUNITY</th>
											<td>
												<input type="text" name="community">
<?php

//Adding a Device to monitor
if(isset($_GET['button']) && !empty($_GET['ip']) && !empty($_GET['port']) && !empty($_GET['community']))
{
	$query = "INSERT INTO frontend_DEVICES (IP, PORT, COMMUNITY) VALUES ('" . $_GET['ip'] . "', '" . $_GET['port'] . "', '" . $_GET['community'] . "')
				ON DUPLICATE KEY UPDATE id=id";
	mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
	echo '</br>Device added';
}

?>
											</td>
										</tr>
									</table>											
								</td>
							</tr>


							<tr>
								<th>Select Interfaces</br>to monitor</th>
								<td>
									<table class = "table table-bordered" style = "width: 100%; text-align: center;">
										<tr>

<?php

//Insert/Update selected INTERFACES of a device on click
if(isset($_GET['button']) && !empty($_GET['if_list']))
{
	$device = array();
	
	foreach($_GET['if_list'] as $input => $output)
	{
		$exploding = explode('_', $input);
		$device[$input]["IP"] = $exploding[0];
		$device[$input]["PORT"] = $exploding[1];
		$device[$input]["COMMUNITY"] = $exploding[2];
		
		if($output == "all")
		{
			$query = "SELECT Interface_List, Interface_Name from frontend_DEVICES WHERE IP = '" . $exploding[0] . "' AND PORT = '" . $exploding[1] . "' AND COMMUNITY = '" . $exploding[2] . "'";
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
		
		$query = "UPDATE frontend_DEVICES SET selected_list = '" . $device[$dev]["iflist"]['string'] . "'" . ', selected_name = ' . "'" . $device[$dev]["ifname"]['string'] . "' 
					WHERE IP='" . $device[$dev]["IP"] . "' AND PORT='" . $device[$dev]["PORT"] . "' AND COMMUNITY='" . $device[$dev]["COMMUNITY"] . "'";
		mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
	}	
}


//Table to select INTERFACES of a device to monitor from frontend_DEVICES
$query = "SELECT * FROM frontend_DEVICES";
$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));

echo '	<th style = "text-align: center;">IP</th>
		<th style = "text-align: center;">Port</th>
		<th style = "text-align: center;">Community</th>
		<th style = "text-align: center;">Interfaces</th>
	  </tr>';

while($row = mysqli_fetch_assoc($result))
{
	if(!empty($row["Interface_List"]))
	{
		$interface_list = explode('|', $row["Interface_List"]);
		$interface_name = explode('|', $row["Interface_Name"]);
		$name_list = array_combine($interface_list, $interface_name);
		
		echo '<tr style = "padding: 2px;">';
		echo "	<td>" . $row["IP"] . "</td>";
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

?>

									</table>
								</td>
							</tr>


							<tr>
								<th>REMOVE</th>
								<td>
									<table class = "table table-bordered" style = "width: 100%; text-align: center;">
										<tr>

<?php

//REMOVE a device on click
if(isset($_GET['button']))
{
	if(!empty($_GET['device_monitor']))
	{
		foreach($_GET['device_monitor'] as $insert)
		{
			$exploding = explode('_', $insert);
			$query = "DELETE FROM frontend_DEVICES WHERE IP = '" . $exploding[0] . "' AND PORT = '" . $exploding[1] . "' AND COMMUNITY = '" . $exploding[2] . "'";
			mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
		}
		
		echo 'Devices Removed</br>';
	}
}


//Table to Remove a device from DEVICES_copy
$query = "SELECT * FROM frontend_DEVICES";
$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));

echo '	<th></th>
		<th style = "text-align: center;">id</th>
		<th style = "text-align: center;">IP</th>
		<th style = "text-align: center;">Port</th>
		<th style = "text-align: center;">Community</th>
	  </tr>';

while($row = mysqli_fetch_assoc($result))
{
		
	echo '<tr style = "padding: 2px;">';		
	echo ' <td>
				<input type = "checkbox" name = device_monitor[] value = "' . $row['IP'] . '_' . $row['PORT'] . '_' . $row['COMMUNITY'] . '">
		   </td>';
	echo "	<td>" . $row["id"] . "</td><td>" . $row["IP"] . "</td>";
	echo "	<td>" . $row["PORT"] . "</td><td>" . $row["COMMUNITY"] . '</td>';
	
	echo "</tr>";
}

?>

									</table>
								</td>
							</tr>
						</table>
						
						
						
						</br>
						
						
						
<!---Servers Table-->
						<table class = "table table-bordered" style = "width: 100%;">
							<thead>
								<h3 style = "width: 100%; text-align: center; color: green; padding-bottom: 10px">
									Add or Remove servers to monitor
								</h3>
							</thead>
							
							<tr style = "text-align: center; padding: 2px;">
								<th></th>
								<th style = "text-align: center;">Servers</th>
							</tr>
							
							<tr style = "text-align: center; padding: 2px;">
								<th>ADD</th>								
								<td>
									IP: <input type="text" name="server_ip"></br>

<?php

//Adding a Server to monitor
if(isset($_GET['button']) && !empty($_GET['server_ip']))
{
	$query = "INSERT INTO frontend_SERVERS (IP) VALUES ('" . $_GET['server_ip'] . "') ON DUPLICATE KEY UPDATE id=id";
	mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
	echo 'Server added';
}

?>
												
								</td>
							</tr>

							<tr>
								<th>REMOVE</th>
								<td>
									<table class = "table table-bordered" style = "width: 100%; text-align: center;">
										<tr>

<?php

//REMOVE a server on click
if(isset($_GET['button']))
{
	if(!empty($_GET['server_monitor']))
	{
		foreach($_GET['server_monitor'] as $insert)
		{
			$query = "DELETE FROM frontend_SERVERS WHERE IP = '" . $insert . "'";
			mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));
		}
		
		echo 'Servers Removed</br>';
	}
}


//Table to Remove a server from frontend_SERVERS
$query = "SELECT * FROM frontend_SERVERS";
$result = mysqli_query($connection, $query) or die("Error:" . mysqli_error($connection));

echo '<th></th>';
echo '	<th style = "text-align: center;">id</th>
		<th style = "text-align: center;">IP</th>
	  </tr>';

while($row = mysqli_fetch_assoc($result))
{
	echo '<tr>';
	echo '	<td>
				<input type = "checkbox" name = "server_monitor[]" value = "' . $row['IP'] . '">
			</td>';
	echo "	<td>" . $row["id"] . "</td><td>" . $row["IP"] . "</td>
		  </tr>";
}

mysqli_close($connection);

?>

										</tr>
									</table>
									
								</td>
							</tr>
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
