<?php
include_once("$REAL_P/_class/data__qry.php");
abstract class cls__ado extends cls__qry{
	protected $RS=null;

	function bind_where(&$addWhere,$oCmd){
		$nn=0;
		if(count($this->arrParams)){
			foreach($this->arrParams as $par){
				$prm_name="prm_".$par->name;

				if($par->char=="X")$par->char=$this->toTypeChar($par->value);
				$pType=$this->char_type($par->char);
				$oPar=$oCmd->createParameter("p".$nn++,$pType,1,-1);
				$oPar->value=$par->value;
				$oCmd->parameters->append($oPar);
				//echo "$prm_name = {$this->$prm_name} $pType $par->char $par->name $par->value val:$oPar->value<br>";
			}
		}
		$orl=0;
		$addWhere=empty($addWhere)?"":" and $addWhere";
		foreach ($this->arrWhere as $whr){
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

			$whr->cnt=$cnt;
			$orWhere="";
			for($ii=1;$ii<=$cnt;$ii++){
				if(isset($whr->fld))$pType=$whr->fld->type;
				elseif(isset($this->arrFroms[$whr->from],$this->arrFroms[$whr->from]->fields[$whr->fname])){
					$oTyp=$this->arrFroms[$whr->from]->fields[$whr->fname];
					$pType=$oTyp->type;
				}
				elseif(is_numeric($whr->{"val$ii"}))$pType=5;
				else$pType=200;

				if(strpos(",,14,131,", ",$pType,"))$pType=5;
				$oPar=$oCmd->createParameter("p".$nn++,$pType,1,-1);
				if($whr->{"val$ii"}=="{}")if(isset($whr->fld))$whr->{"val$ii"}="00:00";else$whr->{"val$ii"}="00:00";
				//echo "Type:$pType from:$whr->fromfld value:$whr->value par:$oPar->value val:",$whr->{"val$ii"},":",$whr->fld->emptyval,"<br>";

				$oPar->value=$whr->{"val$ii"};
				$oCmd->parameters->append($oPar);


				$not=$whr->{"not$ii"}?"not ":"";
				$orWhere.=" or $not$whr->fromfld $oprWhr ?";
			}
			$orWhere=substr($orWhere,4);

			// if($whr->orl=="//" && !$orl){
				// $orl=1;
				// $whr->orl="";
				// if($opr==">>") $addWhere .= " and (($whr->fromfld between ? and ?)";
				// else$addWhere .= " and (($orWhere)";
			// }elseif($orl){
				// if($opr==">>") $addWhere .= " or ($whr->fromfld between ? and ?)";
				// else$addWhere .= " or ($orWhere)";
			// }else{
				// if($opr==">>") $addWhere .= " and ($whr->fromfld between ? and ?)";
				// else$addWhere .= " and ($orWhere)";
			// }
			if($whr->orl=="//"){
				if($orl){
					if($opr==">>") $addWhere .= " or ($whr->fromfld between ? and ?)";
					else$addWhere .= " or ($orWhere)";
				}else{
					$orl=1;
					if($opr==">>") $addWhere .= " and (($whr->fromfld between ? and ?)";
					else$addWhere .= " and (($orWhere)";
				}
			}else{
				if($orl){$orl=0;$addWhere.=")";}
				if($opr==">>") $addWhere .= " and ($whr->fromfld between ? and ?)";
				else$addWhere .= " and ($orWhere)";
			}
		}
		if($orl){$orl=0;$addWhere.=")";}
		$addWhere=substr($addWhere,5);
	}

	function open($addWhere=null,$first=true){
		if($this->active) return false;

		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dblink;
		$this->bind_where($addWhere,$oCmd);
		$sqlStr=$this->getSqlStr($addWhere);
		//$this->print_whrs();
		$this->addWhere=$addWhere;
		//echo "<br>WHR: $addWhere";
		//echo "<br>SQL: $sqlStr";

		$oCmd->commandText=$sqlStr;
		$this->RS=new COM("ADODB.RecordSet");
		$this->RS->CursorType=3;
		$this->RS->CursorLocation=2;
		try{$this->RS->open($oCmd);}catch(exception $e){
			echo $e->getMessage(),$sqlStr,"<br>";
			return false;
		}

		$this->reccount=$this->RS->recordcount;
		$this->active=true;
		$this->setInfo();
		if($this->reccount)if($first)$this->first();else$this->RS->MovePrevious();
		return true;
	}

	function exec($addWhere=null){
		//if($this->active) return false;

		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dblink;
		$this->bind_where($addWhere,$oCmd);
		$sqlStr=$this->getSqlStr($addWhere);
		//echo "<br>SQL: $sqlStr";
		$oCmd->commandText=$sqlStr;
		try{$oCmd->execute($this->affected);}catch(exception $e){
			echo "<br>SQL: $sqlStr";
			echo $this->dblink->errors[0]->source,":",$this->dblink->errors[0]->description,"<br/>",
				 $this->dblink->errors[0]->SQLState,":",$this->dblink->errors[0]->NativeError,"<br/>",$sqlStr;
			//return false;
			echo $e->getMessage(),"<br>";
			return false;
		}
	}
	function execCursor($first=true){
		if($this->active) return false;

		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dblink;
		$this->bind_where($addWhere,$oCmd);
		$sqlStr=$this->getSqlStr($addWhere);
		$oCmd->commandText=$sqlStr;
		$this->RS=new COM("ADODB.RecordSet");
		$this->RS->CursorType=3;
		$this->RS->CursorLocation=2;
		try{
			$this->RS->open($oCmd);
		}catch(exception $e){
			echo $e->getMessage(),"<br>";
			return false;
		}
		$this->reccount=$this->RS->recordCount;
		$this->active=true;
		$this->setInfo();
		if($this->reccount)if($first)$this->first();else$this->RS->MovePrevious();
		return true;
	}

	function close($dropTemp=false){
		if($this->active) {
			$this->RS->close();
			$this->RS=null;
		}

		if($dropTemp && !empty($this->temp)){
			$res=$this->dblink->execute("drop table temp.$this->temp");
			$this->temp="";
		}

		$this->active=false;
		$this->reccount=0;
	}

	function setFromFields($oFro){
		if(isset($oFro->fields)) return;
		$res=$this->dblink->execute("select * from $oFro->dattab where 1=0");
		foreach($res->fields as $fld)$oFro->fields[strtolower($this->propOrgName($fld->name))]=
			(object)array("name"=>$this->propOrgName($fld->name),"type"=>$fld->type);
	}

	function setInfo(){
		if(!$this->active) return;
		if($this->info){
			foreach($this->RS->fields as $adFld){
				$fld_name="fld_".strtolower($adFld->name);
				if($this->$fld_name)$this->$fld_name->fld=$adFld;
			}
			return;
		}

		$this->arrFields=array();
		$nn=0;
		//echo "SETN:$this->setn";
		if($this->setn)$this->setNames();
		//var_dump($this->arrNames);
		foreach($this->RS->fields as $adFld){
			$oInf=(object)array("fld"=>$adFld);
			$oInf->name		= strtolower($adFld->name);
			$oInf->owner	= $this;
			$oInf->order	= $nn++;
			$oInf->from		= ($this->setn ? $this->arrNames[$oInf->order]->from    : "");
			$oInf->orgtable = ($oInf->from ? $this->arrFroms[$oInf->from]->orgtable : "");
			$oInf->orgname	= ($this->setn ? $this->arrNames[$oInf->order]->orgname : "");
			$oInf->type		= $adFld->type;
			$oInf->length	= $adFld->definedsize;
			$oInf->char		= $this->type_char($adFld->type);
			$oInf->emptyval	= $this->empty_val($adFld->type);
			$oInf->like		= strpos(",,129,200,201,8,202,130,203,", ",$adFld->type,")>0;
			$oInf->filter	= null;
			$oInf->int		= null;
			$oInf->req		= null;
			$oInf->read		= null;
			$oInf->upd		= null;

			$fld_name="fld_$oInf->name";
			$this->$fld_name=$oInf;
			$this->arrFields[]=&$this->$fld_name;

			$rec_name="rec_$oInf->name";
			$this->$rec_name=$oInf->emptyval;
			$oInf->value=&$this->$rec_name;
		}
		$this->info=true;
	}

	function setFromUpdate($oFro=null,$oOpt=null){
		if(empty($oFro))return false;
		$keyname=isset($oOpt,$oOpt->key)&&!empty($oOpt->key) ? $oOpt->key : "id";
		$oUpd=(object)array();
		$oUpd->oFrom	 = $oFro;
		$oUpd->id		 = null;
		$oUpd->stmt		 = null;
		$oUpd->autoInc	 = isset($oOpt,$oOpt->auto) && $oOpt->auto==1;
		$oUpd->setInc	 = isset($oOpt,$oOpt->set)  && $oOpt->set==1;
		$oUpd->denk		 = isset($oOpt,$oOpt->denk)?$oOpt->denk:"";
		$oUpd->keyName	 = $keyname;
		$oUpd->keyFld	 = null;
		$oUpd->arrInsert = null;
		$oUpd->arrInsrt2 = null;
		$oUpd->arrUpdate = null;
		$oUpd->arrDelete = null;
		$oUpd->ts		 = "";
		$oUpd->strInsert = "";
		$oUpd->strInsrt2 = "";
		$oUpd->strUpdate = "";
		$oUpd->strDelete = "";
		$oUpd->arrFields = array();

		$nn=-1;
		$res=$this->dblink->execute("select * from $oFro->dattab where 1=0");
		foreach($res->fields as $adFld){
			$qFld=$this->fieldByOrgName($oFro->from,$adFld->name);
			$nn++;
			if(!is_null($qFld)){
				$qFld->upd=!$qFld->read;
				$oUpd->arrFields[]=$qFld;
				$fld_name="fld_$nn";
				$rec_name="rec_$nn";
				$qry_name="rec_$qFld->name";
				$oUpd->$fld_name=$qFld;
				$oUpd->$rec_name=&$this->$qry_name;
				if(strcasecmp($qFld->orgname,$oUpd->keyName)==0)$oUpd->keyFld=$qFld;
			}else{
				$oInf=(object)array("fld"=>$adFld);
				$oInf->name		= "ff_$nn";;
				$oInf->owner	= $this;
				$oInf->from		= $oFro->from;
				$oInf->orgtable	= $oFro->orgtable;
				$oInf->orgname	= $this->propOrgName($adFld->name);
				$oInf->type		= $adFld->type;
				$oInf->char		= $this->type_char($oInf->type);
				$oInf->emptyval	= $this->empty_val($oInf->type);
				$oInf->filter	= null;
				$oInf->int		= null;
				$oInf->req		= null;
				$oInf->read		= null;
				$oInf->upd		= false;

				$fld_name="fld_$nn";
				$rec_name="rec_$nn";
				$oUpd->$fld_name=$oInf;
				$oUpd->$rec_name=$oInf->emptyval;
				$oInf->value=&$oUpd->$rec_name;
				$oUpd->arrFields[]=$oUpd->$fld_name;

				if(strcasecmp($oInf->orgname,$oUpd->keyName)==0)$oUpd->keyFld=$oInf;
			}
	    }
		if(is_null($oUpd->keyFld)) return false;
		$this->arrUpdates[$oFro->from]=$oUpd;

		$strInsFlds="";
		$strInsFld2="";
		$strInsVals="";
		$strInsVal2="";
		$strUpdFlds="";
		$strUpdFlds="";

		$oUpd->arrInsert=array();
		$oUpd->arrInsrt2=array();
		$oUpd->arrUpdate=array();

		$oCmd=new COM("ADODB.Command");
		$nn=0;
		foreach($oUpd->arrFields as $uFld){
			$pType=$uFld->type;
			if(strpos(",,14,131,", ",$uFld->type,"))$pType=5;
			$oPar=$oCmd->createParameter("p".$nn++,$pType,1,-1);
			$uFld->par=$oPar;
			$strInsFlds.=",$uFld->orgname";
			$strInsVals.=",?";
			$oUpd->arrInsert[]=$oPar;
			if($uFld!=$oUpd->keyFld && $uFld->orgname!="ts"){
				$strInsFld2.=",$uFld->orgname";
				$strInsVal2.=",?";
				$oUpd->arrInsrt2[]=$oPar;
				if($uFld->upd){
					$strUpdFlds.=",$uFld->orgname=?";
					$oUpd->arrUpdate[]=$oPar;
				}
			}
	    }
		$oUpd->setId = $this->keyChg && $this->keyQry==$oUpd->keyFld;

		$strInsFlds=substr($strInsFlds,1);
		$strInsFld2=substr($strInsFld2,1);
		$strInsVals=substr($strInsVals,1);
		$strInsVal2=substr($strInsVal2,1);

		$oUpd->strInsert="insert into {$oUpd->oFrom->dattab} ($strInsFlds) values ($strInsVals)";
		$oUpd->strInsrt2="insert into {$oUpd->oFrom->dattab} ($strInsFld2) values ($strInsVal2)";
		if(isset($this->fld_ts)){
			$strUpdFlds.=",ts=?";
			if($oUpd->setId)$strUpdFlds.=",{$oUpd->keyFld->orgname}=?";
			$strUpdFlds=substr($strUpdFlds,1);
			
			$oUpd->strUpdate="update {$oUpd->oFrom->dattab} set $strUpdFlds where {$oUpd->keyFld->orgname}=? and ts=?";
			$oUpd->p_ts=$nn;
			$oPar=$oCmd->createParameter("p".$nn++,$this->fld_ts->type,1,-1);
			$oUpd->arrUpdate[]=$oPar;
			$oUpd->arrUpdate[]=$oUpd->keyFld->par;
			if($oUpd->setId){
				$oUpd->p_id=$nn;
				$oPar=$oCmd->createParameter("p".$nn++,$oUpd->keyFld->type,1,-1);
				$oUpd->arrUpdate[]=$oPar;
			}
			$oUpd->arrUpdate[]=$this->fld_ts->par;

			$oUpd->strDelete="delete from {$oUpd->oFrom->dattab} where {$oUpd->keyFld->orgname}=? and ts=?";
			$oUpd->arrDelete[]=$oUpd->keyFld->par;
			$oUpd->arrDelete[]=$this->fld_ts->par;
		}else{
			if($oUpd->setId)$strUpdFlds.=",{$oUpd->keyFld->orgname}=?";
			$strUpdFlds=substr($strUpdFlds,1);

			$oUpd->strUpdate="update {$oUpd->oFrom->dattab} set $strUpdFlds where {$oUpd->keyFld->orgname}=?";
			$oUpd->arrUpdate[]=$oUpd->keyFld->par;
			if($oUpd->setId){
				$oUpd->p_id=$nn;
				$oPar=$oCmd->createParameter("p".$nn++,$oUpd->keyFld->type,1,-1);
				$oUpd->arrUpdate[]=$oPar;
			}

			$oUpd->strDelete="delete from {$oUpd->oFrom->dattab} where {$oUpd->keyFld->orgname}=?";
			$oUpd->arrDelete[]=$oUpd->keyFld->par;
		}
		return true;
	}

	function fromInsert($oUpd){
	global $oUser,$oMesul;
		if(isset($this->fld_ktarih) && empty($this->rec_ktarih))$this->rec_ktarih=date("Y-m-d");
		//if(isset($this->fld_atarih) && empty($this->rec_atarih))$this->rec_atarih=date("Y-m-d");
		if(isset($this->fld_dtarih) && empty($this->rec_dtarih))$this->rec_dtarih=date("Y-m-d");

		if(isset($this->fld_kuser)	&& empty($this->rec_kuser)) $this->rec_kuser =$oUser->id;
		if(isset($this->fld_sirket)	&& empty($this->rec_sirket))$this->rec_sirket=$oUser->sirket;
        if(isset($this->fld_perso)  && empty($this->rec_perso)) $this->rec_perso =$oUser->perso;
        if(isset($this->fld_mesul)  && empty($this->rec_mesul)) $this->rec_mesul =$oMesul->id;

		if(isset($this->fld_duser)  && empty($this->rec_duser))	$this->rec_duser =$oUser->id;
		if(isset($this->fld_dsaat)  && empty($this->rec_dsaat))	$this->rec_dsaat =date("H:i:s");
		if(isset($this->fld_ksaat)  && empty($this->rec_ksaat))	$this->rec_ksaat =date("H:i:s");

		if(isset($this->fld_ts))								$this->rec_ts    =date("Y-m-d H:i:s.u");
		if(isset($this->fld_indexp) && isset($this->fld_exp))$this->rec_indexp=$this->to_Indexp($this->rec_exp);

		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dblink;
		try{
			foreach($oUpd->arrFields as $uFld){
				if($uFld->int && empty($uFld->value))$uFld->value=-1;
				if($uFld->type==129 && trim($uFld->value)=="")$uFld->par->value=" ";
				elseif(strpos(",,8,202,130,203,", ",$uFld->type,")>0 && trim($uFld->value)=="")$uFld->par->value="\0";
				elseif(($uFld->char=="D" || $uFld->char=="T") && trim($uFld->value)=="")$uFld->par->value="00:00";
				else$uFld->par->value=$uFld->value;
			}
			if($oUpd->autoInc){
				$oCmd->commandText=$oUpd->strInsrt2;
				$suc=$oCmd->execute($this->affected,$oUpd->arrInsrt2);
			}else{
				if($oUpd->setInc){
					$oUpd->keyFld->value=$this->get_SETUP_ID($oUpd->denk?$oUpd->denk:$oUpd->oFrom->orgtable);
					$oUpd->keyFld->par->value=$oUpd->keyFld->value;
				}
				$oCmd->commandText=$oUpd->strInsert;
				$suc=$oCmd->execute($this->affected,$oUpd->arrInsert);
			}
		}catch(exception $e){
			$this->msg=$e->getMessage();
			return false;
		}
		if($oUpd->autoInc){
			$oUpd->keyFld->value=$this->get_AUTO_ID($oUpd->oFrom->orgtable);
			if(isset($oUpd->keyFld->ifd))$oUpd->keyFld->ifd->value=$oUpd->keyFld->value;
		}
		return true;
	}

	function fromUpdate($oUpd){
	global $oUser;
		if(isset($this->fld_dtarih))$this->rec_dtarih=date("Y-m-d");
		if(isset($this->fld_duser))	$this->rec_duser =$oUser->id;
		if(isset($this->fld_dsaat))	$this->rec_dsaat =date("H:i:s");
		if(isset($this->fld_indexp) && isset($this->fld_exp))$this->rec_indexp=$this->to_Indexp("$this->rec_exp");

		if(isset($oUpd->keyFld->ifd))$oUpd->keyFld->value=$oUpd->keyFld->ifd->value;
		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dblink;
		try{
			foreach($oUpd->arrFields as $uFld){
				if($uFld->int && empty($uFld->value))$uFld->value=-1;
				if($uFld->type==129 && trim($uFld->value)=="")$uFld->par->value=" ";
				elseif(strpos(",,8,202,130,203,", ",$uFld->type,")>0 && trim($uFld->value)=="")$uFld->par->value="\0";
				elseif(($uFld->char=="D" || $uFld->char=="T") && trim($uFld->value)=="")$uFld->par->value="00:00";
				else$uFld->par->value=$uFld->value;
			}
			if(isset($oUpd->fld_ts)){
				$oUpd->ts=date("Y-m-d H:i:s.u");
				$oUpd->arrUpdate[$oUpd->p_ts]->value=$oUpd->ts;
				$oUpd->fld_ts->par->value=$oUpd->rec_ts;
			}
			if($oUpd->setId)$oUpd->arrUpdate[$oUpd->p_id]->value=$this->id;
			$oCmd->commandText=$oUpd->strUpdate;
			$oCmd->execute($this->affected,$oUpd->arrUpdate);
		}catch(exception $e){
			$this->msg=$e->getMessage();
			return false;
		}
		return true;
	}

	function fromDelete($oUpd){
		if(isset($oUpd->keyFld->ifd))$oUpd->keyFld->value=$oUpd->keyFld->ifd->value;
		$oCmd=new COM("ADODB.Command");
		$oCmd->activeConnection=$this->dblink;
		try{
			if(isset($oUpd->fld_ts))$oUpd->fld_ts->par->value=$oUpd->rec_ts;
			$oUpd->keyFld->par->value=$oUpd->keyFld->value;
			$oCmd->commandText=$oUpd->strDelete;
			$oCmd->execute($this->affected,$oUpd->arrDelete);
		}catch(exception $e){
			$this->msg=$e->getMessage();
			return false;
		}
		return true;
	}

	function dataSeek($offset){
		if($offset==-1){$this->first();$this->RS->MovePrevious();}
		else$this->RS->move($offset,1);
		if(!$this->RS->BOF && !$this->RS->EOF)$this->load_rec();else $this->load_empty();
	}
	function next(){
		if(!$this->RS->EOF)$this->RS->moveNext();
		if(!$this->RS->EOF)$this->load_rec();else $this->load_empty();
		return !$this->RS->EOF;
	}
	function first(){
		if(!$this->RS->EOF)$this->RS->moveFirst();
		if(!$this->RS->EOF)$this->load_rec();else $this->load_empty();
		return $this->RS->EOF;
	}
	function load_rec(){
		foreach($this->arrFields as $oFld){
			//echo "$oFld->name: $oFld->value<br>";
			if($oFld->char=="S")	$oFld->value=trim($oFld->fld->value);
			elseif($oFld->char=="N")$oFld->value=floatval(strtr($oFld->fld->value,',','.'));
			elseif($oFld->char=="D")$oFld->value=($oFld->fld->value=="00:00:00"?null:$oFld->fld->value);
			else					$oFld->value=$oFld->fld->value;
		}
	}
	function load_empty(){foreach($this->arrFields as $oFld)$oFld->value=$oFld->emptyval;}

	function char_type($char){
		$fld_type=200;
		switch($char){
		case "S": $fld_type=200; break;
		case "N": $fld_type=5; break;
		case "I": $fld_type=5;   break;
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
					$e_value="";
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
	function empty_dat($fld_type){
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
					$e_value=0;
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
					$e_value=0;
					break;
		case 204:	//adVarBinary		Indicates a binary value.
		case 205:	//adLongVarBinary	Indicates a long binary value.
		case 138:	//adPropVariant		Indicates an Automation PROPVARIANT (DBTYPE_PROP_VARIANT).
					$e_value=0;
					break;
		case 132:	//adUserDefined		Indicates a user-defined variable (DBTYPE_UDT).
		case 12	:	//adVariant			Indicates an Automation Variant (DBTYPE_VARIANT).	This data type is currently not supported by ADO. Usage may cause unpredictable results.
					$e_value=0;
					break;
		}
		return $e_value;
	}
}
?>