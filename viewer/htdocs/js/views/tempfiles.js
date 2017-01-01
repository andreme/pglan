
viewer.TempFilesView = function ($container) {
	viewer.View.call(this, $container);

	this._tempFiles = {};
};
viewer.TempFilesView.prototype = new viewer.View();

viewer.views['TempTiles'] = {'Caption': 'Temp Files', 'Class': viewer.TempFilesView};

viewer.TempFilesView.prototype._calculate = function () {
	this._tempFiles = {};

	this._log.walkEvents(viewer.LOG_TYPE_TEMP, this._addTempEvent, this);

	var t = [];
	$.each(this._tempFiles, function () {
		t.push(this);
	});

	t.sort(function (a, b) {
		var result = b['TotalSize'] - a['TotalSize'];

		if (!result) {
			result = b['TempFile']['Hash'] - a['TempFile']['Hash'];
		}

		return result;
	});

	this._tempFiles = t.slice(0, viewer.MAX_MESSAGES_VISIBLE);
};

viewer.TempFilesView.prototype._addTempEvent = function (tempFile, event) {

	var t = this._tempFiles[tempFile['Hash']];
	if (!t) {
		t = this._tempFiles[tempFile['Hash']] = {'TempFile': tempFile, 'TotalSize': 0, 'DateTime': event['DateTime']};
	}

	if (event['Size']) {
		t['TotalSize'] += event['Size'];
	}
};

viewer.TempFilesView.prototype._generate = function () {
	var self = this;

	this._$container.empty();

	$('#Templates .TempFiles').clone().appendTo(this._$container);

	var $orow = this._$container.find('.Row').detach();

	var i = 0;
	$.each(this._tempFiles, function () {

		var dateTime = new Date(this['DateTime'] * 1000);

		var $row = $orow.clone();
		$row.find('[data-name=Rank]').text(++i);
		$row.find('[data-name=DateTime]').text(formatDateTime(dateTime));
		$row.find('[data-name=Size]').text(bytesToSize(this['TotalSize']));
		$row.find('[data-name=File]').text(this['TempFile']['File']);

		$row.appendTo(self._$container.find('tbody'));
	});
};
