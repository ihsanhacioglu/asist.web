
<?php

include_once("$REAL_P/function/cursorfunction.php");
$nSayfakayit=50;

$nAdonem   = tarihtodonemid(time());
$nDonem    = isset($_GET["donem"])?$_GET["donem"]:$nAdonem;
$nSayfa    = isset($_GET["sayfa"])?$_GET["sayfa"]:0;
$nPersonel = isset($_GET["perso"])?$_GET["perso"]:$oUser->perso;
$nOffset   = $nSayfa*$nSayfakayit;

$cFields   = "servis_exp, perso_exp, perso_perno, tasaat Teorik_Gelis, tcsaat Teorik_Çýkýþ, sum(asaat) Geliþ_Fark, sum(csaat) Çýkýþ_Fark";

$cSqlStr   = "select SQL_CALC_FOUND_ROWS $cFields 
                from asist.mesai 
               where mesai.sirket=$oUser->sirket 
                 and mesai.donem=$nDonem 
               group by perso_exp
               order by servis_exp, perso_exp
               limit $nOffset, $nSayfakayit";
               
$curResult = mysqli_query($cDblink, $cSqlStr);

//echo mysqli_error($cDblink);

if ($par_islem=="down")
	cursor_to_excel($curResult,"$oUser->sirket_exp  Þirket Mesai Raporu");
else
{
	$objParam->cLink	= "?personel_sirketmesairaporu&donem=$nDonem";
	$objParam->cBaslik	= "$oUser->sirket_exp ”Þirket Mesai Raporu“";
	$objParam->nSayfano = $nSayfa;
	$objParam->nMaxrec	= $nSayfakayit;
	$objParam->cGrup	= "servis_exp";
    $objParam->aLink[]  = array("personel_exp", "?personel_aylikmesailistesi&donem=$nDonem&sayfa=0&personel=%personel");
	$objParam->cHideFlds= "personel,servis_exp,";
	listelehyperlink($curResult, $objParam);
}

for ($ii=$nDonem+1; $ii>=$nDonem-1 && $ii>=$nAdonem; $ii--)
	echo "<a href=\"?personel_sirketmesairaporu&donem=$ii&sayfa=0\">", donemidtodonem($ii),"</a> &nbsp; ";

?>
