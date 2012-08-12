
viewer.Log = function (data) {
	this._data = data;
};

viewer.Log.prototype.walkEvents = function (func, context) {
	$.each(this._data['log'], function (i, query) {

		$.each(this['Events'], function (i2, event) {
			func.call(context, query, event);
		});
	});
};

viewer.Log.prototype.getUniqueStatementCount = function () {
	return this._data['log'].length;
};