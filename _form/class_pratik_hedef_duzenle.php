<?php
class class_pratik_hedef_duzenle extends class_pratik{
	function formtam(){

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;
		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$exp=(isset($_POST["frm_exp"])?$_POST["frm_exp"]:$this->qry->rec_exp);
		$miktar=(isset($_POST["frm_miktar"])?$_POST["frm_miktar"]:$this->qry->rec_miktar);
		$adet=(isset($_POST["frm_adet"])?$_POST["frm_adet"]:$this->qry->rec_adet);

		$ctarih=(isset($_POST["frm_ctarih"])?$_POST["frm_ctarih"]:$this->qry->rec_ctarih);

		$perso =(isset($_POST["frm_perso"]) &&!empty($_POST["frm_perso"]) ?$_POST["frm_perso"] :$this->qry->rec_perso);
		$aperso=(isset($_POST["frm_aperso"])&&!empty($_POST["frm_aperso"])?$_POST["frm_aperso"]:$this->qry->rec_aperso);

		$perso_exp =(isset($_POST["frm_perso_exp"]) ?$_POST["frm_perso_exp"] :$this->qry->rec_perso_exp);
		$aperso_exp=(isset($_POST["frm_aperso_exp"])?$_POST["frm_aperso_exp"]:$this->qry->rec_aperso_exp);

		$acikla=(isset($_POST["frm_acikla"])?$_POST["frm_acikla"]:$this->qry->rec_acikla);

		$qCCC->prm_exp    = $exp;
		$qCCC->prm_miktar = $miktar;
		$qCCC->prm_adet   = $adet;
		$qCCC->prm_ctarih = $ctarih;
		$qCCC->prm_perso  = $perso;
		$qCCC->prm_aperso = $aperso;
		$qCCC->prm_acikla = trim($acikla);

		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>