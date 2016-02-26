
<html>
<head>
    <?php include("$REAL_P/_template/head.php"); ?>

<style type="text/css">
<!--
.serit {
	border-top-width: thin;
	border-right-width: thin;
	border-bottom-width: thin;
	border-left-width: thin;
	border-top-style: dotted;
	border-right-style: none;
	border-bottom-style: dotted;
	border-left-style: none;
	border-top-color: #999999;
	border-right-color: #999999;
	border-bottom-color: #999999;
	border-left-color: #999999;
}
.anamenu {
	border-right-width: 1px;
	border-right-style: dotted;
	border-right-color: #999999;
}
.style2 {font-size: 12px}
.style3 {font-size: 12px; color: #666666; }
.icerik {
	vertical-align: text-top;
}
-->
</style>
</head>

<body>

<div>
    <div style="float:left;">
    <div style="width:175px; float: left;" height="70px">
	<img src="image/worldmedia.png" alt="world logo" width="164px" height="67px"/>
    </div>
    <div style="width:800px; float: right;">
	<p align="center">[HEADER]</p>
    </div>
    </div>

    <div style="float:left">
    <div style="border: 2px; float:left; width:175px;"> [SERIT] </div>
    <div style="float:right;"> [USERNAME] </div>
    </div>

    <div style="float:left">
    <div align="left" valign="top" class="anamenu" style="float:left; width:175px; height:100%"> <?php include("$REAL_P/_template/leftmenu.php"); ?> </div>
    <div align="left" class="icerik" style="float:left"> <?php include("$REAL_P/_template/main.php"); ?> </div>
    </div>

    <div style="float:left">
    <p class="style3">	[FOOTER]</p>
    </div>
</div>

</body>
</html>
