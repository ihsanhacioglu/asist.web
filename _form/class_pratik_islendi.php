<?php
include_once("$REAL_P/_form/class_util_dilek.php");

class class_pratik_islendi extends class_pratik{
	function formtam(){
		$this->qry->close();
		$this->qry->keyOpen($this->id);

		$strClass=get_class($this->qry);
		$qCCC=new $strClass($this->qry->get_dbLink(),$this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$durum=null;
		$this->bindParams($qCCC,$this->senaryo->parvalues);
		if(($par=$qCCC->paramByName("durum"))){
			if(isset($_GET["par_durum"]))$durum=$_GET["par_durum"];
			if(is_numeric($durum) && strpos(",,-21502,-21505,-21504,-21506,-21508,",",$durum,"))$par->value=$durum;
		}
		if(isset($_GET["par_otomatik"]) && ($oto=$_GET["par_otomatik"])==1){
			$utl=new class_util_dilek();
			$this->strMessage=$utl->dilek_Valid($this->qry);
			if(empty($this->strMessage))$this->oto_isle();

			if(!empty($this->strMessage)){
				$this->strMessage="NICHT GESPEICHERT !<br><br>".$this->strMessage;
				$this->formMessage(15);
				return;
			}
		}
		if(!$this->qryExec($qCCC)){
			$this->strMessage="Der Antrag wurde nicht bearbeitet !";
			$this->formMessage(15);
			return;
		}
		$this->formMessage(3);
	}
	function oto_isle(){
		switch($this->qry->rec_ditur){
			case-2:	
			case-8:	$this->dilek_sosyal_hastalik($this->qry);	break;
			case-3:	
			case-6: $this->dilek_yillik_saatlik($this->qry);	break;
			case-4:	$this->dilek_eksikkart($this->qry);			break;
			case-5:	$this->dilek_gorev($this->qry);				break;
			case-7:	$this->dilek_mesaidegis($this->qry);		break;
			case-9:
			case-10:$this->dilek_fazla_tatilfazla($this->qry);	break;
			case-11:$this->dilek_mesaigunu($this->qry);			break;
		}
	}

	
	function dilek_sosyal_hastalik($DLK){ //rec_ditur: -2,-8
		$tabName="sizin";
		$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_perso =$DLK->__rec->perso;
		$tTab->rec_sirket=$DLK->rec_sirket;
		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_ctarih=$DLK->rec_ctarih;

		if($DLK->rec_ditur==-2){
			$tTab->rec_sebep =-25104; //Sosyal �zin
		}else{
			$tTab->rec_sebep =-25103; //Hastal�k �zni
			$tTab->rec_asaat =$DLK->rec_asaat;
			$tTab->rec_csaat =$DLK->rec_csaat;
		}
		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 24:00");
		$fark=date_diff($tar1,$tar2);
		$tTab->rec_asure =$fark->d;
		$tTab->rec_acikla=$DLK->rec_exp;
		$tTab->insert();
		$this->dilek_isle($DLK->rec_id,$tTab->fld_id->orgtable,$tTab->rec_id);
	}

	function dilek_yillik_saatlik($DLK){ //rec_ditur: -3:Y�ll�k, -6:Saatlik
		$tabName="izindty";$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_izin  =$DLK->__rec->izin;
		$tTab->rec_sirket=$DLK->rec_sirket;
		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_ctarih=$DLK->rec_ctarih;
		if($DLK->rec_ditur==-3){
			$tTab->rec_sebebi="Jahresurlaub";
		}else{
			$tTab->rec_sebebi="Kurzzeit- Abwesenheit";
			$tTab->rec_asaat =$DLK->rec_asaat;
			$tTab->rec_csaat =$DLK->rec_csaat;
		}
		$tTab->rec_acikla=$DLK->rec_exp;
		$tTab->insert();
		$this->dilek_isle($DLK->rec_id,$tTab->fld_id->orgtable,$tTab->rec_id);
	}

	function dilek_eksikkart($DLK){ //rec_ditur: -4
		$tabName="mesaidty";$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_mesai =(empty($DLK->__rec->mesai)?-1:$DLK->__rec->mesai);
		$tTab->rec_kartno=$DLK->__rec->perno;

		$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");

		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_asaat =date_format($tar1,"H:i");
		$tTab->rec_akapi ="Eksik Kart Okutma";
		$tTab->rec_sirket=$DLK->rec_sirket;
		$tTab->rec_acikla=$DLK->__rec->exp;
		$tTab->insert();
		if(!empty($DLK->rec_csaat)){
			$tar2=date_create("$DLK->rec_atarih $DLK->rec_csaat");
			$tTab->rec_asaat=date_format($tar2,"H:i");
			$tTab->insert();
		}
		$this->dilek_isle($DLK->rec_id,$tTab->fld_id->orgtable,$tTab->rec_id);
	}

	function dilek_gorev($DLK){ //rec_ditur: -5
		$tabName="gesai";$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_exp	 =$DLK->rec_exp;
		$tTab->rec_perso =$DLK->__rec->perso;
		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_ctarih=$DLK->rec_ctarih;
		$tTab->rec_asaat =$DLK->rec_asaat;
		$tTab->rec_csaat =$DLK->rec_csaat;
		$tTab->insert();
		if(!empty($DLK->rec_asaat) || !empty($DLK->rec_asaat)){
			$tabName="mesaidty";$tDty=$DLK->derive_tab("$tabName:set=1");
			$tDty->rec_mesai =(empty($DLK->__rec->mesai)?-1:$DLK->__rec->mesai);
			$tDty->rec_kartno=$DLK->__rec->perno;
			$tDty->rec_sirket=$DLK->rec_sirket;
			$tDty->rec_acikla=$DLK->__rec->exp;
			if(!empty($DLK->rec_asaat)){
				$tDty->rec_atarih=$DLK->rec_atarih;
				$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
				$tDty->rec_asaat =date_format($tar1,"H:i");
				$tDty->rec_akapi ="G�rev ba�lang��";
				$tDty->insert();
			}
			if(!empty($DLK->rec_csaat)){
				$tDty->rec_atarih=$DLK->rec_ctarih;
				$tar1=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
				$tDty->rec_asaat =date_format($tar1,"H:i");
				$tDty->rec_akapi ="G�rev biti�";
				$tDty->insert();
			}
		}
		
		$this->dilek_isle($DLK->rec_id,$tTab->fld_id->orgtable,$tTab->rec_id);
	}
	function dilek_mesaidegis($DLK){ //rec_ditur: -7
		$qUpd=$this->qry->derive_qry("update asist!mesai set hasaat=?prm_hasaat,hcsaat=?prm_hcsaat where id=?prm_id");
		$qUpd->prm_id	 =$DLK->__rec->mesai;
		$qUpd->prm_hasaat=$DLK->rec_asaat;
		$qUpd->prm_hcsaat=$DLK->rec_csaat;
		$qUpd->exec();
		$this->dilek_isle($DLK->rec_id,"mesai",$DLK->__rec->mesai);
	}
	function dilek_mesaigunu($DLK){ //rec_ditur: -11
		$qSel=$DLK->derive_qry("select id,hasaat,hcsaat from asist!mesai where id=?prm_id");
		$qSel->prm_id=$DLK->__rec->mesai;
		$qSel->open();
		$a_1=$qSel->rec_hasaat;
		$c_1=$qSel->rec_hcsaat;

		$qSel->close();
		$qSel->prm_id=$DLK->__rec->mesai2;
		$qSel->open();
		$a_2=$qSel->rec_hasaat;
		$c_2=$qSel->rec_hcsaat;

		$qUpd=$this->qry->derive_qry("update asist!mesai set hasaat=?prm_hasaat,hcsaat=?prm_hcsaat where id=?prm_id");
		$qUpd->prm_id	 =$DLK->__rec->mesai;
		$qUpd->prm_hasaat=$a_2;
		$qUpd->prm_hcsaat=$c_2;
		$qUpd->exec();
		
		$qUpd->prm_id	 =$DLK->__rec->mesai2;
		$qUpd->prm_hasaat=$a_1;
		$qUpd->prm_hcsaat=$c_1;
		$qUpd->exec();

		$this->dilek_isle($DLK->rec_id,"mesai",$DLK->__rec->mesai);
		$this->dilek_isle($DLK->rec_id,"mesai",$DLK->__rec->mesai2);
	}
	function dilek_fazla_tatilfazla($DLK){ //rec_ditur: -9,-10
		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if(!empty($DLK->rec_asaat)){
			$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
			$tar2=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
		}
		if(empty($DLK->__rec->hasaat) || $DLK->__rec->hsure==$DLK->__rec->isure){
			$fark=date_diff($tar1,$tar2);
			$msure=$fark->h*60+$fark->i;
		}else{
			$t_hasaat=date_create("{$DLK->__rec->atarih} {$DLK->__rec->hasaat}");
			$t_hcsaat=date_create("{$DLK->__rec->atarih} {$DLK->__rec->hcsaat}");
			if($tar1>=$t_hasaat && $tar1<=$t_hcsaat){
				$tar1=date_create(date_format($t_hcsaat,"Y-m-d H:i"));
				$tar1->modify("+1 minute");
			}
			if($tar2>=$t_hasaat && $tar2<=$t_hcsaat){
				$tar2=date_create(date_format($t_hasaat,"Y-m-d H:i"));
				$tar2->modify("-1 minute");
			}
			if($tar1>$tar2)$msure=0;
			else{
				$fark=date_diff($tar1,$tar2);
				$msure=$fark->h*60+$fark->i;
				if($t_hasaat>=$tar1 && $t_hasaat<=$tar2){
					$fark=date_diff($t_hasaat,$t_hcsaat);
					$msure-=$fark->h*60+$fark->i+2;
				}
			}
		}
		if($msure>12*60)
			$psure=120;
		elseif($msure>9.5*60)
			$psure=90;
		elseif($msure>7*60)
			$psure=60;
		elseif($msure>4.5*60)
			$psure=30;
		else$psure=0;

		$asure=$msure-$psure;
		$nYil=date_format($tar1,"Y");

		$tabName="fesai";$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_perso =$DLK->__rec->perso;
		$tTab->rec_sirket=$DLK->rec_sirket;
		$tTab->rec_acikla=$DLK->rec_exp;
		$tTab->rec_atarih=date_format($tar1,"Y-m-d");
		$tTab->rec_ctarih=date_format($tar2,"Y-m-d");
		$tTab->rec_asaat =date_format($tar1,"H:i");
		$tTab->rec_csaat =date_format($tar2,"H:i");
		$tTab->rec_asure=$asure;
		$tTab->rec_yil=$nYil;

		if($DLK->rec_ditur==-10 && $DLK->__rec->opsk1!=1)$tTab->rec_asure=(int)($asure*1.5);
		$tTab->insert();
		$this->dilek_isle($DLK->rec_id,$tTab->fld_id->orgtable,$tTab->rec_id);
	}
	function dilek_isle($dlk_id,$org_table,$org_id){
		$qUpd=$this->qry->derive_qry("update asist!dilek set iliski=?prm_iliski where id=?prm_id");
		$qUpd->prm_id=$dlk_id;
		$qUpd->prm_iliski="$org_table-$org_id";
		$qUpd->exec();

		$ret=true;
		try{
			echo $cmd="dilek_isle('$org_table',$org_id)";
			$oMES=new COM("MesaiApp.mesaiapp");
			$oMES->dilek_isle($org_table,$org_id);
			if(!empty($oMES->errStr)){
				$ret=false;
				$this->strMessage=$oMES->errStr;
			}
		}catch(exception $e){
			$ret=false;
			$this->strMessage=$e->getMessage();
		}
		return $ret;
	}
}
?>