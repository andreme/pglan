
viewer = {};

viewer.views = {};

viewer.files = {};

viewer.log = null;

viewer.view = null;

viewer.statsView = null;

viewer.start = function () {
	$('#Templates').load('templates.html');

	$.each(viewer.files, function () {
		$('#FileSelect').append($('<option></option>').attr('value', this['FileName']).text(this['FileName']+' ('+bytesToSize(this['FileSize'])+')'));
	});

	$('#FileSelect').change(function () {
		viewer.load($(this).val());
	});

	$.each(viewer.views, function (name) {
		$('#ViewSelect').append($('<option></option>').attr('value', name).text(this['Caption']));
	});

	$('#ViewSelect').change(function () {
		viewer.selectView($(this).val());
	}).change();

	viewer.statsView = new viewer.LogStatsView($('#LogStats'));
};

viewer.load = function (name) {
	var opt = {};
	opt['dataType'] = 'json';
	opt['url'] = 'index.php?loadfile='+name;

	$.ajax(opt).done(function (data) {
		viewer.log = new viewer.Log(data);

		viewer.statsView.load(viewer.log);
		viewer.statsView.display();

		if (viewer.view) {
			viewer.view.load(viewer.log);
			viewer.view.display();
		}
	});
};

viewer.selectView = function (name) {

	var view = viewer.views[name];

	if (!view['Instance']) {
		view['Instance'] = new view['Class']($('#View'));
	}

	viewer.view = view['Instance'];

	if (viewer.log) {
		viewer.view.load(viewer.log);
		viewer.view.display();
	}
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