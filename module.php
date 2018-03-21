<?php
	$_bbs_url = "http://example/miniboard/";
	$_bbs_path = "/home/hosting_users/userid/www/miniboard/";
	$_bbs_upload_dir = "/home/hosting_users/userid/www/miniboard/upload/";

	include $_bbs_path . "classes/atwDB.php";
	include $_bbs_path . "classes/miniboard.php";
	
	if (!$db) {
		$db = new atwDB;
		$db->setConfigFile($_bbs_path . "config/db_config.php");
		$db->connect();
	}
	
	$mBoard = new miniBoard;
	$mBoard->setDB($db);
	$mBoard->setURL($_bbs_url);
	$mBoard->setDIR($_bbs_path);
	$mBoard->setUploadDir($_bbs_upload_dir);
?>