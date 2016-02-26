<?php
class class_pratik_etkin_yonlen extends class_pratik{
	function formtam(){
		global $oUser;

		//$form="";
		//if(isset($_GET["par_form"]))$form=$_GET["par_form"];
		//$this->par=$form;
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;
		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$perso=(isset($_POST["frm_perso"])?$_POST["frm_perso"]:$this->qry->rec_perso);
		$aperso=(isset($_POST["frm_aperso"])?$_POST["frm_aperso"]:$this->qry->rec_aperso);
		$perso_exp=(isset($_POST["frm_perso_exp"])?$_POST["frm_perso_exp"]:"");
		if($perso==$this->qry->rec_aperso)$perso=$this->qry->rec_perso;

		$okuma="";
		if($this->qry->rec_aperso==$this->qry->rec_perso) // kendi
			$okuma="++";
		elseif($oUser->perso==$this->qry->rec_aperso) // verilen
			$okuma="+-";
		elseif($oUser->perso==$this->qry->rec_perso) // alınan
			$okuma="--";

		$span1="";
		$span2="";
		if($okuma=="-+"){
			$span1="<span style='color:gray'>";
			$span2="</span>";
		}
		$acikla="\n\n$span1\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		if(!empty($perso)  && $perso !=$this->qry->rec_perso) $acikla.="\nPerso: $perso_exp";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		$acikla.=$span2;

		$qCCC->prm_id=$this->qry->rec_id;
		$qCCC->prm_durum=$this->qry->rec_durum;
		$qCCC->prm_okuma=$okuma;
		$qCCC->prm_perso=$perso;

		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>