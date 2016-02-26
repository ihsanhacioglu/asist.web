<?php

function Send_Zutrittsprofile1(strQuery As String, strQueryTyp As String, strAktion As String){
    Dim sZP As String * 2
    Dim strTPIHeader As String
    Dim strZPData As String
    Dim SeqResult As Boolean
    
  Set db = CurrentDb()
  Set qry = db.QueryDefs(strQuery)
  
  //Filter setzen ! Filter in strQuery führt zu fehler !!!!!!!
  If strQueryTyp = "ZPN" Or strQueryTyp = "DELZPN" Then
    qry("Ist_Zutrittsprofil:") = strAktion
  End If
  If strQueryTyp = "ZKM" Then
    qry("Ist_ZKMId:") = strAktion
    strQueryTyp = "ALL"
  End If
  
  Set rstAbfrage = qry.OpenRecordset()
  
  If rstAbfrage.EOF Then Exit Sub
  
    SendData "**** Zutrittsprofile-Download gestartet", "**"
    sACMID = "  "
    Do While Not rstAbfrage.EOF
    
        Build_PZ1    // Zutrittsprofil / Türoffenprofil
        
        // Funktion akt. ZProfile laden
        If strQueryTyp = "ZPN" And sACMID = "  " Then
            sACMID = rstAbfrage!ACMNr
            // ZProfile nnn löschen
            SendData gstrTCPServerID & sACMID & "J****!00Pz" & rstAbfrage!ZPNr & "**", "00"
            // ZProfile nnn laden
            strZPData = gstrTCPServerID & rstAbfrage!ACMNr & "J****!00PZ" & rstAbfrage!ZPNr & vonZeit$ & bisZeit$ & sZR & "**"
            SendData strZPData, "00"
        //End If
        
        ElseIf strQueryTyp = "ZPN" And sACMID = rstAbfrage!ACMNr Then
             strZPData = gstrTCPServerID & rstAbfrage!ACMNr & "J****!00PZ" & rstAbfrage!ZPNr & vonZeit$ & bisZeit$ & sZR & "**"
             SendData strZPData, "00"
        End If
     
        If strQueryTyp = "ZPN" And sACMID <> rstAbfrage!ACMNr Then
            SendData gstrTCPServerID & sACMID & "J****!00P9**", "00"
            sACMID = rstAbfrage!ACMNr
            // ZProfile nnn löschen
            SendData gstrTCPServerID & sACMID & "J****!00Pz" & rstAbfrage!ZPNr & "**", "00"
            // ZProfile nnn laden
            strZPData = gstrTCPServerID & rstAbfrage!ACMNr & "J****!00PZ" & rstAbfrage!ZPNr & vonZeit$ & bisZeit$ & sZR & "**"
            SendData strZPData, "00"
        End If
       
       // Funktion ZProfile löschen
       
       If strQueryTyp = "DELZPN" And sACMID = "  " Then
            sACMID = rstAbfrage!ACMNr
            SendData gstrTCPServerID & sACMID & "J****!00Pz" & rstAbfrage!ZPNr & "**", "00"
       End If
       
       If strQueryTyp = "DELZPN" And sACMID <> rstAbfrage!ACMNr Then
            sACMID = rstAbfrage!ACMNr
            SendData gstrTCPServerID & sACMID & "J****!00Pz" & rstAbfrage!ZPNr & "**", "00"
       End If
     
       // Funktion Grundversorgung ZProfile
       
       If strQueryTyp = "ALL" And sACMID = "  " Then
            sACMID = rstAbfrage!ACMNr
            // alle ZProfile löschen
            //SendData gstrTCPServerID & sACMID & "* Zutrittsprofile senden BEGINN", "**"
            strZPData = gstrTCPServerID & sACMID & "J****!00Pz***" & "**"
            SendData strZPData, "00"
            // ZProfile nnn laden
            strZPData = gstrTCPServerID & rstAbfrage!ACMNr & "J****!00PZ" & rstAbfrage!ZPNr & vonZeit$ & bisZeit$ & sZR & "**"
            SendData strZPData, "00"
       
       ElseIf strQueryTyp = "ALL" And sACMID = rstAbfrage!ACMNr Then
            strZPData = gstrTCPServerID & rstAbfrage!ACMNr & "J****!00PZ" & rstAbfrage!ZPNr & vonZeit$ & bisZeit$ & sZR & "**"
            SendData strZPData, "00"
       End If
    
        If strQueryTyp = "ALL" And sACMID <> rstAbfrage!ACMNr Then
            strZPData = gstrTCPServerID & sACMID & "J****!00P9**"
            SendData strZPData, "00"
            SendData gstrTCPServerID & sACMID & "* Zutrittsprofile gesendet", "**"
           // Datum und Uhrzeit letzte Zutrittsprofile-Grundversorgung
            setZKMZeitstempel "ZP", sACMID
            
            sACMID = rstAbfrage!ACMNr
            // ZProfile nnn löschen
            //SendData gstrTCPServerID & sACMID & "* Zutrittsprofile senden BEGINN", "**"
            strZPData = gstrTCPServerID & sACMID & "J****!00Pz***" & "**"
            SendData strZPData, "00"
            //SeqResult = SeqZeileAppendOutput(gstr74Datei, strZPData)
            // ZProfile nnn laden
            strZPData = gstrTCPServerID & rstAbfrage!ACMNr & "J****!00PZ" & rstAbfrage!ZPNr & vonZeit$ & bisZeit$ & sZR & "**"
            SendData strZPData, "00"
            //SeqResult = SeqZeileAppendOutput(gstr74Datei, strZPData)
        End If
    
   rstAbfrage.MoveNext
   Loop
  
   rstAbfrage.MoveFirst
   rstAbfrage.Requery
   If Not rstAbfrage.EOF Then
 
    If strQueryTyp <> "DELZPN" Then
        strZPData = gstrTCPServerID & sACMID & "J****!00P9**"
        SendData strZPData, "00"
        SendData gstrTCPServerID & sACMID & "* Zutrittsprofile gesendet", "**"
        //SeqResult = SeqZeileAppendOutput(gstr74Datei, strZPData)
    End If
    If strQueryTyp = "ALL" Then
        // Datum und Uhrzeit letzte Zutrittsprofile-Grundversorgung
        setZKMZeitstempel "ZP", sACMID
    End If
End If
rstAbfrage.Close
If Not IsNumeric(strAktion) Then
 CheckZKMnichtBereit "qryZutrittsprofileTPInichtBereit", "* Zutrittsprofile FEHLER: TPI nicht Bereit !"
End If
SendData "**** Zutrittsprofile-Download beendet", "**"
//SendData "Download beendet", "STOP"
}

function Build_PZ1(){
Dim iZR As Integer

    sZR = "NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN"  ' Vorbelegung
    iZR = 1
    With rstAbfrage
       If !Sonntag Then Mid(sZR, 1) = "J"
       If !Montag Then Mid(sZR, 2) = "J"
       If !Dienstag Then Mid(sZR, 3) = "J"
       If !Mittwoch Then Mid(sZR, 4) = "J"
       If !Donnerstag Then Mid(sZR, 5) = "J"
       If !Freitag Then Mid(sZR, 6) = "J"
       If !Samstag Then Mid(sZR, 7) = "J"
       If !ST_Kennung1 Then Mid(sZR, 8) = "J"
       If !ST_Kennung2 Then Mid(sZR, 9) = "J"
       If !ST_Kennung3 Then Mid(sZR, 10) = "J"
       Mid(sZR, 11) = "-"   ' Reserviert
       If !Pincodeeingabe Then Mid(sZR, 12) = "J"
       Mid(sZR, 13) = "-JN" ' Reserviert + berechtigt Puffern + Sonderberechtigung
       If ![Terminal 0] Then Mid(sZR, 16) = "J"
       If ![Terminal 1] Then Mid(sZR, 17) = "J"
       If ![Terminal 2] Then Mid(sZR, 18) = "J"
       If ![Terminal 3] Then Mid(sZR, 19) = "J"
       If ![Terminal 4] Then Mid(sZR, 20) = "J"
       If ![Terminal 5] Then Mid(sZR, 21) = "J"
       If ![Terminal 6] Then Mid(sZR, 22) = "J"
       If ![Terminal 7] Then Mid(sZR, 23) = "J"
       If ![Terminal 8] Then Mid(sZR, 24) = "J"
       If ![Terminal 9] Then Mid(sZR, 25) = "J"
       If ![Terminal 10] Then Mid(sZR, 26) = "J"
       If ![Terminal 11] Then Mid(sZR, 27) = "J"
       If ![Terminal 12] Then Mid(sZR, 28) = "J"
       If ![Terminal 13] Then Mid(sZR, 29) = "J"
       If ![Terminal 14] Then Mid(sZR, 30) = "J"
       If ![Terminal 15] Then Mid(sZR, 31) = "J"
       If ![Terminal 16] Then Mid(sZR, 32) = "J"
   
       vonZeit$ = Format$(!vonZeit, "hhmm")
       bisZeit$ = Format$(!bisZeit, "hhmm")
    End With
}

?>