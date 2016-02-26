<?php
	$oUser  = $_SESSION["oUser"];
	$oPerso = $_SESSION["oPerso"];
   	$oMesul = $_SESSION["oMesul"];
	$oSirket= $_SESSION["oSirket"];
	$zaman=time();
	$_SESSION["sesCZaman"]=$zaman;
	$sesLogin=$_SESSION["sesLogin"];
	$sure=$zaman-$_SESSION["sesAZaman"];
	if(!empty($sesLogin)){
		$strSql="update asist.login set czaman=from_unixtime($zaman),sure=$sure where id=$sesLogin";
		$res=mysqli_query($oAPP->dblink, $strSql);
	}
?>