
<?php
class class_form_eposta_39 extends class_form{
	function add_islem(){
		$this->arrIslem["load"]="S";
	}
	function formload($id=null){
	global $REAL_P;
		if(!$this->foundKey())return;

		$this->qry->id=empty($id) ? (isset($_GET["id"])?$_GET["id"]:null) : $id;
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->qry->id);
		if(!$this->foundRec())return;

		
		$dosyad="\\\\wmg-s7\\mes$\\".substr($this->qry->rec_messagefilename,40);
		$dosya="{$this->qry->rec_alici}_{$this->qry->keyRec}.txt";

		include_once("$REAL_P/_mime/MimeMailParser.class.php");
		$Parser = new MimeMailParser();
		$Parser->setPath($dosyad);
		$to = $Parser->getHeader('to');
		$from = $Parser->getHeader('from');
		$subject = $Parser->getHeader('subject');
		$message = $Parser->getMessageBody('text');
		if(empty($message))$message=$Parser->getMessageBody('html');
		header("Content-Type: text/plain; charset='utf-8'");
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
		//echo $dosyad,"\n\n";
		echo "TO: $to\nFROM: $from\nSUBJECT: $subject\n\n$message";

		//echo "\n\n\n\nORJINAL MESSAGE:\n----------------------------------------------------\n";
		//readfile($dosyad);
	}
}
?>
