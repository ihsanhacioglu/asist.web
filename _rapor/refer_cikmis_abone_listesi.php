
<?php

include_once("$REAL_P/function/cursorfunction.php");

$nSayfa=0;
if (count($arr_pars)>=2 && is_numeric($arr_pars[1]))
    $nSayfa=$arr_pars[1];
$nOffset=$nSayfa*20;

$cAboneFields = "kimlik, durum D, exp, ctarih, sehir, pkod, sokak, refer_exp, rekip_exp ";
$strSql="select SQL_CALC_FOUND_ROWS $cAboneFields from asist.abone where durum='C' and abone.refer=$oUser->id limit $nOffset, 20";
$result=mysqli_query($cDblink,$strSql);

if ($par_islem=="down")
	cursor_to_excel($result, "$oUser->exp  Referans Abone Listesi");
else
	list_cursor($result,"?referabolist", "$oUser->exp  Referans Abone Listesi", $nSayfa);
?>