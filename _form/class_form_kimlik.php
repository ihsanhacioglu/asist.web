<?php
class class_form_kimlik extends class_form{
	function add_islem(){
		$this->arrIslem["idx"]="G";
	}
	
	function createCalFlds(){
		$this->arrCals=array();
		
		// Adres Kayd�
		$this->arrCals["adres_cst"]=0;
		$this->arrCals["adres_cst_exp"]="";
		$this->arrCals["adres_kapi"]="";
		$this->arrCals["adres_unvan"]="";
		$this->arrCals["adres_bolge"]=0;
		$this->arrCals["adres_bolge_exp"]="";
		$this->arrCals["adres_bolge_grup"]="";
		$this->arrCals["adres_bolge_ustgrup"]="";
		$this->arrCals["adres_ulke_exp"]="Deutschland";
		
		// Telefon1
		$this->arrCals["tadres_ktipi1"]=0;
		$this->arrCals["tadres_exp1"]="";
		$this->arrCals["tadres_ktipi_exp1"]="";

		// Telefon2
		$this->arrCals["tadres_ktipi2"]=0;
		$this->arrCals["tadres_exp2"]="";
		$this->arrCals["tadres_ktipi_exp2"]="";

		// Telefon3
		$this->arrCals["tadres_ktipi3"]=0;
		$this->arrCals["tadres_exp3"]="";
		$this->arrCals["tadres_ktipi_exp3"]="";

		// Email Adresi
		$this->arrCals["eadres_exp"]="";

		// Web Adresi
		$this->arrCals["wadres_exp"]="";

		// Anket Bilgileri
		$this->arrCals["anket_milet"]=0;
		$this->arrCals["anket_milet_exp"]="";
		$this->arrCals["anket_unvani"]="";

		// Vazife Bilgileri	
		$this->arrCals["vazife_mesev"]=0;
		$this->arrCals["vazife_mesev_exp"]="";
		$this->arrCals["vazife_exp"]="";
		$this->arrCals["vazife_nerde"]="";
		
		// �lgili Kisi Bilgileri	
		$this->arrCals["ikisi_ktipi"]=0;
		$this->arrCals["ikisi_ktipi_exp"]="";
		$this->arrCals["ikisi_exp"]="";
		$this->arrCals["ikisi_tadres_ktipi"]=0;
		$this->arrCals["ikisi_tadres_ktipi_exp"]="";
		$this->arrCals["ikisi_tadres_exp"]="";
		$this->arrCals["ikisi_eadres_exp"]="";		

		// Temas Bilgileri
		$this->arrCals["temas_atarih"]="";
		$this->arrCals["temas_nerde"]="";
		$this->arrCals["temas_exp"]="";
		$this->arrCals["temas_proje"]=0;
		$this->arrCals["temas_proje_exp"]="";
		$this->arrCals["temas_acikla"]="";

		// Kimlik grubu
		$this->arrCals["klist_kgrup1"]=0;
		$this->arrCals["klist_kgrup2"]=0;
		$this->arrCals["klist_kgrup3"]=0;
		$this->arrCals["klist_kgrup_exp1"]="";
		$this->arrCals["klist_kgrup_exp2"]="";
		$this->arrCals["klist_kgrup_exp3"]="";
	}

	function formidx(){
		$this->indexUpdate(true);
		$this->ins_upd_del_message(10);
	}
	function beforePost(){
		$this->createCalFlds();
		$this->bindCalFlds();
		if($this->islem=="ins")$this->adresInsert();
	}
	function afterPost(){
		if($this->islem=="ins")$this->restInsert();
		$this->indexUpdate();
	}
	function recValid($QRY){
		if(!parent::recValid($this->qry))return false;
		return true;
	}


	function adresInsert(){
		// 0. Adres kayd�
		if(empty($this->arrCals["adres_bolge"])){
			preg_match("/^\s*(\S+)\s*,\s*(\S+)\s*+$/",$this->arrCals["adres_bolge_exp"],$arr_match);
			$tBolge = $this->qry->derive_tab("bolge:set=1",-1);
			$tBolge->rec_exp     = $arr_match[1];
			$tBolge->rec_grup    = $arr_match[2];
			$tBolge->rec_ustgrup = $this->arrCals["adres_ulke_exp"];
			$tBolge->insert();
			$this->arrCals["adres_bolge"]=$tBolge->rec_id;
		}
		if(empty($this->arrCals["adres_cst"])){
			$tCst = $this->qry->derive_tab("cst:set=1",-1);
			$tCst->rec_exp   = $this->arrCals["adres_cst_exp"];
			$tCst->rec_bolge = $this->arrCals["adres_bolge"];
			$tCst->insert();
			$this->arrCals["adres_cst"]=$tCst->rec_id;
		}
		if(!empty($this->arrCals["adres_cst"])){
			$tAdres = $this->qry->derive_tab("adres:set=1",-1);
			$tAdres->rec_cst   = $this->arrCals["adres_cst"];
			$tAdres->rec_kapi  = $this->arrCals["adres_kapi"];
			$tAdres->rec_unvan = $this->arrCals["adres_unvan"];
			$tAdres->insert();
			$this->qry->rec_adres=$tAdres->rec_id;
		}
	}

	function restInsert(){
	global $oUser,$oMesul,$oSirket;
		// 1. Telefon numaras�n�n kayd�	
		if(!empty($this->arrCals["tadres_exp1"])){
			$tTadres = $this->qry->derive_tab("tadres:set=1",-1);
			$tTadres->rec_kimlik = $this->qry->rec_id;
			$tTadres->rec_ktipi  = $this->arrCals["tadres_ktipi1"];
			$tTadres->rec_exp    = $this->arrCals["tadres_exp1"];
			$tTadres->insert();
		}
		
		// 2. Telefon numaras�n�n kayd�	
		if(!empty($this->arrCals["tadres_exp2"])){
			$tTadres = $this->qry->derive_tab("tadres:set=1",-1);
			$tTadres->rec_kimlik = $this->qry->rec_id;
			$tTadres->rec_ktipi  = $this->arrCals["tadres_ktipi2"];
			$tTadres->rec_exp    = $this->arrCals["tadres_exp2"];
			$tTadres->insert();
		}

		// 3. Telefon numaras�n�n kayd�	
		if(!empty($this->arrCals["tadres_exp3"])){
			$tTadres = $this->qry->derive_tab("tadres:set=1",-1);
			$tTadres->rec_kimlik = $this->qry->rec_id;
			$tTadres->rec_ktipi  = $this->arrCals["tadres_ktipi3"];
			$tTadres->rec_exp    = $this->arrCals["tadres_exp3"];
			$tTadres->insert();
		}
	
		// Email Adresi
		if(!empty($this->arrCals["eadres_exp"])){
			$tEadres = $this->qry->derive_tab("eadres:set=1",-1);
			$tEadres->rec_kimlik = $this->qry->rec_id;
			$tEadres->rec_ktipi  = -31201 ; //Is Email Adresi                              
			$tEadres->rec_exp    = $this->arrCals["eadres_exp"];
			$tEadres->insert();
		}

		// Web Adres Adresi
		if(!empty($this->arrCals["wadres_exp"])){
			$tWadres = $this->qry->derive_tab("wadres:set=1",-1);
			$tWadres->rec_kimlik = $this->qry->rec_id;
			$tWadres->rec_ktipi  = -31301 ; //Sirket web adresi
			$tWadres->rec_exp    = $this->arrCals["wadres_exp"];
			$tWadres->insert();
		}

		// Anket Bilgilerinin Kayd�
		if(!empty($this->arrCals["anket_milet"]) && $this->arrCals["anket_milet"]!=-1){
			$tAnket=$this->qry->derive_tab("anket:set=1",-1);
			$tAnket->rec_kimlik = $this->qry->rec_id;
			$tAnket->rec_unvani = $this->arrCals["anket_unvani"];
			$tAnket->rec_milet  = $this->arrCals["anket_milet"];
			$tAnket->insert();
		}

		// Meslek-G�rev Bilgilerinin Kayd�
		if(empty($this->arrCals["vazife_mesev"])){
			$tMesev=$this->qry->derive_tab("mesev:set=1",-1);
			$tMesev->rec_exp = $this->arrCals["vazife_mesev_exp"];
			$tMesev->rec_exp = trim($tMesev->rec_exp,"+");
			$tMesev->insert();
			$this->arrCals["vazife_mesev"]=$tMesev->rec_id;
		}

		// Vazife Bilgilerinin Kayd�
		if(!empty($this->arrCals["vazife_mesev"]) && $this->arrCals["vazife_mesev"]!=-1){
			$tVazife=$this->qry->derive_tab("vazife:set=1",-1);
			$tVazife->rec_kimlik = $this->qry->rec_id;
			$tVazife->rec_exp    = $this->arrCals["vazife_exp"];
			$tVazife->rec_nerde  = $this->arrCals["vazife_nerde"];
			$tVazife->rec_mesev  = $this->arrCals["vazife_mesev"];
			$tVazife->insert();
		}

		// Temas Bilgileri Kayd�
		if(!empty($this->arrCals["temas_proje_exp"])){
			$tTemas=$this->qry->derive_tab("temas:set=1",-1);
			$tTemas->rec_kimlik = $this->qry->rec_id;
			$tTemas->rec_exp    = $this->arrCals["temas_exp"];
			$tTemas->rec_atarih = $this->arrCals["temas_atarih"];
			$tTemas->rec_nerde  = $this->arrCals["temas_nerde"];
			if(empty($this->arrCals["temas_proje"])){
				$tTemas->rec_proje  = -1;
				$tTemas->rec_acikla = $this->arrCals["temas_proje_exp"]."\n".$this->arrCals["temas_acikla"];
			}else{
				$tTemas->rec_proje  = $this->arrCals["temas_proje"];
				$tTemas->rec_acikla = $this->arrCals["temas_acikla"];
			}
			$tTemas->insert();
		}


		// �lgili Kisi Bilgilerinin Kayd�
		if(!empty($this->arrCals["ikisi_ktipi"]) && $this->arrCals["ikisi_ktipi"]!=-1){
			$tIkisi=$this->qry->derive_tab("ikisi:set=1",-1);
			$tIkisi->rec_kimlik = $this->qry->rec_id;
			$tIkisi->rec_exp    = $this->arrCals["ikisi_exp"];
			$tIkisi->rec_ktipi  = $this->arrCals["ikisi_ktipi"];
			$tIkisi->insert();
			
			// Ilgili Kisi Telefon numaras�n�n kayd�	
			if(!empty($this->arrCals["ikisi_tadres_ktipi"])){
				$tTadres = $this->qry->derive_tab("tadres:set=1",-1);
				$tTadres->rec_kimlik = $this->qry->rec_id;
				$tTadres->rec_ktipi  = $this->arrCals["ikisi_tadres_ktipi"];
				$tTadres->rec_exp    = $this->arrCals["ikisi_tadres_exp"];
				$tTadres->rec_acikla = $tIkisi->rec_exp;
				$tTadres->rec_iliski = "Ikisi-".$tIkisi->rec_id;
				$tTadres->insert();
			}
			
			// Ilgili Kisi Telefon numaras�n�n kayd�	
			if(!empty($this->arrCals["ikisi_eadres_exp"])){
				$tEadres = $this->qry->derive_tab("eadres:set=1",-1);
				$tEadres->rec_kimlik = $this->qry->rec_id;
				$tEadres->rec_ktipi  = -31201 ; //Is Email Adresi                              
				$tEadres->rec_exp    = $this->arrCals["ikisi_eadres_exp"];
				$tEadres->rec_acikla = $tIkisi->rec_exp;
				$tEadres->rec_iliski = "Ikisi-".$tIkisi->rec_id;
				$tEadres->insert();
			}
		}
		if(!empty($this->arrCals["klist_kgrup1"]) || !empty($this->arrCals["klist_kgrup2"]) || !empty($this->arrCals["klist_kgrup3"])){
			$tKlist=$this->qry->derive_tab("klist:set=1",-1);
			$tKlist->rec_kimlik = $this->qry->rec_id;
			$tKlist->rec_mesul  = $oMesul->id;
			$tKlist->rec_sirket = 0;
			$tKlist->rec_kuser  = 0;
			$tKlist->rec_atarih = date("D-m-y");
			if(!empty($this->arrCals["klist_kgrup1"])){
				$tKlist->rec_kgrup  = $this->arrCals["klist_kgrup1"];
				$tKlist->insert();
			}
			if(!empty($this->arrCals["klist_kgrup2"])){
				$tKlist->rec_kgrup  = $this->arrCals["klist_kgrup2"];
				$tKlist->insert();
			}
			if(!empty($this->arrCals["klist_kgrup3"])){
				$tKlist->rec_kgrup  = $this->arrCals["klist_kgrup3"];
				$tKlist->insert();
			}
		}
    }

	function indexUpdate($gunc=null){
		global $Asist_World, $oAPP;

		$oDB=connect_datab($Asist_World);
		$clsWorld="cls$oDB->cls";

		//Kimlik
		$strSql="select kimlik.id,
					kimlik.exp,
					sehir.exp	sehir_exp,
					mesul.exp	mesul_exp,
					kimlik.cinsi,
					kimlik.acikla,
					kimlik.adres
				from asist!kimlik,
					asist!sirket,
					asist!mesul,
					asist!sehir
				where ".($gunc?"1=1":"kimlik.id=?prm_id")."
					and kimlik.sirket = sirket.id
					and kimlik.mesul  = mesul.id
					and kimlik.sehir  = sehir.id";
		$qCCC=new $clsWorld($oDB->dblink,$strSql);
		$qCCC->prm_id=$this->qry->rec_id;
		$qCCC->open(null,null);

		//Anket
		$arrIdx['ank'][]=new $clsWorld($oDB->dblink,"select milet.exp milet_exp, btarih, sehir.exp sehir from asist!anket, asist!kume_tr milet, asist!sehir where anket.kimlik=?prm_id and anket.milet=milet.id and anket.sehir=sehir.id");
		$arrIdx['ank'][]="\n<br><b>Anket</b>\n";
		$arrIdx['ank'][]="";
		$arrIdx['ank'][]="";

		//Adres
		$arrIdx['adr'][]=new $clsWorld($oDB->dblink,"select unvan, cst.exp str, kapi, bolge.exp PLZ, bolge.grup ORT from asist!adres,	asist!cst, asist!bolge where adres.id=?prm_id and adres.cst=cst.id and cst.bolge=bolge.id");
		$arrIdx['adr'][]="\n<br><b>Adres</b>\n";
		$arrIdx['adr'][]="%str %kapi\n%plz %ort\n";
		$arrIdx['adr'][]="";
		$arrIdx['adr'][]="adres";

		//Telefon
		$arrIdx['tel'][]=new $clsWorld($oDB->dblink,"select tel.exp, ktipi.exp ktipi_exp from asist!tadres tel, asist!kume_tr ktipi where tel.kimlik=?prm_id and tel.ktipi=ktipi.id");
		$arrIdx['tel'][]="\n<br><b>Telefon</b>\n";
		$arrIdx['tel'][]="<i>%ktipi_exp</i>: %exp\n";
		$arrIdx['tel'][]="%ktipi_exp: %exp\n";

		//�lgili ki�i
		$arrIdx['iki'][]=new $clsWorld($oDB->dblink,"select iki.exp, ktipi.exp ktipi_exp from asist!ikisi iki, asist!kume_tr ktipi where iki.kimlik=?prm_id and iki.ktipi=ktipi.id");
		$arrIdx['iki'][]="\n<br><b>�lgili ki�i</b>\n";
		$arrIdx['iki'][]="<i>%ktipi_exp</i>: %exp\n";
		$arrIdx['iki'][]="%ktipi_exp: %exp\n";

		//Vazife
		$arrIdx['vaz'][]=new $clsWorld($oDB->dblink,"select vaz.exp, vaz.nerde, mesev.exp mesev_exp, mesev.grup mesev_grup from asist!vazife vaz, asist!mesev where vaz.mesev=mesev.id and vaz.kimlik=?prm_id");
		$arrIdx['vaz'][]="\n<br><b>Vazife</b>\n";
		$arrIdx['vaz'][]="";
		$arrIdx['vaz'][]="";

		//E�itim
		$arrIdx['egi'][]=new $clsWorld($oDB->dblink,"select aldim.exp, aldim.nerde, bolum.exp bolum_exp from asist!aldim, asist!bolum_tr bolum where aldim.bolum=bolum.id and aldim.kimlik=?prm_id");
		$arrIdx['egi'][]="\n<br><b>E�itim</b>\n";
		$arrIdx['egi'][]="";
		$arrIdx['egi'][]="";

		//E-posta
		$arrIdx['epo'][]=new $clsWorld($oDB->dblink,"select epos.exp, ktipi.exp ktipi_exp from asist!eadres epos, asist!kume_tr ktipi where epos.kimlik=?prm_id and epos.ktipi=ktipi.id");
		$arrIdx['epo'][]="\n<br><b>E-posta</b>\n";
		$arrIdx['epo'][]="<i>%ktipi_exp</i>: %exp\n";
		$arrIdx['epo'][]="%ktipi_exp: %exp\n";

		$strSql="insert into asist.kimlik_text (kimlik,exp,form,formx) values (?prm_kimlik,?prm_exp,?prm_form,?prm_formx) on duplicate key update id=LAST_INSERT_ID(id),exp=?prm_exp,form=?prm_form,formx=?prm_formx";
		$qKIM=new clsApp($oAPP->dblink,$strSql);

		$nn=0;
		while($qCCC->next()){
			$strFull="";
			$strForm="";

			$valIdx="";
			$valFrm="";
			$this->acikVal($qCCC,$valIdx,$valFrm,false);
			if($valFrm)$strForm.="<b>Kimlik</b>\n".$valFrm;
			if($valIdx)$strFull.=$valIdx;

			foreach($arrIdx as $arrQQ){
				$QQ=$arrQQ[0];
				$QQ->close();
				$rec=isset($arrQQ[4]) ? "rec_".$arrQQ[4] : "rec_id";
				$QQ->prm_id=$qCCC->$rec;
				$QQ->open(null,null);

				$valFrm=$arrQQ[2];
				$valIdx=$arrQQ[3];
				$this->acikVal($QQ,$valIdx,$valFrm);
				if($valIdx)$strFull.=$valIdx;
				if($valFrm)$strForm.=$arrQQ[1].$valFrm;
			}

			//Index ekleme
			$qKIM->prm_kimlik=$qCCC->rec_id;
			$qKIM->prm_exp=$qCCC->rec_exp;
			$qKIM->prm_form=$strForm;
			$qKIM->prm_formx=$this->idx_Tran($strFull);
			$qKIM->exec();
			if($gunc){
				if($nn%500==0)echo "$nn: $qCCC->rec_id -> $qCCC->rec_exp<br>";
				$nn++;
			}
		}
	}

	function acikVal($QQ,&$retIdx,&$retFrm,$loop=true){
		$idx=($retIdx ? preg_replace("/%(\w+)/U","\$QQ->rec_\${1}",$retIdx) : "");
		$frm=($retFrm ? preg_replace("/%(\w+)/U","\$QQ->rec_\${1}",$retFrm) : "");

		$retIdx="";
		$retFrm="";
		if($loop)while($QQ->next()){
			if($idx)$retIdx.=eval("return \"$idx\";");
			else foreach($QQ->arrFields as $oFld)if(!empty($oFld->value) && $oFld->value!="...")$retIdx.="$oFld->name: $oFld->value\n";

			if($frm)$retFrm.=eval("return \"$frm\";");
			else foreach($QQ->arrFields as $oFld)if(!empty($oFld->value) && $oFld->value!="...")$retFrm.="$oFld->name: $oFld->value\n";
		}else{
			foreach($QQ->arrFields as $oFld)if(!empty($oFld->value) && $oFld->value!="..."){
				$retIdx.="$oFld->name: $oFld->value\n";
				$retFrm.="$oFld->name: $oFld->value\n";
			}
		}
	}
	function formara(){
		$aratxt=isset($_GET['aratxt'])?$_GET['aratxt']:"";
		if(strtolower(substr($aratxt,0,2))=="a:"){
			$aratxt=substr($aratxt,2);
			$str_ara=$this->idx_Tran($aratxt);
			if(preg_match_all("/(\W+)?(\w+)(\W+|$)/",$str_ara,$arr_match,PREG_SET_ORDER)){
				$str_ara="";
				foreach($arr_match as $ara)$str_ara.=$ara[1].$ara[2]."*".$ara[3];
			}
			echo $str_ara,"<br>";

			$oSayfa=new class_form($this->appLink, "form_kimlik_text");
			$strSql="select * from asist.kimlik_text where match(formx,exp) against(?prm_ara in boolean mode)";
			$qCCC=new clsApp($this->appLink, $strSql);
			$qCCC->prm_ara=$str_ara;
			$this->qry=$qCCC;
			$this->senaryo->listtemp=$oSayfa->senaryo->listtemp;
			$this->senaryo->deffld=$oSayfa->senaryo->deffld;
			$this->qry->open(null,null);
			$objParam=$this->listpar();
			$this->listele($objParam);
		}else{
			$_GET['aratxt']=$aratxt;
			parent::formara();
		}
	}
	function idx_Tran($cStr){
		$cStr=strtolower(strtr($cStr,"������������I���gz","gusiocgusiociaasks"));
		$cStr=strtr($cStr,array("aa"=>"a", "ee"=>"e", "ii"=>"i", "uu"=>"u", "oo"=>"o", "dd"=>"d", "tt"=>"d",
								"ue"=>"u", "oe"=>"o", "ae"=>"a", "ss"=>"s"));
		return $cStr;
	}
	function create_qry($strClass=null){
		parent::create_qry();
		if($this->islem!="sel" && $this->islem!="lst" && !empty($this->islem))return;
		$this->qry->_CCC=CUR_II($this->senaryo->datab);
	}
	function afterOpen(){
		if($this->islem!="sel" && $this->islem!="lst" && !empty($this->islem))return;
		
		$CC_1=CUR_II($this->senaryo->datab);
		$CC_2=CUR_II($this->senaryo->datab);
		$CC_3=CUR_II($this->senaryo->datab);
		
		$CCC=$this->qry->_CCC;
		$QQ_1=$this->qry->derive_qry("select cc.id k_id,g_birle(cc.id,'$CC_1',eadres.exp) sira from asist!eadres,$CCC cc where kimlik=cc.id");	$QQ_1->open();
		$QQ_2=$this->qry->derive_qry("select cc.id k_id,g_birle(cc.id,'$CC_2',tadres.exp) sira from asist!tadres,$CCC cc where kimlik=cc.id");	$QQ_2->open();
		$QQ_3=$this->qry->derive_qry("select adres.id, <<cast(trim(cst.exp)+' '+trim(adres.kapi)+'<br>'+trim(bolge.exp)+' '+trim(bolge.grup) as C(100))>> str_adr
									from $CCC,asist!adres,asist!cst,asist!bolge
									where $CCC.adres=adres.id
										and adres.cst=cst.id
										and cst.bolge=bolge.id
									into cursor $CC_3"); $QQ_3->open();
	
		$QQ=$this->qry->derive_qry("select $CCC.*, $CC_3.str_adr, $CC_1.birle m_epos,$CC_2.birle m_tele, nvl($CC_1.adet,$CC_2.adet) adet
									from $CCC left join $CC_3 on $CCC.adres=$CC_3.id
											  left join $CC_1 on $CCC.id=$CC_1.id
											  left join $CC_2 on $CCC.id=$CC_2.id");	$QQ->open();
		$QQ->open(null,null);
		$this->qry=$QQ;
	}
}
?>