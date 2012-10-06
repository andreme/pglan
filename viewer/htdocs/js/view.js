
viewer.View = function ($container) {

	this._$container = $container;

	this._log = null;

	this._menu = [];
	this._$menu = null;

	this._$menuTarget = null;

	this._params = {};
};

viewer.View.prototype.load = function (log) {

	if (log === this._log) {
		return;
	}

	this._log = log;

	this._calculate();
};

viewer.View.prototype._calculate = function () {
};

viewer.View.prototype.show = function () {
};

viewer.View.prototype.generate = function () {
	this._initMenu();

	this._generate();
};

viewer.View.prototype._generate = function () {
};

viewer.View.prototype.setParams = function (params) {
	this._params = params || {};
	params && (this._log = null);
};

viewer.View.prototype._clickMenuEvent = function (e) {

	var radius = this._$menu.prettypiemenu('getRadius');
	e.preventDefault();
	if (e.which == 3) { // 1 = left, 3 = right
		this._$menuTarget = $(e.target);
		// checking if the menu if going outside the window
		if (e.pageX - radius < 0) {
			this._$menu.prettypiemenu('show', {
				left: e.pageX - (e.pageX - radius),
				top: e.pageY
			});
		} else if (e.pageX + radius > $(window).width()) {
			this._$menu.prettypiemenu('show', {
				left: e.pageX - (e.pageX + radius - $(window).width()),
				top: e.pageY
			});
		} else {
			this._$menu.prettypiemenu('show', {
				left: e.pageX,
				top: e.pageY
			});
		}
	}
	return false;
};

viewer.View.prototype._initMenu = function () {
	var self = this;

	this._$menu && this._$menu.prettypiemenu('destroy');
	this._$menu = null;

	this._menuTarget = null;

	if (!this._menu.length) {
		return;
	}

	var buttons = [];
	$.each(this._menu, function () {
		buttons.push({'img': this['Icon'], 'title': this['Caption']});
	});

	this._$menu = $('<span></span>').prettypiemenu({
		buttons: buttons,
		onSelection: function (item) {
			self._menu[item]['Click'].call(self, self._$menuTarget);
		},
		closeRadius: 25,
		showTitles: true
	});
};

viewer.View.prototype._showAnalyse = function ($el) {
	var $row = $el.closest('tr');

	var query = $row.data('Query');
	var event = $row.data('Event');

	viewer.showCopyText(createAnalyseStatement(query['Text'], event['Params']));
};

viewer.View.prototype._showDetail = function ($el) {
	var $row = $el.closest('tr');

	var query = $row.data('Query');

	setTimeout(function () {
		viewer.man.displayView(viewer.QueryDetailView, {'Query': query});
	}, 0);
};

viewer.View.prototype._highlightCode = function () {
	this._$container.find('pre.sh_sql').each(function () {
		sh_highlightElement(this, sh_languages['sql']);
	});
};
