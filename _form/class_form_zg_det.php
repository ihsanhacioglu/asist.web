
<?php
include_once("$REAL_P/_class/data_adoacc.php");
class clsAdoacc_form_zg_acc extends clsAdoacc{
	function get_SETUP_ID($strTable=null){
		$retval=parent::get_SETUP_ID($strTable);
		$retval=str_pad($retval,3,"0",STR_PAD_LEFT);
		return $retval;
	}
}

class class_form_zg_det extends class_form{
	public $dbClass="clsAdoacc_form_zg_acc";
	public $gstrTCPServerID="00";

	function afterOpen(){
		$this->oVals=$this->qry->getFldVals("zg_nummer");
	}
	function beforePost(){
		$this->createCalFlds();
		$this->bindCalFlds();
	}
	function afterInsert(){
		$this->insertZP();
		$this->addProfil($this->qry->rec_zg_nummer);
	}
	function afterUpdate(){
		$this->updateZP();
		if($this->qry->id!=$this->qry->rec_zg_nummer)$this->delProfil($this->qry->id);
		$this->addProfil($this->qry->rec_zg_nummer);
	}
	function afterDelete(){
		$this->deleteZP();
		$this->delProfil($this->qry->rec_zg_nummer);
	}
	function createCalFlds(){
		$this->arrCals=array();

		$this->ZKPSTR="";
		$strSql="select ZKP_Index, ZKP_Standort,
					iif(yy.Id=1,ZP.[terminal 1],iif(yy.Id=2,ZP.[terminal 2],iif(yy.Id=9,ZP.[terminal 9],ZP.[terminal 10]))) as val,
					ZP.acmnr, yy.Id2,
					ZP.vonzeit, ZP.biszeit,
					ZP.pincodeeingabe,
					ZP.sonntag,
					ZP.montag,
					ZP.dienstag,
					ZP.mittwoch,
					ZP.donnerstag,
					ZP.freitag,
					ZP.samstag,
					ZP.st_kennung1,
					ZP.st_kennung2,
					ZP.st_kennung3
				from ZutrittsProfile ZP, tblZutrittsKontrollpunkte ZKP, s_on yy
				where ZP.zpnr=?prm_zpnr and yy.Id in (1,2,9,10) and ZP.Acmnr+yy.Id2=ZKP.zkp_Index
				order by 1";
		$flds=array();
/* 		$flds[]="sonntag";
		$flds[]="montag";
		$flds[]="dienstag";
		$flds[]="mittwoch";
		$flds[]="donnerstag";
		$flds[]="freitag";
		$flds[]="samstag";
 */
		$qCCC=new clsAdoacc($this->qry->get_dbLink(), $strSql);
		$qCCC->prm_zpnr=$this->qry->id;
		$qCCC->open(null,null);
		$nn=0;
		$retStr="";
		while($qCCC->next()){
			$txt_name="txt{$qCCC->rec_acmnr}{$qCCC->fld_vonzeit->name}";
			if(!array_key_exists($txt_name,$this->arrCals)){
				$lbl_name="label".$nn++;
				$txt_name="txt{$qCCC->rec_acmnr}{$qCCC->fld_vonzeit->name}";
				$retStr.="<br><input name='cal_$txt_name' id='id_$lbl_name' title='cal_$txt_name' value='$qCCC->rec_vonzeit' style='width:50'/>";
				$this->arrCals[$txt_name]=$qCCC->rec_vonzeit;
				$lbl_name="label".$nn++;
				$txt_name="txt{$qCCC->rec_acmnr}{$qCCC->fld_biszeit->name}";
				$retStr.="&nbsp;<input name='cal_$txt_name' id='id_$lbl_name' title='cal_$txt_name' value='$qCCC->rec_biszeit' style='width:50'/><br>";
				$this->arrCals[$txt_name]=$qCCC->rec_biszeit;
 				foreach($flds as $ii=>$gun){
					$oFld=$qCCC->fieldByName($gun);
					$val=($oFld->value?1:0);
					$opt=($val?"checked":"");
					$lbl_name="label".$nn++;
					$txt_name="txt{$qCCC->rec_acmnr}{$oFld->name}";
					$retStr.="<input name='cal_$txt_name' type='hidden' id='id_$lbl_name' value='$val'/><input class='chk' name='chk_$lbl_name' type='checkbox' title='cal_$txt_name' onKeyDown='sonra(this,event)' onClick='id_$lbl_name.value=(this.checked?1:0)'$opt/>";
					$retStr.="<label class='lbl' style='color:red;width:65'>$oFld->name</label><br>\n";
					$this->arrCals[$txt_name]=$oFld->value;
				}
				//$retStr.="<br>";
			}
			$val=($qCCC->rec_val?1:0);
			$opt=($val?"checked":"");
			$lbl_name="label".$nn++;
			$txt_name="txt{$qCCC->rec_acmnr}t{$qCCC->rec_id2}";
			$retStr.="<input name='cal_$txt_name' type='hidden' id='id_$lbl_name' value='$val'/><input class='chk' name='chk_$lbl_name' type='checkbox' title='cal_$txt_name' onKeyDown='sonra(this,event)' onClick='id_$lbl_name.value=(this.checked?1:0)'$opt/>";
			$retStr.="<label class='lbl' style='color:red'>($qCCC->rec_zkp_index) $qCCC->rec_zkp_standort</label><br>\n";
			$this->arrCals[$txt_name]=$val;
		}
		$this->ZKPSTR=$retStr;
	}
	function updateZP(){
		$arrAcmnr=array();
		foreach($this->arrCals as $txt_name=>$val){
			if(substr($txt_name,0,3)!="txt")continue;
			$name=substr($txt_name,5);
			$acmnr=substr($txt_name,3,2);
			$arrAcmnr["$acmnr"]->{"$name"}=$val;
		}
		$strSql="update ZutrittsProfile
				set ".($this->qry->id!=$this->qry->rec_zg_nummer?"zpnr=?prm_zpnr2,":"")."
					vonzeit=?prm_vonzeit,
					biszeit=?prm_biszeit,
					[terminal 1]=?prm_t01,
					[terminal 2]=?prm_t02,
					[terminal 9]=?prm_t09,
					[terminal 10]=?prm_t10
				where zpnr=?prm_zpnr and Acmnr=?prm_acmnr";
		$qCCC=new clsAdoacc($this->qry->get_dbLink(), $strSql);
		if(isset($qCCC->par_zpnr2))$qCCC->prm_zpnr2=$this->qry->rec_zg_nummer;
		$qCCC->prm_zpnr=$this->qry->id;
		
		foreach($arrAcmnr as $acmnr=>$oCal){
			$qCCC->prm_acmnr=$acmnr;
			$qCCC->prm_vonzeit	=(isset($oCal->vonzeit)&&!empty($oCal->vonzeit)?$oCal->vonzeit:"00:00:00");
			$qCCC->prm_biszeit	=(isset($oCal->biszeit)&&!empty($oCal->biszeit)?$oCal->biszeit:"00:00:00");
			$qCCC->prm_t01		=(isset($oCal->t01)?$oCal->t01:0);
			$qCCC->prm_t02		=(isset($oCal->t02)?$oCal->t02:0);
			$qCCC->prm_t09		=(isset($oCal->t09)?$oCal->t09:0);
			$qCCC->prm_t10		=(isset($oCal->t10)?$oCal->t10:0);
			$qCCC->exec();
		}
	}
	function insertZP(){
		$strSql="insert into ZutrittsProfile (zpnr,acmnr,vonzeit,biszeit) values
				(?prm_zpnr,?prm_acmnr,?prm_vonzeit,?prm_biszeit)";
		$strSql="insert into ZutrittsProfile (zpnr,acmnr,vonzeit,biszeit,pincodeeingabe,
				sonntag,montag,dienstag,mittwoch,donnerstag,freitag,samstag) values
				(?prm_zpnr,?prm_acmnr,?prm_vonzeit,?prm_biszeit,0,
				1,1,1,1,1,1,1)";
		$qZP=new clsAdoacc($this->qry->get_dbLink(), $strSql);

 		$qZP->prm_zpnr=$this->qry->rec_zg_nummer;
		$qZP->prm_vonzeit="00:00:00";
		$qZP->prm_biszeit="23:59:00";
		$strSql="select ZKM_ID from tblZKMStatus stat";
		$qCCC=new clsAdoacc($this->qry->get_dbLink(), $strSql);
		$qCCC->open(null,null);
		while($qCCC->next()){
			$qZP->prm_acmnr=$qCCC->rec_zkm_id;
			$qZP->exec();
		}
	}
	function deleteZP(){
		$strSql="delete from ZutrittsProfile where zpnr=?prm_zpnr";
		$qZP=new clsAdoacc($this->qry->get_dbLink(), $strSql);
 		$qZP->prm_zpnr=$this->qry->rec_zg_nummer;
		$qZP->exec();
	}
	function delProfil($profil=null){
		$strSql="select ZKM_Index, sub_tcpIP, sub_tmskanalId from tblSubSysteme ZKM";
		$qPro=new clsAdoacc($this->qry->get_dbLink(), $strSql);
		$qPro->open(null,null);

		$tSend=$this->qry->derive_tab("tblSendData:key=satznr,auto=1");
		$tSend->rec_tx_zkmid="";
		$tSend->rec_tx_typ="00";
		while($qPro->next()){
			$str_DAT=$this->gstrTCPServerID . $qPro->rec_sub_tmskanalid . "J****!00Pz" . $profil . "**";
			$tSend->rec_tx_data=$str_DAT;
			$tSend->insert();
			echo "<font face=Courier>$str_DAT</font><br>";
		}
	}
	function addProfil($profil=null){
		$strSql="select ZP.zpnr, ZP.acmnr, ZP.vonzeit, ZP.biszeit, ZP.pincodeeingabe,
						ZP.[terminal 0]  as t00,
						ZP.[terminal 1]  as t01,
						ZP.[terminal 2]  as t02,
						ZP.[terminal 3]  as t03,
						ZP.[terminal 4]  as t04,
						ZP.[terminal 5]  as t05,
						ZP.[terminal 6]  as t06,
						ZP.[terminal 7]  as t07,
						ZP.[terminal 8]  as t08,
						ZP.[terminal 9]  as t09,
						ZP.[terminal 10] as t10,
						ZP.[terminal 11] as t11,
						ZP.[terminal 12] as t12,
						ZP.[terminal 13] as t13,
						ZP.[terminal 14] as t14,
						ZP.[terminal 15] as t15,
						ZP.[terminal 16] as t16,
						ZP.sonntag,
						ZP.montag,
						ZP.dienstag,
						ZP.mittwoch,
						ZP.donnerstag,
						ZP.freitag,
						ZP.samstag,
						ZP.st_kennung1,
						ZP.st_kennung2,
						ZP.st_kennung3
				from ZutrittsProfile ZP
				where ZP.zpnr=?prm_zpnr";
		$qPro=new clsAdoacc($this->qry->get_dbLink(), $strSql);
		$qPro->prm_zpnr=$profil;
		$qPro->open(null,null);

		$tSend=$this->qry->derive_tab("tblSendData:key=satznr,auto=1");
		$tSend->rec_tx_zkmid="";
		$tSend->rec_tx_typ="00";
		while($qPro->next()){
			$str_DAT=$this->gstrTCPServerID . $qPro->rec_acmnr . "J****!00Pz" . $qPro->rec_zpnr . "**";
			$tSend->rec_tx_data=$str_DAT;
			$tSend->insert();

			$str_PRO=$this->profilDat($qPro);
			$str_DAT=$this->gstrTCPServerID . $qPro->rec_acmnr . "J****!00PZ" . $qPro->rec_zpnr . $str_PRO . "**";
			$tSend->rec_tx_data=$str_DAT;
			$tSend->insert();
			echo "<font face=Courier>$str_DAT</font><br>";
		}
	}
	function profilDat($QRY){
		$retStr="";
		$zzz=empty($QRY->rec_vonzeit)?"00:00:00":$QRY->rec_vonzeit;
		$retStr.=date("Hi",strtotime($zzz));
		$zzz=empty($QRY->rec_biszeit)?"00:00:00":$QRY->rec_biszeit;
		$retStr.=date("Hi",strtotime($zzz));

		$retStr.=$QRY->rec_sonntag		?"J" : "N";
		$retStr.=$QRY->rec_montag		?"J" : "N";
		$retStr.=$QRY->rec_dienstag		?"J" : "N";
		$retStr.=$QRY->rec_mittwoch		?"J" : "N";
		$retStr.=$QRY->rec_donnerstag	?"J" : "N";
		$retStr.=$QRY->rec_freitag		?"J" : "N";
		$retStr.=$QRY->rec_samstag		?"J" : "N";
		$retStr.=$QRY->rec_st_kennung1	?"J" : "N";
		$retStr.=$QRY->rec_st_kennung2	?"J" : "N";
		$retStr.=$QRY->rec_st_kennung3	?"J" : "N";
		$retStr.="-";
		$retStr.=$QRY->rec_pincodeeingabe ?"J" : "N";
		$retStr.="-JN";
		for($ii=0; $ii<=16; $ii++){
			$name=str_pad($ii,2,"0",STR_PAD_LEFT);
			$retStr.=$QRY->{"rec_t$name"} ? "J" : "N";
		}
		return $retStr;
	}
}
?>
