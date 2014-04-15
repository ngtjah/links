<?php

$list = "NoPasswd";

include('configlinks.php');

include('functions/functions.stats.php');


/* set/check cookie for site password */

if (isset($_COOKIE["SitePasswd"])){
        $mySitePasswd = $_COOKIE["SitePasswd"];

        if ($mySitePasswd == $TheSecretPasswd)
                $list = "entire";
        else
                $list = "NoPasswd";

} elseif ( $passwordEnable == 0 ) {

  $list = "entire";

} else {
        $list = "NoPasswd";
}


if ($list != "entire" && $list != "random" && $list != "NoPasswd")
	die("Method undefined.\n");


if ($list != "NoPasswd") {

  $conn = db_connect();
  $conn2 = db_connect();
  list ($rows, $dates, $counts) = db_query_stats($conn);

  #db_query_stats2($conn2);
  list ($rows2, $sites) = db_query_stats3($conn);
  list ($rows4, $announcers4, $counts4) = db_query_stats4($conn);

} #list Not NoPasswd


if ($list == "entire") {

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="favicon.ico">

    <title><?php echo $site_title; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/navbar-fixed-top.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <!-- <link href="css/grid.css" rel="stylesheet"> -->

    <!-- Custom styles for this template -->
    <!-- <link href="css/responsive-video.css" rel="stylesheet"> -->

    <!-- Custom styles for this template -->
    <link href="css/ngtr.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don''t actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
	  google.load("visualization", "1", {packages:["corechart"]});
	  google.setOnLoadCallback(drawChart);
	  function drawChart() {
	    var data = google.visualization.arrayToDataTable([

		      ['Date', 'Posts'],

			<?php
			
			$total_width = 1;
			
			for ($i=0; $i<$rows; $i++) {
			
				db_display_stats($i);
			
			}
			
			?>

		      ]);
	
	    var options = {
	    title: 'Posts Per Month'
	    };
	
	    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
	    chart.draw(data, options);
	  }
    </script>
    
   <!-- <script type="text/javascript">

	google.load("visualization", "1", {packages:["corechart"]});
	  google.setOnLoadCallback(drawChart1);
	  function drawChart1() {
	    var data = google.visualization.arrayToDataTable([


		      ['Year', 'test'],

			<?php
			
			#for ($i=0; $i<$rows; $i++) {
			
			#	db_display_stats2($i);
			
			#}
			
			?>

		      ]);


	    var options = {
	    title: 'Company Performance',
	    hAxis: {title: 'Year', titleTextStyle: {color: 'red'}}
	    };

	    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div2'));
	    chart.draw(data, options);
	  } 


    </script> -->

<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	  google.setOnLoadCallback(drawChart3);
	  function drawChart3() {
	    var data = google.visualization.arrayToDataTable([
		['Site', 'Count'],
			<?php

			  db_display_stats3();

			?>
		]);

	    var options = {
	    title: 'Top 20 Domains',
	    };

	    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div3'));
	    chart.draw(data, options);
	  }
</script>

<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	  google.setOnLoadCallback(drawChart4);
	  function drawChart4() {
	    var data = google.visualization.arrayToDataTable([
		['Nick', 'Count'],
			<?php

			for ($i=0; $i<15; $i++) {
			
				db_display_stats4($i);
			
			}
			
			?>
		]);

	    var options = {
	    title: 'Top 20 Users',
	    };

	    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div4'));
	    chart.draw(data, options);
	  }
</script>


  </head>

  <body>

    <!-- navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?php echo $site_name; ?></a>
        </div>

        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-pills">
            <li><a href="index.php">links <span id="linksBadge" class="badge"></span></a></li>
      <?php if ($thumbsEnable==1) { print "<li><a href=\"thumbs.php\">thumbs <span id=\"thumbsBadge\" class=\"badge\"></span></a></li>\n"; } ?>
            <li><a href="vids.php">vids <span id="vidsBadge" class="badge"></span></a></li>
	    <li class="dropdown">
	      <a href="#" class="dropdown-toggle" data-toggle="dropdown">more <b class="caret"></b></a>
	      <ul class="dropdown-menu">
	        <li class="active"><a href="stats.php">stats</a></li>
	        <?php if ($LinkupEnable==1) { print "<li><a href=\"linkup.php\">link-up</a></li>\n"; } ?>
	        <li><a href="settings.php">settings</a></li>
	      </ul>
	    </li>
          </ul>

        </div> <!--/.nav-collapse -->

      </div> <!--/.container -->
     </div> <!--/.END Fixed navbar -->



    <div class="container">

      <h2>NGT Stats</h2>
       <hr>


     <div id="chart_div" style="width: 960px; height: 500px;"></div>

     <div id="chart_div3" style="width: 960px; height: 500px;"></div>

     <div id="chart_div4" style="width: 960px; height: 500px;"></div>


    </div> <!-- /container -->



    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="dist/js/bootstrap.min.js"></script>
    <script src="js/getBadges.ajax.js"></script>
    <script src="js/ngtr.js"></script>


  </body>
</html>


<?php

} else {

    header("Location: index.php"); /* Redirect browser */
    exit();

}



 
?>



