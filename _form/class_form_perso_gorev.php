<?php
class class_form_perso_gorev extends class_form{
	function form(){
	global $oUser;

		if($this->islem=="edt"){
			$qCCC=$this->qry->derive_qry("update asist!gorev set okuma=?prm_okuma where id=?prm_id",$this->qry);
			if($this->qry->rec_aperso==$this->qry->rec_perso){ // kendi
				if($this->qry->rec_okuma!="++"){
					$okuma="++";
					$qCCC->prm_id=$this->qry->rec_id;
					$qCCC->prm_okuma=$okuma;
					$qCCC->exec();
					$this->qry->rec_okuma=$okuma;
				}
			}
			elseif($oUser->perso==$this->qry->rec_aperso){ // verilen
				$veoku=substr($this->qry->rec_okuma,0,1);
				if($veoku!="x" && $veoku!="+"){
					$aloku=substr($this->qry->rec_okuma,1,1);
					$okuma="+".$aloku;
					$qCCC->prm_id=$this->qry->rec_id;
					$qCCC->prm_okuma=$okuma;
					$qCCC->exec();
					$this->qry->rec_okuma=$okuma;
				}
			}
			elseif($oUser->perso==$this->qry->rec_perso){ // alýnan
				$aloku=substr($this->qry->rec_okuma,1,1);
				if($aloku!="x" && $aloku!="+"){
					$veoku=substr($this->qry->rec_okuma,0,1);
					$okuma=$veoku."+";
					$qCCC->prm_id=$this->qry->rec_id;
					$qCCC->prm_okuma=$okuma;
					$qCCC->exec();
					$this->qry->rec_okuma=$okuma;
				}
			}
		}
		parent::form();
	}
	function beforeInsert(){
	global $oUser;
		if($this->qry->rec_relata=="debas"){
			$this->qry->rec_acikla=str_replace("[{$this->qry->rec_relati}]","<a href='?form_debas&islem=edt&mod=&id={$this->qry->rec_relati}'>{$this->qry->rec_relati}</a>",$this->qry->rec_acikla);
		}
		$acikla="\n---------- ".date("d.m.Y H:i")." - $oUser->exp\n";
		$this->qry->rec_acikla=$acikla.$this->qry->rec_acikla;
	}
}
?>