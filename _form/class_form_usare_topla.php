<?php
include_once("$REAL_P/_form/class_form_topla.php");
class class_form_usare_topla extends class_form_topla{
    function recValid($QRY){
		if(!parent::recValid(null))return false;
		$d1=strtotime("{$this->qry->rec_atarih} {$this->qry->rec_asaat}");
		$d2=time();
		$fs=($d1/3600)-($d2/3600);
		if($fs<24)$this->msg.="Toplant� odas� tahsis edildi.<br><br>�KRAM H�ZMET� ���N ��LETME SERV�S� �LE G�R���N�Z.<br>";
		return true;
	}
}
?>
