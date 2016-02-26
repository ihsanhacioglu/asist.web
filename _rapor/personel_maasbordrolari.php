
<?php

echo "<br/>";
echo "<b>Önceki Dönemlere Ait Maaþ Bordrolarýnýz</b><br/>";

$cDirectory  = "$ROOT_D/_rapor";
$aFiles  = glob("C:/bordro/*$oUser->exp*");

if (count($aFiles)==0)
    echo "Maaþ Bordro Kaydý Bulunmadý"; 
else 
{
    foreach ($aFiles as $cFile)
	{
		$cFile=basename($cFile);
        echo "$cKare <a href=\"http://$SERV_P"."?bordro_getir&dosya=$cFile\" target=\"blank\">$cFile</a><br/>\n";
	}
}
?>
