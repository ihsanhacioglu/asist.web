<?php
class class_form_sayfa__base extends class_form{
	public $tabFld=array();
	public $ilistab="sayfa";

	function etiketArr($cFormStr,&$h_md5){
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
	function denkBind($oDenk){
		$arrDenk=$this->denkTabArr();
		$iliski="$this->ilistab-{$this->qry->rec_id}";
		foreach($this->tabFld as $fldNam=>$tabVal){
			$strNew=$this->qry->{"rec_$fldNam"};
			$h_org="";
			$h_new="";
			$h_tab="";
			if($this->islem=="ins")$etiOrg=array();else $etiOrg=$this->etiketArr($this->oVals->$fldNam,$h_org);
			$etiNew=$this->etiketArr($strNew,$h_new);
			$etiTab=$this->etiketArr($tabVal,$h_tab);

			if($this->islem!="ins" && $h_org==$h_new)foreach($etiOrg as $nn=>$oCOrg){
				$oCNew=$etiNew[$nn];
				foreach($oCNew as $cap=>$val)if(isset($oCOrg->$cap) && $oCOrg->$cap!=$oCNew->$cap){
					echo " &nbsp; &nbsp;",$oCOrg->$cap," --> ",$oCNew->$cap;
					if(empty($newVal))continue;
					$this->sozlukGunc($oDenk->org,$iliski,$oCOrg->$cap,$oCNew->$cap);
				}
			}
			if($this->islem=="ins" || $this->oVals->$fldNam==$tabVal){
				foreach($etiNew as $nn=>$oCNew)foreach($oCNew as $cap=>$val){
					$newVal=$this->sozlukBul($oDenk->org,$oDenk->dil,$iliski,$oCNew->$cap);
					if(empty($newVal))continue;

					$oldVal=preg_replace('=([\/.?()\[\]\|${}<>])=','\$1',$oCNew->$cap);
					$strNew=preg_replace("/(&|#)(\s*)$cap(\s*)=(\s*)$oldVal(\s*)(&|#|$)/sU",
										 "\$1\${2}$cap\$3=\${4}$newVal\$5\$6",$strNew);
				}
			}elseif($h_new==$h_tab)foreach($etiNew as $nn=>$oCNew){
				$oCTab=$etiTab[$nn];
				foreach($oCTab as $cap=>$val)if(isset($oCNew->$cap) && $oCNew->$cap!=$oCTab->$cap){
					$oldVal=preg_replace('/([=\/\^+*?.()\[\]|${}])/','\\\${1}',$oCNew->$cap);
					$strNew=preg_replace("/(&)(\s+)$cap(\s*)=(\s*)$oldVal(\s*)(&|#|$)/sU",
										 "\${1}\${2}$cap\${3}=\${4}$val\${5}\${6}",$strNew);
				}
			}elseif($h_org==$h_tab)foreach($etiOrg as $nn=>$oCOrg){
				$oCTab=$etiTab[$nn];
				foreach($oCTab as $cap=>$val)if(isset($oCOrg->$cap) && $oCOrg->$cap!=$oCTab->$cap){
					$oldVal=preg_replace('/([=\/\^+*?.()\[\]|${}])/','\\\${1}',$oCOrg->$cap);
					$strNew=preg_replace("/(&)(\s+)$cap(\s*)=(\s*)$oldVal(\s*)(&|#|$)/sU",
										 "\${1}\${2}$cap\${3}=\${4}$val\${5}\${6}",$strNew);

					if(($newVal=$this->sozlukBul($oDenk->org,$oDenk->dil,$iliski,$oCOrg->$cap))){
						$this->sozlukGunc($oDenk->dil,$iliski,$newVal,$oCTab->$cap);
					}elseif(($newVal=$this->sozlukBul($oDenk->dil,$oDenk->org,$iliski,$oCTab->$cap))){
						$this->sozlukGunc($oDenk->org,$iliski,$newVal,$oCOrg->$cap);
					}else{
						$this->sozlukEkle($oDenk->org,$iliski,$oCOrg->$cap);
						$this->sozlukGunc($oDenk->dil,$iliski,$oCOrg->$cap,$oCTab->$cap);
					}
				}
			}else foreach($etiNew as $nn=>$oCNew){
				foreach($oCNew as $cap=>$val){
					$newVal=$this->sozlukBul($oDenk->org,$oDenk->dil,$iliski,$oCNew->$cap);
					if(empty($newVal))continue;

					$oldVal=preg_replace('/([=\/\^+*?.()\[\]|${}])/','\\\${1}',$oCNew->$cap);
					$strNew=preg_replace("/(&)(\s+)$cap(\s*)=(\s*)$oldVal(\s*)(&|#|$)/sU",
										 "\${1}\${2}$cap\${3}=\${4}$newVal\${5}\${6}",$strNew);
				}
			}
			//echo $h_org,"==",$h_tab," Esit:",$h_org==$h_tab,"<br><br>";
			//echo $fldNam,substr($strNew,0,50),"----------------------<br>";
			$this->denkReplace($arrDenk,$strNew);
			$oDenk->dTab->{"rec_$fldNam"}=$strNew;
		}
	}
}
?>