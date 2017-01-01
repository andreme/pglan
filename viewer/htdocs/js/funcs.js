
function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

function formatDuration(seconds) {
	if (seconds === null || seconds === undefined || seconds === '' || isNaN(seconds)) {
		return null;
	}

	if (seconds === 0) {
		return '0s';
	}

	//seconds = Math.round(seconds);

	var minutes = Math.floor(seconds / 60);
	seconds -= minutes * 60;

	var hours = Math.floor(minutes / 60);
	minutes -= hours * 60;

	var days = Math.floor(hours / 24);
	hours -= days * 24;

	var result = '';

	if (days) {
		result += days+'d';
	}
	if (hours) {
		result += hours+'h';
	}
	if (minutes) {
		result += minutes+'m';
	}
	if (seconds) {
		if (result) {
			seconds = Math.round(seconds);
		} else {
			seconds = Math.round(seconds * 100) / 100;
		}

		result += seconds+'s';
	}

	return result;
}

function formatNumber(number) {
	if (number === undefined || number === null || number === '' || isNaN(number)) {
		return '';
	}

	var nStr = number+'';
//	var x = nStr.split('.');
	var x1 = nStr;//x[0];
	var x2 = '';//'.' + ((x.length < 2 || x[1].length < 1) ? '00' : x[1].length == 1 ? x[1]+'0' : x[1]);
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1,$2');
	}
	return x1 + x2;
}

function formatNumberDec(number, length) {
	if (number === undefined || number === null || number === '' || isNaN(number)) {
		return '';
	}

	var nStr = number+'';
	var x = nStr.split('.');
	var x1 = x[0];
	var x2 = '.' + (x[1] || '0');
	while (x2.length < length + 1) {
		x2 += '0';
	}
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1,$2');
	}
	return x1 + x2;
}

function formatDateTime(d) {
	return d.getUTCFullYear()+'-'+(d.getUTCMonth() < 9 ? '0' : '')+(d.getUTCMonth() + 1)+'-'+(d.getUTCDate() < 10 ? '0' : '') +d.getUTCDate()+' '+
		(d.getUTCHours() < 10 ? '0' : '')+d.getUTCHours()+':'+(d.getUTCMinutes() < 10 ? '0' : '')+d.getUTCMinutes()+':'+(d.getUTCSeconds() < 10 ? '0' : '') + d.getUTCSeconds();
}

function formatDate(d) {
	return d.getUTCFullYear()+'-'+(d.getUTCMonth() < 9 ? '0' : '')+(d.getUTCMonth() + 1)+'-'+(d.getUTCDate() < 10 ? '0' : '') +d.getUTCDate();
}

function formatTime(d) {
	return (d.getUTCHours() < 10 ? '0' : '')+d.getUTCHours()+':'+(d.getUTCMinutes() < 10 ? '0' : '')+d.getUTCMinutes()+':'+(d.getUTCSeconds() < 10 ? '0' : '') + d.getUTCSeconds();
}

function replaceSQLParams(s, params) {

	if (!params) {
		return s;
	}

	var paramRE = /(\$[a-z\d]+)/ig;

	return s.replace(paramRE, function (match, p1) {
		var result = params[p1];

		if (typeof result == 'string' && (result.length > 256)) {
			result = result.substr(0, 50)+"... string("+result.length+")";
		}

		return result;
	});
}

function createAnalyseStatement(query, params) {

	if (!params) {
		return query;
	}

	var paramRE = /(\$[a-z]+)/ig;

	query = query.replace(paramRE, function (match, p1) {
		return params[p1];
	});

	var bindVarRE = /^\$\d+/
	var bindVars = [];
	$.each(params, function (name, value) {
		if (bindVarRE.test(name)) {
			bindVars.push(value);
		}
	});

	if (bindVars.length) {
		query = "DEALLOCATE ALL;\nPREPARE stmt AS\n"+query+";\n\nEXECUTE stmt("+bindVars.join(', ')+");"
	}

	return query;
}
