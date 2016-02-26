<?php
$par_admin=$par_islem;
$strClass="class_form";
if(file_exists("$REAL_P/_admin/class_$par_admin.php")){
	include_once("$REAL_P/_admin/class_$par_admin.php");
	$strClass="class_$par_admin";
}
$objSayfa=new $strClass($oAPP->dblink, $par_admin);
if(is_object($objSayfa->senaryo))$objSayfa->islem();
?>
