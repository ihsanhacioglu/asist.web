<?php
	$ROOT_D=str_replace("\\","/",dirname($_SERVER['PHP_SELF']));
	$ROOT_D=$ROOT_D=="." ?"":"$ROOT_D";
	$ROOT_D=$ROOT_D=="/" ?"":"$ROOT_D";

	$REAL_P=dirname($_SERVER['SCRIPT_FILENAME']);
	$SERV_P=$_SERVER['SERVER_NAME'];

    include_once("$REAL_P/_login/connect_app.php");
    include_once("$REAL_P/_class/data_mysql.php");

	$mask=isset($_GET["mask"]) ? $_GET["mask"] : "";

    $strSql="select id,exp from asist.perso where exp like '$mask%' order by exp limit 0, 10";
    $result=mysqli_query($oAPP->dblink,$strSql);

    header("Content-Type: text/html; charset=iso-8859-9");

    while($rows=mysqli_fetch_array($result)) echo $rows[0]."\t".$rows[1]."###";
?>