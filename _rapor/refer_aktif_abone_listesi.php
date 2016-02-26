
<?php

include_once("$REAL_P/function/cursorfunction.php");

$nSayfa=0;
if (count($arr_pars)>=2 && is_numeric($arr_pars[1]))
    $nSayfa=$arr_pars[1];
$nOffset=$nSayfa*20;
							
$cAboneFields = "kimlik, durum D, exp, atarih, sehir, pkod, sokak, refer_exp, rekip_exp ";
$strSql="select SQL_CALC_FOUND_ROWS $cAboneFields from asist.abone where (durum='A' or durum='B') and abone.refer=$oUser->id limit $nOffset, 20";
$curResult = mysqli_query($cDblink,$strSql);

if ($par_islem=="down")
	cursor_to_excel($curResult,"$oUser->exp  Referans Abone Listesi");
else
	list_cursor($curResult,"?referabolist","$oUser->exp  Referans Abone Listesi",$nSayfa);

?>
