<?php
function connect_Adovfp($strConn){
	$db_link = new COM("ADODB.Connection");
	$db_link->Open($strConn);
	return $db_link;
}
include_once("$REAL_P/_class/data__ado.php");
class clsAdovfp extends cls__ado{
	protected $fro_s="!";
	function get_SETUP_ID($strTable=null){
		$retval=null;
		try{
			$RS=$this->dblink->Execute("Get_NewID('$strTable')");
			$retval=$RS->fields[0]->value;
		}catch(exception $e){
			echo $e->getMessage(),"<br>";
		}
		return $retval;
	}
	function get_AUTO_ID($strTable=null){
		$retval=null;
		try{
			$RS=$this->dblink->Execute("getautoincvalue(0)");
			$retval=$RS->fields[0]->value;
		}catch(exception $e){
			echo $e->getMessage(),"<br>";
		}
		return $retval;
	}
	function tally(){
		$retval=null;
		try{
			$RS=$this->dblink->Execute("Get_tally()");
			$retval=$RS->fields[0]->value;
		}catch(exception $e){
			echo $e->getMessage(),"<br>";
		}
		return $retval;
	}
	function getSqlStr($strWhere=""){
		$sqlStr=parent::getSqlStr($strWhere);
		if($this->sqltype=="select"){
			if($this->_CCC)  $sqlStr.=" into cursor $this->_CCC";
			if($this->_write)$sqlStr.=" readwrite";
		}
		return $sqlStr;
	}
}
?>
