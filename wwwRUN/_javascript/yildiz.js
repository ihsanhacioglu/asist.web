
function Utils(objSuggest){
	this.sug = objSuggest;
}

Utils.prototype.GenerateID=function(obj){
	dt = new Date();
	obj.id = "GenID" + dt.getTime();
	return obj
}
	
Utils.prototype.FindIn=function(str, fragment, sug){
	if(!fragment || fragment.length <= 0) return -1;
	if (!sug.isCaseSensitive){
		str=str.toLowerCase();
		fragment=fragment.toLowerCase();
	}
	kk=str.indexOf(fragment);
	if(sug.matchFromStart && kk==0 ) return kk;
	else if(!sug.matchFromStart && kk!=-1) return kk;
	else return -1;
}
	
Utils.prototype.CaseSensitize=function(str,sug){
	return sug.isCaseSensitive ? str : str.toLowerCase();
}

Utils.prototype.GetXY=function(obj){
	var x=0;
	var y=0;
	while(obj.offsetParent){
		x+=obj.offsetLeft;
		y+=obj.offsetTop;
		obj = obj.offsetParent;
	}
	return [x,y]
}
	
Utils.prototype.PartialSelect=function(inputObj, startIdx, endIdx){
	if(inputObj.createTextRange){
		var fld= inputObj.createTextRange();
		fld.moveStart("character", startIdx);
		fld.moveEnd("character", endIdx - inputObj.value.length);
		fld.select();
	}else if(inputObj.setSelectionRange){
		inputObj.setSelectionRange(startIdx, endIdx);
	}
}


function yildiz(inputObj,butId){
	this.inputObj = inputObj;
	//this.butId=butId;

	this.id = "";
	this.FieldID = null;
	this.OutputAreaVisible = false;
	this.InputValueBackup = null;
	this.ResultSet = [];
	this.ResultSetIndex = -1;
	this.li_Index = -1;
	this.OutputArea = null;

	this.OutputAreaID="SuggestOutputArea";
	this.InputValueBackup="";

	this.isCaseSensitive = false;
	this.MatchFromStart = true;
	this.SuggestMinLength = 2;
	this.ResultLimit = 15;
	this.BrowserAutoComplete = false;
	this.autocomplete = "off";
	this.IEForceRelative = true;

	this.utils=new Utils(this);
	this.idField=document.getElementById(this.inputObj.getAttribute("idfield"));
	//if (typeof(this.butId) != 'string') this.but = this.butId;
	//else this.but = document.getElementById(this.butId);
	
	// if (this.but){
		// this.but.sug=this;
		// this.but.onclick = function(){
			// if (this.getAttribute("readonly")=='1') return;
			// var self=this.sug;
			// if (!self.inputObj.dic && self.ResultSet.length == 0) return;
			
			// if (self.inputObj.dic){
				// if (self.inputObj.dic.length==0){
					// if (self.inputObj.getAttribute("param")==undefined) return;
					// var onerparam=self.inputObj.getAttribute("param");
					// if((arrPar=onerparam.match(/&(p\d+)=(\w+)/g)))for(ii=0; ii<arrPar.length; ii++){
						// par=arrPar[ii].match(/&(p\d+)=(\w+)/);
						// if((parFld=document.getElementById(par[2])))
							// onerparam=onerparam.replace(par[0],"&"+par[1]+"="+parFld.value);
					// }
					// currentListIndex++;
					// var tmpIndex=currentListIndex/1;
					// var ajaxIndex = ajax_list_objects.length;
					// ajax_list_objects[ajaxIndex] = new sack();
					// var url = ajax_list_externalFile + '?' + onerparam + '&mask=%';
					// ajax_list_objects[ajaxIndex].requestFile = url;
					// var inputObj=self.inputObj;
					// ajax_list_objects[ajaxIndex].onCompletion = function(){self.LoadDic(ajaxIndex,inputObj,onerparam,tmpIndex); };
					// ajax_list_objects[ajaxIndex].runAJAX();
				// }else{
					// if (self.ResultSet.length!=self.inputObj.dic.length){
						// self.ResultSet.length = 0;
						// for(var ii=0; ii<self.inputObj.dic.length; ii++)
							// self.ResultSet[self.ResultSet.length]=self.inputObj.dic[ii];
					// }
					// self.ContentRender(self.inputObj);
				// }
			// }
		// }
	// }
	inputObj.onfocus   = yildiz_Run;
	inputObj.onkeyup   = yildiz_KeyHandler;
	inputObj.onkeydown = yildiz_KeyHandler;
	inputObj.onblur    = yildiz_Stop;
	inputObj.onclick   = yildiz_Click;
	inputObj.ondblclick= yildiz_Dblclick;
}

li_MouseOver=function(){
	var self=this.parentNode.sug;
	if (self.li_Index != parseInt(this.id) && self.li_Index != -1) this.parentNode.childNodes[self.li_Index].className = "";
	this.className = "selected";
	self.li_Index = parseInt(this.id);
	self.ResultSetIndex=self.li_Index
	self.inputObj.value = self.ResultSet[self.li_Index][1];
}

li_MouseOut=function(){
	this.className = "";
}

li_MouseDown=function(){
	var self=this.parentNode.sug;
	self.li_Index = parseInt(this.id);
	self.ResultSetIndex=self.li_Index
	self.inputObj.value=self.ResultSet[self.ResultSetIndex][1];
	if(self.idField) self.idField.value=self.ResultSet[self.ResultSetIndex][0];
	for (ii=2; self.inputObj.getAttribute("id"+ii); ii++)
		document.getElementById(self.inputObj.getAttribute("id"+ii)).value=self.ResultSet[self.ResultSetIndex][ii];
	self.inputObj.focus();
}

yildiz_Click=function(e){
	e = e || window.event;
	if(!e.ctrlKey) return;
	var self=this.sug;
	if (!self.idField || !this.name.match(/frm_(\w+)_/)) return;
	
	var action="?form_"+(this.getAttribute("action") || RegExp.$1);
	var url=action+"&mod=&islem="+(self.idField.value=="-1" ? "new" : "edt&id="+self.idField.value);
	this.setAttribute("url", url);
	modalLink(this);
}
yildiz_Dblclick=function(e){
	e = e || window.event;
	if (this.getAttribute("readonly")=='1') return;
	var self=this.sug;
	if(!self.inputObj.dic){
		window.alert("Enter \"..\" or any two characters that the field may contain.");
		return;
	}

	if(self.inputObj.dic.length==0){
		if (self.inputObj.getAttribute("param")==undefined) return;
		var onerparam=self.inputObj.getAttribute("param");
		if((arrPar=onerparam.match(/&(p\d+)=(\w+)/g)))for(ii=0; ii<arrPar.length; ii++){
			par=arrPar[ii].match(/&(p\d+)=(\w+)/);
			if((parFld=document.getElementById(par[2])))
				onerparam=onerparam.replace(par[0],"&"+par[1]+"="+parFld.value);
		}
		currentListIndex++;
		var tmpIndex=currentListIndex/1;
		var ajaxIndex = ajax_list_objects.length;
		ajax_list_objects[ajaxIndex] = new sack();
		var url = ajax_list_externalFile + '?' + onerparam + '&mask=%';
		ajax_list_objects[ajaxIndex].requestFile = url;
		var inputObj=self.inputObj;
		ajax_list_objects[ajaxIndex].onCompletion = function(){self.LoadDic(ajaxIndex,inputObj,onerparam,tmpIndex); };
		ajax_list_objects[ajaxIndex].runAJAX();
	}else{
		if (self.ResultSet.length!=self.inputObj.dic.length){
			self.ResultSet.length = 0;
			for(var ii=0; ii<self.inputObj.dic.length; ii++)
				self.ResultSet[self.ResultSet.length]=self.inputObj.dic[ii];
		}
		self.ContentRender(self.inputObj);
	}
}

yildiz_Run = function(){
	this.sug.FieldID = this.id;
}
	
yildiz_Stop = function(){
	this.sug.HideOutputArea();
	this.sug.InputValueBackup = null;
	this.sug.ClearOutputAreaContents();
}
	
yildiz_KeyHandler=function(e){
	e = e || window.event;
	var self=this.sug;

	if (this.getAttribute("readonly")=='1') return;
	if (!self.OutputAreaVisible && e.type=="keydown")
		switch (e.keyCode){
		case 13: self.ValueMessage(this);	nextObj(this);	return false;
		case 40: nextObj(this); return false;
		case 38: prevObj(this); return false;
		}
	//Tab
	if(e.keyCode==9 && e.type=="keyup")return;
	
	//Enter
	else if(e.keyCode==13 && e.type=="keyup"){
		self.InputValueBackup = this.value;
		if (self.ResultSetIndex!=-1){
			this.value=self.ResultSet[self.ResultSetIndex][1];
			if(self.idField) self.idField.value=self.ResultSet[self.ResultSetIndex][0];
			for (ii=2; self.inputObj.getAttribute("id"+ii); ii++)
				document.getElementById(self.inputObj.getAttribute("id"+ii)).value=self.ResultSet[self.ResultSetIndex][ii];
		}
		self.HideOutputArea();
		self.ValueMessage(this);
		return;
	}
	
	//Shift
	else if(e.keyCode==16 && e.type=="keyup")return false;
	
	//Esc
	else if(e.keyCode==27 && e.type=="keyup"){
		self.RevertInputValue(this);
		if(self.idField) self.idField.value=0;
		for (ii=2; self.inputObj.getAttribute("id"+ii); ii++)
			document.getElementById(self.inputObj.getAttribute("id"+ii)).value="";
		self.HideOutputArea();
		return;
	}
	
	//Up
	else if(e.keyCode==38 && e.type=="keydown"){
		loLet=this.value.toLowerCase();
		//inputObj=this;
		//if(this.value.length > 0 && this.sug.ResultCache[loLet].length==0){
		//	this.sug.LookUp(this);
		//	this.sug.Render(this);
		//	this.sug.InputValueBackup = this.value;
		//}
		self.MoveResultSetIndex(-1);
		self.SuggestInputValue(this);
		return true;
	}
	
	//Down
	else if(e.keyCode==40 && e.type=="keydown"){
		loLet=this.value.toLowerCase();
		//inputObj=this;
		//if(this.value.length > 0 && this.sug.ResultCache[loLet].length==0){
		//	this.sug.LookUp(this);
		//	this.sug.Render(this);
		//	this.sug.InputValueBackup = this.value;
		//}
		self.MoveResultSetIndex(1);
		self.SuggestInputValue(this);
		return true;
	}
	//Everything else
	else if(e.type=="keyup" && e.keyCode!=37 && e.keyCode!=38 && e.keyCode!=39 && e.keyCode!=40){
		if (this.dic){
			self.LookUp(this);
			if (self.ResultSet.length>0){
				self.ContentRender(this);
				self.InputValueBackup = this.value;
			}
			return;
		}
		var loLet=this.value.toLowerCase();
		loLet=this.value;
		if (loLet.length<self.SuggestMinLength){
			self.HideOutputArea();
			return;
		}
		var onerparam=this.getAttribute("param");
		if((arrPar=onerparam.match(/&(p\d+)=(\w+)/g)))for(ii=0; ii<arrPar.length; ii++){
			par=arrPar[ii].match(/&(p\d+)=(\w+)/);
			if((parFld=document.getElementById(par[2])))
				onerparam=onerparam.replace(par[0],"&"+par[1]+"="+parFld.value);
		}
		if(!ajax_list_cachedLists[onerparam]) ajax_list_cachedLists[onerparam]=new Array();
		if(ajax_list_cachedLists[onerparam][loLet]){
			self.ResultSet.length = 0;
			for(var no=0; no<ajax_list_cachedLists[onerparam][loLet].length-1; no++){
				if(ajax_list_cachedLists[onerparam][loLet][no].length==0)continue;
				self.ResultSet[self.ResultSet.length] = ajax_list_cachedLists[onerparam][loLet][no].split(/\t/g);
			}
			self.ContentRender(this);
			self.InputValueBackup = this.value;
		}else{
			currentListIndex++;
			var tmpIndex=currentListIndex/1;
			var ajaxIndex = ajax_list_objects.length;
			ajax_list_objects[ajaxIndex] = new sack();
			var url = ajax_list_externalFile + '?' + onerparam + '&mask=' + this.value.replace(" ","+");
			ajax_list_objects[ajaxIndex].requestFile = url;
			var inputObj=this;
			ajax_list_objects[ajaxIndex].onCompletion = function(){self.ShowContent(ajaxIndex,inputObj,onerparam,tmpIndex); };
			ajax_list_objects[ajaxIndex].runAJAX();
		}
		return;
	}
}
yildiz.prototype.ValueMessage=function(inputObj){
	valmsg='valmsg_'+inputObj.id;
	if(typeof window[valmsg]!='function')return true;
	window[valmsg](inputObj);
}

yildiz.prototype.ShowContent=function(ajaxIndex,inputObj,onerparam,whichIndex){
	var self=inputObj.sug;
	if(whichIndex!=currentListIndex)return;
	var loLet = inputObj.value.toLowerCase();
	loLet = inputObj.value;
	ajax_list_cachedLists[onerparam][loLet] = ajax_list_objects[ajaxIndex].response.split(/###/g);

	this.ResultSet.length = 0;
	for(var no=0; no<ajax_list_cachedLists[onerparam][loLet].length-1; no++){
		if(ajax_list_cachedLists[onerparam][loLet][no].length==0)continue;
		this.ResultSet[this.ResultSet.length]=ajax_list_cachedLists[onerparam][loLet][no].split(/\t/g);
	}
	this.ContentRender(inputObj);
	this.InputValueBackup = inputObj.value;
}

yildiz.prototype.LoadDic=function(ajaxIndex,inputObj,onerparam,whichIndex){
	var self=inputObj.sug;
	if(whichIndex!=currentListIndex)return;
	var loLet = "%";
	if(!ajax_list_cachedLists[onerparam]) ajax_list_cachedLists[onerparam]=new Array();
	ajax_list_cachedLists[onerparam][loLet] = ajax_list_objects[ajaxIndex].response.split(/###/g);

	this.ResultSet.length = 0;
	inputObj.dic.length = 0;
	for(var no=0; no<ajax_list_cachedLists[onerparam][loLet].length-1; no++){
		if(ajax_list_cachedLists[onerparam][loLet][no].length==0)continue;
		this.ResultSet[this.ResultSet.length]=ajax_list_cachedLists[onerparam][loLet][no].split(/\t/g);
		inputObj.dic[inputObj.dic.length]=ajax_list_cachedLists[onerparam][loLet][no].split(/\t/g);
	}
	this.ContentRender(inputObj);
	this.InputValueBackup = inputObj.value;
}

yildiz.prototype.ContentRender=function(inputObj){
	if (this.ResultSet.length == 0){
		this.HideOutputArea();
		return
	}
	if (!this.outputArea) this.CreateOutputArea();
	this.ClearOutputAreaContents();
	
	var ul = document.createElement("ul");
	    ul.id  = "Suggestions";
	    ul.sug = inputObj.sug;

	for(var ii=0; ii<this.ResultSet.length && ii<this.ResultLimit; ii++){
		var li = document.createElement("li");
		li.id = ii;
		li.onmousedown = li_MouseDown;
		li.onmouseover = li_MouseOver;
		li.onmouseout  = li_MouseOut;
		
		var rhs = document.createTextNode(this.ResultSet[ii][1]);

		li.appendChild(rhs);
		ul.appendChild(li);
	}
	this.outputArea.appendChild(ul);
	this.ShowOutputArea(inputObj);
}

yildiz.prototype.LookUp=function(inputObj){
	this.ResultSet.length = 0;
	for(var ii=0; ii<inputObj.dic.length; ii++){
		if(inputObj.value=="" || inputObj.value=="..")this.ResultSet[this.ResultSet.length]=inputObj.dic[ii];
		else if(this.utils.FindIn(inputObj.dic[ii][1], inputObj.value, this.MatchFromStart) > -1)
				this.ResultSet[this.ResultSet.length] = inputObj.dic[ii];
	}
}
	
yildiz.prototype.Render=function(inputObj){
	if (this.ResultSet.length == 0){
		this.HideOutputArea();
		return;
	}
	if (!this.outputArea) this.CreateOutputArea();
	this.ClearOutputAreaContents();
	
	var ul = document.createElement("ul");
		ul.id = "Suggestions";

	for(var ii=0; ii<this.ResultSet.length && ii<this.ResultLimit; ii++){
		var li = document.createElement("li");
		li.id = ii;
		li.onmouseover = yildiz_MouseOver;
		li.onmouseout = yildiz_MouseOut;
		
		var result = this.BuildHighlightedResult(this.ResultSet[ii], inputObj.value);
		
		li.appendChild(result);
		ul.appendChild(li);
	}
	this.outputArea.appendChild(ul);
	this.ShowOutputArea(inputObj);
}
	
yildiz.prototype.SuggestInputValue=function(){
	if(this.ResultSetIndex == -1) return
	var strValue=this.ResultSet[this.ResultSetIndex][1];

	if(this.ResultSetIndex>=0){
		this.inputObj.value = strValue;
	}else{
		this.inputObj.value += strValue.substring(this.inputObj.value.length, strValue.length);
	}
}
	
yildiz.prototype.AutoComplete=function(inputObj){
	if(!this.MatchFromStart) return;
	inputObj = (!inputObj) ? document.getElementById(this.FieldID) : inputObj;
	var startIdx = inputObj.value.length;
	
	if(this.ResultSet.length > 0){
		inputObj.value += this.ResultSet[0].substring(inputObj.value.length, this.ResultSet[0].length);
		this.PartialSelect(inputObj, startIdx);
	}
}
	
yildiz.prototype.PartialSelect=function(inputObj, startIdx){
	if(!this.MatchFromStart) return;
	inputObj = (!inputObj) ? document.getElementById(this.FieldID) : inputObj;
	this.utils.PartialSelect(inputObj, startIdx, inputObj.value.length);
}

yildiz.prototype.RevertInputValue=function(inputObj){
	inputObj.value = (inputObj.value == this.InputValueBackup) ? "" : this.InputValueBackup;
}
	
yildiz.prototype.MoveResultSetIndex=function(dir){
	if(!this.OutputAreaVisible) return;
	if(this.ResultSet.length == 0) return;

	if(this.outputArea && this.li_Index != -1) this.outputArea.childNodes[0].childNodes[this.li_Index].className = "";
	this.ResultSetIndex += dir;
	if (this.ResultSetIndex == -2) this.ResultSetIndex = this.ResultSet.length-1;
	if (this.ResultSetIndex == this.ResultSet.length) this.ResultSetIndex = -1;
	this.li_Index=this.ResultSetIndex;

	if(this.outputArea && this.ResultSetIndex == -1) this.RevertInputValue();
	if(this.outputArea && this.ResultSetIndex != -1) this.outputArea.childNodes[0].childNodes[this.ResultSetIndex].className = "selected";
}

yildiz.prototype.CreateOutputArea=function(){
	this.outputArea = document.createElement("div");
	this.outputArea.id=this.OutputAreaID;
	this.outputArea.style.display = "none";
	this.outputArea.style.position = "absolute";
	this.OutputAreaVisible = false;
	document.body.appendChild(this.outputArea);
}
	
yildiz.prototype.ShowOutputArea=function(inputObj){
	if(this.OutputAreaVisible) return
	if(inputObj.value == "" && !inputObj.dic) return

	if(this.outputArea){
		xy = this.utils.GetXY(inputObj);
		if(this.IEForceRelative && document.all) this.outputArea.parentNode.style.position = "relative";
		this.outputArea.style.display = "block";
		this.outputArea.style.left = xy[0]+1 + "px";
		this.outputArea.style.top = xy[1]+inputObj.offsetHeight + "px";
		this.outputArea.style.width = inputObj.offsetWidth-2 + "px";
	}
	this.OutputAreaVisible = true;
}
	
yildiz.prototype.HideOutputArea=function(){
	if(!this.OutputAreaVisible) return
	this.ResultCacheIndex = -1;
	this.outputArea.style.display = "none";
	this.OutputAreaVisible = false;
}

yildiz.prototype.ClearOutputAreaContents=function(){
	var sid = this.OutputAreaID;
	if(this.outputArea && this.outputArea.childNodes.length > 0) this.outputArea.removeChild(this.outputArea.childNodes[0]);
}
	
yildiz.prototype.BuildHighlightedResult=function(str, fragment){
	var csStr = this.utils.CaseSensitize(str);
	var csFragment = this.utils.CaseSensitize(fragment);
	var span = document.createElement("span");
	var lhs = document.createTextNode(str.substring(0, csStr.indexOf(csFragment)));
	var strong = document.createElement("strong");
	var highlight = document.createTextNode(str.substring(csStr.indexOf(csFragment), csStr.indexOf(csFragment) + csFragment.length));
	var rhs = document.createTextNode(str.substring(csStr.indexOf(csFragment) + csFragment.length, csStr.length));
	
	strong.appendChild(highlight);
	span.appendChild(lhs);
	span.appendChild(strong);
	span.appendChild(rhs);
	
	return span
}

