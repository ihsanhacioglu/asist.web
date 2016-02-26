<?php

class class_pratik_onayla_vfp extends class_pratik
{
    function form()
    {
        //$this->par="&par_durum=-21503";
        $nRelati = 0;

        if (preg_match("/(.+)-(.+)/", $this->qry->rec_iliski, $match)) {
            $nRelati = $match[2];
        }

        $qDilek = $this->qry->derive_qry("select kimlik, ditur, sirket from asist!dilek where id=?prm_id");
        $qDilek->prm_id = $nRelati;
        $qDilek->open();

        if ($qDilek->reccount == 0 || $qDilek->rec_ditur != -3) {
            $this->formtam();
            return;
        }

        $qPerso = $this->qry->derive_qry("select id perso_id, exp perso_exp from asist!perso where kimlik=?prm_kimlik and sirket=?prm_sirket order by id desc");
        $qPerso->prm_kimlik = $qDilek->rec_kimlik;
        $qPerso->prm_sirket = $qDilek->rec_sirket;
        $qPerso->open();

        $cPerso = $qPerso->rec_perso_exp;
        $nPerso = $qPerso->rec_perso_id;

        $nYil = date("Y");

        $qIzin = $this->qry->derive_qry("select gorta, bakiye from asist!izin where perso=?prm_perso and yil=?prm_yil");
        $qIzin->prm_perso = $nPerso;
        $qIzin->prm_yil = $nYil;
        $qIzin->open();

        $this->msg .= "<br>$cPerso Resturlaub: ";

        if ($qIzin->reccount > 0) {
            if ($qIzin->rec_gorta == 0)
                $this->strMessage .= " Unberechnet";
            else {
                $REST = round($qIzin->rec_bakiye / $qIzin->rec_gorta, 2);
                $this->msg .= number_format($REST, 2, ',', '.') . " Tage";
            }
        } else {
            $this->msg .= " Kein Urlaubsanspruchseintrag gefunden";
        }

        $this->msg .= "<br><br>";
        parent::form();
    }


    // Tamam tuşuna basıldıktan sonra

    function formtam()
    {
        $this->qry->close();
        $this->qry->keyOpen($this->id);

        $qCCC = $this->qry->derive_qry($this->senaryo->pratik_sqlstr, $this->qry);
        $qCCC->senaryo = $this->senaryo->id;

        $this->bindParams($qCCC, $this->senaryo->parvalues);

        if (($par = $qCCC->paramByName("durum"))) {
            $_nDurum = null;

            if (isset($_GET["par_durum"])) {
                $_nDurum = $_GET["par_durum"];
            }

            if (is_numeric($_nDurum) && strpos(",,-21501,-21503,-21504,-21506,-21507,-21508,", ",$_nDurum,")) {
                $par->value = $_nDurum;
            }
        }

        if (!$this->qryExec($qCCC)) {
            $this->formMessage();
            return;
        }

        $sqlStr = "select iliski,
						count(*) adet,
						sum(iif(durum = -21501, 1, 0)) onaylanacak,
						sum(iif(durum = -21502, 1, 0)) islenecek,
						sum(iif(durum = -21503, 1, 0)) onaylandi,
						sum(iif(durum = -21504, 1, 0)) duzeltilecek,
						sum(iif(durum = -21505, 1, 0)) islendi,
						sum(iif(durum = -21506, 1, 0)) kabuledilmedi,
						sum(iif(durum = -21507, 1, 0)) beklemede,
						sum(iif(durum = -21508, 1, 0)) gecersiz
				   from asist!onay where iliski=?prm_iliski
				  group by 1";


        $qCCC = $this->qry->derive_qry($sqlStr);
        $qCCC->prm_iliski = $this->qry->rec_iliski;
        $qCCC->open();

        echo "<br><br>onaylanacak	$qCCC->rec_onaylanacak
				<br>islenecek		$qCCC->rec_islenecek
				<br>onaylandi		$qCCC->rec_onaylandi
				<br>duzeltilecek	$qCCC->rec_duzeltilecek
				<br>islendi			$qCCC->rec_islendi
				<br>kabuledilmedi	$qCCC->rec_kabuledilmedi
				<br>beklemede		$qCCC->rec_beklemede
				<br>gecersiz		$qCCC->rec_gecersiz";


        // dilek tablosu güncelleniyor.

        $cRelata = "";
        $nRelati = 0;
        $nDurum = -1;

        if (preg_match("/(.+)-(.+)/", $this->qry->rec_iliski, $match)) {
            $cRelata = $match[1];
            $nRelati = $match[2];
        }

        if ($cRelata != "dilek") {
            $this->formMessage();
            return;
        }

        if ($qCCC->rec_kabuledilmedi > 0) $nDurum = -21506;
        elseif ($qCCC->rec_duzeltilecek > 0) $nDurum = -21504;
        elseif ($qCCC->rec_onaylanacak > 0) $nDurum = -21501;
        else                                $nDurum = -21502;

        $cSqlStr = "update asist!dilek
				       set durum  = ?prm_durum:I,
				  	       dtarih = ?prm_dtarih:D,
					       dsaat  = ?prm_dsaat
				     where id = ?prm_id:I
				       and durum = -21501";

        $qDilek = $this->qry->derive_qry($cSqlStr);
        $qDilek->prm_durum  = $nDurum;
        $qDilek->prm_dtarih = $this->objVal("ozaman", "bugun");
        $qDilek->prm_dsaat  = $this->objVal("ozaman", "busaat");
        $qDilek->prm_id     = $nRelati;
        $qDilek->exec();
        $this->formMessage();
    }
}

?>
