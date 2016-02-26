<?php
$id=isset($_GET["id"]) ? $_GET["id"] : 0;
$id=empty($id) ? 0 : $id;
if(!empty($id)){
	include_once("$REAL_P/_login/connect_app.php");
	include_once("$REAL_P/_class/data_mysql.php");

	if(is_numeric($id)){
		$qCCC=new clsApp($oAPP->dblink, "select dosya from asist.resim where id=?prm_id");
		$qCCC->prm_id=$id;
		$qCCC->open();
	}else{
		$qCCC=new clsApp($oAPP->dblink, "select dosya from asist.resim where $id");
		$qCCC->open();
	}
	//echo $qCCC->strSql;
	header('Content-Type: image/jpeg');
	echo $qCCC->rec_dosya;
}
?>