<?php
$sayfa_exp=$par_islem;
$strClass="class_report";
if (file_exists("$REAL_P/_form/class_$sayfa_exp.php")){
	include_once("$REAL_P/_form/class_$sayfa_exp.php");
	$strClass="class_$sayfa_exp";
}
$objSayfa=new $strClass($oAPP->dblink, $sayfa_exp);
if(is_object($objSayfa->senaryo))$objSayfa->islem();
?>