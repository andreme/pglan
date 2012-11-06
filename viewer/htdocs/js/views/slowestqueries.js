
viewer.SlowestQueriesView = function ($container) {
	viewer.View.call(this, $container);

	this._maxQueries = 50;

	this._menu.push({'Caption': 'Analyse', 'Click': this._showAnalyse, 'Icon': 'ui-icon-lightbulb'});
	this._menu.push({'Caption': 'Detail', 'Click': this._showDetail, 'Icon': 'ui-icon-zoomin'});

	this._queries = [];
};
viewer.SlowestQueriesView.prototype = new viewer.View();

viewer.views['SlowestQueries'] = {'Caption': 'Slowest Queries', 'Class': viewer.SlowestQueriesView};

viewer.SlowestQueriesView.prototype._calculate = function () {
	this._queries = [];

	this._log.walkEvents(viewer.LOG_TYPE_QUERY, this._addQueryEvent, this);
};

viewer.SlowestQueriesView.prototype._addQueryEvent = function (query, event) {

	var len = this._queries.length;
	for (var i = 0; i < len; i++) {

		if ((event['Duration'] || 0) > (this._queries[i]['Event']['Duration'] || 0)) {
			this._queries.splice(i, 0, {'Query': query, 'Event': event});
			this._onQueryAdded(query, event);

			if (this._queries.length >= this._maxQueries) {
				this._queries.splice(this._maxQueries, 999);
			}

			return;
		}
	}

	if (len < this._maxQueries) {
		this._queries.push({'Query': query, 'Event': event});
		this._onQueryAdded(query, event);
	}
};

viewer.SlowestQueriesView.prototype._onQueryAdded = function (query, event) {
};

viewer.SlowestQueriesView.prototype._generate = function () {
	var self = this;

	this._$container.empty();

	$('#Templates .SlowestQueries').clone().appendTo(this._$container);

	var $orow = this._$container.find('.Row').detach();

	$.each(this._queries, function (i) {

		var $row = $orow.clone();

		$row.data('Query', this['Query']);
		$row.data('Event', this['Event']);

		$row.find('[data-name=Rank]').text(i+1);
		$row.find('[data-name=DateTime]').text(formatDateTime(new Date(this['Event']['DateTime'] * 1000)));
		$row.find('[data-name=Duration]').text((this['Event']['Duration'] / 1000).toFixed(2));
		$row.find('[data-name=Query]').text(replaceSQLParams(this['Query']['Text'], this['Event']['Params']));

		$row.appendTo(self._$container.find('tbody'));
	});

	self._$container.find('table').on('contextmenu', 'td', null, function (e) {
		return self._clickMenuEvent(e);
	});

	this._highlightCode();
};
