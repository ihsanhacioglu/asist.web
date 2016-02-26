
<?php

include_once("$REAL_P/function/cursorfunction.php");

$nSayfa = 0;

$strSql = "drop temporary table if exists table1";
$result = mysqli_query($cDblink,$strSql);

$strSql = "create temporary table table1
	select refer_exp `Referans adý`,
	    max(durum)	Durum,
		sum(hedef)	Hedef,
		sum(aadet)	Aktif,
		sum(acik)	'Açýk',
		cast(sum(aadet)/sum(hedef)*100 as SIGNED) `H %`,
		sum(daadet)	Giren,
		sum(dcadet)	'Çýkan'
	from asist.ahtrefer
	where rekip_exp='$arr_pars[1]'
	group by refer_exp
	with rollup";
$result = mysqli_query($cDblink,$strSql);

$strSql = "update table1 set durum='', `Referans adý`='$arr_pars[1] toplam' where `Referans adý` is null";
$result = mysqli_query($cDblink,$strSql);

$strSql = "select * from table1";
$result = mysqli_query($cDblink,$strSql);

if ($par_islem=="down")
	cursor_to_excel($result,"$arr_pars[2] Rekip Raporu");
else{
	list_cursor($result, "?abone_rp_rekipexpaktifhedef", "$arr_pars[1] Rekip Raporu", $nSayfa);
	include_once("$REAL_P/_rapor/abone_rp_rekipexpaktifhedef_footer.php");
}	

?>
