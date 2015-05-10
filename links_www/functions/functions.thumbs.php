<?php


// Curl helper function
function curl_get($url) {

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	$return = curl_exec($curl);
	curl_close($curl);

    return $return;
}


/* query mysql database to delete pocket security information */
function db_query_pocket_delete($conn) {

  global $access_token;

  $pocketusername = mysql_real_escape_string($access_token['username']);

  $sql = "DELETE from pocketusers where username = '" . $pocketusername . "';";


	$maxdateresult  = mysql_query($sql, $conn)
	  or die($sql . "<BR>" . mysql_error());

}

/* query mysql database to insert pocket security information */
function db_query_pocket_insert($conn) {

  global $access_token;

  $pocketusername = mysql_real_escape_string($access_token['username']);
  $consumer_key = mysql_real_escape_string($access_token['access_token']);

  $sql = "DELETE from pocketusers where username = '" . $pocketusername . "';";


	$maxdateresult  = mysql_query($sql, $conn)
	  or die($sql . "<BR>" . mysql_error());

  $sql = "INSERT INTO pocketusers (username,consumer_key) VALUES ('" . $pocketusername . "','" . $consumer_key . "');";


	$maxdateresult1  = mysql_query($sql, $conn)
	  or die($sql . "<BR>" . mysql_error());

}



/* query mysql database for information SETTINGS */
function db_query_settings($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;


	$maxdatesql = "select (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where ( site LIKE '%youtube.com%' or site LIKE '%vimeo.com%' )) as utube, (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where site like '%soundcloud.com%') as scloud, (select date_format(max(edate), '%m/%d/%y %H:%i:%s') from links where ( filename is not null OR site LIKE '%youtube.com%' or site LIKE '%vimeo.com%' )) as img;";


	$maxdateresult  = mysql_query($maxdatesql, $conn)
	  or die($sql . "<BR>" . mysql_error());

	$maxdateRow = mysql_fetch_row($maxdateresult);

	$dblastdateUtube = $maxdateRow[0];
	$dblastdateScloud = $maxdateRow[1];
	$dblastdateThumbs = $maxdateRow[2];


	return array ($dblastdateThumbs, $dblastdateUtube, $dblastdateScloud);
}



/* query mysql database for information VIDS */
function db_query_vids($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;

	if ( $newer ==  0 && $older == 0 ) {
	 
		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( site LIKE '%youtube.com%' or site LIKE '%vimeo.com%' or site LIKE '%.webm111%' ) order by edate desc, id desc limit $myMaxResults ";

	} elseif ($newer == 0 && $older != 0) {

		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( site LIKE '%youtube.com%' or site LIKE '%vimeo.com%' or site LIKE '%.webm111%' ) and edate < (select edate from links where id = $older) order by edate desc, id desc limit $myMaxResults ";

	} else {

		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( site LIKE '%youtube.com%' or site LIKE '%vimeo.com%' or site LIKE '%.webm111%' ) and edate > (select edate from links where id = $newer) order by edate asc, id asc limit $myMaxResults ";

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

	#if ($totalurls == 0)
	#	die("No entries found.<BR>");

	return array ($rows, $myid, $dates, $announcers, $urls, $types, $totalurls, $filenames, $twidths, $theights, $titles);
}



/* query mysql database for information */
function db_query_thumbs($conn) {

  global $http, $ftp, $announcer, $partialurl, $newer, $older, $filename, $myMaxResults, $myUtubeSQL;

	if ( $newer ==  0 && $older == 0 ) {
	 
		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( filename is not null $myUtubeSQL ) order by edate desc, id desc limit $myMaxResults ";

	} elseif ($newer == 0 && $older != 0) {

		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( filename is not null $myUtubeSQL ) and edate < (select edate from links where id = $older) order by edate desc, id desc limit $myMaxResults ";

	} else {

		$sql = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, twidth, theight, title from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl' ) and ( filename is not null $myUtubeSQL ) and edate > (select edate from links where id = $newer) order by edate asc, id asc limit $myMaxResults ";

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

	#if ($totalurls == 0)
	#	die("No entries found.<BR>");

	return array ($rows, $myid, $dates, $announcers, $urls, $types, $totalurls, $filenames, $twidths, $theights, $titles);
}

function db_query_rand($conn) {

	$sql = "SELECT id, site, announcer, filename, twidth, theight from links WHERE ( filename is not null OR site LIKE '%youtube.com%' ) order by rand() limit 1 ";

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

/* display a line in table */
function db_display($id) {

  global $myid, $dates, $announcers, $urls, $types, $authorization, $cells_bg, $font_size, $filenames, $twidths, $theights, $titles, $lastdate, 
    $total_width, $myMaxWidth, $myImgHeight, $mynoInfoTxt, $s3Enable, $s3Bucket, $thumbs_folder, $img_path, $img_folder, $myGifAutoPlay;

    $infotxt = "";
    $GifAutoPlay       = ($myGifAutoPlay == "on") ? "" : " freezeframe ";

    $postDateTime = strtotime($dates[$id]);
    $lastDateTime = strtotime($lastdate);
    #$today = time();
    #$since = $today - $postDateTime;
    #$readDateTime = time_since($since, $postDateTime);

    #Set Username
    if($types[$id] == "twitter")
      $infotxt .= "@$announcers[$id]<br>";
    elseif ($types[$id] == "pocket")
      $infotxt .=  "#$announcers[$id]<br>";
    else
      $infotxt .= "$announcers[$id]<br>";
    

    $infotxt .= "$dates[$id]<br>";


    #Get the URL from the main string
    $text = explode(" ", $urls[$id]);
    
    $myURLlength=20;
    $lightboxinfotxt = "";
    $url = "";
    $lightboxinfotxt = $infotxt;
    
    foreach ($text as $OGword) {
    
    	if (preg_match("/^http(s)?:\/\//i",$OGword)) {
    
    		$displayURL = preg_replace('/http(s)?:\/\//i','',$OGword);
    		$dispLayurl = preg_replace('/^www\./i','',$displayURL);
    		$cutdisplayURL = substr($displayURL,0,$myURLlength);
    		$url = $OGword;
    		$infotxt .=  "<a target=\"_blank\" href=\"$OGword\">$cutdisplayURL</a> ";
    		$lightboxinfotxt .=  "<a target=\"_blank\" href=\"$OGword\">$OGword</a> ";
    
    	} elseif (preg_match("/www\./i",$OGword)) { 
    
    		$url = str_replace("www.", "http://www.", $OGword);
    		$displayURL = preg_replace('/^www\./i','',$OGword);
    		$cutdisplayURL = substr($displayURL,0,$myURLlength);
    		$infotxt .=  "<a target=\"_blank\" href=\"$url\">$cutdisplayURL</a> ";
    		$lightboxinfotxt .=  "<a target=\"_blank\" href=\"$url\">$OGword</a> ";
    
    
    	} elseif (preg_match("/\.\w{3}\//i",$OGword)) {
    	        #This section needs work
    		$pretext = substr($OGword, 0, strpos($OGword, ' .*\.\w{3}\/'));
    		$url = substr($OGword, strpos($OGword, ' .*\.\w{3}\/'));
    		$url = str_replace("www.", "http://www.", $OGword);
    		$cutURL = substr($url,0,$myURLlength);
    		$infotxt .=  "<a target=\"_blank\" href=$url>$cutURL</a> ";
    		$lightboxinfotxt .=  "<a target=\"_blank\" href=$url>$OGword</a> ";
    	
    	} else {
    
    		#$cutURL = substr($OGword,0,$myURLlength);
    		$cutURL = $OGword;
    		
    		#Insert spaces?
                #Lightbox only
    		$lightboxinfotxt .=  $cutURL . " ";
    
    	}   # URL Match
    
    }  # For Each Word in String


    #Create the filepath and filepath_thumb
    if (preg_match("/youtube/i",$url)) {
    
        $parsed_url = parse_url($url);
    
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
	    
        $youtubeid = isset($args['v']) ? $args['v'] : '';

        #Find the VideoID if its in the path
	if ( $youtubeid == '' ) {

            $parsed_path = isset($parsed_url['path']) ? $parsed_url['path'] : 'FAIL';

            $path_string = explode( '/', $parsed_path );
    	    
	    $markit = 0;
    	    
            foreach( $path_string as $chunk )
    	        {

		  if ($markit == 1) {

		    $youtubeid = $chunk;

		    break;

		  }

		  if ($chunk == 'v' || $chunk == 'embed') {

		    $markit = 1;

		  }


    	        }
    	    
	}
	    
        $filepath = "http://img.youtube.com/vi/" . $youtubeid . "/0.jpg";
        $filepath_thumb = "http://img.youtube.com/vi/" . $youtubeid . "/0.jpg";


    } elseif (preg_match("/vimeo/i",$urls[$id])) {

        $pattern = '/(\/\/www\.)?vimeo.com.*\/(\d+)($|\/|#)/';
        #$pattern = '#.*(player\.)?vimeo\.com(/video)?/(\d+)#i';
        #$pattern = 'vimeo\.com/(\w*/)*(\d+)';
        
        preg_match($pattern, $urls[$id], $matches);
        
        #if (count($matches))
        #{
        
        $vimeoid = isset($matches[2]) ? $matches[2] : '';
        
        #} 
        
        $filepath = "";
        $filepath_thumb = "";

    } else {

        if ($s3Enable == 1) {
     
	  #URL Encode the foler and filename for amazon
	    $filepath = "https://" . $s3Bucket . ".s3.amazonaws.com/" . $img_folder . "/" . urlencode($filenames[$id]);
          $filepath_thumb = "https://" . $s3Bucket . ".s3.amazonaws.com/" . $thumbs_folder . "/" . urlencode("thumb_" . $filenames[$id]);
        
        } else {
        
          $filepath = $img_path . "/" . $img_folder . "/" . $filenames[$id];
          $filepath_thumb = $img_path . "/" . $thumbs_folder . "/thumb_" . $filenames[$id];
        
        }
  	
    }


    #If the thumbnail exists or s3Enabled or youtube or vimeo
    if (file_exists($filepath_thumb) || $s3Enable == 1 || preg_match("/youtube/i",$urls[$id]) || preg_match("/vimeo/i",$urls[$id]) || preg_match("/\.webm/i",$urls[$id])) {
    
    
      if ($s3Enable == 1) {
    
        $width = $twidths[$id];
        $height = $theights[$id];
    
      } elseif (preg_match("/youtube/i",$urls[$id]) || preg_match("/vimeo/i",$urls[$id]) || preg_match("/\.webm/i",$urls[$id])) {
    
        $width = 200;
        $height = 150;
    
    } else {
    
         // Get new dimensions from local File
         list($width, $height) = getimagesize($filepath_thumb);
    
    }


    echo "<div class=\"thumb\">\n";
    
    
    #Build the thumb
    if (preg_match("/youtube/i",$urls[$id])) {
    
       echo "<a ";

        #If the title doesnt exist go grab it from the youtube API
        if (strlen($titles[$id]) == 0) {
      
           $oembed_youtube = simplexml_load_string(file_get_contents("http://gdata.youtube.com/feeds/api/videos/" . $youtubeid . "?fields=title"));
           $myTitle = $oembed_youtube->title;

        } else {
      
          $myTitle = $titles[$id];
          $myTitle = str_replace(" - YouTube","",$myTitle);
          $myTitle = str_replace("YouTube - ","",$myTitle);
      
        }
      
        echo "href=\"https://www.youtube.com/watch?v=" . $youtubeid . "\" ";
        echo "type=\"text/html\" ";
        #echo "title=\"" . $titles[$id] . "\" ";
        echo "title=\"" . htmlentities($myTitle) . "\" ";
        echo "data-description=\"" . htmlentities($lightboxinfotxt) . "\"";
        echo "data-youtube=\"" . $youtubeid . "\" data-gallery ";
        #echo "data-gallery=\"#blueimp-gallery\",\n";
        echo "data-poster=\"https://img.youtube.com/vi/" . $youtubeid . "/0.jpg\">\n";
        #echo "<img src=\"" . $filepath_thumb . "\" class=\"img-responsive img-thumbnail youtube-ngt\"></a>";
        echo "<img src=\"" . $filepath_thumb . "\" class=\"img-thumbnail youtube-ngt\"></a>";


    } elseif (preg_match("/\.webm/i",$urls[$id])) {
    
        #If the title doesnt exist go grab it from the youtube API
        if (strlen($titles[$id]) == 0) {
      
           #$oembed_youtube = simplexml_load_string(file_get_contents("http://gdata.youtube.com/feeds/api/videos/" . $youtubeid . "?fields=title"));
           #$myTitle = $oembed_youtube->title;
      
        } else {
      
          $myTitle = $titles[$id];
      
        }
      
        echo "<video class=\"img-thumbnail webm-ngt\" controls >\n";
        echo "<source src=\"$url\" type='video/webm; codecs=\"vp8, vorbis\"'>\n";
	echo "</video>\n";
        #echo "type=\"text/html\" ";
        #echo "title=\"" . $titles[$id] . "\" ";
        #echo "title=\"" . htmlentities($myTitle) . "\" ";
        #echo "data-description=\"" . htmlentities($lightboxinfotxt) . "\"";
        #echo "data-youtube=\"" . $youtubeid . "\" data-gallery ";
        #echo "data-gallery=\"#blueimp-gallery\",\n";
        #echo "data-poster=\"https://img.youtube.com/vi/" . $youtubeid . "/0.jpg\">\n";
        #echo "<img src=\"" . $filepath_thumb . "\" class=\"img-responsive img-thumbnail youtube-ngt\"></a>";
        #echo "<img src=\"" . $filepath_thumb . "\" class=\"img-thumbnail youtube-ngt\"></a>";

    } elseif (preg_match("/vimeo/i",$urls[$id])) {

       echo "<a ";

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
        
        #If the title doesnt exist go grab it from the vimeo API
        if (strlen($titles[$id]) == 0) {
        
          $myTitle = $oembed->title;
        
        } else {
        
          $myTitle = $titles[$id];
          $myTitle = str_replace("on Vimeo","",$myTitle);
        
        }
        
        $vimeo_thumbnail_url = isset($oembed->thumbnail_url) ? $oembed->thumbnail_url : '';
        
        echo "href=\"https://www.vimeo.com/" . $vimeoid . "\" ";
        echo "type=\"text/html\" ";
        #echo "title=\"" . $titles[$id] . "\" ";
        echo "title=\"" . htmlentities($myTitle) . "\" ";
        echo "data-vimeo=\"" . $vimeoid . "\" data-gallery ";
        echo "data-description=\"" . htmlentities($lightboxinfotxt) . "\" ";
	#echo "\"data-gallery=\"#blueimp-gallery\",\n";
        echo "data-poster=\"" . $vimeo_thumbnail_url . "\">\n";
        echo "<img src=\"" . $vimeo_thumbnail_url . "\" class=\"img-responsive img-thumbnail youtube-ngt\"></a>";


    } elseif (preg_match("/instagr/i",$urls[$id])) {

      $oembed_instagram_url = str_replace("instagram.com","instagr.am",$urls[$id]);

#      $oembed_instagram_data = json_decode(file_get_contents('http://api.instagram.com/oembed?omitscript=true&url='.$oembed_instagram_url), true);

      preg_match('/\/(?P<shortcode>\w{5,})\/$/', $oembed_instagram_url, $instagram_shortcode);

      $oembed_instagram_data = json_decode(file_get_contents('https://api.instagram.com/v1/media/shortcode/'.$instagram_shortcode['shortcode'].'?client_id='.$instagram_clientid), true);

      $oembed_instagram_mp4 = $oembed_instagram_data['data']['videos']['standard_resolution']['url'];

      echo "<div class=\"img-responsive img-thumbnail\">\n";

      echo "<a ";

      #Show the cached copy in the lightbox if it exists
      if( strlen($filepath) > 0 )
	$lightboxinfotxt .= "- <a target=\"_blank\" href=\"$filepath\">(cache)</a>\n";
	#$infotxt .= "  <a target=\"_blank\" href=\"$filepath\">(cache)</a>\n";

	if( strlen($oembed_instagram_mp4) > 0 ) {

           echo "href=\"$oembed_instagram_mp4\" ";
	   echo "type=\"video/mp4\" ";
	   echo "data-poster=\"$filepath\" ";

        } else {

	  echo "href=\"$filepath\" ";

	}

        echo "data-description=\"" . htmlentities($lightboxinfotxt) . "\" data-gallery>\n";

        echo "<img src=\"$filepath_thumb\" class=\"image-ngt $GifAutoPlay\"></a>";

        echo "\n";

        echo "</div>";

    } else {

      echo "<div class=\"img-responsive img-thumbnail\">\n";

       echo "<a ";

        #echo "<a href=\"$filepath\" data-gallery>";
        #echo "<a href=\"thumbs2.php?id=$myid[$id]\" data-gallery>";
        #echo "type=\"image/jpeg',\n";
        #echo "data-gallery=\"#blueimp-gallery',\n";
        
        #Show the cached copy in the lightbox if it exists
        if( strlen($filepath) > 0 )
          $lightboxinfotxt .= "- <a target=\"_blank\" href=\"$filepath\">(cache)</a>\n";
          #$infotxt .= "  <a target=\"_blank\" href=\"$filepath\">(cache)</a>\n";
        
        echo "href=\"$filepath\" ";
        echo "data-description=\"" . htmlentities($lightboxinfotxt) . "\" data-gallery>\n";
        
        if (preg_match("/^.*\.svg$/i",$filepath)) {
        
            echo "<img src=\"$filepath\" class=\"img-responsive img-thumbnail image-ngt\" style=\"height:170px;\"></a>";
        
        } else {
        
            echo "<img src=\"$filepath_thumb\" class=\"image-ngt $GifAutoPlay\"></a>";
        
        }

	echo "\n";

	echo "</div>";

    }


    echo "\n";

    if ( preg_match("/youtube/i",$urls[$id]) || preg_match("/vimeo/i",$urls[$id]) )
          echo "<h4 class=\"post-title\">$myTitle</h4>\n";
    
    if ($mynoInfoTxt == "on") {
    
        #echo "<p><div style=\"position: relative; float: left; padding: 0px;\">\n";
        
      if (!isset($_POST["older"]) && $postDateTime > $lastDateTime) {
            echo "<div class=\"post-new caption\">\n";
      } else {
            echo "<div class=\"caption\">\n";
      }
        
      echo $infotxt;
      
      echo "\n";
      
      echo "</div> <!-- caption -->\n";
    
    } #mynoinfotxt
    
    
    echo "</div> <!-- thumb --> \n\n"; #Main DIV
    
    echo "\n\n";
        
    } #if file exists

} # function end


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

