<?php
class class_form_satin_1 extends class_form{
	function form(){
		if($this->islem=="edt"){
			$cSqlstr="update asist!onay set okuma='+' where id=?prm_id:I";
			$qUpd=$this->qry->derive_qry($cSqlstr);
			$qUpd->prm_id=$this->qry->rec_onay_id;
			$qUpd->exec();
		}
		parent::form();
	}
    function afterDelete(){
		$cSqlstr="delete from asist!onay where iliski=?prm_iliski";
		$qDel=$this->qry->derive_qry($cSqlstr);
        $qDel->prm_iliski="satin-".$this->qry->rec_id;
        $qDel->exec();
	}
    function recValid($QRY){
        global $oPerso;
        
		if($this->islem!="ins")return true;

		if($this->qry->rec_sozlesme=="2"){
			$this->qry->rec_acikla.="\n\nSÖZLEÞMELÝ SATINALMA: Sözleþme imzalanmadan önce hukuk servisinin onayýný alýnýz.";
		}
        $this->arrOnay=array();

		$tabSatur="satur_$this->dil";
        // Personelin amir listesi alýnýyor...
        $cSqlstr = "select id,perso,amiktar,bmiktar,cmiktar,fmiktar from $tabSatur where id=?prm_satur";
        $qSatur=$this->qry->derive_qry($cSqlstr);
        $qSatur->prm_satur = $this->qry->rec_satur;
        $qSatur->open();

		$obj=(object)array();
		$obj->perso = $oPerso->id;
		$obj->kaydeden=1;
		$obj->gorevi= "Satýnalmayý Kaydeden";
		$this->arrOnay[$obj->perso] = $obj;

		$obj=(object)array();
		$obj->perso = $this->qry->rec_perso;
		$obj->gorevi= "Satýnalma Sahibi";
		$this->arrOnay[$obj->perso] = $obj;


		$cSqlstr="select sirket.perso sirket_perso,servis.perso servis_perso,servis.miktar servis_miktar from asist!sirket,asist!servis,asist!perso where sirket.id=servis.sirket and servis.id=perso.servis and perso.id=?prm_perso";
		$cSqlstr="select sirket.perso sirket_perso,servis.perso servis_perso,servis.miktar servis_miktar,100 sirket_miktar from asist!sirket,asist!servis,asist!perso where sirket.id=?prm_sirket and servis.id=perso.servis and perso.id=?prm_perso";
		$qPer=$this->qry->derive_qry($cSqlstr);
		$qPer->prm_perso=$this->qry->rec_perso;
		$qPer->prm_sirket=$this->qry->rec_sirket;
		$qPer->open();


		if($qPer->rec_sirket_miktar < $this->qry->rec_miktar){
			if($qPer->rec_sirket_perso > 0){
				$obj=(object)array();
				$obj->perso = $qPer->rec_sirket_perso;
				$obj->gorevi= "Þirket Müdürü";
				$this->arrOnay[$obj->perso] = $obj;
			}
		}
		
		if($qSatur->rec_amiktar < $this->qry->rec_miktar){
			$tabKume="kume_$this->dil";
			// Personelin amir listesi alýnýyor...
			$cSqlstr = "select osema.pamir, titel.exp titel_exp from asist!osema, $tabKume titel where osema.titel=titel.id and perso=?prm_perso and osema.abc='A'";
			$qSema=$this->qry->derive_qry($cSqlstr);
			$qSema->prm_perso = $this->qry->rec_perso;
			$qSema->open(false, false);
			while($qSema->next()){
				$obj=(object)array();
				$obj->perso =$qSema->rec_pamir;
				$obj->gorevi=$qSema->rec_titel_exp;
				$this->arrOnay[$obj->perso] = $obj;
			}
			if($qSema->reccount==0 && $qPer->rec_servis_perso > 0){
				$obj=(object)array();
				$obj->perso = $qPer->rec_servis_perso;
				$obj->gorevi= "Servis Sorumlusu";
				$this->arrOnay[$obj->perso] = $obj;
			}
		}
		if($qSatur->rec_bmiktar < $this->qry->rec_miktar){
			if($qPer->rec_sirket_perso > 0){
				$obj=(object)array();
				$obj->perso = $qPer->rec_sirket_perso;
				$obj->gorevi= "Þirket Müdürü";
				$this->arrOnay[$obj->perso] = $obj;
			}
		}
		if($qSatur->rec_cmiktar < $this->qry->rec_miktar){
			$cSqlstr = "select sirket.perso from asist!sirket where sirket.id=-1";
			$qSel=$this->qry->derive_qry($cSqlstr);
			$qSel->open();
			if($qSel->rec_perso > 0){
				$obj=(object)array();
				$obj->perso = $qSel->rec_perso;
				$obj->gorevi= "World Media Müdürü";
				$this->arrOnay[$obj->perso] = $obj;
			}
		}
		if($qSatur->rec_fmiktar < $this->qry->rec_miktar){
			$cSqlstr = "select servis.perso from asist!servis where servis.id=36";
			$qSel=$this->qry->derive_qry($cSqlstr);
			$qSel->open();
			if($qSel->rec_perso > 0){
				$obj=(object)array();
				$obj->perso = $qSel->rec_perso;
				$obj->gorevi= "Finans Servisi Sorumlusu";
				$this->arrOnay[$obj->perso] = $obj;
			}
		}
		if($qSatur->rec_perso > 0 && $qSatur->rec_perso != $this->qry->rec_perso){
			$obj=(object)array();
			$obj->perso = $qSatur->rec_perso;
			$obj->gorevi= "Satýnalma Sorumlusu";
			$this->arrOnay[$obj->perso] = $obj;
		}
		if($this->qry->rec_sozlesme=="2"){
			if($qPer->rec_sirket_perso > 0){
				$obj=(object)array();
				$obj->perso = $qPer->rec_sirket_perso;
				$obj->gorevi= "Þirket Müdürü";
				$this->arrOnay[$obj->perso] = $obj;
			}
			$cSqlstr = "select servis.perso from asist!servis where servis.id=36";
			$qSel=$this->qry->derive_qry($cSqlstr);
			$qSel->open();
			if($qSel->rec_perso > 0){
				$obj=(object)array();
				$obj->perso = $qSel->rec_perso;
				$obj->gorevi= "Hukuk Servisi Sorumlusu";
				$this->arrOnay[$obj->perso] = $obj;
			}
		}

		if(count($this->arrOnay)==0){
			$this->msg.="NICHT GESPEICHERT !<br><br>Keine Vorgesetzte zur Genehmigung gefunden.<br>Bitte wenden Sie sich an die Personalabteilung.";
			return false;
		}
		//print_r($this->arrOnay);
		//return false;

		return true;
	}
	function afterInsert(){
        global $oPerso;
        
        $tOnay=$this->qry->derive_tab("from=onay;set=1",-1);
        $tOnay->rec_exp    = $this->qry->rec_exp." ".$this->qry->rec_perso_kisad;
        $tOnay->rec_kimlik = -1;
        $tOnay->rec_iliski = "satin-{$this->qry->rec_id}";
        $tOnay->rec_dtarih = $this->objVal("ozaman","bugun");
        $tOnay->rec_dsaat  = $this->objVal("ozaman","busaat");
		$tOnay->rec_relata = "Satin";
		$tOnay->rec_relati = $this->qry->rec_id;
		$tOnay->rec_unvan  = -1;
        foreach($this->arrOnay as $oObj){
            if($oObj->perso==-1) continue;
			$tOnay->rec_durum  = -21501; // Onaylanacak;
			if(isset($oObj->kaydeden))$tOnay->rec_durum=-21503; // Onaylandý
            $tOnay->rec_perso =$oObj->perso;
            $tOnay->rec_gorevi=$oObj->gorevi;
            $tOnay->rec_sirket=$this->field_value('perso', $oObj->perso, 'sirket');
            $tOnay->rec_servis=$this->field_value('perso', $oObj->perso, 'servis');
            $tOnay->insert();
        }
    }
}
?>