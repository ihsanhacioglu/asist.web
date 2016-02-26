
<?php

include_once("$REAL_P/function/cursorfunction.php");

$nSayfakayit=31;

$nAdonem   = tarihtodonemid(time()) ;
$nDonem    = isset($_GET["donem"])?$_GET["donem"]:$nAdonem;
$nSayfa    = isset($_GET["sayfa"])?$_GET["sayfa"]:0;
$nPersonel = isset($_GET["personel"])?$_GET["personel"]:$oUser->personel;
$cPersonel = $nPersonel==$oUser->personel ? $oUser->exp :table_exp("personel", $nPersonel);
$nOffset   = $nSayfa*$nSayfakayit;

$cFields   = "atarih �al��ma_G�n�, asaat Giri�_Saati, csaat ��k��_Saati, acikla A��klama";
$cSqlStr   = "select SQL_CALC_FOUND_ROWS $cFields 
                from asist.mesai 
               where mesai.personel=$nPersonel 
                 and mesai.donem=$nDonem
				order by atarih
               limit $nOffset, $nSayfakayit";
               
$curResult = mysqli_query($oAPP->dblink, $cSqlStr);
echo mysqli_error($oAPP->dblink);

if ($par_islem=="down")
	cursor_to_excel($curResult,"$cPersonel Personel Ayl�k Mesai Listesi");
else
{
	$objParam->cLink	= "?personel_aylikmesailistesi&donem=$nDonem";
	$objParam->cBaslik	= "$cPersonel Personel Ayl�k Mesai Listesi";
	$objParam->nSayfano = $nSayfa;
	$objParam->nMaxrec	= $nSayfakayit;
	listelehyperlink($curResult, $objParam);
}


// personel mesai �zet bilgileri 

$cSqlStr   = "select personel,
                     sum(if(afark<0, afark, 0)) afarke,
                     sum(if(afark>0, afark, 0)) afarka, 
                     sum(if(cfark<0, cfark, 0)) cfarke,
                     sum(if(cfark>0, cfark, 0)) cfarka
                from asist.mesai 
               where mesai.personel=$nPersonel 
                 and mesai.donem=$nDonem 
               group by personel";

            
$oResult = mysqli_query($oAPP->dblink, $cSqlStr);
// $nKayit  = mysqli_num_rows($oResult);
$oRow = mysqli_fetch_object($oResult);
$cKare = "<IMG height=5 src='image/karenokta.gif' width=5 border=0>";

if (isset($oRow))
echo <<<END
	<br/>
	<b>Genel Durum</b><br/>
	$cKare Sabah Negatif S�re : $oRow->afarke Dakika<br/>
	$cKare Ak�am Negatif S�re : $oRow->cfarke Dakika<br/>
	$cKare Sabah Pozitif S�re : $oRow->afarka Dakika<br/>
	$cKare Ak�am Pozitif S�re : $oRow->cfarka Dakika<br/>
END;

echo "<br/>";
echo "<b>�nceki D�nemlere Ait Mesai Bilgileriniz</b><br/>";

for ($ii=$nDonem+1; $ii>=$nDonem-1 && $ii>=$nAdonem; $ii--)
	echo "$cKare <a href=\"?personel_aylikmesailistesi&donem=$ii&sayfa=0\">", donemidtodonem($ii),"</a> &nbsp; ";

echo <<<END
<br/><br/>
<b>A��klamalar</b><br/>
$cKare <a href="?personel_mesaiduzeltmeformu"> Mesai D�zeltme Formu</a> nu kullanarak d�zeltme talep edebilirsiniz. <br/>
$cKare <a href="?personel_disgorevformu"> D�� G�rev Formu</a> nu kullanarak genel merkez d���ndaki g�revlerinizi sisteme ekletebilirsiniz.
END;

?>