<?php
	function izin_kisasureli_hesap($DLK,$TAB){
		$qSel=$this->qry->derive_qry("select perso,ktarih,hasaat,hcsaat from asist!mesai where perso=?prm_perso and atarih=?prm_atarih");
		$qSel->prm_perso = $DLK->rec__perso;
		$qSel->prm_atarih= $DLK->rec_atarih;
		$qSel->open();
		if($qSel->reccount==0){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihinde mesaisi<br>
				olmadýðý için kýsmi izin hesaplanamaz";
			return false;
		}
		if(empty($qSel->rec_hasaat) || empty($qSel->rec_hcsaat)){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli mesai saatleri<br>
				Baþlama: $qSel->rec_hasaat<br>
				Bitiþ  : $qSel->rec_hcsaat<br>
				tam girilmediði için kýsmi izin hesaplanamaz";
			return false;
		}
		$tar1=date_create("$DLK->rec_atarih $qSel->rec_hasaat");
		$tar2=date_create("$DLK->rec_atarih $qSel->rec_hcsaat");
		$fark=date_diff($tar1,$tar2);
		$nMesai=round(($fark->h*60+$fark->i)/60,2);
			if($nMesai>12)	$nPause=2;
		elseif($nMesai>9.5)	$nPause=1.5;
		elseif($nMesai>7)	$nPause=1;
		elseif($nMesai>4.5)	$nPause=0.5;
		else				$nPause=0;
		$nCalis=$nMesai-$nPause;
		if($nCalis<=0 || $tar1>$tar2){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli mesai saatleri<br>
				Baþlama: $qSel->rec_hasaat<br>
				Bitiþ  : $qSel->rec_hcsaat<br>
				yanlýþ girilmiþ kýsmi izin hesaplanamaz";
			return false;
		}
		$tar1=date_create("$DLK->rec_atarih $DLK->rec_asaat");
		$tar2=date_create("$DLK->rec_atarih $DLK->rec_csaat");
		if(empty($DLK->rec_asaat) || empty($DLK->rec_csaat) || $tar1>$tar2){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli<br>
				Kýsa Süreli Ýzin Dilekçesinde izin saatleri<br>
				Baþlama: $DLK->rec_asaat<br>
				Bitiþ  : $DLK->rec_csaat<br>
				yanlýþ girilmiþ kýsmi izin hesaplanamaz";
			return false;
		}

		$fark=date_diff($tar1,$tar2);
		$TAB->rec_adet = (int)(($fark->h*60+$fark->i)/60/$nCalis*100)/100;
		$TAB->rec_ctarih=$TAB->rec_atarih;
		$TAB->rec_tadet=0;

		$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
			Tarih: $DLK->rec_atarih<br>
			Mesai: $qSel->rec_hasaat-$qSel->rec_hcsaat<br>
			Süre : $nMesai<br>
			Pause: $nPause<br>
			Çalýþ: $nCalis<br>
			------------------<br>
			Ýzin : $DLK->rec_asaat-$DLK->rec_csaat<br>";
			
		if($TAB->rec_adet>1){
			$TAB->rec_asaat = "";
			$TAB->rec_csaat = "";
			$TAB->rec_adet  = 1;
			$this->strMessage.="Ýzin süresi tam güne çevrildi<br>Ýzin : $TAB->rec_adet gün";
		}else$this->strMessage.="Ýzin : $TAB->rec_adet gün";
		return true;
	}
	function izin_yillik_hesap($DLK,$TAB){
		$tar1=date_create("$DLK->rec_atarih 00:00");
		$tar2=date_create("$DLK->rec_ctarih 24:00");
		$fark=date_diff($tar1,$tar2);
		$nFarkGun=$fark->d;
		if($nFarkGun<=0 || empty($DLK->rec_atarih) || empty($DLK->rec_ctarih) || $tar1>$tar2){
			$this->strMessage="$DLK->rec_kimlik_exp personelinin<br>
				$DLK->rec_atarih tarihli<br>
				Yýllýk Ýzin Dilekçesinde izin tarihleri<br>
				Ýlk Tarih: $DLK->rec_atarih<br>
				Son Tarih: $DLK->rec_ctarih<br>
				yanlýþ girilmiþ izin hesaplanamaz";
			return false;
		}

		$qExe=$this->qry->derive_qry("execscript('create cursor curTar (tar D,gun_no I)')");
		$qExe->exec();
		$qExe=$this->qry->derive_qry("insert into curTar values (?prm_tar:D,?prm_gun_no:I)");
		$tar=$tar1;
		for($ii=0; $ii<$nFarkGun; $ii++){
			$qExe->prm_tar=$tar->format('Y-m-d');
			$qExe->prm_gun_no=$tar->format('N'); // 1 for Monday, 7 for Sunday
			$qExe->exec();
			$tar->modify("+1 day");
		}
		$cSqlStr=
		"select	cast(0 as I)sira, perso.id perso, tar atarih, perno,sirket,perso.exp perso_exp, ozgun.ipertatil,
				icase(gun_no=1,asaat1, gun_no=2,asaat2, gun_no=3,asaat3, gun_no=4,asaat4, gun_no=5,asaat5, gun_no=6,asaat6, asaat7) hasaat,
				icase(gun_no=1,csaat1, gun_no=2,csaat2, gun_no=3,csaat3, gun_no=4,csaat4, gun_no=5,csaat5, gun_no=6,csaat6, csaat7) hcsaat
		from asist!perso join asist!hacsa on perso.hacsa=hacsa.id right join curTar on .T. left join asist!ozgun on curTar.tar=ozgun.atarih
		where perso.id=?prm_perso
		into cursor curHacsa
		order by 2,3";
		$qSel=$this->qry->derive_qry($cSqlStr);
		$qSel->prm_perso=$DLK->rec__perso;;
		$qSel->open(null,null);
		$tadet=0;
		$iadet=0;
		while($qSel->next())if(empty($qSel->rec_hasaat) || $qSel->rec_ipertatil=='+') $tadet++; else $iadet++;
		$TAB->rec_tadet=$tadet;
		$TAB->rec_adet =$iadet;
		return true;
	}
?>