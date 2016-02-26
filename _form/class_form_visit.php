<?php
class class_form_visit extends class_form{
	public $arr_ckisi=array();
	function beforePost(){
		$valA=date_format(date_create($this->qry->rec_atarih),"Y-m-d");
		if($valA == date("Y-m-d")){
			if(empty($this->qry->rec_asaat))$this->qry->rec_abc="B";
			elseif($this->qry->rec_asaat < date("H:i")){
				if(empty($this->qry->rec_csaat))$this->qry->rec_abc="A";
				elseif($this->qry->rec_csaat < date("H:i"))$this->qry->rec_abc="C";
				else$this->qry->rec_abc="?";
			}
		}
		elseif($valA > date("Y-m-d")) $this->qry->rec_abc="B";
		elseif($valA < date("Y-m-d")) $this->qry->rec_abc="C";
	}
	function recValid($QRY){
	global $oUser;
		if(!preg_match_all("/\s*(.+?)\s*(,|\r|\n|$)/",$QRY->rec_ckisi,$arr_match,PREG_SET_ORDER))return true;
		if($this->islem!="ins")if(count($arr_match)!=1){
			$this->msg.="Bei der Änderung muss nur 1 Person eingetragen werden.<br>";
			return false;
		}
		if(count($arr_match)>4){
			$this->msg.="Es kann nur 4 Personen zusammen eingetragen werden.<br>";
			return false;
		}
		foreach($arr_match as $match)$this->arr_ckisi[]=$match[1];
		foreach($this->arr_ckisi as $key=>$match){
			$cKisi=$this->qry->to_Indexp($match);
			if((strpos($cKisi,"fatihkok")!==false || strpos($cKisi,"")!==false || strpos($cKisi,"selcukakbulut")!==false || strpos($cKisi,"akbulutselcuk")!==false) && $oUser->id!=1655){
				$this->msg.="Ziyaretçiyi Burhanettin Demir kaydedebilir";
				return false;
			}
		}
		$QRY->rec_ckisi=trim($this->arr_ckisi[0]);
		return true;
	}
	function afterInsert(){
		foreach($this->arr_ckisi as $key=>$match){
			if($key<1)continue;
			$this->qry->rec_ckisi=trim($match);
			$this->qry->insert();
		}
}	}
?>
