<?php

$list = "NoPasswd";

include('configlinks.php');

include('functions/functions.thumbs.php');


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


	if (isset($_POST[ 'submit_settings' ]) ) {
	        
	  if ( isset($_POST[ 'noInfoTxtPost' ]) ) {
	       $mynoInfoTxt = $_POST[ 'noInfoTxtPost' ];
	       setcookie("noInfoTxt", $mynoInfoTxt, time()+(60*60*24*365), "/");
	    } else {
	      $mynoInfoTxt = "off";
	      setcookie("noInfoTxt", $mynoInfoTxt, time()+(60*60*24*365), "/");
	    }
	
	#	echo "post: :";
	#	echo $_POST['noInfoTxtPost'];
	#    echo "\n";
	#
	#	echo "post: :";
	#	echo $_POST['MaxResultsPost'];
	
	
	} elseif (isset($_COOKIE['noInfoTxt'])){
	        $mynoInfoTxt = $_COOKIE["noInfoTxt"];
	#	echo "cookie: ";
	#	echo $_COOKIE["noInfoTxt"];
	
	
	} else {
	        $mynoInfoTxt = "on";
	}
	
	
	if (isset($_POST[ 'submit_settings' ]) ) {
	
	  if ( isset($_POST[ 'noAddUtubePost' ]) ) {
	    $mynoAddUtube = $_POST[ 'noAddUtubePost' ];
	       setcookie("noAddUtube", $mynoAddUtube, time()+(60*60*24*365), "/");
	    } else {
	      $mynoAddUtube = "off";
	      setcookie("noAddUtube", $mynoAddUtube, time()+(60*60*24*365), "/");
	
	    }
	
	} elseif (isset($_COOKIE['noAddUtube'])){
	        $mynoAddUtube = $_COOKIE["noAddUtube"];
	
	} else {
	        $mynoAddUtube = "off";
	}


	if (isset($_POST[ 'submit_settings' ]) ) {
	
	  if ( isset($_POST[ 'GifAutoPlayPost' ]) ) {
	    $myGifAutoPlay = $_POST[ 'GifAutoPlayPost' ];
	       setcookie("GifAutoPlay", $myGifAutoPlay, time()+(60*60*24*365), "/");
	    } else {
	      $myGifAutoPlay = "off";
	      setcookie("GifAutoPlay", $myGifAutoPlay, time()+(60*60*24*365), "/");
	
	    }
	
	} elseif (isset($_COOKIE['GifAutoPlay'])){
	        $myGifAutoPlay = $_COOKIE["GifAutoPlay"];
	
	} else {
	        $myGifAutoPlay = "off";
	}
	
	
	if (isset($_POST[ 'submit_settings' ]) ) {
	
	  if ( isset($_POST[ 'hideImgOnLightboxOpenPost' ]) ) {
	    $myhideImgOnLightboxOpen = $_POST[ 'hideImgOnLightboxOpenPost' ];
	       setcookie("hideImgOnLightboxOpen", $myhideImgOnLightboxOpen, time()+(60*60*24*365), "/");
	    } else {
	      $myhideImgOnLightboxOpen = "off";
	      setcookie("hideImgOnLightboxOpen", $myhideImgOnLightboxOpen, time()+(60*60*24*365), "/");
	
	    }
	
	} elseif (isset($_COOKIE['hideImgOnLightboxOpen'])){
	        $myhideImgOnLightboxOpen = $_COOKIE["hideImgOnLightboxOpen"];
	
	} else {
	        $myhideImgOnLightboxOpen = "off";
	}
	
	
	if ( isset($_POST[ 'submit_settings' ]) ) {
	
	  if ( isset($_POST[ 'HideCachedImgs' ]) ) {
	    $myHideCachedImgs = 1;
	    setcookie("HideCachedImgs", $myHideCachedImgs, time()+(60*60*24*365), "/");
	  } else {
	    $myHideCachedImgs = 0;
	    setcookie("HideCachedImgs", $myHideCachedImgs, time()+(60*60*24*365), "/");
	  }
	
	} elseif (isset($_COOKIE['HideCachedImgs'])){
	  $myHideCachedImgs = $_COOKIE["HideCachedImgs"];
	
	} else {
	  $myHideCachedImgs = 0;
	
	}
	
	
	if ( isset($_POST[ 'submit_settings' ])  ) {
	
	   if ( isset($_POST[ 'HideEmbed' ]) ) {
	     $myHideEmbed = 1;
	     setcookie("HideEmbed", $myHideEmbed, time()+(60*60*24*365), "/");
	   } else {
	     $myHideEmbed = 0;
	     setcookie("HideEmbed", $myHideEmbed, time()+(60*60*24*365), "/");
	   }
	
	} elseif (isset($_COOKIE['HideEmbed'])){
	  $myHideEmbed = $_COOKIE["HideEmbed"];
	
	} else {
	  $myHideEmbed = 0;
	
	}

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
    <meta name="referrer" content="never">
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
      <?php if ($thumbsEnable==1) { print "<li><a href=\"thumbs.php\">thumbs <span id=\"thumbsBadge\" class=\"badge\"></span></a></li>\n"; } ?>
            <li><a href="vids.php">vids <span id="vidsBadge" class="badge"></span></a></li>
	    <li class="dropdown">
	      <a href="#" class="dropdown-toggle" data-toggle="dropdown">more <b class="caret"></b></a>
	      <ul class="dropdown-menu">
	        <li><a href="stats.php">stats</a></li>
	        <?php if ($LinkupEnable==1) { print "<li><a href=\"linkup.php\">link-up</a></li>\n"; } ?>
	        <li class="active"><a href="settings.php">settings</a></li>
	      </ul>
	    </li>
          </ul>

        </div> <!--/.nav-collapse -->

      </div> <!--/.container -->
     </div> <!--/.END Fixed navbar -->



    <div class="container">

      <h2>NGT Settings</h2>
       <hr>

       <h3>links</h3>

	<form role="form" action="settings.php" method="POST">
	  <div class="checkbox">
	    <label>
	      <input type="checkbox" name="HideEmbed"
                <?php
			      if ( $myHideEmbed == 1 )
                                {
				  print( " checked=\"checked\" " );
                                }

                ?>   > hide vids
	    </label>
	  </div>
	  <div class="checkbox">
	    <label>
	      <input type="checkbox" name="HideCachedImgs"
                 <?php
			      if ( $myHideCachedImgs == 1 )
                                {
				  print( " checked=\"checked\" " );
                                }

                ?>   > hide thumbs
	    </label>
	  </div>


       <h3>thumbs</h3>

	  <div class="checkbox">
	    <label>
	      <input type="checkbox" name="noAddUtubePost"
                <?php
			      if ( $mynoAddUtube == "on" )
                                {
				  print( " checked=\"checked\" " );
                                }

                ?>   > add vids
	    </label>
	  </div>

	  <div class="checkbox">
	    <label>
	      <input type="checkbox" name="GifAutoPlayPost"
                <?php
			      if ( $myGifAutoPlay == "on" )
                                {
				  print( " checked=\"checked\" " );
                                }

                ?>   > auto-play animated GIFs
	    </label>
	  </div>

	  <div class="checkbox">
	    <label>
	      <input type="checkbox" name="hideImgOnLightboxOpenPost"
                <?php
			      if ( $myhideImgOnLightboxOpen == "on" )
                                {
				  print( " checked=\"checked\" " );
                                }

                ?>   > hide gallery on lightbox open
	    </label>
	  </div>

       <h3>thumbs & vids</h3>
	  <div class="checkbox">
	    <label>
	      <input type="checkbox" name="noInfoTxtPost"
                 <?php
			      if ( $mynoInfoTxt == "on" )
                                {
				  print( " checked=\"checked\" " );
                                }

                ?>   > add info text
	    </label>
	  </div>
	  <button type="submit" name="submit_settings" class="btn btn-default">Save Settings</button>
	</form>


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



