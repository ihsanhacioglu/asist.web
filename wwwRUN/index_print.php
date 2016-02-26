<?php
	$contents=$_POST["contents"];
	file_put_contents("_arsiv/deneme.txt",$contents);

	$REAL_P=dirname($_SERVER['SCRIPT_FILENAME']);
	include("$REAL_P/_template/__print_page.php");
?>
