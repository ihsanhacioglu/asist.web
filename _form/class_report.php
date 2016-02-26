<?php

class class_report extends class__base{
	public $dest="";
	public $file="";

	function __construct($nLink, $cAction){
		global $oUser;

		if (empty($nLink)) return false;
		$this->appLink=$nLink;
		
		$cDil=strtolower($oUser->dilse);
		if($cDil!="de" && $cDil!="en") $cDil="tr";
		$this->dil=$cDil;
		if(!empty($this->act))$cAction=$this->act;
		if (!($this->senaryo=$this->getSenaryo($cAction))){
			echo "$cAction<br>ACCESS DENIED";
			return false;
		}

		$this->id =isset($_GET["id"])  ? $_GET["id"] :0; $this->id =empty($this->id)  ? 0 : $this->id;
		$this->islem=isset($_GET["islem"]) ? $_GET["islem"] : "";
		$this->mod=isset($_GET["mod"]);

		$this->create_qry();
		//$this->qry=new clsApp($this->appLink, $this->senaryo->sqlstr);
		//$this->qry->senaryo=$this->senaryo->id;
		//$this->qry->open("1=0");
	}

	function getSenaryo($cAction){
		global $oUser;

		$tabSayfa="sayfa_$this->dil";
		$sqlStr="select sen.id,
					sen.exp,
					sen.action,
					sen.datab,
					sen.updtables,
					sen.sqlstr,
					sen.listtemp,
					sen.color,
					sen.tur,
					sen.findtemp filtvalues
				from asist.$tabSayfa sen
				where sen.action='$cAction'";
	    $res    = mysqli_query($this->appLink, $sqlStr);
	    $oRec   = mysqli_fetch_object($res);
		mysqli_free_result($res);
	    return $oRec;
	}

	function islem(){
		$this->formrep();
	}

	function formrep(){
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);
		$this->pdf();
	}
	function pdf(){
	}
}
?>