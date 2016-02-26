<?php
class class_ilis extends class__base{
public $filt = "";

	function __construct($nLink, $cIlis, $main=null, $filt=null){
		global $oUser;

		if(empty($nLink)) return false;
		$this->appLink=$nLink;
		$this->main=$main;
		$this->filt=$filt;

		$cDil=strtolower($oUser->dilse);
		if($cDil!="de" && $cDil!="en") $cDil="tr";
		$this->dil=$cDil;
		if (!($this->senaryo=$this->getSenaryo($cIlis))){
			echo "ACCESS DENIED ($cIlis)";
			return false;
		}
	}

	function getSenaryo($cIlis){
		global $oUser;

		$tabSayfa="sayfa_$this->dil";
		if(is_numeric($cIlis)) $relWhr="sen.id=$cIlis";
		else $relWhr="sen.action='$cIlis'";

		$sqlStr="select sen.id,
					sen.tur,
					sen.datab,
					sen.exp,
					sen.kisad,
					sen.sqlstr,
					sen.yeni,
					sen.snloption,
					sen.snlmodal,
					sen.relmodal,
					sen.updtables,
					' ' options,
					sen.listtemp,
					sen.linkvalues,
					sen.reqfld listfld,
					sen.findtemp filtvalues,
					sen.gridtemp defvalues,
					sen.relasen,
					rela.action,
					rela.action rela_action
				from asist.$tabSayfa sen, asist.$tabSayfa rela
				where $relWhr and sen.relasen=rela.id";
	    $res=mysqli_query($this->appLink, $sqlStr);
		if($oSen=mysqli_fetch_object($res)){
			$oSen->rela_options=null;
			$oSen->filtvalues=empty($this->filt) ? $oSen->filtvalues : $this->filt;
			$sqlStr="select role.once, senrole.options
					from asist.senrole,asist.role
					where senrole.senaryo=$oSen->relasen
						and senrole.role=role.id
						and $oUser->where
					order by 1";
			$res=mysqli_query($this->appLink, $sqlStr);
			if($oRole=mysqli_fetch_object($res)) $oSen->rela_options=$oRole->options;
		}
		mysqli_free_result($res);
	    return $oSen;
	}

	function create_Qry($strClass=null){
		$oDB=connect_datab($this->senaryo->datab);
		if(empty($strClass))$strClass="cls$oDB->cls";
		$setn=strpos(" ".$this->senaryo->snloption,"setn")>0;
		//var_dump($setn,$this->senaryo->snloption,$this->senaryo->exp,$this->senaryo->id);
		$this->qry=new $strClass($oDB->dblink, $this->senaryo->sqlstr, $this->main,$setn);

		$this->qry->senaryo=$this->senaryo->id;
		$this->qry->open("1=0");
		if(!empty($this->senaryo->updtables))$this->qry->setKeyQry($this->senaryo->updtables);
		else{
			$akey=array_keys($this->qry->arrFroms);
			$this->qry->setKeyQry("$akey[0]:key=id");
		} 
	}

	function createIlis($strClass=null){
		$this->create_Qry($strClass);
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");

		$aLink=array();
		if (preg_match_all("/(\w+)\s*:\s*(.+)($|\r|\n)/",$this->senaryo->linkvalues,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match) $aLink[$match[1]]=$match[2];

		$arrListFld=array();
		$cFormStr=$this->senaryo->listtemp;
		if (empty($cFormStr)) $cFormStr=$this->defListTemp($this->qry,$this->senaryo->listfld,$aLink);
		if (!$this->formObjArr($cFormStr)) return false;

		$cls2="";
		$renk=null;
		if (isset($this->qry->fld_abc)) $renk=$this->qry->fld_abc;
		if (isset($this->qry->fld_tipi)){
			if (!$renk) $renk=$this->qry->fld_tipi;
			elseif ($this->qry->fld_abc->order < $this->qry->fld_tipi->order) $renk=$this->qry->fld_abc;
			else $renk=$this->qry->fld_tipi;
		}
		$this->renk=$renk;
		if ($renk) $cls2=" {$renk->name}_{\$renk->value}";
		$this->createEval();
	}
	function ilisShow(){
		global $cKare, $cList;

		$QQ=$this->qry;
		if(count($QQ->arrWhere)==0 && count($QQ->arrParams)==0)return;

		if(count($QQ->arrParams))$this->bindParams($QQ,$this->senaryo->filtvalues);
		if(count($QQ->arrWhere))$this->refWhere($QQ);
		$QQ->close();
		$QQ->open(null,null);
		//if(!$QQ->reccount)return;
		//echo $QQ->reccount,",",$QQ->arrWhere[0]->fromfld,",",$QQ->arrWhere[0]->value,",",$QQ->arrWhere[0]->fld->name,"<br>";
		//$QQ->print_whrs();

		$brwStr="";foreach($QQ->arrWhere as $filt){
			$brwStr.="$filt->name$filt->opr$filt->value".($filt->orl=="//" ? "//;" : ";");
			//echo "cnt:$QQ->reccount, ffld:$filt->fromfld, val:$filt->value, nam:{$filt->fld->name}, orl:$filt->orl<br>";
		}

		$renk=$this->renk;
		$cls="";
		$nn_eval="\$cls=\$nn++%2+1; ";
		
		$optFrm=$this->findOpt($this->senaryo->snloption);
		if(isset($optFrm->sum))foreach($optFrm->sum as $name)if(isset($QQ->{"fld_$name"})){
				$sum_name="SUM_".strtoupper($name);
				$$sum_name=0;
				$nn_eval.="\$$sum_name+=\$QQ->rec_$name; ";
		}
		
		foreach($this->arrEval as $key => $str_eval){
			$nn=0;
			if($key==="REPEAT")while($QQ->next()){eval("$nn_eval"); eval("echo \"$str_eval\";");}
			else eval("echo \"$str_eval\";");
		}
	}

	function defListTemp($QQ,$cShowFlds="",$aLink=null){
		global $cKare, $cList;

		$arrListFld=array(); if (is_null($aLink)) $aLink=array();
		if (!empty($cShowFlds) && preg_match_all("/\s*(\w+)(:img|:pic|:lnk|:pra|:brk)?\s*(,|$)/U",$cShowFlds,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match){
			if($match[2]==":pra") $oFld=(object)array("name"=>$match[1],"pra"=>$match[1]);
			elseif($match[2]==":lnk" || isset($aLink[$match[1]])){
				$oFld=(object)array("name"=>$match[1],"lnk"=>$match[1],"href"=>"");
				if (isset($aLink[$match[1]])) $oFld->href=$aLink[$match[1]];
			}
			elseif(is_null($oFld=$QQ->fieldByName($match[1]))) continue;
			if($match[2]==":img" || $match[2]==":pic") $oFld->pic=$match[1];
			elseif($match[2]==":brk")$oFld->brk=1;
			$arrListFld[]=$oFld;
		}
		if (count($arrListFld)==0) $arrListFld=&$QQ->arrFields;

		$cls2="";
		$renk=null;
		if (isset($QQ->fld_abc)) $renk=$QQ->fld_abc;
		if (isset($QQ->fld_tipi)){
			if (!$renk) $renk=$QQ->fld_tipi;
			elseif ($QQ->fld_abc->order < $QQ->fld_tipi->order) $renk=$QQ->fld_abc;
			else $renk=$QQ->fld_tipi;
		}
		if ($renk) $cls2=" {$renk->name}_{\$renk->value}";

		$cFormStr="";
		foreach($arrListFld as $oFld){
			if (isset($oFld->pra))		$cFormStr.="#B #type=PRA & action=$oFld->name & caption=$oFld->name";
			elseif (isset($oFld->lnk))	$cFormStr.="#B #type=LNK & caption=$oFld->name & href=$oFld->href";
			elseif (isset($aLink[$oFld->name]))	$cFormStr.="#B #type=LNK & caption=$oFld->name & href=".$aLink[$oFld->name];
			elseif (isset($oFld->pic))	$cFormStr.="#B #type=PIC & name=$oFld->name & attrib=class='lst'";
			else 						$cFormStr.="#B #type=DAT & name=$oFld->name".(isset($oFld->brk)?" & brk=1":"")."\n";
		}

#type=HTM & attrib=$cKare #type=HTM & attrib=<label class='lnk-red' onClick='openLink(this)' url='?{$this->senaryo->rela_action}&islem=sel&brw=$brwStr'>{$this->senaryo->exp}</label><br/>
#type=REPEAT
#type=HTM & attrib=$cList #type=HTM & attrib=<label class='lnk abc_{$renk->value}' onClick='blankLink(this)' url='?{$this->senaryo->rela_action}&islem=sel&brw=id=$QQ->rec_id'>
#type=DAT & name=id #B #type=DAT & name=exp #B #type=DAT & name=durum_exp #B #type=DAT & name=iliski #B #type=DAT & name=dtarih
#type=HTM & attrib=</label>
#type=PRA & name=pratik_onayla & action=pratik_onayla & caption=Onayla & param=?durum=$form.durum, ?onay_id=%id
#type=HTM & attrib=<br/>
#type=REPEAT

		if (strpos(" snltek,snlist,", "{$this->senaryo->tur},")){
			$cModalLink=" onClick='modalLink(this)'";
			$cBlankLink=" onClick='blankLink(this)'";
			$cOpenLink =" onClick='openLink(this)'";
			$cRelaLink =$this->senaryo->relmodal?$cModalLink:$cBlankLink;
			$cMod=$this->senaryo->relmodal?"&mod=":"";

			$cHeader="";
			if($this->senaryo->tur=="snlist")
			if(strpos(" {$this->senaryo->rela_options}",'S'))
				$cHeader.="#type=HTM & attrib=$cKare #type=HTM & attrib=<label class='lnk-red'$cOpenLink url='?{$this->senaryo->rela_action}&islem=sel&brw=\$brwStr'>{$this->senaryo->exp}</label>";
			else
				$cHeader.="#type=HTM & attrib=$cKare #type=HTM & attrib=<label class='lst-red'>{$this->senaryo->exp}</label>";

			if(strpos(" ".$this->senaryo->snloption,"new") && strpos(" {$this->senaryo->rela_options}",'I'))
				$cHeader.="#B #type=HTM & attrib=<label class='lnk'$cRelaLink url='?{$this->senaryo->rela_action}$cMod&islem=snl&sen={$this->senaryo->id}&id={\$this->main->rec_id}'>[+]</label>";
			$cHeader.=empty($cHeader)?"":"<br/>\n";

			$lblInner="class='lst$cls2'";
			if(strpos(" {$this->senaryo->rela_options}",'U'))
			if(strpos(" ".$this->senaryo->snloption,"sel"))
				$lblInner="class='lnk$cls2'$cRelaLink url='?{$this->senaryo->rela_action}$cMod&islem=sel&brw=id=\$QQ->rec_id'";
			else
				$lblInner="class='lnk$cls2'$cRelaLink url='?{$this->senaryo->rela_action}$cMod&islem=edt&id=\$QQ->rec_id'";

			$cFormStr=$cHeader.
					  "#type=REPEAT ".
					  "#type=HTM & attrib=$cList #type=HTM & attrib=<label $lblInner>".
					  substr($cFormStr,3)." #type=HTM & attrib=</label><br/>\n".
					  "#type=REPEAT";
		}else{
			$cFormStr="#type=REPEAT ".
					  "#type=HTM & attrib=<label class='lst$cls2'> ".substr($cFormStr,3)."#type=HTM & attrib=</label><br/>".
					  "#type=REPEAT";
		}
		return $cFormStr;
	}
	
	function tempPratik($QQ,$cShowFlds="",$aLink=null){
		$retStr.="<label class='lnk' onClick='modalLink(this)' url='?$QQ->rec_action&mod=&islem=prm&sen={$this->senaryo->id}&id={$this->qry->rec_id}$addParam'>$qCCC->rec_exp</label>\n";
		return $retStr;
	}
}

class class_ilis_list_onay_sevap_fld extends class_ilis{
	function ilisShow(){
		$QQ=$this->qry;
		$this->refWhere($QQ);
		if(count($QQ->arrWhere)==0)return;
		$QQ->close(); $QQ->open(null,null);
		if($QQ->reccount==0)return;

		$brwStr="";foreach($QQ->arrWhere as $filt)$brwStr.="$filt->name$filt->opr$filt->value".($filt->orl=="//" ? "//;" : ";");

		$renk=$this->renk;
		$retStr="";
		foreach ($this->arrEval as $key => $str_eval){
			if ($key==="REPEAT") while ($QQ->next()){
				$keyCond=empty($QQ->rec_cevap) ? "cevapver" : "cevap";
				$CEVAPVER=eval("return \"".$this->arrCond[$keyCond]."\";");
				eval("echo \"$str_eval\";");
			}
			else eval("echo \"$str_eval\";");
		}
	}
}
class class_ilis_fld_dilek_sevap extends class_ilis{
	function ilisShow(){
		$QQ=$this->qry;
		$this->refWhere($QQ);
		if(count($QQ->arrWhere)==0)return;
		$QQ->close(); $QQ->open(null,null);
		if($QQ->reccount==0)return;

		$brwStr="";foreach($QQ->arrWhere as $filt)$brwStr.="$filt->name$filt->opr$filt->value".($filt->orl=="//" ? "//;" : ";");

		$renk=$this->renk;
		$retStr="";
		foreach ($this->arrEval as $key => $str_eval){
			if ($key==="REPEAT") while($QQ->next()){
				$keyCond=empty($QQ->rec_cevap) ? "cevapver" : "cevap";
				$CEVAPVER=eval("return \"".$this->arrCond[$keyCond]."\";");
				eval("echo \"$str_eval\";");
			}
			else eval("echo \"$str_eval\";");
		}
	}
}

class class_ilis_list_senaryo_senaryo extends class_ilis{
	function ilisShow(){
		global $cKare, $cList;

		$brwStr="";
		$cIlis="";
		$cFormStr=$this->main->rec_formtemp.$this->main->rec_listtemp;
		$this->formObjArr($cFormStr);
		foreach($this->arrForm as $objForm)if($objForm->type=="SNL" || $objForm->type=="ILIS" ||
		                          $objForm->type=="ILIS_REL" || $objForm->type=="PRA"){
			$cIlis.=",'$objForm->action'";
			$brwStr.=",$objForm->action";
		}
		if(empty($cIlis))return;

		$cIlis=substr($cIlis,1);
		$tabSayfa="sayfa_$this->dil";
		$sqlStr="select sen.id, sen.exp, sen.action, sen.tur, sen.mainsen, sen.kisad,
				rela.id rela_id, rela.exp rela_exp, rela.action rela_action
				from asist.$tabSayfa sen, asist.$tabSayfa rela
				where sen.relasen=rela.id
					and sen.action in ($cIlis)
				order by 4,3";

		$this->qry=new clsApp($this->appLink, $sqlStr);
		$oQry=$this->qry;
		$QQ=$oQry;
		$QQ->close(); $QQ->open(null,null);
		$brwStr="action=".substr($brwStr,1);

		$renk=$this->renk;
		$retStr="";
		foreach ($this->arrEval as $key => $str_eval){
			if ($key==="REPEAT")while($QQ->next()){
				$RELASEN="";
				if($QQ->rec_rela_id!=-1)$RELASEN=eval("return \"".$this->arrCond["relasen"]."\";");
				eval("echo \"$str_eval\";");
			}
			else eval("echo \"$str_eval\";");
		}
	}
}

class class_ilis_list_onay_relata_fld extends class_ilis{
	function ilisShow(){
		global $cKare, $cList;

		$tabDitur="ditur_$this->dil";
		$ilis_tab="";
		$ilis_id=0;
		if(preg_match("/(.+)-(.+)/",$this->main->rec_iliski,$match)){$ilis_tab=$match[1]; $ilis_id=$match[2];}
		if($ilis_tab=="dilek"){
			$sqlStr="select dilek.acikla,ditur.exp belge_exp from asist.dilek, asist.$tabDitur ditur where dilek.ditur=ditur.id and dilek.id=$ilis_id";
		}elseif($ilis_tab=="satin"){
			$sqlStr="select satin.acikla,satur.exp belge_exp from asist.satin, asist.satur where satin.satur=satur.id and satin.id=$ilis_id";
		}else return;

		$this->qry=new clsApp($this->appLink, $sqlStr);
		$oQry=$this->qry;
		$QQ=$oQry;
		$QQ->close(); $QQ->open(null,null);
		$brwStr="";

		$renk=$this->renk;
		$retStr="";
		$cls="";
		
		$nn_eval="\$cls=\$nn++%2+1; ";
		foreach ($this->arrEval as $key => $str_eval){
			$nn=0;
			if ($key==="REPEAT") while ($QQ->next()){eval("$nn_eval"); eval("echo \"$str_eval\";");}
			else eval("echo \"$str_eval\";");
		}
	}
}

class class_ilis_list_onay_relata_dty extends class_ilis{
	function ilisShow(){
		global $cKare, $cList;

		$ilis_tab="";
		$ilis_id=0;
		if(preg_match("/(.+)-(.+)/",$this->main->rec_iliski,$match)){$ilis_tab=$match[1]; $ilis_id=$match[2];}
		if($ilis_tab=="satin"){
			$sqlStr="select satindty.* from asist.satindty where satin=$ilis_id";
		}else return;

		$this->qry=new clsApp($this->appLink, $sqlStr);
		$oQry=$this->qry;
		$QQ=$oQry;
		$QQ->close(); $QQ->open(null,null);
		$brwStr="";

		$renk=$this->renk;
		$retStr="";
		$cls="";
		
		$nn_eval="\$cls=\$nn++%2+1; ";
		foreach ($this->arrEval as $key => $str_eval){
			$nn=0;
			if ($key==="REPEAT") while ($QQ->next()){eval("$nn_eval"); eval("echo \"$str_eval\";");}
			else eval("echo \"$str_eval\";");
		}
	}
}

class class_ilis_list_onay_belge extends class_ilis{
	function createItic($islem,$id){
		$ticSen="sen_{$this->senaryo->relasen}_{$islem}_$id";
		$ticKey=time()."_".rand();
		$_SESSION[$ticSen]=$ticKey;
		$itic="$ticSen:$ticKey";
		return $itic;
	}
}

?>