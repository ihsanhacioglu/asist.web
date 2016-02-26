<?php
class class_form_stvanket extends class_form{
	public $labelsCreated=false;
	public $anket=12;

	function create_islem(){
		$this->arrIslem=array("new"=>"I","ins"=>"I");
		$this->add_islem();
	}

	function createCalFlds(){
		if($this->labelsCreated) return;
		$this->labelsCreated=true;
		$this->arrCals=array();

		$this->formObjArr($this->senaryo->formtemp);
		foreach($this->arrForm as $objForm)
			if(isset($objForm->cal)) $this->arrCals[$objForm->name]="";
	}
	function beforePost(){
		$this->createCalFlds();
		$this->bindCalFlds();

		$cText="";
		foreach($this->arrCals as $name=>$value)$cText.="#name=$name & value=$value\r\n";

		$this->qry->rec_anket=$this->anket;
		$this->qry->rec_ctext=$cText;
	}
	function formnew(){
		$kimlik=isset($_GET["kid"])&&is_numeric($_GET["kid"]) ? intval($_GET["kid"]) : 0;
		if(empty($kimlik))$kimlik=isset($_GET["kimlik"])&&is_numeric($_GET["kimlik"]) ? intval($_GET["kimlik"]) : 0;
		$this->qry->rec_kimlik=$kimlik;
		parent::formnew();
	}
	function ins_upd_del_message($sure=0){
		echo "
		<br>Ankete katýldýðýnýz için teþekkürler.<br>
		";
	}
}
?>