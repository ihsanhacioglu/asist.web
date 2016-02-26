<?php
class class_pratik_etkin_acikla extends class_pratik{
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
		$doran=(isset($_POST["frm_doran"])?$_POST["frm_doran"]:$this->qry->rec_doran);

		$durum=$this->etkin_durum($doran);
		$durum_exp=$this->table_exp('kume_tr',$durum);

		$okuma="";
		if($this->qry->rec_aperso==$this->qry->rec_perso) // kendi
			$okuma="++";
		elseif($oUser->perso==$this->qry->rec_aperso) // verilen
			$okuma="+-";
		elseif($oUser->perso==$this->qry->rec_perso) // alınan
			$okuma="--";

		//$qCCC->prm_etkin_id=$this->qry->rec_id;
		$qCCC->prm_durum=$durum;
		$qCCC->prm_okuma=$okuma;
		$qCCC->prm_doran=$doran;
		$this->qry->rec_okuma=$okuma;

		$span1="";
		$span2="";
		if($okuma=="-+"){
			$span1="<span style='color:gray'>";
			$span2="</span>";
		}
		$acikla="\n\n$span1\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		if(!empty($durum)  && ($durum !=$this->qry->rec_durum)) $acikla.="\nDurum: $durum_exp";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		$acikla.=$span2;

		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
	function etkin_durum($doran){
		$dur1=-54201;
		    if($doran<0)	$dur1=-54206; // 6. Hedef İptal Edildi
		elseif($doran==0)	$dur1=-54201; // 1. Hedef Henüz Başlamadı
		elseif($doran<100)	$dur1=-54202; // 2. Hedef Devam Ediyor
		elseif($doran>=100)	$dur1=-54203; // 3. Hedef Tamamlandı
		return $dur1;
	}
}
?>