<?php

    $cAra_exp="";
    if (count($arr_pars)>=3)
        $cAra_exp=$arr_pars[2];

    $strSql="select id,abone_exp,sehir,pkod,sokak from asist.abone where abone_exp like '$cAra_exp%' order by exp limit 0, 10";
    $result=mysqli_query($oAPP->dblink,$strSql);

    header("Content-Type: text/html; charset=iso-8859-9");

    while($rows=mysqli_fetch_array($result)) echo $rows[0]."\t".$rows[1]."###";
?>