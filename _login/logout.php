
<?php
if (isset($_SESSION["tmptables"])){
	foreach ($_SESSION["tmptables"] as $tmpname)
		mysqli_query($oAPP->dblink,"drop table temp.$tmpname");
}
$sesSure=$_SESSION["sesCZaman"]-$_SESSION["sesAZaman"];
session_destroy();
header('Content-Type: text/html; charset=iso-8859-9');
?>
<html>
<head>
<title>  World Media Web Servisi </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-9">
</head>
<html>
<body>
<p>Oturum kapatıldı</p>
<p><?php echo "Oturum süresi: $sesSure";?></p>
</body>
</html>