
var ajax_req = new Array();

function PrintThis(obj){
	if (!window.print) return;

	left=Math.floor((screen.width-600)/2);
	top=Math.floor((screen.height-800)/2);

	hwin=window.open("index_contents.php", "winprint1", "height=800,width=600,screenX="+left+",screenY="+top+",location=0,toolbar=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=0,modal=1,dialog=1,titlebar=0",true);
	hwin.onload=function(){
		hwin.document.getElementById("id_contents").value=obj.innerHTML;
		hwin.document.getElementById("id_contents_form").submit();
	}
}

function ValidateForm(form){
	if(form.getAttribute("islem")=="sel"){
		form.submit();
		return true;
	}

	for (ii=0;ii<form.elements.length; ii++){
		if(!ValueCheck(form.elements[ii]))return false;
		if(!ValidType(form.elements[ii])){
			tchar=form.elements[ii].getAttribute("tchar");
			if(tchar=='N')tmesg="\n\n Gültige numerische Wert erforderlich, zum Beispiel 349.54";
			else if(tchar=='I')tmesg="\n\n Gültige Integer-Wert erforderlich, zum Beispiel 673";
			else if(tchar=='D')tmesg="\n\n Gültiges Datum erforderlich, zum Beispiel 23.09.2011";
			else if(tchar=='H')tmesg="\n\n Gültige Uhrzeit erforderlich, zum Beispiel 08:45";
			else tmesg=" ungültige Wert";
			window.alert(form.elements[ii].name+tmesg);
			form.elements[ii].focus();
			return false;
		}

		if(form.elements[ii].getAttribute("req")==undefined)continue;
		attr_req=form.elements[ii].getAttribute("req");
		attr_msg=form.elements[ii].getAttribute("msg");

		if(form.elements[ii].type=="radio"){
			checked=false;
			radList=form.elements[form.elements[ii].name];
			for(jj=0; jj<radList.length; jj++) if(checked=radList[jj].checked) break;
			if (!checked){
				window.alert(form.elements[ii].name+" value required.");
				form.elements[ii].focus();
				return false;
			}
		}else
		if(form.elements[ii].sug && form.elements[ii].sug.idField){
			idField=form.elements[ii].sug.idField;
			if(attr_req=='2'){
				if((isEmpty(idField) || idField.value==0 || idField.value==-1)){
					if(attr_msg==undefined)msg=form.elements[ii].name+" no default value required.";
						else msg=attr_msg.replace(/\$VAL/g,form.elements[ii].value).replace(/\\n/g,String.fromCharCode(13));
					window.alert(msg);
					form.elements[ii].focus();
					return false;
				}
			}else
			if(attr_req=='+'){
				if(isEmpty(form.elements[ii])){
					if(attr_msg==undefined)msg=form.elements[ii].name+" value required.";
						else msg=attr_msg.replace(/\$VAL/g,form.elements[ii].value).replace(/\\n/g,String.fromCharCode(13));
					window.alert(msg);
					form.elements[ii].focus();
					return false;
				}
			}else
			if(attr_req=='++'){
				if((isEmpty(idField) || idField.value==0) && !form.elements[ii].value.match(/\S+\s*\+\s*$/)){
					if(attr_msg==undefined)msg=form.elements[ii].name+" selected or with + ended value required.";
						else msg=attr_msg.replace(/\$VAL/g,form.elements[ii].value).replace(/\\n/g,String.fromCharCode(13));
					window.alert(msg);
					form.elements[ii].focus();
					return false;
				}
			}else
			if(isEmpty(idField) || idField.value==0){
				if(attr_msg==undefined)msg=form.elements[ii].name+" selected value required.";
					else msg=attr_msg.replace(/\$VAL/g,form.elements[ii].value).replace(/\\n/g,String.fromCharCode(13));
				window.alert(msg);
				form.elements[ii].focus();
				return false;
			}
		}else
		if(isEmpty(form.elements[ii])){
			window.alert(form.elements[ii].name+" value required.");
			form.elements[ii].focus();
			return false;
		}
	}
	form.submit();
}
function ValueCheck(obj){
	valchk=obj.getAttribute("valchk");
	if(typeof window[valchk]=='function')return window[valchk](obj);
	valchk='valchk_'+obj.id;
	if(typeof window[valchk]=='function')return window[valchk](obj);
	return true;
}
function ValidType(obj){
	if(obj.getAttribute("tchar")==undefined)return true;
	if(isEmpty(obj))return true;
	tchar=obj.getAttribute("tchar");
	if(tchar=="S")return true;
	if(tchar=='N')return /^\s*-?[\d,\.]+\s*$/.test(obj.value);
	if(tchar=='I')return /^\s*-?[\d]+\s*$/.test(obj.value);
	if(tchar=='D')return /^\s*\d{1,2}[.\/-]\d{1,2}[.\/-]\d{4}\s*$/.test(obj.value) ||
						 /^\s*\d{4}[.\/-]\d{1,2}[.\/-]\d{1,2}\s*$/.test(obj.value);
	if(tchar=='T')return /^\s*\d{1,2}[.\/-]\d{1,2}[.\/-]\d{4}\s+\d{1,2}:\d{1,2}(:\d{1,2})?\s*$/.test(obj.value) ||
						 /^\s*\d{4}[.\/-]\d{1,2}[.\/-]\d{1,2}\s+\d{1,2}:\d{1,2}(:\d{1,2})?\s*$/.test(obj.value);
	if(tchar=='H')return /^\s*\d{1,2}:\d{1,2}(:\d{1,2})?\s*$/.test(obj.value);
	return true;
}



function menuSet(menuObj){
	if(typeof(id_ul_out)!='undefined' && id_ul_out) clearTimeout(id_ul_out);
	if(typeof(id_ul_clk)!='undefined' && id_ul_clk) id_ul_clk.style.display='none';
	id_ul_clk=menuObj;
	menuObj.style.display='block';
}
function menuOver(menuObj){
	if(typeof(id_ul_out)!='undefined' && id_ul_out) clearTimeout(id_ul_out);
	if(typeof(id_ul_clk)!='undefined' && id_ul_clk){
		id_ul_clk.style.display='none';
		id_ul_clk=menuObj;
		menuObj.style.display='block';
	}
}
function menuReset(){
	if(typeof(id_ul_clk)!='undefined' && id_ul_clk) id_ul_clk.style.display='none';
	id_ul_out=setTimeout("menuTimeout()",500);
}
function menuTimeout(){
	if(typeof(id_ul_out)!='undefined' && id_ul_out) clearTimeout(id_ul_out);
	id_ul_clk=null;
}

function Ara(obj,e){
	e = e || window.event;
	if (e.keyCode!=13) return;
	if (isEmpty(obj)) return;
	if (obj.value.match(/\s*u\s*:\s*(.+)/i)){
		var url="?testlogin&user="+RegExp.$1+"&qrys="+window.location.search.substr(1);
	}else if (obj.value.match(/\s*d\s*:\s*(.+)/i)){
		var url="?dilogin&dil="+RegExp.$1+"&qrys="+window.location.search.substr(1);
	}else if (obj.value.match(/\s*s\s*:\s*(.+)/i)){
		var url="?form_"+RegExp.$1;
	}else{
		var url="";
		if (document.getElementById('id_asist_form')) if ((id_asist_form.action+"&").match(/\?(\w+)&/g)) url="?"+RegExp.$1+"&islem=ara";
		url=url+"&aratxt="+urlEncode(obj.value);
	}
	obj.setAttribute("url", url);
	openLink(obj);
}

function ResetForm(form){
	if (form.getAttribute("islem")=="edt") return false;
	return true;
}

function isEmpty(obj){
	var str=obj.value.replace(/\s/g,"");
	if(str=="")return true;
	return false;
}

function EditWindow(){
	var width  = 300;
	var height = 200;
	var left   = (screen.width  - width)/2;
	var top    = (screen.height - height)/2;
	var params = 'width='+width+', height='+height;
	params += ', top='+top+', left='+left;
	params += ', directories=no';
	params += ', location=no';
	params += ', menubar=no';
	params += ', resizable=yes';
	params += ', scrollbars=no';
	params += ', status=no';
	params += ', toolbar=no';
	edtwin=window.open("",'editwindow', params);
	if (window.focus) edtwin.focus();
	return false;
}

function setFormEnter(form){
	for (ii in form.elements){
		if (form.elements[ii].tagName=="input")
			form.elements[ii].onkeydown=sonraki(form.elements[ii]);
	}
}

function dateBtn_sonra(obj,e){
	e = document.all ? window.event : e;
	if (e.keyCode==40) {nextObj(obj); return false}
	if (e.keyCode==38) {prevObj(obj); return false}
}

function notResp(obj,e){
	if (e.keyCode==13 && (obj.type=="button" || obj.type=="textarea" || obj.type=="checkbox" || obj.type=="radio")) return false;
	if ((e.keyCode==40 ||  e.keyCode==38) && obj.type=="textarea") return false;
	return true;
}

function nextObj(obj){
	for(ii=0; ii<obj.form.length; ii++)if(obj.form[ii]==obj){
		for(jj=1; jj<=obj.form.length; jj++){
			kk=(ii+jj)%obj.form.length;
			if(obj.form[kk].type!='hidden' &&
			   obj.form[kk].style.visibility!='hidden' &&
			   obj.form[kk].style.display!='none' &&
			   obj.form[kk].tabIndex!=-1){obj.form[kk].focus(); break;}
		}
		return;
	}
}

function prevObj(obj){
	for(ii=0; ii<obj.form.length; ii++)if(obj.form[ii]==obj){
		for(jj=1; jj<=obj.form.length; jj++){
			kk=(ii-jj+obj.form.length)%obj.form.length;
			if(obj.form[kk].type!='hidden' &&
			   obj.form[kk].style.visibility!='hidden' &&
			   obj.form[kk].style.display!='none' &&
			   obj.form[kk].tabIndex!=-1){obj.form[kk].focus(); break;}
		}
		return;
	}
}

function sonra(obj,e){
	e = e || window.event;
	switch (e.keyCode){
	case 13:if(notResp(obj,e)){nextObj(obj); return false}
	case 40:if(notResp(obj,e)){nextObj(obj); return false}
	case 38:if(notResp(obj,e)){prevObj(obj); return false}
	}
	return;
}

function modalLink(obj){
	url=obj.getAttribute("url");

	left=Math.floor((screen.width-600)/2);
	top=Math.floor((screen.height-800)/2);

	hwin=window.open("", "win1", "height=800,width=600,screenX="+left+",screenY="+top+",location=0,toolbar=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=0,modal=on,dialog=on",true);
	hwin.location.replace(url);
	hwin.onblur=function(){this.close();}
}

function copyLink(obj){
	if(confirm("Bu kaydý kopyalamak istiyormusunuz?"))
		window.location=obj.getAttribute("url");
}
function deleteLink(obj){
	if(confirm("Bu kaydý silmek istiyormusunuz?"))
		window.location=obj.getAttribute("url");
}
function moddelLink(obj){
	if(!confirm("Bu kaydý silmek istiyormusunuz?"))return;
	url=obj.getAttribute("url");

	left=Math.floor((screen.width-600)/2);
	top=Math.floor((screen.height-800)/2);

	hwin=window.open("", "win1", "height=800,width=600,screenX="+left+",screenY="+top+",location=0,toolbar=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=0,modal=on,dialog=on",true);
	hwin.location.replace(url);
	hwin.onblur=function(){this.close();}
}

function openLink(obj){
	window.location=obj.getAttribute("url");
}

function blankLink(obj){
	window.open(obj.getAttribute("url"),"_blank");
}

function iliskiClick(e){
	e = e || window.event;
	if (!e.ctrlKey) return;
	if (!this.value.match(/(\w+)-(\d+)/)) return;
	
	var action="?form_"+(this.getAttribute("action") || RegExp.$1);
	var url=action+("&mod=&bil=&islem=edt&id="+RegExp.$2);
	this.setAttribute("url", url);
	modalLink(this);
}
function iliskiLink(obj){
	if (!obj.innerHTML.match(/(\w+)-(\d+)/)) return;
	
	var action="?form_"+(obj.getAttribute("action") || RegExp.$1);
	var url=action+("&mod=&bil=&islem=edt&id="+RegExp.$2);
	obj.setAttribute("url", url);
	modalLink(obj);
}

function iliskiLabel(obj){
	if (!obj.innerHTML.match(/(\w+)-(\d+)/)) return;
	
	var action="?form_"+(obj.getAttribute("action") || RegExp.$1);
	var url=action+("&mod=&bil=&islem=edt&id="+RegExp.$2);
	obj.setAttribute("url", url);
	modalLink(obj);
}

function displayMessage(url){
    messageObj.setSource(url);
    messageObj.setCssClassMessageBox(false);
    messageObj.setSize(400,200);
    messageObj.setShadowDivVisible(true);
    messageObj.display();
}

function getForm(obj){
	ajaxIndex=ajax_req.length;
	ajax_req[ajaxIndex] = new sack();
	url=obj.getAttribute("url");
	targetObj=document.getElementById("form1_td");
	ajax_req[ajaxIndex].requestFile = "?"+url;
	ajax_req[ajaxIndex].onCompletion = function(){ showForm(ajaxIndex,targetObj); };
	ajax_req[ajaxIndex].runAJAX();
}

function showForm(ajaxIndex,targetObj){
	targetObj.innerHTML=ajax_req[ajaxIndex].response;
	ajax_req.splice(ajaxIndex,1);
	sc=targetObj.getElementsByTagName("script");
	for (ii=0; ii<sc.length; ii++) window.eval(sc[ii].innerHTML);
}

function urlEncode(str){
	//str=window.escape(str);
	str=str.replace(/\+/g, '%2B');
	str=str.replace(/\*/g, '%2A');
	str=str.replace(/\//g, '%2F');
	str=str.replace(/@/g,  '%40');
	return str;
}

var tableToExcel=(function(){
return function(htmNode, name, filename){
		var strStyle = '';
		function saveToXls(){
		var uri = 'data:application/vnd.ms-excel;base64;,'
			, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta content-type: content="text/html"; charset="utf-8"/><style>{style}</style></head><body><table class="tsample">{table}</table></body></html>'
			, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
			, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }

			if(!htmNode.nodeType) htmNode=document.getElementById(htmNode);
			for(;htmNode && htmNode.tagName!="TABLE";htmNode=htmNode.parentNode);
			if(htmNode){
				var strData=htmNode.innerHTML;
				strData=strData.replace(/class="d1 /g, 'class="d1" class="');
				strData=strData.replace(/class="d2 /g, 'class="d2" class="');
				var ctx = {worksheet: name || 'Worksheet', style: strStyle , table: strData}
				var aLink=document.createElement("a");
				aLink.href = uri + base64(format(template, ctx));
				aLink.download = filename;
				aLink.click();
			}
		}
		for(var ii in document.styleSheets)if(document.styleSheets[ii].href && document.styleSheets[ii].href.match(/tablestyle.css/)){var cssHref=document.styleSheets[ii].href; break; }
		if(cssHref){
			if(window.XMLHttpRequest)xhtm=new XMLHttpRequest();else xhtm=new ActiveXObject("Microsoft.XMLHTTP");
			xhtm.onreadystatechange=(function(){
				if(this.readyState==4 /* complete */){
					if(this.status==200)strStyle=this.responseText;else alert("Error loading "+cssHref);
					strStyle=strStyle.replace(/border-width: 1px 1px 1px 1px;/,"border:0;");
					saveToXls();
				}
			})
			xhtm.timeout=5000;
			xhtm.open('GET', cssHref, true);
			xhtm.send('');
		}else saveToXls();
	}
})()
