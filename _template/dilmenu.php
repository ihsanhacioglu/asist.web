<?php
if (!$oUser->roletest && !$oUser->admin) return;
$qrys=$_SERVER["QUERY_STRING"];

$sqlStr="select role.id,role.exp from asist.role";
$res=mysqli_query($cDblink, $sqlStr);
$str_menu="";
foreach($oUser->testroles as $role=>$val) $oUser->testroles[$role]=$role;
while($oRec=mysqli_fetch_object($res))
	if(isset($oUser->testroles[$oRec->id]))$oUser->testroles[$oRec->id]=$oRec->exp;
	else$str_menu.="<li><a class=\"test\" href=\"?testlogin&role=$oRec->id&qrys=$qrys\" title=\"role: $oRec->id\">$oRec->exp</a></li>\n";
mysqli_free_result($res);

$str_test="";
$str_role="";

if(count($oUser->testroles)){
	$ii=0;
	foreach($oUser->testroles as $role=>$val){
		$str_role.=", ".++$ii.".$val";
		$str_test.="<li><a class=\"test-sel\" href=\"?testlogin&role=$role&qrys=$qrys\" title=\"role: $role\">$cKare $val</a></li>\n";
	}
	$str_role="TEST  ".substr($str_role,2);
}else{
	$ii=0;
	$sqlStr="select role.once,role.exp
			from asist.userole,asist.role
			where userole.role=role.id
				and userole.user=$oUser->id
				and userole.abc='A'
			order by 1,2";
	$res=mysqli_query($cDblink, $sqlStr);
	while($oRec=mysqli_fetch_object($res))$str_role.=", ".++$ii.".$oRec->exp";
	mysqli_free_result($res);
	$str_role=substr($str_role,2);
}
if($oUser->admin)
	$str_menu.="<li><a class=\"test\" href=\"?testlogin&roleadmin=".
		($oUser->roleadmin?"0":"1").
		"&qrys=$qrys\" title=\"RoleAdmin...$oUser->roleadmin\">".
		($oUser->roleadmin?"++":"--").
		"RoleAdmin...</a></li>\n";
echo "<div id=\"ustmenu\" onmouseout=\"menuReset();\">\n";
echo "<ul><li style=\"color:blue\" onclick=\"menuSet(id_ul_role);\" onmouseover=\"menuOver(id_ul_role);\">$str_role<ul id=\"id_ul_role\">\n";
echo $str_test,$str_menu;
echo "</ul></li></ul>\n</div>\n";

?>
