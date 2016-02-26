<?php
class clsOdbc{
	private $dbLink=null;
	private $stmt=null;

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
	public	$intFld="";
	public	$reqFld="";
	public	$readFld="";
	public	$reccount=0;
	public	$affected=0;
	public	$senaryo=0;
	public	$recs=array();

	function __construct($nLink,$strSql,$main=null,$strKey=null){
		if(empty($nLink))	return false;
		if(empty($strSql))	return false;

		$this->dbLink=$nLink;
		$strSql=trim($strSql);
		$this->main=$main;

		if(preg_match_all("/(\?prm_(\w+)(\W|$))/iU",$strSql,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match){
				$ii=stripos($strSql,$match[1]);
				$strSql=substr_replace($strSql,"?$match[3]",$ii,strlen($match[1]));

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
		if($this->sqltype=="select"){
			$this->setFroms();
			$this->setKeyFrom($strKey);
		}
		return true;
	}

	function query($senaryo=null,$qrySess=null){
		if (!$this->active) return false;
		if(is_array($qrySess)) $arrPars=$qrySess;
		else{
			$arrPars=array();
			foreach ($this->arrFields as $oFld)
			if (is_null($oFld->filter) && !empty($oFld->value)){

				if(preg_match_all("/(.+)(;|\r|\n|$)/U",$oFld->value,$arr_fld,PREG_SET_ORDER))
				foreach($arr_fld as $fld){
					$opr=null;
					if(preg_match("/^\s*(==|!=|<>|<=|>=|>>|=|<|>|~)(.+)$/U",$fld[1],$match)){$opr=$match[1];$oFld->value=$match[2];}
					$par=(object)array("typechar"=>$oFld->typechar,
									   "fromfld"=>"$oFld->from.$oFld->orgname",
									   "value"=>trim($oFld->value),
									   "like"=>$oFld->like,
									   "opr"=>$opr);
					if(isset($oFld->value2) && !empty($oFld->value2)){$par->value.=",$oFld->value2";$par->opr=">>";}
					if($par->like) $par->value=str_replace("*","%",$par->value);
					$arrPars[]=$par;
				}
			}
		}
		if(count($arrPars)==0 && count($this->arrWhere)==0) return false;
		if($senaryo && count($arrPars)) $_SESSION[$senaryo]=$arrPars;
		$this->close();
		return $this->open($arrPars, false);
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

	function setFilter($strFilter,$strOpt="",$MAIN=null,$add=null){
		if(!$add)$this->arrWhere=array();
		if(preg_match_all("/(.+)(;|\r|\n|$)/U",$strFilter,$arr_filt,PREG_SET_ORDER))
		foreach($arr_filt as $filt){
			if(!preg_match("/^\s*([sSDUPE\?]+:)?((\w+)\.(\w+)|(\w+))\s*(==|!=|<>|<=|>=|>>|=|<|>|~)(.+)$/",$filt[1],$match))continue;
			if(!empty($strOpt) && !empty($match[1]) && !strpbrk($match[1],$strOpt))continue;
			$val=$this->buildValue($match[7],$MAIN);
			if(is_null($val))continue;

			if(!empty($match[5])){
				if(!($fld=$this->fieldByName($match[5])))continue;
				$fld->filter=$val;
				$this->arrWhere[]=(object)array("typechar"=>$fld->typechar,
									 "opr"=>$match[6],
									 "like"=>null,
									 "name"=>$fld->orgname,
									 "fromfld"=>"$fld->from.$fld->orgname",
									 "value"=>$val);
			}elseif(isset($this->arrFroms[$match[3]]))
				$this->arrWhere[]=(object)array("typechar"=>(is_numeric($val)?"d":"s"),
									 "opr"=>$match[6],
									 "like"=>null,
									 "name"=>$match[4],
									 "fromfld"=>"$match[3].$match[4]",
									 "value"=>$val);
		}
	}

	function open($varWhere=null,$next=true){
		if($this->active) return false;
		$strFormat="";
		$arrBind=array();

		if (count($this->arrParams)){
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
			foreach ($arrWhrFlt as $whr){
				if(isset($whr->typechar))$typechar=$whr->typechar; else$typechar=is_numeric($whr->value) ? "d" : "s";
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
					//$arrBind[] = &$whr->{"val$ii"};
					$oPar=$oCmd->createParameter();
					$oPar->value=$this->$prm_name;
					$oCmd->parameters->append($oPar);
					$strFormat.= $typechar;
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

		if (!empty($this->temp)){
			$sqlStr="create table temp.$this->temp as ".$sqlStr;
			$_SESSION["tmptables"][]=$this->temp;
		}

		$this->stmt=odbc_prepare($this->dbLink,$sqlStr);
		if(!odbc_execute($this->stmt,$arrBind)){
			echo odbc_errormsg($this->dbLink),"<br/>";
			return false;
		}
		if (!empty($this->temp)){
			$sqlStr="select * from temp.$this->temp";
			if (!($this->stmt=odbc_exec($this->dbLink,$sqlStr))){
				echo odbc_errormsg($this->dbLink),"<br/>";
				return false;
			}
		}
		$this->reccount=odbc_num_rows($this->stmt);
		$this->active=true;
		$this->setInfo();
		if($next)$this->next();
		return true;
	}
	
	function openTemp($next=true){
		if (!$this->active) return false;
		if (empty($this->temp)) return false;
		$this->close();
		$this->info=false;

		$sqlStr="select * from temp.$this->temp";
		if (!($this->stmt=odbc_exec($this->dbLink,$sqlStr))){
			echo odbc_errormsg($this->dbLink),"<br/>";
			return false;
		}
		$this->reccount=odbc_num_rows($this->stmt);

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
			foreach ($arrCond as $whr){
				if (isset($whr->typechar)) $typechar=($whr->typechar=="d" || $whr->typechar=="i" ? $whr->typechar : "s");
				else $typechar=is_numeric($whr->value) ? "d" : "s";
				$opr=isset($whr->opr)?$whr->opr:"";

				if(isset($whr->value2)){
					$arrBind[] = &$whr->value;
					$strFormat.= $typechar;
					$arrBind[] = &$whr->value2;
					$strFormat.= $typechar;
					$addWhere .= " and $whr->fromfld between ? and ?";
				}elseif (empty($opr)){
					if ($whr->like){
						$orLike=""; $ii=0;
						if (preg_match_all("/\s*(.+)\s*(,|$)/U",$whr->value,$arr_match,PREG_SET_ORDER))
							foreach ($arr_match as $match){
								if (empty($match[1])) continue;
								$ii++; $whr->{"val$ii"}="$match[1]%";
								$arrBind[] = &$whr->{"val$ii"};
								$strFormat.= $typechar;
								$orLike.=" or $whr->fromfld like ?";
							}
						$orLike=substr($orLike,4);
						if (!empty($orLike)) $addWhere .= " and ($orLike)";
					}else{
						$arrBind[] = &$whr->value;
						$strFormat.= $typechar;
						$addWhere .= " and $whr->fromfld = ?";
					}
				}elseif ($opr=="~"){
					$orLike=""; $ii=0;
					if (preg_match_all("/\s*(.+)\s*(,|$)/U",$whr->value,$arr_match,PREG_SET_ORDER))
						foreach ($arr_match as $match){
							if (empty($match[1])) continue;
							$ii++; $whr->{"val$ii"}="$match[1]%";
							$arrBind[] = &$whr->{"val$ii"};
							$strFormat.= $typechar;
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
							$strFormat.= $typechar;
							$orOpr.=" or $whr->fromfld $opr ?";
						}
					$orOpr=substr($orOpr,4);
					if (!empty($orOpr)) $addWhere .= " and ($orOpr)";
				}
			}
			$addWhere=substr($addWhere,5);
		}
		$sqlStr=$this->getSqlStr($addWhere);

		if(count($arrBind)){
			$this->stmt=odbc_prepare($this->dbLink,$sqlStr);
			$suc=odbc_execute($this->stmt,$arrBind);
		}else{
			$this->stmt=odbc_exec($this->dbLink,$sqlStr);
			$suc=$this->stmt ? true : false;
		}
		if(!$suc){
			echo odbc_errormsg($this->dbLink),"<br/>";
			return false;
		}
		if ($this->sqltype=="insert"){
			$RES=odbc_exec($this->dbLink,"getautoincvalue(0)");
			if(odbc_fetch_row($RES))$this->insert_id=odbc_result($RES,0);
		}
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
		if ($this->active) odbc_free_result($this->stmt);
		if ($dropTemp && !empty($this->temp)){
			$RES=odbc_exec($this->dbLink,"drop table temp.$this->temp");
			$this->temp="";
		}
		$this->active=false;
		$this->reccount=0;
	}
	
	function setFroms($strFroms=""){
		$strFroms=substr($this->FromStr,4).", $strFroms";

		$this->arrFroms=array();
		if (preg_match_all("/\s*(((\w+)\.)?(\w+))(\s+(\w+))?\s*(,|$)/U",$strFroms,$arr_match,PREG_SET_ORDER))
		$ii=0;
		foreach($arr_match as $match){
			$from=empty($match[6]) ? $match[4] : $match[6];
			if (isset($this->arrFroms[$from])){
				$oFro=$this->arrFroms[$from];
				$oFro->orgtable=$match[4];
				$oFro->datab=empty($oFro->datab)?$match[3]:$oFro->datab;
				$oFro->dattab=empty($oFro->dattab)?$match[1]:$oFro->dattab;
			}else{
				$oFro=(object)array("from"=>$from,"datab"=>$match[3],"orgtable"=>$match[4],"dattab"=>$match[1],"set"=>null);
				$this->arrFroms[$from]=$oFro;
				$this->arrFroms[$ii++]=&$this->arrFroms[$from];
			}
		}
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
		$RES=odbc_exec($this->dbLink,"select * from $oFro->dattab where 1=0");
		$cntFld=odbc_num_fields($RES);
		for($ii=0; $ii<$cntFld; $ii++){
			$name=odbc_field_name($RES,$ii);
			$oFro->fields[$name]=$name;
		}
	}

	function setKeyFrom($strKey=""){
		$from=$this->arrFroms[0]->from; $key="id";
		if (preg_match("/\s*((\w+)\.)?(\w+)\s*/U",$strKey,$match)){
			$from=empty($match[2]) ? $from : $match[2];
			$key=$match[3];
		}
		$this->keyName="$key";
		$this->keyFrom="$from.$key";
		$this->keyQry=null;
	}

	function setFromFields2($from){
		if (!isset($this->arrFroms[$from]) || isset($this->arrFroms[$from]->fields)) return;
		foreach($this->arrFields as $oFld) if ($oFld->from==$from) $this->arrFroms[$from]->fields[]=$oFld;
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
			//$this->setFromFields2($sol->from);
			//$this->setFromFields2($sag->from);
		}
	}

	function setInfo(){
		if (!$this->active) return;

		$this->arrFields=array();
		if(!$this->info) $this->setNames();
		$cntFld=odbc_num_fields($this->stmt);
		for($ii=0; $ii<$cntFld; $ii++){
			$type=odbc_field_type($this->stmt,$ii);
			$oFld=(object)array();
			$oFld->name		= odbc_field_name($this->stmt,$ii);
			$oFld->owner	= $this;
			$oFld->order	= $nn;
			$oFld->from		= $this->arrFroms[$this->arrNames[$oFld->order]->from]->from;
			$oFld->orgtable	= $this->arrFroms[$this->arrNames[$oFld->order]->from]->orgtable;
			$oFld->orgname	= $this->arrNames[$oFld->order]->orgname;
			$oFld->typechar	= $this->type_char($type);
			$oFld->emptyval	= $this->empty_val($type);
			$oFld->like		= strpos(",,129,200,201,8,202,130,203,7,133,134,135,", ",$type,")>0;
			$oFld->filter	= null;
			$oFld->int		= null;
			$oFld->req		= null;
			$oFld->read		= null;
			$oFld->upd		= null;

			$fld_name="fld_$oFld->name";
			$this->$fld_name=$oFld;
			$this->arrFields[]=&$this->$fld_name;

			$rec_name="rec_$oFld->name";
			$this->$rec_name=$oFld->emptyval;
			$oFld->value=&$this->$rec_name;
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
	
	function setFromUpdate($oFro=null,$keyname=null,$keyQry=null){
		if (empty($oFro)) return false;
		$keyname=empty($keyname)?"id":$keyname;
		$oUpd->oFrom	 = $oFro;
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

		$RES=odbc_exec($this->dbLink,"select * from $oFro->dattab where 1=0");
		$cntFld=odbc_num_fields($RES);
		for($ii=0; $ii<$cntFld; $ii++){
			$name=odbc_field_name($RES,$ii);
			$type=odbc_field_type($RES,$ii);
			$qFld=$this->fieldByOrgName($oFro->from,$name);
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
				$oFld=(object)array();
				$oFld->name		= $name;
				$oFld->owner	= $this;
				$oFld->from		= $oFro->from;
				$oFld->orgtable	= $oFro->orgtable;
				$oFld->orgname	= $oFld->name;
				$oFld->typechar	= $this->type_char($type);
				$oFld->emptyval	= $this->empty_val($type);
				//$oFld->like	= strpos(",,129,200,201,8,202,130,203,7,133,134,135,", ",$type,")>0;
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

				if($oFld->name==$oUpd->keyName) $oUpd->keyFld=$oFld;
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

		foreach ($oUpd->arrFields as $uFld){
			$rec_name   ="rec_$uFld->orgname";

			$strInsFlds.=",$uFld->orgname";
			$strInsVals.=",?";
			$oUpd->frmtInsert.=$uFld->typechar;
			$oUpd->arrInsert[]=&$oUpd->$rec_name;

			if ($uFld!=$oUpd->keyFld && $uFld->orgname!="ts"){
				$strInsFld2.=",$uFld->orgname";
				$strInsVal2.=",?";
				$oUpd->frmtInsrt2.=$uFld->typechar;
				$oUpd->arrInsrt2[]=&$oUpd->$rec_name;
				if ($uFld->upd){
					$strUpdFlds.=",$uFld->orgname=?";
					$oUpd->frmtUpdate.=$uFld->typechar;
					$oUpd->arrUpdate[]=&$oUpd->$rec_name;
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
			$oUpd->frmtUpdate.="i";
			$oUpd->frmtUpdate.=$oUpd->keyFld->typechar;
			$oUpd->frmtUpdate.=$oUpd->fld_ts->typechar;
			$oUpd->arrUpdate[]=&$oUpd->ts;
			$oUpd->arrUpdate[]=&$oUpd->keyFld->value;
			$oUpd->arrUpdate[]=&$oUpd->rec_ts;
		}else{
			$strUpdFlds=substr($strUpdFlds,1);
			$oUpd->strUpdate="update {$oUpd->oFrom->dattab} set $strUpdFlds where {$oUpd->keyFld->orgname}=?";
			$oUpd->frmtUpdate.=$oUpd->keyFld->typechar;
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
			$oUpd->stmt=odbc_prepare($this->dbLink,$oUpd->strInsrt2);
			$suc=odbc_execute($oUpd->stmt,$oUpd->arrInsrt2);
		}else{
			$oUpd->stmt=odbc_prepare($this->dbLink,$oUpd->strInsert);
			$suc=odbc_execute($oUpd->stmt,$oUpd->arrInsert);
		}
		if(!$suc){
			echo odbc_errormsg($this->dbLink),"<br/>";
			return false;
		}elseif($oUpd->autoInc){
			$RES=odbc_exec($this->dbLink,"getautoincvalue(0)");
			if(odbc_fetch_row($RES))$oUpd->keyFld->value=odbc_result($RES,0);
			if(isset($oUpd->keyFld->ifd)) $oUpd->keyFld->ifd->value=$oUpd->keyFld->value;
		}
		return true;
	}

	function fromUpdate($oUpd){
		if(isset($oUpd->fld_ts)) $oUpd->ts=time();
		if(isset($oUpd->keyFld->ifd)) $oUpd->keyFld->value=$oUpd->keyFld->ifd->value;

		$oUpd->stmt=odbc_prepare($this->dbLink,$oUpd->strUpdate);
		$suc=odbc_execute($oUpd->stmt,$oUpd->arrUpdate);
		if(!$suc){
			echo odbc_errormsg($this->dbLink),"<br/>";
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
				$this->$par_name=(object)array("name"=>$name,"typechar"=>(is_numeric($value)?"d":"s"));
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
		$this->stmt->move($offset);
	}
	function next(){
		$arr=array();
		if(!odbc_fetch_into($this->stmt,$arr)) return false;
		foreach($this->arrFields as $key=>$oFld) $oFld->value=$arr[$key];
		return true;
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

	function type_char($fld_type){
		$str_format="s";
		switch ($fld_type){
		case 16	:	//adTinyInt			Indicates a one-byte signed integer (DBTYPE_I1).
		case 2	:	//adSmallInt		Indicates a two-byte signed integer (DBTYPE_I2).
		case 3	:	//adInteger			Indicates a four-byte signed integer (DBTYPE_I4).
		case 20	:	//adBigInt			Indicates an eight-byte signed integer (DBTYPE_I8).

		case 17	:	//adUnsignedTinyInt	Indicates a one-byte unsigned integer (DBTYPE_UI1).
		case 18	:	//adUnsignedSmallInt	Indicates a two-byte unsigned integer (DBTYPE_UI2).
		case 19	:	//adUnsignedInt		Indicates a four-byte unsigned integer (DBTYPE_UI4).
		case 21	:	//adUnsignedBigInt	Indicates an eight-byte unsigned integer (DBTYPE_UI8).
					$str_format="i";
					break;

		case 129:	//adChar			Indicates a string value (DBTYPE_STR).
		case 200:	//adVarChar			Indicates a string value.
		case 201:	//adLongVarChar		Indicates a long string value.
		case 8	:	//adBSTR			Indicates a null-terminated character string (Unicode) (DBTYPE_BSTR).
		case 202:	//adVarWChar		Indicates a null-terminated Unicode character string.
		case 130:	//adWChar			Indicates a null-terminated Unicode character string (DBTYPE_WSTR).
		case 203:	//adLongVarWChar	Indicates a long null-terminated Unicode string value.
					$str_format="s";
					break;

		case 128:	//adBinary			Indicates a binary value (DBTYPE_BYTES).
		case 11	:	//adBoolean			Indicates a Boolean value (DBTYPE_BOOL).
		case 136:	//adChapter			Indicates a four-byte chapter value that identifies rows in a child rowset (DBTYPE_HCHAPTER).
		case 6	:	//adCurrency		Indicates a currency value (DBTYPE_CY). Currency is a fixed-point number with four digits to the right of the decimal point. It is stored in an eight-byte signed integer scaled by 10,000.
					$str_format="i";
					break;

		case 7	:	//adDate			Indicates a date value (DBTYPE_DATE). A date is stored as a double, the whole part of which is the number of days since December 30, 1899, and the fractional part of which is the fraction of a day.
		case 133:	//adDBDate			Indicates a date value (yyyymmdd) (DBTYPE_DBDATE).
		case 134:	//adDBTime			Indicates a time value (hhmmss) (DBTYPE_DBTIME).
		case 135:	//adDBTimeStamp		Indicates a date/time stamp (yyyymmddhhmmss plus a fraction in billionths) (DBTYPE_DBTIMESTAMP).
					$str_format="s";
					break;

		case 14	:	//adDecimal			Indicates an exact numeric value with a fixed precision and scale (DBTYPE_DECIMAL).
		case 131:	//adNumeric			Indicates an exact numeric value with a fixed precision and scale (DBTYPE_NUMERIC).
		case 4	:	//adSingle			Indicates a single-precision floating-point value (DBTYPE_R4).
		case 5	:	//adDouble			Indicates a double-precision floating-point value (DBTYPE_R8).
		case 139:	//adVarNumeric		Indicates a numeric value.
					$str_format="d";
					break;

		case 0	:	//adEmpty			Specifies no value (DBTYPE_EMPTY).
		case 10	:	//adError			Indicates a 32-bit error code (DBTYPE_ERROR).
		case 64	:	//adFileTime		Indicates a 64-bit value representing the number of 100-nanosecond intervals since January 1, 1601 (DBTYPE_FILETIME).
		case 72	:	//adGUID			Indicates a globally unique identifier (GUID) (DBTYPE_GUID).
		case 9	:	//adIDispatch		Indicates a pointer to an IDispatch interface on a COM object (DBTYPE_IDISPATCH).	This data type is currently not supported by ADO. Usage may cause unpredictable results.
		case 13	:	//adIUnknown		Indicates a pointer to an IUnknown interface on a COM object (DBTYPE_IUNKNOWN).		This data type is currently not supported by ADO. Usage may cause unpredictable results.
					$str_format="d";
					break;

		case 204:	//adVarBinary		Indicates a binary value.
		case 205:	//adLongVarBinary	Indicates a long binary value.
		case 138:	//adPropVariant		Indicates an Automation PROPVARIANT (DBTYPE_PROP_VARIANT).
					$str_format="s";
					break;

		case 132:	//adUserDefined		Indicates a user-defined variable (DBTYPE_UDT).
		case 12	:	//adVariant			Indicates an Automation Variant (DBTYPE_VARIANT).	This data type is currently not supported by ADO. Usage may cause unpredictable results.
					$str_format="s";
					break;
		}
		return $str_format;
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
		case 6	:	//adCurrency		Indicates a currency value (DBTYPE_CY). Currency is a fixed-point number with four digits to the right of the decimal point. It is stored in an eight-byte signed integer scaled by 10,000.
					$e_value=0;
					break;

		case 7	:	//adDate			Indicates a date value (DBTYPE_DATE). A date is stored as a double, the whole part of which is the number of days since December 30, 1899, and the fractional part of which is the fraction of a day.
		case 133:	//adDBDate			Indicates a date value (yyyymmdd) (DBTYPE_DBDATE).
		case 134:	//adDBTime			Indicates a time value (hhmmss) (DBTYPE_DBTIME).
		case 135:	//adDBTimeStamp		Indicates a date/time stamp (yyyymmddhhmmss plus a fraction in billionths) (DBTYPE_DBTIMESTAMP).
					$e_value=null;
					break;

		case 14	:	//adDecimal			Indicates an exact numeric value with a fixed precision and scale (DBTYPE_DECIMAL).
		case 131:	//adNumeric			Indicates an exact numeric value with a fixed precision and scale (DBTYPE_NUMERIC).
		case 4	:	//adSingle			Indicates a single-precision floating-point value (DBTYPE_R4).
		case 5	:	//adDouble			Indicates a double-precision floating-point value (DBTYPE_R8).
		case 139:	//adVarNumeric		Indicates a numeric value.
					$e_value="d";
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
			echo "<br/>",$oFld->name,"(",$oFld->typechar,"): ",is_null($oFld->value) ? "null" : $oFld->value;
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
?>
