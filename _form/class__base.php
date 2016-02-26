<?php

class class__base
{
    public $appLink = null;
    public $qry = null;
    public $det = null;
    public $senaryo = null;
    public $arrCals = null;
    public $dil = "";
    public $islem = "";
    public $pre = "";
    public $colCount = 0;
    public $arrTics = null;
    public $arrForm = null;
    public $arrCond = null;
    public $arrEval = null;

    public $arrWrap = null;
    public $arrIlis = null;
    public $arrDetay = null;

    public $arrObj = null;
    public $arrTicket = null;
    public $arrAsgn = null;
    public $mod = null;
    public $grd = null;
    public $tic = null;
    public $ticSen = null;
    public $ticKey = null;
    public $strMessage = "";
    public $main = null;
    public $edit = 1;
    public $dbClass = "";
    public $msg = "";
    public $act = "";
    public $par = "";

    function __construct($nLink, $cAction)
    {
    }

    function modMessage($sure = 0)
    {
        echo "
		<br>{$this->senaryo->exp}<br>
		<input class='tus' type='button' onClick='window.close()' value='Kapat'/>
		<br>$this->strMessage<br>
		";
        $msure = $sure * 1000;
        if (empty($sure)) {
            echo "<script>window.onload=function(){window.opener.location.reload();window.close();}</script>";
        } else {
            echo "<script>window.onload=function(){window.opener.location.reload();setTimeout('window.close()',$msure);}</script>";
        }
    }

    function msgMessage($sure = 0)
    {
        echo "
		<br>{$this->senaryo->exp}<br>
		<br><input class='tus' type='button' onClick='window.close()' value='Kapat'/><br>
		<br>$this->strMessage<br>
		";
        $msure = $sure * 1000;
        if (empty($sure)) {
            echo "<script>window.onload=function(){window.close();}</script>";
        } else {
            echo "<script>window.onload=function(){setTimeout('window.close()',$msure);}</script>";
        }
    }

    function usrMessage($url = "")
    {
        echo "
		<br>{$this->senaryo->exp}<br>
		<br>$this->strMessage<br>
		<br>$url<br>
		";
    }

    function create_qry($strClass = null)
    {
        $oDB = connect_datab($this->senaryo->datab);
        $strClass = (empty($strClass) ? (empty($this->dbClass) ? "cls$oDB->cls" : $this->dbClass) : $strClass);
        $this->qry = new $strClass($oDB->dblink, $this->senaryo->sqlstr, $this->main, true);

        $this->qry->senaryo = $this->senaryo->id;
        $this->qry->open("1=0");
        $this->qry->setKeyQry($this->senaryo->updtables);
    }

    function formObjArr($cFormStr = "")
    {
        $arrObject = explode("#", $cFormStr);
        if (count($arrObject) == 0) {
            return false;
        }

        $this->arrForm = array();
        foreach ($arrObject as $strObject) {
            if (empty($strObject)) {
                continue;
            }
            $type = null;
            $span = null;
            if (strpos(",FF,TT,RR,DD,F/,T/,R/,D/,BR,", substr($strObject, 0, 2) . ",")) {
                $type = substr($strObject, 0, 2);
            } elseif (strpos(",/,+,C,B,-,", substr($strObject, 0, 1) . ",")) {
                $type = substr($strObject, 0, 1);
            }

            if (($cnt = preg_match_all("/\s*(\w+)\s*=(.+)( &|$)/sU", $strObject, $arrProp, PREG_SET_ORDER)) || !is_null($type)) {
                $objForm = (object)array("type" => "$type", "name" => ""/*,"caption"=>""*/, "span" => "$span");
                if ($cnt) {
                    foreach ($arrProp as $val) {
                        $objForm->{$val[1]} = trim($val[2]);
                    }
                }
                $this->arrForm[] = $objForm;
            }
        }
        return true;
    }

    function createEval()
    {
        $this->arrWrap = array();
        $this->arrIlis = array();
        $this->arrDetay = array();
        $this->arrCond = array();
        $this->arrObj = array();
        $this->arrTicket = array();
        $this->arrEval = array();
        $str_eval = "";
        $ifname = "";
        $arrType = array("HEADER" => 0, "REPEAT" => 0, "IFLINE" => 0);
        foreach ($this->arrForm as $objForm) {
            $type = $objForm->type;
            if (isset($arrType[$type])) {
                $arrType[$type] = !$arrType[$type];
                if ($arrType[$type]) {
                    if (!empty($str_eval)) {
                        $this->arrEval[] = $str_eval;
                    }
                    if ($type == "IFLINE") {
                        $ifname = $objForm->name;
                    }
                } elseif ($type == "IFLINE") {
                    $this->arrCond[$ifname] = $str_eval;
                } else {
                    $this->arrEval[$type] = $str_eval;
                }
                $str_eval = "";
            } else {
                $str_eval .= $this->repeatField($this->qry, $objForm);
            }
        }
        if (!empty($str_eval)) {
            $this->arrEval[] = $str_eval;
        }
    }

    function headerCol()
    {
        $header = 0;
        $nHeader = 0;
        $levHH = 0;
        $rowHH = 0;
        $repeat = 0;
        $nRepeat = 0;
        $levRR = 0;
        $rowRR = 0;
        foreach ($this->arrForm as $objForm) {
            if ($objForm->type == "HEADER") {
                $header = !$header;
            }
            if ($objForm->type == "REPEAT") {
                $repeat = !$repeat;
            }
            if ($header) {
                if (!$levHH && $rowHH && strpos(",/,+,RR,R/,", "$objForm->type,")) {
                    break;
                }
                if (!$rowHH && strpos(",/,RR,", "$objForm->type,")) {
                    $rowHH = 1;
                }
                if ($objForm->type == "TT") {
                    $levHH++;
                }
                if ($objForm->type == "T/") {
                    $levHH--;
                }
                if (!$levHH && $rowHH && strpos(",/,DD,C,", "$objForm->type,")) {
                    $nHeader++;
                }
            } elseif ($repeat) {
                if (!$levRR && $rowRR && strpos(",/,+,RR,R/,", "$objForm->type,")) {
                    break;
                }
                if (!$rowRR && strpos(",/,RR,", "$objForm->type,")) {
                    $rowRR = 1;
                }
                if ($objForm->type == "TT") {
                    $levRR++;
                }
                if ($objForm->type == "T/") {
                    $levRR--;
                }
                if (!$levRR && $rowRR && strpos(",/,DD,C,", "$objForm->type,")) {
                    $nRepeat++;
                }
            }
        }
        $this->colCount = $nHeader ? $nHeader : $nRepeat;
    }

    function yildizField($QQ, $objForm, $rec_fld, $cOption)
    {
        $strKey = "frm";
        if (isset($objForm->tic) || isset($objForm->cal)) {
            if (isset($objForm->tic)) {
                $strArr = "arrTics";
                $strKey = "tic";
            } else {
                $strArr = "arrCals";
                $strKey = "cal";
            }
        }

        $yildiz_exp = $objForm->yildiz_exp;
        $yildiz_tip = $objForm->yildiz_tip;

        if (isset($objForm->cal)) {
            $id_fld = isset($objForm->yildiz_id) ? $objForm->yildiz_id : null;
            $cFrm_name_id = is_null($id_fld) ? "" : "{$this->pre}cal_$id_fld";
            $cId_name_id = is_null($id_fld) ? "" : "{$this->pre}id_cal_$id_fld";
            $cIdValue = is_null($id_fld) ? "" : $this->arrCals["$id_fld"];
        } else {
            $id_fld = isset($objForm->yildiz_id) ? $QQ->fieldByName($objForm->yildiz_id) : null;
            $cFrm_name_id = is_null($id_fld) ? "" : "{$this->pre}frm_$id_fld->name";
            $cId_name_id = is_null($id_fld) ? "" : "{$this->pre}id_$id_fld->name";
            $cIdValue = is_null($id_fld) ? "" : ($this->islem == "src" && !empty($id_fld->filter) ? $id_fld->filter : $id_fld->value);
        }

        $cIdField = is_null($id_fld) ? "" : " idfield='$cId_name_id'";
        for ($ii = 2; isset($objForm->{"id$ii"}); $ii++) {
            $cIdField .= " id$ii='{$this->pre}id_" . $objForm->{"id$ii"} . "'";
        }

        if (isset($objForm->cal)) {
            $cFrm_name = "{$this->pre}cal_" . $rec_fld;
            $cId_name = "{$this->pre}id_cal_" . $rec_fld;
            $cValue = $this->arrCals["$rec_fld"];
        } else {
            $cFrm_name = "{$this->pre}frm_" . (is_object($rec_fld) ? $rec_fld->name : $rec_fld);
            $cId_name = "{$this->pre}id_" . (is_object($rec_fld) ? $rec_fld->name : $rec_fld);
            $cValue = is_object($rec_fld) ? $rec_fld->value : "";
        }

        $cBtn_name = "{$this->pre}btn_" . (is_object($rec_fld) ? $rec_fld->name : $rec_fld);
        $cTitle = "$cFrm_name" . (isset($objForm->title) ? ": $objForm->title" : "");

        $cParam = "";
        if ($yildiz_tip == "yildiz") {
            $p0n = "";
            for ($ii = 1; isset($objForm->{"p$ii"}); $ii++) {
                $strP = $objForm->{"p$ii"};
                $strP = $this->buildValue($QQ, $strP);
                //if (preg_match_all("/%(\w+)(\W|$)/U",$strP,$flds,PREG_SET_ORDER))
                //foreach ($flds as $name) if ($oFld=$QQ->fieldByName($name[1]))$strP=str_replace("%$name[1]",$oFld->value,$strP);
                $p0n .= "&p$ii=$strP";
            }
            $cParam = "param='oner&yildiz=yildiz_{$this->senaryo->id}:$yildiz_exp$p0n'";
        } elseif ($yildiz_tip == "oner") {
            $cParam = "param='oner&yildiz=$yildiz_exp'";
        }
        $cParam .= isset($objForm->yildiz_action) ? " action='$objForm->yildiz_action'" : "";
        $cAttrib = isset($objForm->attrib) ? " $objForm->attrib" : "";

        $retStr = "";
        $retStr .= "<input class='txt-exp'$cAttrib name='$cFrm_name' id='$cId_name' itype='YXT' title='$cTitle' value='$cValue'$cIdField$cParam$cOption autocomplete='off'/>";
        $cTusOpt = strpos($cOption, "readonly") ? " readonly='1'" : "";
        //$retStr.="<input class='cmb-tus' id='$cBtn_name' type='button' onKeyDown='sonra(this,event)' value=''$cTusOpt tabindex=-1/>";

        $cIdType = $this->grd ? " hidden" : "";
        if (!is_null($id_fld)) {
            if (isset($objForm->shw)) {
                $retStr .= "<input class='txt-id'$cIdType name='$cFrm_name_id' id='$cId_name_id' title='$cFrm_name_id' onKeyDown='sonra(this,event)' value='$cIdValue' readonly='1' tabindex=-1/>";
            } else {
                $retStr .= "<input type='hidden' name='$cFrm_name_id' id='$cId_name_id' value='$cIdValue'/>";
            }
        }

        $retStr .= "<script>";
        //$retStr.="$cId_name.sug=new yildiz($cId_name,$cBtn_name);";
        $retStr .= "$cId_name.sug=new yildiz($cId_name);";
        if (isset($objForm->dic)) {
            $retStr .= " $cId_name.dic=new Array();";
        }

        if ($yildiz_tip == "query" || $yildiz_tip == "table") {
            if ($yildiz_tip == "table") {
                list($cListTab, $cListFld) = explode(".", $yildiz_exp, 2);
                $cListFld = empty($cListFld) ? "exp" : ($cListFld == "exp" ? $cListFld : "$cListFld exp");
                $cSqlStr = "select id, $cListFld from asist.$cListTab order by 2";
            } else {
                $cSqlStr = $yildiz_exp;
            }
            if (isset($objForm->datab)) {
                $oDB = connect_datab($objForm->datab);
                $strClass = "cls$oDB->cls";
                $oListqry = new $strClass($oDB->dblink, $cSqlStr);
            } else {
                $oListqry = new clsApp($this->appLink, $cSqlStr);
            }
            foreach ($oListqry->arrParams as $par) {
                if ($fld = $QQ->fieldByName(strtolower($par->name))) {
                    $par->value = $fld->value;
                }
            }
            $oListqry->open(null, null);
            $retStr .= " $cId_name.dic=new Array();";
            while ($oListqry->next()) {
                $retStr .= " $cId_name.dic[$cId_name.dic.length]=['{$oListqry->arrFields[0]->value}','{$oListqry->arrFields[1]->value}'";
                for ($ii = 2; $ii < count($oListqry->arrFields); $ii++) {
                    $retStr .= ",'{$oListqry->arrFields[$ii]->value}'";
                }
                $retStr .= "];";
            }
            $oListqry->close();
        } elseif ($yildiz_tip == "value") {
            $arrVals = explode(",", $yildiz_exp);
            if (preg_match_all("/\s*(\w+)\s*=\s*(.+?)\s*(,|$)/", $yildiz_exp, $arr_match, PREG_SET_ORDER)) {
                $retStr .= " $cId_name.dic=new Array();";
                foreach ($arr_match as $match) {
                    $retStr .= " $cId_name.dic[$cId_name.dic.length]=['$match[1]','$match[2]'];";
                }
            }
        }
        $retStr .= "</script>";
        return $retStr;
    }

    function radioField($QQ, $objForm, $rec_fld, $cOption)
    {
        if (isset($objForm->cal)) {
            $cFrm_name = "{$this->pre}cal_" . $rec_fld;
            $cId_name = "{$this->pre}id_cal_" . $rec_fld;
            $cValue = $this->arrCals["$rec_fld"];
        } else {
            $cFrm_name = "{$this->pre}frm_" . (is_object($rec_fld) ? $rec_fld->name : $rec_fld);
            $cId_name = "{$this->pre}id_" . (is_object($rec_fld) ? $rec_fld->name : $rec_fld);
            $cValue = is_object($rec_fld) ? $rec_fld->value : "";
        }

        $cAttrib = isset($objForm->attrib) ? " $objForm->attrib" : "";
        $retStr = "";
        if (!preg_match_all("/\s*(.+?)\s*=\s*(.+?)(,|$)/", $objForm->values, $arr_match, PREG_SET_ORDER)) {
            return false;
        }
        foreach ($arr_match as $match) {
            $rValue = trim($match[1]);
            $rDispl = trim($match[2]);
            $cCheck = $rValue == $cValue ? " checked" : "";
            $retStr .= "<input class='rad'$cAttrib type='radio' name='$cFrm_name' id='$cId_name' onKeyDown='sonra(this,event)' value='$rValue'$cCheck$cOption/><label class='lbl'>$rDispl</label> ";
        }
        return $retStr;
    }

    function ilisWrap($action, $filt)
    {
        if ($this->islem == "new" || $this->islem == "src" || $this->islem == "snl" || $this->islem == "cpy") {
            return;
        }

        if (isset($this->arrIlis[$action])) {
            $objIlis = $this->arrIlis[$action];
        } else {
            $strClass = "class_ilis";
            if (class_exists("class_ilis_$action")) {
                $strClass = "class_ilis_$action";
            }
            $objIlis = new $strClass($this->appLink, $action, $this->qry, $filt);
            if (is_object($objIlis->senaryo)) {
                $objIlis->edit = $this->edit;
                $objIlis->createIlis();
            }
            $this->arrIlis[$action] = $objIlis;
        }
        if (is_object($objIlis->senaryo)) {
            $objIlis->ilisShow();
        }
    }

    function detayWrap($name)
    {
        if ($this->islem == "new" || $this->islem == "src" || $this->islem == "snl" || $this->islem == "cpy") {
            return;
        }
        if (!isset($this->arrDetay[$name])) {
            return;
        }
        $this->arrDetay[$name]->formDetay();
    }

    function createDetay($objForm, $QRY, $det = true)
    {
        global $REAL_P;
        $strClass = "class_detay";
        if (file_exists("$REAL_P/_form/class_$objForm->action.php")) {
            $strClass = "class_$objForm->action";
        }
        include_once("$REAL_P/_form/$strClass.php");
        $oDet = new $strClass($this->appLink, $objForm->action, $objForm->name, $QRY);
        if (is_object($oDet->senaryo)) {
            if ($det) {
                $oDet->create_Det();
            }
            $this->arrDetay[$objForm->name] = $oDet;
            return $oDet;
        }
        return null;
    }

    function createItic($islem, $id)
    {
        $ticSen = "sen_{$this->senaryo->id}_{$islem}_$id";
        $ticKey = time() . "_" . rand();
        $_SESSION[$ticSen] = $ticKey;
        $itic = "$ticSen:$ticKey";
        return $itic;
    }

    function dropItic($itic)
    {
        if (!preg_match("/(sen_.+):(\d+_\d+)/", $itic, $match)) {
            return false;
        }
        $ticSen = $match[1];
        if (isset($_SESSION[$ticSen])) {
            unset($_SESSION[$ticSen]);
        }
    }

    function existsItic($islem, $itic)
    {
        if (!preg_match("/(sen_{$this->senaryo->id}_{$islem}_-?\d+):(\d+_\d+)/", $itic, $match)) {
            return false;
        }
        $ticSen = $match[1];
        $ticKey = $match[2];
        if (isset($_SESSION[$ticSen]) && $_SESSION[$ticSen] == $ticKey) {
            return true;
        }
        return false;
    }


    function createTic($islem)
    {
        $tic = "sen_{$this->senaryo->id}:" . time() . "_" . rand();
        $_SESSION[$tic] = $islem;
        return $tic;
    }

    function dropTic($tic, $id = null)
    {
        if (isset($_SESSION[$tic])) {
            unset($_SESSION[$tic]);
        }
        if (!empty($id)) {
            $_SESSION["sen_id_{$this->senaryo->id}"] = $id;
        }
    }

    function existsTic($islem, $aTic)
    {
        $senTic = (method_exists($this, "createIlis") ? "snl_" : "sen_") . $this->senaryo->id;
        if (!preg_match("/((snl|sen)_-?\d+):((\d+_\d+))/", $aTic, $match)) {
            return false;
        }
        $ticSen = $match[1];
        $ticKey = $match[3];
        if ($senTic != $ticSen) {
            return false;
        }
        $tic = "$senTic:$ticKey";
        if (isset($_SESSION[$tic]) && $_SESSION[$tic] == $islem) {
            return true;
        }
        return false;
    }

    function foundRec()
    {
        if ($this->qry->reccount) {
            return true;
        }

        $this->strMessage = "Record is not found";
        $str = "<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"" .
            "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
        $this->usrMessage($str);
        return false;
    }

    function foundKey()
    {
        if ($this->qry->keyQry) {
            return true;
        }

        $this->strMessage = "Key field is not set";
        $str = "<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"" .
            "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
        $this->usrMessage($str);
        return false;
    }

    function bindTicVals()
    {
        foreach ($this->arrTics as $name => $value) {
            $tic_name = "tic_$name";
            if (isset($_POST[$tic_name])) {
                $this->arrTics[$name] = $_POST[$tic_name];
            }
        }
    }

    function setTic()
    {
        $tic = isset($_GET["tic"]) ? $_GET["tic"] : "";
        $tic = empty($tic) ? null : $tic;
        if (preg_match("/((snl|sen)_\d+):((\d+_\d+))/", $tic, $match)) {
            $this->ticSen = $match[1];
            $this->ticKey = $match[3];
            if (isset($_SESSION[$this->ticSen], $_SESSION[$this->ticSen][$this->ticKey])) {
                return true;
            }
        }
        return false;
    }

    function popTicketArr()
    {
        if (isset($_SESSION[$this->ticSen], $_SESSION[$this->ticSen][$this->ticKey])) {
            $this->arrTics = $_SESSION[$this->ticSen][$this->ticKey];
            unset($_SESSION[$this->ticSen][$this->ticKey]);
            return true;
        }
        return false;
    }

    function putTicketArr($arrTic)
    {
        $senTic = (method_exists($this, "createIlis") ? "snl_" : "sen_") . $this->senaryo->id;
        $keyTic = time() . "_" . rand();
        $_SESSION[$senTic][$keyTic] = $arrTic;
        return "$senTic:$keyTic";
    }

    function createTicket($nIndex)
    {
        $parTicket = $this->arrTicket[$nIndex];
        $arrTic = array();

        if (preg_match_all("/\s*?(\w+)\s*=\s*((\\\$)?(kume_exp\(.+\)|table_exp\(.+\)|field_value\(.+\)|[\w-%]+)(\.(\w+))?)\s*($|\r|\n|,)/U", $parTicket, $arr_match, PREG_SET_ORDER)) {
            foreach ($arr_match as $match) {
                if (substr($match[4], 0, 12) == "field_value" || substr($match[4], 0, 9) == "kume_exp" || substr($match[4], 0, 9) == "table_exp") {
                    $str_eval = "\$exp=\$this->$match[4];";
                    eval($str_eval);
                    $val = $exp;
                } elseif ($match[3] == "$") {
                    $val = $this->objVal($match[4], $match[6]);
                } elseif (($def = $this->qry->fieldByName($match[2]))) {
                    $val = $def->value;
                } else {
                    $strVal = $match[2];
                    if (preg_match_all("/%(\w+)(\W|$)/U", $strVal, $flds, PREG_SET_ORDER)) {
                        foreach ($flds as $name) {
                            if ($def = $this->qry->fieldByName($name[1])) {
                                $strVal = str_replace("%$name[1]", $def->value, $strVal);
                            }
                        }
                    }
                    $val = $strVal;
                }
                $arrTic[$match[1]] = is_null($val) ? $match[2] : $val;
            }
        }
        $senTic = (method_exists($this, "createIlis") ? "snl_" : "sen_") . $this->senaryo->id;
        $keyTic = time() . "_" . rand();
        $_SESSION[$senTic][$keyTic] = $arrTic;
        return "$senTic:$keyTic";
    }

######################################################################################
######################################################################################
    function repeatField($QQ, $objForm)
    {
        global $oUser, $cKare;

        $cAttrib = isset($objForm->attrib) ? " $objForm->attrib" : "";
        $cTChar = "";
        if ($objForm->type == "/") {
            $span = empty($objForm->span) ? "" : " colspan=$objForm->span";
            return "<tr><td$cAttrib$span>";
        }
        if ($objForm->type == "+") {
            $span = empty($objForm->span) ? "" : " colspan=$objForm->span";
            $retStr = "</td></tr>\n<tr><td$cAttrib$span>";
            return $retStr;
        }
        if ($objForm->type == "C") {
            $span = empty($objForm->span) ? "" : " colspan=$objForm->span";
            return "</td><td$cAttrib$span>";
        }
        if ($objForm->type == "B") {
            $span = empty($objForm->span) ? 1 : $objForm->span * 1;
            return str_repeat("&nbsp;", $span);
        }
        if ($objForm->type == "-") {
            $w = isset($objForm->w) ? "{$objForm->w}px" : "90%";
            $h = isset($objForm->h) ? "{$objForm->h}px" : "1px";
            return "<div style='height:0; margin:5px 0 5px 0; border-top:$h solid #CCC; width:$w;'></div>";
        }

        if ($objForm->type == "BR") {
            return "<br>";
        }
        if ($objForm->type == "FF") {
            return "<form$cAttrib>\n";
        }
        if ($objForm->type == "TT") {
            return "<table$cAttrib>\n";
        }
        if ($objForm->type == "RR") {
            return "<tr$cAttrib>";
        }
        if ($objForm->type == "DD") {
            $span = empty($objForm->span) ? "" : " colspan=$objForm->span";
            return "<td$cAttrib$span>";
        }
        if ($objForm->type == "F/") {
            return "</form>\n";
        }
        if ($objForm->type == "T/") {
            return "</table>\n";
        }
        if ($objForm->type == "R/") {
            return "</tr>\n";
        }
        if ($objForm->type == "D/") {
            return "</td>";
        }

        $cOption = "";
        $cValobj = isset($objForm->value) ? $objForm->value : "";

        if (isset($objForm->read) || !$this->edit) {
            $cOption .= " readonly=1";
        }
        if (isset($objForm->req)) {
            $cOption .= " req='$objForm->req' required";
        }
        if (isset($objForm->msg)) {
            $cOption .= " msg='$objForm->msg'";
        }
        if (isset($objForm->notab)) {
            $cOption .= " tabindex=-1";
        }
        if (isset($objForm->place)) {
            $cOption .= " placeholder='$objForm->place'";
        }
        if (isset($objForm->hide)) {
            $cOption .= " type='hidden'";
        }
        if (isset($objForm->osize)) {
            $cOption .= " style='width:{$objForm->osize}px;'";
        }
        if (isset($objForm->style)) {
            $cOption .= " style='$objForm->style'";
        }

        if (strpos(",DATE,HOUR,TXT,TID,HID,EDT,YXT,CHK,RAD,DBL,IMG,PIC,RES,XMG,FILE,DAT,", "$objForm->type,")) {
            if (isset($objForm->tic) || isset($objForm->cal)) {
                if (isset($objForm->tic)) {
                    $strArr = "arrTics";
                    $strKey = "tic";
                } else {
                    $strArr = "arrCals";
                    $strKey = "cal";
                }
                $cFrm_name = "{\$this->pre}{$strKey}_$objForm->name";
                $cId_name = "{\$this->pre}id_{$strKey}_$objForm->name";
                $cFrm2_name = "{\$this->pre}{$strKey}2_$objForm->name";
                $cId2_name = "{\$this->pre}id_{$strKey}2_$objForm->name";
                $cValobj = isset($this->{$strArr}[$objForm->name]) ? "{\$this->{$strArr}['$objForm->name']}" : "";
                $rec_fld = $objForm->name;
            } else {
                $cFrm_name = "{\$this->pre}frm_$objForm->name";
                $cId_name = "{\$this->pre}id_$objForm->name";
                $cId2_name = "{\$this->pre}id2_$objForm->name";
                $cFrm2_name = "{\$this->pre}frm2_$objForm->name";
                if (is_null($rec_fld = $QQ->fieldByName($objForm->name))) {
                    $rec_fld = $objForm->name;
                } else {
                    $fld_name = "fld_$rec_fld->name";
                    $rec_name = "rec_$rec_fld->name";
                    if (isset($objForm->tchar)) {
                        $cTChar = " tchar='$objForm->tchar'";
                    } else {
                        $cTChar = " tchar='$rec_fld->char'";
                    }
                    $cValobj = $this->islem == "src" && !empty($rec_fld->filter) ? "{\$QQ->$fld_name->filter}" : "{\$QQ->$rec_name}";
                    if (isset($rec_fld->read) && !isset($objForm->read)) {
                        $cOption .= " readonly='1'";
                    }
                    if (isset($rec_fld->req) && !isset($objForm->req)) {
                        $cOption .= " req required";
                    }
                }
            }
            $cTitle = "$cFrm_name" . (isset($objForm->title) ? ": $objForm->title" : "");
        }

        if ($objForm->type == "") {
            ;
        } elseif ($objForm->type == "CHK") {
            $cOption .= "\",(\"$cValobj\"=='1'?'checked':''),\"";
            return "<input name='$cFrm_name' type='hidden' id='$cId_name' value='$cValobj'/><input class='chk' name='chk_$cFrm_name' type='checkbox' title='$cTitle' onKeyDown='sonra(this,event)' onClick='$cId_name.value=(this.checked?1:0)'$cOption/>";
        } elseif ($objForm->type == "PRN") {
            $prn_id = "this.parentNode";
            if (isset($objForm->id)) {
                $prn_id = $objForm->id;
            }
            return "<label class='lnk'$cAttrib onClick='PrintThis($prn_id)'>$objForm->caption</label>";
        } elseif ($objForm->type == "LNK") {  // && !$this->mod
            if (isset($objForm->isl) && !strpos(" ,$objForm->isl", ",$this->islem")) {
                return "";
            }
            if (isset($objForm->edt) && !$this->edit) {
                return "";
            }
            if (isset($objForm->shw) && !$this->getShowFilt($this->qry, $objForm->shw)) {
                return "";
            }

            $cClick = "openLink(this)";
            $cHref = "";
            if (isset($objForm->islem)) {
                if ($this->mod) {
                    return "";
                }
                switch ($objForm->islem) {
                    case "src":
                        if (strpbrk($this->senaryo->options, 'S')) {
                            $cHref = "{$this->senaryo->action}&islem=$objForm->islem";
                        }
                        break;
                    case "new":
                        if (strpbrk($this->senaryo->options, 'I')) {
                            $cHref = "{$this->senaryo->action}&islem=$objForm->islem";
                        }
                        break;
                    case "edt":
                        if (strpbrk($this->senaryo->options, 'U')) {
                            $cHref = "{$this->senaryo->action}&islem=$objForm->islem&id={\$QQ->keyRec}";
                        }
                        break;
                    case "cpy":
                        if (strpbrk($this->senaryo->options, 'I')) {
                            $cHref = "{$this->senaryo->action}&islem=$objForm->islem&id={\$QQ->keyRec}";
                        }
                        $cClick = "copyLink(this)";
                        break;
                    case "del":
                        if (strpbrk($this->senaryo->options, 'D')) {
                            $cHref = "{$this->senaryo->action}&islem=$objForm->islem&id={\$QQ->keyRec}";
                        }
                        $cClick = "deleteLink(this)";
                        break;
                    case "sen":
                        if ($oUser->admin == 1) {
                            $cHref = "form_sayfa&islem=edt&id={$this->senaryo->id}";
                        }
                        break;
                    default:
                        $cHref = "{$this->senaryo->action}&islem=$objForm->islem";
                }
                if (empty($cHref)) {
                    return "";
                }
                $cHref = "$cKare <label class='lnk'$cAttrib onClick='$cClick' url='?$cHref'>$objForm->caption</label> &nbsp; ";
                if (!isset($objForm->iif)) {
                    return $cHref;
                } else {
                    return "\",(($objForm->iif)?\"$cHref\":''),\"";
                }
            }

            $cHref = isset($objForm->href) ? $objForm->href : "";
            if (preg_match_all("/%(\w+)(\.(\w+))?(\W|$)/", $cHref, $arr_match, PREG_SET_ORDER)) {
                foreach ($arr_match as $match) {
                    if (empty($match[2])) {
                        if (isset($QQ->{"fld_$match[1]"})) {
                            $cHref = str_replace("%$match[1]", "\$QQ->rec_$match[1]", $cHref);
                        }
                    } elseif (!is_null($val = $this->objVal($match[1], $match[3]))) {
                        $cHref = str_replace("%$match[1]$match[2]", $val, $cHref);
                    }
                }
            }
            $cCapt = isset($objForm->caption) ? $objForm->caption : "[ ]";
            if (preg_match_all("/%(\w+)(\.(\w+))?(\W|$)/", $cCapt, $arr_match, PREG_SET_ORDER)) {
                foreach ($arr_match as $match) {
                    if (empty($match[2])) {
                        if (isset($QQ->{"fld_$match[1]"})) {
                            $cCapt = str_replace("%$match[1]", "\$QQ->rec_$match[1]", $cCapt);
                        }
                    } elseif (!is_null($val = $this->objVal($match[1], $match[3]))) {
                        $cCapt = str_replace("%$match[1]$match[2]", $val, $cCapt);
                    }
                }
            }
            if (isset($objForm->itic)) {
                $cItic = "\",\$this->createItic('$objForm->itic',\$QQ->rec_id),\"";
                $cHref = str_replace('$ITIC', $cItic, $cHref);
            }
            if (substr($cHref, 0, 3) == "<a " || substr($cHref, 0, 7) == "<label ") {
                $cHref = str_replace('$CAP', $cCapt, $cHref);
                if (!isset($objForm->iif)) {
                    return $cHref;
                } else {
                    return "\",(($objForm->iif)?\"$cHref\":''),\"";
                }
            }
            $cTitle = isset($objForm->title) ? $objForm->title : "";
            $cClick = (isset($objForm->open) && strpos(",open,modal,blank,delete,moddel,iliski,", "$objForm->open,") ? $objForm->open . "Link(this)" : "openLink(this)");
            $cHref = (isset($objForm->kare) ? "$cKare " : "") . "<label $cAttrib class='lnk' onClick='$cClick' url='?$cHref' title='$cTitle'>$cCapt</label>";
            if (!isset($objForm->iif)) {
                return $cHref;
            } else {
                return "\",(($objForm->iif)?\"$cHref\":''),\"";
            }
        } elseif ($objForm->type == "PRA") {
            if (isset($objForm->isl) && !strpos(" ,$objForm->isl", ",$this->islem")) {
                return "";
            }
            if (isset($objForm->edt) && !$this->edit) {
                return "";
            }
            if (isset($objForm->shw) && !$this->getShowFilt($this->qry, $objForm->shw)) {
                return "";
            }
            if (!isset($objForm->islem) || $objForm->islem != "tam") {
                $objForm->islem = "prm";
            }
            $parget = isset($objForm->parget) ? "&$objForm->parget" : "";
            $cClick = (isset($objForm->open) && strpos(",open,modal,blank,delete,moddel,iliski,", "$objForm->open,") ? $objForm->open . "Link(this)" : "modalLink(this)");

            $cHref = "$objForm->action&mod=&islem=$objForm->islem$parget&sen=" . (isset($objForm->m_pg) ? $objForm->m_pg : $this->senaryo->id) . "&id={\$QQ->keyRec}";
            $retStr = "<label class='lnk' onClick='$cClick' url='?$cHref'>$objForm->caption</label>";

            if (preg_match_all("/%(((\w+)\.)?(\w+))(\W|$)/U", $retStr, $arr_match, PREG_SET_ORDER)) {
                foreach ($arr_match as $match) {
                    if (empty($match[3])) {
                        if (isset($QQ->{"fld_$match[4]"})) {
                            $retStr = str_replace("%$match[4]", "{\$QQ->rec_$match[4]}", $retStr);
                        }
                    } elseif (!is_null($val = $this->objVal($match[3], $match[4]))) {
                        $retStr = str_replace("%$match[1]", $val, $retStr);
                    }
                }
            }
            if (!isset($objForm->iif)) {
                return $retStr;
            }
            return "\",(($objForm->iif)?\"$retStr\":''),\"";
        } elseif ($objForm->type == "PIC") {
            if (is_object($rec_fld)) {
                if (isset($objForm->id)) {
                    $strId = $this->buildEval($this->qry, $objForm->id, "QQ");
                } else {
                    $strId = "\$QQ->rec_$rec_fld->name";
                }
                $retStr = "\",((empty(\$QQ->rec_$rec_fld->name)||\$QQ->rec_$rec_fld->name==-1) ? '.' : \"<img$cAttrib class='frm' src='?loadpic&id=$strId' id='$cId_name'/>\"),\"";
            } else {
                if (isset($objForm->id)) {
                    $strId = $this->buildEval($this->qry, $objForm->id, "QQ");
                } else {
                    $strId = $cValobj;
                }
                $retStr = "<img$cAttrib class='frm' src='?loadpic&id=$strId' id='$cId_name'/>";
            }
            if (isset($objForm->ekle)) {
                if (!isset($objForm->caption)) {
                    $objForm->caption = "Resim ekle";
                }
                $retStr .= "<label class='lnk' onClick='modalLink(this)' url='?pratik_resimekle&mod=&islem=prm&id={\$QQ->rec_id}&pic=$cId_name'>$objForm->caption</label>";
            }
            return $retStr;
        } elseif ($objForm->type == "RES") {
            if (is_object($rec_fld)) {
                if (isset($objForm->id)) {
                    $strId = $this->buildEval($this->qry, $objForm->id, "QQ");
                } else {
                    $strId = "\$QQ->rec_$rec_fld->name";
                }
                $retStr = "\",(empty(\$QQ->rec_$rec_fld->name) ? '.' : \"<img$cAttrib class='frm' src='?loadres&exp=$strId' id='$cId_name'/>\"),\"";
            } else {
                if (isset($objForm->id)) {
                    $strId = $this->buildEval($this->qry, $objForm->id, "QQ");
                } else {
                    $strId = $cValobj;
                }
                $retStr = "<img$cAttrib class='frm' src='?loadres&id=$strId' id='$cId_name'/>";
            }
            return $retStr;
        } elseif ($objForm->type == "IMG") {
            $strSrc = $this->buildEval($this->qry, $objForm->src, "QQ");
            return "<img$cAttrib src='$strSrc' id='$cId_name'/>";
        } elseif ($objForm->type == "XMG") {
            $cId_name = $objForm->id;
            return "<script>" .
            "   $cId_name.src='?loadpic&id=" . (is_object($rec_fld) ? "{\$QQ->rec_$rec_fld->name}" : $cValobj) . "';" .
            "   $cId_name.style.display='block'" .
            "</script>";
        } elseif ($objForm->type == "RAD") {
            $nIndex = count($this->arrObj);
            $this->arrObj[$nIndex] = $this->radioField($QQ, $objForm, $rec_fld, $cOption);
            return "\",\$this->arrObj[$nIndex],\"";
        } elseif ($objForm->type == "FBL") {
            return "<label class='fbl'$cAttrib$cOption>$objForm->caption</label>";
        } elseif ($objForm->type == "SBL") {
            return "<label class='sbl'$cAttrib$cOption>$objForm->caption</label>";
        } elseif ($objForm->type == "LBL") {
            $cOption .= isset($objForm->title) ? " title='$objForm->title'" : "";
            return "<label class='lbl'$cAttrib$cOption>$objForm->caption</label>";
        } elseif ($objForm->type == "TIP") {
            return "<label class='tip'$cAttrib$cOption>$objForm->caption</label>";
        } elseif ($objForm->type == "LXT") {
            return "<label class='red'$cAttrib$cOption>$objForm->caption</label>";
        } elseif ($objForm->type == "DBL") {
            return "<label class='dbl'$cAttrib$cOption>" . (isset($objForm->brk) ? ("\",preg_replace('/\r\n|\n|\r/','<br>',trim(\"$cValobj\")),\"") : "$cValobj") . "</label>";
        } elseif ($objForm->type == "CBL") {
            return "$objForm->caption";
        } elseif ($objForm->type == "DAT") {
            return isset($objForm->brk) ? ("\".preg_replace('/\r\n|\n|\r/','<br>',trim(\"$cValobj\")).\"") : "$cValobj";
        } elseif ($objForm->type == "HTM") {
            return $cAttrib;
        } elseif ($objForm->type == "DATE") {
            $retStr = "";
            $retStr .= "<input class='date' name='$cFrm_name' id='$cId_name'$cTChar title='$cTitle' onKeyDown='sonra(this,event)' value='$cValobj'$cOption/>";
            $retStr .= "<input class='cmb-tus' id='tus_$cId_name' style='visibility:hidden' type='button' onKeyDown='sonra(this,event)' tabindex='-1' value=''/>";
            $cDil = empty($oUser->dilse) ? "en" : strtolower($oUser->dilse);
            if (!strpos("-,tr,de,en,", ",$cDil,")) {
                $cDil = "en";
            }
            //$retStr.= "<script>";
            //$retStr.="$cId_name.addEventListener('keydown',function(event){if(event.keyIdentifier=='Down'||event.keyIdentifier=='Up'){event.preventDefault()}},false);";
            //$retStr.= "</script>\n";
            //return $retStr;
            $retStr .= "<script>";
            //$retStr.= "if(!/chrome/i.test(navigator.userAgent)){";
            $retStr .= "   tus_$cId_name.style.visibility='visible'; $cId_name.style.width=75;";
            $retStr .= "   $cId_name.cal=new dhtmlxCalendarObject($cId_name, tus_$cId_name);";
            $retStr .= "   $cId_name.cal.loadUserLanguage('$cDil');";

            if (is_object($rec_fld)) {
                $cVal = $rec_fld->value;
            } else {
                $cVal = $cValobj;
            }
            if (preg_match("/\s*(\d{1,2})([.\/-])(\d{1,2})([.\/-])(\d{4})\s*/", $cVal, $mm)) {
                $retStr .= "   $cId_name.cal.setDateFormat('%d$mm[2]%m$mm[4]%Y');";
            } elseif (preg_match("/\s*(\d{4})([.\/-])(\d{1,2})([.\/-])(\d{1,2})\s*/", $cVal, $mm)) {
                $retStr .= "   $cId_name.cal.setDateFormat('%Y$mm[2]%m$mm[4]%d');";
            } else {
                $retStr .= "   $cId_name.cal.setDateFormat('%Y-%m-%d');";
            }

            $retStr .= "   $cId_name.cal.setHeader(true, true, 'MCTX');";
            //$retStr.= "}";
            $retStr .= "</script>";
            if ($this->islem == "src") {
                $retStr .= " &nbsp; <input class='date' name='$cFrm2_name' id='$cId2_name' onKeyDown='sonra(this,event)' value='$cValobj'$cOption/>";
                $retStr .= "<input class='cmb-tus' id='tus_$cId2_name' style='visibility:hidden' type='button' onKeyDown='sonra(this,event)' tabindex='-1' value=''/>";
                $retStr .= "<script>";
                //$retStr.= "if(!/chrome/i.test(navigator.userAgent)){";
                $retStr .= "   tus_$cId2_name.style.visibility='visible'; $cId2_name.style.width=75;";
                $retStr .= "   $cId2_name.cal=new dhtmlxCalendarObject($cId2_name, tus_$cId2_name);";
                $retStr .= "   $cId2_name.cal.loadUserLanguage('$cDil');";
                $retStr .= "   $cId2_name.cal.setDateFormat('%Y-%m-%d');";
                $retStr .= "   $cId2_name.cal.setHeader(true, true, 'MCTX');";
                //$retStr.= "}";
                $retStr .= "</script>";
            }
            return $retStr;
        } elseif ($objForm->type == "HOUR") {
            return "<input class='hour' name='$cFrm_name' id='$cId_name'$cTChar title='$cTitle' onKeyDown='sonra(this,event)' value='$cValobj'$cOption/>";
        } elseif ($objForm->type == "TXT") {
            return "<input class='txt' name='$cFrm_name' id='$cId_name'$cTChar onKeyDown='sonra(this,event)' title='$cTitle' value='$cValobj'$cOption/>";
        } elseif ($objForm->type == "TID") {
            return "<input class='txt-id'$cAttrib name='$cFrm_name' id='$cId_name' title='$cTitle' onKeyDown='sonra(this,event)' value='$cValobj'$cOption readonly=1 tabindex=-1/>";
        } elseif ($objForm->type == "HID") {
            return "<input type='hidden' name='$cFrm_name' id='$cId_name' value='$cValobj'/>";
        } elseif ($objForm->type == "EDT") {
            $wrap = " wrap=off";
            if (strpos($cAttrib, "wrap=")) {
                $wrap = "";
            }
            return "<textarea rows='6' cols='41'$wrap$cAttrib$cOption name='$cFrm_name' id='$cId_name' onKeyDown='sonra(this,event)' title='$cTitle'>$cValobj</textarea>";
        } elseif ($objForm->type == "YXT") {
            $nIndex = count($this->arrObj);
            $this->arrObj[$nIndex] = $this->yildizField($QQ, $objForm, $rec_fld, $cOption);
            return "\",\$this->arrObj[$nIndex],\"";
        } elseif ($objForm->type == "SBM" && $this->edit) {
            return "<input class='tus' type='button' name='tam_$objForm->caption' onKeyDown='sonra(this,event)' value='$objForm->caption' onclick='return ValidateForm(this.form);'/>";
        } elseif ($objForm->type == "CNC" && $this->edit) {
            return "<input class='tus' type='reset' name='ipt_$objForm->caption' onKeyDown='sonra(this,event)'" . ($this->mod ? " onClick='window.close()'" : "") . " value='$objForm->caption'/>";
        } elseif ($objForm->type == "SNL" || $objForm->type == "ILIS" || $objForm->type == "REL") {
            $cFilt = isset($objForm->filt) ? $objForm->filt : "";
            if (isset($objForm->iif)) {
                return "\",(($objForm->iif)?\$this->ilisWrap('$objForm->action','$cFilt'):''),\"";
            } else {
                return "\",\$this->ilisWrap('$objForm->action','$cFilt'),\"";
            }
        } elseif ($objForm->type == "DETAY") {
            $this->createDetay($objForm, $this->qry);
            return "\",\$this->detayWrap('$objForm->name'),\"";
        } elseif ($objForm->type == "GET") {
            return (isset($_GET["$objForm->name"]) ? urldecode($_GET["$objForm->name"]) : "");
        } elseif ($objForm->type == "PRS..." && !$this->mod) {
            return "\",\$this->prasorLink(),\"";
        } elseif ($objForm->type == "PRS" && !$this->mod) {
            return "\",\$this->prasorLink(1),\"";
        } elseif ($objForm->type == "FILE") {
            $retStr = "";
            $retStr .= "<input class='file' type='file' name='$cFrm_name' id='$cId_name' onKeyDown='sonra(this,event)' title='dosya' value='$cValobj'$cOption/>";
            if (isset($objForm->id_exp)) {
                $cId_exp = $objForm->id_exp;
                $retStr .= "<script>$cId_name.onchange=function(){";
                $retStr .= "bugun=new Date(); ctar=bugun.getFullYear()+'.'+(bugun.getMonth()+1)+'.'+bugun.getDate();";
                $retStr .= "str1=$cId_exp.value;if($cId_exp.value.match(/^(.+)_(.+)_/))str1=RegExp.$1;$cId_exp.value=str1+'_'+$cId_name.value+'_'+ctar;}";
                $retStr .= "</script>";
            }
            return $retStr;
        } elseif ($objForm->type == "QUAD") {
            $retStr = "</td></tr>\n";
            $retStr .= "</table>\n";
            $retStr .= "</td>\n";
            $retStr .= "<td valign='top'>\n";
            $retStr .= "<table border='0' width='500'>\n";
            return $retStr;
        } elseif ($objForm->type == "SCRIPT") {
            $nIndex = count($this->arrObj);
            $this->arrObj[$nIndex] = "<script>$objForm->code</script>";
            return "\",\$this->arrObj[$nIndex],\"";
        } else {
            return false;
        }

        if (isset($objForm->iliski)) {
            return "<script>$cId_name.onclick=iliskiClick;</script>";
        }
        return "";
    }
######################################################################################
######################################################################################

    function defFormTemp($QQ)
    {
        $cFormStr = "#TT attrib=border='0' width='500'\n" .
            "#/ span=2 #type=LNK & caption=Listele & islem=lst #type=LNK & caption=Yeni Kay�t & islem=new #type=LNK & caption=Kopyala & islem=cpy #type=LNK & caption=Sayfa & islem=sen #type=LNK & caption=Kayd� Sil & islem=del\n" .
            "#+ span=2 #type=FBL & caption={$this->senaryo->exp}\n";
        foreach ($QQ->arrFields as $oFld) {
            $char = $QQ->type_char($oFld->type);
            if (strpos(",MWQ", $char)) {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name #C #type=EDT & name=$oFld->name\n";
            } elseif (strpos(",DT", $char)) {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name #C #type=DATE & name=$oFld->name\n";
            } elseif (strpos(",H", $char)) {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name #C #type=HOUR & name=$oFld->name\n";
            } else {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name #C #type=TXT & name=$oFld->name\n";
            }
        }
        $cFormStr .= "#+ #C #type=SBM & caption=Kaydet #B #type=CNC & caption=Iptal\n#D/#R/\n#T/";
        return $cFormStr;
    }

    function defFindTemp($QQ)
    {
        $cFormStr = "#TT attrib=border='0' width='500'\n" .
            "#/ span=2 #type=LNK & caption=Listele & islem=lst #type=LNK & caption=Yeni Kay�t & islem=new #type=LNK & caption=Sayfa & islem=sen\n" .
            "#+ span=2 #type=FBL & caption={$this->senaryo->exp}\n" .
            "#+ #C #type=SBM & name=ara1 & caption=Ara #B #type=CNC & caption=Temizle\n";
        foreach ($QQ->arrFields as $oFld) {
            $char = $QQ->type_char($oFld->type);
            if (strpos(",MW", $char)) {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name -Edt-$oFld->type $oFld->char #C #type=TXT & name=$oFld->name\n";
            } elseif (strpos(",DT", $char)) {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name -Date-$oFld->type $oFld->char #C #type=DATE & name=$oFld->name\n";
            } elseif (strpos(",NI", $char)) {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name -Num-$oFld->type $oFld->char #C #type=TXT & name=$oFld->name\n";
            } elseif (strpos(",H", $char)) {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name -Hour-$oFld->type $oFld->char #C #type=HOUR & name=$oFld->name\n";
            } else {
                $cFormStr .= "#+ #type=LBL & caption=$oFld->name -Txt-$oFld->type $oFld->char #C #type=TXT & name=$oFld->name\n";
            }
        }
        $cFormStr .= "#+ #C #type=SBM & name=ara1 & caption=Ara #B #type=CNC & caption=Temizle\n#D/#R/\n#T/";
        return $cFormStr;
    }

    function defListTemp($QQ, $cShowFlds = "", $aLink = null)
    {
        $arrListFld = array();
        if (is_null($aLink)) {
            $aLink = array();
        }
        if (!empty($cShowFlds) && preg_match_all("/\s*(\w+)(:pic|:snl|:lnk|:del|:brk)?\s*(,|$)/U", $cShowFlds, $arr_match, PREG_SET_ORDER)) {
            foreach ($arr_match as $match) {
                if ($match[2] == ":lnk" || $match[2] == ":del" || isset($aLink[$match[1]])) {
                    $oFld = (object)array("name" => $match[1], "lnk" => $match[2], "href" => "");
                    if (isset($aLink[$match[1]])) {
                        $oFld->href = $aLink[$match[1]];
                    }
                } elseif ($match[2] == ":snl") {
                    $oFld = (object)array("name" => $match[1], "snl" => $match[1]);
                } elseif (is_null($oFld = $QQ->fieldByName($match[1]))) {
                    continue;
                }
                if ($match[2] == ":pic") {
                    $oFld->pic = $match[1];
                } elseif ($match[2] == ":brk") {
                    $oFld->brk = 1;
                }
                $arrListFld[] = $oFld;
            }
        }
        if (count($arrListFld) == 0) {
            $arrListFld =& $QQ->arrFields;
        }

        $cFormStr = "";
        $cHeader = "";
        foreach ($arrListFld as $oFld) {
            if (isset($oFld->lnk)) {
                $cFormStr .= "#C #type=LNK & caption=$oFld->name & href=$oFld->href" . ($oFld->lnk == ":del" ? " & open=delete" : "");
            } elseif (isset($aLink[$oFld->name])) {
                $cFormStr .= "#C #type=LNK & caption=$oFld->name & href=" . $aLink[$oFld->name] . "\n";
            } elseif (isset($oFld->snl)) {
                $cFormStr .= "#C #type=SNL & name=$oFld->name & action=$oFld->name\n";
            } elseif (isset($oFld->pic)) {
                $cFormStr .= "#C #type=PIC & name=$oFld->name & attrib=class='lst'\n";
            } else {
                $cFormStr .= "#C #type=DAT & name=$oFld->name" . (isset($oFld->brk) ? " & brk=1" : "") . "\n";
            }
            $cHeader .= "#C #type=CBL & caption=$oFld->name\n";
        }

        $cls2 = "";
        $renk = null;
        if (isset($QQ->fld_abc)) {
            $renk = $QQ->fld_abc;
        }
        if (isset($QQ->fld_tipi)) {
            if (!$renk) {
                $renk = $QQ->fld_tipi;
            } elseif ($QQ->fld_abc->order < $QQ->fld_tipi->order) {
                $renk = $QQ->fld_abc;
            } else {
                $renk = $QQ->fld_tipi;
            }
        }
        if ($renk) {
            $cls2 = " {$renk->name}_{\$renk->value}";
        }

        $cFormStr = "#type=LNK & caption=Listele & islem=lst #type=LNK & caption=Yeni Kay�t & islem=new #type=LNK & caption=Sayfa & islem=sen #type=PRS\n" .
            "#BR\n#TT attrib=class='tsample'\n" .
            "#type=HEADER\n" .
            "#RR attrib=class='d0'\n#DD" . substr($cHeader, 3) . "#D/\n#R/\n" .
            "#type=HEADER\n" .
            "#type=REPEAT\n" .
            "#RR attrib=class='d\$cls$cls2'\n#DD" . substr($cFormStr, 3) . "#D/\n#R/\n" .
            "#type=REPEAT\n" .
            "#T/\n";
        return $cFormStr;
    }

    function defGridTemp($QQ)
    {
        $cFormStr = "";
        $cHeader = "";
        foreach ($QQ->arrFields as $oFld) {
            $char = $QQ->type_char($oFld->type);
            if (strpos(",MW", $char)) {
                $cFormStr .= "#C #type=EDT & name=$oFld->name\n";
            } elseif (strpos(",DT", $char)) {
                $cFormStr .= "#C #type=DATE & name=$oFld->name\n";
            } elseif (strpos(",H", $char)) {
                $cFormStr .= "#C #type=HOUR & name=$oFld->name\n";
            } else {
                $cFormStr .= "#C #type=TXT & name=$oFld->name\n";
            }
        }

        $cls2 = "";
        $renk = null;
        if (isset($QQ->fld_abc)) {
            $renk = $QQ->fld_abc;
        }
        if (isset($QQ->fld_tipi)) {
            if (!$renk) {
                $renk = $QQ->fld_tipi;
            } elseif ($QQ->fld_abc->order < $QQ->fld_tipi->order) {
                $renk = $QQ->fld_abc;
            } else {
                $renk = $QQ->fld_tipi;
            }
        }
        if ($renk) {
            $cls2 = " {$renk->name}_{\$renk->value}";
        }

        $cFormStr = "#/ span=2 #type=LNK & caption=Listele & islem=lst #type=LNK & caption=Yeni Kay�t & islem=new #type=LNK & caption = Sayfa & islem=sen\n" .
            "#+ span=2 #type=FBL & caption={$this->senaryo->exp}\n" .
            "#type=REPEAT\n" .
            "#RR attrib=class='d\$cls$cls2'\n#DD " . substr($cFormStr, 3) . " #D/\n#R/\n" .
            "#type=REPEAT\n" .
            "#+ #C #type=SBM & caption=Kaydet #B #type=CNC & caption=Iptal";
        return $cFormStr;
    }

#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    function table_exp($cTable, $nId)
    {
        $cDil = strtoupper($this->dil);
        $cFrom = str_replace("_TR", "_$cDil", str_replace("_EN", "_$cDil", str_replace("_DE", "_$cDil", strtoupper($cTable))));
        $oDB = connect_datab($this->senaryo->datab);
        $strClass = "cls$oDB->cls";
        $qCCC = new $strClass($oDB->dblink, "select exp from $cFrom where $cFrom.id=?prm_id");
        $qCCC->prm_id = $nId;
        $qCCC->open();
        return $qCCC->rec_exp;
    }

    function kume_exp($nId)
    {
        $tabKume = "kume_$this->dil";
        $oDB = connect_datab($this->senaryo->datab);
        $strClass = "cls$oDB->cls";
        $qCCC = new $strClass($oDB->dblink, "select exp from asist$oDB->fro_s$tabKume kume where kume.id=?prm_id");
        $qCCC->prm_id = $nId;
        $qCCC->open();
        return $qCCC->rec_exp;
    }

    function sid_exp($cStand, $nSid)
    {
        $tabStandart = "standart_$this->dil";
        $qCCC = new clsApp($this->appLink, "select exp from asist.$tabStandart where stand=?prm_stand and sid=?prm_sid");
        $qCCC->prm_stand = $cStand;
        $qCCC->prm_sid = $nSid;
        $qCCC->open();
        return $qCCC->rec_exp;
    }

    function field_value($cTable, $nId, $cField)
    {
        $cDil = strtoupper($this->dil);
        $cFrom = str_replace("_TR", "_$cDil", str_replace("_EN", "_$cDil", str_replace("_DE", "_$cDil", strtoupper($cTable))));
        $oDB = connect_datab($this->senaryo->datab);
        $strClass = "cls$oDB->cls";
        $qCCC = new $strClass($oDB->dblink, "select $cField deger from asist$oDB->fro_s$cFrom where $cFrom.id=?prm_id");
        $qCCC->prm_id = $nId;
        $qCCC->open();
        return $qCCC->rec_deger;
    }

    // Kullan�c�ya a�t terc�h deger� girilmisse kullaniliyor, girilmemisse sirkete ait olan kullaniliyor
    // S�rkete ait olanda yoksa mevcut (default) kullaniyor...
    function tercih_deger($kisad, $user, $sirket, $cField = "deger")
    {
        $sqlStr = "select if(user=-1,  user,  abs(user))   user,
		                if(sirket=-1,sirket,abs(sirket)) sirket,
						tipi,$cField deger
				from asist.tercih_tr tercih
				where tercih.kisad=?prm_kisad
					and user in (-1, $user)
					and sirket in (-1, $sirket)
				order by 1 desc, 2 desc";
        $qCCC = new clsApp($this->appLink, $sqlStr);
        $qCCC->prm_kisad = $kisad;
        $qCCC->open();
        $val = "";
        switch ($qCCC->rec_tipi) {
            case "int":
                $val = is_numeric($qCCC->rec_deger) ? intval($qCCC->rec_deger) : 0;
                break;
            case "dec":
                $val = is_numeric($qCCC->rec_deger) ? floatval($qCCC->rec_deger) : 0.0;
                break;
            case "str":
            case "date":
            case "time":
            case "datetime":
            case "file":
            case "dir":
            case "email":
            case "web":
            default:
                $val = $qCCC->rec_deger;
                break;
        }
        return $val;
    }

    function ibareYoksaEkle($fld_exp, $iliski, $str_caption)
    {
        if (empty($str_caption)) {
            return;
        }
        $str_caption = mysqli_real_escape_string($this->appLink, $str_caption);
        $res = mysqli_query($this->appLink, "select id from asist.ibare where iliski='$iliski' and $fld_exp='$str_caption'");
        if (mysqli_num_rows($res) == 0) {
            $res = mysqli_query($this->appLink, "select id from asist.ibare where $fld_exp='$str_caption'");
        }
        if (mysqli_num_rows($res) == 0) {
            mysqli_query($this->appLink, "insert into asist.ibare (iliski,exp_tr,exp_de,exp_en) values ('$iliski','$str_caption','$str_caption','$str_caption')");
        }
        mysqli_free_result($res);
    }

    function ibareGuncelle($fld_exp, $iliski, $str_caption, $str_bind)
    {
        if (empty($str_caption) || empty($str_bind)) {
            return;
        }
        $str_caption = mysqli_real_escape_string($this->appLink, $str_caption);
        $str_bind = mysqli_real_escape_string($this->appLink, $str_bind);
        mysqli_query($this->appLink, "update asist.ibare set $fld_exp='$str_bind' where iliski='$iliski' and $fld_exp='$str_caption'");
    }

    function ibareKarsilikBul($fld_exp, $dil_fld, $iliski, $str_caption)
    {
        if ($fld_exp == $dil_fld || empty($str_caption)) {
            return $str_caption;
        }
        $str_res = "";
        $str_caption = mysqli_real_escape_string($this->appLink, $str_caption);
        $res = mysqli_query($this->appLink, "select id,$dil_fld from asist.ibare where iliski='$iliski' and $fld_exp='$str_caption' order by id desc");
        if (mysqli_num_rows($res) == 0) {
            $res = mysqli_query($this->appLink, "select id,$dil_fld from asist.ibare where $fld_exp='$str_caption' order by id desc");
        }
        if (mysqli_num_rows($res)) {
            $oRec = mysqli_fetch_object($res);
            $str_res = $oRec->$dil_fld;
        }
        mysqli_free_result($res);
        return $str_res;
    }

    function sozlukBul($org, $dil, $iliski, $exp)
    {
        if ($org == $dil || empty($exp)) {
            return $exp;
        }
        $str_res = "";
        $exp = mysqli_real_escape_string($this->appLink, $exp);
        $res = mysqli_query($this->appLink, "select id,$dil from asist.sozluk where iliski='$iliski' and $org='$exp' order by id desc");
        if (mysqli_num_rows($res) == 0) {
            $res = mysqli_query($this->appLink, "select id,$dil from asist.sozluk where $org='$exp' order by id desc");
        }
        if (mysqli_num_rows($res)) {
            $oRec = mysqli_fetch_object($res);
            $str_res = $oRec->$dil;
        }
        mysqli_free_result($res);
        return $str_res;
    }

    function sozlukEkle($dil, $iliski, $exp)
    {
        if (empty($exp)) {
            return;
        }
        $exp = mysqli_real_escape_string($this->appLink, $exp);
        $res = mysqli_query($this->appLink, "select id from asist.sozluk where iliski='$iliski' and $dil='$exp'");
        if (mysqli_num_rows($res) == 0) {
            global $arrDil;
            $flds = "";
            $flds_exp = "";
            foreach ($arrDil as $dil => $val) {
                $flds .= ",$dil";
                $flds_exp .= ",'$exp'";
            }
            mysqli_query($this->appLink, "insert into asist.sozluk (iliski$flds) values ('$iliski'$flds_exp)");
        }
        mysqli_free_result($res);
    }

    function sozlukGunc($dil, $iliski, $exp, $exp_new)
    {
        if (empty($exp) || empty($exp_new)) {
            return;
        }
        $exp = mysqli_real_escape_string($this->appLink, $exp);
        $exp_new = mysqli_real_escape_string($this->appLink, $exp_new);
        mysqli_query($this->appLink, "update asist.sozluk set $dil='$exp_new' where iliski='$iliski' and $dil='$exp'");
    }

#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    function getPars($strPars)
    {
        $pars = array();
        if (preg_match_all("/(.+)(;|\r|\n|$)/U", $strPars, $arr_pars, PREG_SET_ORDER)) {
            foreach ($arr_pars as $par) {
                if (!preg_match("/^\s*(\w+)\s*=(.+)$/", $par[1], $match)) {
                    continue;
                }
                $pars[$match[1]] = trim($match[2]);
            }
        }
    }

    function setWhere($QRY, $strFilter, $strOpt = "")
    {
        if (count($QRY->arrParams)) {
            $this->bindParams($QRY, $strFilter);
        }
        if (preg_match_all("/(.*?)(\/\/|;|\r|\n|$)/", $strFilter, $arr_filt, PREG_SET_ORDER)) {
            foreach ($arr_filt as $filt) {
                if (!preg_match("/^\s*([sSDUERPCB\?]+:)?((\w+)\.(\w+)|(\w+))\s*(==|!=|<>|<=|>=|>>|=|<|>|~)(.+)$/", $filt[1], $match)) {
                    continue;
                }
                $opt2 = preg_replace("/[R:\?\/]/", "", $match[1]);
                if (!empty($strOpt) && !empty($opt2) && !strpbrk($opt2, $strOpt)) {
                    continue;
                }
                $val = $this->buildValue($QRY, $match[7]);
                if (is_null($val)) {
                    continue;
                }

                $oWhr = null;
                if (!empty($match[5])) {
                    if (!($fld = $QRY->fieldByName($match[5]))) {
                        continue;
                    }
                    $fld->filter = $val;
                    $oWhr = (object)array("fld" => $fld,
                        "opt" => $match[1],
                        "opr" => $match[6],
                        "like" => null,
                        "name" => $fld->orgname,
                        "fromfld" => (empty($fld->from) ? "" : "$fld->from.") . $fld->orgname,
                        "value" => $val,
                        "val" => $match[7],
                        "orl" => $filt[2]);
                } elseif (isset($QRY->arrFroms[$match[3]])) {
                    $oWhr = (object)array("opt" => $match[1],
                        "opr" => $match[6],
                        "like" => null,
                        "name" => "$match[3].$match[4]",
                        "fromfld" => "$match[3].$match[4]",
                        "from" => $match[3],
                        "fname" => $match[4],
                        "value" => $val,
                        "val" => $match[7],
                        "orl" => $filt[2]);
                    //var_dump($oWhr);
                }
                if ($oWhr) {
                    if (strpos(" $match[1]", "R")) {
                        $QRY->arrWhere[$oWhr->fromfld] = $oWhr;
                    } elseif (isset($QRY->arrWhere[$oWhr->fromfld])) {
                        unset($QRY->arrWhere[$oWhr->fromfld]);
                        $QRY->arrWhere[] = $oWhr;
                    } else {
                        $QRY->arrWhere[] = $oWhr;
                    }
                }
            }
        }
    }

    function refWhere($QRY)
    {
        foreach ($QRY->arrWhere as $whr) {
            if (isset($whr->val)) {
                $whr->value = $this->buildValue($QRY, $whr->val);
                //echo "$whr->value<br>";
            }
        }
    }

    function setEdit($QRY, $strCond)
    {
        $strEdit = "";
        if (preg_match_all("/(.+)(;|\r|\n|$)/U", $strCond, $arr_cond, PREG_SET_ORDER)) {
            foreach ($arr_cond as $cond) {
                if (!preg_match("/^\s*([srISDUPER\?]+:)(\w+)\s*(==|!=|<>|<=|>=|=|<|>)(.+)$/", $cond[1], $match)) {
                    continue;
                }
                $oCon = (object)array();
                $oCon->value = $this->buildValue($QRY, $match[4]);
                if (is_null($oCon->value)) {
                    continue;
                }

                if (!strpbrk($match[1], "E") || !($fld = $QRY->fieldByName($match[2]))) {
                    continue;
                }
                $oCon->opr = $match[3];

                $this->buildOrnot($oCon);
                $edit = "";
                for ($ii = 1; $ii <= $oCon->cnt; $ii++) {
                    $val = $oCon->{"val$ii"};
                    $not = $oCon->{"not$ii"};
                    $edit .= " || " . ($not ? "!( " : "") . "\$QRY->rec_{$fld->name}" . ($oCon->opr == "=" ? "==" : $oCon->opr);
                    $edit .= is_numeric($val) ? $val : "'$val'";
                    $edit .= $not ? ")" : "";
                }
                $edit = substr($edit, 4);
                $strEdit .= " && ($edit)";
            }
        }
        $strEdit = substr($strEdit, 4);
        if (!empty($strEdit)) {
            eval("if($strEdit) \$this->edit=1; else \$this->edit=0;");
        }
    }

    function getShowFilt($QQ, $strCond)
    {
        $strShow = "";
        if (preg_match_all("/(.+)(;|\r|\n|$)/U", $strCond, $arr_cond, PREG_SET_ORDER)) {
            foreach ($arr_cond as $cond) {
                if (!preg_match("/^\s*(\w+)\s*(==|!=|<>|<=|>=|=|<|>)(.+)$/", $cond[1], $match)) {
                    continue;
                }
                $oCon = null;
                $oCon->value = $this->buildValue($QQ, $match[3]);
                if (is_null($oCon->value)) {
                    continue;
                }

                if (!($fld = $QQ->fieldByName($match[1]))) {
                    continue;
                }
                $oCon->opr = $match[2];

                $this->buildOrnot($oCon);
                $show = "";
                for ($ii = 1; $ii <= $oCon->cnt; $ii++) {
                    $val = $oCon->{"val$ii"};
                    $not = $oCon->{"not$ii"};
                    $show .= " || " . ($not ? "!( " : "") . "\$QQ->rec_{$fld->name}" . ($oCon->opr == "=" ? "==" : $oCon->opr);
                    $show .= is_numeric($val) ? $val : "'$val'";
                    $show .= $not ? ")" : "";
                }
                $show = substr($show, 4);
                $strShow .= " && ($show)";
            }
        }
        $strShow = substr($strShow, 4);
        $ret = empty($strShow) ? false : eval("return ($strShow);");
        return $ret;
    }

    function bindDefaults($QRY, $strDefaults, $strOpt = "")
    {
        if (preg_match_all("/(.+?)(;|\r|\n|$)/", $strDefaults, $arr_defs, PREG_SET_ORDER)) {
            foreach ($arr_defs as $defs) {
                if (!preg_match("/^\s*([rNIU]+:)?(\w+)\s*=(.+)$/", $defs[1], $match)) {
                    continue;
                }
                if (!empty($strOpt) && !empty($match[1]) && !strpbrk($match[1], $strOpt)) {
                    continue;
                }
                if (!($fld = $QRY->fieldByName($match[2]))) {
                    continue;
                }
                $val = $this->buildValue($QRY, $match[3]);
                if (is_null($val)) {
                    continue;
                }

                if (strpbrk($match[1], "r")) {
                    $fld->read = true;
                }
                $fld->value = $val;
            }
        }
    }

    function bindParams($QRY, $strParams)
    {
        if (preg_match_all("/(.+)(;|\r|\n|$)/U", $strParams, $arr_pars, PREG_SET_ORDER)) {
            foreach ($arr_pars as $pars) {
                if (!preg_match("/^\s*\?(\w+)\s*=(.+)$/", $pars[1], $match)) {
                    continue;
                }
                if (!($par = $QRY->paramByName($match[1]))) {
                    continue;
                }
                $val = $this->buildValue($QRY, $match[2]);
                //echo "$match[1] -- $par->char -- $par->value ",$val," <br>";
                if ($val == "{}") {
                    $val = "00:00";
                }
                if (empty($val) || is_null($val)) {
                    $type = $QRY->char_type($par->char);
                    $val = $QRY->empty_val($type);
                }
                $par->value = $val;
            }
        }
    }

    function bindFields($QRY, $strParams)
    {
        echo $strParams;
        if (preg_match_all("/(.+)(;|\r|\n|$)/U", $strParams, $arr_pars, PREG_SET_ORDER)) {
            foreach ($arr_pars as $pars) {
                if (!preg_match("/^\s*(\w+)\s*=(.+)$/", $pars[1], $match)) {
                    continue;
                }
                echo "$match[1]<br>";
                if (!($fld = $QRY->fieldByName($match[1]))) {
                    continue;
                }
                $val = $this->buildValue($QRY, $match[2]);
                $fld->value = $val;
                //echo "$match[1] -- $fld->char -- $fld->value<br>";
            }
        }
    }

    function buildOrnot($oWhr)
    {
        $cnt = 0;
        if (preg_match_all("/\s*(!(.+)|(.+))\s*(,|$)/U", $oWhr->value, $vals, PREG_SET_ORDER)) {
            foreach ($vals as $vm) {
                if (empty($vm[1])) {
                    continue;
                }
                $cnt++;
                $oWhr->{"val$cnt"} = "$vm[2]$vm[3]";
                $oWhr->{"not$cnt"} = empty($vm[3]);
            }
        }
        $oWhr->cnt = $cnt;
    }

    function buildValue($QRY, $strValue)
    {
        $retVal = trim($strValue);
        if (preg_match_all("/%(\w+)(\W|$)/U", $retVal, $flds, PREG_SET_ORDER)) {
            foreach ($flds as $name) {
                if ($QRY->main) {
                    $fld = $QRY->main->fieldByName($name[1]);
                } elseif ($QRY) {
                    $fld = $QRY->fieldByName($name[1]);
                } else {
                    $fld = null;
                }
                if ($fld) {
                    $retVal = str_replace("%$name[1]", $fld->value, $retVal);
                }
            }
        }
        if (preg_match_all("/((\\\$(\w+)[\\\.-](\w+))|(\\\$(\w+))|(\w+))(\W|$)/", $retVal, $flds, PREG_SET_ORDER)) {
            foreach ($flds as $name) {
                $val = null;
                if (!empty($name[2])) {
                    if ($name[3] == "main") {
                        if ($QRY->main && ($fld = $QRY->main->fieldByName($name[4]))) {
                            $val = $fld->value;
                        }
                    } else {
                        $val = $this->objVal($name[3], $name[4]);
                    }
                } elseif (!empty($name[5])) {
                    $val = $this->objVal($name[6]);
                } elseif (!empty($name[7]) && $retVal == $name[7]) {
                    if ($QRY->main) {
                        $fld = $QRY->main->fieldByName($name[7]);
                    } elseif ($QRY) {
                        $fld = $QRY->fieldByName($name[7]);
                    } else {
                        $fld = null;
                    }
                    if ($fld) {
                        $val = $fld->value;
                    }
                }
                if (!is_null($val)) {
                    $retVal = str_replace($name[1], $val, $retVal);
                }
            }
        }
        if (preg_match("/^\s*((table_exp.+)|(kume_exp.+)|(field_value.+))\s*$/", $strValue)) {
            $retVal = eval("return \$this->$retVal;");
        }
        return str_replace('\n', "\n", $retVal);
    }

    function buildEval($QRY, $strValue, $cQry)
    {
        $retVal = trim($strValue);
        if (preg_match_all("/%(\w+)(\W|$)/U", $retVal, $flds, PREG_SET_ORDER)) {
            foreach ($flds as $name) {
                $val = null;
                if ($QRY->main) {
                    $fld = $QRY->main->fieldByName($name[1]);
                    if ($fld) {
                        $val = '{$' . $cQry . "->main->rec_$fld->name}";
                    }
                } else {
                    $fld = $QRY->fieldByName($name[1]);
                    if ($fld) {
                        $val = "$$cQry" . "->rec_$fld->name";
                    }
                }
                if ($val) {
                    $retVal = str_replace("%$name[1]", $val, $retVal);
                }
            }
        }
        if (preg_match_all("/((\\\$(\w+)[\\\.-](\w+))|(\\\$(\w+))|(\w+))(\W|$)/", $retVal, $flds, PREG_SET_ORDER)) {
            foreach ($flds as $name) {
                $val = null;
                if (!empty($name[2])) {
                    if ($name[3] == "main") {
                        if ($QRY->main && ($fld = $QRY->main->fieldByName($name[4]))) {
                            $val = '{$' . $cQry . "->main->rec_$fld->name}";
                        }
                    } else {
                        $val = $this->objVal($name[3], $name[4]);
                    }
                } elseif (!empty($name[5])) {
                    $val = $this->objVal($name[6]);
                } elseif (!empty($name[7])) {
                    if ($QRY->main) {
                        $fld = $QRY->main->fieldByName($name[1]);
                        if ($fld) {
                            $val = '{$' . $cQry . "->main->rec_$fld->name}";
                        }
                    } else {
                        $fld = $QRY->fieldByName($name[1]);
                        if ($fld) {
                            $val = "$$cQry" . "->rec_$fld->name";
                        }
                    }
                }
                if ($val) {
                    $retVal = str_replace("$name[1]", $val, $retVal);
                }
            }
        }
        return $retVal;
    }

    function toVal(&$val, $type)
    {
        $ret = true;
        switch ($type) {
            case "S":
            case "M":
            case "Q":
            case "W":
                break;
            case "N":
                $ret = is_numeric($val);
                break;
            case "I":
                $ret = is_numeric($val) && intval($val) == $val;
                break;
            case "D":
                $ret = false;
                if (date_create($val) && ($arr = date_parse($val)) && checkdate($arr[2], $arr[1], $arr[0])) {
                    $ret = true;
                    $val = date_format(date_create($val), "Y-m-d");
                }
                break;
            case "T":
                $ret = false;
                if (date_create($val) && ($arr = date_parse($val)) && checkdate($arr[2], $arr[1], $arr[0])) {
                    $ret = true;
                    $val = date_format(date_create($val), "Y-m-d H:i:s");
                }
                break;
            case "H":
                $ret = preg_match('/^\s*\d{1,2}:d{1,2}(:\d{1,2})?\s*$/', $val);
                break;
            case "L":
                $val = $val ? 1 : 0;
        }
        return $ret;
    }

    function toTypeChar($val)
    {
        if (is_null($val)) {
            return "X";
        }
        if (is_numeric($val)) {
            return "N";
        }
        if (preg_match('/^\s*\d{1,2}[./-]\d{1,2}[./-]\d{4}(\s+\d{1,2}:\d{1,2}(:\d{1,2})?)?\s*$/', $val, $match)) {
            return (empty($match[1]) ? "D" : "T");
        }
        if (preg_match('/^\s*\d{4}[./-]\d{1,2}[./-]\d{1,2}(\s+\d{1,2}:\d{1,2}(:\d{1,2})?)?\s*$/', $val, $match)) {
            return (empty($match[1]) ? "D" : "T");
        }
        return "S";
    }

    function objVal($objName = "", $strAttr = "")
    {
        global $oUser, $oPerso, $oMesul, $oSirket;
        $val = null;
        switch ($objName) {
            case "ouser" :
                if (isset($oUser->$strAttr)) {
                    $val = $oUser->$strAttr;
                }
                break;
            case "operso":
                if (isset($oPerso->$strAttr)) {
                    $val = $oPerso->$strAttr;
                }
                break;
            case "omesul":
                if (isset($oMesul->$strAttr)) {
                    $val = $oMesul->$strAttr;
                }
                break;
            case "osirket":
                if (isset($oSirket->$strAttr)) {
                    $val = $oSirket->$strAttr;
                }
                break;
            case "ozaman":
                if ($strAttr == "bugun") {
                    $val = date("Y-m-d");
                } elseif ($strAttr == "tarih") {
                    $val = date("Y-m-d");
                } elseif ($strAttr == "dun") {
                    $val = date("Y-m-d", strtotime("-1 day"));
                } elseif ($strAttr == "yarin") {
                    $val = date("Y-m-d", strtotime("+1 day"));
                } elseif ($strAttr == "saat") {
                    $val = date("H:i:s");
                } elseif ($strAttr == "busaat") {
                    $val = date("H:i:s");
                } elseif ($strAttr == "zaman") {
                    $val = date("Y-m-d H:i:s");
                } elseif ($strAttr == "yil") {
                    $val = date("Y");
                } elseif ($strAttr == "ay") {
                    $val = date("m");
                } elseif ($strAttr == "gun") {
                    $val = date("d");
                } elseif ($strAttr == "donem") {
                    $val = -((date("Y") - 2000) * 100 + date("m"));
                } elseif ($strAttr == "1_donem") {
                    $val = -((date("Y") - 2000) * 100 + 1);
                } elseif ($strAttr == "12_donem") {
                    $val = -((date("Y") - 2000) * 100 + 12);
                } elseif ($strAttr == "adonem") {
                    $val = -((date("Y") - 2000) * 100 + date("m"));
                } elseif ($strAttr == "haftabasi") {
                    $val = date("Y-m-d", strtotime((-date("N") + 1) . " day"));
                } elseif ($strAttr == "aybasi") {
                    $val = date("Y-m-01");
                } elseif ($strAttr == "aysonu") {
                    $val = date("Y-m-d", strtotime(date("Y-m-1") . " +1 month -1 day"));
                } elseif ($strAttr == "yilbasi") {
                    $val = date("Y-01-01");
                } elseif ($strAttr == "yilsonu") {
                    $val = date("Y-12-31");
                } elseif ($strAttr == "1_dakika") {
                    $val = date("d.m.Y H:i:s", strtotime("-1 minute"));
                } elseif ($strAttr == "o_hafta") {
                    $val = date("Y-m-d", strtotime("-7 day"));
                } elseif ($strAttr == "o_ay") {
                    $val = date("Y-m-d", strtotime("-30 day"));
                } elseif ($strAttr == "1_hafta") {
                    $val = date("Y-m-d", strtotime("+7 day"));
                } elseif ($strAttr == "g_haftabasi") {
                    $val = date("Y-m-d", strtotime((-date("N") + 1 - 7) . " day"));
                } elseif ($strAttr == "g_aybasi") {
                    $val = date("Y-m-d", strtotime(date("Y-m-1") . " -1 month"));
                } elseif ($strAttr == "g_aysonu") {
                    $val = date("Y-m-d", strtotime(date("Y-m-1") . " -1 day"));
                } elseif ($strAttr == "g_yilbasi") {
                    $val = date("Y-m-d", strtotime(date("Y-1-1") . " -1 year"));
                } elseif ($strAttr == "g_yilsonu") {
                    $val = date("Y-m-d", strtotime(date("Y-1-1") . " -1 day"));
                } elseif ($strAttr == "s_aybasi") {
                    $val = date("Y-m-d", strtotime(date("Y-m-1") . " +1 month"));
                } elseif ($strAttr == "s_aysonu") {
                    $val = date("Y-m-d", strtotime(date("Y-m-1") . " +2 month -1 day"));
                } elseif ($strAttr == "s_yilbasi") {
                    $val = date("Y-m-d", strtotime(date("Y-1-1") . " +1 year"));
                } elseif ($strAttr == "s_yilsonu") {
                    $val = date("Y-m-d", strtotime(date("Y-1-1") . " +2 year -1 day"));
                }
                break;
            case "osenaryo":
                if (isset($this->senaryo->$strAttr)) {
                    $val = $this->senaryo->$strAttr;
                }
                break;
            case "osayfa":
                if (isset($this->senaryo->$strAttr)) {
                    $val = $this->senaryo->$strAttr;
                }
                break;
            case "omain":
                if (isset($this->main, $this->main->{"fld_$strAttr"})) {
                    $val = $this->main->{"rec_$strAttr"};
                }
                break;
            case "main":
                if (isset($this->main, $this->main->{"fld_$strAttr"})) {
                    $val = $this->main->{"rec_$strAttr"};
                }
                break;
            case "form":
                if (array_key_exists("frm_$strAttr", $_POST)) {
                    $val = $_POST["frm_$strAttr"];
                }
                break;
            default:
                $val = $this->objVal("ozaman", $objName);
        }
        return $val;
    }

    function findOpt($strOpt)
    {
        $oOpt = (object)array();
        if (preg_match_all("/(\w+)\s*(=\s*(.+?)\s*)?(;|\r|\n|$)/", $strOpt, $arr_opt, PREG_SET_ORDER)) {
            foreach ($arr_opt as $opt) {
                if (preg_match_all("/\s*(.+?)\s*(,|$)/", $opt[3], $arr_lst, PREG_SET_ORDER)) {
                    foreach ($arr_lst as $lst) {
                        $oOpt->{"$opt[1]"}[] = empty($lst[1]) ? 1 : $lst[1];
                    }
                }
            }
        }
        return $oOpt;
    }

    function DostoWinChar($cStr)
    {
        return strtr($cStr, "������������", "������������");
    }

    function ChrtranEng($cStr)
    {
        return strtr($cStr, '��������������', 'ASGCOUIasgcoui');
    }
}

?>