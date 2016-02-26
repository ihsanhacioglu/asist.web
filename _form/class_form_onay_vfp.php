<?php
class class_form_onay_vfp extends class_form{
	function islem($islem=""){
		if (!empty($islem)) $this->islem=$islem;
		if (!$this->islemPerm()){
			echo "ACCESS DENIED";
			return false;
		}
		if		($this->islem=="src")	$this->formsrc();
		elseif	($this->islem=="ara")	$this->formara();
		elseif	($this->islem=="lst")	$this->formlst();
		elseif	($this->islem=="brw")	$this->formbrw();
		else	$this->formsel();
	}
	function beforePost(){
		$this->qry->rec_dtarih = $this->objVal("ozaman","bugun");
		$this->qry->rec_dsaat  = $this->objVal("ozaman","busaat");
	}
}
?>

