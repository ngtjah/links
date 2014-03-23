#!/usr/bin/perl


package Links; 


require Exporter; 
@ISA = (Exporter);


@EXPORT = qw( new dupe_check get_remote_server_mimetype create_img_filename download_image get_local_mimetype create_thumbnail move_thumbnail_to_s3_bucket thumbnail_failed image_download_failed get_title db_insert_site db_bump_site bot_bump_site twitter_update_site );
 
sub new {

	my $this = shift;
	my $class = ref($this) || $this;
	#my $self = {};
        my $self = {@_ };

	bless $self, $class;

        printf "Initialize Links Object\n"; 
	

        return $self;

}


sub pre_parse_irc {

    my $self = shift;

    my @segments = split( / /, $self->{'parseline'} );

    #Get the date/time
    @date_time = split( /:/, substr( shift(@segments), 1, -1 ) );
    $self->{'date'} = $date_time[3] . " " . $date_time[0] . ":" . $date_time[1] . ":" . $date_time[2];

    #Parse the Announcer
    my $unparsed_announcer = substr( $segments[0], 1, -1 );
       $self->{'announcer'} = parse_announcer($unparsed_announcer);

    shift @segments;

    $self->{'body'} = join( " ", @segments );

    
    #Strip anonym.to so we can get the title
    $self->{'body'} =~ s/http:\/\/anonym\.to\/\?//i;

    #Set the Type
    $self->{'type'} = 'irc';



}


sub extract_url {

    my $self = shift;

    my $www_url;

    #Extract the first URL
    if ( $self->{'body'} =~ / / ) {

	my @www_segments_spaces = split( / /, $self->{'body'} );
        my @www_segments_grep =
           grep( /(http(s)?:\/\/|www\.|\.com)/i, @www_segments_spaces );
	   $www_url = $www_segments_grep[0];

    } else {

	$www_url = $self->{'body'};

    }

    #Add the http if it isn't there
    $www_url =~ s/^www\./http\:\/\/www\./i;

    #URL Encode Site
    #$encode_site = uri_escape($self->{'body'});

    $self->{'www_url'} = $www_url;

}


sub dupe_check {

    my $self = shift;

    print "Checking existance of site in database..\n" if $main::debug;

    my $quoted_site = $main::dbh->quote($self->{'body'});
    my $sql         = "SELECT id, site, announcer, date_format(edate, '%m/%d/%y %H:%i:%s') as edate1 FROM links WHERE site like $quoted_site LIMIT 1";
    my $sth         = $main::dbh->prepare($sql);
       $sql_exists  = $sth->execute;
    my $sql_rows    = $sth->rows;
    
    my $og_id;
    my $og_site;
    my $og_announcer;
    my $og_edate;
    
    $sth->bind_columns( \my( $dupe_id, $dupe_site, $dupe_announcer, $dupe_edate ) );
    
    if ( $sql_exists && $sql_rows > 0 ) { 

        while($sth->fetch()) {
        
            $self->{'og'}{'og_id'}        = $dupe_id;
            $self->{'og'}{'og_site'}      = $dupe_site;
            $self->{'og'}{'og_announcer'} = $dupe_announcer;
            $self->{'og'}{'og_edate'}     = $dupe_edate;
        
        } 

	$self->{'isdupe'} = 1;

    } else {

	$self->{'isdupe'} = 0;

    }


    return $self->{'isdupe'};

}


sub get_remote_server_mimetype {

    use LWP::UserAgent;

    my $self = shift;

    #Setup the UserAgent
    my $ua = new LWP::UserAgent(timeout=>15);
       #$ua->show_progress(1);

    #Lets get the MIME Type from the Headers
    my $req = HTTP::Request->new(HEAD => $self->{'www_url'} );
       $req->header('Accept' => 'text/html');

    my $res = $ua->request($req);
       
       $self->{'mimetype'} = $res->content_type;
       $self->{'mimetype_returncode'} = $res->code;

    if ( $res->is_success ) {

	#Set the www_url to the resolved URI in case we got a 301 or 302 redirect
	$self->{'www_url'} = $res->request()->uri->as_string;

	print "Remote Server Mime Type: $self->{'mimetype'} \n" if $main::debug;

    }

    return $res->is_success;


}


sub create_img_filename {

    use URI::Escape;
    use MIME::Types qw(by_suffix by_mediatype);

    my $self = shift;

    my $filename;
    my $domain;
    my $filename_unescaped;
    my $full_path;

    #If the URL contains the filename grab it.
    if ( $self->{'www_url'} =~ /.*filename\=.*\.(jpg|jpeg|png|gif).*/i ) {
    
        #Extract the file name from the URL Query String (yfrog);
        my @www_segments_filenameeq = split( /\&/, $self->{'www_url'} );
        my @filename_grep11 =
             grep( /.*filename.*/i, @www_segments_filenameeq );

        $filename = $filename_grep11[0];
        $filename =~ s/filename\=//;
    
    } elsif ( $self->{'www_url'} =~ /.*\.(jpg|jpeg|png|gif).*/i ) {
    
        #Extract the file name from the URL (if it is there)
    	my @www_segments_fwdslash = split( /\//, $self->{'www_url'} );
    	my @filename_grep =
    	     grep( /.*\.(jpg|jpeg|png|gif).*/i, @www_segments_fwdslash );
    	$filename = $filename_grep[-1];
    	
    	#Remove stuff at the end if there are URL variables, like asdf.gif?width=213 etc.
    	if ( $filename =~ /.*\.(jpg|jpeg|png|gif)\?.*/i ) {
    
    		my @filename_questionmarks = split( /\?/, $filename );
    		$filename = $filename_questionmarks[0];
    
    	}

	print "File name created from URL: $filename \n" if $main::debug;
    
    } else { # This is where we need to make up a file name because its NOT in the URL
    
        #Get the domain name
        $domain = $self->{'www_url'};
        $domain =~ s/http\:\/\///i;
    
        my @www_url_fwdslash = split( /\//, $domain );
           $domain           = $www_url_fwdslash[0];
    
        my $range              = 10000;
        my $random_number      = int( rand($range) );
    
        my @mediatype = by_mediatype($mimetype);
        my $file_ext  = $mediatype[0][0];
           $filename  = $domain . "_" . $random_number . "." . $file_ext;
    
        print "File name created by script: $filename \n" if $main::debug;
    
    }


    #Setup the file paths
    $filename_unescaped = uri_unescape($filename);
    $full_path          = $ConfigLinks::imgpath . "/" . $ConfigLinks::imgfolder . "/" . $filename_unescaped;
    
    # If the filename already exists select a new name for the file so we don't overwrite
    # OR if s3 is Enabled we want to check the db for this file to see if it exists before we push it to S3
    if ( -e $full_path || $ConfigLinks::s3_enable == 1 ) {
    
        my $quoted_file = $main::dbh->quote($filename_unescaped);
        my $sql         = "SELECT * FROM links WHERE filename = $quoted_file";
    	my $sth         = $main::dbh->prepare($sql);
    	my $exists      = $sth->execute;
    	my $rows        = $sth->rows;
    
    	if ( $exists && $rows > 0 ) {   #If the file exists and it's in the DB lets rename it.
    
    		my $range              = 10000;
    		my $random_number      = int( rand($range) );
    		my @file_name_splitdot = split( /\./, $filename );

		#Set all of the filename params with the new name
    		$filename              = $file_name_splitdot[0] . "." . $random_number . "." . $file_name_splitdot[-1];
    		$filename_unescaped    = uri_unescape($filename);
    		$full_path             = $ConfigLinks::imgpath . "/" . $ConfigLinks::imgfolder . "/" . $filename_unescaped;
    		
    		print "A file with the name already exists renaming img file to: $full_path \n" if $main::debug;
    
    	} else {   #Else the file exists but it's NOT in the DB so lets just overwrite it.
    
    	    if ( $ConfigLinks::s3_enable == 0 ) {

    		print "A file with the same name already exists but it's not in the DB so we'll overwrite it: $full_path \n" if $main::debug;

    	    }

    	}
    
    }

    $self->{'full_path'}          = $full_path;
    $self->{'filename_unescaped'} = $filename_unescaped;
    $self->{'filename'}           = $filename;

    return $self->{'filename'};

}



sub download_image {

    use LWP::UserAgent;

    $self = shift;

    print "Attempting to Download $self->{'www_url'} IMG file to Path: $self->{'full_path'} \n" if $main::debug;

    #Setup the UserAgent
    my $ua = new LWP::UserAgent(timeout=>15);

    #Go GET the img file
    my $req = new HTTP::Request 'GET', $self->{'www_url'};
    my $res = $ua->request( $req, $self->{'full_path'} );

    $self->{'imagedownload_returncode'} = $res->code;
    #$self->{'return'}{'headers'} = $res->headers_as_string;

    return $res->is_success;

}



sub get_local_mimetype {

    $self = shift;

    use File::LibMagic;

    #I found instances where the default mime type was incorrect but libmagic was correct
    my $flm = File::LibMagic->new;

    #somehow we got escaped files in the directory this is a hack
    #If the file exists then it must be unescaped
    if ( -e $self->{'full_path'} ){
    
        $self->{'mimetype'} = $flm->checktype_filename($ConfigLinks::imgpath . "/" . $ConfigLinks::imgfolder . "/" . $self->{'filename_unescaped'});
    
    } else {
    
        $self->{'mimetype'} = $flm->checktype_filename($ConfigLinks::imgpath . "/" . $ConfigLinks::imgfolder . "/" . $self->{'filename'});
    
        $self->{'full_path'} = $ConfigLinks::imgpath . "/" . $ConfigLinks::imgfolder . "/" . $self->{'filename'};
    
    }
    
    print "libmagic file type: $self->{'mimetype'}\n" if $main::debug;
    
    #If the file is labeled as not a GIF but its actually a gif rename it.
    #I found ImageMagick doesn't like this.
    if ( $self->{'www_url'} !~ /.*\.(gif)$/i && $self->{'mimetype'} =~ /image\/gif.*/) {
    
        my $full_path_new = $self->{'full_path'};
        $full_path_new = $self->{'full_path'} . '.gif';
        
        print "Found a GIF posing as another Image RENAME: $self->{'full_path'} $full_path_new\n" if $main::debug;
        rename $self->{'full_path'}, $full_path_new;
    
        $self->{'full_path'} = $full_path_new;
        $self->{'filename_unescaped'} = $self->{'filename_unescaped'} . ".gif";
        $self->{'filename'} = $self->{'filename'} . '.gif';

    }

    
    return 1;



}



sub create_thumbnail {

    require Image::Magick;

    $self = shift;

    #Set this path because I found if we try to scale down a large gif we will fill /tmp and crash
    $ENV{MAGICK_TEMPORARY_PATH} = $ConfigLinks::image_magick_tmp_path;

    $self->{'thumbnail_full_path'} = $ConfigLinks::imgpath . "/" . $ConfigLinks::thumbfolder . "/thumb_" . $self->{'filename_unescaped'};
    $self->{'thumbnail_filename_unescaped'} = "thumb_" . $self->{'filename_unescaped'};

    #Desired thumbnail Height and Width
    my $MaxThumbHeight = 160;
    my $MaxThumbWidth  = 300;
    
    #JPEG quality. 0-100, 100 is the best quality, 0 is the best compression
    my $Quality = 60;
    
    my $magick = Image::Magick->new;
    my $status = $magick->Read($self->{'full_path'});
    warn "$status" if "$status";
    
    print "IMG Conversion Status: $status \n" if $status;
    
    #If the Image was read into imagemagick successfully continue on and make the thumbnail
    if ( !$status ) {
    
        my $thumbImg = $magick->Clone;
        
        my $Width  = $thumbImg->Get("width") + 0;
        my $Height = $thumbImg->Get("height") + 0;
        
        #Set these so we can store in the db
        $self->{'MWidth'} = $Width;
        $self->{'MHeight'} = $Height;
        
        my $hScale = $MaxThumbHeight / $Height;
        my $wScale = $MaxThumbWidth / $Width;
        
        #Only Scale the thumb for images larger than our max height/width + buffer
        if ( $Height > $MaxThumbHeight + 1 ) {
        	$thumbImg->Scale(
        		width  => $Width * $hScale,
        		height => $MaxThumbHeight
        	);
        }
        elsif ( $Width > $MaxThumbWidth + 1) {
        	$thumbImg->Scale(
        		height => $Height * $wScale,
        		width  => $MaxThumbWidth
        	);
        }
        
        #Do it again in case we only got the height and it's still too wide.
        $Width  = $thumbImg->Get("width") + 0;
        $Height = $thumbImg->Get("height") + 0;
        $hScale = $MaxThumbHeight / $Height;
        $wScale = $MaxThumbWidth / $Width;
        
        if ( $Width > $MaxThumbWidth + 1) {
        	$thumbImg->Scale(
        		height => $Height * $wScale,
        		width  => $MaxThumbWidth
        	);
        }
        
        $Width  = $thumbImg->Get("width") + 0;
        $Height = $thumbImg->Get("height") + 0;
        
        $self->{'TWidth'} = $Width;
        $self->{'THeight'} = $Height;
        
        #Write the thumbnail file
        $thumbImg->Set( quality => $Quality );
        $thumbImg->Write($self->{'thumbnail_full_path'});


    } #If Status


    return $status;


}




sub move_thumbnail_to_s3_bucket {

    require Net::Amazon::S3;

    $self = shift;

    my $s3 = Net::Amazon::S3->new(
        {   aws_access_key_id     => $ConfigLinks::aws_access_key_id,
            aws_secret_access_key => $ConfigLinks::aws_secret_access_key,
            retry                 => 1,
        }
    );

    die "Could not connect to S3" unless defined $s3;
    
    my $bucket = $s3->bucket($ConfigLinks::aws_bucket);
    die "Could not get the bucket $ConfigLinks::aws_bucket" unless $bucket;
    
    #Send full image to S3
    die "File $self->{'full_path'} does not exist or is not readable" unless -f $self->{'full_path'} && -r $self->{'full_path'};
    
    my $response = $bucket->add_key_filename(
                        $ConfigLinks::imgfolder . "/" . $self->{'filename_unescaped'},
                        $self->{'full_path'},
	                { content_type => $self->{'mimetype'}, },
        )
       or die sprintf ("%s: %s", $s3->err, $s3->errstr);
    
    print "Successfully uploaded $self->{'full_path'} into $self->{'filename_unescaped'} in bucket $ConfigLinks::aws_bucket.\n" if $main::debug;

    if ( $ConfigLinks::s3_delete_local_imgs == 1 ) {

	unlink($self->{'full_path'});

	print "Deleting file from server: $self->{'full_path'}\n" if $main::debug;

    }
    
    
    #Send Thumbnail to S3
    die "File $self->{'thumbnail_full_path'} does not exist or is not readable" 
	unless -f $self->{'thumbnail_full_path'} && -r $self->{'thumbnail_full_path'};
    
    
    my $response_thumb = $bucket->add_key_filename(
                         $ConfigLinks::thumbfolder . "/" . $self->{'thumbnail_filename_unescaped'},
                         $self->{'thumbnail_full_path'},
                         { content_type => $self->{'mimetype'}, },
        )
       or die sprintf ("%s: %s", $s3->err, $s3->errstr);
    
    print "Successfully uploaded $self->{'thumbnail_full_path'} into $self->{'thumbnail_filename_unescaped'} "
	                                                       . "in bucket $ConfigLinks::aws_bucket.\n" if $main::debug;
    

    if ( $ConfigLinks::s3_delete_local_imgs == 1 ) {

	unlink($self->{'thumbnail_full_path'});

	print "Deleting file from server: $self->{'thumbnail_full_path'}\n" if $main::debug;

    }




}


sub thumbnail_failed {

    #!!!!!!!!!!!!!!!!!!!!Need to test this!!!!!!!!!!!!!!!!!!!!!!!!

    $self = shift;

    #Delete the original file, it wasn't an image or something went wrong with ImageMagick.
    #Undefine this variable so we don't insert the filename because the download failed.
    undef $self->{'filename_unescaped'};
    unlink($self->{'full_path'});
    $self->{'failed_to_convert_img'} = 1;

    print "Image Magick Failed to make the Thumb so we are going to delete the file. \n" if $main::debug;

    return 1;

}


sub image_download_failed {

    #!!!!!!!!!!!!!!!!!!!!Need to test this!!!!!!!!!!!!!!!!!!!!!!!!

    $self = shift;

    #Undefine this variable so we don't insert the filename because the download failed.
    undef $self->{'filename_unescaped'};
    $self->{'failed_to_convert_img'} = 1;

    print "Image Download Failed. Return Code: $self->{'imagedownload_returncode'} \n" if $main::debug;
    #print "Headers: $headers \n" if $main::debug;

}




sub get_title {

    use LWP::UserAgent;
    use Encode;

    $self = shift;

    my $ua = new LWP::UserAgent;
       $ua->timeout(60);

    #Go GET the webpage
    my $res = $ua->get( $self->{'www_url'} );
    
    #If the Get worked lets get the title
    if ( $res->is_success ) {

	#If the title is there, set it.
	if ( $res->header('Title') ) {


	    my $utf8flag = Encode::is_utf8( $res->header('Title') );

	    if ( $utf8flag == 1 ) {
	    	
	       $self->{'title'} = Encode::encode('UTF-8', $res->header('Title'));
	    	
	    } else {
       
	       $self->{'title'} = $res->header('Title');

	    }
	    
	    print "Title: " . $self->{'title'} . "\n" if $main::debug;

	}
    
    }


}




sub db_insert_site {

    $self = shift;

    #Insert the Site into the Database.
    print "Entering site...\n" if $main::debug;

    my $quoted_site       = $main::dbh->quote($self->{'body'});
    my $quoted_announcer  = $main::dbh->quote($self->{'announcer'});
    my $quoted_type       = $main::dbh->quote($self->{'type'});
    my $quoted_title      = $main::dbh->quote($self->{'title'});
    my $quoted_filename   = $main::dbh->quote($self->{'filename_unescaped'});
    my $quoted_TWidth     = $main::dbh->quote($self->{'TWidth'});
    my $quoted_THeight    = $main::dbh->quote($self->{'THeight'});
    my $quoted_MWidth     = $main::dbh->quote($self->{'MWidth'});
    my $quoted_MHeight    = $main::dbh->quote($self->{'MHeight'});
    my $quoted_date       = $main::dbh->quote($self->{'date'});
    my $quoted_appid      = $main::dbh->quote($self->{'appid'});

    $sql = "INSERT INTO links (site, announcer, edate, type, title, filename, twidth, theight, width, height, appid) "
	    . "VALUES ($quoted_site, $quoted_announcer, $quoted_date, $quoted_type, $quoted_title, $quoted_filename, "
	    . "$quoted_TWidth, $quoted_THeight, $quoted_MWidth, $quoted_MHeight, $quoted_appid)";

    print "MYSQL:" . $sql . "\n";

    
    my $sth = $main::dbh->prepare($sql);
    $main::entries += $sth->execute;


}



sub db_bump_site {

    $self = shift;

    print "Site already in db, bumping site...\n" if $main::debug;

    my $quoted_site  = $main::dbh->quote($self->{'body'});
    
    $sql = "UPDATE links set edate = '$self->{'date'}' where site = $quoted_site";

    print "MYSQL:" . $sql . "\n";

    $sth = $main::dbh->prepare($sql);
    $main::entries += $sth->execute;



}




sub bot_bump_site {

    require Net::Telnet;

    $self = shift;

    my $telnet = new Net::Telnet ( Timeout=>10,
                                   Errmode=>'die',
                                   Port=>$ConfigLinks::botTcpPort);
                                   #Output_log=> 'output.txt'


    my $chatline = '.say #lanfoolz ' . 'URL bumped by ' . $self->{'announcer'} 
                                 . ', original by ' . $self->{'og'}{'og_announcer'} . ' on ' . $self->{'og'}{'og_edate'};
    
    eval{
    
        $telnet->open($ConfigLinks::botHostname);
        $telnet->waitfor('/Nickname\..*$/i');
        $telnet->print($ConfigLinks::botUsername);
        $telnet->waitfor('/Enter your password\..*$/i');
        $telnet->print($ConfigLinks::botPassword);
        $telnet->waitfor('/.*joined\ the\ party\ line\..*/i');
        $telnet->print($chatline);
    
    };


    return 1;


}


sub bot_announce_site {

    require Net::Telnet;

    $self = shift;

    my $chatline;
    my $decode_site = uri_unescape($self->{'body'});

    my $telnet = new Net::Telnet ( Timeout=>10,
                                   Errmode=>'die',
                                   Port=>$ConfigLinks::botTcpPort);
                                   #Output_log=> 'output.txt'


    if ($self->{'type'} eq "twitter") {

                    $chatline = '.say #lanfoolz ' . '<@' . $self->{'announcer'} . '> ' . $decode_site;

    } else {

	if ($self->{'title'}) {

                    $chatline = '.say #lanfoolz ' . '<#' . $self->{'announcer'} . '> ' . $decode_site . ' - ' . $self->{'title'};

	} else {

                    $chatline = '.say #lanfoolz ' . '<#' . $self->{'announcer'} . '> ' . $decode_site;

	}

    }

    
    eval{
    
        $telnet->open($ConfigLinks::botHostname);
        $telnet->waitfor('/Nickname\..*$/i');
        $telnet->print($ConfigLinks::botUsername);
        $telnet->waitfor('/Enter your password\..*$/i');
        $telnet->print($ConfigLinks::botPassword);
        $telnet->waitfor('/.*joined\ the\ party\ line\..*/i');
        $telnet->print($chatline);
    
    };


    return 1;


}




sub twitter_update_site {

    require WWW::Shorten::Bitly;
    require Net::Twitter;
    require Net::Twitter::OAuth;
    Net::Twitter -> import if Net::Twitter -> can ("import");
    Net::Twitter::OAuth -> import if Net::Twitter::OAuth -> can ("import");


    $self = shift;

    my $ban_domain = 0;
    my $quote_ban_domain_twitter  = quotemeta($ConfigLinks::ban_domain_twitter);
    my $quote_ban_domain_twitter2 = quotemeta($ConfigLinks::ban_domain_twitter2);

    #Stop the tweet if it is in the ban list.
    if ( length($quote_ban_domain_twitter) > 0 || length($quote_ban_domain_twitter2) > 0 ) {

	if ( length($quote_ban_domain_twitter) > 0 ) {

	    if ( $self->{'body'} =~ /$quote_ban_domain_twitter/i )  {

		print "Not tweeting because its in the ban_domain_twitter list: $ConfigLinks::ban_domain_twitter \n" if $main::debug;
		$ban_domain = 1;

	    }

	}

	if ( length($quote_ban_domain_twitter2) > 0 ) {

	    if ( $self->{'body'} =~ /$quote_ban_domain_twitter2/i ) {
    
	        print "Not tweeting because its in the ban_domain_twitter list: $ConfigLinks::ban_domain_twitter2 \n" if $main::debug;
	        $ban_domain = 1;

	    }

	}

    }  # If length of ban_domain_twitter > 0

    if ( $ban_domain == 0 ) {
    
        print "Tweeting... \n" if $main::debug;
        
        #Twitter lol
        my $nt = Net::Twitter->new(
        	traits              => ['API::RESTv1_1', 'OAuth'],
        	consumer_key        => $ConfigLinks::consumer_key,
        	consumer_secret     => $ConfigLinks::consumer_secret,
                ssl                 => 1,
        	);
         	
        #You'll save the token and secret in cookie, config file or session database
            #my($access_token, $access_token_secret) = restore_tokens();

        if ($ConfigLinks::access_token && $ConfigLinks::access_token_secret) {
            $nt->access_token($ConfigLinks::access_token);
            $nt->access_token_secret($ConfigLinks::access_token_secret);
        }
        
        unless ( $nt->authorized ) {

            # The client is not yet authorized: Do it now
            print "Authorize this app at ", $nt->get_authorization_url, " and enter the PIN#\n";
        
            my $pin = <STDIN>; # wait for input
            chomp $pin;
        
            my($access_token, $access_token_secret, $user_id, $screen_name) = $nt->request_access_token(verifier => $pin);
            #save_tokens($access_token, $access_token_secret); # if necessary
            print "SAVE THIS AT: $access_token  ATS: $access_token_secret"; # if necessary

        }
    
        my $titlelen = 0;
        my $urllen   = 0;
    
        $urllen = length($self->{'www_url'});

        if($self->{'title'}) { $titlelen = length($self->{'title'}) };

        my $totallen = $urllen + $titlelen;

        # If the total length of the tweet is greater than the max tweet size (140) and the url is actually longer than a normal bitly url.
	# Bitly it
        if ( $totallen > 140 && $urllen > 14 ) {

            $self->{'tweet_bitly'} = WWW::Shorten::Bitly::makeashorterlink($self->{'www_url'}, $ConfigLinks::bitly_account, $ConfigLinks::bitly_api_key); 

	    print "Going to use the bitly URL to tweet this one: $self->{'tweet_bitly'}  \n";


        }

        #Get the final lengths to see if the URL and the title together are still too long
        if($self->{'title'}) {

            if($self->{'bitly'}) {

                $self->{'tweet_len'}    = length($self->{'title'} . " " . $self->{'tweet_bitly'});

            } else {

                $self->{'tweet_len'}    = length($self->{'title'} . " " . $self->{'www_url'});

            }

        } else {

            if ($self->{'bitly'}) {

                $self->{'tweet_len'}    = length($self->{'tweet_bitly'});

            } else {

                $self->{'tweet_len'}    = length($self->{'www_url'});

            }

        }
 

	my $newtitle_length;

	# If they are still too long lets chop the end off the title
	if ( $self->{'tweet_len'} > 140 ) {

	    my $spaceneeded = ( $self->{'tweet_len'} - 140 );

	    $newtitle_length = $titlelen - $spaceneeded;

	    $self->{'tweet_title'} = substr($self->{'title'},0,$newtitle_length);

	    print "Tweet title cut to fit twitter 140 limit: $self->{'tweet_title'}  \n";

	} else {

	    $self->{'tweet_title'} = $self->{'title'};

	}


        #Setup the tweet text
        if($self->{'tweet_title'}) {

            if($self->{'bitly'}) {

                $self->{'tweet_len'}    = length($self->{'tweet_title'} . " " . $self->{'tweet_bitly'});
                $self->{'tweet'}        = $self->{'tweet_title'} . " " . $self->{'tweet_bitly'};

            } else {

                $self->{'tweet_len'}    = length($self->{'tweet_title'} . " " . $self->{'www_url'});
                $self->{'tweet'}        = $self->{'tweet_title'} . " " . $self->{'www_url'};

            }

        } else {

            if ($self->{'bitly'}) {

                $self->{'tweet'}    = $self->{'tweet_bitly'};

            } else {

                $self->{'tweet'}    = $self->{'www_url'};

            }

        }
        
        
        print "Tweeting: $self->{'tweet'}\n";
            
        eval{

    	    # Tweet it
    	    $nt->update({ status => $self->{'tweet'} });

        };
        
        if ( my $err = $@ ) {
            die $@ unless $err->isa('Net::Twitter::Error');
            #die $@ unless blessed $err && $err->isa('Net::Twitter::Error');
        
            warn "HTTP Response Code: ", $err->code, "\n",
                 "HTTP Message......: ", $err->message, "\n",
                 "Twitter error.....: ", $err->error, "\n";
    
        }
    
    
    } # if ban_domain == 0



}




sub parse_announcer {
    my ($nick) = @_;

    #Remove @ and + from nick
    $nick =~ s/[\+@]//g;
    
    #Remove trailing underscores
    $nick =~ s/_*$//g;

    my $main_nick = $nick;

    #Escape the Pipe so the regex doesn't try to evaluate it.
    $nick =~ s/\|/\\\|/g;

    foreach (@main::nicks) {
	if (/$nick/i) {

	    print "Original Nick: $main_nick \n" if $main::debug;

	    @alternate_nicks = split(/:/);
	    $main_nick         = $alternate_nicks[0];

	    print "New Nick: $main_nick \n" if $main::debug;

	    return $main_nick;
	}
    }

    return $main_nick;

}









1;
