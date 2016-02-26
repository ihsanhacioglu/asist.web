<?php
class class_util_gorev{
	function getdurum($nDurum, $dAtarih, $dCTarih){
		$dBugun = date_create(date("Y-m-d")." 00:00");
		$dAtarih= date_create("$dAtarih 00:00");
		$dCTarih= date_create("$dCTarih 00:00");
		
		if($dAtarih > $dCTarih) return $nDurum;

		// 0. Yeni Görev, ctarih, $dBugun-7 den kücük ise, 5. Gecikmis görev cevrilir.
		if($nDurum==-5100){
			$dTar7=date_create(date_format($dBugun,"Y-m-d")." -7 day");
			if($dCTarih < $dTar7)			$nDurum=-5105;
			return $nDurum;
		}

		// echo "DUrum=$nDurum  dAtarih=",date_format($dAtarih,"Y-m-d"),"  ","dCTarih=",date_format($dCTarih,"Y-m-d"),"<br>";
		// 0. Yeni Görev, ctarih, dBugun-7 den kücük ise, 5. Gecikmis görev cevrilir.
		// 1. Aktif Görev, ctarih, dBugun den kücük ise, 5. Gecikmis görev cevrilir.
		// 2. Gelecek Görevler, ctarih, dBugun den kücük ise, 5. Gecikmis görev cevrilir.
		// 5. Geciken Görevlerde atarih, bugunden eşit veya kücük ise 1. Aktif göreve, atarih bugunden büyük ise 2. Gelecek Görev e
		// ctarih < date()-90  (Bir yıldan eski tüm gecikmis görevler otomatik iptal edilecek.)
				
		if($nDurum==-5101 || $nDurum==-5102 || $nDurum==-5105){
			$dTar90=date_create(date_format($dBugun,"Y-m-d")." -90 day");
			if($dAtarih<=$dBugun){
				    if($dCTarih< $dTar90)	$nDurum=-5106;
				elseif($dCTarih>=$dBugun)	$nDurum=-5101;
				else						$nDurum=-5105;
			}else
				$nDurum = -5102;
			return $nDurum;
		}

		// 3. Tamamlanan Görev, kullanıcı taradfından onaylanmadığı takdirde otomatik olarak sistem tarafından ctarihten 45 gün sonra onaylanır.
		// Parametre sayısını artırmamak icin süre aşımı için dtarih yerine ctarih kullanımı tecih edilmiştir.
		
		$dTar45=date_create(date_format($dBugun,"Y-m-d")." -45 day");
		if($nDurum==-5103)					return $dCTarih < $dTar45 ? -5104 : -5103;

		if($nDurum==-5104 || $nDurum==-5106)return $nDurum;

		return $nDurum;
	}
}
?>