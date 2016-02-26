<?php
include_once("$REAL_P/_form/class_util_gorev.php");
class class_pratik_gorev_henuz extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;
		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$okuma="+-";

		$gorev = new class_util_gorev();
		$durum=-5101; // Görev Kabul Edildi
		$durum = $gorev->getdurum($durum, $this->qry->rec_atarih, $this->qry->rec_ctarih);
		$durum_exp=$this->table_exp('durum_tr',$durum);

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");

		$qCCC->prm_id=$this->qry->rec_id;
		$this->qry->rec_okuma=$okuma;
		$acikla="\n\n\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		$acikla.="\nDurum: Görev Tamam Deðil,  Aufgabe ist noch nicht erledigt";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);

		$qCCC->prm_durum=$durum;
		$qCCC->prm_okuma=$okuma;
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>