<?php
function yildiz_exp($yildiz_exp)
{
	if(substr($yildiz_exp,0,5)!="oner_") return false;
    global $oAPP;
	if (!preg_match("/\s*(\w+)\s*:\s*((\w+)\s*(,|$))+/U",$yildiz_exp,$arr_match,PREG_SET_ORDER)) return false;
	$table=$arr_match[1];
	$fields=$arr_match[2];
	if (!preg_match_all("/(\w+)\s*(,|$)/U",$fields,$arr_match,PREG_SET_ORDER)) return false;
	if(count($arr_match)<2) return false;

	$fields="";
	$exp_fld=$arr_match[2];
	foreach($arr_match as $fld) $fields.=",$fld";
	$fields=substr($fields,1);
	$str_sql="select $fields from $table where $exp_fld like ?prm_Exp order by exp limit 0, 10");

	$mask=isset($_GET["mask"]) ? $_GET["mask"] : "";
	$oQry = new clsQry($oAPP->dblink, $str_sql);
	$oQry->prm_Exp="$mask%";
	$oQry->open(false,false);

	header('Content-Type: text/html; charset=iso-8859-9');
    while($oQry->next())) echo $oQry->arrFields[0]->value."\t".arrFields[1]->value."###";
	$oQry->close();
}
?>