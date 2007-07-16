dojo.provide("mywidgets.widget.Calendar");
dojo.require("dojo.date.common");
dojo.require("dojo.date.format");
dojo.require("dojo.date.serialize");
dojo.require("dojo.widget.*");
dojo.require("dojo.widget.HtmlWidget");
dojo.require("dojo.event.*");
dojo.require("dojo.dom");
dojo.require("dojo.html.style");
dojo.require("dojo.json");
dojo.require("dojo.widget.Menu2");
dojo.require("dojo.dnd.*");
dojo.require("dojo.widget.FloatingPane");
dojo.require("dojo.widget.TimePicker");

dojo.widget.defineWidget(
	"mywidgets.widget.Calendar",
	dojo.widget.HtmlWidget,
	{	
		// value: String|Date
		// value property of displayed date
		value: "", 
		// calendarType: String
		// which type of calendar to render first. month, week or day
		calendarType: 'month',
		// m_WeekStartsOn: Integer
		// adjusts the first day of the week in month display 0==Sunday..6==Saturday
		m_WeekStartsOn: "",
		// w_WeekStartsOn: Integer
		// adjusts the first day of the week in week display 0==Sunday..6==Saturday
		w_WeekStartsOn: 1,
		// ShowWeekNo: Bolean
		// if we should use week numbers on week display
		ShowWeekNo: false,
		// selectedtimezone: Object
		// Timezone used. See mywidgets.widget.Timezones
		selectedtimezone: "",
		// timezones: Array
		// Array of all timezones. See mywidgets.widget.Timezones
		timezones: "",
		// eventtypes: Object
		// The different types of events with title and src url.
		eventtypes: {
			meeting: {title: "Meeting", src: dojo.uri.dojoUri("../mywidgets/widget/templates/calimages/meeting.gif")},
			reminder: {title: "Reminder", src: dojo.uri.dojoUri("../mywidgets/widget/templates/calimages/reminder.gif")},
			appointment: {title: "Appointment", src: dojo.uri.dojoUri("../mywidgets/widget/templates/calimages/appointment.gif")}
		},
		
		calendarEvents: {},
		
		changeEventTimes: false,
		createNewEntries: false,
		
		DragObject: "",
		DropObject: "",
		
		templatePath: dojo.uri.dojoUri("../mywidgets/widget/templates/Calendar.html"),
		templateCssPath: dojo.uri.dojoUri("../mywidgets/widget/templates/Calendar.css"),
		
		postMixInProperties: function(){
			mywidgets.widget.Calendar.superclass.postMixInProperties.apply(this, arguments);
			
			if(!this.m_WeekStartsOn){
				this.m_WeekStartsOn = dojo.date.getFirstDayOfWeek(this.lang);
			}
			
			// Localized month names in the template
			//if we dont use unnest, we risk modifying the array inside of dojo.date and screwing up other calendars on the page
			this.monthLabels = dojo.lang.unnest(dojo.date.getNames('months', 'wide', 'standAlone', this.lang));
			
			// Localized day names in the template
			//if we dont use unnest, we risk modifying the array inside of dojo.date and screwing up other calendars on the page
			var m_DayLabels = dojo.lang.unnest(dojo.date.getNames('days', 'wide', 'standAlone', this.lang));
			if(this.m_WeekStartsOn > 0){
				//adjust dayLabels for different first day of week. ie: Monday or Thursday instead of Sunday
				for(var i=0;i<this.m_WeekStartsOn;i++){
					m_DayLabels.push(m_DayLabels.shift());
				}
			}
			this.m_DayLabels = m_DayLabels;
			
			this.today = new Date();
		},
		
		fillInTemplate: function(args, frag) {
			mywidgets.widget.Calendar.superclass.fillInTemplate.apply(this, arguments);
			// Copy style info from input node to output node
			var source = this.getFragNodeRef(frag);
			dojo.html.copyStyle(this.domNode, source);
			
			this._preInitUI(this.today);
		},
		
		_preInitUI: function(dateObj) {	
			dojo.dom.removeChildren(this.calendarHeadNode);
			dojo.dom.removeChildren(this.calendarBodyNode);
			dojo.dom.removeChildren(this.calendarFootNode);
			
			this.value = new Date(dateObj);
			this.firstDay = this._initFirstDay(dateObj);
			this._setLabels();
			
			if(this.calendarType=='month'){
				this._initMonthUI();
			}else if(this.calendarType=='week'){
				this._initWeekUI();
			}else if(this.calendarType=='day'){
				this._initDayUI();
			}
			
			this.onValueChanged(new Date(dateObj));
		},
		
		refreshScreen: function() {
			this._preInitUI(new Date(this.value));
		},
		
		onSetCalendarEntries: function() {
			var hasTimeZone = false;
			if(typeof this.selectedtimezone != "string" && this.selectedtimezone != null){
				hasTimeZone = true;
			}
			
			var allDay = false;
			var startDate, endDate, rStartTime, oDateObject, oLI, oSpan, sHTML, src, oAttributes, iAttr;
			var oDiv, toolTip, oToolTip, tooltipArgs, oImgDiv;
			for(var i in this.calendarEvents){
				allDay = this.calendarEvents[i].allday;
				
				startDate = dojo.date.fromRfc3339(this.calendarEvents[i].starttime);
				if(!allDay && hasTimeZone){
					startDate = this.setTZDate(startDate);
				}
				oDateObject = dojo.byId(dojo.date.toRfc3339(startDate,'dateOnly'));
				if(oDateObject){
					endDate = dojo.date.fromRfc3339(this.calendarEvents[i].endtime);
					if(!allDay && hasTimeZone){
						endDate = this.setTZDate(endDate);
					}
					oLI = document.createElement('li');
					dojo.html.setClass(oLI, "listItem");
					if(allDay){
						dojo.html.setClass(oLI, "listItem allDayEvent");
					}
					oLI.setAttribute("starttime", Number(startDate));
					oLI.setAttribute("endtime", Number(endDate));
					if(oDateObject.childNodes.length > 0){
						if(allDay){
							oDateObject.insertBefore(oLI,oDateObject.childNodes[0]);
						}else{
							insertedLI = false;
							for(var r=0; r<oDateObject.childNodes.length; r++) {
								rStartTime = oDateObject.childNodes[r].getAttribute("starttime");
								if(Number(endDate) <= rStartTime || Number(startDate) <= rStartTime){
									oDateObject.insertBefore(oLI,oDateObject.childNodes[r]);
									insertedLI = true;
									break;
								}
							}
							if(!insertedLI){
								oDateObject.appendChild(oLI);
							}
						}
					}else{
						oDateObject.appendChild(oLI);
					}
					
					oToolTip = document.createElement('span');
					oImgDiv = document.createElement('div');
					oToolTip.appendChild(oImgDiv);
					
					if(this.calendarType!='month'){
						oSpan = document.createElement('span');
					}
					for(var t=0; t<this.calendarEvents[i].type.length; t++) {
						if(this.eventtypes[this.calendarEvents[i].type[t]]){
							oImage = document.createElement("img");
							oImage.setAttribute("title", this.eventtypes[this.calendarEvents[i].type[t]].title);
							oImage.setAttribute("src", this.eventtypes[this.calendarEvents[i].type[t]].src);
							if(this.calendarType!='month'){
								oSpan.appendChild(oImage);
								oLI.appendChild(oSpan);
							}
							oImgDiv.appendChild(oImage.cloneNode(true));
						}
					}
					
					oDiv = document.createElement('div');
					dojo.html.setClass(oDiv, "toolkittime");
					sDate = dojo.date.format(startDate, {formatLength:"medium", selector:"dateOnly", locale:this.lang}) + "<br />";
					sStart = sHTML = sEnd = '';
					if(!allDay){
						oSpan = document.createElement('span');
						if(!this.calendarEvents[i].repeated && this.changeEventTimes){
							dojo.html.setClass(oSpan, "timetext");
						}
						sStart = dojo.date.format(startDate, {formatLength:"short", selector:"timeOnly", locale:this.lang});
						sHTML = '';
						sHTML += ' - ';
						sHTML += dojo.date.format(endDate, {formatLength:"short", selector:"timeOnly", locale:this.lang});
						sEnd = (hasTimeZone?" (" + unescape(this.selectedtimezone.sn) + ")":"");
						
						oSpan.innerHTML = this.calendarType!='month'&&Number(startDate)!=Number(endDate)?sStart+sHTML:sStart;
						oLI.appendChild(oSpan);
					}
					oDiv.innerHTML = sDate + sStart + (Number(startDate)!=Number(endDate)?sHTML:"") + sEnd;
					oToolTip.appendChild(oDiv);
						
					oDiv = document.createElement('div');
					dojo.html.setClass(oDiv, "toolkittitle");
					oDiv.innerHTML = this.calendarEvents[i].title;
					oToolTip.appendChild(oDiv);
					if(this.calendarEvents[i].body != ""){
						oDiv = document.createElement('div');
						dojo.html.setClass(oDiv, "toolkitbody");
						oDiv.innerHTML = this.calendarEvents[i].body;
						oToolTip.appendChild(oDiv);
					}
					
					oLI.setAttribute("itemid", i);
					oSpan = document.createElement('span');
					dojo.html.setClass(oSpan, "titletext");
					
					sHTML = this.calendarEvents[i].title;
					if(this.calendarEvents[i].url != ''){
						sHTML = '<a href="' + this.calendarEvents[i].url + '" target="_blank">' + this.calendarEvents[i].title + '</a>';
					} else if(this.calendarEvents[i].code != ''){
						sHTML = '<a onClick="' + this.calendarEvents[i].code + '">' + this.calendarEvents[i].title + '</a>';
					}
					oSpan.innerHTML = sHTML
					
					sHTMLA = '';
					oAttributes = this.calendarEvents[i].attributes;
					iAttr = 0;
					for(var a in oAttributes) {
						if(iAttr > 0){
							sHTMLA += '<br />';
						}
						sHTMLA += a + ': ' + oAttributes[a];
						iAttr++;
					}
					if(sHTMLA != ""){
						oDiv = document.createElement('div');
						dojo.html.setClass(oDiv, "toolkitattributes");
						oDiv.innerHTML = sHTMLA;
						oToolTip.appendChild(oDiv);
					}
					
					oLI.appendChild(oSpan);
					oSpan.id = "toolTip" + i;
					if(!this.calendarEvents[i].repeated && this.changeEventTimes){
						new dojo.dnd.HtmlDragSource(oLI, "dragListDates");
					}
					
					dojo.body().appendChild(oToolTip);
					tooltipArgs = {
						connectId: "toolTip" + i,
						templateCssPath: "",
						toggle: "fade"
					};
					toolTip = dojo.widget.createWidget("dojo:Tooltip",tooltipArgs,oToolTip);
				}
			}
		},
		
		setCalendarEntries: function(/*Object|String*/entriesObj) {
			/*
			Example:
			entriesObj: {
				"id1": (String - Unique identifier of event) {
					starttime: "2006-12-30T08:05:00-06:00", (String - Formatted according to RFC 3339. See dojo.date.serialize)
					endtime: "2006-12-30T10:05:00-06:00", (String - Formatted according to RFC 3339. See dojo.date.serialize)
					allday: false, (Boolean - Is event an all day event)
					title: "Title 1", (String - Event title)
					url: "http://yourdomain.com/events/thisevent", (String - Event URL (if any))
					body: "This is the body", (String - Event body text (if any))
					attributes: {Location:"Location 1",Chair:"John Doe"}, (Object - All attributes you want in name value pairs)
					type: ["meeting","reminder"] (Array - Event/Icon types you want for this event. See "eventtypes")
				}
			}
			*/
			if(entriesObj != "" && typeof entriesObj=="string"){
				entriesObj = dojo.json.evalJson(entriesObj);
			}
			if(entriesObj){
				this.calendarEvents = entriesObj;
				this.onSetCalendarEntries();
			}
		},
		
		setTimeZones: function(/*Object|String*/timezoneObj) {
			if(timezoneObj != "" && typeof timezoneObj=="string"){
				timezoneObj = dojo.json.evalJson(timezoneObj);
			}
			if(timezoneObj){
				dojo.html.setClass(this.timezoneLabelNode, "selecticon timezoneicon");
				this.timezones = timezoneObj;
			}
		},
		
		_initMonthUI: function() {
			var nextDate = new Date(this.firstDay);
			this.curMonth = new Date(nextDate);
			this.curMonth.setDate(nextDate.getDate()+6); //first saturday gives us the current Month
			this.curMonth.setDate(1);
			var displayWeeks = Math.ceil((dojo.date.getDaysInMonth(this.curMonth) + this._getAdjustedDay(this.curMonth,this.m_WeekStartsOn))/7);
 			var oLabelsTR = this.calendarHeadNode.insertRow(-1);
			var oLabelsTD;
			for(var i=0; i<7; i++) {
				oLabelsTD = oLabelsTR.insertCell(-1);
				oLabelsTD.innerHTML = this.m_DayLabels[i];
			}
			
			var oTR, oTD, oDateDiv, oItemDiv;
			for(var week = 0; week < displayWeeks; week++){
				oTR = this.calendarBodyNode.insertRow(-1);
				oTR.valign = 'top';
				for (var day = 0; day < 7; ++day) {
					oTD = oTR.insertCell(-1);
					var currentClassName = (nextDate.getMonth()<this.value.getMonth())?'otherMonth':(nextDate.getMonth()==this.value.getMonth())?'currentMonth':'otherMonth';
					if(dojo.date.toRfc3339(nextDate,'dateOnly') == dojo.date.toRfc3339(this.today,'dateOnly')){
						currentClassName = currentClassName + " " + "currentDate";
					}
					dojo.html.setClass(oTD, currentClassName);
					
					oDateDiv = document.createElement("div");
					dojo.html.setClass(oDateDiv, "clickDate");
					oDateDiv.setAttribute("date", dojo.date.toRfc3339(nextDate,"dateOnly"));
					dojo.event.connect(oDateDiv, "onclick", this, "onDateClicked");
					oDateDiv.innerHTML = nextDate.getDate();
					
					oTD.appendChild(oDateDiv);
					oItemDiv = document.createElement("div");
					dojo.html.setClass(oItemDiv, "calendarItems");
					var oUL = document.createElement("ul");
					oUL.id = dojo.date.toRfc3339(nextDate,"dateOnly");
					dojo.html.setClass(oUL, "listItems");
					oItemDiv.appendChild(oUL);
					var dt = new dojo.dnd.HtmlDropTarget(oUL, ["dragListDates"]);
					dojo.event.connect(dt, "onDrop", this, dojo.widget.byId(this.widgetId)._dropFunction);
					oTD.appendChild(oItemDiv);
					
					nextDate = dojo.date.add(nextDate, dojo.date.dateParts.DAY, 1);
				}
			}
		},
		
		_initWeekUI: function() {
			function createDateContent(/*Object*/tdObject,/*Date*/dateObj,/*this*/that){
				var oDateDiv = document.createElement("div");
				dojo.html.setClass(oDateDiv, "clickDate weekDate");
				oDateDiv.setAttribute("date", dojo.date.toRfc3339(dateObj,"dateOnly"));
				dojo.event.connect(oDateDiv, "onclick", that, "onDateClicked");
				oDateDiv.innerHTML = dateObj.getDate();
				tdObject.appendChild(oDateDiv);
				var oMonthDiv = document.createElement("div");
				dojo.html.setClass(oMonthDiv, "weekMonth");
				sHTML = dojo.date.format(dateObj, {datePattern:"eeee", selector:"dateOnly", locale:that.lang}) + '<br />';
				sHTML += dojo.date.format(dateObj, {datePattern:"MMMM yyyy", selector:"dateOnly", locale:that.lang});
				oMonthDiv.innerHTML = sHTML;
				tdObject.appendChild(oMonthDiv);
				var oItemDiv = document.createElement("div");
				dojo.html.setClass(oItemDiv, "calendarItems");
				var oUL = document.createElement("ul");
				oUL.id = dojo.date.toRfc3339(dateObj,"dateOnly");
				dojo.html.setClass(oUL, "listItems");
				oItemDiv.appendChild(oUL);
				var dt = new dojo.dnd.HtmlDropTarget(oUL, ["dragListDates"]);
				dojo.event.connect(dt, "onDrop", that, dojo.widget.byId(that.widgetId)._dropFunction);
				
				tdObject.appendChild(oItemDiv);
			}
			
			var nextDate = new Date(this.firstDay);
			var oTR, oTD;
			for (var r = 0; r < 4; ++r) {
				oTR = this.calendarBodyNode.insertRow(-1);
				if(r < 3){
					oTD = oTR.insertCell(-1);
					var currentClassName = "weekDay currentMonth";
					if(dojo.date.toRfc3339(nextDate,'dateOnly') == dojo.date.toRfc3339(this.today,'dateOnly')){
						currentClassName += " " + "currentDate";
					}
					dojo.html.setClass(oTD, currentClassName);
					if(r == 2){
						oTD.rowSpan = 2;
					}
					createDateContent(oTD,nextDate,this);
					nextDate = dojo.date.add(nextDate, dojo.date.dateParts.DAY, 3);
				}
				oTD = oTR.insertCell(-1);
				var currentClassName = "weekDay currentMonth";
				if(dojo.date.toRfc3339(nextDate,'dateOnly') == dojo.date.toRfc3339(this.today,'dateOnly')){
					currentClassName += " " + "currentDate";
				}
				dojo.html.setClass(oTD, currentClassName);
				createDateContent(oTD,nextDate,this);
				if(r == 2){
					nextDate = dojo.date.add(nextDate, dojo.date.dateParts.DAY, 1);
				}else{
					nextDate = dojo.date.add(nextDate, dojo.date.dateParts.DAY, -2);
				}
			}
		},
		
		_initDayUI: function() {
			function createDateContent(/*Object*/tdObject,/*Date*/dateObj,/*this*/that){
				var oDateDiv = document.createElement("div");
				dojo.html.setClass(oDateDiv, "weekDate");
				oDateDiv.innerHTML = dateObj.getDate();
				tdObject.appendChild(oDateDiv);
				var oMonthDiv = document.createElement("div");
				dojo.html.setClass(oMonthDiv, "weekMonth");
				sHTML = dojo.date.format(dateObj, {datePattern:"eeee", selector:"dateOnly", locale:that.lang}) + '<br />';
				sHTML += dojo.date.format(dateObj, {datePattern:"MMMM yyyy", selector:"dateOnly", locale:that.lang});
				oMonthDiv.innerHTML = sHTML;
				tdObject.appendChild(oMonthDiv);
				var oItemDiv = document.createElement("div");
				dojo.html.setClass(oItemDiv, "calendarItems");
				
				var oUL = document.createElement("ul");
				oUL.id = dojo.date.toRfc3339(dateObj,"dateOnly");
				dojo.html.setClass(oUL, "listItems");
				oItemDiv.appendChild(oUL);
				var dt = new dojo.dnd.HtmlDropTarget(oUL, ["dragListDates"]);
				dojo.event.connect(dt, "onDrop", that, dojo.widget.byId(that.widgetId)._dropFunction);
				
				tdObject.appendChild(oItemDiv);
			}
			
			var nextDate = new Date(this.firstDay);
			var oTR, oTD;
			oTR = this.calendarBodyNode.insertRow(-1);
			oTD = oTR.insertCell(-1);
			var currentClassName = "currentMonth";
			if(dojo.date.toRfc3339(nextDate,'dateOnly') == dojo.date.toRfc3339(this.today,'dateOnly')){
				currentClassName += " " + "currentDate";
			}
			dojo.html.setClass(oTD, currentClassName);
			createDateContent(oTD,nextDate,this);
		},
		
		getValue: function() {
			// summary: return current date in RFC 3339 format
			return dojo.date.toRfc3339(new Date(this.value),'dateOnly'); /*String*/
		},

		getDate: function() {
			// summary: return current date as a Date object
			return this.value; /*Date*/
		},
		
		onValueChanged: function(/*Date*/dateObj) {
			//summary: function to overide event by end user
		},
		
		onEventChanged: function(/*string*/eventId, /*object*/eventObject) {
			//summary: function to overide event by end user
		},
		
		_eventChanged: function(/*boolean*/changed,/*string*/eventId,/*Date*/startTime,/*Date*/endTime) {
			if(changed && this.calendarEvents[eventId]){
				//Change the event time and date
				//var oObject = this.calendarEvents[eventId];
				//oObject.starttime = this.updateToRfc3339(startTime);
				//oObject.endtime = this.updateToRfc3339(endTime);
				this.calendarEvents[eventId].starttime = this.updateToRfc3339(startTime);
				this.calendarEvents[eventId].endtime = this.updateToRfc3339(endTime);
				
				
				this.onEventChanged(eventId, this.calendarEvents[eventId]);
				//this.calendarEvents[eventId] = null;
				//this.onEventChanged(eventId, oObject);
			}
			if(!changed){
				this.refreshScreen();
			}
		},
		
		onMoveToDate: function(evt) {
			evt.stopPropagation();
			var d = new Date();
			this.moveToDate(d);
		},
		
		moveToDate: function(/*Date|String*/dateObj) {
			//summary: move to date dateObj and update the UI
			if(typeof dateObj=="string"){
				this.value = dojo.date.fromRfc3339(dateObj);
			}else{
				this.value = new Date(dateObj);
			}
			this._preInitUI(this.value);
		},
		
		onSetCalendarType: function(evt) {
			evt.stopPropagation();
			switch(evt.currentTarget) {
				case this.dayLabelNode:
					this.setCalendarType('day');
					break;
					
				case this.weekLabelNode:
					this.setCalendarType('week');
					break;
					
				case this.monthLabelNode:
					this.setCalendarType('month');
					break;
			}
		},
		
		onDateClicked: function(evt) {
			var eventTarget = evt.target;
			dojo.event.browser.stopEvent(evt);
			this.value = dojo.date.fromRfc3339(eventTarget.getAttribute("date"));
			this.setCalendarType('day');
		},
		
		setCalendarType: function(/*String*/sType) {
			this.calendarType = sType;
			var d = new Date(this.value);
			this._preInitUI(d);
		},
		
		toProperCase: function(/*String*/sString) {
			var stringArray = sString.split(" ");
			var retString = "";
			for(var i=0;i<stringArray.length;i++){
				if(i > 0){
					retString += " ";
				}
				retString += stringArray[i].charAt(0).toUpperCase() + stringArray[i].substring(1,stringArray[i].length).toLowerCase();
			}
			return retString;
		},
		
		_setLabels: function() {
			var d = new Date(this.value);
			var currentMonthLabel = this.monthLabels[d.getMonth()];
			var currentYearLabel = d.getFullYear();
			
			var prevDate,nextDate,prevLabel,nextLabel;
			var lookup = dojo.date._getGregorianBundle(this.lang);
			if(this.calendarType=='month'){
				prevDate = dojo.date.add(d, dojo.date.dateParts.MONTH, -1);
				nextDate = dojo.date.add(d, dojo.date.dateParts.MONTH, 1);
				prevLabel = dojo.date.format(prevDate, {datePattern:"MMM yyyy", selector:"dateOnly", locale:this.lang});
				nextLabel = dojo.date.format(nextDate, {datePattern:"MMM yyyy", selector:"dateOnly", locale:this.lang});
			}else if(this.calendarType=='week'){
				d = new Date(this.firstDay);
				var end = dojo.date.add(d, dojo.date.dateParts.DAY, 6);
				if(d.getMonth() != end.getMonth()){
					currentMonthLabel = this.monthLabels[d.getMonth()] + " - " + this.monthLabels[end.getMonth()];
				}
				if(d.getFullYear() != end.getFullYear()){
					currentYearLabel = d.getFullYear() + " - " + end.getFullYear();
				}
				prevDate = dojo.date.add(d, dojo.date.dateParts.WEEK, -1);
				nextDate = dojo.date.add(d, dojo.date.dateParts.WEEK, 1);
				if(this.ShowWeekNo){
					var prevWeekNo = dojo.date.getWeekOfYear(prevDate, this.w_WeekStartsOn) + 1;
					var currentWeekNo = dojo.date.getWeekOfYear(d, this.w_WeekStartsOn) + 1;
					var nextWeekNo = dojo.date.getWeekOfYear(nextDate, this.w_WeekStartsOn) + 1;
					var fieldWeek = lookup["field-week"];
					prevLabel = fieldWeek + " " + prevWeekNo;
					nextLabel = fieldWeek + " " + nextWeekNo;
					currentLabel = fieldWeek + " " + currentWeekNo + " - " + currentLabel;
				}else{
					prevLabel = dojo.date.format(prevDate, {formatLength:"medium", selector:"dateOnly", locale:this.lang});
					nextLabel = dojo.date.format(nextDate, {formatLength:"medium", selector:"dateOnly", locale:this.lang});
				}
			}else if(this.calendarType=='day'){
				d = new Date(this.firstDay);
				prevDate = dojo.date.add(d, dojo.date.dateParts.DAY, -1);
				nextDate = dojo.date.add(d, dojo.date.dateParts.DAY, 1);
				prevLabel = dojo.date.format(prevDate, {formatLength:"medium", selector:"dateOnly", locale:this.lang});
				nextLabel = dojo.date.format(nextDate, {formatLength:"medium", selector:"dateOnly", locale:this.lang});
			}
			
			this.prevLabelNode.innerHTML = prevLabel;
			this.currentMonthLabelNode.innerHTML = currentMonthLabel;
			this.currentYearLabelNode.innerHTML = currentYearLabel;
			this.nextLabelNode.innerHTML = nextLabel;
			
			//Top icons
			this.dayLabelNode.title = this.toProperCase(lookup["field-day"]);
			this.weekLabelNode.title = this.toProperCase(lookup["field-week"]);
			this.monthLabelNode.title = this.toProperCase(lookup["field-month"]);
			this.todayLabelNode.title = this.toProperCase(dojo.date.format(this.today, {formatLength:"long", selector:"dateOnly", locale:this.lang}));
			
			if(this.createNewEntries){
				dojo.html.setClass(this.newEntryLabelNode, "selecticon newentryicon");
			}else{
				dojo.html.setClass(this.newEntryLabelNode, "");
			}
			
			if(this.timezones != ""){
				dojo.html.setClass(this.timezoneLabelNode, "selecticon timezoneicon");
				if(typeof this.selectedtimezone != "string" && this.selectedtimezone != null){
					this.timezoneLabelNode.title = this.toProperCase(lookup["field-zone"]) + ": " + unescape(this.selectedtimezone.sn);
					var oTR = document.createElement('tr');
					var oTD = document.createElement('td');
					oTD.colSpan = "6";
					oTD.style.paddingLeft = "3px";
					oTD.innerHTML = this.toProperCase(lookup["field-zone"]) + ": " + this.selectedtimezone.name;
					oTR.appendChild(oTD);
					this.calendarFootNode.appendChild(oTR);
				}else{
					this.timezoneLabelNode.title = this.toProperCase(lookup["field-zone"]);
				}
			}else{
				dojo.html.setClass(this.timezoneLabelNode, "");
			}
		},
		
		menuItemSelected: function(/*string*/type, /*number*/value){
			var d = new Date(this.value);
			if(type == 'month'){
				d = d.setMonth(value);
				var newDate = new Date(d);
				if(newDate.getMonth() != value){
					var days = dojo.date.getDaysInMonth(new Date(newDate.getFullYear(), newDate.getMonth()-1));
					d = new Date(newDate.getFullYear(), newDate.getMonth()-1, days);
				}
			}else if(type == 'year'){
				d = d.setFullYear(value);
			}
			this.moveToDate(d);
		},
		
		showMenu: function(evt) {
			evt.stopPropagation();
			var sWidgetId = this.widgetId;
			var d = new Date(this.value);
			var menu;
			switch(evt.currentTarget) {
				case this.currentMonthLabelNode:
					//Create month menu
					menu = dojo.widget.createWidget("PopupMenu2", {});
					var attr, newDate;
					var iMonth = 0;
					for(var i=0;i<this.monthLabels.length;i++){
						var sValue = this.monthLabels[i]+" hello";
						attr = {
							templateString:
		 						'<tr class="dojoMenuItem2" dojoAttachEvent="onMouseOver: onHover; onMouseOut: onUnhover; onClick: _onClick; onKey:onKey;">'
								+'<td>&nbsp;</td>'
								+'<td tabIndex="-1" class="dojoMenuItem2Label">${this.caption}</td>'
								+'<td class="dojoMenuItem2Accel">${this.accelKey}</td>'
								+'</tr>',
							caption: this.toProperCase(this.monthLabels[i]), 
							month: i,
							disabled: (d.getMonth()==i?true:false), 
							onClick:  function(){ dojo.widget.byId(sWidgetId).menuItemSelected('month',this.month); }
						};
						menu.addChild(dojo.widget.createWidget("MenuItem2", attr));
						iMonth++;
					}
					break;
					
				case this.currentYearLabelNode:
					//Create year menu
					var prevYear = dojo.date.add(d, dojo.date.dateParts.YEAR, -2);
					menu = dojo.widget.createWidget("PopupMenu2", {});
					var attr, newDate;
					for(var i=0;i<13;i++){
						attr = {
							templateString:
		 						'<tr class="dojoMenuItem2" dojoAttachEvent="onMouseOver: onHover; onMouseOut: onUnhover; onClick: _onClick; onKey:onKey;">'
								+'<td>&nbsp;</td>'
								+'<td tabIndex="-1" class="dojoMenuItem2Label">${this.caption}</td>'
								+'<td class="dojoMenuItem2Accel">${this.accelKey}</td>'
								+'</tr>',
							caption: prevYear.getFullYear()+i, 
							disabled: (prevYear.getFullYear()+i==d.getFullYear()?true:false),
							onClick:  function(){ dojo.widget.byId(sWidgetId).menuItemSelected('year',this.caption); }
						};
						menu.addChild(dojo.widget.createWidget("MenuItem2", attr));
					}
					break;
			}
			menu.open(evt.currentTarget, null, evt.currentTarget);
		},
		
		dayOfWeek: function(day,month,year) {
			var a = Math.floor((14 - month)/12);
			var y = year - a;
			var m = month + 12*a - 2;
			var d = (day + y + Math.floor(y/4) - Math.floor(y/100) + Math.floor(y/400) + Math.floor((31*m)/12)) % 7;
			return d + 1;
		},
		
		NthDay: function(nth,weekday,month,year) {
			if (nth > 0){
				return (nth-1)*7 + 1 + (7 + weekday - this.dayOfWeek((nth-1)*7 + 1,month,year))%7;
			}
			var days = dojo.date.getDaysInMonth(new Date(year,month));
			return days - (this.dayOfWeek(days,month,year) - weekday + 7)%7;
		},
		
		isDST: function(/*Date*/dateObject) {
			if(this.selectedtimezone.dst == 0){
				return false;
			}else{
				var year = dateObject.getFullYear();
				var aDST = this.selectedtimezone.dst.split(',');
				var aStandard = this.selectedtimezone.standard.split(',');
				var startMonth = aDST[0];
				var startNumber = aDST[1];
				var startDayOfWeek = aDST[2];
				var endMonth = aStandard[0];
				var endNumber = aStandard[1];
				var endDayOfWeek = aStandard[2];
				var startDST = new Date(year,startMonth-1,this.NthDay(startNumber,startDayOfWeek,startMonth,year),2,dateObject.getTimezoneOffset()+this.selectedtimezone.offset);
				var endDST = new Date(year,endMonth-1,this.NthDay(endNumber,endDayOfWeek,endMonth,year),2,dateObject.getTimezoneOffset()+this.selectedtimezone.offset);
				if(Number(startDST) < Number(endDST)){
					if(Number(dateObject) > Number(startDST) && Number(dateObject) < Number(endDST)){
						return true;
					}else{
						return false;
					}
				}else{
					endDST = new Date(year+1,endMonth-1,this.NthDay(endNumber,endDayOfWeek,endMonth,year+1),2,dateObject.getTimezoneOffset()+this.selectedtimezone.offset);
					if(Number(dateObject) > Number(startDST) && Number(dateObject) < Number(endDST)){
						return true;
					}else{
						return false;
					}
				}
			}
		},
		
		setTZDate: function(/*Date*/dateObject) {
			var DSTOffset = this.isDST(dateObject)?3600000:0;
			var utc = dateObject.getTime() + (dateObject.getTimezoneOffset() * 60000);
			return new Date(utc + (this.selectedtimezone.offset*60000) + DSTOffset);
		},
		
		setAbleToCreateNew: function (/*Bolean*/bAble) {
			this.createNewEntries = bAble;
			if(bAble){
				dojo.html.setClass(this.newEntryLabelNode, "selecticon newentryicon");
			}
		},
		
		createNewEntry: function (evt) {
			evt.stopPropagation();
			if(dojo.widget.byId('newentrydialog')){
				dojo.widget.byId('newentrydialog').show();
			}else{
				var width = "460px";
				var height = "350px";
				var div = document.createElement("div");
				div.style.position="absolute";
				div.style.width = width;
				div.style.height = height;
				dojo.body().appendChild(div);
				var pars = {
					contentClass: "mywidgets:CalendarDialogNewEntry",
					openerId: this.widgetId,
					title: "New Entry",
					iconSrc: dojo.uri.dojoUri("../mywidgets/widget/templates/calimages/calendar_add.gif"),
					id: "newentrydialog",
					width: width,
					height: height,
					resizable: false
				};
				var widget = dojo.widget.createWidget("mywidgets:CalendarDialog", pars, div);
			}
		},
		
		onNewEntry: function(/*object*/oEntry) {
			//summary: function to overide event by end user
		},
		
		_createNewEntry: function(/*object*/oEntry) {
			this.onNewEntry(oEntry);
			var d = new Date(this.value);
			this._preInitUI(d);
		},
		
		showTimeZone: function (evt) {
			evt.stopPropagation();
			if(dojo.widget.byId('timezonedialog')){
				dojo.widget.byId('timezonedialog').show();
			}else{
				if(this.timezones != ""){
					var lookup = dojo.date._getGregorianBundle(this.lang);
					var width = "445px";
					var height = "130px";
					var div = document.createElement("div");
					div.style.position="absolute";
					div.style.width = width;
					div.style.height = height;
					dojo.body().appendChild(div);
					var pars = {
						contentClass: "mywidgets:CalendarDialogTimezone",
						openerId: this.widgetId,
						title: this.toProperCase(lookup["field-zone"]),
						iconSrc: dojo.uri.dojoUri("../mywidgets/widget/templates/calimages/timezone_icon.png"),
						id: "timezonedialog",
						width: width,
						height: height,
						resizable: false
					};
					var widget = dojo.widget.createWidget("mywidgets:CalendarDialog", pars, div);
				}
			}
		},
		
		onSetTimeZone: function() {
			//summary: function to overide event by end user
		},
		
		_setTimeZone: function(/*string*/shortname) {
			if(shortname == ''){
				this.selectedtimezone = "";
			}else{
				for(var i=0;i<this.timezones.length;i++){
					if(this.timezones[i].sn == shortname){
						this.selectedtimezone = this.timezones[i];
						break;
					}
				}
			}
			this.onSetTimeZone();
			var d = new Date(this.value);
			this._preInitUI(d);
		},
		
		updateToRfc3339: function(/*Date*/dateObject){
			var _ = dojo.string.pad;
			var formattedDate = [];
			var date = [_(dateObject.getFullYear(),4), _(dateObject.getMonth()+1,2), _(dateObject.getDate(),2)].join('-');
			formattedDate.push(date);
				
			var time = [_(dateObject.getHours(),2), _(dateObject.getMinutes(),2), _(dateObject.getSeconds(),2)].join(':');
			var timezoneOffset = dateObject.getTimezoneOffset();
			if(typeof this.selectedtimezone != "string" && this.selectedtimezone != null){
				timezoneOffset = -this.selectedtimezone.offset;
			}
			time += (timezoneOffset > 0 ? "-" : "+") + _(Math.floor(Math.abs(timezoneOffset)/60),2) + ":" + _(Math.abs(timezoneOffset)%60,2);
			formattedDate.push(time);
			
			return formattedDate.join('T'); // String
		},
		
		_dropFunction: function(evt){
			evt.stopPropagation();
			try{
				this.DragObject = evt.dragObject.domNode;
				this.DropObject = evt.dropTarget;
				if(this.DropObject.tagName == 'LI'){
					this.DropObject = this.DropObject.parentNode;
				}
				
				var eventId = this.DragObject.getAttribute("itemid");
				if(this.calendarEvents[eventId].allday){
					var starttime = new Date(parseInt(this.DragObject.getAttribute("starttime")));
					var endtime = new Date(parseInt(this.DragObject.getAttribute("endtime")));
					var dropDate = dojo.date.fromRfc3339(this.DropObject.getAttribute("id"));
					starttime.setFullYear(dropDate.getFullYear());
					starttime.setMonth(dropDate.getMonth());
					starttime.setDate(dropDate.getDate());
					endtime.setFullYear(dropDate.getFullYear());
					endtime.setMonth(dropDate.getMonth());
					endtime.setDate(dropDate.getDate());
					this._eventChanged(true, eventId, starttime, endtime);
				}else{
					var width = "300px";
					var height = "290px";
					var div = document.createElement("div");
					div.style.position="absolute";
					div.style.width = width;
					div.style.height = height;
					dojo.body().appendChild(div);
					var pars = {
						contentClass: "mywidgets:CalendarDialogChangeTime",
						openerId: this.widgetId,
						title: "Change",
						iconSrc: dojo.uri.dojoUri("../mywidgets/widget/templates/calimages/timezone_icon.png"),
						width: width,
						height: height,
						resizable: false
					};
					var widget = dojo.widget.createWidget("mywidgets:CalendarDialog", pars, div);
				}
			}catch(e){}
		},
		
		onIncrementCalendar: function(evt) {
			evt.stopPropagation();
			var d = new Date(this.value);
			switch(evt.currentTarget) {
				case this.nextLabelNode:
					if(this.calendarType=='month'){
						d = dojo.date.add(d, dojo.date.dateParts.MONTH, 1);
					}else if(this.calendarType=='week'){
						d = dojo.date.add(d, dojo.date.dateParts.WEEK, 1);
					}else if(this.calendarType=='day'){
						d = dojo.date.add(d, dojo.date.dateParts.DAY, 1);
					}
					break;
					
				case this.prevLabelNode:
					if(this.calendarType=='month'){
						d = dojo.date.add(d, dojo.date.dateParts.MONTH, -1);
					}else if(this.calendarType=='week'){
						d = dojo.date.add(d, dojo.date.dateParts.WEEK, -1);
					}else if(this.calendarType=='day'){
						d = dojo.date.add(d, dojo.date.dateParts.DAY, -1);
					}
					break;
			}
			
			this._preInitUI(d);
		},
		
		_initFirstDay: function(/*Date*/dateObj){
			//adj: false for first day of month, true for first day of week adjusted by startOfWeek
			var d = new Date(dateObj);
			if(this.calendarType=='month'){
				d.setDate(1);
				d.setDate(d.getDate()-this._getAdjustedDay(d,this.m_WeekStartsOn));
			}else if(this.calendarType=='week'){
				d.setDate(d.getDate()-this._getAdjustedDay(d,this.w_WeekStartsOn));
			}
			d.setHours(0,0,0,0);
			return d; // Date
		},
		
		_getAdjustedDay: function(/*Date*/dateObj,/*Intiger*/startsOn){
			//summary: used to adjust date.getDay() values to the new values based on the current first day of the week value
			var days = [0,1,2,3,4,5,6];
			if(startsOn>0){
				for(var i=0;i<startsOn;i++){
					days.unshift(days.pop());
				}
			}
			return days[dateObj.getDay()]; // Number: 0..6 where 0=Sunday
		},
		
		destroy: function(){
			mywidgets.widget.Calendar.superclass.destroy.apply(this, arguments);
			//dojo.html.destroyNode(this.m_WeekTemplate);
		}
	}
);

dojo.widget.defineWidget(
	"mywidgets.widget.CalendarDialog",
	[dojo.widget.HtmlWidget, dojo.widget.FloatingPaneBase, dojo.widget.ModalDialogBase],
	{
		// summary:
		//		Provides a Dialog which can be modal or normal.
		templatePath: dojo.uri.dojoUri("src/widget/templates/Editor2/EditorDialog.html"),
		// modal: Boolean: Whether this is a modal dialog. True by default.
		modal: true,
		// width: String: Width of the dialog. None by default.
		width: "",
		// height: String: Height of the dialog. None by default.
		height: "",
		// windowState: String: startup state of the dialog
		windowState: "normal",
		displayCloseAction: true,
		// contentClass: String
		contentClass: "",
		openerId: "",

		fillInTemplate: function(args, frag){
			this.fillInFloatingPaneTemplate(args, frag);
			mywidgets.widget.CalendarDialog.superclass.fillInTemplate.call(this, args, frag);
		},
		postCreate: function(){
			if(this.modal){
				dojo.widget.ModalDialogBase.prototype.postCreate.call(this);
			}else{
				with(this.domNode.style) {
					zIndex = 999;
					display = "none";
				}
			}
			dojo.widget.FloatingPaneBase.prototype.postCreate.apply(this, arguments);
			mywidgets.widget.CalendarDialog.superclass.postCreate.call(this);
			if(this.width && this.height){
				with(this.domNode.style){
					width = this.width;
					height = this.height;
				}
			}
		},
		createContent: function(){
			if(!this.contentWidget && this.contentClass){
				this.contentWidget = dojo.widget.createWidget(this.contentClass);
				this.addChild(this.contentWidget);
			}
		},
		show: function(){
			if(!this.contentWidget){
				//buggy IE: if the dialog is hidden, the button widgets
				//in the dialog can not be shown, so show it temporary (as the
				//dialog may decide not to show it in loadContent() later)
				mywidgets.widget.CalendarDialog.superclass.show.apply(this, arguments);
				this.createContent();
				mywidgets.widget.CalendarDialog.superclass.hide.call(this);
			}

			if(!this.contentWidget || !this.contentWidget.loadContent()){
				return;
			}
			this.showFloatingPane();
			mywidgets.widget.CalendarDialog.superclass.show.apply(this, arguments);
			if(this.modal){
				this.showModalDialog();
			}
			if(this.modal){
				//place the background div under this modal pane
				this.bg.style.zIndex = this.domNode.style.zIndex-1;
			}
		},
		onShow: function(){
			mywidgets.widget.CalendarDialog.superclass.onShow.call(this);
			this.onFloatingPaneShow();
		},
		closeWindow: function(){
			this.hide();
			mywidgets.widget.CalendarDialog.superclass.closeWindow.apply(this, arguments);
		},
		hide: function(){
			if(this.modal){
				this.hideModalDialog();
			}
			mywidgets.widget.CalendarDialog.superclass.hide.call(this);
		},
		//modified from ModalDialogBase.checkSize to call _sizeBackground conditionally
		checkSize: function(){
			if(this.isShowing()){
				if(this.modal){
					this._sizeBackground();
				}
				this.placeModalDialog();
				this.onResized();
			}
		}
	}
);

dojo.widget.defineWidget(
	"mywidgets.widget.CalendarDialogTimezone",
	dojo.widget.HtmlWidget,
{
	// summary:
	// This is the actual content.
	templatePath: dojo.uri.dojoUri("../mywidgets/widget/templates/timezones.html"),
	widgetsInTemplate: true,
	openerId: "",

	loadContent:function(){
		// summary: Load the content. Called when first shown
		this.openerId = dojo.widget.byId(this.parent.openerId);
		this.timezonesnode.options.length = 0;
		this.timezonesnode.options[this.timezonesnode.options.length] = new Option("Default", "");
		for (var i = 0; i < this.openerId.timezones.length; i++){ 
			this.timezonesnode.options[this.timezonesnode.options.length] = new Option(this._buildGMT(this.openerId.timezones[i].offset) + this.openerId.timezones[i].name, this.openerId.timezones[i].sn);
			if(this.openerId.selectedtimezone && this.openerId.timezones[i].sn == this.openerId.selectedtimezone.sn){
				this.timezonesnode.options[this.timezonesnode.options.length-1].selected = true;
			}
		}
		return true;
	},
	_buildGMT: function(/*int*/ offset) {
		if (offset == 0)
			return "(GMT) ";
		
		var hour = Math.abs(parseInt(offset/60));
		var minute = 60 * (Math.abs(offset/60) - hour);
		return "(GMT" + (offset < 0 ? '-' : '+') + dojo.string.pad(""+hour, 2) + ':' + dojo.string.pad(""+minute, 2) + ') '; // string
	},
	ok: function(){
		this.openerId._setTimeZone(this.timezonesnode.options[this.timezonesnode.selectedIndex].value);
		this.cancel();
	},
	cancel: function(){
		this.parent.hide();
	}
});

dojo.widget.defineWidget(
	"mywidgets.widget.CalendarDialogChangeTime",
	dojo.widget.HtmlWidget,
{
	// summary:
	// This is the actual content.
	templatePath: dojo.uri.dojoUri("../mywidgets/widget/templates/changetime.html"),
	widgetsInTemplate: true,
	openerId: "",
	itemId: "",
	eventChanged: false,
	dropDate: "",
	starttime: "",
	endtime: "",
	newstarttime: "",
	newendtime: "",

	loadContent:function(){
		// summary: Load the content. Called when first shown
		this.openerId = dojo.widget.byId(this.parent.openerId);
		var oDragObject = this.openerId.DragObject;
		var oDropObject = this.openerId.DropObject;
		
		this.itemId = oDragObject.getAttribute("itemid");
		this.starttime = new Date(parseInt(oDragObject.getAttribute("starttime")));
		this.endtime = new Date(parseInt(oDragObject.getAttribute("endtime")));

		this.dropDate = dojo.date.fromRfc3339(oDropObject.getAttribute("id"));
		this.date_node.innerHTML = dojo.date.format(this.dropDate, {formatLength:"medium", selector:"dateOnly", locale:this.openerId.lang});
		
		var startPars = {
			storedTime: dojo.date.toRfc3339(this.starttime),
			lang: this.openerId.lang
		};
		this.startPicker = dojo.widget.createWidget("TimePicker", startPars, this.starttime_node);
		
		var endPars = {
			storedTime: dojo.date.toRfc3339(this.endtime),
			lang: this.openerId.lang
		};
		this.endPicker = dojo.widget.createWidget("TimePicker", endPars, this.endtime_node);
		
		return true;
	},
	
	ok: function(){
		this.eventChanged = true;
		
		this.newstarttime = new Date(this.startPicker.time);
		this.newendtime = new Date(this.endPicker.time);
		
		this.newstarttime.setFullYear(this.dropDate.getFullYear());
		this.newstarttime.setMonth(this.dropDate.getMonth());
		this.newstarttime.setDate(this.dropDate.getDate());
		this.newendtime.setFullYear(this.dropDate.getFullYear());
		this.newendtime.setMonth(this.dropDate.getMonth());
		this.newendtime.setDate(this.dropDate.getDate());
		
		this.cancel();
	},
	
	cancel: function(){
		this.parent.hide();
		this.openerId._eventChanged(this.eventChanged, this.itemId, this.newstarttime, this.newendtime);
		this.startPicker.destroy();
		this.endPicker.destroy();
	}
});

dojo.widget.defineWidget(
	"mywidgets.widget.CalendarDialogNewEntry",
	dojo.widget.HtmlWidget,
{
	// summary:
	// This is the actual content.
	templatePath: dojo.uri.dojoUri("../mywidgets/widget/templates/newcalendarentry.html"),
	widgetsInTemplate: true,
	openerId: "",
	
	loadContent:function(){
		// summary: Load the content. Called when first shown
		this.openerId = dojo.widget.byId(this.parent.openerId);
		this.ne_subject.value = "";
		this.ne_location.value = "";
		this.ne_categories.value = "";
		this.ne_body.value = "";
		this.ne_alldayevent.checked = false;
		var dDate = new Date();
		this.ne_starttime.value = dojo.date.format(dDate, {formatLength:"short", selector:"dateTime"});
		dDate.setHours(dDate.getHours()+1);
		this.ne_endtime.value = dojo.date.format(dDate, {formatLength:"short", selector:"dateTime"});
		var oEventtypes = this.openerId.eventtypes;
		this.ne_type.options.length = 0;
		this.ne_type.options[this.ne_type.options.length] = new Option("No Type", "");
		for(var i in oEventtypes){
			this.ne_type.options[this.ne_type.options.length] = new Option(oEventtypes[i].title, oEventtypes[i].title);
		}
		return true;
	},
	
	alldayclicked: function(){
		if(this.ne_alldayevent.checked){
			var dDate = dojo.date.parse(this.ne_starttime.value, {selector:"dateTime", formatLength:"short"});
			if(dDate == null){
				dDate = new Date();
			}
			this.ne_starttime.value = dojo.date.format(dDate, {formatLength:"short", selector:"dateOnly"});
			this.ne_endtime.value = "";
			this.ne_endtime.disabled = true;
		}else{
			var dDate = dojo.date.parse(this.ne_starttime.value, {selector:"dateOnly", formatLength:"short"});
			if(dDate == null){
				dDate = new Date();
			}else{
				var newDate = new Date();
				dDate.setHours(newDate.getHours(), newDate.getMinutes());
			}
			this.ne_starttime.value = dojo.date.format(dDate, {formatLength:"short", selector:"dateTime"});
			dDate.setHours(dDate.getHours()+1);
			this.ne_endtime.disabled = false;
			this.ne_endtime.value = dojo.date.format(dDate, {formatLength:"short", selector:"dateTime"});
		}
	},
	
	ok: function(){
		var isOk = true;
		var alertText = '';
		if(this.ne_subject.value == ""){
			isOk = false;
			alertText += '<br />' + 'Title:';
		}
		var attr;
		if(this.ne_alldayevent.checked){
			attr = {
				selector:"dateOnly", 
				formatLength:"short"
			};
		}else{
			attr = {
				selector:"dateTime", 
				formatLength:"short"
			};
		}
		var dStartDate = dojo.date.parse(this.ne_starttime.value, attr);
		if(dStartDate == null){
			isOk = false;
			dStartDate = new Date();
			alertText += '<br />' + 'Start time: Please format time correctly!<br />i.e. ' + dojo.date.format(dStartDate, attr);
		}
		var dEndDate;
		if(!this.ne_alldayevent.checked){
			dEndDate = dojo.date.parse(this.ne_endtime.value, {selector:"dateTime", formatLength:"short"});
			if(dEndDate == null){
				isOk = false;
				dEndDate = new Date();
				alertText += '<br />' + 'End time: Please format time correctly!<br />i.e. ' + dojo.date.format(dEndDate, {formatLength:"short"});
			}
		}else{
			dEndDate = dStartDate;
		}
		
		if(!isOk){
			dojo.require("mywidgets.widget.ModalAlert");
			var params = {
				height: "230px",
				iconSrc: dojo.uri.dojoUri("../mywidgets/widget/templates/images/error.gif"),
				alertText: '<strong>Please edit/complete the following field(s):</strong><br />' + alertText
			};
			var modal = new mywidgets.widget.ModalAlert(params);
		}else{
			var oEntry = {
				starttime: dojo.date.toRfc3339(dStartDate),
				endtime: dojo.date.toRfc3339(dEndDate),
				allday: this.ne_alldayevent.checked,
				repeated: false,
				title: this.ne_subject.value,
				url: "",
				body: this.ne_body.value,
				attributes: {
					Location: this.ne_location.value,
					Categories: this.ne_categories.value
				},
				type: [this.ne_type.options[this.ne_type.selectedIndex].value]
			};
			
			this.openerId._createNewEntry(oEntry);
			this.cancel();
		}
	},
	
	cancel: function(){
		this.parent.hide();
	}
});
