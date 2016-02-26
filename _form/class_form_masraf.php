<?php
class class_form_masraf extends class_form{
    function afterDelete(){
		$sqlStr="delete from asist.masrafdty where masraf=?prm_masraf";
		$qDel=$this->qry->derive_qry($sqlStr);
		$qDel->prm_masraf=$this->qry->rec_id;
        $qDel->exec();
	}
}
?>
