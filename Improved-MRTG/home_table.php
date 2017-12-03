<?php

include './split.php';

//Create connection
$connection = mysqli_connect($host, $username, $password, $database, $port);

//Check connection
if (!$connection) {
	die("Connection failed: " . mysqli_connect_error());
}

//Statement handle
$query = "SELECT IP, PORT, COMMUNITY, sysName, Interface_List, Interface_Name FROM FRONTEND_sai";
$result = mysqli_query($connection, $query);


//Fetching
while($row = mysqli_fetch_assoc($result))
{
	$file = "./RRD files/" . $row["IP"] . "_" . $row["PORT"] . "_" . $row["COMMUNITY"] . ".rrd";
	if(file_exists($file) && !empty($row["Interface_List"]))
	{
		echo '<div class = "container-fluid" style = "margin: 0 20px 50px 20px; padding: 0; border: solid 1px black; height: 100%;">
				<div class = "row" style = "margin: 0; height: 100%;">
					<table class = "table table-bordered" style = "width: 100%; text-align: center;">
						<tr style = "text-align: center; padding: 2px;">
							<th style = "text-align: center;">IP</th>
							<th style = "text-align: center;">PORT</th>
							<th style = "text-align: center;">Community</th>
							<th style = "text-align: center;">Device Name</th>
						</tr>';

		echo '<tr style = "padding: 2px;">';
		echo "<td>" . $row["IP"] . "</td>";
		echo "<td>" . $row["PORT"] . "</td>";
		echo "<td>" . $row["COMMUNITY"] . "</td>";

		if(isset($row['sysName']))
		{
			echo "<td>" . $row["sysName"] . "</td>";
		}
		
		else
		{
			echo "<td>System Name not available</td>";
		}
		
		echo '			</tr>
					</table>
				</div>
				<div class = "row" style = "margin: 0; height: 100%;">';
				
		$variable_line = explode('|', $row['Interface_List']);
		$if_name = explode('|', $row['Interface_Name']);
		
		foreach(array_keys($variable_line) as $key)
		{
			$opts = array(
				"--start", "-1d",
				"--lower-limit", "0",
				"--slope-mode",
		//		"--units-exponent", "6",
		//		"--rigid",
		//		"--title=Interface " . $if_name[$key] . "\t" . $row['sysName'], 
				"--x-grid", "HOUR:1:HOUR:2:HOUR:2:0:%H",
		//		"--y-grid", "0:1",
		//		"--units-length", "5",
				"--units=si",
				"--grid-dash", "1:3",
				"--alt-autoscale-max",
				"--alt-y-grid",
				"--vertical-label=Bytes per Second",
				"VRULE:00#F00",
				"DEF:inBytes=" . $file . ":bytesIn" . $variable_line[$key] . ":AVERAGE",
				"DEF:outBytes=" . $file . ":bytesOut" . $variable_line[$key] . ":AVERAGE",
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

			$img_location = "./RRD files/" . $row["IP"] . "_" . $row["PORT"] . "_" . $row["COMMUNITY"] . "_" . $variable_line[$key] . "_1d.png";
			$ret = rrd_graph($img_location, $opts);

			if ($ret === FALSE)
			{
				echo "<b>Graph error: </b>".rrd_error()."\n";
			}
			else
			{
				echo "<div class = 'col-md-6 col-centered' style = 'height: 100%; margin: 0 0 20px 0; text-align: center;'>
						<h4 style = 'text-align: left; padding-left: 40px;'><b>#" . $if_name[$key] . " -- " . $row['sysName'] . "</b></h4>
						<form action = './device.php' name = 'device' method = 'get'>
							<a href=./device.php?device=" . $row["IP"] . "_" . $row["PORT"] . "_" . $row["COMMUNITY"] . "_" . $variable_line[$key] . '_' . $if_name[$key] . ">
								<img style = 'margin: 0;' src = './fpassthru_unlink.php?location=" . $img_location . "' title = 'Interface " . $if_name[$key] . "'>
							</a>
						</form>
					  </div>";
			}
		}

		echo '
				</div>
			  </div>';
	}
}

mysqli_close($connection);

?>
