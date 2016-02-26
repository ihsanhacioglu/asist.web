<?php
include_once("$REAL_P/_form/class_util_gorev.php");
class class_pratik_gorev_kabulet extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$atarih=(isset($_POST["frm_atarih"])?$_POST["frm_atarih"]:"");
		$ctarih=(isset($_POST["frm_ctarih"])?$_POST["frm_ctarih"]:"");
		$perso=(isset($_POST["frm_perso"])?$_POST["frm_perso"]:0);
		$perso_exp=(isset($_POST["frm_perso_exp"])?$_POST["frm_perso_exp"]:"");

		$gorev = new class_util_gorev();
		$durum=-5101; // Kabul Edildi
		$durum = $gorev->getdurum($durum, $atarih, $ctarih);
		$durum_exp=$this->table_exp('durum_tr',$durum);
		
		$acikla="\n\n<span style='color:gray'>\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		if(!empty($atarih) && $atarih!=$this->qry->rec_atarih)$acikla.="\nATarih: $atarih";
		if(!empty($ctarih) && $ctarih!=$this->qry->rec_ctarih)$acikla.="\nCTarih: $ctarih";
		if(!empty($perso)  && $perso!=$this->qry->rec_perso)  $acikla.="\nPerso: $perso_exp";
		$acikla.="\nDurum: $durum_exp";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		$acikla=$acikla."</span>";

		$qCCC->prm_durum=$durum;
		$qCCC->prm_atarih=$atarih;
		$qCCC->prm_ctarih=$ctarih;
		$qCCC->prm_perso=$perso;
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>