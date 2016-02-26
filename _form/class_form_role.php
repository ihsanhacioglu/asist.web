
<?php
class class_form_role extends class_form{
	public $ROLESTR="";

	function createCalFlds(){
		$this->arrCals=array();
		$this->arrCals["senroleins"]=0;

		$this->ROLESTR="";

		$strSql="select role.id,role.exp,count(*) adet
				from asist.userole,asist.role
				where userole.role=role.id
					and userole.abc='A'
				group by 1,2";
		$qCCC=new clsApp($this->appLink, $strSql);
		$qCCC->open(null,null);
		
		$nn=0;
		$retStr="";
		while($qCCC->next()){
			$lbl_name="label".$nn++;
			$txt_name="label$qCCC->rec_id";
			$retStr.="<input name='cal_$txt_name' type='hidden' id='id_$lbl_name' value='0'/><input class='chk' name='chk_$lbl_name' type='checkbox' title='cal_$txt_name' onKeyDown='sonra(this,event)' onClick='id_$lbl_name.value=(this.checked?1:0)'/>";
			$retStr.="<label class='lbl' style='color:red'>$qCCC->rec_exp ($qCCC->rec_adet)</label><br/>\n";
			$this->arrCals[$txt_name]=0;
		}
		$this->ROLESTR=$retStr;
	}

	function insertSenrole(){
		return;
		$role_id=$this->qry->id;
		$tabSayfa="sayfa_$this->dil";
		$qCCC=new clsApp($this->appLink, "select tur from asist.$tabSayfa where id=$senaryo_id");
		$qCCC->open();
		if(strpos(" ,snltek,snlist,snlfld,",",$qCCC->rec_tur,")) return;

		$sqlStr="insert into asist.senrole(ktarih,senaryo,role)
				select CURRENT_DATE,senrole.senaryo,$role_id
				from asist.senrole, asist.role
				where senrole.id={$this->qry->rec_id} and role.id not in (select role from asist.senrole where senaryo=$senaryo_id)";
		$res=mysqli_query($this->appLink,$sqlStr);
	}

	function insertUserole(){
		$strSql="select distinct role from asist.userole where abc='A'";
		$qCCC=new clsApp($this->appLink, $strSql);
		$qCCC->open(null,null);
		
		$urlStr="";
		while($qCCC->next()){
			$txt_name="label$qCCC->rec_role";
			if(isset($this->arrCals[$txt_name]) && $this->arrCals[$txt_name]==1) $urlStr.=",$qCCC->rec_role";
		}
		if(empty($urlStr)) return;
		$urlStr=substr($urlStr,1);
		
		$strSql="select distinct user
				from asist.userole
				where abc='A' and role in ($urlStr)
					and user not in (select user from asist.userole where role={$this->qry->rec_id} and abc='A')";
		$qCCC=new clsApp($this->appLink, $strSql);
		$qCCC->open(null,null);
	
		$tUrl=$this->qry->derive_tab("userole:auto=1",-1);
		$tUrl->rec_role=$this->qry->rec_id;
		$tUrl->rec_atarih=$this->objVal("ozaman","bugun");
		$tUrl->rec_abc='A';
		while($qCCC->next()){
			$tUrl->rec_user=$qCCC->rec_user;
			$tUrl->rec_id=0;
			$tUrl->insert();
		}
	}

	function beforePost(){
		$this->createCalFlds();
		$this->bindCalFlds();
	}
	function afterPost(){
		if ($this->arrCals["senroleins"]==1) $this->insertSenrole();
		$this->insertUserole();
	}
}
?>
