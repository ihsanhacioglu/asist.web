
<?php
class class_form_senrole extends class_form{
	public $oVals;

	function createCalFlds(){
		$this->arrCals=array();
		$this->arrCals["senroleupd"]=$this->islem=="edt";
		$this->arrCals["senroleins"]=0;

		$this->arrCals["optmenu"]= strpos(" {$this->qry->rec_options}","+")>0 ? 1 : 0;
		$this->arrCals["optsel"] = strpos(" {$this->qry->rec_options}","S")>0 ? 1 : 0;
		$this->arrCals["optins"] = strpos(" {$this->qry->rec_options}","I")>0 ? 1 : 0;
		$this->arrCals["optdel"] = strpos(" {$this->qry->rec_options}","D")>0 ? 1 : 0;
		$this->arrCals["optupd"] = strpos(" {$this->qry->rec_options}","U")>0 ? 1 : 0;
		$this->arrCals["optgpd"] = strpos(" {$this->qry->rec_options}","G")>0 ? 1 : 0;
	}
	function bindCalFlds(){
		parent::bindCalFlds();
		$strOpt="";
		$strOpt.=$this->arrCals["optmenu"]? "+" : "";
		$strOpt.=$this->arrCals["optsel"] ? "S" : "";
		$strOpt.=$this->arrCals["optins"] ? "I" : "";
		$strOpt.=$this->arrCals["optdel"] ? "D" : "";
		$strOpt.=$this->arrCals["optupd"] ? "U" : "";
		$strOpt.=$this->arrCals["optgpd"] ? "G" : "";
		$this->qry->rec_options=$strOpt;
	}

	function insertSenrole(){
		return;
		$senaryo_id=$this->qry->rec_senaryo;
		$tabSayfa="sayfa_$this->dil";
		$qCCC=new clsApp($this->appLink, "select tur from asist.$tabSayfa where id=$senaryo_id");
		$qCCC->open();
		if(strpos(" ,snltek,snlist,snlfld,",",$qCCC->rec_tur,")) return;

		$res=mysqli_query($this->appLink,
				"insert into asist.senrole
				(ktarih,senaryo,role,listfld,readfld,filtvalues,defvalues,abc)
				select CURRENT_DATE,$senaryo_id,role.id,listfld,readfld,filtvalues,defvalues,senrole.abc
				from asist.senrole, asist.role
				where senrole.id={$this->qry->rec_id} and role.id not in (select role from asist.senrole where senaryo=$senaryo_id)");
	}
	function updateSenrole(){
		if($this->islem!="upd")return;
		if ($this->qry->rec_options    == $this->oVals->options    &&
			$this->qry->rec_listfld    == $this->oVals->listfld    &&
			$this->qry->rec_readfld    == $this->oVals->readfld    &&
			$this->qry->rec_filtvalues == $this->oVals->filtvalues &&
			$this->qry->rec_defvalues  == $this->oVals->defvalues  &&
			$this->qry->rec_filtrele   == $this->oVals->filtrele   &&
			$this->qry->rec_abc        == $this->oVals->abc) return;

		$tabSayfa="sayfa_$this->dil";
		$sqlStr="select senrole.id,senrole.senaryo,senrole.role,
					senrole.options,senrole.listfld,senrole.readfld,senrole.filtvalues,senrole.defvalues,
					senrole.filtrele,senrole.abc
				from asist.senrole, asist.$tabSayfa sen
				where senrole.senaryo=sen.id
					and sen.tur='senaryo'
					and senrole.senaryo={$this->qry->rec_senaryo}
					and(senrole.options    = ?prm_options or
						senrole.listfld    = ?prm_listfld or
						senrole.readfld    = ?prm_readfld or
						senrole.filtvalues = ?prm_filtvalues or
						senrole.defvalues  = ?prm_defvalues)";
		$qCCC=new clsApp($this->appLink, $sqlStr);
		$qCCC->prm_updtables  = $updtables;
		$qCCC->prm_options    = $this->oVals->options;
		$qCCC->prm_listfld    = $this->oVals->listfld;
		$qCCC->prm_readfld    = $this->oVals->readfld;
		$qCCC->prm_filtvalues = $this->oVals->filtvalues;
		$qCCC->prm_defvalues  = $this->oVals->defvalues;
		$qCCC->open(null,null);

		$sqlStr="update asist.senrole
				set options		= ?prm_options,
					listfld		= ?prm_listfld,
					readfld		= ?prm_readfld,
					filtvalues	= ?prm_filtvalues,
					defvalues	= ?prm_defvalues,
					abc			= ?prm_abc
				where senrole.id=?prm_id";
		$qUpd=new clsApp($this->appLink, $sqlStr);

		while ($qCCC->next()){
			if ($qCCC->rec_id==$this->qry->rec_id)continue;

			$oRole=$qCCC->getFldVals();
			if($qCCC->rec_options    == $this->oVals->options)   $oRole->options   =$this->qry->rec_options;
			if($qCCC->rec_listfld    == $this->oVals->listfld)   $oRole->listfld   =$this->qry->rec_listfld;
			if($qCCC->rec_readfld    == $this->oVals->readfld)   $oRole->readfld   =$this->qry->rec_readfld;
			if($qCCC->rec_filtvalues == $this->oVals->filtvalues)$oRole->filtvalues=$this->qry->rec_filtvalues;
			if($qCCC->rec_defvalues  == $this->oVals->defvalues) $oRole->defvalues =$this->qry->rec_defvalues;
			if($qCCC->rec_options    == $this->oVals->options)   $oRole->abc   	   =$this->qry->rec_abc;

			$qUpd->prm_options    = $oRole->options;
			$qUpd->prm_listfld    = $oRole->listfld;
			$qUpd->prm_readfld    = $oRole->readfld;
			$qUpd->prm_filtvalues = $oRole->filtvalues;
			$qUpd->prm_defvalues  = $oRole->defvalues;
			$qUpd->prm_abc		  = $oRole->abc;
			$qUpd->prm_id		  = $qCCC->rec_id;
			$qUpd->exec();
		}
	}
	function beforePost(){
		$this->createCalFlds();
		$this->bindCalFlds();
		$sOpt=strtr("+SIDU",$this->qry->rec_options,str_repeat(" ",10)); $sOpt=trim($sOpt);
		if	  (empty($this->qry->rec_options)) $this->qry->rec_abc="-";
		elseif(empty($sOpt)) $this->qry->rec_abc="+";
		else   $this->qry->rec_abc="B";
	}
	function afterPost(){
		if ($this->arrCals["senroleins"]==1) $this->insertSenrole();
		if ($this->arrCals["senroleupd"]==1) $this->updateSenrole();
	}
	function afterOpen(){
		$this->oVals=$this->qry->getFldVals("id,senaryo,role,options,listfld,readfld,filtvalues,defvalues,filtrele,abc");
	}
}
?>
