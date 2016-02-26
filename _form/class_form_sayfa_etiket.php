<?php
class class_form_sayfa_etiket extends class_form{
	public $labelsCreated=false;

	function islem2($islem=""){
		if (!empty($islem)) $this->islem=$islem;
		if (!$this->islemPerm()){
			echo "ACCESS DENIED";
			return false;
		}
		if		($this->islem=="edt")	$this->formedt();
		elseif	($this->islem=="upd")	$this->formupd();
		elseif	($this->islem=="src")	$this->formsrc();
		elseif	($this->islem=="ara")	$this->formara();
		else	$this->formsel();
	}

	function etiketArr($cFormStr,&$h_md5,$tek=false){
		$arrEti=array();$str_md5="";
		$this->formObjArr($cFormStr);
		foreach($this->arrForm as $objForm){
			$str_md5.=$objForm->type;
			$arrCap=array();
			if($objForm->type=='YXT'){if($objForm->yildiz_tip=="value")	$arrCap["yildiz_exp"]=$objForm->yildiz_exp;}
			if($objForm->type=='RAD')								$arrCap["values"]=$objForm->values;
			if(isset($objForm->caption))							$arrCap["caption"]=$objForm->caption;
			if(isset($objForm->title))								$arrCap["title"]=$objForm->title;
			if(isset($objForm->place))								$arrCap["place"]=$objForm->place;
			if(count($arrCap)){
				$oCap=(object)array("type"=>$objForm->type);
				foreach($arrCap as $cap=>$val)$oCap->$cap=$val;
				$arrEti[]=$oCap;
			}
		}
		$h_md5=md5(count($arrEti).$str_md5);
		return $arrEti;
	}
	function createCalFlds(){
		if($this->labelsCreated)return;
		$this->labelsCreated=true;

		$this->arrCals=array();
		$this->arrBind=array();
		if($this->islem=="edt" || $this->islem=="upd"){
			$cFormStr=$this->qry->rec_formtemp.$this->qry->rec_newtemp.$this->qry->rec_listtemp.$this->qry->rec_findtemp.$this->qry->rec_gridtemp;
			$this->formObjArr($cFormStr);
			$h_qry="";
			$this->arrCals=$this->etiketArr($cFormStr,$h_qry,true);

			// $sqlStr="select formtemp,newtemp,listtemp,findtemp,gridtemp from sayfa_tr where id={$this->qry->rec_id}";
			// $qTR=new clsApp($this->appLink, $sqlStr);
			// $qTR->open();
			// $cFormStr=$qTR->rec_formtemp.$qTR->rec_newtemp.$qTR->rec_listtemp.$qTR->rec_findtemp.$qTR->rec_gridtemp;
			// $this->formObjArr($cFormStr);
			// $h_TR="";
			// $etiTR=$this->etiketArr($cFormStr,$h_TR,false);
			//if($h_qry==$h_TR)foreach($this->arrCals as $key=>$oCap)if(isset($oCap->cap))$oCap->val_tr=$etiTR[$key]->val;
		}
		switch ($this->dil){
			case "tr": $this->arrCals["dil"]="Türkçe"; break;
			case "de": $this->arrCals["dil"]="Deutsch"; break;
			case "en": $this->arrCals["dil"]="English";
		}
		$this->arrCals["exp"]=$this->qry->rec_exp;
	}

	function bindCalFlds(){
		if($this->islem!="edt" && $this->islem!="upd")return;
		$arrFld=array("formtemp"=>0,"newtemp"=>0,"listtemp"=>0,"findtemp"=>0,"gridtemp"=>0);
		foreach($arrFld as $fname=>&$fval)$fval=$this->qry->{"rec_$fname"};
		foreach($this->arrCals as $key=>$oCap){
			$oBind=(object)array();
			if(is_object($oCap))foreach($oCap as $cap=>$val){
				$oBind->$cap="";
				$cal_name="cal_{$key}_{$cap}";
				if(isset($_POST[$cal_name])){
					$bind=$_POST[$cal_name];
					$oBind->$cap=$bind;
					if(!empty($bind))foreach($arrFld as $fname=>&$fval){
						$oldVal=preg_replace('/([=\/\^+*?.()\[\]|${}])/','\\\${1}',$val);
						$fval=preg_replace("/(&)(\s+)$cap(\s*)=(\s*)$oldVal(\s*)(&|#|$)/sU",
											 "\${1}\${2}$cap\${3}=\${4}$bind\${5}\${6}",$fval);
					}
				}
			}
			$this->arrBind[$key]=$oBind;
		}
		foreach($arrFld as $fname=>&$fval)$this->qry->{"rec_$fname"}=$fval;
	}

	function updateSozluk(){
		if($this->islem!="upd")return;
		$iliski="sayfa-{$this->qry->rec_id}";
		$dil=$this->dil;
		if ($this->arrCals["exp"]!=$this->qry->rec_exp)
			$this->sozlukGunc($dil,$iliski,$this->qry->rec_exp,$this->arrCals["exp"]);
		foreach($this->arrCals as $key=>$oCap){
			$oBind=$this->arrBind[$key];
			foreach($oCap as $cap=>$val){
				$bind=$oBind->$cap;
				if($val!=$bind){
					if(isset($oCap->val_tr) && ($oldVal=$this->sozlukBul("tr",$dil,$iliski,$oCap->val_tr)))
						$this->sozlukGunc($dil,$iliski,$oldVal,$bind);
					elseif(($oldVal=$this->sozlukBul($dil,$dil,$iliski,$oCap->val))){
						$this->sozlukGunc($dil,$iliski,$oCap->val,$bind);
					}else{
						if(isset($oCap->val_tr)){ 
							$this->sozlukEkle("tr",$iliski,$oCap->val_tr);
							$this->sozlukGunc($dil,$iliski,$oCap->val_tr,$bind);
						}else{
							$this->sozlukEkle($dil,$iliski,$oCap->val);
							$this->sozlukGunc($dil,$iliski,$oCap->val,$bind);
						}
					}
				}
			}
		}
	}

	function beforePost(){
		$this->createCalFlds();
		$this->bindCalFlds();
	}
	function afterPost(){
		//$this->updateSozluk();
	}
	//function recValid($QRY){return false;}

	function form(){
		$id="";
		$tic="";
		if ($this->islem=="edt"){
			if(empty($this->senaryo->formtemp)) $cFormStr=$this->defFormTemp($this->qry);
			else $cFormStr=$this->senaryo->formtemp;
			$cActislem="upd";
			$LABELFLDS=$this->formLabelFlds();
			$id="&id={$this->qry->id}";
			$tic="&tic=".$this->createTic($cActislem);
		}else{
			if(empty($this->senaryo->findtemp)) $cFormStr=$this->defFindTemp($this->qry);
			else $cFormStr=$this->senaryo->findtemp;
			$cActislem="sel";
		}

		if (!$this->formObjArr($cFormStr)) return false;
		if (!empty($this->senaryo->color)) echo "\n<style>body{background:#{$this->senaryo->color};}</style>\n";
		$this->createEval();

		if(!empty($this->msg))echo "$this->msg";
		$this->pre="";
		$oQry=$this->qry;
		$QQ=$oQry;
		echo "\n<form enctype='multipart/form-data' name='asist_form' id='id_asist_form' method='post' action='?{$this->senaryo->action}",$this->mod?"&mod=":"","&islem=$cActislem$id$tic' islem='$cActislem' target='_self' onReset='return ResetForm(this);'>\n";
		foreach($this->arrEval as $str_eval) eval("echo \"".$str_eval."\";");
		echo "</form>\n";
	}
	function formLabelFlds(){
		$fr=array('&',		'"',		"'",		'<',	'>');
		$to=array('&amp;',	'&quot;',	'&#039;',	'&lt;',	'&gt;');
		$retStr="";
		foreach($this->arrCals as $key=>$oCap)
		if(is_object($oCap))foreach($oCap as $cap=>$val)if($cap!="type"){
			if(isset($this->arrBind[$key],$this->arrBind[$key]->$cap))$val=$this->arrBind[$key]->$cap;
			$val=str_replace($fr,$to,$val);
			$retStr.="<tr><td><label class='lbl' style='color:red'>$oCap->type:{$key}_{$cap}</label></td>";
			$retStr.="<td><label class='lbl' style='color:red'>$val</label></td>";
			$retStr.="<td><input class='txt' style='width:400' name='cal_{$key}_{$cap}' itype='cal' id='id_{$key}_{$cap}' onKeyDown='sonra(this,event)' value='$val'/></td></tr>\n";
		}
		return $retStr;
	}
}
?>