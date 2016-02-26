<?php
class class_pratik_gorev_gorevikabulet extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;
		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$dur_str = "Görev Kabul Edildi";

		$acikla=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$acikla=trim($this->qry->rec_acikla)."\n\n--------------------------\n".date("d.m.Y H:i")." $oUser->exp $dur_str\n$acikla";

		$qCCC->prm_durum  = -54102;
		$qCCC->prm_acikla = $acikla;
		$qCCC->prm_atarih = $_POST["frm_atarih"] ;
		$qCCC->prm_ctarih = $_POST["frm_ctarih"] ;
		$this->qryExec($qCCC);
		$this->formMessage();
	}

	function form($addParam=''){
		$durum=isset($_GET["par_durum"]) ? $_GET["par_durum"] : "";
		$this->baslik="Görev Durumu Deðiþtir";
		if($durum==-54102)$this->baslik="Görev Kabul Edildi";
		if($durum==-54103)$this->baslik="Görev Tamamlandý";
		if($durum==-54104)$this->baslik="Arþivle";
		if($durum==-54106)$this->baslik="Ýptal Et";
		$addParam=empty($durum)?"":"&par_durum=$durum";
		parent::form($addParam);
	}
}
?>
