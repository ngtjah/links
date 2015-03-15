#!/usr/bin/perl

package ConfigLinks;

use strict;

# Main Paths
our $path                    = "/home/user/links";           #Local
our $imgpath                 = "/home/user/links_img";       #Local
our $image_magick_tmp_path   = "/home/user/links_img/tmp";   #Temp folder for thumbnail creation (up to 1.5G)
our $imgfolder               = "imgs";                       #Local and s3
our $thumbfolder             = "thumbs";                     #Local and s3
our $default_logfile         = "/home/user/links/IrcLog.Window_1";
our $nickfile                = "/home/user/links/nickalts.data";

# SQL Database
our $driver   = "mysql";
our $database = "links";
our $hostname = "localhost";
our $port     = "3306";
our $user     = "user";
our $password = "password";

# Enable/Disable Image Download. (logs, twitter, and pocket)
our $img_download_enable = "0";

# My URL
# Ignore links from this domain or URL.
our $my_url = "www.mysite.com/links";

# Enable/Disable Eggdrop Bot Chat. (logs, twitter, and pocket)
our $bot_enable = "0";

# Eggdrop Bot TELNET Login (use only if the bot is running locally)
our $botUsername = "";
our $botPassword = "";
our $botHostname = "localhost";
our $botTcpPort  = "5555";

# Youtube API v3 Server Key
our $youtubeapikey = "";

# Enable/Disable Posting To Twitter from: (logs, twitter, and pocket)
our $twitter_enable_push = "0";

# Keep these Domains or URLs a secret and DO NOT EVER tweet them. 
# blank them out for none. (logs, twitter, and pocket)
our $ban_domain_twitter   = "";
our $ban_domain_twitter2  = "";

# Twitter Authentication (parsetwitter.pl)
our $twitter_account     = "";
our $consumer_key        = "";
our $consumer_secret     = "";
our $access_token        = "";
our $access_token_secret = "";

# Bitly URL Shortener (parsetwitter.pl)
our $bitly_account     = "";
our $bitly_api_key     = "";

# Pocket API (parsepocket.pl)
our $pocket_consumer_key = "";

# Amazon S3 Image Storage (logs, twitter, and pocket)
our $s3_enable             = "0";  # Copy the files to S3 and delete the local files.
our $s3_delete_local_imgs  = "0";  # Delete the local image and thumb after we upload to S3.
our $aws_access_key_id     = "";
our $aws_secret_access_key = "";
our $aws_bucket 	   = "";

# Browser Agent
our $browser_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.76 Safari/537.36";

1;
