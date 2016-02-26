<?php
include_once("$REAL_P/_login/connect_app.php");

$cOner=isset($_GET["yildiz"]) ? $_GET["yildiz"] : "";
if(substr($cOner,0,5)=="oner_") oner_list($cOner);
elseif(substr($cOner,0,7)=="yildiz_") yildiz_list($cOner);
else{
	switch ($cOner){
	case "abonelist":		include("$REAL_P/_oner/abone_list.php"); break;
	case "kimliklist":		include("$REAL_P/_oner/kimlik_list.php"); break;
	case "sebeplist":		include("$REAL_P/_oner/sebep_list.php"); break;
	case "persolist":		include("$REAL_P/_oner/perso_list.php"); break;
	case "personellist":	include("$REAL_P/_oner/personel_list.php"); break;
	case "kimliklist":		include("$REAL_P/_oner/kimlik_list.php"); break;
	case "sirketlist":		include("$REAL_P/_oner/sirket_list.php"); break;
	}
}

function oner_list($yildiz_exp){
    global $oAPP;

	if (!preg_match("/oner_(\w+)\s*:(.+)/",$yildiz_exp,$arr_match)) return false;
	$table=$arr_match[1]; $fields=$arr_match[2];
	if (!preg_match_all("/(\w+)\s*(,|$)/U",$fields,$arr_match,PREG_SET_ORDER)) return false;
	if(count($arr_match)<2) return false;

	$fields="";
	foreach($arr_match as $fld)$fields.=",$fld[1]";
	$fields=substr($fields,1);
	$exp_fld=$arr_match[1][1];
	$str_sql="select $fields from $table where $exp_fld like ?prm_exp order by 2 limit 0, 10";

	$mask=isset($_GET["mask"]) ? $_GET["mask"] : "";
	$mask=strtr($mask,array(".."=>"%","*"=>"%"));
	$qCCC=new clsApp($oAPP->dblink, $str_sql);
	$qCCC->prm_exp="$mask%";
	for($ii=1;isset($_GET["p$ii"]);$ii++)if(isset($qCCC->{"par_p$ii"}))$qCCC->{"prm_p$ii"}=$_GET["p$ii"];
	$qCCC->open(false,false);

	$cRowLine="";
	foreach($qCCC->arrFields as $oFld)$cRowLine.='\t$qCCC->rec_'.$oFld->name;
	$cRowLine='echo "'.substr($cRowLine,2).'###";';

    header("Content-Type: text/html; charset=iso-8859-9");
	while($qCCC->next()) eval($cRowLine);
	$qCCC->close();
}

function yildiz_list($yildiz_exp){
    global $oAPP, $oUser;

	if(!preg_match("/yildiz_(\d+)\s*:(.+)/",$yildiz_exp,$arr_match)) return false;
	$sayfa_id=$arr_match[1];
	$yildiz=$arr_match[2];

	$cDil=strtolower($oUser->dilse);
	if($cDil!="de" && $cDil!="en") $cDil="tr";
	$tabSayfa="sayfa_$cDil";
	$qCCC=new clsApp($oAPP->dblink, "select yildizvalues,datab from asist.$tabSayfa sen where id=?prm_id");
	$qCCC->prm_id=$sayfa_id;
	$qCCC->open();

	$mask=isset($_GET["mask"]) ? $_GET["mask"] : "";
	$mask=strtr($mask,array(".."=>"·","*"=>"·","%"=>"·"));
	$datab=$qCCC->rec_datab;
	if(preg_match("/#name=$yildiz(.+)(#|$)/sU",$qCCC->rec_yildizvalues,$arr_match)){
		$oSql=objProp($arr_match[1]);
		$str_sql=$oSql->sql;
		if(isset($oSql->iexp) || preg_match("/where.+indexp\s+/iU",$str_sql,$arr_match))$mask=$qCCC->to_Indexp($mask);
		if(isset($oSql->datab))$datab=$oSql->datab;
	}elseif(preg_match("/$yildiz(-datab)?\s*:\s*(.+)($|[\r\n])/",$qCCC->rec_yildizvalues,$arr_match)){
		$str_sql=$arr_match[2];
		if(preg_match("/where.+indexp\s+/iU",$str_sql,$arr_match))$mask=$qCCC->to_Indexp($mask);
	}
	else return false;
	$mask=strtr($mask,array("·"=>"%"));

	$oDB=connect_datab($datab);
	$strClass="cls$oDB->cls";
	$qCCC->close();

	$qCCC=new $strClass($oDB->dblink, $str_sql);if(!isset($qCCC->par_exp)) return false;
	$qCCC->prm_exp="$mask%";
	for($ii=1;isset($_GET["p$ii"]);$ii++)if(isset($qCCC->{"par_p$ii"}))$qCCC->{"prm_p$ii"}=$_GET["p$ii"];
	$qCCC->open(false,false);

	$cRowLine="";
	foreach($qCCC->arrFields as $oFld)$cRowLine.='\t$qCCC->rec_'.$oFld->name;
	$cRowLine='echo "'.substr($cRowLine,2).'###";';

    header("Content-Type: text/html; charset=iso-8859-9");
	while($qCCC->next()) eval($cRowLine);
	$qCCC->close();
}
function objProp($strProp=""){
	$obj=(object)array();
	if(preg_match_all("/\s*(\w+)\s*=(.+)( &|$)/sU",$strProp,$arrProp,PREG_SET_ORDER))foreach($arrProp as $val)$obj->{$val[1]}=trim($val[2]);
	return $obj;
}
?>