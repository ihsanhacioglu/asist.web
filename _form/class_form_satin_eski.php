<?php
class class_form_satin_eski extends class_form{
    function afterDelete(){
		$sqlStr="delete from asist.satindty where satin=?prm_satin";
		$qDel=$this->qry->derive_qry($sqlStr);
		$qDel->prm_satin=$this->qry->rec_id;
        $qDel->exec();
	}
}
?>
