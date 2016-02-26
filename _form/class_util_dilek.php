<?php
class class_util_dilek{
	function dilek_Valid($DLK){
		$qSel=$DLK->derive_qry("select id,exp,perno from asist!perso where kimlik=?prm_kimlik and sirket=?prm_sirket order by id desc");
		$qSel->prm_kimlik=$DLK->rec_kimlik;
		$qSel->prm_sirket=$DLK->rec_sirket;
		$qSel->open();
		$DLK->__rec=(object)array();
		$DLK->__rec->perso=$qSel->rec_id;
		$DLK->__rec->exp  =$qSel->rec_exp;
		$DLK->__rec->perno=$qSel->rec_perno;
		
		$ret="";
		switch($DLK->rec_ditur){
			case-2: $ret=$this->dilek_sosyal($DLK);		break;	
			case-8:	$ret=$this->dilek_hastalik($DLK);	break;
			case-3:	$ret=$this->dilek_yillik($DLK);		break;
			case-6: $ret=$this->dilek_saatlik($DLK);	break;
			case-4:	$ret=$this->dilek_eksikkart($DLK);	break;
			case-5:	$ret=$this->dilek_gorev($DLK);		break;
			case-7:	$ret=$this->dilek_mesaidegis($DLK);	break;
			case-9: $ret=$this->dilek_fazla($DLK);		break;
			case-10:$ret=$this->dilek_tatilfazla($DLK);	break;
			case-11:$ret=$this->dilek_mesaigunu($DLK);	break;
		}
		return $ret;
	}

	function dilek_sosyal($DLK){ //rec_ditur: -2: Sosyal
		$ret="";
		if(empty($DLK->rec_atarih))return"Bei dem Sonderurlaubantrag das Datum muss eingetragen werden.<br>Die Antritts- und Endzeit müssen ohne Eingabe bleiben.";

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1!=$tar2){
			$ret="Antrittsdatum: $DLK->rec_atarih<br>
				  Enddatum     : $DLK->rec_ctarih<br>
				  Sonderurlaub kann nur für einen Tag beantragt werden.";
		}elseif(!empty($DLK->rec_asaat) || !empty($DLK->rec_csaat)){
			$ret="Antrittszeit: $DLK->rec_asaat<br>
				  Endzeit     : $DLK->rec_csaat<br>
				  Sonderurlaub kann nur für ganzen Tag beantragt werden.";
		}
		return $ret;
	}
	function dilek_hastalik($DLK){ //rec_ditur: -8: Hastalık
		$ret="";
		if(empty($DLK->rec_atarih) || empty($DLK->rec_ctarih) || (empty($DLK->rec_asaat)&&!empty($DLK->rec_csaat)) || (!empty($DLK->rec_asaat)&&empty($DLK->rec_csaat)))
			return"Bei der Krankmeldung beide Antrittsdatum und Enddatum müssen eingetragen werden.<br>
				   Für ganzen Tag Krankmeldung müssen die Antritts- und Endzeit ohne Eingabe bleiben.<br>
				   Bei der Kurzzeit- Krankmeldung Antrittszeit und Endzeit müssen auch eingetragen werden.";

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1>$tar2){
			$ret="Antrittsdatum: $DLK->rec_atarih<br>
				  Enddatum     : $DLK->rec_ctarih<br>
				  Bei der Krankmeldung Antrittsdatum muss vor dem Enddatum oder das gleiche.";
		}elseif(!empty($DLK->rec_asaat)&&!empty($DLK->rec_csaat)){
			if($tar1!=$tar2){
				$ret="Antrittsdatum: $DLK->rec_atarih<br>
					  Enddatum     : $DLK->rec_ctarih<br>
					  Die Kurzzeit- Krankmeldung kann nur für einen Tag beantragt werden.";
			}else{
				$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
				$tar2=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
				if($tar1>$tar2){
					$ret="Antrittszeit: $DLK->rec_asaat<br>
						  Endzeit     : $DLK->rec_csaat<br>
						  Antrittszeit muss vor der Endzeit.";
				}
			}
		}
		return $ret;
	}
	function dilek_yillik($DLK){ //rec_ditur: -3:Yıllık
		$ret="";
		if(empty($DLK->rec_atarih) || empty($DLK->rec_ctarih))return"Bei dem Jahresurlaubsantrag beide Antrittsdatum und Enddatum müssen eingetragen werden.";
		if(!empty($DLK->rec_asaat)||!empty($DLK->rec_csaat))
			return"Für ganzen Tag des Jahresurlaubantrags müssen die Antritts- und Endzeit ohne Eingabe bleiben.<br>
				   Bei der stündlichen Abwesenheit muss Antragsart 'Kurzzeit- Abwesenheitsantrag' ausgewählt werden.";

		$nYil=date("Y",strtotime("$DLK->rec_atarih 00:00"));
		$qSel=$DLK->derive_qry("select id from asist!izin where perso=?prm_perso and yil=?prm_yil");
		$qSel->prm_perso=$DLK->__rec->perso;
		$qSel->prm_yil	=$nYil;
		$qSel->open();
		if($qSel->reccount==0)return"Für das Jahr $nYil ist kein Urlaubsanspruchseintrag gefunden.<br>Bitte wenden Sie sich an Personalabteilung. $qSel->prm_perso {$DLK->rec_kimlik} $DLK->rec_sirket";
		$DLK->__rec->izin=$qSel->rec_id;

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1>$tar2){
			$ret="Antrittsdatum: $DLK->rec_atarih<br>
				  Enddatum     : $DLK->rec_ctarih<br>
				  Bei dem Jahresurlaubsantrag Antrittsdatum muss vor dem Enddatum oder das gleiche.";
		}
		return $ret;
	}
	function dilek_saatlik($DLK){ //rec_ditur: -6:Kısa Süreli
		$ret="";
		if(!empty($DLK->rec_atarih) && empty($DLK->rec_asaat) && empty($DLK->rec_csaat))return"Für ganzen Tag des Urlaubs muss Antragsart 'Jahresurlaubantrag' ausgewählt werden.";
		if(empty($DLK->rec_atarih) || empty($DLK->rec_asaat) || empty($DLK->rec_csaat))return"Bei dem Kurzzeit- Abwesenheitsantrag Datum, Antrittszeit und Endzeit müssen eingetragen werden.";

		$nYil=date("Y",strtotime("$DLK->rec_atarih 00:00"));
		$qSel=$DLK->derive_qry("select id from asist!izin where perso=?prm_perso and yil=?prm_yil");
		$qSel->prm_perso=$DLK->__rec->perso;
		$qSel->prm_yil	=$nYil;
		$qSel->open();
		if($qSel->reccount==0)return"Für das Jahr $nYil ist kein Urlaubsanspruchseintrag gefunden.<br>Bitte wenden Sie sich an Personalabteilung.";
		$DLK->__rec->izin=$qSel->rec_id;

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1!=$tar2){
			$ret="Antrittsdatum: $DLK->rec_atarih<br>
				  Enddatum     : $DLK->rec_ctarih<br>
				  Bei dem Kurzzeit- Abwesenheitsantrag Antrittsdatum und Enddatum muss das gleiche Datum.";
		}else{
			$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
			$tar2=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
			if($tar1>$tar2){
				$ret="Antrittszeit: $DLK->rec_asaat<br>
					  Endzeit     : $DLK->rec_csaat<br>
					  Antrittszeit muss vor der Endzeit.";
			}
		}
		return $ret;
	}
	function dilek_eksikkart($DLK){ //rec_ditur: -4: Eksikkart
		$ret="";
		if(empty($DLK->rec_atarih) || empty($DLK->rec_asaat))return"Bei der Fehlende Zeiterfassung Datum und Uhrzeit müssen eingetragen werden.<br>Wahlweise kann die Endzeit auch eingetragen werden.";
		$tar1=date("Y-m-d",strtotime("$DLK->rec_atarih 00:00"));
		$tar2=date("Y-m-d");
		if($tar1>$tar2)return"Datum kann heutigen Tag spätestens.";
		
		$qSel=$DLK->derive_qry("select id from asist!mesai where perso=?prm_perso and atarih=?prm_atarih");
		$qSel->prm_perso = $DLK->__rec->perso;
		$qSel->prm_atarih= $DLK->rec_atarih;
		$qSel->open();
		if($qSel->reccount==0)return"Für den Tag $DLK->rec_atarih ist kein Arbeitszeiteneintrag gefunden.<br>Bitte wenden Sie sich an Personalabteilung.";
		$DLK->__rec->mesai=$qSel->rec_id;

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1!=$tar2){
			$ret="Antrittsdatum: $DLK->rec_atarih<br>
				  Enddatum     : $DLK->rec_ctarih<br>
				  Bei der Fehlende Zeiterfassung Antrittsdatum und Enddatum muss das gleiche Datum.";
		}elseif(!empty($DLK->rec_asaat)&&!empty($DLK->rec_csaat)){
			$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
			$tar2=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
			if($tar1>$tar2){
				$ret="Antrittszeit: $DLK->rec_asaat<br>
					  Endzeit     : $DLK->rec_csaat<br>
					  Antrittszeit muss vor der Endzeit.";
			}
		}
		return $ret;
	}
	function dilek_gorev($DLK){ //rec_ditur: -5
		$ret="";
		if(empty($DLK->rec_atarih) || empty($DLK->rec_ctarih) || (empty($DLK->rec_asaat)&&!empty($DLK->rec_csaat)) || (!empty($DLK->rec_asaat)&&empty($DLK->rec_csaat)))
			return"Bei der Außendienstanmeldung beide Antrittsdatum und Enddatum müssen eingetragen werden.<br>
				   Für ganzen Tag Außendienstanmeldung müssen die Antritts- und Endzeit ohne Eingabe bleiben.<br>
				   Bei der Kurzzeit- Außendienstanmeldung Antrittszeit und Endzeit müssen auch eingetragen werden.";

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1>$tar2){
			$ret="Antrittsdatum: $DLK->rec_atarih<br>
				  Enddatum     : $DLK->rec_ctarih<br>
				  Antrittsdatum muss vor dem Enddatum oder das gleiche.";
		}

		$qSel=$DLK->derive_qry("select id from asist!mesai where perso=?prm_perso and atarih=?prm_atarih");
		$qSel->prm_perso = $DLK->__rec->perso;
		$qSel->prm_atarih= $DLK->rec_atarih;
		$qSel->open();
		if($qSel->reccount!=0)$DLK->__rec->mesai=$qSel->rec_id;

		return $ret;
	}
	function dilek_mesaidegis($DLK){ //rec_ditur: -7: Mesai Değiş
		$ret="";
		if(empty($DLK->rec_atarih) || empty($DLK->rec_asaat) || empty($DLK->rec_csaat))return"Bei der Arbeitszeitänderung Datum, Antrittszeit und Endzeit müssen eingetragen werden.";
		$tar1=date("Y-m-d",strtotime("$DLK->rec_atarih 00:00"));
		$tar2=date("Y-m-d");
		if($tar1>$tar2)return"Datum kann heutigen Tag spätestens.";

		$qSel=$DLK->derive_qry("select id from asist!mesai where perso=?prm_perso and atarih=?prm_atarih");
		$qSel->prm_perso = $DLK->__rec->perso;
		$qSel->prm_atarih= $DLK->rec_atarih;
		$qSel->open();
		if($qSel->reccount==0)return"Für den Tag $DLK->rec_atarih ist kein Arbeitszeiteneintrag gefunden.<br>Bitte wenden Sie sich an Personalabteilung.";
		$DLK->__rec->mesai=$qSel->rec_id;

		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 00:00");
		if($tar1!=$tar2){
			$ret="Antrittsdatum: $DLK->rec_atarih<br>
				  Enddatum     : $DLK->rec_ctarih<br>
				  Bei der Arbeitszeitänderung Antrittsdatum und Enddatum muss das gleiche Datum.";
		}else{
			$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
			$tar2=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
			if($tar1>$tar2){
				$ret="Antrittszeit: $DLK->rec_asaat<br>
					  Endzeit     : $DLK->rec_csaat<br>
					  Antrittszeit muss vor der Endzeit.";
			}
		}
		return $ret;
	}
	function dilek_mesaigunu($DLK){ //rec_ditur: -11: Mesai Günü
		$ret="";
		if(empty($DLK->rec_atarih) || empty($DLK->rec_ctarih))return"Bei der Arbeitstagwechsel Tagsdatum und Wechseltagsdatum müssen eingetragen werden.";
		$tar1=date("Y-m-d",strtotime("$DLK->rec_atarih 00:00"));
		$tar2=date("Y-m-d",strtotime("$DLK->rec_ctarih 00:00"));

		$qSel=$DLK->derive_qry("select id from asist!mesai where perso=?prm_perso and atarih=?prm_tarih");
		$qSel->prm_perso = $DLK->__rec->perso;
		$qSel->prm_tarih= $DLK->rec_atarih;
		$qSel->open();
		if($qSel->reccount==0)return"Für den Tag $DLK->rec_atarih ist kein Arbeitszeiteneintrag gefunden.<br>Bitte wenden Sie sich an Personalabteilung.";
		$DLK->__rec->mesai=$qSel->rec_id;
		$qSel->close();
		$qSel->prm_tarih= $DLK->rec_ctarih;
		$qSel->open();
		if($qSel->reccount==0)return"Für den Tag $DLK->rec_ctarih ist kein Arbeitszeiteneintrag gefunden.<br>Bitte wenden Sie sich an Personalabteilung.";
		$DLK->__rec->mesai2=$qSel->rec_id;

		return $ret;
	}
	function dilek_fazla($DLK){ //rec_ditur: -9: Fazla mesai
		$ret="";
		if(empty($DLK->rec_atarih) || empty($DLK->rec_asaat) || empty($DLK->rec_csaat))return"Bei dem Überstundenantrag Datum, Antrittszeit und Endzeit müssen eingetragen werden.";
		$tar1=date("Y-m-d",strtotime("$DLK->rec_atarih 00:00"));
		$tar2=date("Y-m-d");
		if($tar1>$tar2)return"Datum kann spätestens heutigen Tag sein.";

		$nYil=date("Y",strtotime("$DLK->rec_atarih 00:00"));
		$nAy =date("m",strtotime("$DLK->rec_atarih 00:00"));
		$nDonem=-($nYil-2000)*100-$nAy;
		$strSql="select mesai.id,atarih,opsure,opsk1,hasaat,hcsaat,asaat,csaat,mesai.hsure,mesai.isure
				from asist!perne,asist!mesai
				where perne.donem=?prm_donem
					and perne.perso=?prm_perso
					and mesai.atarih=?prm_atarih
					and perne.perso=mesai.perso";
		$qSel=$DLK->derive_qry($strSql);
		$qSel->prm_donem = $nDonem;
		$qSel->prm_perso = $DLK->__rec->perso;
		$qSel->prm_atarih= $DLK->rec_atarih;
		$qSel->open();
		if($qSel->reccount==0)return"Für den Tag $DLK->rec_atarih ist kein Arbeitszeiteneintrag und/oder Monatszeugnis gefunden.<br>Bitte wenden Sie sich an Personalabteilung.";
		$DLK->__rec->mesai =$qSel->rec_id;
		$DLK->__rec->atarih=$qSel->rec_atarih;
		$DLK->__rec->opsure=$qSel->rec_opsure;
		$DLK->__rec->opsk1 =$qSel->rec_opsk1;
		$DLK->__rec->hasaat=$qSel->rec_hasaat;
		$DLK->__rec->hcsaat=$qSel->rec_hcsaat;
		$DLK->__rec->asaat =$qSel->rec_asaat;
		$DLK->__rec->csaat =$qSel->rec_csaat;
		$DLK->__rec->hsure =$qSel->rec_hsure;
		$DLK->__rec->isure =$qSel->rec_isure;
		//var_dump($DLK->__rec);

		$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
		$tar2=date_create("$DLK->rec_ctarih $DLK->rec_csaat");
		if($tar1>$tar2){
			$ret="Antrittszeit: $DLK->rec_asaat<br>
				  Endzeit     : $DLK->rec_csaat<br>
				  Antrittszeit muss vor der Endzeit.";
		}else{
			$fark=date_diff($tar1,$tar2);
			$nYil=date_format($tar1,"Y");
			$msure=$fark->h*60+$fark->i;
			if($msure>23*60){
				$ret="Für den Tag $DLK->rec_atarih<br>
					 Überstundenzeiten<br>
					 -----------------<br>
					 Antrittszeit: $DLK->rec_asaat<br>
					 Endzeit     : $DLK->rec_csaat<br>
					 Überstundendauer kann nicht mehr als 23 Stunden sein.";
			}
		}
		return $ret;
	}
	function dilek_tatilfazla($DLK){ //rec_ditur: -10: Tailde Fazla mesai
		return $this->dilek_fazla($DLK);
	}
}
?>
