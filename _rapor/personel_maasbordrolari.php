
<?php

echo "<br/>";
echo "<b>�nceki D�nemlere Ait Maa� Bordrolar�n�z</b><br/>";

$cDirectory  = "$ROOT_D/_rapor";
$aFiles  = glob("C:/bordro/*$oUser->exp*");

if (count($aFiles)==0)
    echo "Maa� Bordro Kayd� Bulunmad�"; 
else 
{
    foreach ($aFiles as $cFile)
	{
		$cFile=basename($cFile);
        echo "$cKare <a href=\"http://$SERV_P"."?bordro_getir&dosya=$cFile\" target=\"blank\">$cFile</a><br/>\n";
	}
}
?>
