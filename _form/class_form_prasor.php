<?php
class class_form_prasor extends class_form{
    function afterPost(){
		$this->denkUpdate("prasor");
    }
	function denkFlds($oDenk){
		$flds=parent::denkFlds($oDenk);
		$flds.=" sqlstr";
		return $flds;
	}
	function denkBind($oDenk){
		parent::denkBind($oDenk);
		$arrDenk=$this->denkTabArr();
		$this->denkReplace($arrDenk,$oDenk->dTab->rec_sqlstr);
	}
}
?>