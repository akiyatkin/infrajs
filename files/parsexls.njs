var fs = require('fs');
var path = require('path');
var office = require('office');

this.init = function(req, res, next, root) {
	var page = require(path.join(__dirname, 'page.njs'));
	var end = function() {
		res.writeHead(200, { 'Content-Type': 'application/json; charset=UTF-8' }); 
		res.end(JSON.stringify(ans, null, "\t"), 'utf-8');
	};
	var GET = {};
	var ans = { "result": 0 };
	if (req.query && Object.keys(req.query).length) {
		var name; for (name in req.query) { if (req.query.hasOwnProperty(name)) {
			var new_name = name.trim();
			if (new_name) { GET[new_name] = req.query[name].trim(); }
		}}
	}
	GET.src = page.preparePath(GET.src);
	if (GET.src && (path.extname(GET.src).toLowerCase() == '.xls')) {
		office.parse(path.join(root, GET.src), function(err, data) {
			if (!err && data && data.sheets.sheet) {
				ans.data = [];
				var table; // готовый объект таблицы
				var headers; // Заголовки
				var sheet = data.sheets.sheet;
				if (sheet.length) { sheet = sheet[0]; }
				// взять первую страницу, где в первой строке заголовоки
				var i; for (i = 0; i < sheet.rows.row.length; i++) {
					var item = {};
					var item_true;
					var row = sheet.rows.row[i].cell;
					var ii; for (ii = 0; ii < row.length; ii++) {
						var cell = row[ii].$t;
						if (!cell) { cell = row[ii].B; }
						if (!cell && row[ii].A) { cell = row[ii].A.$t; }
						if (!cell) { cell = row[ii].U; }
						if (!cell) { cell = row[ii].I; }
						//if (!cell) { console.log(row[ii]); }
						if (cell) {
							if (!headers) {
								headers = { poss: [], run: true };
								headers.first_col = row[ii].col;
								headers.last_col = row[ii].col;
								headers.poss.push(cell);
							} else if (headers.run) {
								headers.last_col = row[ii].col;
								headers.poss.push(cell);
							} else { // позиция
								if ((row[ii].col >= headers.first_col) && (row[ii].col <= headers.last_col)) {
									item_true = true;
									var name = headers.poss[row[ii].col-headers.first_col];
									item[name] = cell;
								}
							}
						}
					}
					if (headers && headers.run) { delete(headers.run); }
					if (item_true) { ans.data.push(item); }
				}
				ans.result = 1;
				end();
			} else { end(); }
		});
	} else { end(); }
};
