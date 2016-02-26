<?php

$cDil=strtolower($oUser->dilse);
if(!isset($arrDil[$cDil]))$cDil="tr";
$tabSayfa="sayfa_$cDil";
$tabStandart="standart_$cDil";
$sqlStr="select sen.id
		from asist.$tabSayfa sen, asist.senrole, asist.role
		where sen.action='form_standart'
			and sen.id=senrole.senaryo
			and senrole.role=role.id
			and $oUser->where
			and instr(senrole.options,'+')>0";
$res=mysqli_query($oAPP->dblink, $sqlStr);

$sqlStr="select 1 sira, sen.ustmenu, sen.altmenu, sen.exp, sen.id, role.once, sen.action
		from asist.$tabSayfa sen, asist.senrole, asist.role
		where sen.id=senrole.senaryo
			and senrole.role=role.id
			and $oUser->where
			and instr(senrole.options,'+')>0
			and sen.tur='senaryo'
			and sen.action!='form_anasayfa'"; 
$sen_standart=0;
if ($oRec=mysqli_fetch_object($res)){
	$sen_standart=$oRec->id;
	$sqlStr.=" union
		select distinct 2 sira, 'Standart' ustmenu, ustgrup altmenu, grup exp, $sen_standart,$sen_standart, concat('form_standart&islem=sel&brw=standi=',standi)
		from asist.$tabStandart
		where id!=-1";
}
$sqlStr.=" order by 1,2,3,4,5,6";
$res=mysqli_query($oAPP->dblink, $sqlStr);

$cUstmenu="";
$cAltmenu="";

$ii=0;
$senaryo_id=0;
echo "<div id=\"ustmenu\" onmouseout=\"menuReset();\">\n";
while($oRec=mysqli_fetch_object($res)){
	if($sen_standart!=$oRec->id && $senaryo_id==$oRec->id) continue;
	$senaryo_id=$oRec->id;
	if ($cUstmenu!=$oRec->ustmenu){
		$ii++;
		if (!empty($cUstmenu)) echo "</ul></li></ul>\n";
		echo "<ul><li onclick=\"menuSet(id_ul_$ii);\" onmouseover=\"menuOver(id_ul_$ii);\">$oRec->ustmenu<ul id=\"id_ul_$ii\">\n";
		$cUstmenu=$oRec->ustmenu;
	}
	if ($cAltmenu!=$oRec->altmenu){
		echo "<li>&nbsp $oRec->altmenu</li>\n";
		$cAltmenu=$oRec->altmenu;
	}
	echo "<li><a href=\"?$oRec->action\">$oRec->exp</a></li>\n";
}
echo "</ul></li></ul>\n</div>\n";
mysqli_free_result($res);
include("$REAL_P/_template/testmenu.php");

?>