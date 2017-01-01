
viewer.AllQueriesView = function ($container) {
	viewer.View.call(this, $container);

	this._menu.push({'Caption': 'Analyse', 'Click': this._showAnalyse, 'Icon': 'ui-icon-lightbulb'});
	this._menu.push({'Caption': 'Detail', 'Click': this._showDetail, 'Icon': 'ui-icon-zoomin'});

	this._queries = [];
};
viewer.AllQueriesView.prototype = new viewer.View();

viewer.views['AllQueries'] = {'Caption': 'All Queries', 'Class': viewer.AllQueriesView};

viewer.AllQueriesView.prototype._calculate = function () {
	this._log.walkEvents(viewer.LOG_TYPE_QUERY, this._addQueryEvent, this);

	this._queries.sort(function (a, b) {
		return a['Event']['DateTime'] - b['Event']['DateTime'];
	});
};

viewer.AllQueriesView.prototype._addQueryEvent = function (query, event) {
	this._queries.push({'Query': query, 'Event': event});
};

viewer.AllQueriesView.prototype._generate = function () {
	this._createTable(Math.floor(viewer.statsView.getFirstDate().valueOf() / 1000),
		Math.ceil(viewer.statsView.getLastDate().valueOf() / 1000));

	this._createGraph();
};

viewer.AllQueriesView.prototype._createTable = function (start, end) {
	var self = this;

	this._$container.die().empty();

	var tmpl = $('#Templates .QueryDetail').clone();

	var $orow = tmpl.find('.Row').detach();

	var $tbody = tmpl.find('tbody');

	var i = 0;

	var filteredQueries = $.map(this._queries, function (a) {
		return (a['Event']['DateTime'] >= start && a['Event']['DateTime'] <= end) ? a : null;
	});

	var addFunc = function () {
		var query = this['Query'];
		var event = this['Event'];

		var $row = $orow.clone();
		$row.data('Query', query);
		$row.data('Event', event);

		var dateTime = new Date(event['DateTime'] * 1000);

		$row.find('[data-name=No]').text(++i);
		$row.find('[data-name=DateTime]').text(formatDateTime(dateTime));
		$row.find('[data-name=Duration]').text((event['Duration'] === undefined || event['Duration'] === null) ? '' : (event['Duration'] / 1000).toFixed(2));
		$row.find('[data-name=Query]').text(replaceSQLParams(query['Text'], event['Params']));

		$row.appendTo($tbody);
	};

	if (filteredQueries.length <= viewer.MAX_QUERIES_VISIBLE) {
		$.each(filteredQueries, addFunc);
	} else {
		$.each(filteredQueries.slice(0, viewer.MAX_QUERIES_VISIBLE), addFunc);

		var $cut = $('<tr><td colspan="99">'+(filteredQueries.length-viewer.MAX_QUERIES_VISIBLE)+' queries hidden.</td></tr>')
		$tbody.append($cut);

		i = filteredQueries.length-viewer.MAX_QUERIES_VISIBLE;
		$.each(filteredQueries.slice(-viewer.MAX_QUERIES_VISIBLE), addFunc);
	}

	tmpl.appendTo(this._$container);

	self._$container.find('table').on('contextmenu', 'td', null, function (e) {
		return self._clickMenuEvent(e);
	});

	this._$container.find('button.Back').remove();

	this._highlightCode();
};

viewer.AllQueriesView.prototype._createGraph = function () {
	var self = this;

	var d = [];

	$.each(this._queries, function () {
		d.push([this['Event']['DateTime'] * 1000, this['Event']['Duration'] / 1000]);
	});

    var options = {
		series: {
			bars: { show: true, barWidth: 10 },
			points: { show: true, fill: false },
			highlightColor: 'red'
        },
        xaxis: {
			mode: "time",
			min: viewer.statsView.getFirstDate(),
			max: viewer.statsView.getLastDate()
		},
		yaxis: {
			tickFormatter: function (v, axis) {
				return v.toFixed(axis.tickDecimals)+' s';
			}
		},
        selection: { mode: "x" },
        grid: { markings: viewer.graph.markWeekendAreas }
    };

    var plot = $.plot($("#GraphCont"), [d], options);

    var overview = $.plot($("#GraphOverview"), [d], {
        series: {
            lines: { show: true, lineWidth: 1 },
            shadowSize: 0
        },
        xaxis: {
			ticks: [],
			mode: "time",
			min: viewer.statsView.getFirstDate(),
			max: viewer.statsView.getLastDate()
		},
        yaxis: { ticks: [], min: 0, autoscaleMargin: 0.1 },
        selection: { mode: "x" }
    });

    $("#GraphCont").bind("plotselected", function (event, ranges) {
        // do the zooming
        plot = $.plot($("#GraphCont"), [d],
                      $.extend(true, {}, options, {
                          xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
                      }));

        // don't fire event on the overview to prevent eternal loop
        overview.setSelection(ranges, true);

		self._createTable(Math.floor(ranges.xaxis.from / 1000), Math.ceil(ranges.xaxis.to / 1000));
    });

    $("#GraphOverview").bind("plotselected", function (event, ranges) {
        plot.setSelection(ranges);
    });
};
