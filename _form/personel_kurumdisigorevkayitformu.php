<?php header('Content-Type: text/html; charset=iso-8859-9');?>
<html>
    <head>
	<title>  World Media Web Servisi </title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-9">
    </head>

    <body leftmargin = "0" topmargin = "0" marginheight = "0" marginwidth = "0">
        <p>&nbsp;</p>
 
        <p>&nbsp;</p>

		<form name="form1" method="post" action="?kurumdisigorevkaydi">
		<table width = "400" border = "0" cellpadding = "3" cellspacing = "1" bgcolor = "#FFFFFF">
			<tr><td colspan = "3"><h2>Kurum D��� G�revlendirme Formu </h2></td></tr>
			<tr><td>.</td></tr>
			<tr><td colspan = "3"><b>Personel Bilgileri</b></td></tr>
			<tr><td colspan = "3"><hr/></td></tr>
			<tr><td> Ad� Soyad�</td><td>:</td><td><input name="perso_exp" type="text" id="perso" value="personel adi giriniz"/></td></tr>
			<tr><td>Servis Ad�</td><td>:</td><td><input name="servis_exp"   type="text" id="servis"   value="a"/></td></tr>
			<tr><td>.</td></tr>
			<tr><td colspan = "3"><b>G�rev Bilgileri</b></td></tr>
			<tr><td colspan = "3"><hr/></td></tr>
            <tr><td>G�rev Ba�lama Tarihi</td><td>:</td><td><input name="atarih" type="date" id="atarih" value=""/></td></tr>
			<tr><td>G�rev Biti� Tarihi  </td><td>:</td><td><input name="ctarih" type="date" id="ctarih" value=""/></td></tr>
			<tr><td>G�reve Gidilen Yer  </td><td>:</td><td><input name="nereye" type="text" id="nereye" value=""/></td></tr>
			<tr><td>G�rev Sebebi        </td><td>:</td><td><input name="sebebi" type="text" id="sebebi" value=""/></td></tr>
			<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type = "submit" name = "kaydet" value = "Kaydet"/></td></tr>
		</table>
		</form>
    </body>
</html>
