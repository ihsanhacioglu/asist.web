<?php
class class_form_seyahtdty extends class_form{
	function beforePost(){
		parent::beforePost();
		if($this->qry->rec_tur!="zone"){
			$this->qry->rec_saat=null;
			$this->qry->rec_veraus=null;
		}
		$km_frm=strtotime("{$this->qry->rec_tarih}");
		$km_025=strtotime("2013-03-01");
		if($this->qry->rec_tur=="fahrt"){
			if($km_frm<$km_025)$this->qry->rec_miktar=$this->qry->rec_km*0.2;
			else$this->qry->rec_miktar=$this->qry->rec_km*0.25;
		}else$this->qry->rec_km=null;
		if($this->qry->rec_tur=="pausc"){
			$sqlStr="select pausc
					from asist.seyaht, asist.s_veraus veraus
					where seyaht.veraus=veraus.id and seyaht.id=?prm_seyaht";
			$qCCC=$this->qry->derive_qry($sqlStr);
			$qCCC->prm_seyaht=$this->qry->rec_seyaht;
			$qCCC->open();
			$this->qry->rec_miktar=$this->qry->rec_gun*$qCCC->rec_pausc;
		}elseif(!strpos(",,otel1,otel2,otel3,","{$this->qry->rec_tur},"))$this->qry->rec_gun=null;
	}
	function recValid($QRY){
		if(strpos(",,taxii,neben,otel1,otel2,otel3,","{$QRY->rec_tur},") && empty($QRY->rec_miktar)){
			$this->msg.="Betrag muss eingetragen werden<br>";
			return false;
		}elseif(strpos(",,pausc,otel1,otel2,otel3,","{$QRY->rec_tur},") && empty($QRY->rec_gun)){
			$this->msg.="Übernactungstage müssen eingetragen werden<br>";
			return false;
		}elseif($QRY->rec_tur=="fahrt" && empty($QRY->rec_km)){
			$this->msg.="Fahrt KM muss eingetragen werden<br>";
			return false;
		}elseif($QRY->rec_tur=="zone" && empty($QRY->rec_veraus)){
			$this->msg.="Reise-Zone muss eingetragen werden<br>";
			return false;
		}elseif($QRY->rec_tur=="zone" && empty($QRY->rec_saat)){
			$this->msg.="Reise-Zone Anfangsuhr muss eingetragen werden<br>";
			return false;
		}
		return true;
	}
}
?>
