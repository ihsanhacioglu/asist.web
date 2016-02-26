<?php
class class_form extends class__base{
	function __construct($nLink, $cAction){
		global $oUser;

		if(empty($nLink)) return false;
		$this->appLink=$nLink;

		$cDil=strtolower($oUser->dilse);
		if($cDil!="de" && $cDil!="en") $cDil="tr";
		$this->dil=$cDil;
		if (!($this->senaryo=$this->getSenaryo($cAction))){
			echo "$cAction<br>ACCESS DENIED";
			return false;
		}
		if($this->senaryo->tur=="bilgi") $this->edit=0;
		$this->islem=isset($_GET["islem"]) ? $_GET["islem"] : "";
		$this->mod=isset($_GET["mod"]);
		$this->grd=$this->islem=="gdt" || $this->islem=="gok";
		$this->create_islem();
		$this->create_qry();
	}

	function add_islem(){}
	function create_islem(){
		$this->arrIslem=array(
			"new"=>"I",
			"ins"=>"I",
			"edt"=>"U",
			"upd"=>"U",
			"cpy"=>"I",
			"del"=>"D",
			"src"=>"S",
			"ara"=>"S",
			"lst"=>"S",
			"sel"=>"S",
			"brw"=>"S",
			"gdt"=>"G",
			"gok"=>"G",
			"gpd"=>"G",
			"snl"=>"I");
		$this->add_islem();
	}
	function getSenaryo($cAction){
		global $oUser;

		$tabSayfa="sayfa_$this->dil";
		$sqlStr="select role.once,
					sen.id,
					sen.exp,
					sen.action,
					sen.sqlstr,
					sen.ustmenu,
					sen.altmenu,
					sen.updtables,
					sen.newtemp,
					sen.formtemp,
					sen.findtemp,
					sen.gridtemp,
					sen.listtemp,
					sen.linkvalues,
					sen.reqfld,
					sen.intfld,
					sen.deffld,
					sen.detay,
					sen.grid,
					sen.color,
					sen.tur,
					sen.datab,
					sen.snloption,
					sen.snlmodal,
					sen.relmodal,
					senrole.defvalues,
					senrole.filtvalues,
					senrole.filtrele,
					senrole.listfld,
					senrole.readfld,
					senrole.options
				from asist.$tabSayfa sen, asist.senrole, asist.role
				where sen.action='$cAction'
					and sen.tur in('senaryo','bilgi')
					and sen.id=senrole.senaryo
					and senrole.role=role.id
					and $oUser->where
				order by 1";
		$res=mysqli_query($this->appLink, $sqlStr);
		$oSen=mysqli_fetch_object($res);
		mysqli_free_result($res);
	    return $oSen;
	}
	function setQryUpdates(){
		$this->qry->intFld=$this->senaryo->intfld;
		$this->qry->reqFld=$this->senaryo->reqfld;
		$this->qry->readFld=$this->senaryo->readfld;
		$this->qry->setUpdates($this->senaryo->updtables);
	}

	function islem($islem=""){
		if(!empty($islem))$this->islem=$islem;
		if(!$this->islemPerm()){echo "ACCESS DENIED";return false;}
		if(isset($this->arrIslem[$this->islem]))$this->{"form$this->islem"}();else$this->formdef();
	}

	function islemPerm($islem=""){
		if(empty($islem))$islem=$this->islem;
		if(isset($this->arrIslem[$islem]))$cPerm=$this->arrIslem[$islem];else$cPerm="S";
		return strpbrk($this->senaryo->options,$cPerm);
	}

	function ins_upd_del_message($sure=0){
		if($this->mod)$this->modMessage();
		else{
			if($this->senaryo->filtrele==2)
			$url="{$this->senaryo->action}&islem=edt&id={$this->qry->id}";
			else$url="{$this->senaryo->action}";
			$str="";
			if(empty($this->msg))$msure=empty($sure)?0:$sure*1000;else{$msure=7000;$str=$this->msg;}
			$str.="<a href=\"?$url\">{$this->senaryo->exp}</a> $sure
				 <script>setTimeout('window.location.replace(\"?$url\")',$msure);</script>";
			echo $str;
		}
	}
	function bindPostVals($QRY){
		foreach($QRY->arrFields as $oFld){
			$post_name="frm_$oFld->name";
			if(isset($_POST[$post_name]))
				if($oFld->char=="S")
					$oFld->value=substr($_POST[$post_name],0,$oFld->length);
				elseif($oFld->char=="N")
					$oFld->value=strtr($_POST[$post_name],',','.');
				elseif(empty($_POST[$post_name]))
					$oFld->value=$oFld->emptyval;
				else$oFld->value=$_POST[$post_name];

			$post_name="frm2_$oFld->name";
			if(isset($_POST[$post_name]))
				if($oFld->char=="S")
					$oFld->value2=substr($_POST[$post_name],0,$oFld->length);
				elseif($oFld->char=="N")
					$oFld->value2=strtr($_POST[$post_name],',','.');
				elseif(empty($_POST[$post_name]))
					$oFld->value2=$oFld->emptyval;
				else$oFld->value2=$_POST[$post_name];
		}
	}
	function bindGetVals($QRY){
		foreach($QRY->arrFields as $oFld){
			$get_name="frm_$oFld->name";
			if(isset($_GET[$get_name]))
				if($oFld->char=="S")
					$oFld->value=substr($_GET[$get_name],0,$oFld->length);
				elseif($oFld->char=="N")
					$oFld->value=strtr($_GET[$get_name],',','.');
				elseif(empty($_GET[$get_name]))
					$oFld->value=$oFld->emptyval;
				else$oFld->value=$_GET[$get_name];

			$get_name="frm2_$oFld->name";
			if(isset($_GET[$get_name]))
				if($oFld->char=="S")
					$oFld->value2=substr($_GET[$get_name],0,$oFld->length);
				elseif($oFld->char=="N")
					$oFld->value2=strtr($_GET[$get_name],',','.');
				elseif(empty($_GET[$get_name]))
					$oFld->value2=$oFld->emptyval;
				else$oFld->value2=$_GET[$get_name];
		}
	}
	function defValid($QRY){
		if($this->islem=="upd" && !$QRY->keyChg && $QRY->id!=$QRY->keyQry->value){
			$this->msg.="Key field cannot be changed ({$QRY->id}->{$QRY->keyQry->value})<br>";
			$QRY->keyQry->value=$QRY->id;
			return false;
		}
		if(!$this->recValid($QRY))return false;
		return true;
	}
	function recValid($QRY){return true;}

	function insValid(){return true;}
	function edtValid(){return true;}
	function updValid(){return true;}
	function delValid(){return true;}

	function afterOpen(){}
	function beforePost(){
		if(isset($this->qry->fld_abc,$this->qry->fld_atarih,$this->qry->fld_ctarih)){
			if(!empty($this->qry->rec_abc) && !strpos(",,A,B,C,?,",",".trim($this->qry->rec_abc).","))return;
			if    (empty($this->qry->rec_atarih)) $this->qry->rec_abc="?";
			elseif(empty($this->qry->rec_ctarih)) $this->qry->rec_abc="A";
			else{
				$valA=date_format(date_create($this->qry->rec_atarih),"Y-m-d");
				$valC=date_format(date_create($this->qry->rec_ctarih),"Y-m-d");
				if($valA > date("Y-m-d")) $this->qry->rec_abc="B";
				elseif($valC < date("Y-m-d")) $this->qry->rec_abc="C";
				elseif($valC >=date("Y-m-d")) $this->qry->rec_abc="A";
			}
		}elseif(isset($this->qry->fld_abc,$this->qry->fld_atarih) && !isset($this->qry->fld_ctarih)){
			$valA=date_format(date_create($this->qry->rec_atarih),"Y-m-d");
			if($valA > date("Y-m-d")) $this->qry->rec_abc="B";
			elseif($valA < date("Y-m-d")) $this->qry->rec_abc="C";
			elseif($valA == date("Y-m-d")) $this->qry->rec_abc="A";
		}
	}
	function afterPost(){}

	function beforeInsert(){$this->beforePost();}
	function beforeUpdate(){$this->beforePost();}

	function afterInsert(){$this->afterPost();}
	function afterUpdate(){$this->afterPost();}

	function beforeDelete(){}
	function afterDelete(){}

	function createCalFlds(){}
	function bindCalFlds(){
		foreach($this->arrCals as $name=>$value)if(isset($_POST["cal_$name"]))$this->arrCals[$name]=$_POST["cal_$name"];
	}

	function detay_createDetay($objForm,$QRY){
		foreach($this->arrForm as $objForm)if($objForm->type=="DETAY"){
			$oDet=$this->createDetay($objForm,$this->qry,false);
			if(!$oDet)continue;
			$oDet->create_qry($strClass);
			$oDet->setWhere($oDet->qry,$this->senaryo->filtvalues,"U");
		}
	}
	function detay_formupd($oDet=null){
		if(is_object($oDet))$arrDet[]=$oDet;
		else$arrDet=$this->arrDetay;
		foreach($arrDet as $oDet){
			$oDet->setQryUpdates();
		}
	}
	function detay_setQryUpdates($oDet=null){
		if(is_object($oDet))$arrDet[]=$oDet;
		else$arrDet=$this->arrDetay;
		foreach($arrDet as $oDet)$oDet->setQryUpdates();
	}

	function formnew(){
		$def=(isset($_GET["def"])?$_GET["def"]:null);
		$this->bindDefaults($this->qry,$this->senaryo->defvalues.(empty($def)?"":"\n$def"));
		$this->qry->setReqFld($this->senaryo->reqfld);
		$this->qry->setReadFld($this->senaryo->readfld);
		$this->createCalFlds();
		$this->form();
	}
	function formcpy(){
		if(!$this->foundKey())return;

		$this->qry->id=(isset($_GET["id"])?$_GET["id"]:null);
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->qry->keyOpen($this->qry->id);
		if(!$this->foundRec())return;

		$this->qry->keyQry->value=$this->qry->keyQry->emptyval;
		$this->bindDefaults($this->qry,$this->senaryo->defvalues);
		$this->qry->setReqFld($this->senaryo->reqfld);
		$this->qry->setReadFld($this->senaryo->readfld);
		$this->islem="new";
		$this->createCalFlds();
		$this->form();
	}
	function formsnl(){
		global $oUser, $oPerso, $oSirket;

		$tabSayfa="sayfa_$this->dil";
		$id =isset($_GET["id"]) ? $_GET["id"]  : 0;	$id =empty($id)  ? 0 : $id;
		$sen=isset($_GET["sen"])? $_GET["sen"] : 0; $sen=empty($sen) ? 0 : $sen;

		$strDefs="";
		$qMain=null;
		if(!empty($sen)){
			$sqlStr="select sen.gridtemp defvalues, main.sqlstr, main.updtables
					from asist.$tabSayfa sen, asist.$tabSayfa main
					where sen.id=$sen and sen.mainsen=main.id";
			$qCCC = new clsApp($this->appLink, $sqlStr);
			$qCCC->open();
			$qMain = $this->qry->derive_qry($qCCC->rec_sqlstr,null,true);
			$qMain->open("1=0");
			$qMain->setKeyQry($qCCC->rec_updtables);
			$qMain->keyOpen($id);
			$strDefs="\n$qCCC->rec_defvalues";
			$this->qry->main=$qMain;
		}
		$this->bindDefaults($this->qry,$this->senaryo->defvalues.$strDefs);
		$this->qry->setReqFld($this->senaryo->reqfld);
		$this->qry->setReadFld($this->senaryo->readfld);
		$this->createCalFlds();
		$this->form();
	}

	function formins(){
		if(!$this->foundKey())return;

		$tic=isset($_GET["tic"])?$_GET["tic"]:"";
		if(!$this->existsTic("ins",$tic)){
			if(isset($_SESSION["sen_id_{$this->senaryo->id}"])){
				$this->islem="edt";
				$this->formedt($_SESSION["sen_id_{$this->senaryo->id}"]);
				return;
			}
			$this->strMessage="Insert ticket does not exist";
			$str="<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"".
				 "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
			$this->usrMessage($str);
			return;
		}
		if(!$this->insValid())return;

		$this->setQryUpdates();
		$this->bindPostVals($this->qry);
		$this->bindDefaults($this->qry,$this->senaryo->defvalues,"I");
		$this->beforeInsert();

		if(!$this->defValid($this->qry)){
			$this->islem="new";
			$this->form();
			return;
		}

		if(!$this->qry->insert()){
			$this->msg=$this->qry->msg."<br>";
			$this->islem="new";
			$this->form();
			return;
		}
		$this->afterInsert();

		$this->qry->id=$this->qry->keyRec;
		$this->dropTic($tic,$this->qry->id);
		$this->ins_upd_del_message();
	}
	function formedt($id=null){
		if(!$this->foundKey())return;

		$this->qry->id=empty($id) ? (isset($_GET["id"])?$_GET["id"]:null) : $id;
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"U");
		$this->qry->keyOpen($this->qry->id);
		if(!$this->foundRec())return;
		if(!$this->edtValid())return;

		$this->qry->id=$this->qry->keyRec;
		$this->afterOpen();
		$this->qry->setReqFld($this->senaryo->reqfld);
		$this->qry->setReadFld($this->senaryo->readfld);
		$this->createCalFlds();
		$this->form();
	}
	function formupd($id=null){
		if(!$this->foundKey())return;

		$tic=isset($_GET["tic"])?$_GET["tic"]:"";
		if(!$this->existsTic("upd",$tic)){
			if(isset($_SESSION["sen_id_{$this->senaryo->id}"])){
				$this->islem="edt";
				$this->formedt($_SESSION["sen_id_{$this->senaryo->id}"]);
				return;
			}
			$this->strMessage="Update ticket does not exist";
			$str="<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"".
				 "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
			$this->usrMessage($str);
			return;
		}

		$this->qry->id=empty($id) ? (isset($_GET["id"])?$_GET["id"]:null) : $id;
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"U");
		$this->qry->keyOpen($this->qry->id);
		if(!$this->foundRec())return;
		if(!$this->updValid())return;

		$this->qry->id=$this->qry->keyRec;
		$this->afterOpen();
		$this->setQryUpdates();
		$this->bindPostVals($this->qry);
		$this->beforeUpdate();

		if(!$this->defValid($this->qry)){
			$this->islem="edt";
			$this->form();
			return;
		}
		
		if(!$this->qry->update()){
			$this->msg=$this->qry->msg."<br>";
			$this->islem="edt";
			$this->form();
			return;
		}

		$this->afterUpdate();
		$this->dropTic($tic,$this->qry->id);
		$this->islem="";
		//$this->qry->dataSeek(0);
		$this->ins_upd_del_message();
	}
	function formdel(){
		$id=empty($id) ? (isset($_GET["id"])?$_GET["id"]:0) : $id;
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"D");
		$this->qry->keyOpen($id);
		if(!$this->delValid())return;

		$this->qry->setUpdates($this->senaryo->updtables);
		$this->beforeDelete();
		if(!$this->qry->delete()){
			$this->strMessage="Delete Error !!!";
			$str="<meta http-equiv=\"refresh\" content=\"10;url=?{$this->senaryo->action}\"".
				 "<a href=\"?{$this->senaryo->action}\">{$this->senaryo->exp}</a>";
			$this->usrMessage($str);
			return;
		}
		$this->afterDelete();
		$this->ins_upd_del_message();
	}

	function formgdt(){
		$senaryo_id="senaryo_".$this->senaryo->id;
		$qrySess=null;
		if (isset($_SESSION[$senaryo_id])) $qrySess=$_SESSION[$senaryo_id];
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"U");
		//$this->qry->query(null,$qrySess);
		$this->formGrid();
	}
	function formgok(){
		$senaryo_id="senaryo_".$this->senaryo->id;
		$qrySess=null;
		if (isset($_SESSION[$senaryo_id])) $qrySess=$_SESSION[$senaryo_id];
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"U");
		//$this->qry->query(null,$qrySess);
		$this->formGrid(true);
	}
	function formgpd(){
		$this->setQryUpdates();
		$reccount=isset($_POST["grd_reccount"]) ? $_POST["grd_reccount"] : 0;
		for ($ii=0; $ii<$reccount; $ii++){
			$post_id="grd_{$ii}_id";
			$id=isset($_POST[$post_id]) ? $_POST[$post_id] : 0;
			$this->qry->close();
			$this->qry->keyOpen($id);
			$this->qry->bindGridPostVals($ii);
			$this->beforeUpdate();
			$this->qry->update();
			$this->afterUpdate();
		}
		$this->formsel();
	}
	function formsrc(){
		$this->form();
	}
	function formara(){
		$strFilt=$this->filtArama($this->qry);
		if(!empty($strFilt)){
			$this->saveFilter($strFilt);
			$this->qry->close();
			$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
			$this->setWhere($this->qry,$strFilt);
			//$this->qry->print_whrs();
			$this->qry->open(null,null);
		}
		$objParam=$this->listpar();
		$this->listele($objParam);
	}

	function formlst(){
		$strFilt=$this->getFilter();
		if (!empty($strFilt)){
			$this->qry->close();
			$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
			$this->setWhere($this->qry,$strFilt);
			$this->qry->open(null,null);
		}else{
			$this->qry->close();
			$this->setWhere($this->qry,$this->senaryo->filtvalues,"sS");
			$this->qry->open(null,null);
		}
		$this->afterOpen();
		$objParam=$this->listpar();
		$this->listele($objParam);
	}
	
	function formsel(){
		if(isset($_GET["brw"])){
			if(isset($_GET["filt"]))$this->saveFilter($_GET["filt"]);
			$this->qry->close();
			$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
			$this->browFilter();
			$this->qry->open(null,null);
		}else{
			$this->removeFilter();
			$this->bindPostVals($this->qry);
			$strFilt=$this->filtQuery($this->qry);
			if(!empty($strFilt)){
				$this->saveFilter($strFilt);
				$this->qry->close();
				$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
				$this->setWhere($this->qry,$strFilt);
				$this->qry->open(null,null);
			}
		}
		$this->afterOpen();
		$objParam=$this->listpar();
		$this->listele($objParam);
	}

	function formbrw(){
		if(isset($_GET["filt"]))$this->saveFilter($_GET["filt"]);
		$this->qry->close();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"S");
		$this->browFilter();
		$this->qry->open(null,null);

		$objParam=$this->listpar();
		$this->listele($objParam);
	}

	function formdef(){
		if($this->senaryo->filtrele==1){
			$this->qry->close();
			$this->setWhere($this->qry,$this->senaryo->filtvalues,"sS");
			$this->qry->open();
			if ($this->qry->reccount==0){
				$this->islem="new";
				$this->formnew();
			}else{
				$this->createCalFlds();
				$this->islem="edt";
				$this->form();
			}
			return;
		}
		$this->removeFilter();
		$this->setWhere($this->qry,$this->senaryo->filtvalues,"sS");
		if(count($this->qry->arrWhere) || count($this->qry->arrParams)){
			$this->qry->close();
			$this->qry->open(null,null);
			$this->afterOpen();
		}
		$objParam=$this->listpar();
		$this->listele($objParam);
	}
	function listpar(){
		global $oUser;

		$nPerso = isset($_GET["perso"])?$_GET["perso"]:$oUser->perso;
		$cperso = $nPerso==$oUser->perso ? $oUser->exp :$this->table_exp("perso", $nPerso);
		if(isset($this->qry->par_perso)) $this->qry->prm_perso=$nPerso;

		$objParam=(object)array();
		$objParam->qry 		= $this->qry;
		$objParam->cLink	= "?{$this->senaryo->action}&islem=sel";
		$objParam->cBaslik	= $this->senaryo->exp;
		$objParam->nSayfa	= 0;

		$objParam->nSayfarec= 0;
		if(($ii=strpos($this->senaryo->snloption,"Say="))!==false){
			$objParam->nSayfarec=substr($this->senaryo->snloption,$ii+4);
			if(isset($_GET["sayfa"]))$objParam->nSayfa=$_GET["sayfa"];else$objParam->nSayfa=1;
		}

		$objParam->cShowFlds= $this->senaryo->listfld;
		$objParam->aLink=$this->linkArr($this);
		return $objParam;
	}
	function linkArr($oSayfa){
		$arrLink=array();
		if($oSayfa->qry->keyQry){
			$name=$oSayfa->qry->keyQry->name;
			$arrLink["$name"]="<a href=\\\"?{$oSayfa->senaryo->action}&islem=edt&id=%$name\\\">%$name</a>";
		}

		if (preg_match_all("/(\w+)\s*:\s*(.+)($|\r|\n)/U",$oSayfa->senaryo->linkvalues,$arr_match,PREG_SET_ORDER))
			foreach($arr_match as $match) $arrLink[$match[1]]=$match[2];
		return $arrLink;
	}
	
	function listele($oParam){
		global $cKare,$oUser,$oPerso;

	    $cLink    = isset($oParam->cLink)	 ? $oParam->cLink    : "";
	    $cBaslik  = isset($oParam->cBaslik)	 ? $oParam->cBaslik  : "";
	    $cGrup    = isset($oParam->cGrup)	 ? $oParam->cGrup    : "";
	    $cShowFlds= isset($oParam->cShowFlds)? $oParam->cShowFlds: "";
		$nSayfa   = isset($oParam->nSayfa)   ? $oParam->nSayfa   : 0;
		$QQ=$oParam->qry;

		$nSayfarec=0;
		$nSayfasay=1;
		if($oParam->nSayfarec>0){
			$nSayfarec=$oParam->nSayfarec;
			$nSayfasay=ceil($QQ->reccount/$nSayfarec);
		}
		$cSayfaStr="#$QQ->reccount&nbsp;&nbsp;";
		if($nSayfasay==1 || $nSayfa<0)$nSayfa=0;elseif($nSayfa>$nSayfasay)$nSayfa=$nSayfasay;
		if($nSayfa>0 && $nSayfasay>1){
			$nRec1=($nSayfa-1)*$nSayfarec+1;
			$nRec2=$nSayfa*$nSayfarec;
			if($nRec2>$QQ->reccount)$nRec2=$QQ->reccount;
			$cSayfaStr="<a href=\"?{$this->senaryo->action}&islem=lst&sayfa=0\">#$QQ->reccount</a>&nbsp; &nbsp;";
			if($nSayfa>1){$nLsayfa=$nSayfa-1; $cSayfaStr.="<a href=\"?{$this->senaryo->action}&islem=lst&sayfa=$nLsayfa\">$nLsayfa</a>&nbsp;&nbsp;";}
			$cSayfaStr.="$nRec1-$nRec2&nbsp;&nbsp;";
			if($nSayfa<$nSayfasay){$nLsayfa=$nSayfa+1; $cSayfaStr.="<a href=\"?{$this->senaryo->action}&islem=lst&sayfa=$nLsayfa\">$nLsayfa</a>&nbsp;&nbsp;";}
		}elseif($nSayfasay>1){
			$cSayfaStr.="<a href=\"?{$this->senaryo->action}&islem=lst&sayfa=1\">1</a>&nbsp;&nbsp;";
		}

		$cFormStr=$this->senaryo->listtemp;
		if(empty($cFormStr)) $cFormStr=$this->defListTemp($QQ,$cShowFlds,$oParam->aLink);
		if(!$this->formObjArr($cFormStr)) return false;
		
		if(!empty($this->senaryo->color)) echo "\n<style>body{background:#{$this->senaryo->color};}</style>\n";
		echo "\n<form id='id_asist_form' action='?{$this->senaryo->action}'></form>\n";

		$renk=null;
		if(isset($QQ->fld_abc)) $renk=$QQ->fld_abc;
		if(isset($QQ->fld_tipi)){
			if(!$renk) $renk=$QQ->fld_tipi;
			elseif($QQ->fld_abc->order < $QQ->fld_tipi->order) $renk=$QQ->fld_abc;
			else $renk=$QQ->fld_tipi;
		}
		if(!$renk) $renk=(object)array("value"=>"");
		$this->createEval();
		$this->headerCol();
		$cls="";
		$nn_eval="\$cls=\$nn++%2+1; ";
		
		$optFrm=$this->findOpt($this->senaryo->snloption);
		if(isset($optFrm->sum))foreach($optFrm->sum as $name)if(isset($QQ->{"fld_$name"})){
			$sum_name="SUM_".strtoupper($name);
			$$sum_name=0;
			$nn_eval.="\$$sum_name+=\$QQ->rec_$name; ";
		}
		if(preg_match("/xls=1/",$this->senaryo->snloption)){
			$xls_f="$cBaslik ".date("Y-m-d").".xls";
			$cSayfaStr="<label class='lnk' onClick=\"tableToExcel(this, '$xls_f', '$xls_f')\">XLS</label> $cSayfaStr";
		}

		foreach ($this->arrEval as $key => $str_eval){
			if($key==="HEADER"){
				if($this->colCount) echo "<tr class='baslik'><td align=right colspan=$this->colCount><br/><div style='float:left'><b>$cBaslik</b></div><div style='float:right'>$cSayfaStr</div></td></tr>\n";
				eval("echo \"".$str_eval."\";");
			}elseif($key==="REPEAT"){
				$nn=0;
				$str_eval="echo \"".$str_eval."\n\";";
				if($nSayfa==0)while($QQ->next()){
					eval($nn_eval);
					eval($str_eval);
				}else{
					$offset=($nSayfa-1)*$nSayfarec-1;
					$QQ->dataSeek($offset);
					while($nn<$nSayfarec && $QQ->next()){
						eval($nn_eval);
						eval($str_eval);
					}
				}
			}else{
				eval("echo \"".$str_eval."\";");
			}
		}
	}

	function hizliLink($action){
		global $cKare, $cList, $oUser;
		if ($this->islem=="new" || $this->islem=="src") return "";

		$relWhr=($action=="..." ? "sen.mainsen=?prm_sayfa" : "sen.action='$action'");

		$tabSayfa="sayfa_$this->dil";
		$sqlStr="select sen.id, sen.exp, sen.action from asist.$tabSayfa sen where sen.tur='hizli' and $relWhr";
		$qCCC = new clsApp($this->appLink, $sqlStr);
		if (isset($qCCC->par_sayfa)) $qCCC->prm_sayfa=$this->senaryo->id;
		$qCCC->open(null,null);

		$retStr="";
		while($qCCC->next()){
			$sqlStr="select senrole.id
					from asist.senrole,asist.role
					where senrole.senaryo=$qCCC->rec_id
						and senrole.role=role.id
						and $oUser->where
						and instr(senrole.options,'+')>0";
			$res=mysqli_query($this->appLink, $sqlStr);
			if(mysqli_num_rows($res))
			$retStr.="<label class='lnk' onClick='modalLink(this)' url='?$qCCC->rec_action&mod=&islem=src&sen=$qCCC->rec_id&id={$this->qry->rec_id}'>$qCCC->rec_exp</label> &nbsp;";
		}
		return $retStr;
	}

	function prasorLink($brk=null){
		global $oUser, $cKare, $cList;
		if ($this->islem=="new" || $this->islem=="src") return "";

		if($oUser->roletest && count($oUser->testroles))$strWhere="(prasor.role=-1 and prasor.user=-1) or prasor.role in ($oUser->whrRoles) or (prasor.user=$oUser->id and prasor.role=-1)";
		else$strWhere="(prasor.role=-1 and prasor.user=-1) or prasor.role in (select role from asist.userole where user=$oUser->id and abc='A') or (prasor.user=$oUser->id and prasor.role=-1)";
		if($oUser->admin){
			if($oUser->roleadmin)$strWhere="($strWhere or role.admin=1)";
			else$strWhere="($strWhere) and role.admin=0";
		}else$strWhere="($strWhere)";

		$tabPrasor="prasor_$this->dil";
		$sqlStr="select prasor.*
				from asist.$tabPrasor prasor, asist.role
				where prasor.role=role.id
					and prasor.senaryo=?prm_senaryo
					and prasor.sira<>0
				order by prasor.sira";
		$qCCC=new clsApp($this->appLink, $sqlStr);
		$qCCC->prm_senaryo=$this->senaryo->id;
		$qCCC->open($strWhere,false);
		if ($qCCC->reccount==0) return "";

		$retStr="";
		while($qCCC->next()){
			if (!empty($qCCC->rec_sqlstr)){
				$oDB=connect_datab($qCCC->rec_datab);
				$strClass="cls$oDB->cls";
				$qGGG=new $strClass($oDB->dblink,$qCCC->rec_sqlstr,$this->qry);

				$this->bindParams($qGGG,$qCCC->rec_filtvalues);
				$qGGG->main=null;
				$qGGG->open(null,null);
				
				$this->setWhere($qGGG,$qCCC->rec_filtvalues,"BPC");
				$str_tit="";
				$str_lnk="";
				foreach($qGGG->arrWhere as $whr)if(isset($whr->fld)){
					$val=$this->buildEval($qGGG,$whr->val,"qGGG");
					if($whr->opt=="C:" && $whr->opr=="=")$str_lnk.=" $val";
					if($whr->opt=="B:")$str_tit.="$val ";
				}
				$this->setWhere($this->qry,$qCCC->rec_filtvalues,"BPC");
				$str_brw="";
				foreach($this->qry->arrWhere as $whr)if(isset($whr->fld)){
					$val=$this->buildEval($qGGG,$whr->val,"qGGG");
					if($whr->opt=="P:")$str_brw.="{$whr->fld->name}$whr->opr$val;";
				}else{
					$val=$this->buildEval($qGGG,$whr->val,"qGGG");
					if($whr->opt=="P:")$str_brw.="{$whr->fromfld}$whr->opr$val;";
				}
				if(empty($str_lnk))
				foreach($qGGG->arrFields as $oFld)
				if (!strpos(",kayit_adet,abc,","$oFld->name,")){
					$str_brw.="$oFld->name=\$qGGG->rec_$oFld->name;";
					$str_lnk.=" \$qGGG->rec_$oFld->name";
				}
				if(!empty($str_tit))$str_brw.="&listitle=$str_tit";
				if(isset($qGGG->fld_kayit_adet))$str_lnk.=" (\$qGGG->rec_kayit_adet)";
				$str_lnk="return \"$str_lnk\";";
				if (isset($qGGG->fld_abc))
					while($qGGG->next()){
						$ret2=eval($str_lnk);
						$rBrw=eval("return \"$str_brw\";");
						$retStr.=" | <label class='lnk abc_$qGGG->rec_abc' onClick='openLink(this)' url='?{$this->senaryo->action}&islem=sel&brw=$rBrw'>$ret2</label>\n";
					}
					else
					while($qGGG->next()){
						$ret2=eval($str_lnk);
						$rBrw=eval("return \"$str_brw\";");
						$retStr.=" | <label class='lnk' onClick='openLink(this)' url='?{$this->senaryo->action}&islem=sel&brw=$rBrw'>$ret2</label>\n";
					}
			}else
			$retStr.=" | <label class='lnk abc_$qCCC->rec_abc' onClick='openLink(this)' url='?{$this->senaryo->action}&islem=sel&brw=&prs=$qCCC->rec_id'>$qCCC->rec_exp</label>\n";
		}
		if(empty($retStr)) return "";
		return ($brk?"<br><br>":"")."$cKare ".substr($retStr,3);
	}

	function browFilter(){
		$strFilt=$this->getFilter();
		if(!empty($strFilt))$this->setWhere($this->qry,$strFilt);
		if(isset($_GET["prs"]))$this->prasorFilter();
		if(isset($_GET["brw"]))$this->setWhere($this->qry,$_GET["brw"]);
	}
	function prasorFilter(){
		if($this->islem=="new" || $this->islem=="src") return;
		$prs=isset($_GET["prs"]) ? $_GET["prs"] : 0;

		$tabPrasor="prasor_$this->dil";
		$sqlStr="select id, filtvalues from asist.$tabPrasor where id=?prm_id";
		$qCCC=new clsApp($this->appLink, $sqlStr);
		$qCCC->prm_id=$prs;
		$qCCC->open();
		$this->setWhere($this->qry,$qCCC->rec_filtvalues,"P");
	}
	
	function form(){
		global $cKare, $cList, $oUser, $oPerso;

		$id="";
		$tic="";
		if ($this->islem=="new" || $this->islem=="snl"){
			if(!empty($this->senaryo->newtemp))$cFormStr=$this->senaryo->newtemp;
			elseif(empty($this->senaryo->formtemp)) $cFormStr=$this->defFormTemp($this->qry);
			else $cFormStr=$this->senaryo->formtemp;
			$cActislem="ins";
			$tic="&tic=".$this->createTic($cActislem);
		}elseif ($this->islem=="edt"){
			$this->setEdit($this->qry,$this->senaryo->filtvalues);
			if(empty($this->senaryo->formtemp)) $cFormStr=$this->defFormTemp($this->qry);
			else $cFormStr=$this->senaryo->formtemp;
			$cActislem="upd";
			$id="&id={$this->qry->id}";
			$tic="&tic=".$this->createTic($cActislem);
		}else{
			if(empty($this->senaryo->findtemp)) $cFormStr=$this->defFindTemp($this->qry);
			else $cFormStr=$this->senaryo->findtemp;
			$cActislem="sel";
		}
		if (!$this->formObjArr($cFormStr))return false;
		if (!empty($this->senaryo->color))echo"\n<style>body{background:#{$this->senaryo->color};}</style>\n";
		$this->createEval();

		if(!empty($this->msg))echo"<font color='red'>$this->msg</font>\n";
		$this->pre="";
		$QQ=$this->qry;
		echo "\n<form enctype='multipart/form-data' name='asist_form' id='id_asist_form' method='post' action='?{$this->senaryo->action}",$this->mod?"&mod=":"","&islem=$cActislem$id$tic$this->par' islem='$cActislem' target='_self' onReset='return ResetForm(this);'>\n";
		foreach($this->arrEval as $str_eval) eval("echo \"".$str_eval."\";");
		echo "</form>\n";
	}

	function gridHeader(){
		echo "<tr class='d0'>";
		$this->rowline="echo \"<tr class=\\\"d\$cls\\\">";
		foreach($this->arrRepeat as $objRep)
		if (strpos(",DATE,HOUR,TXT,TID,EDT,YXT,CHK,RAD,","$objRep->type,")){
			$rec_fld=$this->qry->fieldByName($objRep->name);
			if ($rec_fld){
				$fld_name=$rec_fld->name;
				$this->rowline.='<td>$QQ->rec_'.$rec_fld->name.'</td> ';
			}else{
				$fld_name=$objRep->name;
				$this->rowline.='<td>'.$fld_name.'</td> ';
			}
			echo "<td>$fld_name</td>";
		}
		echo "</tr>\n";
	    $this->rowline.='</tr>\n";';
	}

	function formGrid($ok=null){
		global $cKare, $cList;

		if		($this->islem=="new"){$cFormStr=$this->senaryo->formtemp; $cActislem="ins";}
		elseif	($this->islem=="gdt" || $this->islem=="gok"){$cFormStr=$this->senaryo->gridtemp; $cActislem="gpd";}
		else						 {$cFormStr=$this->senaryo->findtemp; $cActislem="sel";}

		if (empty($cFormStr))
		if		($this->islem=="new"){$cFormStr=$this->defFormTemp($this->qry); $cActislem="ins";}
		elseif	($this->islem=="gdt" || $this->islem=="gok"){$cFormStr=$this->defGridTemp($this->qry); $cActislem="gpd";}
		else						 {$cFormStr=$this->defFindTemp($this->qry); $cActislem="sel";}
		
		if (!$this->formObjArr($cFormStr)) return false;
		if (!empty($this->senaryo->color)) echo "\n<style>body{background:#{$this->senaryo->color};}</style>\n";
		if ($ok){
			array_unshift($this->arrRepeat,(object)array("type"=>"C"));
			array_unshift($this->arrRepeat,(object)array("type"=>"CHK","name"=>"ok"));
		}
		$renk=null;
		if (isset($this->qry->fld_abc)) $renk=$this->qry->fld_abc;
		if (isset($this->qry->fld_tipi)){
			if (!$renk) $renk=$this->qry->fld_tipi;
			elseif ($this->qry->fld_abc->order < $this->qry->fld_tipi->order) $renk=$this->qry->fld_abc;
			else $renk=$this->qry->fld_tipi;
		}
		if (!$renk) $renk=(object)array("value"=>"");
		$this->createEval();

		$this->pre="";
		$QQ=$this->qry;
		echo "\n<form name='form1' id='id_form_form1' method='post' action='?{$this->senaryo->action}",$this->mod?"&mod=":"","&islem=$cActislem' islem='$cActislem' onReset='return ResetForm(this);'>\n";
		foreach ($this->arrEval as $key => $str_eval){
			if ($key==="REPEAT"){
				$nn=0;
				$str_eval="echo \"".$str_eval."\n\";";
				while($this->qry->next()){
					$this->pre="grd_{$nn}_";
					$cls = $nn++%2+1;
					eval($str_eval);
				}
			}else{
				eval("echo \"".$str_eval."\";");
			}
		}
		echo "</form>\n";
	}

	function filtQuery($QRY){
		$strFilter="";
		foreach($QRY->arrFields as $oFld)
		if (is_null($oFld->filter) && !empty($oFld->value)){
			if(preg_match_all("/(.+)(;|\r|\n|$)/U",$oFld->value,$arr_fld,PREG_SET_ORDER))
			foreach($arr_fld as $fld){
				if(!preg_match("/^\s*(R:)?(==|!=|<>|<=|>=|>>|=|<|>|~)?(.+)$/",$fld[1],$match))continue;
				$val=trim($match[3]);
				if(empty($val))continue;
				$opr=empty($match[2]) ? "=" : $match[2];
				$opr=$oFld->like && $opr=="=" ? "~" : $opr;
				if(!empty($oFld->value2))
					$strFilter.=$match[1]."$oFld->name>>$val,$oFld->value2\n";
				else
					$strFilter.=$match[1]."$oFld->name$opr$val\n";
			}
		}
		return $strFilter;
	}
	function filtArama($QRY){
		$strFilter="";
		$aratxt=isset($_GET['aratxt'])?$_GET['aratxt']:"";
		$oDef=$QRY->fieldByName(empty($this->senaryo->deffld)?"exp":trim($this->senaryo->deffld));
		if(preg_match_all("/(.+)(;|\r|\n|$)/U",$aratxt,$arr_fld,PREG_SET_ORDER))
		foreach($arr_fld as $fld){
			if(!preg_match("/^\s*((R:)?(\w+)?\s*(==|!=|<>|<=|>=|>>|=|<|>|~))?(.+)$/",$fld[1],$match))continue;
			$val=trim($match[5]);
			$oFld=empty($match[3])?$oDef:$QRY->fieldByName($match[3]);
			if(!$oFld || empty($val))continue;
			$opr=empty($match[4]) ? "=" : $match[4];
			$opr=$oFld->like && $opr=="=" ? "~" : $opr;
			if($opr=="~" && empty($match[3]) && preg_match("/^(\w+\_)?(exp)$/",$oFld->name,$mt)){
				$mf=$QRY->fieldByName($mt[1]."indexp");
				if($mf)$oFld=$mf;
			}
			if($opr=="~" && preg_match("/^(\w+\_)?indexp$/",$oFld->name))$val=$QRY->to_Indexp($val);
			$strFilter.=$match[2].$oFld->name.$opr.$val."\n";
		}
		//echo $strFilter,"<br>";
		return $strFilter;
	}

	function saveFilter($strFilter){
		$filter_id="filter_".$this->senaryo->id;
		$_SESSION[$filter_id]=$strFilter;
	}
	function getFilter(){
		$filter_id="filter_".$this->senaryo->id;
		if(isset($_SESSION[$filter_id]))return $_SESSION[$filter_id];
		else return "";
	}
	function removeFilter(){
		$filter_id="filter_".$this->senaryo->id;
		unset($_SESSION[$filter_id]);
	}
	function denkUpdate($denk_tab){
		$denk_tab=strtolower($denk_tab);
		$orgtable="{$denk_tab}_$this->dil";
		foreach($this->qry->arrUpdates as $oUpd)if($oUpd->denk==$denk_tab)break;
		if(!isset($oUpd) || $oUpd->oFrom->orgtable!=$orgtable)return;
		$arrDenkObj=$this->denkObjArr($denk_tab);
		if(isset($arrDenkObj[$orgtable]))$arrDenkObj[$orgtable]->orgtab=true;

		$datPre=empty($oUpd->oFrom->datab)?"":$oUpd->oFrom->datab.$oUpd->oFrom->fro_s;
		foreach($arrDenkObj as $denktable=>$oDenk){
			if(isset($oDenk->orgtab))continue;
			if($oUpd->keyName!="id")$opts=",key=$oUpd->keyName";else$opts="";
			if($this->islem!="ins"){
				$flds=$this->denkFlds($oDenk);
				if(empty($flds))return;
				$opts.=",fld=$flds";
			}
			$id=$oUpd->keyFld->value;
			if($oUpd->setId){$opts.=",chkey=1";$id=$this->qry->id;}
			if($opts)$opts=":".substr($opts,1);
			$dTab=$this->qry->derive_tab("$datPre$denktable$opts",$id);

			if($this->islem!="ins" && !$dTab->reccount)continue;
			$oDenk->dTab=$dTab;
			$oDenk->oUpd=$oUpd;
			if($oUpd->setId)$dTab->id=$this->qry->id;
			$this->denkBind($oDenk);
			if($this->islem=="ins")$dTab->insert();
			else				   $dTab->update();
		}
	}
	function denkObjArr($denk_tab){
		global $arrDil;
		$arrDenkObj=array();
		$sqlStr="select * from denk where exp='$denk_tab'";
		$qDenk=new clsApp($this->appLink, $sqlStr);
		$qDenk->open();
		if($qDenk->reccount)foreach($arrDil as $dil=>$val){
			if(!preg_match("/$dil\s*=\s*({$denk_tab}_$dil)(;|\r|\n|$)/iU",$qDenk->rec_tabs,$match))continue;
			$denktable=strtolower($match[1]);
			$arrDenkObj[$denktable]=(object)array("org"=>$this->dil,"dil"=>$dil,"tab"=>$denktable,"flds"=>$qDenk->rec_flds);
		}
		return $arrDenkObj;
	}
	function denkFlds($oDenk){
		$flds="";
		if(preg_match_all("/(-)?(\w+)(,|\r|\n|$)/U",$oDenk->flds,$arr_flds,PREG_SET_ORDER))
			foreach($arr_flds as $match)$flds.=" $match[1]$match[2]";
		return $flds;
	}
	function denkBind($oDenk){
		foreach($oDenk->oUpd->arrFields as $uFld){
			$fld_name="fld_$uFld->name";
			if(isset($oDenk->dTab->$fld_name)) $oDenk->dTab->$fld_name->value=$uFld->value;
		}
	}
	function denkTabArr(){
		global $arrDil;
		$arrDenk=array();
		$sqlStr="select exp,tabs from denk where aktif=1";
		$qDenk=new clsApp($this->appLink, $sqlStr);
		$qDenk->open(null,null);
		while($qDenk->next()){
			if(!preg_match("/$this->dil\s*=\s*({$qDenk->rec_exp}_$this->dil)(;|\r|\n|$)/iU",$qDenk->rec_tabs,$match))continue;
			$orgtable=strtolower($match[1]);
			foreach($arrDil as $dil=>$val){
				if(!preg_match("/$dil\s*=\s*({$qDenk->rec_exp}_$dil)(;|\r|\n|$)/iU",$qDenk->rec_tabs,$match))continue;
				$denktable=strtolower($match[1]);
				if($orgtable==$denktable)continue;
				$arrDenk[$denktable]=(object)array("orgtab"=>$orgtable,"tab"=>$denktable);
			}
		}
		return $arrDenk;
	}
	function denkReplace(&$arrDenk,&$denkStr){
		//echo $denkStr,"- ";
		foreach($arrDenk as $oObj){
			//echo "$oObj->orgtab --> $oObj->tab<br>";
			$denkStr=preg_replace("/(\Woner_)$oObj->orgtab(\W|$)/iU","\${1}$oObj->tab\$2",$denkStr);
			$denkStr=preg_replace("/(\W)$oObj->orgtab(\W|$)/iU","\${1}$oObj->tab\$2",$denkStr);
		}
	}
}
?>