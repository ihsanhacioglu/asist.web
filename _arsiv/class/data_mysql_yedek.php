<?php

class clsQry{
	private $dbLink=null;
	public $stmt=null;

	private $FieldStr="";
	private $FromStr="";
	private $WhereStr="";
	private $GroupStr="";
	private $OrderStr="";
	private $LimitStr="";

	private $sqltype="";
	private	$info=false;
	private $state="";

	public  $main=null;
	public	$insert_id=0;
	public  $temp="";
	public  $active=false;
	public  $strSql="";
	public	$message="";

	public	$ktarih="";
	public	$kuser=-1;
	public	$sirket=-1;
    public  $personel=-1;
	
	public	$arrFrom   =array();
	public  $arrParams =array();
	public  $arrFields =array();
	public	$arrNames  =array();
	public	$arrFroms  =array();
	public	$arrJoins  =array();
	public	$arrUpdates=array();
	public	$arrWhere  =array();
	public	$arrDetail =array();
	public	$arrCals   =array();

	public	$strNames="";
	public	$keyFrom="";
	public	$keyName="";
	public	$keyQry=null;
	public	$intFld="";
	public	$reqFld="";
	public	$readFld="";
	public	$reccount=0;
	public	$affected=0;
	public	$senaryo=0;
	public	$recs=array();
	
	function __construct($nLink,$strSql,$main=null){
		if(empty($nLink))	return false;
		if(empty($strSql))	return false;

		$this->dbLink=$nLink;
		$strSql=trim($strSql);
		$this->main=$main;

		if(preg_match_all("/(\?prm_(\w+)(:[SNIDTHMW])?(\W|$))/iU",$strSql,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match){
				$ii=stripos($strSql,$match[1]);
				$strSql=substr_replace($strSql,"?$match[4]",$ii,strlen($match[1]));

				$name=$match[2];
				$par_name="par_$name";
				if(!isset($this->$par_name)){
					$prm_name="prm_$name";
					$this->$prm_name=null;
					$oPar=(object)array("name"=>$name);
					$oPar->type=empty($match[3])?"S":$match[3];
					$oPar->value=&$this->$prm_name;
					$this->$par_name=$oPar;
				}
				$this->arrParams[]=&$this->$par_name;
			}

		if(preg_match_all("/(\bselect|update|insert|delete|from|where|order\s+by|group\s+by|limit\b)/iU",$strSql,$arr_match,PREG_OFFSET_CAPTURE)){
			$arrCla=$arr_match[0];
			$arrCla[count($arrCla)][1]=strlen($strSql);
			for($ii=0; $ii<count($arrCla)-1; $ii++){
				$strExpr=substr($strSql,$arrCla[$ii][1],$arrCla[$ii+1][1]-$arrCla[$ii][1]);
				$strCla=strtolower($arrCla[$ii][0]);
				if($strCla=="select" || $strCla=="update" ||
					$strCla=="insert" || $strCla=="delete") $this->FieldStr=$strExpr;
				elseif ($strCla=="from")  $this->FromStr =$strExpr;
				elseif ($strCla=="where") $this->WhereStr=$strExpr;
				elseif (substr($strCla,0,5)=="order") $this->OrderStr=$strExpr;
				elseif (substr($strCla,0,5)=="group") $this->GroupStr=$strExpr;
				elseif ($strCla=="limit") $this->LimitStr=$strExpr;
			}
			$this->sqltype=trim(substr($this->FieldStr,0,6));
		}

		$this->strSql=$strSql;
		if($this->sqltype=="select")$this->setFroms();
		return true;
	}

	function buildValue($strValue,$MAIN=null){
		$val=null;
		if(preg_match("/^\s*(\\\$(\w+)\.(\w+)|(\w+))\s*$/",$strValue,$vals)){
			if(!empty($vals[2])){
				if($vals[2]=="main"){if($MAIN && ($fld=$MAIN->fieldByName($vals[3])))$val=$fld->value;}
				else$val=$this->objVal($vals[2],$vals[3]);
			}elseif(!empty($vals[4])){
				if($MAIN)$fld=$MAIN->fieldByName($vals[4]);
				else$fld=$this->fieldByName($vals[4]);
				if($fld)$val=$fld->value;
				else$val=$vals[4];
			}
		}else{
			$val=trim($strValue);
			if (preg_match_all("/%(\w+)(\W|$)/U",$val,$flds,PREG_SET_ORDER))
			foreach($flds as $name){
				if($MAIN)$fld=$MAIN->fieldByName($name[1]);
				else$fld=$this->fieldByName($name[1]);
				if($fld)$val=str_replace("%$name[1]",$fld->value,$val);
			}
		}
		return $val;
	}

	function open($varWhere=null,$next=true){
		if($this->active) return false;
		$strFormat="";
		$arrBind=array();
		if (count($this->arrParams)){
			$arrBind[]=&$this->stmt; $arrBind[]=&$strFormat;
			foreach($this->arrParams as $par){
				$prm_name="prm_".$par->name;
				$strFormat.=is_numeric($this->$prm_name) ? "d" : "s";
				$arrBind[]=&$this->$prm_name;
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
			if(count($arrBind)==0){$arrBind[]=&$this->stmt; $arrBind[]=&$strFormat;}
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
					$arrBind[] = &$whr->{"val$ii"};
					$strFormat.= isset($whr->fld)?$whr->fld->format : is_numeric($whr->{"val$ii"})?"d":"s";
					$not=$whr->{"not$ii"}?"not ":"";
					$orWhere.=" or $not$whr->fromfld $oprWhr ?";
				}
				$orWhere=substr($orWhere,4);

				if($opr==">>") $addWhere .= " and ($whr->fromfld between ? and ?)";
				else$addWhere .= " and ($orWhere)";
			}
			$addWhere=substr($addWhere,5);
		}
		$sqlStr=$this->getSqlStr($addWhere);
		//echo "WHR: $addWhere<br/>";
		//echo "SQL: $sqlStr<br/>";

		if (!empty($this->temp)){
			$sqlStr="create table temp.$this->temp as ".$sqlStr;
			$_SESSION["tmptables"][]=$this->temp;
		}

		$this->stmt=mysqli_prepare($this->dbLink,$sqlStr);
		if (count($arrBind)>2) call_user_func_array("mysqli_stmt_bind_param",$arrBind);
		if (!mysqli_stmt_execute($this->stmt)){
			echo mysqli_error($this->dbLink),"<br/>",$sqlStr;
			return false;
		}
		if (!empty($this->temp)){
			$sqlStr="select * from temp.$this->temp";
			mysqli_stmt_close($this->stmt);
			$this->stmt=mysqli_prepare($this->dbLink,$sqlStr);
			if (!mysqli_stmt_execute($this->stmt)){
				echo mysqli_error($this->dbLink),"<br/>",$sqlStr;
				return false;
			}
		}

		mysqli_stmt_store_result($this->stmt);
		$this->reccount=mysqli_stmt_num_rows($this->stmt);

		$this->active=true;
		$this->setInfo();

		if ($next) $this->next();
		return true;
	}
	
	function openTemp($next=true){
		if (!$this->active) return false;
		if (empty($this->temp)) return false;
		$this->close();
		$this->info=false;

		$sqlStr="select * from temp.$this->temp";
		$this->stmt=mysqli_prepare($this->dbLink,$sqlStr);
		if (!mysqli_stmt_execute($this->stmt)){
			echo mysqli_error($this->dbLink),"<br/>",$sqlStr;
			return false;
		}
		mysqli_stmt_store_result($this->stmt);
		$this->reccount=mysqli_stmt_num_rows($this->stmt);

		$this->active=true;
		$this->setInfo();
		if ($next) $this->next();
		return true;
	}

	function exec($varWhere=null){
		if ($this->active) return false;

		$strFormat="";
		$arrBind=array();
		if (count($this->arrParams)){
			$arrBind[]=&$this->stmt; $arrBind[]=&$strFormat;
			foreach($this->arrParams as $par){
				$prm_name="prm_".$par->name;
				$strFormat.=is_numeric($this->$prm_name) ? "d" : "s";
				$arrBind[]=&$this->$prm_name;
			}
		}
		$arrCond=null; $addWhere="";
		if     (is_string($varWhere)) $addWhere=$varWhere;
		elseif (is_object($varWhere)) $arrCond[]=$varWhere;
		elseif (is_array ($varWhere)) $arrCond=&$varWhere;
		
		if (is_array($arrCond) && count($arrCond)){
			$addWhere=empty($addWhere)?"":" and $addWhere";
			if (count($arrBind)==0) {$arrBind[]=&$this->stmt; $arrBind[]=&$strFormat;}
			foreach ($arrCond as $whr){
				if (isset($whr->format)) $format=($whr->format=="d" || $whr->format=="i" ? $whr->format : "s");
				else $format=is_numeric($whr->value) ? "d" : "s";
				$opr=isset($whr->opr)?$whr->opr:"";

				if(isset($whr->value2)){
					$arrBind[] = &$whr->value;
					$strFormat.= $format;
					$arrBind[] = &$whr->value2;
					$strFormat.= $format;
					$addWhere .= " and $whr->fromfld between ? and ?";
				}elseif (empty($opr)){
					if ($whr->like){
						$orLike=""; $ii=0;
						if (preg_match_all("/\s*(.+)\s*(,|$)/U",$whr->value,$arr_match,PREG_SET_ORDER))
							foreach ($arr_match as $match){
								if (empty($match[1])) continue;
								$ii++; $whr->{"val$ii"}="$match[1]%";
								$arrBind[] = &$whr->{"val$ii"};
								$strFormat.= $format;
								$orLike.=" or $whr->fromfld like ?";
							}
						$orLike=substr($orLike,4);
						if (!empty($orLike)) $addWhere .= " and ($orLike)";
					}else{
						$arrBind[] = &$whr->value;
						$strFormat.= $format;
						$addWhere .= " and $whr->fromfld = ?";
					}
				}elseif ($opr=="~"){
					$orLike=""; $ii=0;
					if (preg_match_all("/\s*(.+)\s*(,|$)/U",$whr->value,$arr_match,PREG_SET_ORDER))
						foreach ($arr_match as $match){
							if (empty($match[1])) continue;
							$ii++; $whr->{"val$ii"}="$match[1]%";
							$arrBind[] = &$whr->{"val$ii"};
							$strFormat.= $format;
							$orLike.=" or $whr->fromfld like ?";
						}
					$orLike=substr($orLike,4);
					if (!empty($orLike)) $addWhere .= " and ($orLike)";
				}else{
					$orOpr=""; $ii=0;
					if (preg_match_all("/\s*(.+)\s*(,|$)/U",$whr->value,$arr_match,PREG_SET_ORDER))
						foreach ($arr_match as $match){
							if (empty($match[1])) continue;
							$ii++; $whr->{"val$ii"}="$match[1]";
							$arrBind[] = &$whr->{"val$ii"};
							$strFormat.= $format;
							$orOpr.=" or $whr->fromfld $opr ?";
						}
					$orOpr=substr($orOpr,4);
					if (!empty($orOpr)) $addWhere .= " and ($orOpr)";
				}
			}
			$addWhere=substr($addWhere,5);
		}
		$sqlStr=$this->getSqlStr($addWhere);

		$this->stmt=mysqli_prepare($this->dbLink,$sqlStr);
		if (count($arrBind)) call_user_func_array("mysqli_stmt_bind_param",$arrBind);
		if (!mysqli_stmt_execute($this->stmt)){
			echo mysqli_error($this->dbLink),"<br/>",$sqlStr;
			return false;
		}
		$this->affected=mysqli_stmt_affected_rows($this->stmt);
		if ($this->sqltype=="insert") $this->insert_id=mysqli_insert_id($this->dbLink);
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
		if ($this->active) mysqli_stmt_close($this->stmt);
		if ($dropTemp && !empty($this->temp)){
			mysqli_query($cDblink,"drop table temp.$this->temp");
			$this->temp="";
		}
		$this->active=false;
		$this->reccount=0;
	}
	
	function setNames($strNames=""){
		if (!empty($strNames)) $this->strNames=$strNames;
		$strNames=($this->sqltype=="select"?substr($this->FieldStr,6).", ":"").$this->strNames;

		$this->arrNames=array();
		if (preg_match_all("/\s*(((\w+)\.)?(\w+|\*))(\s+(\w+))?\s*(,|$)/U",$strNames,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$name=empty($match[6]) ? $match[4] : $match[6];
			if (isset($this->arrNames[$name]))
			if($name!="*"){
				$obj=$this->arrNames[$name];
				$obj->from=empty($obj->from)?$match[3]:$obj->from;
				$obj->orgname=$match[4];
				$obj->fromfld=empty($obj->fromfld)?$match[1]:$obj->fromfld;
			}else{
				$obj=(object)array("name"=>$name,"from"=>$match[3],"orgname"=>$match[4],"fromfld"=>$match[1]);
				$this->arrNames[$name]=$obj;
			}
		}
	}

	function setIliski(){
		$this->iliski="{$this->arrFroms[0]->orgtable}-$this->rec_id";
	}

	function setFroms($strFroms=""){
		$strFroms="$this->FromStr, $strFroms";

		$ii=0;
		$this->arrFroms=array();
		if (preg_match_all("/\s*(((\w+)\.)?(\w+))(\s+(\w+))?\s*(,|$)/U",$strFroms,$arr_match,PREG_SET_ORDER))
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
		$key=null;
		$from=null;
		$chkey=null;
		if (preg_match_all("/(\w+)\s*(:(.+))?\s*(;|$)/U",$strUpdates,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$from=$match[1]; $keyname=$match[3];
			if(!isset($this->arrFroms[$from])) continue;
			$oOpt->qry ="0";
			$oOpt->key ="";
			$oOpt->chkey="0";
			if(preg_match_all("/(\w+)\s*(=(.+))?\s*(,|$)/U",$match[3],$arr_opt,PREG_SET_ORDER))
				foreach($arr_opt as $opt)$oOpt->{"$opt[1]"}=$opt[3];
			if($oOpt->qry==1){
				$key=$oOpt->key;
				$chkey=$oOpt->chkey;
				break;
			}
		}
		if(empty($key)){$key="id";$this->arrFroms[0]->from;}
		$this->keyName="$key";
		$this->keyFrom="$from.$key";
		$this->keyCh=$chkey==1;
		$this->keyQry=$this->fieldByOrgName($from,$key);
		if($this->keyQry){
			$this->keyName=$this->keyQry->name;
			$this->keyFrom=$this->keyQry->from.".".$this->keyQry->orgname;
		}
	}

	function setFromFields($from){
		if (!isset($this->arrFroms[$from]) || isset($this->arrFroms[$from]->fields)) return;
		foreach($this->arrFields as $oFld) if ($oFld->table==$from) $this->arrFroms[$from]->fields[]=$oFld;
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
			$this->setFromFields($sol->from);
			$this->setFromFields($sag->from);
		}
	}

	function setInfo(){
		if (!$this->active) return;
		$arrBind=array();
		if ($this->info){
			$arrBind[]=&$this->stmt;
			foreach($this->arrFields as $oFld)
				$arrBind[]=&$this->{"rec_$oFld->name"};
			call_user_func_array("mysqli_stmt_bind_result",$arrBind);
			return;
		}

		$this->arrFields=array();
		$res=mysqli_stmt_result_metadata($this->stmt);

		$nn=0;
		$arrBind[]=&$this->stmt;
		while ($oInfo=mysqli_fetch_field($res)){
			$oInfo->owner=$this;
			$oInfo->order=$nn++;
			$oInfo->from = $oInfo->table;
			$oInfo->orgtable  = empty($oInfo->orgtable) ? $oInfo->table : $oInfo->orgtable;
			$oInfo->orgname   = empty($oInfo->orgname)  ? $oInfo->name  : $oInfo->orgname;
			$oInfo->typechar  = $this->type_char($oInfo->type);
			$oInfo->format    = $this->type_format($oInfo->type);
			$oInfo->emptyval  = $this->empty_val($oInfo->type);
			$oInfo->like      = strpos(",,15,252,253,254,",",$oInfo->type,")>0;
			$oInfo->filter    = null;
			$oInfo->int       = null;
			$oInfo->req       = null;
			$oInfo->read      = null;
			$oInfo->upd		  = null;

			$fld_name="fld_".$oInfo->name;
			$this->$fld_name=$oInfo;
			$this->arrFields[]=&$this->$fld_name;

			$rec_name="rec_$oInfo->name";
			$this->$rec_name=$oInfo->emptyval;
			$oInfo->value=&$this->$rec_name;

			$arrBind[]=&$this->$rec_name;
			$this->$fld_name->tfd=null;
			$this->$fld_name->tab=null;
		}
		call_user_func_array("mysqli_stmt_bind_result",$arrBind);
		$this->info=true;
	}

	function setTabFields($oTab){
		foreach ($this->arrFields as $qFld)
		if ($oTab->from==$qFld->table && ($tFld=$oTab->fieldByName($qFld->orgname))){
			$qFld->tfd=$tFld;
			$qFld->tab=$oTab;
			$tFld->qfd=$qFld;
			$tFld->upd=true;
		}
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

		$this->arrUpdates=array();$ii=0;
		if (preg_match_all("/\s*(\w+)\s*(:\s*(\w+)?\s*(:\s*(q))?)?\s*(,|$)/U",$strUpdates,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$ii++;
			$from=$match[1]; $keyname=$match[3];
			if(!isset($this->arrFroms[$from])) continue;
			$strTable=$this->arrFroms[$from]->dattab.(empty($keyname) ? "" : ":$keyname");
			if (empty($match[5])){$oTab=new clsTable($this->dbLink,$strTable,null,null,$from);$this->setTabFields($oTab);}
			else $oTab=new clsTable($this->dbLink,$strTable,null,$this,$from);
			$tab_name="tab_".count($this->arrUpdates);
			$this->$tab_name=$oTab;
			$this->arrUpdates[$from]=&$this->$tab_name;
			$oTab->autoInc=true;
			if($ii==1)$this->keyQry=$oTab->keyFld;
		}
		if (count($this->arrUpdates)>1){
			$this->setJoins();
			foreach ($this->arrUpdates as $from=>$oTab){
				if (isset($oTab->keyFld->qfd))continue;
				foreach ($this->arrJoins as $oJoin)
				if ($oJoin->from==$from && is_null($oJoin->fld) && !is_null($oJoin->esit->fld))
					$oTab->keyFld->qfd=$oJoin->esit->fld;
			}
		}
	}

	function setUpdates2($strUpdates=""){
		if (!$this->info) return false;

		$this->setIntFld();
		$this->setReqFld();
		$this->setReadFld();

		$this->arrUpdates=array();
		if (preg_match_all("/(\w+)\s*(:(\w+)\s*(:q)?)?\s*(,|$)/U",$strUpdates,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			$from=$match[1]; $keyname=$match[3];
			if(!isset($this->arrFroms[$from])) continue;
			$this->setFromUpdate($this->arrFroms[$from],$keyname,$match[4]);
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
	
	function setFromUpdate($oFrom=null,$keyname=null,$keyQry=null){
		if (empty($oFrom)) return false;
		$keyname=empty($keyname)?"id":$keyname;
		$oUpd->oFrom	 = $oFrom;
		$oUpd->stmt		 = null;
		$oUpd->autoInc	 = true;
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

		$res=mysqli_query($this->dbLink,"select * from $oFrom->dattab where 1=0");
		if (mysqli_errno($this->dbLink)) {echo mysqli_error($this->dbLink); return false;}
		while ($oInfo=mysqli_fetch_field($res)){
			$qFld=$this->fieldByOrgName($oFrom->from,$oInfo->name);
			if (isset($qFld)){
				$qFld->upd=!$qFld->read;
				$oUpd->arrFields[]=$qFld;

				$fld_name="fld_$qFld->orgname";
				$rec_name="rec_$qFld->orgname";
				$qry_name="rec_$qFld->name";
				$oUpd->$fld_name=$qFld;
				$oUpd->$rec_name=&$this->$qry_name;

				if($qFld->name==$oUpd->keyName){
					$oUpd->keyFld=$qFld;
					if($keyQry)$this->keyQry=$qFld;
				}
			}else{
				$oInfo->owner=$this;
				$oInfo->orgtable=empty($oInfo->orgtable) ? $oInfo->table : $oInfo->orgtable;
				$oInfo->orgname =empty($oInfo->orgname)  ? $oInfo->name  : $oInfo->orgname;
				$oInfo->typechar=$this->type_char($oInfo->type);
				$oInfo->format  =$this->type_format($oInfo->type);
				$oInfo->emptyval=$this->empty_val($oInfo->type);
				$oInfo->upd=true;
				$oUpd->arrFields[]=$oInfo;

				$fld_name="fld_$oInfo->name";
				$rec_name="rec_$oInfo->name";
				$oUpd->$fld_name=$oInfo;
				$oUpd->$rec_name=$oInfo->emptyval;
				$oInfo->value=&$oUpd->$rec_name;

				if($oInfo->name==$oUpd->keyName) $oUpd->keyFld=$oInfo;
			}
	    }
		if (is_null($oUpd->keyFld)) return false;
		$this->arrUpdates[$oFrom->from]=$oUpd;

		$strInsFlds="";
		$strInsFld2="";
		$strInsVals="";
		$strInsVal2="";
		$strUpdFlds="";

		$oUpd->arrInsert=array();  $oUpd->arrInsert[]=&$oUpd->stmt;  $oUpd->arrInsert[]=&$oUpd->frmtInsert;
		$oUpd->arrInsrt2=array();  $oUpd->arrInsrt2[]=&$oUpd->stmt;  $oUpd->arrInsrt2[]=&$oUpd->frmtInsrt2;
		$oUpd->arrUpdate=array();  $oUpd->arrUpdate[]=&$oUpd->stmt;  $oUpd->arrUpdate[]=&$oUpd->frmtUpdate;

		foreach ($oUpd->arrFields as $uFld){
			$rec_name   ="rec_$uFld->orgname";

			$strInsFlds.=",$uFld->orgname";
			$strInsVals.=",?";
			$oUpd->frmtInsert.=$uFld->format;
			$oUpd->arrInsert[]=&$oUpd->$rec_name;

			if ($uFld!=$oUpd->keyFld && $uFld->orgname!="ts"){
				$strInsFld2.=",$uFld->orgname";
				$strInsVal2.=",?";
				$oUpd->frmtInsrt2.=$uFld->format;
				$oUpd->arrInsrt2[]=&$oUpd->$rec_name;
				if ($uFld->upd){
					$strUpdFlds.=",$uFld->orgname=?";
					$oUpd->frmtUpdate.=$uFld->format;
					$oUpd->arrUpdate[]=&$oUpd->$rec_name;
				}
			}
	    }

		$strInsFlds=substr($strInsFlds,1);
		$strInsFld2=substr($strInsFld2,1);
		$strInsVals=substr($strInsVals,1);
		$strInsVal2=substr($strInsVal2,1);

		$oUpd->strInsert="insert into $oUpd->dattab ($strInsFlds) values ($strInsVals)";
		$oUpd->strInsrt2="insert into $oUpd->dattab ($strInsFld2) values ($strInsVal2)";
		if(isset($oUpd->fld_ts)){
			$strUpdFlds.=",ts=FROM_UNIXTIME(?)";
			$strUpdFlds=substr($strUpdFlds,1);
			$oUpd->strUpdate="update $oUpd->dattab set $strUpdFlds where {$oUpd->keyFld->orgname}=? and ts=?";
			$oUpd->frmtUpdate.="i";
			$oUpd->frmtUpdate.=$oUpd->keyFld->format;
			$oUpd->frmtUpdate.=$oUpd->fld_ts->format;
			$oUpd->arrUpdate[]=&$oUpd->ts;
			$oUpd->arrUpdate[]=&$oUpd->keyFld->value;
			$oUpd->arrUpdate[]=&$oUpd->rec_ts;
		}else{
			$strUpdFlds=substr($strUpdFlds,1);
			$oUpd->strUpdate="update $oUpd->dattab set $strUpdFlds where {$oUpd->keyFld->orgname}=?";
			$oUpd->frmtUpdate.=$oUpd->keyFld->format;
			$oUpd->arrUpdate[]=&$oUpd->keyFld->value;
		}
		return true;
	}

	function fromInsert($oUpd){
		if(isset($oUpd->fld_ktarih)   && empty($oUpd->rec_ktarih))	$oUpd->rec_ktarih=$this->ktarih;
		if(isset($oUpd->fld_kuser)	  && empty($oUpd->rec_kuser))	$oUpd->rec_kuser =$this->kuser;
		if(isset($oUpd->fld_sirket)	  && empty($oUpd->rec_sirket))	$oUpd->rec_sirket=$this->sirket;
        if(isset($oUpd->fld_personel) && empty($oUpd->rec_personel))$oUpd->rec_personel=$this->personel;
		foreach($oUpd->arrFields as $oFld) if ($oFld->int && empty($oFld->value)) $oFld->value=-1;

		if($oUpd->autoInc){
			$oUpd->stmt=mysqli_prepare($this->dbLink,$oUpd->strInsrt2);
			call_user_func_array("mysqli_stmt_bind_param",$oUpd->arrInsrt2);
		}else{
			$oUpd->stmt=mysqli_prepare($this->dbLink,$oUpd->strInsert);
			call_user_func_array("mysqli_stmt_bind_param",$oUpd->arrInsert);
		}

		$suc=mysqli_stmt_execute($oUpd->stmt);
		if(!$suc)echo mysqli_error($this->dbLink),"<br/>";
		elseif($oUpd->autoInc){
			$oUpd->keyFld->value=mysqli_insert_id($this->dbLink);
			if(isset($oUpd->keyFld->ifd)) $oUpd->keyFld->ifd->value=$oUpd->keyFld->value;
		}
		mysqli_stmt_close($oUpd->stmt);
		return $suc;
	}

	function fromUpdate($oUpd){
		if(isset($oUpd->fld_ts)) $oUpd->ts=time();
		if(isset($oUpd->keyFld->ifd)) $oUpd->keyFld->value=$oUpd->keyFld->ifd->value;
		$oUpd->stmt=mysqli_prepare($this->dbLink,$oUpd->strUpdate);
		call_user_func_array("mysqli_stmt_bind_param",$oUpd->arrUpdate);
		$suc=mysqli_stmt_execute($oUpd->stmt);
		if(!$suc)echo mysqli_error($this->dbLink),"<br/>";
		mysqli_stmt_close($oUpd->stmt);
		return $suc;
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

	function addParam2($varWhere=null){
		$arrCond=null;$addWhere="";
		if     (is_string($varWhere)) $addWhere=$varWhere;
		elseif (is_object($varWhere)) $arrCond[]=$varWhere;
		elseif (is_array ($varWhere)) $arrCond=&$varWhere;

		if (is_array($arrCond)){
			foreach($arrCond as $whr){
				$name=count($this->arrParams);
				$value=$whr->value;
				$par_name="par_$name";
				$this->$par_name=(object)array("name"=>$name,"format"=>(is_numeric($value)?"d":"s"));
				$prm_name="prm_$name";
				$this->$prm_name=$value;
				$this->$par_name->value=&$this->$prm_name;
				$addWhere .= " and $whr->fromfld=?";
				$this->arrParams[]=&$this->$par_name;
			}
			$addWhere=substr($addWhere,5);
		}
		$this->WhereStr=empty($this->WhereStr) ? "where $addWhere" : "$this->WhereStr and $addWhere";
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
		foreach ($this->arrFields as $oFld) if(is_null($oFld->filter)) $oFld->value=$oFld->emptyval;
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
		mysqli_stmt_data_seek($this->stmt,$offset);
	}
	function next(){
		if (!mysqli_stmt_fetch($this->stmt)) return false;
		return true;
	}

	function insert(){
		foreach($this->arrFields as $oFld) if(isset($oFld->int) && empty($oFld->value))$oFld->value=-1;
		$this->bindTabVals();
		foreach ($this->arrUpdates as $oTab) if(!$oTab->insert()) return false;
		return true;
	}

	function update(){
		foreach($this->arrFields as $oFld) if(isset($oFld->int) && empty($oFld->value))$oFld->value=-1;
		$this->bindTabVals();
		foreach ($this->arrUpdates as $oTab) if(!$oTab->update()) return false;
		return true;
	}

	function fieldByOrgName($from,$orgname){
		$retFld=null;
		foreach($this->arrFields as $fld) if($fld->table==$from && $fld->orgname==$orgname){$retFld=$fld; break;}
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

	function bindTabVals(){
		foreach($this->arrFields as $oFld)
		if (isset($oFld->tfd)){
			$oFld->tfd->value=$oFld->value;
			//echo $oFld->tfd->table,".",$oFld->tfd->name," = ",$oFld->tfd->value,"<br/>";
		}
	}

	function bindPostName($frm_name){
		if(!isset($_POST[$frm_name]) || substr($frm_name,0,4)!="frm_") return;
		$fld_name="fld_".substr($frm_name,4);
		if(!isset($this->$fld_name)) return;
		$this->$fld_name->value=$_POST[$frm_name];
	}

	function bindPostVals(){
		foreach($this->arrFields as $oFld){
			$post_name="frm_$oFld->name";
			if(isset($_POST[$post_name]))
				if(empty($_POST[$post_name]))
					$oFld->value=$oFld->emptyval;
				elseif(strpos(",,15,253,254,",",$oFld->type,"))
					$oFld->value=substr($_POST[$post_name],0,$oFld->length);
				else$oFld->value=$_POST[$post_name];

			$post_name="frm2_$oFld->name";
			if(isset($_POST[$post_name]))
				if(empty($_POST[$post_name]))
					$oFld->value2=$oFld->emptyval;
				elseif(strpos(",,15,253,254,",",$oFld->type,"))
					$oFld->value2=substr($_POST[$post_name],0,$oFld->length);
				else$oFld->value2=$_POST[$post_name];
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

	function type_format($fld_type){
		$str_format="s";
		switch ($fld_type)
		{
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
		$str_format="s";
		switch ($fld_type)
		{
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

	function empty_val($fld_type)
	{
		$e_value="";
		switch ($fld_type)
		{
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

	function print_vals(){
		foreach ($this->arrFields as $oFld)
			echo "<br/>",$oFld->name,"(",$oFld->format,"): ",is_null($oFld->value) ? "null" : $oFld->value;
	}

	function print_pars(){
		foreach ($this->arrParams as $oPar)
			echo "<br/>",$oPar->name,"(",$oPar->type,"): ",is_null($oPar->value) ? "null" : $oPar->value;
	}
    
	function objVal($objName="",$strAttr=""){
		global $oUser, $oPerso, $oSirket;
		$val=null;
		switch ($objName){
			case "ouser" :	if(isset($oUser->$strAttr))  $val=$oUser->$strAttr;  break;
			case "operso":	if(isset($oPerso->$strAttr)) $val=$oPerso->$strAttr; break;
			case "osirket":	if(isset($oSirket->$strAttr))$val=$oSirket->$strAttr; break;
			case "ozaman":	if    ($strAttr=="bugun")    $val=date("Y-m-d");
							elseif($strAttr=="busaat")   $val=date("H:i:s");
							elseif($strAttr=="zaman")    $val=date("Y-m-d H:i:s");
							elseif($strAttr=="yarin")    $val=date("Y-m-d",strtotime(date("Y-m-d")." +1 day"));
							elseif($strAttr=="adonem")   $val=-((date("Y")-2000)*100+date("m"));
							elseif($strAttr=="haftabasi")$val=date("Y-m-d",strtotime((-date("N")+1)." day"));
							elseif($strAttr=="aybasi")   $val=date("Y-m-01");
							elseif($strAttr=="aysonu")   $val=date("Y-m-d",strtotime(date("Y-m-1")." +1 month -1 day"));
							elseif($strAttr=="yilbasi")  $val=date("Y-01-01");
							elseif($strAttr=="yilsonu")  $val=date("Y-12-31");
							elseif($strAttr=="g_aybasi") $val=date("Y-m-d",strtotime(date("Y-m-1")." -1 month"));
							elseif($strAttr=="g_aysonu") $val=date("Y-m-d",strtotime(date("Y-m-1")." -1 day"));
							elseif($strAttr=="g_yilbasi")$val=date("Y-m-d",strtotime(date("Y-1-1")." -1 year"));
							elseif($strAttr=="g_yilsonu")$val=date("Y-m-d",strtotime(date("Y-1-1")." -1 day"));
							elseif($strAttr=="s_aybasi") $val=date("Y-m-d",strtotime(date("Y-m-1")." +1 month"));
							elseif($strAttr=="s_aysonu") $val=date("Y-m-d",strtotime(date("Y-m-1")." +2 month -1 day"));
							elseif($strAttr=="s_yilbasi")$val=date("Y-m-d",strtotime(date("Y-1-1")." +1 year"));
							elseif($strAttr=="s_yilsonu")$val=date("Y-m-d",strtotime(date("Y-1-1")." +2 year -1 day"));
							break;
			case "omain":
			case "main":	if(isset($this->main,$this->main->{"fld_$strAttr"}))  $val=$this->main->{"rec_$strAttr"}; break;
			case "form":	if(isset($_POST["frm_$strAttr"])) $val=$_POST["frm_$strAttr"]; break;
		}
		return $val;
	}
}













class clsTable{
	public	$datab="";
	public	$table="";
	public	$dattab="";

	private $dbLink=null;
	private $stmt=null;
	private $active=false;
	private $info=false;
	private $qry=null;
	
	public	$ktarih="";
	public	$kuser=-1;
	public	$sirket=-1;
    public  $personel=-1;

	public  $arrFields=array();
	public	$intFld="";
	public  $autoInc=false;
	private $keyName="";
	public	$keyFld=null;

	private	$arrInsert;
	private	$arrInsrt2;
	private	$arrUpdate;
	private	$ts="";

	private	$strInsert="";
	private	$strInsrt2="";
	private	$strUpdate="";

	private	$frmtInsert="";
	private	$frmtInsrt2="";
	private	$frmtUpdate="";

	function __construct($nLink,$strTable=null,$nID=null,$upd=null,$from=null){
		if (empty($nLink)) return false;
		$this->dbLink=$nLink;
		$this->from=$from;

		if (!preg_match("/^\s*((\w+)\.)?(\w+)\s*(:\s*(\w+))?\s*$/",$strTable,$match)) return false;
		$this->datab=$match[2];
		$this->table=$match[3];
		$this->keyName=empty($match[5])?"id":$match[5];
		$this->dattab=empty($this->datab)?$this->table:"$this->datab.$this->table";

		$res=mysqli_query($this->dbLink,"select * from $this->dattab where ".(empty($nID) ? "1=0" : "$this->keyName=$nID"));
		if (mysqli_errno($this->dbLink)) {echo mysqli_error($this->dbLink); return false;}

		$this->ktarih=date("Y-m-d");
		$nn=0;
		$this->arrFields=array();
		while ($oInfo=mysqli_fetch_field($res)){
			$oInfo->owner=$this;
			$oInfo->order=$nn++;
			$oInfo->orgtable=empty($oInfo->orgtable) ? $oInfo->table : $oInfo->orgtable;
			$oInfo->orgname =empty($oInfo->orgname)  ? $oInfo->name  : $oInfo->orgname;
			$oInfo->typechar=$this->type_char($oInfo->type);
			$oInfo->format  =$this->type_format($oInfo->type);
			$oInfo->emptyval=$this->empty_val($oInfo->type);
			$oInfo->upd=empty($upd);
			$fld_name="fld_".$oInfo->name;
			$this->$fld_name=$oInfo;
			$this->arrFields[]=&$this->$fld_name;

			$rec_name="rec_".$oInfo->name;
			$this->$rec_name=$oInfo->emptyval;
			$oInfo->value=&$this->$rec_name;
	    }
		if (!empty($nID) && ($rec=mysqli_fetch_object($res))){
			foreach ($this->arrFields as $tFld) $tFld->value=$rec->{"$tFld->name"};
			$this->{"rec_$this->keyName"}=0;
		}

		$this->info=true;
		if (is_object($upd)) $this->setQryFields($upd);
		elseif (is_string($upd)) foreach ($this->arrFields as $oFld) $oFld->upd=strpos(",,$upd",",$oFld->name,")>0;
		if (!$this->setUpdate()) return false;
		return true;
	}

	function setQryFields($oQry){
		foreach ($this->arrFields as $tFld)
		if ($qFld=$oQry->fieldByOrgName($this->from,$tFld->name)){
			$tFld->qfd=$fld;
			$tFld->upd=true;
			$qFld->tfd=$tFld;
			$qFld->tab=$this;
		}
	}

	function setUpdate(){
		if (!$this->info) return false;
		$this->keyFld=$this->fieldByName($this->keyName);
		if (is_null($this->keyFld)) return false;

		$strInsFlds="";
		$strInsVals="";
		$strInsFld2="";
		$strInsVal2="";
		$strUpdFlds="";

		$this->arrInsert=array();  $this->arrInsert[]=&$this->stmt;  $this->arrInsert[]=&$this->frmtInsert;
		$this->arrInsrt2=array();  $this->arrInsrt2[]=&$this->stmt;  $this->arrInsrt2[]=&$this->frmtInsrt2;
		$this->arrUpdate=array();  $this->arrUpdate[]=&$this->stmt;  $this->arrUpdate[]=&$this->frmtUpdate;

		foreach ($this->arrFields as $oFld){
			$rec_name="rec_".$oFld->name;
			$strInsFlds.=",".$oFld->name;
			$strInsVals.=",?";

			$this->frmtInsert.=$oFld->format;
			$this->arrInsert[]=&$this->$rec_name;
			if ($oFld->name!=$this->keyName && $oFld->name!="ts"){
				$strInsFld2.=",".$oFld->name;  $strInsVal2.=",?";
				$this->frmtInsrt2.=$oFld->format;
				$this->arrInsrt2[]=&$this->$rec_name;
				if ($oFld->upd){
					$strUpdFlds.=",$oFld->name=?";
					$this->frmtUpdate.=$oFld->format;
					$this->arrUpdate[]=&$this->$rec_name;
				}
			}
	    }

		$strInsFlds=substr($strInsFlds,1);
		$strInsFld2=substr($strInsFld2,1);
		$strInsVals=substr($strInsVals,1);
		$strInsVal2=substr($strInsVal2,1);

		$this->strInsert="insert into $this->dattab ($strInsFlds) values ($strInsVals)";
		$this->strInsrt2="insert into $this->dattab ($strInsFld2) values ($strInsVal2)";
		if(isset($this->fld_ts)){
			$strUpdFlds.=",ts=FROM_UNIXTIME(?)";
			$strUpdFlds=substr($strUpdFlds,1);
			$this->strUpdate="update $this->dattab set $strUpdFlds where $this->keyName=? and ts=?";
			$this->frmtUpdate.="i";
			$this->frmtUpdate.=$this->keyFld->format;
			$this->frmtUpdate.=$this->fld_ts->format;
			$this->arrUpdate[]=&$this->ts;
			$this->arrUpdate[]=&$this->keyFld->value;
			$this->arrUpdate[]=&$this->rec_ts;
		}else{
			$strUpdFlds=substr($strUpdFlds,1);
			$this->strUpdate="update $this->dattab set $strUpdFlds where $this->keyName=?";
			$this->frmtUpdate.=$this->keyFld->format;
			$this->arrUpdate[]=&$this->keyFld->value;
		}
		return true;
	}

	function bindPostVals(){
		foreach ($_POST as $frm_name => $value)
		if (substr($frm_name,0,4)=="frm_"){
			$rec_name="rec_".substr($frm_name,4);
			if (isset($this->$rec_name)) $this->$rec_name=$value;
		}
	}

	function insert(){
		if (isset($this->fld_ktarih)   && empty($this->rec_ktarih))	 $this->rec_ktarih=$this->ktarih;
		if (isset($this->fld_kuser)    && empty($this->rec_kuser))	 $this->rec_kuser =$this->kuser;
		if (isset($this->fld_sirket)   && empty($this->rec_sirket))	 $this->rec_sirket=$this->sirket;
        if (isset($this->fld_personel) && empty($this->rec_personel))$this->rec_personel=$this->personel;
        

		if (preg_match_all("/\s*(\w+)\s*(,|$)/U",$this->intFld,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match) if(($fld=$this->fieldByName($match[1])) && empty($fld->value))$fld->value=-1;

		if ($this->autoInc){
			$this->stmt=mysqli_prepare($this->dbLink,$this->strInsrt2);
			call_user_func_array("mysqli_stmt_bind_param",$this->arrInsrt2);
		}else{
			$this->stmt=mysqli_prepare($this->dbLink,$this->strInsert);
			call_user_func_array("mysqli_stmt_bind_param",$this->arrInsert);
		}

		if (!($suc=mysqli_stmt_execute($this->stmt))) echo mysqli_error($this->dbLink),"<br/>";
		elseif ($this->autoInc){
			$this->keyFld->value=mysqli_insert_id($this->dbLink);
			if (isset($this->keyFld->qfd)) $this->keyFld->qfd->value=$this->keyFld->value;
		}
		mysqli_stmt_close($this->stmt);
		return $suc;
	}

	function update(){
		if(isset($this->fld_ts)) $this->ts=time();
		if(isset($this->keyFld->qfd)) $this->keyFld->value=$this->keyFld->qfd->value;
		$this->stmt=mysqli_prepare($this->dbLink,$this->strUpdate);
		call_user_func_array("mysqli_stmt_bind_param",$this->arrUpdate);
		$suc=mysqli_stmt_execute($this->stmt);
		if (!$suc) echo mysqli_error($this->dbLink),"<br/>";
		mysqli_stmt_close($this->stmt);
		return $suc;
	}

	function fieldByName($name){
		$retFld=null; $fld_name="fld_$name";
		if (isset($this->$fld_name)) $retFld=$this->$fld_name;
		return $retFld;
	}
	
	function type_format($fld_type){
		$str_format="s";
		switch ($fld_type)
		{
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

	function char_type($char){
		$fld_type=254;
		switch($fld_type){
		case "S": $fld_type=254; break;
		case "N": $fld_type=246; break;
		case "I": $fld_type=8;   break;
		case "D": $fld_type=14;  break;
		case "H": $fld_type=11;  break;
		case "T": $fld_type=7;   break;
		case "M": $fld_type=251; break;
		case "W": $fld_type=252; break;
		}
		return $fld_type;
	}

	function type_char($fld_type){
		$char="S";
		switch ($fld_type){
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
		case 15:	//VARCHAR
		case 247:	//ENUM
		case 248:	//SET
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
		switch ($fld_type)
		{
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

	function print_vals(){
		if ($this->autoInc) echo "<br/>Auto increment";
		foreach ($this->arrFields as $oFld)
			echo "<br/>",$oFld->name,"(",$oFld->format,"): ",is_null($oFld->value)?"null":$oFld->value;
	}
    
}
?>