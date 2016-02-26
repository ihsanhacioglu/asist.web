<?php
	
  //  if (strpos($par_islem, "_")>0 && (strpos($par_islem, "formdaara")===false))
  //      $oUser->aktifform = $par_islem ;
  
    // par_islem analiz edilerek muhtemel klasör adý ve dosya adý tespit ediliyor bulunursa kullanýlýyor        

    $cDizin = '_'.substr ($par_islem, 0, strpos($par_islem, "_"));
    
    if (file_exists($cDizin)){
        $cDDosya = $REAL_P."\\".$cDizin."\\".$par_islem.".php";
        
        if (file_exists($cDDosya)){ 
            include($cDDosya);
            exit;
        }   
    }
    
	if ($par_islem=="ara")						include("$REAL_P/_form/__ara.php");
	elseif (substr($par_islem,0,5)=="form_")	include("$REAL_P/_form/__form.php");
	elseif (substr($par_islem,0,6)=="hizli_")	include("$REAL_P/_form/__hizli.php");
	elseif (substr($par_islem,0,7)=="pratik_")	include("$REAL_P/_form/__pratik.php");
	else                                                                
	switch ($par_islem){
		case "personel_aylikmesailistesi":
			include("$REAL_P/_rapor/personel_aylik_mesai_listesi.php");
			break;

       case "personel_mesaiduzeltmelistesi":
            include("$REAL_P/_rapor/personel_mesaiduzeltmelistesi.php");
            break;
           
        case "personel_mesaiduzeltmeformu":
            include("$REAL_P/_form/personel_mesaiduzeltmeformu.php");
            break;

        case "personel_servismesairaporu":
            include("$REAL_P/_rapor/personel_servis_mesai_raporu.php");
            break;

        case "personel_maasbordrolari":
            include("$REAL_P/_rapor/personel_maasbordrolari.php");
            break;

        
        case "personel_sirketmesairaporu":
            include("$REAL_P/_rapor/personel_sirket_mesai_raporu.php");
            break;

        case "sirket_formlar":
            include("$REAL_P/_arsiv/formlar/sirket_formlar.php");
            break;

        case "sirket_yonergeler":
            include("$REAL_P/_arsiv/yonergeler/sirket_yonergeler.php");
            break;

		case "aboneara":
			include("$REAL_P/_form/abone_ara.php");
			break;
		case "dikkatekle":
			include("$REAL_P/_form/dikkat_ekle.php");
			break;
		default:
			include("$REAL_P/_template/default.php");
	}
?>