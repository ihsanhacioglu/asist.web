<?php
include_once("$REAL_P/_form/class_form_sayfa__base.php");
class class_form_sayfa extends class_form_sayfa__base{
	public $ROLESTR="";

    function afterDelete(){
		$strSql="delete from asist.senrole where senaryo=?prm_senaryo";
		$qDel=$this->qry->derive_qry($strSql);
        $qDel->prm_senaryo=$this->qry->rec_id;
        $qDel->exec();
	}

	function createCalFlds(){
		$this->arrCals=array();
		$this->arrCals["senroleins"]=0;
		$this->arrCals["mesajupd"]=0;
		$this->arrCals["senaryoupd"]=0;
		$this->arrCals["paramupd"]=0;

		$this->arrCals["formtempupd"]=0;
		$this->arrCals["findtempupd"]=0;
		$this->arrCals["listtempupd"]=0;
		$this->arrCals["gridtempupd"]=0;
		$this->arrCals["yildizupd"]=0;
		$this->arrCals["reqfldupd"]=0;

		$this->ROLESTR="";
		if(strpos(" ,snltek,snlist,snlfld,",",{$this->qry->rec_tur},")) return;

		$senaryo_id=empty($this->qry->rec_id)?0:$this->qry->rec_id;
		$strSql="select id,exp from role where id not in (select role from asist.senrole where senaryo=$senaryo_id)";
		$qCCC=new clsApp($this->appLink, $strSql);
		$qCCC->open(null,null);
		
		$nn=0;
		$retStr="";
		while($qCCC->next()){
			$lbl_name="label".$nn++;
			$txt_name="label$qCCC->rec_id";
			$retStr.="<input name='cal_$txt_name' type='hidden' id='id_$lbl_name' value='0'/><input class='chk' name='chk_$lbl_name' type='checkbox' title='cal_$txt_name' onKeyDown='sonra(this,event)' onClick='id_$lbl_name.value=(this.checked?1:0)'/>";
			$retStr.="<label class='lbl' style='color:red'>$qCCC->rec_exp</label><br/>\n";
			$this->arrCals[$txt_name]=0;
		}
		$this->ROLESTR=$retStr;
	}
	function insertSenrole(){
		if(strpos(" ,snltek,snlist,snlfld,",",{$this->qry->rec_tur},")) return;

		$senaryo_id=$this->qry->rec_id;
		$tabSayfa="sayfa_$this->dil";
		$strSql="select options,listfld,readfld,filtvalues,defvalues,filtrele,abc
				from asist.senrole
				where senaryo=$senaryo_id and role=-1";
		$qSel=new clsApp($this->appLink, $strSql);
		$qSel->open();

		$strSql="insert into asist.senrole (ktarih,senaryo,role,options,listfld,readfld,filtvalues,defvalues,filtrele,abc)
				values (CURRENT_DATE,$senaryo_id,?prm_role,?prm_options,?prm_listfld,?prm_readfld,?prm_filtvalues,?prm_defvalues,?prm_filtrele,?prm_abc)";
		$qIns=new clsApp($this->appLink, $strSql);
		$qIns->prm_options    = $qSel->rec_options;
		$qIns->prm_listfld    = $qSel->rec_listfld;
		$qIns->prm_readfld    = $qSel->rec_readfld;
		$qIns->prm_filtvalues = $qSel->rec_filtvalues;
		$qIns->prm_defvalues  = $qSel->rec_defvalues;
		$qIns->prm_filtrele   = $qSel->rec_filtrele;
		$qIns->prm_abc        = $qSel->rec_abc;

		$strSql="select id,exp from role where id not in (select role from asist.senrole where senaryo=$senaryo_id)";
		$qCCC=new clsApp($this->appLink, $strSql);
		$qCCC->open(null,null);

		while($qCCC->next()){
			$txt_name="label$qCCC->rec_id";
			if(!isset($this->arrCals[$txt_name]) || $this->arrCals[$txt_name]==0) continue;
			$qIns->prm_role=$qCCC->rec_id;
			$qIns->exec();
		}
		if($qSel->reccount==0){$qIns->prm_role=-1;$qIns->prm_options="+";$qIns->exec();}
	}

	function updateParam(){
		if ($this->arrCals["paramupd"]!=1) return;

		$aa=1;
		$senaryo_id=$this->qry->rec_id;
		$sira=0;

		$strSql="update asist.param set parvalues=?prm_Parvalues where senaryo=$senaryo_id and action=?prm_Action";
		$qUpd=new clsApp($this->appLink, $strSql);
		$strSql="select id from asist.param where senaryo=$senaryo_id and action=?prm_Action";
		$qSel=new clsApp($this->appLink, $strSql);
		$tPar=$this->qry->derive_tab("param:auto=1",-1);

		$arrFld=array($this->qry->fld_formtemp,$this->qry->fld_listtemp);
		foreach($arrFld as $fld){
			$str_temp=$fld->value;
			if (!preg_match_all("/((type\s*=\s*PRA\s+).+)(#|$)/sU",$str_temp,$arr_match,PREG_SET_ORDER)) continue;
			foreach($arr_match as $match){
				$strPra=$match[1];
				$action="";$parvalues=""; $sira+=10;

				$objPra=(object)array("action"=>"","param"=>"");
				if (preg_match_all("/\s*(\w+)\s*=(.+)( &|$)/sU",$strPra,$arrProp,PREG_SET_ORDER))
					foreach($arrProp as $val) $objPra->{$val[1]}=trim($val[2]);
				$action   =isset($objPra->action) ? $objPra->action : "";
				$parvalues=isset($objPra->param)  ? $objPra->param  : "";
				if(empty($parvalues)) continue;
				$parvalues=preg_replace("/\s*,\s*/","\r\n",$parvalues);

				$qSel->close();
				$qSel->prm_Action=$action;
				$qSel->open();
				if($qSel->reccount){
					$qUpd->prm_Action=$action;
					$qUpd->prm_Parvalues=preg_replace("/\s*,\s*/","\r\n",$parvalues);
					$qUpd->affected=0;
					$qUpd->exec();
				}else{
					$tPar->rec_senaryo=$senaryo_id;
					$tPar->rec_sira=$sira;
					$tPar->rec_parvalues=$parvalues;
					$tPar->rec_action=$action;
					$tPar->insert();
				}
			}
		}
	}

	function updateTemplate(){
		if ($this->arrCals["formtempupd"]!=1 &&
			$this->arrCals["findtempupd"]!=1 &&
			$this->arrCals["listtempupd"]!=1 &&
			$this->arrCals["gridtempupd"]!=1) return;
		echo "{$this->qry->rec_action}<br/>";
		$oSayfa=new class_form($this->appLink, $this->qry->rec_action);
		if(!is_object($oSayfa->senaryo)) return;

		if ($this->arrCals["formtempupd"]==1 && empty($this->qry->rec_formtemp))
			$this->qry->rec_formtemp=$oSayfa->defFormTemp($oSayfa->qry);

		if ($this->arrCals["findtempupd"]==1 && empty($this->qry->rec_findtemp))
			$this->qry->rec_findtemp=$oSayfa->defFindTemp($oSayfa->qry);

		$aLink=$this->linkArr($oSayfa);
		if ($this->arrCals["listtempupd"]==1 && empty($this->qry->rec_listtemp))
			$this->qry->rec_listtemp=$oSayfa->defListTemp($oSayfa->qry,$oSayfa->senaryo->listfld,$aLink);

		if ($this->arrCals["gridtempupd"]==1 && empty($this->qry->rec_gridtemp))
			$this->qry->rec_gridtemp=$oSayfa->defGridTemp($oSayfa->qry);
	}

	function updateYildiz(){
		if ($this->arrCals["yildizupd"]!=1) return;

		$oQry=new clsApp($this->appLink, $this->qry->rec_sqlstr);
		$QQ=$oQry;
		$QQ->open("1=0");
		$QQ->setUpdates($this->qry->rec_updtables);
		$QQ->setJoins();
		$strYildiz="";
		foreach ($QQ->arrJoins as $oJoin){
			if (is_null($oJoin->fld) || !is_null($oJoin->esit->fld)) continue;
			$strYildiz.="\r\n{$oJoin->fld->name}={$QQ->arrFroms[$oJoin->esit->from]->orgtable}:{$oJoin->esit->orgname}";
			foreach ($QQ->arrFroms[$oJoin->esit->from]->fields as $oFld) $strYildiz.=",$oFld->orgname";
		}
		$this->qry->rec_yildizvalues=substr($strYildiz,2);
	}
	
	function beforePost(){
		$this->createCalFlds();
		$this->bindCalFlds();
		$this->updateTemplate();
		$this->updateYildiz();
		$this->updateParam();
	}
	function afterInsert(){
		$this->denkUpdate("sayfa");
	}
	function afterPost(){
		$this->insertSenrole();
		$this->denkUpdate("sayfa");
	}
	function afterOpen(){$this->oVals=$this->qry->getFldVals();}

	function denkFlds($oDenk){
		$flds=parent::denkFlds($oDenk);
		$flds.=" sqlstr yildizvalues formtemp newtemp listtemp findtemp gridtemp";
		return $flds;
	}
	function denkBind($oDenk){
		$tabFld=array("sqlstr"=>0,"yildizvalues"=>0,"formtemp"=>0,"newtemp"=>0,"listtemp"=>0,"findtemp"=>0,"gridtemp"=>0);
		if(strpos(",,snltek,snlist,snlfld,",",{$this->qry->rec_tur},")){
			unset($tabFld["findtemp"]);
			unset($tabFld["gridtemp"]);
		}
		foreach($tabFld as $fldNam=>$tabVal)$tabFld[$fldNam]=&$oDenk->dTab->{"rec_$fldNam"};
		foreach($oDenk->oUpd->arrFields as $uFld){
			if(isset($tabFld[$uFld->name]))continue;
			$fld_name="fld_$uFld->name";
			if(isset($oDenk->dTab->$fld_name))$oDenk->dTab->$fld_name->value=$uFld->value;
		}
		$this->tabFld=$tabFld;
		parent::denkBind($oDenk);
	}
}
?>