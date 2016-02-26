<?php
include_once("$REAL_P/_form/class_util_gorev.php");
class class_pratik_gorev_acikla extends class_pratik{
	function formtam(){
		global $oUser;

		$form="";
		if(isset($_GET["par_form"]))$form=$_GET["par_form"];
		$this->qry->close();
		if($form!="servis")$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;
		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$perso=(isset($_POST["cal_perso"])&&!empty($_POST["cal_perso"])?$_POST["cal_perso"]:$this->qry->rec_perso);
		$perso_exp=(isset($_POST["cal_perso_exp"])?$_POST["cal_perso_exp"]:"");
		if($perso==$this->qry->rec_aperso)$perso=$this->qry->rec_perso;

		$gorev = new class_util_gorev();
		$durum = $this->qry->rec_durum;
		$durum = $gorev->getdurum($durum, $this->qry->rec_atarih, $this->qry->rec_ctarih);
		$durum_exp=$this->table_exp('durum_tr',$durum);

		$okuma="";
		if($this->qry->rec_aperso==$this->qry->rec_perso) // kendi
			$okuma="++";
		elseif($oUser->perso==$this->qry->rec_aperso) // verilen
			$okuma="+-";
		elseif($oUser->perso==$this->qry->rec_perso) // alınan
			$okuma="-+";

		$qCCC->prm_gorev_id=$this->qry->rec_id;
		$qCCC->prm_okuma=$okuma;
		$qCCC->prm_durum=$durum;
		$qCCC->prm_perso=$perso;
		$this->qry->rec_okuma=$okuma;

		if($okuma=="-+"){
			$acikla="\n\n<span style='color:gray'>\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
			if($perso!=$this->qry->rec_perso)$acikla.="\nPerso: $perso_exp";
			if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
			$acikla=$acikla."</span>";
		}else{
			$acikla="\n\n\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
			if($perso!=$this->qry->rec_perso)$acikla.="\nPerso: $perso_exp";
			if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		}

		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		//$qCCC->print_pars();
		$this->qryExec($qCCC);
		$this->formMessage();
	}
	function form(){
		if(isset($_GET["par_form"]))$this->par.="&par_form=".$_GET["par_form"];
		parent::form();
	}
}
?>