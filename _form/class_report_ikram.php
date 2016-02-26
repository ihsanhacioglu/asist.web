<?php
class class_report_ikram extends class_report{
	public $act="form_ikram";

	function pdf(){
	global $REAL_P;
		$arrLoc=localeconv();
		$arrLoc["decimal_point"]=",";
		setlocale(LC_NUMERIC,$arrLoc);

		$yil=date("Y",strtotime("{$this->qry->rec_tarih}"));
		$baslik="Bewirtungsformular $yil";

		$fld1 ="%FDF-1.2\n%âãÏÓ\n1 0 obj<</FDF<</Fields[\n";
		$fld1.="<</T(baslik)/V($baslik)>>\n";
		$fld1.="<</T(id)/V({$this->qry->rec_id})>>\n";
		$fld1.="<</T(perso_exp)/V({$this->qry->rec_perso_exp})>>\n";
		$fld1.="<</T(tarih)/V(".date("d.m.Y",strtotime("{$this->qry->rec_tarih}")).")>>\n";
		$fld1.="<</T(yer)/V({$this->qry->rec_yer}\n{$this->qry->rec_yeradr})>>\n";
		$fld1.="<</T(kisiler)/V({$this->qry->rec_kisiler})>>\n";
		$fld1.="<</T(gerekce)/V({$this->qry->rec_gerekce})>>\n";
		$fld1.="<</T(yemek)/V(1)>>\n";
		//$fld1.="<</T(diger)/V(0)>>\n";

		$fld1.="<</T(miktar)/V(".number_format($this->qry->rec_miktar,2,',','.').")>>\n";
		$fld1.="<</T(kdv)/V(".number_format($this->qry->rec_kdv,2,',','.').")>>\n";
		$fld1.="<</T(net)/V(".number_format($this->qry->rec_net,2,',','.').")>>\n";
		$fld1.="<</T(ktarih)/V(".date("d.m.Y",strtotime("{$this->qry->rec_ktarih}")).")>>\n";

		if($this->qry->rec_tur=="islet"){
			$islet_100=$this->qry->rec_net;
			$islet_vergi=$this->qry->rec_kdv;
			$fld1.="<</T(islet)/V(1)>>\n";
			$fld1.="<</T(islet_100)/V(".number_format($islet_100,2,',','.').")>>\n";
			$fld1.="<</T(islet_vergi)/V(".number_format($islet_vergi,2,',','.').")>>\n";
		}else{
			$fld1.="<</T(ticari)/V(1)>>\n";
			$ticari_vergi=$this->qry->rec_kdv;
			$ticari_70=$this->qry->rec_net*0.7;
			$ticari_30=$this->qry->rec_net*0.3;
			$fld1.="<</T(ticari)/V(1)>>\n";
			$fld1.="<</T(ticari_70)/V(".number_format($ticari_70,2,',','.').")>>\n";
			$fld1.="<</T(ticari_30)/V(".number_format($ticari_30,2,',','.').")>>\n";
			$fld1.="<</T(ticari_vergi)/V(".number_format($ticari_vergi,2,',','.').")>>\n";
		}
		$fld1.="]>>>>\nendobj\ntrailer\n<</Root 1 0 R>>\n%%EOF";

		$fil0=tempnam("$REAL_P/pdftk","fld"); $hh=fopen("$fil0","w"); fwrite($hh,$fld1); fclose($hh);
		$pdf_name=$this->ChrtranEng(trim($this->qry->rec_perso_exp));

		header("Content-Type: application/pdf");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header("Content-Disposition: inline; filename=\"Ikram_{$this->qry->rec_id}_$pdf_name.pdf\";");
		passthru("$REAL_P/pdftk/pdftk.exe $REAL_P/pdftk/wmg_bew.pdf fill_form $fil0 output - flatten");
		unlink($fil0);
	}
}
?>