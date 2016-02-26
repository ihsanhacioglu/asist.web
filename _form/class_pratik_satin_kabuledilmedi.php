<?php
class class_pratik_satin_kabuledilmedi extends class_pratik{

	function formtam(){
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->id);

		$qCCC=$this->qry->derive_qry($this->senaryo->pratik_sqlstr,$this->qry);
		$qCCC->senaryo=$this->senaryo->id;

		$this->bindParams($qCCC,$this->senaryo->parvalues);
		$durum=-21506; // Kabul Edilmedi
		$qCCC->prm_durum=$durum;
		if (!$this->qryExec($qCCC)){
			$this->formMessage();
			return;
		}

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
		$qCCC->prm_iliski=$this->qry->rec_iliski;
		$qCCC->open();
		
		$ilis_tab="";
		$ilis_id=0;
		if(preg_match("/(.+)-(.+)/",$this->qry->rec_iliski,$match)){$ilis_tab=$match[1]; $ilis_id=$match[2];}
		if($ilis_tab!="satin"){$this->formMessage();return;}
		
		$nDurum=-1;
		if	  ($qCCC->rec_kabuledilmedi>0)	$nDurum=-21506;
		elseif($qCCC->rec_duzeltilecek>0)	$nDurum=-21504;
		elseif($qCCC->rec_onaylanacak>0)	$nDurum=-21501;
		else								$nDurum=-21502;

		$ekacik=(isset($_POST["frm_ekacik"])?$_POST["frm_ekacik"]:"");
		$ekacik=empty($ekacik) ? "" : "\n\n".trim($ekacik);
		$acikla=trim($this->qry->rec_satin_acikla).$ekacik;
		
		$sqlStr="update asist!satin
				set durum =?prm_durum:I,
					dtarih=?prm_dtarih:D,
					dsaat =?prm_dsaat,
					acikla=?prm_acikla
				where id=?prm_id:I";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_durum =$nDurum;
		$qCCC->prm_dtarih=$this->objVal("ozaman","bugun");
		$qCCC->prm_dsaat =$this->objVal("ozaman","busaat");
		$qCCC->prm_id=$ilis_id;
		$qCCC->prm_acikla=$acikla;
		$qCCC->exec();
		$this->formMessage();
	}
}
?>