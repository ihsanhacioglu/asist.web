<?php
class class_pratik_gorev_durum extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$durum=$this->qry->rec_durum;
		if(isset($_GET["par_durum"]))$durum=$_GET["par_durum"];
		if(is_numeric($durum) && strpos(",,-54101,-54102,-54103,-54104,-54106,",",$durum,"))$qCCC->prm_durum=$durum;
		$dur_str=(abs($this->qry->rec_durum)%54100).">".(abs($durum)%54100);

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$atarih=(isset($_POST["frm_atarih"])?$_POST["frm_atarih"]:"");
		$ctarih=(isset($_POST["frm_ctarih"])?$_POST["frm_ctarih"]:"");
		$perso=(isset($_POST["frm_perso"])?$_POST["frm_perso"]:0);
		$perso_exp=(isset($_POST["frm_perso_exp"])?$_POST["frm_perso_exp"]:"");
		
		$acikla="---------- ".date("d.m.Y H:i")." - $oUser->exp";
		if(!empty($atarih) && $atarih!=$this->qry->rec_atarih)$acikla.="\nATarih: $atarih";
		if(!empty($ctarih) && $ctarih!=$this->qry->rec_ctarih)$acikla.="\nCTarih: $ctarih";
		if(!empty($perso)  && $perso!=$this->qry->rec_perso)  $acikla.="\nPerso: $perso_exp";
		if(!empty($ekacik))$acikla.="\n\n".trim($ekacik);

		$qCCC->prm_acikla=trim($this->qry->rec_acikla)."\n\n$acikla";
		$this->qryExec($qCCC);
		$this->formMessage();
	}
	function form(){
		$durum=isset($_GET["par_durum"]) ? $_GET["par_durum"] : "";
		$this->baslik="Görev Durumu Deðiþtir";
		if($durum==-54102)$this->baslik="Görev Kabûlü";
		if($durum==-54103)$this->baslik="Görev Tamamlandý";
		if($durum==-54104)$this->baslik="Arþivle";
		if($durum==-54106)$this->baslik="Ýptal Et";
		if(!empty($durum))$this->par.="&par_durum=$durum";
		parent::form();
	}
}
?>
