What is pglan?
====
A PostgreSQL Log File Analyser written in PHP.

It works in two steps, first the log file is parsed and stored in a JSON data file. Then this file can be loaded in the web based viewer and looked at with different views.

Queries are normalised, multiple calls to the same SQL with different values are added up, depending on the view.

Features
====

- Logfile only needs to be parsed once.
- Parse gzipped (.gz) or bzipped (.bz2) log files.
- Different views to analyse data.
- Copy SQL command to EXPLAIN the SQL with PREPARE if required (bind variables).

[Screenshots available here](https://github.com/andreme/pglan/wiki/Screenshots)

Installation
====
1. Extract [zip](https://github.com/andreme/pglan/zipball/master) or clone this repository.
2. Copy config.dist.php to config.php if you want to change settings.
	- `Config->DataPath` can be used to store the data files in a different directory. Defaults to the data subdirectory.
3. The webserver needs access to viewer/htdocs, either by creating an alias or moving the whole installation into the doc root.
	- **Make sure to restrict access.** pglan has no built-in access control.

Usage
====
1. Call `php analyser.php logfilname` and replace logfilename with the Logfile you want to parse. If there is already a data file with this name, it will get overwritten.
2. Reload the viewer in the browser, select the Logfile from the File list. (disable Firebug, it makes pglan slow.)
3. The View list gives the option to select a different view.
4. Right click on a SQL yields a context menu:
	- Detail: shows all calls for this SQL.
	- Analyse: A dialog to copy the SQL to run [EXPLAIN](http://www.postgresql.org/docs/current/static/sql-explain.html).