<?php
class class_admin_sql extends class_form{
	function islem($islem=""){
		if(!empty($islem)) $this->islem=$islem;
		if(!$this->islemPerm()){
			echo "ACCESS DENIED";
			return false;
		}
		if($this->islem=="exc")$this->formexc();
		else parent::islem($islem="");
	}

	function islemPerm(){
	global $oUser;
		$ret=false;
		if($this->islem=="exc")$ret=$oUser->admin;
		else$ret=parent::islemPerm();
		return $ret;
	}

	function ins_upd_del_message(){
		if($this->mod) $this->modMessage();
		else{
			$url="{$this->senaryo->action}";
			$str="<meta http-equiv=\"refresh\" content=\"1000;url=?$url\"".
				 "<a href=\"?$url\">{$this->senaryo->exp}</a>";
			echo $str;
		}
	}
	function formexc($id=null){$this->formupd($id);}
}
?>