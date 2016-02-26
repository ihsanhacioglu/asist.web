<?php
    $ROOT_D=str_replace("\\","/",dirname($_SERVER['PHP_SELF']));
    $ROOT_D=$ROOT_D=="."?"":"$ROOT_D";
    $ROOT_D=$ROOT_D=="/"?"":"$ROOT_D";

    $SERV_P=$_SERVER['SERVER_NAME'];
    $PORT_P=$_SERVER['SERVER_PORT'];
    $REAL_P=dirname($_SERVER['SCRIPT_FILENAME']);
	$REAL_P=dirname($REAL_P);
	$REAL_P=dirname($REAL_P);

	$keys=array_keys($_GET);
	$par_islem=isset($keys[0])?$keys[0]:"";

	include_once("$REAL_P/_login/connect_app.php");
	switch($par_islem){
		case "unsubscribe":	include("$REAL_P/_egrup/unsub.php");break;
		case "resubscribe":	include("$REAL_P/_egrup/resub.php");break;
    }
	include_once("$REAL_P/_login/close_app.php");
?>