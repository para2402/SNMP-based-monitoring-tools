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
				  <li role="presentation" class="active"><a href="#">Home</a></li>
				  <li role="presentation"><a href="#">Add Devices</a></li>
				  <li role="presentation"><a href="#">Remove Devices</a></li>
				</ul>	
			</div>


			<div class = "row" style = "margin: 0; height: 100%;">
				<h3 style = "text-align: center; color: green; padding-bottom: 10px">Index Page</h3>
			</div>
			
			
			<!--Status panel-->
			<div class = "row" style = "margin: 0; height: 100%;">
				<div class = "col-md-12" style = "height: 100%;">
						<?php include "./home_table.php"; ?>
				</div>
			</div>
			
			<div class = "row" style = "margin: 5px;"></div>
			
		</div>
	</body>
	
	<footer><b>Name:</b> Sesha Sai Srinivas Jayapala Vemula	&nbsp |	&nbsp <b>P.No:</b> 9406232935</footer>
	
</html>
