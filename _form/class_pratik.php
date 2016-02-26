<?php

class class_pratik extends class__base
{
    function __construct($nLink, $cAction)
    {
        global $oUser;

        if (empty($nLink)) {
            return false;
        }
        $this->appLink = $nLink;

        $cDil = strtolower($oUser->dilse);
        if ($cDil != "de" && $cDil != "en") {
            $cDil = "tr";
        }
        $this->dil = $cDil;
        $this->sen = isset($_GET["sen"]) ? $_GET["sen"] : 0;
        $this->sen = empty($this->sen) ? 0 : $this->sen;
        if (!($this->senaryo = $this->getSenaryo($cAction))) {
            echo "$cAction<br>ACCESS DENIED";
            return false;
        }
        $this->senaryo->parvalues = $this->getParams($cAction);

        $this->id = isset($_GET["id"]) ? $_GET["id"] : 0;
        $this->id = empty($this->id) ? 0 : $this->id;
        $this->islem = isset($_GET["islem"]) ? $_GET["islem"] : "";
        $this->mod = isset($_GET["mod"]);

        $this->create_qry();
        $this->main = $this->qry;
    }

    function getSenaryo($cAction)
    {
        global $oUser;

        $tabSayfa = "sayfa_$this->dil";
        $strSql = "select role.once,
					sen.id,
					sen.exp,
					sen.action,
					sen.sqlstr,
					sen.updtables,
					senrole.defvalues,
					senrole.filtvalues
				from asist.$tabSayfa sen, asist.senrole, asist.role
				where sen.id=$this->sen
					and sen.id=senrole.senaryo
					and senrole.role=role.id
					and $oUser->where
				order by 1";
        $res = mysqli_query($this->appLink, $strSql);
        $mSay = mysqli_fetch_object($res);
        mysqli_free_result($res);
        if (empty($mSay)) {
            return null;
        }

        $strSql = "select sen.id,
					sen.exp,
					sen.action,
					sen.sqlstr pratik_sqlstr,
					sen.formtemp,
					sen.color,
					sen.tur,
					sen.datab,
					sen.snloption
				from asist.$tabSayfa sen, asist.senrole, asist.role
				where sen.action='$cAction'
					and sen.id=senrole.senaryo
					and senrole.role=role.id
					and $oUser->where
					and instr(senrole.options,'+')>0
				order by 1";
        $res = mysqli_query($this->appLink, $strSql);
        $oSen = mysqli_fetch_object($res);
        mysqli_free_result($res);
        if (empty($oSen)) {
            return null;
        }

        $oSen->sqlstr = $mSay->sqlstr;
        $oSen->updtables = $mSay->updtables;
        $oSen->defvalues = $mSay->defvalues;
        $oSen->filtvalues = $mSay->filtvalues;

        return $oSen;
    }

    function getParams($cAction)
    {
        $strSql = "select parvalues from asist.param where param.action='$cAction' and param.senaryo=$this->sen";
        $res = mysqli_query($this->appLink, $strSql);
        $oRec = mysqli_fetch_object($res);
        mysqli_free_result($res);
        if ($oRec) {
            return $oRec->parvalues;
        } else {
            return "";
        }
    }

    function islem()
    {
        if ($this->islem == "tam") {
            $this->formtam();
        } else {
            $this->formpra();
        }
    }

    function formpra()
    {
        $this->qry->close();
        $this->setWhere($this->qry, $this->senaryo->filtvalues, "S");
        $this->qry->keyopen($this->id);
        $this->form();
    }

    function formtam()
    {
        $this->qry->close();
        $this->setWhere($this->qry, $this->senaryo->filtvalues, "S");
        $this->qry->keyopen($this->id);

        $strClass = get_class($this->qry);
        $qCCC = new $strClass($this->qry->get_dbLink(), $this->senaryo->pratik_sqlstr, $this->qry);
        $qCCC->senaryo = $this->senaryo->id;

        $this->bindParams($qCCC, $this->senaryo->parvalues);
        $this->qryExec($qCCC);
        $this->formMessage();
    }

    function qryExec($qCCC)
    {
        $strMessage = "";
        $ok = true;
        foreach ($qCCC->arrParams as $oPar) {
            if (is_null($oPar->value)) {
                $strMessage .= "<br>$oPar->name = null";
            }
        }
        if ($strMessage == "") {
            $qCCC->exec();
            $strMessage = "UPDATED";
        } else {
            $ok = false;
            $strMessage = "ERROR - NO UPDATE$strMessage";
        }

        $this->strMessage = $strMessage;

        return $ok;
    }

    function formMessage($sure = 0)
    {
        if ($this->mod) {
            $this->modMessage($sure);
        } else {
            $this->msgMessage($sure);
        }
    }

    function form()
    {
        global $cKare, $cList;

        $cFormStr = $this->senaryo->formtemp;
        if ($this->islem == "prm") {
            $cActislem = "tam";
        } else {
            $cActislem = "prm";
        }
        $pic = isset($_GET["pic"]) ? $_GET["pic"] : "";
        $pic = empty($pic) ? "" : $pic;
        $this->par .= empty($pic) ? "" : "&pic=$pic";

        if (!$this->formObjArr($cFormStr)) {
            return false;
        }
        if (!empty($this->senaryo->color)) {
            echo "\n<style>body{background:#{$this->senaryo->color};}</style>\n";
        }
        $optFrm = $this->findOpt($this->senaryo->snloption);
        if (isset($optFrm->width)) {
            echo "\n<script>x_left=Math.floor((screen.width-{$optFrm->width[0]})/2);
												y_top=Math.floor((screen.height-{$optFrm->height[0]})/2);
												window.moveTo(x_left,y_top);
												window.resizeTo({$optFrm->width[0]},{$optFrm->height[0]});
										</script>\n";
        }
        $this->createEval();

        if (!empty($this->msg)) {
            echo "<font color='red'>$this->msg</font>\n";
        }
        $this->pre = "";
        $QQ = $this->qry;
        echo "\n<form enctype='multipart/form-data' name='form1' id='id_form_form1' method='post' action='?{$this->senaryo->action}", $this->mod ? "&mod=" : "", "&islem=$cActislem&sen=$this->sen&id=$this->id$this->par' islem='$cActislem' target='_self' onReset='return ResetForm(this);'>\n";
        foreach ($this->arrEval as $str_eval) {
            eval("echo \"" . $str_eval . "\";");
        }
        echo "</form>\n";
    }
}


?>