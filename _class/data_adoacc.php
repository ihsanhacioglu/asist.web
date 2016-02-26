<?php
function connect_Adoacc($strConn){
	$db_link = new COM("ADODB.Connection");
	$db_link->Open($strConn);
	return $db_link;
}
include_once("$REAL_P/_class/data__ado.php");
class clsAdoacc extends cls__ado{
	function propOrgName($orgname){
		if(!preg_match("/\s+/",$orgname))return $orgname;
		if(preg_match("/^\[.+\]$/",$orgname))return $orgname;
		return "[$orgname]";
	}
	function setFromsAcc(){
		$strFroms=$this->FromStr;

		$this->arrFroms=array();
		if(preg_match_all("/\s*(\w+)((\s+as)?\s+(\w+))?\s*(,|$)/i",substr($this->FromStr,4),$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$from=empty($match[4]) ? $match[1] : $match[4];
			$oFro=(object)array("from"=>$from,"datab"=>"","orgtable"=>$match[1],"dattab"=>$match[1],"fro_s"=>&$this->fro_s);
			$this->arrFroms[$from]=$oFro;
		}
		elseif(preg_match_all("/(from\s+|join\s+)((\w+)((\s+as)?\s+(\w+))?)(\s+on|\s*$|\s+)/i",$this->FromStr,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$from=empty($match[6]) ? $match[3] : $match[6];
			$oFro=(object)array("from"=>$from,"datab"=>"","orgtable"=>$match[3],"dattab"=>$match[3],"fro_s"=>&$this->fro_s);
			$this->arrFroms[$from]=$oFro;
		}
	}
	function get_SETUP_ID($strTable=null){
		$retval=null;
		$strTable=strtoupper($strTable);
		try{
			$RS=new COM("ADODB.RecordSet");
			$RS->open("select * from setup where exp='$strTable'",$this->dblink,1,2,1);
			$exp=$RS->fields["exp"]->value;$RS->fields["exp"]->value=$exp;
			$newid=$RS->fields["newid"]->value;
			$RS->fields["newid"]->value=++$newid;
			$RS->update();
			$retval=$newid;
		}catch(exception $e){
			echo $e->getMessage(),"<br>";
		}
		return $retval;
	}
	function get_AUTO_ID($strTable=null){
		$retval=null;
		try{
			$RS=$this->dblink->Execute("select @@Identity");
			$retval=$RS->fields[0]->value;
		}catch(exception $e){
			echo $e->getMessage(),"<br>";
			echo $this->dblink->errors[0]->source,":",$this->dblink->errors[0]->description,"<br/>";
		}
		return $retval;
	}
}
?>
