<?php
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
			$RET=$this->oto_isle();
			if(!$RET || !empty($this->strMessage)){
				$this->strMessage="NICHT GESPEICHERT !<br><br>".$this->strMessage;
				$this->formMessage(7);
			}
			return;
		}
		if(!$this->qryExec($qCCC)){
			$this->strMessage="Der Antrag wurde nicht bearbeitet !";
			$this->formMessage(7);
			return;
		}
		$this->formMessage(3);
	}
	function oto_isle(){
		$qSel=$this->qry->derive_qry("select id,exp,perno from asist!perso where kimlik=?prm_kimlik and sirket=?prm_sirket order by id desc");
		$qSel->prm_kimlik=$this->qry->rec_kimlik;
		$qSel->prm_sirket=$this->qry->rec_sirket;
		$qSel->open();
		$this->qry->rec__perso=$qSel->rec_id;
		$this->qry->rec__exp  =$qSel->rec_exp;
		$this->qry->rec__perno=$qSel->rec_perno;

		$RET=1;
		switch($this->qry->rec_betur){
			case-2:	
			case-8:	$RET=$this->dilek_sosyal_hastalik($this->qry);	break;
			case-3:	
			case-6: $RET=$this->dilek_yillik_saatlik($this->qry);break;
			case-4:	$RET=$this->dilek_eksikkart($this->qry);		break;
			case-5:	$RET=$this->dilek_gorev($this->qry);			break;
			case-7:	$RET=$this->dilek_mesaidegis($this->qry);		break;
			case-9:
			case-10:$RET=$this->dilek_fazla_tatilfazla($this->qry);	break;
		}
		return $RET;
	}

	
	function dilek_sosyal_hastalik($DLK){ //rec_betur: -2,-8
		$tabName="sizin";
		$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_perso =$DLK->rec__perso;
		$tTab->rec_sirket=$DLK->rec_sirket;
		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_ctarih=$DLK->rec_ctarih;
		$tTab->rec_asaat ="";
		$tTab->rec_csaat ="";

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1>$tar2){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				Baþlama: $DLK->rec_atarih<br>
				Bitiþ  : $DLK->rec_ctarih<br>
				Tarihleri hatalý.";
			return null;
		}
		if($DLK->rec_betur==-2){
			$tTab->rec_sebep =-25104; //Sosyal Ýzin
			if($tar1!=$tar2){
				$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
					$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
					Baþlama: $DLK->rec_atarih<br>
					Bitiþ  : $DLK->rec_ctarih<br>
					Ýzin 1 günlük olmalý.";
				return null;
			}
		}else{
			$tTab->rec_sebep =-25103; //Hastalýk Ýzni
			if(!empty($DLK->rec_asaat)&&!empty($DLK->rec_csaat)){
				if($tar1!=$tar2){
					$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
						$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
						Baþlama: $DLK->rec_atarih<br>
						Bitiþ  : $DLK->rec_ctarih<br>
						Saatlik izin için baþlama bitiþ tarihleri ayný gün olmalý.";
					return null;
				}
				$tTab->rec_asaat =$DLK->rec_asaat;
				$tTab->rec_csaat =$DLK->rec_csaat;
			}
		}
			$tar1=date_create("$DLK->rec_atarih 00:00");
			$tar2=date_create("$DLK->rec_ctarih 24:00");
			$fark=date_diff($tar1,$tar2);
		$tTab->rec_asure =$fark->d;
		$tTab->rec_acikla=$DLK->rec_exp;
		$tTab->insert();
		$this->dilek_isle($DLK,$tTab);
		return true;
	}
	function dilek_yillik_saatlik($DLK){ //rec_betur: -3:Yýllýk, -6:Saatlik
		$nYil=date("Y",strtotime("$DLK->rec_atarih"));
		$qSel=$this->qry->derive_qry("select id from asist!izin where perso=?prm_perso and yil=?prm_yil");
		$qSel->prm_perso=$DLK->rec__perso;
		$qSel->prm_yil	=$nYil;
		$qSel->open();
		if($qSel->reccount==0){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				$nYil yýlý için<br>
				Ýzin tahakkuku yapýlmamýþ.";
			return null;
		}

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1>$tar2){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				Baþlama: $DLK->rec_atarih<br>
				Bitiþ  : $DLK->rec_ctarih<br>
				Tarihleri hatalý.";
			return null;
		}
		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_ctarih=$DLK->rec_ctarih;

		$tabName="izindty";$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_izin=$qSel->rec_id;
		$tTab->rec_sirket=$DLK->rec_sirket;
		if($DLK->rec_betur==-3){
			$tTab->rec_sebebi="Jahresurlaub";
		}else{
			$tTab->rec_sebebi="Kurzzeit- Abwesenheit";
			if($tar1!=$tar2){
				$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
					$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
					Baþlama: $DLK->rec_atarih<br>
					Bitiþ  : $DLK->rec_ctarih<br>
					Saatlik izin için baþlama bitiþ tarihleri ayný gün olmalý.";
				return null;
			}
			if(empty($DLK->rec_asaat)||empty($DLK->rec_csaat)){
				$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
					$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
					Baþlama bitiþ saatleri<br>
					Baþlama: $DLK->rec_asaat<br>
					Bitiþ  : $DLK->rec_csaat<br>
					Eksik girilmiþ.";
				return null;
			}
			if($DLK->rec_asaat>$DLK->rec_csaat){
				$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
					$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
					Baþlama bitiþ saatleri<br>
					Baþlama: $DLK->rec_asaat<br>
					Bitiþ  : $DLK->rec_csaat<br>
					Hatalý girilmiþ.";
				return null;
			}
			$tTab->rec_asaat =$DLK->rec_asaat;
			$tTab->rec_csaat =$DLK->rec_csaat;
		}
		$tTab->rec_acikla=$DLK->rec_exp;
		$tTab->insert();
		$this->dilek_isle($DLK,$tTab);
		return true;
	}
	function dilek_eksikkart($DLK){ //rec_betur: -4
		$qSel=$this->qry->derive_qry("select id from asist!mesai where perso=?prm_perso and atarih=?prm_atarih");
		$qSel->prm_perso = $DLK->rec__perso;
		$qSel->prm_atarih= $DLK->rec_atarih;
		$qSel->open();

		$tabName="mesaidty";$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_mesai =empty($qSel->rec_id)?-1:$qSel->rec_id;
		$tTab->rec_kartno=$DLK->rec__perno;
		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_asaat =$DLK->rec_asaat;
		$tTab->rec_akapi ="Düzeltme Dilekçesi";
		$tTab->rec_sirket=$DLK->rec_sirket;
		$tTab->rec_acikla=$DLK->rec__exp;
		$tTab->insert();
		if(!empty($DLK->rec_csaat)){
			$tTab->rec_asaat=$DLK->rec_csaat;
			$tTab->insert();
		}
		$this->dilek_isle($DLK,$tTab);
		return true;
	}
	function dilek_gorev($DLK){ //rec_betur: -5
		if((!empty($DLK->rec_asaat)&&empty($DLK->rec_csaat)) || (empty($DLK->rec_asaat)&&!empty($DLK->rec_csaat)))
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				Baþlama: $DLK->rec_asaat<br>
				Bitiþ  : $DLK->rec_csaat<br>
				Saatlik görev için baþlama bitiþ saatleri girilmeli.";
			return null;
		}

		$tabName="gesai";$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_exp	 =$DLK->rec_exp;
		$tTab->rec_perso =$DLK->rec__perso;
		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_ctarih=$DLK->rec_ctarih;
		$tTab->rec_asaat =$DLK->rec_asaat;
		$tTab->rec_csaat =$DLK->rec_csaat;
		$tTab->insert();
		$this->dilek_isle($DLK,$tTab);
	}
	function dilek_mesaidegis($DLK){ //rec_betur: -7
		$qSel=$this->qry->derive_qry("select id asist!mesai where where atarih=?prm_atarih and perso=?prm_perso");
		$qSel->prm_atarih=$DLK->rec_atarih;
		$qSel->prm_perso=$DLK->rec__perso;
		$qSel->open();
		if($qSel->reccount==0){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				$DLK->rec_atarih günü için<br>
				Mesai tahakkuku yapýlmamýþ.";
			return null;
		}
		if(empty($DLK->rec_asaat)||empty($DLK->rec_csaat)){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				Baþlama bitiþ saatleri<br>
				Baþlama: $DLK->rec_asaat<br>
				Bitiþ  : $DLK->rec_csaat<br>
				Eksik girilmiþ.";
			return null;
		}

		$qUpd=$this->qry->derive_qry("update asist!mesai set hasaat=?prm_hasaat,hcsaat=?prm_hcsaat where id=?prm_id");
		$qUpd->prm_id	 =$qSel->rec_id;
		$qUpd->prm_hasaat=$DLK->rec_asaat;
		$qUpd->prm_hcsaat=$DLK->rec_csaat;
		$qUpd->exec();
		$this->dilek_isle($DLK,$qSel);
		return true;
	}
	function dilek_fazla_tatilfazla($DLK){ //rec_betur: -9,-10
		$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
		$tar2=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
		$fark=date_diff($tar1,$tar2);

		if(empty($DLK->rec_asaat)||empty($DLK->rec_csaat)||$tar1>$tar2){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				Fazla mesai saatleri<br>
				Baþlama: $DLK->rec_asaat<br>
				Bitiþ  : $DLK->rec_csaat<br>
				yanlýþ girilmiþ fazla mesai hesaplanamaz";
			return null;
		}
		if(empty($fark->d>0){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				Fazla mesai tarihleri<br>
				Baþlama: $DLK->rec_atarih<br>
				Bitiþ  : $DLK->rec_ctarih<br>
				yanlýþ girilmiþ fazla mesai hesaplanamaz";
			return null;
		}

		$nYil=date_format($tar1,"Y");
		$nAy =date_format($tar1,"m");
		$nDonem=-($nYil-2000)*100-$nAy;
		$strSql="select mesai.perso,atarih,opsure,opsk1,hasaat,hcsaat,asaat,csaat
				from asist!perne,asist!mesai
				where perne.donem=?prm_donem
					and perne.perso=?prm_perso
					and mesai.atarih=?prm_atarih
					and perne.perso=mesai.perso";
		$qSel=$this->qry->derive_qry($strSql);
		$qSel->prm_donem = $nDonem;
		$qSel->prm_perso = $DLK->rec__perso;
		$qSel->prm_atarih= $DLK->rec_atarih;
		$qSel->open();
		if($qSel->reccount==0){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli $DLK->betur_exp dilekçesinde<br>
				$DLK->rec_atarih günü için<br>
				Mesai ve/veya Karne tahakkuku yapýlmamýþ.";
			return null;
		}

		$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
		$tar2=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
		if(!empty($qSel->rec_hasaat)){
			$nOpsdak=($qSel->rec_opsure<20?20:$qSel->rec_opsure);
			$t_csaat=date_create("$qSel->rec_atarih $qSel->rec_csaat");
			$t_opsdak=date_create("$qSel->rec_atarih $qSel->rec_hcsaat");
			$t_opsdak->modify("+$nOpsdak minute");
			
			$t_csaat=($t_csaat>$t_opsdak ? $t_opsdak : $t_csaat);
			$tar1=($tar1<$t_csaat ? $t_csaat : $tar1);

			if($tar1>$tar2){
				$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
					$DLK->rec_atarih tarihli<br>
					Normal mesai saatleri<br>
					Baþlama: $qSel->rec_hasaat<br>
					Bitiþ  : $qSel->rec_hcsaat<br><br>
					Fazla Mesai Dilekçesinde fazla mesai saatleri<br>
					Baþlama: $DLK->rec_asaat<br>
					Bitiþ  : $DLK->rec_csaat<br>
					yanlýþ girilmiþ fazla mesai hesaplanamaz";
				return null;
			}
		}

		$tabName="fesai";$tTab=$DLK->derive_tab("$tabName:set=1");
		$tTab->rec_perso =$DLK->rec__perso;
		$tTab->rec_sirket=$DLK->rec_sirket;
		$tTab->rec_acikla=$DLK->rec_exp;
		$tTab->rec_atarih=$DLK->rec_atarih;
		$tTab->rec_ctarih=$DLK->rec_ctarih;
		$tTab->rec_asaat =date_format($tar1,"H:i");
		$tTab->rec_csaat =date_format($tar2,"H:i");
			$fark=date_diff($tar1,$tar2);
			$nYil=date_format($tar1,"Y");
		$msure=$fark->h*60+$fark->i;

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

		$tTab->rec_asure=$asure;
		if($DLK->rec_betur==-10 && $qSel->rec_opsk1!=1)
			$tTab->rec_asure=(int)($asure*1.5);
			
		$tTab->rec_yil=$nYil;
		$tTab->insert();
		$this->dilek_isle($DLK,$tTab);
		return true;
	}

	function dilek_isle($DLK,$TAB){
		$qUpd=$this->qry->derive_qry("update asist!dilek set iliski=?prm_iliski where id=?prm_id");
		$qUpd->prm_id=$DLK->rec_id;
		$qUpd->prm_iliski=$TAB->fld_id->orgtable."-$TAB->rec_id";
		$qUpd->exec();

		
		try{
			echo $cmd="dilek_isle('{$TAB->fld_id->orgtable}',$TAB->rec_id)";
			$oMES=new COM("MesaiApp.mesaiapp");
			$oMES->dilek_isle($TAB->fld_id->orgtable,$TAB->rec_id);
			if(!empty($oMES->errStr))$this->strMessage=$oMES->errStr;
		}catch(exception $e){
			$this->strMessage=$e->getMessage();
		}
		//$qExe=$this->qry->derive_qry("__dilek_isle('{$TAB->fld_id->orgtable}',$TAB->rec_id)");
		//$qExe->exec();
	}
}


?>