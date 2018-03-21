<?
	include "module.php";
	
	$mBoard->fileDownload($_GET[no],urldecode($_GET[code]),$_GET[type]);
?>