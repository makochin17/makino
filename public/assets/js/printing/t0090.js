window.onload = function(){
	change();
}

// 項目の活性状態制御
function change() {
	
	//庸車先ラジオボタン操作
	var carrierRs = document.getElementsByName("carrier_radio");
	if(carrierRs[1].checked) {
		document.getElementById('carrier_code').disabled = false;
		document.getElementById('carrier_search').disabled = false;
	} else {
		document.getElementById('carrier_code').disabled = true;
		document.getElementById('carrier_search').disabled = true;
	}
	
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