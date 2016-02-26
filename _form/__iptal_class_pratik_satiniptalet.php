<?php

class class_pratik_satiniptalet extends class_pratik{
	function formtam(){
		$this->qry->close();
		$this->qry->keyOpen($this->id);

		$strSql="delete from asist.onay where iliski=?prm_iliski";
        $qCCC = new clsApp($this->appLink, $strSql);
        $qCCC->prm_iliski="satin-{$this->qry->rec_id}";
        $qCCC->exec();

		$strSql="update asist.belge set abc='X' where iliski=?prm_iliski and exp='satin_no_{$this->qry->rec_id}.pdf'";
        $qCCC = new clsApp($this->appLink, $strSql);
        $qCCC->prm_iliski="satin-{$this->qry->rec_id}";
        $qCCC->exec();
		
        $strSql="update asist.satin set durum=?prm_durum, dtarih=?prm_dtarih, dsaat=?prm_dsaat where id=?prm_id";
        $qCCC = new clsApp($this->appLink, $strSql);
        $qCCC->prm_id=$this->qry->rec_id;
        $qCCC->prm_durum = -21604; // Ýptal
        $qCCC->prm_dtarih= $this->objVal("ozaman","bugun");
        $qCCC->prm_dsaat = $this->objVal("ozaman","busaat");
		$qCCC->exec();

		$this->strMessage="Talep iptal edildi";
		$this->formMessage();
	}
}
?>
