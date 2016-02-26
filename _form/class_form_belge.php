<?php
class class_form_belge extends class_form{
	function updateBelge(){
		$belge_id=$this->qry->rec_id;

		$strDosya="";
		if(is_uploaded_file($_FILES["dosya"]["tmp_name"])) $strDosya=file_get_contents($_FILES["dosya"]["tmp_name"]);
		$strDosya=mysqli_real_escape_string($this->appLink,$strDosya);
		$ktarih=time();
		if ($this->islem=="upd")
		$strSql="update asist.arsiv set dosya='$strDosya' where id=$belge_id";
		else
		$strSql="insert into asist.arsiv (id,ktarih,dosya) values ($belge_id,FROM_UNIXTIME($ktarih),'$strDosya')";
		mysqli_query($this->appLink,$strSql);
	}

	function afterPost(){
		$this->updateBelge();
	}
	function add_islem(){
		$this->arrIslem["load"]="S";
	}
	function formload($id=null){
		if(!$this->foundKey())return;

		$this->qry->id=empty($id) ? (isset($_GET["id"])?$_GET["id"]:null) : $id;
		$this->qry->close();
		
		if(isset($_GET["itic"])){
			$itic=$_GET["itic"];
			if(!$this->existsItic("load",$itic)){
				$this->strMessage="ID ticket does not exist";
				$str="<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"".
					 "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
				$this->usrMessage($str);
				return;
			}
			//$this->dropItic($itic);
			if(!preg_match("/sen_{$this->senaryo->id}_load_(-?\d+):\d+_\d+/",$itic,$match))return;
			$this->qry->id=$match[1];
		}elseif(isset($_GET["keyid"])){
			$get_keyid=$_GET["keyid"];
			if(!preg_match("/^(\d+),(.+)$/",$get_keyid,$match)){
				$this->strMessage="KEYID false";
				$str="<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"".
					 "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
				$this->usrMessage($str);
				return;
			}
			$this->qry->id=$match[1];
			$keyid=$match[2];
		}else{
			$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		}
		$this->qry->keyOpen($this->qry->id);
		if(isset($_GET["keyid"]))if(empty($keyid) || $keyid!=$this->qry->rec_keyid){
			$this->strMessage="KEYID not found";
			$str="<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"".
				 "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
			$this->usrMessage($str);
			return;
		}
		if(!$this->foundRec())return;

		$belge=trim($this->qry->rec_arsivdir); // "\\World\asist.belge$\WORLD";
		$dosyad=str_replace("\\\\\$belge",$belge,$this->qry->rec_dosyad);
		$dosya=basename($dosyad);

		$this->content_type($dosya);
		readfile($dosyad);
	}
	function formload2($id=null){
		if(!$this->foundKey())return;

		$this->qry->id=empty($id) ? (isset($_GET["id"])?$_GET["id"]:null) : $id;
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->qry->id);
		if(!$this->foundRec())return;

		$belge=trim($this->qry->rec_arsivdir); // "\\World\asist.belge$\WORLD";
		$dosyad=str_replace("\\\\\$belge",$belge,$this->qry->rec_dosyad);
		$dosya=basename($dosyad);

		$this->content_type($dosya);
		readfile($dosyad);
	}

	function content_type($dosya){
		$ret="";
			if(preg_match("/.+\.pdf$/i",$dosya)) $ret="application/pdf";
		elseif(preg_match("/.+\.doc$/i",$dosya)) $ret="application/msword";
		elseif(preg_match("/.+\.xls$/i",$dosya)) $ret="application/vnd.ms-excel";
		elseif(preg_match("/.+\.ppt$/i",$dosya)) $ret="application/vnd.ms-powerpoint";
		elseif(preg_match("/.+\.docx$/i",$dosya))$ret="application/vnd.openxmlformats-officedocument.wordprocessingml.document";
		elseif(preg_match("/.+\.xlsx$/i",$dosya))$ret="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
		elseif(preg_match("/.+\.pptx$/i",$dosya))$ret="application/vnd.openxmlformats-officedocument.presentationml.presentation";
		elseif(preg_match("/.+\.(gif|jpg|jpeg|png|tiff)$/i",$dosya))$ret="image/jpeg";
		elseif(preg_match("/.+\.(txt|csv)$/i",$dosya))$ret="text/plain";
		elseif(preg_match("/.+\.html$/i",$dosya))$ret="text/html";
		elseif(preg_match("/.+\.xml$/i",$dosya))$ret="text/xml";
		
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		$brw_name=$this->ChrtranEng($dosya);
		if(empty($ret)){
			header("Content-Type: application/x-download");
			header("Content-Disposition: attachment; filename=\"$brw_name\"");
		}else{
			header("Content-Type: $ret");
			header("Content-Disposition: inline; filename=\"$brw_name\"");
		}
	}
}
?>