<?php
$qrys=isset($_GET["qrys"]) ? $_GET["qrys"] : "";
$dil =isset($_GET["dil"])  ? $_GET["dil"]  : "";
$dil  =strtoupper($dil);
$l_dil=strtolower($dil);
if(isset($arrDil[strtolower($dil)])){
	$oUser->dilse=$dil;
	header("location: http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$qrys");
	echo "$dil, dil seçeneði deðiþtirildi";
	return;
}
echo "<meta http-equiv=\"refresh\" content=\"5;url=http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$qrys\">";
echo "$dil, Unsupported language";
echo "<a href=\"http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$qrys\">http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$qrys</a>";
?>