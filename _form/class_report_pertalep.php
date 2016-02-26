<?php
class class_report_pertalep extends class_report{
	public $act="form_pertalep";

	function pdf(){
	global $oPerso,$oSirket;
		$arrLoc=localeconv();
		$arrLoc["decimal_point"]=",";
		setlocale(LC_NUMERIC,$arrLoc);

		$this->qry->keyOpen($this->id);

		$arrOnay=array();
		$arrOnay[]=(object)array("exp"=>"","gorevi"=>"Þirket Müdürü/Birim sorumlusu");
		$arrOnay[]=(object)array("exp"=>"","gorevi"=>"Personel Müdürü");
		$arrOnay[]=(object)array("exp"=>"","gorevi"=>"Genel Müdür");

		$pdf=new TCPDF("P","mm","A4",0,"cp1254");
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDisplayMode("fullpage");
		$pdf->setleftmargin(25);
		$pdf->SetDrawColor(214);
		$pdf->AddPage();
		
		//$pdf->AddFont('Times','','Times.php');
		//$pdf->AddFont('Times','B','Timesbd.php');
		
		$pdf->SetFont('Times','',19);
		$pdf->setY(30);
		$pdf->Cell(170,5,'Personel Talep Formu',0,1,'C');
		$pdf->ln(7);

		$pdf->SetFont('Times','B',9);
		$pdf->SetX(150);$pdf->Cell(15,5,'Datum',0);   $pdf->Cell(30,5,': '.date("d.m.Y",strtotime("{$this->qry->rec_ktarih}")),0,1);
		$pdf->SetX(150);$pdf->Cell(15,5,'Nummer',0);  $pdf->Cell(30,5,': '.$this->qry->rec_id,0,1);
		
		$yy=$pdf->gety();
		$pdf->Cell(20,5,'Þirket',0,1);
		$pdf->Cell(20,5,'Servis',0);
		$xx=$pdf->getx();
		
		$pdf->SetY($yy);
		$pdf->SetFont('Times','',9);
		$pdf->SetX($xx);$pdf->Cell(30,5,': '.$this->qry->rec_sirket_exp,0,1);
		$pdf->SetX($xx);$pdf->Cell(30,5,': '.$this->qry->rec_servis_exp,0,1);
		$pdf->ln(5);

		$pdf->SetFont('Times','B',9);
		$pdf->Cell(160,5,'Yeni personel',0,1);

		$pdf->SetFont('Times','I',8);
		$yy=$pdf->gety();
		$pdf->Cell(35,5,"Adý Soyadý",1,1);
		$pdf->Cell(35,5,"Baþlama tarihi",1,1);
		$pdf->Cell(35,5,"Sözleþme süresi",1,1);
		$pdf->Cell(35,5,"Ýþ tanýmý",1,1);
		$pdf->Cell(35,5,"Brüt maaþ (saat ücreti)",1,1);
		$pdf->Cell(35,5,"Net maaþ",1,1);
		$pdf->Cell(35,5,"Sözleþme türü",1,1);
		$pdf->Cell(35,5,"Vergi durumu (1,3,4,5)",1,1);
		$pdf->Cell(35,5,"Çalýþma süresi",1,1);
		$pdf->Cell(35,5,"Mesai durumu",1,1);
		$xx=$pdf->getx()+35;

		$pdf->SetFont('Times','',9);
		$pdf->SetY($yy);
		$pdf->SetX($xx);$pdf->Cell(125,5,$this->qry->rec_adsoyad,"TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,date("d.m.Y",strtotime("{$this->qry->rec_bastarih}")),"TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,date("d.m.Y",strtotime("{$this->qry->rec_bittarih}")),"TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,$this->qry->rec_istanim,"TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,number_format($this->qry->rec_brutmaas,2,',','.')." €","TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,number_format($this->qry->rec_netmaas,2,',','.')." €","TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,$this->qry->rec_sozlestur,"TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,$this->qry->rec_versinif,"TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,$this->qry->rec_calissaat,"TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,$this->qry->rec_mesai,"TRB",1);
		$pdf->ln(5);

		$pdf->SetFont('Times','B',9);
		$pdf->Cell(160,5,'Talep eden',0,1);
		$pdf->SetFont('Times','I',8);

		$yy=$pdf->gety();
		$pdf->Cell(35,5,"Adý Soyadý",1,1);
		$pdf->Cell(35,5,"Birim",1,1);
		$xx=$pdf->getx()+35;

		$pdf->SetFont('Times','',9);
		$pdf->SetY($yy);
		$pdf->SetX($xx);$pdf->Cell(125,5,$this->qry->rec_perso_exp,"TRB",1);
		$pdf->SetX($xx);$pdf->Cell(125,5,$this->qry->rec_servis_exp,"TRB",1);
		$pdf->ln(5);

		$yy=$pdf->gety();
		$pdf->SetFont('Times','B',9);
		$pdf->Cell(160,5,'Niçin ihtiyaç duyulduðuna dair açýklama ve diðer notlar',0,1);
		$pdf->SetFont('Times','',9);
		$pdf->rect($pdf->getx(),$pdf->gety(),160,30);
		$pdf->multiCell(160,4,$this->qry->rec_acikla,0,'',0,0,'','',1,0,0,0,30);
		$pdf->sety($yy+35);
		$pdf->ln(7);

		$pdf->SetFont('Times','B',9);
		$pdf->Cell(count($arrOnay)*50,5,'Talep sürecine ait imzalar',0,1);
		$yy=$pdf->gety();
		$xx=$pdf->getx();
		$nn=0;
		foreach($arrOnay as $pam=>$oPam){
			if($pam==-1)continue;
			$pdf->SetFont('Times','BI',7);
			$pdf->setxy(50*$nn+$xx,$yy);$pdf->Cell(50,5,$oPam->gorevi,1,1,'C');
			$pdf->setx(50*$nn+$xx);$pdf->rect($pdf->getx(),$pdf->gety(),50,20);
			$pdf->SetFont('Times','',5);
			$pdf->Cell(50,5,$oPam->exp,0,1,'C');
			$nn++;
		}

		$pdf->Output("Pertalep_$this->id.pdf","I");
	}
}
?>
