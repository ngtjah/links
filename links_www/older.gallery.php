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


if ($list == "entire") {


	#Search POST and URL vars
	$partialurl = isset($_POST["partialurl"]) ? "%" . $_POST["partialurl"] . "%" : '%';
	$newer = isset($_GET["newer"]) ? $_GET["newer"] : 0;

        $older = isset($_POST["older"]) ? $_POST["older"] : '0';
        $older = isset($_GET["older"]) ? $_GET["older"] : $older;
	
	$myMaxResults = "25";
	
	#Settings
	$myHideCachedImgs = isset($_COOKIE['HideCachedImgs']) ? $_COOKIE["HideCachedImgs"] : 0;
	$myHideEmbed      = isset($_COOKIE['HideEmbed'])      ? $_COOKIE["HideEmbed"] : 0;
	$myGifAutoPlay    = isset($_COOKIE['GifAutoPlay'])    ? $_COOKIE["GifAutoPlay"]    : 'off';	
	$mynoInfoTxt      = isset($_COOKIE['noInfoTxt'])      ? $_COOKIE["noInfoTxt"] : 'on';
	$mynoAddUtube     = isset($_COOKIE['noAddUtube'])     ? $_COOKIE["noAddUtube"]: 'off';
	$myUtubeSQL       = ($mynoAddUtube == "on")           ? "OR site like '%youtube.com%' OR site like '%vimeo.com%'" : "";
	
	
        #Page Name
        if (isset( $_GET["p"] ))
	  $p = urldecode($_GET["p"]);

	
        $conn = db_connect();

        #Support both thumbs and vids via url var from ajax call
	if ($p == "vids") {

	  list ($rows, $myid, $dates, $announcers, $urls, $types, $totalurls, $filenames, $twidths, $theights, $titles) = db_query_vids($conn);

	} else {

          list ($rows, $myid, $dates, $announcers, $urls, $types, $totalurls, $filenames, $twidths, $theights, $titles) = db_query_thumbs($conn);

	}
	
	
	#Prev/Next
	$oldestid = end($myid);
	$newestid = $myid[0];
	

	$results = "";

	ob_start(); // Start output buffering

        #print "<div class=\"thumb\"> post: ";
        #print_r($_POST);
        #print "</div>\n";

	for ($i=0; $i<$rows; $i++) {

	  db_display($i);

	}

	$results = ob_get_clean(); // End buffering and clean up

        echo json_encode(
			 array("results" => $results,
			       "olderid"    => $oldestid)

                         );

	
} #list entire


 

?>