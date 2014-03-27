[[_TOC_]]



How to install links on a fresh install of Centos 6.5


## System Install

### Install LAMP
Install Apache
```
sudo yum install httpd
sudo service httpd start
```

Install MySQL
```
sudo yum install mysql-server
sudo service mysqld start
sudo chkconfig mysqld on
sudo /usr/bin/mysql_secure_installation
```

Install PHP
```
sudo yum install php php-mysql
sudo chkconfig httpd on
sudo service httpd restart
```

### Create User

Replace ```*user*``` with your username as we go.

Add User
```
useradd *user*
```

Lock User
```
passwd -l *user*
```

### Security

Add IPTables rules to open port 80 and 443 (centos only)
```
REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT -m comment --comment "Permit HTTP Service"
REJECT_RULE_NO=$(iptables -L INPUT --line-numbers | grep 'REJECT' | awk '{print $1}');/sbin/iptables -I INPUT $REJECT_RULE_NO -m state --state NEW -m tcp -p tcp --dport 443 -j ACCEPT -m comment --comment "Permit HTTP Service"

/sbin/service iptables save
```

Add SELinux permission for httpd to access the home directory
```
chcon -t httpd_sys_content_t /home/*user*/
chcon -R -t httpd_sys_content_t /home/*user*/links_www/
```


### General Setup

Download Links
```
cd /home/*user*
sudo yum install wget
wget https://github.com/djclicking/links/archive/master.tar.gz -O links.master.tar.gz
tar -xzvf links.master.tar.gz
mv links-master/* .
rmdir links-master
```

Set Home Directory Owner & Permissions
```
chmod 750 /home/*user*
chgrp apache /home/*user*
chown -R *user* /home/*user*
```


### Configure Apache

Root site or Sub folder site (pick one of these)

Root URL Site (www.google.com)
```
cp links_www/httpd.conf/links.conf.root /etc/httpd/conf.d/links.conf
```

Sub-folder Site (www.google.com/links)
```
cp links_www/httpd.conf/links.conf.subfolder /etc/httpd/conf.d/links.conf
```
edit /etc/httpd/conf.d/links.conf
Replace \*user\* with your username
```
emacs /etc/httpd/conf.d/links.conf
```

Restart apache
```
/etc/init.d/httpd restart
```


### Setup MySQL DB

Create MySQL DB and tables
```
mysqladmin -uroot -p create links
mysql -uroot -p links < links/links.sql
```

Create MySQL user
Replace mysqlusername and mysqlpassword
```
mysql -uroot -p -e "GRANT ALL PRIVILEGES ON links.* TO mysqlusername@localhost IDENTIFIED BY 'mysqlpassowrd'"
```
Configure mySQL connection settings in links_www/configlinks.php
```PHP
/* MySQL Variables */
$host     = "localhost";
$username = "user";
$password = "password";
$database = "links";
```

Test MySQL INSERT
```
mysql -uroot -p -e "INSERT INTO links (site,announcer,edate,type) VALUES ('https://github.com/djclicking/links','djclicking', now(),'irc')" links
```
You should see a post on the web page now!


## Site Settings

### Script Settings
Set Path and URL settings in links/ConfigLinks.pm
```Perl
# Main Paths
our $path = "/home/user/links";

# My URL
# Ignore links from this domain or URL.
our $my_url = "www.mysite.com/links";
```


### Web Settings 
Set Site Title and Name in links_www/configlinks.php
```PHP
/* Site <title> */
$site_title = "Links";

/* Site Name (in the menu) */
$site_name = "Links";
```

Disable Password or Set Password
```PHP
/* Enable Password Protected Site */
$passwordEnable = 1;

/* Password to Access the Site */
$TheSecretPasswd = "#password#";
```



## IRC Pull Install

### Eggdrop Install

Install the eggdrop bot, and links_logs.sh script for retrieving links from the IRC log.

Install Eggdrop Build Pre-reqs
```
yum install gcc tcl-devel
```

Download eggdrop
```
Download eggdrop bot from: www.eggheads.org

./configure && make config && make && make install
```

Setup Eggdrop Directory and create mirclogs directory for irc logs
```
mv /root/eggdrop /home/jah
mkdir eggdrop/mirclogs
mkdir eggdrop/mirclogs/oldlogs
```

Copy Logger.tcl script to eggdrop scripts folder
```
cp links/Logger.tcl eggdrop/scripts
```

Change the owner of the eggdrop directory to your user
```
chown -R *user* eggdrop
```

Configure eggdrop.conf (it is long, read the whole thing or it won't start)
```
emacs eggdrop.conf
```

Add logger script to eggdrop.conf
Near the bottom where the other scripts are
```
source scripts/Logger.tcl
```

Run eggdrop as your new user
```
su - jah
cd eggdrop
./eggdrop
```

Go register your username and password with the bot in IRC.
```irc
/msg bot hello
```

### Setup parselogs.pl

Yum Pre-reqs
```
yum install perl-libwww-perl
yum install perl-MIME-Types
yum install file-devel
yum install perl-CPAN
```

Use CPAN to install
```
perl -MCPAN -e shell

cpan> install File::LibMagic
```

Configure variables in links/links_logs.sh
```perl
# Set these
ROOT=/home/user/jah/links
LOGPATH=/home/user/eggdrop/mirclogs
LOGFILE="#channel.log"
```

Add to crontab
```
*/1  * * * *    jah /home/jah/links/links_logs.sh
```

Your site should be picking up new links from IRC now!


## Image Download Feature

This feature downloads, saves and creates a thumbnail using Image Magick for each image that is found.
Saves 4chan images before they are 404'd!!!!!

### Image Download Setup

Image Download Pre-reqs
```
yum install Imagemagick-perl
```

Create links_img folder
```
mkdir /home/*user*/links_img
mkdir /home/*user*/links_img/thumbs
mkdir /home/*user*/links_img/imgs
```

Create links_img tmp folder (for thumbnail creation)
```
mkdir /home/*user*/links_img/tmp
```

Set Permissions
```
chown -R *user*:apache /home/*user*/links_img
chmod -R 755 /home/*user*/links_img
```

### Image Download Settings

Configure variables in links/ConfigLinks.pm
```perl
# Enable/Disable Image Download. (logs, twitter, and pocket)
our $img_download_enable = "1";

our $imgpath                 = "/home/user/links_img";       #Local
our $image_magick_tmp_path   = "/home/user/links_img/tmp";   #Temp folder for thumbnail creation (up to 1.5G)
```

note: Very large animated GIFs can require a lot of temp space to create the thumbnail. A extremely large 40MB gif took 1.8G of temp space and 50 minutes to create the thumbnail.

Configure variables in links_www/configlinks.php
```php
/* Enable Thumbs Page */
$thumbsEnable = 1;

/* Image Path (local) shouldn't need to mess with this */
$img_path = "../links_img";
```

## Bot Chat

Configure the BOT for telnet. (Don't use this unless the script is on the same box as the bot.)

The bot will announce in IRC a when a new link is found in a twitter @mention or pocket. The bot will also announce when a dupe link is posted in the irc channel.

### Bot Setup

Install Pre-reqs
```
yum install perl-Net-Telnet
```

Un-comment the listen line and set the port number in eggdrop.conf.
```
listen 5555 all
```

Restart the eggdrop bot to apply the change.


### Bot Settings
Configure these settings in links/ConfigLinks.pm
```
# Enable/Disable Eggdrop Bot Chat. (logs, twitter, and pocket)
our $bot_enable = "1";

# Eggdrop Bot TELNET Login (use only if the bot is running locally)
our $botUsername = "user";
our $botPassword = "pass";
our $botHostname = "localhost";
our $botTcpPort  = "5555";
```

### Bot Testing

Telnet to localhost and port of the bot 

If you specified a particular IP to listen on in eggdrop.conf you will need to use that IP/Host  (my-hostname or my-ip).
```
$ telnet localhost 5555
Trying 127.0.0.1...
Connected to localhost.
Escape character is '^]'.

     (Eggdrop v1.6.21 (C) 1997 Robey Pointer (C) 2011 Eggheads)

Please enter your nickname.
```

Make sure you have a local firewall installed and that this port is closed to the outside world.

## Twitter

### Twitter General

If you would like to push to twitter or pull from twitter do these steps.

Twitter Pre-reqs
```
yum install perl-Crypt-SSLeay
```

Install from CPAN

If Bitly fails on tinyurl.t just try it again a few times (https://rt.cpan.org/Public/Bug/Display.html?id=88052)
```
perl -MCPAN -e shell

cpan> install WWW::Shorten::Bitly
```

```
perl -MCPAN -e shell

cpan> install Net::Twitter
```

### Twitter Account

Configure Twitter push (push all sites to twitter as new tweets)

Create a new twitter account for your site.

Create a twitter application under that account: https://apps.twitter.com/app/new

  Callback URL can be left blank

  Read and Write permissions

Create bitly api key

### Twitter Config

links/ConfigLinks.pm
```perl
# Twitter Authentication (parsetwitter.pl)
our $twitter_account     = "";   # No @ sign
our $consumer_key        = "";
our $consumer_secret     = "";


# Bitly URL Shortener (parsetwitter.pl)
our $bitly_account     = "";
our $bitly_api_key     = "";
```

### Twitter Authorize

Run the perl script manually first as your user to authorize with twitter as a client. 
```
su - jah
/home/jah/links/parsetwitter.pl
exit
```

The script will output access token information that needs to into the config file.

Add the access tokens to links/ConfigLinks.pm
```perl
our $access_token        = "";
our $access_token_secret = "";
```

### Enable Twitter for Web
links_www/configlinks.php
```perl
/* Enable Link-up Page (twitter and pocket) */
$LinkupEnable = 1;

/* Twitter Username */
$twitter_account = "username"; # No @ sign
```


## Twitter Push

Push all new posts gathered from irc, twitter and pocket up to twitter as new tweets.

The ban_domin settings are to stop certain urls or domains from making it to twitter.
For instance if your URL is links.com/links the scripts will never grab a link from itself (links.com/links/*) already but if you want to stop all links.com URLs from ever being tweeted you can put that in here so your URL remains private.

### Twitter Push Config

links/ConfigLinks.pm
```perl
# Enable/Disable Posting To Twitter (logs, twitter, and pocket)
our $twitter_enable = "1";

# Keep these Domains or URLs a secret and DO NOT EVER tweet them to the public.
# blank them out for none, use one or both. (logs, twitter, and pocket)
our $ban_domain_twitter   = "";
our $ban_domain_twitter2  = "";
```


## Twitter Pull (Mentions)

Pull tweets in from anyone who @mentions your twitter account.

### Configure links_twitter.sh
Set variables in links/links_twitter.sh
```
# Set this
ROOT=/home/user/links
```

add to /etc/cronttab
```
*/10 * * * *    *user* /home/jah/links/links_twitter.sh
```

To disable pulling from twitter just stop the crontab by commenting it out.

If you are adding a twitter account populated with tweets it will only retrieve the last 50 entries the first time.


## Pocket

Retrieve URLs from Pocket (getpocket.com)
This app gives us a web extension and mobile app/share for quick posting.

### Pocket Setup
```
yum install perl-Net-SSLeay
```

```
perl -MCPAN -e shell

cpan> install Class::Accessor::Lite
```

https://github.com/kiririmode/p5-WebService-Pocket-Lite
```
cd /home/user
git clone git://github.com/kiririmode/p5-WebService-Pocket-Lite.git
cd p5-WebService-Pocket-Lite
perl Makefile.PL
make && make test
```

create pocket application, get "consumer" key

http://getpocket.com/developer/apps/

### Pocket Settings

links_www/configlinks.php
```php
/* Enable Link-up Page (twitter and pocket) */
$LinkupEnable = 1;

/* Pocket API Key */
$pocket_consumer_key = "";
```

Authorize a pocket account via the link-up page.

Add to crontab
```
*/2  * * * *    *user* /home/jah/links/links_pocket.sh
```

If you authorize a pocket account populated with URLs it will only retrieve the last 10 entries per user the first time.



## Amazon S3 Storage

Use Amazon S3 storage for primary or backup storage.

### S3 Setup

Install Pre-reqs
```
yum install perl-XML-LibXML
```

Install CPAN
```
perl -MCPAN -e shell

cpan[1]> install Net::Amazon::S3
```

Sign-up for aws account http://aws.amazon.com/console/

Services->S3, create bucket, name bucket.

right-click the bucket and select properties 

open permissions and select 'add bucket policy'

replace the "bucket" name and paste this policy to allow public read to your bucket

```json
{
  "Version":"2012-10-17",
  "Statement":[{
	"Sid":"AddPerm",
        "Effect":"Allow",
	  "Principal": {
            "AWS": "*"
         },
      "Action":["s3:GetObject"],
      "Resource":["arn:aws:s3:::bucket/*"
      ]
    }
  ]
}
```


click the bucket name text in the console to go inside the bucket. create two folders in your bucket: imgs and thumbs

https://portal.aws.amazon.com/gp/aws/securityCredentials#access_credentials create a new access key

### S3 Settings

grab "Access Key ID" from aws webpage

Set links/ConfigLinks.pm
```perl
our $aws_access_key_id     = "";
```

click show secret access key and add to
```perl
our $aws_secret_access_key = "";

set bucknet name
our $aws_bucket            = "ngt_thumbs";

# Amazon S3 Image Storage (logs, twitter, and pocket)
our $s3_enable             = "1";  # Copy the files to S3 and delete the local files.
our $s3_delete_local_imgs  = "0";  # Delete the local image and thumb after we upload to S3.
```

Post an image and see if it makes it to S3 through the console. right-click open should allow you to view it.

If you would like to save files in both places leave it like this. (redundancy!)

If you would like it to delete the images from the local system after they are sent to s3 set

```perl
our $s3_delete_local_imgs  = "1";  # Delete the local image and thumb after we upload to S3.
```

To switch the webpage to point to users the S3 bucket (aws charges will apply) set 

links_www/configlinks.php
```php
/* Enable S3 */
$s3Enable = 1;
$s3Bucket = "bucket_name";
```






Setup Eggdrop

http://www.eggheads.org/redirect.php?url=ftp://ftp.eggheads.org/pub/eggdrop/source/1.6/eggdrop1.6.21.tar.gz


yum provides "*/perl(XML::LibXML)" OR yum search perl | grep -i xml
