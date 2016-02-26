<?php
class class_form_tanim extends class_form{
    function afterPost(){
		$this->denkUpdate("tanim");
    }
}
?>