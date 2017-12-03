<?php

include "./split.php";

$color_1 = "FFEEEE";
$color_30 = "FF0000";
$new_color = 2056;

//Create connection
$connection = mysqli_connect($host, $username, $password, $database, $port);

//Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

//Statement handle
$query = "SELECT id, IP, PORT, COMMUNITY, sysName, req_lost FROM INFO";
$result = mysqli_query($connection, $query);

echo '	<thead><h3 style = "width: 100%; text-align: center; color: green; padding-bottom: 10px">Device Status</h3></thead>
			<tr style = "text-align: center; padding: 2px;">
				<th style = "text-align: center;">id</th>
				<th style = "text-align: center;">IP</th>
				<th style = "text-align: center;">Community</th>
				<th style = "text-align: center;">Device Name</th>
				<th style = "text-align: center;">Status</th>
			</tr>';

//Fetching
while($row = mysqli_fetch_assoc($result))
{
		echo '<tr style = "padding: 2px;">';
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["IP"] . "</td>";
        echo "<td>" . $row["COMMUNITY"] . "</a></td>";

		if(isset($row['sysName']) && !empty($row['sysName']))
		{
			echo "<td><form action = 'index.php' name = 'device' method = 'get'>
			<button style = 'width: 100%;' type = 'submit' name = 'device' value = '" . $row["IP"] . "_" . $row["PORT"] . "_" . $row["COMMUNITY"] . "'><b>" . $row["sysName"] . "</b>
			</button>
			</form></td>";
        }
        
        elseif(isset($row['sysName']) && empty($row['sysName']))
		{
			echo "<td><form action = 'index.php' name = 'device' method = 'get'>
			<button style = 'width: 100%;' type = 'submit' name = 'device' value = '" . $row["IP"] . "_" . $row["PORT"] . "_" . $row["COMMUNITY"] . "'><b>Device id: " . $row["id"] . "</b>
			</button>
			</form></td>";
        }
        
        else
        {
			echo "<td><form action = 'index.php' name = 'device' method = 'get'>
			<button style = 'width: 100%;' type = 'submit' name = 'device' value = '" . $row["IP"] . "_" . $row["PORT"] . "_" . $row["COMMUNITY"] . "'>" . "Device Details not available" . "
			</button>
			</form></td>";
		}
        
        //Determining color for device status
        if($row["req_lost"] == 0)
        {
			echo "<td style = 'color: green'>GOOD</td></tr>";
			//echo "<td style = 'background-color: #" . $color_0 . "'>" . $row["req_lost"] . "</td></tr>";
		}
		
		elseif($row["req_lost"] == 1)
        {
			echo "<td style = 'background-color: #" . $color_1 . "'></td></tr>";
			//echo "<td style = 'background-color: #" . $color_1 . "'>" . $row["req_lost"] . "</td></tr>";
		}
		
		elseif($row["req_lost"] >= 30)
        {
			echo "<td style = 'background-color: #" . $color_30 . "'></td></tr>";
			//echo "<td style = 'background-color: #" . $color_30 . "'>" . $row["req_lost"] . "</td></tr>";
		}
		
		else
        {
			$color = dechex(hexdec($color_1) - ($row["req_lost"] * $new_color));
			echo "<td style = 'background-color: #" . $color . "'></td></tr>";
			//echo "<td style = 'background-color: #" . $color . "'>" . $color . "</td></tr>";
		}
}

mysqli_close($connection);

?>
