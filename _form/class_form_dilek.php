<?php
include_once("$REAL_P/_form/class_util_dilek.php");
class class_form_dilek extends class_form{
	public $bet=0;
	public $tar="";

	function form(){
	global $oPerso;
		if($this->bet!=-3 && $this->bet!=-6){
			parent::form();
			return;
		}
		$nYil=date("Y");
		$qSel=$this->qry->derive_qry("select gorta,bakiye from asist!izin where perso=?prm_perso and yil=?prm_yil");
		$qSel->prm_perso=$oPerso->id;
		$qSel->prm_yil	=$nYil;
		$qSel->open();
		$this->msg.="<br>Ihr Resturlaub: ";
		if($qSel->reccount>0){
			if($qSel->rec_gorta==0)
				$this->msg.=" Unberechnet";
			else{
				$REST=round($qSel->rec_bakiye/$qSel->rec_gorta,2);
				$this->msg.=number_format($REST, 2,',','.')." Tage";
			}
		}else
			$this->msg.=" Kein Urlaubsanspruchseintrag gefunden";
		parent::form();
	}
	function formnew(){
		$ditur=0;
		if(isset($_GET["ditur"])){
			$ditur=$_GET["ditur"];
			$ditur=(is_numeric($ditur) ? $ditur : 0);
			$this->qry->rec_ditur=$ditur;
			$this->par.="&ditur=$ditur";
		}
		$this->bet=$ditur;
		if(isset($_GET["tar"])){
			$tar=$_GET["tar"];
			$this->qry->rec_atarih=$tar;
			$this->par.="&tar=$tar";
			$this->tar=$tar;
		}

		$tabDitur="ditur_$this->dil";
		$cSqlstr="select id,exp,formtemp from asist!$tabDitur where id=?prm_ditur";
		$qSel=$this->qry->derive_qry($cSqlstr);
		$qSel->prm_ditur=$ditur;
		$qSel->open();
		if($qSel->reccount==1){
			$this->senaryo->newtemp=$qSel->rec_formtemp;
			parent::formnew();
		}else{
			$cSqlstr="select id ditur,exp from asist!$tabDitur where id<-1 order by onay1";
			$qSel=$this->qry->derive_qry($cSqlstr);
			$qSel->open(null,null);
			$this->qry=$qSel;
			$this->senaryo->listtemp=$this->senaryo->newtemp;
			$objParam=$this->listpar();
			$this->listele($objParam);
		}
	}
	function afterOpen(){
		if($this->islem!="edt")return;
		$tabDitur="ditur_$this->dil";
		$cSqlstr="select id,exp,formtemp from asist!$tabDitur where id=?prm_ditur";
		$qSel=$this->qry->derive_qry($cSqlstr);
		$qSel->prm_ditur=$this->qry->rec_ditur;
		$qSel->open();
		$this->senaryo->formtemp=$qSel->rec_formtemp;
	}
	function edtValid(){
		return $this->edt_upd_del_Valid();
	}
	function updValid(){
		return $this->edt_upd_del_Valid();
	}
	function delValid(){
		return $this->edt_upd_del_Valid();
	}
	
	function edt_upd_del_Valid(){
		$cSqlstr="select durum from asist!onay where iliski=?prm_iliski and kimlik=?prm_kimlik";
		$qSel=$this->qry->derive_qry($cSqlstr);
		$qSel->prm_iliski="dilek-".$this->qry->rec_id;
		$qSel->prm_kimlik=$this->qry->rec_kimlik;
		$qSel->open(false,false);
		while($qSel->next())
		if($qSel->rec_durum!=-21501){
			echo "ID: $qSel->prm_iliski<br>";
			echo "ACCESS DENIED<br>Mindestens eine der Genehmigungen ist nicht auf der Genehmigungswarteliste.";
			return false;
		}
		return true;
	}
    function afterDelete(){
		$cSqlstr="delete from asist!onay where iliski=?prm_iliski and kimlik=?prm_kimlik";
		$qDel=$this->qry->derive_qry($cSqlstr);
        $qDel->prm_iliski="dilek-".$this->qry->rec_id;
        $qDel->prm_kimlik=$this->qry->rec_kimlik;
        $qDel->exec();
	}
    function recValid($QRY){
        global $oPerso;
        
		$QRY->rec_asaat=strtr($QRY->rec_asaat,".,","::");
		$QRY->rec_csaat=strtr($QRY->rec_csaat,".,","::");
		if(!empty($QRY->rec_asaat)){
			$asaat=$QRY->rec_asaat;
			$tar1=date_create(date("d-m-Y")." $asaat");
			$asaat=date_format($tar1,"H:i");
			$QRY->rec_asaat=$asaat;
		}
		if(!empty($QRY->rec_csaat)){
			$csaat=$QRY->rec_csaat;
			$tar2=date_create(date("d-m-Y")." $csaat");
			$csaat=date_format($tar2,"H:i");
			$QRY->rec_csaat=$csaat;
		}

		
		$QRY->rec_ctarih=(empty($QRY->rec_ctarih) ? $QRY->rec_atarih : $QRY->rec_ctarih);

		if($QRY->rec_ditur==-1)$ret="Sie müssen Art des Antrags auswählen.";
		else{
			$utl=new class_util_dilek();
			$ret=$utl->dilek_Valid($QRY);
		}
		if(!empty($ret)){
			$this->msg="NICHT GESPEICHERT !<br><br>".$ret;
			return false;
		}

        $this->arrOnay=array();
		if($this->islem!="ins")return true;

		$tabKume="kume_$this->dil";
        // Personelin amir listesi alýnýyor...
        $cSqlstr = "select osema.pamir, osema.titel, titel.exp titel_exp from asist!osema, $tabKume titel where osema.titel=titel.id and perso=$oPerso->id and osema.abc='A'";
        $qOsema=$this->qry->derive_qry($cSqlstr);
        $qOsema->prm_perso = $oPerso->id;
        $qOsema->open(false, false);
		if($qOsema->reccount==0){
			$this->msg.="NICHT GESPEICHERT !<br><br>Keine Vorgesetzte zur Genehmigung gefunden.<br>Bitte wenden Sie sich an die Personalabteilung.";
			return false;
		}
        
        while($qOsema->next()){
			$obj=(object)array();
			$obj->durum =-21501; // Onaylanacak
			$obj->perso =$qOsema->rec_pamir;
			$obj->unvan =$qOsema->rec_titel;
			$obj->gorevi=$qOsema->rec_titel_exp;
            $this->arrOnay[$qOsema->rec_pamir] = $obj;
        }
		return true;
	}
	function afterInsert(){
        global $oPerso;
        
        $tOnay=$this->qry->derive_tab("onay:set=1",-1);
        $tOnay->rec_exp    = $this->qry->rec_ditur_exp ;
        $tOnay->rec_kimlik = $oPerso->kimlik;
        $tOnay->rec_iliski = "dilek-{$this->qry->rec_id}";
        $tOnay->rec_dtarih = $this->objVal("ozaman","bugun");
        $tOnay->rec_dsaat  = $this->objVal("ozaman","busaat");
        foreach($this->arrOnay as $oObj){
            if($oObj->perso==-1 || $oObj->perso==$oPerso->id) continue;
			$tOnay->rec_durum =$oObj->durum;
            $tOnay->rec_perso =$oObj->perso;
            $tOnay->rec_unvan =$oObj->unvan;
            $tOnay->rec_gorevi=$oObj->gorevi;
            $tOnay->rec_sirket=$this->field_value('perso', $oObj->perso, 'sirket');
            $tOnay->rec_servis=$this->field_value('perso', $oObj->perso, 'servis');
            $tOnay->insert();
        }
    }
}
?>