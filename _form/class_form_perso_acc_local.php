
<?php
include_once("$REAL_P/_class/data_adoacc.php");
class clsAdoacc_form_perso_acc extends clsAdoacc{
	function get_SETUP_ID($strTable=null){
		$retval=parent::get_SETUP_ID($strTable);
		$retval="PP_".str_pad($retval,3,"0",STR_PAD_LEFT);
		return $retval;
	}
}

class class_form_perso_acc_local extends class_form{
	public $dbClass="clsAdoacc_form_perso_acc";
	public $gstrTPI_KNRlng=14;
	public $gstrTCPServerID="00";

	function afterOpen(){
		$this->oVals=$this->qry->getFldVals("zprofilnummer,kartennummer");
	}
	function beforePost(){
		parent::beforePost();
		if(preg_match("/^(.+)(\s+)(\S+)$/",$this->qry->rec_name,$match)){
			$this->qry->rec_vorname=$match[1];
			$this->qry->rec_nachname=$match[3];
		}else{
			$this->qry->rec_nachname=$this->qry->rec_name;
		}
	}
	function beforeInsert(){
		$this->beforePost();
		$this->qry->rec_pincode=null;
		$this->qry->rec_lastbuch_ort=null;
		$this->qry->rec_datgueltig_von=null;
		$this->qry->rec_datgueltig_bis="31.12.2099";
		$this->qry->rec_uhrgueltig_von=null;
		$this->qry->rec_uhrgueltig_bis=null;

		$this->qry->rec_lastbuch_datum=null;
		$this->qry->rec_lastbuch_uhrzeit=null;
		$this->qry->rec_lastbuch_ort=null;

		$this->qry->rec_lastsend_datum=null;
		$this->qry->rec_lastsend_uhrzeit=null;
		$this->qry->rec_kemas_zpnr=null;
	}
	function afterInsert(){
		$this->addKarte($this->qry->rec_kartennummer,$this->qry->rec_zprofilnummer,
						$this->qry->rec_datgueltig_bis,$this->qry->rec_pincode);
	}
	function afterUpdate(){
		if($this->qry->rec_zprofilnummer!=$this->oVals->zprofilnummer || 
		    $this->qry->rec_kartennummer!=$this->oVals->kartennummer)
			$this->addKarte($this->qry->rec_kartennummer,$this->qry->rec_zprofilnummer,
							$this->qry->rec_datgueltig_bis,$this->qry->rec_pincode);
	}
	function afterDelete(){
		$this->delKarte($this->qry->rec_kartennummer);
	}
	function delKarte($kartnum=null){
		$sqlStr="select ZKM_Index, sub_tcpIP, sub_tmskanalId from tblSubSysteme ZKM";
		$qZKM=new clsAdoacc($this->qry->get_dbLink(),$sqlStr);
		$qZKM->open(null,null);

		$tSend=$this->qry->derive_tab("tblSendData:key=satznr,auto=1");
		$tSend->rec_tx_zkmid="";
		$tSend->rec_tx_typ="00";
		$str_KNR=str_pad($kartnum,$this->gstrTPI_KNRlng,"0",STR_PAD_LEFT);
		while($qZKM->next()){
			$str_DAT=$this->gstrTCPServerID . $qZKM->rec_sub_tmskanalid . "J****!00Y1" . $str_KNR . "**";
			$tSend->rec_tx_data=$str_DAT;
			//$tSend->insert();
			echo "<font face=Courier>$str_DAT</font><br>";

			// aktuelle Uhrzeit senden
			$str_DAT="00$qZKM->rec_sub_tmskanalid"."J****!00U3".date("YmdHisw")."**";
			$tSend->rec_tx_data=$str_DAT;
			$tSend->rec_tx_typ="U3";
			//echo $str_DAT,"<br>";
			//$tSend->insert();
		}
	}
	function addKarte($kartnum=null,$profil=null,$gultig=null,$pincode=null){
		$sqlStr="select ZKM_Index, sub_tcpIP, sub_tmskanalId from tblSubSysteme ZKM";
		$qZKM=new clsAdoacc($this->qry->get_dbLink(),$sqlStr);
		$qZKM->open(null,null);

		$tSend=$this->qry->derive_tab("tblSendData:key=satznr,auto=1");
		$tSend->rec_tx_zkmid="";

		$str_Date=date_format(date_create($gultig),"Ymd");
		$str_KNR=str_pad($kartnum,$this->gstrTPI_KNRlng,"0",STR_PAD_LEFT);
		$str_PIN=str_pad(rtrim($pincode),6,"0",STR_PAD_LEFT);
		$str_PRO=$str_KNR . $profil . $str_Date . $str_PIN;
		while($qZKM->next()){
			$str_DAT=$this->gstrTCPServerID . $qZKM->rec_sub_tmskanalid . "J****!00Y0" . $str_PRO . "**";
			$tSend->rec_tx_data=$str_DAT;
			$tSend->rec_tx_typ="00";
			//$tSend->insert();
			echo "<font face=Courier>$str_DAT</font><br>";

			// aktuelle Uhrzeit senden
			$str_DAT="00$qZKM->rec_sub_tmskanalid"."J****!00U3".date("YmdHisw")."**";
			$tSend->rec_tx_data=$str_DAT;
			$tSend->rec_tx_typ="U3";
			//echo $str_DAT,"<br>";
			//$tSend->insert();
		}
	}
}
?>
