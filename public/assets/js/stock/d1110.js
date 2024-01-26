// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

// 画面遷移ボタン押下時処理
function onJump(url_str) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '1';
    f.method = "GET";
    f.action = url_str;
    f.submit();
    
    return true;
}

// 新規登録ボタン押下時処理
function onAdd(url_str) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '1';
    f.method = "POST";
    f.action = url_str;
    f.submit();
    
    return true;
}

// 編集ボタン押下時処理
function onEdit(url_str, stock_number) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '2';
    f.stock_number.value = stock_number;
    f.select_record .value = 1;
    f.method = "POST";
    f.action = url_str;
    f.submit();
    
    return true;
}

// 削除ボタン押下時処理
function onDelete(stock_number) {
	//削除確認
	var stock_number_p = ('0000000000' + stock_number).slice(-10);
	var flag = window.confirm(processing_msg1.replace('XXXXX', stock_number_p));
	if (!flag)return false;
	
    var f = document.forms["selectForm"];
    f.processing_division.value = '3';
    f.stock_number.value = stock_number;
    f.method = "POST";
    f.submit();
    
    return true;
}

// 入力ボタン押下時処理
function onInput(url_str, stock_number) {
    var f = document.forms["searchForm"];
    f.stock_number.value = stock_number;
    f.method = "POST";
    f.action = url_str;
    f.submit();
    
    return true;
}

// 得意先検索ボタン押下時処理
function onClientSearch(url_str) {

    var callback_id = 'callback_s0020';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

