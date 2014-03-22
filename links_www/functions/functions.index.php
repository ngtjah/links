<?php

function time_since($since, $original) {
  global $font_size_small;

  $chunks = array(
                  array(60 * 60 * 24 * 365 , 'year'),
                  array(60 * 60 * 24 * 30 , 'month'),
                  array(60 * 60 * 24 * 7, 'week'),
                  array(60 * 60 * 24 , 'day'),
                  array(60 * 60 , 'hr'),
                  array(60 , 'min'),
                  array(1 , 'sec')
                  );

  if($since > 86400) {
    $print = date("M jS G:i:s", $original);

    if($since > 31536000) {
      $print .= ", " . date("Y", $original);
    }
    return $print;
  }

  $found = 0;

  for ($i = 0, $j = count($chunks); $i < $j; $i++) {

    $seconds = $chunks[$i][0];
    $name = $chunks[$i][1];

    if ($found != 0) {
      $count = floor($remainSeconds / $seconds);
      break;

    }

    if (($count = floor($since / $seconds)) != 0) {
      $mainname = $name;
      $maincount = $count;
      $remainSeconds = ($since - ($count * $seconds));
      $found = 1;
    }
  }

  if ( ($mainname == 'min' && $count != 0 ) || ($mainname == 'hr' && $maincount < 6 && $count != 0) ) {
    $print = ($maincount == 1) ? '1 '.$mainname." " : "$maincount {$mainname}s ";
    $print .= "";
    $print .= ($count == 1) ? '1 '.$name." ago" : "$count {$name}s ago";
    $print .= "";
  } else {
    $print = ($maincount == 1) ? '1 '.$mainname." ago" : "$maincount {$mainname}s ago";
  }


  return $print;
}




/* query mysql database for information  LINKS */
function db_query($conn) {

  global $http, $ftp, $announcer, $partialurl, $startdate, $enddate, $filename, $RemoveThumbsSQL, $newer, $older, $myMaxResults, $RemoveEmbedSQL;

    if ( $newer ==  0 && $older == 0 ) {

      $sql = "SELECT  site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, title, category, id from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl') $RemoveThumbsSQL $RemoveEmbedSQL order by edate desc, id desc limit $myMaxResults ";

    } elseif ($newer == 0 && $older != 0) {

    $sql = "SELECT  site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, title, category, id from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl') and edate <= (select edate from links where id = $older) $RemoveThumbsSQL $RemoveEmbedSQL order by edate desc, id desc limit $myMaxResults ";

  } else {

    $sql = "SELECT  site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1, type, filename, title, category, id from links WHERE ( site LIKE '$partialurl' OR announcer LIKE '$partialurl' OR title LIKE '$partialurl') and edate >= (select edate from links where id = $newer) $RemoveThumbsSQL $RemoveEmbedSQL order by edate asc, id asc limit $myMaxResults ";

  }


	$result = mysql_query($sql, $conn)
		or die($sql . "<BR>" . mysql_error());

	$rows = mysql_num_rows($result);
	$fields = mysql_num_fields($result);

	for ($i=0; $i<$rows; $i++) {
		$dates[$i] = mysql_result($result, $i, "edate1");
		$announcers[$i] = mysql_result($result, $i, "announcer");
		$urls[$i] = mysql_result($result, $i, "site");
		$types[$i] = mysql_result($result, $i, "type");
		$filenames[$i] = mysql_result($result, $i, "filename");
		$titles[$i] = mysql_result($result, $i, "title");
		$categories[$i] = mysql_result($result, $i, "category");
		$thisID[$i] = mysql_result($result, $i, "id");
	}

        if ( $newer !=  "0" ) {
	  $dates = array_reverse($dates);
	  $announcers = array_reverse($announcers);
	  $urls = array_reverse($urls);
	  $types = array_reverse($types);
	  $filenames = array_reverse($filenames);
          $titles = array_reverse($titles);
	  $categories = array_reverse($categories);
	  $thisID = array_reverse($thisID);

        }


	$totalurls = $i;


	if ($totalurls == 0)
		die("No entries found.<BR>");

	return array ($rows, $dates, $announcers, $urls, $types, $totalurls, $filenames, $titles, $categories, $thisID);
}

/* display a line in table */
function db_display($id) {

  global $dates, $announcers, $urls, $types, $authorization, $cells_bg, $cells_bg2, $font_size, $filenames, $titles, $categories, $thisID, $GooglePlus1, $lastdate, $myHidePreviewImgs, $img_path, $img_folder;

  if ($myHidePreviewImgs)
    echo "<tr valign=\"bottom\" height=\"350\" bgcolor=";
  else
#    echo "<tr bgcolor=";
    echo "<tr";

  $postDateTime = strtotime($dates[$id]);
  $lastDateTime = strtotime($lastdate);
  $today = time();
  $since = $today - $postDateTime;
  $readDateTime = time_since($since, $postDateTime);


#  if ($postDateTime > $lastDateTime)
#        echo $cells_bg2;
#      else
#  	echo $cells_bg;

  echo ">\n";
  
  #echo "><td colspan=\"3\" width=\"100\"><font size=$font_size>";
  #
  #echo $readDateTime;
  #
  #echo "</font></td>\n";
  #
  echo "<td class=\"col-sm-1 nick\">&lt;";


    if($types[$id] == "twitter")
      echo "@$announcers[$id]&gt;</td>\n";
    elseif ($types[$id] == "pocket")
      echo "#$announcers[$id]&gt;</td>\n";
    else
      echo "$announcers[$id]&gt;</td>\n";

#	  echo "<td colspan=\"2\" style=\"WORD-BREAK:BREAK-ALL; background-image: url(/links/screen/shot.php?url=ngtr.uk.to);\">";

	if ($categories[$id]) 
		echo "<td style=\"WORD-BREAK:BREAK-ALL\">";
	elseif ($myHidePreviewImgs)
	  echo "<td colspan=\"2\" style=\"WORD-BREAK:BREAK-ALL;\" class=\"myrow-$id\">";
	else
	  echo "<td class=\"col-sm-11\">";
#	  echo "<td class=\"col-sm-11\" style=\"WORD-BREAK:BREAK-ALL\">";

	if ( $GooglePlus1 == 1 ) 
	  echo "<div style=\"float:right;\"><g:plusone size=\"small\" href=\"$url\"></g:plusone></div>\n";
	else
	  echo "\n";


#echo "<font size=$font_size>";

	if ($titles[$id])
	   echo $titles[$id] . "<br>\n";


	$text = explode(" ", $urls[$id]);

	foreach ($text as $word) {

		if( strlen($filenames[$id]) > 0 ) {
			$filepath = $img_path . "/" . $img_folder . "/" . $filenames[$id];
		} else {
			$filepath = "";
		}

		$url = "";
		#echo $thisID[$id] . " ";

		if (preg_match("/^http(s)?:\/\//i",$word)) {

			$cutword = substr($word,0,150);
			$url = $word;
			
			echo "<a target=\"_blank\" href=\"$word\">$cutword</a> \n";

		     if( strlen($filepath) > 0 ) 
			 #echo "- <a target=\"_blank\" href=\"thumbs2.php?id=$thisID[$id]\">(cache)</a>\n";
			 echo "- <a target=\"_blank\" href=\"$filepath\">(cache)</a>\n";


		} elseif (preg_match("/^www\./i",$word)) { 

			$url = str_replace("www.", "http://www.", $word);
			$cutword = substr($word,0,150);
			echo "<a target=\"_blank\" href=\"$url\">$cutword</a> \n";

		if( strlen($filepath) > 0 ) 
			echo "- <a target=\"_blank\" href=\"$filepath\">(cache)</a>\n";

		} elseif (preg_match("/http(s)?:\/\//i",$word)) {
		
			$pretext = substr($word, 0, strpos($word, 'http'));
			$url = substr($word, strpos($word, 'http'));
			$cutword = substr($url,0,150);
			echo "$pretext<a target=\"_blank\" href=\"$url\">$cutword</a> \n";

		if( strlen($filepath) > 0 ) 
			echo "- <a target=\"_blank\" href=\"$filepath\">(cache)</a>\n";

		} elseif (preg_match("/www\./i",$word)) { 

			$pretext = substr($word, 0, strpos($word, 'www'));
			$url = substr($word, strpos($word, 'www'));
			$urlfixed = str_replace("www.", "http://www.", $url);
			$cutword = substr($urlfixed,0,150);
			echo "$pretext<a target=\"_blank\" href=\"$urlfixed\">$cutword</a> \n";

		if( strlen($filepath) > 0 ) 
			echo "- <a target=\"_blank\" href=\"$filepath\">(cache)</a>\n";

		} else {

			$cutword = substr($word,0,150);
			$url = $word;
			echo "$cutword \n";

		}



		# Add utube link
		#if (preg_match("/youtube\.com/i",$word) || preg_match("/vimeo\.com/i",$word)) 
		#	echo "- <a target=\"_blank\" href=\"utube.php?id=$thisID[$id]\">(vid)</a>\n";
		#elseif (preg_match("/soundcloud\.com/i",$word)) 
		#	echo "- <a target=\"_blank\" href=\"scloud.php?id=$thisID[$id]\">(scloud)</a>\n";
			  
		
	}

	echo "&nbsp;|&nbsp;$readDateTime";

	#echo "<style type=\"text/css\">.myrow-$id { background-image: url(/links/screen/shot.php?url=$url&w=1024&h=768&clipw=1024&cliph=768); background-repeat:no-repeat; background-position:0px 0px; background-size: contain } </style>";


	echo "</td>\n";
	
	#if ($categories[$id]) {
	#	echo "<td><font size=$font_size>\n";
	#	echo "$categories[$id]\n";
	#	echo "</font></td>\n";
	#}

	#echo "<td><font size=$font_size><a target=\"_blank\" href=$types[$id]://$urls[$id]>$types[$id]://$urls[$id]</a></font></td>\n";

	if ($authorization == 1) {
	
		?>
		<form action="administration.php" method="POST">
		<td>
			<input value=<?php echo $urls[$id]?> name=edit_url type=hidden>
			<input value="Edit" type="image" name=edit src="images/edit.png">
			<input value=<?php echo $urls[$id]?> name=edit_url type=hidden>
			<input value="Delete" type="image" name=delete src="images/delete.png">
		</td>
		</form>
		<?php
	}

	echo "</tr>\n";
}

#facebook stuff

function parse_signed_request($signed_request, $secret) {
  list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

  // decode the data
  $sig = base64_url_decode($encoded_sig);
  $data = json_decode(base64_url_decode($payload), true);

  if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
    error_log('Unknown algorithm. Expected HMAC-SHA256');
    return null;
  }

  // check sig
  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
  if ($sig !== $expected_sig) {
    error_log('Bad Signed JSON signature!');
    return null;
  }

  return $data;
}

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_', '+/'));
}


/* End of Functions */


?>