<?php
class class_pratik_resimekle extends class_pratik{
	function formtam(){
		global $oUser, $oPerso;

		$this->qry->close();
		$this->qry->keyOpen($this->id);

		$RESIM=null;
		$tRes=$this->qry->derive_tab("resim:auto=1",-1);
		if(is_uploaded_file($_FILES["frm_dosya"]["tmp_name"])) $tRes->rec_dosya=file_get_contents($_FILES["frm_dosya"]["tmp_name"]);

		//echo $this->senaryo->parvalues,"<br/>";
		if (preg_match_all("/\s*\?(\w+)\s*=\s*((\\\$)?([\w-% ]+)(\.(\w+))?)\s*(,|$|\r|\n)/U",$this->senaryo->parvalues,$arr_match,PREG_SET_ORDER))
		foreach($arr_match as $match)
		if($match[1]=="RESIM" && empty($match[3])) $RESIM=$this->qry->fieldByName($match[4]);
		elseif($fld=$tRes->fieldByName($match[1])){
			if (is_null($fld)) continue;
			if (empty($match[3])){
				$strVal=$match[4];
				if (preg_match_all("/%(\w+)(\W|$)/U",$strVal,$flds,PREG_SET_ORDER))
				foreach ($flds as $name) if ($def=$this->qry->fieldByName($name[1]))
					$strVal=str_replace("%$name[1]",$def->value,$strVal);
				$fld->value=$strVal;
			}else{
				if (!is_null($val=$this->objVal($match[4],$match[6]))) $fld->value=$val;
			}
		}
		$tRes->insert();

		if($RESIM){
			$table=$RESIM->orgtable; $field=$RESIM->orgname;
			$qCCC=new clsApp($this->appLink, "update asist.$table set $field=?prm_resim where id=?prm_id");
			$qCCC->prm_resim=$tRes->rec_id;
			$qCCC->prm_id=$this->id;
			$qCCC->exec();
		}
		echo "</br></br>Eklendi/Güncellendi</br></br>";
		echo "<input class='tus' type='button' onClick='window.close()' value='Kapat'/>";
		if (isset($_GET["pic"])){
			$pic=$_GET["pic"];
			echo "<script>";
			echo "id_resim=window.opener.document.getElementById('$pic');";
			echo "if(id_resim){id_resim.src='?loadpic&id=$tRes->rec_id'; window.onload=function(){ window.close(); };}";
			echo "</script>";
		}
	}
}


?>