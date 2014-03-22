<?php

$list = "NoPasswd";

include('configlinks.php');

#include('functions.newposts.php');

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

#$RemoveThumbsSQL = ($myHideCachedImgs == 1) ? "and file_name is null " : "";
#$RemoveEmbedSQL = ($myHideEmbed == 1) ? "and site not like '%youtube.com%' and site not like '%vimeo.com%'" : "";

/* Last Date Tracker */

#best i could do with strtotime forcing a sane time
$defaultDate = "2035-01-01 00:00:00";

$lastdate = isset($_COOKIE['SiteLastDate']) ? $_COOKIE["SiteLastDate"] : $defaultDate;

$lastdateThumbs = isset($_COOKIE['ThumbsLastDate']) ? $_COOKIE["ThumbsLastDate"] : $defaultDate;

$lastdateVids = isset($_COOKIE['UtubeLastDate']) ? $_COOKIE["UtubeLastDate"] : $defaultDate;
	
$lastdateScloud = isset($_COOKIE['ScloudLastDate']) ? $_COOKIE["ScloudLastDate"] : $defaultDate;


if ($list == "entire") {

  $lastdatetime = strtotime($lastdate);
  $lastdatetimeThumbs = strtotime($lastdateThumbs);
  $lastdatetimeVids = strtotime($lastdateVids);
  $lastdatetimeScloud = strtotime($lastdateScloud);

  $lastdate = date('Y-m-d H:i:s', $lastdatetime);
  $lastdateThumbs = date('Y-m-d H:i:s', $lastdatetimeThumbs);
  $lastdateVids = date('Y-m-d H:i:s', $lastdatetimeVids);
  $lastdateScloud = date('Y-m-d H:i:s', $lastdatetimeScloud);

#  $thumbs = strval(urldecode($_GET['thumbs']));
#  $vids = strval(urldecode($_GET['vids']));
#  $links = strval(urldecode($_GET['links']));

  $con = mysqli_connect($host,$username,$password,$database);
  if (!$con)
    {
      die('Could not connect: ' . mysqli_error($con));
    }

  mysqli_select_db($con,$database);
	$sql = "select (select count(*) from links where ( site LIKE '%youtube.com%' or site LIKE '%vimeo.com%' ) and edate >  '$lastdateVids' ) as vids, 
			(select count(*) from links where site like '%soundcloud.com%' and edate >  '$lastdateScloud' ) as scloud, 
			(select count(*) from links where filename is not null and edate >  '$lastdateThumbs' ) as thumbs, 
			(select count(*) from links where filename is null and site not like '%youtube.com%' and site not like '%vimeo.com%' 
                         and edate >  '$lastdate' ) as links;";

#	echo $sql;

#  $sql="SELECT count(*) as count from links WHERE id > '".$q."'";

  $result = mysqli_query($con,$sql);

  $array = array();

while($row = mysqli_fetch_array($result))
  {

    $array[] = $row;

#  $row['Job'] . "</td>";
#    print_r($row);

  }


mysqli_close($con);

echo json_encode($array);



}

?>