<?php

function regor_kontrol($cGorevyeri){
    global $oRegor;

    for ($ii=1; $ii<count($oRegor); $ii++)
        if ($oRegor[$ii]->goryeri==$cGorevyeri) return true;
    return false;
}

function table_exp($cTable, $nId){
    global $oAPP;
    
    $cSqlStr  = "select exp from asist.$cTable where $cTable.id = $nId";
    $oResult  = mysqli_query($oAPP->dblink, $cSqlStr);
    $oRow     = mysqli_fetch_object($oResult);

    return isset($oRow) ? $oRow->exp : "";
}


function getTableKayit($cTable, $nId=-1){
    global $oAPP;
    
    $cSqlStr  = "select * from asist.$cTable where $cTable.id = $nId";
    $oResult  = mysqli_query($oAPP->dblink, $cSqlStr);
    $oRow     = mysqli_fetch_object($oResult);

    return $oRow;
}


function setTableKayit($cTable, $oKayit){
    global $oAPP;

    $cSetStr   = getSetStrFromObject($oKayit);
    $cInsStr   = "insert into asist.$cTable set $cSetStr";
	echo $cSetStr;
    $oResult   = mysqli_query($oAPP->dblink, $cInsStr);

    return ;
}


function getTableFieldStr($cTable){
    return ;
}

function getObjectFieldStr($oKayit){
    $aKayit = get_object_vars($oKayit);
    
    foreach($aKayit  as $cField => $cValue)
    {
        $cFieldStr .= ", '".$cField."'" ; 
    }
    $cFieldStr = substr($cFieldStr, 2) ;
    
    return $cFieldStr ;
}

function getSetStrFromObject($oKayit){
	$cFieldStr="";
    
    foreach($oKayit  as $cField => $cValue)
        $cFieldStr .= ",$cField=\$oKayit->$cField";
    $cFieldStr = substr($cFieldStr,1);

    return $cFieldStr ;
}

function getSetStrFromObjectAuto($oKayit)
{
	$cFieldStr="";
    
    foreach($oKayit  as $cField => $cValue)
        $cFieldStr .= ",$cField=\$oKayit->$cField";
    $cFieldStr = substr($cFieldStr,1);

    return $cFieldStr ;
}

function yildizField($yildiz_exp, $yildiz_tip, $rec_fld, $id_fld){
    global $oAPP;

	$cIdField=is_null($id_fld)?"":" idfield='id_$id_fld->name'";
	$cIdName =is_null($id_fld)?"":$id_fld->name;
	$cIdValue=is_null($id_fld)?"":$id_fld->value;
	$cField=is_object($rec_fld)?$rec_fld->name :$rec_fld;
	$cValue=is_object($rec_fld)?$rec_fld->value:"";
	$cParam=$yildiz_tip=="yildiz" ? " param='oner&yildiz=$yildiz_exp'" : "";

	echo "<input class='txt-exp' name='frm_$cField' id='id_$cField' itype='YXT' value='$cValue'$cIdField$cParam/>";
	echo "<input class='cmb-tus' id='btn_$cField' type='button' onClick='id_$cField.value=\"..\"' value=''/>";

	if (!is_null($id_fld)) echo "<input class='txt-id' name='frm_$cIdName' id='id_$cIdName' value='$cIdValue' readonly/>";
    echo "\n<script>\n";
	echo "	id_$cField.onfocus=Typecast.Behaviours.Suggest.Run;\n";
	echo "	id_$cField.onkeyup=Typecast.Behaviours.Suggest.KeyHandler;\n";
	echo "	id_$cField.onkeydown=Typecast.Behaviours.Suggest.KeyHandler;\n";
	echo "	id_$cField.onblur=Typecast.Behaviours.Suggest.Stop;\n";
	echo "	id_$cField.onmouseup=Typecast.Behaviours.Suggest.MouseUp;\n";
	if($yildiz_tip=="query" || $yildiz_tip=="table"){
		if($yildiz_tip=="table"){
			list($cListTab,$cListFld)=explode(".",$yildiz_exp,2);
			$cListFld=empty($cListFld)?"exp":($cListFld=="exp"?$cListFld:"$cListFld exp");
		    $cSqlStr="select id, $cListFld from asist.$cListTab order by 2";
		}
		else $cSqlStr=$yildiz_exp;

	    $oListqry=new clsQry($oAPP->dblink, $cSqlStr);
		$oListqry->open(false,false);
		echo "	newDic=new Array();\n";
		while($oListqry->next()){
			echo "	newDic[newDic.length]=['",$oListqry->arrFields[0]->value,"','",$oListqry->arrFields[1]->value,"'];\n";
		}
		echo "	Typecast.Config.Data.Suggest.Dictionaries.id_$cField=newDic;\n";
		$oListqry->close();
	}
	elseif($yildiz_tip=="value"){
		$arrVals=explode(",", $strValue);
		echo "	newDic=new Array();\n";
		foreach ($arrVals as $strVal)
			echo "	newDic[newDic.length]=['",$cVal,"','",$cDisp,"'];\n";
		echo "	Typecast.Config.Data.Suggest.Dictionaries.id_$cField=newDic;\n";
	}
	echo "</script>\n";
}

// *--------------

function listelehyperlink($curRes, $oParam)
{
    global $oAPP;
    
    $cLink      = isset($oParam->cLink)		? $oParam->cLink    : "";
    $cBaslik    = isset($oParam->cBaslik)	? $oParam->cBaslik  : "";
    $nSayfano   = isset($oParam->nSayfano)	? $oParam->nSayfano : 0;
    $nMaxrec    = isset($oParam->nMaxrec)	? $oParam->nMaxrec  : 20;
    $cGrup      = isset($oParam->cGrup)		? $oParam->cGrup    : "";
    $cHideFlds	= isset($oParam->cHideFlds)	? $oParam->cHideFlds: "";
	$aLink      = "";
    if (isset($oParam->aLink))
	{
		$aLink   = $oParam->aLink[0];
		$cHypFld = $aLink[0];
	    $cHypLink= $aLink[1];
	}

    $oCursor   = mysqli_query($oAPP->dblink, "select FOUND_ROWS()");
    $oRow      = mysqli_fetch_array($oCursor);
    $nReccount = $oRow[0];
    $nMaxsayfa = floor($nReccount/$nMaxrec);

    echo "\n<table class='sample'>\n";

    $cSayfaStr = "";
    
    if ($nSayfano>0)
    {
        $nLsayfa=$nSayfano-1;
        $cSayfaStr.="<a href=\"$cLink&sayfa=$nLsayfa\">Önceki</a>&nbsp;&nbsp;";
    }
    if ($nMaxsayfa>0)
        $cSayfaStr.=($nSayfano+1)."/".($nMaxsayfa+1)." &nbsp;&nbsp;";
    else
        $cSayfaStr.="$nReccount Kayýt &nbsp;&nbsp;";

    if ($nSayfano<$nMaxsayfa)
    {
        $nLsayfa=$nSayfano+1;
        $cSayfaStr.="<a href=\"$cLink&sayfa=$nLsayfa\">Sonraki</a>&nbsp;&nbsp;";
    }

    $nCount = mysqli_num_fields($curRes);
    echo "<tr class='sayfa'><td align=right colspan=$nCount style='padding-left:0; padding-right:0'><div style='float:left'><b>$cBaslik</b></div><div style='float:right'>$cSayfaStr</div></td></tr>\n";
    $cRowLine = "echo \"<tr class=\\\"d\$cls\\\">";
    echo "<tr class='d0'>";

    // row deseni oluþturuluyor oluþturuluyor
    // grup field ýn sýranosu tespit ediliyor
	$aFields=mysqli_fetch_fields($curRes);
	if (is_array($aLink))
		for ($ii=0; $ii<count($aFields); $ii++)
			$cHypLink=str_replace("%".$aFields[$ii]->name,'$oRow->'.$aFields[$ii]->name,$cHypLink);

	for ($ii=0; $ii<count($aFields); $ii++)
    {
        if ($cGrup==$aFields[$ii]->name) $cGrupFld=$cGrup;
		if (strpos($cHideFlds,$aFields[$ii]->name.",")===false)
		{
	        echo '<td>'.$aFields[$ii]->name.'</td>';
	        if (isset($cHypFld) && $cHypFld==$aFields[$ii]->name) 
	            $cRowLine.='<td><a href=\"'.$cHypLink.'\">$oRow->'.$aFields[$ii]->name.'</a></td> ';
	        else
	            $cRowLine.='<td>$oRow->'.$aFields[$ii]->name.'</td> ';
		}
    }

    echo "</tr>\n";

    $cRowLine.='</tr>\n";';
    $nn=0;
    $cGrupExp="";

    while($oRow = mysqli_fetch_object($curRes))
    {
        if (isset($cGrupFld) && $cGrupExp!=$oRow->$cGrupFld)
        {
            $cGrupExp = $oRow->$cGrupFld;
            echo "<td colspan=$nCount>".$oRow->$cGrupFld."</td>";
        }
        $cls = $nn++%2+1;
        eval($cRowLine);
    }
    
    echo("</table>\n");
}


function listele($curRes, $oParam)
{
    global $oAPP;
	
    $cLink		= isset($oParam->cLink)	    ? $oParam->cLink	: "";
    $cBaslik	= isset($oParam->cBaslik)	? $oParam->cBaslik	: "";
    $nSayfano	= isset($oParam->nSayfano)	? $oParam->nSayfano : 0;
    $nMaxrec	= isset($oParam->nMaxrec)	? $oParam->nMaxrec	: 20;
    $cGrup		= isset($oParam->cGrup)	    ? $oParam->cGrup	: "";

    $oCursor   = mysqli_query($oAPP->dblink, "select FOUND_ROWS()");
    $aRow      = mysqli_fetch_array($oCursor);
    $nReccount = $aRow[0];
    $nMaxsayfa = floor($nReccount/$nMaxrec);

    echo "\n<table class='sample'>\n";

    $cSayfaStr = "";
    
    if ($nSayfano>0)
    {
        $nLsayfa=$nSayfano-1;
        $cSayfaStr.="<a href=\"$cLink, $nLsayfa\">Önceki</a>&nbsp;&nbsp;";
    }
    if ($nMaxsayfa>0)
        $cSayfaStr.=($nSayfano+1)."/".($nMaxsayfa+1)." &nbsp;&nbsp;";
    else
        $cSayfaStr.="$nReccount Kayýt &nbsp;&nbsp;";

    if ($nSayfano<$nMaxsayfa)
    {
        $nLsayfa=$nSayfano+1;
        $cSayfaStr.="<a href=\"$cLink,$nLsayfa\">Sonraki</a>&nbsp;&nbsp;";
    }
    
    $nFldCount = mysqli_num_fields($curRes);

    echo "<tr class='sayfa'><td align=right colspan=$nFldCount style='padding-left:0; padding-right:0'><div style='float:left'><b>$cBaslik</b></div><div style='float:right'>$cSayfaStr</div></td></tr>\n";

    $cRowLine = "echo \"<tr class=\\\"d\$cls\\\">";
    
    //$cRowLine = 'echo "'."<tr class='d\$cls'".'>"';
    
    
    echo "<tr class='d0'>";
    for ($ii=0; $ii<mysqli_num_fields($curRes); $ii++)
    {
		$objFld_info=mysqli_fetch_field_direct($curRes,$ii);
		if ($cGrup==$objFld_info->name) 
            $nGrupFld=$ii;
		echo '<td>'.$objFld_info->name.'</td>';
		$cRowLine.='<td>".$aRow['.$ii.']. "</td> ';
    }

    echo "</tr>\n";

    $cRowLine.='</tr>\n";';
    $nn=0;
	$cGrupExp="";
    while($aRow = mysqli_fetch_array($curRes))
	{
		if (isset($nGrupFld) && $cGrupExp!=$aRow[$nGrupFld])
		{
			$cGrupExp=$aRow[$nGrupFld];
			echo "<td colspan=$nFldCount>$aRow[$nGrupFld]</td>";
		}
		$cls = $nn++%2+1;
        eval($cRowLine);
    }
    echo("</table>\n");
}



function list_cursor($curRes, $cLink, $cBaslik, $nSayfano, $nMaxrec=20)
{
    global $oAPP;

    $oCursor   = mysqli_query($oAPP->dblink, "select FOUND_ROWS()");
    $aRow      = mysqli_fetch_array($oCursor);
    $nReccount = $aRow[0];
    $nMaxsayfa = floor($nReccount/$nMaxrec);

    echo "\n<table class='sample'>\n";

    $cSayfaStr="";
    
    if ($nSayfano>0)
    {
        $nLsayfa=$nSayfano-1;
        $cSayfaStr.="<a href=\"$cLink,$nLsayfa\">Önceki</a>&nbsp;&nbsp;";
    }
    if ($nMaxsayfa>0)
        $cSayfaStr.=($nSayfano+1)."/".($nMaxsayfa+1)." &nbsp;&nbsp;";
    else
        $cSayfaStr.="$nReccount Kayýt &nbsp;&nbsp;";

    if ($nSayfano<$nMaxsayfa)
    {
        $nLsayfa=$nSayfano+1;
        $cSayfaStr.="<a href=\"$cLink,$nLsayfa\">Sonraki</a>&nbsp;&nbsp;";
    }

    echo "<tr class=\"sayfa\"><td align=right colspan=".mysqli_num_fields($curRes)." style=\"padding-left:0; padding-right:0\"><div style=\"float:left\"><b>$cBaslik</b></div><div style=\"float:right\">$cSayfaStr</div></td></tr>\n";

    $cRowLine="echo \"<tr class=\\\"d\$cls\\\">";
    
    echo "<tr class=\"d0\">";
    for ($ii=0; $ii<mysqli_num_fields($curRes); $ii++)
    {
		$objFld_info=mysqli_fetch_field_direct($curRes,$ii);
        echo '<td>'.$objFld_info->name.'</td>';
		$cRowLine.='<td>".$rows['. $ii .']. "</td> ';
    }

    echo "</tr>\n";

    $cRowLine.='</tr>\n";';
    $nn=0;
    while($rows=mysqli_fetch_array($curRes)){
	    $cls = $nn++%2+1;
        eval($cRowLine);
    }
    echo("</table>\n");
}


function list_form($curRes,$cLink,$cBaslik,$nSayfano)
{
    global $oAPP;

    $oCursor=mysqli_query($oAPP->dblink,"select FOUND_ROWS()");
    $aRow=mysqli_fetch_array($oCursor);
    $nReccount=$aRow[0];
    $nMaxsayfa=floor($nReccount/20);

    echo "\n<table class='sample'>\n";

    $cSayfaStr="";
    
    if ($nSayfano>0)
    {
        $nLsayfa=$nSayfano-1;
        $cSayfaStr.="<a href=\"$cLink($nLsayfa)\">Önceki</a>&nbsp;&nbsp;";
    }
    if ($nMaxsayfa>0)
        $cSayfaStr.="$nSayfano/$nMaxsayfa &nbsp;&nbsp;";
    else
        $cSayfaStr.="$nReccount Kayýt &nbsp;&nbsp;";

    if ($nSayfano<$nMaxsayfa)
    {
        $nLsayfa=$nSayfano+1;
        $cSayfaStr.="<a href=\"$cLink($nLsayfa)\">Sonraki</a>&nbsp;&nbsp;";
    }

    //echo "<tr class=\"sayfa\"><td align=right colspan=".mysqli_num_fields($curRes)." style=\"padding-left:0; padding-right:0\"><div style=\"float:left\"><b>$cBaslik</b></div><div style=\"float:right\">$cSayfaStr</div></td></tr>\n";

    $cRowLine="echo \"<tr class=\\\"d\$cls\\\">";
    
    echo "<tr class=\"d0\">";
    for ($ii=0; $ii<mysql_num_fields($curRes); $ii++)
    {
	$objFld_info=mysqli_fetch_field_direct($curRes,$ii);
	echo '<td>'.$objFld_info->name.'</td>';
	$cRowLine.='<td>".$rows['. $ii .']. "</td> ';
    }

    echo "</tr>\n";

    $cRowLine.='</tr>\n";';
    $nn=0;
    while($rows=mysqli_fetch_array($curRes)){
	    $cls = $nn++%2+1;
        eval($cRowLine);
    }
    echo("</table>\n");
}


function cursor_to_excel($curRes,$cDosya_adi)
{
    $file_name = $cDosya_adi;
    $file_type = "vnd.ms-excel";
    $file_ext  = "xls";

    header("Content-Type: application/$file_type; charset=iso-8859-9");
    header("Content-Disposition: attachment; filename=\"$file_name.$file_ext\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    $cRowLine='echo "';
    for ($ii=0; $ii<mysqli_num_fields($curRes); $ii++)
    {
		$objFld_info=mysqli_fetch_field_direct($curRes,$ii);
        echo $objFld_info->name."\t";
        $cRowLine.='$rows['.$ii.']\t';
    }
    echo "\n";
    $cRowLine.='\n";';

    while($rows=mysqli_fetch_array($curRes))
        eval($cRowLine);
}

function getdefaultvalue($cField){    
    global $oUser;
    
    $cValue = "";
         
    if     ($cField=="pamir_exp")  : $cValue = $oUser->perso_exp ;             
    elseif ($cField=="servis_exp") : $cValue = $oUser->servis_exp ;
    endif;
    return $cValue ; 
}

function getsenaryo($cFname){
    global $oAPP, $oUser;

	$cDil=strtolower($oUser->dilse);
	if($cDil!="de" && $cDil!="en") $cDil="tr";
	$tabSenaryo="senaryo_$cDil";

    $cSqlStr  = "select * from asist.$tabSenaryo senaryo where senaryo.action = '$cFname'";
    $oResult  = mysqli_query($oAPP->dblink, $cSqlStr);
    $oRow     = mysqli_fetch_object($oResult);

    return $oRow;
}

function stringextract($cString, $cBeginDelim, $cEndDelim=''){
	if (empty($cBeginDelim)) $nStart=0;
	else{
		$nStart=strpos($cString, $cBeginDelim);
		if($nStart===false) return "";
		$nStart+=strlen($cBeginDelim);
	}

	if (empty($cEndDelim)) $nFinish=strlen($cString);
	elseif (($nFinish=strpos($cString, $cEndDelim, $nStart))===false) return "";

    return  substr($cString, $nStart, $nFinish-$nStart);
}


/* tarihtodonemid()
kendisine parametre olrak verilen tarihe ait donemid deðerini döndürüü
15.03.2009 tarihi için -903 deðerini döndürür. */

function TarihToDonemId($dTarihi){
    return !empty($dTarihi) ? -(date("Y", $dTarihi)-2000)*100-date("n", $dTarihi) : -1;
}

/* tarihtodonem()
kendisine parametre olrak verilen tarihe ait donemid deðerini döndürüü
15.03.2009 tarihi için 2009. 03. Ay stringini döndürür. */

function DonemIdToDonem($nDonem){
    return !empty($nDonem) ? (-substr($nDonem, 0, strlen($nDonem)-2)+2000).". ".(substr($nDonem, -2).". Ay") : "";
}

/* tarihtodonem()
kendisine parametre olrak verilen tarihe ait donemid deðerini döndürüü
15.03.2009 tarihi için 2009. 03. Ay stringini döndürür. */

function TarihToDonem($dTarihi){
    return !empty($dTarihi) ? date("Y", $dTarihi).". ".date("n", $dTarihi).". Ay" : "";
}

?>