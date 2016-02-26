<?php
class class_form_seyahtdty_zone extends class_form{
	function beforePost(){
		parent::beforePost();
		$this->qry->rec_tur="zone";
	}
	function recValid($QRY){
		if(!parent::recValid($QRY))return false;

		if(empty($QRY->rec_veraus)){
			$this->msg.="Zone müssen eingetragen werden.<br>";
			return false;
		}

		$sqlStr="select atarih,ctarih from asist.seyaht where id=?prm_id";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_id=$this->qry->rec_seyaht;
		$qCCC->open();
		$tar0=date("Y-m-d");
		$tarZ=date_format(date_create("$QRY->rec_tarih 00:00"),"Y-m-d");
		$tar1=date_format(date_create("$qCCC->rec_atarih 00:00"),"Y-m-d");
		$tar2=date_format(date_create("$qCCC->rec_ctarih 00:00"),"Y-m-d");
		if($tarZ>$tar0)
			$ret="Das Einreisedatum kann spätestens der heutige Tag sein.";
		elseif($tarZ<$tar1 || $tarZ>$tar2)
			$ret="Das Einreisedatum der Grenzzone muss zwischen der Reiseanfangsdatum und Enddatum liegen.";

		if(!empty($ret)){
			$this->msg="NICHT GESPEICHERT !<br><br>".$ret;
			return false;
		}
		$QRY->rec_tarih=$tarZ;
		return true;
	}

	function afterPost(){$this->updateYer2();}
	function afterDelete(){$this->updateYer2();}

	function updateYer2(){
		$sqlStr="select acikla yer2 from asist.seyahtdty where seyaht=?prm_seyaht and tur='zone' order by tarih,saat";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_seyaht=$this->qry->rec_seyaht;
		$qCCC->open(null,null);
		$yer2="";
		while($qCCC->next())$yer2.=",$qCCC->rec_yer2";
		$yer2=substr($yer2,1);

		$sqlStr="update asist.seyaht set yer2=?prm_yer2 where id=?prm_seyaht";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_yer2=$yer2;
		$qCCC->prm_seyaht=$this->qry->rec_seyaht;
		$qCCC->exec();
	}
}
?>
