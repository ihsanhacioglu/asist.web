<?php
class class_form_admin_qry extends class_form{
	function formins(){
		$tic=isset($_GET["tic"])?$_GET["tic"]:"";
		if(!$this->existsTic("ins",$tic)){
			if(isset($_SESSION["sen_id_{$this->senaryo->id}"])){
				$this->islem="edt";
				$this->formedt($_SESSION["sen_id_{$this->senaryo->id}"]);
				return;
			}
			$this->strMessage="Insert ticket does not exist";
			$str="<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"".
				 "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
			$this->usrMessage($str);
			return;
		}
		$this->bindPostVals($this->qry);

		$oDB=connect_datab($this->qry->rec_datab);
		$aa=$this->qry->rec_sqlstr;
		$strClass="cls$oDB->cls";
		$this->qry=new $strClass($oDB->dblink,$aa,null,true);
		$this->qry->open(null,null);

		$objParam=(object)array();
		$objParam->qry 		= $this->qry;
		$objParam->cLink	= "?";
		$objParam->cBaslik	= "SORGU SONUCU";
		$objParam->nSayfa	= 0;

		$objParam->nSayfarec= 0;
		if(($ii=strpos($this->senaryo->snloption,"Say="))!==false){
			$objParam->nSayfarec=substr($this->senaryo->snloption,$ii+4);
			if(isset($_GET["sayfa"]))$objParam->nSayfa=$_GET["sayfa"];else$objParam->nSayfa=1;
		}

		$objParam->cShowFlds="";
		$objParam->aLink=array();

		$this->listele($objParam);
	}
	function formdef(){
		$this->form();
	}
}
?>
