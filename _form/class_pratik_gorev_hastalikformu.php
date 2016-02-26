<?php
class class_pratik_gorev_hastalikformu extends class_pratik{
	function formtam(){
		global $oUser,$oPerso;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$sebep1=(isset($_POST["frm_sebep1"])?$_POST["frm_sebep1"]:"");
		$sebep=(isset($_POST["frm_sebep"])?$_POST["frm_sebep"]:"");

		if($this->qry->rec_perso!=$oPerso->id){
			$acikla="\n\n<span style='color:gray'>\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
			if(!empty($sebep))$acikla.="\nSebep: ".trim($sebep);
			if(!empty($sebep1))$acikla.="\nSebep: ".trim($sebep1);
			if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
			$acikla=$acikla."</span>";
		}else{
			$acikla="\n\n\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
			if(!empty($sebep))$acikla.="\nSebep: ".trim($sebep);
			if(!empty($sebep1))$acikla.="\nSebep: ".trim($sebep1);
			if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		}
		
		$nDurum=-5103;
		$sqlStr="update asist!gorev
				set durum =?prm_durum:I,
					dtarih=?prm_dtarih:D,
					dsaat =?prm_dsaat,
					acikla=?prm_acikla
				where id=?prm_id:I";
		$qCCC=$this->qry->derive_qry($sqlStr,$this->qry);
		$qCCC->prm_id=$this->qry->rec_id;
		$qCCC->prm_durum=$nDurum;
		$qCCC->prm_dtarih=$this->objVal("bugun");
		$qCCC->prm_dsaat =$this->objVal("saat");
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$qCCC->exec();
		$this->formMessage();
	}
}
?>