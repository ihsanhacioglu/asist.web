<?php
class class_form_dene extends class_form{
    function afterPost(){
        global $oPerso;
        
        $tDene2=$this->qry->derive_tab("dene2:auto=1",1);
		$tDene2->print_vals();
        $tDene2->rec_exp    = "Harun Teðmen";
		$tDene2->rec_tarih  = $this->objVal("bugun");
		$tDene2->rec_perso  = $oPerso->id;
        $tDene2->rec_kimlik = -1;
		$tDene2->print_vals();
		$tDene2->update();
    }
}
?>