<?php
$exp=isset($_GET["exp"]) ? $_GET["exp"] : 0;
$exp=empty($exp) ? 0 : $exp;
if(!empty($exp)){
	$resFile="\\\\Wmg-s3\\asist.world$\\data.world\\foto\\$exp.jpg";
	if(file_exists($resFile)){
		header('Content-Type: image/jpeg');
		readfile($resFile);
	}
}
?>
