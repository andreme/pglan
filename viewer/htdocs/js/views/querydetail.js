
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

	this._events = events;
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
	$.each(this._events.slice(0, this._maxQueries), function () {

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

	this._createGraph();
};

viewer.QueryDetailView.prototype._createGraph = function () {

	var d = [];

	$.each(this._events, function () {
		d.push([this['DateTime'] * 1000, this['Duration'] / 1000]);
	});

    // helper for returning the weekends in a period
    var weekendAreas = function (axes) {
        var markings = [];
        var d = new Date(axes.xaxis.min);
        // go to the first Saturday
        d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
        d.setUTCSeconds(0);
        d.setUTCMinutes(0);
        d.setUTCHours(0);
        var i = d.getTime();
        do {
            // when we don't set yaxis, the rectangle automatically
            // extends to infinity upwards and downwards
            markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
            i += 7 * 24 * 60 * 60 * 1000;
        } while (i < axes.xaxis.max);

        return markings;
    };

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
        grid: { markings: weekendAreas }
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
