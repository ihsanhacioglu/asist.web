<?php
class class_form_ikisi extends class_form{
	function createCalFlds(){
		$this->arrCals=array();
		
	
		// Telefon1
		$this->arrCals["tadres_ktipi1"]=0;
		$this->arrCals["tadres_exp1"]="";
		$this->arrCals["tadres_ktipi_exp1"]="";

		// Telefon2
		$this->arrCals["tadres_ktipi2"]=0;
		$this->arrCals["tadres_exp2"]="";
		$this->arrCals["tadres_ktipi_exp2"]="";

		// Email Adresi
		$this->arrCals["eadres_exp"]="";

		// Web Adresi
		$this->arrCals["wadres_exp"]="";
	}

	function beforePost(){
		$this->createCalFlds();
		$this->bindCalFlds();
	}
	function afterPost(){
		if($this->islem=="ins")$this->restInsert();
	}
	
	function recValid($QRY){
		return true;
	}

	function restInsert(){
		// 1. Telefon numarasýnýn kaydý	
		$cExp    = $this->qry->rec_exp.", ".$this->qry->rec_ktipi_exp;
		$iliski = "Ikisi-".$this->qry->rec_id;


		if(!empty($this->arrCals["tadres_exp1"])){
			$tTadres = $this->qry->derive_tab("tadres:set=1",-1);
			$tTadres->rec_kimlik = $this->qry->rec_kimlik;
			$tTadres->rec_ktipi  = $this->arrCals["tadres_ktipi1"];
			$tTadres->rec_exp    = $this->arrCals["tadres_exp1"];
			$tTadres->rec_acikla = $cExp;
			$tTadres->rec_iliski = $iliski;
			
			$tTadres->insert();
		}
		
		// 2. Telefon numarasýnýn kaydý	
		if(!empty($this->arrCals["tadres_exp2"])){
			$tTadres = $this->qry->derive_tab("tadres:set=1",-1);
			$tTadres->rec_kimlik = $this->qry->rec_kimlik;
			$tTadres->rec_ktipi  = $this->arrCals["tadres_ktipi2"];
			$tTadres->rec_exp    = $this->arrCals["tadres_exp2"];
			$tTadres->rec_acikla = $cExp;
			$tTadres->rec_iliski = $iliski;
			$tTadres->insert();
		}
	
		// Email Adresi
		if(!empty($this->arrCals["eadres_exp"])){
			$tEadres = $this->qry->derive_tab("eadres:set=1",-1);
			$tEadres->rec_kimlik = $this->qry->rec_kimlik;
			$tEadres->rec_ktipi  = -31201 ; //Is Email Adresi                              
			$tEadres->rec_exp    = $this->arrCals["eadres_exp"];
			$tEadres->rec_acikla = $cExp;
			$tEadres->rec_iliski = $iliski;
			$tEadres->insert();
		}

		// Web Adres Adresi
		if(!empty($this->arrCals["wadres_exp"])){
			$tWadres = $this->qry->derive_tab("wadres:set=1",-1);
			$tWadres->rec_kimlik = $this->qry->rec_kimlik;
			$tWadres->rec_ktipi  = -31301 ; //Sirket web adresi
			$tWadres->rec_exp    = $this->arrCals["wadres_exp"];
			$tWadres->rec_acikla = $cExp;
			$tWadres->rec_iliski = $iliski;
			$tWadres->insert();
		}
    }

	
}
?>