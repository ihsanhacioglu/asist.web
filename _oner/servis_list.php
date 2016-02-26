<?php
	$ROOT_D=str_replace("\\","/",dirname(dirname($_SERVER['PHP_SELF'])));
	$ROOT_D=$ROOT_D=="." ?"":"$ROOT_D";
	$ROOT_D=$ROOT_D=="/" ?"":"$ROOT_D";

	$REAL_P=dirname(dirname($_SERVER['SCRIPT_FILENAME']));
	$SERV_P=$_SERVER['SERVER_NAME'];

    include_once("$REAL_P/_login/connect_app.php");
    include_once("$REAL_P/_class/data_mysql.php");

	$mask=isset($_GET["mask"]) ? $_GET["mask"] : "";
	$pos =isset($_GET["pos"])  ? $_GET["pos"]  : 0;

	$oListqry = new clsQry($oAPP->dblink, "select id, exp from asist.personel where exp like ?prm_Exp order by exp limit 0, 10");
	$oListqry->prm_Exp="$mask%";
	$oListqry->open();

    header("Content-Type: text/xml; charset=iso-8859-9");
	echo  "<?xml version=\"1.0\"?>\n",($pos==0)?"<complete>":"<complete add='true'>\n";
	while($oListqry->next())
		echo "<option value=\"$oListqry->rec_id\">$oListqry->rec_exp</option>\n";
	echo "</complete>";
	$oListqry->close();
?>