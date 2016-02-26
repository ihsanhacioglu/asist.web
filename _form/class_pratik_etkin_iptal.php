 <?php
class class_pratik_etkin_iptal extends class_pratik{
	function formtam(){
		global $oUser;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$doran=(isset($_POST["frm_doran"])?$_POST["frm_doran"]:$this->qry->rec_doran);

		$durum=-54206;
		$durum_exp=$this->table_exp('kume_tr',$durum);

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
		if(!empty($durum)  && ($durum !=$this->qry->rec_durum)) $acikla.="\nDurum: $durum_exp";
		if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		$acikla.=$span2;

		$qCCC->prm_durum=$durum;
		$qCCC->prm_okuma=$okuma;
		$qCCC->prm_doran=-1;

		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$this->qryExec($qCCC);
		$this->formMessage();
	}
}
?>