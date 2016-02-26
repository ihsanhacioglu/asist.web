<?php
include_once("$REAL_P/_login/connect_app.php");
$user=-1001;
$whrUser="user.id=$user";

$sqlStr="select user.*, kimlik.exp kimlik_exp, perso.exp perso_exp, sirket.exp sirket_exp
		from asist.user, asist.kimlik, asist.perso, asist.sirket
		where user.kimlik=kimlik.id
			and user.perso=perso.id
			and user.sirket=sirket.id
			and $whrUser";
$res=mysqli_query($oAPP->dblink, $sqlStr);

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

	$oUsr->imperso	=$oUsr->id;
	$oUsr->testroles=array();
	$oUsr->roleadmin=$oUsr->admin;
	$oUsr->whrRoles="";

	$oUsr->where="senrole.role in (select role from asist.userole where user=$oUsr->id and abc='A') or senrole.user=$oUsr->id";
	if($oUsr->roleadmin)$oUsr->where="($oUsr->where or role.admin=1)";
	else$oUsr->where="($oUsr->where)";

	$_SESSION["cAuth"]  = "Evet";
	$_SESSION["oUser"]  = $oUsr;
	$_SESSION["oPerso"] = $oPer;
	$_SESSION["oMesul"] = $oMes;
	$_SESSION["oSirket"]= $oSrk;

	$zaman=time();
	$_SESSION["sesAZaman"] = $zaman;
	$_SESSION["sesCZaman"] = $zaman;
	$_SESSION["sesLogin"]  = 0;

	if(!strpos(",10.0.10.40,10.0.10.41,127.0.0.1",$_SERVER['SERVER_ADDR'])&&
	   !strpos(",10.0.10.41,127.0.0.1",$_SERVER['REMOTE_ADDR'])){
		$ipadr=$_SERVER['REMOTE_ADDR'];
		//$host=gethostbyaddr($ipadr);
		$host=$ipadr;
		$strSql="insert into asist.login (user,azaman,username,ipadr,host)
				values ($oUsr->id,from_unixtime($zaman),'$oUsr->username','$ipadr','$host')";
		$res=mysqli_query($oAPP->dblink, $strSql);
		$_SESSION["sesLogin"]=mysqli_insert_id($oAPP->dblink);
		$strSql="update asist.user set songir=from_unixtime($zaman) where id=$oUsr->id";
		$res=mysqli_query($oAPP->dblink, $strSql);
	}
}
?>