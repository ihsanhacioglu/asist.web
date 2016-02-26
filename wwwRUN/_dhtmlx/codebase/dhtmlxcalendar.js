
function dhtmlxCalendarObject (contId, butId, isAutoDraw, options){
	this.isAutoDraw = isAutoDraw;
	this.contId = contId;
	this.butId = butId;
	this.scriptName = 'dhtmlxcalendar.js';
	this.date = this.cutTime(new Date());
	this.selDate = this.cutTime(new Date());
	this.curDate = this.cutTime(new Date());
	this.entObj = document.createElement("DIV");
	this.monthPan = document.createElement("TABLE");
	this.dlabelPan = document.createElement("TABLE");
	this.daysPan = document.createElement("TABLE");

	this.parent = null;
	this.style = "dhtmlxcalendar";
	this.skinName = "";
	this.doOnClick = null;
	this.sensitiveFrom = null;
	this.sensitiveTo = null;
	this.insensitiveDates = null;
	this.activeCell = null;
	this.hotCell = null;
	this.winHeader = null
	this.onLanguageLoaded = null;
	this.dragging = false;
	this.minimized = false;
	this.winTitle = 'Calendar header';
	this.uid = 'sc&Cal'+Math.round(1000000*Math.random());
	this.holidays = null;
	this.time = false;
	this.daysCells = {};
	
	dhtmlxEventable(this);
	
	this.options = {
		btnPrev:	"&laquo;",
		btnBgPrev:	null,
		btnNext:	"&raquo;",
		btnBgNext:	null,
		yearsRange:	[1900, 2100],
		isMonthEditable: false,
		isYearEditable: false,
		isWinHeader: false,
		headerButtons: 'TMX',	// T - set date pointer on today,
								// M - Minimize/Normalize window,
								// X - hide window.
		isWinDrag: true
	}
	defLeng = {
		langname:	   'en',
		dateformat:	   '%Y-%m-%d',
		monthesFNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
		monthesSNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
		daysFNames:	   ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
		daysSNames:	   ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
		weekend:	   [0, 6],
		weekstart:	   0,
		msgClose:	   "Close",
		msgMinimize:   "Minimize",
		msgToday:	   "Today",
		msgClear:	   "Clear"
	}
	defDe = {
	    langname:      'de',
	    dateformat:    '%Y.%m.%d',
	    monthesFNames: ["Januar", "Februar", "M�rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
	    monthesSNames: ["Jan", "Feb", "M�r", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
	    daysFNames:    ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"],
	    daysSNames:    ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
	    weekend:       [0, 6],
	    weekstart:     1,
	    msgClose:      "Schlie�en",
	    msgMinimize:   "Drehen",
	    msgToday:      "Heute",
		msgClear:	   "Entleeren"
	}
	defTr = {
	    langname:      'tr',
	    dateformat:    '%Y.%m.%d',
	    monthesFNames: ["Ocak", "�ubat", "Mart", "Nisan", "May�s", "Haziran", "Temmuz", "A�ustos", "Eyl�l", "Ekim", "Kas�m", "Aral�k"],
	    monthesSNames: ["Oca", "�ub", "Mar", "Nis", "May", "Haz", "Tem", "A�u", "Eyl", "Eki", "Kas", "Ara"],
	    daysFNames:    ["Pazar", "Pazartesi", "Sal�", "�ar�amba", "Per�embe", "Cuma", "Cumartesi"],
	    daysSNames:    ["Pa", "Pt", "Sa", "�a", "Pe", "Cu", "Ct"],
	    weekend:       [0, 6],
	    weekstart:     1,
	    msgClose:      "Kapat",
	    msgMinimize:   "K���lt",
	    msgToday:      "Bug�n",
		msgClear:	   "Temizle"
	}
	if (!window.dhtmlxCalendarLangModules) window.dhtmlxCalendarLangModules = {};
	window.dhtmlxCalendarLangModules['en'] = defLeng;
	window.dhtmlxCalendarLangModules['de'] = defDe;
	window.dhtmlxCalendarLangModules['tr'] = defTr;

	if (window.dhtmlxCalendarObjects) window.dhtmlxCalendarObjects[window.dhtmlxCalendarObjects.length] = this;
	else window.dhtmlxCalendarObjects = [this];

	for (lg in defLeng) 
		this.options[lg] = defLeng[lg];

	if (options)
		for (param in options) 
			this.options[param] = options[param];
	this.loadUserLanguage();
	if (options)
		for (param in options) 
			this.options[param] = options[param];
	
	this.allYears = Array();
	with (this.options)
		for (var i=yearsRange[0]; i <= yearsRange[1]; i++)
			this.allYears.push(i);

	if(isAutoDraw !== false) this.draw(options);
	return this;
}
dhtmlXCalendarObject = dhtmlxCalendarObject;

dhtmlxCalendarObject.prototype.setHeader = function(isVisible, isDrag, btnsOpt){
	with (this.options){
		isWinHeader = isVisible;
		isWinDrag = isDrag;
		if (btnsOpt) headerButtons = btnsOpt;
	}
	this.setSkin (this.skinName);
}

dhtmlxCalendarObject.prototype.setYearsRange = function(minYear, maxYear){
	this.options.yearsRange = [parseInt(minYear), parseInt(maxYear)];
	this.allYears = [];
	for (var i=minYear; i <= maxYear; i++)
		this.allYears.push(i);
}

dhtmlxCalendarObject.prototype.createStructure = function(){
	// Create Structure
	var self = this;
	if (!this.entObj.className) this.setSkin(this.skinName);

	this.entObj.style.position = "relative";
	if (this.options.isWinHeader) {
		this.winHeader = document.createElement('DIV');
		this.entObj.appendChild(this.winHeader);
	}

	this.entBox = document.createElement("TABLE");
	this.entBox.className = "entbox";
	with (this.entBox) {
		cellPadding = "0px";
		cellSpacing = "0px";
		width = '100%';
	}
	
	this.entObj.appendChild(this.entBox);
	var monthBox = this.entBox.insertRow(0).insertCell(0);
	with (this.monthPan) {
		cellPadding = "1px";
		cellSpacing = "0px";
		width = '100%';
		align = 'center';
	}
	this.monthPan.className = "dxcalmonth";
	monthBox.appendChild(this.monthPan);

	var dlabelBox = this.entBox.insertRow(1).insertCell(0);
	dlabelBox.appendChild(this.dlabelPan);
	with (this.dlabelPan) {
		cellPadding = "0px";
		cellSpacing = "0px";
		width = '100%';
		align = 'center';
	}
	this.dlabelPan.className = "dxcaldlabel";
	
	var daysBox = this.entBox.insertRow(2).insertCell(0);
	daysBox.appendChild(this.daysPan);
	with (this.daysPan) {
		cellPadding = "1px";
		cellSpacing = "0px";
		width = '100%';
		align = 'center';
	}
	if(_isIE || _isKHTML) this.daysPan.className = "dxcaldays_ie"; else this.daysPan.className = "dxcaldays";
	this.daysPan.onmousemove = function (e) {self.doHotKeys(e);}
	this.daysPan.onmouseout = function () {self.endHotKeys();}
	
	if (typeof(this.contId) != 'string') this.con = this.contId;
	else this.con = document.getElementById(this.contId);
	if (typeof(this.butId) != 'string') this.but = this.butId;
	else this.but = document.getElementById(this.butId);

	if (this.con.nodeName == 'INPUT') {
		var div = document.createElement('DIV');
		var aleft = getAbsoluteLeft(this.con);
		var atop = getAbsoluteTop(this.con);
		with (div.style) {
			position = 'absolute';
			display = 'none';
			top = atop+'px';
			left = this.con.offsetWidth+aleft+'px';
			zIndex = "99";
		}
		this.setParent(div);

		document.body.appendChild(div);
		this.con.onclick = function () {if (self.isVisible()) self.hide();}
		this.but.onclick = function () {
		  	if (self.isVisible()) self.hide();
		  	else{
				if (self.con.value){
		            var val = self.con.value.split (" ");
		            if (self.time) self.setFormatedTime(null, val [1]);
		            self.setDate (self.getFormatedDate(val [0]));
				}
				self.show();
			}
		}
		this.con._c = this.parent;
		this.doOnClick = function (date){
			self.con.value = self.getFormatedDate(self.options.dateformat, date) + (self.time ? " " + self.getFormatedTime () : "");
			self.hide();
			self.con.focus();
			return true;
		}
	}else this.setParent(this.con);

	if(_isIE){//add iframe under calendard in IE (IE 6 issue with select box fix)
		if(this.parent.style.zIndex==0){
			this.parent.style.zIndex = 100;
		}
		if(this.ifr == undefined && this._dblC == undefined){
			this.ifr = document.createElement("IFRAME");
			this.ifr.style.position = "absolute";
			this.ifr.style.zIndex = 1;
			this.ifr.frameBorder = "no";
			this.ifr.style.top =  this.entObj.offsetTop + 'px';
			this.ifr.style.left = this.entObj.offsetLeft + 'px';
			this.ifr.scrolling = 'no';
			this.ifr.style.display = this.parent.style.display;
			this.ifr.className = this.style + (this.skinName?'_':"") + this.skinName + "_ifr";

			this.parent.appendChild(this.ifr)//document.body.appendChild(this.ifr);
		}
	}
	this.entObj.onclick = function (e) {
		e = e||event;
		if (e.stopPropagation) e.stopPropagation();
		else e.cancelBubble = true;
	}
	if (!this.entObj.className) this.setSkin (this.skinName);
}

dhtmlxCalendarObject.prototype.drawHeader = function(){
	if (this._dblC) return;
	if (!this.options.isWinHeader) return;
	var self = this;
							   
	if (!this.winHeader) return false;             
	while (this.winHeader.hasChildNodes())
		this.winHeader.removeChild(this.winHeader.firstChild);
	
	this.winHeader.className = 'header';
	this.winHeader.onselectstart=function(){ return false};

	this.headerLabel = document.createElement('div');	// WINDOW TITLE
	this.headerLabel.className = 'winTitle';
	this.headerLabel.appendChild(document.createTextNode(this.winTitle));
	this.headerLabel.setAttribute('title', this.winTitle);
	this.winHeader.appendChild(this.headerLabel);
	if (this.options.isWinDrag) {				// DRAG & DROP
		this.winHeader.onmousedown = function(e) {
			self.startDrag(e);
		}
	}

	if (this.options.headerButtons.indexOf('X')>=0) {
		var btnClose = document.createElement('DIV'); // CLOSE BUTTON
		btnClose.className = 'btn_close';
		btnClose.setAttribute('title', this.options.msgClose);
		btnClose.onmousedown = function (e) {
			e = e||event;
			self.hide();
			if (e.stopPropagation) e.stopPropagation();
			else e.cancelBubble = true;
		}
		this.winHeader.appendChild(btnClose);
	}					

	if (this.options.headerButtons.indexOf('M')>=0) {
		var btnMin = document.createElement('DIV'); // MINIMIZE BUTTON
		btnMin.className = 'btn_mini';
		btnMin.setAttribute('title', this.options.msgMinimize);
		btnMin.onmousedown = function(e) {
			e = e||event;
			self.minimize();
			if (e.stopPropagation) e.stopPropagation();
			else e.cancelBubble = true;
		}
		this.winHeader.appendChild(btnMin);
	}

	if (this.options.headerButtons.indexOf('C')>=0) {  
		var btnClear = document.createElement('DIV');	// TO CLEAN INPUT
		btnClear.className = 'btn_clear';
		btnClear.setAttribute('title', this.options.msgClear);
		btnClear.onmousedown = function(e) {
			e = e||event;
			if (e.stopPropagation) e.stopPropagation();
			else e.cancelBubble = true;
			self.con.value = "";
			self.hide();				
		}
		this.winHeader.appendChild(btnClear);
	}
	
					
	if (this.options.headerButtons.indexOf('T')>=0) {
		var btnToday = document.createElement('DIV');	// GOTO TODAY
		btnToday.className = 'btn_today';
		btnToday.setAttribute('title', this.options.msgToday);
		btnToday.onmousedown = function(e) {
			e = e||event;
			if (e.stopPropagation) e.stopPropagation();
			else e.cancelBubble = true;
			self.setDate(new Date());
			if (self.doOnClick) self.doOnClick(new Date());
		}
		this.winHeader.appendChild(btnToday);
	}
}

dhtmlxCalendarObject.prototype.drawMonth = function(){
	var self = this;
	if (this.monthPan.childNodes.length>0) this.monthPan.removeChild(this.monthPan.childNodes[0]);
	var row = this.monthPan.insertRow(0);

	var cArLeft  = row.insertCell(0);
	var cContent = row.insertCell(1);
	var cArRight = row.insertCell(2);
	
	cArLeft.align = "left";
	cArLeft.className = 'month_btn_left';
	var btnLabel = document.createElement("div");
	btnLabel.innerHTML = " ";
	cArLeft.appendChild(btnLabel);
	cArLeft.onclick = function(){ self.prevMonth() }
	
	cArRight.align = "right";
	cArRight.className = 'month_btn_right';
	var btnLabel = document.createElement("div");
	btnLabel.innerHTML = " ";
	cArRight.appendChild(btnLabel);
	cArRight.onclick = function(){ self.nextMonth() }
	
	cContent.align = 'center';
	var mHeader = document.createElement("TABLE");
	with (mHeader) {
		cellPadding = "0px";
		cellSpacing = "0px";
		align = "center";
	}
	var mRow = mHeader.insertRow(0);
	var cMonth = mRow.insertCell(0);
	var cComma = mRow.insertCell(1);
	var cYear = mRow.insertCell(2);
	
	cContent.appendChild(mHeader);
	
	this.planeMonth = document.createElement('DIV');
	this.planeMonth._c = this;
	this.planeMonth.appendChild(document.createTextNode(this.options.monthesFNames[this.date.getMonth()]));
	this.planeMonth.className = 'planeMonth';
	cMonth.appendChild(this.planeMonth);
	if (this.options.isMonthEditable) {
		this.planeMonth.style.cursor = 'pointer';
		this.editorMonth = new dhtmlxRichSelector({
			nodeBefore: this.planeMonth,
			valueList: [0,1,2,3,4,5,6,7,8,9,10,11],
			titleList: this.options.monthesFNames,
			activeValue: this.options.monthesFNames[this.date.getMonth()],
			onSelect: this.onMonthSelect
		});
		this.editorMonth._c = this;
	}

	cComma.appendChild(document.createTextNode(","));
	cComma.className = 'comma';

	this.planeYear = document.createElement('DIV');
	this.planeYear._c = this;
	this.planeYear.appendChild(document.createTextNode(this.date.getFullYear()));
	this.planeYear.className = 'planeYear';
	cYear.appendChild(this.planeYear);
	if (this.options.isYearEditable) {
		this.planeYear.style.cursor = 'pointer';
		this.editorYear = new dhtmlxRichSelector({
			nodeBefore: this.planeYear,
			valueList: this.allYears,
			titleList: this.allYears,
			activeValue: this.date.getFullYear(),
			onSelect: this.onYearSelect,
			isOrderedList: true,
			isNumbersList: true,
			isAllowUserValue: true
		});
		this.editorYear._c = this;
	}
}


dhtmlxCalendarObject.prototype.drawDayLabels = function() {
	var self = this;
	if(this.dlabelPan.childNodes.length>0)
		this.dlabelPan.removeChild(this.dlabelPan.childNodes[0]);
	
	var row = this.dlabelPan.insertRow(-1);
	row.className = "daynames";
	for(var i=0; i<7; i++){
		var cDay = row.insertCell(i);
		cDay.appendChild(document.createTextNode(this.getDayName(i)))
	}
}


dhtmlxCalendarObject.prototype.drawDays = function() {
	var self = this;
	var row = {}, cell;

	if(!this.daysPan.childNodes.length){
		for (var weekNumber=0; weekNumber<6; weekNumber++){
			row = this.daysPan.insertRow(-1);
			this.daysCells [weekNumber] = {};
			for (var i=0; i<7; i++){
				this.daysCells [weekNumber] [i] = row.insertCell(-1);
				this.daysCells [weekNumber] [i].appendChild(document.createTextNode(""));
			}
		}
	}

	var tempDate = new Date(this.date);
	tempDate.setDate(1);
	var day1 = (tempDate.getDay() - this.options.weekstart) % 7;

	if (day1 <= 0) day1 += 7;
	tempDate.setDate(- day1);
	tempDate.setDate(tempDate.getDate() + 1);
	if (tempDate.getDate() < tempDate.getDay()) tempDate.setMonth(tempDate.getMonth() - 1);
			
	for (var weekNumber=0; weekNumber<6; weekNumber++){
		for (var i=0; i<7; i++){
			cell = this.daysCells [weekNumber] [i];
			cell.setAttribute('id', this.uid+tempDate.getFullYear()+tempDate.getMonth()+tempDate.getDate());
			cell.childNodes [0].nodeValue = tempDate.getDate();
			cell.thisdate = tempDate.toString();
			cell.className = (tempDate.getMonth()==this.date.getMonth() ? "thismonth" : "othermonth");

			if (this.insensitiveDates){
				var c = false;
				for (var j=0; j<this.insensitiveDates.length; j++){
					var s = /\.|\-/.exec(this.insensitiveDates[j]);
					if (s) var f = (this.insensitiveDates[j].split (s).length == 2 ? '%m'+s+'%d' : '%Y'+s+'%m'+s+'%d');
					if ((s && this.getFormatedDate(f, tempDate) == this.insensitiveDates[j])
						|| (this.insensitiveDates[j].length == 1 && tempDate.getDay () == this.insensitiveDates[j])){
						this.addClass(cell, "insensitive");
						tempDate.setDate(tempDate.getDate() + 1);
						c = true;
						break;
					}
				}
				if (c) continue;
			}
				

			if (this.sensitiveFrom && this.sensitiveFrom instanceof Array) {
				var c = true;
				for (var j=0; j<this.sensitiveFrom.length; j++) {
					var s = this.sensitiveFrom[j].indexOf ('.') != -1 ? '.' : '-';
					var f = (this.sensitiveFrom[j].split (s).length == 2 ? '%m'+s+'%d' : '%Y'+s+'%m'+s+'%d');
					
					if (this.getFormatedDate(f, tempDate) == this.sensitiveFrom[j])
					  c = false;
				}
				if (c) {
					this.addClass(cell, "insensitive");
			  tempDate.setDate(tempDate.getDate() + 1);					
					continue;
				}
			}
			
			if (this.sensitiveFrom && (tempDate.valueOf() < this.sensitiveFrom.valueOf())){
				this.addClass(cell, "insensitive");
				tempDate.setDate(tempDate.getDate() + 1);
				continue;
			}
			if (this.sensitiveTo && (tempDate.valueOf() > this.sensitiveTo.valueOf())){
				this.addClass(cell, "insensitive");
				tempDate.setDate(tempDate.getDate() + 1);
				continue;
			}
			if (this.isWeekend(i) && tempDate.getMonth()==this.date.getMonth())
				cell.className = "weekend";
			if (tempDate.toDateString() == this.curDate.toDateString())
				this.addClass(cell, "current");
			
			if (tempDate.toDateString() == this.selDate.toDateString()){
				this.activeCell = cell;
				this.addClass(cell, "selected");
			}

			if (this.holidays)
				for (var j=0; j<this.holidays.length; j++) {				
					var s = this.holidays[j].indexOf ('.') != -1 ? '.' : '-';
					var f = (this.holidays[j].split (s).length == 2 ? '%m'+s+'%d' : '%Y'+s+'%m'+s+'%d');
					if (this.getFormatedDate(f, tempDate) == this.holidays[j])
						this.addClass(cell, "holiday");
				}
		
			cell.onclick = function(){
				if(!self.doOnClick || self.doOnClick(this.thisdate)){
					self.setDate(this.thisdate);
					self.callEvent("onClick", [this.thisdate]);
					if (self.activeCell) self.resetClass(self.activeCell);
					self.addClass(this, "selected");
					self.activeCell = this;
				}
			}
			tempDate.setDate(tempDate.getDate() + 1);
		}
	}
}


dhtmlxCalendarObject.prototype.draw = function(options){
	var self = this;
	if (this.loadingLanguage){
		setTimeout(function() {
			self.draw(options);
			return;
		}, 20);
		return;
		}
	else
		if (!this.parent) this.createStructure(options);

	this.drawHeader();
	this.drawMonth();
	this.drawDayLabels();
	this.drawDays();
	this.isAutoDraw = true;
}


dhtmlxCalendarObject.prototype.startDrag = function(e) {
	e = e||event;
	if ((e.button === 0) || (e.button === 1)) {
		if (this.dragging) {
			this.stopDrag(e);
		}
	
		this.drag_mx = e.clientX;
		this.drag_my = e.clientY;


		this.drag_spos = this.getPosition(this.parent);
		document.body.appendChild(this.parent);
		with (this.parent.style) {
			left = this.drag_spos[0] + 'px';
			top = this.drag_spos[1] + 'px';
			margin = '0px';
			position = 'absolute';
		}
		
    if (this.ifr) {
      this.ifr.style.top =  '0px';
      this.ifr.style.left = '0px';
    }
		this.bu_onmousemove = document.body.onmousemove;
		var self = this;
		document.body.onmousemove = function (e) {
			self.onDrag(e);
		}
		this.bu_onmouseup = document.body.onmouseup;
		document.body.onmouseup = function (e) {
			self.stopDrag(e);
		}

		this.dragging = true;
	}

}


dhtmlxCalendarObject.prototype.onDrag = function(e) {
	e = e||event;
	if ((e.button === 0) || (e.button === 1)) {
		var delta_x = this.drag_mx - e.clientX;
		var delta_y = this.drag_my - e.clientY;
		this.parent.style.left = this.drag_spos[0] - delta_x + 'px';
		this.parent.style.top = this.drag_spos[1] - delta_y + 'px';
    if (this.time) {
	this.tp.setPosition (getAbsoluteLeft (cal.parent) + 30, getAbsoluteTop (cal.parent) + 160);   
    }
		//iframe dragging (fix for IE6)
		if(_isIE) {
			this.ifr.style.left = 0;//this.parent.offsetLeft + 'px';
			this.ifr.style.top = 0;//this.parent.offsetTop + 'px';
		}
		
	} else {
		this.stopDrag(e);
	}
}


dhtmlxCalendarObject.prototype.stopDrag = function(e) {
	e = e||event;   
	document.body.onmouseup = (this.bu_onmouseup === window.undefined)? null: this.bu_onmouseup;
	document.body.onmousemove = (this.bu_onmousemove === window.undefined)? null: this.bu_onmousemove;
	this.dragging = false;
}

dhtmlxCalendarObject.prototype.doHotKeys = function(e){
	e = e||event;
	var cell = ((e.target) ? e.target : e.srcElement);
	if (cell.className.toString().indexOf('insensitive') >=0 ) {
		this.endHotKeys();
	} else {
		if (this.hotCell) this.resetHotClass(this.hotCell);
		this.addClass(cell, 'hover');
		this.hotCell = cell;
	}
}


dhtmlxCalendarObject.prototype.endHotKeys = function(){
	if (this.hotCell) {
		this.resetHotClass(this.hotCell);
		this.hotCell = null;
	}
}


dhtmlxCalendarObject.prototype.minimize = function(){
	if (!this.winHeader) return;
  this.minimized = !this.minimized;
	this.entBox.style.display = (!this.minimized) ? '' : 'none';
  
  this.setSkin (this.skinName);
}


dhtmlxCalendarObject.prototype.loadUserLanguage = function(language, userCBfunction){ 
	if (userCBfunction)
		this.onLanguageLoaded = userCBfunction;
	// define user (system) language
	if (!language){
		language="en";
		/*
		if (window.navigator.language)
			language = window.navigator.language;
		else if (window.navigator.userLanguage)
			language = window.navigator.userLanguage;   */
	}
	// mark this object
	this.loadingLanguage = language;
	// language not defined
	if (!language) {
		this.loadUserLanguageCallback(false);
		return;
	}
	// break if current Language
	if (language == this.options.langname) { 
		this.loadUserLanguageCallback(true);
		return;
	}
	// if language module already exist...
	var __lm = window.dhtmlxCalendarLangModules;
	if (__lm[language]) {
		for (lg in __lm[language])
			this.options[lg] = __lm[language][lg];
		this.loadUserLanguageCallback(true);
		return;
	}
	// define language module path
	var src, path = null;
	var scripts = document.getElementsByTagName('SCRIPT');
	for (var i=0; i<scripts.length; i++)
		if(src = scripts[i].getAttribute('src'))
			if (src.indexOf(this.scriptName) >= 0) {
				path = src.substr(0, src.indexOf(this.scriptName));
				break;
			}
	// path to language module file not defined
	if (path === null) {	
		this.loadUserLanguageCallback(false);
		return;
	}
	this.options.langname = language;
	var langPath = path + 'lang/' + language + '.js';
	// if language module already linked (loading now) ...
	for (var i=0; i<scripts.length; i++)
		if(src = scripts[i].getAttribute('src'))
			if (src == langPath) return;
	// load language module
	var script = document.createElement('SCRIPT');
	script.setAttribute('language', "Java-Script");
	script.setAttribute('type', "text/javascript");
	script.setAttribute('src', langPath);
	document.body.appendChild(script);
}

dhtmlxCalendarObject.prototype.loadUserLanguageCallback = function(status) {
	this.loadingLanguage = null;
	if (this.isAutoDraw !== false) this.draw();
	if (this.onLanguageLoaded && (typeof(this.onLanguageLoaded) == 'function'))
		this.onLanguageLoaded(status);
}

function loadLanguageModule(langModule) {
	var __c = window.dhtmlxCalendarObjects;
	for (var i=0; i<__c.length; i++) {
		if (__c[i].loadingLanguage == langModule.langname) {
			for (lg in langModule)
				__c[i].options[lg] = langModule[lg];
			__c[i].loadUserLanguageCallback(true);
		}
	}
	window.dhtmlxCalendarLangModules[langModule.langname] = langModule;
}

dhtmlxCalendarObject.prototype.show = function(){
	this.parent.style.display = '';

	if (this.con.nodeName == 'INPUT'){
		var aleft = getAbsoluteLeft(this.con);
		var atop = getAbsoluteTop(this.con);
		if (this.but){
			this.parent.style.left = getAbsoluteLeft(this.but) + this.but.offsetWidth + 'px';
			this.parent.style.top = getAbsoluteTop(this.but) + 'px';
		}else{
			this.parent.style.left = getAbsoluteLeft(this.con) + this.con.offsetWidth + 'px';
			this.parent.style.top = getAbsoluteTop(this.con) + 'px';
		}
	}	

	if (this.ifr != undefined){
		this.ifr.style.top  = this.entObj.offsetTop  + 'px';
		this.ifr.style.left = this.entObj.offsetLeft + 'px';
		this.ifr.style.display = 'block';
	}	
  
	if (this.time && !this.minimized){
		this.tp.setPosition (getAbsoluteLeft (cal.parent) + 30, getAbsoluteTop (cal.parent) + 160);   
		this.tp.show ();
	}
	return this;
}


dhtmlxCalendarObject.prototype.hide = function(){
	this.parent.style.display = 'none';
	if(this.ifr!=undefined) this.ifr.style.display = 'none';
	if (this.time) this.tp.hide();
	return this;
}


dhtmlxCalendarObject.prototype.setDateFormat = function(format){
	this.options.dateformat = format;
}


dhtmlxCalendarObject.prototype.isWeekend = function(k){
	var q = k + this.options.weekstart;
	if (q > 6) q -= 7;
	for (var i=0; i<this.options.weekend.length; i++)
		if (this.options.weekend[i] == q)
			return true;
	return false;
}


dhtmlxCalendarObject.prototype.getDayName = function(k){
	var q = k + this.options.weekstart;
	if (q > 6) q = q - 7;
	return this.options.daysSNames[q];
}


dhtmlxCalendarObject.prototype.cutTime = function(date) {
	date = new Date(date);
	var ndate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
	return ndate;
}


dhtmlxCalendarObject.prototype.onYearSelect = function(value) {
	if (!isNaN(value))
		this._c.date = new Date(this._c.date.setFullYear(value));
	this._c.draw();
	return (!isNaN(value));
}

dhtmlxCalendarObject.prototype.onMonthSelect = function(value) {
	this._c.date = new Date(this._c.date.setMonth(value));
	this._c.draw();
	return true;
}


dhtmlxCalendarObject.prototype.setParent = function(newParent){
	if (newParent) {
		this.parent = newParent;
		this.parent.appendChild(this.entObj);
	}
}


dhtmlxCalendarObject.prototype.setDate = function(date){
	dateTmp = this.setFormatedDate(this.options.dateformat,date);
	if(isNaN(dateTmp) || dateTmp==null)
		date = new Date(date);
	else if(dateTmp)
		date = dateTmp;
	else
		date = new Date();    
  if (date.toString().indexOf ('Invalid')>-1 || isNaN(date))
    date = new Date();
    
		this.date = new Date(this.cutTime(date));
		this.selDate = new Date(this.cutTime(date));
		if (this.isAutoDraw) this.draw();

	this.draw();	
}

dhtmlxCalendarObject.prototype.addClass = function(obj, styleName) {
	obj.className += ' ' + styleName;
}

dhtmlxCalendarObject.prototype.resetClass = function(obj) {
	obj.className = obj.className.toString().split(' ')[0];
}

dhtmlxCalendarObject.prototype.resetHotClass = function(obj) {
	obj.className = obj.className.toString().replace(/hover/, '');
}

dhtmlxCalendarObject.prototype.setSkin = function(newSkin) {
	this.skinName = newSkin;
  var mode = "";
  mode = (this.minimized
         ? "_mini" 
         : (this.time 
           ? "_long"
           : (this.options.isWinHeader
             ? "_maxi"
             : ""
             )
           )
         ); 
  
  this.entObj.className = this.style + (newSkin ? '_' + newSkin : '');
  if (mode)
    this.entObj.className += " " + this.entObj.className + mode;

	if(this.ifr!=undefined) {
		this.ifr.className = this.style + (newSkin ? '_' + newSkin : '') + mode + "_ifr";
    
	}
  if (this.time)
    (this.isVisible () && !this.minimized) ? this.tp.show () : this.tp.hide ();
}

dhtmlxCalendarObject.prototype.getDate = function(){
	return this.selDate.toString();
}


dhtmlxCalendarObject.prototype.nextMonth = function(){
  this.date.setDate(1);
	this.date.setMonth(this.date.getMonth()+1);
	if (this.checkEvent ("onChangeMonth")) this.callEvent ("onChangeMonth",[(this.date.getMonth()+1 > 12 ? 1 : this.date.getMonth()+1),this.date.getMonth() == 0 ? 12 : this.date.getMonth()]);	
	if (this.isAutoDraw) this.draw();
}

dhtmlxCalendarObject.prototype.prevMonth = function(){
  this.date.setDate(1);
	this.date.setMonth(this.date.getMonth()-1);
	if (this.checkEvent ("onChangeMonth")) this.callEvent ("onChangeMonth",[this.date.getMonth()+1,this.date.getMonth()+2 > 12 ? 1 : this.date.getMonth()+2]);
	if (this.isAutoDraw) this.draw();
}

dhtmlxCalendarObject.prototype.setOnClickHandler = function(func){
	this.doOnClick = func;
}

dhtmlxCalendarObject.prototype.setPosition = function(argA,argB,argC){
	if(typeof(argA)=='object'){
		var posAr = this.getPosition(argA)
		var left = posAr[0]+argA.offsetWidth+(argC||0);
		var top = posAr[1]+(argB||0);
	}
	this.parent.style.position = "absolute";
	this.parent.style.top = (top||argA)+"px";
	this.parent.style.left = (left||argB)+"px";
	if (this.ifr != undefined) {
		this.ifr.style.left = '0px';
		this.ifr.style.top = '0px';
	}
  if (this.time)
    this.tp.setPosition (getAbsoluteLeft (cal.parent) + 30, getAbsoluteTop (cal.parent) + 160);   
}

dhtmlxCalendarObject.prototype.close = function(func){
	this.hide ();
}

dhtmlxCalendarObject.prototype.getPosition = function(oNode,pNode) { 
   if(!pNode)
   		var pNode = document.body
   var oCurrentNode=oNode;
   var iLeft=0;
   var iTop=0;
                  while ((oCurrentNode)&&(oCurrentNode!=pNode)){//.tagName!="BODY"){ 
               iLeft+=oCurrentNode.offsetLeft-oCurrentNode.scrollLeft;
               iTop+=oCurrentNode.offsetTop-oCurrentNode.scrollTop;
               oCurrentNode=oCurrentNode.offsetParent;//isIE()?:oCurrentNode.parentNode;
                  }
              if (pNode == document.body ){
                 if (_isIE){
                 if (document.documentElement.scrollTop)
                  iTop+=document.documentElement.scrollTop;
                 if (document.documentElement.scrollLeft)
                  iLeft+=document.documentElement.scrollLeft;
                  }
                  else
                       if (!_isFF){
                             iLeft+=document.body.offsetLeft;
                           iTop+=document.body.offsetTop;
                  }
                 }

   return new Array(iLeft,iTop);
     
}


dhtmlxCalendarObject.prototype.getFormatedDate = function (dateformat, date) {
	if(!dateformat) dateformat = this.options.dateformat
	if(!date) date = this.selDate;
	date = new Date(date);
	var out = '';
	var plain = true;
	for (var i=0; i<dateformat.length; i++) {
		var replStr = dateformat.substr(i, 1);
		if (plain) {
			if (replStr == '%') {
				plain = false;				
				continue;
			}
			out += replStr;
		} else {
			switch (replStr) {
//	---	Day	---
			  case 'e':
			  	replStr = date.getDate();
				break;
			  case 'd':
			  	replStr = date.getDate();
				if (replStr.toString().length == 1)
					replStr='0'+replStr;
				break;
			  case 'j':
				var x = new Date(date.getFullYear(), 0, 0, 0, 0, 0, 0);
				replStr = Math.ceil((date.valueOf() - x.valueOf())/1000/60/60/24 - 1);
				while (replStr.toString().length < 3)
					replStr = '0' + replStr;
				break;
			  case 'a':
			  	replStr = this.options.daysSNames[date.getDay()];
				break;
			  case 'W':
			  	replStr = this.options.daysFNames[date.getDay()];
				break;
//	---	Month	---
			  case 'c':
			  	replStr = 1 + date.getMonth();
				break;
			  case 'm':
			  	replStr = 1 + date.getMonth();
				if (replStr.toString().length == 1)
					replStr = '0' + replStr;
				break;
			  case 'b':
			  	replStr = this.options.monthesSNames[date.getMonth()];
				break;
			  case 'M':
			  	replStr = this.options.monthesFNames[date.getMonth()];
				break;
//	---	Year	---
			  case 'y':
			  	replStr = date.getFullYear();
				replStr = replStr.toString().substr(2);
				break;
			  case 'Y':
			  	replStr = date.getFullYear();
			}
			out += replStr;
			plain = true;
		}
	}

	return out;
}

dhtmlxCalendarObject.prototype.setFormatedDate = function(dateformatarg, date){
	if (!date) return false;
	if(!dateformatarg) dateformatarg = this.options.dateformat;
	date = date.toString();
	function parseMonth(val){
		var tmpAr = new Array(this.options.monthesSNames,this.options.monthesFNames);
		for(var j=0;j<tmpAr.length;j++){
			for (var i=0; i<tmpAr[j].length; i++)
				if (tmpAr[j][i].indexOf(val) == 0)
					return i;
		}
		return -1;
	}
	var outputDate = new Date(2008, 0, 1);//default date 2008.01.01
	var j=0;//position in date
	for(var i=0;i<dateformatarg.length;i++){
		
		var _char = dateformatarg.substr(i,1);
		if(_char=="%"){
			var _cd = dateformatarg.substr(i+1,1);//code of format item
			var _nextpc = dateformatarg.indexOf("%",i+1);
			var _nextDelim = dateformatarg.substr(i+2,_nextpc-i-1-1);
			var _nDelimInDatePos = date.indexOf(_nextDelim,j);
			if(_nextDelim=="")
				_nDelimInDatePos = date.length
			if(_nDelimInDatePos==-1)
				return null;
			
			var value = date.substr(j, _nDelimInDatePos-j);//value in date
			if (_cd != 'M' && _cd != 'b') 
				value -= 0;
			j=_nDelimInDatePos+_nextDelim.length
			switch (_cd) {
//	---	Day	---
			  case 'd':
			  case 'e':
			  	outputDate.setDate(parseFloat(value));
				break;
//	---	Month	---
			case "c":
			case "m":
				outputDate.setMonth(parseFloat(value) - 1);
				break;
			case "M":
				var val = parseMonth.call(this,value);
				if(val!=-1)
					outputDate.setMonth(parseFloat(val));
				else 
					return null;
				break;
			case "b":
				var val = parseMonth.call(this,value);
				if(val!=-1)
					outputDate.setMonth(parseFloat(val));
				else 
					return null;
				break;
//	---	Year	---
			  case 'Y':
			  	outputDate.setFullYear(parseFloat(value));
			  	break;
			  case 'y':
			  	var year=parseFloat(value);
			  	outputDate.setFullYear(((year>20)?1900:2000) + year);
			  	break;			  	
			}
		}
	}
	this.date = outputDate;
	this.selDate = outputDate;
	this.setDate (outputDate);
	return this.selDate;
}


dhtmlxCalendarObject.prototype.setSensitive = function(fromDate,toDate){
  if (fromDate)
		if (fromDate instanceof Date) {
			this.sensitiveFrom = this.cutTime(fromDate);
		} else {
		  this.sensitiveFrom = fromDate.toString ().split (',');
		}
	if (toDate) this.sensitiveTo = this.cutTime(toDate);
	if (this.isAutoDraw) this.draw();
}


// #################################################################################################


function dhtmlxRichSelector(parametres) {
	for (x in parametres)
		this[x] = parametres[x];
	
	this.initValue = this.activeValue;
	if (!this.selectorSize)	this.selectorSize = 7;
	
	var self = this;
	this.blurTimer = null;

	this.nodeBefore.onclick = function() {
		self.show();
	}

	this.editor = document.createElement('TEXTAREA');
	this.editor.value = this.activeValue;
	this.editor._s = this;
	this.editor.className = 'dhtmlxRichSelector';

	this.editor.onfocus = this.onFocus;
	this.editor.onblur = this.onBlur;
	this.editor.onkeydown = this.onKeyDown;
	this.editor.onkeyup = this.onKeyUp;

	this.selector = document.createElement('SELECT');
	this.selector.size = this.selectorSize;
	this.selector.className = 'dhtmlxRichSelector';

	if (this.valueList)
		for (var i = 0; i < this.valueList.length; i++)
			this.selector.options[i] = new Option(this.titleList[i], this.valueList[i], false, false);
		
	this.selector._s = this;
	
	this.selector.onfocus = this.onFocus;
	this.selector.onblur = this.onBlur;
	this.selector.onclick = function () {
		self.onSelect(self.selector.value);
    clearTimeout(self.blurTimer);
	}

	this.selector.getIndexByValue = function (Value, isFull) {
		var Select = this;
		Value = Value.toString().toUpperCase();
		if (!isFull) isFull=false;
		for (var i=0; i<Select.length; i++) {
			var i_value = Select[i].text.toUpperCase();
			if (isFull) {
				if(i_value == Value) return i;
			} else {
				if (i_value.indexOf(Value) == 0) return i;
			}
		}
		if (Select._s.isOrderedList) {
			if (Select._s.isNumbersList)
				if (isNaN(Value)) return -1;
			// first
			i_value = Select[0].text.substring(0, Value.length).toUpperCase();
			if (i_value > Value) return 0;
			// last
			i_value = Select[Select.length-1].text.substring(0, Value.length);
			if (i_value < Value) return Select.length-1;
		}
		return -1;
	}
	
	this.con = document.createElement('DIV')
	this.con.className = 'dhtmlxRichSelector';
	with (this.con.style) {
		width = 'auto';
		display = 'none';
	}

	this.con.appendChild(this.editor);
	this.con.appendChild(this.selector);
	this.nodeBefore.parentNode.insertBefore(this.con, this.nodeBefore);
	return this;
}


dhtmlxRichSelector.prototype.show = function() {
	this.con.style.display = 'block';

	with (this.selector.style) {
		marginTop = parseInt(this.nodeBefore.offsetHeight)+'px';
		width = 'auto';
	}
	with (this.editor.style) {
		width = parseInt(this.nodeBefore.offsetWidth)+15+'px';
		height = parseInt(this.nodeBefore.offsetHeight)+'px';
	}
	this.selector.selectedIndex = this.selector.getIndexByValue(this.activeValue);
	this.editor.focus();
}

dhtmlxRichSelector.prototype.hide = function() {
	this.con.style.display = 'none';
}

dhtmlxRichSelector.prototype.onBlur = function() {
	var self = this._s;
	self.blurTimer = setTimeout(function(){
		if (self.isAllowUserValue) {
			if (self.onSelect(self.editor.value))
				 self.activeValue = self.editor.value;
		} else {
			if (self.onSelect(self.selector.value))
				 self.activeValue = self.selector.value;
		}
	}, 10);
}

dhtmlxRichSelector.prototype.onFocus = function() {
	var self = this._s;
	if(self.blurTimer) {
		clearTimeout(self.blurTimer);
		self.blurTimer = null;
	}
	if (this === this._s.selector)
		self.editor.focus();
}

dhtmlxRichSelector.prototype.onKeyDown = function(e) {
	var self = this._s;
	var e = e || event;
	var isCase = true;
	switch (e.keyCode) {
		case 33: // <Page Up>
			if (self.selector.selectedIndex < self.selector.size) self.selector.selectedIndex = 0;
			else self.selector.selectedIndex -= parseInt(self.selector.size)-1;
			break;
		case 34: // <Page Down>
			if (self.selector.length-self.selector.selectedIndex < self.selector.size) self.selector.selectedIndex = self.selector.length-1;
			else self.selector.selectedIndex += parseInt(self.selector.size)-1;
			break;
		case 35: // <End>
			if (e.ctrlKey) self.selector.selectedIndex = self.selector.length-1;
			break;
		case 36: // <Home>
			if (e.ctrlKey) self.selector.selectedIndex = 0;
			break;
		case 38: // <Arrow Up>
			if (self.selector.selectedIndex == 0)
//				self.selector.selectedIndex = self.selector.length-1;
;
			else self.selector.selectedIndex -= 1;
			break;
		case 40: // <Arrow Down>
			if (self.selector.selectedIndex == self.selector.length-1)
//				self.selector.selectedIndex = 0;
;
			else self.selector.selectedIndex += 1;
			break;
		default:
			isCase = false;
	}
	if (isCase) {
		self.editor.value = self.selector.options[self.selector.selectedIndex].text;
		self.editor.focus();
	}
}

dhtmlxRichSelector.prototype.onKeyUp = function(e) {
	var self = this._s;
	var e = e || event;
	switch (e.keyCode) {
		case 13: // <Enter>
			self.editor.blur();
			break;
		case 27: // <Esc>
			self.editor.value = self.initValue;
			self.selector.selectedIndex = self.selector.getIndexByValue(self.initValue, true);
			self.editor.blur();
			break;
		default:
			var selectedIndex = self.selector.getIndexByValue(self.editor.value);
			if (selectedIndex >= 0)
				self.selector.selectedIndex = selectedIndex;
	}
}


dhtmlxCalendarObject.prototype.isVisible = function(){
	return (this.parent.style.display != 'none'?true:false);
}


dhtmlxCalendarObject.prototype.setHolidays = function(dates){
	this.holidays = dates.toString().split(",");
	if (this.isAutoDraw) this.draw();
}


dhtmlxCalendarObject.prototype.onChangeMonth = function (func) {
  	this.attachEvent ("onChangeMonth",func);
}


dhtmlxCalendarObject.prototype.setInsensitiveDates = function (dates) {
	this.insensitiveDates = dates.toString().split(",");
	if (this.isAutoDraw) this.draw();
}


dhtmlxCalendarObject.prototype.enableTime = function (mode) {
  if (this.time = mode) {
    this.tp = new dhtmlXTimePicker (); 
        this.tp.setPosition (getAbsoluteLeft (cal.parent) + 30, getAbsoluteTop (cal.parent) + 160);   
    
    for (m in dhtmlXTimePicker.prototype)
      (function (m) { 
        if (!dhtmlxCalendarObject.prototype [m])
          dhtmlxCalendarObject.prototype [m] = function (){return this.tp[m].apply(this.tp, arguments)}
      })(m);
  } else {
    this.tp.entBox.parentNode.removeChild (this.tp.entBox);
    this.tp = null;
  }
  this.setSkin(this.skinName);
}

dhtmlxCalendarObject.prototype.setHeaderText = function (text) {
  this.winTitle = text;
  this.headerLabel.childNodes[0].nodeValue = this.winTitle;
  this.headerLabel.setAttribute('title', this.winTitle);
}