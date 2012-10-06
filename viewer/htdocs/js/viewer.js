
viewer = {};

viewer.views = {};

viewer.files = {};

viewer.log = null;

viewer.statsView = null;

viewer.LOG_TYPE_QUERY = 'Query';
viewer.LOG_TYPE_SYSTEM = 'System';

viewer.start = function () {
	$('#Templates').load('templates.html');

	$.each(viewer.files, function () {
		$('#FileSelect').append($('<option></option>').attr('value', this['FileName']).text(this['FileName']+' ('+bytesToSize(this['FileSize'])+')'));
	});

	$('#FileSelect').change(function () {
		viewer.load($(this).val());
	});

	$.each(viewer.views, function (name) {
		if ((this['ShowInList'] !== undefined) && !this['ShowInList']) {
			return;
		}
		$('#ViewSelect').append($('<option></option>').attr('value', name).text(this['Caption']));
	});

	$('#ViewSelect').change(function () {
		viewer._selectView($(this).val());
	});

	viewer.statsView = new viewer.LogStatsView($('#LogStats'));

	viewer.man = new viewer.ViewManager($('#View'));
};

viewer.load = function (filename) {
	var opt = {};
	opt['dataType'] = 'json';
	opt['url'] = 'index.php?loadfile='+filename;

	$.ajax(opt).done(function (data) {
		viewer.log = new viewer.Log(data);

		viewer.statsView.load(viewer.log);
		viewer.statsView.generate();
		viewer.statsView.show();

		viewer.man.clear();
		viewer.man.setLog(viewer.log);
		viewer.man.displayView(viewer.views[$('#ViewSelect').val()]['Class']);
	});
};

viewer.selectView = function (name, params) {
	viewer._selectView(name, params);

	$(window).scrollTop(0);
};

viewer._selectView = function (name, params) {

	var view = viewer.views[name];

	viewer.man.displayView(view['Class'], params);
};

viewer.showCopyText = function (text) {

	$("#copyText .Text").val(text)

	$("#copyText").dialog({
		width: 400,
		modal: true,
		buttons: {
			Ok: function() {
				$(this).dialog("close");
			}
		},
		open: function(event, ui) {
			$("#copyText .Text")
				.unbind('keydown')
				.keydown(function (e) {
					if (e.ctrlKey && (e.keyCode == 67)) {
						setTimeout(function () {
							$("#copyText").dialog('close');
						}, 1);
					}
				})
				.focus()[0].select();
		}
	});
};

sh_languages['sql'][0].push([
      /\b(?:COPY|UNION|CASE|WHEN|THEN|END)\b/gi,
      'sh_keyword',
      -1
    ]);
sh_languages['sql'][0].push([
      /\b(?:NUMERIC|INTERVAL)\b/gi,
      'sh_type',
      -1
    ]);
