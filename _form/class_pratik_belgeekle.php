
<?php
class class_pratik_belgeekle extends class_pratik{

	function formtam(){
		global $oUser, $oPerso;

		$this->qMain=new clsApp($this->appLink, $this->senaryo->sqlstr, true);
		$this->qMain->keyOpen($this->id);

		$BELGE=null;
		$tRes=$this->qry->derive_tab("belge:auto=1",-1);
		if (is_uploaded_file($_FILES["dosya"]["tmp_name"])) $tRes->rec_dosya=file_get_contents($_FILES["dosya"]["tmp_name"]);

		if (preg_match_all("/\s*\?(\w+)\s*=\s*((\\\$)?([\w-%]+)(\.(\w+))?)\s*(,|$|\r|\n)/U",$this->senaryo->parvalues,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match)
		if ($match[1]=="BELGE" && $match[4]=="main") $BELGE=$this->qMain->fieldByName($match[6]);
		elseif($fld=$tRes->fieldByName($match[1])){
			if (is_null($fld)) continue;
			if (empty($match[3])){
				$strVal=$match[4];
				if (preg_match_all("/%(\w+)(\W|$)/U",$strVal,$flds,PREG_SET_ORDER))
				foreach ($flds as $name) if ($def=$this->qMain->fieldByName($name[1]))
					$strVal=str_replace("%$name[1]",$def->value,$strVal);
				$fld->value=$strVal;
			}else{
				$strVal=$match[6];
				switch ($match[4]){
					case "ouser": $fld->value=$oUser->$strVal; break;
					case "operso":$fld->value=$oPerso->$strVal; break;
					case "main": if ($def=$this->qMain->fieldByName($strVal)) $fld->value=$def->value; break;
					case "form": if (isset($_POST["frm_$strVal"])) $fld->value=$_POST["frm_$strVal"]; break;
					case "bugun": $fld->value=date("Y-m-d"); break;
					case "busaat": $fld->value=date("H:i:s"); break;
				}
			}
		}
		$tRes->insert();
		if ($BELGE){
			$table=$BELGE->orgtable; $field=$BELGE->orgname;
			$qCCC=new clsApp($this->appLink, "update asist.$table set $field=?prm_Belge where id=?prm_Id");
			$qCCC->prm_Belge=$tRes->rec_id;
			$qCCC->prm_Id=$this->id;
			$qCCC->exec();
		}
		echo "<br/><br/>{$this->senaryo->exp}";
		echo "<br/><br/><input class='tus' type='button' onClick='window.close()' value='Kapat'/>";
		echo "<br/><br/>Güncellendi<br/>";
		echo "<br/><br/><input class='tus' type='button' onClick='window.close()' value='Kapat'/>";
	}
}
?>
