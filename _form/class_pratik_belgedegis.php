
<?php
class class_pratik_belgedegis extends class_pratik{

	function formtam(){
		$this->qMain=new clsApp($this->appLink, $this->senaryo->sqlstr, true);
		$this->qMain->keyOpen($this->id);

		$BELGE=null;
		$qCCC=new clsApp($this->appLink, "update asist.belge set dosya=?prm_Dosya where id=?prm_Id");
		if (is_uploaded_file($_FILES["dosya"]["tmp_name"])) $qCCC->prm_dosya=file_get_contents($_FILES["dosya"]["tmp_name"]);

		if (preg_match_all("/\s*\?(\w+)\s*=\s*((\\\$)?([\w-%]+)(\.(\w+))?)\s*(,|$|\r|\n)/U",$this->senaryo->parvalues,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match)
			if ($match[1]=="BELGE" && $match[4]=="main") $BELGE=$this->qMain->fieldByName($match[6]);
		if ($BELGE){
			$qCCC->prm_Id=$BELGE->value;
			$qCCC->exec();
		}

		echo "<br/><br/>{$this->senaryo->exp}<br/>";
		echo "<br/><input class='tus' type='button' onClick='window.close()' value='Kapat'/><br/><br/>";
		echo "<br/>Güncellendi<br/>";
		echo "<br/><input class='tus' type='button' onClick='window.close()' value='Kapat'/><br/>";
	}
}
?>
