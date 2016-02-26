<?php
class class_pratik_gorev_sahip extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$per_str="";
		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$qCCC->prm_sahip=$this->qry->rec_aperso;
		$qCCC->prm_sorum=$this->qry->rec_perso;

		$perso_exp=isset($_POST["frm_sahip_exp"]) ? $_POST["frm_sahip_exp"] : "";
		$perso=isset($_POST["frm_perso"]) ? $_POST["frm_perso"] : 0;
		$sahip=isset($_GET["par_sahip"])  ? $_GET["par_sahip"]  : "";

		if($sahip=="sahip"){
			if(isset($perso))$qCCC->prm_sahip=$perso;
			$acikla=trim($this->qry->rec_acikla).
				"\n\n--------------------------\n".
				date("d.m.Y H:i")." $oUser->exp\n".
				"$sahip: {$this->qry->rec_aperso_exp} > $perso_exp";
		}
		if($sahip=="sorum"){
			if(isset($perso))$qCCC->prm_sorum=$perso;
			$acikla=trim($this->qry->rec_acikla).
				"\n\n--------------------------\n".
				date("d.m.Y H:i")." $oUser->exp\n".
				"$sahip: {$this->qry->rec_perso_exp} > $perso_exp";
		}
		$qCCC->prm_acikla=$acikla;

		$this->qryExec($qCCC);
		$this->formMessage();
	}
	function form($addParam=''){
		$sahip=isset($_GET["par_sahip"]) ? $_GET["par_sahip"] : "";
		$this->baslik="Görev Sahip-Sorumulusu Deðiþtir";
		if($sahip=="sahip")$this->baslik="Görev Sahibini Deðiþtir";
		if($sahip=="sorum")$this->baslik="Görev Sorumlusunu Deðiþtir";
		$addParam=empty($sahip)?"":"&par_sahip=$sahip";
		parent::form($addParam);
	}
}
?>
