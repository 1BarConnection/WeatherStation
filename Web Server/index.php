<?php
header("Refresh: 60");
$servername = "localhost";

// REPLACE with your Database name
$dbname = "oxxxxxxxx_weather";
// REPLACE with Database user
$username = "oxxxxxxxx_admin_weather";
// REPLACE with Database user password
$password = "xxxxxxxxxxxxxxxxxxxxxxxx";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT id, value1, value2, value3, value4, value5, value6, reading_time FROM Sensor order by reading_time desc limit 80";

$result = $conn->query($sql);

while ($data = $result->fetch_assoc()){
    $sensor_data[] = $data;
}

$readings_time = array_column($sensor_data, 'reading_time');

// ******* Uncomment to convert readings time array to your timezone ********
$i = 0;
foreach ($readings_time as $reading){
    // Uncomment to set timezone to - 1 hour (you can change 1 to any number)
    //$readings_time[$i] = date("Y-m-d H:i:s", strtotime("$reading - 1 hours"));
    // Uncomment to set timezone to + 4 hours (you can change 4 to any number)
    $readings_time[$i] = date("Y-m-d H:i:s", strtotime("$reading + 0 hours"));
    $i += 1;
}

$value1 = json_encode(array_reverse(array_column($sensor_data, 'value1')), JSON_NUMERIC_CHECK);
$value2 = json_encode(array_reverse(array_column($sensor_data, 'value2')), JSON_NUMERIC_CHECK);
$value3 = json_encode(array_reverse(array_column($sensor_data, 'value3')), JSON_NUMERIC_CHECK);
$value4 = json_encode(array_reverse(array_column($sensor_data, 'value4')), JSON_NUMERIC_CHECK);
$value5 = json_encode(array_reverse(array_column($sensor_data, 'value5')), JSON_NUMERIC_CHECK);
$value6 = json_encode(array_reverse(array_column($sensor_data, 'value6')), JSON_NUMERIC_CHECK);
$reading_time = json_encode(array_reverse($readings_time), JSON_NUMERIC_CHECK);

$lvalue1 = reset(array_column($sensor_data, 'value1'));
$lvalue2 = reset(array_column($sensor_data, 'value2'));
$lvalue3 = reset(array_column($sensor_data, 'value3'));
$lvalue4 = reset(array_column($sensor_data, 'value4'));
$lvalue5 = reset(array_column($sensor_data, 'value5'));
$lvalue6 = reset(array_column($sensor_data, 'value6'));
$lreading_time = reset(array_column($sensor_data, 'reading_time'));
$lreading_time = date("d.m.Y, H:i", strtotime("$lreading_time + 0 hours"));

/*echo $value1;
echo $value2;
echo $value3;
echo $value4;
echo $value5;
echo $value6;
echo $reading_time;*/

$result->free();
$conn->close();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Solar Weather Station Project">
    <meta name="author" content="1BC">
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="manifest" href="img/site.webmanifest">

    <title>Weather</title>
	
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/sticky-footer-navbar/">

    <!-- Bootstrap core CSS -->
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="https://getbootstrap.com/docs/4.1/examples/sticky-footer-navbar/sticky-footer-navbar.css" rel="stylesheet">
    
    <script src="https://code.highcharts.com/highcharts.js"></script>
	
	<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    
    <style>
	.mySlides {display:none;}
    .center {
      text-align: center;
    }
    img {
      display: inline-block;
      margin-left: auto;
      margin-right: auto;
      width:90%;
    }
    </style>
    <style>
    .dbox {
    position: relative;
    background: rgb(255, 86, 65);
    background: -moz-linear-gradient(top, rgba(255, 86, 65, 1) 0%, rgba(253, 50, 97, 1) 100%);
    background: -webkit-linear-gradient(top, rgba(255, 86, 65, 1) 0%, rgba(253, 50, 97, 1) 100%);
    background: linear-gradient(to bottom, rgba(255, 86, 65, 1) 0%, rgba(253, 50, 97, 1) 100%);
    filter: progid: DXImageTransform.Microsoft.gradient( startColorstr='#ff5641', endColorstr='#fd3261', GradientType=0);
    border-radius: 4px;
    text-align: center;
    margin: 10px 0 50px;
	}
	.dbox__icon {
		position: absolute;
		transform: translateY(-50%) translateX(-50%);
		left: 50%;
	}
	.dbox__icon:before {
		width: 75px;
		height: 75px;
		position: absolute;
		background: #fda299;
		background: rgba(253, 162, 153, 0.34);
		content: '';
		border-radius: 50%;
		left: -17px;
		top: -17px;
		z-index: -2;
	}
	.dbox__icon:after {
		width: 60px;
		height: 60px;
		position: absolute;
		background: #f79489;
		background: rgba(247, 148, 137, 0.91);
		content: '';
		border-radius: 50%;
		left: -10px;
		top: -10px;
		z-index: -1;
	}
	.dbox__icon > i {
		background: #ff5444;
		border-radius: 50%;
		line-height: 40px;
		color: #FFF;
		width: 40px;
		height: 40px;
		font-size:22px;
	}
	.dbox__body {
		padding: 50px 20px;
	}
	.dbox__count {
		display: block;
		font-size: 30px;
		color: #FFF;
		font-weight: 300;
	}
	.dbox__title {
		font-size: 13px;
		color: #FFF;
		color: rgba(255, 255, 255, 0.81);
	}
	.dbox__action {
		transform: translateY(-50%) translateX(-50%);
		position: absolute;
		left: 50%;
	}
	.dbox__action__btn {
		border: none;
		background: #FFF;
		border-radius: 19px;
		padding: 7px 16px;
		text-transform: uppercase;
		font-weight: 500;
		font-size: 11px;
		letter-spacing: .5px;
		color: #003e85;
		box-shadow: 0 3px 5px #d4d4d4;
	}
	.dbox--color-2 {
		background: rgb(252, 190, 27);
		background: -moz-linear-gradient(top, rgba(235, 220, 2, 1) 1%, rgba(150, 120, 72, 1) 99%);
		background: -webkit-linear-gradient(top, rgba(235, 220, 2, 1) 1%, rgba(150, 120, 72, 1) 99%);
		background: linear-gradient(to bottom, rgba(235, 220, 2, 1) 1%, rgba(150, 120, 72, 1) 99%);
		filter: progid: DXImageTransform.Microsoft.gradient( startColorstr='#fcbe1b', endColorstr='#f8c948', GradientType=0);
	}
	.dbox--color-2 .dbox__icon:after {
		background: #fee036;
		background: rgba(254, 224, 54, 0.61);
	}
	.dbox--color-2 .dbox__icon:before {
		background: #fee036;
		background: rgba(254, 224, 54, 0.64);
	}
	.dbox--color-2 .dbox__icon > i {
		background: #ebcc02;
	}
	.dbox--color-3 {
		background: rgb(183,71,247);
		background: -moz-linear-gradient(top, rgba(183,71,247,1) 0%, rgba(108,83,220,1) 100%);
		background: -webkit-linear-gradient(top, rgba(183,71,247,1) 0%,rgba(108,83,220,1) 100%);
		background: linear-gradient(to bottom, rgba(183,71,247,1) 0%,rgba(108,83,220,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b747f7', endColorstr='#6c53dc',GradientType=0 );
	}
	.dbox--color-3 .dbox__icon:after {
		background: #b446f5;
		background: rgba(180, 70, 245, 0.76);
	}
	.dbox--color-3 .dbox__icon:before {
		background: #e284ff;
		background: rgba(226, 132, 255, 0.66);
	}
	.dbox--color-3 .dbox__icon > i {
		background: #8150e4;
	}
	.dbox--color-4 {
		background: rgb(99,129,64);
		background: -moz-linear-gradient(top, rgba(122,171,16,1) 0%, rgba(108,183,190,1) 100%);
		background: -webkit-linear-gradient(top, rgba(122,171,16,1) 0%,rgba(108,813,190,1) 100%);
		background: linear-gradient(to bottom, rgba(122,171,16,1) 0%,rgba(108,183,190,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#47f75f', endColorstr='#197c2c',GradientType=0 );
	}
	.dbox--color-4 .dbox__icon:after {
		background: #7vvb11;
		background: rgba(122, 200, 34, 0.76);
	}
	.dbox--color-4 .dbox__icon:before {
		background: #7aab11;
		background: rgba(11, 92, 55, 0.46);
	}
	.dbox--color-4 .dbox__icon > i {
		background: #7aab11;
	}
	.dbox--color-5 {
		background: rgb(122,77,16);
		background: -moz-linear-gradient(top, rgba(0,153,255,1) 0%, rgba(55,77,255,1) 100%);
		background: -webkit-linear-gradient(top, rgba(0,153,255,1) 0%,rgba(55,77,255,1) 100%);
		background: linear-gradient(to bottom, rgba(0,153,255,1) 0%,rgba(55,77,255,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#57f75f', endColorstr='#297c2c',GradientType=0 );
	}
	.dbox--color-5 .dbox__icon:after {
		background: #8vvb11;
		background: rgba(88, 153, 255, 0.46);
	}
	.dbox--color-5 .dbox__icon:before {
		background: #8vvb11;
		background: rgba(55, 153, 255, 0.66);
	}
	.dbox--color-5 .dbox__icon > i {
		background: #0099ff;
	}
	.dbox--color-6 {
		background: rgb(122,77,16);
		background: -moz-linear-gradient(top, rgba(209, 155, 36,1) 0%, rgba(209, 99, 36,1) 100%);
		background: -webkit-linear-gradient(top, rgba(209, 155, 36,1) 0%,rgba(209, 99, 36,1) 100%);
		background: linear-gradient(to bottom, rgba(209, 155, 36,1) 0%,rgba(209, 99, 36,1) 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#57f75f', endColorstr='#297c2c',GradientType=0 );
	}
	.dbox--color-6 .dbox__icon:after {
		background: #d17524;
		background: rgba(209, 155, 36, 0.46);
	}
	.dbox--color-6 .dbox__icon:before {
		background: #d17524;
		background: rgba(209, 125, 36, 0.66);
	}
	.dbox--color-6 .dbox__icon > i {
		background: #d17524;
	}
	.center {
	  display: block;
	  margin-left: auto;
	  margin-right: auto;
	}
	.center2 {
	  display: block;
	  margin-left: auto;
	  margin-right: auto;
	  width: 65%;
	}
	/* Solid border */
	hr.solid {
	  border-top: 3px solid #bbb;
	  width: 50%;
	  margin-left: auto;
	  margin-right: auto;
	}
    </style>
    
  </head>

  <body>

    <header>
      <nav class="navbar navbar-expand-md navbar-dark navbar-static-top" style="background-color: #546f88;">
      <!-- Fixed navbar -->
        <a class="navbar-brand" href="#"><i class="fa fa-cloud"></i> <b>Weather Station</b></a>
      </nav>
    </header>
    
    <div class="container">
	<div class="row justify-content-md-center">
		<div class="col-md-3">
		<br>
			<div class="dbox dbox--color-1">
				<div class="dbox__icon">
					<i class="fa fa-thermometer"></i> 
				</div>
				<div class="dbox__body">
					<span class="dbox__count"><?php echo $lvalue1; ?>°C</span>
					<span class="dbox__title"><p><b>Temperature</b></p></span>
				</div>
				
				<div class="dbox__action">
				<form action="#chart-temperature">
					<button class="dbox__action__btn"><a href="#chart-temperature">Graphical view</a></button>
				</form>
				</div>				
			</div>
		</div>
		<div class="col-md-3">
		<br>
			<div class="dbox dbox--color-2">
				<div class="dbox__icon">
					<i class="fa fa-cloud"></i> 
				</div>
				<div class="dbox__body">
					<span class="dbox__count"><?php echo $lvalue2; ?>%</span>
					<span class="dbox__title"><p><b>Humidity</b></p></span>
				</div>
				
				<div class="dbox__action">
				<form action="#chart-humidity">
					<button class="dbox__action__btn"><a href="#chart-humidity">Graphical view</a></button>
				</form>
				</div>				
			</div>
		</div>
		<div class="col-md-3">
		<br>
			<div class="dbox dbox--color-3">
				<div class="dbox__icon">
					<i class="fa fa-tachometer"></i>
				</div>
				<div class="dbox__body">
					<span class="dbox__count"><?php echo $lvalue3; ?>hPa</span>
					<span class="dbox__title"><p><b>Pressure</b></p></span>
				</div>
				
				<div class="dbox__action">
				<form action="#chart-pressure">
					<button class="dbox__action__btn"><a href="#chart-pressure">Graphical view</a></button>
				</form>
				</div>				
			</div>
		</div>
		<div class="col-md-3">
		<br>
			<div class="dbox dbox--color-4">
				<div class="dbox__icon">
					<i class="fa fa-sun-o"></i>
				</div>
				<div class="dbox__body">
					<span class="dbox__count"><?php echo $lvalue4; ?></span>
					<span class="dbox__title"><p><b>UV Index</b></p></span>
				</div>
				
				<div class="dbox__action">
			    <form action="#chart-uvindex">
					<button class="dbox__action__btn"><a href="#chart-uvindex">Graphical view</a></button>
			    </form>
				</div>				
			</div>
		</div>
		<div class="col-md-3">
		<br>
			<div class="dbox dbox--color-5">
				<div class="dbox__icon">
					<i class="fa fa-battery-full"></i>
				</div>
				<div class="dbox__body">
					<span class="dbox__count"><?php if ($lvalue5 != "-") {
													  echo $lvalue5 . "%";
													} else {
													  echo "No data";
													} ?></span>
					<span class="dbox__title"><p><b>Battery</b></p></span>
				</div>
				
				<div class="dbox__action">
			    <form action="#chart-battery">
					<button class="dbox__action__btn"><a href="#chart-battery">Graphical view</a></button>
			    </form>
				</div>				
			</div>
		</div>
		<div class="col-md-3">
		<br>
			<div class="dbox dbox--color-6">
				<div class="dbox__icon">
					<i class="fa fa-arrow-up"></i>
				</div>
				<div class="dbox__body">
					<span class="dbox__count"><?php echo $lvalue6; ?>m</span>
					<span class="dbox__title"><p><b>Altitude</b></p></span>
				</div>
				
				<div class="dbox__action">
			    <form action="#chart-altitude">
			    </form>
				</div>				
			</div>
		</div>
	</div>
	<br>
    <p style="text-align:center">Newest data record: <?php echo $lreading_time; ?></p>
	<br>
	<hr class="solid">
	<!-- START OF IMAGE SLIDER -->
	  <div class="w3-content w3-display-container">
	  <img class="mySlides" src="img/weather_station_transparent_v32.png" style="width:100%">
	  <img class="mySlides" src="img/weather_station_transparent_wifi_v2.png" style="width:100%">

	  <button class="w3-button w3-black w3-display-left" onclick="plusDivs(-1)">&#10094;</button>
	  <button class="w3-button w3-black w3-display-right" onclick="plusDivs(1)">&#10095;</button>
	</div>

	<script>
	var slideIndex = 1;
	showDivs(slideIndex);

	function plusDivs(n) {
	  showDivs(slideIndex += n);
	}

	function showDivs(n) {
	  var i;
	  var x = document.getElementsByClassName("mySlides");
	  if (n > x.length) {slideIndex = 1}
	  if (n < 1) {slideIndex = x.length}
	  for (i = 0; i < x.length; i++) {
		x[i].style.display = "none";  
	  }
	  x[slideIndex-1].style.display = "block";  
	}
	</script>
	<!-- END OF IMAGE SLIDER -->
	</div>

    <!-- Begin page content -->
    <br>
	    <div class="jumbotron">
        <div id="chart-temperature" class="jumbotron"></div>
        <div id="chart-humidity" class="jumbotron"></div>
        <div id="chart-pressure" class="jumbotron"></div>
		<div id="chart-uvindex" class="jumbotron"></div>
		<div id="chart-battery" class="jumbotron"></div>
		</div>
        <script>
        
        var value1 = <?php echo $value1; ?>;
        var value2 = <?php echo $value2; ?>;
        var value3 = <?php echo $value3; ?>;
		var value4 = <?php echo $value4; ?>;
		var value5 = <?php echo $value5; ?>;
        var reading_time = <?php echo $reading_time; ?>;
        
        var chartT = new Highcharts.Chart({
          chart:{ renderTo : 'chart-temperature'},
          title: { text: 'Temperature' },
          series: [{
			name: 'Temperature',
            showInLegend: false,
            data: value1
          }],
          plotOptions: {
            line: { animation: true,
              dataLabels: { enabled: true }
            },
            series: { color: '#F79489', lineWidth: 6 }
          },
          xAxis: { 
            type: 'datetime',
            categories: reading_time
          },
          yAxis: {
            title: { text: 'Temperature (Celsius)' }
          },
          credits: { enabled: false }
        });
        
        var chartH = new Highcharts.Chart({
          chart:{ renderTo:'chart-humidity' },
          title: { text: 'Humidity' },
          series: [{
			name: 'Humidity',
            showInLegend: false,
            data: value2
          }],
          plotOptions: {
            line: { animation: true,
              dataLabels: { enabled: true }
            },
            series: { color: '#FCB320', lineWidth: 6 }
          },
          xAxis: {
            type: 'datetime',
            //dateTimeLabelFormats: { second: '%H:%M:%S' },
            categories: reading_time
          },
          yAxis: {
            title: { text: 'Humidity (%)' }
          },
          credits: { enabled: false }
        });
        
        
        var chartP = new Highcharts.Chart({
          chart:{ renderTo:'chart-pressure' },
          title: { text: 'Pressure' },
          series: [{
			name: 'Pressure',
            showInLegend: false,
            data: value3
          }],
          plotOptions: {
            line: { animation: true,
              dataLabels: { enabled: true }
            },
            series: { color: '#b446f5', lineWidth: 6 }
          },
          xAxis: {
            type: 'datetime',
            categories: reading_time
          },
          yAxis: {
            title: { text: 'Pressure (hPa)' }
          },
          credits: { enabled: false }
        });
		
		var chartU = new Highcharts.Chart({
          chart:{ renderTo:'chart-uvindex' },
          title: { text: 'UV Index' },
          series: [{
			name: 'UV Index',
            showInLegend: false,
            data: value4
          }],
          plotOptions: {
            line: { animation: true,
              dataLabels: { enabled: true }
            },
            series: { color: '#7aab11', lineWidth: 6 }
          },
          xAxis: {
            type: 'datetime',
            categories: reading_time
          },
          yAxis: {
            title: { text: 'UV Index' }
          },
          credits: { enabled: false }
        });
		
		var chartB = new Highcharts.Chart({
          chart:{ renderTo:'chart-battery' },
          title: { text: 'Battery' },
          series: [{
			name: 'Battery',
            showInLegend: false,
            data: value5
          }],
          plotOptions: {
            line: { animation: true,
              dataLabels: { enabled: true }
            },
            series: { color: '#0099ff', lineWidth: 6 }
          },
          xAxis: {
            type: 'datetime',
            categories: reading_time
          },
          yAxis: {
            title: { text: 'Battery (%)' }
          },
          credits: { enabled: false }
        });
        
        </script>
		
		<img src="img/weather_station_transparent_v4.png" alt="Weather station" class="center2">
		<br><br>
		
    </main>
    <br><br>
    
    <footer class="footer font-small" style="background-color: #546f88;">
      <div class="container text-center" style="color: #FFFFFF;">© 2021 |
        <a href="https://www.youtube.com/channel/UCIP_eFtwZDB8dqesjWkk9Iw" style="color: #FFFFFF;"> 1BC</a>
		<a style="color: #FFFFFF;"> | version 0.0.5</a>
      </div>
    </footer>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="https://getbootstrap.com/docs/4.0/assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    <script src="https://getbootstrap.com/docs/4.0/assets/js/vendor/popper.min.js"></script>
    <script src="https://getbootstrap.com/docs/4.0/dist/js/bootstrap.min.js"></script>
  </body>
</html>