<?php
function connect_Sqlite($strConn){
	$db_link=new SQLite3($strConn);
	return $db_link;
}
include_once("$REAL_P/_class/data__qry.php");
class clsSqlite extends cls__qry{
	protected $stmt=null;
	protected $RS=null;

	function findParams(&$strSql){
		if(preg_match_all("/((:prm_(\w+))(:[SNIDTHMWQL])?(\W|$))/iU",$strSql,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match){
				$ii=stripos($strSql,$match[1]);
				$strSql=substr_replace($strSql,"$match[2]$match[5]",$ii,strlen($match[1]));

				$name=$match[3];
				$par_name=strtolower("par_$name");
				if(!isset($this->$par_name)){
					$prm_name="prm_$name";
					$this->$prm_name=null;
					$oPar=(object)array("name"=>$name);
					$oPar->char=empty($match[4])?"S":$match[4];
					$oPar->value=&$this->$prm_name;
					$this->$par_name=$oPar;
				}
				$this->arrParams[]=&$this->$par_name;
			}
	}

	function bind_where(&$addWhere,&$arrBind){
		$nn=0;
		if(count($this->arrParams)){
			foreach($this->arrParams as $par){
				$prm_name="prm_".$par->name;
				$whr1=":$prm_name";
				$arrBind[$whr1]=&$this->$prm_name;
			}
		}

		$addWhere=empty($addWhere)?"":" and $addWhere";
		foreach($this->arrWhere as $whr){
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
				$whr1=":whr".$nn++;
				$arrBind[$whr1]=&$whr->{"val$ii"};
				$not=$whr->{"not$ii"}?"not ":"";
				$orWhere.=" or $not$whr->fromfld $oprWhr $whr1";
			}
			$orWhere=substr($orWhere,4);

			if($opr==">>"){
				$nn-=2;
				$whr1=":whr".$nn++;
				$whr2=":whr".$nn++;
				$addWhere .= " and ($whr->fromfld between $whr1 and $whr2)";
			}else$addWhere.= " and ($orWhere)";
		}
		$addWhere=substr($addWhere,5);
	}

	function open($addWhere=null,$next=true){
		if($this->active) return false;

		$arrBind=array();
		$this->bind_where($addWhere,$arrBind);
		$sqlStr=$this->getSqlStr($addWhere);
		echo $sqlStr,"<br>";

		$stmt=$this->dblink->prepare($sqlStr);
		foreach($arrBind as $key=>$ref)$stmt->bindParam($key,$ref);
		if(!($this->RS=$stmt->execute())){
			echo $this->dblink->lastErrorMsg(),"<br/>",$sqlStr;
			return false;
		}

		$this->reccount=-1;
		$this->active=true;
		$this->setInfo();
		if($next)$this->next();
		return true;
	}
	
	function server_cursor($next=true){
		if (!$this->active) return false;
		if (empty($this->temp)) return false;
		$this->close();
		$this->info=false;

		$sqlStr="select * from temp.$this->temp";
		$stmt=$this->dblink->prepare($sqlStr);
		if(!($this->RS=$stmt->execute())){
			echo $this->dblink->lastErrorMsg(),"<br/>",$sqlStr;
			return false;
		}
		$this->reccount=-1;
		$this->active=true;
		$this->setInfo();
		if($next)$this->next();
		return true;
	}

	function exec($addWhere=null){
		if ($this->active) return false;

		$arrBind=array();
		$this->bind_where($addWhere,$arrBind);
		$sqlStr=$this->getSqlStr($addWhere);

		$stmt=$this->dblink->prepare($sqlStr);
		foreach($arrBind as $key=>$ref)$stmt->bindParam($key,$ref);
		if(!($this->RS=$stmt->execute())){
			echo $this->dblink->lastErrorMsg(),"<br/>",$sqlStr;
			return false;
		}

		$this->affected=$this->dblink->changes();
		if($this->sqltype=="insert")$this->insert_id=$this->dblink->lastInsertRowID();
		return true;
	}

	function close($dropTemp=false){
		$this->RS->finalize();
		$this->RS=null;
		$this->active=false;
		$this->reccount=0;
	}
	
	function setFromFields($oFro){
		if(isset($oFro->fields)) return;
		$sqlStr="select * from $oFro->dattab where 1=0";
		$stmt=$this->dblink->prepare($sqlStr);
		$res=$stmt->execute();
		for($ii=0;$ii<$res->numColumns();$ii++){
			$name=$this->propOrgName($res->columnName($ii));
			$type=$res->columnType($ii);
			$oFro->fields[strtolower($name)]=(object)array("name"=>$name,"type"=>$type);
		}
	}

	function setInfo(){
		if(!$this->active) return;
		if($this->info) return;

		$this->arrFields=array();
		if($this->setn)$this->setNames();
		for($ii=0;$ii<$this->RS->numColumns();$ii++){
			$name=$this->RS->columnName($ii);
			$type=$this->RS->columnType($ii);
			$oInf=(object)array();
			$oInf->name		= $name;
			$oInf->type		= $type;
			$oInf->owner	= $this;
			$oInf->order	= $ii;
			$oInf->from		= ($this->setn ? $this->arrNames[$oInf->order]->from    : "");
			$oInf->orgtable = ($oInf->from ? $this->arrFroms[$oInf->from]->orgtable : "");
			$oInf->orgname	= ($this->setn ? $this->arrNames[$oInf->order]->orgname : "");
			$oInf->char		= $this->type_char($oInf->type);
			$oInf->emptyval	= $this->empty_val($oInf->type);
			$oInf->like		= strpos(",,15,252,253,254,",",$oInf->type,")>0;
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

			$this->$fld_name->tfd=null;
			$this->$fld_name->tab=null;
		}
		$this->info=true;
	}

	function setFromUpdate($oFro=null,$oOpt=null){
		if(empty($oFro))return false;
		$keyname=isset($oOpt,$oOpt->key)&&!empty($oOpt->key) ? $oOpt->key : "id";
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

		$sqlStr="select * from $oFro->dattab where 1=0";
		$stmt=$this->dblink->prepare($sqlStr);
		$res=$stmt->execute();
		for($ii=0;$ii<$res->numColumns();$ii++){
			$name=$res->columnName($ii);
			$qFld=$this->fieldByOrgName($oFro->from,$name);
			if (isset($qFld)){
				$qFld->upd=!$qFld->read;
				$oUpd->arrFields[]=$qFld;

				$fld_name="fld_$qFld->orgname";
				$rec_name="rec_$qFld->orgname";
				$qry_name="rec_$qFld->name";
				$oUpd->$fld_name=$qFld;
				$oUpd->$rec_name=&$this->$qry_name;

				if($qFld->orgname==$oUpd->keyName)$oUpd->keyFld=$qFld;
			}else{
				$oInf->owner	= $this;
				$oInf->from		= $oFro->from;
				$oInf->orgtable	= $oFro->orgtable;
				$oInf->orgname	= $name;
				$oInf->char		= $this->type_char($oInf->type);
				$oInf->format	= $this->type_format($oInf->type);
				$oInf->emptyval	= $this->empty_val($oInf->type);
				$oInf->filter	= null;
				$oInf->int		= null;
				$oInf->req		= null;
				$oInf->read		= null;
				$oInf->upd		= true;

				$fld_name="fld_$name";
				$oUpd->$fld_name=$oInf;
				$oUpd->arrFields[]=&$oUpd->$fld_name;

				$rec_name="rec_$name";
				$oUpd->$rec_name=$oInf->emptyval;
				$oInf->value=&$oUpd->$rec_name;

				if($oInf->orgname==$oUpd->keyName)$oUpd->keyFld=$oInf;
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
		$nn=0;
		foreach ($oUpd->arrFields as $uFld){
			$rec_name   ="rec_$uFld->orgname";

			$par1=":par$nn++";
			$strInsFlds.=",$uFld->orgname";
			$strInsVals.=",$par1";
			$oUpd->arrInsert[$par1]=&$oUpd->$rec_name;

			if($uFld!=$oUpd->keyFld && $uFld->orgname!="ts"){
				$strInsFld2.=",$uFld->orgname";
				$strInsVal2.=",$par1";
				$oUpd->arrInsrt2[$par1]=&$oUpd->$rec_name;
				if($uFld->upd){
					$strUpdFlds.=",$uFld->orgname=$par1";
					$oUpd->arrUpdate[$par1]=&$oUpd->$rec_name;
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
			$strUpdFlds.=",ts=FROM_UNIXTIME(:ts)";
			$strUpdFlds=substr($strUpdFlds,1);
			$oUpd->arrUpdate[":ts"]=&$oUpd->ts;
			$oUpd->arrUpdate[":key"]=&$oUpd->keyFld->value;
			$oUpd->arrUpdate[":rec_ts"]=&$oUpd->rec_ts;
			$oUpd->strUpdate="update {$oUpd->oFrom->dattab} set $strUpdFlds where {$oUpd->keyFld->orgname}=:key and ts=:rec_ts";

			$oUpd->arrDelete[":key"]=&$oUpd->keyFld->value;
			$oUpd->arrDelete[":rec_ts"]=&$oUpd->rec_ts;
			$oUpd->strDelete="delete from {$oUpd->oFrom->dattab} where {$oUpd->keyFld->orgname}=:key and ts=:rec_ts";
		}else{
			$strUpdFlds=substr($strUpdFlds,1);
			$oUpd->strUpdate="update {$oUpd->oFrom->dattab} set $strUpdFlds where {$oUpd->keyFld->orgname}=:key";
			$oUpd->arrUpdate[":key"]=&$oUpd->keyFld->value;

			$oUpd->arrDelete[":key"]=&$oUpd->keyFld->value;
			$oUpd->strDelete="delete from {$oUpd->oFrom->dattab} where {$oUpd->keyFld->orgname}=:key";
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
		if(isset($this->fld_indexp) && isset($this->fld_exp))	$this->rec_indexp=$this->to_Indexp($this->rec_exp);

		foreach($oUpd->arrFields as $uFld)if($uFld->int && empty($uFld->value))$uFld->value=-1;
		if($oUpd->autoInc){
			$oUpd->stmt=$this->dblink->prepare($oUpd->strInsrt2);
			foreach($oUpd->arrInsrt2 as $key=>$ref)$oUpd->stmt->bindParam($key,$ref);
		}else{
			if($oUpd->setInc){
				$oUpd->keyFld->value=$this->get_SETUP_ID($oUpd->oFrom->orgtable);
				$oUpd->keyFld->par->value=$oUpd->keyFld->value;
			}
			$oUpd->stmt=$this->dblink->prepare($oUpd->strInsert);
			foreach($oUpd->arrInsert as $key=>$ref)$oUpd->stmt->bindParam($key,$ref);
		}
		if(!($suc=$oUpd->stmt->execute())){
			echo $this->dblink->lastErrorMsg(),"<br/>";
			return false;
		}elseif($oUpd->autoInc){
			$oUpd->keyFld->value=$this->dblink->lastInsertRowID();
			if(isset($oUpd->keyFld->ifd)) $oUpd->keyFld->ifd->value=$oUpd->keyFld->value;
		}
		$oUpd->stmt->close();
		return $suc;
	}

	function fromUpdate($oUpd){
		if(isset($this->fld_indexp) && isset($this->fld_exp))$this->rec_indexp=$this->to_Indexp($this->rec_exp);

		if(isset($oUpd->fld_ts))$oUpd->ts=time();
		if(isset($oUpd->keyFld->ifd))$oUpd->keyFld->value=$oUpd->keyFld->ifd->value;
		foreach($oUpd->arrFields as $uFld)if($uFld->int && empty($uFld->value))$uFld->value=-1;
		$oUpd->stmt=$this->dblink->prepare($oUpd->strUpdate);
		foreach($oUpd->arrUpdate as $key=>$ref)$oUpd->stmt->bindParam($key,$ref);
		$suc=$oUpd->stmt->execute();
		if(!$suc)echo $this->dblink->lastErrorMsg(),"<br/>";
		$oUpd->stmt->close();
		return $suc;
	}

	function fromDelete($oUpd){
		if(isset($oUpd->fld_ts))$oUpd->ts=time();
		if(isset($oUpd->keyFld->ifd))$oUpd->keyFld->value=$oUpd->keyFld->ifd->value;
		$oUpd->stmt=$this->dblink->prepare($oUpd->strDelete);
		foreach($oUpd->arrDelete as $key=>$ref)$oUpd->stmt->bindParam($key,$ref);
		$suc=$oUpd->stmt->execute();
		if(!$suc)echo $this->dblink->lastErrorMsg(),"<br/>";
		$oUpd->stmt->close();
		return $suc;
	}

	function dataSeek($offset){$this->RS->reset();}
	function next(){
		if(($arrRec=$this->RS->fetchArray(SQLITE3_NUM))){
			foreach($this->arrFields as $oFld){
				//echo "$oFld->order: $oFld->name,$oFld->type<br>";
				$oFld->value=$arrRec[$oFld->order];
			}
			return true;
		}
		foreach($this->arrFields as $oFld)$oFld->value=$oFld->emptyval;
		return false;
	}
	function first(){$this->RS->reset();}

	function type_format($fld_type){
		$str_format="s";
		switch($fld_type){
		case 1:		//TINYINT
		case 2:		//SMALLINT
		case 3:		//INTEGER
		case 8:		//BIGINT
		case 9:		//MEDIUMINT
		case 13:	//YEAR
		case 16:	//BIT
					$str_format="i";
					break;

		case 0:		//DECIMAL
		case 4:		//FLOAT
		case 5:		//DOUBLE
		case 246:	//DECIMAL
					$str_format="d";
					break;

		case 6:		//NULL
		case 7:		//TIMESTAMP
		case 10:	//DATE
		case 11:	//TIME
		case 12:	//DATETIME
		case 14:	//DATE
		case 15:	//VARCHAR
		case 247:	//ENUM
		case 248:	//SET
		case 253:	//VARCHAR
		case 254:	//CHAR
					$str_format="s";
					break;

		case 249:	//TINYBLOB
		case 250:	//MEDIUMBLOB
		case 251:	//LONGBLOB
		case 252:	//BLOB
		case 255:	//GEOMETRY
					$str_format="s";
					break;
		}
		return $str_format;
	}

	function type_char($fld_type){
		$char="S";
		switch($fld_type){
		case 1:		//TINYINT
		case 2:		//SMALLINT
		case 3:		//INTEGER
		case 8:		//BIGINT
		case 9:		//MEDIUMINT
		case 13:	//YEAR
		case 16:	//BIT
					$char="I";
					break;

		case 0:		//DECIMAL
		case 4:		//FLOAT
		case 5:		//DOUBLE
		case 246:	//DECIMAL
					$char="N";
					break;

		case 10:	//DATE
		case 14:	//DATE
					$char="D";
					break;

		case 7:		//TIMESTAMP
		case 12:	//DATETIME
					$char="T";
					break;

		case 11:	//TIME
					$char="H";
					break;

		case 6:		//NULL
		case 247:	//ENUM
		case 248:	//SET
					$char="I";
					break;

		case 15:	//VARCHAR
		case 253:	//VARCHAR
		case 254:	//CHAR
					$char="S";
					break;

		case 249:	//TINYBLOB
		case 250:	//MEDIUMBLOB
		case 251:	//LONGBLOB
					$char="M";
					break;

		case 252:	//BLOB
		case 255:	//GEOMETRY
					$char="W";
					break;
		}
		return $char;
	}

	function empty_val($fld_type){
		$e_value="";
		switch($fld_type){
		case 1:		//TINYINT
		case 2:		//SMALLINT
		case 3:		//INTEGER
		case 8:		//BIGINT
		case 9:		//MEDIUMINT
		case 13:	//YEAR
		case 16:	//BIT
		case 0:		//DECIMAL
		case 4:		//FLOAT
		case 5:		//DOUBLE
		case 246:	//DECIMAL
					$e_value=0;
					break;

		case 6:		//NULL
		case 15:	//VARCHAR
		case 247:	//ENUM
		case 248:	//SET
		case 253:	//VARCHAR
		case 254:	//CHAR
		case 249:	//TINYBLOB
		case 250:	//MEDIUMBLOB
		case 251:	//LONGBLOB
		case 252:	//BLOB
		case 255:	//GEOMETRY
					$e_value="";
					break;

		case 7:		//TIMESTAMP
		case 10:	//DATE
		case 11:	//TIME
		case 12:	//DATETIME
		case 14:	//DATE
					$e_value=null;
					break;
		}
		return $e_value;
	}
}
?>