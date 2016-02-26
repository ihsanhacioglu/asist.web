
<?php
class class_form_user extends class_form{
	function insertAdmin(){
		$user_id=$this->qry->rec_id;
		$sqlStr="insert into asist.userole(user,role,atarih,abc)
				select $user_id,id,CURRENT_DATE,'A'
				from asist.role
				where admin=1
					and id not in (select role from asist.userole where user=$user_id and abc='A')";
		$res=mysqli_query($this->appLink,$sqlStr);
	}
	function afterPost(){
		return;
		if($this->qry->rec_admin)$this->insertAdmin();
	}
}
?>
