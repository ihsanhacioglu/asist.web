<?php
function connect_Adovfp($strConn){
	$cDblink = new COM("ADODB.Connection");
	$cDblink->Open($strConn);
	return $cDblink;
}

include_once("$REAL_P/_class/data__ado.php");
class clsAdovfp extends cls__qry{
	function bind_param($varWhere=null){
		$this->oCmd=new COM("ADODB.Command");
		$this->oCmd->activeConnection=$this->dbLink;
		$nn=0;

		if (count($this->arrParams)){
			foreach($this->arrParams as $par){
				$prm_name="prm_".$par->name;
				$pType=$this->char_type($par->type);
				$oPar=$this->oCmd->createParameter("p$nn++",$pType,1,-1);

				$oPar->value=$this->$prm_name;
				$this->oCmd->parameters->append($oPar);
			}
		}
		$arrCond=null; $addWhere="";
		if     (is_string($varWhere)) $addWhere=$varWhere;
		elseif (is_object($varWhere)) $arrCond[]=$varWhere;
		elseif (is_array ($varWhere)) $arrCond=&$varWhere;
		
		$WHRFLT[]=&$this->arrWhere; $WHRFLT[]=&$arrCond;
		foreach($WHRFLT as $arrWhrFlt){
			if(!is_array($arrWhrFlt) || !count($arrWhrFlt)) continue;

			$addWhere=empty($addWhere)?"":" and $addWhere";
			foreach ($arrWhrFlt as $whr){
				$opr=$whr->opr;

				if($opr==">>")		$oprWhr=">>";
				elseif($opr=="~")	$oprWhr="like";
				elseif($opr=="==")	$oprWhr="=";
				else				$oprWhr=$opr;

				$cnt=0;
				if(preg_match_all("/\s*(!(.+)|(.+))\s*(,|$)/U",$whr->value,$vals,PREG_SET_ORDER))
				foreach ($vals as $vm){
					if(empty($vm[1]))continue;
					$cnt++;
					if($opr=="~")$whr->{"val$cnt"}=str_replace("*","%","$vm[2]$vm[3]%");
					else$whr->{"val$cnt"}="$vm[2]$vm[3]";
					$whr->{"not$cnt"}=empty($vm[3]);
				}
				if($cnt==0)continue;
				if($opr==">>" && $cnt!=2) continue;

				$orWhere="";
				for($ii=1;$ii<=$cnt;$ii++){
					if(isset($whr->fld))$pType=$whr->fld->type;
					elseif(is_numeric($whr->{"val$ii"}))$pType=139;
					else$pType=200;
					
					//echo "$whr->fromfld: $whr->value: ",$whr->{"val$ii"},"<br>";
					
					$oPar=$this->oCmd->createParameter("p$nn++",$pType,1,-1);
					$oPar->value=$whr->{"val$ii"};
					$this->oCmd->parameters->append($oPar);

					$not=$whr->{"not$ii"}?"not ":"";
					$orWhere.=" or $not$whr->fromfld $oprWhr ?";
				}
				$orWhere=substr($orWhere,4);

				if($opr==">>") $addWhere .= " and ($whr->fromfld between ? and ?)";
				else$addWhere .= " and ($orWhere)";
			}
			$addWhere=substr($addWhere,5);
		}
		return $addWhere;
	}
	function open($varWhere=null,$first=true){
		if($this->active) return false;

		$addWhere=$this->bind_param($varWhere);
		$sqlStr=$this->getSqlStr($addWhere);
		//echo "WHR: $addWhere<br>";
		//echo "SQL: $sqlStr<br>";

		if (!empty($this->temp)){
			$sqlStr="create table temp.$this->temp as ".$sqlStr;
			$_SESSION["tmptables"][]=$this->temp;
		}

		$this->oCmd->commandText=$sqlStr;
		$this->stmt=new COM("ADODB.RecordSet");
		$this->stmt->cursorType=3;
		try{$this->stmt->open($this->oCmd);}catch(exception $e){
			echo $e->getMessage(),"<br>";
			echo $this->dbLink->errors[0]->source,":",$this->dbLink->errors[0]->description,"<br/>";
			return false;
		}

		if (!empty($this->temp)){
			$sqlStr="select * from temp.$this->temp";
			$oCmd=new COM("ADODB.Command");
			$oCmd->activeConnection=$this->dbLink;
			$oCmd->commandText=$sqlStr;
			try{$this->stmt=$oCmd->execute();}catch(exception $e){
				echo $e->getMessage(),"<br>";
				echo $this->dbLink->errors[0]->source,":",$this->dbLink->errors[0]->description,"<br/>";
				return false;
			}
		}
		$this->reccount=$this->stmt->recordCount;
		$this->active=true;
		$this->setInfo();
		if($this->reccount)if($first)$this->first();else $this->stmt->MovePrevious();
		return true;
	}
	
	function openTemp($next=true){
		if (!$this->active) return false;
		if (empty($this->temp)) return false;
		$this->close();
		$this->info=false;

		$sqlStr="select * from temp.$this->temp";
		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dbLink;
		$oCmd->commandText=$sqlStr;
		try{$this->stmt=$oCmd->execute();}catch(exception $e){
			echo $e->getMessage(),"<br>";
			echo $this->dbLink->errors[0]->source,":",$this->dbLink->errors[0]->description,"<br/>";
			return false;
		}
		$this->reccount=$this->stmt->recordCount;
		$this->active=true;
		$this->setInfo();
		if ($next) $this->next();
		return true;
	}

	function getSqlStr($strWhere=""){
		if (empty($strWhere)) return $this->strSql;

		$strWhere=empty($this->WhereStr) ? "where $strWhere" : "$this->WhereStr and $strWhere";
		$strGroup=empty($this->GroupStr) ? "" : " $this->GroupStr";
		$strOrder=empty($this->OrderStr) ? "" : " $this->OrderStr";
		$sqlStr="$this->FieldStr $this->FromStr $strWhere$strGroup$strOrder";
		return $sqlStr;
	}

	function close($dropTemp=false){
		if ($this->active) $this->stmt->close();
		if ($dropTemp && !empty($this->temp)){
			$res=$this->dbLink->execute("drop table temp.$this->temp");
			$this->temp="";
		}
		$this->active=false;
		$this->reccount=0;
	}

	function setFroms($strFroms=""){
		$strFroms=substr($this->FromStr,4).", $strFroms";

		$ii=0;
		$this->arrFroms=array();
		if (preg_match_all("/\s*(((\w+)!)?(\w+))(\s+(\w+))?\s*(,|$)/U",$strFroms,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$from=empty($match[6]) ? $match[4] : $match[6];
			if (isset($this->arrFroms[$from])){
				$oFro=$this->arrFroms[$from];
				$oFro->orgtable=$match[4];
				$oFro->datab=empty($oFro->datab)?$match[3]:$oFro->datab;
				$oFro->dattab=empty($oFro->dattab)?$match[1]:$oFro->dattab;
			}else{
				$oFro=(object)array("from"=>$from,"datab"=>$match[3],"orgtable"=>$match[4],"dattab"=>$match[1]);
				$this->arrFroms[$from]=$oFro;
				$this->arrFroms[$ii++]=&$this->arrFroms[$from];
			}
		}
	}

	function setKeyFrom($strUpdates=""){
		$arrOpt=array();
		if (preg_match_all("/(\w+)\s*(:(.+))?\s*(;|$)/U",$strUpdates,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$oOpt=(object)array();
			$oOpt->from ="";
			$oOpt->key  ="";
			$oOpt->qry  =0;
			$oOpt->chkey=0;
			$oOpt->from=$match[1];
			if(!isset($this->arrFroms[$oOpt->from])) continue;
			if(preg_match_all("/(\w+)\s*(=(.+))?\s*(,|$)/U",$match[3],$arr_opt,PREG_SET_ORDER))
				foreach($arr_opt as $opt)$oOpt->{"$opt[1]"}=$opt[3];
			$arrOpt[]=$oOpt;
			if($oOpt->qry==1)break;
		}
		if(!count($arrOpt)){
			$oOpt->from=$this->arrFroms[0]->from;
			$oOpt->key ="id";
			$oOpt->chkey=0;
		}elseif($oOpt->qry!=1)$oOpt=$arrOpt[0];
		
		$oOpt->key=empty($oOpt->key)?"id":$oOpt->key;
		$this->keyFrom="$oOpt->from.$oOpt->key";
		$this->keyQry =$this->fieldByOrgName($oOpt->from,$oOpt->key);
		$this->keyCh  =$oOpt->chkey==1;
	}

	function setNames($strNames=""){
		if (!empty($strNames)) $this->strNames=$strNames;
		$strNames=($this->sqltype=="select"?substr($this->FieldStr,6).", ":"").$this->strNames;

		$this->arrNames=array();
		if (preg_match_all("/\s*(((\w+)\.)?(\w+|\*))(\s+(\w+))?\s*(,|$)/U",$strNames,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$orgname=$match[4];
			$name=empty($match[6]) ? $orgname : $match[6];
			$from=$match[3];
			if($name=="*"){
				$arr=array();
				if(empty($from))$arr=&$this->arrFroms;
				elseif(!isset($this->arrFroms[$from]))continue;
				else $arr[$from]=$this->arrFroms[$from];

				foreach($arr as $kFro=>$oFro){
					if(is_string($kFro)){
						$this->setFromFields($oFro);
						foreach($oFro->fields as $nFld)
							$this->arrNames[]=(object)array("name"=>$nFld,"from"=>$kFro,"orgname"=>$nFld);
					}
				}
			}elseif(empty($from)){
				foreach($this->arrFroms as $kFro=>$oFro){
					if(is_numeric($kFro)){
						$this->setFromFields($oFro);
						if(isset($oFro->fields[$orgname]))
							$this->arrNames[]=(object)array("name"=>$name,"from"=>$oFro->from,"orgname"=>$orgname);
					}
				}
			}else $this->arrNames[]=(object)array("name"=>$name,"from"=>$from,"orgname"=>$orgname);
		}
	}

	function setFromFields($oFro){
		if(isset($oFro->fields)) return;
		$res=$this->dbLink->execute("select * from $oFro->dattab where 1=0");
		foreach($res->fields as $fld)$oFro->fields[$fld->name]=$fld->name;
	}

	function setIliski(){
		$this->iliski="{$this->arrFroms[0]->orgtable}->$this->rec_id";
	}

	function setJoins(){
		if (!preg_match_all("/\s*(\w+)\.(\w+)\s*=\s*(\w+)\.(\w+)(\s+and|\s*$)/U",$this->WhereStr,$arr_match,PREG_SET_ORDER)) return;
		$this->arrJoins=array();
		$nn=0;
		foreach($arr_match as $match){
			$sol=(object)array("order"=>$nn++,"from"=>$match[1],"orgname"=>$match[2],"fld"=>null,"esit"=>null);
			$sag=(object)array("order"=>$nn++,"from"=>$match[3],"orgname"=>$match[4],"fld"=>null,"esit"=>null);
			if (!isset($this->arrFroms[$sol->from]) || !isset($this->arrFroms[$sag->from])) continue;

			$sol->fld=$this->fieldByOrgName($sol->from,$sol->orgname);
			$sag->fld=$this->fieldByOrgName($sag->from,$sag->orgname);
			$sol->esit=$sag;
			$sag->esit=$sol;
			$this->arrJoins[]=$sol;
			$this->arrJoins[]=$sag;
		}
	}

	function setInfo(){
		if(!$this->active) return;
		if($this->info){
			foreach($this->stmt->fields as $adFld){
				$fld_name="fld_".strtolower($adFld->name);
				if($this->$fld_name)$this->$fld_name->fld=$adFld;
			}
			return;
		}

		$this->arrFields=array();
		$nn=0;
		$this->setNames();
		foreach($this->stmt->fields as $adFld){
			$oFld=(object)array("fld"=>$adFld);
			$oFld->name		= strtolower($adFld->name);
			$oFld->owner	= $this;
			$oFld->order	= $nn++;
			$oFld->from		= $this->arrFroms[$this->arrNames[$oFld->order]->from]->from;
			$oFld->table	= $oFld->from;
			$oFld->orgtable	= $this->arrFroms[$this->arrNames[$oFld->order]->from]->orgtable;
			$oFld->orgname	= $this->arrNames[$oFld->order]->orgname;
			$oFld->type		= $adFld->type;
			$oFld->len		= $adFld->definedsize;
			$oFld->char		= $this->type_char($adFld->type);
			$oFld->emptyval	= $this->empty_val($adFld->type);
			$oFld->like		= strpos(",,129,200,201,8,202,130,203,", ",$adFld->type,")>0;
			$oFld->filter	= null;
			$oFld->int		= null;
			$oFld->req		= null;
			$oFld->read		= null;
			$oFld->upd		= null;
			$fld_name="fld_$oFld->name";
			$this->$fld_name=$oFld;
			$this->arrFields[]=&$this->$fld_name;

			$rec_name="rec_$oFld->name";
			//$this->$rec_name=$oFld->emptyval;
			//$oFld->value=&$this->$rec_name;
			$oFld->value=$oFld->emptyval;
			$this->$rec_name=&$oFld->value;
		}
		$this->info=true;
	}

	function setIntFld($strFld=""){
		$strFld=empty($strFld)?$this->intFld:$strFld;
		if (preg_match_all("/\s*(\w+)\s*(,|$)/U",$strFld,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match) if($fld=$this->fieldByName($match[1])) $fld->int=true;
	}
	function setReqFld($strFld=""){
		$strFld=empty($strFld)?$this->reqFld:$strFld;
		if (preg_match_all("/\s*(\w+)\s*(,|$)/U",$strFld,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match) if($fld=$this->fieldByName($match[1])) $fld->req=true;
	}
	function setReadFld($strFld=""){
		$strFld=empty($strFld)?$this->readFld:$strFld;
		if (preg_match_all("/\s*(\w+)\s*(,|$)/U",$strFld,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match) if($fld=$this->fieldByName($match[1])) $fld->read=true;
	}
	
	function setUpdates($strUpdates=""){
		if (!$this->info) return false;

		$this->setIntFld();
		$this->setReqFld();
		$this->setReadFld();
		$this->arrUpdates=array();
		if (preg_match_all("/(\w+)\s*(:(.+))?\s*(;|$)/U",$strUpdates,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$from=$match[1];
			if(!isset($this->arrFroms[$from])) continue;
			$oOpt->auto="0";
			$oOpt->set ="0";
			$oOpt->qry ="0";
			$oOpt->key ="";
			$oOpt->chkey="0";
			if(preg_match_all("/(\w+)\s*(=(.+))?\s*(,|$)/U",$match[3],$arr_opt,PREG_SET_ORDER))
				foreach($arr_opt as $opt)$oOpt->{"$opt[1]"}=$opt[3];
			$this->setFromUpdate($this->arrFroms[$from],$oOpt);
		}
		if (count($this->arrUpdates)>1){
			$this->setJoins();
			foreach ($this->arrUpdates as $from=>$oUpd){
				if (isset($oUpd->keyFld->ifd))continue;
				foreach ($this->arrJoins as $oJoin)
				if ($oJoin->from==$from && $oJoin->orgname==$oUpd->keyFld->orgname && is_null($oJoin->fld) && !is_null($oJoin->esit->fld))
					$oUpd->keyFld->ifd=$oJoin->esit->fld;
			}
		}
	}
	
	function setFromUpdate($oFro=null,$oOpt=null){
		if (empty($oFro)) return false;
		$keyname=isset($oOpt,$oOpt->key)&&!empty($oOpt->key) ? $oOpt->key : "id";
		$oUpd->oFrom	 = $oFro;
		$oUpd->stmt		 = null;
		$oUpd->autoInc	 = isset($oOpt,$oOpt->auto) && $oOpt->auto==1;
		$oUpd->setInc	 = isset($oOpt,$oOpt->set)  && $oOpt->set==1;
		$oUpd->keyName	 = $keyname;
		$oUpd->keyFld	 = null;
		$oUpd->arrInsert = null;
		$oUpd->arrInsrt2 = null;
		$oUpd->arrUpdate = null;
		$oUpd->ts		 = "";
		$oUpd->strInsert = "";
		$oUpd->strInsrt2 = "";
		$oUpd->strUpdate = "";
		$oUpd->frmtInsert= "";
		$oUpd->frmtInsrt2= "";
		$oUpd->frmtUpdate= "";
		$oUpd->arrFields = array();

		$res=$this->dbLink->execute("select * from $oFro->dattab where 1=0");
		foreach($res->fields as $adFld){
			$qFld=$this->fieldByOrgName($oFro->from,$adFld->name);
			if(isset($qFld)){
				$qFld->upd=!$qFld->read;
				$oUpd->arrFields[]=$qFld;
				$fld_name="fld_$qFld->orgname";
				$rec_name="rec_$qFld->orgname";
				$qry_name="rec_$qFld->name";
				$oUpd->$fld_name=$qFld;
				$oUpd->$rec_name=&$this->$qry_name;
				if($qFld->orgname==$oUpd->keyName)$oUpd->keyFld=$qFld;
			}else{
				$oFld=(object)array("fld"=>$adFld);
				$oFld->name		= $adFld->name;
				$oFld->owner	= $this;
				$oFld->from		= $oFro->from;
				$oFld->orgtable	= $oFro->orgtable;
				$oFld->orgname	= $oFld->name;
				$oFld->char		= $this->type_char($adFld->type);
				$oFld->emptyval	= $this->empty_val($adFld->type);
				//$oFld->like	= strpos(",,129,200,201,8,202,130,203,7,133,134,135,", ",$adFld->type,")>0;
				//$oFld->filter	= null;
				//$oFld->int	= null;
				//$oFld->req	= null;
				//$oFld->read	= null;
				$oFld->upd		= true;
				$oUpd->arrFields[]=$oFld;

				$fld_name="fld_$oFld->name";
				$rec_name="rec_$oFld->name";
				$oUpd->$fld_name=$oFld;
				$oUpd->$rec_name=$oFld->emptyval;
				$oFld->value=&$oUpd->$rec_name;
				if($oFld->orgname==$oUpd->keyName)$oUpd->keyFld=$oFld;
			}
	    }
		if (is_null($oUpd->keyFld)) return false;
		$this->arrUpdates[$oFro->from]=$oUpd;

		$strInsFlds="";
		$strInsFld2="";
		$strInsVals="";
		$strInsVal2="";
		$strUpdFlds="";

		$oUpd->arrInsert=array();
		$oUpd->arrInsrt2=array();
		$oUpd->arrUpdate=array();

		$oCmd=new COM("ADODB.Command");
		$nn=0;
		foreach ($oUpd->arrFields as $uFld){
			$rec_name   ="rec_$uFld->orgname";

			$oPar=$oCmd->createParameter("p$nn++",$uFld->type,1,-1);
			$uFld->par=$oPar;
			$strInsFlds.=",$uFld->orgname";
			$strInsVals.=",?";
			$oUpd->arrInsert[]=$oPar;
			if ($uFld!=$oUpd->keyFld && $uFld->orgname!="ts"){
				$strInsFld2.=",$uFld->orgname";
				$strInsVal2.=",?";
				$oUpd->arrInsrt2[]=$oPar;
				if ($uFld->upd){
					$strUpdFlds.=",$uFld->orgname=?";
					$oUpd->arrUpdate[]=$oPar;
				}
			}
	    }

		$strInsFlds=substr($strInsFlds,1);
		$strInsFld2=substr($strInsFld2,1);
		$strInsVals=substr($strInsVals,1);
		$strInsVal2=substr($strInsVal2,1);

		$oUpd->strInsert="insert into {$oUpd->oFrom->dattab} ($strInsFlds) values ($strInsVals)";
		$oUpd->strInsrt2="insert into {$oUpd->oFrom->dattab} ($strInsFld2) values ($strInsVal2)";
		if(isset($oUpd->fld_ts)){
			$strUpdFlds.=",ts=FROM_UNIXTIME(?)";
			$strUpdFlds=substr($strUpdFlds,1);
			$oUpd->strUpdate="update {$oUpd->oFrom->dattab} set $strUpdFlds where {$oUpd->keyFld->orgname}=? and ts=?";
			$oPar=$oCmd->createParameter("p$nn++",$oUpd->fld_ts->type,1,-1);
			$oUpd->arrUpdate["ts"]=$oPar;
			$oUpd->arrUpdate[]=$oUpd->keyFld->par;
			$oUpd->arrUpdate[]=$oUpd->fld_ts->par;
		}else{
			$strUpdFlds=substr($strUpdFlds,1);
			$oUpd->strUpdate="update {$oUpd->oFrom->dattab} set $strUpdFlds where {$oUpd->keyFld->orgname}=?";

			$oUpd->arrUpdate[]=$oUpd->keyFld->par;
		}
		return true;
	}

	function fromInsert($oUpd){
		if(isset($oUpd->fld_ktarih)   && empty($oUpd->rec_ktarih))	$oUpd->rec_ktarih=$this->ktarih;
		if(isset($oUpd->fld_kuser)	  && empty($oUpd->rec_kuser))	$oUpd->rec_kuser =$this->kuser;
		if(isset($oUpd->fld_sirket)	  && empty($oUpd->rec_sirket))	$oUpd->rec_sirket=$this->sirket;
        if(isset($oUpd->fld_personel) && empty($oUpd->rec_personel))$oUpd->rec_personel=$this->personel;

		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dbLink;
		try{
			foreach($oUpd->arrFields as $uFld){
				if($uFld->int && empty($uFld->value)) $uFld->value=-1;
				$uFld->par->value=$uFld->value;
			}
			if($oUpd->autoInc){
				$arrIns=array();
				$oCmd->commandText=$oUpd->strInsrt2;
				$suc=$oCmd->execute($this->affected,$oUpd->arrInsrt2);
			}else{
				if($oUpd->setInc){
					$RS=$this->dbLink->Execute("Get_NewID('{$oUpd->oFrom->orgtable}')");
					$oUpd->keyFld->value=$RS->fields[0]->value;
					$oUpd->keyFld->par->value=$oUpd->keyFld->value;
				}
				$oCmd->commandText=$oUpd->strInsert;
				$suc=$oCmd->execute($this->affected,$oUpd->arrInsert);
			}
		}catch(execption $e){
			echo $this->dbLink->errors[0]->source,":",$this->dbLink->errors[0]->description,"<br/>",
				 $this->dbLink->errors[0]->SQLState,":",$this->dbLink->errors[0]->NativeError,"<br/>";
			return false;

		}
		if($oUpd->autoInc){
			$RS=$this->dbLink->Execute("getautoincvalue(0)");
			$oUpd->keyFld->value=$RS->fields[0]->value;
			if(isset($oUpd->keyFld->ifd)) $oUpd->keyFld->ifd->value=$oUpd->keyFld->value;
		}
		return true;
	}

	function fromUpdate($oUpd){
		if(isset($oUpd->keyFld->ifd))$oUpd->keyFld->value=$oUpd->keyFld->ifd->value;
		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dbLink;
		try{
			foreach($oUpd->arrFields as $uFld){
				if($uFld->int && empty($uFld->value)) $uFld->value=-1;
				$uFld->par->value=$uFld->value;
			}
			if(isset($oUpd->fld_ts)){
				$oUpd->ts=time();
				$oUpd->arrUpdate["ts"]->value=$oUpd->ts;
				$oUpd->fld_ts->par->value=$oUpd->ts;
			}
			$oCmd->commandText=$oUpd->strUpdate;
			$oCmd->execute($this->affected,$oUpd->arrUpdate);
		}catch(exception $e){
			echo $e->getMessage(),"<br>";
			echo $this->dbLink->errors[0]->source,":",$this->dbLink->errors[0]->description,"<br/>";
			return false;
		}
		return true;
	}

	function addParam($parWhere=null){
		if(preg_match_all("/(\?prm_(\w+)(\W|$))/iU",$parWhere,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match){
				$ii=stripos($parWhere,$match[1]);
				$parWhere=substr_replace($parWhere,"?$match[3]",$ii,strlen($match[1]));

				$name=$match[2];
				$par_name="par_$name";
				if(!isset($this->$par_name)){
					$prm_name="prm_$name";
					$this->$prm_name=null;
					$oPar=(object)array("name"=>$name,"type"=>"s");
					$oPar->value=&$this->$prm_name;
					$this->$par_name=$oPar;
				}
				$this->arrParams[]=&$this->$par_name;
			}
		if(empty($parWhere))return fale;
		$this->WhereStr=empty($this->WhereStr) ? "$parWhere" : "$this->WhereStr and $parWhere";
	}

	function bindObjVals($objVals){
		foreach ($objVals as $name => $value)
			$rec_name="rec_$name";
			if (isset($this->$rec_name)) $this->$rec_name=$value;
	}
	function getFldVals($strFields=""){
		$oVals=(object)array();
		if (empty($strFields))
			foreach ($this->arrFields as $oFld) $oVals->{"$oFld->name"}=$this->{"rec_$oFld->name"};
		elseif (preg_match_all("/\s*(\w+)\s*(,|$)/U",$strFields,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match) if ($oFld=$this->fieldByName($match[1])) $oVals->{"$oFld->name"}=$this->{"rec_$oFld->name"};
		return $oVals;
	}
	function blankVals(){
		foreach($this->arrFields as $oFld)if(is_null($oFld->filter)) $oFld->value=$oFld->emptyval;
	}

	function addCal($name){
		$fcal_name="fcal_$name";
		if (isset($this->$fcal_name)) return false;

		$oCal=(object)array();
		$oCal->name=$name;
		$oCal->order=count($this->arrCals);
		$oCal->emptyval="";
		$oCal->valstr="";
		$this->$fcal_name=$oCal;
		$this->arrCals[] =&$this->$fcal_name;
		$cal_name="cal_$oCal->name";
		$this->$cal_name=$oCal->emptyval;
		$oCal->value=&$this->$cal_name;
		return $oCal;
	}

	function createCals($strCals=null){
		if (!is_string($strCals)) return false;
		$this->arrCals=array();
		if (!preg_match_all("/\s*(\w+)\s*=\s*((\\\$(\w+)\.)?([\w-% ]+))($|\r|\n)/U",$strCals,$arr_match,PREG_SET_ORDER)) return false;
		foreach($arr_match as $match){
			if (!empty($match[3])) if (is_null($value=$this->objVal($match[3],$match[4]))) continue;
			if ($oCal=$this->addCal($match[1])) {$oCal->valstr=$match[2]; $oCal->value=$match[2];}
		}
	}

	function setCals($main=null){
		if (is_null($main)) $main=$this;
		foreach($this->arrCals as $oCal)
		if (preg_match_all("/%(\w+)(\W|$)/U",$oCal->valstr,$flds,PREG_SET_ORDER)){
			$value=$oCal->valstr;
			foreach ($flds as $name){
				if ($mFld=$main->fieldByName($name[1]))
				$value=str_replace("%$name[1]",$mFld->value,$value);
			}
			$oCal->value=$value;
		}
	}

	function dataSeek($offset){
		$this->stmt->move($offset);
	}
	function next(){
		if(!$this->stmt->EOF) $this->stmt->moveNext();
		if(!$this->stmt->EOF) foreach($this->arrFields as $oFld) $oFld->value=$oFld->fld->value;
		else foreach($this->arrFields as $oFld) $oFld->value=$oFld->emptyval;
		return !$this->stmt->EOF;
	}
	function first(){
		$this->stmt->moveFirst();
		if(!$this->stmt->EOF) foreach($this->arrFields as $oFld) $oFld->value=$oFld->fld->value;
		else foreach($this->arrFields as $oFld) $oFld->value=$oFld->emptyval;
		return $this->stmt->EOF;
	}

	function insert(){
		foreach($this->arrFields as $oFld) if(isset($oFld->int) && empty($oFld->value))$oFld->value=-1;
		foreach ($this->arrUpdates as $oUpd) if(!$this->fromInsert($oUpd)) return false;
		return true;
	}

	function update(){
		foreach($this->arrFields as $oFld) if(isset($oFld->int) && empty($oFld->value))$oFld->value=-1;
		foreach ($this->arrUpdates as $oUpd) if(!$this->fromUpdate($oUpd)) return false;
		return true;
	}

	function fieldByOrgName($from,$orgname){
		$retFld=null;
		foreach($this->arrFields as $fld){
			//echo "$from.$orgname: $fld->from.$fld->orgname<br>";
			if($fld->from==$from && $fld->orgname==$orgname){$retFld=$fld; break;}
		}
		return $retFld;
	}

	function fieldByName($name){
		$retFld=null; $fld_name="fld_$name";
		if(isset($this->$fld_name)) $retFld=$this->$fld_name;
		return $retFld;
	}

	function paramByName($name){
		$retPar=null;$par_name="par_$name";
		if(isset($this->$par_name)) $retPar=$this->$par_name;
		return $retPar;
	}

	function bindPostName($frm_name){
		if(!isset($_POST[$frm_name]) || substr($frm_name,0,4)!="frm_") return;
		$fld_name="fld_".substr($frm_name,4);
		if(!isset($this->$fld_name)) return;
		$this->$fld_name->value=$_POST[$frm_name];
	}

	function bindPostVals(){
		foreach ($this->arrFields as $oFld){
			$post_name="frm_$oFld->name";
			if (isset($_POST[$post_name]))
				if (empty($_POST[$post_name]))
					$oFld->value=$oFld->emptyval;
				elseif (strpos(",,15,253,254,",",$oFld->type,"))
					$oFld->value=substr($_POST[$post_name],0,$oFld->length);
				else $oFld->value=$_POST[$post_name];

			$post_name="frm2_$oFld->name";
			if (isset($_POST[$post_name]))
				if (empty($_POST[$post_name]))
					$oFld->value2=$oFld->emptyval;
				elseif (strpos(",,15,253,254,",",$oFld->type,"))
					$oFld->value2=substr($_POST[$post_name],0,$oFld->length);
				else $oFld->value2=$_POST[$post_name];
		}
	}

	function bindGetVals(){
		foreach ($_GET as $frm_name => $frm_val)
		if (substr($frm_name,0,4)=="frm_"){
			$fld_name="fld_".substr($frm_name,4);
			if (isset($this->$fld_name))
				if (empty($frm_val)) $this->$fld_name->value=$this->$fld_name->emptyval;
				elseif (strpos(",,15,253,254,",",{$this->$fld_name->type},"))
				$this->$fld_name->value=substr($frm_val,0,$this->$fld_name->length);
				else $this->$fld_name->value=$frm_val;
		}
	}

	function bindGetName($frm_name){
		if(!isset($_GET[$frm_name]) || substr($frm_name,0,4)!="frm_") return;
		$fld_name="fld_".substr($frm_name,4);
		$frm_val=$_GET[$frm_name];
		if(!isset($this->$fld_name)) return;
			if (empty($frm_val)) $this->$fld_name->value=$this->$fld_name->emptyval;
			elseif (strpos(",,15,253,254,",",{$this->$fld_name->type},"))
			$this->$fld_name->value=substr($frm_val,0,$this->$fld_name->length);
			else $this->$fld_name->value=$frm_val;
	}

	function bindGridPostVals($ii=0){
		foreach ($this->arrFields as $oFld){
			$post_name="grd_{$ii}_frm_$oFld->name";
			if (isset($_POST[$post_name]))
				$oFld->value=empty($_POST[$post_name])?$oFld->emptyval:$_POST[$post_name];
		}
	}

	function char_type($char){
		$fld_type=200;
		switch($fld_type){
		case "S": $fld_type=200; break;
		case "N": $fld_type=139; break;
		case "I": $fld_type=20;  break;
		case "D": $fld_type=133; break;
		case "H": $fld_type=134; break;
		case "T": $fld_type=135; break;
		case "M": $fld_type=201; break;
		case "W": $fld_type=205; break;
		}
		return $fld_type;
	}

	function type_char($fld_type){
		$char="S";
		switch($fld_type){
		case 16	:	//adTinyInt			Indicates a one-byte signed integer (DBTYPE_I1).
		case 2	:	//adSmallInt		Indicates a two-byte signed integer (DBTYPE_I2).
		case 3	:	//adInteger			Indicates a four-byte signed integer (DBTYPE_I4).
		case 20	:	//adBigInt			Indicates an eight-byte signed integer (DBTYPE_I8).

		case 17	:	//adUnsignedTinyInt	Indicates a one-byte unsigned integer (DBTYPE_UI1).
		case 18	:	//adUnsignedSmallInt	Indicates a two-byte unsigned integer (DBTYPE_UI2).
		case 19	:	//adUnsignedInt		Indicates a four-byte unsigned integer (DBTYPE_UI4).
		case 21	:	//adUnsignedBigInt	Indicates an eight-byte unsigned integer (DBTYPE_UI8).
					$char="I";
					break;

		case 129:	//adChar			Indicates a string value (DBTYPE_STR).
		case 200:	//adVarChar			Indicates a string value.
		case 8	:	//adBSTR			Indicates a null-terminated character string (Unicode) (DBTYPE_BSTR).
		case 202:	//adVarWChar		Indicates a null-terminated Unicode character string.
		case 130:	//adWChar			Indicates a null-terminated Unicode character string (DBTYPE_WSTR).
					$char="S";
					break;
		case 201:	//adLongVarChar		Indicates a long string value.
		case 203:	//adLongVarWChar	Indicates a long null-terminated Unicode string value.
					$char="M";
					break;
		case 128:	//adBinary			Indicates a binary value (DBTYPE_BYTES).
		case 11	:	//adBoolean			Indicates a Boolean value (DBTYPE_BOOL).
		case 136:	//adChapter			Indicates a four-byte chapter value that identifies rows in a child rowset (DBTYPE_HCHAPTER).
					$char="I";
					break;
		case 7	:	//adDate			Indicates a date value (DBTYPE_DATE). A date is stored as a double, the whole part of which is the number of days since December 30, 1899, and the fractional part of which is the fraction of a day.
		case 133:	//adDBDate			Indicates a date value (yyyymmdd) (DBTYPE_DBDATE).
					$char="D";
					break;
		case 134:	//adDBTime			Indicates a time value (hhmmss) (DBTYPE_DBTIME).
					$char="H";
					break;
		case 135:	//adDBTimeStamp		Indicates a date/time stamp (yyyymmddhhmmss plus a fraction in billionths) (DBTYPE_DBTIMESTAMP).
					$char="T";
					break;
		case 6	:	//adCurrency		Indicates a currency value (DBTYPE_CY). Currency is a fixed-point number with four digits to the right of the decimal point. It is stored in an eight-byte signed integer scaled by 10,000.
		case 14	:	//adDecimal			Indicates an exact numeric value with a fixed precision and scale (DBTYPE_DECIMAL).
		case 131:	//adNumeric			Indicates an exact numeric value with a fixed precision and scale (DBTYPE_NUMERIC).
		case 4	:	//adSingle			Indicates a single-precision floating-point value (DBTYPE_R4).
		case 5	:	//adDouble			Indicates a double-precision floating-point value (DBTYPE_R8).
		case 139:	//adVarNumeric		Indicates a numeric value.
					$char="N";
					break;
		case 0	:	//adEmpty			Specifies no value (DBTYPE_EMPTY).
		case 10	:	//adError			Indicates a 32-bit error code (DBTYPE_ERROR).
		case 64	:	//adFileTime		Indicates a 64-bit value representing the number of 100-nanosecond intervals since January 1, 1601 (DBTYPE_FILETIME).
		case 72	:	//adGUID			Indicates a globally unique identifier (GUID) (DBTYPE_GUID).
		case 9	:	//adIDispatch		Indicates a pointer to an IDispatch interface on a COM object (DBTYPE_IDISPATCH).	This data type is currently not supported by ADO. Usage may cause unpredictable results.
		case 13	:	//adIUnknown		Indicates a pointer to an IUnknown interface on a COM object (DBTYPE_IUNKNOWN).		This data type is currently not supported by ADO. Usage may cause unpredictable results.
					$char="I";
					break;
		case 204:	//adVarBinary		Indicates a binary value.
		case 205:	//adLongVarBinary	Indicates a long binary value.
					$char="W";
					break;
		case 138:	//adPropVariant		Indicates an Automation PROPVARIANT (DBTYPE_PROP_VARIANT).
		case 132:	//adUserDefined		Indicates a user-defined variable (DBTYPE_UDT).
		case 12	:	//adVariant			Indicates an Automation Variant (DBTYPE_VARIANT).	This data type is currently not supported by ADO. Usage may cause unpredictable results.
					$char="S";
					break;
		}
		return $char;
	}

	function empty_val($fld_type){

		$e_value="";
		switch ($fld_type){
		case 16	:	//adTinyInt			Indicates a one-byte signed integer (DBTYPE_I1).
		case 2	:	//adSmallInt		Indicates a two-byte signed integer (DBTYPE_I2).
		case 3	:	//adInteger			Indicates a four-byte signed integer (DBTYPE_I4).
		case 20	:	//adBigInt			Indicates an eight-byte signed integer (DBTYPE_I8).

		case 17	:	//adUnsignedTinyInt	Indicates a one-byte unsigned integer (DBTYPE_UI1).
		case 18	:	//adUnsignedSmallInt	Indicates a two-byte unsigned integer (DBTYPE_UI2).
		case 19	:	//adUnsignedInt		Indicates a four-byte unsigned integer (DBTYPE_UI4).
		case 21	:	//adUnsignedBigInt	Indicates an eight-byte unsigned integer (DBTYPE_UI8).
					$e_value=0;
					break;

		case 129:	//adChar			Indicates a string value (DBTYPE_STR).
		case 200:	//adVarChar			Indicates a string value.
		case 201:	//adLongVarChar		Indicates a long string value.
		case 8	:	//adBSTR			Indicates a null-terminated character string (Unicode) (DBTYPE_BSTR).
		case 202:	//adVarWChar		Indicates a null-terminated Unicode character string.
		case 130:	//adWChar			Indicates a null-terminated Unicode character string (DBTYPE_WSTR).
		case 203:	//adLongVarWChar	Indicates a long null-terminated Unicode string value.
					$e_value="";
					break;

		case 128:	//adBinary			Indicates a binary value (DBTYPE_BYTES).
		case 11	:	//adBoolean			Indicates a Boolean value (DBTYPE_BOOL).
		case 136:	//adChapter			Indicates a four-byte chapter value that identifies rows in a child rowset (DBTYPE_HCHAPTER).
					$e_value=0;
					break;

		case 7	:	//adDate			Indicates a date value (DBTYPE_DATE). A date is stored as a double, the whole part of which is the number of days since December 30, 1899, and the fractional part of which is the fraction of a day.
		case 133:	//adDBDate			Indicates a date value (yyyymmdd) (DBTYPE_DBDATE).
		case 134:	//adDBTime			Indicates a time value (hhmmss) (DBTYPE_DBTIME).
		case 135:	//adDBTimeStamp		Indicates a date/time stamp (yyyymmddhhmmss plus a fraction in billionths) (DBTYPE_DBTIMESTAMP).
					$e_value=null;
					break;

		case 6	:	//adCurrency		Indicates a currency value (DBTYPE_CY). Currency is a fixed-point number with four digits to the right of the decimal point. It is stored in an eight-byte signed integer scaled by 10,000.
		case 14	:	//adDecimal			Indicates an exact numeric value with a fixed precision and scale (DBTYPE_DECIMAL).
		case 131:	//adNumeric			Indicates an exact numeric value with a fixed precision and scale (DBTYPE_NUMERIC).
		case 4	:	//adSingle			Indicates a single-precision floating-point value (DBTYPE_R4).
		case 5	:	//adDouble			Indicates a double-precision floating-point value (DBTYPE_R8).
		case 139:	//adVarNumeric		Indicates a numeric value.
					$e_value=0;
					break;

		case 0	:	//adEmpty			Specifies no value (DBTYPE_EMPTY).
		case 10	:	//adError			Indicates a 32-bit error code (DBTYPE_ERROR).
		case 64	:	//adFileTime		Indicates a 64-bit value representing the number of 100-nanosecond intervals since January 1, 1601 (DBTYPE_FILETIME).
		case 72	:	//adGUID			Indicates a globally unique identifier (GUID) (DBTYPE_GUID).
		case 9	:	//adIDispatch		Indicates a pointer to an IDispatch interface on a COM object (DBTYPE_IDISPATCH).	This data type is currently not supported by ADO. Usage may cause unpredictable results.
		case 13	:	//adIUnknown		Indicates a pointer to an IUnknown interface on a COM object (DBTYPE_IUNKNOWN).		This data type is currently not supported by ADO. Usage may cause unpredictable results.
					$e_value=null;
					break;

		case 204:	//adVarBinary		Indicates a binary value.
		case 205:	//adLongVarBinary	Indicates a long binary value.
		case 138:	//adPropVariant		Indicates an Automation PROPVARIANT (DBTYPE_PROP_VARIANT).
					$e_value=null;
					break;

		case 132:	//adUserDefined		Indicates a user-defined variable (DBTYPE_UDT).
		case 12	:	//adVariant			Indicates an Automation Variant (DBTYPE_VARIANT).	This data type is currently not supported by ADO. Usage may cause unpredictable results.





					$e_value=null;
					break;
		}
		return $e_value;
	}

	function print_vals(){
		foreach ($this->arrFields as $oFld)
			echo "<br/>",$oFld->name,"(",$oFld->char,"): ",is_null($oFld->value) ? "null" : $oFld->value;
	}

	function print_pars(){
		foreach ($this->arrParams as $oPar)
			echo "<br/>",$oPar->name,"(",$oPar->type,"): ",is_null($oPar->value) ? "null" : $oPar->value;
	}
}
?>
