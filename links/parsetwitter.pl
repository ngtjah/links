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

use File::Basename;
use lib dirname (__FILE__);

#Load the Config File
use ConfigLinks;


use DBI;
use Net::Twitter;
use Net::Twitter::OAuth;

use Data::Dumper;


#Local Class
use Links::Twitter;


#Debug
our $version = '3.00';
our $debug   = 1;

# Global Vars
our $dbh;
our $drh;
our $entries   = 0;      # num entries found
our @nicks;              # array of nicknames (: seperated)
our @alternate_nicks;    # array of alt. nicknames


my $MAXtweetid;

#Twitter lol
my $nt = Net::Twitter->new(
    traits              => ['API::RESTv1_1', 'OAuth'],
    consumer_key        => $ConfigLinks::consumer_key,
    consumer_secret     => $ConfigLinks::consumer_secret,
    ssl                 => 1,
    );


# sets logfile and connects to mysql database
sub startup {

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
        print "Add this to the config file: Access Token: $access_token  Access Token Secret: $access_token_secret"; # if necessary
    }


    my $dsn = "DBI:$ConfigLinks::driver:database=$ConfigLinks::database;host=$ConfigLinks::hostname;port=$ConfigLinks::port";
    $dbh = DBI->connect( $dsn, $ConfigLinks::user, $ConfigLinks::password ) || die("Cannot connect to database\n");
    $drh = DBI->install_driver("mysql");
    
    #Get the last tweet id in the db
    my $sql            = "SELECT IFNULL(max(appid),0) FROM links where type = 'twitter'";
    my $sth            = $dbh->prepare($sql);
    my $tweetid_exists = $sth->execute;
    
    my @data = $sth->fetchrow;
    $MAXtweetid = $data[0];

}

# read nicknames from nickname file and put them into @nicks
# get_nicks(nickfile)
#
# form is Nickname:alternative:alternative:...
# while loop taken from perl cookbook
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



# parse the twitter
sub parse_twitter {


    my $mentions;
    my $myURL_meta = quotemeta($ConfigLinks::my_url);

    if (  $MAXtweetid > 0 ) {

	eval { $mentions = $nt->mentions({since_id => $MAXtweetid});  }; # this might die!

    } else {
	
	#Max to retrieve the first time
	eval { $mentions = $nt->mentions({count => 50});  }; # this might die!

    }
    
    if ( my $err = $@ ) {
        die $@ unless $err->isa('Net::Twitter::Error');
        #die $@ unless blessed $err && $err->isa('Net::Twitter::Error');
    
        warn "HTTP Response Code: ", $err->code, "\n",
             "HTTP Message......: ", $err->message, "\n",
             "Twitter error.....: ", $err->error, "\n";
    }


    #print Dumper(\$mentions);

    # this information is useful to log at the beinning of the script
    # .. it includes how many more messages you can send w/in the hour
    #my $ratelimit = $nt->rate_limit_status();
    #print Dumper($ratelimit);

    #Flip the array so we get the oldest first like IRC.
    @$mentions = reverse(@$mentions);

    #Parse the mentions
    for my $mention ( @$mentions ) {

	#print Dumper $mention;

	my $friendship = $nt->show_friendship({source_screen_name => $mention->{user}{screen_name}, target_screen_name => $ConfigLinks::twitter_account});

	   #If the person who did the mention is following the account AND mentioner is not the bot account go ahead. SPAM FILTER
	   if ( $friendship->{relationship}{target}{followed_by} == 1 && $mention->{user}{screen_name} ne $ConfigLinks::twitter_account ) {
		
	       print "Mention: $mention->{id} $mention->{created_at} <$mention->{user}{screen_name}> $mention->{text}\n" if $debug;

			#Process the line if it has a url AND it's not our own site AND its not a retweet AND this twitter ID gt our MAX twitter ID 
			if ( $mention->{text} =~ /(http(s)?:\/\/|www\.|\.com)/i && $mention->{text} !~ /$myURL_meta/i 
			                                 && $mention->{text} !~ /RT\ \@$ConfigLinks::twitter_account/i && $mention->{id} > $MAXtweetid ) {

			    my $Twitter = new Links::Twitter(appid=>$mention->{id},created_at=>$mention->{created_at},body=>$mention->{text},announcer=>$mention->{user}{screen_name});

			    $Twitter->pre_parse_twitter;

			    $Twitter->extract_url;
			    
			    $Twitter->dupe_check;

			    #If NOT a DUPE
                            if ( $Twitter->{'isdupe'} == 0 ) {

				print "not dupe\n";

                                # If the inital mimetype could be retrieved successfully
                                if ( $Twitter->get_remote_server_mimetype ) {

                                    #If it looks like it might be an image lets try to grab it AND image download is enabled
                                    if ( ( $Twitter->{'www_url'} =~ /.*\.(jpg|jpeg|png|gif).*/i || $Twitter->{'mimetype'} =~ /image.*/i ) && $ConfigLinks::img_download_enable == 1 ) {

                                        $Twitter->create_img_filename;

                                        if ( $Twitter->download_image ) {

                                            $Twitter->get_local_mimetype;

                                            if ( ! $Twitter->create_thumbnail ) {

                                                #Send the image and thumbnail to S3 Bucket
                                                if ($ConfigLinks::s3_enable == 1) {

						    $Twitter->move_thumbnail_to_s3_bucket;

                                                } # If S3 Enabled

                                            } else { # Else create_thumbnail failed

                                                $Twitter->thumbnail_failed;

                                            } # If create thumbnail was a success

                                        } else { # Else download image failed

                                            $Twitter->image_download_failed;

                                        } # If download image was a success

                                    } #If it looks like it might be an image lets try to grab it

				    # If it doesn't look like an image or archive or audio or video. OR if it was a failed image conversion.
                                    if ( ( $Twitter->{'www_url'} !~ /.*\.(jpg|jpeg|png|gif).*/i && $Twitter->{'www_url'} !~ /.*\.(iso|rar|mp3|avi|mpg|mpeg|zip)/i 
					   && $Twitter->{'mimetype'} !~ /image.*/i &&  $Twitter->{'mimetype'} !~ /(video|application|audio).*/i  ) || ( $Twitter->{'failed_to_convert_img'} ) ) {

                                        $Twitter->get_title;

                                    }

				    $Twitter->db_insert_site;

				    if ( $ConfigLinks::bot_enable == 1 ) {

					$Twitter->bot_announce_site;

				    }

                                    if ($ConfigLinks::twitter_enable_push == 1) {

                                        $Twitter->twitter_update_site;

                                    }

                                    print "Announcer : $Twitter->{'announcer'}   URL : $Twitter->{'www_url'}\n\n" if $debug;

                                } else { # If the initial mimetype download could NOT be retrieved successfully

                                    print "This URL: $Twitter->{'www_url'} doesn't really exist!! Return Code: $Twitter->{'mimetype_returncode'}\n\n" if $debug;

                                } # If the initial mimetype could be retrieved successfully

                            } else { #IS a DUPE

				#We can't bump stuff for now because the twitter ID is not stored on bump
				#$Twitter->db_bump_site;
				#
                                #if ( $ConfigLinks::bot_enable == 1 ) {
				#
                                #    $Twitter->bot_bump_site;
				#
                                #}

                            }  #If NOT a DUPE


			    print Dumper $Twitter;


		}  elsif (  $mention->{text} =~ /$myURL_meta/i ) { # this is our URL

		    print "Didn't parse this line because it matched my_url: $ConfigLinks::my_url -> $mention->{text}\n" if $debug;

		}  #Process the line if it has a url and it's not our own site and its not a retweet


	   # Else If person who did the mentioning is NOT following the account. SPAM FILTER
	   } elsif ( $friendship->{relationship}{target}{followed_by} != 1 && $mention->{user}{screen_name} ne $ConfigLinks::twitter_account ) { 

	       print "No Friendship Found for $ConfigLinks::twitter_account and $mention->{user}{screen_name}, IGNORING: $mention->{text}\n" if $debug;

	   } #If the person who did the mention is following the account go ahead. SPAM FILTER

    }  #Mention Loop

} #sub parse_twitter





# Main Script #
&startup();
&get_nicks($ConfigLinks::nickfile);
&parse_twitter();




if ( $entries == 1 ) {
	print "$entries entry added to database.\n";
}
else {
	printf "$entries entries added to database.\n";
}

print "done.\n";

