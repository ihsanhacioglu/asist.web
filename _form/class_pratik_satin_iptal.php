<?php
class class_pratik_satin_iptal extends class_pratik{
	function formpra(){
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);
		if($this->qry->rec_onay_durum==-21506){
			$this->strMessage="Verkauf Antrag ist schon von Ihnen beendet worden.";
			$this->msgMessage(5);
			return;
		}
		parent::formpra();
	}
	function formtam(){
		$this->qry->close();
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$durum=-21506; // Kabul edilmedi
		$qCCC->prm_durum=$durum;
		if(!$this->qryExec($qCCC)){
			$this->formMessage();
			return;
		}

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

		$sqlStr="select iliski,
						count(*) adet,
						sum(iif(durum=-21501,1,0)) onaylanacak,
						sum(iif(durum=-21502,1,0)) islenecek,
						sum(iif(durum=-21503,1,0)) onaylandi,
						sum(iif(durum=-21504,1,0)) duzeltilecek,
						sum(iif(durum=-21505,1,0)) islendi,
						sum(iif(durum=-21506,1,0)) kabuledilmedi,
						sum(iif(durum=-21507,1,0)) beklemede,
						sum(iif(durum=-21508,1,0)) gecersiz
					from asist!onay where iliski=?prm_iliski
					group by 1";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_iliski=$iliski;
		$qCCC->open();
		
		$nDurum=-1;
		if	  ($qCCC->rec_kabuledilmedi>0)	$nDurum=-21506;
		elseif($qCCC->rec_duzeltilecek>0)	$nDurum=-21504;
		elseif($qCCC->rec_onaylanacak>0)	$nDurum=-21501;
		else								$nDurum=-21502;

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
				set durum =?prm_durum:I,
					dtarih=?prm_dtarih:D,
					dsaat =?prm_dsaat,
					acikla=?prm_acikla
				where id=?prm_id:I and durum=-21501";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_durum =$nDurum;
		$qCCC->prm_dtarih=$this->objVal("bugun");
		$qCCC->prm_dsaat =$this->objVal("saat");
		$qCCC->prm_id=$this->qry->rec_id;
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$qCCC->exec();
		$this->formMessage();
	}
}
?>