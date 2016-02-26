<?php
class class_form_perso_gorev_diag extends class_form{
	function create_qry($strClass=null){
		if($this->islem!="sel" && !empty($this->islem)){
			$sqlStr=$this->senaryo->sqlstr;
			$lisStr=$this->senaryo->listtemp;
			$sqlStr=str_replace("\$CAL_FLDS","",$sqlStr);
			$lisStr=str_replace("\$CAL_COLS","",$lisStr);
			$lisStr=str_replace("\$CAL_HEDS","",$lisStr);
			$this->senaryo->sqlstr=$sqlStr;
			$this->senaryo->listtemp=$lisStr;
			parent::create_qry();
			return;
		}

		$ay_sonu=date("d",strtotime(date("Y-m-1")." +1 month -1 day"));

		$strFld="";		$strCol="";		$strHed="";
		for($ii=1;$ii<=$ay_sonu;$ii++){
			$cFld=sprintf("f%02d",$ii);
			$strFld.=",'0' $cFld";
			$strCol.="#C attrib=class='bg_\$QQ->rec_$cFld' #type=DAT & name=$cFld";
			$strHed.="#C #type=CBL & caption=$cFld";
		}
		$sqlStr=$this->senaryo->sqlstr;
		$sqlStr=str_replace("\$CAL_FLDS",$strFld,$sqlStr);

		$lisStr=$this->senaryo->listtemp;
		$lisStr=str_replace("\$CAL_COLS",$strCol,$lisStr);
		$lisStr=str_replace("\$CAL_HEDS",$strHed,$lisStr);

		$this->senaryo->sqlstr=$sqlStr;
		$this->senaryo->listtemp=$lisStr;

		parent::create_qry();
		$this->qry->_write=1;
		$this->qry->_CCC=CUR_II($this->senaryo->datab);
	}
	function afterOpen(){
		if($this->islem!="sel" && !empty($this->islem))return;

		$cFld="";
		for($ii=1;$ii<=100;$ii++){
			$cFld=sprintf("f%02d",$ii);
			if(!isset($this->qry->{"fld_$cFld"}))break;
			$strSql="update {$this->qry->_CCC} set $cFld=iif(between(?prm_tar1:D,atarih,ctarih) or
												   between(?prm_tar2:D,atarih,ctarih) or
												   between(atarih,?prm_tar1:D,?prm_tar2:D) or
												   between(ctarih,?prm_tar1:D,?prm_tar2:D),abc,'')";
			$qUpd=$this->qry->derive_qry($strSql);
			$qUpd->prm_tar1=date("Y-m-$ii");
			$qUpd->prm_tar2=date("Y-m-$ii");
			$qUpd->exec();
		}
		$QRY=$this->qry->derive_qry("select * from {$this->qry->_CCC}");
		$QRY->open(null,null);
		$this->qry=$QRY;
	}
}
?>