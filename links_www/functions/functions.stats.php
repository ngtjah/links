<?php



/* query mysql database for information STATS */
function db_query_stats($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;


  $sql = "select DATE_FORMAT(edate, '%Y-%m-01') as edate1, count(*) as count1 from links where edate < DATE_FORMAT(now(), '%Y-%m-01 00:00:00') group by DATE_FORMAT(edate, '%Y-%m-01') order by date(edate)";


	$result = mysql_query($sql, $conn)
		or die($sql . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result);
	$fields = mysql_num_fields($result);

	for ($i=0; $i<$rows; $i++) {
#		$myid[$i] = mysql_result($result, $i, "");
		$dates[$i] = mysql_result($result, $i, "edate1");
		$counts[$i] = mysql_result($result, $i, "count1");
#		$announcers[$i] = mysql_result($result, $i, "announcer");
#		$urls[$i] = mysql_result($result, $i, "site");
#		$types[$i] = mysql_result($result, $i, "type");
#		$filenames[$i] = mysql_result($result, $i, "filename");
#		$twidths[$i] = mysql_result($result, $i, "twidth");
#		$theights[$i] = mysql_result($result, $i, "theight");
#		$titles[$i] = mysql_result($result, $i, "title");
	}

	$totalurls = $i;


	if ($totalurls == 0)
		die("No entries found.<BR>");

	return array ($rows, $dates, $counts);

}


/* query mysql database for information STATS */
function db_query_stats2($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;


  $sql1 = "SET SESSION group_concat_max_len = 50000;";
  $sql2 = "SET @sql1 = NULL;";

  $sql3 = <<<EOF

SELECT GROUP_CONCAT(DISTINCT
	       CONCAT(
		      'sum(CASE WHEN announcer = ''',
		      replace(announcer,'`',''),
		      ''' THEN 1 ELSE 0 END) AS `',
		      replace(announcer,'`',''), '`'
		      )
	       ) INTO @sql1
from links
WHERE announcer is not null and announcer != '';

EOF;


  $sql4 = <<<EOF

set @sql1
  = CONCAT('SELECT DATE_FORMAT(edate, \'%Y\') as edate1, ',
	   @sql1,
	   ' from links p group by DATE_FORMAT(edate, \'%Y\')');

EOF;

  $sql5 = <<<EOF

PREPARE stmt FROM @sql1;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

EOF;

	$result1 = mysql_query($sql1, $conn)
	  or die($sql1 . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result1);

	#echo $sql1;


	$result2 = mysql_query($sql2, $conn)
	  or die($sql2 . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result2);

	#echo $sql2;


	$result3 = mysql_query($sql3, $conn)
	  or die($sql3 . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result3);

	#echo $sql3;


	$result4 = mysql_query($sql4, $conn)
	  or die($sql4 . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result4);

	#echo $sql4;

	$result5 = mysql_query($sql5, $conn)
	  or die($sql5 . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result5);

	#echo $sql5;


#	$result = mysql_query($sql, $conn)
#		or die($sql . "<BR>" . mysql_error());
#
#	$rows = mysql_num_rows($result);
#	echo $rows;
#	$fields = mysql_num_fields($result);
#
#	for ($i=0; $i<$rows; $i++) {
#
#	  $k = 0;
#	  while ($k < mysql_num_fields($result)) {
#
#	    $meta = mysql_fetch_field($result, $i);
#
#	    $meta->name[$i] = mysql_result($result, $i, $meta->name);
#
#	    echo $meta->name;
#
##		$k[$i] = mysql_result($result, $i, "");
##		"column" . $k[$i] = mysql_result($result, $i, "edate1");
##		"column" . $k[$i] = mysql_result($result, $i, "count1");
##		$announcers[$i] = mysql_result($result, $i, "announcer");
##		$urls[$i] = mysql_result($result, $i, "site");
##		$types[$i] = mysql_result($result, $i, "type");
##		$filenames[$i] = mysql_result($result, $i, "filename");
##		$twidths[$i] = mysql_result($result, $i, "twidth");
##		$theights[$i] = mysql_result($result, $i, "theight");
##		$titles[$i] = mysql_result($result, $i, "title");
#
#	  }
#	}

	$totalurls = $i;


#	if ($totalurls == 0)
#		die("No entries found.<BR>");

	return array ($rows, $dates, $counts);

}


/* query mysql database for information STATS */
function db_query_stats3($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;


  $sql = "select site from links order by id";


	$result = mysql_query($sql, $conn)
		or die($sql . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result);
	$fields = mysql_num_fields($result);

	for ($i=0; $i<$rows; $i++) {
		$sites[$i] = mysql_result($result, $i, "site");
	}

	$totalurls = $i;


	if ($totalurls == 0)
		die("No entries found.<BR>");


	return array ($rows, $sites);

}


function db_query_stats4($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;


  $sql = "select ";

  $casesql = "CASE WHEN announcer LIKE '%house%' or announcer like 'gm%' THEN 'House/GM' ";
  $casesql .= "WHEN announcer like '%click%' THEN 'click' ";
  $casesql .= "WHEN announcer like 'arc%' or announcer like 'FITH-arc%' THEN 'arcane' ";
  $casesql .= "WHEN announcer like '%kush%' or announcer like 'ilya%' or announcer like 'bilz%' THEN 'Ilya' ";
  $casesql .= "WHEN announcer like '%eleme%' or announcer like 'lm%' THEN 'eleme' ";
  $casesql .= "WHEN announcer like '%tofu%' or announcer like '%t0fu%' THEN 'tofu' ";
  $casesql .= "WHEN announcer like '%melinko%' or announcer like '%mel%' THEN 'melinko' ";
  $casesql .= "WHEN announcer like '%d4rksun%' THEN 'd4rksun' ";
  $casesql .= "WHEN announcer like '%wixx%' THEN 'wixx' ";
  
  $sql .= $casesql . "ELSE announcer END as announcer, count(*) as count1 from links group by ";

  $sql .= $casesql . "ELSE announcer END order by count(*) desc;";


	$result = mysql_query($sql, $conn)
		or die($sql . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result);
	$fields = mysql_num_fields($result);

	for ($i=0; $i<$rows; $i++) {
		$announcers[$i] = mysql_result($result, $i, "announcer");
		$counts[$i] = mysql_result($result, $i, "count1");
	}

	$totalurls = $i;

	if ($totalurls == 0)
		die("No entries found.<BR>");

	return array ($rows, $announcers, $counts);

}









/* query mysql database for information VIDS */
function db_query_vids($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;

	if ( $newer ==  "0" && $older == "0" ) {
	 
		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( site LIKE '%youtube%' or site LIKE '%vimeo.com%' ) order by edate desc, id desc limit $myMaxResults ";

	} elseif ($newer == "0" && $older != "0") {

		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( site LIKE '%youtube%' or site LIKE '%vimeo.com%' ) and edate <= (select edate from links where id = $older) order by edate desc, id desc limit $myMaxResults ";

	} else {

		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( site LIKE '%youtube%' or site LIKE '%vimeo.com%' ) and edate >= (select edate from links where id = $newer) order by edate asc, id asc limit $myMaxResults ";

	}

	#echo "sql: $sql\n";
	$result = mysql_query($sql, $conn)
		or die($sql . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result);
	$fields = mysql_num_fields($result);

	for ($i=0; $i<$rows; $i++) {
		$myid[$i] = mysql_result($result, $i, "id");
		$dates[$i] = mysql_result($result, $i, "edate1");
		$announcers[$i] = mysql_result($result, $i, "announcer");
		$urls[$i] = mysql_result($result, $i, "site");
		$types[$i] = mysql_result($result, $i, "type");
		$filenames[$i] = mysql_result($result, $i, "filename");
		$twidths[$i] = mysql_result($result, $i, "twidth");
		$theights[$i] = mysql_result($result, $i, "theight");
		$titles[$i] = mysql_result($result, $i, "title");
	}

	if ( $newer !=  "0" ) {
		$myid = array_reverse($myid);
		$dates = array_reverse($dates);
		$announcers = array_reverse($announcers);
		$urls = array_reverse($urls);
		$types = array_reverse($types);
		$filenames = array_reverse($filenames);
		$twidths = array_reverse($twidths);
		$theights = array_reverse($theights);
		$titles = array_reverse($titles);

	}

	$totalurls = $i;

	$maxdatesql = "select (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where ( site LIKE '%youtube.com%' or site LIKE '%vimeo.com%' )) as utube, (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where site like '%soundcloud.com%') as scloud, (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where ( filename is not null OR site like '%youtube%' or site LIKE '%vimeo.com%' )) as img;";


	$maxdateresult  = mysql_query($maxdatesql, $conn)
	  or die($sql . "<BR>" . mysql_error());

	$maxdateRow = mysql_fetch_row($maxdateresult);

	$dblastdateUtube = $maxdateRow[0];
	$dblastdateScloud = $maxdateRow[1];
	$dblastdateThumbs = $maxdateRow[2];


	if ($totalurls == 0)
		die("No entries found.<BR>");

	return array ($rows, $myid, $dates, $announcers, $urls, $types, $totalurls, $filenames, $twidths, $theights, $titles, $dblastdateThumbs, $dblastdateUtube, $dblastdateScloud);
}



/* query mysql database for information */
function db_query_thumbs($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;

	if ( $newer ==  "0" && $older == "0" ) {
	 
		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( filename is not null $myUtubeSQL ) order by edate desc, id desc limit $myMaxResults ";

	} elseif ($newer == "0" && $older != "0") {

		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( filename is not null $myUtubeSQL ) and edate <= (select edate from links where id = $older) order by edate desc, id desc limit $myMaxResults ";

	} else {

		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( filename is not null $myUtubeSQL ) and edate >= (select edate from links where id = $newer) order by edate asc, id asc limit $myMaxResults ";

	}

	#echo "sql: $sql\n";
	$result = mysql_query($sql, $conn)
		or die($sql . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result);
	$fields = mysql_num_fields($result);

	for ($i=0; $i<$rows; $i++) {
		$myid[$i] = mysql_result($result, $i, "id");
		$dates[$i] = mysql_result($result, $i, "edate1");
		$announcers[$i] = mysql_result($result, $i, "announcer");
		$urls[$i] = mysql_result($result, $i, "site");
		$types[$i] = mysql_result($result, $i, "type");
		$filenames[$i] = mysql_result($result, $i, "filename");
		$twidths[$i] = mysql_result($result, $i, "twidth");
		$theights[$i] = mysql_result($result, $i, "theight");
		$titles[$i] = mysql_result($result, $i, "title");
	}

	if ( $newer !=  "0" ) {
		$myid = array_reverse($myid);
		$dates = array_reverse($dates);
		$announcers = array_reverse($announcers);
		$urls = array_reverse($urls);
		$types = array_reverse($types);
		$filenames = array_reverse($filenames);
		$twidths = array_reverse($twidths);
		$theights = array_reverse($theights);
		$titles = array_reverse($titles);

	}

	$totalurls = $i;

	$maxdatesql = "select (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where ( site LIKE '%youtube.com%' or site LIKE '%vimeo.com%' )) as utube, (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where site like '%soundcloud.com%') as scloud, (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where ( filename is not null OR site like '%youtube%' or site LIKE '%vimeo.com%' )) as img;";


	$maxdateresult  = mysql_query($maxdatesql, $conn)
	  or die($sql . "<BR>" . mysql_error());

	$maxdateRow = mysql_fetch_row($maxdateresult);

	$dblastdateUtube = $maxdateRow[0];
	$dblastdateScloud = $maxdateRow[1];
	$dblastdateThumbs = $maxdateRow[2];


	if ($totalurls == 0)
		die("No entries found.<BR>");

	return array ($rows, $myid, $dates, $announcers, $urls, $types, $totalurls, $filenames, $twidths, $theights, $titles, $dblastdateThumbs, $dblastdateUtube, $dblastdateScloud);
}

function db_query_rand($conn) {

	$sql = "SELECT id, site, announcer, filename, twidth, theight from links WHERE ( filename is not null OR site like '%youtube%' ) order by rand() limit 1 ";

	#echo "sql: $sql\n";
	$result = mysql_query($sql, $conn)
		or die($sql . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result);
	$fields = mysql_num_fields($result);

	for ($i=0; $i<$rows; $i++) {
		$myid[$i] = mysql_result($result, $i, "id");
		$announcers[$i] = mysql_result($result, $i, "announcer");
		$urls[$i] = mysql_result($result, $i, "site");
		$filenames[$i] = mysql_result($result, $i, "filename");
		$twidths[$i] = mysql_result($result, $i, "twidth");
                $theights[$i] = mysql_result($result, $i, "theight");
	}

	$totalurls = $i;

	if ($totalurls == 0)
		die("No entries found.<BR>");

	return array ($myid, $announcers, $urls, $filenames, $twidths, $theights);
}

function db_display_stats($id)  {

  global $rows, $dates, $counts, $authorization, $myMaxWidth, $myImgHeight, $mynoInfoTxt, $s3Enable, $s3Bucket;

  echo "['" . $dates[$id] . "', " . $counts[$id] . "],\n";


}

function db_display_stats2($id)  {

  global $rows, $dates, $counts, $authorization, $myMaxWidth, $myImgHeight, $mynoInfoTxt, $s3Enable, $s3Bucket;

  echo "['" . $dates[$id] . "', " . $counts[$id] . "],\n";


}


function db_display_stats3()  {

  global $rows2, $sites, $authorization, $myMaxWidth, $myImgHeight, $mynoInfoTxt, $s3Enable, $s3Bucket;


	$domainsCount = array();

	for ($i=0; $i<$rows2; $i++) {

	  $text = explode(" ", $sites[$i]);

		foreach ($text as $OGword) {
	
		  if (preg_match("/^http(s)?:\/\//i",$OGword) || preg_match("/www\./i",$OGword) || preg_match("/\.\w{3}\//i",$OGword)) {

		  $url = str_replace("http://", "", $OGword);
		  $url = str_replace("https://", "", $url);
	
		  $chunks = explode("/", $url);
	
	          $url = $chunks[0];
			
	          }   # URL Match
	
		}  # For Each Word in String

	
		if (isset($domainsCount[$url])) {
	
		  $domainsCount[$url] = $domainsCount[$url] + 1;
	
		  } else {
	
		  $domainsCount[$url] = 1;

		} # if domainscount[]

	} #for

	arsort($domainsCount);

	#print_r($domainsCount);

	$k = 0;

        #Print the top 20?
	foreach($domainsCount as $key => $value) {

	  if ($k < 20)
	    $k++;
	  else
	    break;

	  echo "['$key',  $value ],\n";



	}


}

function db_display_stats4($id)  {

  global $rows4, $announcers4, $counts4, $authorization, $myMaxWidth, $myImgHeight, $mynoInfoTxt, $s3Enable, $s3Bucket;

  echo "['" . $announcers4[$id] . "', " . $counts4[$id] . "],\n";


}





/* display a line in table */
function db_display($id) {

  global $myid, $dates, $announcers, $urls, $types, $authorization, $cells_bg, $font_size, $filenames, $twidths, $theights, $titles, $total_width, $myMaxWidth, $myImgHeight, $mynoInfoTxt, $s3Enable, $s3Bucket;

#  $total_width = $total_width + 1;

  	if ($s3Enable == 1) {
	  
	  $filepath = "https://" . $s3Bucket . ".s3.amazonaws.com/imgs/" . $filenames[$id];
	  $filepath_thumb = "https://" . $s3Bucket . ".s3.amazonaws.com/thumbs/thumb_" . $filenames[$id];

  	} else {
  	
	  if (preg_match("/youtube/i",$urls[$id])) {

	    $parsed_url = parse_url($urls[$id]);

	    $parsed_query = isset($parsed_url['query']) ? $parsed_url['query'] : 'FAIL';

	    $query_string = explode( '&', $parsed_query );

	      $args = array( ); // return array

	      foreach( $query_string as $chunk )
		{
		  $chunk = explode( '=', $chunk );
		  // it's only really worth keeping if the parameter
		  // has an argument.
		  if ( count( $chunk ) == 2 )
		    {
		      list( $key, $val ) = $chunk;
		      $args[ $key ] = urldecode( $val );
		    }
		}

	      $youtubeid = $args['v'];

	      $filepath = "http://img.youtube.com/vi/" . $youtubeid . "/0.jpg";
	      $filepath_thumb = "http://img.youtube.com/vi/" . $youtubeid . "/0.jpg";

	  } elseif (preg_match("/vimeo/i",$urls[$id])) {

	    $pattern = '/(\/\/www\.)?vimeo.com.*\/(\d+)($|\/|#)/';
#	    $pattern = '#.*(player\.)?vimeo\.com(/video)?/(\d+)#i';
#	    $pattern = 'vimeo\.com/(\w*/)*(\d+)';
	    preg_match($pattern, $urls[$id], $matches);
	    if (count($matches))
	      {
		$vimeoid = $matches[2];
	      }

	      $filepath = "";
	      $filepath_thumb = "";

	  } else {

  	    $filepath = "../links_img/" . $filenames[$id];
  	    $filepath_thumb = "..//links_img/thumbs2/thumb_" . $filenames[$id];

	    }
  	
  	}


	if (file_exists($filepath_thumb) || $s3Enable == 1 || preg_match("/youtube/i",$urls[$id]) || preg_match("/vimeo/i",$urls[$id])) {


	  if ($s3Enable == 1) {

	    $width = $twidths[$id];
	    $height = $theights[$id];

	  } elseif (preg_match("/youtube/i",$urls[$id]) || preg_match("/vimeo/i",$urls[$id])) {

	  $width = 200;
	  $height = 150;


	} else {

		// Get new dimensions from local File
		list($width, $height) = getimagesize($filepath_thumb);

	  }

#	  if($total_width %6 == 0 && $total_width != 0) {
#	  
#	      echo "</div> <!-- close row --> \n";
#	  
#              echo "<div class=\"row\"> <!-- new row --> \n";
#	  
#	  }

#	  echo "<div class=\"col-xs-6 col-sm-4 col-md-4 col-lg-2\">";
#	  echo "<div class=\"col-xs-6 col-md-3 col-lg-2\">\n";
	  echo "<div class=\"thumb\">\n";


	  echo "<a ";

	  if (preg_match("/youtube/i",$urls[$id])) {

	    echo "href=\"https://www.youtube.com/watch?v=" . $youtubeid . "\" ";
	    echo "type=\"text/html\" ";
	    echo "title=\"" . $titles[$id] . "\" ";
	    echo "data-youtube=\"" . $youtubeid . "\" data-gallery ";
	    #echo "data-gallery=\"#blueimp-gallery\",\n";
	    echo "data-poster=\"https://img.youtube.com/vi/" . $youtubeid . "/0.jpg\">\n";
	    echo "<img src=\"" . $filepath_thumb . "\" class=\"img-responsive img-thumbnail youtube-ngt\"";

#	    echo '<div class="post">';
#	    echo '<h4 class="post-title-thumbs" id="utitle-' . $id . '"></h4>';
#           echo "<a class=\"post-thumb\" href=\"javascript:void(0);\">\n";

	  } elseif (preg_match("/vimeo/i",$urls[$id])) {

	    #$vimeodata = file_get_contents("http://vimeo.com/api/v2/video/" . $vimeoid . ".json");
	    #$vimeodata = json_decode($data);
	    #$vimeoimage = $vimeodata[0]->thumbnail_medium;

	    $oembed_endpoint = 'http://vimeo.com/api/oembed';

	    // Grab the video url from the url, or use default
	    $video_url = "https://www.vimeo.com/" . $vimeoid;

	    // Create the URLs
	    $json_url = $oembed_endpoint . '.json?url=' . rawurlencode($video_url) . '&height=170';
	    $xml_url = $oembed_endpoint . '.xml?url=' . rawurlencode($video_url) . '&width=640';

	    // Load in the oEmbed XML
	    $oembed = simplexml_load_string(curl_get($xml_url));

	    echo "href=\"https://www.vimeo.com/" . $vimeoid . "\" ";
            echo "type=\"text/html\" ";
#            echo "title=\"" . $titles[$id] . "\" ";
            echo "title=\"" . $oembed->title . "\" ";
            echo "data-vimeo=\"" . $vimeoid . "\" data-gallery ";
            #echo "data-gallery=\"#blueimp-gallery\",\n";
            echo "data-poster=\"" . $oembed->thumbnail_url . "\">\n";
            echo "<img src=\"" . $oembed->thumbnail_url . "\" class=\"img-responsive img-thumbnail youtube-ngt\"";


	  } else {

#	    echo "<a href=\"$filepath\" data-gallery>";
#	    echo "<a href=\"thumbs2.php?id=$myid[$id]\" data-gallery>";
	    #echo "type=\"image/jpeg',\n";
	    #echo "data-gallery=\"#blueimp-gallery',\n";

	    echo "href=\"$filepath\" data-gallery>\n";
	    echo "<img src=\"$filepath_thumb\" class=\"img-responsive img-thumbnail image-ngt\"";

	  }

	    echo "> </a>\n";

	if ($mynoInfoTxt == "on") {

#	echo "<p><div style=\"position: relative; float: left; padding: 0px;\">\n";
	echo "<div class=\"caption\">\n";

	if($types[$id] == "twitter")
	  echo "@$announcers[$id]<br>";
	elseif ($types[$id] == "pocket")
	  echo "#$announcers[$id]<br>";
	else
	  echo "$announcers[$id]<br>";


	echo "$dates[$id]<br>";
	echo "";

	$text = explode(" ", $urls[$id]);

	$myURLlength=20;

	foreach ($text as $OGword) {

		if (preg_match("/^http(s)?:\/\//i",$OGword)) {

			$displayURL = preg_replace('/http(s)?:\/\//i','',$OGword);
			$displayURL = preg_replace('/^www\./i','',$displayURL);
			$cutdisplayURL = substr($displayURL,0,$myURLlength);
			echo "<a target=\"_blank\" href=\"$OGword\">$cutdisplayURL</a> ";

		} elseif (preg_match("/www\./i",$OGword)) { 

			$url = str_replace("www.", "http://www.", $OGword);
			$displayURL = preg_replace('/^www\./i','',$OGword);
			$cutdisplayURL = substr($displayURL,0,$myURLlength);
			echo "<a target=\"_blank\" href=\"$url\">$cutdisplayURL</a> ";


		} elseif (preg_match("/\.\w{3}\//i",$OGword)) {
		#This section needs work
			$pretext = substr($OGword, 0, strpos($OGword, ' .*\.\w{3}\/'));
			$url = substr($OGword, strpos($OGword, ' .*\.\w{3}\/'));
			$url = str_replace("www.", "http://www.", $OGword);
			$cutURL = substr($url,0,$myURLlength);
			echo "<a target=\"_blank\" href=$url>$cutURL</a> ";
		
		} else {

			#$cutURL = substr($OGword,0,$myURLlength);
			
			#Insert spaces?
			echo "$cutURL ";

		}	

		
	}




	echo "\n";

	echo "</div> <!-- caption -->\n";


	} #mynoinfotxt



	echo "</div> <!-- thumb --> \n\n"; #Main DIV

	echo "\n\n";


	} #if file exists

}


/* display a line in table */
function rand_display($id) {

  global $myid_rand, $announcers_rand, $urls_rand, $cells_bg, $font_size, $filenames_rand, $twidths_rand, $theights_rand, $myImgHeight, $s3Enable, $s3Bucket;


	  if ($s3Enable == 1) {
	
	    $filepath = "https://" . $s3Bucket . ".s3.amazonaws.com/imgs/" . $filenames_rand[$id];
	    $filepath_thumb = "https://" . $s3Bucket . ".s3.amazonaws.com/thumbs/thumb_" . $filenames_rand[$id];
	
	  } else {
	
	    $filepath = "links_img/" . $filenames_rand[$id];
	    $filepath_thumb = "links_img/thumbs/thumb_" . $filenames_rand[$id];
	
	  }

	if (file_exists($filepath_thumb) || $s3Enable == 1) {

	  if ($s3Enable == 1) {

            $width = $twidths_rand[$id];
            $height = $theights_rand[$id];

          } else {

	    // Get new dimensions
	    list($width, $height) = getimagesize($filepath_thumb);

	  }

	if ($height > $myImgHeight) {
		$scale = $myImgHeight/$height;
		$width = $width*$scale;
	}


	echo "<a href=\"thumbs2.php?id=$myid_rand[$id]\"><img height=\"80\" src=\"$filepath_thumb\" border=0></a>";

	} else
		echo "<div style=\"font-size:x-small;\">Error: $filenames_rand[$id]</div>";
}




/* End of Functions */


?>

