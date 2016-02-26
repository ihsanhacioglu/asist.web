<?php
$id=isset($_GET["id"]) ? $_GET["id"] : 0;
$id=empty($id) ? 0 : $id;
if (!empty($id)){
	include_once("$REAL_P/_login/connect_app.php");
	include_once("$REAL_P/_class/data_mysql.php");

	$qCCC=new clsApp($oAPP->dblink, "select exp,dosya,tur from asist.belge where id=?prm_id");
	$qCCC->prm_id=$id;
	$qCCC->open();
	header('Content-Type: application/x-download');
	header("Content-Disposition: attachment; filename=\"$qCCC->rec_exp\"");
	header('Cache-Control: private, max-age=0, must-revalidate');
	header('Pragma: public');
	ini_set('zlib.output_compression','0');
	echo $qCCC->rec_dosya;
}
?>