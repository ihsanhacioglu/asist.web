<?php

class class_pratik_onayla_mhsb extends class_pratik{
	function formtam(){
		$this->qry->close();
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		if(($par=$qCCC->paramByName("durum"))){
			$durum=null;
			if(isset($_GET["par_durum"]))$durum=$_GET["par_durum"];
			if(is_numeric($durum) && strpos(",,-21501,-21503,-21504,-21506,-21507,-21508,",",$durum,"))$par->value=$durum;
		}
		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>