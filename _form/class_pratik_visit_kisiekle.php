<?php
class class_pratik_visit_kisiekle extends class_pratik{
	function formpra(){
		$this->qry->close();
		$this->qry->keyopen($this->id);

		$this->qry->rec_ckisi="";
		$this->qry->rec_asaat="";
		$this->qry->rec_csaat="";
		$this->qry->rec_kartno="";
		$this->form();
	}
	function formtam(){
		$this->qry->close();
		$this->qry->keyOpen($this->id);
		$this->qry->setUpdates($this->senaryo->updtables);

		$this->bindFields($this->qry,$this->senaryo->parvalues);
		if(!$this->qry->insert()){
			$this->msg=$this->qry->msg."<br>";
			$this->form();
			return;
		}
		$this->formMessage();
	}
}
?>
