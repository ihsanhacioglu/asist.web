
<?php
class class_form_anketcevap extends class_form{
	public $labelsCreated=false;
	public $anketdty=0;

	function islem($islem=""){
		if (!empty($islem)) $this->islem=$islem;
		if (!$this->islemPerm()){
			echo "ACCESS DENIED";
			return false;
		}
		if		($this->islem=="edt")	$this->formedt();
		elseif	($this->islem=="upd")	$this->formupd();
		elseif	($this->islem=="src")	$this->formsrc();
		elseif	($this->islem=="ara")	$this->formara();
		else	$this->formsel();
	}

	function createCalFlds(){
		global $oUser;

		if ($this->labelsCreated) return;
		$this->labelsCreated=true;
		$this->arrCals=array();

		if ($this->islem!="edt" && $this->islem!="upd") return;

		$this->formObjArr($this->qry->rec_atext);
		foreach($this->arrForm as $objForm)
			if(isset($objForm->soru)) $this->arrCals[$objForm->name]="";
		$qCCC=new clsApp($this->appLink, "select * from asist.anketdty where anket=?prm_Anket and kimlik=?prm_Kimlik");
		$qCCC->prm_Anket=$this->qry->rec_id;
		$qCCC->prm_Kimlik=$oUser->kimlik;
		$qCCC->open();
		$this->anketdty=$qCCC->rec_id;

		$this->formObjArr($qCCC->rec_ctext);
		foreach($this->arrForm as $objForm)
			if (isset($this->arrCals[$objForm->name])) $this->arrCals[$objForm->name]=$objForm->value;
	}
	function bindCalFlds(){
		if ($this->islem!="edt" && $this->islem!="upd") return;
		foreach($this->arrCals as $name=>$value) if(isset($_POST["cal_$name"])){
			$this->arrCals[$name]=$_POST["cal_$name"];
			echo $this->arrCals[$name];
		}
	}
	function beforePost(){
		global $oUser;

		$this->createCalFlds();
		$this->bindCalFlds();

		$cText="";
		foreach($this->arrCals as $name=>$value) $cText.="#name=$name & value=$value\r\n";

		$tAnket=$this->qry->derive_tab("anketdty:auto=1",$this->anketdty);
		$tAnket->rec_id=$this->anketdty;
		$tAnket->rec_ctext=$cText;
		$tAnket->rec_dtarih=date("Y-m-d");
		$tAnket->rec_dsaat=date("H:i:s");

		if ($this->anketdty){
			$tAnket->update();
		}else{
			$tAnket->rec_anket=$this->qry->rec_id;
			$tAnket->rec_kimlik=$oUser->kimlik;
			$tAnket->insert();
		}
	}
	function afterOpen(){
		$this->senaryo->formtemp=$this->qry->rec_atext;
	}
}
?>
