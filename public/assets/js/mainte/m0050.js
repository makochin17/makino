// 項目の活性状態制御
window.onload = change;
function change() {
    var flag = (document.entryForm.processing_division.value == '1') ? false : true;
    document.entryForm.car_code_text.disabled = flag;
}

// 「入力項目クリア」の確認ダイアログ表示
function submitChkClear() {
    var flag = window.confirm (clear_msg);
    return flag;
}

// 「確定」の確認ダイアログ表示
function submitChkExecution() {
    var value = document.entryForm.processing_division.value;
    var flag = false;
    
    // 処理区分によってメッセージを変える
    if (value == '1') {
        flag = window.confirm (processing_msg1);
    } else if(value == '2') {
        flag = window.confirm (processing_msg2);
    } else if(value == '3') {
        flag = window.confirm (processing_msg3);
    }
    
    // hiddenに車両コードコピー
    document.entryForm.car_code.value = document.entryForm.car_code_text.value;
    
    return flag;
}

// 検索ボタン押下時処理
function carSearch(url_str) {
    var callback_id = 'callback_m0050'; //IDをふる
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1000,height=700');
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}