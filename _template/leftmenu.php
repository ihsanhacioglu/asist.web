<?php

$cDil=strtolower($oUser->dilse);
if($cDil!="de" && $cDil!="en") $cDil="tr";

$tabSayfa="sayfa_$cDil";

$sqlStr="select sen.exp, sen.id, role.once, role.admin, sen.action, sen.ustmenu, sen.altmenu, sen.menusira
		from asist.$tabSayfa sen, asist.senrole, asist.role
		where sen.tur='senaryo'
			and sen.id=senrole.senaryo
			and senrole.role=role.id
			and $oUser->where
			and instr(senrole.options,'+')>0
		order by 7,8,1,2,3";
$res=mysqli_query($oAPP->dblink, $sqlStr);

$senaryo_id=0;
$str1_Menu="";
$str1_Admn="";
$strMenu="";
$sayMenu=0;
$altmenu="";
$arrMenu=array();
while($oRec=mysqli_fetch_object($res)){
	if($senaryo_id==$oRec->id) continue;
	$senaryo_id=$oRec->id;
	$arrMenu[]=$oRec;
}
mysqli_free_result($res);


for($ii=count($arrMenu)-1;$ii>=0;$ii--){
	if($altmenu!=$arrMenu[$ii]->altmenu){$sayMenu=0;$altmenu=$arrMenu[$ii]->altmenu;}
	$arrMenu[$ii]->say=++$sayMenu;
}
$altmenu="";
$say=0;
foreach($arrMenu as $oRec){
	if(!$say)$say=$oRec->say;
	$cls=$say==1?"lnk-tekmenu":"lnk-menu";
	$bos=$say==1?"":str_repeat("&nbsp; ",2);
	if($oRec->admin && $oUser->admin && $oUser->roleadmin)
		$str1_Admn.="<label class='lnk-menu lnk-admmenu' onClick='openLink(this)' url='?$oRec->action'>$bos$oRec->exp</label><br/>";
	else
		$str1_Menu.="<label class='lnk-menu' onClick='openLink(this)' url='?$oRec->action'>$bos$oRec->exp</label><br/>";

	if($oRec->say==1){
		$strAlt=$say>1?"<label class='lnk-altmenu'>$oRec->altmenu</label><br/>":"";
		$strMenu.=$strAlt.$str1_Menu.$str1_Admn;
		$altmenu=$oRec->altmenu;
		$str1_Menu="";
		$str1_Admn="";
		$say=0;
	}
}
echo $strMenu,$str1_Menu,$str1_Admn;
?>