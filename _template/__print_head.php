<head>
<title>  World Media Web Servisi </title>
<meta Content-Type: content="text/html"; charset="windows-1254"/>

<link rel="STYLESHEET" type="text/css" href="_style/page.css">
<link rel="STYLESHEET" type="text/css" href="_style/senaryo.css">
<link rel="STYLESHEET" type="text/css" href="_style/tablestyle.css">
<script type="text/javascript">
window.onload=function(){
	arr=document.getElementsByTagName("tr");
	for(var ii=arr.length-1;ii>=0;ii--){
		if(arr[ii].getAttribute("del")) arr[ii].parentNode.removeChild(arr[ii]);
	}
	arr=document.getElementsByTagName("input");
	for(var ii=arr.length-1;ii>=0;ii--){
		if("button,submit,reset".indexOf(arr[ii].type)!=-1){
			if(!arr[ii].getAttribute("prn")) arr[ii].parentNode.removeChild(arr[ii]);
		}else if(arr[ii].className.indexOf("txt-id")!=-1){
			if(!arr[ii].getAttribute("prn")) arr[ii].parentNode.removeChild(arr[ii]);
		}
	}
	arr=document.getElementsByTagName("label");
	for(var ii=arr.length-1;ii>=0;ii--){
		if(arr[ii].className.indexOf("lnk")!=-1) if(!arr[ii].getAttribute("prn")) arr[ii].parentNode.removeChild(arr[ii]);
	}
	print();
	close();
}
</script>
</head>
