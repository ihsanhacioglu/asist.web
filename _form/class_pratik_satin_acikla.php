<?php
class class_pratik_satin_acikla extends class_pratik{

	function formtam(){
		global $oPerso,$oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$iliski="satin-".$this->qry->rec_id;
		$sqlStr="update asist!onay
				set okuma='+',
					dtarih=?prm_dtarih:D,
					dsaat =?prm_dsaat
				where iliski=?prm_iliski and perso=?prm_perso";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_dtarih=$this->objVal("bugun");
		$qCCC->prm_dsaat =$this->objVal("saat");
		$qCCC->prm_iliski=$iliski;
		$qCCC->prm_perso =$oPerso->id;
		$qCCC->exec();

		$sqlStr="update asist!onay
				set okuma='-',
					dtarih=?prm_dtarih:D,
					dsaat =?prm_dsaat
				where iliski=?prm_iliski and perso<>?prm_perso";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_dtarih=$this->objVal("bugun");
		$qCCC->prm_dsaat =$this->objVal("saat");
		$qCCC->prm_iliski=$iliski;
		$qCCC->prm_perso =$oPerso->id;
		$qCCC->exec();


		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		if($this->qry->rec_perso!=$oPerso->id){
			$acikla="\n\n<span style='color:gray'>\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
			if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
			$acikla=$acikla."</span>";
		}else{
			$acikla="\n\n\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
			if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		}

		$sqlStr="update asist!satin
				set dtarih=?prm_dtarih:D,
					dsaat =?prm_dsaat,
					acikla=?prm_acikla
				where id=?prm_id:I";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_dtarih=$this->objVal("bugun");
		$qCCC->prm_dsaat =$this->objVal("saat");
		$qCCC->prm_id=$this->qry->rec_id;
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$qCCC->exec();
		$this->formMessage();
	}
}
?>