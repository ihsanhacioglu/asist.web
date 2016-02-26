
<?php

include_once("$REAL_P/function/cursorfunction.php");

$nSayfakayit=31;

$nSayfa    = isset($_GET["sayfa"])?$_GET["sayfa"]:0;
$nPersonel = isset($_GET["personel"])?$_GET["personel"]:$oUser->personel;

$cPersonel = $nPersonel==$oUser->personel ? $oUser->exp :table_exp("personel", $nPersonel);
$nOffset   = $nSayfa*$nSayfakayit;

// $cFields   = "A.id, A.ktarih, A.atarih, A.asaat, P.exp pamir_exp, A.sebebi, A.personel, A.durum, D.exp durum_exp ";
$cFields   = "A.*, P.exp personel_exp, D.exp durum_exp ";

$cSqlStr   = "select SQL_CALC_FOUND_ROWS $cFields 
                from asist.mesaidty A,
                     asist.personel P,
                     asist.durum D 
               where A.personel=P.id 
                 and A.durum = D.id
                 and A.personel=$nPersonel 
				order by A.atarih
               limit $nOffset, $nSayfakayit";
               
$curResult = mysqli_query($cDblink, $cSqlStr);
echo mysqli_error($cDblink);

if ($par_islem=="down")
	cursor_to_excel($curResult,"$cPersonel Mesai Düzeltme Listesi");
else {
	$objParam->cLink	= "?personel_mesaiduzeltmelistesi";
	$objParam->cBaslik	= "$cPersonel Mesai Düzeltme Listesi";
    $objParam->cGrup    = "durumu";    
	$objParam->nSayfano = $nSayfa;
	$objParam->nMaxrec	= $nSayfakayit;
	$objParam->aLink[]  = array("id", "?personel_mesaiduzeltmeformu&id=%id");
	listelehyperlink($curResult, $objParam);
}

echo <<<END
<br/><br/>
<b>Açýklamalar</b><br/>
$cKare <a href="?personel_mesaiduzeltmeformu"> ”Mesai Düzeltme Formu</a> nu kullanarak düzeltme talep edebilirsiniz.“ <br/>
$cKare <a href="?personel_disgorevformu"> ”Dýþ Görev Formu</a> nu kullanarak genel merkez dýþýndaki görevlerinizi sisteme ekletebilirsiniz.“
END;
?>
