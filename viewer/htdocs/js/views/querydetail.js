
viewer.QueryDetailView = function ($container) {
	viewer.View.call(this, $container);

	this._maxQueries = 1000;

	this._menu.push({'Caption': 'Analyse', 'Click': this._showAnalyse, 'Icon': 'ui-icon-lightbulb'});

	this._query = null;
};
viewer.QueryDetailView.prototype = new viewer.View();

viewer.views['QueryDetail'] = {'Caption': 'Query Detail', 'Class': viewer.QueryDetailView};

viewer.QueryDetailView.prototype._calculate = function () {
	this._query = this._params['Query'];

	if (!this._query) {
		return;
	}

	var events = this._query['Events'].slice(0);

	events.sort(function (a, b) {
		return a['DateTime'] - b['DateTime'];
	});

	this._events = events.slice(0, this._maxQueries);
};

viewer.QueryDetailView.prototype._display = function () {
	var self = this;

	this._$container.empty();

	if (!this._query) {
		this._$container.text('Dont select this view directly, use the context menu on a query.');

		return;
	}

	$('#Templates .QueryDetail').clone().appendTo(this._$container);

	var $orow = this._$container.find('.Row').detach();

	var i = 0;
	$.each(this._events, function () {

		var $row = $orow.clone();
		$row.data('Query', self._query);
		$row.data('Event', this);

		var dateTime = new Date(this['DateTime'] * 1000);

		$row.find('[data-name=No]').text(++i);
		$row.find('[data-name=DateTime]').text(formatDateTime(dateTime));
		$row.find('[data-name=Duration]').text((this['Duration'] / 1000).toFixed(2));
		$row.find('[data-name=Query]').text(replaceSQLParams(self._query['Text'], this['Params']));

		$row.appendTo(self._$container.find('tbody'));
	});

	self._$container.find('table').on('contextmenu', 'td', null, function (e) {
		return self._clickMenuEvent(e);
	});

	sh_highlightDocument();
};
