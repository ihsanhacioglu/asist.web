<?php
class class_report_masraf extends class_report{
	public $act="form_masraf";

	function pdf(){
	global $oPerso,$oSirket;
		$arrLoc=localeconv();
		$arrLoc["decimal_point"]=",";
		setlocale(LC_NUMERIC,$arrLoc);

		$this->qry->keyOpen($this->id);

		$arrOnay=array();
		$arrOnay[]=(object)array("exp"=>"","gorevi"=>"Besteller");
		$arrOnay[]=(object)array("exp"=>"","gorevi"=>"Abteilungsleiter");
		$arrOnay[]=(object)array("exp"=>"","gorevi"=>"Geschäftsführer");

		$sqlStr="select * from asist.masrafdty where masraf=?prm_masraf";
        $qCCC = $this->qry->derive_qry($sqlStr);
		$qCCC->prm_masraf=$this->qry->rec_id;
		$qCCC->open(null,null);
		if($qCCC->reccount==0){
			$this->strMessage="Die Kosteneinzelheiten müssen Sie eingeben.";
			$this->msgMessage();
			return;
		}

		$pdf=new TCPDF("P","mm","A4",0,"cp1254");
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDisplayMode("fullpage");
		$pdf->setleftmargin(25);
		$pdf->SetDrawColor(214);
		$pdf->AddPage();
		
		//$pdf->AddFont('Arial','','arial.php');
		//$pdf->AddFont('Arial','B','arialbd.php');
		
		$pdf->SetFont('Arial','',19);
		$pdf->setY(30);
		$pdf->Cell(170,5,'Kostenabrechnung',0,1,'C');
		$pdf->ln(7);

		$pdf->SetFont('Arial','B',9);
		$pdf->SetX(150);$pdf->Cell(15,5,'Datum',0);   $pdf->Cell(30,5,': '.date("d.m.Y",strtotime("{$this->qry->rec_ktarih}")),0,1);
		$pdf->SetX(150);$pdf->Cell(15,5,'Nummer',0);  $pdf->Cell(30,5,': '.$this->qry->rec_id,0,1);
		
		$yy=$pdf->GetY();
		$pdf->Cell(20,5,'Besteller',0,1);
		$pdf->Cell(20,5,'Firma',0,1);
		$pdf->Cell(20,5,'Abteilung',0);
		$xx=$pdf->getx();
		
		$pdf->SetY($yy);
		$pdf->SetFont('Arial','',9);
		$pdf->SetX($xx);$pdf->Cell(30,5,': '.$this->qry->rec_perso_exp,0,1);
		$pdf->SetX($xx);$pdf->Cell(30,5,': '.$this->qry->rec_sirket_exp,0,1);
		$pdf->SetX($xx);$pdf->Cell(30,5,': '.$this->qry->rec_servis_exp,0,1);
		$pdf->ln(7);

		$pdf->SetFillColor(224);
		$pdf->SetFont('Arial','I',7);
		$pdf->Cell(7,5,"",'LTB',0,'',1);
		$pdf->Cell(16,5,"Datum",1,0,'C',1);
		$pdf->Cell(117,5,"Einkaufsort, Aufwendungsort, Bezeichnung",1,0,'',1);
		//$pdf->Cell(50,5,"Einkaufsort",1,0,'',1);
		//$pdf->Cell(30,5,"Aufwendungsort",1,0,'',1);
		//$pdf->Cell(37,5,"Bezeichnung",1,0,'',1);
		$pdf->Cell(20,5,"Betrag(ink.MwSt)",1,1,'',1);
		$yy=$pdf->GetY();
		$xx=$pdf->getx();

		$pdf->SetFont('Arial','',7);
		$nn=0;
		$sum_miktar=0;
		while($qCCC->next()){
			$miktar=$qCCC->rec_miktar*1;
			$sum_miktar+=$qCCC->rec_miktar;

			//$pdf->Cell(7,5,++$nn,1,0,'C');
			//$pdf->Cell(16,5,date("d.m.Y",strtotime("{$qCCC->rec_tarih}")),1,0,'C');
			//$pdf->Cell(50,5,$qCCC->rec_yer,1,0);
			//$pdf->Cell(30,5,$qCCC->rec_harca,1,0);
			//$pdf->Cell(37,5,$qCCC->rec_acikla,1,0);
			//$pdf->Cell(20,5,number_format($miktar,2,',','.')." €",1,1,'R');

			$pdf->setx(23+$xx);
			$y1=$pdf->getY();
			$pdf->multiCell(117,5,"$qCCC->rec_yer, $qCCC->rec_harca, $qCCC->rec_acikla",1,'',0,1);
			$y2=$pdf->getY();

			$pdf->sety($y1);
			$pdf->Cell(7,$y2-$y1,++$nn,1,0,'C');
			$pdf->Cell(16,$y2-$y1,date("d.m.Y",strtotime("{$qCCC->rec_tarih}")),1,0,'C');

			$pdf->sety($y1);
			$pdf->setx(140+$xx);
			$pdf->Cell(20,$y2-$y1,number_format($miktar,2,',','.')." €",1,1,'R');

		}
		for($ii=$nn+1; $ii<9; $ii++){
			$pdf->Cell(7,5,$ii,1,0,'C');
			$pdf->Cell(153,5,'',1,1);
		}

		$yy=$pdf->getY();
		$pdf->SetFillColor(234);
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(10,5,'','LTB');
		$pdf->Cell(130,5,'Insgesamt (ink.MwSt)','TB',0,'R');
		$pdf->Cell(20,5,number_format($sum_miktar,2,',','.')." €",1,1,'R');
		$pdf->ln(7);

		$yy=$pdf->gety();
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(160,5,'Erläuterungen und Hinweise',1,1);
		$pdf->SetFont('Arial','',9);
		$pdf->rect($pdf->getx(),$pdf->gety(),160,30);
		$pdf->multiCell(160,4,$this->qry->rec_acikla,0,'',0,0,'','',1,0,0,0,30);
		$pdf->sety($yy+35);
		$pdf->ln(7);

		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(count($arrOnay)*40,5,'Unterschriften',0,1);
		$yy=$pdf->gety();
		$xx=$pdf->getx();
		$nn=0;
		foreach($arrOnay as $pam=>$oPam){
			if($pam==-1)continue;
			$pdf->SetFont('Arial','B',9);
			$pdf->setxy(40*$nn+$xx,$yy);$pdf->Cell(40,5,$oPam->gorevi,1,1,'C');
			$pdf->setx(40*$nn+$xx);$pdf->rect($pdf->getx(),$pdf->gety(),40,20);
			$pdf->SetFont('Arial','',9);
			//$pdf->Cell(40,5,$oPam->exp);
			$pdf->SetFont('Arial','',5);
			$pdf->Cell(40,5,$oPam->exp,0,1,'C');
			$nn++;
		}

		$pdf->Output("Masraf_$this->id.pdf","I");
	}
}
?>