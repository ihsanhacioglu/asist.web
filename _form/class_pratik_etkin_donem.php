 <?php
class class_pratik_etkin_donem extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$okuma="";
		if($this->qry->rec_aperso==$this->qry->rec_perso) // kendi
			$okuma="++";
		elseif($oUser->perso==$this->qry->rec_aperso) // verilen
			$okuma="+-";
		elseif($oUser->perso==$this->qry->rec_perso) // alınan
			$okuma="--";

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$atarih=(isset($_POST["frm_atarih"])?$_POST["frm_atarih"]:$this->qry->rec_atarih);
		$ctarih=(isset($_POST["frm_ctarih"])?$_POST["frm_ctarih"]:$this->qry->rec_ctarih);

		$span1="";
		$span2="";
		if($okuma=="-+"){
			$span1="<span style='color:gray'>";
			$span2="</span>";
		}
		$acikla="\n\n$span1\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
		if(!empty($atarih) && ($atarih!=$this->qry->rec_atarih))$acikla.="\nATarih: $atarih";
		if(!empty($ctarih) && ($ctarih!=$this->qry->rec_ctarih))$acikla.="\nCTarih: $ctarih";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		$acikla.=$span2;

		$qCCC->prm_durum=$this->qry->rec_durum;
		$qCCC->prm_okuma=$okuma;
		$qCCC->prm_atarih=$atarih;
		$qCCC->prm_ctarih=$ctarih;

		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>