// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

$(function(){

    // 配車表出力
    $('#output').click(function(e){

        $('#output_dl').val(1);
        $('#excel_dl').val(0);
        $('#entryForm').submit();

        return e.preventDefault();
    });
    // エクセル出力
    $('#excel').click(function(e){

        $('#excel_dl').val(1);
        $('#output_dl').val(0);
        $('#entryForm').submit();

        return e.preventDefault();
    });

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

});

// 「入力項目クリア」の確認ダイアログ表示
function submitChkClear(kind) {

	var flag = true;
	if (!kind) {
	    var flag = window.confirm (clear_msg);
	}
    return flag;
}

// 社員検索ボタン押下時処理
function onCustomerSearch(url_str, list) {

    var callback_id = 'callback_s0010';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 車両検索ボタン押下時処理
function onCarSearch(url_str, list) {

    var callback_id = 'callback_s0050';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 傭車先検索ボタン押下時処理
function onCarrierSearch(url_str, list) {

    var callback_id = 'callback_s0030';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 得意先検索ボタン押下時処理
function onClientSearch(url_str, list) {

    var callback_id = 'callback_s0020';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}
