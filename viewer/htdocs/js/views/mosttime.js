
viewer.MostTimeView = function ($container) {
	viewer.View.call(this, $container);

	this._menu.push({'Caption': 'Analyse', 'Click': this._showAnalyse, 'Icon': 'ui-icon-lightbulb'});
	this._menu.push({'Caption': 'Detail', 'Click': this._showDetail, 'Icon': 'ui-icon-zoomin'});

	this._queries = {};
};
viewer.MostTimeView.prototype = new viewer.View();

viewer.views['MostTime'] = {'Caption': 'Most Time', 'Class': viewer.MostTimeView};

viewer.MostTimeView.prototype._calculate = function () {
	this._queries = {};

	this._log.walkEvents(viewer.LOG_TYPE_QUERY, this._addQueryEvent, this);

	var q = [];
	$.each(this._queries, function () {
		q.push(this);
	});

	q.sort(function (a, b) {
		var result = b['TotalDuration'] - a['TotalDuration'];

		if (!result) {
			result = b['Query']['Hash'] - a['Query']['Hash'];
		}

		return result;
	});

	this._queries = q.slice(0, viewer.MAX_QUERIES_VISIBLE);
};

viewer.MostTimeView.prototype._addQueryEvent = function (query, event) {

	var q = this._queries[query['Hash']];
	if (!q) {
		q = this._queries[query['Hash']] = {'Query': query, 'TotalDuration': 0, 'Count': query['Events'].length, 'Slowest': event};
	}

	if (event['Duration']) {
		q['TotalDuration'] += event['Duration'];

		if (event['Duration'] > q['Slowest']['Duration']) {
			q['Slowest'] = event;
		}
	}
};

viewer.MostTimeView.prototype._generate = function () {
	var self = this;

	this._$container.empty();

	$('#Templates .MostTime').clone().appendTo(this._$container);

	var $orow = this._$container.find('.Row').detach();

	var i = 0;
	$.each(this._queries, function () {

		var $row = $orow.clone();
		$row.data('Query', this['Query']);
		$row.data('Event', this['Slowest']);

		$row.find('[data-name=Rank]').text(++i);
		$row.find('[data-name=TotalDuration]').text(formatDuration((this['TotalDuration'] / 1000)));
		$row.find('[data-name=Count]').text(formatNumber((this['Count']).toFixed(0)));
		$row.find('[data-name=AvgDuration]').text((this['TotalDuration'] / this['Count'] / 1000).toFixed(2));
		$row.find('[data-name=Query]').text(replaceSQLParams(this['Query']['Text'], this['Slowest']['Params']));

		$row.appendTo(self._$container.find('tbody'));
	});

	self._$container.find('table').on('contextmenu', 'td', null, function (e) {
		return self._clickMenuEvent(e);
	});

	this._highlightCode();
};
