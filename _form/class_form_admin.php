<?php
class class_form_admin extends class_form{
	function add_islem(){
		$this->arrIslem["net"]="S";
		$this->arrIslem["kar"]="U";
	}
	function formnet(){
		$this->ins_upd_del_message(10);
		$retval=array();
		echo "<pre>";
		$strCmd='net use \\\\ezaman-ts03\\int$ /delete';
		exec($strCmd, $retval);

		$strCmd='net use \\\\wmg-s7\\mes$ /delete';
		exec($strCmd, $retval);

		$strCmd='net use \\\\wmg-s1\\asist.abone$ /delete';
		exec($strCmd, $retval);

		$strCmd='net use \\\\world\\asist.belge$ /delete';
		exec($strCmd, $retval);

		$strCmd='net use \\\\wmg-s1\\asist.world$ /delete';
		exec($strCmd, $retval);


		$strCmd='net use \\\\ezaman-ts03\\int$ 90212655Pak /persistent:yes /user:wmgag\tegmen';
		exec($strCmd, $retval);

		$strCmd='net use \\\\wmg-s7\\mes$ 90212655Pak /persistent:yes /user:wmgag\tegmen';
		exec($strCmd, $retval);

		$strCmd='net use \\\\wmg-s1\\asist.abone$ 90212655Pak /persistent:yes /user:wmgag\tegmen';
		exec($strCmd, $retval);

		$strCmd='net use \\\\world\\asist.belge$ 90212655Pak /persistent:yes /user:wmgag\tegmen';
		exec($strCmd, $retval);

		$strCmd='net use \\\\wmg-s1\\asist.world$ 90212655Pak /persistent:yes /user:wmgag\tegmen';
		exec($strCmd, $retval);

		$strCmd='net use';
		exec($strCmd, $retval);
	
		$retStr="";
		foreach($retval as $val)$retStr.="$val<br>";
		$retStr=$this->DostoWinChar($retStr);
		echo $retStr;
		echo "<br><br><br>";
		echo "</pre>";
	}
	function add_ord_to_temp($temp){
		if(preg_match_all("/#type(\s*)=(\s*)([\w\.]+?)/U",$temp,$arrM,PREG_OFFSET_CAPTURE)){
			$arrCla=$arrM[0];
			$arrCla[count($arrCla)][1]=strlen($temp);
			$otmp=substr($temp,0,$arrCla[0][1]);
			for($ii=0; $ii<count($arrCla)-1; $ii++){
				$strExpr=substr($temp,$arrCla[$ii][1],$arrCla[$ii+1][1]-$arrCla[$ii][1]);
				$strCla=$arrCla[$ii][0];
				$jj=$ii+1;
				$strExpr=str_replace($strCla,"$strCla & ord=$jj",$strExpr);
				$otmp.=$strExpr;
			}
			return $otmp;
		}
		return $temp;
	}
	function cmp_ord_of_temp($tTR,$tDE){
		preg_match_all("/#type\s*=\s*([\w\.]+?)\s+&\s+ord=(\d+)\D+/U",$tTR,$arrTR,PREG_SET_ORDER);
		preg_match_all("/#type\s*=\s*([\w\.]+?)\s+&\s+ord=(\d+)\D+/U",$tDE,$arrDE,PREG_SET_ORDER);
		if(count($arrTR)!=count($arrDE))return " cnt:".count($arrTR)."<>".count($arrDE);
		for($ii=0; $ii<count($arrTR); $ii++){
			if($arrTR[$ii][1]!=$arrDE[$ii][1])return " name_$ii:".$arrTR[$ii][1]."<>".$arrDE[$ii][1];
			if($arrTR[$ii][2]!=$arrDE[$ii][2])return " ord_$ii:".$arrTR[$ii][2]."<>".$arrDE[$ii][2];
		}
		return "";
	}
	function del_ord_from_temp($temp){
		$temp=preg_replace("/#type\s*=\s*([\w\.]+?)\s+&\s+ord=\d+(\D+)/U","#type=$1$2",$temp);
		return $temp;
	}
	function formkar(){
		$this->formdef();
		//$this->ins_upd_del_message(100);
		$id=isset($_GET["id"])?$_GET["id"]:null;
		$isle2=isset($_GET["isle2"])?$_GET["isle2"]:null;

		$strSql="select id,exp,formtemp,listtemp from asist.sayfa_tr";
		$qTR=$this->qry->derive_qry($strSql);
		$qTR->open(null,null);
		$qTR->setKeyQry("sayfa_tr");
		$qTR->setUpdates("sayfa_tr");

		$strSql="select id,exp,formtemp,listtemp from asist.sayfa_de where id=?prm_id";
		$qDE=$this->qry->derive_qry($strSql);
		$qDE->open(null,null);
		$qDE->setKeyQry("sayfa_de");
		$qDE->setUpdates("sayfa_de");

		echo "<pre>";
		while($qTR->next()){
			$str="";
			$cmp="";
			$cmp1="";
			$cmp2="";
			$qDE->prm_id=$qTR->rec_id;
			$qDE->close();$qDE->open();

			if(!empty($qTR->rec_formtemp)||!empty($qDE->rec_formtemp) || !empty($qTR->rec_listtemp)||!empty($qDE->rec_listtemp)){
				if(!empty($isle2) && $isle2=="orall"){
					$qTR->rec_formtemp=$this->del_ord_from_temp($qTR->rec_formtemp);
					$qDE->rec_formtemp=$this->del_ord_from_temp($qDE->rec_formtemp);
					$qTR->rec_listtemp=$this->del_ord_from_temp($qTR->rec_listtemp);
					$qDE->rec_listtemp=$this->del_ord_from_temp($qDE->rec_listtemp);
				}
				$cmp1=$this->cmp_ord_of_temp($qTR->rec_formtemp,$qDE->rec_formtemp);
				$cmp2=$this->cmp_ord_of_temp($qTR->rec_listtemp,$qDE->rec_listtemp);
				if($cmp1 || $cmp2 || (!empty($id) && $id==$qTR->rec_id)){
					if(!empty($isle2) && !empty($id) && $id==$qTR->rec_id){
						if($isle2=="ordel"){
							$qTR->rec_formtemp=$this->del_ord_from_temp($qTR->rec_formtemp);
							$qDE->rec_formtemp=$this->del_ord_from_temp($qDE->rec_formtemp);
							$qTR->rec_listtemp=$this->del_ord_from_temp($qTR->rec_listtemp);
							$qDE->rec_listtemp=$this->del_ord_from_temp($qDE->rec_listtemp);
							//echo htmlspecialchars($qDE->rec_formtemp,ENT_QUOTES),"<br><br>";
						}elseif($isle2=="oradd"){
							$qTR->rec_formtemp=$this->del_ord_from_temp($qTR->rec_formtemp);
							$qDE->rec_formtemp=$this->del_ord_from_temp($qDE->rec_formtemp);
							$qTR->rec_listtemp=$this->del_ord_from_temp($qTR->rec_listtemp);
							$qDE->rec_listtemp=$this->del_ord_from_temp($qDE->rec_listtemp);

							$qTR->rec_formtemp=$this->add_ord_to_temp($qTR->rec_formtemp);
							$qDE->rec_formtemp=$this->add_ord_to_temp($qDE->rec_formtemp);
							$qTR->rec_listtemp=$this->add_ord_to_temp($qTR->rec_listtemp);
							$qDE->rec_listtemp=$this->add_ord_to_temp($qDE->rec_listtemp);
						}
					}else{
						$cmp.=$cmp1?"Form$cmp1 ":"";
						$cmp.=$cmp2?"List$cmp2 ":"";
						$str.="<br> <a href='?form_admin&islem=kar&isle2=ordel&id=$qTR->rec_id'>Ord Del</a>";
						$str.="<br> <a href='?form_admin&islem=kar&isle2=oradd&id=$qTR->rec_id'>Ord Add</a>";
					}
				}
			}
			if(!empty($isle2) && $isle2=="orall"){
				if(!$qTR->update())echo $qTR->msg;
				if(!$qDE->update())echo $qDE->msg;
				echo "$cmp &nbsp;$qTR->rec_id: $qTR->rec_exp UPDATED<br><br>";
			}
			if(!empty($id) && !empty($isle2) && $id==$qTR->rec_id){
				if(!$qTR->update())echo $qTR->msg;
				if(!$qDE->update())echo $qDE->msg;
				echo "$cmp &nbsp;$qTR->rec_id: $qTR->rec_exp UPDATED<br><br>";
			}
			if($str){
				echo "$cmp<br><br>$qTR->rec_id:";
				echo "<label class='lnk' onClick='blankLink(this)' url='?form_sayfa&islem=edt&id=$qTR->rec_id'>$qTR->rec_exp(TR)</label>";
				echo " &nbsp; <label class='lnk' onClick='blankLink(this)' url='?form_sayfade&islem=edt&id=$qTR->rec_id'>$qDE->rec_exp(DE)</label>$str<br><br><br>";
			}
		}
		echo "<br><br><br>";
		echo "</pre>";
	}
}
?>
