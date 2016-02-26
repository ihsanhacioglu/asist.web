<?php
if (!$oUser->usertest && !$oUser->roletest && !$oUser->admin){
	header("location: http://".$_SERVER['SERVER_NAME']."$ROOT_D/");
	echo "Test veya admin kullanýcýsý deðil.";
	return;
}
$role=isset($_GET["role"])?$_GET["role"]:0; $role=empty($role)?0:$role;
$user=isset($_GET["user"])?$_GET["user"]:0; $user=empty($user)?0:$user;
if (empty($role) && empty($user) && !isset($_GET["roleadmin"])) return;

$role=mysqli_real_escape_string($oAPP->dblink,$role);
if (empty($role) || !is_numeric($role)) $role=0;

$user=mysqli_real_escape_string($oAPP->dblink,$user);
if(!$oUser->usertest)
	$whrUser="user.id=$oUser->id";
elseif(is_numeric($user)){
	if (empty($user)) $user=$oUser->id;
	$whrUser="user.id=$user";
}else{
	if (empty($user)) return;
	$whrUser="user.username='$user'";
}

$roleadmin=isset($_GET["roleadmin"])?$_GET["roleadmin"]:$oUser->roleadmin;
$roleadmin=empty($roleadmin)?0:$roleadmin;
$roleadmin=mysqli_real_escape_string($oAPP->dblink,$roleadmin);
if (empty($roleadmin) || !is_numeric($roleadmin)) $roleadmin=0;
if($oUser->admin)$oUser->roleadmin=$roleadmin==1?1:0;


$qrys=isset($_GET["qrys"]) ? $_GET["qrys"] : "";
$strSql="select user.*, kimlik.exp kimlik_exp, perso.exp perso_exp, sirket.exp sirket_exp
		from asist.user, asist.kimlik, asist.perso, asist.sirket
		where user.kimlik=kimlik.id
			and user.perso=perso.id
			and user.sirket=sirket.id
			and $whrUser";
$res=mysqli_query($oAPP->dblink, $strSql);

if($oUsr=mysqli_fetch_object($res)){
	$strSql="select perso.*, kimlik.exp kimlik_exp, servis.exp servis_exp
			from asist.perso, asist.kimlik, asist.servis
			where perso.kimlik=kimlik.id
				and perso.servis=servis.id
				and perso.id=$oUsr->perso";
	$res=mysqli_query($oAPP->dblink, $strSql);
	$oPer=mysqli_fetch_object($res);

	$strSql="select mesul.* from asist.mesul where mesul.id=$oUsr->mesul";
	$res=mysqli_query($oAPP->dblink, $strSql);
	$oMes=mysqli_fetch_object($res);

	$strSql="select * from asist.sirket where id=$oUsr->sirket";
	$res=mysqli_query($oAPP->dblink, $strSql);
	$oSrk=mysqli_fetch_object($res);

	if ($oPer && $oSrk){
		$oUsr->imperso	 = $oUser->imperso;
		$oUsr->testroles = $oUser->testroles;
		$oUsr->roleadmin = $oUser->roleadmin;
		$oUsr->usertest	 = $oUser->usertest;
		$oUsr->roletest	 = $oUser->roletest;
		$oUsr->admin	 = $oUser->admin;

		if($oUsr->roletest && !empty($role)){
			if(isset($oUsr->testroles[$role]))
			unset($oUsr->testroles[$role]);
			else$oUsr->testroles[$role]=$role;
		}
		$whrRoles="";
		foreach($oUsr->testroles as $role=>$val)$whrRoles.=",$role";
		$whrRoles=substr($whrRoles,1);
		$oUsr->whrRoles=$whrRoles;

		if($oUsr->roletest && count($oUsr->testroles))$oUsr->where="senrole.role in ($whrRoles) or senrole.user=$oUsr->id";
		else$oUsr->where="senrole.role in (select role from asist.userole where user=$oUsr->id and abc='A') or senrole.user=$oUsr->id";
		if($oUsr->admin){
			if($oUsr->roleadmin)$oUsr->where="($oUsr->where or role.admin=1)";
			else$oUsr->where="($oUsr->where) and role.admin=0";
		}else$oUsr->where="($oUsr->where)";
		
		$_SESSION["oUser"]  = $oUsr;
		$_SESSION["oPerso"] = $oPer;
		$_SESSION["oMesul"] = $oMes;
		$_SESSION["oSirket"]= $oSrk;

		header("location: http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$qrys");
		echo "$oUsr->imperso, $oUsr->id, Kullanýcý Kabul Edildi";
		return;
	}
}

echo "<meta http-equiv=\"refresh\" content=\"5;url=http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$qrys\">";
echo "Wrong Username or Password<br/>";
echo "<a href=\"http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$qrys\">http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$qrys</a>";

?>
