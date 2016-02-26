<?php
class class_pratik_satin_onayla extends class_pratik{
	function formpra(){
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		if($this->qry->rec_onay_durum==-21503){
			$this->strMessage="Verkauf Antrag ist schon von Ihnen genehmigt worden.";
			$this->msgMessage(5);
			return;
		}
		parent::formpra();
	}
	function formtam(){
	global $oUser, $oPerso;

		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"SU");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$durum=-21503; // Onaylandý
		$qCCC->prm_durum=$durum;
		if (!$this->qryExec($qCCC)){
			$this->msgMessage();
			return;
		}

		$iliski="satin-".$this->qry->rec_id;
		$sqlStr="select iliski,
						count(*) adet,
						sum(iif(durum=-21501,1,0)) onaylanacak,
						sum(iif(durum=-21502,1,0)) islenecek,
						sum(iif(durum=-21503,1,0)) onaylandi,
						sum(iif(durum=-21504,1,0)) duzeltilecek,
						sum(iif(durum=-21505,1,0)) islendi,
						sum(iif(durum=-21506,1,0)) kabuledilmedi,
						sum(iif(durum=-21507,1,0)) beklemede,
						sum(iif(durum=-21508,1,0)) gecersiz
					from asist!onay where iliski=?prm_iliski
					group by 1";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_iliski=$iliski;
		$qCCC->open();
		
		echo   "<br><br>onaylanacak	$qCCC->rec_onaylanacak
				<br>islenecek		$qCCC->rec_islenecek
				<br>onaylandi		$qCCC->rec_onaylandi
				<br>duzeltilecek	$qCCC->rec_duzeltilecek
				<br>islendi			$qCCC->rec_islendi
				<br>kabuledilmedi	$qCCC->rec_kabuledilmedi
				<br>beklemede		$qCCC->rec_beklemede
				<br>gecersiz		$qCCC->rec_gecersiz";

		$nDurum=-1;
		if	  ($qCCC->rec_kabuledilmedi>0)	$nDurum=-21506;
		elseif($qCCC->rec_duzeltilecek>0)	$nDurum=-21504;
		elseif($qCCC->rec_onaylanacak>0)	$nDurum=-21501;
		else								$nDurum=-21502;

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		if($this->qry->rec_perso!=$oPerso->id){
			$acikla="\n\n<span style='color:gray'>\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
			if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
			$acikla=$acikla."</span>";
		}else{
			$acikla="\n\n\n---------- ".date("d.m.Y H:i")." - $oUser->exp";
			if(!empty($ekacik))$acikla.="\nAcikla: ".trim($ekacik);
		}

		$sqlStr="update asist!satin
				set durum =?prm_durum:I,
					dtarih=?prm_dtarih:D,
					dsaat =?prm_dsaat,
					acikla=?prm_acikla
				where id=?prm_id:I";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_durum =$nDurum;
		$qCCC->prm_dtarih=$this->objVal("bugun");
		$qCCC->prm_dsaat =$this->objVal("saat");
		$qCCC->prm_id=$this->qry->rec_id;
		$qCCC->prm_acikla=trim($this->qry->rec_acikla).$acikla;
		$qCCC->exec();
		if($nDurum!=-21502){$this->msgMessage();return;}


		$sqlStr="select id from asist!gorev where relata='Satin' and relati=?prm_relati and gotur=?prm_gotur";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_relati=$this->qry->rec_id;
		$qCCC->prm_gotur =30;
		$qCCC->open();
		if($qCCC->reccount>0){$this->msgMessage();return;}


		$sqlStr="select onay.dtarih, onay.gorevi, perso.exp perso_exp
				from asist!onay, asist!perso
				where onay.perso=perso.id
					and onay.iliski=?prm_iliski
				order by 1";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_iliski=$iliski;
		$qCCC->open(null,null);
		$strOnay="";
		while($qCCC->next())$strOnay.="\n$qCCC->rec_dtarih, $qCCC->rec_perso_exp, $qCCC->rec_gorevi";

        $tGorev=$this->qry->derive_tab("from=gorev;set=1",-1);
        $tGorev->rec_exp    = "Satýnalma: ".$this->qry->rec_exp;
        $tGorev->rec_kimlik = -1;
        $tGorev->rec_proje  = -1;
        $tGorev->rec_atarih = $this->objVal("bugun");
        $tGorev->rec_ctarih = $this->objVal("1_hafta");
		$tGorev->rec_relata = "Satin";
		$tGorev->rec_relati = $this->qry->rec_id;
		$tGorev->rec_durum  = -5100; // Yeni Görev
		$tGorev->rec_gotur  = 30;
		$tGorev->rec_servis = -1;
		$tGorev->rec_kplan  = -1;
		$tGorev->rec_gorev  = -1;
		$tGorev->rec_okuma  = "--";
		if($this->qry->rec_satur_perso>0){
			$tGorev->rec_aperso = $this->qry->rec_perso;
			$tGorev->rec_perso  = $this->qry->rec_satur_perso;
		}else{
			$tGorev->rec_aperso = $this->qry->rec_perso;
			$tGorev->rec_perso  = $this->qry->rec_perso;
		}
		$perso_exp=$this->field_value("perso",$tGorev->rec_perso,"exp");
		
		$sqlStr="select exp from asist!perso where id=?prm_id";
		$qDDD=$this->qry->derive_qry($sqlStr);
		$qDDD->prm_iliski=$iliski;
		$qDDD->open(null,null);
		
		$tGorev->rec_acikla="Sayýn $perso_exp,\naþaðýdaki ürünün sipariþ onaylarý tamamlandý, lütfen satýnalma sürecini baþlatýnýz.".
							"\n\n{$this->qry->rec_exp}\n".str_repeat('-',strlen($this->qry->rec_exp)).
							"\nBütçe : {$this->qry->rec_miktar}".
							"\nAçýkla: {$this->qry->rec_acikla}".
							"\nSatur : {$this->qry->rec_satur_exp}".
							"\nKtarih: {$this->qry->rec_ktarih}".
							"\n\nOnaylar\n-------$strOnay";
		$tGorev->insert();
		$this->msgMessage();
	}
}
?>