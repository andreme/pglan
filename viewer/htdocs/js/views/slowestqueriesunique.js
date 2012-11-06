
viewer.SlowestQueriesUniqueView = function ($container) {
	viewer.SlowestQueriesView.call(this, $container);

	this._addedQueries = {};
};
viewer.SlowestQueriesUniqueView.prototype = new viewer.SlowestQueriesView();

viewer.views['SlowestQueriesUnique'] = {'Caption': 'Slowest Queries (Unique)', 'Class': viewer.SlowestQueriesUniqueView};

viewer.SlowestQueriesUniqueView.prototype._addQueryEvent = function (query, event) {
	var oldEvent;

	if ((oldEvent = this._addedQueries[query['Hash']])) {
		if ((event['Duration'] || 0) <= (oldEvent['Duration'] || 0)) {
			return;
		}

		this._removeQuery(query['Hash']);
	}

	viewer.SlowestQueriesView.prototype._addQueryEvent.call(this, query, event);
};

viewer.SlowestQueriesUniqueView.prototype._removeQuery = function (hash) {
	var len = this._queries.length;
	for (var i = 0; i < len; i++) {

		if (this._queries[i]['Query']['Hash'] == hash) {
			this._queries.splice(i, 1);

			delete this._addedQueries[hash];

			return;
		}
	}
};

viewer.SlowestQueriesUniqueView.prototype._onQueryAdded = function (query, event) {
	this._addedQueries[query['Hash']] = event;
};
