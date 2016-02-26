<?php

	$par_liste=isset($_GET["par_liste"])?$_GET["par_liste"]:"";
	switch ($par_liste)
	{
		case "referabolist":
			include("$REAL_P/_main/refer_abone_listesi.php");
			break;

		case "abone_rp_rekipexpaktifhedef":
			include("$REAL_P/_main/abone_rp_rekipexpaktifhedef.php");
			break;			
		case "abone_rp_rekipgrupaktifhedef":
			include("$REAL_P/_main/abone_rp_rekipgrupaktifhedef.php");
			break;
		case "abone_rp_rekipustgrupaktifhedef":
			include("$REAL_P/_main/abone_rp_rekipustgrupaktifhedef.php");
			break;
		case "abone_rp_rekipustgrup1aktifhedef":
			include("$REAL_P/_main/abone_rp_rekipustgrup1aktifhedef.php");
			break;
		case "rekipgrupabolist":
			include("$REAL_P/_main/rekip_grup_abone_listesi.php");
			break;

		default:
			include("$REAL_P/_template/default.php");
	}
?>