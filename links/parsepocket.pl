#!/usr/bin/perl -w
#
# parselogs.pl - Search logs for URLs
# Copyright (C) 2000 Zachary P. Landau <kapheine@hypa.net>
#
#   This program is free software; you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation; either version 2 of the License, or
#   (at your option) any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program; if not, write to the Free Software
#   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#

#
# Format of logs: [TIME-DATE] <nickname> text
# Example: [20:15:03:2014-01-03] <clicking> hitup http://www.redlightmusic.co.uk
#

use strict;
use warnings;

use lib '/home/jah/links';
#Load the Config File
use ConfigLinks;


use DBI;
use WebService::Pocket::Lite;


#Local Class
use Links::Pocket;


#Debug
our $version = '3.00';
our $debug   = 1;

# Global Vars
our $dbh;
our $drh;
our $entries   = 0;      # num entries found
our @nicks;              # array of nicknames (: seperated)
our @alternate_nicks;    # array of alt. nicknames

my $pocketusers;
my $id = 0;
my %insert_list = ();
my $count = -1;



# connects to mysql database and grab authorized pocket users
sub startup {

	my $dsn = "DBI:$ConfigLinks::driver:database=$ConfigLinks::database;host=$ConfigLinks::hostname;port=$ConfigLinks::port";
	   $dbh = DBI->connect( $dsn, $ConfigLinks::user, $ConfigLinks::password ) || die("Cannot connect to database\n");
	   $drh = DBI->install_driver("mysql");

	#Get the users
	my $sql1               = "SELECT username, consumer_key from pocketusers;";
	   $pocketusers        = $dbh->prepare($sql1);
	my $pocketusers_exists = $pocketusers->execute;

	if ( $pocketusers_exists == 0) {
	    die("No Pocket Users in Database\n");
	}

}

# Import alternate nick file
sub get_nicks {
	my ($file) = @_;

	open( NICKFILE, $file ) or die "$0: Can't open $file for reading: $!\n";

	while (<NICKFILE>) {
		chomp;
		s/#.*//;
		s/^\s+//;
		s/\s+$//;
		next unless length;
		push( @nicks, $_ );
	}

	close(NICKFILE);
}


sub parse_pocket {

    # Each Authorized Pocket User
    while (my @pocket_local_users = $pocketusers->fetchrow_array()) {
    
        my $lite_res;
        my $username = $pocket_local_users[0];
        my $myURL_meta = quotemeta($ConfigLinks::my_url);

        print "\tRunning Pocket for: $username\n";
        
        #Get the last pocket id for this user
        my $sql             = "SELECT IFNULL(max(appid),0), IFNULL(max(UNIX_TIMESTAMP(edate)),0) FROM links where type = 'pocket' and announcer = '$username'";
        my $sth             = $dbh->prepare($sql);
        my $pocketid_exists = $sth->execute;
        
        my @pocketuserdata1 = $sth->fetchrow;
        my $MAXpocketid     = $pocketuserdata1[0];
        my $MAXpocketdate   = $pocketuserdata1[1];
        
        
        #Pocket
        my $lite = WebService::Pocket::Lite->new(
                   access_token => $pocket_local_users[1],
                   consumer_key => $ConfigLinks::pocket_consumer_key
        );
    
    
        print "Scanning Pocket since: $MAXpocketdate \n" if $debug;
    
        if ( $MAXpocketdate == 0 ) {
    
	    $lite_res = $lite->retrieve( {state => 'all'} );
    
        } else {
    	
	    $lite_res = $lite->retrieve( {state => 'all', since => $MAXpocketdate} );
    
        }
    
    
	# If Pocket came back with something lets check it out
        if (%$lite_res) {
    
            hash_walk(\%$lite_res, [], \&print_keys_and_value);
    	
	    # Each Pocket Post Retrieved
            for my $i (0..$count) {
    
		my $Pocket = new Links::Pocket(appid=>$insert_list{$i}{'pocketid'},time_added=>$insert_list{$i}{'time_added'},body=>$insert_list{$i}{'resolved_url'},announcer=>$username);

		$Pocket->pre_parse_pocket;

		$Pocket->extract_url;

    	        #Process the line if it has a url AND it's not our own site
    	        if ( $Pocket->{'www_url'} =~ /(http(s)?:\/\/|www\.|\.com)/i && $Pocket->{'www_url'} !~ /$myURL_meta/i ) {

    	            print "Pocket: $Pocket->{'www_url'} at $Pocket->{'date'} by $Pocket->{'announcer'} \n" if $debug;

		    $Pocket->dupe_check;

                    #If NOT a DUPE
		    if ( $Pocket->{'isdupe'} == 0 ) {

			print "not dupe\n";

                        # If the inital mimetype could be retrieved successfully
			if ( $Pocket->get_remote_server_mimetype ) {
			    
                            #If it looks like it might be an image AND image download is enabled, lets try to grab it
			    if ( ( $Pocket->{'www_url'} =~ /.*\.(jpg|jpeg|png|gif).*/i || $Pocket->{'mimetype'} =~ /image.*/i ) && $ConfigLinks::img_download_enable == 1 ) {

				$Pocket->create_img_filename;

				if ( $Pocket->download_image ) {

				    $Pocket->get_local_mimetype;

				    if ( ! $Pocket->create_thumbnail ) {

                                        #Send the image and thumbnail to S3 Bucket
					if ($ConfigLinks::s3_enable == 1) {

					    $Pocket->move_thumbnail_to_s3_bucket;

					} # If S3 Enabled

				    } else { # Else create_thumbnail failed

					$Pocket->thumbnail_failed;

				    } # If create thumbnail was a success

			    } else { # Else download image failed

				$Pocket->image_download_failed;

			    } # If download image was a success

			} #If it looks like it might be an image lets try to grab it

                        # If it doesn't look like an image or archive or audio or video. OR if it was a failed image conversion 
			if ( ( $Pocket->{'www_url'} !~ /.*\.(jpg|jpeg|png|gif).*/i && $Pocket->{'www_url'} !~ /.*\.(iso|rar|mp3|avi|mpg|mpeg|zip)/i 
			       && $Pocket->{'mimetype'} !~ /image.*/i &&  $Pocket->{'mimetype'} !~ /(video|application|audio).*/i  ) || ( $Pocket->{'failed_to_convert_img'} ) ) {

			    $Pocket->get_title;

			}

                        $Pocket->db_insert_site;

			if ( $ConfigLinks::bot_enable == 1 ) {

			    $Pocket->bot_announce_site;

			}

			if ($ConfigLinks::twitter_enable == 1) {

			    $Pocket->twitter_update_site;

			}

			print "Announcer : $Pocket->{'announcer'}   URL : $Pocket->{'www_url'}\n\n" if $debug;

		    } else { # If the initial mimetype download could NOT be retrieved successfully


			print "This URL: $Pocket->{'www_url'} doesn't really exist!! Return Code: $Pocket->{'mimetype_returncode'}\n\n" if $debug;

		    } # If the initial mimetype could be retrieved successfully


		} else { #IS a DUPE

                      #We can't bump stuff for now because the twitter ID is not stored on bump
                      #$Pocket->db_bump_site;
                      #
                      #if ( $ConfigLinks::bot_enable == 1 ) {
                      #
                      #    $Pocket->bot_bump_site;
                      #
                      #}

		}  #If NOT a DUPE


		    use Data::Dumper;
		    print Dumper $Pocket;


		}  elsif (  $Pocket->{'www_url'} =~ /$myURL_meta/i ) { # this is our URL

		    print "Didn't parse this line because it matched my_url: $ConfigLinks::my_url -> $Pocket->{'www_url'}\n" if $debug;

		}  #Process the line if it has a url and it's not our own site
    	        
	    } # For each pocket post by this user

	} # If Pocket came back with something (lite res
    
    } # while each user

} # sub parse_pocket




sub hash_walk {
    my ($hash, $key_list, $callback) = @_;
	while (my ($k, $v) = each %$hash) {
	        # Keep track of the hierarchy of keys, in case
	        # our callback needs it.
	    push @$key_list, $k;
	
	    if (ref($v) eq 'HASH') {
	            # Recurse.
		hash_walk($v, $key_list, $callback);
	    }
	    else {
	            # Otherwise, invoke our callback, passing it
	            # the current key and value, along with the
	            # full parentage of that key.
		$callback->($k, $v, $key_list);
	    }
	
	    pop @$key_list;
	}
}


sub print_keys_and_value {
    my ($k, $v, $key_list) = @_;

    if ( grep (/list/, @$key_list) ) {
       
	if (!@$key_list[1]) {

	    return

	}

	if ($id != @$key_list[1]) {

	    $id = @$key_list[1];

	    $count++;

#	    print "@$key_list[1]\n";

	    $insert_list{$count}{'pocketid'} = @$key_list[1];

	}

    if ( grep (/resolved_url|time_added/, $k) ) {

##	printf "k = %-8s  v = %-4s  key_list = [%s]\n", $k, $v, "@$key_list";
#	printf "k = %-8s  v = %-4s\n", $k, $v;

	$insert_list{$count}{$k} = $v;


    }

    }

}






&startup();
&get_nicks($ConfigLinks::nickfile);
&parse_pocket();



if ( $entries == 1 ) {
	print "$entries entry added to database.\n";
}
else {
	printf "$entries entries added to database.\n";
}

print "done.\n";
