<?php

$list = "NoPasswd";

include('configlinks.php');

include('functions/functions.index.php');

    /* set/check cookie for site password */

    if ( isset( $_POST[ 'SitePasswd' ] ) ) {
    
        $mySitePasswd = $_POST[ 'SitePasswd' ];
    
        if ($mySitePasswd == $TheSecretPasswd) {

    	  setcookie("SitePasswd", $mySitePasswd, time()+(60*60*24*365), "/");

    	  $list = "entire";

    	}
    
    } elseif (isset($_COOKIE['SitePasswd'])) {
    
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
    	die("Method undefined (allowed methods: random, entire)\n");
    
    
    if ($list != "NoPasswd") {


	#Search POST and URL vars
	$partialurl = isset($_POST["partialurl"]) ? "%" . $_POST["partialurl"] . "%" : '%';
	$newer = isset($_GET["newer"]) ? $_GET["newer"] : '0';
	$older = isset($_GET["older"]) ? $_GET["older"] : '0';
	
	
	#Max Results Dropdown POST and Cookies
	if ( isset( $_POST[ 'MaxResultsPost' ] ) ) {
	  $myMaxResults = $_POST[ 'MaxResultsPost' ];
	  setcookie("LinksMaxResults", $myMaxResults, time()+(60*60*24*365), "/");
	} elseif (isset($_COOKIE['LinksMaxResults'])){
	  $myMaxResults = $_COOKIE["LinksMaxResults"];
	} else {
	  $myMaxResults = "50";
	}
	
	
	#Settings
	$myHideCachedImgs = isset($_COOKIE['HideCachedImgs']) ? $_COOKIE["HideCachedImgs"] : 0;
	
	$myHideEmbed = isset($_COOKIE['HideEmbed']) ? $_COOKIE["HideEmbed"] : 0;
	
	$RemoveThumbsSQL = ($myHideCachedImgs == 1) ? "and filename is null " : "";
	$RemoveEmbedSQL = ($myHideEmbed == 1) ? "and site not like '%youtube.com%' and site not like '%vimeo.com%'" : "";
	
	
	#Search Stuff
	if ( isset( $_GET["search"] ) ) {
	  $partialurl = urldecode($_GET["search"]);
	}
	
	$partialurl_clean = preg_replace('/^\%/', '', $partialurl);
	$partialurl_clean = preg_replace('/\%$/', '', $partialurl_clean);


	$conn = db_connect();
	list($rows, $dates, $announcers, $urls, $types, $totalurls, $filenames, $titles, $categories, $thisID) = db_query($conn);


	#Prev/Next
	$oldestid = end($thisID);
	$newestid = $thisID[0];
	
	
	/* Last Date Tracker */
	$lastdate = isset($_COOKIE['SiteLastDate']) ? $_COOKIE["SiteLastDate"] : '';
	
	if ( !isset( $_POST[ 'partialurl' ] ) && $newer == 0 && $older == 0 ) {
	
	  #$maxdate = max(array_values( $dates ));
	  $maxdate = $dates[0];
	
	  $lastdatetime = strtotime($lastdate);
	  $maxdatetime = strtotime($maxdate);
	
	#Disabled because sometimes strange dates very far in the future might make it in here.
	#if ($lastdatetime < $maxdatetime) 
	    setcookie("SiteLastDate", $maxdate, time()+(60*60*24*365), "/");
	
	}
	
	
	$lastdateThumbs = isset($_COOKIE["ThumbsLastDate"]) ? $_COOKIE["ThumbsLastDate"] : '';
	
	$lastdateUtube = isset($_COOKIE["UtubeLastDate"]) ? $_COOKIE["UtubeLastDate"] : '';
	
}   #list


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
    <!-- <link href="grid.css" rel="stylesheet"> -->

    <!-- Custom styles for this template -->
    <link href="css/ngtr.css" rel="stylesheet">

        <script language="JavaScript">
	    function resubmit_all()
	    {
	      document.myform1.action="index.php?<?php echo $_SERVER['QUERY_STRING'] ?>";
	      document.myform1.submit();
	    }
        </script>

    <!-- Just for debugging purposes. Don''t actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

  <!-- SSL Alert
  <div class="container">
    <div class="alert alert-success fade" id="sslAlert">
	  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		Switch to the secure site? <a href="https://<?php print $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI] ?>" class="alert-link">Yes</a>&nbsp;
                                           <a href="https://<?php print $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI] ?>" class="alert-link">No</a>
    </div>
  </div>  -->

    <!-- Fixed navbar -->
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
          <ul class="nav navbar-nav nav-pills">
            <li class="active"><a href="index.php">links <span id="linksBadge" class="badge"></span></a></li>
      <?php if ($thumbsEnable==1) { print "<li><a href=\"thumbs.php\">thumbs <span id=\"thumbsBadge\" class=\"badge\"></span></a></li>\n"; } ?>
            <li><a href="vids.php">vids <span id="vidsBadge" class="badge"></span></a></li>
	    <li class="dropdown">
	      <a href="#" class="dropdown-toggle" data-toggle="dropdown">more <b class="caret"></b></a>
	      <ul class="dropdown-menu">
	        <li><a href="stats.php">stats</a></li>
	       <?php if ($LinkupEnable==1) { print "<li><a href=\"linkup.php\">link-up</a></li>\n"; } ?>
	        <li><a href="settings.php">settings</a></li>
	      </ul>
	    </li>
          </ul>

          <div class="pull-right">
           <div class="btn-toolbar nav navbar-nav btn-group-md">
              <a href="index.php?newer=<?php echo $newestid . "&search=" . urlencode($partialurl); ?>">
                <button type="button" class="btn btn-default prev navbar-btn">
                  <i class="glyphicon glyphicon-chevron-left"></i>
                </button></a>
              <a href="index.php?older=<?php echo $oldestid . "&search=" . urlencode($partialurl); ?>">
                <button type="button" class="btn btn-primary next navbar-btn">
                  <i class="glyphicon glyphicon-chevron-right"></i>
                </button>
              </a>
           </div> <!--/.btn-toolbar -->

           <div class="nav navbar-nav">
           	<form class="navbar-form" role="search" method="POST" name="myform" action="index.php">
           	  <div class="form-group">
           	    <input type="text" class="form-control" placeholder="Search" name="partialurl" value="<?php echo $partialurl_clean; ?>">
           	  </div>
           	  <button type="submit" class="btn btn-default hidden">Submit</button>
           	</form> 
           </div>

           <div class="nav navbar-nav">
               <form class="navbar-form" name="myform1" action="index.php" method="POST">
                 <div class="form-group">Results: 
		   <select class="form-control" name="MaxResultsPost" onchange="resubmit_all()">
			<?php
			      $myMaxResultsarray = array(25, 50, 75, 100, 200);
			
			  foreach ($myMaxResultsarray as $i => $value)
			    {
			      $thismaxresults = $myMaxResultsarray[$i];
			      if ( $thismaxresults == $myMaxResults )
				{
				  print( "<option selected>$thismaxresults</option>\n" );
				}
			      else
				{
				  print( "<option>$thismaxresults</option>\n" );
				}
			    }
                      ?>
                  </select>
                 </div>
               </form>
           </div>
          </div> <!-- /.pull-right -->

        </div> <!--/.nav-collapse -->

      </div> <!--/.container -->

     </div> <!--/.navbar -->

      <!-- END Fixed navbar -->

    <div class="container container-main">

<div class="table-responsive">

<table class="table table-condensed">

<?php
     for ($i=0; $i<$rows; $i++)
        db_display($i);

?>

</table>


</table> <!-- /table responsive -->

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

    db_shutdown();

} else {

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
    <link href="dist/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/ngtr.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/jumbotron-narrow.css" rel="stylesheet">

    <script language="JavaScript">
	    function resubmit_all()
	    {
	      document.myform1.action="index.php?<?php echo $_SERVER['QUERY_STRING'] ?>";
	      document.myform1.submit();
	    }
    </script>

    <!-- Just for debugging purposes. Don''t actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

  <div class="container">

    <div class="jumbotron">

     <h1><?php echo $site_name; ?></h1>

      <form class="form-signin" role="form" action="index.php" method="POST" name="myform">
        <h2 class="form-signin-heading">Enter Password PLZ</h2>
        <input type="password" class="form-control" name="SitePasswd" placeholder="Password" required autofocus>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Yes</button>
      </form>

    </div> <!-- /jumbotron -->

  </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="dist/js/bootstrap.min.js"></script>
  </body>
</html>







<?php


    }

?>