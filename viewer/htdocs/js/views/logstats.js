
viewer.LogStatsView = function ($container) {
	viewer.View.call(this, $container);

	this._statementCount = 0;
	this._uniqueStatementCount = 0;
	this._time = 0;
	this._first = Infinity;
	this._last = null;
};
viewer.LogStatsView.prototype = new viewer.View();

//viewer.views['LogStats'] = {'Caption': 'Stats', 'Class': viewer.LogStatsView};

viewer.LogStatsView.prototype.getFirstDate = function () {
	return new Date(this._first * 1000);
};

viewer.LogStatsView.prototype.getLastDate = function () {
	return new Date(this._last * 1000);
};

viewer.LogStatsView.prototype._calculate = function () {
	this._statementCount = 0;
	this._uniqueStatementCount = this._log.getUniqueStatementCount();
	this._time = 0;
	this._first = Infinity;
	this._last = null;

	this._log.walkEvents(viewer.LOG_TYPE_QUERY, this._addQueryEvent, this);
	this._log.walkEvents(viewer.LOG_TYPE_SYSTEM, this._addSystemEvent, this);
};

viewer.LogStatsView.prototype._addSystemEvent = function (query, event) {

	this._statementCount++;
};

viewer.LogStatsView.prototype._addQueryEvent = function (query, event) {

	this._statementCount++;

	if (event['Duration'] !== undefined) {
		this._time += event['Duration'];
	}

	if (event['DateTime'] < this._first) {
		this._first = event['DateTime'];
	}

	if (event['DateTime'] > this._last) {
		this._last = event['DateTime'];
	}
};

viewer.LogStatsView.prototype._display = function () {
	this._$container.empty();

	var $tmpl = $('#Templates .LogStats').clone().appendTo(this._$container);

	$tmpl.find('[data-name=StatementCount]').text(formatNumber(this._statementCount));
	$tmpl.find('[data-name=UniqueStatementCount]').text(formatNumber(this._uniqueStatementCount));
	$tmpl.find('[data-name=Duration]').text(formatDuration(this._time / 1000));
	$tmpl.find('[data-name=First]').text(formatDateTime(this.getFirstDate()));
	$tmpl.find('[data-name=Last]').text(formatDateTime(this.getLastDate()));
};
