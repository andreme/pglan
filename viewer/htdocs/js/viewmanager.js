viewer.ViewManager = function ($container) {

	this._$container = $container;

	this._history = [];

	this._log = null;
};

viewer.ViewManager.prototype.setLog = function (log) {
	this._log = log;
};

viewer.ViewManager.prototype.clear = function () {
	this._history = [];
};

viewer.ViewManager.prototype.goBack = function () {
	this._history.pop();

	this._display(this._history.length-1);
};

viewer.ViewManager.prototype.displayView = function (classname, params) {

	if (!this._log) {
		return;
	}

	var step = {};

	step['Container'] = $('<div></div>');

	var view = step['Instance'] = new classname(step['Container']);

	view.setParams(params ? params : null);

	view.load(this._log);
	view.generate();

	this._history.push(step);

	this._display(this._history.length-1);
};

viewer.ViewManager.prototype._display = function (index) {
	var step = this._history[index];

	this._$container.children().detach();

	this._$container.append(step['Container']);

	step['Instance'].show();
};
