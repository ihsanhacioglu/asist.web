<?php
class class_pratik_debas_persodegistir extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;
		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$perso=(isset($_POST["cal_perso"])?$_POST["cal_perso"]:$this->qry->rec_perso);
		$perso_exp=(isset($_POST["cal_perso_exp"])?$_POST["cal_perso_exp"]:"");
		$deyer=(isset($_POST["frm_deyer"])?$_POST["frm_deyer"]:$this->qry->rec_deyer);
		$deyer_exp=(isset($_POST["frm_deyer_exp"])?$_POST["frm_deyer_exp"]:"");
		
		
		
		if(empty($perso)){
			$this->msg="Lütfen zimmetli personel seçiniz.";
			$this->formMessage();
			return;
		}
		if($perso==$this->qry->rec_perso && $deyer==$this->qry->rec_deyer){
			$this->msg="Lütfen farklı zimmetlipersonel/bulunduğu yer seçiniz.";
			$this->formMessage();
			return;
		}
		$durum=-16211; // Zimmetlendi
		$durum_exp=$this->table_exp('kume_tr',$durum);

		$qCCC->prm_id=$this->qry->rec_id;
		$qCCC->prm_deyer=$deyer;
		$qCCC->prm_durum=$durum;
		$qCCC->prm_perso=$perso;

		/*
		$acikla="\n\n\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		$acikla.="\nDurum: $durum_exp";
		if($perso!=$this->qry->rec_perso)  $acikla.="\nPerso: $perso_exp";
		if($deyer!=$this->qry->rec_deyer)  $acikla.="\nDeyer: $deyer_exp";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		*/

		$qCCC->print_pars();
		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>