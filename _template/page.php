<html>
<?php
	include_once("$REAL_P/_template/head.php");
	include_once("$REAL_P/_form/class_form.php");

$cUserInfo= $oUser->id	." | ".
			$oUser->exp ." | ".
			$oSirket->exp." | ".
			$oPerso->servis_exp." | ".
			$oUser->dilse." &nbsp;&nbsp; ".
			"<a href=?logout>Logout</a> &nbsp;&nbsp; ";

$cKare = "<IMG height=3 src='image/karenokta.gif' width=4 border=0/>";
$cList = "<IMG height=1 src='image/listnokta.gif' width=5 border=0/>";
$aratxt=isset($_GET['aratxt'])?$_GET['aratxt']:"";
?>
<body>
<table width="100%" height="400" border="0" cellpadding="5" cellspacing="0">
  <tr>
    <td width="220" height="75" valign="top">
	<img src="image/worldmedia.gif" alt="world logo" width="164" height="67"/>
	</td>
    <td height="75">
			<span class="ara"><input name="aratxt" value="<?php echo $aratxt;?>" class="ara" title="Ara" onKeyDown="Ara(this,event)"/></span>
			<?php
				echo "<span class=\"testinfo\">",$cUserInfo,"</span>";
			?>
	</td>
  </tr>
  <tr>
    <td class="serit">&nbsp;</td>
    <td class="serit"><?php include("$REAL_P/_template/testmenu.php"); ?></td>
  </tr>
                                                                         
  <tr>
    <td width="220" height="300" align="left" valign="top" class="anamenu"><?php include("$REAL_P/_template/leftmenu.php"); ?></td>
    <td class="icerik" id="form1_td"><?php include("$REAL_P/_template/main.php"); ?></td>
  </tr>
</table>

</body>
</html>
