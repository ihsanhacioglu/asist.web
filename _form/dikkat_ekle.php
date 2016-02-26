<?php
	$cKimlik_exp="";
	$nKimlik_id=0;
	$cSebep_exp="";
	$nSebep_id=0;
	$cAcikla = "";
    if (isset($_POST["kimlik_exp_id"]))
    {
		$cKimlik_exp=$_POST["kimlik_exp"];
		$nKimlik_id=$_POST["kimlik_exp_id"];
		$cSebep_exp=$_POST["sebep_exp"];
		$nSebep_id=$_POST["sebep_exp_id"];
		$cAcikla = $_POST["acikla"];
		include_once("_class/dataclass.php");
		$tab=$this->qry->derive_tab("dikkat:auto=1",-1);
		$tab->fld_id->value=15;
		$tab->autoInc=true;
		$tab->fld_ktarih->value=date("Y-m-d",time());
		$tab->fld_kimlik->value=$nKimlik_id;
		$tab->fld_sebep->value=$nSebep_id;
		$tab->fld_acikla->value=$cAcikla;
		$tab->insert();
		echo "$cKimlik_exp<br/>$nKimlik_id<br/>$cSebep_exp<br/>$cAcikla<br/>eklendi";
	}

	echo "<form name=formdikkat action='?dikkatekle' method='post'>\n<br/>";
	echo "Kimlik($nKimlik_id): <input name='kimlik_exp' type='text' value='$cKimlik_exp' onkeyup=\"ajax_showOptions(this,'oner,kimliklist',event)\">\n<br/><br/>";
	echo "<input name='kimlik_exp_id' id='kimlik_exp_id' value='$nKimlik_id' type='hidden'>\n<br/>";

	echo "Sebep($nSebep_id): <input name='sebep_exp' type='text' value='$cSebep_exp' onkeyup=\"ajax_showOptions(this,'oner,sebeplist',event)\">\n<br/>";
	echo "<input name='sebep_exp_id' id='sebep_exp_id' value='$nSebep_id' type='hidden'>\n<br/>";
	
	echo "<textarea name='acikla' rows=5 style='width:250;height:150'>$cAcikla</textarea>\n<br/>";
	echo "<input name='kaydet' type='submit' value='Kaydet'>\n<br/>";
	echo "</form>\n";
?>

<script type="text/javascript" src="_oner/ajax.js"></script>
<script type="text/javascript" src="_oner/ajax-dynamic-list.js"></script>
