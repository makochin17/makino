window.onload = function(){
	change();
}

// 項目の活性状態制御
function change() {
	
	//得意先ラジオボタン操作
	var clientRs = document.getElementsByName("client_radio");
	if(clientRs[1].checked) {
		document.getElementById('client_code').disabled = false;
		document.getElementById('client_search').disabled = false;
	} else {
		document.getElementById('client_code').disabled = true;
		document.getElementById('client_search').disabled = true;
	}
	
}

// 検索ボタン押下時処理
function clientSearch(url_str) {
    var callback_id = 'callback_s0020'; //IDをふる
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}