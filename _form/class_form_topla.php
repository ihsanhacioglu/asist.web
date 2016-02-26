<?php
class class_form_topla extends class_form{
    function afterDelete(){
		$cSqlstr="delete from usare where iliski=?prm_iliski";
		$qDel=$this->qry->derive_qry($cSqlstr);
        $qDel->prm_iliski="topla-".$this->qry->rec_id;
        $qDel->exec();
	}
    function recValid($QRY){
        global $oPerso;
		if(empty($this->qry->rec_perso))$this->qry->rec_perso=$oPerso->id;
		$this->qry->rec_asaat=strtr($this->qry->rec_asaat,".,","::");
		$this->qry->rec_csaat=strtr($this->qry->rec_csaat,".,","::");

		$asaat=$this->qry->rec_asaat;
		$csaat=$this->qry->rec_csaat;
		$tar1=date_create(date("d-m-Y")." $asaat");
		$tar2=date_create(date("d-m-Y")." $csaat");
		$asaat=date_format($tar1,"H:i");
		$csaat=date_format($tar2,"H:i");
		$this->qry->rec_asaat=$asaat;
		$this->qry->rec_csaat=$csaat;
		
		$cSqlstr="select id,exp,aanda,kadet from toyer where id=?prm_toyer";
		$qSel=$this->qry->derive_qry($cSqlstr);
        $qSel->prm_toyer=$this->qry->rec_toyer;
		$qSel->open();
		if($qSel->rec_aanda=="+")return true;
		if($qSel->rec_kadet<$this->qry->rec_kadet){
			$this->msg.="NICHT GESPEICHERT !<br><br>Toplantý odasý ez fazla {$qSel->rec_kadet} kiþi alabilir.";
			return false;
		}

		$cSqlstr="select id,exp,asaat,csaat,atarih from topla where atarih=?prm_atarih and toyer=?prm_toyer";
		$qSel=$this->qry->derive_qry($cSqlstr);
        $qSel->prm_atarih=$this->qry->rec_atarih;
        $qSel->prm_toyer=$this->qry->rec_toyer;
		$qSel->open(null,null);
		$mesgul=false;
		$msg="";
		while($qSel->next()){
			if($qSel->rec_id==$this->qry->rec_id)continue;

			if(($this->qry->rec_asaat>=$qSel->rec_asaat && $this->qry->rec_asaat<=$qSel->rec_csaat)||
			   ($this->qry->rec_csaat>=$qSel->rec_asaat && $this->qry->rec_csaat<=$qSel->rec_csaat)||
			   ($qSel->rec_asaat>=$this->qry->rec_asaat && $qSel->rec_asaat<=$this->qry->rec_csaat)||
			   ($qSel->rec_csaat>=$this->qry->rec_asaat && $qSel->rec_csaat<=$this->qry->rec_csaat)){
				$mesgul=true;
				$msg.="<br><b>$qSel->rec_asaat-$qSel->rec_csaat: $qSel->rec_exp</b>";
			}else$msg.="<br>$qSel->rec_asaat-$qSel->rec_csaat: $qSel->rec_exp";
		}
		if($mesgul){
			$this->msg.="NICHT GESPEICHERT !<br><br>Toplantý odasý {$this->qry->rec_asaat}-{$this->qry->rec_csaat} saatleri içinde meþgul.$msg";
			return false;
		}
		return true;
	}
	function afterInsert(){
        global $oUser;
        
		$cSqlstr="select users from usare where iliski=?prm_iliski";
		$qSel=$this->qry->derive_qry($cSqlstr);
		if(isset($_GET["cpyid"])){
			$id=$_GET["cpyid"];
			$id=empty($id) ? 0 : $id;
	        $qSel->prm_iliski="topla-$id";
		}else
			$qSel->prm_iliski="totur-".$this->qry->rec_totur;
        $qSel->open(null,null);

        $tUsare=$this->qry->derive_tab("usare:set=1",-1);
        $tUsare->rec_iliski = "topla-{$this->qry->rec_id}";
        $tUsare->rec_dsaat  = $this->objVal("saat");
        $tUsare->rec_abc    = "A";
        $tUsare->rec_payet  = "-";
		$sahip=$oUser->id;
        while($qSel->next()){
			if($oUser->id==$qSel->rec_users)$sahip=0;
			$tUsare->rec_users =$qSel->rec_users;
            $tUsare->insert();
        }
		if($sahip){
			$tUsare->rec_users =$sahip;
            $tUsare->insert();
		}
    }
	function formcpy(){
		$this->par=(isset($_GET["id"]) ? "&cpyid=".$_GET["id"] : null);
		parent::formcpy();
	}
}
?>
