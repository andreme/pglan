
viewer.QueryDetailView = function ($container) {
	viewer.View.call(this, $container);

	this._menu.push({'Caption': 'Analyse', 'Click': this._showAnalyse, 'Icon': 'ui-icon-lightbulb'});

	this._query = null;
};
viewer.QueryDetailView.prototype = new viewer.View();

viewer.views['QueryDetail'] = {'Caption': 'Query Detail', 'Class': viewer.QueryDetailView, 'ShowInList': false};

viewer.QueryDetailView.prototype._calculate = function () {
	this._query = this._params['Query'];

	if (!this._query) {
		return;
	}

	var events = this._query['Events'].slice(0);

	events.sort(function (a, b) {
		return a['DateTime'] - b['DateTime'];
	});

	this._events = events;
};

viewer.QueryDetailView.prototype._generate = function () {
	var self = this;

	this._$container.empty();

	$('#Templates .QueryDetail').clone().appendTo(this._$container);

	var $orow = this._$container.find('.Row').detach();

	var $tbody = this._$container.find('tbody');

	var i = 0;

	var addFunc = function () {

		var $row = $orow.clone();
		$row.data('Query', self._query);
		$row.data('Event', this);

		var dateTime = new Date(this['DateTime'] * 1000);

		$row.find('[data-name=No]').text(++i);
		$row.find('[data-name=DateTime]').text(formatDateTime(dateTime));
		$row.find('[data-name=Duration]').text((this['Duration'] / 1000).toFixed(2));
		$row.find('[data-name=Query]').text(replaceSQLParams(self._query['Text'], this['Params']));

		$row.appendTo($tbody);
	};

	if (this._events.length <= viewer.MAX_QUERIES_VISIBLE) {
		$.each(this._events, addFunc);
	} else {
		$.each(this._events.slice(0, viewer.MAX_QUERIES_VISIBLE / 2), addFunc);

		var $cut = $('<tr><td colspan="99">'+(this._events.length-viewer.MAX_QUERIES_VISIBLE)+' queries hidden.</td></tr>')
		$tbody.append($cut);

		i = this._events.length-(viewer.MAX_QUERIES_VISIBLE / 2);
		$.each(this._events.slice(-(viewer.MAX_QUERIES_VISIBLE / 2)), addFunc);
	}

	self._$container.find('table').on('contextmenu', 'td', null, function (e) {
		return self._clickMenuEvent(e);
	});

	this._$container.find('button.Back').click(function () {
		viewer.man.goBack();
	});

	this._highlightCode();

	this._createGraph();
};

viewer.QueryDetailView.prototype._createGraph = function () {

	var d = [];

	$.each(this._events, function () {
		d.push([this['DateTime'] * 1000, this['Duration'] / 1000]);
	});

    var options = {
		series: {
			bars: { show: true, barWidth: 10 },
//			lines: { show: true, fill: true },
			points: { show: true, fill: false },
			highlightColor: 'red'
//			points: { show: true }
        },
        xaxis: {
			mode: "time",/*, tickLength: 5*/
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
    });

    $("#GraphOverview").bind("plotselected", function (event, ranges) {
        plot.setSelection(ranges);
    });
};
