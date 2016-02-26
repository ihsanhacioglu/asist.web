<?php header('Content-Type: text/html; charset=iso-8859-9');?>
<html>
    <head>
    <title> World Media Web Servisi </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-9">
    </head>

    <body leftmargin = "0" topmargin = "0" marginheight = "0" marginwidth = "0">
        <p>&nbsp;</p>
 
        <p>&nbsp;</p>

        <table width = "300" border = "0" align = "center" cellpadding = "0" cellspacing = "1" bgcolor = "#CCCCCC">
            <tr>
                <form name="form1" method="post" action="?checklogin">
                    <td>
                        <table width = "100%" border = "0" cellpadding = "3" cellspacing = "1" bgcolor = "#FFFFFF">
                            <tr>
                                <td colspan = "3"><strong>Kullanıcı Girişi </strong></td>
                            </tr>

                            <tr>
                                <td>Kullanıcı</td><td>:</td><td><input name  = "myusername" 
                                                                       type  = "text"
                                                                       id    = "myusername" 
                                                                       value = ""/></td>
                            </tr>

                            <tr>
                                <td>Şifre</td><td>:</td><td><input name  = "mypassword" 
                                                                   type  = "password"
                                                                   id    = "mypassword"
                                                                   value = ""/></td>
                            </tr>

                            <tr>
                                <td>&nbsp;</td>

                                <td>&nbsp;</td>

                                <td><input type = "submit" name = "Submit" value = "Login"/></td>
                            </tr>
                        </table>
                    </td>
                </form>
            </tr>
        </table>
    </body>
</html>
