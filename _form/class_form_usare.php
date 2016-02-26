<?php
class class_form_usare extends class_form{
	function afterOpen(){
		$this->oVals=$this->qry->getFldVals("iliski,users");
	}
	function recValid($QRY){
		if($this->islem=="upd"&&$this->oVals->iliski==$QRY->rec_iliski&&$this->oVals->users==$QRY->rec_users)return true;
		$sqlStr="select id from asist!usare where iliski=?prm_iliski and users=?prm_users:I";
		$q1=$this->qry->derive_qry($sqlStr);
		$q1->prm_iliski=$QRY->rec_iliski;
		$q1->prm_users=$QRY->rec_users;
		$q1->open();
		if($this->islem=="ins"&&$q1->reccount){
			$this->msg.="NICHT GESPEICHERT!<br><br>Paylaþým zaten var.";
			return false;
		}
		if($this->islem=="upd"&&$QRY->rec_iliski!=$q1->rec_id){
			$this->msg.="NICHT GESPEICHERT!<br><br>Paylaþým zaten var.";
			return false;
		}
		return true;
	}
}
?>
