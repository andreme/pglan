
viewer.Log = function (data) {
	this._data = data;
};

viewer.Log.prototype.walkEvents = function (type, func, context) {
	if (!this._data['log'][type]) {
		return;
	}

	$.each(this._data['log'][type], function (i, query) {

		$.each(this['Events'], function (i2, event) {
			func.call(context, query, event);
		});
	});
};

viewer.Log.prototype.getUniqueStatementCount = function () {
	var result = 0;
	$.each(this._data['log'], function () {
		result += this.length;
	});
	return result;
};