<?php
include_once("$REAL_P/_form/class_form_sayfa__base.php");
class class_form_betur extends class_form_sayfa__base{
	public $ilistab="betur";

	function afterOpen(){$this->oVals=$this->qry->getFldVals();}
    function afterPost(){
		$this->denkUpdate("betur");
    }
	function denkFlds($oDenk){
		$flds=parent::denkFlds($oDenk);
		$flds.=" formtemp";
		return $flds;
	}
	function denkBind($oDenk){
		$tabFld=array("formtemp"=>0);
		foreach($tabFld as $fldNam=>$tabVal)$tabFld[$fldNam]=&$oDenk->dTab->{"rec_$fldNam"};
		foreach($oDenk->oUpd->arrFields as $uFld){
			if(isset($tabFld[$uFld->name]))continue;
			$fld_name="fld_$uFld->name";
			if(isset($oDenk->dTab->$fld_name))$oDenk->dTab->$fld_name->value=$uFld->value;
		}
		$this->tabFld=$tabFld;
		parent::denkBind($oDenk);
	}
}
?>