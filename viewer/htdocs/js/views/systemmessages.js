
viewer.SystemMessagesView = function ($container) {
	viewer.View.call(this, $container);

//	this._menu.push({'Caption': 'Detail', 'Click': this._showDetail, 'Icon': 'ui-icon-zoomin'});

	this._messages = [];
};
viewer.SystemMessagesView.prototype = new viewer.View();

viewer.views['SystemMessages'] = {'Caption': 'System Messages', 'Class': viewer.SystemMessagesView};

viewer.SystemMessagesView.prototype._calculate = function () {
	this._log.walkEvents(viewer.LOG_TYPE_SYSTEM, this._addSystemEvent, this);

	this._messages.sort(function (a, b) {
		return a['Event']['DateTime'] - b['Event']['DateTime'];
	});
};

viewer.SystemMessagesView.prototype._addSystemEvent = function (message, event) {
	this._messages.push({'Message': message, 'Event': event});
};

viewer.SystemMessagesView.prototype._generate = function () {
	this._createTable(Math.floor(viewer.statsView.getFirstDate().valueOf() / 1000),
		Math.ceil(viewer.statsView.getLastDate().valueOf() / 1000));

	this._createGraph();
};

viewer.SystemMessagesView.prototype._createTable = function (start, end) {
	this._$container.die().empty();

	var tmpl = $('#Templates .MessageList').clone();

	var $orow = tmpl.find('.Row').detach();

	var $tbody = tmpl.find('tbody');

	var i = 0;

	var filteredMessages = $.map(this._messages, function (a) {
		return (a['Event']['DateTime'] >= start && a['Event']['DateTime'] <= end) ? a : null;
	});

	var addFunc = function () {
		var message = this['Message'];
		var event = this['Event'];

		var $row = $orow.clone();
		$row.data('Message', message);
		$row.data('Event', event);

		var dateTime = new Date(event['DateTime'] * 1000);

		$row.find('[data-name=No]').text(++i);
		$row.find('[data-name=DateTime]').text(formatDateTime(dateTime));
		$row.find('[data-name=Message]').text(replaceSQLParams(message['Text'], event['Params']));

		$row.appendTo($tbody);
	};

	if (filteredMessages.length <= viewer.MAX_MESSAGES_VISIBLE) {
		$.each(filteredMessages, addFunc);
	} else {
		$.each(filteredMessages.slice(0, viewer.MAX_MESSAGES_VISIBLE), addFunc);

		var $cut = $('<tr><td colspan="99">'+(filteredMessages.length-viewer.MAX_MESSAGES_VISIBLE)+' messages hidden.</td></tr>')
		$tbody.append($cut);

		i = filteredMessages.length-viewer.MAX_MESSAGES_VISIBLE;
		$.each(filteredMessages.slice(-viewer.MAX_MESSAGES_VISIBLE), addFunc);
	}

	tmpl.appendTo(this._$container);
};

viewer.SystemMessagesView.prototype._createGraph = function () {
	var self = this;

	var d = [];

	$.each(this._messages, function () {
		d.push([this['Event']['DateTime'] * 1000, 0]);
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
