<?php
class class_detay extends class__base{
	function __construct($nLink, $cAction, $name, $main=null){
		global $oUser;

		if(empty($nLink)) return false;
		$this->appLink=$nLink;
		$this->name=$name;
		$this->main=$main;

		$cDil=strtolower($oUser->dilse);
		if($cDil!="de" && $cDil!="en") $cDil="tr";
		$this->dil=$cDil;
		if (!($this->senaryo=$this->getSenaryo($cAction))){
			echo "$cAction<br>ACCESS DENIED---";
			return false;
		}
	}

	function getSenaryo($cAction){
		global $oUser;

		$tabSayfa="sayfa_$this->dil";
		$sqlStr="select role.once,
					sen.id,
					sen.exp,
					sen.action,
					sen.sqlstr,
					sen.ustmenu,
					sen.altmenu,
					sen.updtables,
					sen.formtemp,
					sen.findtemp filtvalues,
					sen.gridtemp defvalues,
					sen.listtemp,
					sen.linkvalues,
					sen.reqfld,
					sen.intfld,
					sen.deffld,
					sen.detay,
					sen.grid,
					sen.color,
					sen.tur,
					sen.datab,
					sen.snlmodal,
					sen.relmodal,
					senrole.defvalues def1,
					senrole.filtvalues filt1,
					senrole.filtrele,
					senrole.listfld,
					senrole.readfld,
					senrole.options
				from asist.$tabSayfa sen, asist.senrole, asist.role
				where sen.action='$cAction'
					and sen.tur in('detay')
					and sen.id=senrole.senaryo
					and senrole.role=role.id
					and $oUser->where
				order by 1";
		$res=mysqli_query($this->appLink, $sqlStr);
		$oSen=mysqli_fetch_object($res);
		mysqli_free_result($res);
	    return $oSen;
	}

	function create_Qry($strClass=null){
		$oDB=connect_datab($this->senaryo->datab);
		if(empty($strClass))$strClass="cls$oDB->cls";
		$this->qry=new $strClass($oDB->dblink, $this->senaryo->sqlstr, $this->main,true);

		$this->qry->senaryo=$this->senaryo->id;
		$this->qry->open("1=0");
		if(!empty($this->senaryo->updtables))$this->qry->setKeyQry($this->senaryo->updtables);
		else{
			$akey=array_keys($this->qry->arrFroms);
			$this->qry->setKeyQry("$akey[0]:key=id");
		} 
	}

	function create_Det($strClass=null){
		$this->create_Qry($strClass);
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");

		$cFormStr=$this->senaryo->formtemp;
		if(empty($cFormStr)) $cFormStr=$this->defGridTemp($this->qry);
		if(!$this->formObjArr($cFormStr)) return false;

		$cls2="";
		$renk=null;
		if (isset($this->qry->fld_abc)) $renk=$this->qry->fld_abc;
		if (isset($this->qry->fld_tipi)){
			if (!$renk) $renk=$this->qry->fld_tipi;
			elseif ($this->qry->fld_abc->order < $this->qry->fld_tipi->order) $renk=$this->qry->fld_abc;
			else $renk=$this->qry->fld_tipi;
		}
		if($renk)$cls2=" {$renk->name}_{\$renk->value}";
		$this->createEval();
	}
	function setQryUpdates(){
		$this->qry->intFld=$this->senaryo->intfld;
		$this->qry->reqFld=$this->senaryo->reqfld;
		$this->qry->readFld=$this->senaryo->readfld;
		$this->qry->setUpdates($this->senaryo->updtables);
	}

	function bindPostVals($DD,$ii=0){
		foreach($DD->arrFields as $oFld){
			$post_name="det{$DD->name}_{$ii}_frm_$oFld->name";
			if(!isset($_POST[$post_name]))continue;

			if(empty($_POST[$post_name]))
				$oFld->value=$oFld->emptyval;
			elseif(strpos(",S",$oFld->char))
				$oFld->value=substr($_POST[$post_name],0,$oFld->length);
			else $oFld->value=$_POST[$post_name];
		}
	}
	function bindGetVals($DD,$ii=0){
		foreach($DD->arrFields as $oFld){
			$get_name="det{$DD->name}_{$ii}_frm_$oFld->name";
			if(!isset($_GET[$get_name]))continue;

			if(empty($_GET[$get_name]))
				$oFld->value=$oFld->emptyval;
			elseif(strpos(",S",$oFld->char))
				$oFld->value=substr($_GET[$get_name],0,$oFld->length);
			else $oFld->value=$_GET[$get_name];
		}
	}
	function defValid($QRY){
		if($this->islem=="upd" && !$QRY->keyChg && $QRY->id!=$QRY->keyQry->value){
			$this->msg.="Key field cannot be changed ({$QRY->id}->{$QRY->keyQry->value})<br>";
			$QRY->keyQry->value=$QRY->id;
			return false;
		}
		if(!$this->recValid($QRY))return false;
		return true;
	}
	function recValid($QRY){return true;}

	function afterOpen(){}
	function beforePost(){
		if (isset($this->qry->fld_abc,$this->qry->fld_atarih,$this->qry->fld_ctarih)
			&& (empty($this->qry->rec_abc) || strpos(",,A,B,C,?,",",".trim($this->qry->rec_abc).",")))
			if    (empty($this->qry->rec_atarih)) $this->qry->rec_abc="?";
			elseif(empty($this->qry->rec_ctarih)) $this->qry->rec_abc="A";
			elseif(empty($this->qry->rec_atarih)) $this->qry->rec_abc="B";
			elseif($this->qry->rec_ctarih < date("Y-m-d")) $this->qry->rec_abc="C";
			elseif($this->qry->rec_ctarih >=date("Y-m-d")) $this->qry->rec_abc="A";
	}
	function afterPost(){}

	function beforeInsert(){$this->beforePost();}
	function beforeUpdate(){$this->beforePost();}

	function afterInsert(){$this->afterPost();}
	function afterUpdate(){$this->afterPost();}

	function beforeDelete(){}
	function afterDelete(){}

	function createCalFlds(){}
	function bindCalFlds($DD,$ii=0){
		foreach($this->arrCals as $name=>$val){
			$cal_name="cal{$DD->name}_{$ii}_$name";
			if(!isset($_POST[$cal_name]))continue;
			$this->arrCals[$name]=$_POST[$cal_name];
		}
	}

	function formDetay(){
		global $cKare, $cList;

		$QQ=$this->qry;
		$oQry=$QQ;
		$this->refWhere($QQ);
		if(count($QQ->arrWhere)==0)return;
		$QQ->close(); $QQ->open(null,null);

		$color="";
		if(!empty($this->senaryo->color))$color=" style='background-color:#{$this->senaryo->color};'";

		if(!empty($this->msg))echo "$this->msg";
		$this->pre="";
		$pre="det$this->name";
		echo "\n<div id='$pre'$color>\n";
		foreach($this->arrEval as $key=>$str_eval){
			if($key==="REPEAT"){
				$nn=0;
				$str_eval="echo \"".$str_eval."\n\";";
				while($QQ->next()){
					$nn++;
					$this->pre="{$pre}_{$nn}_";
					eval($str_eval);
				}
			}else{
				eval("echo \"".$str_eval."\";");
			}
		}
		echo "</div>\n";
	}
}
?>