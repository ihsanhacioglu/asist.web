<?php
class class_report_topla extends class_report{
	public $act="form_topla";

	function pdf(){
	global $oPerso,$oSirket;
		setlocale(LC_TIME,"trk");

		$tur=isset($_GET["tur"])?$_GET["tur"]:"bugun";
		if($tur=="bugun"){
			$tar1="=".$this->objVal("bugun");
		}elseif($tur=="yarin"){
			$tar1="=".$this->objVal("yarin");
		}elseif($tur=="haftabasi"){
			$tar1="=".$this->objVal("haftabasi");
		}elseif($tur=="g_haftabasi"){
			$tar1="=".$this->objVal("g_haftabasi");
		}else$tar1=">=".$this->objVal("bugun");

		$qCCC=$this->qry;
		$qCCC->close();

		$this->setWhere($qCCC,"atarih$tar1");
		$qCCC->open(null,null);

		$pdf=new TCPDF("L","mm","A4",0,"cp1254");
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDisplayMode("fullpage");
		$pdf->setleftmargin(25);
		$pdf->SetDrawColor(214);
		$pdf->AddPage();
		
		//$pdf->AddFont('Arial','','arial.php');
		//$pdf->AddFont('Arial','B','arialbd.php');
		
		$pdf->SetFont('Arial','',19);
		$pdf->setY(20);
		$pdf->Cell(170,5,'Toplantý Listesi');
		$pdf->SetFont('Arial','',9);
		$pdf->SetX(160);$pdf->Cell(30,5,"Tarih: ".date("d.m.Y H:i"),0,1);
		$pdf->ln(7);
		
		;

		$pdf->SetFillColor(224);
		$pdf->SetFont('Arial','I',7);
		$pdf->Cell(7,5,"",'LTB',0,'',1);
		$pdf->Cell(20,5,"Toplantý Tarihi",1,0,'',1);
		$pdf->Cell(50,5,"Toplantý",1,0,'',1);
		$pdf->Cell(10,5,"Baþla",1,0,'',1);
		$pdf->Cell(10,5,"Bitiþ",1,0,'',1);
		$pdf->Cell(70,5,"Açýkla",1,0,'',1);
		$pdf->Cell(7,5,"Kiþi",1,0,'',1);
		$pdf->Cell(40,5,"Yer",1,0,'',1);
		$pdf->Cell(20,5,"Sorumlu",1,1,'',1);
		$yy=$pdf->GetY();

		$pdf->SetFont('Arial','',7);
		$nn=0;
		$sum_miktar=0;
		while($qCCC->next()){
			$pdf->Cell(7,5,++$nn,1,0,'C');
			$pdf->Cell(20,5,strftime("%d.%m.%Y %a",strtotime($qCCC->rec_atarih)),1,0,'');
			$pdf->Cell(50,5,$qCCC->rec_exp,1,0);
			$pdf->Cell(10,5,$qCCC->rec_asaat,1,0);
			$pdf->Cell(10,5,$qCCC->rec_csaat,1,0);
			$pdf->Cell(70,5,$qCCC->rec_acikla,1,0);
			$pdf->Cell(7,5,$qCCC->rec_kadet,1,0,'C');
			$pdf->Cell(40,5,$qCCC->rec_toyer_exp,1,0);
			$pdf->Cell(20,5,$qCCC->rec_perso_exp,1,1);
		}

		$pdf->Output("Toplanti_$this->id.pdf","I");
	}
}
?>