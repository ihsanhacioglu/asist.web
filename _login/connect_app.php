<?php
$Asist_World=1;

$arrDatab[-1]=null;
$oAPP=create_APP();

$arrDil=array("tr"=>"Türkçe","de"=>"Deutsch");
include_once("$REAL_P/_class/data_mysql.php");
class_alias("clsMysql","clsApp");

function connect_datab($datab){
	global $REAL_P,$arrDatab,$oAPP;

	if(isset($arrDatab[$datab]))return $arrDatab[$datab];

	$res=mysqli_query($oAPP->dblink, "select * from asist.datab where id=$datab");
	$oDB=mysqli_fetch_object($res);
	$oDB->CUR_II=0;
	mysqli_free_result($res);
	include_once("$REAL_P/_class/data_$oDB->cls.php");
	$func="connect_$oDB->cls";
	$oDB->dblink=$func($oDB->pars);
	$arrDatab[$datab]=$oDB;
	return $oDB;
}

function CUR_II($datab){
	$oDB=connect_datab($datab);
	$oDB->CUR_II++;
	return "CUR_$oDB->CUR_II";
}

function create_APP(){
	global $arrDatab;
	$datab=-1;
	if(isset($arrDatab[$datab]))return $arrDatab[$datab];


	$oAPP=(object)array();
	$oAPP->cHost    = "10.2.10.15";  // "localhost"
	$oAPP->cUsername= "root";		 // "asist";
	$oAPP->cPassword= "altun-2015";	 //	"Asist-2013-Local";
	$oAPP->cDbname  = "asist";
	$oAPP->CUR_II	= 0;

	$oAPP->id		= -1;
	$oAPP->exp		= "Default";
	$oAPP->cls		= "app";
	$oAPP->pars		= "";
	$oAPP->fro_s	= ".";
	$oAPP->dblink	= null;
	$oAPP->dblink=mysqli_connect("$oAPP->cHost", "$oAPP->cUsername", "$oAPP->cPassword") or die("cannot connect");

	mysqli_select_db($oAPP->dblink, "$oAPP->cDbname") or die("cannot select DB");
	mysqli_query($oAPP->dblink,"set character set 'latin5' ");
	$arrDatab[$datab]=$oAPP;

	return $oAPP;
}
?>