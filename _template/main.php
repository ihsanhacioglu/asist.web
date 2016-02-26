<?php
	if($par_islem=="ara")						include("$REAL_P/_form/__ara.php");
	elseif (substr($par_islem,0,5)=="form_")	include("$REAL_P/_form/__form.php");
	elseif (substr($par_islem,0,6)=="hizli_")	include("$REAL_P/_form/__hizli.php");
	elseif (substr($par_islem,0,7)=="pratik_")	include("$REAL_P/_form/__pratik.php");
	elseif (substr($par_islem,0,6)=="admin_")	include("$REAL_P/_admin/__admin.php");
	else										include("$REAL_P/_template/default.php");
?>