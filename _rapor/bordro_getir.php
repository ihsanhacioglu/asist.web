
<?php
$aFiles  = glob("C:/bordro/*$oUser->exp*");

$cFile=basename($_GET["dosya"]);
if (!preg_match("/.*$oUser->exp.*/",$cFile) || !is_file("C:/bordro/$cFile"))
    echo "$cFile dosyasý yok"; 
else 
{
    header("Content-Type: application/pdf");
    header("Content-Disposition: inline; filename=\"$cFile\"");
    header("Pragma: no-cache");
    header("Expires: 0");

	$strFile=file_get_contents("C:/bordro/$cFile");
	echo $strFile;
}
?>