<?php
include_once("$REAL_P/_form/class_util_gorev.php");
class class_pratik_gorev_tamam extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");

		if(empty($ekacik) && substr($this->qry->rec_okuma,0,1)=="x"){
			$durum=-5104; // -5104=Görev Onaylandı
		}else{
			$durum=-5103; // -5103=Görev Tamamlandı
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
}
?>