
viewer.CheckpointsView = function ($container) {
	viewer.View.call(this, $container);

	this._checkpoints = [];
};
viewer.CheckpointsView.prototype = new viewer.View();

viewer.views['Checkpoints'] = {'Caption': 'Check points', 'Class': viewer.CheckpointsView};

viewer.CheckpointsView.prototype._calculate = function () {
	this._checkpoints = this._log.getCheckpoints();

	this._checkpoints.sort(function (a, b) {
		return a['DateTime'] - b['DateTime'];
	});
};

viewer.CheckpointsView.prototype._generate = function () {
	this._createTable(Math.floor(viewer.statsView.getFirstDate().valueOf() / 1000),
		Math.ceil(viewer.statsView.getLastDate().valueOf() / 1000));

	this._createGraph();
};

viewer.CheckpointsView.prototype._createTable = function (start, end) {
	this._$container.die().empty();

	var tmpl = $('#Templates .CheckpointList').clone();

	var $orow = tmpl.find('.Row').detach();

	var $tbody = tmpl.find('tbody');

	var i = 0;

	var filteredCheckpoints = $.map(this._checkpoints, function (a) {
		return (a['DateTime'] >= start && a['DateTime'] <= end) ? a : null;
	});

	var addFunc = function () {
		var $row = $orow.clone();
		$row.data('Checkpoint', this);

		var dateTime = new Date(this['DateTime'] * 1000);

		$row.find('[data-name=No]').text(++i);
		$row.find('[data-name=DateTime]').text(formatDateTime(dateTime));
		$row.find('[data-name=BuffersWritten]').text(this['BuffersWritten']);
		$row.find('[data-name=BuffersPercentage]').text(formatNumberDec(this['BuffersPercentage'], 1));
		$row.find('[data-name=WriteTime]').text(formatNumberDec(this['WriteTime'], 3));
		$row.find('[data-name=SyncTime]').text(formatNumberDec(this['SyncTime'], 3));
		$row.find('[data-name=TotalTime]').text(formatNumberDec(this['TotalTime'], 3));

		$row.appendTo($tbody);
	};

	if (filteredCheckpoints.length <= viewer.MAX_MESSAGES_VISIBLE) {
		$.each(filteredCheckpoints, addFunc);
	} else {
		$.each(filteredCheckpoints.slice(0, viewer.MAX_MESSAGES_VISIBLE), addFunc);

		var $cut = $('<tr><td colspan="99">'+(filteredCheckpoints.length-viewer.MAX_MESSAGES_VISIBLE)+' messages hidden.</td></tr>')
		$tbody.append($cut);

		i = filteredCheckpoints.length-viewer.MAX_MESSAGES_VISIBLE;
		$.each(filteredCheckpoints.slice(-viewer.MAX_MESSAGES_VISIBLE), addFunc);
	}

	tmpl.appendTo(this._$container);
};

viewer.CheckpointsView.prototype._createGraph = function () {
	var self = this;

	var d = [];

	$.each(this._checkpoints, function () {
		d.push([this['DateTime'] * 1000, this['TotalTime']]);
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
