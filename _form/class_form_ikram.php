<?php
class class_form_ikram extends class_form{
	function beforePost(){
		parent::beforePost();
		$this->qry->rec_net=$this->qry->rec_miktar - $this->qry->rec_kdv;
	}
	function __recValid($QRY){
		if(!preg_match_all("/\s*(.+?)\s*(\r|\n|$)/",$QRY->rec_kisiler,$arr_match,PREG_SET_ORDER))return true;
		foreach($arr_match as $match)if(!preg_match_all("/(.+?)(,|$)/",$match[1],$m1,PREG_SET_ORDER)||count($m1)!=3){
			$this->msg.="NICHT GESPEICHERT!<br><br>Herbir satýra kiþi adý,þirketi,görevi ile birlikte girilmeli.";
			return false;
		}
		return true;
	}
}
?>
