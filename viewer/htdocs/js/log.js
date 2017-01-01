
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

viewer.Log.prototype.getCheckpoints = function () {
	if (!this._data['log'][viewer.LOG_TYPE_CECHKPOINT]) {
		return [];
	}
	
	return this._data['log'][viewer.LOG_TYPE_CECHKPOINT];
};

viewer.Log.prototype.getUniqueStatementCount = function () {
	var result = 0;
	if (this._data['log']['Query']) {
		result = this._data['log']['Query'].length;
	}

	return result;
};
