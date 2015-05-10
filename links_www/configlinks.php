<?php

/* Enable Thumbs Page */
$thumbsEnable = 0;

/* Enable Link-up Page (twitter and pocket) */
$LinkupEnable = 0;

/* Enable S3 */
$s3Enable = 0;
$s3Bucket = "exampleBucket";

/* Image Path (local) shouldn't need to mess with this */
$img_path = "../links_img";

/* Folders (local and s3 bucket) */
$img_folder    = "imgs";
$thumbs_folder = "thumbs";

/* Site <title> */
$site_title = "Links";

/* Site Name (in the menu) */
$site_name = "Links";

/* Enable Password Protected Site */
$passwordEnable = 1;

/* Password to Access the Site */
$TheSecretPasswd = "#password#";

/* Twitter Username */
$twitter_account = "";

/* Pocket API Key */
$pocket_consumer_key = "";

/* Instagram API Client ID */
$instagram_clientid = "";

/* MySQL Variables */
$host     = "localhost";
$username = "user";
$password = "password";
$database = "links";


/* connect to mysql database and setup $conn */
function db_connect() {

	global $host, $username, $password, $database; /* configlinks.php */

	$conn = mysql_pconnect($host, $username, $password);
	if (!$conn)
 		die("MySQL connection could not be established");

	mysql_select_db($database, $conn);

	return $conn;
};


/* disconnect from mysql */
/* note: does nothing, because persistant links arent closed */
function db_shutdown() {

	mysql_close();
}

?>
