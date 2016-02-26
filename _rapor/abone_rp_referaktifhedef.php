
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
	echo "Aktif Abone Say�n�z : $oRow->aadet <br/>" ;
	echo "Hedefe Ula�ma Y�zdeniz : $oRow->yuzde % <br/>";
    
	echo "<br/>";
	echo "<strong>Son Bir Ayda</strong><br/>";
	echo "Yapt���n�z Abone Say�n�z : $oRow->daadet <br/>" ;
	echo "Abonelikten ��kan Say�n�z : $oRow->dcadet <br/>" ;
	include_once("$REAL_P/_class/dataclass.php");

	$tab=new clsTable($cDblink,"abone",-1);
	$tab->autoInc=true;
	$tab->fld_id->value=-2;
	$tab->fld_abone_exp->value="DENEMED�R";
	$tab->insert();
}
else 
{
	echo "Size ait aktif hedef bilgileri tespit edilemedi.";
}

?>
