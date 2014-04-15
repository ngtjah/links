<?php

$list = "NoPasswd";

include('configlinks.php');

include('functions/functions.index.php');

/* set/check cookie for site password */
if (isset($_COOKIE['SitePasswd'])){
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

if ($list == "entire") {


	#Search POST and URL vars
	$partialurl = isset($_POST["partialurl"]) ? "%" . $_POST["partialurl"] . "%" : '%';
	$newer = isset($_GET["newer"]) ? $_GET["newer"] : '0';

        $older = isset($_POST["older"]) ? $_POST["older"] : '0';
	$older = isset($_GET["older"]) ? $_GET["older"] : $older;

        $myMaxResults = "50";

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

	$results = "";

	ob_start(); // Start output buffering

        #print "<tr> <td> post: </td><td>";
        #print_r($_POST);
        #print "</td></tr>\n";


        for ($i=0; $i<$rows; $i++)
            db_display($i);

	$results = ob_get_clean(); // End buffering and clean up

	echo json_encode(
	    array("results" => $results,
                  "olderid"    => $oldestid)
	
			 );



} # $list == entire

?>