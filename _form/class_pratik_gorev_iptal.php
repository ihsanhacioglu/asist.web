<?php
include_once("$REAL_P/_form/class_util_gorev.php");
class class_pratik_gorev_iptal extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$durum=-5106; // Görevi İptal Et
		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");

		$acikla="\n\n\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		$durum_exp=$this->table_exp('durum_tr',$durum);
		$acikla.="\nDurum: $durum_exp";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);

		$qCCC->prm_durum=$durum;
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>