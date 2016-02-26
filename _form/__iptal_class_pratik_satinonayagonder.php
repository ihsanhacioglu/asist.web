<?php

class class_pratik_satinonayagonder extends class_pratik{
	function formtam(){
        global $oUser, $oPerso, $oSirket, $REAL_P;
	
		$this->qry->close();
		$this->qry->keyOpen($this->id);
		
        $sql_str="select * from asist.satur where id=?prm_satur";
        $qCCC = new clsApp($this->appLink, $sql_str);
        $qCCC->prm_satur=$this->qry->rec_satur;
        $qCCC->open();
		$arrOnay=array();
		if($this->qry->rec_miktar>=$qCCC->rec_pamir_miktar)
			$arrOnay[$oPerso->pamir]   =(object)array("perso"=>$oPerso->pamir,   "gorevi"=>"Servis sorumlusu");
		if($this->qry->rec_miktar>=$qCCC->rec_mudur_miktar)
			$arrOnay[$oSirket->mudur]  =(object)array("perso"=>$oSirket->mudur,  "gorevi"=>"Þirket müdürü");
		if($this->qry->rec_miktar>=$qCCC->rec_gudur_miktar)
			$arrOnay[$oSirket->gudur]  =(object)array("perso"=>$oSirket->gudur,  "gorevi"=>"Genel Müdür");
		if($this->qry->rec_miktar>=$qCCC->rec_perso_miktar)
			$arrOnay[$qCCC->rec_perso] =(object)array("perso"=>$qCCC->rec_perso, "gorevi"=>"Danýþman");
		if($this->qry->rec_miktar>=$qCCC->rec_fperso_miktar)
			$arrOnay[$qCCC->rec_fperso]=(object)array("perso"=>$qCCC->rec_fperso,"gorevi"=>"Finans");

        $tOnay=$this->qry->derive_tab("onay:set=1",-1);
        $tOnay->rec_exp = $this->qry->rec_satur_exp ;
        $tOnay->rec_durum = -21501; // Onaylanacak
        $tOnay->rec_kimlik= $oPerso->kimlik;
        $tOnay->rec_iliski= "satin-{$this->qry->rec_id}";
        $tOnay->rec_dtarih= $this->objVal("ozaman","bugun");
        $tOnay->rec_dsaat = $this->objVal("ozaman","busaat");
		foreach($arrOnay as $oObj){
			if ($oObj->perso==-1 || $oObj->perso==$oPerso->id) continue;
            $tOnay->rec_perso=$oObj->perso;
            $tOnay->rec_gorevi=$oObj->gorevi;
            $tOnay->rec_sirket=$this->field_value('perso', $oObj->perso, 'sirket');
            $tOnay->rec_servis=$this->field_value('perso', $oObj->perso, 'servis');
            $tOnay->insert();
		}
		
        $sql_str="update asist.satin set durum=?prm_durum, dtarih=?prm_dtarih, dsaat=?prm_dsaat where id=?prm_id";
        $qCCC = new clsApp($this->appLink, $sql_str);
        $qCCC->prm_id=$this->id;
        $qCCC->prm_durum = -21602; // Satýn Onaylanacak
        $qCCC->prm_dtarih= $this->objVal("ozaman","bugun");
        $qCCC->prm_dsaat = $this->objVal("ozaman","busaat");
		$qCCC->exec();
		
		//include("$REAL_P/fpdf16/fpdf.php");
		include("$REAL_P/tcpdf/tcpdf.php");
		include("$REAL_P/_form/class_report.php");
		include("$REAL_P/_form/class_report_satin.php");
		$oRep=new class_report_satin($this->appLink, "report_satin");
		$oRep->id=$this->id;
		$oRep->dest="S";
		$oRep->file="$REAL_P/_arsiv/satin/satin_no_$this->id.pdf";
		$oRep->qry->close();
		$this->setWhere($oRep->qry,$oRep->senaryo->filtvalues,"S");
		$oRep->qry->keyOpen($this->id);
		$dosya=$oRep->pdf();

        $tBelge=$this->qry->derive_tab("belge:auto=1",-1);
		$tBelge->rec_exp="satin_no_$this->id.pdf";
		$tBelge->rec_iliski="satin-$this->id";
		$tBelge->rec_kimlik=$oUser->kimlik;
		$tBelge->rec_sirket=$oSirket->id;
		$tBelge->rec_kuser=$oUser->id;
		$tBelge->rec_belgeno=$this->qry->rec_belgeno;
		$tBelge->rec_dosya=$dosya;
		$tBelge->rec_tur="pdf/a";
		$tBelge->insert();

		$this->strMessage="Talep formu onaya gönderildi";
		$this->formMessage();
	}
}
?>
