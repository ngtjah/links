#!/usr/bin/perl -w
#
# parselogs.pl - Search logs for URLs
# Copyright (C) 2014
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

#Config File
use ConfigLinks;


#
#Modules
#

#NOT USED??
#use LWP::Simple;
#use HTML::HeadParser;
#use HTTP::Headers;
#use Scalar::Util;
#use threads::shared;
#use HTTP::Date qw(:DEFAULT parse_date);
#use charnames ':full';



#Used in Links.pm
#use File::LibMagic;
#use URI::Escape;
#use LWP::UserAgent;
#use MIME::Types qw(by_suffix by_mediatype);
#use Encode;

#require Net::Amazon::S3;
#require Net::Telnet;
#require Net::Twitter;
#require Net::Twitter::OAuth;
#require WWW::Shorten::Bitly;


#Keep Here
use DBI;
use POSIX qw/strftime/;

use Links;


#Debug
my $version = '3.00';
our $debug   = 1;

# Global Vars
our $dbh;
our $drh;
our $entries   = 0;      # num entries found
our @nicks;              # array of nicknames (: seperated)
our @alternate_nicks;    # array of alt. nicknames

my  $logfile;            # logfile to use



# sets logfile and connects to mysql database
sub startup {

	if ( @ARGV > 0 ) {
		$logfile = $ARGV[0];
		print strftime('%F %T',localtime) . ": Using logfile from command line ($logfile)..\n" if $debug;
	}
	else {
		$logfile = $ConfigLinks::default_logfile;
		print strftime('%F %T',localtime) . ": Using logfile from config ($logfile)..\n" if $debug;
	}

	open( LOG, $logfile ) or die "$0: Can't open $logfile for reading: $!\n";
	binmode(LOG, ":utf8");


	my $dsn = "DBI:$ConfigLinks::driver:database=$ConfigLinks::database;host=$ConfigLinks::hostname;port=$ConfigLinks::port";
	$dbh = DBI->connect( $dsn, $ConfigLinks::user, $ConfigLinks::password, {mysql_enable_utf8 => 1} ) || die("Cannot connect to database\n");
	$drh = DBI->install_driver("mysql");

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



# Parse through the text logs
sub parse_log {

    my $parseline;
    my $myURL_meta = quotemeta($ConfigLinks::my_url);

	while ( $parseline = <LOG> ) {

            #Remove Carriage Returns and newlines
	    $parseline =~ s/(\r|\n)//g;

            my @segments = split( / /, $parseline );

		# Make sure we have a line that has at least 1 space
	if ( scalar(@segments) > 1 ) {

    		    # check for nickname at beginning of line AND check for a URL but not a URL to ourself ($myURL_meta)
    		    if ( $segments[1] =~ /\A<[_0-9a-zA-Z]*/ && ( $parseline =~ /(http(s)?:\/\/|www\.|\.com)/i && $parseline !~ /$myURL_meta/i )) {
    		    
    		    	chomp($parseline);
    		    	
    		    	if ($parseline) { 
    		    	
    		    	    my $Link = new Links(parseline=>$parseline);
    		    
    		    	    $Link->pre_parse_irc;
    		    
    		    	    $Link->extract_url;
    		    	
    		    	    $Link->dupe_check;
    		    
    		    	    #If NOT a DUPE
    		    	    if ( $Link->{'isdupe'} == 0 ) {
    		    
    		    		print "not dupe\n";
    		    
    		    		# If the inital mimetype could be retrieved successfully
    		    		if ( $Link->get_remote_server_mimetype ) {
    		    
    		    		    #If it looks like it might be an image AND image download is enabled, lets try to grab it
    		    		    if ( ( $Link->{'www_url'} =~ /.*\.(jpg|jpeg|png|gif).*/i || $Link->{'mimetype'} =~ /image.*/i ) && $ConfigLinks::img_download_enable == 1 ) {
    		    		    
    		    		        $Link->create_img_filename;
    		    		    
    		    		        if ( $Link->download_image ) {
    		    
    		    			    $Link->get_local_mimetype;
    		    
    		    			    if ( ! $Link->create_thumbnail ) {
    		    
    		    			        #Send the image and thumbnail to S3 Bucket
    		    			        if ($ConfigLinks::s3_enable == 1) {
    		    			        
    		    			        	$Link->move_thumbnail_to_s3_bucket;
    		    			        
    		    			        } # If S3 Enabled
    		    
    		    			    } else { # Else create_thumbnail failed
    		    
    		    				$Link->thumbnail_failed;
    		    
    		    			    } # If create thumbnail was a success
    		    		    
    		    		        } else { # Else download image failed
    		    
    		    			    $Link->image_download_failed;
    		    
    		    			} # If download image was a success
    		    		    
    		    		    } #If it looks like it might be an image lets try to grab it
    		    
    		    		    # If it doesn't look like an image or archive or audio or video. OR if it was a failed image conversion.
    		    		    if ( ( $Link->{'www_url'} !~ /.*\.(jpg|jpeg|png|gif).*/i && $Link->{'www_url'} !~ /.*\.(iso|rar|mp3|avi|mpg|mpeg|zip)/i && $Link->{'mimetype'} !~ /image.*/i 
    		    			   &&  $Link->{'mimetype'} !~ /(video|application|audio).*/i  ) || ( $Link->{'failed_to_convert_img'} ) ) {
    		    
    		    			$Link->get_title;
    		    
    		    		    } 
    		    
    		    		    $Link->db_insert_site;
    		    
    		    		    if ($ConfigLinks::twitter_enable_push == 1) {
    		    
    		    			$Link->twitter_update_site;
    		    
    		    		    }
    		    
    		    		    
    		    		    print "Announcer : $Link->{'announcer'}   URL : $Link->{'www_url'}\n\n" if $debug;
    		    		    
    		    		} else { # If the initial mimetype download could NOT be retrieved successfully
    		    
    		    		    print "This URL: $Link->{'www_url'} doesn't really exist!! Return Code: $Link->{'mimetype_returncode'}\n\n";

				    if ( $ConfigLinks::bot_enable == 1 ) {

					$Link->bot_announce_sitefail;

				    }
    		    
    		    		} # If the initial mimetype could be retrieved successfully
    		    
    		    	    } else { #IS a DUPE
    		    
    		    		$Link->db_bump_site;
    		    
    		    		if ( $ConfigLinks::bot_enable == 1 ) {
    		    		    
    		    		    $Link->bot_bump_site;
    		    		
    		    		}
    		    
    		    	    }  #If NOT a DUPE
    		    	                  
    		    	    use Data::Dumper;
			    $Data::Dumper::Sortkeys = 1;
    		    	    print Dumper $Link;
    		    	
    		    	}    #if $parseline
    		    
    		    }  elsif (  $parseline =~ /$myURL_meta/i ) { # this is our URL
    		    
    		        print "Didn't parse this line because it matched $ConfigLinks::my_url -> $parseline\n" if $debug;
    		    
    		    }  #if segments and parseline

		} #Make sure we have at least 1 space on the line

	}    #while

}    #parse_log





&startup();
&get_nicks($ConfigLinks::nickfile);
&parse_log();



if ( $entries == 1 ) {
	print "$entries entry added/updated to the database.\n";
}
else {
	printf "$entries entries added/updated to the database.\n";
}

print "done.\n";



