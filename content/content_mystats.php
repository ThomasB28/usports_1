
<html>
    <?php
 $tab=MatchBasket::getStats($dbh, '1', 'thomas');
 print_r($tab);
 $pts = (int)$tab['0']['Points'];
 $rbds = (int)$tab['0']['Rebonds'];
 $ftes = (int)$tab['0']['Fautes'];
 $asst = (int)$tab['0']['Assists'];
 $data="[$pts,$rbds,$ftes,$asst]";
 print($data);
 ?>
 
	<head>
		<title>Radar Chart</title>
		<script src="Chart.js-master/Chart.js"></script>
		<meta name = "viewport" content = "initial-scale = 1, user-scalable = no">
		<style>
			canvas{
			}
		</style>
	</head>
	<body>
		<div style="width:30%">
			<canvas id="canvas" height="450" width="450"></canvas>
		</div>


	<script>
	var radarChartData = {
		labels: ["Points", "Rebonds", "Fautes", "Assists"],
		datasets: [
			{
                            
				label: "My First dataset",
				fillColor: "rgba(220,220,220,0.2)",
				strokeColor: "rgba(220,220,220,1)",
				pointColor: "rgba(220,220,220,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(220,220,220,1)",
				data: <?php echo $data;?>
			},
			{
				label: "My Second dataset",
				fillColor: "rgba(151,187,205,0.2)",
				strokeColor: "rgba(151,187,205,1)",
				pointColor: "rgba(151,187,205,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(151,187,205,1)",
				data: [28,48,40,19]
			}
		]
	};

	window.onload = function(){
		window.myRadar = new Chart(document.getElementById("canvas").getContext("2d")).Radar(radarChartData, {
			responsive: true
		});
	}

	</script>
	</body>
        
        
</html>
