
<?php
include_once("$REAL_P/function/cursorfunction.php");

if (!regor_kontrol($arr_pars[1]))
{
	include("_template/default.php");
	exit;
}

include_once("$REAL_P/function/cursorfunction.php");

$nSayfa = 0;

$strSql = "drop temporary table if exists table1";
$result = mysqli_query($cDblink,$strSql);

$strSql = "create temporary table table1
	select rekip_grup `Rekip Grup Adý`,
		sum(1)	Refer,	
		sum(if(durum='A', 1, 0))	Durum,
		sum(hedef)	Hedef,
		sum(aadet)	Aktif,
		sum(acik)	'Açýk',
		cast(sum(aadet)/sum(hedef)*100 as SIGNED) `H %`,
		sum(daadet)	Giren,
		sum(dcadet)	'Çýkan'
	from asist.ahtrefer
	where rekip_ustgrup='$arr_pars[1]'
	group by rekip_grup
	with rollup";
$result = mysqli_query($cDblink,$strSql);

$strSql = "update table1 set `Rekip Grup Adý`='$arr_pars[1] Toplam' where `Rekip Grup Adý` is null";
$result = mysqli_query($cDblink,$strSql);

$strSql = "select * from table1";
$result = mysqli_query($cDblink,$strSql);

if ($par_islem=="down")
	cursor_to_excel($result,"$arr_pars[2] Rekip Grup Raporu");
else
	list_cursor($result, "?abone_rp_rekipustgrupaktifhedef", "$arr_pars[1] Rekip Grup Raporu", $nSayfa);
	include_once("$REAL_P/_rapor/abone_rp_rekipgrupaktifhedef_footer.php");

?>
