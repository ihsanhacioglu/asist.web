<?php
class class_form_seyaht extends class_form{
	function afterPost(){
		if($this->islem=="ins" || $this->oVals->veraus==$this->qry->rec_veraus)return;
		$sqlStr="update asist.seyahtdty
				set miktar=gun*?prm_pausc
				where seyaht=?prm_seyaht and tur='pausc'";
		$qUpd=$this->qry->derive_qry($sqlStr);
		$qUpd->prm_seyaht=$this->qry->rec_id;
		$qUpd->prm_pausc=$this->qry->rec_pausc;
		$qUpd->exec();
	}
	function afterOpen(){
		$this->oVals=$this->qry->getFldVals("veraus");
	}
    function afterDelete(){
		$sqlStr="delete from asist.seyahtdty where seyaht=?prm_seyaht";
		$qDel=$this->qry->derive_qry($sqlStr);
		$qDel->prm_seyaht=$this->qry->rec_id;
        $qDel->exec();
	}
    function recValid($QRY){
		if(!parent::recValid($QRY))return false;
        
		$tar0=date("Y-m-d");
		$tar1=date_format(date_create("$QRY->rec_atarih 00:00"),"Y-m-d");
		$tar2=date_format(date_create("$QRY->rec_ctarih 00:00"),"Y-m-d");
		if($tar1>$tar0 || $tar2>$tar0)
			$ret="Das Anfangsdatum und das Enddatum können spätestens der heutige Tag sein.";
		elseif($tar1>$tar2)
			$ret="Das Anfangsdatum muss vor dem Enddatum liegen.";

		if(!empty($ret)){
			$this->msg="NICHT GESPEICHERT !<br><br>".$ret;
			return false;
		}
		$QRY->rec_atarih=$tar1;
		$QRY->rec_ctarih=$tar2;
		$sqlStr="select id from asist.seyahtdty where seyaht=?prm_seyaht and tur='zone'";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_seyaht=$QRY->rec_id;
		$qCCC->open();

		if($QRY->rec_yer1==$QRY->rec_yer3 && $qCCC->reccount==0)$this->msg="Achten Sie darauf dass Sie mindestens eine Reisezone gespeichert haben.<br><br>";
		return true;
	}
}
?>