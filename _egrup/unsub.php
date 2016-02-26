<html>
<head>
<title>  World Media Group AG </title>
<meta Content-Type: content="text/html"; charset="windows-1254"/>
</head>
<body>

<?php
	$elist	= isset($_GET["elist"]) ? $_GET["elist"] : 0;	$elist=empty($elist)  || is_numeric($elist) ? $elist  : 0;
	$eadres	= isset($_GET["eadres"])? $_GET["eadres"]: 0;	$eadres=empty($eadres)|| is_numeric($eadres)? $eadres : 0;
	$email	= isset($_GET["email"]) ? $_GET["email"] : "";

	if(empty($email)){
		echo "--------------------------------------------<br>";
		echo "E-posta listesinden çıkacak e-posta adresi belirtilmemiş.<br>";
		exit;
	}


	$oDB=connect_datab($Asist_World);
	$strClass="cls$oDB->cls";
	$cSqlstr="select elist.abc, elist.ctarih, elist.csaat, eadres.exp from asist!elist, asist!eadres
			where elist.eadres=eadres.id
				and elist.id=?prm_elist:I
				and elist.eadres=?prm_eadres:I";
	$qList = new $strClass($oDB->dblink, $cSqlstr);
	$qList->prm_elist=$elist;
	$qList->prm_eadres=$eadres;
	$qList->open();
	if($qList->reccount==0 || strtolower($qList->rec_exp)!=strtolower(trim($email))){
		echo "--------------------------------------------<br>";
		echo "$email<br>E-posta listesine üye değil<br>";
		exit;
	}


	if($qList->rec_abc=='C'){
		echo "--------------------------------------------<br>";
		echo "$email<br>$qList->rec_ctarih  $qList->rec_csaat tarihinde listeden çıkarılmış<br>";
		exit;
	}

	$cSqlstr="update asist!elist
			set abc='C',
				ctarih=?prm_ctarih:D,
				csaat=?prm_csaat
			where id=?prm_id:I";
	$qList = new $strClass($oDB->dblink, $cSqlstr);
	$qList->prm_id=$elist;
	$qList->prm_ctarih=date("Y-m-d");
	$qList->prm_csaat=date("H:i");
	$qList->exec();

	echo "--------------------------------------------<br>";
	echo "$email<br>E-posta listesinden çıkarıldı<br>";
?>

</body>
</html>
