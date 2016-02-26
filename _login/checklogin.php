<?php
include_once("$REAL_P/_login/connect_app.php");
$myusername = isset($_POST['myusername'])?$_POST['myusername']:"altun";
$mypassword = isset($_POST['mypassword'])?$_POST['mypassword']:"yucell.9";

if (empty($myusername) || empty($mypassword)){
	echo "<meta http-equiv=\"refresh\" content=\"5;url=http://".$_SERVER['SERVER_NAME']."$ROOT_D\">";
	echo "Wrong Username or Password<br/>";
	echo "<a href=\"http://".$_SERVER['SERVER_NAME']."$ROOT_D\">http://".$_SERVER['SERVER_NAME']."$ROOT_D</a>";
	return;
}

$cUsername = mysqli_real_escape_string($oAPP->dblink,$myusername);
$cPassword = mysqli_real_escape_string($oAPP->dblink,$mypassword);
$strSql = "select user.*, kimlik.exp kimlik_exp, perso.exp perso_exp, sirket.exp sirket_exp
		   from asist.user, asist.kimlik, asist.perso, asist.sirket
		  where user.kimlik=kimlik.id
			and user.perso=perso.id
			and user.sirket=sirket.id
			and user.id!=-1
			and user.username='$cUsername' and user.password='$cPassword'";
$res = mysqli_query($oAPP->dblink, $strSql);

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
		$oUsr->imperso	=$oUsr->id;
		$oUsr->testroles=array();
		$oUsr->roleadmin=$oUsr->admin;
		$oUsr->whrRoles="";

		$oUsr->where="senrole.role in (select role from asist.userole where user=$oUsr->id and abc='A') or senrole.user=$oUsr->id";
		if($oUsr->roleadmin)$oUsr->where="($oUsr->where or role.admin=1)";
		else$oUsr->where="($oUsr->where)";

		//session_start();
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
			$host=gethostbyaddr($ipadr);
			$strSql="insert into asist.login (user,azaman,username,ipadr,host)
					values ($oUsr->id,from_unixtime($zaman),'$oUsr->username','$ipadr','$host')";
			$res=mysqli_query($oAPP->dblink, $strSql);
			$_SESSION["sesLogin"]=mysqli_insert_id($oAPP->dblink);
			$strSql="update asist.user set songir=from_unixtime($zaman) where id=$oUsr->id";
			$res=mysqli_query($oAPP->dblink, $strSql);
		}
		$defForm=empty($oUsr->defaction)?"form_anasayfa":trim($oUsr->defaction);
		header("location: http://".$_SERVER['SERVER_NAME']."$ROOT_D/?$defForm");
	    echo "Kullan�c� Kabul Edildi $defForm";
		return;
	}
}
echo "<meta http-equiv=\"refresh\" content=\"5;url=http://".$_SERVER['SERVER_NAME']."$ROOT_D\">";
echo "Wrong Username or Password<br/>";
echo "<a href=\"http://".$_SERVER['SERVER_NAME']."$ROOT_D\">http://".$_SERVER['SERVER_NAME']."$ROOT_D</a>";

?>
