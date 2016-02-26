<?php
$id=isset($_GET["id"]) ? $_GET["id"] : 0;
$id=empty($id) ? 0 : $id;
if (!empty($id)){
	$belge=trim($oSirket->arsivdir); // "\\World\asist.belge$\WORLD";

	include_once("$REAL_P/_login/connect_app.php");
	include_once("$REAL_P/_class/data_adovfp.php");

	$oDB=connect_datab($Asist_World);
	$strClass="cls$oDB->cls";
	$qCCC=new $strClass($oDB->dblink, "select exp,dosyad from asist!belge where id=?prm_id:I");
	$qCCC->prm_id=$id;
	$qCCC->open();
	$dosyad=str_replace("\\\\\$belge",$belge,$qCCC->rec_dosyad);
	$dosya=basename($dosyad);
	
	//header('Content-Type: application/x-download');
	header("Content-Type: application/pdf");
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	//header("Content-Disposition: attachment; filename=\"$dosya\"");
	header("Content-Disposition: inline; filename=\"$dosya\"");
	readfile($dosyad);
}
?>