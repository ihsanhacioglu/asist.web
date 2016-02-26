<?php
//include_once("$REAL_P/_form/class_util_gorev.php");
class class_pratik_gorev_faturayionayla  extends class_pratik{
	public $SATIN_MSG="";
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$cal_gorev=(isset($_POST["cal_gorev"])?$_POST["cal_gorev"]:"");
		$cal_gorev_exp=(isset($_POST["cal_gorev_exp"])?$_POST["cal_gorev_exp"]:"");

		if(!empty($cal_gorev_exp) && empty($cal_gorev)){
			$this->SATIN_MSG="Uyarý: Fatura onayý için gerekli satýnalma görevini seçmediniz. Tekrar deneyiniz.<br><br>";
			$this->islem="prm";
			parent::form();
			return;
		}

		if(substr($this->qry->rec_exp,0,8)=="Fatura:+" && empty($cal_gorev)){
			$this->SATIN_MSG="Uyarý: Bu fatura sadece onaylanmýþ bir satýnlama görev kaydý seçilerek onaylanabilir!<br><br>";
			$this->islem="prm";
			parent::form();
			return;
		}

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");

		if(empty($ekacik) && substr($this->qry->rec_okuma,0,1)=="x"){
			$durum=-5104; // -5104=Görev Onaylandý
		}else{
			$durum=-5103; // -5103=Görev Tamamlandý
		}

		if($cal_gorev){
			$ekacik.="<br>Ýlgili Satýn Alma Görevi: <a href='?form_perso_gorev&mod=&islem=edt&id=$cal_gorev'>$cal_gorev_exp ($cal_gorev)</a>";
			$qUpd=$this->qry->derive_qry("update asist!gorev set relata=?prm_relata,relati=?prm_relati where id=?prm_gorev");
			$qUpd->prm_relata=$this->qry->rec_relata;
			$qUpd->prm_relati=$this->qry->rec_relati;
			$qUpd->prm_gorev =$cal_gorev;
			$qUpd->exec();
		}

		$acikla="\n\n<span style='color:gray'>\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		$durum_exp=$this->table_exp('durum_tr',$durum);
		$acikla.="\nDurum: $durum_exp";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		$acikla=$acikla."</span>";

		$qCCC->prm_durum=$durum;
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
	function form(){
		if(substr($this->qry->rec_exp,0,8)=="Fatura:+" && empty($cal_gorev)){
			$this->SATIN_MSG="*Zorunlu Alan";
		}else{
			$this->SATIN_MSG="*Ýsteðe Baðlý";
		}

		parent::form();
	}
}
?>