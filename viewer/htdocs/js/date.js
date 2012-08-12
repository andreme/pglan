
Date.prototype.format = function(format) {
	var returnStr = '';
	var replace = Date._replaceChars;
	for (var i = 0; i < format.length; i++) {
		var curChar = format.charAt(i);
		if (i - 1 >= 0 && format.charAt(i - 1) == "\\") {
			returnStr += curChar;
		}
		else if (replace[curChar]) {
			returnStr += replace[curChar].call(this);
		} else if (curChar != "\\"){
			returnStr += curChar;
		}
	}
	return returnStr;
};

Date.prototype.compare = function(date) {
	if (!(date instanceof Date) || !isFinite(date.valueOf())) {
		return null;
	}
	var a = this.valueOf(), b = date.valueOf();
	return (a>b)-(a<b);
};

Date.prototype.equals = function(date) {
	return this.compare(date) === 0;
};

Date.prototype.modify = function(string) {
	var thisDate = this;

	var reg = /([+\-]?)([1-9][0-9]*) ?([a-z]+)/ig;
	var result = [];
	while ((result = reg.exec(string)) !== null) {
		var add = result[1] != '-';
		var range = parseInt(result[2]) * (add ? 1 : -1);
		var interval = result[3].toLowerCase().replace(/s%/i, '');

		if (Date._modifyFunctions[interval]) {
			Date._modifyFunctions[interval].call(thisDate, range);
		}
	}
	return this;
};

Date._modifyFunctions = {
	'second': /** @this {Date} */ function(n) {this.setSeconds(this.getSeconds() + n);},
	'minute': /** @this {Date} */ function(n) {this.setMinutes(this.getMinutes() + n);},
	'hour': /** @this {Date} */ function(n) {this.setHours(this.getHours() + n);},
	'day': /** @this {Date} */ function(n) {Date._modifyFunctions['hour'].call(this,n*24)},
	'week': /** @this {Date} */ function(n) {Date._modifyFunctions['day'].call(this,n*7)},
	'month': /** @this {Date} */ function(n) {
		var curMonth = this.getMonth();
		var newDay = this.getDate();
		var newMonth = curMonth + n % 12;
		var newYear = this.getFullYear() + Math.floor(curMonth + n / 12);
		var monthDays = Date._replaceChars.monthDays;
		monthDays[1] = ((this.format('L') === (true).toString()) ? 29 : monthDays[1]);
		if (newDay == monthDays[curMonth] || newDay > monthDays[newMonth]) {
			newDay = monthDays[newMonth];
		}
		this.setFullYear(newYear, newMonth, newDay);
	},
	'year': /** @this {Date} */ function(n) {this.setFullYear(this.getFullYear() + n);}
};

Date._replaceChars = {
    shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    longMonths: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    shortDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    longDays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    monthDays: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],

    // Day
    'd': /** @this {Date} */ function() {return (this.getDate() < 10 ? '0' : '') + this.getDate();},
    'D': /** @this {Date} */ function() {return Date._replaceChars.shortDays[this.getDay()];},
    'j': /** @this {Date} */ function() {return this.getDate();},
    'l': /** @this {Date} */ function() {return Date._replaceChars.longDays[this.getDay()];},
    'N': /** @this {Date} */ function() {return this.getDay() + 1;},
    'S': /** @this {Date} */ function() {return (this.getDate() % 10 == 1 && this.getDate() != 11 ? 'st' : (this.getDate() % 10 == 2 && this.getDate() != 12 ? 'nd' : (this.getDate() % 10 == 3 && this.getDate() != 13 ? 'rd' : 'th')));},
    'w': /** @this {Date} */ function() {return this.getDay();},
    'z': /** @this {Date} */ function() {var d = new Date(this.getFullYear(),0,1);return Math.ceil((this - d) / 86400000);}, // Fixed now
    // Week
    'W': /** @this {Date} */ function() {var d = new Date(this.getFullYear(), 0, 1);return Math.ceil((((this - d) / 86400000) + d.getDay() + 1) / 7);}, // Fixed now
    // Month
    'F': /** @this {Date} */ function() {return Date._replaceChars.longMonths[this.getMonth()];},
    'm': /** @this {Date} */ function() {return (this.getMonth() < 9 ? '0' : '') + (this.getMonth() + 1);},
    'M': /** @this {Date} */ function() {return Date._replaceChars.shortMonths[this.getMonth()];},
    'n': /** @this {Date} */ function() {return this.getMonth() + 1;},
    't': /** @this {Date} */ function() {var d = new Date();return new Date(d.getFullYear(), d.getMonth(), 0).getDate()}, // Fixed now, gets #days of date
    // Year
    'L': /** @this {Date} */ function() {var year = this.getFullYear();return (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0));},   // Fixed now
    'o': /** @this {Date} */ function() {var d  = new Date(this.valueOf());d.setDate(d.getDate() - ((this.getDay() + 6) % 7) + 3);return d.getFullYear();}, //Fixed now
    'Y': /** @this {Date} */ function() {return this.getFullYear();},
    'y': /** @this {Date} */ function() {return ('' + this.getFullYear()).substr(2);},
    // Time
    'a': /** @this {Date} */ function() {return this.getHours() < 12 ? 'am' : 'pm';},
    'A': /** @this {Date} */ function() {return this.getHours() < 12 ? 'AM' : 'PM';},
    'B': /** @this {Date} */ function() {return Math.floor((((this.getUTCHours() + 1) % 24) + this.getUTCMinutes() / 60 + this.getUTCSeconds() / 3600) * 1000 / 24);}, // Fixed now
    'g': /** @this {Date} */ function() {return this.getHours() % 12 || 12;},
    'G': /** @this {Date} */ function() {return this.getHours();},
    'h': /** @this {Date} */ function() {return ((this.getHours() % 12 || 12) < 10 ? '0' : '') + (this.getHours() % 12 || 12);},
    'H': /** @this {Date} */ function() {return (this.getHours() < 10 ? '0' : '') + this.getHours();},
    'i': /** @this {Date} */ function() {return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes();},
    's': /** @this {Date} */ function() {return (this.getSeconds() < 10 ? '0' : '') + this.getSeconds();},
    'u': /** @this {Date} */ function() {var m = this.getMilliseconds();return (m < 10 ? '00' : (m < 100 ? '0' : '')) + m;},
    // Timezone
    'e': /** @this {Date} */ function() {return "Not Yet Supported";},
    'I': /** @this {Date} */ function() {return "Not Yet Supported";},
    'O': /** @this {Date} */ function() {return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + '00';},
    'P': /** @this {Date} */ function() {return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + ':00';}, // Fixed now
    'T': /** @this {Date} */ function() {var m = this.getMonth();this.setMonth(0);var result = this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/, '$1');this.setMonth(m);return result;},
    'Z': /** @this {Date} */ function() {return -this.getTimezoneOffset() * 60;},
    // Full Date/Time
    'c': /** @this {Date} */ function() {return this.format("Y-m-d\\TH:i:sP");}, // Fixed now
    'r': /** @this {Date} */ function() {return this.toString();},
    'U': /** @this {Date} */ function() {return this.getTime() / 1000;}
};
