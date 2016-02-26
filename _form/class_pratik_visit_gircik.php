<?php
class class_pratik_visit_gircik extends class_pratik{
	function formpra(){
		$gircik=isset($_GET["gircik"]) ? $_GET["gircik"] : "";
		$this->qry->close();
		$this->qry->keyopen($this->id);

		if($gircik=="giris"){
			$this->qry->fld_asaat->value=date("H:i");
			$this->qry->fld_asaat->req=true;
			$this->qry->fld_csaat->read=true;
		}elseif($gircik=="cikis"){
			$this->qry->fld_csaat->value=date("H:i");
			$this->qry->fld_csaat->req=true;
			$this->qry->fld_asaat->read=true;
			$this->qry->fld_kartno->read=true;
		}
		$this->form();
	}
	function formtam(){
		$gircik=isset($_GET["gircik"]) ? $_GET["gircik"] : "";
		$this->qry->close();
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$qCCC->prm_asaat=$this->qry->rec_asaat;
		$qCCC->prm_csaat=$this->qry->rec_csaat;
		if($gircik=="giris" && empty($qCCC->prm_asaat))$qCCC->prm_asaat=date("H:i");
		if($gircik=="cikis" && empty($qCCC->prm_csaat))$qCCC->prm_csaat=date("H:i");
		if($gircik=="degis")$this->bindParams($qCCC,$this->senaryo->parvalues);

		$valA=date_format(date_create($this->qry->rec_atarih),"Y-m-d");
		if($valA==date("Y-m-d")){
			if(empty($qCCC->prm_asaat))$abc="B";
			elseif($qCCC->prm_asaat <= date("H:i")){
				if(empty($qCCC->prm_csaat))$abc="A";
				elseif($qCCC->prm_csaat <= date("H:i"))$abc="C";
				else$abc="?";
			}
		}
		elseif($valA > date("Y-m-d"))$abc="B";
		elseif($valA < date("Y-m-d"))$abc="C";
		$qCCC->prm_abc=$abc;
		
		$this->qryExec($qCCC);
		$this->formMessage();
	}
	function form(){
		$gircik=isset($_GET["gircik"]) ? $_GET["gircik"] : "";
		if(!empty($gircik))$this->par.="&gircik=$gircik";
		parent::form();
	}
}
?>
