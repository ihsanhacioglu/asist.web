<?php
class class_util_abone{
	function getabc($cAbc, $dAtarih, $dCTarih){
		$dBugun = date_create(date("Y-m-d")." 00:00");
		$dAtarih= date_create("$dAtarih 00:00");
		$dCTarih= date_create("$dCTarih 00:00");
		$cAbc   = empty($cAbc) ? "" : $cAbc;

		if(strpos(",,T,F,",",$cAbc,"))		return $cAbc;	// Önkayıt
		if(empty($dAtarih))					return "?";
		if(!empty($dCTarih) && $dCTarih<$dBugun)return $cAbc=="X" ? "X" : "C";

		if($dAtarih > $dBugun)				return "B";
		if($dAtarih <=$dBugun && (empty($dCTarih) || $dCTarih >= $dBugun))	return "A";
		return "";
	}
}
?>