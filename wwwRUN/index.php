<?php
    $ROOT_D=str_replace("\\","/",dirname($_SERVER['PHP_SELF']));
    $ROOT_D=$ROOT_D=="."?"":"$ROOT_D";
    $ROOT_D=$ROOT_D=="/"?"":"$ROOT_D";

    $SERV_P=$_SERVER['SERVER_NAME'];
	if($_SERVER['SERVER_PORT']!="80")$SERV_P.=":".$_SERVER['SERVER_PORT'];
    $REAL_P=dirname($_SERVER['SCRIPT_FILENAME']);
	$REAL_P=dirname($REAL_P);
	$ASIS_T=$REAL_P;

	session_start();
	$keys=array_keys($_GET);
	$par_islem=isset($keys[0])?$keys[0]:"";

	if (!isset($_SESSION["cAuth"]) || $_SESSION["cAuth"]!="Evet"){
        if($par_islem=="checklogin"){
			include("$REAL_P/_login/checklogin.php");
		} else {
			include("$REAL_P/_login/login.php");
		}
		exit;
    }
	include("$REAL_P/_form/___intranet.php");
?>