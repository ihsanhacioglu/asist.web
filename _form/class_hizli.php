<?php

class class_hizli extends class__base{
	function __construct($nLink, $cAction){
		global $oUser;

		if (empty($nLink)) return false;
		$this->appLink=$nLink;

		$cDil=strtolower($oUser->dilse);
		if($cDil!="de" && $cDil!="en") $cDil="tr";
		$this->dil=$cDil;
		$this->sen=isset($_GET["sen"]) ? $_GET["sen"]:0; $this->sen=empty($this->sen) ? 0 : $this->sen;
		if (!($this->senaryo=$this->getSenaryo($cAction))){
			echo "$cAction<br>ACCESS DENIED";
			return false;
		}

		$this->id =isset($_GET["id"])  ? $_GET["id"] :0; $this->id =empty($this->id)  ? 0 : $this->id;
		$this->islem=isset($_GET["islem"]) ? $_GET["islem"] : "";
		$this->mod=isset($_GET["mod"]);
		$this->grd=$this->islem=="gdt" || $this->islem=="gok";

		$this->qry=new clsApp($this->appLink, $this->senaryo->sqlstr);
		$this->qry->senaryo=$this->senaryo->id;
		$this->qry->open("1=0");
	}

	function getSenaryo($cAction){
		global $oUser;

		$tabSayfa="sayfa_$this->dil";
		$sqlStr="select role.once,
					sen.id,
					sen.exp,
					sen.action,
					sen.sqlstr,
					sen.formtemp,
					sen.gridtemp,
					sen.color,
					sen.tur,
					senrole.defvalues,
					senrole.filtvalues,
					senrole.listfld
				from asist.$tabSayfa sen, asist.senrole, asist.role
				where sen.id=$this->sen
					and sen.id=senrole.senaryo
					and senrole.role=role.id
					and $oUser->where
					and instr(senrole.options,'+')>0
				order by 1";
	    $res=mysqli_query($this->appLink, $sqlStr);
		$oSen=mysqli_fetch_object($res);
		mysqli_free_result($res);
	    return $oSen;
	}

	function islem(){
		if		($this->islem=="gok")	$this->formgok();
		elseif	($this->islem=="ins")	$this->formins();
		else	$this->formsrc();
	}

	function formgok(){
		global $oUser;
		$ilis_id="ilis_".$this->sen;
		$this->qry->temp="tmp{$oUser->id}_".time()."_".rand();
		$this->bindPostVals($this->qry);
		//$this->qry->query($ilis_id,null);
		$this->formGrid(true);
	}

	function formsrc(){
		global $oUser, $oPerso;

		$tabSayfa="sayfa_$this->dil";
		if (!empty($this->sen) && !empty($this->id)){
			$sql_str="select role.once, sen.id, senrole.defvalues, sen.sqlstr
					from asist.$tabSayfa sen, asist.senrole,asist.role
					where sen.id=$this->sen
						and sen.id=senrole.senaryo
						and senrole.role=role.id
						and $whrRole
					order by 1";
			$qSen=new clsApp($this->appLink, $sql_str);
			$qSen->open();
			$oDet=new clsApp($this->appLink, $qSen->rec_sqlstr, true);
			$oDet->keyOpen($this->id);

			if (preg_match_all("/\s*(\w+)\s*=\s*((\\\$)?([\w-%]+)(\.(\w+))?)\s*(,|$|\r|\n)/U",$qSen->rec_defvalues,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match){
				$fld=$this->qry->fieldByName($match[1]);
				$def=$oDet->fieldByName($match[2]);
				if (is_null($fld)) continue;
				if (is_null($def)){
					if (empty($match[3])){
						$strFld=$match[4];
						if (preg_match_all("/%(\w+)(\W|$)/U",$strFld,$flds,PREG_SET_ORDER))
						foreach ($flds as $name) if ($def=$oDet->fieldByName($name[1]))
							$strFld=str_replace("%$name[1]",$def->value,$strFld);
						$fld->value=$strFld;
					}else{
						$strFld=$match[6];
						switch ($match[4]){
							case "ouser": $fld->value=$oUser->$strFld; break;
							case "operso":$fld->value=$oPerso->$strFld; break;
							case "bugun": $fld->value=date("Y-m-d"); break;
						}
					}
				}else $fld->value=$def->value;
			}
		}
		$this->form();
	}

	function formins(){
		global $oUser, $oPerso;

		$tabSayfa="sayfa_$this->dil";
		$sql_str="select role.once, sen.id, senrole.defvalues, sen.sqlstr, main.sqlstr main_sqlstr, rela.sqlstr rela_sqlstr, rela.updtables rela_updtables
				from asist.$tabSayfa sen, asist.$tabSayfa main, asist.$tabSayfa rela, asist.senrole,asist.role
				where sen.id=$this->sen
					and sen.mainsen=main.id
					and sen.relasen=rela.id
					and sen.id=senrole.senaryo
					and senrole.role=role.id
					and $whrRole
				order by 1";
		$qCCC=new clsApp($this->appLink, $sql_str);
		$qCCC->open();
		$this->qMain=new clsApp($this->appLink, $qCCC->rec_main_sqlstr, true);
		$this->qMain->keyOpen($this->id);
		$qRel=new clsApp($this->appLink, $qCCC->rec_rela_sqlstr);
		$qRel->open();
		$qRel->setUpdates($qCCC->rec_rela_updtables);
		$qSen=new clsApp($this->appLink, $qCCC->rec_sqlstr);
		$qSen->open();

		$this->arrOKFld=array();
		if (preg_match_all("/\s*(\w+)\s*=\s*((\\\$)?([\w-%]+)(\.(\w+))?)\s*($|\r|\n)/U",$qCCC->rec_defvalues,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match)
		if(($fld=$qRel->fieldByName($match[1]))){
			$def=$this->qMain->fieldByName($match[2]);
			if (is_null($fld)) continue;
			if (is_null($def)){
				if (empty($match[3])){
					$strFld=$match[4];
					if (preg_match_all("/%(\w+)(\W|$)/U",$strFld,$flds,PREG_SET_ORDER))
					foreach ($flds as $name) if ($def=$this->qMain->fieldByName($name[1]))
						$strFld=str_replace("%$name[1]",$def->value,$strFld);
					$fld->value=$strFld;
				}else{
					if (!is_null($val=$this->objVal($match[4],$match[6]))) $fld->value=$val;
					if ($match[4]=="okiste") $this->arrOKFld[]=$fld; $fld->frmfld=$match[6];
				}
			}else $fld->value=$def->value;
		}

		echo "<br/><br/>{$this->senaryo->exp}<br/>";
		echo "<br/><input class='tus' type='button' onClick='window.close()' value='Kapat'/><br/><br/>";
		$reccount=isset($_POST["grd_reccount"]) ? $_POST["grd_reccount"] : 0;
		for ($ii=0; $ii<$reccount; $ii++)
		if (isset($_POST["grd_{$ii}_frm_ok"]) && $_POST["grd_{$ii}_frm_ok"]==1){
			foreach ($this->arrOKFld as $fld)
				if (isset($_POST["grd_{$ii}_frm_$fld->frmfld"]))
					$fld->value=$_POST["grd_{$ii}_frm_$fld->frmfld"];
			$qRel->insert();
			$this->postZRapor($ii,$qSen);
		}
		echo "<br/><input class='tus' type='button' onClick='window.close()' value='Kapat'/><br/>";
	}
	function postZRapor($ii,$qSen){
		foreach ($qSen->arrFields as $fld)
			if (isset($_POST["grd_{$ii}_frm_$fld->name"]))
				echo $_POST["grd_{$ii}_frm_$fld->name"]," &nbsp;";
		echo "<br/>";
	}

	function form(){
		global $cKare;

		$cFormStr=$this->senaryo->formtemp;
		if     ($this->islem=="src") $cActislem="gok";
		elseif ($this->islem=="gok") $cActislem="ins";
		else    $cActislem="gok";

		if (!$this->formObjArr($cFormStr)) return false;
		if (!empty($this->senaryo->color)) echo "\n<style>body{background:#{$this->senaryo->color};}</style>\n";
		$this->createEval();

		$this->pre="";
		$oQry=$this->qry;
		$QQ=$oQry;
		echo "\n<form enctype='multipart/form-data' name='form1' id='id_form_form1' method='post' action='?{$this->senaryo->action}",$this->mod?"&mod=":"","&islem=$cActislem&sen=$this->sen&id=$this->id' islem='$cActislem' target='_self' onReset='return ResetForm(this);'>\n";
		foreach ($this->arrEval as $str_eval) eval("echo \"".$str_eval."\";");
		echo "</form>\n";
	}
	
	function formGrid($ok=null){
		global $cKare;

		if		($this->islem=="src"){$cFormStr=$this->senaryo->formtemp; $cActislem="gok";}
		elseif	($this->islem=="gok"){$cFormStr=$this->senaryo->gridtemp; $cActislem="ins";}
		else						 {$cFormStr=$this->senaryo->findtemp; $cActislem="gok";}

		if (empty($cFormStr))
		if		($this->islem=="src"){$cFormStr=$this->defFormTemp($this->qry); $cActislem="gok";}
		elseif	($this->islem=="gok"){$cFormStr=$this->defGridTemp($this->qry); $cActislem="ins";}
		else						 {$cFormStr=$this->defFormTemp($this->qry); $cActislem="gok";}

		if (!$this->formObjArr($cFormStr)) return false;
		if ($ok){
			array_unshift($this->arrRepeat,(object)array("type"=>"C"));
			array_unshift($this->arrRepeat,(object)array("type"=>"CHK","name"=>"ok"));
		}
		$renk=null;
		if (isset($this->qry->fld_abc)) $renk=$this->qry->fld_abc;
		if (isset($this->qry->fld_tipi)){
			if (!$renk) $renk=$this->qry->fld_tipi;
			elseif ($this->qry->fld_abc->order < $this->qry->fld_tipi->order) $renk=$this->qry->fld_abc;
			else $renk=$this->qry->fld_tipi;
		}
		if (!$renk) $renk=(object)array("value"=>"");
		$this->createEval();

		$this->pre="";
		$oQry=$this->qry;
		$QQ=$oQry;
		echo "\n<form name='form1' id='id_form_form1' method='post' action='?{$this->senaryo->action}",$this->mod?"&mod=":"","&islem=$cActislem&sen=$this->sen&id=$this->id' islem='$cActislem' onReset='return ResetForm(this);'>\n";
		foreach ($this->arrEval as $key => $str_eval){
			if ($key==="REPEAT"){
				$nn=0;
				$str_eval="echo \"".$str_eval."\n\";";
				while($this->qry->next()){
					$this->pre="grd_{$nn}_";
					$cls = $nn++%2+1;
					eval($str_eval);
				}
			}else{
				eval("echo \"".$str_eval."\";");
			}
		}
		echo "</form>\n";
	}
}

?>