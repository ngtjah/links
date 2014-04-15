<?php

$list = "NoPasswd";

include('configlinks.php');

include('functions/functions.thumbs.php');


    /* set/check cookie for site password */
    if (isset($_COOKIE["SitePasswd"])) {
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


	#Search POST and URL vars
	$partialurl = isset($_POST["partialurl"]) ? "%" . $_POST["partialurl"] . "%" : '%';
	$newer = isset($_GET["newer"]) ? $_GET["newer"] : 0;

        $older = isset($_POST["older"]) ? $_POST["older"] : '0';
        $older = isset($_GET["older"]) ? $_GET["older"] : $older;

	
	$myMaxResults = "25";
	
	#Settings
	$myHideCachedImgs = isset($_COOKIE['HideCachedImgs']) ? $_COOKIE["HideCachedImgs"] : 0;
	
	$myHideEmbed = isset($_COOKIE['HideEmbed']) ? $_COOKIE["HideEmbed"] : 0;
	
	$mynoInfoTxt = isset($_COOKIE['noInfoTxt']) ? $_COOKIE["noInfoTxt"] : 'on';
	
	$mynoAddUtube = isset($_COOKIE['noAddUtube']) ? $_COOKIE["noAddUtube"]: 'off';
	
	$myUtubeSQL = ($mynoAddUtube == "on") ? "OR site like '%youtube.com%' OR site like '%vimeo.com%'" : "";
	
	
	
	#Search Stuff
	if (isset( $_GET["search"] ))
	   $partialurl = urldecode($_GET["search"]);
	
	$partialurl_clean = preg_replace('/^\%/', '', $partialurl);
	$partialurl_clean = preg_replace('/\%$/', '', $partialurl_clean);
	
	
	  $conn = db_connect();
	  list ($rows, $myid, $dates, $announcers, $urls, $types, $totalurls, $filenames, $twidths, $theights, $titles) = db_query_thumbs($conn);
	
	
	#Prev/Next
	$oldestid = end($myid);
	$newestid = $myid[0];
	
	
	/* Last Date Tracker */
	
	$lastdate = isset($_COOKIE["ThumbsLastDate"]) ? $_COOKIE["ThumbsLastDate"] : '';
		
	if ( !isset( $_POST[ 'partialurl' ] ) && $newer == 0 && $older == 0 ) {
	
	        #Grab the oldest date
	        $maxdate = $dates[0];
		#$maxdate = max(array_values( $dates ));
	
		$lastdatetime = strtotime($lastdate);
		$maxdatetime = strtotime($maxdate);
	
	#	echo $lastdate . " " . $maxdate . "\n";
	
	#	if ($lastdatetime < $maxdatetime) {
		    setcookie("ThumbsLastDate", $maxdate, time()+(60*60*24*365), "/");
		    $lastdate = $maxdate;
	#	}
	
	}
	
	$lastdateThumbs = isset($_COOKIE['ThumbsLastDate']) ? $lastdate : '';
	
	$lastdateUtube = isset($_COOKIE['UtubeLastDate']) ? $_COOKIE["UtubeLastDate"] : '';

} #list passwd
	


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

    <!-- Bootstrap Image Gallery -->
    <link rel="stylesheet" href="gallery/css/blueimp-gallery.min.css">
    <link rel="stylesheet" href="css/bootstrap-image-gallery.min.css">

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
  </head>

  <body>

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
          <ul class="nav navbar-nav navbar-pills">
            <li><a href="index.php">links <span id="linksBadge" class="badge"></span></a></li>
            <li class="active"><a href="thumbs.php">thumbs <span id="thumbsBadge" class="badge"></span></a></li>
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

           <noscript>
           <div id="paging-buttons" class="btn-toolbar nav navbar-nav btn-group-md">
              <a href="thumbs.php?newer=<?php echo $newestid . "&search=" . urlencode($partialurl); ?>">
                <button type="button" class="btn btn-default prev navbar-btn">
                  <i class="glyphicon glyphicon-chevron-left"></i>
                </button></a>
              <a href="thumbs.php?older=<?php echo $oldestid . "&search=" . urlencode($partialurl); ?>">
                <button type="button" class="btn btn-primary next navbar-btn">
                  <i class="glyphicon glyphicon-chevron-right"></i>
                </button>
              </a>
           </div> <!--/.btn-toolbar -->
           </noscript>

           <div class="nav navbar-nav">
           	<form class="navbar-form" role="search" method="POST" name="myform" action="thumbs.php">
           	  <div class="form-group">
           	    <input type="text" class="form-control" placeholder="Search" name="partialurl" value="<?php echo $partialurl_clean; ?>">
           	  </div>
           	  <button type="submit" class="btn btn-default hidden">Submit</button>
           	</form> 
           </div>

          </div> <!-- /.pull-right -->

        </div> <!--/.nav-collapse -->

      </div> <!--/.container -->
     </div> <!--/.END Fixed navbar -->

	<!-- The Bootstrap Image Gallery lightbox, should be a child element of the document body -->
	<div id="blueimp-gallery" class="blueimp-gallery">
	    <!-- The container for the modal slides -->
	    <div class="slides"></div>
	    <!-- Controls for the borderless lightbox -->
	    <h3 class="title"></h3>
	    <a class="prev">‹</a>
	    <a class="next">›</a>
	    <a class="close">×</a>
	    <a class="play-pause"></a>
	    <ol class="indicator"></ol>
	    <!-- The modal dialog, which will be used to wrap the lightbox content -->
	    <div class="modal fade">
	        <div class="modal-dialog">
	            <div class="modal-content">
	                <div class="modal-header">
	                    <button type="button" class="close" aria-hidden="true">&times;</button>
	                    <h4 class="modal-title"></h4>
	                </div>
	                <div class="modal-body next"></div>
                        <p class="description"></p> <!-- image description in lightbox -->
	                <div class="modal-footer">
	                    <button type="button" class="btn btn-default pull-left prev">
	                        <i class="glyphicon glyphicon-chevron-left"></i>
	                        Previous
	                    </button>

                            <button type="button" class="btn btn-default" onclick="lightboxbigger()">
                                Lightbox Size
                            </button>

	                    <button type="button" class="btn btn-primary next">
	                        Next
	                        <i class="glyphicon glyphicon-chevron-right"></i>
	                    </button>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>

        <!-- End of The Bootstrap Image Gallery lightbox -->

	<div class="container container-main"> 
	
		<!-- The container for bootstrap gallery images and videos -->
		<div id="links" class="links">

			<?php
			
			for ($i=0; $i<$rows; $i++) {
			
			    db_display($i);
			
			}
			
			?>

		</div> <!-- /links -->

                <div class="loading" id="loading">Loading please wait...</div>
                <div class="loading" id="nomoreresults">Out of Results</div>
	
	</div <!-- /container -->

    <!-- Hidden Var -->
    <input type="hidden" id="older" data-val="<?php echo $oldestid; ?>" />


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="dist/js/bootstrap.min.js"></script>

    <script src="js/getBadges.ajax.js"></script>
    <script src="js/ngtr.gallery.js"></script>
    <script src="js/ngtr.js"></script>

     <!-- Bootstrap Image Gallery -->
     <script src="gallery/js/jquery.blueimp-gallery.min.js"></script>
     <script src="js/bootstrap-image-gallery.min.js"></script>

     <script src="js/scroll.gallery.js"></script>


  </body>
</html>


<?php

	 } else {
 
  header("Location: index.php"); /* Redirect browser */
  exit();

    }

?>