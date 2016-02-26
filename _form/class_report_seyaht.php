<?php
class class_report_seyaht extends class_report{
	public $act="form_seyaht";

	function pdf(){
	global $REAL_P;
		$arrLoc=localeconv();
		$arrLoc["decimal_point"]=",";
		setlocale(LC_NUMERIC,$arrLoc);

		$pdf=new TCPDF("P","mm","A4",0,"cp1254");
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDisplayMode("fullpage");
		$pdf->setleftmargin(25);
		$pdf->SetDrawColor(214);
		$pdf->AddPage();


		$pdf->setY(20);
		$form_yil=date("Y",strtotime("{$this->qry->rec_atarih}"));

		$baslik="Reisekosten-Formular $form_yil (Nr.{$this->qry->rec_id})";
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(160,5,$baslik,0,1,'C');

		$REC=(object)array();
		$REC->fahrt=0;
		$REC->km=0;
		$REC->taxii=0;
		$REC->neben=0;
		$REC->otel1=0;
		$REC->otel2=0;
		$REC->otel3=0;
		$REC->gun_1=0;
		$REC->gun_2=0;
		$REC->gun_3=0;
		$REC->pausc=0;
		$REC->gun_p=0;
		$REC->verpf=0;
		$REC->ziele="";

		$fil1=tempnam("$REAL_P/pdftk","fld"); $hh=fopen("$fil1","w"); fclose($hh);
		$fil2=tempnam("$REAL_P/pdftk","fld"); $hh=fopen("$fil2","w"); fclose($hh);

		$this->yolListe($REC,$fil1);
		$REC->otell=$REC->otel1+$REC->otel2+$REC->otel3;
		$REC->abzug=$REC->fahrt+$REC->taxii+$REC->otell+$REC->neben+$REC->pausc+$REC->verpf;

		$pdf->ln(3);
		$yy=$pdf->gety();
		
		$pdf->SetFont('Arial','B',9);	$pdf->Cell(20,5,'Name',0,0);		$pdf->setX(45); $pdf->SetFont('Arial','',9); $pdf->Cell(30,5,$this->qry->rec_perso_exp,0,1);
		$pdf->SetFont('Arial','B',9);	$pdf->Cell(20,5,'Beginn ',0,0);		$pdf->setX(45); $pdf->SetFont('Arial','',9); $pdf->Cell(30,5,date("d.m.Y",strtotime("{$this->qry->rec_atarih}"))."  ".substr($this->qry->rec_asaat,0,5),0,1);
		$pdf->SetFont('Arial','B',9);	$pdf->Cell(20,5,'Ende ',0,0);		$pdf->setX(45); $pdf->SetFont('Arial','',9); $pdf->Cell(30,5,date("d.m.Y",strtotime("{$this->qry->rec_ctarih}"))."  ".substr($this->qry->rec_csaat,0,5),0,1);
		$pdf->SetFont('Arial','B',9);	$pdf->Cell(20,5,'Reiseziele',0,0);	$pdf->setX(45); $pdf->SetFont('Arial','',9); //$pdf->Cell(2,5,': ',0,0);
		$pdf->multiCell(140,5,$REC->ziele,0,'L');
		$pdf->ln(1);
		$y2=$pdf->gety();
		$pdf->rect($pdf->getx(),$yy,160,$y2-$yy);

		$pdf->ln(3);
		$pdf->SetFont('Arial','B',9);
		$yy=$pdf->gety();
		$pdf->Cell(20,5,'Anlass',0,1);
		$pdf->SetFont('Arial','',7);
		$pdf->multiCell(160,5,$this->qry->rec_gerekce,0,'L');
		$y2=$pdf->gety();
		$pdf->rect($pdf->getx(),$yy,160,$y2-$yy);

		//Fahrt
		$pdf->ln(3);
		$yy=$pdf->gety();
		$pdf->SetFont('Arial','B',9);
		$pdf->rect($pdf->getx(),$pdf->gety(),160,135);
		$pdf->Cell(110,5,'I. Fahrtkosten',0,1);
		$pdf->SetFont('Arial','',9);
		
		$pdf->Cell(20,5,'1. Pkw im Betriebsvermögen',0,0);
		$pdf->setX(140);$pdf->Cell(15,5,'–',0,1,'R');
		$pdf->Cell(20,5,'2. Privat-Pkw',0,0);

		$TAR_FRM=strtotime("{$this->qry->rec_atarih}");
		$TAR_Y13=strtotime("2013-03-01");
		$TAR_Y14=strtotime("2014-01-01");
		
		if($TAR_FRM<$TAR_Y13)$pdf->Cell(15,5,"($REC->km km x 0,2€/km)",0,0);
		else$pdf->Cell(15,5,"($REC->km km x 0,25€/km)",0,0);

		$pdf->setX(140);$pdf->Cell(15,5,number_format($REC->fahrt,2,',','.').'€',0,1,'R');
		
		$pdf->Cell(20,5,'3. Öffentliche Verkehrsmittel, Taxi (lt. Belegen)',0,0);
		$pdf->setX(140);$pdf->Cell(15,5,number_format($REC->taxii,2,',','.').'€',0,1,'R');


		//Verpflegung 08,14,24
		$pdf->ln(3);
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(110,5,'II. Verpflegungsmehraufwand',0,0);
		$pdf->SetFont('Arial','',9);
		$pdf->setX(140);$pdf->Cell(15,5,number_format($REC->verpf,2,',','.').'€',0,1,'R');


		//Übernachtung
		$pdf->ln(3);
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(110,5,'III. Übernachtungskosten',0,1);
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(100,5,'1.Tatsächliche Kosten',0,0);
		$pdf->setX(140);$pdf->Cell(15,5,number_format($REC->otell,2,',','.').'€',0,1,'R');
		$strAcik=
"  – bei Auslandsreisen ggf. Kürzung eines Gesamtpreises für Unterkunft und Verpflegung um 20% des länderspezifischen Verpflegungspauschbetrags für Frühstück und jeweils 40% für Mittag- und Abendessen
  – bei Inlandsreisen ohne Frühstück, ggf. pauschale Kürzung eines Sammelpostens (für Leistungen mit 19% USt.) um 4,80 €**\n";
		$pdf->SetFont('Arial','',7);
		$pdf->multiCell(85,5,$strAcik,0,'J');
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(100,5,'2. Pauschale (abhängig vom Reiseland)',0,0);
		$pdf->setX(140);$pdf->Cell(15,5,number_format($REC->pausc,2,',','.').'€',0,1,'R');
		$strAcik="  – nur bei Auslagenersatz durch Arbeitgeber";
		$pdf->SetFont('Arial','',7);
		$pdf->multiCell(85,5,$strAcik,0,'L');


		//Neben
		$pdf->ln(5);
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(110,5,'IV. Reise-Nebenkosten',0,0);
		$pdf->SetFont('Arial','',9);
		$pdf->setX(140);$pdf->Cell(15,5,number_format($REC->neben,2,',','.').'€',0,1,'R');
		$strAcik=
"  – Tatsächliche Kosten (ggf. Eigenbeleg) z.B. für Telekommunikation,  Porto, Trinkgelder, Parkplatz, Gepäckbeförderung und -aufbewahrung, Straßenbenutzung, Schadensersatzleistungen infolge von Verkehrsunfälle.\n";
		$pdf->SetFont('Arial','',7);
		$pdf->multiCell(85,5,$strAcik,0,'J');


		//Abzug
		$pdf->ln(5);
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(110,5,'Abzugsfähige Reisekosten',0,0);
		$pdf->SetFont('Arial','',9);
		$pdf->setX(140);$pdf->Cell(15,5,number_format($REC->abzug,2,',','.').'€',0,1,'R');
		$pdf->SetFont('Arial','',7);
		$pdf->multiCell(85,5,'  – ggf. abzügl. steuerfreie Erstattungen',0,'L');
		$pdf->ln(5);
		$strAcik=
"  – Unternehmer können bei Reisen im Ausland unter bestimmten Voraussetzungen die dort gezahlte Umsatzsteuer (Mehrwertsteuer - MwSt) im Rahmen eines besonderen Vergütungsverfahrens erstattet bekommen. Allerdings werden die Vorsteuern aus Reisekosten, Bewirtungen und Pkw-Kosten in einigen Ländern nicht oder nur beschränkt erstattet.
  – Unternehmer können bei Inlandsreisen im Zusammenhang mit ihrer unternehmerischen Tätigkeit die in einer Rechnung gesondert ausgewiesene Umsatzsteuer als Vorsteuer abziehen. Ein pauschaler Vorsteuerabzug aus Reisekosten- und Kilometerpauschalen ist nicht möglich.
  ** Vergleiche BMF-Schreiben vom 05.03.2010, Az: IV D 2 - S 7210/07/10003; IV C 5 - S 2353/09/10008 (Abruf-Nr. st 28638)\n";
		$pdf->SetFont('Arial','',7);
		$pdf->multiCell(140,30,$strAcik,0,'J');



		$pdf->SetFont('Arial','',9);
		$pdf->SetY(260);
		$pdf->Cell(20,5,date("d.m.Y",strtotime("{$this->qry->rec_ktarih}")));
		$pdf->SetY(265);
		$pdf->rect($pdf->getx(),$pdf->gety(),160,0.1);

		$pdf->Cell(20,5,'Datum');
		$pdf->SetX(70);
		$pdf->Cell(50,5,"Unterschrift Personal");
		$pdf->SetX(140);
		$pdf->Cell(50,5,"Unterschrift Vorgesetzten");

		$pdf->Output("$fil2","F");
		$pdf_name=$this->ChrtranEng(trim($this->qry->rec_perso_exp));

		header("Content-Type: application/pdf");
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header("Content-Disposition: inline; filename=\"Seyahat_{$this->qry->rec_id}_$pdf_name.pdf\";");
		passthru("$REAL_P/pdftk/pdftk.exe A=$fil2 B=$fil1 cat A B output -");

		unlink($fil1);
		unlink($fil2);
	}
	function yolListe(&$REC,$fil1){
		$pdf=new TCPDF("P","mm","A4",0,"cp1254");
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetDisplayMode("fullpage");
		$pdf->setleftmargin(25);
		$pdf->SetDrawColor(214);
		$pdf->AddPage();

		$pdf->SetFont('Arial','B',16);
		$pdf->setY(20);
		$pdf->Cell(160,5,"Reisekostenabrechnung (Nr.{$this->qry->rec_id})",0,1,'C');
		$pdf->ln(3);

		$sqlStr="select if(tur='fahrt',1,
						if(tur='taxii',2,
						if(tur='otel1',3,
						if(tur='otel2',4,
						if(tur='otel3',5,
						if(tur='pausc',6,
						if(tur='neben',7,8))))))) sira,
						if(tur='fahrt','I.',
						if(tur='taxii','I.',
						if(tur='otel1','III.',
						if(tur='otel2','III.',
						if(tur='otel3','III.',
						if(tur='pausc','III.',
						if(tur='neben','IV.',''))))))) bas,
					D.*,pausc
				from asist.seyahtdty D left join asist.s_veraus V on (D.veraus=V.id)
				where seyaht=?prm_seyaht and tur<>'zone' order by 1,D.tarih,D.saat";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_seyaht=$this->qry->rec_id;
		$qCCC->open(null,null);

		$pdf->SetFont('Arial','B',9);
		$yy=$pdf->GetY();
		$sum_miktar=0;
		$nn=0;

		$pdf->Cell(7,5,"",1,0,'C');
		$pdf->Cell(20,5,"Datum",1,0,'C');
		$pdf->multiCell(115,5,"Erläuterung",1,'',0,0,'','',1,0,0,0,5);
		$pdf->Cell(18,5,"Betrag",1,1,'R');

		$TAR_FRM=strtotime("{$this->qry->rec_atarih}");
		$TAR_Y13=strtotime("2013-03-01");
		$TAR_Y14=strtotime("2014-01-01");

		$pdf->SetFont('Arial','',9);
		while($qCCC->next()){
			$miktar=$qCCC->rec_miktar*1;
			$acikla="";
			if($qCCC->rec_tur=="fahrt"){
				$REC->fahrt+=$miktar;
				$REC->km+=$qCCC->rec_km;
				if($TAR_FRM<$TAR_Y13)$acikla.="Fahrtkosten ({$qCCC->rec_km}km x 0,2€/km)";
				else$acikla.="Fahrtkosten ({$qCCC->rec_km}km x 0,25€/km)";
			}elseif($qCCC->rec_tur=="taxii"){
				$REC->taxii+=$miktar;
				$acikla.="Öffentliche Verkehrsmittel";
			}elseif($qCCC->rec_tur=="neben"){
				$REC->neben+=$miktar;
				$acikla.="Nebenkosten";
			}elseif($qCCC->rec_tur=="otel1"){
				$REC->gun_1+=$qCCC->rec_gun;
				$REC->otel1+=$miktar;
				$acikla.="Hotel ohne Frühstück";
			}elseif($qCCC->rec_tur=="otel2"){
				$REC->gun_2+=$qCCC->rec_gun;
				$miktar2=$miktar;
				$acikla.="Hotel mit Frühstück";
				if($qCCC->rec_veraus==-1){
					$miktar-=$qCCC->rec_gun*4.8;
					$acikla.=" ($miktar2-{$qCCC->rec_gun}x4,8€)";
				}else{
					$miktar*=0.8;
					$acikla.=" ($miktar2-%20)";
				}
				$REC->otel2+=$miktar;
			}elseif($qCCC->rec_tur=="otel3"){
				$REC->gun_3+=$qCCC->rec_gun;
				$miktar2=$miktar;
				$acikla.="Hotel mit Frühstück und Abendessen";
				if($qCCC->rec_veraus==-1){
					$miktar-=$qCCC->rec_gun*9.6;
					$acikla.=" ($miktar2-{$qCCC->rec_gun}x9,6€)";
				}else{
					$miktar*=0.6;
					$acikla.=" ($miktar2-%40)";
				}
				$REC->otel3+=$miktar;
			}elseif($qCCC->rec_tur=="pausc"){
				$REC->gun_p+=$qCCC->rec_gun;
				$REC->pausc+=$qCCC->rec_gun*$qCCC->rec_pausc;
				$acikla.="Pauschale ({$qCCC->rec_gun}x{$qCCC->rec_pausc}€)";
			}
			$sum_miktar+=$miktar;
			if(!empty($qCCC->rec_acikla))$acikla=$qCCC->rec_acikla;

			$pdf->Cell(7,5,++$nn,1,0,'C');
			$pdf->Cell(20,5,date("d.m.Y",strtotime("$qCCC->rec_tarih")),1,0,'C');
			$pdf->multiCell(115,5,$acikla,1,'',0,0,'','',1,0,0,0,5);
			$pdf->Cell(18,5,number_format($miktar,2,',','.')."€",1,1,'R');
		}

		$R0=null;
		$RN=null;
		$sqlStr="select seyaht.id,atarih tarih,asaat saat,yer1 yer,
						V.id v_id,V.exp v_exp,v08,v14,v24
				from asist.seyaht, asist.s_veraus V
				where seyaht.id=?prm_seyaht
					and seyaht.ver0=V.id";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_seyaht=$this->qry->rec_id;
		$qCCC->open();
		if($qCCC->reccount)$R0=$qCCC->getFldVals();

		$sqlStr="select seyaht.id,ctarih tarih,csaat saat,yer3 yer,
						V.id v_id,V.exp v_exp,v08,v14,v24
				from asist.seyaht, asist.s_veraus V
				where seyaht.id=?prm_seyaht
					and seyaht.ver0=V.id";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_seyaht=$this->qry->rec_id;
		$qCCC->open();
		if($qCCC->reccount)$RN=$qCCC->getFldVals();


		$arrYol=array();
		if(!is_null($R0))$arrYol[]=$R0;
		$sqlStr="select D.id,D.tarih,D.saat,D.acikla yer,
						V.id v_id,V.exp v_exp,v08,v14,v24
				from asist.seyahtdty D, asist.s_veraus V
				where D.veraus=V.id and D.seyaht=?prm_seyaht and D.tur='zone'
				order by D.tarih,D.saat";
		$qCCC=$this->qry->derive_qry($sqlStr);
		$qCCC->prm_seyaht=$this->qry->rec_id;
		$qCCC->open(null,null);
		while($qCCC->next())$arrYol[]=$qCCC->getFldVals();
		if(!is_null($RN))$arrYol[]=$RN;

		$arrSur=array();
		foreach($arrYol as $key=>$R2){
			$REC->ziele.=', '.$R2->yer;
			$D2=date_create("$R2->tarih $R2->saat");
			$D2_00=date_create("$R2->tarih 00:00");
			if($key==0){
				$R1=$R2;
				$D1=$D2;
				continue;
			}
			$D1_24=date_create(date_format($D1,"Y-m-d")." 24:00");
			$dif=date_diff($D1_24,$D2_00);
			if($dif->invert)$this->yolSure($D1,$D2,$arrSur,$R1);
			else{
				$this->yolSure($D1,$D1_24,		$arrSur,$R1);
				$this->yolSure($D1_24,$D2_00,	$arrSur,$R1);
				$this->yolSure($D2_00,$D2,		$arrSur,$R1);
			}
			$R1=$R2;
			$D1=$D2;
		}
		$REC->ziele=substr($REC->ziele,2);

		if($this->qry->rec_sirket!=-103 && $this->qry->rec_harcirah=="var"){
			$arr_g2=array();
			$tar1="";
			$tar2="";
			$kk=0;
			foreach($arrSur as $tar=>$arr_a1){
				$kk++;
				$max_a1=null;
				$sum_a1=0;
				foreach($arr_a1 as $a1){
					$sum_a1+=$a1->sur;
					if(is_null($max_a1))$max_a1=$a1;
					if($a1->sur>$max_a1->sur)$max_a1=$a1;
				}
				$max_a1->top=$sum_a1;
				$arr_g2[]=$max_a1;
				if($kk==1)$tar1=$tar;
				if($kk==2)$tar2=$tar;
			}
			if($TAR_FRM<$TAR_Y14){
				if(count($arr_g2)==2 && $arr_g2[0]->top<8 && $arr_g2[1]->top<8){
					$arr_g2[0]->top+=$arr_g2[1]->top;
					$arr_g2[1]->top=0;
					foreach($arrSur[$tar2] as $a1){
						$a1->g2=1;
						$arrSur[$tar1][]=$a1;
					}
					unset($arrSur[$tar2]);
				}
			}

			$pdf->Cell(7,5,++$nn,'LR',0,'C');
			$pdf->Cell(153,5,"Verpflegungsmehraufwand",'LR',1);
			$pdf->Cell(7,4,'','LR',0);
			$pdf->SetFont('Arial','',7);
			$pdf->Cell(153,4,$REC->ziele,'LRB',1);
			foreach($arrSur as $tar=>$arr_a1){
				$a2=null;
				$miktar=0;
				$aa=0;
				$pdf->SetFont('Arial','',8);
				foreach($arr_a1 as $a1){
					if($a1->top>0)$a2=$a1;
					$acikla=($a1->sur>=24||$a1->g2?"$a1->sa1 - $a1->sa2($a1->ta2) ":"$a1->sa1 - $a1->sa2 ").$a1->r1->v_exp.($a1->top>0?"(**{$a1->sur}St.)":"({$a1->sur}St.)");
					$pdf->Cell(7,4,'','LR');
					$pdf->Cell(20,4,'','LR');
					$pdf->Cell(115,4,$acikla,'R',0);
					$pdf->Cell(18,4,'','LR',1);
				}
				if(is_null($a2))continue;

				$pdf->SetFont('Arial','',9);
				$acikla=$a2->r1->v_exp." ";
				if($TAR_FRM<$TAR_Y14){
					if($a2->top<8){
						$miktar=0;
						$acikla.="({$a2->top}St. 0(Null)€)";
					}elseif($a2->top>=8 && $a2->top<14){
						$miktar=$a2->r1->v08;
						$acikla.="({$a2->top}St. {$a2->r1->v08}€)";
					}elseif($a2->top>=14 && $a2->top<24){
						$miktar=$a2->r1->v14;
						$acikla.="({$a2->top}St. {$a2->r1->v14}€)";
					}elseif($a2->top>=24){
						$miktar=floor($a2->top/24)*$a2->r1->v24;
						$acikla.="(".floor($a2->top/24)."Tage x{$a2->r1->v24}€)";
					}
				}else{
					if(count($arrSur)==1){
						if($a2->top<8){
							$miktar=0;
							$acikla.="({$a2->top}St. 0(Null)€)";
						}else{
							$miktar=$a2->r1->v14;
							$acikla.="({$a2->top}St. {$a2->r1->v14}€)";
						}
					}elseif($a2->top<24){
						$miktar=$a2->r1->v14;
						$acikla.="({$a2->top}St. {$a2->r1->v14}€)";
					}elseif($a2->top>=24){
						$miktar=floor($a2->top/24)*$a2->r1->v24;
						$acikla.="(".floor($a2->top/24)."Tage x{$a2->r1->v24}€)";
					}
				}

				$pdf->Cell(7,5,'','LR',0,'C');
				$pdf->Cell(20,5,date("d.m.Y",strtotime($tar)),'LRB',0,'C');
				$pdf->Cell(115,5,$acikla,'BRB',0);
				$pdf->Cell(18,5,number_format($miktar,2,',','.')."€",'RB',1,'R');
				$sum_miktar+=$miktar;
				$REC->verpf+=$miktar;
			}
		}
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(142,5,'Abzugsfähige Reisekosten ','LTB',0,'R');
		$pdf->Cell(18,5,number_format($sum_miktar,2,',','.')."€",1,1,'R');

		$pdf->Output("$fil1","F");
		
	}

	function yolSure($t1,$t2,&$arr,$r1){
		$dif=date_diff($t1,$t2);
		if($dif->invert)return;
		
		$sur=$dif->d*24+$dif->h+round(($dif->i*60+$dif->s)/3600,2);
		$a1=(object)array("sa1"=>date_format($t1,"H:i"),
						  "ta2"=>date_format($t2,"d.m.Y"),
						  "sa2"=>date_format($t2,"H:i"),
						  "sur"=>$sur,
						  "top"=>0,
						  "g2"=>0,
						  "r1"=>$r1);
		$arr[date_format($t1,"Y-m-d")][]=$a1;
		//$arr[date_format($t1,"Y-m-d")][$r1->v_id]=$a1;
	}
}
?>