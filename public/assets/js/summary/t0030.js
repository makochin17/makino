window.onload = function(){
	change();
}

// 項目の活性状態制御
function change() {
	
	//庸車先ラジオボタン操作
	var clientRs = document.getElementsByName("carrier_radio");
	if(clientRs[1].checked) {
		document.getElementById('carrier_code').disabled = false;
		document.getElementById('carrier_search').disabled = false;
	} else {
		document.getElementById('carrier_code').disabled = true;
		document.getElementById('carrier_search').disabled = true;
	}
	
	//集計単位操作
	var v = document.getElementById('aggregation_unit_date').value;
	flgD = (v=='2' || v=='3')?true:false;
	flgM = (v=='3')?true:false;
	document.getElementById('start_day').disabled = flgD;
	document.getElementById('end_day').disabled = flgD;
	document.getElementById('start_month').disabled = flgM;
	document.getElementById('end_month').disabled = flgM;
}

// 検索ボタン押下時処理
function clientSearch(url_str) {
    var callback_id = 'callback_s0030'; //IDをふる
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=800,height=700');
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}