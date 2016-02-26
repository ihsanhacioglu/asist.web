
<?php

$strSql = "select hedef,
			 	  aadet,
				  acik,
				  cast(aadet/hedef*100 as SIGNED) yuzde,
				  daadet,
				  dcadet
			 from asist.ahtrefer
	        where refer = $oUser->id";

$curResult = mysqli_query($cDblink,$strSql);
$nCount = mysqli_num_rows($curResult);

if($nCount==1){
    $oRow = mysqli_fetch_object($curResult);
	
	echo "<br/>";
	echo "<strong>Aktif Hedef Bilgileri</strong><br/>";
	echo "Hedefiniz : $oRow->hedef <br/>" ;
	echo "Aktif Abone Sayýnýz : $oRow->aadet <br/>" ;
	echo "Hedefe Ulaþma Yüzdeniz : $oRow->yuzde % <br/>";
    
	echo "<br/>";
	echo "<strong>Son Bir Ayda</strong><br/>";
	echo "Yaptýðýnýz Abone Sayýnýz : $oRow->daadet <br/>" ;
	echo "Abonelikten Çýkan Sayýnýz : $oRow->dcadet <br/>" ;
	include_once("$REAL_P/_class/dataclass.php");

	$tab=new clsTable($cDblink,"abone",-1);
	$tab->autoInc=true;
	$tab->fld_id->value=-2;
	$tab->fld_abone_exp->value="DENEMEDÝR";
	$tab->insert();
}
else 
{
	echo "Size ait aktif hedef bilgileri tespit edilemedi.";
}

?>
