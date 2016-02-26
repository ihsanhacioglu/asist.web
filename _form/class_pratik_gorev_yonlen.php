<?php
include_once("$REAL_P/_form/class_util_gorev.php");
class class_pratik_gorev_yonlen extends class_pratik{
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

		$durum=-5100; // Yeni Görev
		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$perso=(isset($_POST["cal_perso"])?$_POST["cal_perso"]:$this->qry->rec_perso);
		$perso_exp=(isset($_POST["cal_perso_exp"])?$_POST["cal_perso_exp"]:"");
		if($perso==$this->qry->rec_aperso){
			$durum=-5101; // Aktif Görev
			$perso=$this->qry->rec_perso;
		}
		
		$gorev = new class_util_gorev();
		$durum = $gorev->getdurum($durum, $this->qry->rec_atarih, $this->qry->rec_ctarih);
		$durum_exp=$this->table_exp('durum_tr',$durum);

		$okuma="";
		if($this->qry->rec_aperso==$this->qry->rec_perso) // kendi
			$okuma="++";
		elseif($oUser->perso==$this->qry->rec_aperso) // verilen
			$okuma="+-";
		elseif($oUser->perso==$this->qry->rec_perso) // alınan
			$okuma="--";

		$this->qry->rec_okuma=$okuma;

		$qCCC->prm_id=$this->qry->rec_id;
		$qCCC->prm_durum=$durum;
		$qCCC->prm_okuma=$okuma;
		$qCCC->prm_perso=$perso;

		$span1="";
		$span2="";
		if($okuma=="-+"){
			$span1="<span style='color:gray'>";
			$span2="</span>";
		}
		$acikla="\n\n$span1\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		$acikla.="\nDurum: $durum_exp";
		if($perso!=$this->qry->rec_perso)  $acikla.="\nPerso: $perso_exp";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		$acikla.=$span2;

		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
	function form(){
		$form=isset($_GET["par_form"]) ? $_GET["par_form"] : "";
		if(!empty($form))$this->par.="&par_form=$form";
		parent::form();
	}
}
?>