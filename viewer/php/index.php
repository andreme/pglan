<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<title>PG Logfile viewer</title>

		<link rel="stylesheet" type="text/css" href="css/viewer.css" />
		<link rel="stylesheet" type="text/css" href="css/sh_style.css" />
		<link rel="stylesheet" type="text/css" href="css/jquery.ui.ppmenu.css" />
		<link rel="stylesheet" type="text/css" href="css/ui-lightness/jquery-ui.css" />
		<script type="text/javascript" src="js/vendor/jquery.min.js"></script>
		<script type="text/javascript" src="js/vendor/jquery-ui.js"></script>
		<script type="text/javascript" src="js/vendor/jquery.ui.prettypiemenu.js"></script>
		<script type="text/javascript" src="js/vendor/sh_main.js"></script>
		<script type="text/javascript" src="js/vendor/sh_sql.js"></script>
		<script type="text/javascript" src="js/funcs.js"></script>
		<script type="text/javascript" src="js/date.js"></script>
		<script type="text/javascript" src="js/viewer.js"></script>
		<script type="text/javascript" src="js/log.js"></script>
		<script type="text/javascript" src="js/view.js"></script>
		<script type="text/javascript" src="js/views/slowestqueries.js"></script>
		<script type="text/javascript" src="js/views/mosttime.js"></script>
		<script type="text/javascript" src="js/views/logstats.js"></script>
		<script type="text/javascript" src="js/views/querydetail.js"></script>
	</head>
	<body>
		<div id="head">
			<div>
				<div id="FileSelector">
					<select id="FileSelect">
						<option>- Select -</option>
					</select>
				</div>
				<div id="LogStats">
				</div>
				<div id="ViewSelector">
					<select id="ViewSelect">
					</select>
				</div>
			</div>
			<div id="graph"></div>
		</div>
		<div id="main">
			<div id="View"></div>
		</div>
		<div id="Templates" style="display: none;">
		</div>
		<script>
			$(function () {
				viewer.files = <?php echo $this->dataFiles ? json_encode($this->dataFiles) : '{}'; ?>;

				viewer.start();
			});
		</script>
	</body>
</html>
