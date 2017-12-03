<?php

//Finding path to 'db.conf'
	$cpath = __FILE__;
	$split_arr = explode('/', $cpath);
	array_splice($split_arr, -2, count($split_arr), array('db.conf'));
	$real_path = implode('/', $split_arr);
	
	$file_handle = fopen($real_path, "r") or die("Unable to open file!");

//Getting one line at a time until end of file (EOF) is reached
	while(!feof($file_handle))
	{
		$line = fgets($file_handle);
		$tline = trim($line);
	  
		if(substr($tline, 0, 1) === '$')
		{
			$variable_line = explode('=', $tline);
			$variable = trim($variable_line[0]);
			$value = explode('"', $variable_line[1]);
			
			if($variable === '$host')
			{
				$host = trim($value[1]);
			}
			
			elseif($variable === '$port')
			{
				$port = trim($value[1]);
			}
			
			elseif($variable === '$database')
			{
				$database = trim($value[1]);
			}
			
			elseif($variable === '$username')
			{
				$username = trim($value[1]);
			}
			
			else
			{
				$password = trim($value[1]);
			}
		}
		
		else
		{
			continue;
		}
	}
	
	fclose($file_handle);
//print "\n" . $host . "\n" . $port . "\n" . $database . "\n" . $username . "\n" . $password . "\n";
?>
