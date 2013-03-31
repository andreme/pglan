
viewer.HourlyStatsView = function ($container) {
	viewer.View.call(this, $container);

	this._hours = {};
};
viewer.HourlyStatsView.prototype = new viewer.View();

viewer.views['HourlyStats'] = {'Caption': 'Hourly Stats', 'Class': viewer.HourlyStatsView};

viewer.HourlyStatsView.prototype._calculate = function () {
	this._hours = {};

	this._log.walkEvents(viewer.LOG_TYPE_QUERY, this._addQueryEvent, this);

	var h = [];
	$.each(this._hours, function () {
		h.push(this);
	});

	h.sort(function (a, b) {
		return a['DateTime'].valueOf() - b['DateTime'].valueOf();
	});

	this._hours = h;
};

viewer.HourlyStatsView.prototype._addQueryEvent = function (query, event) {

	var dateTime = new Date(event['DateTime'] * 1000);

	var key = dateTime.setMinutes(0, 0, 0).valueOf();

	var agg = this._hours[key];
	if (!agg) {
		agg = this._hours[key] = {'Count': 0, 'Duration': 0, 'DateTime': dateTime};
	}

	agg['Duration'] += (event['Duration'] || 0);
	agg['Count']++;
};

viewer.HourlyStatsView.prototype._generate = function () {
	var self = this;

	this._$container.empty();

	$('#Templates .HourlyStats').clone().appendTo(this._$container);

	var $oRow = this._$container.find('.Row').detach();

	var oldDate = null;
	$.each(this._hours, function () {

		var $row = $oRow.clone();

		if (oldDate != formatDate(this['DateTime'])) {
			$row.find('[data-name=Date]').text(formatDate(this['DateTime']));
			oldDate = formatDate(this['DateTime']);
		}
		$row.find('[data-name=Time]').text(formatTime(this['DateTime']));
		$row.find('[data-name=Duration]').text(formatDuration((this['Duration'] / 1000)));
		$row.find('[data-name=Count]').text((this['Count']).toFixed(0));

		$row.appendTo(self._$container.find('tbody'));
	});

	this._createGraph();
};

viewer.HourlyStatsView.prototype._createGraph = function () {

	var duration = [];
	var count = [];

	$.each(this._hours, function () {
		duration.push([this['DateTime'].valueOf(), this['Duration'] / 1000]);
		count.push([this['DateTime'].valueOf(), this['Count']]);
	});

    var options = {
		series: {
			highlightColor: 'red'
        },
        xaxis: {
			mode: "time",
			min: viewer.statsView.getFirstDate(),
			max: viewer.statsView.getLastDate()
		},
		yaxes: [{
			tickFormatter: function (v, axis) {
				return (v/60).toFixed(axis.tickDecimals)+' m';
			}
		}, {
			alignTicksWithAxis: true,
			position: 'right'
		}],
        selection: { mode: "x" },
        grid: { markings: viewer.graph.markWeekendAreas }
    };

	var dataDetail = [
		{data: duration, label: 'Duration', lines: { show: true, fill: true }},
		{data: count, label: 'Count', yaxis: 2, lines: { show: true }}
	];

	var dataOverview = [
		{data: duration, lines: { show: true, fill: true }},
		{data: count, yaxis: 2, lines: { show: true }}
	];

    var plot = $.plot($("#GraphCont"), dataDetail, options);

    var overview = $.plot($("#GraphOverview"), dataOverview, {
        series: {
            lines: { lineWidth: 1 },
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
        plot = $.plot($("#GraphCont"), dataDetail,
			$.extend(true, {}, options, {
				xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
			})
		);

        // don't fire event on the overview to prevent eternal loop
        overview.setSelection(ranges, true);
    });

    $("#GraphOverview").bind("plotselected", function (event, ranges) {
        plot.setSelection(ranges);
    });
};
