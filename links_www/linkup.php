<?php

$list = "NoPasswd";

include('configlinks.php');

include('functions/functions.thumbs.php');

require_once('api/Pocket_api.php');


$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';

/**
 * ===================================
 *
 * First your application needs "platform consumer key"
 * you can obtain it on http://getpocket.com/developer/apps/new
 * ""A Pocket consumer key looks like: 1234-abcd1234abcd1234abcd1234""
 *
 * Initialize class and set up consumer key and redirect uri
 *
 * you can specified your values
 * - inside Pocket_api.php file
 * - $pocket = new Pocket_api($consumer_key, $redirect_uri);
 * - $pocket->consumer_key = "YOUR_KEY";
 */
$pocket = new Pocket_api($pocket_consumer_key, "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?p=authenticate");



/**
 * ===================================
 *
 * Check if curl functions are avaible
 */
if ($pocket->curl_check() === FALSE)
  {
    echo "curl functions are not avaible";
    exit(1);
  }

/**
 * ===================================
 *
 * Now we need to decide what action its needed. There are 3 possibility
 * - Authorization or Re-Authorization
 *  If we want to
 * - Authentication
 * authentication is 2-way proccess
 * first we need to obtain request token and with this token redirect user to pocket web site where he accept or reject our application
 * after he will be redirected back (riderect_uri) and authentication is continue
 * - Action
 *
 */


$p = ( ! empty($_GET['p'])) ? $_GET['p'] : "authorization";
switch ($p) {
case "authorization":

$obtain_request_token = $pocket->obtain_request_token();

if ($obtain_request_token === FALSE)
  {
    echo "curl failed";
    exit(1);
  }

if ($obtain_request_token['x-error'] == 1)
  {
    echo "Some problem with Pocket\n";
    echo "HTTP Status: " . $obtain_request_token['status'] . "\n";
    echo "X-Error(" . $obtain_request_token['x-error-code'] . "): " . $obtain_request_token['x-error-message'];
    exit(1);
  }

$pocket->redirect_uri = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?p=authenticate".urlencode("&")."request_token=" . $obtain_request_token['code'];


break;

case 'authenticate':

  $access_token = $pocket->obtain_access_token($_GET['request_token']);

break;

case 'deauthenticate':

  $access_token = $pocket->obtain_access_token($_GET['request_token']);

break;

}


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

    <script src="<?php echo $protocol ?>://platform.twitter.com/widgets.js" type="text/javascript"></script>

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
	        <li class="active"><a href="linkup.php">link-up</a></li>
	        <li><a href="settings.php">settings</a></li>
	      </ul>
	    </li>
          </ul>

        </div> <!--/.nav-collapse -->

      </div> <!--/.container -->
     </div> <!--/.END Fixed navbar -->



    <div class="container">

      <h2>NGT Link-up</h2>
       <hr>


      <h3><a target="_blank" href="http://www.getpocket.com"><img src="img/pocket_logo.png"></a></h3>

         <p>Connect to push new pocket posts into links. <br>
            Use the browser extensions and the mobile app for quick posting.</p>


<?php

switch ($p) {
case "authorization":

  #echo "Your request token is " . $obtain_request_token['code'] . "<br>";

echo '<a class="btn btn-default btn-xs" href="' . $pocket->user_authorization_link($obtain_request_token['code']) . '">Authorize pocket</a>';

$pocket->redirect_uri = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?p=deauthenticate".urlencode("&")."request_token=" . $obtain_request_token['code'];

echo '<a class="btn btn-default btn-xs" href="' . $pocket->user_authorization_link($obtain_request_token['code']) . '">De-Authorize pocket</a>';

break;

case 'authenticate':

  #echo "Your request token is " . $_GET['request_token'] . "<br>";

  if ($access_token === FALSE)
    {
      echo "curl faild";
      exit(1);
    }

  if ($access_token['x-error'] == 1)
    {
      echo "Some problem with Pocket\n";
      echo "HTTP Status: " . $access_token['status'] . "\n";
      echo "X-Error(" . $access_token['x-error-code'] . "): " . $access_token['x-error-message'];
      exit(1);
    }

  $conn = db_connect();
  db_query_pocket_insert($conn);

  echo "Pocket username ". $access_token['username'] ." authorized successfully<br>";


break;

case 'deauthenticate':

  if ($access_token === FALSE)
    {
      echo "curl faild";
      exit(1);
    }

  if ($access_token['x-error'] == 1)
    {
      echo "Some problem with Pocket\n";
      echo "HTTP Status: " . $access_token['status'] . "\n";
      echo "X-Error(" . $access_token['x-error-code'] . "): " . $access_token['x-error-message'];
      exit(1);
    }

  $conn = db_connect();
  db_query_pocket_delete($conn);

  echo "Pocket username ". $access_token['username'] ." de-authorized successfully<br>";

  

break;

}

?>

<br>

    <h3 style="font-family:"Helvetica Neue",Helvetica ;"><img style="height: 30px;" src="img/Twitter_logo_blue.png"> Twitter </h3>

<p>Follow to get the updates in your feed. <br>
   After following, mention @<?php echo $twitter_account; ?> to post.</p>

<a href="http://twitter.com/<?php echo $twitter_account; ?>"
   class="twitter-follow-button" data-show-count="false" data-width="120" data-align="left" data-button="grey" data-link-color="FFCC33">Follow @<?php echo $twitter_account; ?></a>


    </div> <!-- /container -->



    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="dist/js/bootstrap.min.js"></script>
    <script src="js/getBadges.ajax.js"></script>


  </body>
</html>


<?php

} else {

    header("Location: index.php"); /* Redirect browser */
    exit();

  }



 
?>



