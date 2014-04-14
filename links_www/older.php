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

	#Max Results Dropdown POST and Cookies
	if ( isset( $_POST[ 'MaxResultsPost' ] ) ) {
	  $myMaxResults = $_POST[ 'MaxResultsPost' ];
	  setcookie("LinksMaxResults", $myMaxResults, time()+(60*60*24*365), "/");
	} elseif (isset($_COOKIE['LinksMaxResults'])){
	  $myMaxResults = $_COOKIE["LinksMaxResults"];
	} else {
	  $myMaxResults = "50";
	}

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

#	print "<html>\n";

	$results = "";
	$vars = "";


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

#	print "</html>\n";


} # $list == entire

?>