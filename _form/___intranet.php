<?php
	include_once("$REAL_P/_login/connect_app.php");
    include_once("$REAL_P/_login/session_app.php");
	include_once("$REAL_P/_form/class__base.php");
	include_once("$REAL_P/_form/class_ilis.php");

	if(isset($_GET["dwn"]) && (substr($par_islem,0,5)=="form_")){
		$class=substr($par_islem,0,strpos($par_islem,"_"));
		include_once("$REAL_P/_form/class_$class.php");
		include("$REAL_P/_form/__$class.php");
		exit;
	}
	if(isset($_GET["mod"]) && (substr($par_islem,0,5)=="form_" || substr($par_islem,0,6)=="hizli_" || substr($par_islem,0,7)=="pratik_")){
		$class=substr($par_islem,0,strpos($par_islem,"_"));
		include_once("$REAL_P/_template/head.php");
		include_once("$REAL_P/_form/class_$class.php");
		include("$REAL_P/_form/__$class.php");
		exit;
	}
	if(isset($_GET["mod"]) && (substr($par_islem,0,7)=="report_")){
		$class=substr($par_islem,0,strpos($par_islem,"_"));
		include_once("$REAL_P/tcpdf/tcpdf.php");
		include_once("$REAL_P/_form/class_$class.php");
		include("$REAL_P/_form/__$class.php");
		exit;
	}

	switch($par_islem){
		case "logout":		include("$REAL_P/_login/logout.php");		break;
		case "testlogin":	include("$REAL_P/_login/testlogin.php");	break;
		case "dilogin":		include("$REAL_P/_login/dilogin.php");		break;
        case "oner":		include("$REAL_P/_oner/__oner.php");		break;
		case "loadres":		include("$REAL_P/_form/load_res.php");		break;
		case "loadimg":		include("$REAL_P/_form/load_pic.php");		break;
		case "loadpic":		include("$REAL_P/_form/load_pic.php");		break;
		case "loadbelge":	include("$REAL_P/_form/load_belge.php");	break;
		case "loaddosyad":	include("$REAL_P/_form/load_dosyad.php");	break;
		case "down":		include("$REAL_P/_template/download.php");	break;
		case "bordro_getir":include("$REAL_P/_rapor/bordro_getir.php");	break;
        case "message":		include("$REAL_P/_template/message.php");	break;
        default:			include("$REAL_P/_template/page.php");		break;
    }
	//sleep(10);
	include_once("$REAL_P/_login/close_app.php");
?>