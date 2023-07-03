//グラフオプション
var graph_options = {
    legend: {
    	labels: {
    		fontSize: 14
    	}
	},
	scales: {
		xAxes: [{
			ticks: {
				fontSize: 14
			}
		}],
		yAxes: [{
			ticks: {
				fontSize: 14
			}
		}]
	},
	plugins: {
        colorschemes: {
            scheme: 'brewer.Paired12'
        }
    }
};
var column_title = ['claim_sales', 'carrier_payment', 'margin', 'margin_rate'];
var column_title_sc = ['in_fee', 'out_fee', 'storage_fee'];

window.onload = function(){
	change();
	var v = document.getElementById('summary_category').value;
	if (v == 1) {
		changeGraphItemD();
	} else if (v == 2) {
		changeGraphItemDS();
	} else if (v == 3) {
		changeGraphItemSC();
	}
	
	if (v == 1 || v == 2) {
		changeGraphItemS();
	}
}

//グラフ項目プルダウン操作（配車データ）
function changeGraphItemD() {
	var graph_item = document.getElementById('d_graph_item').value;
	
	//表示する金額種別取得
	switch (graph_item){
		case '1':
			var column_name = column_title[0];
			break;
		case '2':
			var column_name = column_title[1];
			break;
		case '3':
			var column_name = column_title[2];
			break;
		case '4':
			var column_name = column_title[3];
			break;
		default:
			var column_name = null;
	}
	
	//テーブル取得
	var table = document.getElementById('d_table');
	
	//１行目以外を削除
	while (table.rows[1])table.deleteRow(1);
	
	//データ部生成
	for (var i = 0; i < summary_data_dispatch.length; i++) {
		var tr = document.createElement('tr');
		
		//見出し出力
		var th = document.createElement('th');
		th.textContent = summary_data_dispatch[i]['division_name'];
		tr.appendChild(th);
		
		//明細部出力
		var summary_data_list = summary_data_dispatch[i][column_name];
		for (var j = 0; j < summary_data_list.length; j++) {
			var td = document.createElement('td');
			td.style = 'width: 100px;text-align: right';
			
			if (column_name == column_title[3]) {
				//差益率は小数点第１位まで表示
				td.textContent = Number(summary_data_list[j]).toLocaleString( undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1});
			} else {
				td.textContent = Number(summary_data_list[j]).toLocaleString();
			}
			
			tr.appendChild(td);
		}
		
		table.appendChild(tr);
	}
	
	//グラフ表示
	drawGraphD(sales_category, column_name);
}

//グラフ項目プルダウン操作（月極その他情報）
function changeGraphItemS() {
	var graph_item = document.getElementById('s_graph_item').value;
	var sales_category = document.getElementById('sales_category').value;
	
	//表示する金額種別取得
	switch (graph_item){
		case '1':
			var column_name = column_title[0];
			break;
		case '2':
			var column_name = column_title[1];
			break;
		case '3':
			var column_name = column_title[2];
			break;
		case '4':
			var column_name = column_title[3];
			break;
		default:
			var column_name = null;
	}
	
	//テーブル取得
	var table = document.getElementById('s_table');
	
	//１行目以外を削除
	while (table.rows[1])table.deleteRow(1);
	
	//データ部生成
	for (var i = 0; i < summary_data_sales_correction.length; i++) {
		var tr = document.createElement('tr');
		
		//見出し出力
		var th = document.createElement('th');
		th.textContent = summary_data_sales_correction[i]['division_name'];
		tr.appendChild(th);
		
		//明細部出力
		var summary_data_list = summary_data_sales_correction[i]['summary_list'][sales_category][column_name];
		for (var j = 0; j < summary_data_list.length; j++) {
			var td = document.createElement('td');
			td.style = 'width: 100px;text-align: right';
			
			if (column_name == column_title[3]) {
				//差益率は小数点第１位まで表示
				td.textContent = Number(summary_data_list[j]).toLocaleString( undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1});
			} else {
				td.textContent = Number(summary_data_list[j]).toLocaleString();
			}
			
			tr.appendChild(td);
		}
		
		table.appendChild(tr);
	}
	
	//グラフ表示
	drawGraphS(sales_category, column_name);
}

//グラフ項目プルダウン操作（共配便データ）
function changeGraphItemDS() {
	var graph_item = document.getElementById('ds_graph_item').value;
	
	//表示する金額種別取得
	switch (graph_item){
		case '1':
			var column_name = column_title[0];
			break;
		case '2':
			var column_name = column_title[1];
			break;
		case '3':
			var column_name = column_title[2];
			break;
		case '4':
			var column_name = column_title[3];
			break;
		default:
			var column_name = null;
	}
	
	//テーブル取得
	var table = document.getElementById('ds_table');
	
	//１行目以外を削除
	while (table.rows[1])table.deleteRow(1);
	
	//データ部生成
	for (var i = 0; i < summary_data_dispatch_share.length; i++) {
		var tr = document.createElement('tr');
		
		//見出し出力
		var th = document.createElement('th');
		th.textContent = summary_data_dispatch_share[i]['division_name'];
		tr.appendChild(th);
		
		//明細部出力
		var summary_data_list = summary_data_dispatch_share[i][column_name];
		for (var j = 0; j < summary_data_list.length; j++) {
			var td = document.createElement('td');
			td.style = 'width: 100px;text-align: right';
			
			if (column_name == column_title[3]) {
				//差益率は小数点第１位まで表示
				td.textContent = Number(summary_data_list[j]).toLocaleString( undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1});
			} else {
				td.textContent = Number(summary_data_list[j]).toLocaleString();
			}
			
			tr.appendChild(td);
		}
		
		table.appendChild(tr);
	}
	
	//グラフ表示
	drawGraphDS(column_name);
}

//グラフ項目プルダウン操作（入出庫料・保管料データ）
function changeGraphItemSC() {
	var graph_item = document.getElementById('sc_graph_item').value;
	
	//表示する金額種別取得
	switch (graph_item){
		case '1':
			var column_name = column_title_sc[0];
			break;
		case '2':
			var column_name = column_title_sc[1];
			break;
		case '3':
			var column_name = column_title_sc[2];
			break;
		default:
			var column_name = null;
	}
	
	//テーブル取得
	var table = document.getElementById('sc_table');
	
	//１行目以外を削除
	while (table.rows[1])table.deleteRow(1);
	
	//データ部生成
	for (var i = 0; i < summary_data_stock.length; i++) {
		var tr = document.createElement('tr');
		
		//見出し出力
		var th = document.createElement('th');
		th.textContent = summary_data_stock[i]['division_name'];
		tr.appendChild(th);
		
		//明細部出力
		var summary_data_list = summary_data_stock[i][column_name];
		for (var j = 0; j < summary_data_list.length; j++) {
			var td = document.createElement('td');
			td.style = 'width: 100px;text-align: right';
			td.textContent = Number(summary_data_list[j]).toLocaleString();
			tr.appendChild(td);
		}
		
		table.appendChild(tr);
	}
	
	//グラフ表示
	drawGraphSC(column_name);
}

//グラフ表示（配車データ）
function drawGraphD(sales_category, column_name) {
	var datasets = [];
	for (let i=0; i<summary_data_dispatch.length; i++) {
		datasets.push({
                label: summary_data_dispatch[i]['division_name'],
                data: summary_data_dispatch[i][column_name],
                lineTension: 0,
                fill: false,
                borderWidth: 3});
	}
	
	var ctx = document.getElementById('dispatch_chart');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: caption_list,
            datasets: datasets
        },
        options: graph_options
    });
}

//グラフ表示（月極その他情報）
function drawGraphS(sales_category, column_name) {
	var datasets = [];
	for (let i=0; i<summary_data_sales_correction.length; i++) {
		datasets.push({
                label: summary_data_sales_correction[i]['division_name'],
                data: summary_data_sales_correction[i]['summary_list'][sales_category][column_name],
                lineTension: 0,
                fill: false,
                borderWidth: 3});
	}
	
	var ctx = document.getElementById('sales_correction_chart');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: caption_list,
            datasets: datasets
        },
        options: graph_options
    });
}

//グラフ表示（共配便データ）
function drawGraphDS(column_name) {
	var datasets = [];
	for (let i=0; i<summary_data_dispatch_share.length; i++) {
		datasets.push({
                label: summary_data_dispatch_share[i]['division_name'],
                data: summary_data_dispatch_share[i][column_name],
                lineTension: 0,
                fill: false,
                borderWidth: 3});
	}
	
	var ctx = document.getElementById('dispatch_share_chart');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: caption_list,
            datasets: datasets
        },
        options: graph_options
    });
}

//グラフ表示（入出庫料・保管料データ）
function drawGraphSC(column_name) {
	var datasets = [];
	for (let i=0; i<summary_data_stock.length; i++) {
		datasets.push({
                label: summary_data_stock[i]['division_name'],
                data: summary_data_stock[i][column_name],
                lineTension: 0,
                fill: false,
                borderWidth: 3});
	}
	
	var ctx = document.getElementById('stock_chart');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: caption_list,
            datasets: datasets
        },
        options: graph_options
    });
}

// 項目の活性状態制御
function change() {
	
	//集計単位操作
	var v = document.getElementById('aggregation_unit_date').value;
	flgD = (v=='2' || v=='3')?true:false;
	flgM = (v=='3')?true:false;
	document.getElementById('start_day').disabled = flgD;
	document.getElementById('end_day').disabled = flgD;
	document.getElementById('start_month').disabled = flgM;
	document.getElementById('end_month').disabled = flgM;
	
	//https://itsakura.com/javascript-display
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

