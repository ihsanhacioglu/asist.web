<?php
class class_pratik_kartprofil extends class_pratik{
	public $gstrTPI_KNRlng=14;
	public $gstrTCPServerID="00";

	function formtam(){
		$this->qry->close();
		$this->qry->keyopen($this->id);

		//ZProfilnummer,Kartennummer,DATGueltig_bis,Pincode
		$sqlStr="select Sub_TMSKanalID, ZKM_ID,ZKM_aktTPIStatus,ZKM_aktTCPStatus
				from tblSubSysteme sub, tblZKMStatus stat
				where sub.ZKM_Index=stat.ZKM_Index and stat.ZKM_Index='0004'";
		$qZKM=new clsAdoacc($this->qry->get_dbLink(),$sqlStr);
		$qZKM->open(null,null);

		$tSend=$this->qry->derive_tab("tblSendData:key=satznr,auto=1");
		$tSend->rec_tx_zkmid="";

		echo $this->qry->rec_datgueltig_bis,"<br>";
		$str_Date=$this->qry->rec_datgueltig_bis;
		$str_Date=date_format(date_create($this->qry->rec_datgueltig_bis),"Ymd");
		echo $str_Date,"<br>";
		
		$str_KNR=str_pad($this->qry->rec_kartennummer,$this->gstrTPI_KNRlng,"0",STR_PAD_LEFT);
		$str_ProData=$str_KNR.$this->qry->rec_zprofilnummer.$str_Date.rtrim($this->qry->rec_pincode);
		//$str_ProData=$str_KNR."100".$str_Date.rtrim($this->qry->rec_pincode);
		while($qZKM->next()){
			// Sil
			//$str_SendData = $this->gstrTCPServerID . $qZKM->rec_zkm_id . "J****!00Y1" . $str_KNR . "**";
			// Ekle
			  $str_SendData = $this->gstrTCPServerID . $qZKM->rec_zkm_id . "J****!00Y0" . $str_ProData . "**";
			echo $str_SendData,"<br>";
			$tSend->rec_tx_data=$str_SendData;
			$tSend->rec_tx_typ="00";
			//$tSend->insert();
			//$this->SendData($str_SendData, "00");

			// aktuelle Uhrzeit senden
			$str_SendData = $this->build_datetime($qZKM->rec_zkm_id);
			$tSend->rec_tx_data=$str_SendData;
			$tSend->rec_tx_typ="U3";
			//$tSend->insert();
			echo $str_SendData,"<br>";
		}
		return;
		
		$qCCC=new $strClass($this->qry->dblink,$this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$this->qryExec($qCCC);
		$this->formMessage();
	}
	function build_Y01($QQ){
		$str_KNR=str_pad($QQ->rec_kartennummer,$this->gstrTPI_KNRlng,"0",STR_PAD_LEFT);
		$str_Gultig=date("Ymd",$QQ->DATGueltig_bis);
		return "$str_KNR$QQ->ZProfilnummer$str_Gultig".rtrim($QQ->Pincode)."**";
	}
	function build_datetime($sACMID){
		$str_DT=date("YmdHisw");
		return "00$sACMID"."J****!00U3$str_DT**";
	}
}



?>