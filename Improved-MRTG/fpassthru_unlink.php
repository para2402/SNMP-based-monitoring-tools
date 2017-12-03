<?php

	header('Content-type: image/png');
	header('Content-length: '.filesize($_GET['location']));
	$file = fopen($_GET['location'], 'rb');
	
	if($file)
	{
		fpassthru($file);
	}
	
	unlink($_GET['location']);
	
?>
